<?php
/**
 * Template Name: Quản trị đơn hàng
 *
 * Trang quản trị riêng cho quản trị viên: thống kê, danh sách đơn, chi tiết, thao tác nhanh, thông báo.
 */
if ( ! is_user_logged_in() ) {
	wp_safe_redirect( vs_account_url( '', array( 'redirect_to' => rawurlencode( get_permalink() ) ) ) );
	exit;
}
if ( ! vs_can_manage_orders() ) {
	wp_die( 'Trang này chỉ dành cho quản trị viên.', 'Không có quyền truy cập', array( 'response' => 403 ) );
}
get_header();
?>
<section class="adm" data-adm>
	<div class="container">
		<div class="adm__top">
			<div>
				<h1>Quản trị đơn hàng</h1>
				<p class="muted">Tự cập nhật mỗi 20 giây · <span data-adm-updated>đang tải…</span></p>
			</div>
			<div class="adm__tools">
				<button type="button" class="btn btn--outline btn--sm" data-adm-sound title="Phát âm thanh và hiện thông báo trình duyệt khi có sự kiện mới">🔕 Bật âm báo</button>
				<div class="bell adm-bell" data-adm-bell>
					<button type="button" class="bell__btn" aria-label="Thông báo quản trị" aria-expanded="false" data-adm-bell-toggle>
						<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
						<span class="bell__count" data-adm-bell-count hidden>0</span>
					</button>
					<div class="bell__panel" data-adm-bell-panel hidden>
						<div class="bell__head"><b>Thông báo quản trị</b><button type="button" class="bell__all" data-adm-bell-seen>Đánh dấu đã xem</button></div>
						<div class="bell__list" data-adm-bell-list><div class="bell__empty">Chưa có thông báo.</div></div>
					</div>
				</div>
				<a class="btn btn--outline btn--sm" href="<?php echo esc_url( admin_url( 'edit.php?post_type=vs_order' ) ); ?>">WP Admin</a>
			</div>
		</div>

		<div class="adm-stats" data-adm-stats>
			<div class="adm-stat"><span>Đơn hôm nay</span><b data-s="today">-</b></div>
			<div class="adm-stat"><span>Doanh thu hôm nay</span><b data-s="rev_today">-</b></div>
			<div class="adm-stat"><span>Doanh thu tháng</span><b data-s="rev_month">-</b></div>
			<div class="adm-stat adm-stat--warn"><span>Chờ thanh toán</span><b data-s="cho_thanh_toan">-</b></div>
			<div class="adm-stat adm-stat--info"><span>Đang xử lý</span><b data-s="dang_xu_ly">-</b></div>
			<div class="adm-stat adm-stat--alert"><span>Cần xử lý tay</span><b data-s="manual">-</b></div>
		</div>

		<div class="adm-bar">
			<div class="adm-tabs" role="tablist" data-adm-tabs>
				<button type="button" class="is-active" data-status="all">Tất cả <i data-c="all"></i></button>
				<button type="button" data-status="cho_thanh_toan">Chờ thanh toán <i data-c="cho_thanh_toan"></i></button>
				<button type="button" data-status="dang_xu_ly">Đang xử lý <i data-c="dang_xu_ly"></i></button>
				<button type="button" data-status="hoan_thanh">Hoàn thành <i data-c="hoan_thanh"></i></button>
				<button type="button" data-status="huy">Đã hủy <i data-c="huy"></i></button>
			</div>
			<div class="adm-filters">
				<select data-adm-type aria-label="Loại đơn">
					<option value="all">Mọi loại đơn</option>
					<option value="check">Kiểm tra đạo văn &amp; AI</option>
					<option value="reduce">Giảm tỷ lệ</option>
				</select>
				<input type="search" data-adm-q placeholder="Tìm mã đơn, tên, SĐT, email…" aria-label="Tìm đơn">
			</div>
		</div>

		<div class="adm-list" data-adm-list><div class="adm-empty">Đang tải đơn hàng…</div></div>
		<div class="adm-pager" data-adm-pager></div>
	</div>

	<div class="adm-drawer" data-adm-drawer hidden>
		<div class="adm-drawer__bg" data-adm-close></div>
		<aside class="adm-drawer__panel" role="dialog" aria-modal="true" aria-label="Chi tiết đơn hàng">
			<button type="button" class="adm-drawer__x" data-adm-close aria-label="Đóng">×</button>
			<div data-adm-detail></div>
		</aside>
	</div>
</section>
<?php
get_footer();
