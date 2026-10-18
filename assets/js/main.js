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

	function filesHtml(results, files, waiting) {
		var h = '';
		if (waiting && waiting.length) h += '<div class="rs rs--modal">' + waitHtml(waiting) + '</div>';
		if (results && results.length) {
			h += '<div class="results results--sm">' + results.map(function (r) {
				var v = function (x) { return x == null ? '—' : String(x).replace('.', ',') + '%'; };
				return '<div class="result"><h4>' + esc(r.tool) + '</h4>' +
					(r.plag != null ? '<div class="result__row"><span>Đạo văn</span><b>' + v(r.plag) + '</b></div>' : '') +
					(r.ai != null ? '<div class="result__row"><span>Tỷ lệ AI</span><b>' + v(r.ai) + '</b></div>' : '') + '</div>';
			}).join('') + '</div>';
		}
		if (files && files.length) {
			h += '<ul class="files">' + files.map(function (f) {
				return '<li><a href="' + esc(f.url) + '" target="_blank" rel="noopener" download>📄 Tải ' + esc(f.name) + '</a></li>';
			}).join('') + '</ul>';
		}
		return h;
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
		var steps = ['Đã đặt hàng', 'Đang xử lý', 'Hoàn thành'];
		var idx = { cho_thanh_toan: 0, dang_xu_ly: 1, hoan_thanh: 2 }[d.status];
		var html = '<div class="tr__head"><span class="tr__code">' + esc(d.code) + '</span><span class="status status--' + esc(d.status) + '">' + esc(d.label) + '</span></div>' +
			'<div class="tr__meta">' + esc(d.summary) + ' · <b>' + money(d.total) + '</b> · Đặt lúc ' + esc(d.date) + '</div>';
		if (idx != null) html += '<div class="progress">' + steps.map(function (s, i) { return '<span class="' + (i <= idx ? 'is-done' : '') + '">' + s + '</span>'; }).join('') + '</div>';
		html += autoHtml(d.auto);
		if (d.waiting && d.waiting.length) html += '<div class="rs rs--track">' + waitHtml(d.waiting) + '</div>';
		if (d.results.length) {
			html += '<div class="results">' + d.results.map(function (r) {
				return '<div class="result"><h4>' + esc(r.tool) + '</h4>' +
					'<div class="result__row"><span>Đạo văn</span><b class="' + lvl(r.plag) + '">' + pct(r.plag) + '</b></div>' +
					'<div class="result__row"><span>Tỷ lệ AI</span><b class="' + lvl(r.ai) + '">' + pct(r.ai) + '</b></div></div>';
			}).join('') + '</div>';
		}
		if (d.files.length) {
			if (d.partial) html += '<div class="dl-head">📄 Báo cáo đã sẵn sàng - tải xuống ngay<small>Phần còn lại đang xử lý</small></div>';
			html += '<ul class="files">' + d.files.map(function (f) {
				return '<li><a href="' + esc(f.url) + '" target="_blank" rel="noopener" download>📄 Tải ' + esc(f.name) + '</a></li>';
			}).join('') + '</ul>';
		}
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

	function calcReduce(f) {
		var r = P.reduce;
		var cur = parseFloat(String(f.current.value).replace(',', '.')) || 0;
		var pages = parseInt(f.pages.value, 10) || 0;
		var target = parseInt((f.querySelector('input[name=target]:checked') || {}).value || 30, 10);
		var dlKey = (f.querySelector('input[name=deadline]:checked') || {}).value || 'standard';
		var withAi = f.with_ai.checked;
		var linked = f.linked.value.trim() !== '';

		var t30 = f.querySelector('input[name=target][value="30"]');
		t30.disabled = cur > 0 && cur <= 30;
		if (t30.disabled && t30.checked) { f.querySelector('input[name=target][value="20"]').checked = true; target = 20; }

		if (cur <= 0 || cur > 100) return { error: 'Nhập tỷ lệ đạo văn hiện tại (1-100%).', lines: [], total: 0 };
		if (cur <= target) return { error: 'Tỷ lệ hiện tại (' + cur + '%) đã dưới ' + target + '%, bạn không cần giảm thêm.', lines: [], total: 0 };
		if (pages < 1 || pages > r.max_pages) return { error: 'Số trang phải từ 1 đến ' + r.max_pages + '.', lines: [], total: 0 };

		var unit = (target === 20 ? r.t20 : r.t30) * tierMult(cur);
		var dl = r.deadlines[dlKey];
		var lines = [['Mục tiêu dưới ' + target + '% (từ ' + cur + '%)', money(unit) + '/trang']];
		var total = pages * unit;
		lines.push([pages + ' trang × ' + money(unit), money(total)]);
		if (dl.mult !== 1) lines.push([dl.label + ' (×' + String(dl.mult).replace('.', ',') + ')', '+' + money(total * (dl.mult - 1))]);
		total *= dl.mult;
		if (withAi) { lines.push(['Giảm tỷ lệ AI (+' + r.ai_pct + '%)', '+' + money(total * r.ai_pct / 100)]); total *= 1 + r.ai_pct / 100; }
		if (linked) { lines.push(['Ưu đãi từ đơn kiểm tra (-' + r.linked_pct + '%)', '-' + money(total * r.linked_pct / 100), 'is-disc']); total *= 1 - r.linked_pct / 100; }
		total = roundK(total);
		if (total < r.min) { lines.push(['Áp dụng giá tối thiểu', money(r.min)]); total = r.min; }
		return { total: total, lines: lines, floor: r.min };
	}

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
		if (params.get('current')) formReduce.current.value = params.get('current');
		if (params.get('pages')) formReduce.pages.value = params.get('pages');
		var tg = params.get('target');
		if (tg) { var t = $('input[name=target][value="' + tg + '"]', formReduce); if (t) t.checked = true; }
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
			else if (['doc', 'docx'].indexOf(ext(file)) < 0) msg = 'File bản gốc phải là Word (.doc, .docx).';
			else if (ext(rep) !== 'pdf') msg = 'Báo cáo đạo văn/AI phải là file PDF.';
			else if (rep.size > VS.maxMB * 1048576) msg = 'File báo cáo vượt quá ' + VS.maxMB + 'MB.';
		} else if (!file && !linked) msg = 'Vui lòng tải lên file tài liệu.';
		if (!msg && file && file.size > VS.maxMB * 1048576) msg = 'File vượt quá ' + VS.maxMB + 'MB.';
		else if (name.length < 2) msg = 'Vui lòng nhập họ tên.';
		else if (!/^(0\d{9,10}|84\d{9,10})$/.test(phone)) msg = 'Số điện thoại không hợp lệ.';
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
				box.innerHTML = '<span class="pay-wait__ok">✓</span><div><b>Đã nhận thanh toán!</b>' + autoHtml(j.data.auto) + filesHtml(j.data.results, j.data.files, j.data.waiting) + (s === 'hoan_thanh' ? '<span>Kết quả đã được gửi qua email và tài khoản của bạn.</span>' : (j.data.files && j.data.files.length ? '<span>Báo cáo đã sẵn sàng - bạn có thể tải ngay.</span>' : '')) + '</div>';
				['#modal-content .paycard', '#modal-content .ok-steps', '#modal-content .pay'].forEach(function (q) { var x = $(q); if (x) x.remove(); }); var pv = $('#modal-content .pay-verify'); if (pv) pv.remove();
				if (s !== 'hoan_thanh' && s !== 'huy') setTimeout(tick, 15000);
			}).catch(function () { setTimeout(tick, 15000); });
		};
		setTimeout(tick, 8000);
	}
})();
