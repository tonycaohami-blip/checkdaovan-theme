<?php
/**
 * Template Name: Liên hệ
 */
get_header();
get_template_part( 'template-parts/page-hero', null, array(
	'title'   => 'Liên hệ với chúng tôi',
	'eyebrow' => 'Hỗ trợ ' . vs_opt( 'hours' ),
	'lead'    => 'Nhắn Zalo để được tư vấn nhanh nhất. Đội ngũ phản hồi trong vài phút trong giờ làm việc.',
	'size'    => 'sm',
) );
$cards = array(
	array( 'phone', 'Hotline', vs_opt( 'hotline' ), vs_tel( vs_opt( 'hotline' ) ), 'Gọi ngay' ),
	array( 'chat', 'Zalo', vs_opt( 'zalo' ), vs_zalo( vs_opt( 'zalo' ) ), 'Nhắn Zalo' ),
);
if ( vs_opt( 'email' ) ) {
	$cards[] = array( 'mail', 'Email', vs_opt( 'email' ), 'mailto:' . vs_opt( 'email' ), 'Gửi email' );
}
if ( vs_opt( 'address' ) ) {
	$cards[] = array( 'pin', 'Địa chỉ', vs_opt( 'address' ), 'https://www.google.com/maps/search/' . rawurlencode( vs_opt( 'address' ) ), 'Xem bản đồ' );
}
?>
<section class="section section--tight">
	<div class="container">
		<div class="contact-grid">
			<?php foreach ( $cards as $c ) : ?>
				<a class="contact-card reveal" href="<?php echo esc_url( $c[3] ); ?>" <?php echo 0 === strpos( $c[3], 'http' ) ? 'target="_blank" rel="noopener"' : ''; ?>>
					<span class="contact-card__icon"><?php echo vs_icon( $c[0], 26 ); // phpcs:ignore ?></span>
					<span class="contact-card__label"><?php echo esc_html( $c[1] ); ?></span>
					<b><?php echo esc_html( $c[2] ); ?></b>
					<span class="contact-card__cta"><?php echo esc_html( $c[4] ); ?> →</span>
				</a>
			<?php endforeach; ?>
		</div>

		<div class="split split--center contact-more">
			<div class="reveal">
				<span class="eyebrow">Câu hỏi thường gặp</span>
				<h2>Có thể bạn đang thắc mắc</h2>
				<div class="faq">
					<?php foreach ( array_slice( vs_faqs(), 0, 5, true ) as $q => $a ) : ?>
						<details class="faq__item"><summary><?php echo esc_html( $q ); ?></summary><p><?php echo esc_html( $a ); ?></p></details>
					<?php endforeach; ?>
				</div>
			</div>
			<div class="cta-card reveal">
				<?php echo vs_icon( 'search', 36 ); // phpcs:ignore ?>
				<h3>Đã đặt hàng?</h3>
				<p>Tra cứu tiến độ, tải báo cáo bằng mã đơn và số điện thoại.</p>
				<a class="btn btn--primary btn--block" href="<?php echo esc_url( vs_page_url( 'tra-cuu-don-hang' ) ); ?>">Tra cứu đơn hàng</a>
				<?php if ( vs_opt( 'company' ) || vs_opt( 'tax_code' ) ) : ?>
					<p class="muted small"><?php echo esc_html( trim( vs_opt( 'company' ) . ( vs_opt( 'tax_code' ) ? ' · MST/ĐKKD: ' . vs_opt( 'tax_code' ) : '' ) ) ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
<?php
get_footer();
