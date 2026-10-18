<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main">Bỏ qua đến nội dung</a>
<?php $me = wp_get_current_user(); ?>

<div class="topbar">
	<div class="container topbar__inner">
		<span class="topbar__msg">
			<svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true"><path d="M12 2l8 4v6c0 5-3.4 9.3-8 10-4.6-.7-8-5-8-10V6z" fill="currentColor"/></svg>
			Hoạt động 24/7 · Không lưu bài · Tự xóa file sau 30 ngày
		</span>
		<span class="topbar__links">
			<span class="topbar__hours">Tư vấn viên: <?php echo esc_html( vs_opt( 'hours' ) ); ?></span>
			<a href="<?php echo esc_url( vs_page_url( 'tra-cuu-don-hang' ) ); ?>">Tra cứu đơn</a>
			<a href="<?php echo esc_url( vs_zalo( vs_opt( 'zalo' ) ) ); ?>" target="_blank" rel="noopener">Zalo <?php echo esc_html( vs_opt( 'zalo' ) ); ?></a>
		</span>
	</div>
</div>

<header class="site-header" id="top">
	<div class="container site-header__inner">
		<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?> - Trang chủ">
			<?php
			if ( has_custom_logo() ) {
				echo wp_get_attachment_image( get_theme_mod( 'custom_logo' ), 'full', false, array( 'class' => 'brand__logo' ) );
			} else {
				get_template_part( 'template-parts/logo', null, array( 'variant' => 'dark' ) );
			}
			?>
		</a>
		<nav class="nav" id="nav" aria-label="Menu chính">
			<?php
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => 'nav__list',
				'fallback_cb'    => 'vs_fallback_menu',
				'depth'          => 2,
			) );
			?>
			<div class="nav__mobile-cta">
				<?php if ( $me->exists() ) : ?>
					<a class="btn btn--outline btn--block" href="<?php echo esc_url( vs_account_url() ); ?>">Tài khoản: <?php echo esc_html( $me->display_name ); ?></a>
				<?php else : ?>
					<a class="btn btn--outline btn--block" href="<?php echo esc_url( vs_account_url() ); ?>">Đăng nhập / Đăng ký</a>
				<?php endif; ?>
				<a class="btn btn--accent btn--block" href="<?php echo esc_url( vs_page_url( 'dat-hang' ) ); ?>">Đặt hàng ngay</a>
			</div>
		</nav>
		<div class="site-header__cta">
			<a class="hotline" href="<?php echo esc_attr( vs_tel( vs_opt( 'hotline' ) ) ); ?>">
				<span class="hotline__icon"><?php echo vs_icon( 'phone', 16 ); // phpcs:ignore ?></span>
				<span><small>Hotline</small><?php echo esc_html( vs_opt( 'hotline' ) ); ?></span>
			</a>
			<?php if ( $me->exists() ) : ?>
				<?php echo vs_bell_html(); // phpcs:ignore -- đã escape. ?>
				<a class="acct-btn acct-btn--in" href="<?php echo esc_url( vs_account_url() ); ?>" title="<?php echo esc_attr( 'Tài khoản: ' . $me->display_name ); ?>">
					<span class="avatar avatar--sm"><?php $w = preg_split( '/\s+/u', trim( $me->display_name ) ); echo esc_html( mb_strtoupper( mb_substr( end( $w ), 0, 1 ) ) ); ?></span>
					<span class="acct-btn__name"><small>Xin chào</small><?php echo esc_html( $me->display_name ); ?></span>
				</a>
			<?php else : ?>
				<a class="acct-btn" href="<?php echo esc_url( vs_account_url() ); ?>"><?php echo vs_icon( 'user', 18 ); // phpcs:ignore ?><span class="acct-btn__name">Đăng nhập</span></a>
			<?php endif; ?>
			<a class="btn btn--primary btn--sm header-order" href="<?php echo esc_url( vs_page_url( 'dat-hang' ) ); ?>">Đặt hàng</a>
			<button class="nav-toggle" aria-label="Mở menu" aria-controls="nav" aria-expanded="false"><span></span><span></span><span></span></button>
		</div>
	</div>
</header>
<main id="main">
