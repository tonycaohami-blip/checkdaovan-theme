<?php
/**
 * Thông báo trong website.
 *
 * - Khách hàng: lưu trong user meta _vs_notices (tối đa 60 thông báo), hiện ở chuông trên đầu trang
 *   và tab "Thông báo" trong tài khoản. Tạo khi: đặt đơn, nhận thanh toán, có kết quả, hoàn thành, hủy, rút tiền CTV.
 * - Quản trị: lưu trong option vs_admin_notices (tối đa 200), hiện ở trang Quản trị đơn hàng (/quan-tri/)
 *   và thanh quản trị. Tạo từ mọi sự kiện đang gửi Telegram (đơn mới, thanh toán, cần xử lý tay...).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* =========================================================
 * Lưu trữ
 * ======================================================= */

/** Thêm thông báo cho khách. */
function vs_notify_user( $uid, $type, $title, $text = '', $url = '', $key = '' ) {
	$uid = (int) $uid;
	if ( ! $uid || ! get_userdata( $uid ) ) {
		return;
	}
	$list = (array) get_user_meta( $uid, '_vs_notices', true );
	if ( $key ) {
		foreach ( $list as $n ) {
			if ( isset( $n['key'] ) && $n['key'] === $key ) {
				return; // Đã báo sự kiện này rồi.
			}
		}
	}
	array_unshift( $list, array(
		'id'    => substr( md5( uniqid( '', true ) ), 0, 10 ),
		'key'   => $key,
		't'     => time(),
		'type'  => $type,
		'title' => $title,
		'text'  => $text,
		'url'   => $url,
		'read'  => 0,
	) );
	update_user_meta( $uid, '_vs_notices', array_slice( $list, 0, 60 ) );
}

function vs_user_notices( $uid ) {
	return array_values( array_filter( (array) get_user_meta( (int) $uid, '_vs_notices', true ), 'is_array' ) );
}

function vs_user_unread( $uid ) {
	return count( array_filter( vs_user_notices( $uid ), function ( $n ) {
		return empty( $n['read'] );
	} ) );
}

/** Thêm thông báo quản trị. */
function vs_notify_admin( $type, $title, $text = '', $order_id = 0 ) {
	$list = (array) get_option( 'vs_admin_notices', array() );
	array_unshift( $list, array(
		'id'    => substr( md5( uniqid( '', true ) ), 0, 10 ),
		't'     => time(),
		'type'  => $type,
		'title' => $title,
		'text'  => $text,
		'order' => (int) $order_id,
	) );
	update_option( 'vs_admin_notices', array_slice( $list, 0, 200 ), false );
}

function vs_admin_notices() {
	return array_values( array_filter( (array) get_option( 'vs_admin_notices', array() ), 'is_array' ) );
}

/** Quản trị viên: thông báo mới hơn lần xem cuối. */
function vs_admin_unread( $uid = 0 ) {
	$seen = (int) get_user_meta( $uid ? $uid : get_current_user_id(), '_vs_admin_seen', true );
	return count( array_filter( vs_admin_notices(), function ( $n ) use ( $seen ) {
		return (int) $n['t'] > $seen;
	} ) );
}

function vs_can_manage_orders() {
	return current_user_can( 'manage_options' ) || current_user_can( 'edit_others_posts' );
}

/** Thời gian dạng "5 phút trước". */
function vs_time_ago( $t ) {
	$d = time() - (int) $t;
	if ( $d < 60 ) {
		return 'Vừa xong';
	}
	if ( $d < HOUR_IN_SECONDS ) {
		return floor( $d / 60 ) . ' phút trước';
	}
	if ( $d < DAY_IN_SECONDS ) {
		return floor( $d / HOUR_IN_SECONDS ) . ' giờ trước';
	}
	if ( $d < 7 * DAY_IN_SECONDS ) {
		return floor( $d / DAY_IN_SECONDS ) . ' ngày trước';
	}
	return wp_date( 'd/m/Y', $t );
}

/* =========================================================
 * Sự kiện -> thông báo khách
 * ======================================================= */

function vs_order_link_for_user( $id ) {
	return vs_account_url( 'don-hang', array( 'don' => vs_meta( $id, 'code' ) ) ) . '#' . vs_meta( $id, 'code' );
}

add_action( 'vs_order_created', function ( $id ) {
	$uid = (int) vs_meta( $id, 'user' );
	if ( $uid ) {
		vs_notify_user( $uid, 'order', 'Đã tạo đơn ' . vs_meta( $id, 'code' ), vs_order_summary( $id ) . ' - ' . vs_money( vs_meta( $id, 'price' ) ) . '. Vui lòng thanh toán để bắt đầu xử lý.', vs_order_link_for_user( $id ), 'created-' . $id );
	}
}, 20 );

add_action( 'vs_order_status', function ( $id, $status, $old ) {
	$uid  = (int) vs_meta( $id, 'user' );
	$code = vs_meta( $id, 'code' );
	if ( ! $uid || $status === $old ) {
		return;
	}
	if ( 'dang_xu_ly' === $status ) {
		$text = 'check' === vs_meta( $id, 'type' ) ? 'Hệ thống đang kiểm tra, kết quả sẽ có trong ít phút.' : 'Biên tập viên đang xử lý đơn của bạn.';
		vs_notify_user( $uid, 'paid', 'Đã nhận thanh toán đơn ' . $code, $text, vs_order_link_for_user( $id ), 'paid-' . $id );
	} elseif ( 'hoan_thanh' === $status ) {
		vs_notify_user( $uid, 'done', 'Đơn ' . $code . ' đã hoàn thành', 'Kết quả và báo cáo đã sẵn sàng để tải xuống.', vs_order_link_for_user( $id ), 'done-' . $id );
	} elseif ( 'huy' === $status ) {
		vs_notify_user( $uid, 'cancel', 'Đơn ' . $code . ' đã bị hủy', 'Liên hệ hỗ trợ nếu bạn cần thêm thông tin.', vs_order_link_for_user( $id ), 'cancel-' . $id );
	}
}, 20, 3 );

add_action( 'vs_auto_job_done', function ( $id, $job ) {
	$uid = (int) vs_meta( $id, 'user' );
	if ( ! $uid || 'hoan_thanh' === vs_meta( $id, 'status' ) ) {
		return;
	}
	$tool = isset( $job['tool'], vs_pricing()['tools'][ $job['tool'] ] ) ? vs_pricing()['tools'][ $job['tool'] ]['name'] : 'phần mềm';
	$kind = isset( $job['kind'] ) && 'ai' === $job['kind'] ? 'tỷ lệ AI' : 'tỷ lệ đạo văn';
	vs_notify_user( $uid, 'result', 'Đã có ' . $kind . ' ' . $tool . ' - đơn ' . vs_meta( $id, 'code' ), 'Xem kết quả và tải báo cáo ngay trong đơn hàng.', vs_order_link_for_user( $id ), 'job-' . $id . '-' . ( isset( $job['tool'] ) ? $job['tool'] : '' ) . '-' . ( isset( $job['kind'] ) ? $job['kind'] : '' ) );
}, 20, 2 );

/* Rút tiền cộng tác viên: báo khi trạng thái yêu cầu thay đổi. */
add_action( 'updated_post_meta', function ( $meta_id, $pid, $key, $value ) {
	if ( '_vs_status' !== $key || 'vs_payout' !== get_post_type( $pid ) ) {
		return;
	}
	$uid = (int) get_post_meta( $pid, '_vs_user', true );
	$amt = vs_money( (int) get_post_meta( $pid, '_vs_amount', true ) );
	if ( 'paid' === $value ) {
		vs_notify_user( $uid, 'money', 'Đã chuyển tiền hoa hồng ' . $amt, 'Vui lòng kiểm tra tài khoản ngân hàng của bạn.', vs_account_url( 'cong-tac-vien' ), 'payout-paid-' . $pid );
	} elseif ( 'rejected' === $value ) {
		vs_notify_user( $uid, 'cancel', 'Yêu cầu rút tiền chưa được duyệt', 'Liên hệ hỗ trợ để biết thêm chi tiết.', vs_account_url( 'cong-tac-vien' ), 'payout-rej-' . $pid );
	}
}, 10, 4 );

/* =========================================================
 * Sự kiện -> thông báo quản trị (dùng lại nội dung Telegram)
 * ======================================================= */

add_action( 'vs_telegram', function ( $text ) {
	$lines = array_values( array_filter( array_map( 'trim', explode( "\n", (string) $text ) ) ) );
	if ( ! $lines ) {
		return;
	}
	// Bỏ dòng chứa đường dẫn quản trị (trang quản trị có nút mở đơn riêng).
	$lines = array_values( array_filter( $lines, function ( $l ) {
		return false === strpos( $l, 'wp-admin/' );
	} ) );
	$title = array_shift( $lines );
	$order = 0;
	if ( preg_match( '/\bVS\d{4}[A-Z0-9]{4}\b/', (string) $text, $m ) ) {
		$found = get_posts( array( 'post_type' => 'vs_order', 'post_status' => 'any', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_key' => '_vs_code', 'meta_value' => $m[0] ) );
		$order = $found ? (int) $found[0] : 0;
	}
	$type = 'info';
	foreach ( array( '🆕' => 'new', '💰' => 'paid', '✅' => 'done', '📄' => 'result', '⚠️' => 'warn', '🖐' => 'warn', '✍️' => 'warn', '🔔' => 'bell', '💸' => 'money' ) as $icon => $t ) {
		if ( 0 === strpos( $title, $icon ) ) {
			$type = $t;
			break;
		}
	}
	vs_notify_admin( $type, $title, implode( ' · ', $lines ), $order );
} );

/* =========================================================
 * AJAX
 * ======================================================= */

function vs_notice_out( $n ) {
	return array(
		'id'    => $n['id'],
		'type'  => $n['type'],
		'title' => $n['title'],
		'text'  => $n['text'],
		'url'   => isset( $n['url'] ) ? $n['url'] : '',
		'order' => isset( $n['order'] ) ? (int) $n['order'] : 0,
		'ago'   => vs_time_ago( $n['t'] ),
		't'     => (int) $n['t'],
		'read'  => ! empty( $n['read'] ),
	);
}

/* Khách: lấy thông báo / đánh dấu đã đọc */
add_action( 'wp_ajax_vs_notices', function () {
	nocache_headers();
	$uid  = get_current_user_id();
	$list = vs_user_notices( $uid );
	if ( ! empty( $_POST['mark'] ) ) {
		$mark = sanitize_text_field( wp_unslash( $_POST['mark'] ) );
		foreach ( $list as &$n ) {
			if ( 'all' === $mark || $n['id'] === $mark ) {
				$n['read'] = 1;
			}
		}
		unset( $n );
		update_user_meta( $uid, '_vs_notices', $list );
	}
	wp_send_json_success( array(
		'unread' => count( array_filter( $list, function ( $n ) {
			return empty( $n['read'] );
		} ) ),
		'items'  => array_map( 'vs_notice_out', array_slice( $list, 0, 15 ) ),
	) );
} );

/* =========================================================
 * Chuông thông báo trên đầu trang (khách đã đăng nhập)
 * ======================================================= */

function vs_bell_html() {
	if ( ! is_user_logged_in() ) {
		return '';
	}
	$n = vs_user_unread( get_current_user_id() );
	ob_start();
	?>
	<div class="bell" data-bell>
		<button type="button" class="bell__btn" aria-label="Thông báo" aria-expanded="false" data-bell-toggle>
			<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
			<span class="bell__count" data-bell-count<?php echo $n ? '' : ' hidden'; ?>><?php echo (int) min( 99, $n ); ?></span>
		</button>
		<div class="bell__panel" data-bell-panel hidden>
			<div class="bell__head"><b>Thông báo</b><button type="button" class="bell__all" data-bell-readall>Đánh dấu đã đọc</button></div>
			<div class="bell__list" data-bell-list><div class="bell__empty">Đang tải…</div></div>
			<a class="bell__more" href="<?php echo esc_url( vs_account_url( 'thong-bao' ) ); ?>">Xem tất cả thông báo</a>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

/* =========================================================
 * Thanh quản trị WordPress: lối tắt + số thông báo mới
 * ======================================================= */

add_action( 'admin_bar_menu', function ( $bar ) {
	if ( ! vs_can_manage_orders() ) {
		return;
	}
	$n = vs_admin_unread();
	$bar->add_node( array(
		'id'    => 'vs-orders',
		'title' => '📋 Quản trị đơn hàng' . ( $n ? ' <span class="vs-ab-count" style="display:inline-block;min-width:18px;padding:0 5px;border-radius:9px;background:#d63638;color:#fff;font-size:11px;line-height:18px;text-align:center">' . (int) $n . '</span>' : '' ),
		'href'  => vs_page_url( 'quan-tri' ),
	) );
}, 80 );
