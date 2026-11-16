<?php
/**
 * Tự hủy đơn "Chờ thanh toán" quá 30 phút (đồng bộ toàn web: website, /quan-tri/, app., WordPress).
 *
 * - Quét mỗi phút theo cron, và quét thêm khi có người truy cập (sau khi trang đã gửi xong) để không phụ thuộc cron.
 * - Trước khi hủy: đối soát ngân hàng thêm 1 lần (nếu đã cấu hình SePay) để không hủy nhầm đơn vừa chuyển khoản.
 * - Không tự hủy đơn khách đã bấm "Tôi đã chuyển khoản" (chờ chuyên viên đối soát).
 * - Khách chuyển khoản sau khi đơn đã tự hủy: đơn tự khôi phục và chuyển sang "Đang xử lý" (xem vs_mark_paid).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Số phút chờ thanh toán trước khi tự hủy (đổi được qua filter vs_pay_timeout_min). */
function vs_pay_timeout_min() {
	return max( 5, (int) apply_filters( 'vs_pay_timeout_min', 30 ) );
}

/** Thời điểm hết hạn thanh toán của đơn (timestamp). */
function vs_pay_expires( $id ) {
	return (int) get_post_time( 'U', true, $id ) + vs_pay_timeout_min() * MINUTE_IN_SECONDS;
}

/** Quét và hủy các đơn quá hạn. Trả về số đơn đã hủy. */
function vs_autocancel_sweep() {
	if ( get_transient( 'vs_ac_running' ) ) {
		return 0;
	}
	set_transient( 'vs_ac_running', 1, 120 );
	$before = gmdate( 'Y-m-d H:i:s', time() - vs_pay_timeout_min() * MINUTE_IN_SECONDS );
	$ids    = get_posts( array(
		'post_type'      => 'vs_order',
		'post_status'    => 'publish',
		'posts_per_page' => 50,
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'meta_key'       => '_vs_status',
		'meta_value'     => 'cho_thanh_toan',
		'date_query'     => array( array( 'column' => 'post_date_gmt', 'before' => $before ) ),
	) );
	if ( ! $ids ) {
		delete_transient( 'vs_ac_running' );
		return 0;
	}
	// Đối soát ngân hàng lần cuối trước khi hủy.
	if ( function_exists( 'vs_auto_cfg' ) && vs_auto_cfg( 'sepay_token' ) && function_exists( 'vs_sepay_pull' ) ) {
		vs_sepay_pull( true );
	}
	$n = 0;
	foreach ( $ids as $id ) {
		if ( 'cho_thanh_toan' !== vs_meta( $id, 'status' ) || get_post_meta( $id, '_vs_claimed_paid', true ) ) {
			continue; // Vừa nhận tiền, hoặc khách báo đã chuyển khoản: để chuyên viên xử lý.
		}
		update_post_meta( $id, '_vs_status', 'huy' );
		update_post_meta( $id, '_vs_auto_cancel', time() );
		delete_post_meta( $id, '_vs_auto_pending' );
		if ( function_exists( 'vs_auto_log' ) ) {
			vs_auto_log( 'Tự hủy: quá ' . vs_pay_timeout_min() . ' phút chưa thanh toán.', $id );
		}
		do_action( 'vs_order_status', $id, 'huy', 'cho_thanh_toan' );
		$n++;
	}
	delete_transient( 'vs_ac_running' );
	return $n;
}

/* Cron mỗi phút (dùng chung lịch vs_auto_tick của hệ thống tự động). */
add_action( 'vs_auto_tick', 'vs_autocancel_sweep', 3 );

/* Dự phòng khi cron không chạy: tối đa 1 lần/phút, chạy sau khi trang đã gửi cho người xem. */
add_action( 'init', function () {
	if ( wp_doing_cron() || get_transient( 'vs_ac_tick' ) ) {
		return;
	}
	set_transient( 'vs_ac_tick', 1, MINUTE_IN_SECONDS );
	add_action( 'shutdown', function () {
		if ( function_exists( 'fastcgi_finish_request' ) ) {
			fastcgi_finish_request();
		}
		vs_autocancel_sweep();
	}, 100 );
}, 50 );

/* Khi dò giao dịch: tính cả đơn vừa tự hủy (6 giờ qua) để nhận được tiền chuyển trễ. */
add_action( 'vs_auto_tick', function () {
	if ( ! function_exists( 'vs_auto_cfg' ) || ! vs_auto_cfg( 'sepay_token' ) ) {
		return;
	}
	$late = get_posts( array(
		'post_type'      => 'vs_order',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
		'meta_query'     => array( // phpcs:ignore
			array( 'key' => '_vs_status', 'value' => 'huy' ),
			array( 'key' => '_vs_auto_cancel', 'value' => time() - 6 * HOUR_IN_SECONDS, 'compare' => '>', 'type' => 'NUMERIC' ),
		),
	) );
	if ( $late && ! get_transient( 'vs_ac_late_pull' ) ) {
		set_transient( 'vs_ac_late_pull', 1, 3 * MINUTE_IN_SECONDS );
		vs_sepay_pull();
	}
}, 6 );
