/* Check Đạo Văn - giao diện, đặt hàng, tra cứu, tài khoản. Công thức giá khớp với inc/pricing.php */
(function () {
	'use strict';

	var $ = function (s, r) { return (r || document).querySelector(s); };
	var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
	var esc = function (s) { var d = document.createElement('div'); d.textContent = s == null ? '' : String(s); return d.innerHTML; };
	var money = function (n) { return Math.round(n).toLocaleString('vi-VN') + 'đ'; };
	var roundK = function (n) { return Math.round(n / 500) * 500; };
	var hasVS = typeof VS !== 'undefined';
	var post = function (data) {
		return fetch(VS.ajax, { method: 'POST', body: data, credentials: 'same-origin' }).then(function (r) { return r.json(); });
	};
	var params = new URLSearchParams(location.search);

	/* ================= Chung ================= */

	// Dấu phân cách " · " -> chấm tròn được định dạng (.sep), giữ nguyên chữ cho trình đọc màn hình.
	var SKIP = /^(SCRIPT|STYLE|TEXTAREA|INPUT|OPTION|SELECT|CODE|PRE|TITLE)$/;
	function decorateSeps(root) {
		if (!root || !root.querySelectorAll) return;
		var walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, {
			acceptNode: function (n) {
				if (n.nodeValue.indexOf('·') < 0) return NodeFilter.FILTER_REJECT;
				for (var p = n.parentNode; p && p !== root.parentNode; p = p.parentNode) {
					if (SKIP.test(p.nodeName) || (p.classList && (p.classList.contains('sep') || p.classList.contains('no-sep')))) return NodeFilter.FILTER_REJECT;
				}
				return NodeFilter.FILTER_ACCEPT;
			}
		});
		var nodes = [];
		while (walker.nextNode()) nodes.push(walker.currentNode);
		nodes.forEach(function (n) {
			var parts = n.nodeValue.split(/\s*·\s*/), frag = document.createDocumentFragment();
			parts.forEach(function (t, i) {
				if (i) { var s = document.createElement('span'); s.className = 'sep'; s.textContent = ' · '; frag.appendChild(s); }
				if (t) frag.appendChild(document.createTextNode(t));
			});
			n.parentNode.replaceChild(frag, n);
		});
	}
	decorateSeps(document.body);
	if ('MutationObserver' in window) {
		new MutationObserver(function (list) {
			list.forEach(function (m) { m.addedNodes.forEach(function (n) { if (n.nodeType === 1) decorateSeps(n); else if (n.nodeType === 3 && n.parentNode) decorateSeps(n.parentNode); }); });
		}).observe(document.body, { childList: true, subtree: true });
	}

	// Menu mobile
	var toggle = $('.nav-toggle'), nav = $('#nav');
	if (toggle && nav) {
		toggle.addEventListener('click', function () {
			var open = nav.classList.toggle('is-open');
			toggle.setAttribute('aria-expanded', open);
		});
		nav.addEventListener('click', function (e) {
			if (e.target.closest('a')) { nav.classList.remove('is-open'); toggle.setAttribute('aria-expanded', false); }
		});
	}
	// Menu con (bấm trên mobile, rê chuột trên desktop)
	$$('.nav__parent').forEach(function (b) {
		b.addEventListener('click', function () {
			var li = b.parentNode, open = !li.classList.contains('is-open');
			$$('.has-sub.is-open').forEach(function (x) { x.classList.remove('is-open'); });
			li.classList.toggle('is-open', open);
			b.setAttribute('aria-expanded', open);
		});
	});
	document.addEventListener('click', function (e) {
		if (!e.target.closest('.has-sub')) $$('.has-sub.is-open').forEach(function (x) { x.classList.remove('is-open'); });
	});

	// Header đổ bóng khi cuộn
	var header = $('.site-header');
	var onScroll = function () { header && header.classList.toggle('is-stuck', window.scrollY > 8); };
	window.addEventListener('scroll', onScroll, { passive: true });
	onScroll();

	// Hiện dần khi cuộn tới
	var reveals = $$('.reveal');
	if ('IntersectionObserver' in window) {
		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (en) {
				if (en.isIntersecting) { en.target.classList.add('is-in'); io.unobserve(en.target); }
			});
		}, { rootMargin: '0px 0px -6% 0px' });
		reveals.forEach(function (el, i) { el.style.transitionDelay = (i % 4) * 70 + 'ms'; io.observe(el); });
	} else {
		reveals.forEach(function (el) { el.classList.add('is-in'); });
	}

	// Sao chép
	document.addEventListener('click', function (e) {
		var b = e.target.closest('[data-copy]');
		if (!b) return;
		e.preventDefault();
		navigator.clipboard && navigator.clipboard.writeText(b.dataset.copy).then(function () {
			var t = b.textContent; b.textContent = 'Đã chép ✓';
			setTimeout(function () { b.textContent = t; }, 1500);
		});
	});

	// Hiện / ẩn mật khẩu
	$$('[data-pw]').forEach(function (b) {
		b.addEventListener('click', function () {
			var i = b.parentNode.querySelector('input');
			var show = i.type === 'password';
			i.type = show ? 'text' : 'password';
			b.textContent = show ? 'Ẩn' : 'Hiện';
		});
	});

	// Tab đăng nhập / đăng ký
	var auth = $('[data-auth]');
	if (auth) {
		document.addEventListener('click', function (e) {
			var t = e.target.closest('[data-auth-tab]');
			if (!t) return;
			e.preventDefault();
			var name = t.dataset.authTab;
			$$('.auth__tabs [data-auth-tab]', auth).forEach(function (b) { b.classList.toggle('is-active', b.dataset.authTab === name); });
			$$('[data-auth-panel]', auth).forEach(function (p) { p.hidden = p.dataset.authPanel !== name; });
		});
	}

	// Lọc kết quả thực tế
	var filter = $('[data-filter]');
	if (filter) {
		filter.addEventListener('click', function (e) {
			var b = e.target.closest('button');
			if (!b) return;
			$$('button', filter).forEach(function (x) { x.classList.toggle('is-active', x === b); });
			$$('[data-filter-list] > [data-cat]').forEach(function (el) {
				el.hidden = b.dataset.f !== 'all' && el.dataset.cat !== b.dataset.f;
				var card = el.firstElementChild; if (card) card.classList.add('is-in');
			});
		});
	}

	// Ước tính thu nhập cộng tác viên
	var calc = $('[data-calc]');
	if (calc) {
		var upd = function () {
			var n = +$('[data-in=n]', calc).value, v = +$('[data-in=v]', calc).value, pct = +calc.dataset.pct;
			$('[data-out=n]', calc).textContent = n;
			$('[data-out=v]', calc).textContent = money(v);
			$('[data-out=r]', calc).textContent = money(n * v * pct / 100);
		};
		calc.addEventListener('input', upd); upd();
	}

	// Mục lục bài viết
	var prose = $('[data-prose]'), tocBox = $('[data-toc]');
	if (prose && tocBox) {
		var heads = $$('h2', prose);
		if (heads.length >= 3) {
			var list = $('[data-toc-list]', tocBox);
			heads.forEach(function (h, i) {
				h.id = h.id || 'muc-' + (i + 1);
				list.insertAdjacentHTML('beforeend', '<li><a href="#' + h.id + '">' + esc(h.textContent) + '</a></li>');
			});
			tocBox.hidden = false;
			var links = $$('a', list);
			if ('IntersectionObserver' in window) {
				var tio = new IntersectionObserver(function (entries) {
					entries.forEach(function (en) {
						if (en.isIntersecting) links.forEach(function (a) { a.classList.toggle('is-active', a.getAttribute('href') === '#' + en.target.id); });
					});
				}, { rootMargin: '-20% 0px -70% 0px' });
				heads.forEach(function (h) { tio.observe(h); });
			}
		}
	}

	function toast(msg) {
		var t = document.createElement('div');
		t.textContent = msg;
		t.setAttribute('role', 'alert');
		t.className = 'toast';
		document.body.appendChild(t);
		setTimeout(function () { t.remove(); }, 3800);
	}

	// Modal
	var modal = $('#modal');
	function openModal(html) {
		$('#modal-content').innerHTML = html;
		modal.hidden = false;
		document.body.style.overflow = 'hidden';
	}
	function closeModal() { modal.hidden = true; document.body.style.overflow = ''; }
	if (modal) {
		modal.addEventListener('click', function (e) { if (e.target.closest('[data-close]')) closeModal(); });
		document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !modal.hidden) closeModal(); });
	}

	function payHtml(pay, total, code) {
		if (!pay) {
			return '<div class="ok-note">Chúng tôi sẽ liên hệ qua Zalo/điện thoại để hướng dẫn thanh toán đơn <b>' + esc(code) + '</b> (' + money(total) + ').</div>';
		}
		var row = function (k, v, copy, cls) {
			return '<div class="kv2__row"><span>' + k + '</span><b class="' + (cls || '') + '">' + v + '</b>' + (copy ? '<button type="button" class="copy" data-copy="' + esc(copy) + '">Chép</button>' : '') + '</div>';
		};
		return '<div class="paycard">' +
			'<div class="paycard__qr"><img src="' + esc(pay.qr) + '" alt="Mã QR thanh toán" loading="lazy"><span>Quét bằng app ngân hàng</span></div>' +
			'<div class="kv2">' +
			row('Ngân hàng', esc(pay.bank)) +
			row('Số tài khoản', esc(pay.account), pay.account) +
			row('Chủ tài khoản', esc(pay.holder)) +
			row('Số tiền', money(pay.amount), String(pay.amount), 'kv2__money') +
			row('Nội dung', esc(pay.content), pay.content, 'kv2__code') +
			'</div></div>';
	}

	var watch = $('[data-autorefresh]');
	if (watch) setTimeout(function () { if (!document.querySelector('input:focus, textarea:focus')) location.reload(); }, (+watch.dataset.autorefresh || 30) * 1000);

	function autoHtml(a) {
		if (!a || (!a.paid_at && !a.jobs.length)) return '';
		var li = function (cls, icon, name, sub) { return '<li class="' + cls + '"><i>' + icon + '</i><span>' + esc(name) + ' <small>' + esc(sub) + '</small></span></li>'; };
		var h = '<ul class="auto-steps">';
		if (a.paid_at) h += li('is-done', '✓', 'Đã nhận thanh toán', a.paid_at);
		a.jobs.forEach(function (j) { h += li(j.state === 'done' ? 'is-done' : (j.state === 'failed' ? 'is-manual' : 'is-run'), j.state === 'done' ? '✓' : '•', j.name, j.label); });
		a.manual.forEach(function (m) { h += li('is-manual', '•', m, 'Đang chờ kết quả...'); });
		return h + '</ul>';
	}

	// "Tôi đã chuyển khoản": yêu cầu hệ thống xác minh ngay
	document.addEventListener('click', function (e) {
		var b = e.target.closest('[data-pay-verify]');
		if (!b || !hasVS) return;
		var msg = b.parentNode.querySelector('[data-pay-verify-msg]');
		var fd = new FormData();
		fd.append('action', 'vs_pay_verify'); fd.append('code', b.dataset.code); fd.append('phone', b.dataset.phone || '');
		b.disabled = true; var t = b.textContent; b.textContent = 'Đang xác minh…';
		post(fd).then(function (j) {
			var m = (j.data && j.data.message) || 'Không xác minh được, vui lòng liên hệ hỗ trợ.';
			if (msg) { msg.textContent = m; msg.className = j.success && j.data.paid ? 'is-ok' : ''; }
			if (j.success && j.data.paid && !$('[data-pay-wait]')) setTimeout(function () { location.reload(); }, 1200);
		}).catch(function () { if (msg) msg.textContent = 'Lỗi kết nối, vui lòng thử lại.'; })
			.then(function () { setTimeout(function () { b.disabled = false; b.textContent = t; }, 4000); });
	});

	function waitHtml(waiting) {
		if (!waiting || !waiting.length) return '';
		return waiting.map(function (w) {
			return '<div class="rs__card rs__card--wait" aria-live="polite"><span class="rs__tool">' + esc(w.tool) + '</span>' +
				'<div class="wait"><span class="wait__ring" aria-hidden="true"></span><span class="wait__text">Đang chờ kết quả<span class="wait__dots" aria-hidden="true"><i></i><i></i><i></i></span></span></div>' +
				w.kinds.map(function (k) { return '<div class="rs__row"><span>' + esc(k) + '</span><i class="skel skel--num"></i></div><i class="skel skel--bar"></i>'; }).join('') +
				'</div>';
		}).join('');
	}

	/** Thẻ kết quả (giống trang tài khoản) + thẻ gợi ý giảm tỷ lệ nằm cạnh. */
	function rsCards(results, upsell, waiting) {
		var v = function (x) { return x == null ? '—' : String(x).replace('.', ',') + '%'; };
		var cls = function (x) { return x == null ? '' : (x < 20 ? 'lvl-ok' : (x < 30 ? 'lvl-mid' : 'lvl-bad')); };
		var h = (waiting && waiting.length ? waitHtml(waiting) : '') + (results || []).map(function (r) {
			return '<div class="rs__card"><span class="rs__tool">' + esc(r.tool) + '</span>' + [['plag', 'Đạo văn'], ['ai', 'Tỷ lệ AI']].map(function (k) {
				var x = r[k[0]]; if (x == null) return '';
				return '<div class="rs__row"><span>' + k[1] + '</span><b class="' + cls(x) + '">' + v(x) + '</b></div><div class="bar"><i class="bar__fill ' + (x < 20 ? 'bar__fill--ok' : 'bar__fill--bad') + '" style="width:' + Math.min(100, Math.max(2, x)) + '%"></i></div>';
			}).join('') + '</div>';
		}).join('') + (upsell || '');
		return h ? '<div class="rs rs--wide">' + h + '</div>' : '';
	}
	/** Báo cáo tải xuống: thẻ file, tên rút gọn, không tràn chữ. */
	function fcardsHtml(files, partial) {
		if (!files || !files.length) return '';
		return '<div class="dl-head">📄 Báo cáo đã sẵn sàng' + (partial ? '<small>Phần còn lại đang xử lý</small>' : '') + '</div><div class="fcards">' + files.map(function (f) {
			return '<a class="fcard" href="' + esc(f.url) + '" target="_blank" rel="noopener" download title="' + esc(f.name) + '"><span class="fcard__icon">PDF</span><span class="fcard__name">' + esc(f.short || f.name) + '</span><span class="fcard__btn">Tải xuống</span></a>';
		}).join('') + '</div>';
	}

	function filesHtml(results, files, waiting) {
		return rsCards(results, arguments[3], waiting) + fcardsHtml(files);
	}

	if (!hasVS) return;

	/* ================= Chuông thông báo (khách đã đăng nhập) ================= */
	(function () {
		var bell = $('[data-bell]');
		if (!bell) return;
		var btn = $('[data-bell-toggle]', bell), panel = $('[data-bell-panel]', bell), list = $('[data-bell-list]', bell), count = $('[data-bell-count]', bell);
		var last = parseInt(count.textContent, 10) || 0, loaded = false;
		var ICON = { order: '🧾', paid: '💳', result: '📊', done: '✅', cancel: '⛔', money: '💸', info: '🔔' };
		function setCount(n) { count.textContent = n > 99 ? '99+' : n; count.hidden = !n; }
		function render(items) {
			list.innerHTML = items.length ? items.map(function (n) {
				var tag = n.url ? 'a href="' + esc(n.url) + '"' : 'div';
				return '<' + tag + ' class="bell__item' + (n.read ? '' : ' is-new') + '" data-id="' + esc(n.id) + '">' +
					'<span class="bell__ic">' + (ICON[n.type] || ICON.info) + '</span>' +
					'<span class="bell__txt"><b>' + esc(n.title) + '</b>' + (n.text ? '<span>' + esc(n.text) + '</span>' : '') + '<small>' + esc(n.ago) + '</small></span>' +
					'</' + (n.url ? 'a' : 'div') + '>';
			}).join('') : '<div class="bell__empty">Chưa có thông báo nào.</div>';
		}
		function load(mark) {
			var fd = new FormData(); fd.append('action', 'vs_notices'); if (mark) fd.append('mark', mark);
			return post(fd).then(function (j) {
				if (!j || !j.success) return;
				setCount(j.data.unread);
				if (j.data.unread > last && loaded === 'poll') {
					var fresh = j.data.items.filter(function (n) { return !n.read; })[0];
					if (fresh) toast('🔔 ' + fresh.title);
				}
				last = j.data.unread;
				render(j.data.items);
				return j.data;
			});
		}
		function open(on) {
			panel.hidden = !on; btn.setAttribute('aria-expanded', on ? 'true' : 'false');
			if (on) load();
		}
		btn.addEventListener('click', function (e) { e.stopPropagation(); open(panel.hidden); });
		document.addEventListener('click', function (e) { if (!panel.hidden && !bell.contains(e.target)) open(false); });
		document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !panel.hidden) open(false); });
		$('[data-bell-readall]', bell).addEventListener('click', function () { load('all'); });
		list.addEventListener('click', function (e) {
			var it = e.target.closest('.bell__item.is-new');
			if (it) { var fd = new FormData(); fd.append('action', 'vs_notices'); fd.append('mark', it.dataset.id); navigator.sendBeacon ? navigator.sendBeacon(VS.ajax, fd) : post(fd); }
		});
		// Kiểm tra thông báo mới mỗi phút khi tab đang mở.
		loaded = 'poll';
		setInterval(function () { if (!document.hidden) load(); }, 60000);
	})();
	var P = VS.pricing;
	var modes = { plag: 'Chỉ đạo văn', ai: 'Chỉ tỷ lệ AI', combo: 'Đạo văn + AI' };

	/* ================= Báo giá nhanh (trang chủ) ================= */
	var quote = $('[data-quote]');
	if (quote) {
		var qUpd = function () {
			var tool = ($('input[name=q_tool]:checked', quote) || {}).value || 'turnitin';
			syncModes(quote, tool, 'q_mode');
			var mode = ($('input[name=q_mode]:checked', quote) || {}).value || 'combo';
			$('[data-quote-total]', quote).textContent = money(P.tools[tool][mode]);
			var go = $('[data-quote-go]', quote), u = new URL(go.href);
			u.searchParams.set('tool', tool); u.searchParams.set('mode', mode);
			go.href = u.toString();
		};
		quote.addEventListener('change', qUpd); qUpd();
	}

	/* ================= Tra cứu đơn ================= */
	var trackForm = $('#form-track'), trackBox = $('#track-result');
	var lvl = function (v) { return v == null ? '' : (v < 20 ? 'lvl-ok' : (v < 30 ? 'lvl-mid' : 'lvl-bad')); };
	var pct = function (v) { return v == null ? '—' : String(v).replace('.', ',') + '%'; };

	function renderTrack(d) {
		// Mỗi bước: [chưa tới, đang làm, đã xong]
		var steps = [['Đặt hàng', 'Đã đặt hàng', 'Đã đặt hàng'], ['Xử lý', 'Đang xử lý', 'Đã xử lý'], ['Hoàn thành', 'Hoàn thành', 'Đã hoàn thành']];
		var idx = { cho_thanh_toan: 0, dang_xu_ly: 1, hoan_thanh: 2 }[d.status];
		var html = '<div class="tr__head"><span class="tr__code">' + esc(d.code) + '</span><span class="status status--' + esc(d.status) + '">' + esc(d.label) + '</span></div>' +
			'<div class="tr__meta">' + esc(d.summary) + ' · <b>' + money(d.total) + '</b> · Đặt lúc ' + esc(d.date) + '</div>';
		if (idx != null) html += '<div class="progress">' + steps.map(function (s, i) {
			// Bước đã qua (hoặc bước cuối khi đơn hoàn thành) -> "Đã ..."; bước hiện tại -> "Đang ...".
			var done = i < idx || (i === idx && (d.status === 'hoan_thanh' || i === 0));
			return '<span class="' + (i <= idx ? 'is-done' : '') + '">' + (done ? s[2] : (i === idx ? s[1] : s[0])) + '</span>';
		}).join('') + '</div>';
		html += autoHtml(d.auto);
		var rsHtml = rsCards(d.results, d.upsell, d.waiting);
		if (rsHtml) html += '<h4 class="tr__h">Kết quả</h4>' + rsHtml;
		html += fcardsHtml(d.files, d.partial);
		if (d.pay) html += payHtml(d.pay, d.total, d.code);
		if (d.reduce) {
			var cur = d.reduce.current, base = trackForm.dataset.orderUrl;
			var link = function (t) {
				var u = new URL(base);
				u.searchParams.set('tab', 'reduce'); u.searchParams.set('linked', d.code); u.searchParams.set('current', cur);
				u.searchParams.set('pages', d.reduce.pages); u.searchParams.set('target', t); u.searchParams.set('phone', trackForm.phone.value);
				return u.toString();
			};
			html += '<div class="upsell"><div><b>Tỷ lệ đạo văn đang là ' + pct(cur) + '</b><span>Đặt giảm ngay từ đơn này - không cần gửi lại file, giảm thêm ' + d.reduce.discount + '%.</span></div><div class="upsell__btns">' +
				(cur > 30 ? '<a class="btn btn--primary btn--sm" href="' + esc(link(30)) + '">Giảm dưới 30%</a>' : '') +
				'<a class="btn btn--accent btn--sm" href="' + esc(link(20)) + '">Giảm dưới 20%</a></div></div>';
		}
		trackBox.innerHTML = html;
		trackBox.hidden = false;
	}

	if (trackForm) {
		trackForm.dataset.orderUrl = trackForm.dataset.orderUrl || (location.origin + '/dat-hang/');
		var track = function (code, phone) {
			var data = new FormData();
			data.append('action', 'vs_track_order'); data.append('code', code); data.append('phone', phone);
			var btn = trackForm.querySelector('button');
			btn.disabled = true;
			return post(data)
				.then(function (j) {
					if (!j.success) throw new Error((j.data && j.data.message) || 'Không tìm thấy đơn.');
					renderTrack(j.data);
				})
				.catch(function (e) {
					trackBox.hidden = false;
					trackBox.innerHTML = '<p style="margin:0;color:var(--danger)">' + esc(e.message) + '</p>';
				})
				.then(function () { btn.disabled = false; });
		};
		trackForm.addEventListener('submit', function (e) {
			e.preventDefault();
			track(trackForm.code.value.trim(), trackForm.phone.value.trim());
		});
		if (trackForm.code.value) trackForm.phone.focus({ preventScroll: true });
	}

	/* ================= Đặt hàng ================= */
	var formCheck = $('#form-check'), formReduce = $('#form-reduce');
	if (!formCheck || !formReduce) return;

	function showTab(name) {
		$$('.tabs__btn').forEach(function (b) {
			var on = b.dataset.tab === name;
			b.classList.toggle('is-active', on);
			b.setAttribute('aria-selected', on);
		});
		formCheck.hidden = name !== 'check';
		formReduce.hidden = name !== 'reduce';
	}
	$$('.tabs__btn').forEach(function (b) { b.addEventListener('click', function () { showTab(b.dataset.tab); }); });
	document.addEventListener('click', function (e) {
		var g = e.target.closest('[data-goto-tab]');
		if (!g) return;
		showTab(g.dataset.gotoTab);
		$('.tabs').scrollIntoView({ behavior: 'smooth', block: 'start' });
	});

	/** Khóa các hình thức mà phần mềm không hỗ trợ (giá 0) và tự chọn hình thức còn lại. */
	function syncModes(scope, tool, name) {
		var radios = $$('input[name=' + name + ']', scope);
		if (!tool || !P.tools[tool]) return;
		radios.forEach(function (r) {
			var ok = P.tools[tool][r.value] > 0;
			r.disabled = !ok;
			r.closest('label').classList.toggle('is-na', !ok);
			r.closest('label').title = ok ? '' : P.tools[tool].name + ' không hỗ trợ hình thức này';
		});
		var cur = radios.filter(function (r) { return r.checked; })[0];
		if (!cur || cur.disabled) {
			var pick = ['combo', 'plag', 'ai'].map(function (m) { return radios.filter(function (r) { return r.value === m && !r.disabled; })[0]; }).filter(Boolean)[0];
			if (pick) pick.checked = true;
		}
	}

	function calcCheck(f) {
		var tools = $$('input[name="tools[]"]:checked', f).map(function (i) { return i.value; });
		syncModes(f, tools[0], 'mode');
		var langNote = $('[data-ai-lang]', f), md = (f.querySelector('input[name=mode]:checked') || {}).value;
		if (langNote) langNote.hidden = !(['turnitin', 'ithenticate'].indexOf(tools[0]) >= 0 && (md === 'ai' || md === 'combo'));
		var lim = wordLimited(f), wr = $('[data-word-rule]', f);
		if (wr) wr.classList.toggle('is-active', lim);
		paintWords(f, $('[data-words]', f));
		var mode = (f.querySelector('input[name=mode]:checked') || {}).value || 'combo';
		var pages = Math.max(1, Math.min(2000, parseInt(f.pages.value, 10) || 1));
		var express = f.express.checked;
		var c = P.check, lines = [], sum = 0;
		if (!tools.length) return { error: 'Vui lòng chọn 1 phần mềm.', lines: [], total: 0 };
		tools = tools.slice(0, 1); // Mỗi đơn 1 phần mềm.
		tools.forEach(function (t) {
			var price = P.tools[t][mode];
			sum += price;
			lines.push([P.tools[t].name + ' · ' + modes[mode], money(price)]);
		});
		var blocks = Math.max(0, Math.ceil(pages / Math.max(1, c.pages_included)) - 1);
		var factor = 1 + blocks * c.extra_pct / 100;
		if (blocks) lines.push(['Phụ phí ' + pages + ' trang', '+' + money(sum * (factor - 1))]);
		var sub = sum * factor;
		if (express) lines.push(['Hỏa tốc 30 phút (+' + c.express_pct + '%)', '+' + money(sub * c.express_pct / 100)]);
		var total = sub * (express ? 1 + c.express_pct / 100 : 1);
		return { total: roundK(total), lines: lines, floor: 0 };
	}

	function tierMult(cur) {
		var tiers = P.reduce.tiers;
		for (var i = 0; i < tiers.length; i++) if (cur <= tiers[i][0]) return tiers[i][1];
		return tiers[tiers.length - 1][1];
	}

	function num(v) { var n = parseFloat(String(v == null ? '' : v).replace(',', '.')); return isNaN(n) ? null : n; }
	function unitPlag(t) {
		var r = P.reduce;
		if (t >= 30) return r.t30;
		if (t >= 20) return r.t20 + (r.t30 - r.t20) * (t - 20) / 10;
		return r.t20 * (1 + (20 - t) * r.step_pct / 100);
	}
	function unitAi(t) {
		var r = P.reduce;
		var k = t >= 20 ? Math.max(0.7, 1 - (t - 20) * 0.02) : 1 + (20 - t) * r.step_pct / 100;
		return r.ai_unit * k;
	}
	var GOAL_NAMES = { plag: 'đạo văn', ai: 'AI' };

	/** Hiện/ẩn thẻ đạo văn / AI theo lựa chọn và vẽ mũi tên "45% → 20%". */
	function syncGoal(f) {
		var goal = (f.querySelector('input[name=goal]:checked') || {}).value || 'plag';
		$$('[data-goal-box]', f).forEach(function (box) {
			var k = box.dataset.goalBox, on = goal === 'both' || goal === k;
			box.hidden = !on;
			$$('input', box).forEach(function (i) { i.required = on; });
		});
		[['plag', 'current', 'target'], ['ai', 'current_ai', 'target_ai']].forEach(function (x) {
			var el = $('[data-goal-arrow="' + x[0] + '"]', f), c = num(f[x[1]].value), t = num(f[x[2]].value);
			if (el) el.innerHTML = c != null && t != null ? '<s>' + c + '%</s> → <b>' + t + '%</b>' : '';
			var tIn = f[x[2]]; if (tIn && c != null) tIn.max = Math.max(0, Math.ceil(c) - 1);
			$$('[data-chips="' + x[2] + '"] button', f).forEach(function (bt) { bt.disabled = c != null && +bt.dataset.v >= c; bt.classList.toggle('is-on', +bt.dataset.v === t); });
		});
		return goal;
	}

	function calcReduce(f) {
		var r = P.reduce;
		var goal = syncGoal(f);
		var pages = parseInt(f.pages.value, 10) || 0;
		var dlKey = (f.querySelector('input[name=deadline]:checked') || {}).value || 'standard';
		var linked = f.linked.value.trim() !== '';
		if (pages < 1 || pages > r.max_pages) return { error: 'Số trang phải từ 1 đến ' + r.max_pages + '.', lines: [], total: 0 };

		var lines = [], parts = { plag: 0, ai: 0 };
		var spec = [['plag', 'current', 'target'], ['ai', 'current_ai', 'target_ai']];
		for (var i = 0; i < spec.length; i++) {
			var k = spec[i][0];
			if (goal !== 'both' && goal !== k) continue;
			var cur = num(f[spec[i][1]].value), tgt = num(f[spec[i][2]].value);
			if (cur == null || cur <= 0 || cur > 100) return { error: 'Nhập tỷ lệ ' + GOAL_NAMES[k] + ' hiện tại (1-100%).', lines: [], total: 0 };
			if (tgt == null || tgt < 0 || tgt >= cur) return { error: 'Mục tiêu ' + GOAL_NAMES[k] + ' phải nhỏ hơn tỷ lệ hiện tại (' + String(cur).replace('.', ',') + '%).', lines: [], total: 0 };
			var unit = (k === 'plag' ? unitPlag(tgt) : unitAi(tgt)) * tierMult(cur);
			parts[k] = pages * unit;
			lines.push([(k === 'plag' ? 'Đạo văn ' : 'AI ') + String(cur).replace('.', ',') + '% → dưới ' + tgt + '%', money(unit) + '/trang']);
		}
		var total = parts.plag + parts.ai;
		lines.push([pages + ' trang', money(total)]);
		if (goal === 'both' && r.both_pct < 100) {
			var save = parts.ai * (1 - r.both_pct / 100);
			lines.push(['Ưu đãi giảm cả hai (phần AI -' + (100 - r.both_pct) + '%)', '-' + money(save), 'is-disc']);
			total -= save;
		}
		var dl = r.deadlines[dlKey];
		if (dl.mult !== 1) lines.push([dl.label + ' (×' + String(dl.mult).replace('.', ',') + ')', '+' + money(total * (dl.mult - 1))]);
		total *= dl.mult;
		if (linked) { lines.push(['Ưu đãi từ đơn kiểm tra (-' + r.linked_pct + '%)', '-' + money(total * r.linked_pct / 100), 'is-disc']); total *= 1 - r.linked_pct / 100; }
		total = roundK(total);
		if (total < r.min) { lines.push(['Áp dụng giá tối thiểu', money(r.min)]); total = r.min; }
		return { total: total, lines: lines, floor: r.min };
	}

	// Chọn nhanh mục tiêu
	document.addEventListener('click', function (e) {
		var bt = e.target.closest('[data-chips] button');
		if (!bt || bt.disabled) return;
		var f = bt.closest('form'), name = bt.closest('[data-chips]').dataset.chips;
		f[name].value = bt.dataset.v;
		update(f);
	});

	function update(f) {
		var res = f === formCheck ? calcCheck(f) : calcReduce(f);
		var cp = f._coupon;
		// Mã giảm giá hoặc ưu đãi giới thiệu CTV (không cộng dồn) - khớp với inc/ajax.php
		var pctOff = cp ? cp.pct : (+VS.refPct || 0);
		if (!res.error && pctOff) {
			var after = Math.max(res.floor, roundK(res.total * (1 - pctOff / 100)));
			res.lines.push([cp ? 'Mã ' + cp.code + ' (-' + cp.pct + '%)' : 'Ưu đãi giới thiệu (-' + pctOff + '%)', '-' + money(res.total - after), 'is-disc']);
			res.total = after;
		}
		$('[data-lines]', f).innerHTML = res.lines.map(function (l) {
			return '<li class="' + (l[2] || '') + '"><span>' + esc(l[0]) + '</span><b>' + esc(l[1]) + '</b></li>';
		}).join('');
		var totalEl = $('[data-total]', f);
		if (totalEl.textContent !== money(res.total)) {
			totalEl.textContent = money(res.total);
			if (totalEl.animate) totalEl.animate([{ transform: 'scale(1.06)' }, { transform: 'none' }], { duration: 220 });
		}
		var err = $('[data-error]', f);
		if (err) { err.hidden = !res.error; err.textContent = res.error || ''; }
		f.querySelector('[type=submit]').disabled = !!res.error;
		return res;
	}

	function setupCoupon(f) {
		var input = $('[data-coupon] input', f), btn = $('[data-coupon-apply]', f), msg = $('[data-coupon-msg]', f);
		input.removeAttribute('name');
		function show(text, err) { msg.hidden = false; msg.textContent = text; msg.classList.toggle('is-err', !!err); }
		function apply() {
			var code = input.value.trim().toUpperCase();
			if (!code) { f._coupon = null; msg.hidden = true; update(f); return; }
			var d = new FormData();
			d.append('action', 'vs_coupon'); d.append('code', code); d.append('type', f.dataset.type);
			btn.disabled = true;
			post(d).then(function (j) {
				if (!j.success) throw new Error(j.data && j.data.message);
				f._coupon = { code: code, pct: j.data.pct };
				show('✓ Đã áp dụng mã ' + code + ' - giảm ' + j.data.pct + '%');
			}).catch(function (e) {
				f._coupon = null;
				show(e.message || 'Mã không hợp lệ.', true);
			}).then(function () { btn.disabled = false; update(f); });
		}
		btn.addEventListener('click', apply);
		input.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); apply(); } });
		input.addEventListener('input', function () {
			if (f._coupon && input.value.trim().toUpperCase() !== f._coupon.code) { f._coupon = null; msg.hidden = true; update(f); }
		});
	}

	function countPages(file) {
		var ext = (file.name.split('.').pop() || '').toLowerCase();
		var fromWords = function (w) { return Math.max(1, Math.ceil(w / 350)); };
		if (ext === 'docx' && window.JSZip) {
			return JSZip.loadAsync(file).then(function (zip) {
				var app = zip.file('docProps/app.xml');
				return (app ? app.async('string') : Promise.resolve('')).then(function (xml) {
					var m = xml.match(/<Pages>(\d+)<\/Pages>/);
					if (m && +m[1] > 0) return +m[1];
					var w = xml.match(/<Words>(\d+)<\/Words>/);
					if (w && +w[1] > 0) return fromWords(+w[1]);
					var doc = zip.file('word/document.xml');
					if (!doc) return null;
					return doc.async('string').then(function (x) {
						return fromWords(x.replace(/<[^>]+>/g, ' ').split(/\s+/).filter(Boolean).length);
					});
				});
			});
		}
		if (ext === 'pdf') {
			return file.arrayBuffer().then(function (buf) {
				var s = new TextDecoder('latin1').decode(new Uint8Array(buf));
				var n = (s.match(/\/Type\s*\/Page[^s]/g) || []).length;
				if (!n) n = (s.match(/\/Count\s+\d+/g) || []).reduce(function (m, x) { return Math.max(m, +x.replace(/\D/g, '')); }, 0);
				return n || null;
			});
		}
		if (ext === 'txt') return file.text().then(function (t) { return fromWords(t.split(/\s+/).filter(Boolean).length); });
		return Promise.resolve(null);
	}

	/** Đếm số từ: .docx, .txt, .rtf đọc trực tiếp; .pdf dùng pdf.js (tải khi cần). Trả về null nếu không đọc được. */
	function countWords(file) {
		var e = (file.name.split('.').pop() || '').toLowerCase();
		var words = function (t) { return (t.match(/[\p{L}\p{N}]+(?:['’-][\p{L}\p{N}]+)*/gu) || []).length; };
		if (e === 'docx' && window.JSZip) {
			return JSZip.loadAsync(file).then(function (zip) {
				var doc = zip.file('word/document.xml');
				return doc ? doc.async('string').then(function (x) { return words(x.replace(/<\/w:p>|<w:tab\/>|<w:br\/>/g, ' ').replace(/<[^>]+>/g, '').replace(/&[a-z]+;/g, ' ')); }) : null;
			});
		}
		if (e === 'txt') return file.text().then(words);
		if (e === 'rtf') return file.text().then(function (t) { return words(t.replace(/\{\\\*[^{}]*\}/g, ' ').replace(/\\[a-z]+-?\d* ?/gi, ' ').replace(/[{}]/g, ' ')); });
		if (e === 'pdf') {
			var load = window.pdfjsLib ? Promise.resolve() : new Promise(function (ok, no) {
				var sc = document.createElement('script');
				sc.src = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js';
				sc.onload = ok; sc.onerror = no; document.head.appendChild(sc);
			});
			return load.then(function () {
				pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
				return file.arrayBuffer();
			}).then(function (buf) { return pdfjsLib.getDocument({ data: buf }).promise; }).then(function (pdf) {
				var n = Math.min(pdf.numPages, 400), jobs = [];
				for (var i = 1; i <= n; i++) jobs.push(pdf.getPage(i).then(function (pg) { return pg.getTextContent(); }).then(function (tc) { return tc.items.map(function (it) { return it.str; }).join(' '); }));
				return Promise.all(jobs).then(function (t) { return words(t.join(' ')); });
			});
		}
		return Promise.resolve(null);
	}

	/** Giới hạn số từ chỉ áp dụng cho đơn kiểm tra bằng Turnitin / iThenticate. */
	function wordLimited(f) {
		if (f !== formCheck) return false;
		var t = f.querySelector('input[name="tools[]"]:checked');
		return !!t && (VS.wordTools || []).indexOf(t.value) >= 0;
	}
	function paintWords(f, box) {
		var n = f._words;
		if (!box || n == null) return;
		var lim = wordLimited(f), bad = lim && (n < VS.minWords || n > VS.maxWords);
		box.className = 'drop__words ' + (bad ? 'is-bad' : 'is-ok');
		box.textContent = (bad ? '✗ ' : '✓ ') + n.toLocaleString('vi-VN') + ' từ' + (!bad ? '' : (n < VS.minWords ? ' - Turnitin/iThenticate cần tối thiểu ' + VS.minWords + ' từ' : ' - vượt quá ' + VS.maxWords.toLocaleString('vi-VN') + ' từ của Turnitin/iThenticate'));
	}

	/** Hiển thị số từ dưới ô tải file, tô đỏ khi ngoài giới hạn. */
	function showWords(f, drop, file) {
		var box = $('[data-words]', drop);
		if (!box) { box = document.createElement('span'); box.className = 'drop__words'; box.setAttribute('data-words', ''); drop.appendChild(box); }
		f._words = null;
		if (!file) { box.textContent = ''; return; }
		box.className = 'drop__words'; box.textContent = 'Đang đếm số từ…';
		countWords(file).then(function (n) {
			if (n == null) { box.textContent = ''; return; }
			f._words = n;
			paintWords(f, box);
		}).catch(function () { box.textContent = ''; });
	}

	function setupDrop(f) {
		$$('[data-drop]', f).forEach(function (drop) { setupOneDrop(f, drop); });
	}
	function setupOneDrop(f, drop) {
		var input = drop.querySelector('input[type=file]'), label = $('[data-file-name]', drop);
		var hint = $('[data-pages-hint]', f), hintText = hint ? hint.textContent : '';
		input.addEventListener('change', function () {
			var file = input.files[0];
			drop.classList.toggle('has-file', !!file);
			label.textContent = file ? file.name : '';
			label.title = file ? file.name : '';
			if (input.name === 'file') showWords(f, drop, file);
			if (file && file.size > VS.maxMB * 1048576) toast('File phải nhỏ hơn ' + VS.maxMB + ' MB.');
			if (!file || !hint || input.name !== 'file') return;
			hint.textContent = 'Đang đọc số trang…';
			hint.classList.remove('is-ok');
			countPages(file).then(function (n) {
				if (n) {
					f.pages.value = Math.min(n, parseInt(f.pages.max, 10) || n);
					hint.textContent = '✓ Đã tự đọc: ' + n + ' trang (có thể sửa lại)';
					hint.classList.add('is-ok');
					update(f);
				} else {
					hint.textContent = 'Không đọc được số trang, vui lòng nhập tay.';
				}
			}).catch(function () { hint.textContent = hintText; });
		});
		['dragenter', 'dragover'].forEach(function (ev) { drop.addEventListener(ev, function () { drop.classList.add('is-over'); }); });
		['dragleave', 'drop'].forEach(function (ev) { drop.addEventListener(ev, function () { drop.classList.remove('is-over'); }); });
	}

	function setLinked(code) {
		var f = formReduce;
		f.linked.value = code || '';
		$('[data-linked-box]', f).hidden = !code;
		$('[data-linked-input]', f).hidden = !!code;
		$('[data-file-field]', f).hidden = !!code;
		$('[data-linked-code]', f).textContent = code || '';
		update(f);
	}
	$('[data-unlink]', formReduce).addEventListener('click', function () { setLinked(''); });

	[formCheck, formReduce].forEach(function (f) {
		f.addEventListener('input', function (e) { if (!e.target.closest('[data-coupon]')) update(f); });
		f.addEventListener('change', function (e) { if (!e.target.closest('[data-coupon]')) update(f); });
		setupDrop(f);
		setupCoupon(f);
		f.addEventListener('submit', function (e) { e.preventDefault(); submit(f); });
	});

	// Điền sẵn từ đường dẫn: ?tab=&tool=&mode=&linked=&current=&pages=&target=&phone=
	(function prefill() {
		var tab = params.get('tab');
		var tool = params.get('tool');
		if (tool && P.tools[tool]) {
			$$('input[name="tools[]"]', formCheck).forEach(function (cb) { cb.checked = cb.value === tool; });
		}
		var mode = params.get('mode');
		if (mode && modes[mode]) { var m = $('input[name=mode][value="' + mode + '"]', formCheck); if (m) m.checked = true; }
		var goalP = params.get('goal');
		if (goalP) { var gi = $('input[name=goal][value="' + goalP + '"]', formReduce); if (gi) gi.checked = true; }
		if (params.get('current')) formReduce.current.value = params.get('current');
		if (params.get('current_ai')) formReduce.current_ai.value = params.get('current_ai');
		if (params.get('target_ai')) formReduce.target_ai.value = params.get('target_ai');
		if (params.get('pages')) formReduce.pages.value = params.get('pages');
		if (params.get('target')) formReduce.target.value = params.get('target');
		if (params.get('phone') && !formReduce.phone.value) formReduce.phone.value = params.get('phone');
		update(formCheck);
		update(formReduce);
		if (params.get('linked')) setLinked(params.get('linked'));
		showTab(tab === 'reduce' || params.get('linked') ? 'reduce' : 'check');
	})();

	function submit(f) {
		var res = update(f);
		if (res.error) return;
		var name = f.elements.namedItem('name').value.trim(), phone = f.phone.value.replace(/\D/g, '');
		var file = f.querySelector('input[name=file]').files[0];
		var rep = f.querySelector('input[name=report]'); rep = rep && rep.files[0];
		var linked = f.linked && f.linked.value.trim();
		var ext = function (x) { return (x.name.split('.').pop() || '').toLowerCase(); };
		var msg = '';
		if (f === formReduce && !linked) {
			if (!file || !rep) msg = 'Vui lòng tải đủ 2 file: Word bản gốc và báo cáo PDF đạo văn/AI. Chưa có báo cáo? Bấm "Kiểm tra trước".';
			else if (ext(file) !== 'docx') msg = 'File bản gốc phải là Word (.docx).';
			else if (ext(rep) !== 'pdf') msg = 'Báo cáo đạo văn/AI phải là file PDF.';
			else if (rep.size > VS.maxMB * 1048576) msg = 'File báo cáo vượt quá ' + VS.maxMB + 'MB.';
		} else if (!file && !linked) msg = 'Vui lòng tải lên file tài liệu.';
		else if (f === formCheck && file && ['docx', 'pdf', 'txt', 'rtf'].indexOf(ext(file)) < 0) msg = 'Chỉ nhận file .docx, .pdf, .txt, .rtf.';
		if (!msg && file && file.size > VS.maxMB * 1048576) msg = 'File phải nhỏ hơn ' + VS.maxMB + ' MB.';
		else if (!msg && file && wordLimited(f) && f._words != null && f._words < VS.minWords) msg = 'Tài liệu chỉ có ' + f._words.toLocaleString('vi-VN') + ' từ - Turnitin/iThenticate cần tối thiểu ' + VS.minWords + ' từ (các phần mềm khác không giới hạn).';
		else if (!msg && file && wordLimited(f) && f._words != null && f._words > VS.maxWords) msg = 'Tài liệu có ' + f._words.toLocaleString('vi-VN') + ' từ - vượt quá ' + VS.maxWords.toLocaleString('vi-VN') + ' từ. Vui lòng tách thành nhiều đơn.';
		else if (name.length < 2) msg = 'Vui lòng nhập họ tên.';
		else if (!/^(0\d{9,10}|84\d{9,10})$/.test(phone)) msg = 'Số điện thoại không hợp lệ.';
		if (!msg) { var unc = $$('input[type=checkbox][required]', f).filter(function (c) { return !c.checked && !c.closest('[hidden]'); })[0]; if (unc) { msg = unc.dataset.msg || 'Vui lòng đánh dấu xác nhận.'; unc.focus(); } }
		if (msg) return toast(msg);

		var data = new FormData(f);
		data.append('action', 'vs_submit_order');
		data.append('nonce', VS.nonce);
		data.append('type', f.dataset.type);
		if (f._coupon) data.append('coupon', f._coupon.code);
		if (f.express && !f.express.checked) data.delete('express');

		var btn = f.querySelector('[type=submit]'), html = btn.innerHTML;
		btn.disabled = true; btn.textContent = 'Đang gửi đơn…';
		// Lấy mã bảo mật mới trước khi gửi: trang có thể là bản lưu đệm cũ của hosting.
		var fn = new FormData(); fn.append('action', 'vs_fresh_nonce');
		post(fn)
			.then(function (n) { if (n && n.success) { VS.nonce = n.data.nonce; data.set('nonce', VS.nonce); } }, function () {})
			.then(function () { return post(data); })
			.then(function (j) {
				if (!j.success) throw new Error((j.data && j.data.message) || 'Có lỗi xảy ra.');
				showSuccess(j.data, phone);
				$$('input[type=file]', f).forEach(function (i) { i.value = ''; });
				$$('[data-drop]', f).forEach(function (d) { d.classList.remove('has-file'); $('[data-file-name]', d).textContent = ''; });
				f.note.value = '';
				if (f === formReduce) setLinked('');
				update(f);
			})
			.catch(function (err) { toast(err.message || 'Không gửi được đơn, vui lòng thử lại.'); })
			.then(function () { btn.disabled = false; btn.innerHTML = html; });
	}

	function showSuccess(d, phone) {
		var c = d.contact || {};
		var acc = document.body.classList.contains('logged-in');
		openModal(
			'<div class="ok-head">' +
				'<div class="success-icon"><svg viewBox="0 0 24 24" width="30" height="30"><path d="M5 12.5l4.5 4.5L19 7.5" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg></div>' +
				'<div class="ok-head__text"><h3 id="modal-title">Đặt hàng thành công!</h3>' +
				'<p>Mã đơn <b class="modal__code">' + esc(d.code) + '</b><button type="button" class="copy" data-copy="' + esc(d.code) + '">Chép</button></p></div>' +
			'</div>' +
			'<div class="ok-sum"><span>' + esc(d.summary) + '</span><b>' + money(d.total) + '</b></div>' +
			(d.pay ? '<ol class="ok-steps"><li><b>1</b>Mở app ngân hàng, quét mã QR</li><li><b>2</b>Kiểm tra số tiền &amp; nội dung</li><li><b>3</b>Xác nhận - hệ thống tự cập nhật</li></ol>' : '') +
			payHtml(d.pay, d.total, d.code) +
			(d.pay ? '<div class="pay-wait" data-pay-wait><span class="spinner"></span><span>Đang chờ thanh toán… Trang sẽ tự cập nhật khi nhận được tiền.</span></div><div class="pay-verify"><button type="button" class="btn btn--outline btn--sm" data-pay-verify data-code="' + esc(d.code) + '" data-phone="' + esc(phone) + '">Tôi đã chuyển khoản</button><span data-pay-verify-msg></span></div>' : '') +
			'<div class="modal__actions">' +
			(acc ? '<a class="btn btn--primary" href="' + esc(d.account) + '">Xem đơn trong tài khoản</a>' : '<a class="btn btn--primary" href="' + esc(d.trackUrl) + '">Tra cứu đơn</a><a class="btn btn--outline" href="' + esc(d.register) + '">Tạo tài khoản quản lý đơn</a>') +
			(c.zalo ? '<a class="btn btn--ghost-dark" href="https://zalo.me/' + esc(String(c.zalo).replace(/\D/g, '')) + '" target="_blank" rel="noopener">Nhắn Zalo</a>' : '') +
			'</div>'
		);
		if (d.pay) watchPayment(d.code, phone);
	}

	function watchPayment(code, phone) {
		var box = $('[data-pay-wait]'), n = 0;
		var tick = function () {
			if (!box || !document.body.contains(box) || ++n > 180) return; // tối đa ~30 phút
			var fd = new FormData();
			fd.append('action', 'vs_pay_status'); fd.append('code', code); fd.append('phone', phone);
			post(fd).then(function (j) {
				if (!j.success) return setTimeout(tick, 10000);
				var s = j.data.status;
				if (s === 'cho_thanh_toan') return setTimeout(tick, 10000);
				box.classList.add('is-paid');
				box.innerHTML = '<span class="pay-wait__ok">✓</span><div><b>Đã nhận thanh toán!</b>' + autoHtml(j.data.auto) + filesHtml(j.data.results, j.data.files, j.data.waiting, j.data.upsell) + (s === 'hoan_thanh' ? '<span>Kết quả đã được gửi qua email và tài khoản của bạn.</span>' : (j.data.files && j.data.files.length ? '<span>Báo cáo đã sẵn sàng - bạn có thể tải ngay.</span>' : '')) + '</div>';
				['#modal-content .paycard', '#modal-content .ok-steps', '#modal-content .pay'].forEach(function (q) { var x = $(q); if (x) x.remove(); }); var pv = $('#modal-content .pay-verify'); if (pv) pv.remove();
				if (s !== 'hoan_thanh' && s !== 'huy') setTimeout(tick, 15000);
			}).catch(function () { setTimeout(tick, 15000); });
		};
		setTimeout(tick, 8000);
	}
})();
