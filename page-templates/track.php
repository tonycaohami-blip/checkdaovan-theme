<?php
/**
 * Template Name: Tra cứu đơn hàng
 */
get_header();
get_template_part( 'template-parts/page-hero', null, array(
	'title'   => 'Tra cứu đơn hàng',
	'eyebrow' => 'Không cần đăng nhập',
	'lead'    => 'Nhập mã đơn và số điện thoại đã dùng khi đặt hàng để xem tiến độ, kết quả và tải báo cáo.',
	'size'    => 'sm',
) );
?>
<section class="section section--tight">
	<div class="container container--narrow">
		<form class="track" id="form-track" data-order-url="<?php echo esc_url( vs_page_url( 'dat-hang' ) ); ?>">
			<input type="text" name="code" placeholder="Mã đơn (VD: VS1018AB2C)" required autocomplete="off" aria-label="Mã đơn" value="<?php echo isset( $_GET['ma'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET['ma'] ) ) ) : ''; ?>">
			<input type="tel" name="phone" placeholder="Số điện thoại đặt hàng" required aria-label="Số điện thoại">
			<button class="btn btn--primary" type="submit"><?php echo vs_icon( 'search', 18 ); // phpcs:ignore ?> Tra cứu</button>
		</form>
		<div class="track-result" id="track-result" hidden></div>
		<p class="center-note">
			<?php if ( is_user_logged_in() ) : ?>
				Xem tất cả đơn của bạn trong <a href="<?php echo esc_url( vs_account_url( 'don-hang' ) ); ?>">trang tài khoản</a>.
			<?php else : ?>
				Có tài khoản? <a href="<?php echo esc_url( vs_account_url() ); ?>">Đăng nhập</a> để xem tất cả đơn hàng mà không cần nhập mã.
			<?php endif; ?>
		</p>
	</div>
</section>
<?php
get_footer();
