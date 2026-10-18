<?php
/**
 * Tài khoản khách hàng: đăng ký, đăng nhập, hồ sơ, đổi mật khẩu, danh sách đơn.
 * Trang hiển thị: page-templates/account.php (slug tai-khoan).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ---------- Khách hàng không thấy thanh quản trị, không vào wp-admin ---------- */

add_filter( 'show_admin_bar', function ( $show ) {
	return current_user_can( 'edit_posts' ) ? $show : false;
} );

add_action( 'admin_init', function () {
	if ( wp_doing_ajax() || current_user_can( 'edit_posts' ) ) {
		return;
	}
	$script = isset( $_SERVER['SCRIPT_NAME'] ) ? basename( $_SERVER['SCRIPT_NAME'] ) : '';
	if ( 'admin-post.php' !== $script ) {
		wp_safe_redirect( vs_page_url( 'tai-khoan' ) );
		exit;
	}
} );

/* ---------- Tiện ích ---------- */

function vs_user_phone( $uid = 0 ) {
	return (string) get_user_meta( $uid ? $uid : get_current_user_id(), '_vs_phone', true );
}

function vs_find_user_by_phone( $phone ) {
	$phone = vs_norm_phone( $phone );
	if ( ! $phone ) {
		return null;
	}
	$users = get_users( array( 'meta_key' => '_vs_phone', 'meta_value' => $phone, 'number' => 1 ) );
	return $users ? $users[0] : null;
}

/** Các đơn của khách: đơn gắn tài khoản hoặc cùng số điện thoại. */
function vs_user_orders( $uid, $limit = -1 ) {
	$phone = vs_user_phone( $uid );
	$mq    = array( 'relation' => 'OR', array( 'key' => '_vs_user', 'value' => $uid ) );
	if ( $phone ) {
		$mq[] = array( 'key' => '_vs_phone', 'value' => $phone );
	}
	return get_posts( array(
		'post_type'      => 'vs_order',
		'post_status'    => 'publish',
		'posts_per_page' => $limit,
		'meta_query'     => $mq,
		'fields'         => 'ids',
	) );
}

/** Đơn có thuộc tài khoản hiện tại không. */
function vs_user_owns_order( $order_id, $uid = 0 ) {
	$uid = $uid ? $uid : get_current_user_id();
	if ( ! $uid ) {
		return false;
	}
	if ( (int) vs_meta( $order_id, 'user' ) === $uid ) {
		return true;
	}
	$phone = vs_user_phone( $uid );
	return $phone && vs_meta( $order_id, 'phone' ) === $phone;
}

function vs_account_url( $tab = '', $args = array() ) {
	if ( $tab ) {
		$args = array( 'tab' => $tab ) + $args;
	}
	return vs_page_url( 'tai-khoan', $args );
}

/** Quay lại trang với thông báo. */
function vs_back( $msg, $url = '' ) {
	$url = $url ? $url : wp_get_referer();
	$url = $url ? $url : vs_account_url();
	wp_safe_redirect( add_query_arg( 'vs_msg', $msg, remove_query_arg( 'vs_msg', $url ) ) );
	exit;
}

function vs_messages() {
	return array(
		'login_fail'   => array( 'err', 'Thông tin đăng nhập không đúng. Vui lòng thử lại.' ),
		'login_empty'  => array( 'err', 'Vui lòng nhập email/số điện thoại và mật khẩu.' ),
		'reg_name'     => array( 'err', 'Vui lòng nhập họ tên.' ),
		'reg_phone'    => array( 'err', 'Số điện thoại không hợp lệ.' ),
		'reg_phone_ex' => array( 'err', 'Số điện thoại này đã có tài khoản. Hãy đăng nhập.' ),
		'reg_email'    => array( 'err', 'Email không hợp lệ.' ),
		'reg_email_ex' => array( 'err', 'Email này đã có tài khoản. Hãy đăng nhập.' ),
		'reg_pass'     => array( 'err', 'Mật khẩu cần ít nhất 8 ký tự.' ),
		'reg_terms'    => array( 'err', 'Vui lòng đồng ý với điều khoản dịch vụ.' ),
		'reg_fail'     => array( 'err', 'Không tạo được tài khoản, vui lòng thử lại.' ),
		'welcome'      => array( 'ok', 'Chào mừng bạn! Tài khoản đã được tạo thành công.' ),
		'profile_ok'   => array( 'ok', 'Đã lưu thông tin tài khoản.' ),
		'pass_ok'      => array( 'ok', 'Đã đổi mật khẩu.' ),
		'pass_wrong'   => array( 'err', 'Mật khẩu hiện tại không đúng.' ),
		'payout_ok'    => array( 'ok', 'Đã gửi yêu cầu rút tiền. Chúng tôi sẽ chuyển khoản trong 1-3 ngày làm việc.' ),
		'payout_min'   => array( 'err', 'Số dư chưa đạt mức tối thiểu để rút.' ),
		'payout_bank'  => array( 'err', 'Vui lòng cập nhật thông tin ngân hàng trước khi rút tiền.' ),
		'payout_pend'  => array( 'err', 'Bạn đang có một yêu cầu rút tiền chờ xử lý.' ),
		'too_many'     => array( 'err', 'Bạn thao tác quá nhiều lần, vui lòng thử lại sau ít phút.' ),
		'bad_nonce'    => array( 'err', 'Phiên làm việc đã hết hạn, vui lòng thử lại.' ),
	);
}

function vs_notice() {
	if ( empty( $_GET['vs_msg'] ) ) {
		return;
	}
	$all = vs_messages();
	$key = sanitize_key( $_GET['vs_msg'] );
	if ( isset( $all[ $key ] ) ) {
		printf( '<div class="notice notice--%s" role="status">%s</div>', esc_attr( $all[ $key ][0] ), esc_html( $all[ $key ][1] ) );
	}
}

function vs_check_form_nonce( $action ) {
	if ( ! isset( $_POST['_vsn'] ) || ! wp_verify_nonce( $_POST['_vsn'], $action ) ) {
		vs_back( 'bad_nonce' );
	}
}

function vs_soft_limit( $bucket, $max ) {
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : 'x';
	$key = 'vs_rl_' . $bucket . '_' . md5( $ip );
	$n   = (int) get_transient( $key );
	if ( $n >= $max ) {
		vs_back( 'too_many' );
	}
	set_transient( $key, $n + 1, 15 * MINUTE_IN_SECONDS );
}

/* ---------- Đăng nhập ---------- */

add_action( 'admin_post_nopriv_vs_login', 'vs_handle_login' );
add_action( 'admin_post_vs_login', 'vs_handle_login' );
function vs_handle_login() {
	vs_check_form_nonce( 'vs_login' );
	vs_soft_limit( 'login', 10 );
	$id   = trim( (string) wp_unslash( isset( $_POST['login'] ) ? $_POST['login'] : '' ) );
	$pass = (string) wp_unslash( isset( $_POST['password'] ) ? $_POST['password'] : '' );
	if ( '' === $id || '' === $pass ) {
		vs_back( 'login_empty' );
	}
	// Cho phép đăng nhập bằng số điện thoại.
	if ( ! is_email( $id ) && preg_match( '/^[\d\s.+]+$/', $id ) ) {
		$u  = vs_find_user_by_phone( $id );
		$id = $u ? $u->user_login : $id;
	}
	$user = wp_signon( array(
		'user_login'    => $id,
		'user_password' => $pass,
		'remember'      => ! empty( $_POST['remember'] ),
	), is_ssl() );
	if ( is_wp_error( $user ) ) {
		vs_back( 'login_fail' );
	}
	$to = isset( $_POST['redirect_to'] ) ? wp_validate_redirect( wp_unslash( $_POST['redirect_to'] ), vs_account_url() ) : vs_account_url();
	wp_safe_redirect( $to );
	exit;
}

/* ---------- Đăng ký ---------- */

add_action( 'admin_post_nopriv_vs_register', 'vs_handle_register' );
add_action( 'admin_post_vs_register', 'vs_handle_register' );
function vs_handle_register() {
	vs_check_form_nonce( 'vs_register' );
	if ( ! empty( $_POST['website'] ) ) {
		vs_back( 'reg_fail' );
	}
	vs_soft_limit( 'register', 5 );
	$p     = wp_unslash( $_POST );
	$name  = sanitize_text_field( isset( $p['name'] ) ? $p['name'] : '' );
	$phone = vs_norm_phone( isset( $p['phone'] ) ? $p['phone'] : '' );
	$email = sanitize_email( isset( $p['email'] ) ? $p['email'] : '' );
	$pass  = (string) ( isset( $p['password'] ) ? $p['password'] : '' );

	if ( mb_strlen( $name ) < 2 ) {
		vs_back( 'reg_name' );
	}
	if ( ! preg_match( '/^0\d{9,10}$/', $phone ) ) {
		vs_back( 'reg_phone' );
	}
	if ( vs_find_user_by_phone( $phone ) ) {
		vs_back( 'reg_phone_ex' );
	}
	if ( ! is_email( $email ) ) {
		vs_back( 'reg_email' );
	}
	if ( email_exists( $email ) ) {
		vs_back( 'reg_email_ex' );
	}
	if ( strlen( $pass ) < 8 ) {
		vs_back( 'reg_pass' );
	}
	if ( empty( $p['terms'] ) ) {
		vs_back( 'reg_terms' );
	}

	$login = sanitize_user( 'kh' . $phone, true );
	$uid   = wp_insert_user( array(
		'user_login'   => $login,
		'user_email'   => $email,
		'user_pass'    => $pass,
		'display_name' => $name,
		'first_name'   => $name,
		'role'         => 'subscriber',
	) );
	if ( is_wp_error( $uid ) ) {
		vs_back( 'reg_fail' );
	}
	update_user_meta( $uid, '_vs_phone', $phone );
	vs_aff_code( $uid ); // Tạo sẵn mã giới thiệu.

	// Ghi nhận người giới thiệu (nếu vào qua link cộng tác viên).
	$ref = vs_ref_user_from_cookie();
	if ( $ref && $ref !== $uid ) {
		update_user_meta( $uid, '_vs_referred_by', $ref );
	}

	// Gắn các đơn đặt trước đây (khi chưa có tài khoản) cùng số điện thoại.
	foreach ( get_posts( array( 'post_type' => 'vs_order', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_key' => '_vs_phone', 'meta_value' => $phone ) ) as $oid ) {
		if ( ! vs_meta( $oid, 'user' ) ) {
			update_post_meta( $oid, '_vs_user', $uid );
		}
	}

	wp_set_current_user( $uid );
	wp_set_auth_cookie( $uid, true, is_ssl() );
	wp_mail( $email, 'Chào mừng bạn đến với ' . get_bloginfo( 'name' ), "Chào $name,\n\nTài khoản của bạn đã được tạo.\nĐăng nhập tại: " . vs_account_url() . "\n\nMã giới thiệu của bạn: " . vs_aff_code( $uid ) . "\nChia sẻ link để nhận hoa hồng: " . vs_aff_link( $uid ) );
	wp_safe_redirect( add_query_arg( 'vs_msg', 'welcome', vs_account_url() ) );
	exit;
}

/* ---------- Cập nhật hồ sơ ---------- */

add_action( 'admin_post_vs_profile', function () {
	vs_check_form_nonce( 'vs_profile' );
	$uid   = get_current_user_id();
	$p     = wp_unslash( $_POST );
	$name  = sanitize_text_field( isset( $p['name'] ) ? $p['name'] : '' );
	$phone = vs_norm_phone( isset( $p['phone'] ) ? $p['phone'] : '' );
	$email = sanitize_email( isset( $p['email'] ) ? $p['email'] : '' );
	$back  = vs_account_url( 'thong-tin' );

	if ( mb_strlen( $name ) < 2 ) {
		vs_back( 'reg_name', $back );
	}
	if ( ! preg_match( '/^0\d{9,10}$/', $phone ) ) {
		vs_back( 'reg_phone', $back );
	}
	$other = vs_find_user_by_phone( $phone );
	if ( $other && (int) $other->ID !== $uid ) {
		vs_back( 'reg_phone_ex', $back );
	}
	if ( ! is_email( $email ) ) {
		vs_back( 'reg_email', $back );
	}
	$owner = email_exists( $email );
	if ( $owner && (int) $owner !== $uid ) {
		vs_back( 'reg_email_ex', $back );
	}
	wp_update_user( array( 'ID' => $uid, 'display_name' => $name, 'first_name' => $name, 'user_email' => $email ) );
	update_user_meta( $uid, '_vs_phone', $phone );
	foreach ( array( 'bank_name', 'bank_account', 'bank_holder' ) as $k ) {
		update_user_meta( $uid, '_vs_' . $k, sanitize_text_field( isset( $p[ $k ] ) ? $p[ $k ] : '' ) );
	}
	vs_back( 'profile_ok', $back );
} );

add_action( 'admin_post_vs_password', function () {
	vs_check_form_nonce( 'vs_password' );
	$user = wp_get_current_user();
	$back = vs_account_url( 'thong-tin' );
	$cur  = (string) wp_unslash( isset( $_POST['current'] ) ? $_POST['current'] : '' );
	$new  = (string) wp_unslash( isset( $_POST['new'] ) ? $_POST['new'] : '' );
	if ( ! wp_check_password( $cur, $user->user_pass, $user->ID ) ) {
		vs_back( 'pass_wrong', $back );
	}
	if ( strlen( $new ) < 8 ) {
		vs_back( 'reg_pass', $back );
	}
	wp_set_password( $new, $user->ID );
	wp_set_auth_cookie( $user->ID, true, is_ssl() );
	vs_back( 'pass_ok', $back );
} );

/* Đăng xuất xong quay về trang chủ. */
add_filter( 'logout_redirect', function ( $to, $requested, $user ) {
	return ( $user && ! is_wp_error( $user ) && user_can( $user, 'edit_posts' ) ) ? $to : home_url( '/' );
}, 10, 3 );

/* ---------- Cột bổ sung trong Quản trị > Người dùng ---------- */

add_filter( 'manage_users_columns', function ( $cols ) {
	$cols['vs_phone']  = 'Điện thoại';
	$cols['vs_orders'] = 'Đơn hàng';
	$cols['vs_aff']    = 'Mã giới thiệu';
	return $cols;
} );
add_filter( 'manage_users_custom_column', function ( $out, $col, $uid ) {
	switch ( $col ) {
		case 'vs_phone':
			return esc_html( vs_user_phone( $uid ) );
		case 'vs_orders':
			return (string) count( vs_user_orders( $uid ) );
		case 'vs_aff':
			return esc_html( get_user_meta( $uid, '_vs_aff_code', true ) );
	}
	return $out;
}, 10, 3 );
