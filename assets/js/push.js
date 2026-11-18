/**
 * Thông báo đẩy trên điện thoại và máy tính (dùng chung cho website và app quản trị).
 * window.VSPush: state(), enable(), disable(), test(), mount(el, { compact }).
 */
(function () {
	'use strict';
	var C = window.VSPUSH;
	if (!C || window.VSPush) return;
	var KIND = C.kind === 'app' ? 'app' : 'site';
	var FLAG = 'vspush_' + KIND;
	var ok = 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window && window.isSecureContext !== false;
	var ios = /iPhone|iPad|iPod/.test(navigator.userAgent) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
	var standalone = (window.matchMedia && matchMedia('(display-mode: standalone)').matches) || navigator.standalone === true;
	var mounts = [];

	function ls(k, v) { try { if (v === undefined) return localStorage.getItem(k); if (v === null) localStorage.removeItem(k); else localStorage.setItem(k, v); } catch (e) { return null; } }
	function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
	function keyBytes(b64) {
		var s = (b64 + '==='.slice(0, (4 - b64.length % 4) % 4)).replace(/-/g, '+').replace(/_/g, '/'), raw = atob(s), a = new Uint8Array(raw.length);
		for (var i = 0; i < raw.length; i++) a[i] = raw.charCodeAt(i);
		return a;
	}
	function b64(buf) { var s = '', a = new Uint8Array(buf || []); for (var i = 0; i < a.length; i++) s += String.fromCharCode(a[i]); return btoa(s).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, ''); }
	function post(op, sub, extra) {
		var fd = new FormData();
		fd.append('action', 'vs_push'); fd.append('nonce', C.nonce); fd.append('op', op); fd.append('kind', KIND);
		if (sub) {
			var j = sub.toJSON();
			fd.append('endpoint', j.endpoint);
			if (j.keys) { fd.append('p256dh', j.keys.p256dh); fd.append('auth', j.keys.auth); }
		}
		if (KIND === 'app') fd.append('base', location.origin + location.pathname);
		Object.keys(extra || {}).forEach(function (k) { fd.append(k, extra[k]); });
		return fetch(C.ajax, { method: 'POST', body: fd, credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(function (j) {
			if (!j || !j.success) throw new Error((j && j.data && j.data.message) || 'Lỗi kết nối, vui lòng thử lại.');
			return j.data;
		});
	}
	function registration() {
		return navigator.serviceWorker.getRegistration('/').then(function (r) { return r || navigator.serviceWorker.register(C.sw, { scope: '/' }); }).then(function () { return navigator.serviceWorker.ready; });
	}
	function current() {
		if (!ok) return Promise.resolve(null);
		return navigator.serviceWorker.getRegistration('/').then(function (r) { return r ? r.pushManager.getSubscription() : null; }).catch(function () { return null; });
	}

	/** 'unsupported' | 'ios-install' | 'denied' | 'off' | 'on' */
	function state() {
		if (!ok || !C.key) return Promise.resolve(ios && !standalone ? 'ios-install' : 'unsupported');
		if (Notification.permission === 'denied') return Promise.resolve('denied');
		return current().then(function (s) { return s && Notification.permission === 'granted' && ls(FLAG) === s.endpoint ? 'on' : 'off'; });
	}
	function enable() {
		if (!ok) return Promise.reject(new Error(ios && !standalone ? 'Trên iPhone/iPad, hãy thêm trang vào Màn hình chính rồi mở từ biểu tượng đó.' : 'Trình duyệt này chưa hỗ trợ thông báo đẩy.'));
		return Promise.resolve(Notification.permission === 'granted' ? 'granted' : Notification.requestPermission()).then(function (p) {
			if (p !== 'granted') throw new Error(p === 'denied' ? 'Bạn đã chặn thông báo. Hãy cho phép trong cài đặt trang web của trình duyệt.' : 'Bạn chưa cho phép thông báo.');
			return registration();
		}).then(function (reg) {
			return reg.pushManager.getSubscription().then(function (s) {
				// Khóa máy chủ đã đổi: đăng ký lại.
				if (s && s.options && s.options.applicationServerKey && b64(s.options.applicationServerKey) !== C.key) return s.unsubscribe().then(function () { return null; });
				return s;
			}).then(function (s) {
				return s || reg.pushManager.subscribe({ userVisibleOnly: true, applicationServerKey: keyBytes(C.key) }).catch(function (e) {
					throw new Error(/denied|incognito|private/i.test(e && e.message) ? 'Trình duyệt không cho đăng ký thông báo (thường do đang mở ở chế độ ẩn danh). Hãy mở trang ở cửa sổ thường.' : 'Không đăng ký được thông báo: ' + ((e && e.message) || 'lỗi không rõ'));
				});
			});
		}).then(function (s) {
			return post('sub', s).then(function () { ls(FLAG, s.endpoint); paint(); return s; });
		});
	}
	function disable() {
		return current().then(function (s) {
			ls(FLAG, null);
			if (!s) return;
			return post('unsub', s).then(function () {
				// Không còn trang nào trên tên miền này dùng thông báo: hủy đăng ký với trình duyệt.
				if (!ls('vspush_' + (KIND === 'app' ? 'site' : 'app'))) return s.unsubscribe();
			});
		}).then(paint);
	}
	function test() {
		return current().then(function (s) { if (!s) throw new Error('Thiết bị này chưa bật thông báo.'); return post('test', s); });
	}

	/* ---------- Giao diện dùng chung ---------- */
	var BELL = '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>';
	var DEV = '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="4" width="14" height="10" rx="2"/><path d="M6 18h6M9 14v4"/><rect x="17" y="8" width="5" height="12" rx="1.2"/></svg>';
	var TEXT = {
		on: 'Đang bật: thông báo hiện trên màn hình thiết bị này, kể cả khi đã đóng trang.',
		off: KIND === 'app' ? 'Nhận thông báo đơn mới, thanh toán và việc cần xử lý ngay trên điện thoại / máy tính.' : 'Nhận thông báo khi đơn được thanh toán, có kết quả và hoàn thành, kể cả khi đã đóng trang.',
		denied: 'Thông báo đang bị chặn. Bấm biểu tượng ổ khóa cạnh địa chỉ trang > Thông báo > Cho phép, rồi tải lại trang.',
		'ios-install': 'Trên iPhone/iPad: bấm nút Chia sẻ > “Thêm vào MH chính”, mở trang từ biểu tượng mới rồi bật thông báo (iOS 16.4 trở lên).',
		unsupported: 'Trình duyệt này chưa hỗ trợ thông báo đẩy. Hãy dùng Chrome, Edge, Firefox hoặc Safari bản mới.'
	};
	var PILL = { on: 'Đang bật', off: 'Đang tắt', denied: 'Bị chặn', 'ios-install': 'Cần cài', unsupported: 'Không hỗ trợ' };
	function html(st, o) {
		if (o.compact) {
			if (st !== 'off' && st !== 'ios-install') return '';
			return '<div class="pushc pushc--compact pushc--' + st + '"><span class="pushc__ic">' + BELL + '</span><div class="pushc__t"><b>Thông báo trên thiết bị này</b><small>' + esc(st === 'off' ? 'Báo ngay cả khi đã đóng trang.' : TEXT[st]) + '</small></div>' +
				(st === 'off' ? '<button type="button" class="pushc__btn pushc__btn--pri" data-push-on>Bật</button>' : '') + '</div>';
		}
		var acts = st === 'off' ? '<button type="button" class="pushc__btn pushc__btn--pri" data-push-on>' + BELL.replace(/20/g, '16') + '<span>Bật thông báo</span></button>'
			: st === 'on' ? '<button type="button" class="pushc__btn" data-push-test><span>Gửi thử</span></button><button type="button" class="pushc__btn pushc__btn--ghost" data-push-off><span>Tắt</span></button>' : '';
		return '<div class="pushc pushc--' + st + '"><span class="pushc__ic">' + DEV + '</span><div class="pushc__t"><b>Thông báo trên điện thoại, máy tính <em class="pushc__pill">' + PILL[st] + '</em></b><small>' + esc(TEXT[st]) + '</small></div>' + (acts ? '<div class="pushc__a">' + acts + '</div>' : '') + '</div>';
	}
	function run(btn, fn, okMsg) {
		btn.disabled = true; btn.classList.add('is-busy');
		fn().then(function (r) { note(okMsg || (r && r.message) || ''); }, function (e) { note(e.message, true); paint(); }).then(function () { btn.disabled = false; btn.classList.remove('is-busy'); });
	}
	function note(msg, err) {
		if (!msg) return;
		if (typeof C.toast === 'function') return C.toast(msg, err);
		if (window.VSToast) return window.VSToast(msg, err);
		var t = document.createElement('div'); t.className = 'pushc-toast' + (err ? ' is-err' : ''); t.textContent = msg; document.body.appendChild(t);
		setTimeout(function () { t.classList.add('on'); }, 10); setTimeout(function () { t.classList.remove('on'); setTimeout(function () { t.remove(); }, 300); }, 3800);
	}
	function paintOne(m) {
		if (!document.body.contains(m.el)) return;
		state().then(function (st) {
			m.el.innerHTML = html(st, m.o);
			m.el.hidden = !m.el.innerHTML;
			var on = m.el.querySelector('[data-push-on]'), off = m.el.querySelector('[data-push-off]'), ts = m.el.querySelector('[data-push-test]');
			if (on) on.onclick = function () { run(on, enable, 'Đã bật thông báo trên thiết bị này.'); };
			if (off) off.onclick = function () { run(off, disable, 'Đã tắt thông báo trên thiết bị này.'); };
			if (ts) ts.onclick = function () { run(ts, test); };
		});
	}
	function paint() { mounts = mounts.filter(function (m) { return document.body.contains(m.el); }); mounts.forEach(paintOne); }
	function mount(el, o) { if (!el) return; var m = { el: el, o: o || {} }; mounts.push(m); paintOne(m); }

	/* Bấm thông báo khi trang đang mở / có thông báo mới. */
	if (ok) {
		navigator.serviceWorker.addEventListener('message', function (e) {
			var d = e.data || {};
			if (d.vsOpen) { if (d.vsOpen !== location.href) location.href = d.vsOpen; else window.dispatchEvent(new Event('hashchange')); }
			if (d.vsPush) window.dispatchEvent(new CustomEvent('vs:push', { detail: d.vsPush }));
		});
		// Đồng bộ lại đăng ký 1 lần mỗi phiên (thiết bị đổi tài khoản, khóa thiết bị thay đổi...).
		current().then(function (s) {
			if (!s || ls(FLAG) !== s.endpoint || Notification.permission !== 'granted') return;
			try { if (sessionStorage.getItem(FLAG + '_sync')) return; sessionStorage.setItem(FLAG + '_sync', '1'); } catch (e) { /* bỏ qua */ }
			post('sub', s).catch(function () { /* thử lại phiên sau */ });
		});
	}

	window.VSPush = { supported: ok, kind: KIND, state: state, enable: enable, disable: disable, test: test, mount: mount, paint: paint };
	document.querySelectorAll('[data-push-card]').forEach(function (el) { mount(el, { compact: el.getAttribute('data-push-card') === 'compact' }); });
})();
