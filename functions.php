<?php
/**
 * Check Đạo Văn theme bootstrap.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'VS_VERSION', '6.4.1' );
define( 'VS_BRAND', 'Check Đạo Văn' );       // Tên thương hiệu mặc định (đổi trong Cài đặt > Chung > Tên website).
define( 'VS_DOMAIN', 'checkdaovan247.com' );          // Tên miền chính thức.
define( 'VS_EMAIL', 'hotro@checkdaovan247.com' );     // Email hỗ trợ mặc định.
define( 'VS_DIR', get_template_directory() );
define( 'VS_URI', get_template_directory_uri() );

require VS_DIR . '/inc/pricing.php';
require VS_DIR . '/inc/settings.php';
require VS_DIR . '/inc/orders.php';
require VS_DIR . '/inc/ajax.php';
require VS_DIR . '/inc/extras.php';
require VS_DIR . '/inc/accounts.php';
require VS_DIR . '/inc/affiliate.php';
require VS_DIR . '/inc/cases.php';
require VS_DIR . '/inc/auto/core.php';
require VS_DIR . '/inc/auto/payments.php';
require VS_DIR . '/inc/auto/providers.php';
require VS_DIR . '/inc/auto/pdf.php';
require VS_DIR . '/inc/auto/admin.php';
require VS_DIR . '/inc/auto/selfupdate.php';
require VS_DIR . '/inc/notify.php';
require VS_DIR . '/inc/admin-orders.php';
require VS_DIR . '/inc/content.php';

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'custom-logo', array( 'height' => 48, 'width' => 200, 'flex-width' => true ) );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'script', 'style' ) );
	add_theme_support( 'responsive-embeds' );
	register_nav_menus( array(
		'primary' => 'Menu chính',
		'footer'  => 'Menu chân trang',
	) );
} );

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'vs-fonts', 'https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap', array(), null );
	wp_enqueue_style( 'vs-main', VS_URI . '/assets/css/main.css', array(), VS_VERSION );
	$deps = array();
	if ( is_page_template( 'page-templates/order.php' ) ) {
		// Đọc số trang file Word ngay trên trình duyệt.
		wp_enqueue_script( 'jszip', 'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js', array(), '3.10.1', true );
		$deps[] = 'jszip';
	}
	wp_enqueue_script( 'vs-main', VS_URI . '/assets/js/main.js', $deps, VS_VERSION, true );
	wp_localize_script( 'vs-main', 'VS', array(
		'ajax'    => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'vs_order' ),
		'pricing' => vs_pricing(),
		'maxMB'   => vs_max_upload_mb(),
		'refPct'  => vs_ref_discount_active() ? (float) vs_opt( 'ref_discount' ) : 0,
	) );
} );

/** Định dạng tiền Việt: 150000 -> 150.000đ */
function vs_money( $n ) {
	return number_format( (float) $n, 0, ',', '.' ) . 'đ';
}

/** Giá trị mặc định khi chưa cấu hình. */
function vs_opt_defaults() {
	return array(
		'email'        => VS_EMAIL,
		'notify_email' => VS_EMAIL,
		'hotline'      => '0944 580 600',
		'zalo'         => '0944 580 600',
		'hours'        => '7:00 - 23:00, tất cả các ngày',
		'bank_code'    => 'NCB',
		'bank_name'    => 'NCB Bank (Quốc Dân)',
		'bank_account' => '106936837029',
		'bank_holder'  => 'PHAM VAN KHANH',
		'aff_pct'      => 10,
		'ref_discount' => 5,
		'payout_min'   => 200000,
		'ref_days'     => 30,
	);
}

/** Lấy 1 thiết lập (liên hệ, thanh toán, cộng tác viên…). */
function vs_opt( $key, $default = null ) {
	$opts = get_option( 'vs_settings', array() );
	if ( isset( $opts[ $key ] ) && '' !== $opts[ $key ] ) {
		return $opts[ $key ];
	}
	if ( null !== $default ) {
		return $default;
	}
	$d = vs_opt_defaults();
	return isset( $d[ $key ] ) ? $d[ $key ] : '';
}

function vs_max_upload_mb() {
	return (int) min( 20, floor( wp_max_upload_size() / 1048576 ) );
}

function vs_tel( $n ) {
	return 'tel:' . preg_replace( '/[^\d+]/', '', $n );
}

function vs_zalo( $n ) {
	return 'https://zalo.me/' . preg_replace( '/\D/', '', $n );
}

/** URL của trang theo slug (dùng cho các trang hệ thống: dat-hang, tai-khoan…). */
function vs_page_url( $slug, $args = array() ) {
	static $cache = array();
	if ( ! isset( $cache[ $slug ] ) ) {
		$page           = get_page_by_path( $slug );
		$cache[ $slug ] = $page ? get_permalink( $page ) : home_url( '/' . $slug . '/' );
	}
	return $args ? add_query_arg( $args, $cache[ $slug ] ) : $cache[ $slug ];
}

/** Giá thấp nhất của dịch vụ kiểm tra. */
function vs_min_check_price() {
	return min( array_filter( array( vs_min_price( 'plag' ), vs_min_price( 'ai' ) ) ) );
}

/** Màu, chữ viết tắt, nhãn của từng phần mềm. */
function vs_tool_meta() {
	return array(
		'turnitin'    => array( 'T', '#d43f3a', 'Trường đại học' ),
		'ithenticate' => array( 'iT', '#1f6feb', 'Tạp chí khoa học' ),
		'copyleaks'   => array( 'C', '#7c3aed', 'Đa ngôn ngữ' ),
		'originality' => array( 'O', '#0f9d76', 'Chuyên AI' ),
		'gptzero'     => array( 'GZ', '#ea580c', 'Phát hiện AI' ),
		'winston'     => array( 'W', '#0284c7', 'AI + đạo văn' ),
		'grammarly'   => array( 'G', '#16a34a', 'Bài tiếng Anh' ),
		'doit'        => array( 'D', '#be123c', 'Việt Nam' ),
	);
}

/** Bộ icon nét dùng chung. */
function vs_icon( $name, $size = 24 ) {
	$paths = array(
		'shield' => '<path d="M12 2l8 4v6c0 5-3.4 9.3-8 10-4.6-.7-8-5-8-10V6z"/><path d="M8.5 12l2.5 2.5 4.5-5"/>',
		'clock'  => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
		'lock'   => '<rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 018 0v3"/>',
		'file'   => '<path d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8z"/><path d="M14 3v5h5M9 13h6M9 17h4"/>',
		'pen'    => '<path d="M4 20h4L19 9l-4-4L4 16z"/><path d="M13.5 6.5l4 4"/>',
		'qr'     => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><path d="M14 14h3v3h-3zM20 14v.01M14 20h.01M17 20h4v-3"/>',
		'chat'   => '<path d="M4 5h16v11H9l-5 4z"/>',
		'bell'   => '<path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>',
		'refund' => '<path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/><path d="M12 8v8"/>',
		'target' => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1"/>',
		'user'   => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0116 0"/>',
		'users'  => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0113 0"/><path d="M16 4.5a3.5 3.5 0 010 7M18 14a6.5 6.5 0 013.5 6"/>',
		'gift'   => '<rect x="3" y="8" width="18" height="5" rx="1"/><path d="M5 13v8h14v-8M12 8v13M12 8S10.5 3 8 3.5 7 8 12 8zM12 8s1.5-5 4-4.5S17 8 12 8z"/>',
		'link'   => '<path d="M10 14a5 5 0 007 0l3-3a5 5 0 00-7-7l-1 1"/><path d="M14 10a5 5 0 00-7 0l-3 3a5 5 0 007 7l1-1"/>',
		'wallet' => '<rect x="3" y="6" width="18" height="14" rx="2"/><path d="M3 10h18M16 15h2"/>',
		'chart'  => '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>',
		'list'   => '<path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>',
		'home'   => '<path d="M3 11l9-7 9 7v9a1 1 0 01-1 1h-5v-6H9v6H4a1 1 0 01-1-1z"/>',
		'logout' => '<path d="M15 4h4a1 1 0 011 1v14a1 1 0 01-1 1h-4M10 17l5-5-5-5M15 12H3"/>',
		'search' => '<circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/>',
		'phone'  => '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 005 5L15 13l5 2v4a2 2 0 01-2 2A16 16 0 013 6a2 2 0 012-2"/>',
		'mail'   => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/>',
		'pin'    => '<path d="M12 21s-7-6.5-7-12a7 7 0 0114 0c0 5.5-7 12-7 12z"/><circle cx="12" cy="9" r="2.5"/>',
		'check'  => '<path d="M5 12.5l4.5 4.5L19 7.5"/>',
		'arrow'  => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'star'   => '<path d="M12 3l2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1-4.4-4.3 6.1-.9z"/>',
	);
	return '<svg viewBox="0 0 24 24" width="' . (int) $size . '" height="' . (int) $size . '" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ( isset( $paths[ $name ] ) ? $paths[ $name ] : '' ) . '</svg>';
}

/** Menu chính mặc định (có menu con), dùng khi chưa tạo menu trong Giao diện > Menu. */
function vs_fallback_menu() {
	$items = array(
		array( 'Dịch vụ', '#', array(
			array( 'Kiểm tra đạo văn & AI', vs_page_url( 'kiem-tra-dao-van' ), 'Turnitin, DoIT, GPTZero và 5 phần mềm khác', 'target' ),
			array( 'Giảm tỷ lệ đạo văn + AI', vs_page_url( 'giam-ty-le-dao-van' ), 'Cam kết dưới 30% / 20%, kèm giảm AI', 'pen' ),
			array( 'Tra cứu đơn hàng', vs_page_url( 'tra-cuu-don-hang' ), 'Xem kết quả, tải báo cáo', 'search' ),
		) ),
		array( 'Khám phá', '#', array(
			array( 'Bảng giá', vs_page_url( 'bang-gia' ), 'Giá minh bạch, tính tự động', 'wallet' ),
			array( 'Kết quả thực tế', vs_page_url( 'ket-qua-thuc-te' ), 'Tỷ lệ trước và sau khi xử lý', 'chart' ),
			array( 'Cộng tác viên', vs_page_url( 'cong-tac-vien' ), 'Giới thiệu bạn bè, nhận ' . vs_opt( 'aff_pct' ) . '% hoa hồng', 'gift' ),
		) ),
		array( 'Blog', get_option( 'page_for_posts' ) ? get_permalink( get_option( 'page_for_posts' ) ) : home_url( '/blog/' ) ),
		array( 'Liên hệ', vs_page_url( 'lien-he' ) ),
	);
	$current = home_url( add_query_arg( array() ) );
	echo '<ul class="nav__list">';
	foreach ( $items as $it ) {
		if ( ! empty( $it[2] ) ) {
			$here = strtok( $current, '?' );
			$cur  = array_filter( $it[2], function ( $x ) use ( $here ) { return untrailingslashit( $x[1] ) === untrailingslashit( $here ); } );
			echo '<li class="has-sub' . ( $cur ? ' is-current' : '' ) . '"><button type="button" class="nav__parent" aria-expanded="false">' . esc_html( $it[0] ) . '<svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true"><path d="M6 9l6 6 6-6" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg></button><div class="sub"><ul>';
			foreach ( $it[2] as $s ) {
				echo '<li><a href="' . esc_url( $s[1] ) . '"' . ( untrailingslashit( $s[1] ) === untrailingslashit( $here ) ? ' class="is-active"' : '' ) . '><span class="sub__icon">' . vs_icon( $s[3], 20 ) . '</span><span><b>' . esc_html( $s[0] ) . '</b><small>' . esc_html( $s[2] ) . '</small></span></a></li>'; // phpcs:ignore
			}
			echo '</ul></div></li>';
		} else {
			$active = untrailingslashit( $it[1] ) === untrailingslashit( strtok( $current, '?' ) ) ? ' class="is-current"' : '';
			echo '<li' . $active . '><a href="' . esc_url( $it[1] ) . '">' . esc_html( $it[0] ) . '</a></li>'; // phpcs:ignore
		}
	}
	echo '</ul>';
}

/** Số đơn đã hoàn thành (chỉ hiển thị khi đủ lớn). */
function vs_completed_count() {
	$n = get_transient( 'vs_done_count' );
	if ( false === $n ) {
		$n = count( get_posts( array(
			'post_type'      => 'vs_order',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_key'       => '_vs_status',
			'meta_value'     => 'hoan_thanh',
		) ) );
		set_transient( 'vs_done_count', $n, HOUR_IN_SECONDS );
	}
	return (int) $n;
}

add_filter( 'excerpt_length', function () {
	return 26;
} );
add_filter( 'excerpt_more', function () {
	return '…';
} );

/* Giữ nguyên dấu "-" (WordPress mặc định tự đổi thành dấu gạch dài). */
add_filter( 'run_wptexturize', '__return_false' );
add_filter( 'document_title_separator', function () {
	return '-';
} );
