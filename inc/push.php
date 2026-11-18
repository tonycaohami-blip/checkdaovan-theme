<?php
/**
 * Thông báo đẩy (Web Push) trên điện thoại và máy tính.
 *
 * - Hiện thông báo của hệ điều hành kể cả khi đã đóng website / app quản trị.
 * - Quản trị viên (app quản trị): đơn mới, thanh toán, việc cần xử lý... (cùng nội dung chuông /quan-tri/).
 * - Khách hàng (website): đã nhận thanh toán, đã có kết quả, đơn hoàn thành, hoa hồng CTV...
 * - Chuẩn Web Push (RFC 8030/8291/8292, VAPID), mã hóa bằng OpenSSL của PHP, không cần thư viện ngoài.
 *   Khóa VAPID tự tạo 1 lần và lưu trong cơ sở dữ liệu (option vs_push_vapid), không nằm trong mã nguồn.
 * - Đăng ký của từng thiết bị lưu trong user meta _vs_push.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function vs_b64u( $s ) {
	return rtrim( strtr( base64_encode( $s ), '+/', '-_' ), '=' );
}

function vs_b64u_dec( $s ) {
	$s = strtr( (string) $s, '-_', '+/' );
	return (string) base64_decode( $s . str_repeat( '=', ( 4 - strlen( $s ) % 4 ) % 4 ), true );
}

/** Máy chủ có đủ OpenSSL để mã hóa thông báo đẩy không. */
function vs_push_supported() {
	return function_exists( 'openssl_pkey_new' ) && function_exists( 'openssl_pkey_derive' ) && function_exists( 'hash_hkdf' )
		&& in_array( 'aes-128-gcm', array_map( 'strtolower', openssl_get_cipher_methods() ), true );
}

/** Tham số OpenSSL (máy chủ thiếu openssl.cnf thì dùng 1 tệp cấu hình tối thiểu trong thư mục tạm). */
function vs_push_ssl_args( $extra = array() ) {
	static $cnf = null;
	if ( null === $cnf ) {
		$cnf = '';
		$sys = getenv( 'OPENSSL_CONF' ) ? getenv( 'OPENSSL_CONF' ) : ( function_exists( 'openssl_get_cert_locations' ) ? openssl_get_cert_locations()['default_default_cert_area'] . '/openssl.cnf' : '' );
		if ( ! $sys || ! @is_readable( $sys ) ) {
			$cnf = trailingslashit( get_temp_dir() ) . 'vs-openssl.cnf';
			if ( ! is_file( $cnf ) ) {
				@file_put_contents( $cnf, "[req]
distinguished_name = dn
[dn]
" );
			}
		}
	}
	return array_merge( array( 'private_key_bits' => 2048 ), $cnf ? array( 'config' => $cnf ) : array(), $extra );
}

/** Tạo khóa EC P-256, trả về [khóa, khóa công khai dạng 65 byte]. */
function vs_push_ec_new() {
	$key = openssl_pkey_new( vs_push_ssl_args( array( 'curve_name' => 'prime256v1', 'private_key_type' => OPENSSL_KEYTYPE_EC ) ) );
	if ( ! $key ) {
		return null;
	}
	$d = openssl_pkey_get_details( $key );
	if ( empty( $d['ec']['x'] ) ) {
		return null;
	}
	return array( $key, "\x04" . str_pad( $d['ec']['x'], 32, "\0", STR_PAD_LEFT ) . str_pad( $d['ec']['y'], 32, "\0", STR_PAD_LEFT ) );
}

/** Khóa VAPID của website (tạo 1 lần). */
function vs_push_keys() {
	$k = get_option( 'vs_push_vapid' );
	if ( is_array( $k ) && ! empty( $k['pub'] ) && ! empty( $k['pem'] ) ) {
		return $k;
	}
	if ( ! vs_push_supported() ) {
		return null;
	}
	$ec = vs_push_ec_new();
	if ( ! $ec || ! openssl_pkey_export( $ec[0], $pem, null, vs_push_ssl_args() ) ) {
		return null;
	}
	$k = array( 'pub' => vs_b64u( $ec[1] ), 'pem' => $pem );
	update_option( 'vs_push_vapid', $k, false );
	return $k;
}

/** Khóa công khai 65 byte -> PEM để OpenSSL đọc được. */
function vs_push_pub_pem( $raw ) {
	$der = hex2bin( '3059301306072a8648ce3d020106082a8648ce3d030107034200' ) . $raw;
	return "-----BEGIN PUBLIC KEY-----\n" . chunk_split( base64_encode( $der ), 64, "\n" ) . "-----END PUBLIC KEY-----\n";
}

/** Chữ ký ECDSA dạng DER -> 64 byte (r||s) theo chuẩn JWT ES256. */
function vs_push_der2raw( $der ) {
	$o = 2;
	$out = '';
	for ( $i = 0; $i < 2; $i++ ) {
		if ( ! isset( $der[ $o + 1 ] ) || "\x02" !== $der[ $o ] ) {
			return '';
		}
		$len  = ord( $der[ $o + 1 ] );
		$int  = ltrim( substr( $der, $o + 2, $len ), "\0" );
		$out .= str_pad( $int, 32, "\0", STR_PAD_LEFT );
		$o   += 2 + $len;
	}
	return 64 === strlen( $out ) ? $out : '';
}

/** Mã thông báo VAPID cho 1 dịch vụ đẩy (lưu tạm trong lần chạy). */
function vs_push_vapid_header( $endpoint ) {
	static $cache = array();
	$u   = wp_parse_url( $endpoint );
	$aud = $u['scheme'] . '://' . $u['host'] . ( isset( $u['port'] ) ? ':' . $u['port'] : '' );
	if ( isset( $cache[ $aud ] ) ) {
		return $cache[ $aud ];
	}
	$k = vs_push_keys();
	if ( ! $k ) {
		return '';
	}
	$data = vs_b64u( wp_json_encode( array( 'typ' => 'JWT', 'alg' => 'ES256' ) ) ) . '.' . vs_b64u( wp_json_encode( array(
		'aud' => $aud,
		'exp' => time() + 12 * HOUR_IN_SECONDS,
		'sub' => home_url( '/' ),
	) ) );
	$priv = openssl_pkey_get_private( $k['pem'] );
	if ( ! $priv || ! openssl_sign( $data, $sig, $priv, OPENSSL_ALGO_SHA256 ) ) {
		return '';
	}
	$sig = vs_push_der2raw( $sig );
	return $cache[ $aud ] = $sig ? 'vapid t=' . $data . '.' . vs_b64u( $sig ) . ', k=' . $k['pub'] : '';
}

/**
 * Mã hóa nội dung theo RFC 8291 (aes128gcm).
 *
 * @param string $plain  Nội dung.
 * @param string $ua_pub Khóa công khai của trình duyệt (65 byte).
 * @param string $auth   Mã xác thực của trình duyệt (16 byte).
 * @return string|null Thân yêu cầu đã mã hóa.
 */
function vs_push_encrypt( $plain, $ua_pub, $auth ) {
	$ec = vs_push_ec_new();
	if ( ! $ec ) {
		return null;
	}
	$peer = openssl_pkey_get_public( vs_push_pub_pem( $ua_pub ) );
	if ( ! $peer ) {
		return null;
	}
	$shared = openssl_pkey_derive( $peer, $ec[0], 32 );
	if ( ! $shared ) {
		return null;
	}
	$salt  = random_bytes( 16 );
	$ikm   = hash_hkdf( 'sha256', $shared, 32, "WebPush: info\0" . $ua_pub . $ec[1], $auth );
	$cek   = hash_hkdf( 'sha256', $ikm, 16, "Content-Encoding: aes128gcm\0", $salt );
	$nonce = hash_hkdf( 'sha256', $ikm, 12, "Content-Encoding: nonce\0", $salt );
	$tag   = '';
	$ct    = openssl_encrypt( $plain . "\x02", 'aes-128-gcm', $cek, OPENSSL_RAW_DATA, $nonce, $tag, '', 16 );
	if ( false === $ct ) {
		return null;
	}
	return $salt . pack( 'N', 4096 ) . chr( 65 ) . $ec[1] . $ct . $tag;
}

/** Chỉ gửi tới dịch vụ đẩy thật của trình duyệt (Chrome/Android, Firefox, Edge/Windows, Safari/iPhone). */
function vs_push_endpoint_ok( $url ) {
	$u = wp_parse_url( (string) $url );
	if ( empty( $u['scheme'] ) || 'https' !== $u['scheme'] || empty( $u['host'] ) ) {
		return false;
	}
	return (bool) preg_match( '/(^|\.)(fcm\.googleapis\.com|android\.googleapis\.com|push\.services\.mozilla\.com|notify\.windows\.com|push\.apple\.com)$/i', $u['host'] );
}

/**
 * Gửi 1 thông báo tới 1 thiết bị. Trả về mã HTTP (201 = thành công, 404/410 = thiết bị đã hủy đăng ký).
 */
function vs_push_send_one( $sub, $payload ) {
	if ( empty( $sub['e'] ) || ! vs_push_endpoint_ok( $sub['e'] ) ) {
		return 410;
	}
	$ua   = vs_b64u_dec( isset( $sub['k'] ) ? $sub['k'] : '' );
	$auth = vs_b64u_dec( isset( $sub['a'] ) ? $sub['a'] : '' );
	if ( 65 !== strlen( $ua ) || 16 !== strlen( $auth ) ) {
		return 410;
	}
	$body = vs_push_encrypt( wp_json_encode( $payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ), $ua, $auth );
	$auth = vs_push_vapid_header( $sub['e'] );
	if ( ! $body || ! $auth ) {
		return 0;
	}
	$r = wp_remote_post( $sub['e'], array(
		'timeout' => 8,
		'headers' => array(
			'TTL'              => '86400',
			'Urgency'          => 'high',
			'Content-Type'     => 'application/octet-stream',
			'Content-Encoding' => 'aes128gcm',
			'Authorization'    => $auth,
		),
		'body'    => $body,
	) );
	return is_wp_error( $r ) ? 0 : (int) wp_remote_retrieve_response_code( $r );
}

/* =========================================================
 * Danh sách thiết bị đã bật thông báo
 * ======================================================= */

function vs_push_subs( $uid ) {
	return array_filter( (array) get_user_meta( (int) $uid, '_vs_push', true ), 'is_array' );
}

/** Gỡ 1 thiết bị khỏi mọi tài khoản (dùng khi thiết bị đổi tài khoản đăng nhập hoặc đã hủy đăng ký). */
function vs_push_forget( $endpoint, $except_uid = 0 ) {
	$id    = md5( $endpoint );
	$users = get_users( array( 'meta_key' => '_vs_push', 'fields' => 'ID', 'number' => 500 ) );
	foreach ( $users as $uid ) {
		if ( (int) $uid === (int) $except_uid ) {
			continue;
		}
		$subs = vs_push_subs( $uid );
		if ( isset( $subs[ $id ] ) ) {
			unset( $subs[ $id ] );
			$subs ? update_user_meta( $uid, '_vs_push', $subs ) : delete_user_meta( $uid, '_vs_push' );
		}
	}
}

/** Có ít nhất 1 thiết bị quản trị đang bật thông báo không (dùng cho trang Cài đặt). */
function vs_push_count( $uid, $kind = '' ) {
	return count( array_filter( vs_push_subs( $uid ), function ( $s ) use ( $kind ) {
		return ! $kind || vs_push_has_kind( $s, $kind );
	} ) );
}

/** Thiết bị có nhận loại thông báo này không: 'site' (khách, website) hoặc 'app' (app quản trị). */
function vs_push_has_kind( $s, $kind ) {
	return 'app' === $kind ? ! empty( $s['app'] ) : ! empty( $s['site'] );
}

/* =========================================================
 * Hàng đợi: gửi sau khi đã trả trang cho người dùng (không làm chậm đặt đơn, cập nhật đơn)
 * ======================================================= */

function vs_push_queue( $uids, $kind, $msg ) {
	if ( ! vs_push_supported() ) {
		return;
	}
	$GLOBALS['vs_push_q'][] = array( 'uids' => array_map( 'intval', (array) $uids ), 'kind' => $kind, 'msg' => $msg );
	if ( ! has_action( 'shutdown', 'vs_push_flush' ) ) {
		add_action( 'shutdown', 'vs_push_flush', 1000 );
	}
}

function vs_push_flush() {
	$q = isset( $GLOBALS['vs_push_q'] ) ? $GLOBALS['vs_push_q'] : array();
	$GLOBALS['vs_push_q'] = array();
	if ( ! $q ) {
		return;
	}
	if ( function_exists( 'fastcgi_finish_request' ) ) {
		fastcgi_finish_request();
	} elseif ( function_exists( 'litespeed_finish_request' ) ) {
		litespeed_finish_request();
	}
	ignore_user_abort( true );
	foreach ( $q as $job ) {
		foreach ( array_unique( $job['uids'] ) as $uid ) {
			$subs    = vs_push_subs( $uid );
			$changed = false;
			foreach ( $subs as $id => $s ) {
				if ( ! vs_push_has_kind( $s, $job['kind'] ) ) {
					continue;
				}
				$msg = $job['msg'];
				if ( 'app' === $job['kind'] ) {
					// Mở đúng app quản trị mà thiết bị đã bật (app.tên-miền hoặc tên-miền/app/).
					$msg['url'] = $s['app'] . ( ! empty( $msg['order'] ) ? '#/orders?o=' . (int) $msg['order'] : '#/' );
				}
				unset( $msg['order'] );
				$code = vs_push_send_one( $s, $msg );
				if ( in_array( $code, array( 404, 410 ), true ) ) {
					unset( $subs[ $id ] );
					$changed = true;
				} elseif ( $code >= 200 && $code < 300 ) {
					$subs[ $id ]['last'] = time();
					$changed             = true;
				}
			}
			if ( $changed ) {
				$subs ? update_user_meta( $uid, '_vs_push', $subs ) : delete_user_meta( $uid, '_vs_push' );
			}
		}
	}
}

/** Nội dung 1 thông báo đẩy. */
function vs_push_msg( $title, $text, $url, $tag, $order = 0 ) {
	return array(
		'title' => wp_strip_all_tags( (string) $title ),
		'body'  => wp_html_excerpt( wp_strip_all_tags( (string) $text ), 220, '…' ),
		'url'   => (string) $url,
		'tag'   => (string) $tag,
		'order' => (int) $order,
		'icon'  => VS_URI . '/assets/img/icon-192.png',
		'badge' => VS_URI . '/assets/img/badge-96.png',
		'ts'    => time(),
	);
}

/** Quản trị viên / biên tập viên có thiết bị đã bật thông báo app. */
function vs_push_admin_ids() {
	$ids = array();
	foreach ( get_users( array( 'meta_key' => '_vs_push', 'fields' => 'ID', 'number' => 500 ) ) as $uid ) {
		if ( user_can( $uid, 'manage_options' ) || user_can( $uid, 'edit_others_posts' ) ) {
			$ids[] = (int) $uid;
		}
	}
	return $ids;
}

/* Sự kiện -> thông báo đẩy (gọi từ vs_notify_user / vs_notify_admin). */
add_action( 'vs_notice_user_added', function ( $uid, $n ) {
	vs_push_queue( $uid, 'site', vs_push_msg( $n['title'], $n['text'], $n['url'] ? $n['url'] : home_url( '/' ), 'vs-u-' . $n['id'] ) );
}, 10, 2 );

add_action( 'vs_notice_admin_added', function ( $n ) {
	$ids = vs_push_admin_ids();
	if ( $ids ) {
		// Cùng mã "tag" với thông báo trong app (vs-{id}) để không hiện trùng 2 lần.
		vs_push_queue( $ids, 'app', vs_push_msg( $n['title'], $n['text'], '', 'vs-' . $n['id'], $n['order'] ) );
	}
} );

/* =========================================================
 * Service worker + manifest (cùng tên miền, không đụng web khác)
 * ======================================================= */

add_action( 'init', function () {
	$path = (string) wp_parse_url( isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '/', PHP_URL_PATH );
	if ( '/vs-sw' === $path ) {
		header( 'Content-Type: application/javascript; charset=utf-8' );
		header( 'Cache-Control: no-cache, max-age=0' );
		header( 'X-Robots-Tag: noindex' );
		header( 'Service-Worker-Allowed: /' );
		readfile( VS_DIR . '/assets/js/sw.js' );
		exit;
	}
	if ( '/vs-manifest' === $path ) {
		header( 'Content-Type: application/manifest+json; charset=utf-8' );
		header( 'Cache-Control: public, max-age=3600' );
		echo wp_json_encode( array(
			'name'             => get_bloginfo( 'name' ),
			'short_name'       => get_bloginfo( 'name' ),
			'start_url'        => '/tai-khoan/',
			'scope'            => '/',
			'display'          => 'standalone',
			'background_color' => '#ffffff',
			'theme_color'      => '#3d6bff',
			'icons'            => vs_push_icons(),
		) );
		exit;
	}
}, 1 );

function vs_push_icons() {
	$p = wp_parse_url( VS_URI, PHP_URL_PATH ) . '/assets/img/';
	return array(
		array( 'src' => $p . 'icon-192.png?v=' . VS_VERSION, 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any' ),
		array( 'src' => $p . 'icon-512.png?v=' . VS_VERSION, 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any' ),
		array( 'src' => $p . 'favicon.svg?v=' . VS_VERSION, 'sizes' => 'any', 'type' => 'image/svg+xml' ),
	);
}

/** Cấu hình cho assets/js/push.js. */
function vs_push_config( $kind ) {
	$k = vs_push_keys();
	return array(
		'ajax'  => '/wp-admin/admin-ajax.php',
		'nonce' => wp_create_nonce( 'vs_push' ),
		'key'   => $k ? $k['pub'] : '',
		'sw'    => '/vs-sw',
		'kind'  => $kind,
		'icon'  => VS_URI . '/assets/img/icon-192.png',
	);
}

/* Website: manifest (để "Thêm vào màn hình chính" trên iPhone nhận được thông báo) + script bật thông báo. */
add_action( 'wp_head', function () {
	echo '<link rel="manifest" href="/vs-manifest">' . "\n";
	echo '<link rel="apple-touch-icon" href="' . esc_url( VS_URI . '/assets/img/icon-192.png' ) . '">' . "\n";
}, 2 );

add_action( 'wp_enqueue_scripts', function () {
	if ( ! is_user_logged_in() ) {
		return;
	}
	wp_enqueue_script( 'vs-push', VS_URI . '/assets/js/push.js', array(), VS_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
	wp_localize_script( 'vs-push', 'VSPUSH', vs_push_config( 'site' ) );
}, 20 );

/* =========================================================
 * AJAX: bật / tắt / gửi thử
 * ======================================================= */

add_action( 'wp_ajax_vs_push', function () {
	nocache_headers();
	if ( ! check_ajax_referer( 'vs_push', 'nonce', false ) ) {
		wp_send_json_error( array( 'message' => 'Phiên làm việc đã hết hạn, vui lòng tải lại trang.' ), 403 );
	}
	$uid  = get_current_user_id();
	$op   = isset( $_POST['op'] ) ? sanitize_key( $_POST['op'] ) : '';
	$end  = isset( $_POST['endpoint'] ) ? esc_url_raw( wp_unslash( $_POST['endpoint'] ), array( 'https' ) ) : '';
	$subs = vs_push_subs( $uid );
	$id   = md5( $end );

	if ( 'sub' === $op ) {
		if ( ! vs_push_supported() || ! vs_push_keys() ) {
			wp_send_json_error( array( 'message' => 'Máy chủ chưa hỗ trợ thông báo đẩy (thiếu OpenSSL).' ) );
		}
		$k    = isset( $_POST['p256dh'] ) ? sanitize_text_field( wp_unslash( $_POST['p256dh'] ) ) : '';
		$a    = isset( $_POST['auth'] ) ? sanitize_text_field( wp_unslash( $_POST['auth'] ) ) : '';
		$kind = isset( $_POST['kind'] ) && 'app' === $_POST['kind'] && vs_can_manage_orders() ? 'app' : 'site';
		if ( ! vs_push_endpoint_ok( $end ) || 65 !== strlen( vs_b64u_dec( $k ) ) || 16 !== strlen( vs_b64u_dec( $a ) ) ) {
			wp_send_json_error( array( 'message' => 'Trình duyệt này chưa hỗ trợ thông báo đẩy.' ) );
		}
		// App quản trị: nhớ địa chỉ app (app.tên-miền/ hoặc tên-miền/app/) để bấm thông báo mở đúng nơi.
		$base = '';
		if ( 'app' === $kind ) {
			$b    = isset( $_POST['base'] ) ? esc_url_raw( wp_unslash( $_POST['base'] ), array( 'https', 'http' ) ) : '';
			$host = (string) wp_parse_url( $b, PHP_URL_HOST );
			$ok   = array( (string) wp_parse_url( home_url(), PHP_URL_HOST ) );
			if ( function_exists( 'vs_app_host' ) && vs_app_host() ) {
				$ok[] = vs_app_host();
			}
			$base = $b && in_array( $host, $ok, true ) ? strtok( $b, '#?' ) : home_url( vs_app_path() );
		}
		vs_push_forget( $end, $uid ); // Thiết bị này đổi sang tài khoản khác: ngừng gửi cho tài khoản cũ.
		$old         = isset( $subs[ $id ] ) ? $subs[ $id ] : array();
		$subs[ $id ] = array(
			'e'    => $end,
			'k'    => $k,
			'a'    => $a,
			'site' => 'site' === $kind ? 1 : ( empty( $old['site'] ) ? 0 : 1 ),
			'app'  => 'app' === $kind ? $base : ( empty( $old['app'] ) || ! vs_can_manage_orders() ? '' : $old['app'] ),
			'ua'   => isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ), 0, 160 ) : '',
			't'    => isset( $old['t'] ) ? $old['t'] : time(),
			'last' => isset( $old['last'] ) ? $old['last'] : 0,
		);
		// Mỗi tài khoản tối đa 12 thiết bị (giữ thiết bị mới nhất).
		uasort( $subs, function ( $x, $y ) {
			return (int) $y['t'] - (int) $x['t'];
		} );
		update_user_meta( $uid, '_vs_push', array_slice( $subs, 0, 12, true ) );
		wp_send_json_success( array( 'devices' => min( 12, count( $subs ) ) ) );
	}

	if ( 'unsub' === $op ) {
		// Chỉ tắt loại thông báo của trang đang đứng (website hoặc app quản trị).
		if ( isset( $subs[ $id ] ) ) {
			$subs[ $id ][ isset( $_POST['kind'] ) && 'app' === $_POST['kind'] ? 'app' : 'site' ] = '';
			if ( empty( $subs[ $id ]['app'] ) && empty( $subs[ $id ]['site'] ) ) {
				unset( $subs[ $id ] );
			}
		}
		$subs ? update_user_meta( $uid, '_vs_push', $subs ) : delete_user_meta( $uid, '_vs_push' );
		wp_send_json_success( array( 'devices' => count( $subs ) ) );
	}

	if ( 'test' === $op ) {
		if ( ! isset( $subs[ $id ] ) ) {
			wp_send_json_error( array( 'message' => 'Thiết bị này chưa bật thông báo.' ) );
		}
		$app  = isset( $_POST['kind'] ) && 'app' === $_POST['kind'] && ! empty( $subs[ $id ]['app'] );
		$msg  = vs_push_msg( 'Thông báo thử thành công', $app ? 'Bạn sẽ nhận thông báo đơn mới, thanh toán và việc cần xử lý trên thiết bị này.' : 'Bạn sẽ nhận thông báo khi đơn được thanh toán, có kết quả và hoàn thành.', $app ? $subs[ $id ]['app'] . '#/' : vs_account_url( 'thong-bao' ), 'vs-test' );
		unset( $msg['order'] );
		$code = vs_push_send_one( $subs[ $id ], $msg );
		if ( $code >= 200 && $code < 300 ) {
			wp_send_json_success( array( 'message' => 'Đã gửi thông báo thử tới thiết bị này.' ) );
		}
		if ( in_array( $code, array( 404, 410 ), true ) ) {
			unset( $subs[ $id ] );
			$subs ? update_user_meta( $uid, '_vs_push', $subs ) : delete_user_meta( $uid, '_vs_push' );
		}
		wp_send_json_error( array( 'message' => $code ? 'Dịch vụ thông báo từ chối (mã ' . $code . '). Hãy tắt rồi bật lại.' : 'Không kết nối được dịch vụ thông báo, vui lòng thử lại.' ) );
	}

	wp_send_json_error( array( 'message' => 'Yêu cầu không hợp lệ.' ) );
} );
