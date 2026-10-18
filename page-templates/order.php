<?php
/**
 * Template Name: Đặt hàng
 */
get_header();
get_template_part( 'template-parts/page-hero', null, array(
	'title'   => 'Đặt hàng',
	'eyebrow' => 'Chỉ mất 1 phút',
	'lead'    => 'Tải file lên, chọn dịch vụ và xem giá ngay. Thanh toán bằng mã QR, nhận mã đơn để theo dõi tiến độ.',
	'size'    => 'sm',
) );
?>
<section class="section section--order section--tight">
	<div class="container">
		<?php if ( vs_ref_discount_active() ) : ?>
			<div class="notice notice--ok notice--center">🎁 Bạn được giới thiệu bởi cộng tác viên - tự động giảm <?php echo esc_html( vs_opt( 'ref_discount' ) ); ?>% cho đơn hàng (không áp dụng cùng mã giảm giá khác).</div>
		<?php endif; ?>
		<?php get_template_part( 'template-parts/order-form' ); ?>
		<div class="order-steps">
			<div><?php echo vs_icon( 'file', 22 ); // phpcs:ignore ?><span><b>1. Tải file</b>Hệ thống tự đọc số trang</span></div>
			<div><?php echo vs_icon( 'qr', 22 ); // phpcs:ignore ?><span><b>2. Thanh toán QR</b>Số tiền điền sẵn</span></div>
			<div><?php echo vs_icon( 'clock', 22 ); // phpcs:ignore ?><span><b>3. Xử lý ngay</b>Từ 30 phút với gói hỏa tốc</span></div>
			<div><?php echo vs_icon( 'check', 22 ); // phpcs:ignore ?><span><b>4. Nhận kết quả</b>Email, Zalo, trang tài khoản</span></div>
		</div>
	</div>
</section>
<?php
get_footer();
