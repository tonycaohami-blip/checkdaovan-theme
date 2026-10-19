<?php
/**
 * Gia cố bảo mật chung: ẩn thông tin phiên bản, chặn dò tên tài khoản, tắt XML-RPC, header bảo mật.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* Không cho sửa code theme/plugin ngay trong trang quản trị (nếu tài khoản admin bị lộ, kẻ xấu không chèn mã được). */
if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
	define( 'DISALLOW_FILE_EDIT', true );
}

/* Ẩn phiên bản WordPress. */
remove_action( 'wp_head', 'wp_generator' );
add_filter( 'the_generator', '__return_empty_string' );

/* Tắt XML-RPC (không dùng; hay bị dò mật khẩu hàng loạt) và pingback. */
add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter( 'wp_headers', function ( $h ) {
	unset( $h['X-Pingback'] );
	return $h;
} );
add_filter( 'xmlrpc_methods', function () {
	return array();
} );

/* Chặn dò tên đăng nhập: API danh sách người dùng chỉ dành cho người có quyền quản lý tài khoản. */
add_filter( 'rest_endpoints', function ( $e ) {
	if ( ! current_user_can( 'list_users' ) ) {
		foreach ( array_keys( $e ) as $route ) {
			if ( 0 === strpos( $route, '/wp/v2/users' ) ) {
				unset( $e[ $route ] );
			}
		}
	}
	return $e;
} );

/* Chặn ?author=1 và trang tác giả (lộ tên đăng nhập trong đường dẫn). */
add_action( 'template_redirect', function () {
	if ( is_user_logged_in() && current_user_can( 'list_users' ) ) {
		return;
	}
	if ( isset( $_GET['author'] ) || is_author() ) { // phpcs:ignore
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
}, 1 );
add_filter( 'author_link', function () {
	return home_url( '/' );
} );
/* Sitemap của WordPress: bỏ danh sách tác giả. */
add_filter( 'wp_sitemaps_add_provider', function ( $provider, $name ) {
	return 'users' === $name ? false : $provider;
}, 10, 2 );

/* Đăng nhập sai: không tiết lộ tên đăng nhập có tồn tại hay không. */
add_filter( 'login_errors', function () {
	return 'Thông tin đăng nhập không đúng.';
} );

/* Header bảo mật cho mọi trang. */
add_action( 'send_headers', function () {
	if ( headers_sent() ) {
		return;
	}
	header_remove( 'X-Powered-By' );
	header( 'X-Content-Type-Options: nosniff' );
	header( 'X-Frame-Options: SAMEORIGIN' );
	header( 'Referrer-Policy: strict-origin-when-cross-origin' );
	header( 'Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()' );
	if ( is_ssl() ) {
		header( 'Strict-Transport-Security: max-age=15552000' );
	}
} );
