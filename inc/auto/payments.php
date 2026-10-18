<?php
/**
 * Tự xác nhận thanh toán chuyển khoản qua webhook của SePay hoặc Casso.
 *
 * SePay:  Cấu hình webhook URL  {site}/wp-json/vansach/v1/pay/sepay
 *         Kiểu chứng thực "API Key" -> SePay gửi header "Authorization: Apikey <KEY>".
 * Casso:  Cấu hình webhook URL  {site}/wp-json/vansach/v1/pay/casso
 *         Khóa bảo mật (secure token) -> Casso gửi header "Secure-Token: <TOKEN>".
 *
 * Đơn được khớp theo mã đơn (VSxxxxxxxx) trong nội dung chuyển khoản và số tiền >= giá đơn.
 * Hãy đối chiếu tên trường với tài liệu webhook hiện hành của nhà cung cấp trước khi chạy thật.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'rest_api_init', function () {
	register_rest_route( 'vansach/v1', '/pay/(?P<gw>sepay|casso)', array(
		'methods'             => 'POST',
		'callback'            => 'vs_pay_webhook',
		'permission_callback' => '__return_true', // Chứng thực bằng khóa trong header, kiểm tra trong callback.
	) );
} );

function vs_pay_webhook( WP_REST_Request $req ) {
	$gw = $req['gw'];
	if ( 'sepay' === $gw ) {
		$key  = (string) vs_auto_cfg( 'sepay_key' );
		$auth = trim( (string) $req->get_header( 'authorization' ) );
		if ( ! $key || ! hash_equals( 'Apikey ' . $key, $auth ) ) {
			return new WP_REST_Response( array( 'success' => false, 'message' => 'unauthorized' ), 401 );
		}
		$b   = (array) $req->get_json_params();
		$txs = array();
		if ( isset( $b['transferType'] ) && 'in' === $b['transferType'] ) {
			$txs[] = array(
				'id'      => 'sepay-' . ( isset( $b['id'] ) ? $b['id'] : ( isset( $b['referenceCode'] ) ? $b['referenceCode'] : md5( wp_json_encode( $b ) ) ) ),
				'content' => trim( ( isset( $b['content'] ) ? $b['content'] : '' ) . ' ' . ( isset( $b['code'] ) ? $b['code'] : '' ) . ' ' . ( isset( $b['description'] ) ? $b['description'] : '' ) ),
				'amount'  => (int) ( isset( $b['transferAmount'] ) ? $b['transferAmount'] : 0 ),
			);
		}
	} else {
		$token = (string) vs_auto_cfg( 'casso_token' );
		$sent  = (string) $req->get_header( 'secure_token' );
		if ( ! $token || ! hash_equals( $token, $sent ) ) {
			return new WP_REST_Response( array( 'error' => 1, 'message' => 'unauthorized' ), 401 );
		}
		$b   = (array) $req->get_json_params();
		$txs = array();
		foreach ( (array) ( isset( $b['data'] ) ? $b['data'] : array() ) as $t ) {
			$t = (array) $t;
			if ( (int) ( isset( $t['amount'] ) ? $t['amount'] : 0 ) > 0 ) {
				$txs[] = array(
					'id'      => 'casso-' . ( isset( $t['tid'] ) ? $t['tid'] : ( isset( $t['id'] ) ? $t['id'] : md5( wp_json_encode( $t ) ) ) ),
					'content' => (string) ( isset( $t['description'] ) ? $t['description'] : '' ),
					'amount'  => (int) $t['amount'],
				);
			}
		}
	}

	$matched = 0;
	foreach ( $txs as $tx ) {
		if ( vs_pay_match_tx( $tx, strtoupper( $gw ) ) ) {
			$matched++;
		}
	}
	return 'sepay' === $gw
		? new WP_REST_Response( array( 'success' => true, 'matched' => $matched ), 200 )
		: new WP_REST_Response( array( 'error' => 0, 'matched' => $matched ), 200 );
}

/**
 * Khớp 1 giao dịch tiền vào với đơn hàng. $tx = [id, content, amount]. Trả về true nếu đơn được xác nhận.
 */
function vs_pay_match_tx( $tx, $source ) {
	if ( get_option( 'vs_tx_' . md5( $tx['id'] ) ) ) {
		return false; // Giao dịch đã xử lý.
	}
	update_option( 'vs_tx_' . md5( $tx['id'] ), time(), false );
	// Ngân hàng thường bỏ dấu cách/ký tự đặc biệt: tìm "VS" + 8 ký tự.
	if ( ! preg_match( '/VS\s*([0-9]{4}[A-Z0-9]{4})/i', $tx['content'], $m ) ) {
		vs_auto_log( 'Giao dịch ' . vs_money( $tx['amount'] ) . ' không có mã đơn: ' . mb_substr( $tx['content'], 0, 80 ) );
		return false;
	}
	$code = 'VS' . strtoupper( $m[1] );
	$id   = vs_find_order( $code );
	if ( ! $id ) {
		vs_auto_log( 'Không tìm thấy đơn ' . $code . ' cho giao dịch ' . vs_money( $tx['amount'] ) );
		return false;
	}
	if ( $tx['amount'] < (int) vs_meta( $id, 'price' ) ) {
		vs_auto_log( 'Chuyển thiếu: nhận ' . vs_money( $tx['amount'] ) . ' / cần ' . vs_money( vs_meta( $id, 'price' ) ), $id );
		vs_telegram( '⚠️ Đơn ' . $code . ' chuyển thiếu: ' . vs_money( $tx['amount'] ) . ' / ' . vs_money( vs_meta( $id, 'price' ) ) );
		return false;
	}
	return vs_mark_paid( $id, $source, $tx['id'], $tx['amount'] );
}

/**
 * Chủ động dò giao dịch mới qua SePay API (bổ sung cho webhook - phòng khi webhook bị lỡ).
 * API: GET https://my.sepay.vn/userapi/transactions/list (Authorization: Bearer <API Token>).
 * Trả về số đơn được xác nhận, hoặc WP_Error.
 */
function vs_sepay_pull( $force = false ) {
	$token = (string) vs_auto_cfg( 'sepay_token' );
	if ( ! $token ) {
		return new WP_Error( 'cfg', 'Chưa cấu hình SePay API Token' );
	}
	if ( ! $force && get_transient( 'vs_sepay_pull' ) ) {
		return 0; // Tối đa 1 lần / 20 giây.
	}
	set_transient( 'vs_sepay_pull', 1, 20 );
	$url = add_query_arg( array(
		'account_number' => preg_replace( '/\D/', '', vs_opt( 'bank_account' ) ),
		'limit'          => 50,
	), 'https://my.sepay.vn/userapi/transactions/list' );
	$r = vs_http_json( wp_remote_get( $url, array( 'timeout' => 20, 'headers' => array( 'Authorization' => 'Bearer ' . $token, 'Content-Type' => 'application/json' ) ) ) );
	if ( is_wp_error( $r ) || 200 !== $r['code'] || ! isset( $r['body']['transactions'] ) ) {
		return vs_http_fail( $r, 'SePay API' );
	}
	$n = 0;
	foreach ( (array) $r['body']['transactions'] as $t ) {
		$t  = (array) $t;
		$in = (int) round( (float) ( isset( $t['amount_in'] ) ? $t['amount_in'] : 0 ) );
		if ( $in <= 0 ) {
			continue;
		}
		$tx = array(
			'id'      => 'sepay-' . ( isset( $t['id'] ) ? $t['id'] : md5( wp_json_encode( $t ) ) ),
			'content' => (string) ( isset( $t['transaction_content'] ) ? $t['transaction_content'] : '' ),
			'amount'  => $in,
		);
		if ( vs_pay_match_tx( $tx, 'SePay API' ) ) {
			$n++;
		}
	}
	return $n;
}

/* Mỗi phút: nếu còn đơn chờ thanh toán trong 24 giờ qua thì dò giao dịch. */
add_action( 'vs_auto_tick', function () {
	if ( ! vs_auto_cfg( 'sepay_token' ) ) {
		return;
	}
	$waiting = get_posts( array(
		'post_type'      => 'vs_order',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'meta_key'       => '_vs_status',
		'meta_value'     => 'cho_thanh_toan',
		'date_query'     => array( array( 'after' => '24 hours ago' ) ),
	) );
	if ( $waiting ) {
		vs_sepay_pull();
	}
}, 5 );

/* Khách bấm "Tôi đã chuyển khoản": dò giao dịch ngay; nếu chưa xác minh được thì báo chuyên viên. */
add_action( 'wp_ajax_vs_pay_verify', 'vs_ajax_pay_verify' );
add_action( 'wp_ajax_nopriv_vs_pay_verify', 'vs_ajax_pay_verify' );
function vs_ajax_pay_verify() {
	$id    = vs_find_order( sanitize_text_field( wp_unslash( isset( $_POST['code'] ) ? $_POST['code'] : '' ) ) );
	$phone = vs_norm_phone( wp_unslash( isset( $_POST['phone'] ) ? $_POST['phone'] : '' ) );
	if ( ! $id || ( vs_meta( $id, 'phone' ) !== $phone && ! vs_user_owns_order( $id ) ) ) {
		wp_send_json_error( array( 'message' => 'Không tìm thấy đơn hàng.' ) );
	}
	if ( 'cho_thanh_toan' !== vs_meta( $id, 'status' ) ) {
		wp_send_json_success( array( 'paid' => true, 'message' => 'Đơn đã được xác nhận thanh toán.' ) );
	}
	if ( get_transient( 'vs_verify_' . $id ) ) {
		wp_send_json_success( array( 'paid' => false, 'message' => 'Hệ thống đang đối chiếu giao dịch, vui lòng chờ thêm ít phút.' ) );
	}
	set_transient( 'vs_verify_' . $id, 1, 30 );
	if ( vs_auto_cfg( 'sepay_token' ) ) {
		vs_sepay_pull( true );
		if ( 'cho_thanh_toan' !== vs_meta( $id, 'status' ) ) {
			wp_send_json_success( array( 'paid' => true, 'message' => 'Đã xác minh thanh toán thành công!' ) );
		}
	}
	if ( ! get_post_meta( $id, '_vs_claimed_paid', true ) ) {
		update_post_meta( $id, '_vs_claimed_paid', time() );
		vs_auto_log( 'Khách báo đã chuyển khoản, chưa thấy giao dịch khớp', $id );
		vs_telegram( '🔔 Khách báo đã chuyển khoản đơn ' . vs_meta( $id, 'code' ) . ' (' . vs_money( vs_meta( $id, 'price' ) ) . ') - vui lòng kiểm tra.' . "\n" . admin_url( 'post.php?post=' . $id . '&action=edit' ) );
	}
	wp_send_json_success( array(
		'paid'    => false,
		'message' => vs_auto_pay_on()
			? 'Chưa thấy giao dịch. Ngân hàng có thể chậm vài phút - hệ thống sẽ tự cập nhật. Chuyên viên cũng đã được thông báo.'
			: 'Đã báo chuyên viên đối chiếu giao dịch. Đơn sẽ được xác nhận sớm nhất.',
	) );
}

/* Khách đang chờ trên trang có thể hỏi trạng thái thanh toán (mã đơn + SĐT). */
add_action( 'wp_ajax_vs_pay_status', 'vs_ajax_pay_status' );
add_action( 'wp_ajax_nopriv_vs_pay_status', 'vs_ajax_pay_status' );
function vs_ajax_pay_status() {
	$id    = vs_find_order( sanitize_text_field( wp_unslash( isset( $_POST['code'] ) ? $_POST['code'] : '' ) ) );
	$phone = vs_norm_phone( wp_unslash( isset( $_POST['phone'] ) ? $_POST['phone'] : '' ) );
	if ( ! $id || ( vs_meta( $id, 'phone' ) !== $phone && ! vs_user_owns_order( $id ) ) ) {
		wp_send_json_error();
	}
	// Dự phòng khi cron máy chủ không chạy: khách đang chờ trên trang cũng đẩy đơn đi tiếp.
	if ( get_post_meta( $id, '_vs_auto_pending', true ) && ! get_transient( 'vs_auto_poke_' . $id ) ) {
		set_transient( 'vs_auto_poke_' . $id, 1, 20 );
		vs_auto_process( $id );
	}
	$labels = vs_statuses();
	$st     = vs_meta( $id, 'status' );
	$view = vs_order_view( $id );
	wp_send_json_success( array( 'status' => $st, 'label' => $labels[ $st ], 'auto' => vs_auto_view( $id ), 'results' => $view['results'], 'files' => $view['files'], 'waiting' => $view['waiting'], 'upsell' => $view['upsell'] ) );
}
