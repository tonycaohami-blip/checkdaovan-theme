<?php
/**
 * CDV Scan: gắn vào trang đặt đơn, bảng giá và quy trình xử lý đơn tự động 24/7.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Tên phần mềm theo mã (kể cả khi CDV Scan đang tắt bán). */
function vs_tool_name( $k ) {
	$p = vs_pricing();
	if ( isset( $p['tools'][ $k ]['name'] ) ) {
		return $p['tools'][ $k ]['name'];
	}
	return 'cdv' === $k ? vs_cdv_name() : $k;
}

/* Bán cho khách: thêm vào danh sách phần mềm (đứng đầu) khi quản trị bật. */
add_filter( 'vs_pricing', function ( $p ) {
	if ( ! vs_cdv_sellable() ) {
		return $p;
	}
	$price = max( 1000, (int) vs_cdv_cfg( 'price' ) );
	$tool  = array(
		'cdv' => array(
			'name'  => vs_cdv_name(),
			'desc'  => 'Bộ kiểm tra trùng lặp do ' . get_bloginfo( 'name' ) . ' phát triển: đối chiếu với Internet và kho tài liệu riêng, báo cáo tô màu từng nguồn. Không phải Turnitin, kết quả có thể khác phần mềm trường dùng.',
			'plag'  => $price,
			'ai'    => 0,
			'combo' => 0,
			'note'  => array( 'Cơ sở dữ liệu', 'Internet + kho riêng' ),
		),
	);
	$p['tools'] = $tool + $p['tools'];
	return $p;
}, 5 );

/* Chạy tự động trong hàng đợi 24/7. */
add_filter( 'vs_auto_capabilities', function ( $c ) {
	$c['cdv'] = array( 'plag' => 'cdv' );
	return $c;
} );

add_filter( 'vs_auto_tool_ready', function ( $pre, $tool ) {
	return 'cdv' === $tool ? (bool) vs_cdv_cfg( 'on' ) : $pre;
}, 10, 2 );

/**
 * Bước xử lý của đơn (gọi từ cron "Tự động 24/7" mỗi phút).
 * queued -> processing (tìm Internet, tải nguồn, so khớp) -> done.
 */
function vs_prov_cdv_step( $id, $job ) {
	if ( 'queued' === $job['state'] ) {
		$text = vs_job_text( $id );
		if ( ! $text ) {
			return vs_job_no_text( $job );
		}
		$data = (array) vs_meta( $id, 'data', array() );
		$sid  = vs_cdv_scan_new( $text, array(
			'title' => (string) vs_meta( $id, 'file_name' ) ? (string) vs_meta( $id, 'file_name' ) : 'Đơn ' . vs_meta( $id, 'code' ),
			'ref'   => $id,
			'repo'  => ! empty( $data['repo'] ) ? 1 : 0,
			'by'    => 0,
		) );
		if ( is_wp_error( $sid ) ) {
			$job['state'] = 'failed';
			$job['error'] = $sid->get_error_message();
			return $job;
		}
		$job['ext']['scan'] = $sid;
		$job['state']       = 'processing';
		vs_auto_log( 'cdv: bắt đầu kiểm tra (' . ( vs_cdv_web_ready() ? 'Internet + kho' : 'kho nội bộ' ) . ')', $id );
	}
	$s = vs_cdv_scan_step( $job['ext']['scan'], 40 );
	if ( ! $s ) {
		return new WP_Error( 'scan', 'Không tìm thấy lượt kiểm tra' );
	}
	if ( 'done' !== $s['state'] ) {
		vs_auto_kick(); // Còn bước tìm/tải nguồn: chạy tiếp ngay.
		return $job;
	}
	$job['result'] = (float) $s['result']['score'];
	vs_auto_set_result( $id, 'cdv', 'plag', $job['result'] );
	vs_auto_log( 'cdv: tỷ lệ trùng ' . $job['result'] . '% với ' . count( $s['result']['sources'] ) . ' nguồn (' . $s['webdocs'] . ' trang web)', $id );
	try {
		$job['file'] = vs_save_report( $id, 'cdv', vs_cdv_report_pdf( $s ) );
	} catch ( \Throwable $e ) {
		vs_auto_log( 'cdv: không tạo được PDF (' . $e->getMessage() . ')', $id );
	}
	$job['view']  = $s['id'];
	$job['state'] = 'done';
	return $job;
}

/* Lựa chọn lưu bài vào kho (chỉ khi khách tự đánh dấu). */
add_filter( 'vs_order_data', function ( $data, $post ) {
	if ( isset( $data['tools'] ) && in_array( 'cdv', (array) $data['tools'], true ) ) {
		$data['repo'] = ! empty( $post['cdv_repo'] ) && vs_cdv_cfg( 'repo' ) ? 1 : 0;
	}
	return $data;
}, 10, 2 );

/** Ô đồng ý lưu bài (hiện khi khách chọn CDV Scan). */
function vs_cdv_order_field() {
	if ( ! vs_cdv_sellable() || ! vs_cdv_cfg( 'repo' ) ) {
		return;
	}
	?>
	<label class="cdv-repo" data-cdv-repo hidden>
		<input type="checkbox" name="cdv_repo" value="1">
		<span><b>Lưu bài vào kho đối chiếu của <?php echo esc_html( vs_cdv_name() ); ?></b><small>Giúp phát hiện bài nộp sau này trùng với bài của bạn (giống chế độ “Repository” của Turnitin). Bỏ trống: bài chỉ được kiểm tra, không lưu lại.</small></span>
	</label>
	<?php
}
