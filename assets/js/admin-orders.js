/* Check Đạo Văn - trang Quản trị đơn hàng (/quan-tri/): danh sách, chi tiết, thao tác nhanh, thông báo. */
(function () {
	'use strict';
	var root = document.querySelector('[data-adm]');
	if (!root || typeof VSA === 'undefined') return;

	var $ = function (s, r) { return (r || root).querySelector(s); };
	var $$ = function (s, r) { return Array.prototype.slice.call((r || root).querySelectorAll(s)); };
	var esc = function (s) { var d = document.createElement('div'); d.textContent = s == null ? '' : String(s); return d.innerHTML; };
	var money = function (n) { return Math.round(n || 0).toLocaleString('vi-VN') + 'đ'; };
	var pct = function (v) { return v == null ? '—' : String(v).replace('.', ',') + '%'; };

	function call(action, data) {
		var fd = new FormData();
		fd.append('action', action);
		fd.append('nonce', VSA.nonce);
		Object.keys(data || {}).forEach(function (k) { fd.append(k, data[k]); });
		return fetch(VSA.ajax, { method: 'POST', body: fd, credentials: 'same-origin' })
			.then(function (r) { return r.json(); })
			.then(function (j) { if (!j || !j.success) throw new Error((j && j.data && j.data.message) || 'Có lỗi xảy ra.'); return j.data; });
	}

	function toast(msg, kind) {
		var t = document.createElement('div');
		t.className = 'toast' + (kind ? ' toast--' + kind : '');
		t.setAttribute('role', 'status');
		t.textContent = msg;
		document.body.appendChild(t);
		setTimeout(function () { t.remove(); }, 4500);
	}

	/* ---------- Trạng thái lọc ---------- */
	var state = { status: 'all', type: 'all', q: '', page: 1 };
	var listEl = $('[data-adm-list]'), pagerEl = $('[data-adm-pager]');

	function badge(st, label) { return '<span class="status status--' + esc(st) + '">' + esc(label) + '</span>'; }

	function renderList(d) {
		Object.keys(d.stats).forEach(function (k) {
			var el = $('[data-s="' + k + '"]');
			if (el) el.textContent = /^rev_/.test(k) ? money(d.stats[k]) : d.stats[k];
			var c = $('[data-c="' + k + '"]');
			if (c) c.textContent = d.stats[k];
		});
		var cAll = $('[data-c="all"]'); if (cAll) cAll.textContent = d.stats.all;
		if (!d.items.length) {
			listEl.innerHTML = '<div class="adm-empty">Không có đơn phù hợp.</div>';
		} else {
			listEl.innerHTML = '<div class="adm-row adm-row--head"><span>Mã đơn</span><span>Khách hàng</span><span>Dịch vụ</span><span>Tổng tiền</span><span>Trạng thái</span><span>Thời gian</span></div>' +
				d.items.map(function (o) {
					return '<button type="button" class="adm-row" data-open="' + o.id + '">' +
						'<span class="adm-code"><b>' + esc(o.code) + '</b><small>' + (o.type === 'reduce' ? 'Giảm tỷ lệ' : 'Kiểm tra') + '</small></span>' +
						'<span class="adm-cust"><b>' + esc(o.name || '—') + '</b><small>' + esc(o.phone || '') + '</small></span>' +
						'<span class="adm-sum">' + esc(o.summary) + '</span>' +
						'<span class="adm-total">' + esc(o.total) + '</span>' +
						'<span>' + badge(o.status, o.label) + (o.auto ? '<small class="adm-auto">⚙ đang tự động</small>' : '') + '</span>' +
						'<span class="adm-time"><b>' + esc(o.date) + '</b><small>' + esc(o.ago) + '</small></span>' +
						'</button>';
				}).join('');
		}
		pagerEl.innerHTML = d.pages > 1 ? Array.apply(null, { length: d.pages }).map(function (_, i) {
			return '<button type="button" class="' + (i + 1 === d.page ? 'is-active' : '') + '" data-page="' + (i + 1) + '">' + (i + 1) + '</button>';
		}).join('') : '';
		$('[data-adm-updated]').textContent = 'cập nhật lúc ' + new Date().toLocaleTimeString('vi-VN');
	}

	function loadList(quiet) {
		if (!quiet) listEl.classList.add('is-loading');
		return call('vs_adm_orders', state).then(renderList).catch(function (e) { if (!quiet) toast(e.message, 'err'); })
			.then(function () { listEl.classList.remove('is-loading'); });
	}

	$$('[data-adm-tabs] button').forEach(function (b) {
		b.addEventListener('click', function () {
			$$('[data-adm-tabs] button').forEach(function (x) { x.classList.toggle('is-active', x === b); });
			state.status = b.dataset.status; state.page = 1; loadList();
		});
	});
	$('[data-adm-type]').addEventListener('change', function (e) { state.type = e.target.value; state.page = 1; loadList(); });
	var qTimer;
	$('[data-adm-q]').addEventListener('input', function (e) {
		clearTimeout(qTimer);
		qTimer = setTimeout(function () { state.q = e.target.value.trim(); state.page = 1; loadList(); }, 350);
	});
	pagerEl.addEventListener('click', function (e) {
		var b = e.target.closest('[data-page]'); if (!b) return;
		state.page = +b.dataset.page; loadList(); window.scrollTo({ top: root.offsetTop, behavior: 'smooth' });
	});
	listEl.addEventListener('click', function (e) { var r = e.target.closest('[data-open]'); if (r) openOrder(+r.dataset.open); });

	/* ---------- Chi tiết đơn ---------- */
	var drawer = $('[data-adm-drawer]'), detail = $('[data-adm-detail]'), current = 0;

	function renderDetail(o) {
		current = o.id;
		var kv = function (k, v) { return v ? '<dt>' + esc(k) + '</dt><dd>' + v + '</dd>' : ''; };
		var acts = [];
		if (o.status === 'cho_thanh_toan') acts.push('<button class="btn btn--primary btn--sm" data-act="paid">💳 Xác nhận đã thanh toán</button>');
		if (o.status === 'dang_xu_ly' || o.status === 'cho_thanh_toan') acts.push('<button class="btn btn--accent btn--sm" data-act="done">✅ Hoàn thành &amp; báo khách</button>');
		if (o.status !== 'huy' && o.status !== 'hoan_thanh') acts.push('<button class="btn btn--outline btn--sm adm-danger" data-act="cancel">Hủy đơn</button>');
		if (o.status === 'huy' || o.status === 'hoan_thanh') acts.push('<button class="btn btn--outline btn--sm" data-act="reopen">Mở lại đơn</button>');
		acts.push('<a class="btn btn--outline btn--sm" href="' + esc(o.edit) + '" target="_blank" rel="noopener">✏️ Nhập kết quả / báo cáo</a>');

		detail.innerHTML =
			'<div class="adm-d__head"><div><small>' + esc(o.title) + '</small><h2>' + esc(o.code) + '</h2></div>' + badge(o.status, o.label) + '</div>' +
			'<div class="adm-d__actions">' + acts.join('') + '</div>' +
			'<div class="adm-d__grid">' +
				'<section><h3>Khách hàng</h3><dl class="kv">' +
					kv('Họ tên', esc(o.customer.name)) +
					kv('Điện thoại', o.customer.phone ? '<a href="tel:' + esc(o.customer.phone) + '">' + esc(o.customer.phone) + '</a>' : '') +
					kv('Email', o.customer.email ? '<a href="mailto:' + esc(o.customer.email) + '">' + esc(o.customer.email) + '</a>' : '') +
					kv('Tài khoản', o.customer.account ? '<a href="' + esc(o.customer.account) + '" target="_blank" rel="noopener">Xem tài khoản</a>' : 'Khách vãng lai') +
					kv('Ghi chú của khách', esc(o.customer.note)) +
				'</dl></section>' +
				'<section><h3>Đơn hàng</h3><dl class="kv">' +
					kv('Dịch vụ', esc(o.summary)) +
					Object.keys(o.details).map(function (k) { return kv(k, esc(o.details[k])); }).join('') +
					kv('Tổng tiền', '<b>' + esc(o.total) + '</b>') +
					kv('Thanh toán', o.paid_at ? esc(o.paid_at) + (o.paid_via ? ' · ' + esc(o.paid_via) : '') : '<span class="muted">Chưa thanh toán</span>') +
					kv('Hoàn thành', esc(o.done_at)) +
				'</dl></section>' +
			'</div>' +
			(o.files.length ? '<section><h3>File khách gửi</h3><div class="sent-files">' + o.files.map(function (f) {
				var ext = (f.name.split('.').pop() || '').toUpperCase();
				return '<a class="sent-file" href="' + esc(f.url) + '" title="' + esc(f.name) + '"><span class="sent-file__ext sent-file__ext--' + esc(ext.toLowerCase()) + '">' + esc(ext) + '</span><span class="sent-file__body"><span class="sent-file__name">' + esc(f.short) + '</span><span class="sent-file__meta">' + esc(f.label) + '</span></span><span class="sent-file__dl">⬇</span></a>';
			}).join('') + '</div></section>' : '') +
			'<section><h3>Kết quả' + (o.auto ? ' <small class="adm-auto">⚙ đang chạy tự động</small>' : '') + '</h3>' +
				(o.results.length ? '<div class="adm-results">' + o.results.map(function (r) {
					return '<div><b>' + esc(r.tool) + '</b><span>Đạo văn <i>' + pct(r.plag) + '</i></span><span>AI <i>' + pct(r.ai) + '</i></span></div>';
				}).join('') + '</div>' : '<p class="muted">Chưa có kết quả.</p>') +
				(o.reports.length ? '<ul class="files">' + o.reports.map(function (f) { return '<li><a href="' + esc(f.url) + '" target="_blank" rel="noopener">📄 ' + esc(f.name) + '</a></li>'; }).join('') + '</ul>' : '') +
			'</section>' +
			'<section><h3>Ghi chú nội bộ</h3><textarea rows="3" data-note placeholder="Chỉ quản trị viên xem được">' + esc(o.note) + '</textarea><button class="btn btn--outline btn--sm" data-act="note">Lưu ghi chú</button></section>' +
			(o.log.length ? '<section><h3>Nhật ký</h3><ul class="adm-log">' + o.log.map(function (l) { return '<li><small>' + esc(l.ago) + '</small>' + esc(l.msg) + '</li>'; }).join('') + '</ul></section>' : '');
	}

	function openOrder(id) {
		drawer.hidden = false;
		document.body.classList.add('adm-lock');
		detail.innerHTML = '<div class="adm-empty">Đang tải…</div>';
		call('vs_adm_order', { id: id }).then(renderDetail).catch(function (e) { detail.innerHTML = '<div class="adm-empty">' + esc(e.message) + '</div>'; });
	}
	function closeOrder() { drawer.hidden = true; document.body.classList.remove('adm-lock'); current = 0; }
	$$('[data-adm-close]').forEach(function (b) { b.addEventListener('click', closeOrder); });
	document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !drawer.hidden) closeOrder(); });

	detail.addEventListener('click', function (e) {
		var b = e.target.closest('[data-act]'); if (!b) return;
		var act = b.dataset.act, extra = {};
		if (act === 'done') {
			if (!confirm('Đánh dấu đơn hoàn thành?\nHãy chắc chắn đã nhập kết quả / tải báo cáo cho khách.')) return;
			extra.mail = confirm('Gửi email kết quả cho khách ngay?') ? 1 : 0;
		}
		if (act === 'cancel' && !confirm('Hủy đơn này? Khách sẽ nhận được thông báo.')) return;
		if (act === 'paid' && !confirm('Xác nhận đã nhận đủ tiền cho đơn này?')) return;
		if (act === 'note') extra.note = $('[data-note]', detail).value;
		b.disabled = true;
		call('vs_adm_action', Object.assign({ id: current, act: act }, extra))
			.then(function (d) { toast(d.message, 'ok'); renderDetail(d.order); loadList(true); })
			.catch(function (err) { toast(err.message, 'err'); b.disabled = false; });
	});

	/* ---------- Thông báo quản trị: chuông, âm báo, thông báo trình duyệt ---------- */
	var bell = $('[data-adm-bell]'), bPanel = $('[data-adm-bell-panel]'), bList = $('[data-adm-bell-list]'), bCount = $('[data-adm-bell-count]');
	var soundBtn = $('[data-adm-sound]'), lastT = null, baseTitle = document.title;
	var soundOn = false;
	try { soundOn = localStorage.getItem('vsAdmSound') === '1'; } catch (e) {}
	function paintSound() { soundBtn.textContent = soundOn ? '🔔 Âm báo: bật' : '🔕 Bật âm báo'; soundBtn.classList.toggle('is-on', soundOn); }
	paintSound();
	soundBtn.addEventListener('click', function () {
		soundOn = !soundOn;
		try { localStorage.setItem('vsAdmSound', soundOn ? '1' : '0'); } catch (e) {}
		if (soundOn && 'Notification' in window && Notification.permission === 'default') Notification.requestPermission();
		if (soundOn) beep();
		paintSound();
	});
	function beep() {
		try {
			var C = window.AudioContext || window.webkitAudioContext, ctx = new C();
			[0, 0.18].forEach(function (t, i) {
				var o = ctx.createOscillator(), g = ctx.createGain();
				o.frequency.value = i ? 1046 : 784; o.type = 'sine';
				g.gain.setValueAtTime(0.0001, ctx.currentTime + t);
				g.gain.exponentialRampToValueAtTime(0.25, ctx.currentTime + t + 0.02);
				g.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + t + 0.16);
				o.connect(g); g.connect(ctx.destination); o.start(ctx.currentTime + t); o.stop(ctx.currentTime + t + 0.18);
			});
		} catch (e) {}
	}
	var ICON = { new: '🆕', paid: '💰', done: '✅', result: '📄', warn: '⚠️', bell: '🔔', money: '💸', info: 'ℹ️' };
	function renderFeed(d) {
		bCount.textContent = d.unread > 99 ? '99+' : d.unread;
		bCount.hidden = !d.unread;
		document.title = (d.unread ? '(' + d.unread + ') ' : '') + baseTitle;
		bList.innerHTML = d.items.length ? d.items.map(function (n) {
			return '<button type="button" class="bell__item' + (n.read ? '' : ' is-new') + '"' + (n.order ? ' data-open="' + n.order + '"' : '') + '>' +
				'<span class="bell__ic">' + (ICON[n.type] || ICON.info) + '</span>' +
				'<span class="bell__txt"><b>' + esc(n.title.replace(/^\S+\s/, '')) + '</b>' + (n.text ? '<span>' + esc(n.text) + '</span>' : '') + '<small>' + esc(n.ago) + '</small></span></button>';
		}).join('') : '<div class="bell__empty">Chưa có thông báo.</div>';
		if (lastT !== null && d.latest > lastT) {
			var fresh = d.items.filter(function (n) { return n.t > lastT; });
			fresh.slice(0, 3).forEach(function (n) { toast(n.title, 'info'); });
			if (soundOn) {
				beep();
				if ('Notification' in window && Notification.permission === 'granted' && document.hidden) {
					try { new Notification('Check Đạo Văn - quản trị', { body: fresh[0].title, tag: 'vs-adm' }); } catch (e) {}
				}
			}
			loadList(true);
			if (current) call('vs_adm_order', { id: current }).then(renderDetail).catch(function () {});
		}
		lastT = d.latest;
	}
	function loadFeed(seen) { return call('vs_adm_feed', seen ? { seen: 1 } : {}).then(renderFeed).catch(function () {}); }
	$('[data-adm-bell-toggle]').addEventListener('click', function (e) {
		e.stopPropagation();
		var on = bPanel.hidden; bPanel.hidden = !on;
		this.setAttribute('aria-expanded', on ? 'true' : 'false');
	});
	document.addEventListener('click', function (e) { if (!bPanel.hidden && !bell.contains(e.target)) bPanel.hidden = true; });
	$('[data-adm-bell-seen]').addEventListener('click', function () { loadFeed(true); });
	bList.addEventListener('click', function (e) {
		var it = e.target.closest('[data-open]');
		if (it) { bPanel.hidden = true; openOrder(+it.dataset.open); }
	});

	/* ---------- Khởi động + tự làm mới ---------- */
	loadList();
	loadFeed();
	setInterval(function () {
		if (document.hidden && !soundOn) return;
		loadFeed();
		if (!document.hidden) loadList(true);
	}, 20000);
})();
