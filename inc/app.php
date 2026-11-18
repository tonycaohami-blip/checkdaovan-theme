<?php
/**
 * Ứng dụng quản trị riêng tại tên miền phụ app.<tên miền> (ví dụ app.checkdaovan247.com).
 *
 * - Chạy chung WordPress và cơ sở dữ liệu với website chính nên mọi dữ liệu luôn đồng bộ.
 * - Chỉ kích hoạt khi truy cập bằng đúng tên miền app.; website chính không thay đổi gì.
 * - Đăng nhập riêng (cookie chỉ thuộc tên miền app.), chỉ tài khoản quản lý đơn mới vào được.
 * - Dùng lại API của trang /quan-tri/ (đơn hàng, thao tác, thông báo) và thêm API cho
 *   tổng quan, khách hàng, cộng tác viên, nhập kết quả.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Tên miền của app (có thể đổi bằng tùy chọn vs_app_host, ví dụ khi thử trên máy). */
function vs_app_host() {
	$h = strtolower( trim( (string) get_option( 'vs_app_host', '' ) ) );
	if ( '' === $h ) {
		$h = 'app.' . preg_replace( '/^www\./', '', strtolower( (string) wp_parse_url( home_url(), PHP_URL_HOST ) ) );
	}
	return $h;
}

/** Yêu cầu hiện tại có đến từ tên miền app. không. */
function vs_is_app() {
	static $r = null;
	if ( null === $r ) {
		$host = strtolower( isset( $_SERVER['HTTP_HOST'] ) ? (string) $_SERVER['HTTP_HOST'] : '' );
		$r    = '' !== $host && vs_app_host() === $host;
	}
	return $r;
}

/** Đường dẫn gốc của app khi chạy trên tên miền chính (không cần cấu hình nginx/DNS). */
function vs_app_path() {
	return '/app/';
}

/** Yêu cầu hiện tại có phải trang app trên tên miền chính (/app/...) không. */
function vs_is_app_path() {
	static $r = null;
	if ( null === $r ) {
		$path = (string) wp_parse_url( isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '/', PHP_URL_PATH );
		$r    = ! vs_is_app() && (bool) preg_match( '#^/app(/|/index\.php)?$|^/app/manifest\.webmanifest$#', $path );
	}
	return $r;
}

/** Đường dẫn tương đối của theme (để tải CSS/JS/font từ chính tên miền app., không phụ thuộc CORS). */
function vs_app_asset( $file ) {
	return wp_parse_url( VS_URI, PHP_URL_PATH ) . '/' . ltrim( $file, '/' ) . '?v=' . VS_VERSION;
}

/* ---------- Điều hướng mọi yêu cầu trên tên miền app. ---------- */
add_action( 'init', function () {
	$path = (string) wp_parse_url( isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '/', PHP_URL_PATH );

	// Chế độ đường dẫn trên tên miền chính: chỉ xử lý đúng /app/ và manifest của nó, mọi trang khác giữ nguyên.
	if ( vs_is_app_path() && ! wp_doing_ajax() ) {
		if ( '/app' === $path ) {
			wp_redirect( vs_app_path() . ( empty( $_SERVER['QUERY_STRING'] ) ? '' : '?' . $_SERVER['QUERY_STRING'] ), 301 );
			exit;
		}
		if ( '/app/manifest.webmanifest' === $path ) {
			vs_app_manifest( vs_app_path() );
		}
		vs_app_render();
		exit;
	}
	if ( ! vs_is_app() ) {
		return;
	}

	// API, AJAX, tải file, cron: để WordPress xử lý bình thường.
	if ( wp_doing_ajax() || wp_doing_cron() || isset( $_GET['rest_route'] ) || preg_match( '#^/(wp-json/|wp-admin/admin-(ajax|post)\.php|wp-cron\.php)#', $path ) ) {
		return;
	}
	if ( '/robots.txt' === $path ) {
		header( 'Content-Type: text/plain; charset=utf-8' );
		echo "User-agent: *\nDisallow: /\n";
		exit;
	}
	if ( '/manifest.webmanifest' === $path ) {
		vs_app_manifest( '/' );
	}
	// Trang quản trị WordPress, đăng nhập WordPress: chuyển về website chính.
	if ( 0 === strpos( $path, '/wp-admin' ) ) {
		wp_redirect( home_url( isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '/' ) );
		exit;
	}
	if ( 0 === strpos( $path, '/wp-login.php' ) ) {
		wp_redirect( '/' );
		exit;
	}
	vs_app_render();
	exit;
}, 99 );

/* Lối tắt mở app quản trị trên thanh quản trị WordPress. */
add_action( 'admin_bar_menu', function ( $bar ) {
	if ( ! function_exists( 'vs_can_manage_orders' ) || ! vs_can_manage_orders() ) {
		return;
	}
	$bar->add_node( array(
		'id'    => 'vs-app',
		'title' => '<span class="ab-icon dashicons dashicons-chart-area" style="top:2px"></span>App quản trị',
		'href'  => home_url( vs_app_path() ),
		'meta'  => array( 'target' => '_blank', 'title' => 'Mở app quản trị đơn hàng' ),
	) );
}, 80 );

/* Trên tên miền app.: không chuyển hướng "canonical" về tên miền chính. */
add_filter( 'redirect_canonical', function ( $to ) {
	return vs_is_app() ? false : $to;
} );

/** Tệp manifest để "Thêm vào màn hình chính" như một ứng dụng. */
function vs_app_manifest( $start ) {
	header( 'Content-Type: application/manifest+json; charset=utf-8' );
	echo wp_json_encode( array(
		'name'             => 'Quản trị ' . get_bloginfo( 'name' ),
		'short_name'       => 'Quản trị',
		'start_url'        => $start,
		'scope'            => $start,
		'display'          => 'standalone',
		'background_color' => '#0b1630',
		'theme_color'      => '#0b1630',
		'icons'            => array( array( 'src' => vs_app_asset( 'assets/img/favicon.svg' ), 'sizes' => 'any', 'type' => 'image/svg+xml' ) ),
	) );
	exit;
}

/** Trang khung của app (giao diện dựng bằng assets/app/app.js). */
function vs_app_render() {
	nocache_headers();
	header( 'Content-Type: text/html; charset=utf-8' );
	header( 'X-Robots-Tag: noindex, nofollow' );
	header( 'X-Frame-Options: DENY' );
	header( 'X-Content-Type-Options: nosniff' );
	header( 'Referrer-Policy: same-origin' );

	$user = wp_get_current_user();
	$can  = $user->ID && vs_can_manage_orders();
	$cfg  = array(
		'ajax'     => '/wp-admin/admin-ajax.php',
		'logged'   => (bool) $user->ID,
		'can'      => $can,
		'site'     => home_url( '/' ),
		'name'     => get_bloginfo( 'name' ),
		'logo'     => vs_app_asset( 'assets/img/logo.svg' ),
		'version'  => VS_VERSION,
	);
	if ( $can ) {
		$cfg = array_merge( $cfg, array(
			'nonce'    => wp_create_nonce( 'vs_adm' ),
			'anonce'   => wp_create_nonce( 'vs_avatar' ),
			'statuses' => vs_statuses(),
			'user'     => array(
				'name'   => $user->display_name,
				'email'  => $user->user_email,
				'avatar'  => vs_avatar_url( $user->ID ),
				'initial' => vs_avatar_initial( $user->ID ),
				'role'   => current_user_can( 'manage_options' ) ? 'Quản trị viên' : 'Biên tập viên',
			),
			'wp'       => array(
				'orders'   => admin_url( 'edit.php?post_type=vs_order' ),
				'payouts'  => admin_url( 'edit.php?post_type=vs_payout' ),
				'settings' => admin_url( 'admin.php?page=vs-settings' ),
				'users'    => admin_url( 'users.php' ),
				'dash'     => admin_url(),
			),
			'export'   => add_query_arg( array( 'action' => 'vs_export', '_wpnonce' => wp_create_nonce( 'vs_export' ) ), '/wp-admin/admin-post.php' ),
			'aff'      => array( 'pct' => (float) vs_opt( 'aff_pct' ), 'min' => vs_money( vs_opt( 'payout_min' ) ) ),
		) );
	} elseif ( $user->ID ) {
		$cfg['user']   = array( 'name' => $user->display_name );
		$cfg['lnonce'] = wp_create_nonce( 'vs_app_logout' );
	}
	?><!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="robots" content="noindex, nofollow">
<meta name="theme-color" content="#0b1630">
<title>Quản trị đơn hàng | <?php echo esc_html( get_bloginfo( 'name' ) ); ?></title>
<link rel="icon" href="<?php echo esc_url( vs_app_asset( 'assets/img/favicon.svg' ) ); ?>" type="image/svg+xml">
<link rel="manifest" href="<?php echo esc_url( vs_is_app_path() ? vs_app_path() . 'manifest.webmanifest' : '/manifest.webmanifest' ); ?>">
<link rel="preload" href="<?php echo esc_url( wp_parse_url( VS_URI, PHP_URL_PATH ) . '/assets/app/fonts/inter-latin.woff2' ); ?>" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="<?php echo esc_url( wp_parse_url( VS_URI, PHP_URL_PATH ) . '/assets/app/fonts/inter-vietnamese.woff2' ); ?>" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="<?php echo esc_url( vs_app_asset( 'assets/app/app.css' ) ); ?>">
<script>try{var t=localStorage.getItem('vsapp_theme');if(t==='dark'||(t!=='light'&&matchMedia('(prefers-color-scheme: dark)').matches))document.documentElement.dataset.theme='dark';}catch(e){}</script>
</head>
<body>
<div id="app" aria-live="polite"><div class="boot"><span class="boot__spin"></span></div></div>
<script>window.VSAPP=<?php echo wp_json_encode( $cfg ); ?>;</script>
<script src="<?php echo esc_url( vs_app_asset( 'assets/app/app.js' ) ); ?>"></script>
</body>
</html>
	<?php
}

/* ---------- Đăng nhập / đăng xuất riêng cho app. ---------- */
function vs_app_json_only() {
	nocache_headers();
}

add_action( 'wp_ajax_nopriv_vs_app_login', 'vs_app_login' );
add_action( 'wp_ajax_vs_app_login', 'vs_app_login' );
function vs_app_login() {
	vs_app_json_only();
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : 'x';
	$key = 'vs_rl_applogin_' . md5( $ip );
	$n   = (int) get_transient( $key );
	if ( $n >= 8 ) {
		wp_send_json_error( array( 'message' => 'Bạn đã thử quá nhiều lần. Vui lòng đợi 15 phút rồi thử lại.' ), 429 );
	}
	$id   = trim( (string) wp_unslash( isset( $_POST['login'] ) ? $_POST['login'] : '' ) );
	$pass = (string) wp_unslash( isset( $_POST['password'] ) ? $_POST['password'] : '' );
	if ( '' === $id || '' === $pass ) {
		wp_send_json_error( array( 'message' => 'Vui lòng nhập tài khoản và mật khẩu.' ) );
	}
	if ( ! is_email( $id ) && preg_match( '/^[\d\s.+]+$/', $id ) && function_exists( 'vs_find_user_by_phone' ) ) {
		$u  = vs_find_user_by_phone( $id );
		$id = $u ? $u->user_login : $id;
	}
	$user = wp_authenticate( $id, $pass );
	if ( is_wp_error( $user ) ) {
		set_transient( $key, $n + 1, 15 * MINUTE_IN_SECONDS );
		wp_send_json_error( array( 'message' => 'Tài khoản hoặc mật khẩu không đúng.' ) );
	}
	if ( ! user_can( $user, 'manage_options' ) && ! user_can( $user, 'edit_others_posts' ) ) {
		set_transient( $key, $n + 1, 15 * MINUTE_IN_SECONDS );
		wp_send_json_error( array( 'message' => 'Tài khoản này không có quyền quản lý đơn hàng.' ), 403 );
	}
	wp_set_current_user( $user->ID );
	wp_set_auth_cookie( $user->ID, ! empty( $_POST['remember'] ), is_ssl() );
	do_action( 'wp_login', $user->user_login, $user );
	delete_transient( $key );
	wp_send_json_success( array( 'reload' => true ) );
}

add_action( 'wp_ajax_vs_app_logout', function () {
	vs_app_json_only();
	$ok = ( isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'vs_adm' ) ) || ( isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'vs_app_logout' ) );
	if ( ! $ok ) {
		wp_send_json_error( array( 'message' => 'Phiên đã hết hạn, vui lòng tải lại trang.' ), 403 );
	}
	wp_logout();
	wp_send_json_success( array( 'reload' => true ) );
} );

/* ---------- Tổng quan ---------- */
add_action( 'wp_ajax_vs_app_dash', function () {
	vs_adm_guard();
	$days = 30;
	$ids  = get_posts( array( 'post_type' => 'vs_order', 'post_status' => 'publish', 'posts_per_page' => 5000, 'fields' => 'ids', 'no_found_rows' => true, 'orderby' => 'date', 'order' => 'DESC' ) );
	$p    = vs_pricing();

	$series = array();
	for ( $i = $days - 1; $i >= 0; $i-- ) {
		$d            = wp_date( 'Y-m-d', time() - $i * DAY_IN_SECONDS );
		$series[ $d ] = array( 'd' => wp_date( 'd/m', time() - $i * DAY_IN_SECONDS ), 'rev' => 0, 'orders' => 0 );
	}
	$month      = wp_date( 'Y-m' );
	$prev_month = wp_date( 'Y-m', strtotime( wp_date( 'Y-m-01' ) . ' -1 day' ) );
	$k          = array( 'rev_month' => 0, 'rev_prev' => 0, 'rev_all' => 0, 'paid_orders' => 0, 'orders_month' => 0, 'orders_prev' => 0, 'done_secs' => 0, 'done_n' => 0 );
	$by_status  = array_fill_keys( array_keys( vs_statuses() ), 0 );
	$by_type    = array( 'check' => 0, 'reduce' => 0 );
	$tools      = array();
	$phones     = array();
	$today      = array();

	foreach ( $ids as $id ) {
		$st      = vs_meta( $id, 'status', 'cho_thanh_toan' );
		$created = get_post_time( 'U', true, $id );
		$cday    = wp_date( 'Y-m-d', $created );
		$by_status[ $st ] = isset( $by_status[ $st ] ) ? $by_status[ $st ] + 1 : 1;
		$type    = vs_meta( $id, 'type' );
		if ( isset( $by_type[ $type ] ) ) {
			$by_type[ $type ]++;
		}
		if ( isset( $series[ $cday ] ) ) {
			$series[ $cday ]['orders']++;
		}
		$cm = wp_date( 'Y-m', $created );
		if ( $cm === $month ) {
			$k['orders_month']++;
		} elseif ( $cm === $prev_month ) {
			$k['orders_prev']++;
		}
		$ph = preg_replace( '/\D/', '', (string) vs_meta( $id, 'phone' ) );
		if ( $ph ) {
			$phones[ $ph ] = 1;
		}
		$paid  = (int) get_post_meta( $id, '_vs_paid_at', true );
		$price = (int) vs_meta( $id, 'price' );
		if ( $paid && in_array( $st, array( 'dang_xu_ly', 'hoan_thanh' ), true ) ) {
			$k['rev_all'] += $price;
			$k['paid_orders']++;
			$pd = wp_date( 'Y-m-d', $paid );
			$pm = wp_date( 'Y-m', $paid );
			if ( isset( $series[ $pd ] ) ) {
				$series[ $pd ]['rev'] += $price;
			}
			if ( $pm === $month ) {
				$k['rev_month'] += $price;
			} elseif ( $pm === $prev_month ) {
				$k['rev_prev'] += $price;
			}
			$done = (int) get_post_meta( $id, '_vs_done_at', true );
			if ( 'hoan_thanh' === $st && $done > $paid && $done - $paid < 14 * DAY_IN_SECONDS ) {
				$k['done_secs'] += $done - $paid;
				$k['done_n']++;
			}
			if ( 'check' === $type ) {
				$data = (array) vs_meta( $id, 'data', array() );
				foreach ( isset( $data['tools'] ) ? (array) $data['tools'] : array() as $t ) {
					$name          = isset( $p['tools'][ $t ]['name'] ) ? $p['tools'][ $t ]['name'] : $t;
					$tools[ $name ] = isset( $tools[ $name ] ) ? $tools[ $name ] + 1 : 1;
				}
			}
		}
	}
	arsort( $tools );
	$tool_rows = array();
	foreach ( array_slice( $tools, 0, 7, true ) as $n => $c ) {
		$tool_rows[] = array( 'name' => $n, 'n' => $c );
	}

	$pending_payouts = get_posts( array( 'post_type' => 'vs_payout', 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_key' => '_vs_status', 'meta_value' => 'pending' ) );
	$feed            = array();
	foreach ( array_slice( vs_admin_notices(), 0, 8 ) as $n ) {
		$o           = vs_notice_out( $n );
		$o['code']   = $o['order'] ? vs_meta( $o['order'], 'code' ) : '';
		$feed[]      = $o;
	}

	wp_send_json_success( array(
		'stats'    => vs_adm_stats(),
		'kpi'      => array(
			'rev_month'    => $k['rev_month'],
			'rev_prev'     => $k['rev_prev'],
			'rev_all'      => $k['rev_all'],
			'orders_month' => $k['orders_month'],
			'orders_prev'  => $k['orders_prev'],
			'aov'          => $k['paid_orders'] ? (int) round( $k['rev_all'] / $k['paid_orders'] ) : 0,
			'customers'    => count( $phones ),
			'avg_done'     => $k['done_n'] ? (int) round( $k['done_secs'] / $k['done_n'] ) : 0,
			'payouts'      => count( $pending_payouts ),
		),
		'series'   => array_values( $series ),
		'status'   => $by_status,
		'type'     => $by_type,
		'tools'    => $tool_rows,
		'recent'   => array_map( 'vs_adm_item', array_slice( $ids, 0, 8 ) ),
		'feed'     => $feed,
	) );
} );

/* ---------- Khách hàng (gộp theo số điện thoại từ đơn hàng) ---------- */
add_action( 'wp_ajax_vs_app_customers', function () {
	vs_adm_guard();
	$q    = isset( $_POST['q'] ) ? mb_strtolower( trim( sanitize_text_field( wp_unslash( $_POST['q'] ) ) ) ) : '';
	$sort = isset( $_POST['sort'] ) ? sanitize_key( $_POST['sort'] ) : 'last';
	$page = max( 1, isset( $_POST['page'] ) ? (int) $_POST['page'] : 1 );
	$ids  = get_posts( array( 'post_type' => 'vs_order', 'post_status' => 'publish', 'posts_per_page' => 5000, 'fields' => 'ids', 'no_found_rows' => true, 'orderby' => 'date', 'order' => 'DESC' ) );
	$c    = array();
	foreach ( $ids as $id ) {
		$phone = preg_replace( '/\D/', '', (string) vs_meta( $id, 'phone' ) );
		$email = strtolower( (string) vs_meta( $id, 'email' ) );
		$key   = $phone ? $phone : ( $email ? $email : 'o' . $id );
		$st    = vs_meta( $id, 'status', 'cho_thanh_toan' );
		$t     = get_post_time( 'U', true, $id );
		if ( ! isset( $c[ $key ] ) ) {
			$c[ $key ] = array(
				'key'    => $key,
				'name'   => (string) vs_meta( $id, 'name' ),
				'phone'  => (string) vs_meta( $id, 'phone' ),
				'email'  => (string) vs_meta( $id, 'email' ),
				'orders' => 0,
				'paid'   => 0,
				'spent'  => 0,
				'last'   => $t,
				'first'  => $t,
				'user'   => (int) vs_meta( $id, 'user' ),
			);
		}
		$r = &$c[ $key ];
		$r['orders']++;
		$r['first'] = min( $r['first'], $t );
		if ( ! $r['email'] && $email ) {
			$r['email'] = (string) vs_meta( $id, 'email' );
		}
		if ( ! $r['user'] ) {
			$r['user'] = (int) vs_meta( $id, 'user' );
		}
		if ( get_post_meta( $id, '_vs_paid_at', true ) && in_array( $st, array( 'dang_xu_ly', 'hoan_thanh' ), true ) ) {
			$r['paid']++;
			$r['spent'] += (int) vs_meta( $id, 'price' );
		}
		unset( $r );
	}
	if ( '' !== $q ) {
		$qd = preg_replace( '/\D/', '', $q );
		$c  = array_filter( $c, function ( $r ) use ( $q, $qd ) {
			return false !== mb_strpos( mb_strtolower( $r['name'] . ' ' . $r['email'] ), $q ) || ( $qd && false !== strpos( preg_replace( '/\D/', '', $r['phone'] ), $qd ) );
		} );
	}
	$sorts = array( 'last' => 'last', 'spent' => 'spent', 'orders' => 'orders' );
	$f     = isset( $sorts[ $sort ] ) ? $sorts[ $sort ] : 'last';
	uasort( $c, function ( $a, $b ) use ( $f ) {
		return $b[ $f ] <=> $a[ $f ];
	} );
	$total = count( $c );
	$per   = 25;
	$rows  = array();
	foreach ( array_slice( array_values( $c ), ( $page - 1 ) * $per, $per ) as $r ) {
		$r['spent_f'] = vs_money( $r['spent'] );
		$r['last_f']  = vs_time_ago( $r['last'] );
		$r['first_f'] = wp_date( 'd/m/Y', $r['first'] );
		$r['account'] = $r['user'] ? true : false;
		$r['avatar']  = $r['user'] ? vs_avatar_url( $r['user'] ) : '';
		unset( $r['user'] );
		$rows[] = $r;
	}
	$sum = 0;
	foreach ( $c as $r ) {
		$sum += $r['spent'];
	}
	wp_send_json_success( array(
		'items' => $rows,
		'total' => $total,
		'pages' => max( 1, (int) ceil( $total / $per ) ),
		'page'  => $page,
		'sum'   => vs_money( $sum ),
		'repeat'=> count( array_filter( $c, function ( $r ) {
			return $r['paid'] > 1;
		} ) ),
	) );
} );

/* ---------- Cộng tác viên & rút tiền ---------- */
function vs_app_payout_row( $pid ) {
	$labels = vs_payout_labels();
	$u      = get_userdata( (int) vs_meta( $pid, 'user' ) );
	$st     = vs_meta( $pid, 'status', 'pending' );
	$bank   = (array) vs_meta( $pid, 'bank', array() );
	return array(
		'id'      => $pid,
		'name'    => $u ? $u->display_name : '—',
		'avatar'  => $u ? vs_avatar_url( $u->ID ) : '',
		'phone'   => $u ? vs_user_phone( $u->ID ) : '',
		'amount'  => vs_money( vs_meta( $pid, 'amount' ) ),
		'status'  => $st,
		'label'   => isset( $labels[ $st ] ) ? $labels[ $st ] : $st,
		'bank'    => array(
			'name'    => isset( $bank['bank_name'] ) ? $bank['bank_name'] : '',
			'account' => isset( $bank['bank_account'] ) ? $bank['bank_account'] : '',
			'holder'  => isset( $bank['bank_holder'] ) ? $bank['bank_holder'] : '',
		),
		'date'    => vs_post_date( 'H:i d/m/Y', $pid ),
		'ago'     => vs_time_ago( get_post_time( 'U', true, $pid ) ),
	);
}

add_action( 'wp_ajax_vs_app_affiliates', function () {
	vs_adm_guard();
	$rows = array();
	$tot  = array( 'clicks' => 0, 'orders' => 0, 'approved' => 0, 'paid' => 0, 'pending' => 0 );
	foreach ( get_users( array( 'meta_key' => '_vs_aff_code', 'number' => 1000 ) ) as $u ) {
		$s = vs_aff_stats( $u->ID );
		if ( ! $s['clicks'] && ! $s['orders'] && ! $s['customers'] ) {
			continue;
		}
		foreach ( $tot as $k => $v ) {
			$tot[ $k ] += (int) $s[ $k ];
		}
		$rows[] = array(
			'name'      => $u->display_name,
			'avatar'    => vs_avatar_url( $u->ID ),
			'phone'     => vs_user_phone( $u->ID ),
			'email'     => $u->user_email,
			'code'      => vs_aff_code( $u->ID ),
			'clicks'    => (int) $s['clicks'],
			'orders'    => (int) $s['orders'],
			'customers' => (int) $s['customers'],
			'pending'   => vs_money( $s['pending'] ),
			'approved'  => vs_money( $s['approved'] ),
			'paid'      => vs_money( $s['paid'] ),
			'available' => vs_money( $s['available'] ),
			'sort'      => (int) $s['approved'] + (int) $s['pending'],
		);
	}
	usort( $rows, function ( $a, $b ) {
		return $b['sort'] <=> $a['sort'];
	} );
	$payouts = array_map( 'vs_app_payout_row', get_posts( array( 'post_type' => 'vs_payout', 'post_status' => 'publish', 'posts_per_page' => 200, 'fields' => 'ids' ) ) );
	$members = count( get_users( array( 'meta_key' => '_vs_aff_code', 'fields' => 'ID', 'number' => 5000 ) ) );
	wp_send_json_success( array(
		'items'   => $rows,
		'payouts' => $payouts,
		'members' => $members,
		'total'   => array(
			'clicks'   => $tot['clicks'],
			'orders'   => $tot['orders'],
			'approved' => vs_money( $tot['approved'] ),
			'paid'     => vs_money( $tot['paid'] ),
			'pending'  => vs_money( $tot['pending'] ),
		),
	) );
} );

add_action( 'wp_ajax_vs_app_payout', function () {
	vs_adm_guard();
	$pid = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;
	$st  = isset( $_POST['status'] ) ? sanitize_key( $_POST['status'] ) : '';
	if ( 'vs_payout' !== get_post_type( $pid ) || ! isset( vs_payout_labels()[ $st ] ) || ! current_user_can( 'edit_post', $pid ) ) {
		wp_send_json_error( array( 'message' => 'Yêu cầu không hợp lệ.' ) );
	}
	$old = vs_meta( $pid, 'status', 'pending' );
	update_post_meta( $pid, '_vs_status', $st );
	$uid = (int) vs_meta( $pid, 'user' );
	if ( $st !== $old && $uid && function_exists( 'vs_notify_user' ) ) {
		if ( 'paid' === $st ) {
			update_post_meta( $pid, '_vs_paid_at', time() );
			vs_notify_user( $uid, 'payout', 'Đã chuyển hoa hồng ' . vs_money( vs_meta( $pid, 'amount' ) ), 'Vui lòng kiểm tra tài khoản ngân hàng của bạn.', vs_account_url( 'cong-tac-vien' ), 'payout_paid_' . $pid );
		} elseif ( 'rejected' === $st ) {
			vs_notify_user( $uid, 'payout', 'Yêu cầu rút tiền chưa được duyệt', 'Vui lòng liên hệ hỗ trợ để biết thêm chi tiết.', vs_account_url( 'cong-tac-vien' ), 'payout_rej_' . $pid );
		}
	}
	$labels = vs_payout_labels();
	wp_send_json_success( array( 'message' => 'Đã cập nhật: ' . $labels[ $st ] . '.', 'row' => vs_app_payout_row( $pid ) ) );
} );

/* ---------- Nhập kết quả (tỷ lệ đạo văn / AI) ---------- */
add_action( 'wp_ajax_vs_app_results', function () {
	vs_adm_guard();
	$id = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;
	if ( 'vs_order' !== get_post_type( $id ) || ! current_user_can( 'edit_post', $id ) ) {
		wp_send_json_error( array( 'message' => 'Không có quyền với đơn này.' ), 403 );
	}
	$in      = json_decode( wp_unslash( isset( $_POST['values'] ) ? $_POST['values'] : '' ), true );
	$in_src  = json_decode( wp_unslash( isset( $_POST['srcs'] ) ? $_POST['srcs'] : '' ), true );
	$in_src  = is_array( $in_src ) ? $in_src : array();
	$allowed = array();
	foreach ( vs_result_rows( $id ) as $key => $row ) {
		$allowed[ $key ] = $row[1];
	}
	$results = (array) vs_meta( $id, 'results', array() );
	$srcs    = (array) get_post_meta( $id, '_vs_result_src', true );
	foreach ( is_array( $in ) ? $in : array() as $key => $vals ) {
		$key = sanitize_key( $key );
		if ( ! isset( $allowed[ $key ] ) || ! is_array( $vals ) ) {
			continue;
		}
		foreach ( $allowed[ $key ] as $f ) {
			if ( ! array_key_exists( $f, $vals ) ) {
				continue;
			}
			if ( null === $vals[ $f ] || '' === $vals[ $f ] ) {
				unset( $results[ $key ][ $f ], $srcs[ $key ][ $f ] );
				continue;
			}
			$val = max( 0, min( 100, round( (float) str_replace( ',', '.', (string) $vals[ $f ] ), 1 ) ) );
			$psrc = isset( $in_src[ $key ][ $f ] ) && 'pdf' === $in_src[ $key ][ $f ] ? 'pdf' : 'manual';
			$osrc = isset( $srcs[ $key ][ $f ]['src'] ) ? $srcs[ $key ][ $f ]['src'] : '';
			if ( ! isset( $results[ $key ][ $f ] ) || (float) $results[ $key ][ $f ] !== $val || ( 'pdf' === $psrc && 'pdf' !== $osrc ) ) {
				$results[ $key ][ $f ] = $val;
				$srcs[ $key ][ $f ]    = array( 'src' => $psrc, 'at' => time(), 'note' => '' );
			}
		}
		if ( empty( $results[ $key ] ) ) {
			unset( $results[ $key ] );
		}
	}
	update_post_meta( $id, '_vs_results', $results );
	update_post_meta( $id, '_vs_result_src', $srcs );
	$msg = 'Đã lưu kết quả.';
	$st  = vs_meta( $id, 'status', 'cho_thanh_toan' );
	if ( ! empty( $_POST['complete'] ) && 'dang_xu_ly' === $st && vs_results_complete( $id ) ) {
		update_post_meta( $id, '_vs_status', 'hoan_thanh' );
		update_post_meta( $id, '_vs_done_at', time() );
		delete_post_meta( $id, '_vs_auto_pending' );
		do_action( 'vs_order_status', $id, 'hoan_thanh', $st );
		vs_auto_mail_done( $id );
		$msg = 'Đã lưu kết quả, chuyển đơn sang Hoàn thành và gửi email cho khách.';
	}
	$u = wp_get_current_user();
	vs_auto_log( 'Quản trị (' . $u->display_name . '): cập nhật kết quả trên app.', $id );
	wp_send_json_success( array( 'message' => $msg, 'order' => vs_adm_order_data( $id ) ) );
} );

/* =========================================================
 * Cập nhật đơn đầy đủ trên app (đồng bộ với hộp "Kết quả trả khách" / "Trạng thái" trong WP Admin)
 * ======================================================= */

/** File trả khách đã đính kèm (Thư viện Media) - danh sách cho app, kèm id để gỡ. */
function vs_app_order_atts( $id ) {
	$out = array();
	foreach ( array_filter( array_map( 'intval', explode( ',', (string) vs_meta( $id, 'report_ids' ) ) ) ) as $fid ) {
		$path = (string) get_attached_file( $fid );
		if ( ! $path || ! is_file( $path ) ) {
			continue;
		}
		$n     = basename( $path );
		$st    = vs_file_stats( $id, 'att:' . $fid, $n, $path, 'PDF' );
		$out[] = array_merge( $st, array(
			'id'    => $fid,
			'name'  => $n,
			'short' => vs_short_filename( $n, 34 ),
			'meta'  => vs_file_meta_text( $st ),
			'url'   => wp_get_attachment_url( $fid ),
		) );
	}
	return $out;
}
add_filter( 'vs_adm_order_data', function ( $d, $id ) {
	$d['atts']      = vs_app_order_atts( $id );
	$d['autofiles'] = array();
	foreach ( function_exists( 'vs_auto_files' ) ? vs_auto_files( $id ) : array() as $i => $f ) {
		$st               = vs_file_stats( $id, 'auto:' . basename( $f['path'] ), $f['name'], vs_report_dir() . '/' . basename( $f['path'] ), 'PDF' );
		$d['autofiles'][] = array_merge( $st, array( 'name' => $f['name'], 'short' => vs_short_filename( $f['name'], 34 ), 'meta' => vs_file_meta_text( $st ) . ' | tự động', 'url' => vs_file_url( $id, $i ) ) );
	}
	return $d;
}, 10, 2 );

function vs_app_order_guard() {
	vs_adm_guard();
	$id = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;
	if ( 'vs_order' !== get_post_type( $id ) || ! current_user_can( 'edit_post', $id ) ) {
		wp_send_json_error( array( 'message' => 'Không có quyền với đơn này.' ), 403 );
	}
	return $id;
}

/* Tải file trả khách (báo cáo PDF, file Word đã chỉnh sửa...) lên đơn: lưu vào Thư viện Media như WP Admin. */
add_action( 'wp_ajax_vs_app_upload', function () {
	$id = vs_app_order_guard();
	if ( empty( $_FILES['file']['name'] ) ) {
		wp_send_json_error( array( 'message' => 'Chưa chọn file.' ) );
	}
	$ext = strtolower( pathinfo( (string) $_FILES['file']['name'], PATHINFO_EXTENSION ) );
	if ( ! in_array( $ext, array( 'pdf', 'docx', 'doc', 'xlsx', 'zip', 'png', 'jpg', 'jpeg' ), true ) ) {
		wp_send_json_error( array( 'message' => 'Chỉ nhận file PDF, Word, Excel, ZIP hoặc ảnh.' ) );
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$fid = media_handle_upload( 'file', 0 );
	if ( is_wp_error( $fid ) ) {
		wp_send_json_error( array( 'message' => 'Không tải được file: ' . $fid->get_error_message() ) );
	}
	$ids   = array_filter( array_map( 'intval', explode( ',', (string) vs_meta( $id, 'report_ids' ) ) ) );
	$ids[] = (int) $fid;
	update_post_meta( $id, '_vs_report_ids', implode( ',', array_unique( $ids ) ) );
	$u = wp_get_current_user();
	vs_auto_log( 'Quản trị (' . $u->display_name . '): thêm file trả khách ' . basename( (string) get_attached_file( $fid ) ) . ' trên app.', $id );
	wp_send_json_success( array( 'message' => 'Đã thêm file.', 'atts' => vs_app_order_atts( $id ) ) );
} );

/* Gỡ file trả khách khỏi đơn (file vẫn còn trong Thư viện Media, giống nút "Gỡ" trong WP Admin). */
add_action( 'wp_ajax_vs_app_unattach', function () {
	$id  = vs_app_order_guard();
	$fid = isset( $_POST['fid'] ) ? (int) $_POST['fid'] : 0;
	$ids = array_values( array_diff( array_filter( array_map( 'intval', explode( ',', (string) vs_meta( $id, 'report_ids' ) ) ) ), array( $fid ) ) );
	update_post_meta( $id, '_vs_report_ids', implode( ',', $ids ) );
	wp_send_json_success( array( 'message' => 'Đã gỡ file khỏi đơn.', 'atts' => vs_app_order_atts( $id ) ) );
} );

/* Đổi trạng thái bất kỳ (như ô "Trạng thái" trong WP Admin). */
add_action( 'wp_ajax_vs_app_status', function () {
	$id  = vs_app_order_guard();
	$new = isset( $_POST['status'] ) ? sanitize_key( $_POST['status'] ) : '';
	$old = vs_meta( $id, 'status', 'cho_thanh_toan' );
	$all = vs_statuses();
	if ( ! isset( $all[ $new ] ) ) {
		wp_send_json_error( array( 'message' => 'Trạng thái không hợp lệ.' ) );
	}
	if ( $new === $old ) {
		wp_send_json_error( array( 'message' => 'Đơn đang ở trạng thái ' . $all[ $new ] . '.' ) );
	}
	$mail = ! empty( $_POST['mail'] );
	if ( 'dang_xu_ly' === $new && 'cho_thanh_toan' === $old ) {
		vs_mark_paid( $id, 'Quản trị xác nhận', '', (int) vs_meta( $id, 'price' ) ); // Ghi nhận thanh toán + chạy phần tự động.
	} else {
		update_post_meta( $id, '_vs_status', $new );
		if ( 'hoan_thanh' === $new ) {
			update_post_meta( $id, '_vs_done_at', time() );
			delete_post_meta( $id, '_vs_auto_pending' );
		}
		do_action( 'vs_order_status', $id, $new, $old );
		if ( 'hoan_thanh' === $new && $mail ) {
			vs_auto_mail_done( $id );
		}
	}
	$msg = 'Đã chuyển đơn sang ' . $all[ $new ] . ( 'hoan_thanh' === $new && $mail ? ' và gửi email kết quả cho khách.' : '.' );
	$u   = wp_get_current_user();
	vs_auto_log( 'Quản trị (' . $u->display_name . '): ' . $all[ $old ] . ' → ' . $all[ $new ] . ' (trên app).', $id );
	wp_send_json_success( array( 'message' => $msg, 'order' => vs_adm_order_data( $id ) ) );
} );
