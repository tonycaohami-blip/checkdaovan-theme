/* Check Đạo Văn - hộp "Kết quả trả khách": tự đọc tỷ lệ từ báo cáo PDF (pdf.js), quản lý file trả khách. */
jQuery(function ($) {
	'use strict';
	var $box = $('.vs-rb');
	if (!$box.length) return;
	var $ids = $('#vs-report-ids'), $list = $('#vs-files'), $log = $('#vs-rb-log'), frame;

	if (window.pdfjsLib) {
		pdfjsLib.GlobalWorkerOptions.workerSrc = VSADM.worker;
	}

	function sync() {
		$ids.val($list.children('[data-id]').map(function () { return $(this).data('id'); }).get().join(','));
	}
	function log(msg, cls) {
		$log.prop('hidden', false).append($('<div>').addClass(cls || '').text(msg));
	}
	function setSrc($input, src, tag) {
		var $cell = $input.closest('.vs-rb-cell');
		$cell.find('.vs-rb-src').val(src);
		$cell.attr('class', 'vs-rb-cell vs-rb-cell--' + src);
		$cell.find('.vs-rb-tag').text(tag);
	}

	// Mở khóa số liệu tự động để sửa tay
	$box.on('click', '.vs-rb-edit', function () {
		var $i = $(this).closest('.vs-rb-cell').find('input[type=number]');
		$i.prop('readonly', false).trigger('focus');
		setSrc($i, 'manual', '● Nhập tay');
		$(this).remove();
	});
	// Gõ tay -> nguồn "Nhập tay"
	$box.on('input', 'input[type=number]', function () {
		setSrc($(this), $(this).val() === '' ? '' : 'manual', $(this).val() === '' ? '○ Chưa có' : '● Nhập tay');
	});

	/* ---------- Đọc PDF ---------- */
	function pdfText(url) {
		return pdfjsLib.getDocument({ url: url, withCredentials: true }).promise.then(function (pdf) {
			var pages = [], n = Math.min(pdf.numPages, 4);
			for (var i = 1; i <= n; i++) {
				pages.push(pdf.getPage(i).then(function (pg) {
					return pg.getTextContent().then(function (tc) { return tc.items.map(function (it) { return it.str; }).join(' '); });
				}));
			}
			return Promise.all(pages).then(function (t) { return t.join(' \n ').replace(/\s+/g, ' '); });
		});
	}

	var NUM = '(\\d{1,3}(?:[.,]\\d{1,2})?)\\s*%';
	function first(text, patterns) {
		for (var i = 0; i < patterns.length; i++) {
			var m = text.match(new RegExp(patterns[i], 'i'));
			if (m) {
				var v = parseFloat(m[1].replace(',', '.'));
				if (v >= 0 && v <= 100) return v;
			}
		}
		return null;
	}

	/** Nhận diện phần mềm + tỷ lệ từ nội dung báo cáo. */
	function detect(text, filename) {
		var hay = text + ' ' + filename, tool = null;
		// Thứ tự quan trọng: báo cáo Turnitin cũ có tiêu đề "ORIGINALITY REPORT" nên không dùng chữ "originality" đơn lẻ.
		if (/ithenticate/i.test(hay)) tool = 'ithenticate';
		else if (/copyleaks/i.test(hay)) tool = 'copyleaks';
		else if (/originality\s*\.?\s*ai\b/i.test(hay)) tool = 'originality';
		else if (/turnitin|feedback ?studio|similarity\s+index|integrity\s+overview|overall\s+similarity|detected\s+as\s+AI|trn:oid/i.test(hay)) tool = 'turnitin';

		var plag = first(text, [
			NUM + '\\s*Overall\\s+Similarity',
			NUM + '\\s*SIMILARITY\\s+INDEX',
			'Overall\\s+Similarity\\D{0,30}?' + NUM,
			'Similarity\\s+Index\\D{0,30}?' + NUM,
			'Similarity\\s+(?:Score|Report)?\\D{0,20}?' + NUM,
			NUM + '\\s*(?:Plagiarism|Matched\\s+Text|Identical)',
			'(?:Plagiarism|Matched\\s+Text)\\s*(?:Score|Detected)?\\D{0,20}?' + NUM
		]);
		var ai = first(text, [
			NUM + '\\s*detected\\s+as\\s+AI',
			NUM + '\\s*(?:of\\s+text\\s+)?(?:likely\\s+)?AI[- ]?(?:generated|written|writing|content)',
			'AI\\s+(?:writing|generated|content|score|detection)\\D{0,30}?' + NUM,
			NUM + '\\s*AI\\b',
			'\\bAI\\s*(?:Score|Likelihood|Content|Probability)?\\s*:?\\s*' + NUM
		]);
		var aiStar = /\*\s*%\s*detected\s+as\s+AI/i.test(text);
		return { tool: tool, plag: plag, ai: ai, aiStar: aiStar };
	}

	function targetRow(tool) {
		var $rows = $box.find('tr[data-row]');
		if (tool && $rows.filter('[data-row="' + tool + '"]').length) return $rows.filter('[data-row="' + tool + '"]');
		if ($rows.length === 1) return $rows.first();
		var $empty = $rows.filter(function () { return $(this).find('input[type=number]').filter(function () { return !this.value; }).length; });
		return $empty.first();
	}

	function fill($row, kind, value, file) {
		var $i = $row.find('input[data-kind="' + kind + '"]');
		if (!$i.length) return false;
		if ($i.prop('readonly')) { log('• ' + $row.data('name') + ' - ' + (kind === 'ai' ? 'AI' : 'Đạo văn') + ': giữ số liệu tự động từ API (' + $i.val() + '%), bỏ qua giá trị trong PDF.', 'is-skip'); return false; }
		$i.val(value);
		setSrc($i, 'pdf', '● Đọc từ PDF - kiểm tra lại');
		$i.closest('.vs-rb-cell').addClass('is-fresh');
		return true;
	}

	function handlePdf(att) {
		var name = att.get('filename') || att.get('title');
		log('Đang đọc ' + name + '…');
		return pdfText(att.get('url')).then(function (text) {
			var d = detect(text, name), $row = targetRow(d.tool), got = [];
			if (!$row.length) { log('✗ ' + name + ': không tìm thấy hàng phù hợp trong đơn.', 'is-err'); return; }
			if (d.plag !== null && fill($row, 'plag', d.plag, name)) got.push('Đạo văn ' + d.plag + '%');
			if (d.ai !== null && fill($row, 'ai', d.ai, name)) got.push('AI ' + d.ai + '%');
			if (d.aiStar) log('• ' + name + ': Turnitin ghi “*%” (dưới 20%, độ tin cậy thấp) - vui lòng nhập tay tỷ lệ AI nếu cần.', 'is-skip');
			if (got.length) log('✓ ' + name + ' → ' + $row.data('name') + (d.tool ? '' : ' (không nhận ra phần mềm, đã điền vào hàng trống)') + ': ' + got.join(', '), 'is-ok');
			else log('✗ ' + name + ': không tìm thấy tỷ lệ trong báo cáo - vui lòng nhập tay.', 'is-err');
		}).catch(function () {
			log('✗ ' + name + ': không đọc được file PDF (có thể là ảnh scan) - vui lòng nhập tay.', 'is-err');
		});
	}

	$('#vs-add-file').on('click', function (e) {
		e.preventDefault();
		frame = frame || wp.media({ title: 'Chọn báo cáo / file trả khách', button: { text: 'Thêm vào đơn' }, multiple: true });
		frame.off('select').on('select', function () {
			var chain = Promise.resolve();
			frame.state().get('selection').each(function (a) {
				if (!$list.children('[data-id="' + a.id + '"]').length) {
					$list.append($('<li>').attr('data-id', a.id).text('📎 ' + (a.get('filename') || a.get('title')) + ' ').append('<a href="#" class="vs-rm">Gỡ</a>'));
				}
				if (a.get('mime') === 'application/pdf' && window.pdfjsLib) {
					chain = chain.then(function () { return handlePdf(a); });
				}
			});
			sync();
			chain.then(function () {
				if ($box.find('.is-fresh').length) log('Kiểm tra lại các ô màu vàng rồi bấm “Cập nhật đơn” để lưu.', 'is-hint');
			});
		});
		frame.open();
	});
	$list.on('click', '.vs-rm', function (e) { e.preventDefault(); $(this).closest('li').remove(); sync(); });
});
