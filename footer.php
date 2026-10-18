</main>
<?php
$socials = array_filter( array(
	'Facebook' => vs_opt( 'facebook' ),
	'TikTok'   => vs_opt( 'tiktok' ),
	'YouTube'  => vs_opt( 'youtube' ),
) );
$cols = array(
	'Dịch vụ'   => array(
		'Kiểm tra đạo văn & AI' => vs_page_url( 'kiem-tra-dao-van' ),
		'Giảm tỷ lệ đạo văn'    => vs_page_url( 'giam-ty-le-dao-van' ),
		'Bảng giá'              => vs_page_url( 'bang-gia' ),
		'Kết quả thực tế'       => vs_page_url( 'ket-qua-thuc-te' ),
		'Đặt hàng'              => vs_page_url( 'dat-hang' ),
	),
	'Khách hàng' => array(
		'Tài khoản của tôi'     => vs_account_url(),
		'Tra cứu đơn hàng'      => vs_page_url( 'tra-cuu-don-hang' ),
		'Chương trình CTV'      => vs_page_url( 'cong-tac-vien' ),
		'Blog kiến thức'        => get_option( 'page_for_posts' ) ? get_permalink( get_option( 'page_for_posts' ) ) : home_url( '/blog/' ),
		'Liên hệ'               => vs_page_url( 'lien-he' ),
	),
	'Chính sách' => array(
		'Chính sách bảo mật'    => vs_page_url( 'chinh-sach-bao-mat' ),
		'Điều khoản dịch vụ'    => vs_page_url( 'dieu-khoan-dich-vu' ),
		'Chính sách hoàn tiền'  => vs_page_url( 'chinh-sach-hoan-tien' ),
	),
);
?>
<footer class="site-footer">
	<div class="container site-footer__grid">
		<div class="site-footer__brand">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="brand"><?php get_template_part( 'template-parts/logo', null, array( 'variant' => 'light' ) ); ?></a>
			<p class="site-footer__about">Nền tảng kiểm tra tỷ lệ trùng lặp, tỷ lệ AI và hỗ trợ chỉnh sửa giảm tỷ lệ đạo văn cho tiểu luận, khóa luận, luận văn, luận án và bài báo khoa học.</p>
			<ul class="contact-list">
				<li><?php echo vs_icon( 'phone', 16 ); // phpcs:ignore ?><a href="<?php echo esc_attr( vs_tel( vs_opt( 'hotline' ) ) ); ?>"><?php echo esc_html( vs_opt( 'hotline' ) ); ?></a></li>
				<li><?php echo vs_icon( 'chat', 16 ); // phpcs:ignore ?><a href="<?php echo esc_url( vs_zalo( vs_opt( 'zalo' ) ) ); ?>" target="_blank" rel="noopener">Zalo <?php echo esc_html( vs_opt( 'zalo' ) ); ?></a></li>
				<?php if ( vs_opt( 'email' ) ) : ?><li><?php echo vs_icon( 'mail', 16 ); // phpcs:ignore ?><a href="mailto:<?php echo esc_attr( vs_opt( 'email' ) ); ?>"><?php echo esc_html( vs_opt( 'email' ) ); ?></a></li><?php endif; ?>
				<?php if ( vs_opt( 'address' ) ) : ?><li><?php echo vs_icon( 'pin', 16 ); // phpcs:ignore ?><span><?php echo esc_html( vs_opt( 'address' ) ); ?></span></li><?php endif; ?>
				<li><?php echo vs_icon( 'clock', 16 ); // phpcs:ignore ?><span><?php echo esc_html( vs_opt( 'hours' ) ); ?></span></li>
			</ul>
			<?php if ( $socials ) : ?>
				<div class="socials">
					<?php foreach ( $socials as $label => $url ) : ?>
						<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $label ); ?></a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php foreach ( $cols as $title => $links ) : ?>
			<div>
				<h4><?php echo esc_html( $title ); ?></h4>
				<ul>
					<?php foreach ( $links as $label => $url ) : ?>
						<li><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endforeach; ?>
	</div>
	<div class="container site-footer__bottom">
		<span>© <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php echo esc_html( vs_opt( 'company', get_bloginfo( 'name' ) ) ); ?><?php echo vs_opt( 'tax_code' ) ? ' · MST/ĐKKD: ' . esc_html( vs_opt( 'tax_code' ) ) : ''; ?></span>
		<span class="site-footer__note">Turnitin, iThenticate, Copyleaks, Originality.ai, GPTZero, Winston AI, Grammarly, DoIT là nhãn hiệu của các chủ sở hữu tương ứng. Chúng tôi là đơn vị cung cấp dịch vụ độc lập, không phải đại diện của các hãng.</span>
	</div>
</footer>

<div class="float-contact">
	<a class="float-contact__btn float-contact__btn--zalo" href="<?php echo esc_url( vs_zalo( vs_opt( 'zalo' ) ) ); ?>" target="_blank" rel="noopener" aria-label="Chat Zalo">Zalo</a>
	<a class="float-contact__btn float-contact__btn--call" href="<?php echo esc_attr( vs_tel( vs_opt( 'hotline' ) ) ); ?>" aria-label="Gọi hotline"><?php echo vs_icon( 'phone', 22 ); // phpcs:ignore ?></a>
</div>

<nav class="mobile-bar" aria-label="Thao tác nhanh">
	<a href="<?php echo esc_url( vs_account_url() ); ?>"><?php echo vs_icon( 'user', 20 ); // phpcs:ignore ?><?php echo is_user_logged_in() ? 'Tài khoản' : 'Đăng nhập'; ?></a>
	<a href="<?php echo esc_url( vs_zalo( vs_opt( 'zalo' ) ) ); ?>" target="_blank" rel="noopener"><?php echo vs_icon( 'chat', 20 ); // phpcs:ignore ?>Zalo</a>
	<a class="mobile-bar__main" href="<?php echo esc_url( vs_page_url( 'dat-hang' ) ); ?>">Đặt hàng ngay</a>
</nav>

<div class="modal" id="modal" hidden>
	<div class="modal__backdrop" data-close></div>
	<div class="modal__card" role="dialog" aria-modal="true" aria-labelledby="modal-title">
		<button class="modal__x" data-close aria-label="Đóng">×</button>
		<div class="modal__content" id="modal-content"></div>
	</div>
</div>
<?php wp_footer(); ?>
</body>
</html>
