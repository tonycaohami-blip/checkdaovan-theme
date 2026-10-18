<?php
/**
 * Tự động hóa 24/7: hàng đợi kiểm tra, cron, giao kết quả, tải file bảo mật.
 *
 * Luồng: thanh toán được xác nhận (webhook ngân hàng hoặc quản trị) -> đơn chuyển "Đang xử lý"
 * -> tạo "job" cho từng phần mềm/loại kiểm tra -> cron chạy mỗi phút đẩy từng job qua API chính thức
 * -> đủ kết quả thì lưu báo cáo PDF, chuyển "Hoàn thành", gửi email kèm file cho khách.
 * Phần nào không tự động được (chưa cấu hình API, loại kiểm tra không hỗ trợ, lỗi) -> chờ xử lý tay
 * và báo ngay cho quản trị qua Telegram/email.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ---------- Cấu hình ---------- */

function vs_auto_cfg( $key = null ) {
	$c = wp_parse_args( get_option( 'vs_auto', array() ), array(
		'enabled'     => '',
		'turnitin'    => array(),
		'ithenticate' => array(),
		'copyleaks'   => array(),
		'originality' => array(),
		'sepay_key'   => '',
		'sepay_token' => '',
		'casso_token' => '',
	) );
	return null === $key ? $c : ( isset( $c[ $key ] ) ? $c[ $key ] : null );
}

/**
 * Khả năng tự động của từng phần mềm: loại kiểm tra (plag/ai) => nhà cung cấp API.
 * Chỉ khai báo những gì đã có API chính thức và được triển khai ở providers.php.
 */
function vs_auto_capabilities() {
	return apply_filters( 'vs_auto_capabilities', array(
		'turnitin'    => array( 'plag' => 'tca' ),
		'ithenticate' => array( 'plag' => 'tca' ),
		'copyleaks'   => array( 'plag' => 'copyleaks' ),
		'originality' => array( 'ai' => 'originality' ),
	) );
}

/** Phần mềm này đã được bật & cấu hình đủ để chạy tự động chưa. */
function vs_auto_tool_ready( $tool ) {
	$pre = apply_filters( 'vs_auto_tool_ready', null, $tool );
	if ( null !== $pre ) {
		return (bool) $pre;
	}
	if ( ! vs_auto_cfg( 'enabled' ) ) {
		return false;
	}
	$c = (array) vs_auto_cfg( $tool );
	if ( empty( $c['on'] ) ) {
		return false;
	}
	switch ( $tool ) {
		case 'turnitin':
		case 'ithenticate':
			return ! empty( $c['base'] ) && ! empty( $c['key'] ) && ! empty( $c['owner'] );
		case 'copyleaks':
			return ! empty( $c['email'] ) && ! empty( $c['key'] );
		case 'originality':
			return ! empty( $c['key'] );
	}
	return false;
}

/** Có ít nhất một phần mềm chạy tự động (dùng để hiển thị nội dung "tự động 24/7"). */
function vs_auto_on() {
	foreach ( array_keys( vs_auto_capabilities() ) as $t ) {
		if ( vs_auto_tool_ready( $t ) ) {
			return true;
		}
	}
	return false;
}

/** Thanh toán được xác nhận tự động qua ngân hàng. */
function vs_auto_pay_on() {
	return (bool) ( vs_auto_cfg( 'sepay_key' ) || vs_auto_cfg( 'sepay_token' ) || vs_auto_cfg( 'casso_token' ) );
}

/* ---------- Nhật ký ---------- */

function vs_auto_log( $msg, $order_id = 0 ) {
	$line = wp_date( 'd/m H:i:s' ) . ( $order_id ? ' [' . vs_meta( $order_id, 'code' ) . ']' : '' ) . ' ' . $msg;
	$log  = (array) get_option( 'vs_auto_log', array() );
	array_unshift( $log, $line );
	update_option( 'vs_auto_log', array_slice( $log, 0, 300 ), false );
	if ( $order_id ) {
		$ol   = (array) get_post_meta( $order_id, '_vs_auto_log', true );
		$ol[] = $line;
		update_post_meta( $order_id, '_vs_auto_log', array_slice( $ol, -50 ) );
	}
}

/* ---------- Thanh toán ---------- */

/** Đánh dấu đã thanh toán (từ webhook ngân hàng). Trả về true nếu đơn được chuyển trạng thái. */
function vs_mark_paid( $order_id, $source, $tx_id = '', $amount = 0 ) {
	if ( 'cho_thanh_toan' !== vs_meta( $order_id, 'status', 'cho_thanh_toan' ) ) {
		return false;
	}
	update_post_meta( $order_id, '_vs_status', 'dang_xu_ly' );
	update_post_meta( $order_id, '_vs_paid_at', time() );
	update_post_meta( $order_id, '_vs_paid_via', $source );
	update_post_meta( $order_id, '_vs_paid_tx', $tx_id );
	vs_auto_log( 'Đã nhận thanh toán ' . vs_money( $amount ) . ' qua ' . $source, $order_id );
	vs_telegram( '💰 Đã nhận ' . vs_money( $amount ) . ' cho đơn ' . vs_meta( $order_id, 'code' ) . ' (' . $source . ')' );
	do_action( 'vs_order_status', $order_id, 'dang_xu_ly', 'cho_thanh_toan' );
	return true;
}

/* Khi đơn chuyển từ "Chờ thanh toán" sang "Đang xử lý" (tự động hoặc quản trị bấm) -> đưa vào hàng đợi. */
add_action( 'vs_order_status', function ( $id, $status, $old ) {
	if ( 'dang_xu_ly' !== $status || 'cho_thanh_toan' !== $old ) {
		return;
	}
	if ( ! get_post_meta( $id, '_vs_paid_at', true ) ) {
		update_post_meta( $id, '_vs_paid_at', time() );
		update_post_meta( $id, '_vs_paid_via', 'Quản trị xác nhận' );
	}
	vs_auto_enqueue( $id );
}, 20, 3 );

/* ---------- Hàng đợi ---------- */

function vs_auto_enqueue( $id ) {
	if ( 'check' !== vs_meta( $id, 'type' ) ) {
		vs_telegram( '✍️ Đơn giảm tỷ lệ ' . vs_meta( $id, 'code' ) . ' đã thanh toán - cần phân công biên tập viên.' );
		return;
	}
	$data  = (array) vs_meta( $id, 'data', array() );
	$kinds = 'combo' === $data['mode'] ? array( 'plag', 'ai' ) : array( $data['mode'] );
	$caps  = vs_auto_capabilities();
	$jobs  = array();
	$manual = array();
	foreach ( (array) $data['tools'] as $tool ) {
		foreach ( $kinds as $kind ) {
			if ( isset( $caps[ $tool ][ $kind ] ) && vs_auto_tool_ready( $tool ) ) {
				$jobs[] = array(
					'tool'     => $tool,
					'kind'     => $kind,
					'provider' => $caps[ $tool ][ $kind ],
					'state'    => 'queued',
					'tries'    => 0,
					'ext'      => array(),
					'result'   => null,
					'file'     => '',
					'error'    => '',
					'started'  => time(),
				);
			} else {
				$manual[] = vs_pricing()['tools'][ $tool ]['name'] . ' (' . ( 'ai' === $kind ? 'AI' : 'đạo văn' ) . ')';
			}
		}
	}
	update_post_meta( $id, '_vs_jobs', $jobs );
	update_post_meta( $id, '_vs_manual', $manual );

	if ( $jobs ) {
		update_post_meta( $id, '_vs_auto_pending', 1 );
		vs_auto_log( 'Đưa vào hàng đợi tự động: ' . count( $jobs ) . ' lượt kiểm tra', $id );
		vs_auto_kick();
	}
	if ( $manual ) {
		vs_auto_log( 'Cần xử lý tay: ' . implode( ', ', $manual ), $id );
		vs_telegram( '🖐 Đơn ' . vs_meta( $id, 'code' ) . ' cần kiểm tra tay: ' . implode( ', ', $manual ) . "\n" . admin_url( 'post.php?post=' . $id . '&action=edit' ) );
	}
}

/* Cron mỗi phút. Trên hosting thật nên tắt WP-Cron mặc định và gọi wp-cron.php bằng cron của máy chủ mỗi phút. */
add_filter( 'cron_schedules', function ( $s ) {
	$s['vs_minute'] = array( 'interval' => 60, 'display' => 'Mỗi phút (Check Đạo Văn)' );
	return $s;
} );
add_action( 'init', function () {
	if ( ! wp_next_scheduled( 'vs_auto_tick' ) ) {
		wp_schedule_event( time() + 60, 'vs_minute', 'vs_auto_tick' );
	}
} );
add_action( 'switch_theme', function () {
	wp_clear_scheduled_hook( 'vs_auto_tick' );
} );

/** Chạy ngay một lượt xử lý (không chờ tới phút kế tiếp). */
function vs_auto_kick() {
	wp_schedule_single_event( time(), 'vs_auto_tick_now', array( wp_rand() ) );
	spawn_cron();
}
add_action( 'vs_auto_tick_now', 'vs_auto_tick' );
add_action( 'vs_auto_tick', 'vs_auto_tick' );

function vs_auto_tick() {
	if ( get_transient( 'vs_auto_lock' ) ) {
		return;
	}
	set_transient( 'vs_auto_lock', 1, 5 * MINUTE_IN_SECONDS );
	update_option( 'vs_auto_last_tick', time(), false );
	$ids = get_posts( array(
		'post_type'      => 'vs_order',
		'posts_per_page' => 20,
		'fields'         => 'ids',
		'meta_key'       => '_vs_auto_pending',
		'meta_value'     => 1,
	) );
	foreach ( $ids as $id ) {
		vs_auto_process( $id );
	}
	delete_transient( 'vs_auto_lock' );
}

/** Đẩy các job của 1 đơn sang bước tiếp theo. */
function vs_auto_process( $id ) {
	if ( 'dang_xu_ly' !== vs_meta( $id, 'status' ) ) {
		delete_post_meta( $id, '_vs_auto_pending' ); // Quản trị đã xử lý/hủy đơn.
		return;
	}
	$jobs     = (array) get_post_meta( $id, '_vs_jobs', true );
	$changed  = false;
	$done_now = array();
	foreach ( $jobs as $i => $job ) {
		if ( in_array( $job['state'], array( 'done', 'failed' ), true ) ) {
			continue;
		}
		$prev = $job['state'];
		$fn   = 'vs_prov_' . $job['provider'] . '_step';
		if ( ! function_exists( $fn ) ) {
			$job['state'] = 'failed';
			$job['error'] = 'Chưa hỗ trợ';
		} else {
			$res = $fn( $id, $job );
			if ( is_wp_error( $res ) ) {
				$job['tries']++;
				$job['error'] = $res->get_error_message();
				vs_auto_log( $job['tool'] . ': lỗi lần ' . $job['tries'] . ' - ' . $job['error'], $id );
				if ( $job['tries'] >= 5 ) {
					$job['state'] = 'failed';
				}
			} else {
				$job = $res;
			}
		}
		// Quá 3 giờ chưa xong thì chuyển xử lý tay.
		if ( ! in_array( $job['state'], array( 'done', 'failed' ), true ) && time() - (int) $job['started'] > 3 * HOUR_IN_SECONDS ) {
			$job['state'] = 'failed';
			$job['error'] = 'Quá thời gian chờ';
		}
		$jobs[ $i ] = $job;
		$changed    = true;
		if ( 'done' === $job['state'] && 'done' !== $prev ) {
			$done_now[] = $job;
		}
	}
	if ( $changed ) {
		update_post_meta( $id, '_vs_jobs', $jobs );
	}
	foreach ( $done_now as $dj ) {
		do_action( 'vs_auto_job_done', $id, $dj );
	}
	vs_auto_maybe_finish( $id );
}

/* Mỗi khi một phần mềm có kết quả: báo quản trị ngay (Telegram) - khách thấy và tải được ngay trong tài khoản. */
add_action( 'vs_auto_job_done', function ( $id, $job ) {
	$name = vs_pricing()['tools'][ $job['tool'] ]['name'];
	$val  = null === $job['result'] ? '' : ' ' . $job['result'] . '%';
	vs_auto_log( $name . ': đã có kết quả' . $val . ( $job['file'] ? ' + báo cáo PDF' : '' ) . ', khách đã tải được', $id );
	vs_telegram( '📄 Kết quả ' . $name . ( 'ai' === $job['kind'] ? ' (AI)' : '' ) . $val . ' - đơn ' . vs_meta( $id, 'code' ) . "
" . admin_url( 'post.php?post=' . $id . '&action=edit' ) );
}, 10, 2 );

/** Lưu kết quả 1 job (gọi từ provider khi có số liệu / file). */
function vs_auto_set_result( $id, $tool, $kind, $value ) {
	$r = (array) vs_meta( $id, 'results', array() );
	$r[ $tool ][ $kind ] = round( (float) $value, 1 );
	update_post_meta( $id, '_vs_results', $r );
	vs_set_result_src( $id, $tool, $kind, 'api' );
}

function vs_auto_maybe_finish( $id ) {
	$jobs = (array) get_post_meta( $id, '_vs_jobs', true );
	foreach ( $jobs as $j ) {
		if ( ! in_array( $j['state'], array( 'done', 'failed' ), true ) ) {
			return; // Còn job đang chạy.
		}
	}
	delete_post_meta( $id, '_vs_auto_pending' );
	$failed = array_filter( $jobs, function ( $j ) { return 'failed' === $j['state']; } );
	$manual = (array) get_post_meta( $id, '_vs_manual', true );

	if ( $failed || $manual ) {
		$names = array_merge( $manual, array_map( function ( $j ) { return $j['tool'] . ' (' . $j['error'] . ')'; }, $failed ) );
		vs_auto_log( 'Tự động xong một phần, cần xử lý tay: ' . implode( ', ', $names ), $id );
		vs_telegram( '⚠️ Đơn ' . vs_meta( $id, 'code' ) . ' cần hoàn tất tay: ' . implode( ', ', $names ) . "\n" . admin_url( 'post.php?post=' . $id . '&action=edit' ) );
		$has_done = array_filter( $jobs, function ( $j ) { return 'done' === $j['state']; } );
		if ( $has_done && ! get_post_meta( $id, '_vs_partial_mailed', true ) ) {
			update_post_meta( $id, '_vs_partial_mailed', time() );
			vs_auto_mail_done( $id, true );
		}
		return;
	}

	$old = vs_meta( $id, 'status' );
	update_post_meta( $id, '_vs_status', 'hoan_thanh' );
	update_post_meta( $id, '_vs_done_at', time() );
	do_action( 'vs_order_status', $id, 'hoan_thanh', $old );
	vs_auto_log( 'Hoàn thành tự động, đã gửi kết quả cho khách', $id );
	vs_auto_mail_done( $id );
	vs_telegram( '✅ Đơn ' . vs_meta( $id, 'code' ) . ' đã hoàn thành tự động.' );
}

/* ---------- Lưu & tải file báo cáo bảo mật ---------- */

function vs_report_dir() {
	$dir = vs_private_dir() . '/reports';
	if ( ! is_dir( $dir ) ) {
		wp_mkdir_p( $dir );
		file_put_contents( $dir . '/index.php', "<?php // Silence.\n" );
	}
	return $dir;
}

/** Lưu nội dung PDF, trả về tên file (đường dẫn tương đối trong thư mục reports). */
function vs_save_report( $id, $tool, $bytes ) {
	$name = vs_meta( $id, 'code' ) . '-' . $tool . '-' . wp_generate_password( 8, false ) . '.pdf';
	file_put_contents( vs_report_dir() . '/' . $name, $bytes );
	return $name;
}

/** Danh sách file báo cáo tự động của đơn: [ [path, label] ]. */
function vs_auto_files( $id ) {
	$out = array();
	foreach ( (array) get_post_meta( $id, '_vs_jobs', true ) as $j ) {
		if ( ! empty( $j['file'] ) ) {
			$out[] = array( 'path' => $j['file'], 'name' => 'Bao-cao-' . vs_pricing()['tools'][ $j['tool'] ]['name'] . '-' . vs_meta( $id, 'code' ) . '.pdf' );
		}
	}
	return $out;
}

function vs_file_key( $id, $i ) {
	return substr( wp_hash( $id . '|' . $i . '|' . vs_meta( $id, 'code' ), 'auth' ), 0, 24 );
}

function vs_file_url( $id, $i ) {
	return add_query_arg( array( 'vs_file' => $id, 'i' => $i, 'k' => vs_file_key( $id, $i ) ), home_url( '/' ) );
}

/* Tải báo cáo: chỉ với link có khóa ký bằng secret của website. */
add_action( 'template_redirect', function () {
	if ( empty( $_GET['vs_file'] ) ) {
		return;
	}
	$id    = (int) $_GET['vs_file'];
	$i     = isset( $_GET['i'] ) ? (int) $_GET['i'] : -1;
	$k     = isset( $_GET['k'] ) ? (string) $_GET['k'] : '';
	$files = vs_auto_files( $id );
	if ( ! isset( $files[ $i ] ) || ! hash_equals( vs_file_key( $id, $i ), $k ) || ! in_array( vs_meta( $id, 'status' ), array( 'dang_xu_ly', 'hoan_thanh' ), true ) ) {
		wp_die( 'Liên kết không hợp lệ hoặc đã hết hạn.', 'Không tải được file', array( 'response' => 403 ) );
	}
	$path = vs_report_dir() . '/' . basename( $files[ $i ]['path'] );
	if ( ! is_file( $path ) ) {
		wp_die( 'File đã được xóa theo chính sách lưu trữ 30 ngày. Vui lòng liên hệ hỗ trợ.', 'Không tìm thấy file', array( 'response' => 404 ) );
	}
	nocache_headers();
	header( 'Content-Type: application/pdf' );
	header( 'Content-Disposition: attachment; filename="' . rawurlencode( $files[ $i ]['name'] ) . '"' );
	header( 'Content-Length: ' . filesize( $path ) );
	header( 'X-Robots-Tag: noindex' );
	readfile( $path );
	exit;
} );

/* Tải lại file khách đã gửi (tài liệu / báo cáo PDF gốc): link có chữ ký theo mã đơn. */
function vs_src_key( $id, $which ) {
	return substr( wp_hash( $id . '|src|' . $which . '|' . vs_meta( $id, 'code' ), 'auth' ), 0, 24 );
}

function vs_src_url( $id, $which ) {
	return add_query_arg( array( 'vs_src' => $id, 'w' => $which, 'k' => vs_src_key( $id, $which ) ), home_url( '/' ) );
}

add_action( 'template_redirect', function () {
	if ( empty( $_GET['vs_src'] ) ) {
		return;
	}
	$id    = (int) $_GET['vs_src'];
	$which = isset( $_GET['w'] ) && 'src_report' === $_GET['w'] ? 'src_report' : 'file';
	$k     = isset( $_GET['k'] ) ? (string) $_GET['k'] : '';
	if ( 'vs_order' !== get_post_type( $id ) || ! vs_meta( $id, $which ) || ! hash_equals( vs_src_key( $id, $which ), $k ) ) {
		wp_die( 'Liên kết không hợp lệ hoặc đã hết hạn.', 'Không tải được file', array( 'response' => 403 ) );
	}
	$path = vs_private_dir() . '/' . basename( vs_meta( $id, $which ) );
	if ( ! is_file( $path ) ) {
		wp_die( 'File đã được xóa theo chính sách lưu trữ 30 ngày.', 'Không tìm thấy file', array( 'response' => 404 ) );
	}
	$name = (string) vs_meta( $id, $which . '_name', basename( $path ) );
	nocache_headers();
	header( 'Content-Type: application/octet-stream' );
	header( "Content-Disposition: attachment; filename=\"" . sanitize_file_name( remove_accents( $name ) ) . "\"; filename*=UTF-8''" . rawurlencode( $name ) );
	header( 'Content-Length: ' . filesize( $path ) );
	header( 'X-Robots-Tag: noindex' );
	readfile( $path );
	exit;
} );

/* Dọn file báo cáo tự động theo cùng chính sách 30 ngày. */
add_action( 'vs_cleanup_files', function () {
	$days = (int) apply_filters( 'vs_file_retention_days', 30 );
	foreach ( glob( vs_report_dir() . '/*.pdf' ) ?: array() as $f ) {
		if ( filemtime( $f ) < time() - $days * DAY_IN_SECONDS ) {
			unlink( $f );
		}
	}
} );

/* ---------- Email kết quả kèm file ---------- */

function vs_auto_mail_done( $id, $partial = false ) {
	$email = vs_meta( $id, 'email' );
	if ( ! $email && vs_meta( $id, 'user' ) ) {
		$u     = get_userdata( (int) vs_meta( $id, 'user' ) );
		$email = $u ? $u->user_email : '';
	}
	if ( ! is_email( $email ) ) {
		return;
	}
	$p      = vs_pricing();
	$lines  = array();
	foreach ( (array) vs_meta( $id, 'results', array() ) as $tool => $r ) {
		$lines[] = '- ' . ( isset( $p['tools'][ $tool ] ) ? $p['tools'][ $tool ]['name'] : $tool ) . ': '
			. ( isset( $r['plag'] ) ? 'Đạo văn ' . $r['plag'] . '%' : '' )
			. ( isset( $r['plag'], $r['ai'] ) ? ', ' : '' )
			. ( isset( $r['ai'] ) ? 'AI ' . $r['ai'] . '%' : '' );
	}
	$attach = array();
	$links  = array();
	foreach ( array_filter( array_map( 'intval', explode( ',', (string) vs_meta( $id, 'report_ids' ) ) ) ) as $fid ) {
		$fp = get_attached_file( $fid );
		if ( $fp && is_file( $fp ) && filesize( $fp ) < 8 * 1048576 ) {
			$attach[] = $fp;
			$links[]  = basename( $fp ) . ': ' . wp_get_attachment_url( $fid );
		}
	}
	foreach ( vs_auto_files( $id ) as $i => $f ) {
		$path = vs_report_dir() . '/' . basename( $f['path'] );
		if ( is_file( $path ) && filesize( $path ) < 8 * 1048576 ) {
			$attach[] = $path;
		}
		$links[] = $f['name'] . ': ' . vs_file_url( $id, $i );
	}
	$body  = 'Chào ' . vs_meta( $id, 'name' ) . ",\n\n";
	$body .= 'Đơn ' . vs_meta( $id, 'code' ) . ( $partial ? ' đã có kết quả một phần (phần còn lại đang được chuyên viên xử lý, bạn sẽ nhận email khi xong)' : ' đã có kết quả' ) . ":\n" . implode( "\n", $lines ) . "\n\n";
	if ( $links ) {
		$body .= "Báo cáo PDF được đính kèm email này. Bạn cũng có thể tải lại tại:\n" . implode( "\n", $links ) . "\n\n";
	}
	$body .= 'Xem trong tài khoản: ' . vs_account_url( 'don-hang' ) . "\n";
	$body .= 'Hoặc tra cứu: ' . vs_page_url( 'tra-cuu-don-hang', array( 'ma' => vs_meta( $id, 'code' ) ) ) . "\n\n";
	$max = 0;
	foreach ( (array) vs_meta( $id, 'results', array() ) as $r ) {
		$max = max( $max, isset( $r['plag'] ) ? (float) $r['plag'] : 0 );
	}
	if ( $max > 20 ) {
		$body .= 'Tỷ lệ đạo văn còn ' . $max . '%. Bạn có thể đặt dịch vụ giảm xuống dưới 30% hoặc 20% ngay trong tài khoản, được giảm thêm ' . $p['reduce']['linked_pct'] . "%.\n\n";
	}
	$body .= 'File của bạn được lưu trong thư mục bảo mật và tự động xóa sau 30 ngày.' . "\n" . get_bloginfo( 'name' ) . ' - Hotline/Zalo ' . vs_opt( 'hotline' );
	wp_mail( $email, '[' . get_bloginfo( 'name' ) . '] ' . ( $partial ? 'Đã có kết quả một phần' : 'Kết quả kiểm tra' ) . ' đơn ' . vs_meta( $id, 'code' ), $body, '', $attach );
}

/* ---------- Thông tin tiến độ cho khách ---------- */

function vs_job_label( $j ) {
	$name = vs_pricing()['tools'][ $j['tool'] ]['name'] . ( 'ai' === $j['kind'] ? ' (AI)' : '' );
	$map  = array(
		'queued'     => 'Đang chờ kết quả...',
		'uploaded'   => 'Đang chờ kết quả...',
		'similarity' => 'Đang chờ kết quả...',
		'processing' => 'Đang chờ kết quả...',
		'pdf'        => 'Đang chờ kết quả...',
		'done'       => 'Đã có kết quả',
		'failed'     => 'Đang chờ kết quả...',
	);
	return array( 'name' => $name, 'state' => $j['state'], 'label' => isset( $map[ $j['state'] ] ) ? $map[ $j['state'] ] : $j['state'] );
}

function vs_auto_view( $id ) {
	$jobs    = array_filter( (array) get_post_meta( $id, '_vs_jobs', true ) );
	$results = (array) vs_meta( $id, 'results', array() );
	$done    = 'hoan_thanh' === vs_meta( $id, 'status' );
	$has     = function ( $tool, $kind ) use ( $results ) {
		return isset( $results[ $tool ][ $kind ] ) && '' !== $results[ $tool ][ $kind ];
	};

	// Việc chạy tự động: đã có kết quả (kể cả do quản trị nhập tay khi API lỗi) hoặc đơn đã hoàn thành -> "Đã có kết quả".
	$labels = array();
	foreach ( $jobs as $j ) {
		if ( $done || $has( $j['tool'], $j['kind'] ) ) {
			$j['state'] = 'done';
		}
		$labels[] = vs_job_label( $j );
	}

	// Phần xử lý tay ("Turnitin (AI)", "DoIT (đạo văn)"): bỏ khỏi danh sách chờ khi đã có kết quả.
	$names  = array();
	foreach ( vs_pricing()['tools'] as $k => $t ) {
		$names[ $t['name'] ] = $k;
	}
	$manual = array();
	foreach ( (array) get_post_meta( $id, '_vs_manual', true ) as $m ) {
		if ( $done ) {
			break;
		}
		if ( preg_match( '/^(.*) \((AI|đạo văn)\)$/u', (string) $m, $mm ) && isset( $names[ $mm[1] ] ) && $has( $names[ $mm[1] ], 'AI' === $mm[2] ? 'ai' : 'plag' ) ) {
			continue;
		}
		$manual[] = $m;
	}

	return array(
		'paid_at' => get_post_meta( $id, '_vs_paid_at', true ) ? wp_date( 'H:i d/m/Y', (int) get_post_meta( $id, '_vs_paid_at', true ) ) : '',
		'paid_via'=> (string) get_post_meta( $id, '_vs_paid_via', true ),
		'jobs'    => $labels,
		'manual'  => $manual,
		'pending' => ! $done && (bool) get_post_meta( $id, '_vs_auto_pending', true ),
	);
}
