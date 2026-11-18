/* Service worker: chỉ nhận thông báo đẩy và mở đúng trang khi bấm. Không lưu cache, không chặn tải trang. */
self.addEventListener('install', function () { self.skipWaiting(); });
self.addEventListener('activate', function (e) { e.waitUntil(self.clients.claim()); });

self.addEventListener('push', function (e) {
	var d = {};
	try { d = e.data ? e.data.json() : {}; } catch (x) { d = { body: e.data ? e.data.text() : '' }; }
	var opts = {
		body: d.body || '',
		icon: d.icon || undefined,
		badge: d.badge || undefined,
		tag: d.tag || undefined,
		renotify: !!d.tag,
		timestamp: d.ts ? d.ts * 1000 : Date.now(),
		vibrate: [90, 50, 90],
		data: { url: d.url || '/' }
	};
	e.waitUntil(Promise.all([
		self.registration.showNotification(d.title || 'Check Đạo Văn', opts),
		// Báo cho các tab đang mở để cập nhật số thông báo ngay.
		self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (list) {
			list.forEach(function (c) { c.postMessage({ vsPush: d }); });
		})
	]));
});

self.addEventListener('notificationclick', function (e) {
	e.notification.close();
	var url = new URL((e.notification.data && e.notification.data.url) || '/', self.location.origin);
	if (url.origin !== self.location.origin) url = new URL('/', self.location.origin);
	var href = url.href, page = href.split('#')[0];
	e.waitUntil(self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (list) {
		// Đang mở đúng trang: đưa lên trước và chuyển tới nội dung của thông báo.
		for (var i = 0; i < list.length; i++) {
			var c = list[i];
			if (c.url.split('#')[0] === page && 'focus' in c) {
				c.postMessage({ vsOpen: href });
				return c.focus();
			}
		}
		return self.clients.openWindow(href);
	}));
});
