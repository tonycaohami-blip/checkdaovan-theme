<?php
/**
 * Tối ưu tốc độ tải trang: bỏ mã không dùng, tải font không chặn hiển thị.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* Bỏ emoji của WordPress (trình duyệt hiện emoji sẵn) - bớt 1 script và 1 khối CSS mỗi trang. */
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );
remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
add_filter( 'emoji_svg_url', '__return_false' );

/* Bỏ các thẻ không cần trong <head>. */
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );

/* CSS khối của trình soạn thảo chỉ cần ở trang bài viết / trang nội dung. */
add_action( 'wp_enqueue_scripts', function () {
	if ( is_singular() && ! is_page_template() ) {
		return;
	}
	foreach ( array( 'wp-block-library', 'wp-block-library-theme', 'classic-theme-styles', 'global-styles' ) as $h ) {
		wp_dequeue_style( $h );
	}
}, 100 );

/* Font Google: tải không chặn hiển thị (trang hiện ngay bằng font hệ thống, đổi sang Be Vietnam Pro khi tải xong). */
add_filter( 'style_loader_tag', function ( $tag, $handle, $href ) {
	if ( 'vs-fonts' !== $handle || is_admin() ) {
		return $tag;
	}
	$href = esc_url( $href );
	return '<link rel="preload" as="style" href="' . $href . '">' . "\n"
		. '<link rel="stylesheet" href="' . $href . '" media="print" onload="this.media=\'all\'">' . "\n"
		. '<noscript><link rel="stylesheet" href="' . $href . '"></noscript>' . "\n";
}, 10, 3 );
