<?php
/**
 * Trang "Quản trị đơn hàng" riêng cho quản trị viên (/quan-tri/, template page-templates/admin-orders.php).
 * Toàn bộ dữ liệu lấy qua AJAX bên dưới; chỉ người có quyền quản lý đơn (vs_can_manage_orders) mới dùng được.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ---------- Tài nguyên cho trang ---------- */
add_action( 'wp_enqueue_scripts', function () {
	if ( ! is_page_template( 'page-templates/admin-orders.php' ) || ! vs_can_manage_orders() ) {
		return;
	}
	wp_enqueue_script( 'vs-admin-orders', VS_URI . '/assets/js/admin-orders.js', array(), VS_VERSION, true );
	wp_localize_script( 'vs-admin-orders', 'VSA', array(
		'ajax'     => admin_url( 'admin-ajax.php' ),
		'nonce'    => wp_create_nonce( 'vs_adm' ),
		'statuses' => vs_statuses(),
		'wpAdmin'  => admin_url( 'edit.php?post_type=vs_order' ),
	) );
}, 20 );

/* Không đưa trang quản trị lên Google. */
add_filter( 'wp_robots', function ( $r ) {
	if ( is_page_template( 'page-templates/admin-orders.php' ) ) {
		$r['noindex']  = true;
		$r['nofollow'] = true;
	}
	return $r;
} );
add_filter( 'wp_sitemaps_posts_query_args', function ( $args, $type ) {
	if ( 'page' === $type ) {
		$p = get_page_by_path( 'quan-tri' );
		if ( $p ) {
			$args['post__not_in'] = array_merge( isset( $args['post__not_in'] ) ? (array) $args['post__not_in'] : array(), array( $p->ID ) );
		}
	}
	return $args;
}, 10, 2 );

/* ---------- Kiểm tra quyền cho mọi yêu cầu AJAX của trang ---------- */
function vs_adm_guard() {
	nocache_headers();
	if ( ! vs_can_manage_orders() || ! check_ajax_referer( 'vs_adm', 'nonce', false ) ) {
		wp_send_json_error( array( 'message' => 'Phiên đăng nhập quản trị đã hết hạn, vui lòng tải lại trang.' ), 403 );
	}
}

/* ---------- Thống kê nhanh ---------- */
function vs_adm_stats() {
	$ids    = get_posts( array( 'post_type' => 'vs_order', 'post_status' => 'publish', 'posts_per_page' => 3000, 'fields' => 'ids', 'no_found_rows' => true ) );
	$today  = wp_date( 'Y-m-d' );
	$month  = wp_date( 'Y-m' );
	$s      = array( 'today' => 0, 'rev_today' => 0, 'rev_month' => 0, 'cho_thanh_toan' => 0, 'dang_xu_ly' => 0, 'hoan_thanh' => 0, 'huy' => 0, 'all' => 0, 'manual' => 0 );
	foreach ( $ids as $id ) {
		$st = vs_meta( $id, 'status', 'cho_thanh_toan' );
		$s['all']++;
		if ( isset( $s[ $st ] ) ) {
			$s[ $st ]++;
		}
		if ( vs_post_date( 'Y-m-d', $id ) === $today ) {
			$s['today']++;
		}
		$paid = (int) get_post_meta( $id, '_vs_paid_at', true );
		if ( $paid && in_array( $st, array( 'dang_xu_ly', 'hoan_thanh' ), true ) ) {
			$price = (int) vs_meta( $id, 'price' );
			if ( wp_date( 'Y-m-d', $paid ) === $today ) {
				$s['rev_today'] += $price;
			}
			if ( wp_date( 'Y-m', $paid ) === $month ) {
				$s['rev_month'] += $price;
			}
		}
		if ( 'dang_xu_ly' === $st && ! vs_auto_view_pending( $id ) ) {
			$s['manual']++; // Đang xử lý nhưng không còn việc tự động nào: cần người xử lý.
		}
	}
	return $s;
}

/** Đơn còn phần đang chạy tự động không. */
function vs_auto_view_pending( $id ) {
	foreach ( (array) get_post_meta( $id, '_vs_jobs', true ) as $j ) {
		if ( is_array( $j ) && isset( $j['state'] ) && ! in_array( $j['state'], array( 'done', 'failed' ), true ) ) {
			return true;
		}
	}
	return false;
}

/* ---------- Danh sách đơn ---------- */
add_action( 'wp_ajax_vs_adm_orders', function () {
	vs_adm_guard();
	$status = isset( $_POST['status'] ) ? sanitize_key( $_POST['status'] ) : 'all';
	$type   = isset( $_POST['type'] ) ? sanitize_key( $_POST['type'] ) : 'all';
	$q      = isset( $_POST['q'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['q'] ) ) ) : '';
	$page   = max( 1, isset( $_POST['page'] ) ? (int) $_POST['page'] : 1 );

	$meta = array( 'relation' => 'AND' );
	if ( isset( vs_statuses()[ $status ] ) ) {
		$meta[] = array( 'key' => '_vs_status', 'value' => $status );
	}
	if ( in_array( $type, array( 'check', 'reduce' ), true ) ) {
		$meta[] = array( 'key' => '_vs_type', 'value' => $type );
	}
	if ( '' !== $q ) {
		$or = array( 'relation' => 'OR' );
		foreach ( array( '_vs_code', '_vs_phone', '_vs_name', '_vs_email' ) as $k ) {
			$or[] = array( 'key' => $k, 'value' => '_vs_phone' === $k ? preg_replace( '/\D/', '', $q ) ?: $q : $q, 'compare' => 'LIKE' );
		}
		$meta[] = $or;
	}
	$query = new WP_Query( array(
		'post_type'      => 'vs_order',
		'post_status'    => 'publish',
		'posts_per_page' => 20,
		'paged'          => $page,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'meta_query'     => $meta, // phpcs:ignore
		'fields'         => 'ids',
	) );
	$labels = vs_statuses();
	$items  = array();
	foreach ( $query->posts as $id ) {
		$st      = vs_meta( $id, 'status', 'cho_thanh_toan' );
		$items[] = array(
			'id'      => $id,
			'code'    => vs_meta( $id, 'code' ),
			'type'    => vs_meta( $id, 'type' ),
			'name'    => vs_meta( $id, 'name' ),
			'phone'   => vs_meta( $id, 'phone' ),
			'summary' => vs_order_summary( $id ),
			'total'   => vs_money( vs_meta( $id, 'price' ) ),
			'status'  => $st,
			'label'   => isset( $labels[ $st ] ) ? $labels[ $st ] : $st,
			'date'    => vs_post_date( 'H:i d/m', $id ),
			'ago'     => vs_time_ago( get_post_time( 'U', true, $id ) ),
			'auto'    => vs_auto_view_pending( $id ),
		);
	}
	wp_send_json_success( array(
		'items' => $items,
		'total' => (int) $query->found_posts,
		'pages' => (int) $query->max_num_pages,
		'page'  => $page,
		'stats' => vs_adm_stats(),
	) );
} );

/* ---------- Chi tiết đơn ---------- */
function vs_adm_order_data( $id ) {
	$v     = vs_order_view( $id );
	$files = array();
	foreach ( array( 'file' => 'Tài liệu khách gửi', 'src_report' => 'Báo cáo khách gửi' ) as $which => $label ) {
		if ( vs_meta( $id, $which ) ) {
			$name    = (string) vs_meta( $id, $which . '_name', basename( vs_meta( $id, $which ) ) );
			$files[] = array(
				'label' => $label,
				'name'  => $name,
				'short' => vs_short_filename( $name, 34 ),
				'url'   => wp_nonce_url( admin_url( 'admin-post.php?action=vs_download&order=' . $id . ( 'src_report' === $which ? '&which=report' : '' ) ), 'vs_dl_' . $id ),
			);
		}
	}
	// Nhật ký dạng "d/m H:i:s [MÃ] nội dung" -> tách thời gian và nội dung.
	$log = array();
	foreach ( array_slice( array_reverse( (array) get_post_meta( $id, '_vs_auto_log', true ) ), 0, 10 ) as $l ) {
		if ( is_string( $l ) && preg_match( '/^(\S+ \S+)\s+(?:\[[^\]]+\]\s+)?(.*)$/u', $l, $m ) ) {
			$log[] = array( 'ago' => $m[1], 'msg' => $m[2] );
		}
	}
	$p       = vs_pricing();
	$results = array();
	foreach ( (array) vs_meta( $id, 'results', array() ) as $k => $r ) {
		$results[] = array(
			'tool' => 'final' === $k ? 'Sau chỉnh sửa' : ( isset( $p['tools'][ $k ] ) ? $p['tools'][ $k ]['name'] : $k ),
			'plag' => isset( $r['plag'] ) ? $r['plag'] : null,
			'ai'   => isset( $r['ai'] ) ? $r['ai'] : null,
		);
	}
	$uid = (int) vs_meta( $id, 'user' );
	return array(
		'id'       => $id,
		'code'     => $v['code'],
		'title'    => $v['title'],
		'status'   => $v['status'],
		'label'    => $v['label'],
		'summary'  => $v['summary'],
		'total'    => vs_money( $v['total'] ),
		'date'     => $v['date'],
		'paid_at'  => $v['paid_at'],
		'paid_via' => (string) get_post_meta( $id, '_vs_paid_via', true ),
		'done_at'  => $v['done_at'],
		'customer' => array(
			'name'    => vs_meta( $id, 'name' ),
			'phone'   => vs_meta( $id, 'phone' ),
			'email'   => vs_meta( $id, 'email' ),
			'account' => $uid ? get_edit_user_link( $uid ) : '',
			'note'    => vs_meta( $id, 'note' ),
		),
		'details'  => $v['details'],
		'files'    => $files,
		'reports'  => $v['files'],
		'results'  => $results,
		'auto'     => vs_auto_view_pending( $id ),
		'log'      => $log,
		'note'     => (string) vs_meta( $id, 'admin_note' ),
		'drive'    => vs_drive_text( $id ),
		'edit'     => get_edit_post_link( $id, 'raw' ),
	);
}

add_action( 'wp_ajax_vs_adm_order', function () {
	vs_adm_guard();
	$id = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;
	if ( 'vs_order' !== get_post_type( $id ) ) {
		wp_send_json_error( array( 'message' => 'Không tìm thấy đơn.' ), 404 );
	}
	wp_send_json_success( vs_adm_order_data( $id ) );
} );

/* ---------- Thao tác nhanh ---------- */
add_action( 'wp_ajax_vs_adm_action', function () {
	vs_adm_guard();
	$id  = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;
	$act = isset( $_POST['act'] ) ? sanitize_key( $_POST['act'] ) : '';
	if ( 'vs_order' !== get_post_type( $id ) || ! current_user_can( 'edit_post', $id ) ) {
		wp_send_json_error( array( 'message' => 'Không có quyền với đơn này.' ), 403 );
	}
	$old = vs_meta( $id, 'status', 'cho_thanh_toan' );
	$msg = '';
	switch ( $act ) {
		case 'paid':
			if ( 'cho_thanh_toan' !== $old ) {
				wp_send_json_error( array( 'message' => 'Đơn không ở trạng thái chờ thanh toán.' ) );
			}
			vs_mark_paid( $id, 'Quản trị xác nhận', '', (int) vs_meta( $id, 'price' ) );
			$msg = 'Đã xác nhận thanh toán, đơn chuyển sang Đang xử lý.';
			break;
		case 'done':
			if ( 'hoan_thanh' === $old ) {
				wp_send_json_error( array( 'message' => 'Đơn đã hoàn thành.' ) );
			}
			update_post_meta( $id, '_vs_status', 'hoan_thanh' );
			update_post_meta( $id, '_vs_done_at', time() );
			delete_post_meta( $id, '_vs_auto_pending' );
			do_action( 'vs_order_status', $id, 'hoan_thanh', $old );
			if ( ! empty( $_POST['mail'] ) ) {
				vs_auto_mail_done( $id );
			}
			$msg = 'Đã đánh dấu hoàn thành' . ( ! empty( $_POST['mail'] ) ? ' và gửi email kết quả cho khách.' : '.' );
			break;
		case 'cancel':
			update_post_meta( $id, '_vs_status', 'huy' );
			do_action( 'vs_order_status', $id, 'huy', $old );
			$msg = 'Đã hủy đơn.';
			break;
		case 'reopen':
			update_post_meta( $id, '_vs_status', 'dang_xu_ly' );
			do_action( 'vs_order_status', $id, 'dang_xu_ly', $old );
			$msg = 'Đã mở lại đơn (Đang xử lý).';
			break;
		case 'drive':
			list( $n, $bad ) = vs_drive_save( $id, sanitize_textarea_field( wp_unslash( isset( $_POST['drive'] ) ? $_POST['drive'] : '' ) ) );
			if ( $bad ) {
				wp_send_json_error( array( 'message' => 'Đã lưu ' . $n . ' link. Không nhận (không phải link Google Drive): ' . implode( ' ; ', $bad ) ) );
			}
			$msg = $n ? 'Đã lưu ' . $n . ' link Google Drive. Khách xem và tải được khi đơn Đang xử lý/Hoàn thành.' : 'Đã xóa link Google Drive.';
			break;
		case 'note':
			update_post_meta( $id, '_vs_admin_note', sanitize_textarea_field( wp_unslash( isset( $_POST['note'] ) ? $_POST['note'] : '' ) ) );
			$msg = 'Đã lưu ghi chú.';
			break;
		default:
			wp_send_json_error( array( 'message' => 'Thao tác không hợp lệ.' ) );
	}
	if ( in_array( $act, array( 'paid', 'done', 'cancel', 'reopen' ), true ) ) {
		$u = wp_get_current_user();
		vs_auto_log( 'Quản trị (' . $u->display_name . '): ' . $msg, $id );
	}
	wp_send_json_success( array( 'message' => $msg, 'order' => vs_adm_order_data( $id ) ) );
} );

/* ---------- Thông báo quản trị ---------- */
add_action( 'wp_ajax_vs_adm_feed', function () {
	vs_adm_guard();
	$uid  = get_current_user_id();
	$seen = (int) get_user_meta( $uid, '_vs_admin_seen', true );
	if ( ! empty( $_POST['seen'] ) ) {
		update_user_meta( $uid, '_vs_admin_seen', time() );
	}
	$items = array();
	foreach ( array_slice( vs_admin_notices(), 0, 30 ) as $n ) {
		$o            = vs_notice_out( $n );
		$o['read']    = (int) $n['t'] <= $seen;
		$o['code']    = $o['order'] ? vs_meta( $o['order'], 'code' ) : '';
		$items[]      = $o;
	}
	wp_send_json_success( array(
		'unread' => count( array_filter( $items, function ( $i ) {
			return ! $i['read'];
		} ) ),
		'latest' => $items ? $items[0]['t'] : 0,
		'items'  => $items,
	) );
} );

/* Tạo trang /quan-tri/ ngay khi theme cập nhật (không cần vào trang quản trị WordPress). */
add_action( 'init', function () {
	if ( get_option( 'vs_adm_page' ) ) {
		return;
	}
	$page = get_page_by_path( 'quan-tri' );
	$id   = $page ? $page->ID : wp_insert_post( array(
		'post_type'   => 'page',
		'post_status' => 'publish',
		'post_title'  => 'Quản trị đơn hàng',
		'post_name'   => 'quan-tri',
	) );
	if ( $id && ! is_wp_error( $id ) ) {
		update_post_meta( $id, '_wp_page_template', 'page-templates/admin-orders.php' );
		update_option( 'vs_adm_page', (int) $id );
		update_option( 'vs_flush', 1 );
	}
}, 30 );
