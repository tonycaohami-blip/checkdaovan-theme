/* =========================================================
   App quản trị đơn hàng (app.<tên miền>) - Check Đạo Văn
   Không dùng thư viện ngoài. Dữ liệu lấy qua admin-ajax của WordPress.
   ========================================================= */
(function () {
	'use strict';

	var C = window.VSAPP || {};
	var root = document.getElementById('app');

	/* ---------- Tiện ích ---------- */
	function $(s, r) { return (r || document).querySelector(s); }
	function $$(s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); }
	function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
	function money(n) { return Math.round(n || 0).toLocaleString('vi-VN') + 'đ'; }
	function short(n) {
		n = n || 0;
		if (n >= 1e6) return (Math.round(n / 1e5) / 10).toString().replace('.', ',') + 'tr';
		if (n >= 1e3) return Math.round(n / 1e3) + 'k';
		return String(n);
	}
	function pct(v) { return v == null ? '—' : String(v).replace('.', ',') + '%'; }
	function num(n) { return (n || 0).toLocaleString('vi-VN'); }
	function dur(s) {
		if (!s) return '—';
		var h = Math.floor(s / 3600), m = Math.round((s % 3600) / 60);
		return h ? h + ' giờ ' + (m ? m + ' phút' : '') : m + ' phút';
	}
	function delta(cur, prev) {
		if (!prev) return cur ? '<span class="delta delta--up">Mới</span>' : '';
		var d = Math.round((cur - prev) / prev * 100);
		return '<span class="delta delta--' + (d >= 0 ? 'up' : 'down') + '">' + (d >= 0 ? '▲ ' : '▼ ') + Math.abs(d) + '%</span>';
	}
	// Đường dẫn tải file của WordPress luôn trỏ về tên miền chính; đổi về tên miền app. để dùng phiên đăng nhập hiện tại.
	function fixUrl(u) { return String(u || '').replace(/^https?:\/\/[^\/]+(?=\/wp-admin\/admin-post\.php)/, ''); }
	function debounce(fn, ms) { var t; return function () { var a = arguments, s = this; clearTimeout(t); t = setTimeout(function () { fn.apply(s, a); }, ms); }; }
	function store(k, v) { try { if (v === undefined) return localStorage.getItem('vsapp_' + k); localStorage.setItem('vsapp_' + k, v); } catch (e) { return null; } }
	function phoneDigits(p) { return String(p || '').replace(/\D/g, ''); }

	/* ---------- Biểu tượng ---------- */
	var I = {
		home: '<path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V20a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1V9.5"/>',
		orders: '<path d="M9 4h6a1 1 0 0 1 1 1v1H8V5a1 1 0 0 1 1-1z"/><path d="M8 6H6a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-2"/><path d="M8 12h8M8 16h5"/>',
		users: '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><path d="M16 4.5a3.5 3.5 0 0 1 0 7M18.5 20a6.5 6.5 0 0 0-2.5-5.1"/>',
		aff: '<circle cx="12" cy="12" r="9"/><path d="M14.8 9.2a2.9 2.9 0 0 0-2.8-1.7c-1.6 0-2.8.9-2.8 2.2 0 3 5.8 1.6 5.8 4.6 0 1.3-1.3 2.2-3 2.2a3 3 0 0 1-2.9-1.8M12 6v1.5M12 16.5V18"/>',
		bell: '<path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.9 1.9 0 0 0 3.4 0"/>',
		cog: '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/>',
		search: '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
		menu: '<path d="M4 6h16M4 12h16M4 18h16"/>',
		x: '<path d="M18 6 6 18M6 6l12 12"/>',
		sun: '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
		moon: '<path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/>',
		out: '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5M21 12H9"/>',
		ext: '<path d="M15 3h6v6M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>',
		money: '<rect x="2" y="6" width="20" height="13" rx="2"/><circle cx="12" cy="12.5" r="2.5"/><path d="M6 10v5M18 10v5"/>',
		trend: '<path d="m3 17 6-6 4 4 8-8"/><path d="M15 7h6v6"/>',
		chart: '<path d="M3 3v18h18"/><path d="M7 15v2M11 11v6M15 7v10M19 12v5"/>',
		cart: '<circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path d="M2 3h3l2.7 12.4a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.5L21 8H6"/>',
		check: '<path d="m5 12 5 5L20 7"/>',
		clock: '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
		card: '<rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/>',
		chev: '<path d="m9 6 6 6-6 6"/>',
		phone: '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2z"/>',
		mail: '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 6-10 7L2 6"/>',
		chat: '<path d="M21 11.5a8.4 8.4 0 0 1-12.4 7.4L3 21l2.1-5.6A8.5 8.5 0 1 1 21 11.5z"/>',
		copy: '<rect x="9" y="9" width="12" height="12" rx="2"/><path d="M5 15H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v1"/>',
		down: '<path d="M12 3v12M7 10l5 5 5-5M5 21h14"/>',
		bolt: '<path d="M13 2 4 14h7l-1 8 9-12h-7z"/>',
		shield: '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/>',
		eye: '<path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
		eyeoff: '<path d="M17.9 17.9A10 10 0 0 1 12 19c-6.4 0-10-7-10-7a18 18 0 0 1 4.2-5.1M9.9 5.2A9 9 0 0 1 12 5c6.4 0 10 7 10 7a18 18 0 0 1-2.2 3.2M2 2l20 20"/><path d="M14.1 14.1a3 3 0 1 1-4.2-4.2"/>',
		globe: '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/>',
		file: '<path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><path d="M14 3v6h6"/>',
		drive: '<path d="M8 3h8l6 10-4 7H6l-4-7z"/><path d="m8 3 6 10H2M16 3l-6 10 4 7"/>',
		note: '<path d="M4 4h16v12l-4 4H4z"/><path d="M16 20v-4h4M8 9h8M8 13h5"/>',
		list: '<path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>',
		lock: '<rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>',
		sound: '<path d="M11 5 6 9H2v6h4l5 4z"/><path d="M15.5 8.5a5 5 0 0 1 0 7M19 5a10 10 0 0 1 0 14"/>',
		refresh: '<path d="M21 12a9 9 0 0 1-15.5 6.2L3 16"/><path d="M3 12a9 9 0 0 1 15.5-6.2L21 8"/><path d="M21 3v5h-5M3 21v-5h5"/>',
		bank: '<path d="M3 10 12 4l9 6"/><path d="M5 10v8M9.5 10v8M14.5 10v8M19 10v8M3 21h18"/>',
		inbox: '<path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.5 5h13L22 12v6a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-6z"/>',
		user: '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
		camera: '<path d="M4 8h3l2-3h6l2 3h3a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9a1 1 0 0 1 1-1z"/><circle cx="12" cy="13.5" r="3.5"/>',
		trash: '<path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/>',
		tag: '<path d="M3 12V4a1 1 0 0 1 1-1h8l9 9-9 9z"/><circle cx="7.5" cy="7.5" r="1.5"/>',
		left: '<path d="m15 6-6 6 6 6"/>',
		palette: '<circle cx="12" cy="12" r="9"/><circle cx="8" cy="10" r="1.2"/><circle cx="12" cy="7.5" r="1.2"/><circle cx="16" cy="10" r="1.2"/><path d="M12 21a3 3 0 0 1 0-6h2a3 3 0 0 0 3-3"/>',
		wp: '<circle cx="12" cy="12" r="9"/><path d="M4 9.5 8.2 20M9 7h3M7.5 7l4.3 12.4L14.5 12M14 7h3M15.6 7l3 8.6"/>',
	};
	function ic(n, s) { return '<svg width="' + (s || 18) + '" height="' + (s || 18) + '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + (I[n] || '') + '</svg>'; }
	function avHTML(url, name, cls, initial) {
		var w = String(name || '').trim().split(/\s+/);
		var ini = initial || (w[w.length - 1] || '?').charAt(0).toUpperCase();
		return '<span class="av' + (cls ? ' ' + cls : '') + '">' + (url ? '<img src="' + esc(url) + '" alt="" loading="lazy">' : esc(ini)) + '</span>';
	}
	function meAv(cls) { return '<span data-me-av="' + (cls || '') + '" style="display:contents">' + avHTML(C.user.avatar, C.user.name, cls, C.user.initial) + '</span>'; }
	function paintMe() { $$('[data-me-av]').forEach(function (e) { e.innerHTML = avHTML(C.user.avatar, C.user.name, e.dataset.meAv, C.user.initial); }); }
	var LOGO = '<span class="brand__ic"><svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M6.5 3h7.5l4.5 4.5V20a1 1 0 0 1-1 1h-11a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1z" fill="#fff"/><path d="M14 3v4a.5.5 0 0 0 .5.5h4" fill="#cfdaff"/><circle cx="16.5" cy="17.5" r="4" fill="#10b981" stroke="#fff" stroke-width="1.5"/><path d="m14.8 17.6 1.2 1.2 2.2-2.4" stroke="#fff" stroke-width="1.5" fill="none" stroke-linecap="round"/></svg></span>';

	/* ---------- Dấu phân cách " | " -> vạch dọc được định dạng (.sep) ---------- */
	var SEP_SKIP = /^(SCRIPT|STYLE|TEXTAREA|INPUT|OPTION|SELECT|CODE|PRE|TITLE)$/;
	function decorateSeps(root) {
		if (!root || !root.querySelectorAll) return;
		var w = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, {
			acceptNode: function (n) {
				if (!/\s[|·]\s/.test(n.nodeValue)) return NodeFilter.FILTER_REJECT;
				for (var p = n.parentNode; p && p !== root.parentNode; p = p.parentNode) {
					if (SEP_SKIP.test(p.nodeName) || (p.classList && (p.classList.contains('sep') || p.classList.contains('no-sep')))) return NodeFilter.FILTER_REJECT;
				}
				return NodeFilter.FILTER_ACCEPT;
			}
		});
		var list = [];
		while (w.nextNode()) list.push(w.currentNode);
		list.forEach(function (n) {
			var parts = n.nodeValue.split(/\s+[|·]\s+/), frag = document.createDocumentFragment();
			parts.forEach(function (t, i) {
				if (i) { var s = document.createElement('span'); s.className = 'sep'; s.textContent = ' | '; frag.appendChild(s); }
				if (t) frag.appendChild(document.createTextNode(t));
			});
			n.parentNode.replaceChild(frag, n);
		});
	}
	if ('MutationObserver' in window) {
		new MutationObserver(function (ms) {
			ms.forEach(function (m) { m.addedNodes.forEach(function (n) { if (n.nodeType === 1) decorateSeps(n); else if (n.nodeType === 3 && n.parentNode) decorateSeps(n.parentNode); }); });
		}).observe(document.body, { childList: true, subtree: true });
	}

	/* ---------- Gọi máy chủ ---------- */
	var expiredShown = false;
	function api(action, data) {
		var fd = new FormData();
		fd.append('action', action);
		if (C.nonce) fd.append('nonce', C.nonce);
		Object.keys(data || {}).forEach(function (k) { if (data[k] != null) fd.append(k, data[k]); });
		return fetch(C.ajax, { method: 'POST', body: fd, credentials: 'same-origin' }).then(function (r) {
			return r.text().then(function (t) {
				var j = null;
				try { j = JSON.parse(t); } catch (e) { /* phản hồi không phải JSON */ }
				if (!j || !j.success) {
					var m = (j && j.data && j.data.message) || (r.status === 403 || t === '-1' || t === '0' ? 'Phiên đăng nhập đã hết hạn.' : 'Không kết nối được máy chủ (' + r.status + ').');
					if (C.nonce && (r.status === 403 || t === '-1') && /hết hạn|expired/i.test(m)) sessionExpired();
					var err = new Error(m);
					err.status = r.status;
					throw err;
				}
				return j.data;
			});
		});
	}
	function sessionExpired() {
		if (expiredShown) return;
		expiredShown = true;
		modal({ title: 'Phiên đăng nhập đã hết hạn', text: 'Vì lý do bảo mật, vui lòng tải lại trang để tiếp tục.', ok: 'Tải lại trang', cancel: false }).then(function () { location.reload(); });
	}

	/* ---------- Thông báo nổi & hộp thoại ---------- */
	function toast(msg, type, title, onClick) {
		var box = $('.toasts');
		if (!box) { box = document.createElement('div'); box.className = 'toasts'; document.body.appendChild(box); }
		var t = document.createElement('div');
		t.className = 'toast toast--' + (type || 'ok');
		t.setAttribute('role', 'status');
		t.innerHTML = ic(type === 'err' ? 'x' : type === 'new' ? 'bell' : 'check', 18) + '<div>' + (title ? '<b>' + esc(title) + '</b>' : '') + esc(msg) + '</div>';
		t.onclick = function () { if (onClick) onClick(); t.remove(); };
		box.appendChild(t);
		setTimeout(function () { t.style.transition = 'opacity .3s'; t.style.opacity = '0'; setTimeout(function () { t.remove(); }, 320); }, type === 'new' ? 9000 : 4200);
	}
	function modal(o) {
		return new Promise(function (resolve) {
			var m = document.createElement('div');
			m.className = 'modal';
			m.innerHTML = '<div class="modal__box" role="dialog" aria-modal="true"><h3>' + esc(o.title) + '</h3>' + (o.text ? '<p>' + esc(o.text) + '</p>' : '') + (o.html || '') +
				'<div class="modal__acts">' + (o.cancel === false ? '' : '<button class="btn" data-x>' + esc(o.cancel || 'Hủy') + '</button>') + '<button class="btn ' + (o.danger ? 'btn--danger' : 'btn--primary') + '" data-ok>' + esc(o.ok || 'Đồng ý') + '</button></div></div>';
			document.body.appendChild(m);
			function close(v) { m.remove(); document.removeEventListener('keydown', key); resolve(v); }
			function key(e) { if (e.key === 'Escape' && o.cancel !== false) close(false); }
			document.addEventListener('keydown', key);
			m.addEventListener('click', function (e) {
				if (e.target === m && o.cancel !== false) close(false);
				if (e.target.closest('[data-x]')) close(false);
				if (e.target.closest('[data-ok]')) close(o.collect ? o.collect(m) : true);
			});
			setTimeout(function () { var b = $('[data-ok]', m); if (b) b.focus(); }, 30);
		});
	}
	function copy(text, label) {
		(navigator.clipboard ? navigator.clipboard.writeText(text) : Promise.reject()).then(function () { toast('Đã sao chép ' + (label || '') + '.'); }, function () {
			var a = document.createElement('textarea'); a.value = text; document.body.appendChild(a); a.select();
			try { document.execCommand('copy'); toast('Đã sao chép ' + (label || '') + '.'); } catch (e) { /* bỏ qua */ }
			a.remove();
		});
	}

	/* ---------- Giao diện: sáng / tối ---------- */
	function applyTheme(t) {
		store('theme', t);
		var dark = t === 'dark' || (t !== 'light' && matchMedia('(prefers-color-scheme: dark)').matches);
		if (dark) document.documentElement.dataset.theme = 'dark'; else delete document.documentElement.dataset.theme;
		var b = $('[data-theme-btn]');
		if (b) b.innerHTML = ic(dark ? 'sun' : 'moon', 18);
	}

	/* =========================================================
	   ĐĂNG NHẬP
	   ========================================================= */
	function renderLogin() {
		document.title = 'Đăng nhập quản trị | ' + (C.name || '');
		if (C.logged && !C.can) {
			root.innerHTML = '<div class="login"><div class="login__art">' + artHTML() + '</div><div class="login__form"><div class="login__box"><div class="brand" style="color:var(--ink);padding:0">' + LOGO + '<div><b>' + esc(C.name) + '</b></div></div>' +
				'<h1>Không có quyền truy cập</h1><p>Tài khoản <b>' + esc(C.user && C.user.name) + '</b> không có quyền quản lý đơn hàng. Vui lòng đăng nhập bằng tài khoản quản trị.</p>' +
				'<button class="btn btn--primary btn--block" id="lo">' + ic('out', 18) + 'Đăng xuất và đăng nhập lại</button></div></div></div>';
			$('#lo').onclick = function () {
				this.disabled = true;
				var fd = new FormData(); fd.append('action', 'vs_app_logout'); fd.append('nonce', C.lnonce);
				fetch(C.ajax, { method: 'POST', body: fd, credentials: 'same-origin' }).then(function () { location.reload(); });
			};
			return;
		}
		root.innerHTML = '<div class="login"><div class="login__art">' + artHTML() + '</div>' +
			'<div class="login__form"><form class="login__box" id="lf" novalidate>' +
				'<div class="brand" style="color:var(--ink);padding:0">' + LOGO + '<div><b>' + esc(C.name) + '</b><small style="color:var(--ink-3)">Trang quản trị</small></div></div>' +
				'<h1>Chào mừng trở lại 👋</h1><p>Đăng nhập để quản lý đơn hàng, khách hàng và cộng tác viên.</p>' +
				'<div class="err hide" id="le"></div>' +
				'<label class="field field--ic"><span>Tên đăng nhập, email hoặc số điện thoại</span>' + ic('user', 17) + '<input class="input" name="login" autocomplete="username" required autofocus></label>' +
				'<label class="field field--ic"><span>Mật khẩu</span>' + ic('lock', 17) + '<div class="pw"><input class="input" type="password" name="password" autocomplete="current-password" required><button type="button" aria-label="Hiện mật khẩu" id="pwt">' + ic('eye', 18) + '</button></div></label>' +
				'<div style="display:flex;align-items:center;justify-content:space-between;margin:2px 0 18px"><label class="check"><input type="checkbox" name="remember" value="1" checked> Ghi nhớ đăng nhập</label><a href="' + esc(C.site) + 'wp-login.php?action=lostpassword" target="_blank" rel="noopener" style="font-size:13px">Quên mật khẩu?</a></div>' +
				'<button class="btn btn--primary btn--block" id="lb">' + ic('lock', 17) + 'Đăng nhập</button>' +
				'<div class="login__note">' + ic('shield', 16) + 'Chỉ dành cho quản trị viên. Kết nối được mã hóa và giới hạn số lần thử.</div>' +
			'</form></div></div>';
		$('#pwt').onclick = function () {
			var i = $('[name=password]'); var show = i.type === 'password';
			i.type = show ? 'text' : 'password'; this.innerHTML = ic(show ? 'eyeoff' : 'eye', 18);
		};
		$('#lf').onsubmit = function (e) {
			e.preventDefault();
			var f = this, b = $('#lb'), er = $('#le');
			if (!f.login.value.trim() || !f.password.value) { er.textContent = 'Vui lòng nhập tài khoản và mật khẩu.'; er.classList.remove('hide'); return; }
			b.disabled = true; b.innerHTML = '<span class="spin" style="border-top-color:#fff"></span> Đang đăng nhập...';
			api('vs_app_login', { login: f.login.value.trim(), password: f.password.value, remember: f.remember.checked ? 1 : '' }).then(function () {
				location.reload();
			}, function (err) {
				er.textContent = err.message; er.classList.remove('hide');
				b.disabled = false; b.innerHTML = ic('lock', 17) + 'Đăng nhập';
				f.password.select();
			});
		};
	}
	function artHTML() {
		return '<div class="brand" style="padding:0">' + LOGO + '<div><b>' + esc(C.name) + '</b><small>Admin Console</small></div></div>' +
			'<h2>Quản lý mọi đơn hàng <em>ở một nơi</em>, mọi lúc.</h2>' +
			'<p>Dữ liệu đồng bộ trực tiếp với website: đơn mới, thanh toán, kết quả và hoa hồng cộng tác viên cập nhật tức thì.</p>' +
			'<div class="feat"><div>' + ic('bolt') + 'Thông báo đơn mới</div><div>' + ic('trend') + 'Báo cáo doanh thu</div><div>' + ic('orders') + 'Xử lý đơn nhanh</div><div>' + ic('users') + 'Khách hàng & CTV</div></div>';
	}

	/* =========================================================
	   KHUNG ỨNG DỤNG
	   ========================================================= */
	var NAV = [
		{ id: '', t: 'Tổng quan', i: 'home', s: 'Hoạt động kinh doanh hôm nay' },
		{ id: 'orders', t: 'Đơn hàng', i: 'orders', s: 'Theo dõi và xử lý đơn', b: 'orders' },
		{ id: 'customers', t: 'Khách hàng', i: 'users', s: 'Tổng hợp theo số điện thoại đặt đơn' },
		{ id: 'affiliates', t: 'Cộng tác viên', i: 'aff', s: 'Hoa hồng và yêu cầu rút tiền', b: 'payouts' },
		{ id: 'notifications', t: 'Thông báo', i: 'bell', s: 'Hoạt động gần đây trên website', b: 'unread', hide: true },
		{ id: 'settings', t: 'Cài đặt', i: 'cog', s: 'Tài khoản, giao diện và liên kết nhanh' },
	];
	var badges = { orders: 0, payouts: 0, unread: 0 };
	var route = { id: '', q: {} };

	function renderShell() {
		root.innerHTML =
			'<div class="shell">' +
				'<aside class="side" id="side">' +
					'<div class="brand">' + LOGO + '<div><b>' + esc(C.name) + '</b><small>Admin Console</small></div></div>' +
					'<nav class="nav" aria-label="Điều hướng chính"><div class="nav__lbl">Quản lý</div>' +
						NAV.map(function (n, i) {
							if (n.hide) return '';
							return (n.id === 'settings' ? '<div class="nav__lbl">Hệ thống</div>' : '') + '<a href="#/' + n.id + '" data-nav="' + n.id + '">' + ic(n.i, 19) + '<span>' + n.t + '</span>' + (n.b ? '<span class="badge hide" data-badge="' + n.b + '"></span>' : '') + '</a>';
						}).join('') +
					'</nav>' +
					'<div class="side__foot">' +
						'<a class="side__site" href="' + esc(C.site) + '" target="_blank" rel="noopener">' + ic('globe', 17) + 'Xem website' + ic('ext', 14) + '</a>' +
						'<div class="me">' + meAv() + '<div><b>' + esc(C.user.name) + '</b><small>' + esc(C.user.role) + '</small></div><button title="Đăng xuất" data-logout>' + ic('out', 18) + '</button></div>' +
					'</div>' +
				'</aside>' +
				'<div class="main">' +
					'<header class="top" id="top">' +
						'<button class="ibtn top__menu" id="menu" aria-label="Mở menu">' + ic('menu', 20) + '</button>' +
						'<div><h1 id="ttl"></h1><div class="top__sub" id="sub"></div></div>' +
						'<div class="top__right">' +
							'<label class="search">' + ic('search', 17) + '<input id="gq" placeholder="Tìm mã đơn, tên, SĐT, email..." aria-label="Tìm đơn hàng"><kbd>/</kbd></label>' +
							'<button class="ibtn" data-theme-btn title="Đổi giao diện sáng/tối"></button>' +
							'<button type="button" class="ibtn" title="Thông báo" id="bell" aria-haspopup="dialog" aria-expanded="false" aria-controls="npop">' + ic('bell', 19) + '<span class="cnt hide"></span></button>' +
						'</div>' +
					'</header>' +
					'<main class="view" id="view"></main>' +
				'</div>' +
			'</div>' +
			'<nav class="bnav" aria-label="Điều hướng">' +
				[NAV[0], NAV[1], NAV[2], NAV[3], NAV[5]].map(function (n) {
					return '<a href="#/' + n.id + '" data-nav="' + n.id + '">' + ic(n.i, 21) + '<span>' + (n.id === 'affiliates' ? 'CTV' : n.id === 'settings' ? 'Cài đặt' : n.t) + '</span>' + (n.b ? '<span class="badge hide" data-badge="' + n.b + '"></span>' : '') + '</a>';
				}).join('') +
			'</nav>';

		applyTheme(store('theme') || 'system');
		$('[data-theme-btn]').onclick = function () { applyTheme(document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark'); };
		$('#menu').onclick = function () { toggleSide(true); };
		$$('[data-logout]').forEach(function (b) { b.onclick = logout; });
		$('#gq').addEventListener('keydown', function (e) {
			if (e.key === 'Enter') { location.hash = '#/orders?q=' + encodeURIComponent(this.value.trim()); this.blur(); }
		});
		document.addEventListener('keydown', function (e) {
			if (e.key === '/' && !/INPUT|TEXTAREA|SELECT/.test(document.activeElement.tagName)) { e.preventDefault(); var g = $('#gq'); if (g && g.offsetParent) g.focus(); }
			if (e.key === 'Escape') { closeDrawer(); toggleSide(false); }
		});
		window.addEventListener('scroll', function () { $('#top').classList.toggle('is-scrolled', window.scrollY > 4); }, { passive: true });
	}
	function toggleSide(on) {
		var s = $('#side'); if (!s) return;
		s.classList.toggle('on', on);
		var ov = $('.ov--side');
		if (on && !ov) { ov = document.createElement('div'); ov.className = 'ov ov--side'; ov.onclick = function () { toggleSide(false); }; document.body.appendChild(ov); requestAnimationFrame(function () { ov.classList.add('on'); }); }
		if (!on && ov) ov.remove();
	}
	function logout() {
		modal({ title: 'Đăng xuất?', text: 'Bạn sẽ cần đăng nhập lại để vào trang quản trị.', ok: 'Đăng xuất' }).then(function (ok) {
			if (!ok) return;
			api('vs_app_logout').then(function () { location.reload(); }, function () { location.reload(); });
		});
	}
	function setBadges(o) {
		Object.keys(o).forEach(function (k) { badges[k] = o[k] || 0; });
		$$('[data-badge]').forEach(function (b) {
			var n = badges[b.dataset.badge] || 0;
			b.textContent = n > 99 ? '99+' : n;
			b.classList.toggle('hide', !n);
		});
		var d = $('#bell .cnt'); if (d) { d.textContent = badges.unread > 99 ? '99+' : badges.unread; d.classList.toggle('hide', !badges.unread); }
		document.title = (badges.unread ? '(' + badges.unread + ') ' : '') + (navItem().t) + ' | ' + C.name;
	}
	function navItem() { return NAV.filter(function (n) { return n.id === route.id; })[0] || NAV[0]; }

	/* ---------- Bộ định tuyến (#/trang?tham_so) ---------- */
	function parseHash() {
		var h = location.hash.replace(/^#\/?/, '');
		var parts = h.split('?');
		var q = {};
		(parts[1] || '').split('&').forEach(function (p) { if (!p) return; var kv = p.split('='); q[decodeURIComponent(kv[0])] = decodeURIComponent((kv[1] || '').replace(/\+/g, ' ')); });
		return { id: parts[0].split('/')[0], q: q };
	}
	function go(id, q) {
		var s = Object.keys(q || {}).filter(function (k) { return q[k] !== '' && q[k] != null && !(k === 'page' && +q[k] === 1); }).map(function (k) { return encodeURIComponent(k) + '=' + encodeURIComponent(q[k]); }).join('&');
		location.hash = '#/' + id + (s ? '?' + s : '');
	}
	var views = {};
	var viewToken = 0;
	function render() {
		if (parseHash().id === 'notifications') { history.replaceState(null, '', '#/'); setTimeout(function () { openNotif(); }, 60); }
		route = parseHash();
		if (!views[route.id]) route.id = '';
		var n = navItem();
		$('#ttl').textContent = n.t;
		$('#sub').textContent = n.s;
		$$('[data-nav]').forEach(function (a) { a.classList.toggle('on', a.dataset.nav === route.id); });
		toggleSide(false);
		setBadges({});
		var token = ++viewToken;
		views[route.id]($('#view'), route.q, function () { return token === viewToken; });
		if (route.q.o) openOrder(+route.q.o);
		window.scrollTo(0, 0);
	}
	function refreshView() { if (views[route.id] && views[route.id].refresh) views[route.id].refresh(); }

	function skeleton(rows) {
		var h = '';
		for (var i = 0; i < (rows || 6); i++) h += '<div class="sk" style="height:18px;margin:14px 18px;max-width:calc(100% - 36px);width:' + (60 + (i * 13) % 35) + '%"></div>';
		return h;
	}
	function emptyHTML(t, s, icon) { return '<div class="empty">' + ic(icon || 'inbox', 42) + '<b>' + esc(t) + '</b>' + esc(s || '') + '</div>'; }
	function errHTML(m) { return '<div class="empty">' + ic('x', 36) + '<b>Không tải được dữ liệu</b>' + esc(m) + '<div style="margin-top:12px"><button class="btn btn--sm" onclick="location.reload()">' + ic('refresh', 15) + 'Tải lại</button></div></div>'; }
	function pill(st, label) { return '<span class="pill pill--' + esc(st) + '">' + esc(label) + '</span>'; }

	/* =========================================================
	   TỔNG QUAN
	   ========================================================= */
	views[''] = function (el, q, alive) {
		el.innerHTML = '<div class="grid g-kpi">' + [1, 2, 3, 4].map(function () { return '<div class="card kpi"><div class="sk" style="height:14px;width:50%"></div><div class="sk" style="height:26px;width:70%;margin-top:14px"></div></div>'; }).join('') + '</div><div class="card mt" style="height:320px"></div>';
		api('vs_app_dash').then(function (d) {
			if (!alive()) return;
			dashData = d;
			var s = d.stats, k = d.kpi;
			setBadges({ orders: s.manual, payouts: k.payouts });
			el.innerHTML =
				'<div class="alerts">' +
					alertHTML('warn', s.cho_thanh_toan, 'Chờ thanh toán', 'Đơn khách chưa chuyển khoản', '#/orders?status=cho_thanh_toan') +
					alertHTML('primary', s.manual, 'Cần xử lý thủ công', 'Đơn đã thanh toán, chờ kết quả', '#/orders?status=dang_xu_ly') +
					alertHTML('violet', k.payouts, 'Yêu cầu rút tiền', 'Cộng tác viên chờ chuyển khoản', '#/affiliates?tab=payouts') +
				'</div>' +
				'<div class="grid g-kpi">' +
					kpiHTML('hero', 'money', 'Doanh thu tháng này', money(k.rev_month), delta(k.rev_month, k.rev_prev) + ' so với tháng trước') +
					kpiHTML('ok', 'trend', 'Doanh thu hôm nay', money(s.rev_today), num(s.today) + ' đơn mới hôm nay') +
					kpiHTML('', 'cart', 'Đơn hàng tháng này', num(k.orders_month), delta(k.orders_month, k.orders_prev) + ' so với tháng trước') +
					kpiHTML('violet', 'users', 'Khách hàng', num(k.customers), 'Giá trị đơn TB ' + money(k.aov)) +
				'</div>' +
				'<div class="grid g-2 mt">' +
					'<div class="card"><div class="card__hd"><h3>Doanh thu 30 ngày</h3><div class="legend"><span><i style="background:#5b74ff"></i>Doanh thu</span><span><i style="background:#ff8a5c"></i>Số đơn</span></div></div><div class="card__bd"><div class="chart" id="ch"></div></div></div>' +
					'<div class="card"><div class="card__hd"><h3>Trạng thái đơn hàng</h3><span class="muted">' + num(s.all) + ' đơn</span></div><div class="card__bd">' + donutHTML(d.status) + '</div></div>' +
				'</div>' +
				'<div class="grid g-2 mt">' +
					'<div class="card"><div class="card__hd"><h3>Đơn hàng mới nhất</h3><a class="btn btn--sm" href="#/orders">Xem tất cả ' + ic('chev', 14) + '</a></div>' +
						(d.recent.length ? '<div class="tbl-wrap tbl-o"><table class="tbl"><tbody>' + d.recent.map(miniHTML).join('') + '</tbody></table></div><div class="olist" style="padding:0 12px 12px">' + d.recent.map(cardHTML).join('') + '</div>' : emptyHTML('Chưa có đơn hàng', 'Đơn mới sẽ hiện ở đây.')) +
					'</div>' +
					'<div class="card"><div class="card__hd"><h3>Hoạt động gần đây</h3><button type="button" class="btn btn--sm" data-open-notif>Tất cả</button></div><div class="card__bd" style="padding-top:0">' +
						(d.feed.length ? '<ul class="feed">' + d.feed.map(feedHTML).join('') + '</ul>' : emptyHTML('Chưa có hoạt động', '', 'bell')) +
					'</div></div>' +
				'</div>' +
				'<div class="grid g-2 mt">' +
					'<div class="card"><div class="card__hd"><h3>Phần mềm được dùng nhiều</h3><span class="muted">Đơn kiểm tra đã thanh toán</span></div><div class="card__bd">' +
						(d.tools.length ? '<div class="hbars">' + d.tools.map(function (t) { return '<div class="hbar"><span>' + esc(t.name) + '</span><div><i style="width:' + Math.max(4, t.n / d.tools[0].n * 100) + '%"></i></div><b>' + num(t.n) + '</b></div>'; }).join('') + '</div>' : emptyHTML('Chưa có dữ liệu', '', 'chart')) +
					'</div></div>' +
					'<div class="card"><div class="card__hd"><h3>Chỉ số vận hành</h3></div><div class="card__bd"><dl class="kv">' +
						'<dt>Tổng doanh thu</dt><dd><b>' + money(k.rev_all) + '</b></dd>' +
						'<dt>Giá trị đơn trung bình</dt><dd>' + money(k.aov) + '</dd>' +
						'<dt>Thời gian xử lý TB</dt><dd>' + dur(k.avg_done) + ' <span class="muted">(từ lúc thanh toán)</span></dd>' +
						'<dt>Kiểm tra / Giảm tỷ lệ</dt><dd>' + num(d.type.check) + ' / ' + num(d.type.reduce) + ' đơn</dd>' +
						'<dt>Đang xử lý</dt><dd>' + num(s.dang_xu_ly) + ' đơn <span class="muted">(' + num(s.manual) + ' cần làm tay)</span></dd>' +
					'</dl></div></div>' +
				'</div>';
			drawChart($('#ch'), d.series);
			bindRows(el);
		}, function (e) { if (alive()) el.innerHTML = '<div class="card">' + errHTML(e.message) + '</div>'; });
	};
	views[''].refresh = function () { if (!$('.drawer.on')) render(); };
	var dashData = null;

	function alertHTML(type, n, t, s, href) { return '<a class="alert alert--' + type + '" href="' + href + '"><span class="alert__n">' + num(n) + '</span><span><b>' + esc(t) + '</b><small>' + esc(s) + '</small></span>' + ic('chev', 18) + '</a>'; }
	function kpiHTML(type, icon, t, v, f) {
		return '<div class="card kpi' + (type === 'hero' ? ' kpi--hero' : '') + '"><div class="kpi__top"><span class="kpi__ic' + (type && type !== 'hero' ? ' kpi__ic--' + type : '') + '">' + ic(icon, 18) + '</span>' + esc(t) + '</div><div class="kpi__v mono">' + v + '</div><div class="kpi__f">' + f + '</div></div>';
	}
	var ST_COLORS = { cho_thanh_toan: '#f2a93b', dang_xu_ly: '#4f6bff', hoan_thanh: '#12b386', huy: '#e05656' };
	function donutHTML(st) {
		var total = 0, keys = Object.keys(C.statuses);
		keys.forEach(function (k) { total += st[k] || 0; });
		var r = 52, c = 2 * Math.PI * r, off = 0, arcs = '';
		keys.forEach(function (k) {
			var v = st[k] || 0; if (!v || !total) return;
			var len = v / total * c;
			arcs += '<circle cx="75" cy="75" r="' + r + '" fill="none" stroke="' + ST_COLORS[k] + '" stroke-width="18" stroke-dasharray="' + Math.max(0, len - 2) + ' ' + (c - len + 2) + '" stroke-dashoffset="' + (-off) + '" transform="rotate(-90 75 75)"/>';
			off += len;
		});
		return '<div class="donut"><svg viewBox="0 0 150 150"><circle cx="75" cy="75" r="' + r + '" fill="none" stroke="var(--line-2)" stroke-width="18"/>' + arcs + '<text x="75" y="76" text-anchor="middle" class="donut__c">' + num(total) + '</text><text x="75" y="94" text-anchor="middle" class="donut__l">ĐƠN HÀNG</text></svg>' +
			'<ul>' + keys.map(function (k) { return '<li><i style="background:' + ST_COLORS[k] + '"></i>' + esc(C.statuses[k]) + '<b>' + num(st[k]) + '</b></li>'; }).join('') + '</ul></div>';
	}
	function drawChart(el, data) {
		if (!el) return;
		function draw() {
			var W = el.clientWidth, H = el.clientHeight, pl = 44, pr = 8, pt = 10, pb = 26;
			var maxR = Math.max.apply(null, data.map(function (d) { return d.rev; })) || 100000;
			var maxO = Math.max.apply(null, data.map(function (d) { return d.orders; })) || 1;
			var step = Math.pow(10, Math.floor(Math.log10(maxR)));
			var top = Math.ceil(maxR / step) * step; if (top / step < 4) { step /= 2; top = Math.ceil(maxR / step) * step; }
			var iw = W - pl - pr, ih = H - pt - pb, bw = iw / data.length;
			var g = '<defs><linearGradient id="gRev" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#5b74ff"/><stop offset="1" stop-color="#8a6bff" stop-opacity=".75"/></linearGradient></defs>';
			for (var v = 0; v <= top + 1; v += step) {
				var y = pt + ih - v / top * ih;
				g += '<line class="gl" x1="' + pl + '" x2="' + (W - pr) + '" y1="' + y + '" y2="' + y + '"/><text class="ax" x="' + (pl - 8) + '" y="' + (y + 3.5) + '" text-anchor="end">' + short(v) + '</text>';
			}
			var pts = [];
			data.forEach(function (d, i) {
				var x = pl + i * bw, h = d.rev / top * ih;
				g += '<rect class="bar-r" data-i="' + i + '" x="' + (x + bw * .18) + '" y="' + (pt + ih - h) + '" width="' + Math.max(2, bw * .64) + '" height="' + Math.max(0, h) + '" rx="' + Math.min(4, bw * .2) + '"/>';
				pts.push([x + bw / 2, pt + ih - d.orders / maxO * ih * .9]);
				if (i % Math.ceil(data.length / (W < 500 ? 5 : 10)) === 0 || i === data.length - 1) g += '<text class="ax" x="' + (x + bw / 2) + '" y="' + (H - 6) + '" text-anchor="middle">' + d.d + '</text>';
			});
			g += '<path class="ln" d="' + pts.map(function (p, i) { return (i ? 'L' : 'M') + p[0].toFixed(1) + ' ' + p[1].toFixed(1); }).join('') + '"/>';
			pts.forEach(function (p, i) { if (data[i].orders) g += '<circle class="pt" cx="' + p[0] + '" cy="' + p[1] + '" r="3"/>'; });
			data.forEach(function (d, i) { g += '<rect data-hit="' + i + '" x="' + (pl + i * bw) + '" y="' + pt + '" width="' + bw + '" height="' + ih + '" fill="transparent"/>'; });
			el.innerHTML = '<svg viewBox="0 0 ' + W + ' ' + H + '" preserveAspectRatio="none">' + g + '</svg><div class="tip"></div>';
			var tip = $('.tip', el);
			$$('[data-hit]', el).forEach(function (r) {
				r.addEventListener('mouseenter', show); r.addEventListener('touchstart', show, { passive: true });
				r.addEventListener('mouseleave', function () { tip.classList.remove('on'); $$('.bar-r.on', el).forEach(function (b) { b.classList.remove('on'); }); });
			});
			function show() {
				var i = +this.dataset.hit, d = data[i];
				$$('.bar-r.on', el).forEach(function (b) { b.classList.remove('on'); });
				var b = $('.bar-r[data-i="' + i + '"]', el); if (b) b.classList.add('on');
				tip.innerHTML = '<b>' + d.d + '</b><br>Doanh thu: ' + money(d.rev) + '<br>Đơn mới: ' + d.orders;
				tip.style.left = Math.min(Math.max(70, pl + i * bw + bw / 2), W - 70) + 'px';
				tip.style.top = (pts[i] ? Math.min(pts[i][1], pt + ih - d.rev / top * ih) : pt) + 'px';
				tip.classList.add('on');
			}
		}
		draw();
		var w = el.clientWidth;
		var ro = new ResizeObserver(debounce(function () { if (!document.body.contains(el)) { ro.disconnect(); return; } if (el.clientWidth !== w) { w = el.clientWidth; draw(); } }, 120));
		ro.observe(el);
	}
	var FEED_IC = { order: ['cart', ''], paid: ['money', 'ok'], done: ['check', 'ok'], cancel: ['x', 'danger'], payout: ['bank', 'warn'], result: ['file', ''], auto: ['bolt', ''] };
	function feedHTML(n) {
		var t = FEED_IC[n.type] || ['bell', ''];
		return '<li class="' + (n.order ? 'click ' : '') + (n.read === false ? 'unread' : '') + '"' + (n.order ? ' data-order="' + n.order + '"' : '') + '><span class="feed__ic' + (t[1] ? ' feed__ic--' + t[1] : '') + '">' + ic(t[0], 17) + '</span><div style="min-width:0"><div class="feed__t">' + esc(n.title) + '</div>' + (n.text ? '<div class="feed__x">' + esc(n.text) + '</div>' : '') + '</div><span class="feed__a">' + esc(n.ago) + '</span></li>';
	}

	/* ---------- Dòng đơn hàng ---------- */
	function rowHTML(o) {
		return '<tr class="click" data-order="' + o.id + '"><td><div class="code">' + esc(o.code) + '</div><small class="muted">' + esc(o.date) + '</small></td>' +
			'<td class="who"><b>' + esc(o.name || '—') + '</b><small>' + esc(o.phone) + '</small></td>' +
			'<td><div class="sum">' + esc(o.summary) + '</div>' + (o.auto ? '<span class="tag tag--auto">' + ic('bolt', 12) + 'Tự động</span>' : '') + '</td>' +
			'<td class="r money">' + esc(o.total) + '</td><td>' + pill(o.status, o.label) + '</td><td class="muted" style="white-space:nowrap">' + esc(o.ago) + '</td></tr>';
	}
	function miniHTML(o) {
		return '<tr class="click" data-order="' + o.id + '"><td><div class="code">' + esc(o.code) + '</div><small class="muted">' + esc(o.ago) + '</small></td>' +
			'<td class="who"><b>' + esc(o.name || '—') + '</b><small>' + esc(o.type === 'reduce' ? 'Giảm tỷ lệ' : 'Kiểm tra') + '</small></td>' +
			'<td class="r money">' + esc(o.total) + '</td><td class="r">' + pill(o.status, o.label) + '</td></tr>';
	}
	function cardHTML(o) {
		return '<div class="ocard" data-order="' + o.id + '"><div><div class="code">' + esc(o.code) + '</div><div class="who"><b>' + esc(o.name || '—') + '</b></div></div><div style="text-align:right"><div class="money">' + esc(o.total) + '</div></div>' +
			'<div class="sum">' + esc(o.summary) + '</div><div class="ocard__ft">' + pill(o.status, o.label) + '<span>' + (o.auto ? '⚡ ' : '') + esc(o.ago) + '</span></div></div>';
	}
	function bindRows(el) {
		el.addEventListener('click', function (e) {
			var r = e.target.closest('[data-order]');
			if (r && !e.target.closest('a,button')) openOrder(+r.dataset.order);
		});
	}

	/* =========================================================
	   ĐƠN HÀNG
	   ========================================================= */
	views.orders = function (el, q, alive) {
		var st = q.status || 'all', type = q.type || 'all', range = q.range || '', search = q.q || '', page = +q.page || 1;
		var tabs = [['all', 'Tất cả']].concat(Object.keys(C.statuses).map(function (k) { return [k, C.statuses[k]]; }));
		el.innerHTML =
			'<div class="bar">' +
				'<div class="tabs" id="otabs">' + tabs.map(function (t) { return '<button data-st="' + t[0] + '" class="' + (t[0] === st ? 'on' : '') + '">' + (t[0] === 'all' ? ic('list', 14) : '<i class="dot" style="background:' + ST_COLORS[t[0]] + '"></i>') + esc(t[1]) + ' <span class="n" data-n="' + t[0] + '">…</span></button>'; }).join('') + '</div>' +
			'</div>' +
			'<div class="bar">' +
				'<label class="search" style="display:block;flex:1;min-width:200px;width:auto">' + ic('search', 17) + '<input id="oq" value="' + esc(search) + '" placeholder="Mã đơn, tên, SĐT, email..." aria-label="Tìm đơn"></label>' +
				'<select class="select" id="otype" aria-label="Loại dịch vụ"><option value="all">Mọi dịch vụ</option><option value="check"' + (type === 'check' ? ' selected' : '') + '>Kiểm tra</option><option value="reduce"' + (type === 'reduce' ? ' selected' : '') + '>Giảm tỷ lệ</option></select>' +
				'<select class="select" id="orange" aria-label="Thời gian"><option value="">Mọi thời gian</option>' + [['today', 'Hôm nay'], ['7d', '7 ngày qua'], ['30d', '30 ngày qua'], ['month', 'Tháng này']].map(function (r) { return '<option value="' + r[0] + '"' + (range === r[0] ? ' selected' : '') + '>' + r[1] + '</option>'; }).join('') + '</select>' +
				'<a class="btn" href="' + esc(C.export) + '" title="Xuất toàn bộ đơn ra Excel (CSV)">' + ic('down', 16) + '<span>Xuất CSV</span></a>' +
			'</div>' +
			'<div class="card card--olist" id="ores">' + skeleton(8) + '</div>';
		function nav(ch) { go('orders', Object.assign({ status: st === 'all' ? '' : st, type: type === 'all' ? '' : type, range: range, q: search, page: 1 }, ch)); }
		$('#otabs').onclick = function (e) { var b = e.target.closest('[data-st]'); if (b) nav({ status: b.dataset.st === 'all' ? '' : b.dataset.st }); };
		$('#otype').onchange = function () { nav({ type: this.value === 'all' ? '' : this.value }); };
		$('#orange').onchange = function () { nav({ range: this.value }); };
		$('#oq').addEventListener('input', debounce(function () { search = this.value.trim(); nav({ q: search }); }, 450));
		if (search) { var gq = $('#gq'); if (gq) gq.value = ''; }
		bindRows($('#ores'));
		function load() {
			api('vs_adm_orders', { status: st, type: type, q: search, page: page, range: range, per: 20 }).then(function (d) {
				if (!alive()) return;
				var s = d.stats;
				setBadges({ orders: s.manual });
				$$('[data-n]', el).forEach(function (n) { var k = n.dataset.n; n.textContent = num(k === 'all' ? s.all : s[k]); });
				var box = $('#ores');
				if (!d.items.length) { box.innerHTML = emptyHTML('Không có đơn hàng phù hợp', search || range || type !== 'all' ? 'Thử đổi bộ lọc hoặc từ khóa tìm kiếm.' : 'Đơn mới sẽ hiện ở đây.'); return; }
				box.innerHTML =
					'<div class="tbl-wrap tbl-o"><table class="tbl"><thead><tr><th>' + ic('tag', 14) + 'Mã đơn</th><th>' + ic('user', 14) + 'Khách hàng</th><th>' + ic('orders', 14) + 'Dịch vụ</th><th class="r">' + ic('money', 14) + 'Tổng tiền</th><th>' + ic('check', 14) + 'Trạng thái</th><th>' + ic('clock', 14) + 'Thời gian</th></tr></thead><tbody>' + d.items.map(rowHTML).join('') + '</tbody></table></div>' +
					'<div class="olist">' + d.items.map(cardHTML).join('') + '</div>' +
					pagerHTML(d.page, d.pages, d.total, 'đơn');
				bindPager(box, function (p) { nav({ page: p }); });
			}, function (e) { if (alive()) $('#ores').innerHTML = errHTML(e.message); });
		}
		load();
		views.orders.refresh = load;
	};
	function pagerHTML(page, pages, total, unit) {
		return '<div class="pager"><span>' + num(total) + ' ' + unit + ' | Trang ' + page + '/' + Math.max(1, pages) + '</span><div>' +
			'<button class="btn btn--sm" data-pg="' + (page - 1) + '"' + (page <= 1 ? ' disabled' : '') + '>' + ic('left', 14) + 'Trước</button>' +
			'<button class="btn btn--sm" data-pg="' + (page + 1) + '"' + (page >= pages ? ' disabled' : '') + '>Sau' + ic('chev', 14) + '</button></div></div>';
	}
	function bindPager(el, fn) { $$('[data-pg]', el).forEach(function (b) { b.onclick = function () { fn(+b.dataset.pg); }; }); }

	/* ---------- Chi tiết đơn (ngăn trượt) ---------- */
	var cur = null;
	function openOrder(id) {
		if (!id) return;
		var dr = $('.drawer');
		if (!dr) {
			var ov = document.createElement('div'); ov.className = 'ov ov--drawer'; ov.onclick = closeDrawer;
			dr = document.createElement('aside'); dr.className = 'drawer'; dr.setAttribute('role', 'dialog'); dr.setAttribute('aria-modal', 'true');
			document.body.appendChild(ov); document.body.appendChild(dr);
			requestAnimationFrame(function () { ov.classList.add('on'); dr.classList.add('on'); });
			document.body.style.overflow = 'hidden';
		}
		dr.innerHTML = '<div class="drawer__hd"><div><h2>Đang tải...</h2></div><button class="ibtn" data-close aria-label="Đóng">' + ic('x', 18) + '</button></div><div class="drawer__bd">' + skeleton(10) + '</div>';
		$('[data-close]', dr).onclick = closeDrawer;
		api('vs_adm_order', { id: id }).then(function (o) { renderOrder(o); }, function (e) { dr.querySelector('.drawer__bd').innerHTML = errHTML(e.message); });
	}
	function closeDrawer() {
		var dr = $('.drawer'), ov = $('.ov--drawer');
		if (!dr) return;
		dr.classList.remove('on'); if (ov) ov.classList.remove('on');
		setTimeout(function () { dr.remove(); if (ov) ov.remove(); }, 260);
		document.body.style.overflow = '';
		cur = null;
		if (route.q.o) { var q = Object.assign({}, route.q); delete q.o; history.replaceState(null, '', '#/' + route.id + (Object.keys(q).length ? '?' + Object.keys(q).map(function (k) { return k + '=' + encodeURIComponent(q[k]); }).join('&') : '')); route.q = q; }
	}
	var SRC = { api: 'API', pdf: 'PDF', manual: 'Nhập tay' };
	function renderOrder(o) {
		cur = o;
		var dr = $('.drawer'); if (!dr) return;
		var c = o.customer, ph = phoneDigits(c.phone);
		var acts = '';
		if (o.status === 'cho_thanh_toan') acts = '<button class="btn btn--ok" data-act="paid">' + ic('check', 16) + 'Xác nhận đã thanh toán</button><button class="btn btn--danger" data-act="cancel">' + ic('x', 16) + 'Hủy đơn</button>';
		else if (o.status === 'dang_xu_ly') acts = '<button class="btn btn--primary" data-act="done" data-mail="1">' + ic('mail', 16) + 'Hoàn thành & gửi email</button><button class="btn" data-act="done">' + ic('check', 16) + 'Hoàn thành (không gửi email)</button><button class="btn btn--danger" data-act="cancel">' + ic('x', 16) + 'Hủy đơn</button>';
		else acts = '<button class="btn" data-act="reopen">' + ic('refresh', 16) + 'Mở lại đơn (Đang xử lý)</button>';

		var kinds = { plag: 'Đạo văn', ai: 'Tỷ lệ AI' };
		var res = o.rows && o.rows.length ?
			'<div class="sec"><h4>' + ic('chart', 15) + 'Kết quả' + (o.auto ? ' <span class="tag tag--auto">' + ic('bolt', 12) + 'Đang chạy tự động</span>' : '') + '</h4>' +
				'<div class="tbl-wrap"><table class="res"><thead><tr><th>Phần mềm</th><th>' + kinds.plag + '</th><th>' + kinds.ai + '</th></tr></thead><tbody>' +
				o.rows.map(function (r) {
					return '<tr><td><b>' + esc(r.name) + '</b></td>' + ['plag', 'ai'].map(function (k) {
						var cell = r.cells[k];
						if (!cell) return '<td class="na">—</td>';
						return '<td><div class="pct' + (cell.src === 'api' ? ' pct--api' : '') + '"><input inputmode="decimal" data-row="' + esc(r.key) + '" data-kind="' + k + '" value="' + (cell.v == null ? '' : esc(String(cell.v).replace('.', ','))) + '" placeholder="' + (o.auto ? '...' : '') + '" aria-label="' + esc(r.name + ' ' + kinds[k]) + '"><span>%</span><small>' + (cell.src ? '● ' + (SRC[cell.src] || cell.src) : '○ Chưa có') + '</small></div></td>';
					}).join('') + '</tr>';
				}).join('') + '</tbody></table></div>' +
				(o.status === 'dang_xu_ly' ? '<label class="check" style="margin-top:12px"><input type="checkbox" id="rcomp" checked> Đủ kết quả thì chuyển <b>&nbsp;Hoàn thành&nbsp;</b> và gửi email cho khách</label>' : '') +
				'<div class="acts" style="margin-top:12px"><button class="btn btn--primary btn--sm" id="rsave">' + ic('check', 15) + 'Lưu kết quả</button></div>' +
				(o.reports.length ? '<div class="files" style="margin-top:14px">' + o.reports.map(reportHTML).join('') + '</div>' : '') +
			'</div>' : (o.reports.length ? '<div class="sec"><h4>' + ic('file', 15) + 'File trả khách</h4><div class="files">' + o.reports.map(reportHTML).join('') + '</div></div>' : '');

		dr.innerHTML =
			'<div class="drawer__hd"><div><h2>' + esc(o.code) + ' ' + pill(o.status, o.label) + '</h2><small>Đặt lúc ' + esc(o.date) + '</small></div><button class="ibtn" data-close aria-label="Đóng">' + ic('x', 18) + '</button></div>' +
			'<div class="drawer__bd">' +
				'<div class="sec"><h4>' + ic('bolt', 15) + 'Thao tác</h4><div class="acts">' + acts + '</div></div>' +
				'<div class="sec"><h4>' + ic('users', 15) + 'Khách hàng</h4><dl class="kv">' +
					'<dt>' + ic('user', 14) + 'Họ tên</dt><dd><b>' + esc(c.name || '—') + '</b></dd>' +
					'<dt>' + ic('phone', 14) + 'Điện thoại</dt><dd>' + esc(c.phone || '—') + (c.phone ? ' <button class="copy" data-copy="' + esc(c.phone) + '" data-l="số điện thoại">Sao chép</button>' : '') + '</dd>' +
					'<dt>' + ic('mail', 14) + 'Email</dt><dd>' + esc(c.email || '—') + '</dd>' +
					(c.note ? '<dt>' + ic('chat', 14) + 'Lời nhắn</dt><dd>' + esc(c.note) + '</dd>' : '') +
				'</dl><div class="contact">' +
					(ph ? '<a class="btn btn--sm" href="tel:' + ph + '">' + ic('phone', 15) + 'Gọi</a><a class="btn btn--sm" href="https://zalo.me/' + ph + '" target="_blank" rel="noopener">' + ic('chat', 15) + 'Zalo</a>' : '') +
					(c.email ? '<a class="btn btn--sm" href="mailto:' + esc(c.email) + '?subject=' + encodeURIComponent('Đơn hàng ' + o.code) + '">' + ic('mail', 15) + 'Email</a>' : '') +
					(ph ? '<a class="btn btn--sm" href="#/orders?q=' + ph + '" data-close-go>' + ic('orders', 15) + 'Đơn khác của khách</a>' : '') +
					(c.account ? '<a class="btn btn--sm" href="' + esc(c.account) + '" target="_blank" rel="noopener">' + ic('ext', 14) + 'Tài khoản</a>' : '') +
				'</div></div>' +
				'<div class="sec"><h4>' + ic('orders', 15) + 'Đơn hàng</h4><dl class="kv">' +
					'<dt>' + ic('orders', 14) + 'Dịch vụ</dt><dd>' + esc(o.summary) + '</dd>' +
					Object.keys(o.details || {}).map(function (k) { return '<dt>' + ic(detIcon(k), 14) + esc(k) + '</dt><dd>' + esc(o.details[k]) + '</dd>'; }).join('') +
					'<dt>' + ic('money', 14) + 'Tổng tiền</dt><dd><b class="money">' + esc(o.total) + '</b></dd>' +
					'<dt>' + ic('card', 14) + 'Thanh toán</dt><dd>' + (o.paid_at ? esc(o.paid_at) + (o.paid_via ? ' | ' + esc(o.paid_via) : '') : '<span class="muted">Chưa thanh toán</span>') + '</dd>' +
					(o.done_at ? '<dt>' + ic('check', 14) + 'Hoàn thành</dt><dd>' + esc(o.done_at) + '</dd>' : '') +
					(o.ref ? '<dt>' + ic('aff', 14) + 'Cộng tác viên</dt><dd>' + esc(o.ref.name) + ' | hoa hồng ' + esc(o.ref.amount) + '</dd>' : '') +
				'</dl></div>' +
				(o.files.length ? '<div class="sec"><h4>' + ic('file', 15) + 'File khách gửi</h4><div class="files">' + o.files.map(function (f) {
					var ext = (f.name.split('.').pop() || '').toLowerCase();
					return '<a class="file" href="' + esc(fixUrl(f.url)) + '" title="' + esc(f.name) + '"><span class="file__ext file__ext--' + esc(ext) + '">' + esc(ext.toUpperCase().slice(0, 4)) + '</span><span class="file__b"><b>' + esc(f.short || f.name) + '</b><small>' + esc(f.label + (f.meta ? ' | ' + f.meta : '')) + '</small></span>' + ic('down', 18) + '</a>';
				}).join('') + '</div></div>' : '') +
				res +
				'<div class="sec"><h4>' + ic('drive', 15) + 'Trả kết quả qua Google Drive</h4>' +
					'<textarea class="textarea" id="drv" rows="3" placeholder="Dán link Google Drive (file hoặc thư mục), mỗi dòng một link">' + esc(o.drive) + '</textarea>' +
					'<div class="acts" style="margin-top:10px;align-items:center"><button class="btn btn--sm" id="drvs">' + ic('check', 15) + 'Lưu link</button><span class="muted" style="font-size:12.5px">Khách xem và tải khi đơn Đang xử lý / Hoàn thành.</span></div></div>' +
				'<div class="sec"><h4>' + ic('note', 15) + 'Ghi chú nội bộ</h4><textarea class="textarea" id="nt" rows="3" placeholder="Chỉ quản trị viên thấy">' + esc(o.note) + '</textarea><div class="acts" style="margin-top:10px"><button class="btn btn--sm" id="nts">' + ic('check', 15) + 'Lưu ghi chú</button></div></div>' +
				(o.log.length ? '<div class="sec"><h4>' + ic('list', 15) + 'Nhật ký xử lý</h4><ul class="log">' + o.log.map(function (l) { return '<li><small>' + esc(l.ago) + '</small>' + esc(l.msg) + '</li>'; }).join('') + '</ul></div>' : '') +
			'</div>' +
			'<div class="drawer__ft"><button class="btn btn--sm" data-copy="' + esc(o.code) + '" data-l="mã đơn">' + ic('copy', 15) + 'Sao chép mã</button><a class="btn btn--sm" href="' + esc(o.edit) + '" target="_blank" rel="noopener">' + ic('wp', 15) + 'Mở trong WordPress</a></div>';

		$('[data-close]', dr).onclick = closeDrawer;
		$$('[data-copy]', dr).forEach(function (b) { b.onclick = function () { copy(b.dataset.copy, b.dataset.l); }; });
		$$('[data-close-go]', dr).forEach(function (a) { a.addEventListener('click', closeDrawer); });
		$$('[data-act]', dr).forEach(function (b) { b.onclick = function () { orderAction(b.dataset.act, b.dataset.mail); }; });
		var rs = $('#rsave', dr);
		if (rs) rs.onclick = function () {
			var vals = {}, bad = false;
			$$('.pct input', dr).forEach(function (i) {
				var v = i.value.trim().replace(',', '.');
				if (v !== '' && (isNaN(+v) || +v < 0 || +v > 100)) { bad = true; i.style.borderColor = 'var(--danger)'; }
				(vals[i.dataset.row] = vals[i.dataset.row] || {})[i.dataset.kind] = v === '' ? null : v;
			});
			if (bad) { toast('Tỷ lệ phải là số từ 0 đến 100.', 'err'); return; }
			busy(rs, api('vs_app_results', { id: o.id, values: JSON.stringify(vals), complete: $('#rcomp', dr) && $('#rcomp', dr).checked ? 1 : '' })).then(done);
		};
		$('#drvs', dr).onclick = function () { busy(this, api('vs_adm_action', { id: o.id, act: 'drive', drive: $('#drv', dr).value })).then(done); };
		$('#nts', dr).onclick = function () { busy(this, api('vs_adm_action', { id: o.id, act: 'note', note: $('#nt', dr).value })).then(done); };
	}
	// Biểu tượng cho từng dòng chi tiết đơn (theo nhãn máy chủ trả về).
	function detIcon(k) {
		var m = [[/phần mềm|công cụ/i, 'shield'], [/hình thức|mục tiêu|loại/i, 'list'], [/trang|từ/i, 'file'], [/thời gian|hạn|ngày/i, 'clock'], [/giảm giá|mã/i, 'tag'], [/tỷ lệ|hiện tại/i, 'chart'], [/thanh toán/i, 'card']];
		for (var i = 0; i < m.length; i++) if (m[i][0].test(k)) return m[i][1];
		return 'tag';
	}
	function reportHTML(f) {
		var ext = f.drive ? (f.drive === 'folder' ? 'dir' : 'pdf') : ((f.name || '').split('.').pop() || 'pdf').toLowerCase();
		return '<a class="file" href="' + esc(fixUrl(f.view || f.url)) + '" target="_blank" rel="noopener" title="' + esc(f.name) + '"><span class="file__ext file__ext--' + esc(ext) + '">' + esc(f.drive === 'folder' ? 'DIR' : ext.toUpperCase().slice(0, 4)) + '</span><span class="file__b"><b>' + esc(f.short || f.name) + '</b><small>' + esc(f.meta || (f.drive ? 'Google Drive' : 'Báo cáo trả khách')) + '</small></span>' + ic(f.drive ? 'ext' : 'down', 17) + '</a>';
	}
	function busy(btn, p) {
		var h = btn.innerHTML; btn.disabled = true; btn.innerHTML = '<span class="spin"></span> Đang lưu...';
		return p.then(function (d) { btn.disabled = false; btn.innerHTML = h; return d; }, function (e) { btn.disabled = false; btn.innerHTML = h; toast(e.message, 'err'); throw e; });
	}
	function done(d) { toast(d.message); if (d.order) renderOrder(d.order); refreshView(); }
	var ACT_CONFIRM = {
		paid: ['Xác nhận đã thanh toán?', 'Đơn sẽ chuyển sang Đang xử lý và hệ thống bắt đầu xử lý (kể cả phần tự động).', 'Xác nhận'],
		cancel: ['Hủy đơn hàng này?', 'Đơn chuyển sang Đã hủy; hoa hồng cộng tác viên (nếu có) cũng bị hủy.', 'Hủy đơn', true],
		done: ['Đánh dấu hoàn thành?', '', 'Hoàn thành'],
		reopen: ['Mở lại đơn?', 'Đơn chuyển về Đang xử lý.', 'Mở lại'],
	};
	function orderAction(act, mail) {
		var c = ACT_CONFIRM[act];
		modal({ title: c[0], text: act === 'done' ? (mail ? 'Khách sẽ nhận email kèm kết quả.' : 'Không gửi email cho khách.') : c[1], ok: c[2], danger: c[3] }).then(function (ok) {
			if (!ok || !cur) return;
			var b = $('[data-act="' + act + '"]' + (act === 'done' ? (mail ? '[data-mail]' : ':not([data-mail])') : ''));
			busy(b || document.createElement('button'), api('vs_adm_action', { id: cur.id, act: act, mail: mail ? 1 : '' })).then(done);
		});
	}

	/* =========================================================
	   KHÁCH HÀNG
	   ========================================================= */
	views.customers = function (el, q, alive) {
		var search = q.q || '', sort = q.sort || 'last', page = +q.page || 1;
		el.innerHTML =
			'<div class="grid g-3" id="csum" style="margin-bottom:16px">' + [1, 2, 3].map(function () { return '<div class="card kpi"><div class="sk" style="height:14px;width:40%"></div><div class="sk" style="height:24px;width:60%;margin-top:12px"></div></div>'; }).join('') + '</div>' +
			'<div class="bar"><label class="search" style="display:block;flex:1;min-width:200px;width:auto">' + ic('search', 17) + '<input id="cq" value="' + esc(search) + '" placeholder="Tìm tên, SĐT, email..." aria-label="Tìm khách hàng"></label>' +
			'<select class="select" id="cs" aria-label="Sắp xếp">' + [['last', 'Đặt gần đây'], ['spent', 'Chi tiêu nhiều nhất'], ['orders', 'Nhiều đơn nhất']].map(function (s) { return '<option value="' + s[0] + '"' + (sort === s[0] ? ' selected' : '') + '>' + s[1] + '</option>'; }).join('') + '</select></div>' +
			'<div class="card card--olist" id="cres">' + skeleton(8) + '</div>';
		function nav(ch) { go('customers', Object.assign({ q: search, sort: sort === 'last' ? '' : sort, page: 1 }, ch)); }
		$('#cq').addEventListener('input', debounce(function () { search = this.value.trim(); nav({ q: search }); }, 450));
		$('#cs').onchange = function () { nav({ sort: this.value === 'last' ? '' : this.value }); };
		api('vs_app_customers', { q: search, sort: sort, page: page }).then(function (d) {
			if (!alive()) return;
			$('#csum').innerHTML = kpiHTML('', 'users', 'Khách hàng', num(d.total), search ? 'Theo từ khóa tìm kiếm' : 'Gộp theo số điện thoại') + kpiHTML('violet', 'refresh', 'Khách quay lại', num(d.repeat), 'Có từ 2 đơn đã thanh toán') + kpiHTML('ok', 'money', 'Tổng chi tiêu', esc(d.sum), 'Đơn đã thanh toán');
			var box = $('#cres');
			if (!d.items.length) { box.innerHTML = emptyHTML('Không tìm thấy khách hàng', search ? 'Thử từ khóa khác.' : '', 'users'); return; }
			box.innerHTML = '<div class="tbl-wrap tbl-o"><table class="tbl"><thead><tr><th>Khách hàng</th><th>Điện thoại</th><th class="r">Đơn</th><th class="r">Đã chi</th><th>Khách từ</th><th>Gần nhất</th><th></th></tr></thead><tbody>' +
				d.items.map(function (c) {
					var ph = phoneDigits(c.phone);
					return '<tr><td><div class="who who--av">' + avHTML(c.avatar, c.name) + '<span class="who"><b>' + esc(c.name || '—') + (c.account ? ' <span class="tag" title="Có tài khoản trên website">' + ic('user', 11) + 'TK</span>' : '') + '</b><small>' + esc(c.email || '') + '</small></span></div></td><td class="mono">' + esc(c.phone || '—') + '</td><td class="r mono">' + c.paid + '<span class="muted">/' + c.orders + '</span></td><td class="r money">' + esc(c.spent_f) + '</td><td class="muted">' + esc(c.first_f) + '</td><td class="muted">' + esc(c.last_f) + '</td>' +
						'<td class="r" style="white-space:nowrap"><a class="btn btn--sm" href="#/orders?q=' + encodeURIComponent(ph || c.email) + '">' + ic('orders', 14) + 'Xem đơn</a> ' + (ph ? '<a class="btn btn--sm btn--ghost" href="https://zalo.me/' + ph + '" target="_blank" rel="noopener" title="Zalo">' + ic('chat', 15) + '</a>' : '') + '</td></tr>';
				}).join('') + '</tbody></table></div>' +
				'<div class="olist">' + d.items.map(function (c) {
					var ph = phoneDigits(c.phone);
					return '<a class="ocard" href="#/orders?q=' + encodeURIComponent(ph || c.email) + '" style="color:inherit"><div class="who who--av">' + avHTML(c.avatar, c.name, 'av--sm') + '<span class="who"><b>' + esc(c.name || '—') + '</b><small>' + esc(c.phone || c.email) + '</small></span></div><div class="money" style="text-align:right">' + esc(c.spent_f) + '</div><div class="ocard__ft"><span>' + c.paid + '/' + c.orders + ' đơn đã thanh toán</span><span>' + esc(c.last_f) + '</span></div></a>';
				}).join('') + '</div>' +
				pagerHTML(d.page, d.pages, d.total, 'khách');
			bindPager(box, function (p) { nav({ page: p }); });
		}, function (e) { if (alive()) $('#cres').innerHTML = errHTML(e.message); });
	};

	/* =========================================================
	   CỘNG TÁC VIÊN
	   ========================================================= */
	views.affiliates = function (el, q, alive) {
		var tab = q.tab === 'payouts' ? 'payouts' : 'members';
		el.innerHTML = '<div class="grid g-kpi" id="asum">' + [1, 2, 3, 4].map(function () { return '<div class="card kpi"><div class="sk" style="height:14px;width:40%"></div><div class="sk" style="height:24px;width:60%;margin-top:12px"></div></div>'; }).join('') + '</div>' +
			'<div class="bar mt"><div class="tabs" id="atabs"><button data-t="members" class="' + (tab === 'members' ? 'on' : '') + '">Cộng tác viên</button><button data-t="payouts" class="' + (tab === 'payouts' ? 'on' : '') + '">Yêu cầu rút tiền <span class="n" id="apn">…</span></button></div><span class="bar__grow"></span><span class="muted" style="font-size:13px">Hoa hồng ' + esc(String(C.aff.pct).replace('.', ',')) + '% | rút tối thiểu ' + esc(C.aff.min) + '</span></div>' +
			'<div class="card card--olist" id="ares">' + skeleton(6) + '</div>';
		$('#atabs').onclick = function (e) { var b = e.target.closest('[data-t]'); if (b) go('affiliates', { tab: b.dataset.t === 'payouts' ? 'payouts' : '' }); };
		function load() {
			api('vs_app_affiliates').then(function (d) {
				if (!alive()) return;
				var pend = d.payouts.filter(function (p) { return p.status === 'pending'; }).length;
				setBadges({ payouts: pend });
				$('#apn').textContent = pend;
				$('#asum').innerHTML =
					kpiHTML('', 'users', 'Thành viên CTV', num(d.members), num(d.items.length) + ' người có hoạt động') +
					kpiHTML('violet', 'cart', 'Đơn giới thiệu', num(d.total.orders), num(d.total.clicks) + ' lượt nhấp link') +
					kpiHTML('ok', 'money', 'Hoa hồng đã duyệt', esc(d.total.approved), 'Chờ duyệt ' + esc(d.total.pending)) +
					kpiHTML('warn', 'bank', 'Đã chi trả', esc(d.total.paid), pend ? pend + ' yêu cầu đang chờ' : 'Không có yêu cầu chờ');
				var box = $('#ares');
				if (tab === 'members') {
					if (!d.items.length) { box.innerHTML = emptyHTML('Chưa có cộng tác viên hoạt động', 'Khi CTV chia sẻ link và có lượt nhấp, họ sẽ hiện ở đây.', 'aff'); return; }
					box.innerHTML = '<div class="tbl-wrap tbl-o"><table class="tbl"><thead><tr><th>Cộng tác viên</th><th>Mã</th><th class="r">Click</th><th class="r">Đơn</th><th class="r">Khách</th><th class="r">Chờ duyệt</th><th class="r">Đã duyệt</th><th class="r">Đã trả</th><th class="r">Khả dụng</th></tr></thead><tbody>' +
						d.items.map(function (a) { return '<tr><td><div class="who who--av">' + avHTML(a.avatar, a.name) + '<span class="who"><b>' + esc(a.name) + '</b><small>' + esc(a.phone || a.email) + '</small></span></div></td><td><span class="tag">' + esc(a.code) + '</span></td><td class="r mono">' + num(a.clicks) + '</td><td class="r mono">' + num(a.orders) + '</td><td class="r mono">' + num(a.customers) + '</td><td class="r mono">' + esc(a.pending) + '</td><td class="r mono">' + esc(a.approved) + '</td><td class="r mono">' + esc(a.paid) + '</td><td class="r money">' + esc(a.available) + '</td></tr>'; }).join('') +
						'</tbody></table></div><div class="olist">' + d.items.map(function (a) { return '<div class="ocard" style="cursor:default"><div class="who"><b>' + esc(a.name) + '</b><small>' + esc(a.code) + ' | ' + num(a.clicks) + ' click | ' + num(a.orders) + ' đơn</small></div><div style="text-align:right"><div class="money">' + esc(a.available) + '</div><small class="muted">khả dụng</small></div><div class="ocard__ft"><span>Đã duyệt ' + esc(a.approved) + '</span><span>Đã trả ' + esc(a.paid) + '</span></div></div>'; }).join('') + '</div>';
				} else {
					if (!d.payouts.length) { box.innerHTML = emptyHTML('Chưa có yêu cầu rút tiền', '', 'bank'); return; }
					box.innerHTML = '<div class="tbl-wrap tbl-o"><table class="tbl"><thead><tr><th>Cộng tác viên</th><th class="r">Số tiền</th><th>Tài khoản nhận</th><th>Ngày yêu cầu</th><th>Trạng thái</th><th></th></tr></thead><tbody>' +
						d.payouts.map(function (p) { return '<tr><td><div class="who who--av">' + avHTML(p.avatar, p.name) + '<span class="who"><b>' + esc(p.name) + '</b><small>' + esc(p.phone) + '</small></span></div></td><td class="r money">' + esc(p.amount) + '</td><td>' + bankHTML(p.bank) + '</td><td class="muted">' + esc(p.date) + '</td><td>' + pill(p.status, p.label) + '</td><td class="r" style="white-space:nowrap">' + payActs(p) + '</td></tr>'; }).join('') +
						'</tbody></table></div><div class="olist">' + d.payouts.map(function (p) { return '<div class="ocard" style="cursor:default"><div class="who"><b>' + esc(p.name) + '</b><small>' + esc(p.date) + '</small></div><div class="money" style="text-align:right">' + esc(p.amount) + '</div><div class="sum" style="white-space:normal">' + bankHTML(p.bank) + '</div><div class="ocard__ft">' + pill(p.status, p.label) + '<span>' + payActs(p) + '</span></div></div>'; }).join('') + '</div>';
					$$('[data-pay]', box).forEach(function (b) {
						b.onclick = function () {
							var paid = b.dataset.st === 'paid';
							modal({ title: paid ? 'Xác nhận đã chuyển khoản?' : b.dataset.st === 'rejected' ? 'Từ chối yêu cầu?' : 'Đặt lại trạng thái chờ?', text: paid ? 'Cộng tác viên sẽ nhận thông báo đã chuyển hoa hồng.' : '', ok: paid ? 'Đã chuyển' : 'Đồng ý', danger: b.dataset.st === 'rejected' }).then(function (ok) {
								if (ok) busy(b, api('vs_app_payout', { id: b.dataset.pay, status: b.dataset.st })).then(function (r) { toast(r.message); load(); });
							});
						};
					});
					$$('[data-copy]', box).forEach(function (b) { b.onclick = function () { copy(b.dataset.copy, 'số tài khoản'); }; });
				}
			}, function (e) { if (alive()) $('#ares').innerHTML = errHTML(e.message); });
		}
		load();
		views.affiliates.refresh = load;
	};
	function bankHTML(b) { return '<div style="font-size:13px;line-height:1.45"><b>' + esc(b.account) + '</b> <button class="copy" data-copy="' + esc(b.account) + '">Sao chép</button><br><span class="muted">' + esc(b.name) + ' | ' + esc(b.holder) + '</span></div>'; }
	function payActs(p) {
		if (p.status === 'pending') return '<button class="btn btn--sm btn--ok" data-pay="' + p.id + '" data-st="paid">' + ic('check', 14) + 'Đã chuyển</button> <button class="btn btn--sm btn--danger" data-pay="' + p.id + '" data-st="rejected">' + ic('x', 14) + 'Từ chối</button>';
		return '<button class="btn btn--sm btn--ghost" data-pay="' + p.id + '" data-st="pending">' + ic('refresh', 14) + 'Đặt lại chờ</button>';
	}

	/* =========================================================
	   THÔNG BÁO
	   ========================================================= */
	views.notifications = function (el, q, alive) {
		el.innerHTML = '<div class="card"><div class="card__hd"><h3>Hoạt động trên website</h3><button class="btn btn--sm" id="nread">' + ic('check', 15) + 'Đánh dấu đã đọc</button></div><div class="card__bd" id="nres">' + skeleton(8) + '</div></div>';
		bindRows($('#nres'));
		function load(seen) {
			api('vs_adm_feed', seen ? { seen: 1 } : {}).then(function (d) {
				if (!alive()) return;
				setBadges({ unread: seen ? 0 : d.unread });
				$('#nres').innerHTML = d.items.length ? '<ul class="feed">' + d.items.map(feedHTML).join('') + '</ul>' : emptyHTML('Chưa có thông báo', 'Đơn mới, thanh toán và kết quả sẽ hiện ở đây.', 'bell');
				if (seen) $$('#nres .unread').forEach(function (li) { li.classList.remove('unread'); });
			}, function (e) { if (alive()) $('#nres').innerHTML = errHTML(e.message); });
		}
		$('#nread').onclick = function () { load(true); feedState.unread = 0; };
		load(false);
		views.notifications.refresh = function () { load(false); };
	};

	/* =========================================================
	   POPUP THÔNG BÁO (bấm chuông)
	   ========================================================= */
	function notifOpen() { return !!$('#npop'); }
	function openNotif() {
		if (notifOpen()) { closeNotif(); return; }
		var b = $('#bell');
		var ov = document.createElement('div'); ov.className = 'npop-ov'; ov.onclick = closeNotif;
		var p = document.createElement('div');
		p.id = 'npop'; p.className = 'npop'; p.setAttribute('role', 'dialog'); p.setAttribute('aria-label', 'Thông báo');
		p.innerHTML = '<div class="npop__hd"><b>' + ic('bell', 17) + 'Thông báo</b><span class="npop__n" id="npn"></span><button type="button" class="ibtn" data-x aria-label="Đóng">' + ic('x', 16) + '</button></div><div class="npop__bd" id="npb">' + skeleton(5) + '</div>';
		document.body.appendChild(ov); document.body.appendChild(p);
		if (b) {
			b.setAttribute('aria-expanded', 'true');
			var r = b.getBoundingClientRect();
			if (window.innerWidth > 900) { p.style.top = (r.bottom + 10) + 'px'; p.style.right = Math.max(12, window.innerWidth - r.right) + 'px'; }
		}
		requestAnimationFrame(function () { ov.classList.add('on'); p.classList.add('on'); });
		$('[data-x]', p).onclick = closeNotif;
		$('#npb', p).addEventListener('click', function (e) {
			var li = e.target.closest('[data-order]');
			if (li) { closeNotif(); openOrder(+li.dataset.order); }
		});
		loadNotif();
	}
	function loadNotif() {
		api('vs_adm_feed', { seen: 1 }).then(function (d) {
			var bd = $('#npb'); if (!bd) return;
			var unread = d.items.filter(function (i) { return !i.read; }).length;
			$('#npn').textContent = unread ? unread + ' mới' : '';
			bd.innerHTML = d.items.length ? '<ul class="feed">' + d.items.map(feedHTML).join('') + '</ul>' : emptyHTML('Chưa có thông báo', 'Đơn mới, thanh toán và kết quả sẽ hiện ở đây.', 'bell');
			setBadges({ unread: 0 });
			feedState.unread = 0;
		}, function (e) { var bd = $('#npb'); if (bd) bd.innerHTML = errHTML(e.message); });
	}
	function closeNotif() {
		var p = $('#npop'), ov = $('.npop-ov'), b = $('#bell');
		if (b) b.setAttribute('aria-expanded', 'false');
		if (p) { p.removeAttribute('id'); p.classList.remove('on'); setTimeout(function () { p.remove(); }, 180); }
		if (ov) { ov.className = 'npop-ov is-closing'; setTimeout(function () { ov.remove(); }, 180); }
	}
	document.addEventListener('click', function (e) {
		if (e.target.closest('#bell')) { e.preventDefault(); openNotif(); return; }
		if (e.target.closest('[data-open-notif]')) { e.preventDefault(); openNotif(); }
	});
	document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeNotif(); });
	window.addEventListener('resize', debounce(function () { if (notifOpen() && window.innerWidth > 900) { closeNotif(); } }, 200));

	/* =========================================================
	   CÀI ĐẶT
	   ========================================================= */
	views.settings = function (el) {
		var theme = store('theme') || 'system', sound = store('sound') !== '0';
		var perm = 'Notification' in window ? Notification.permission : 'unsupported';
		el.innerHTML =
			'<div class="grid g-2">' +
				'<div class="card"><div class="card__hd"><h3>Tài khoản</h3></div><div class="card__bd">' +
					'<div class="av-edit">' + meAv('av--xl') +
						'<div class="av-edit__b"><b>' + esc(C.user.name) + '</b><small>' + esc(C.user.email) + ' | ' + esc(C.user.role) + '</small><small>Ảnh JPG, PNG, WEBP tối đa 5 MB | hiển thị đồng bộ trên website, app và WordPress</small>' +
							'<div class="av-edit__acts"><label class="btn btn--sm btn--primary">' + ic('camera', 15) + 'Đổi ảnh<input type="file" id="avf" accept="image/jpeg,image/png,image/webp,image/gif" hidden></label>' +
							'<button class="btn btn--sm btn--danger" id="avr"' + (C.user.avatar ? '' : ' hidden') + '>' + ic('trash', 15) + 'Xóa ảnh</button></div></div></div>' +
					'<div class="set"><span class="set__ic">' + ic('lock', 18) + '</span><div class="set__t"><b>Đổi mật khẩu, thông tin cá nhân</b><small>Quản lý trong trang hồ sơ WordPress</small></div><a class="btn btn--sm" href="' + esc(C.wp.dash) + 'profile.php" target="_blank" rel="noopener">' + ic('ext', 14) + 'Mở</a></div>' +
					'<div class="set"><span class="set__ic set__ic--danger">' + ic('out', 18) + '</span><div class="set__t"><b>Đăng xuất</b><small>Kết thúc phiên trên thiết bị này</small></div><button class="btn btn--sm btn--danger" data-logout>' + ic('out', 15) + 'Đăng xuất</button></div>' +
				'</div></div>' +
				'<div class="card"><div class="card__hd"><h3>Giao diện & thông báo</h3></div><div class="card__bd">' +
					'<div class="set"><span class="set__ic">' + ic('palette', 18) + '</span><div class="set__t"><b>Chế độ hiển thị</b><small>Sáng, tối hoặc theo thiết bị</small></div><div class="seg" id="thm">' + [['light', 'Sáng'], ['dark', 'Tối'], ['system', 'Tự động']].map(function (t) { return '<button data-v="' + t[0] + '" class="' + (theme === t[0] ? 'on' : '') + '">' + t[1] + '</button>'; }).join('') + '</div></div>' +
					'<div class="set"><span class="set__ic set__ic--ok">' + ic('sound', 18) + '</span><div class="set__t"><b>Âm báo đơn mới</b><small>Phát tiếng “ting” khi có đơn hoặc thanh toán mới</small></div><label class="check"><input type="checkbox" id="snd"' + (sound ? ' checked' : '') + '> Bật</label></div>' +
					'<div class="set"><span class="set__ic set__ic--warn">' + ic('bell', 18) + '</span><div class="set__t"><b>Thông báo trên máy</b><small>' + (perm === 'granted' ? 'Đã bật: hiện thông báo kể cả khi đang ở tab khác' : perm === 'denied' ? 'Đã bị chặn trong cài đặt trình duyệt' : perm === 'unsupported' ? 'Trình duyệt không hỗ trợ' : 'Hiện thông báo kể cả khi đang ở tab khác') + '</small></div>' + (perm === 'default' ? '<button class="btn btn--sm" id="ntf">' + ic('bell', 15) + 'Bật</button>' : '') + '</div>' +
					'<div class="set"><span class="set__ic">' + ic('bolt', 18) + '</span><div class="set__t"><b>Thử âm báo</b><small>Kiểm tra loa của thiết bị</small></div><button class="btn btn--sm" id="tst">' + ic('sound', 15) + 'Phát thử</button></div>' +
				'</div></div>' +
			'</div>' +
			'<div class="card mt"><div class="card__hd"><h3>Liên kết nhanh</h3><span class="muted">Mở trang quản trị WordPress trên website chính</span></div><div class="card__bd"><div class="links">' +
				lnk(C.export, 'down', 'Xuất đơn hàng (CSV)', 'Mở bằng Excel, đủ tiếng Việt', true) +
				lnk(C.site, 'globe', 'Xem website', C.site.replace(/^https?:\/\//, '').replace(/\/$/, '')) +
				lnk(C.wp.orders, 'orders', 'Đơn hàng trong WordPress', 'Sửa chi tiết, tải báo cáo PDF lên') +
				lnk(C.wp.payouts, 'bank', 'Rút tiền CTV trong WordPress', 'Danh sách đầy đủ') +
				lnk(C.wp.settings, 'cog', 'Cài đặt website', 'Liên hệ, thanh toán, CTV, API') +
				lnk(C.wp.users, 'users', 'Người dùng', 'Tài khoản khách và quản trị') +
			'</div></div></div>' +
			'<p class="muted" style="text-align:center;font-size:12.5px;margin-top:22px">Admin Console | phiên bản ' + esc(C.version) + ' | dữ liệu đồng bộ trực tiếp với ' + esc(C.site.replace(/^https?:\/\//, '').replace(/\/$/, '')) + '</p>';
		$$('[data-logout]', el).forEach(function (b) { b.onclick = logout; });
		$('#thm').onclick = function (e) { var b = e.target.closest('[data-v]'); if (!b) return; applyTheme(b.dataset.v); $$('#thm button').forEach(function (x) { x.classList.toggle('on', x === b); }); };
		$('#snd').onchange = function () { store('sound', this.checked ? '1' : '0'); };
		$('#tst').onclick = function () { ding(true); };
		var n = $('#ntf'); if (n) n.onclick = function () { Notification.requestPermission().then(function () { render(); }); };
		// Đổi / xóa ảnh đại diện (đồng bộ toàn website).
		function avSend(fd) {
			var big = $('.av--xl', el); if (big) big.classList.add('is-busy');
			fd.append('action', 'vs_avatar'); fd.append('nonce', C.anonce);
			return fetch(C.ajax, { method: 'POST', body: fd, credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(function (j) {
				if (!j || !j.success) throw new Error((j && j.data && j.data.message) || 'Không lưu được ảnh.');
				C.user.avatar = j.data.url; C.user.initial = j.data.initial;
				paintMe(); $('#avr').hidden = !j.data.url; toast(j.data.message);
			}).catch(function (e) { toast(e.message || 'Lỗi kết nối, vui lòng thử lại.', 'err'); }).then(function () { var b = $('.av--xl', el); if (b) b.classList.remove('is-busy'); $('#avf').value = ''; });
		}
		$('#avf').onchange = function () {
			var f = this.files && this.files[0]; if (!f) return;
			if (!/^image\/(jpeg|png|webp|gif)$/.test(f.type)) { toast('Chỉ nhận ảnh JPG, PNG, WEBP hoặc GIF.', 'err'); this.value = ''; return; }
			if (f.size > 5 * 1024 * 1024) { toast('Ảnh quá lớn (tối đa 5 MB).', 'err'); this.value = ''; return; }
			var fd = new FormData(); fd.append('op', 'upload'); fd.append('file', f); avSend(fd);
		};
		$('#avr').onclick = function () {
			modal({ title: 'Xóa ảnh đại diện?', text: 'Ảnh sẽ bị xóa khỏi website, app và WordPress.', ok: 'Xóa ảnh', danger: true }).then(function (ok) {
				if (!ok) return; var fd = new FormData(); fd.append('op', 'remove'); avSend(fd);
			});
		};
	};
	function lnk(href, icon, t, s, same) { return '<a class="lnk" href="' + esc(href) + '"' + (same ? '' : ' target="_blank" rel="noopener"') + '>' + ic(icon, 20) + '<span><b>' + esc(t) + '</b><small>' + esc(s) + '</small></span></a>'; }

	/* =========================================================
	   ĐỒNG BỘ THỜI GIAN THỰC (hỏi máy chủ định kỳ)
	   ========================================================= */
	var feedState = { latest: 0, unread: 0, first: true };
	var audioCtx = null;
	function ding(force) {
		if (!force && store('sound') === '0') return;
		try {
			audioCtx = audioCtx || new (window.AudioContext || window.webkitAudioContext)();
			[[880, 0], [1320, .13]].forEach(function (n) {
				var o = audioCtx.createOscillator(), g = audioCtx.createGain(), t = audioCtx.currentTime + n[1];
				o.type = 'sine'; o.frequency.value = n[0];
				g.gain.setValueAtTime(0.0001, t); g.gain.exponentialRampToValueAtTime(0.25, t + 0.02); g.gain.exponentialRampToValueAtTime(0.0001, t + 0.45);
				o.connect(g); g.connect(audioCtx.destination); o.start(t); o.stop(t + 0.5);
			});
		} catch (e) { /* trình duyệt chặn âm thanh */ }
	}
	function poll() {
		if (document.hidden && !feedState.first && 'Notification' in window && Notification.permission !== 'granted') return;
		api('vs_adm_feed').then(function (d) {
			var fresh = d.items.filter(function (n) { return n.t > feedState.latest; });
			if (!feedState.first && fresh.length) {
				fresh.slice(0, 3).forEach(function (n) {
					toast(n.text || '', 'new', n.title, n.order ? function () { openOrder(n.order); } : null);
					if (document.hidden && 'Notification' in window && Notification.permission === 'granted') {
						try { var nt = new Notification(n.title, { body: n.text || '', icon: C.logo, tag: 'vs-' + n.id }); nt.onclick = function () { window.focus(); if (n.order) openOrder(n.order); nt.close(); }; } catch (e) { /* bỏ qua */ }
					}
				});
				ding();
				if (route.id === '' || route.id === 'orders') refreshView();
				if (notifOpen()) loadNotif();
			}
			feedState.first = false;
			feedState.latest = Math.max(feedState.latest, d.latest || 0);
			setBadges({ unread: notifOpen() ? 0 : d.unread });
		}, function () { /* mất mạng: thử lại lần sau */ });
	}

	/* =========================================================
	   KHỞI ĐỘNG
	   ========================================================= */
	if (!C.logged || !C.can) { renderLogin(); return; }
	renderShell();
	window.addEventListener('hashchange', function () { closeDrawerSilently(); render(); });
	function closeDrawerSilently() { var dr = $('.drawer'); if (dr && !parseHash().q.o) { dr.remove(); var ov = $('.ov--drawer'); if (ov) ov.remove(); document.body.style.overflow = ''; cur = null; } }
	render();
	poll();
	setInterval(poll, 20000);
	document.addEventListener('visibilitychange', function () { if (!document.hidden) poll(); });
	// Lấy số liệu cho huy hiệu menu khi không mở trang Tổng quan.
	if (route.id !== '') api('vs_app_dash').then(function (d) { setBadges({ orders: d.stats.manual, payouts: d.kpi.payouts }); }, function () {});
})();
