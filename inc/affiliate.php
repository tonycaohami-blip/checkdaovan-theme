<?php
/**
 * Chương trình cộng tác viên (affiliate):
 * - Mỗi tài khoản có mã + link giới thiệu (?ref=MÃ), lưu cookie theo số ngày cài đặt.
 * - Khách vào qua link được ưu đãi, CTV nhận hoa hồng khi đơn "Hoàn thành".
 * - CTV gửi yêu cầu rút tiền, quản trị duyệt trong Đơn hàng > Rút tiền CTV.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ---------- Mã & link giới thiệu ---------- */

function vs_aff_code( $uid ) {
	$code = get_user_meta( $uid, '_vs_aff_code', true );
	if ( $code ) {
		return $code;
	}
	$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
	do {
		$code = '';
		for ( $i = 0; $i < 6; $i++ ) {
			$code .= $chars[ wp_rand( 0, strlen( $chars ) - 1 ) ];
		}
	} while ( vs_aff_user_by_code( $code ) );
	update_user_meta( $uid, '_vs_aff_code', $code );
	return $code;
}

function vs_aff_user_by_code( $code ) {
	$code = strtoupper( preg_replace( '/[^A-Za-z0-9]/', '', (string) $code ) );
	if ( '' === $code ) {
		return 0;
	}
	$u = get_users( array( 'meta_key' => '_vs_aff_code', 'meta_value' => $code, 'number' => 1, 'fields' => 'ID' ) );
	return $u ? (int) $u[0] : 0;
}

function vs_aff_link( $uid, $url = '' ) {
	return add_query_arg( 'ref', vs_aff_code( $uid ), $url ? $url : home_url( '/' ) );
}

/* Ghi nhận lượt click link giới thiệu. */
add_action( 'template_redirect', function () {
	if ( empty( $_GET['ref'] ) ) {
		return;
	}
	$uid = vs_aff_user_by_code( wp_unslash( $_GET['ref'] ) );
	if ( ! $uid || get_current_user_id() === $uid ) {
		return;
	}
	$code = vs_aff_code( $uid );
	if ( ! isset( $_COOKIE['vs_ref'] ) || $_COOKIE['vs_ref'] !== $code ) {
		update_user_meta( $uid, '_vs_aff_clicks', (int) get_user_meta( $uid, '_vs_aff_clicks', true ) + 1 );
	}
	$days = max( 1, (int) vs_opt( 'ref_days' ) );
	setcookie( 'vs_ref', $code, time() + $days * DAY_IN_SECONDS, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, is_ssl(), true );
	$_COOKIE['vs_ref'] = $code;
} );

function vs_ref_user_from_cookie() {
	return isset( $_COOKIE['vs_ref'] ) ? vs_aff_user_by_code( wp_unslash( $_COOKIE['vs_ref'] ) ) : 0;
}

/** Người giới thiệu của khách hiện tại (tài khoản đã gắn hoặc cookie), không tính tự giới thiệu. */
function vs_current_ref_user() {
	$me  = get_current_user_id();
	$ref = $me ? (int) get_user_meta( $me, '_vs_referred_by', true ) : 0;
	if ( ! $ref ) {
		$ref = vs_ref_user_from_cookie();
	}
	return ( $ref && $ref !== $me ) ? $ref : 0;
}

function vs_ref_discount_active() {
	return vs_current_ref_user() && (float) vs_opt( 'ref_discount' ) > 0;
}

/* ---------- Hoa hồng ---------- */

add_action( 'vs_order_created', function ( $id ) {
	$ref = vs_current_ref_user();
	if ( ! $ref ) {
		return;
	}
	// Không tính hoa hồng khi CTV tự đặt cho chính số điện thoại của mình.
	if ( vs_user_phone( $ref ) && vs_user_phone( $ref ) === vs_meta( $id, 'phone' ) ) {
		return;
	}
	update_post_meta( $id, '_vs_ref_user', $ref );
	update_post_meta( $id, '_vs_commission', (int) round( vs_meta( $id, 'price' ) * (float) vs_opt( 'aff_pct' ) / 100 ) );
	update_post_meta( $id, '_vs_comm_status', 'pending' );
}, 5 );

add_action( 'vs_order_status', function ( $id, $status ) {
	if ( ! vs_meta( $id, 'ref_user' ) ) {
		return;
	}
	$map = array( 'hoan_thanh' => 'approved', 'huy' => 'void' );
	update_post_meta( $id, '_vs_comm_status', isset( $map[ $status ] ) ? $map[ $status ] : 'pending' );
}, 10, 2 );

function vs_comm_labels() {
	return array( 'pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'void' => 'Đã hủy' );
}

function vs_aff_orders( $uid ) {
	return get_posts( array(
		'post_type'      => 'vs_order',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'meta_key'       => '_vs_ref_user',
		'meta_value'     => $uid,
		'fields'         => 'ids',
	) );
}

function vs_aff_payouts( $uid ) {
	return get_posts( array(
		'post_type'      => 'vs_payout',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'meta_key'       => '_vs_user',
		'meta_value'     => $uid,
		'fields'         => 'ids',
	) );
}

function vs_aff_stats( $uid ) {
	$s = array(
		'clicks'    => (int) get_user_meta( $uid, '_vs_aff_clicks', true ),
		'orders'    => 0,
		'customers' => count( get_users( array( 'meta_key' => '_vs_referred_by', 'meta_value' => $uid, 'fields' => 'ID' ) ) ),
		'pending'   => 0,
		'approved'  => 0,
		'paid'      => 0,
		'requested' => 0,
	);
	foreach ( vs_aff_orders( $uid ) as $oid ) {
		$st = vs_meta( $oid, 'comm_status' );
		if ( 'void' === $st ) {
			continue;
		}
		$s['orders']++;
		$s[ 'approved' === $st ? 'approved' : 'pending' ] += (int) vs_meta( $oid, 'commission' );
	}
	foreach ( vs_aff_payouts( $uid ) as $pid ) {
		$st = vs_meta( $pid, 'status' );
		if ( 'paid' === $st ) {
			$s['paid'] += (int) vs_meta( $pid, 'amount' );
		} elseif ( 'pending' === $st ) {
			$s['requested'] += (int) vs_meta( $pid, 'amount' );
		}
	}
	$s['available'] = max( 0, $s['approved'] - $s['paid'] - $s['requested'] );
	return $s;
}

/* ---------- Yêu cầu rút tiền ---------- */

add_action( 'init', function () {
	register_post_type( 'vs_payout', array(
		'labels'          => array( 'name' => 'Rút tiền CTV', 'singular_name' => 'Yêu cầu rút tiền', 'edit_item' => 'Yêu cầu rút tiền', 'all_items' => 'Rút tiền CTV', 'not_found' => 'Chưa có yêu cầu nào' ),
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => 'edit.php?post_type=vs_order',
		'supports'        => array( 'title' ),
		'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
		'map_meta_cap'    => true,
	) );
} );

add_action( 'admin_post_vs_payout', function () {
	vs_check_form_nonce( 'vs_payout' );
	$uid  = get_current_user_id();
	$back = vs_account_url( 'cong-tac-vien' );
	$s    = vs_aff_stats( $uid );
	if ( $s['requested'] > 0 ) {
		vs_back( 'payout_pend', $back );
	}
	if ( $s['available'] < (int) vs_opt( 'payout_min' ) ) {
		vs_back( 'payout_min', $back );
	}
	$bank = array();
	foreach ( array( 'bank_name', 'bank_account', 'bank_holder' ) as $k ) {
		$bank[ $k ] = (string) get_user_meta( $uid, '_vs_' . $k, true );
		if ( '' === $bank[ $k ] ) {
			vs_back( 'payout_bank', $back );
		}
	}
	$user = wp_get_current_user();
	$pid  = wp_insert_post( array(
		'post_type'   => 'vs_payout',
		'post_status' => 'publish',
		'post_title'  => $user->display_name . ' - ' . vs_money( $s['available'] ),
	) );
	update_post_meta( $pid, '_vs_user', $uid );
	update_post_meta( $pid, '_vs_amount', $s['available'] );
	update_post_meta( $pid, '_vs_status', 'pending' );
	update_post_meta( $pid, '_vs_bank', $bank );
	vs_telegram( '💸 Yêu cầu rút tiền CTV: ' . $user->display_name . ' - ' . vs_money( $s['available'] ) . "\n" . implode( ' / ', $bank ) );
	wp_mail( vs_opt( 'notify_email' ), '[CTV] Yêu cầu rút ' . vs_money( $s['available'] ), $user->display_name . "\n" . implode( "\n", $bank ) . "\n\n" . admin_url( 'edit.php?post_type=vs_payout' ) );
	vs_back( 'payout_ok', $back );
} );

function vs_payout_labels() {
	return array( 'pending' => 'Chờ chuyển khoản', 'paid' => 'Đã chuyển', 'rejected' => 'Từ chối' );
}

add_filter( 'manage_vs_payout_posts_columns', function () {
	return array( 'cb' => '<input type="checkbox">', 'title' => 'Cộng tác viên', 'vs_amount' => 'Số tiền', 'vs_bank' => 'Tài khoản nhận', 'vs_pstatus' => 'Trạng thái', 'date' => 'Ngày yêu cầu' );
} );
add_action( 'manage_vs_payout_posts_custom_column', function ( $col, $id ) {
	$labels = vs_payout_labels();
	if ( 'vs_amount' === $col ) {
		echo '<strong>' . esc_html( vs_money( vs_meta( $id, 'amount' ) ) ) . '</strong>';
	} elseif ( 'vs_bank' === $col ) {
		echo esc_html( implode( ' · ', (array) vs_meta( $id, 'bank', array() ) ) );
	} elseif ( 'vs_pstatus' === $col ) {
		$st = vs_meta( $id, 'status', 'pending' );
		echo esc_html( isset( $labels[ $st ] ) ? $labels[ $st ] : $st );
	}
}, 10, 2 );

add_action( 'add_meta_boxes_vs_payout', function () {
	add_meta_box( 'vs_payout_box', 'Xử lý yêu cầu', function ( $post ) {
		wp_nonce_field( 'vs_payout_save', 'vs_payout_nonce' );
		$u    = get_userdata( (int) vs_meta( $post->ID, 'user' ) );
		$bank = (array) vs_meta( $post->ID, 'bank', array() );
		echo '<p><strong>Cộng tác viên:</strong> ' . esc_html( $u ? $u->display_name . ' (' . vs_user_phone( $u->ID ) . ')' : '—' ) . '</p>';
		echo '<p><strong>Số tiền:</strong> ' . esc_html( vs_money( vs_meta( $post->ID, 'amount' ) ) ) . '</p>';
		echo '<p><strong>Chuyển đến:</strong> ' . esc_html( implode( ' · ', $bank ) ) . '</p>';
		echo '<p><select name="vs_pstatus">';
		foreach ( vs_payout_labels() as $k => $l ) {
			printf( '<option value="%s"%s>%s</option>', esc_attr( $k ), selected( vs_meta( $post->ID, 'status', 'pending' ), $k, false ), esc_html( $l ) );
		}
		echo '</select> <button class="button button-primary">Lưu</button></p>';
	}, 'vs_payout', 'normal', 'high' );
} );
add_action( 'save_post_vs_payout', function ( $id ) {
	if ( isset( $_POST['vs_payout_nonce'] ) && wp_verify_nonce( $_POST['vs_payout_nonce'], 'vs_payout_save' ) && current_user_can( 'edit_post', $id ) ) {
		$st = sanitize_key( isset( $_POST['vs_pstatus'] ) ? $_POST['vs_pstatus'] : 'pending' );
		if ( isset( vs_payout_labels()[ $st ] ) ) {
			update_post_meta( $id, '_vs_status', $st );
		}
	}
} );

/* ---------- Trang tổng hợp cộng tác viên trong quản trị ---------- */

add_action( 'admin_menu', function () {
	add_submenu_page( 'edit.php?post_type=vs_order', 'Cộng tác viên', 'Cộng tác viên', 'edit_posts', 'vs-affiliates', function () {
		$users = get_users( array( 'meta_key' => '_vs_aff_code', 'number' => 500 ) );
		$rows  = array();
		foreach ( $users as $u ) {
			$s = vs_aff_stats( $u->ID );
			if ( $s['clicks'] || $s['orders'] || $s['customers'] ) {
				$rows[] = array( $u, $s );
			}
		}
		usort( $rows, function ( $a, $b ) { return $b[1]['approved'] - $a[1]['approved']; } );
		echo '<div class="wrap"><h1>Cộng tác viên</h1><p>Hoa hồng hiện tại: <strong>' . esc_html( vs_opt( 'aff_pct' ) ) . '%</strong> · Ưu đãi khách được giới thiệu: <strong>' . esc_html( vs_opt( 'ref_discount' ) ) . '%</strong> · Đổi trong Cài đặt & Bảng giá.</p>';
		echo '<table class="widefat striped"><thead><tr><th>Cộng tác viên</th><th>Mã</th><th>Lượt click</th><th>Khách đăng ký</th><th>Đơn</th><th>Chờ duyệt</th><th>Đã duyệt</th><th>Đã trả</th><th>Có thể rút</th></tr></thead><tbody>';
		if ( ! $rows ) {
			echo '<tr><td colspan="9">Chưa có hoạt động giới thiệu nào.</td></tr>';
		}
		foreach ( $rows as $r ) {
			list( $u, $s ) = $r;
			printf(
				'<tr><td><a href="%s">%s</a><br><small>%s</small></td><td><code>%s</code></td><td>%d</td><td>%d</td><td>%d</td><td>%s</td><td>%s</td><td>%s</td><td><strong>%s</strong></td></tr>',
				esc_url( get_edit_user_link( $u->ID ) ),
				esc_html( $u->display_name ),
				esc_html( vs_user_phone( $u->ID ) ),
				esc_html( vs_aff_code( $u->ID ) ),
				$s['clicks'],
				$s['customers'],
				$s['orders'],
				esc_html( vs_money( $s['pending'] ) ),
				esc_html( vs_money( $s['approved'] ) ),
				esc_html( vs_money( $s['paid'] ) ),
				esc_html( vs_money( $s['available'] ) )
			);
		}
		echo '</tbody></table></div>';
	} );
}, 20 );
