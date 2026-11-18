<?php
/**
 * Mã giảm giá: lưu trữ, kiểm tra, thống kê lượt dùng và API cho App quản trị.
 *
 * Mỗi mã (option vs_coupon_list, khóa = MÃ viết hoa):
 *   kind  pct|fixed    value  % hoặc số tiền     max   giảm tối đa (đ, chỉ với %; 0 = không giới hạn)
 *   min   đơn tối thiểu (đ)   scope all|check|reduce
 *   start / end  timestamp (0 = không giới hạn)   limit  tổng lượt (0 = không giới hạn)
 *   per   lượt / khách (theo SĐT, 0 = không giới hạn)   phones  [SĐT] = mã riêng cho khách
 *   active bool   note  ghi chú nội bộ   created  timestamp
 * Lượt dùng = số đơn có mã này và chưa bị hủy (đơn tự hủy sau 30 phút sẽ trả lại lượt).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const VS_COUPON_OPT = 'vs_coupon_list';

function vs_coupon_defaults() {
	return array(
		'code'    => '',
		'kind'    => 'pct',
		'value'   => 0,
		'max'     => 0,
		'min'     => 0,
		'scope'   => 'all',
		'start'   => 0,
		'end'     => 0,
		'limit'   => 0,
		'per'     => 0,
		'phones'  => array(),
		'active'  => true,
		'note'    => '',
		'created' => 0,
	);
}

/** Danh sách mã. Lần đầu: chuyển các mã cũ (cài đặt dạng MÃ|%|hạn|dịch vụ) sang định dạng mới. */
function vs_coupon_list() {
	$list = get_option( VS_COUPON_OPT, null );
	if ( null === $list ) {
		$list = array();
		foreach ( preg_split( '/\r?\n/', (string) vs_opt( 'coupons' ) ) as $line ) {
			$p = array_map( 'trim', explode( '|', $line ) );
			if ( count( $p ) < 2 || '' === $p[0] ) {
				continue;
			}
			$code = vs_coupon_norm_code( $p[0] );
			if ( ! $code ) {
				continue;
			}
			$end = 0;
			if ( ! empty( $p[2] ) ) {
				$d   = DateTime::createFromFormat( 'd/m/Y H:i:s', $p[2] . ' 23:59:59', wp_timezone() );
				$end = $d ? $d->getTimestamp() : 0;
			}
			$list[ $code ] = array_merge( vs_coupon_defaults(), array(
				'code'    => $code,
				'value'   => max( 0, min( 90, (float) $p[1] ) ),
				'end'     => $end,
				'scope'   => isset( $p[3] ) && in_array( $p[3], array( 'check', 'reduce' ), true ) ? $p[3] : 'all',
				'created' => time(),
			) );
		}
		update_option( VS_COUPON_OPT, $list, false );
	}
	$out = array();
	foreach ( (array) $list as $k => $c ) {
		if ( is_array( $c ) ) {
			$out[ $k ] = array_merge( vs_coupon_defaults(), $c );
		}
	}
	return $out;
}
add_action( 'init', 'vs_coupon_list', 5 ); // Chuyển dữ liệu cũ trước khi trang cài đặt (đã bỏ ô mã cũ) được lưu lại.

function vs_coupon_save_list( $list ) {
	update_option( VS_COUPON_OPT, $list, false );
	delete_transient( 'vs_coupon_usage' );
}

function vs_coupon_norm_code( $code ) {
	$code = strtoupper( preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $code ) );
	return strlen( $code ) >= 3 && strlen( $code ) <= 24 ? $code : '';
}

function vs_coupon_get( $code ) {
	$all  = vs_coupon_list();
	$code = strtoupper( trim( (string) $code ) );
	return isset( $all[ $code ] ) ? $all[ $code ] : null;
}

/** Thống kê lượt dùng: [MÃ => [used, paid, discount, phones => [sđt => số lượt]]] (đơn chưa hủy). */
function vs_coupon_usage( $fresh = false ) {
	$cache = $fresh ? false : get_transient( 'vs_coupon_usage' );
	if ( is_array( $cache ) ) {
		return $cache;
	}
	$ids = get_posts( array(
		'post_type'      => 'vs_order',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'meta_query'     => array( array( 'key' => '_vs_coupon', 'value' => '', 'compare' => '!=' ) ),
	) );
	$out = array();
	foreach ( $ids as $id ) {
		$code = strtoupper( (string) vs_meta( $id, 'coupon' ) );
		$st   = vs_meta( $id, 'status' );
		if ( ! $code || 'huy' === $st || 0 === strpos( $code, 'CTV-' ) ) {
			continue;
		}
		if ( ! isset( $out[ $code ] ) ) {
			$out[ $code ] = array( 'used' => 0, 'paid' => 0, 'discount' => 0, 'phones' => array() );
		}
		$out[ $code ]['used']++;
		$ph = vs_norm_phone( vs_meta( $id, 'phone' ) );
		if ( $ph ) {
			$out[ $code ]['phones'][ $ph ] = ( isset( $out[ $code ]['phones'][ $ph ] ) ? $out[ $code ]['phones'][ $ph ] : 0 ) + 1;
		}
		if ( in_array( $st, array( 'dang_xu_ly', 'hoan_thanh' ), true ) ) {
			$out[ $code ]['paid']++;
			$out[ $code ]['discount'] += (int) vs_meta( $id, 'discount' );
		}
	}
	set_transient( 'vs_coupon_usage', $out, 10 * MINUTE_IN_SECONDS );
	return $out;
}
/* Đơn đổi trạng thái / đơn mới: tính lại lượt dùng. */
add_action( 'vs_order_created', function () { delete_transient( 'vs_coupon_usage' ); } );
add_action( 'vs_order_status', function () { delete_transient( 'vs_coupon_usage' ); } );
add_action( 'updated_post_meta', function ( $mid, $pid, $key ) {
	if ( '_vs_status' === $key ) {
		delete_transient( 'vs_coupon_usage' );
	}
}, 10, 3 );

/** Mô tả ưu đãi ngắn: "Giảm 10% (tối đa 50.000đ)" / "Giảm 20.000đ". */
function vs_coupon_label( $c ) {
	if ( 'fixed' === $c['kind'] ) {
		return 'Giảm ' . vs_money( $c['value'] );
	}
	$v = rtrim( rtrim( number_format( (float) $c['value'], 1, ',', '' ), '0' ), ',' );
	return 'Giảm ' . $v . '%' . ( $c['max'] ? ' (tối đa ' . vs_money( $c['max'] ) . ')' : '' );
}

/** Tình trạng: active | scheduled | expired | used_up | off. */
function vs_coupon_state( $c, $usage = null ) {
	$now = time();
	if ( empty( $c['active'] ) ) {
		return 'off';
	}
	if ( $c['end'] && $c['end'] < $now ) {
		return 'expired';
	}
	if ( $c['start'] && $c['start'] > $now ) {
		return 'scheduled';
	}
	if ( $c['limit'] && $usage && $usage['used'] >= $c['limit'] ) {
		return 'used_up';
	}
	return 'active';
}

function vs_coupon_states() {
	return array( 'active' => 'Đang chạy', 'scheduled' => 'Chưa bắt đầu', 'expired' => 'Hết hạn', 'used_up' => 'Hết lượt', 'off' => 'Tạm tắt' );
}

/**
 * Kiểm tra mã cho một đơn.
 * @param int|null $subtotal Tạm tính (null = chưa biết, bỏ qua kiểm tra đơn tối thiểu).
 * @return array|WP_Error|null  null khi không nhập mã.
 */
function vs_coupon_eval( $code, $type, $subtotal = null, $phone = '' ) {
	$code = strtoupper( trim( (string) $code ) );
	if ( '' === $code ) {
		return null;
	}
	$c = vs_coupon_get( $code );
	if ( ! $c ) {
		return new WP_Error( 'coupon', 'Mã giảm giá không tồn tại.' );
	}
	$usage = vs_coupon_usage();
	$u     = isset( $usage[ $code ] ) ? $usage[ $code ] : array( 'used' => 0, 'phones' => array() );
	$state = vs_coupon_state( $c, $u );
	$msgs  = array(
		'off'       => 'Mã giảm giá hiện không còn áp dụng.',
		'expired'   => 'Mã giảm giá đã hết hạn.',
		'scheduled' => 'Mã giảm giá chưa đến thời gian áp dụng (từ ' . wp_date( 'd/m/Y', $c['start'] ) . ').',
		'used_up'   => 'Mã giảm giá đã hết lượt sử dụng.',
	);
	if ( isset( $msgs[ $state ] ) ) {
		return new WP_Error( 'coupon', $msgs[ $state ] );
	}
	if ( 'all' !== $c['scope'] && $c['scope'] !== $type ) {
		return new WP_Error( 'coupon', 'Mã này chỉ áp dụng cho dịch vụ ' . ( 'check' === $c['scope'] ? 'kiểm tra đạo văn' : 'giảm tỷ lệ' ) . '.' );
	}
	$phone = vs_norm_phone( $phone );
	if ( $c['phones'] ) {
		if ( ! $phone ) {
			return new WP_Error( 'coupon', 'Mã này dành riêng cho khách được tặng. Vui lòng nhập số điện thoại đặt hàng trước khi áp dụng.' );
		}
		if ( ! in_array( $phone, $c['phones'], true ) ) {
			return new WP_Error( 'coupon', 'Mã này không áp dụng cho số điện thoại ' . $phone . '.' );
		}
	}
	if ( $c['per'] && $phone && isset( $u['phones'][ $phone ] ) && $u['phones'][ $phone ] >= $c['per'] ) {
		return new WP_Error( 'coupon', 'Số điện thoại này đã dùng hết lượt của mã giảm giá.' );
	}
	if ( null !== $subtotal && $c['min'] && $subtotal < $c['min'] ) {
		return new WP_Error( 'coupon', 'Mã áp dụng cho đơn từ ' . vs_money( $c['min'] ) . '.' );
	}
	$disc = null === $subtotal ? 0 : vs_coupon_discount( $c, $subtotal );
	return array(
		'code'     => $code,
		'kind'     => $c['kind'],
		'value'    => (float) $c['value'],
		'max'      => (int) $c['max'],
		'min'      => (int) $c['min'],
		'label'    => vs_coupon_label( $c ),
		'discount' => $disc,
	);
}

function vs_coupon_discount( $c, $subtotal ) {
	$d = 'fixed' === $c['kind'] ? (float) $c['value'] : $subtotal * (float) $c['value'] / 100;
	if ( 'pct' === $c['kind'] && $c['max'] ) {
		$d = min( $d, (float) $c['max'] );
	}
	return (int) max( 0, min( $subtotal, round( $d ) ) );
}

/* ---------- Khách áp dụng mã trên form đặt hàng ---------- */
add_action( 'wp_ajax_vs_coupon', 'vs_ajax_coupon' );
add_action( 'wp_ajax_nopriv_vs_coupon', 'vs_ajax_coupon' );
function vs_ajax_coupon() {
	vs_rate_limit( 'coupon', 30 );
	$r = vs_coupon_eval(
		wp_unslash( isset( $_POST['code'] ) ? $_POST['code'] : '' ),
		sanitize_key( isset( $_POST['type'] ) ? $_POST['type'] : 'check' ),
		null,
		wp_unslash( isset( $_POST['phone'] ) ? $_POST['phone'] : '' )
	);
	if ( is_wp_error( $r ) ) {
		wp_send_json_error( array( 'message' => $r->get_error_message() ) );
	}
	if ( ! $r ) {
		wp_send_json_error( array( 'message' => 'Vui lòng nhập mã giảm giá.' ) );
	}
	unset( $r['discount'] );
	$r['pct'] = 'pct' === $r['kind'] ? $r['value'] : 0; // Tương thích giao diện cũ (đang lưu trong bộ nhớ đệm trình duyệt).
	wp_send_json_success( $r );
}

/** Mã riêng còn dùng được của một khách (theo SĐT tài khoản) - hiển thị trong trang tài khoản. */
function vs_user_coupons( $uid ) {
	$phone = vs_norm_phone( vs_user_phone( $uid ) );
	if ( ! $phone ) {
		return array();
	}
	$usage = vs_coupon_usage();
	$out   = array();
	foreach ( vs_coupon_list() as $code => $c ) {
		if ( ! in_array( $phone, (array) $c['phones'], true ) ) {
			continue;
		}
		$u = isset( $usage[ $code ] ) ? $usage[ $code ] : array( 'used' => 0, 'phones' => array() );
		if ( ! in_array( vs_coupon_state( $c, $u ), array( 'active', 'scheduled' ), true ) ) {
			continue;
		}
		if ( $c['per'] && isset( $u['phones'][ $phone ] ) && $u['phones'][ $phone ] >= $c['per'] ) {
			continue;
		}
		$out[] = array(
			'code'  => $code,
			'label' => vs_coupon_label( $c ),
			'big'   => 'fixed' === $c['kind'] ? vs_coupon_short_money( $c['value'] ) : rtrim( rtrim( number_format( (float) $c['value'], 1, ',', '' ), '0' ), ',' ) . '%',
			'scope' => array( 'all' => 'Mọi dịch vụ', 'check' => 'Kiểm tra đạo văn', 'reduce' => 'Giảm tỷ lệ' )[ $c['scope'] ],
			'min'   => $c['min'] ? 'Đơn từ ' . vs_money( $c['min'] ) : '',
			'end'   => $c['end'] ? 'HSD ' . wp_date( 'd/m/Y', $c['end'] ) : 'Không thời hạn',
			'url'   => vs_page_url( 'dat-hang', array( 'tab' => 'reduce' === $c['scope'] ? 'reduce' : 'check', 'coupon' => $code ) ),
		);
	}
	return $out;
}

function vs_coupon_short_money( $v ) {
	$v = (int) $v;
	if ( $v >= 1000000 ) {
		return rtrim( rtrim( number_format( $v / 1000000, 1, ',', '' ), '0' ), ',' ) . 'TR';
	}
	return $v >= 1000 ? round( $v / 1000 ) . 'K' : $v . 'Đ';
}

/* =========================================================
 * API cho App quản trị
 * ======================================================= */

function vs_coupon_row( $c, $usage ) {
	$u     = isset( $usage[ $c['code'] ] ) ? $usage[ $c['code'] ] : array( 'used' => 0, 'paid' => 0, 'discount' => 0 );
	$state = vs_coupon_state( $c, $u );
	return array(
		'code'     => $c['code'],
		'kind'     => $c['kind'],
		'value'    => (float) $c['value'],
		'max'      => (int) $c['max'],
		'min'      => (int) $c['min'],
		'scope'    => $c['scope'],
		'start'    => $c['start'] ? wp_date( 'Y-m-d', $c['start'] ) : '',
		'end'      => $c['end'] ? wp_date( 'Y-m-d', $c['end'] ) : '',
		'start_f'  => $c['start'] ? wp_date( 'd/m/Y', $c['start'] ) : '',
		'end_f'    => $c['end'] ? wp_date( 'd/m/Y', $c['end'] ) : '',
		'limit'    => (int) $c['limit'],
		'per'      => (int) $c['per'],
		'phones'   => array_values( (array) $c['phones'] ),
		'active'   => ! empty( $c['active'] ),
		'note'     => (string) $c['note'],
		'label'    => vs_coupon_label( $c ),
		'state'    => $state,
		'state_l'  => vs_coupon_states()[ $state ],
		'used'     => (int) $u['used'],
		'paid'     => (int) $u['paid'],
		'saved'    => (int) $u['discount'],
		'saved_f'  => vs_money( $u['discount'] ),
		'soon'     => 'active' === $state && $c['end'] && $c['end'] - time() < 7 * DAY_IN_SECONDS,
		'created'  => (int) $c['created'],
	);
}

function vs_coupon_admin_guard() {
	vs_adm_guard();
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Chỉ quản trị viên được thay đổi mã giảm giá.' ), 403 );
	}
}

add_action( 'wp_ajax_vs_app_coupons', function () {
	vs_adm_guard();
	$usage = vs_coupon_usage( true );
	$rows  = array();
	foreach ( vs_coupon_list() as $c ) {
		$rows[] = vs_coupon_row( $c, $usage );
	}
	usort( $rows, function ( $a, $b ) {
		$o = array( 'active' => 0, 'scheduled' => 1, 'used_up' => 2, 'off' => 3, 'expired' => 4 );
		return $o[ $a['state'] ] - $o[ $b['state'] ] ?: $b['created'] - $a['created'];
	} );
	$sum = array( 'active' => 0, 'used' => 0, 'paid' => 0, 'saved' => 0, 'soon' => 0 );
	foreach ( $rows as $r ) {
		$sum['active'] += 'active' === $r['state'] ? 1 : 0;
		$sum['soon']   += $r['soon'] ? 1 : 0;
		$sum['used']   += $r['used'];
		$sum['paid']   += $r['paid'];
		$sum['saved']  += $r['saved'];
	}
	$sum['saved_f'] = vs_money( $sum['saved'] );
	wp_send_json_success( array( 'items' => $rows, 'sum' => $sum, 'admin' => current_user_can( 'manage_options' ) ) );
} );

add_action( 'wp_ajax_vs_app_coupon_save', function () {
	vs_coupon_admin_guard();
	$in   = wp_unslash( $_POST );
	$orig = vs_coupon_norm_code( isset( $in['orig'] ) ? $in['orig'] : '' );
	$code = vs_coupon_norm_code( isset( $in['code'] ) ? $in['code'] : '' );
	if ( ! $code ) {
		wp_send_json_error( array( 'message' => 'Mã gồm 3-24 ký tự: chữ không dấu, số, gạch ngang hoặc gạch dưới.' ) );
	}
	$list = vs_coupon_list();
	if ( $code !== $orig && isset( $list[ $code ] ) ) {
		wp_send_json_error( array( 'message' => 'Mã ' . $code . ' đã tồn tại.' ) );
	}
	if ( $orig && ! isset( $list[ $orig ] ) ) {
		wp_send_json_error( array( 'message' => 'Mã cần sửa không còn tồn tại, vui lòng tải lại.' ) );
	}
	$kind  = isset( $in['kind'] ) && 'fixed' === $in['kind'] ? 'fixed' : 'pct';
	$num   = function ( $k ) use ( $in ) {
		return isset( $in[ $k ] ) ? (float) str_replace( array( '.', ',', ' ' ), array( '', '.', '' ), (string) $in[ $k ] ) : 0;
	};
	$value = 'pct' === $kind ? round( (float) str_replace( ',', '.', isset( $in['value'] ) ? $in['value'] : 0 ), 1 ) : (int) $num( 'value' );
	if ( 'pct' === $kind && ( $value <= 0 || $value > 90 ) ) {
		wp_send_json_error( array( 'message' => 'Phần trăm giảm phải từ 0,1% đến 90%.' ) );
	}
	if ( 'fixed' === $kind && $value < 1000 ) {
		wp_send_json_error( array( 'message' => 'Số tiền giảm tối thiểu 1.000đ.' ) );
	}
	$date = function ( $k, $end ) use ( $in ) {
		$v = isset( $in[ $k ] ) ? trim( (string) $in[ $k ] ) : '';
		if ( ! $v ) {
			return 0;
		}
		$d = DateTime::createFromFormat( 'Y-m-d H:i:s', $v . ( $end ? ' 23:59:59' : ' 00:00:00' ), wp_timezone() );
		return $d ? $d->getTimestamp() : 0;
	};
	$start = $date( 'start', false );
	$end   = $date( 'end', true );
	if ( $start && $end && $end < $start ) {
		wp_send_json_error( array( 'message' => 'Ngày hết hạn phải sau ngày bắt đầu.' ) );
	}
	$phones = array();
	foreach ( preg_split( '/[\s,;]+/', isset( $in['phones'] ) ? (string) $in['phones'] : '' ) as $p ) {
		$p = vs_norm_phone( $p );
		if ( '' === $p ) {
			continue;
		}
		if ( ! preg_match( '/^0\d{9,10}$/', $p ) ) {
			wp_send_json_error( array( 'message' => 'Số điện thoại không hợp lệ: ' . $p ) );
		}
		$phones[] = $p;
	}
	$phones = array_values( array_unique( $phones ) );
	$old    = $orig ? $list[ $orig ] : null;
	$c      = array_merge( vs_coupon_defaults(), array(
		'code'    => $code,
		'kind'    => $kind,
		'value'   => $value,
		'max'     => 'pct' === $kind ? max( 0, (int) $num( 'max' ) ) : 0,
		'min'     => max( 0, (int) $num( 'min' ) ),
		'scope'   => isset( $in['scope'] ) && in_array( $in['scope'], array( 'check', 'reduce' ), true ) ? $in['scope'] : 'all',
		'start'   => $start,
		'end'     => $end,
		'limit'   => max( 0, (int) $num( 'limit' ) ),
		'per'     => max( 0, (int) $num( 'per' ) ),
		'phones'  => $phones,
		'active'  => ! empty( $in['active'] ),
		'note'    => sanitize_textarea_field( isset( $in['note'] ) ? $in['note'] : '' ),
		'created' => $old ? $old['created'] : time(),
	) );
	if ( $orig && $orig !== $code ) {
		unset( $list[ $orig ] );
	}
	$list[ $code ] = $c;
	vs_coupon_save_list( $list );

	// Mã riêng: báo cho khách có tài khoản (mỗi khách chỉ báo 1 lần cho mỗi mã).
	$sent = 0;
	if ( ! empty( $in['notify'] ) && $phones && $c['active'] ) {
		foreach ( $phones as $p ) {
			$u = vs_find_user_by_phone( $p );
			if ( $u ) {
				vs_notify_user( $u->ID, 'promo', 'Bạn được tặng mã giảm giá ' . $code, vs_coupon_label( $c ) . ( $c['end'] ? ', hạn dùng đến ' . wp_date( 'd/m/Y', $c['end'] ) : '' ) . '. Nhập mã khi đặt hàng để được giảm.', vs_page_url( 'dat-hang', array( 'tab' => 'reduce' === $c['scope'] ? 'reduce' : 'check', 'coupon' => $code ) ), 'coupon_' . $code . '_' . $u->ID );
				$sent++;
			}
		}
	}
	$msg = ( $old ? 'Đã cập nhật mã ' : 'Đã tạo mã ' ) . $code . '.';
	if ( ! empty( $in['notify'] ) && $phones ) {
		$msg .= $sent ? ' Đã gửi thông báo cho ' . $sent . ' khách có tài khoản.' : ' Khách chưa có tài khoản trên website nên chưa gửi được thông báo - hãy gửi mã qua Zalo/SMS.';
	}
	wp_send_json_success( array( 'message' => $msg, 'item' => vs_coupon_row( $c, vs_coupon_usage( true ) ) ) );
} );

add_action( 'wp_ajax_vs_app_coupon_toggle', function () {
	vs_coupon_admin_guard();
	$code = vs_coupon_norm_code( isset( $_POST['code'] ) ? wp_unslash( $_POST['code'] ) : '' );
	$list = vs_coupon_list();
	if ( ! isset( $list[ $code ] ) ) {
		wp_send_json_error( array( 'message' => 'Không tìm thấy mã.' ) );
	}
	$list[ $code ]['active'] = ! empty( $_POST['active'] );
	vs_coupon_save_list( $list );
	wp_send_json_success( array( 'message' => ( $list[ $code ]['active'] ? 'Đã bật mã ' : 'Đã tạm tắt mã ' ) . $code . '.', 'item' => vs_coupon_row( $list[ $code ], vs_coupon_usage() ) ) );
} );

add_action( 'wp_ajax_vs_app_coupon_del', function () {
	vs_coupon_admin_guard();
	$code = vs_coupon_norm_code( isset( $_POST['code'] ) ? wp_unslash( $_POST['code'] ) : '' );
	$list = vs_coupon_list();
	if ( ! isset( $list[ $code ] ) ) {
		wp_send_json_error( array( 'message' => 'Không tìm thấy mã.' ) );
	}
	unset( $list[ $code ] );
	vs_coupon_save_list( $list );
	wp_send_json_success( array( 'message' => 'Đã xóa mã ' . $code . '. Các đơn đã dùng mã vẫn giữ nguyên.' ) );
} );

/* =========================================================
 * Trang tóm tắt trong WP Admin (quản lý đầy đủ trong App quản trị)
 * ======================================================= */
add_action( 'admin_menu', function () {
	add_submenu_page( 'edit.php?post_type=vs_order', 'Mã giảm giá', 'Mã giảm giá', 'manage_options', 'vs-coupons', 'vs_coupon_admin_page' );
}, 20 );

function vs_coupon_admin_page() {
	$usage  = vs_coupon_usage( true );
	$app    = function_exists( 'vs_app_path' ) ? home_url( vs_app_path() ) . '#/coupons' : '';
	$scopes = array( 'all' => 'Mọi dịch vụ', 'check' => 'Kiểm tra', 'reduce' => 'Giảm tỷ lệ' );
	$colors = array( 'active' => '#047857', 'scheduled' => '#4338ca', 'expired' => '#6b7280', 'used_up' => '#b45309', 'off' => '#6b7280' );
	echo '<div class="wrap"><h1 class="wp-heading-inline">Mã giảm giá</h1>';
	if ( $app ) {
		echo ' <a class="page-title-action" href="' . esc_url( $app . '?new=1' ) . '" target="_blank" rel="noopener">Tạo mã mới</a>';
	}
	echo '<hr class="wp-header-end"><p>Tạo, sửa, bật/tắt và tặng mã riêng cho khách trong <a href="' . esc_url( $app ) . '" target="_blank" rel="noopener"><b>App quản trị → Mã giảm giá</b></a>. Danh sách dưới đây đồng bộ trực tiếp.</p>';
	echo '<table class="widefat striped"><thead><tr><th>Mã</th><th>Ưu đãi</th><th>Áp dụng</th><th>Đối tượng</th><th>Lượt dùng</th><th>Thời hạn</th><th>Đã giảm</th><th>Trạng thái</th></tr></thead><tbody>';
	$list = vs_coupon_list();
	if ( ! $list ) {
		echo '<tr><td colspan="8">Chưa có mã giảm giá nào.</td></tr>';
	}
	foreach ( $list as $c ) {
		$r = vs_coupon_row( $c, $usage );
		echo '<tr><td><code style="font-size:13px;font-weight:600">' . esc_html( $r['code'] ) . '</code></td>';
		echo '<td>' . esc_html( $r['label'] ) . ( $r['min'] ? '<br><small>Đơn từ ' . esc_html( vs_money( $r['min'] ) ) . '</small>' : '' ) . '</td>';
		echo '<td>' . esc_html( $scopes[ $r['scope'] ] ) . '</td>';
		echo '<td>' . ( $r['phones'] ? esc_html( implode( ', ', $r['phones'] ) ) : 'Mọi khách hàng' ) . '</td>';
		echo '<td>' . (int) $r['used'] . ( $r['limit'] ? ' / ' . (int) $r['limit'] : '' ) . '</td>';
		echo '<td>' . esc_html( ( $r['start_f'] ? $r['start_f'] . ' → ' : '' ) . ( $r['end_f'] ? $r['end_f'] : 'Không giới hạn' ) ) . '</td>';
		echo '<td>' . esc_html( $r['saved_f'] ) . '</td>';
		echo '<td><b style="color:' . esc_attr( $colors[ $r['state'] ] ) . '">' . esc_html( $r['state_l'] ) . '</b></td></tr>';
	}
	echo '</tbody></table></div>';
}
