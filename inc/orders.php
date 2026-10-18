<?php
/**
 * Đơn hàng: post type, lưu file bảo mật, màn hình quản trị, email.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function vs_statuses() {
	return array(
		'cho_thanh_toan' => 'Chờ thanh toán',
		'dang_xu_ly'     => 'Đang xử lý',
		'hoan_thanh'     => 'Hoàn thành',
		'huy'            => 'Đã hủy',
	);
}

add_action( 'init', function () {
	register_post_type( 'vs_order', array(
		'labels'          => array(
			'name'          => 'Đơn hàng',
			'singular_name' => 'Đơn hàng',
			'menu_name'     => 'Đơn hàng',
			'all_items'     => 'Tất cả đơn hàng',
			'edit_item'     => 'Chi tiết đơn hàng',
			'search_items'  => 'Tìm đơn hàng',
			'not_found'     => 'Chưa có đơn hàng nào',
		),
		'public'          => false,
		'show_ui'         => true,
		'menu_icon'       => 'dashicons-clipboard',
		'menu_position'   => 25,
		'supports'        => array( 'title' ),
		'capability_type' => 'post',
		'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
		'map_meta_cap'    => true,
	) );
} );

function vs_meta( $id, $key, $default = '' ) {
	$v = get_post_meta( $id, '_vs_' . $key, true );
	return '' === $v || null === $v ? $default : $v;
}

function vs_find_order( $code ) {
	$q = get_posts( array(
		'post_type'      => 'vs_order',
		'post_status'    => 'any',
		'posts_per_page' => 1,
		'meta_key'       => '_vs_code',
		'meta_value'     => strtoupper( trim( $code ) ),
		'fields'         => 'ids',
	) );
	return $q ? (int) $q[0] : 0;
}

function vs_new_code() {
	$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
	do {
		$rand = '';
		for ( $i = 0; $i < 4; $i++ ) {
			$rand .= $chars[ wp_rand( 0, strlen( $chars ) - 1 ) ];
		}
		$code = 'VS' . wp_date( 'md' ) . $rand;
	} while ( vs_find_order( $code ) );
	return $code;
}

/* ---------- Lưu file khách gửi vào thư mục được bảo vệ ---------- */

function vs_private_dir() {
	$dir = trailingslashit( wp_upload_dir()['basedir'] ) . 'vs-orders';
	if ( ! is_dir( $dir ) ) {
		wp_mkdir_p( $dir );
	}
	if ( ! file_exists( $dir . '/.htaccess' ) ) {
		file_put_contents( $dir . '/.htaccess', "Require all denied\nDeny from all\n" );
		file_put_contents( $dir . '/index.php', "<?php // Silence.\n" );
	}
	return $dir;
}

/**
 * @return array|WP_Error ['path'=>, 'name'=>]
 */
function vs_store_upload( $file, $code, $exts = null ) {
	if ( empty( $file['tmp_name'] ) || UPLOAD_ERR_OK !== $file['error'] ) {
		return new WP_Error( 'file', 'Vui lòng tải lên file tài liệu.' );
	}
	if ( $file['size'] > vs_max_upload_mb() * 1048576 ) {
		return new WP_Error( 'file', 'File vượt quá ' . vs_max_upload_mb() . 'MB.' );
	}
	$allowed = array(
		'doc'  => 'application/msword',
		'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		'pdf'  => 'application/pdf',
		'txt'  => 'text/plain',
	);
	if ( $exts ) {
		$allowed = array_intersect_key( $allowed, array_flip( $exts ) );
	}
	$check = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'], $allowed );
	if ( empty( $check['ext'] ) ) {
		return new WP_Error( 'file', 'Chỉ nhận file ' . implode( ', ', array_map( function ( $e ) { return '.' . $e; }, array_keys( $allowed ) ) ) . '.' );
	}
	$dest = vs_private_dir() . '/' . $code . '-' . wp_generate_password( 12, false ) . '.' . $check['ext'];
	if ( ! move_uploaded_file( $file['tmp_name'], $dest ) ) {
		return new WP_Error( 'file', 'Không lưu được file, vui lòng thử lại.' );
	}
	return array(
		'path' => basename( $dest ),
		'name' => sanitize_file_name( $file['name'] ),
	);
}

/* Tải file gốc của khách (chỉ quản trị). */
add_action( 'admin_post_vs_download', function () {
	$id = isset( $_GET['order'] ) ? (int) $_GET['order'] : 0;
	if ( ! $id || ! current_user_can( 'edit_post', $id ) || ! wp_verify_nonce( isset( $_GET['_wpnonce'] ) ? $_GET['_wpnonce'] : '', 'vs_dl_' . $id ) ) {
		wp_die( 'Không có quyền.' );
	}
	$which = isset( $_GET['which'] ) && 'report' === $_GET['which'] ? 'src_report' : 'file';
	$path  = vs_private_dir() . '/' . basename( vs_meta( $id, $which ) );
	if ( ! vs_meta( $id, $which ) || ! is_file( $path ) ) {
		wp_die( 'File không tồn tại.' );
	}
	nocache_headers();
	header( 'Content-Type: application/octet-stream' );
	header( 'Content-Disposition: attachment; filename="' . rawurlencode( vs_meta( $id, $which . '_name', basename( $path ) ) ) . '"' );
	header( 'Content-Length: ' . filesize( $path ) );
	readfile( $path );
	exit;
} );

/* ---------- Mô tả đơn ---------- */

function vs_order_summary( $id ) {
	$p    = vs_pricing();
	$data = (array) vs_meta( $id, 'data', array() );
	if ( 'check' === vs_meta( $id, 'type' ) ) {
		$names = array();
		foreach ( (array) ( isset( $data['tools'] ) ? $data['tools'] : array() ) as $t ) {
			$names[] = isset( $p['tools'][ $t ] ) ? $p['tools'][ $t ]['name'] : $t;
		}
		$modes = vs_check_modes();
		return sprintf(
			'Kiểm tra %s · %s · %d trang%s',
			implode( ', ', $names ),
			isset( $modes[ $data['mode'] ] ) ? $modes[ $data['mode'] ] : '',
			(int) $data['pages'],
			! empty( $data['express'] ) ? ' · Hỏa tốc' : ''
		);
	}
	$dl = isset( $p['reduce']['deadlines'][ $data['deadline'] ] ) ? $p['reduce']['deadlines'][ $data['deadline'] ]['label'] : '';
	return sprintf(
		'Giảm đạo văn %s%% → dưới %d%% · %d trang · %s%s',
		(float) $data['current'],
		(int) $data['target'],
		(int) $data['pages'],
		$dl,
		! empty( $data['with_ai'] ) ? ' · Kèm giảm AI' : ''
	);
}

/* ---------- Danh sách đơn trong quản trị ---------- */

add_filter( 'manage_vs_order_posts_columns', function () {
	return array(
		'cb'        => '<input type="checkbox">',
		'title'     => 'Mã đơn',
		'vs_type'   => 'Dịch vụ',
		'vs_cust'   => 'Khách hàng',
		'vs_price'  => 'Thành tiền',
		'vs_status' => 'Trạng thái',
		'date'      => 'Ngày đặt',
	);
} );

add_action( 'manage_vs_order_posts_custom_column', function ( $col, $id ) {
	switch ( $col ) {
		case 'vs_type':
			echo esc_html( vs_order_summary( $id ) );
			break;
		case 'vs_cust':
			echo esc_html( vs_meta( $id, 'name' ) ) . '<br><small>' . esc_html( vs_meta( $id, 'phone' ) ) . '</small>';
			break;
		case 'vs_price':
			echo '<strong>' . esc_html( vs_money( vs_meta( $id, 'price', 0 ) ) ) . '</strong>';
			break;
		case 'vs_status':
			$s = vs_meta( $id, 'status', 'cho_thanh_toan' );
			$l = vs_statuses();
			printf( '<span class="vs-badge vs-badge--%s">%s</span>', esc_attr( $s ), esc_html( isset( $l[ $s ] ) ? $l[ $s ] : $s ) );
			break;
	}
}, 10, 2 );

add_action( 'restrict_manage_posts', function ( $type ) {
	if ( 'vs_order' !== $type ) {
		return;
	}
	$cur = isset( $_GET['vs_status'] ) ? sanitize_key( $_GET['vs_status'] ) : '';
	echo '<select name="vs_status"><option value="">Mọi trạng thái</option>';
	foreach ( vs_statuses() as $k => $l ) {
		printf( '<option value="%s"%s>%s</option>', esc_attr( $k ), selected( $cur, $k, false ), esc_html( $l ) );
	}
	echo '</select>';
} );

add_action( 'pre_get_posts', function ( $q ) {
	if ( ! is_admin() || ! $q->is_main_query() || 'vs_order' !== $q->get( 'post_type' ) ) {
		return;
	}
	if ( ! empty( $_GET['vs_status'] ) ) {
		$q->set( 'meta_key', '_vs_status' );
		$q->set( 'meta_value', sanitize_key( $_GET['vs_status'] ) );
	}
	// Cho phép tìm theo SĐT / email / tên khách.
	$s = $q->get( 's' );
	if ( $s ) {
		$q->set( 's', '' );
		$q->set( 'meta_query', array(
			'relation' => 'OR',
			array( 'key' => '_vs_code', 'value' => $s, 'compare' => 'LIKE' ),
			array( 'key' => '_vs_phone', 'value' => $s, 'compare' => 'LIKE' ),
			array( 'key' => '_vs_email', 'value' => $s, 'compare' => 'LIKE' ),
			array( 'key' => '_vs_name', 'value' => $s, 'compare' => 'LIKE' ),
		) );
	}
} );

add_action( 'admin_head', function () {
	$screen = get_current_screen();
	if ( ! $screen || 'vs_order' !== $screen->post_type ) {
		return;
	}
	?>
	<style>
		.vs-badge{display:inline-block;padding:3px 10px;border-radius:99px;font-size:12px;font-weight:600;background:#eef2ff;color:#3730a3}
		.vs-badge--cho_thanh_toan{background:#fff7ed;color:#c2410c}
		.vs-badge--dang_xu_ly{background:#eff6ff;color:#1d4ed8}
		.vs-badge--hoan_thanh{background:#ecfdf5;color:#047857}
		.vs-badge--huy{background:#f3f4f6;color:#6b7280}
		.vs-info th{text-align:left;width:170px;padding:6px 8px 6px 0;vertical-align:top}
		.vs-info td{padding:6px 0}
		.vs-results input{width:80px}
		.vs-rb-intro{color:#50575e;margin-top:0}
		.vs-rb{width:100%;border-collapse:separate;border-spacing:0 6px}
		.vs-rb th{text-align:left;font-size:12px;text-transform:uppercase;letter-spacing:.04em;color:#787c82;padding:0 8px}
		.vs-rb td{background:#f6f7f7;padding:10px 8px;vertical-align:top}
		.vs-rb td:first-child{border-radius:8px 0 0 8px;width:130px}
		.vs-rb td:last-child{border-radius:0 8px 8px 0}
		.vs-rb-na{color:#a7aaad;text-align:center}
		.vs-rb-cell{display:flex;flex-wrap:wrap;align-items:center;gap:4px}
		.vs-rb-cell input[type=number]{width:84px;font-weight:600}
		.vs-rb-cell input[readonly]{background:#ecfdf5;border-color:#6ee7b7;color:#065f46}
		.vs-rb-pct{color:#787c82}
		.vs-rb-tag{flex-basis:100%;font-size:11.5px;color:#787c82}
		.vs-rb-cell--api .vs-rb-tag{color:#047857}
		.vs-rb-cell--pdf .vs-rb-tag{color:#b45309}
		.vs-rb-cell--wait .vs-rb-tag{color:#2563eb}
		.vs-rb-cell.is-fresh input{background:#fffbeb;border-color:#f59e0b}
		.vs-rb-edit{border:0;background:none;color:#2271b1;cursor:pointer;font-size:12px;padding:0 2px;text-decoration:underline}
		.vs-rb-files a{display:block;font-weight:600;white-space:nowrap}
		.vs-rb-upload{display:flex;flex-wrap:wrap;align-items:center;gap:10px;margin:12px 0}
		.vs-rb-log{background:#1d2327;color:#c3c4c7;font:12px/1.7 monospace;padding:10px 12px;border-radius:6px;margin-bottom:12px}
		.vs-rb-log .is-ok{color:#6ee7b7}.vs-rb-log .is-err{color:#fca5a5}.vs-rb-log .is-skip{color:#fcd34d}.vs-rb-log .is-hint{color:#93c5fd}
		.vs-files li.is-auto em{color:#047857;font-style:normal;font-size:12px}
		.vs-files li{display:flex;gap:8px;align-items:center}
		#titlediv #title{pointer-events:none;background:#f6f7f7}
	</style>
	<?php
} );

/* ---------- Màn hình chi tiết đơn ---------- */

add_action( 'add_meta_boxes_vs_order', function () {
	remove_meta_box( 'submitdiv', 'vs_order', 'side' );
	add_meta_box( 'vs_info', 'Thông tin đơn hàng', 'vs_box_info', 'vs_order', 'normal', 'high' );
	add_meta_box( 'vs_result', 'Kết quả trả khách', 'vs_box_result', 'vs_order', 'normal', 'default' );
	add_meta_box( 'vs_status_box', 'Trạng thái', 'vs_box_status', 'vs_order', 'side', 'high' );
} );

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( in_array( $hook, array( 'post.php', 'post-new.php' ), true ) && 'vs_order' === get_post_type() ) {
		wp_enqueue_media();
		// pdf.js: đọc chữ trong báo cáo PDF ngay trên trình duyệt để tự điền tỷ lệ.
		wp_enqueue_script( 'pdfjs', 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js', array(), '3.11.174', true );
		wp_enqueue_script( 'vs-admin-results', VS_URI . '/assets/js/admin-results.js', array( 'jquery', 'pdfjs', 'media-editor' ), VS_VERSION, true );
		wp_localize_script( 'vs-admin-results', 'VSADM', array( 'worker' => 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js' ) );
	}
} );

function vs_box_info( $post ) {
	$id   = $post->ID;
	$file = vs_meta( $id, 'file' );
	$rows = array(
		'Mã đơn'      => '<strong>' . esc_html( vs_meta( $id, 'code' ) ) . '</strong>',
		'Dịch vụ'     => esc_html( vs_order_summary( $id ) ),
		'Thành tiền'  => '<strong style="font-size:15px">' . esc_html( vs_money( vs_meta( $id, 'price', 0 ) ) ) . '</strong>',
		'Mã giảm giá' => esc_html( vs_meta( $id, 'coupon', '—' ) ),
		'Tài khoản'   => vs_meta( $id, 'user' ) ? '<a href="' . esc_url( get_edit_user_link( (int) vs_meta( $id, 'user' ) ) ) . '">' . esc_html( get_the_author_meta( 'display_name', (int) vs_meta( $id, 'user' ) ) ) . '</a>' : 'Khách vãng lai',
		'Giới thiệu bởi' => vs_meta( $id, 'ref_user' ) ? esc_html( get_the_author_meta( 'display_name', (int) vs_meta( $id, 'ref_user' ) ) . ' · hoa hồng ' . vs_money( vs_meta( $id, 'commission' ) ) . ' (' . vs_comm_labels()[ vs_meta( $id, 'comm_status', 'pending' ) ] . ')' ) : '—',
		'Khách hàng'  => esc_html( vs_meta( $id, 'name' ) ),
		'Điện thoại'  => esc_html( vs_meta( $id, 'phone' ) ) . ( vs_meta( $id, 'phone' ) ? ' · <a target="_blank" href="https://zalo.me/' . esc_attr( preg_replace( '/\D/', '', vs_meta( $id, 'phone' ) ) ) . '">Nhắn Zalo</a>' : '' ),
		'Email'       => esc_html( vs_meta( $id, 'email', '—' ) ),
		'Ghi chú'     => nl2br( esc_html( vs_meta( $id, 'note', '—' ) ) ),
		'File gốc'    => $file
			? '<a class="button" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=vs_download&order=' . $id ), 'vs_dl_' . $id ) ) . '">⬇ Tải ' . esc_html( vs_meta( $id, 'file_name' ) ) . '</a>'
			: '—',
		'Báo cáo khách gửi' => vs_meta( $id, 'src_report' )
			? '<a class="button" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=vs_download&which=report&order=' . $id ), 'vs_dl_' . $id ) ) . '">⬇ Tải ' . esc_html( vs_meta( $id, 'src_report_name' ) ) . '</a>'
			: ( 'reduce' === vs_meta( $id, 'type' ) ? 'Dùng báo cáo của đơn kiểm tra liên kết' : '—' ),
	);
	$linked = vs_meta( $id, 'linked' );
	if ( $linked ) {
		$lid                       = vs_find_order( $linked );
		$rows['Từ đơn kiểm tra']   = $lid ? '<a href="' . esc_url( get_edit_post_link( $lid ) ) . '">' . esc_html( $linked ) . '</a>' : esc_html( $linked );
	}
	echo '<table class="vs-info">';
	foreach ( $rows as $k => $v ) {
		echo '<tr><th>' . esc_html( $k ) . '</th><td>' . $v . '</td></tr>'; // phpcs:ignore -- đã escape ở trên.
	}
	echo '</table>';
}

/** Các ô kết quả cần có của đơn: [ key => [label, kinds[]] ]. */
function vs_result_rows( $id ) {
	$p    = vs_pricing();
	$data = (array) vs_meta( $id, 'data', array() );
	$rows = array();
	if ( 'check' === vs_meta( $id, 'type' ) ) {
		$kinds = 'combo' === $data['mode'] ? array( 'plag', 'ai' ) : array( $data['mode'] );
		foreach ( (array) $data['tools'] as $t ) {
			$rows[ $t ] = array( isset( $p['tools'][ $t ] ) ? $p['tools'][ $t ]['name'] : $t, $kinds );
		}
	} else {
		$rows['final'] = array( 'Sau chỉnh sửa', ! empty( $data['with_ai'] ) ? array( 'plag', 'ai' ) : array( 'plag' ) );
	}
	return $rows;
}

/** Ghi nhận nguồn của 1 số liệu: api | pdf | manual. */
function vs_set_result_src( $id, $tool, $kind, $src, $note = '' ) {
	$s                   = (array) get_post_meta( $id, '_vs_result_src', true );
	$s[ $tool ][ $kind ] = array( 'src' => $src, 'at' => time(), 'note' => $note );
	update_post_meta( $id, '_vs_result_src', $s );
}

/** Đơn đã có đủ mọi kết quả cần có chưa. */
function vs_results_complete( $id ) {
	$res = (array) vs_meta( $id, 'results', array() );
	foreach ( vs_result_rows( $id ) as $key => $row ) {
		foreach ( $row[1] as $k ) {
			if ( ! isset( $res[ $key ][ $k ] ) ) {
				return false;
			}
		}
	}
	return true;
}

function vs_box_result( $post ) {
	$id      = $post->ID;
	$results = (array) vs_meta( $id, 'results', array() );
	$srcs    = (array) get_post_meta( $id, '_vs_result_src', true );
	$files   = array_filter( array_map( 'intval', explode( ',', (string) vs_meta( $id, 'report_ids' ) ) ) );
	$jobs    = (array) get_post_meta( $id, '_vs_jobs', true );
	$auto_f  = function_exists( 'vs_auto_files' ) ? vs_auto_files( $id ) : array();
	$labels  = array( 'plag' => 'Đạo văn', 'ai' => 'Tỷ lệ AI' );
	$src_lbl = array( 'api' => 'Tự động từ API', 'pdf' => 'Đọc từ PDF', 'manual' => 'Nhập tay' );
	?>
	<p class="vs-rb-intro">Số liệu được <b>tự động điền</b> khi phần mềm trả kết quả qua API. Với phần kiểm tra tay, bấm <b>“Tải báo cáo PDF lên”</b> - hệ thống tự đọc tỷ lệ trong báo cáo Turnitin, iThenticate, Copyleaks, Originality.ai.</p>
	<table class="vs-rb">
		<thead><tr><th>Phần mềm</th><?php foreach ( $labels as $l ) { echo '<th>' . esc_html( $l ) . '</th>'; } ?><th>Báo cáo</th></tr></thead>
		<tbody>
		<?php foreach ( vs_result_rows( $id ) as $key => $row ) : ?>
			<tr data-row="<?php echo esc_attr( $key ); ?>" data-name="<?php echo esc_attr( $row[0] ); ?>">
				<td><strong><?php echo esc_html( $row[0] ); ?></strong></td>
				<?php
				foreach ( $labels as $kind => $lbl ) :
					if ( ! in_array( $kind, $row[1], true ) ) {
						echo '<td class="vs-rb-na">—</td>';
						continue;
					}
					$val     = isset( $results[ $key ][ $kind ] ) ? $results[ $key ][ $kind ] : '';
					$src     = isset( $srcs[ $key ][ $kind ]['src'] ) ? $srcs[ $key ][ $kind ]['src'] : '';
					$pending = false;
					foreach ( $jobs as $j ) {
						if ( ! is_array( $j ) || $j['tool'] !== $key || $j['kind'] !== $kind ) {
							continue;
						}
						if ( ! in_array( $j['state'], array( 'done', 'failed' ), true ) ) {
							$pending = true;
						} elseif ( ! $src && 'done' === $j['state'] && '' !== $val ) {
							$src = 'api'; // Kết quả có trước khi ghi nguồn: nhận ra từ job tự động đã xong.
							vs_set_result_src( $id, $key, $kind, 'api' );
						}
					}
					if ( ! $src && '' !== $val ) {
						$src = 'manual';
					}
					$locked = 'api' === $src && '' !== $val;
					?>
					<td>
						<div class="vs-rb-cell vs-rb-cell--<?php echo esc_attr( $src ? $src : ( $pending ? 'wait' : 'empty' ) ); ?>">
							<input type="number" step="0.1" min="0" max="100" data-kind="<?php echo esc_attr( $kind ); ?>" name="vs_results[<?php echo esc_attr( $key ); ?>][<?php echo esc_attr( $kind ); ?>]" value="<?php echo esc_attr( $val ); ?>" <?php echo $locked ? 'readonly' : ''; ?> placeholder="<?php echo $pending ? '...' : ''; ?>"><span class="vs-rb-pct">%</span>
							<input type="hidden" class="vs-rb-src" name="vs_result_src[<?php echo esc_attr( $key ); ?>][<?php echo esc_attr( $kind ); ?>]" value="<?php echo esc_attr( $src ); ?>">
							<?php if ( $locked ) : ?><button type="button" class="vs-rb-edit" title="Sửa tay số liệu tự động">Sửa</button><?php endif; ?>
							<small class="vs-rb-tag"><?php echo $src ? '● ' . esc_html( $src_lbl[ $src ] ) . ( ! empty( $srcs[ $key ][ $kind ]['at'] ) ? ' · ' . esc_html( wp_date( 'H:i d/m', (int) $srcs[ $key ][ $kind ]['at'] ) ) : '' ) : ( $pending ? '● Đang chờ kết quả...' : '○ Chưa có' ); ?></small>
						</div>
					</td>
				<?php endforeach; ?>
				<td class="vs-rb-files">
					<?php
					foreach ( $auto_f as $i => $f ) {
						if ( false !== strpos( $f['path'], '-' . $key . '-' ) ) {
							echo '<a href="' . esc_url( vs_file_url( $id, $i ) ) . '">⬇ PDF tự động</a>';
						}
					}
					?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

	<div class="vs-rb-upload">
		<button type="button" class="button button-primary" id="vs-add-file">📄 Tải báo cáo PDF lên (tự đọc tỷ lệ)</button>
		<span class="description">Có thể chọn nhiều file. File Word đã chỉnh sửa cũng thêm tại đây.</span>
	</div>
	<div id="vs-rb-log" class="vs-rb-log" hidden></div>

	<p><strong>File trả khách</strong></p>
	<ul class="vs-files" id="vs-files">
		<?php foreach ( $auto_f as $i => $f ) : ?>
			<li class="is-auto">🤖 <a href="<?php echo esc_url( vs_file_url( $id, $i ) ); ?>"><?php echo esc_html( $f['name'] ); ?></a> <em>tự động</em></li>
		<?php endforeach; ?>
		<?php foreach ( $files as $fid ) : ?>
			<li data-id="<?php echo (int) $fid; ?>">📎 <?php echo esc_html( get_the_title( $fid ) ?: basename( (string) get_attached_file( $fid ) ) ); ?> <a href="#" class="vs-rm">Gỡ</a></li>
		<?php endforeach; ?>
	</ul>
	<input type="hidden" name="vs_report_ids" id="vs-report-ids" value="<?php echo esc_attr( implode( ',', $files ) ); ?>">
	<p><label><input type="checkbox" name="vs_auto_complete" value="1" checked> Khi đủ kết quả: tự chuyển <b>Hoàn thành</b> và gửi email kèm báo cáo cho khách</label></p>
	<?php
}

function vs_box_status( $post ) {
	wp_nonce_field( 'vs_save_order', 'vs_order_nonce' );
	$cur = vs_meta( $post->ID, 'status', 'cho_thanh_toan' );
	echo '<p><select name="vs_status" style="width:100%">';
	foreach ( vs_statuses() as $k => $l ) {
		printf( '<option value="%s"%s>%s</option>', esc_attr( $k ), selected( $cur, $k, false ), esc_html( $l ) );
	}
	echo '</select></p>';
	echo '<p><label><input type="checkbox" name="vs_notify" value="1" checked> Gửi email cho khách khi chuyển sang “Hoàn thành”</label></p>';
	echo '<p><label>Ghi chú nội bộ<br><textarea name="vs_admin_note" rows="3" style="width:100%">' . esc_textarea( vs_meta( $post->ID, 'admin_note' ) ) . '</textarea></label></p>';
	echo '<p><button type="submit" class="button button-primary button-large" style="width:100%">Cập nhật đơn</button></p>';
	echo '<p style="color:#666">Đặt lúc ' . esc_html( get_the_date( 'H:i d/m/Y', $post ) ) . '</p>';
}

add_action( 'save_post_vs_order', function ( $id ) {
	if ( ! isset( $_POST['vs_order_nonce'] ) || ! wp_verify_nonce( $_POST['vs_order_nonce'], 'vs_save_order' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $id ) ) {
		return;
	}
	$old    = vs_meta( $id, 'status', 'cho_thanh_toan' );
	$status = isset( $_POST['vs_status'] ) ? sanitize_key( $_POST['vs_status'] ) : $old;
	if ( isset( vs_statuses()[ $status ] ) ) {
		update_post_meta( $id, '_vs_status', $status );
		if ( $status !== $old ) {
			do_action( 'vs_order_status', $id, $status, $old );
		}
	}
	// Gộp với kết quả hiện có (kết quả API có thể vừa về trong lúc trang đang mở): không ghi đè số liệu API trừ khi đã bấm "Sửa".
	$results = (array) vs_meta( $id, 'results', array() );
	$srcs    = (array) get_post_meta( $id, '_vs_result_src', true );
	$in_src  = isset( $_POST['vs_result_src'] ) && is_array( $_POST['vs_result_src'] ) ? $_POST['vs_result_src'] : array();
	if ( ! empty( $_POST['vs_results'] ) && is_array( $_POST['vs_results'] ) ) {
		foreach ( $_POST['vs_results'] as $k => $r ) {
			$k = sanitize_key( $k );
			foreach ( array( 'plag', 'ai' ) as $f ) {
				if ( ! isset( $r[ $f ] ) ) {
					continue;
				}
				$posted_src = isset( $in_src[ $k ][ $f ] ) ? sanitize_key( $in_src[ $k ][ $f ] ) : 'manual';
				$was_api    = isset( $srcs[ $k ][ $f ]['src'] ) && 'api' === $srcs[ $k ][ $f ]['src'];
				if ( $was_api && 'api' === $posted_src ) {
					continue; // Giữ nguyên số liệu tự động.
				}
				if ( '' === $r[ $f ] ) {
					if ( ! $was_api ) {
						unset( $results[ $k ][ $f ], $srcs[ $k ][ $f ] );
					}
					continue;
				}
				$val = max( 0, min( 100, round( (float) $r[ $f ], 1 ) ) );
				if ( ! isset( $results[ $k ][ $f ] ) || (float) $results[ $k ][ $f ] !== $val || ! isset( $srcs[ $k ][ $f ] ) ) {
					$srcs[ $k ][ $f ] = array( 'src' => in_array( $posted_src, array( 'pdf', 'manual' ), true ) ? $posted_src : 'manual', 'at' => time(), 'note' => '' );
				}
				$results[ $k ][ $f ] = $val;
			}
			if ( empty( $results[ $k ] ) ) {
				unset( $results[ $k ] );
			}
		}
	}
	update_post_meta( $id, '_vs_results', $results );
	update_post_meta( $id, '_vs_result_src', $srcs );
	$ids = isset( $_POST['vs_report_ids'] ) ? implode( ',', array_filter( array_map( 'intval', explode( ',', $_POST['vs_report_ids'] ) ) ) ) : '';
	update_post_meta( $id, '_vs_report_ids', $ids );
	update_post_meta( $id, '_vs_admin_note', sanitize_textarea_field( wp_unslash( isset( $_POST['vs_admin_note'] ) ? $_POST['vs_admin_note'] : '' ) ) );

	// Đủ kết quả -> tự chuyển Hoàn thành (nếu đang xử lý và có tick).
	if ( 'dang_xu_ly' === $status && ! empty( $_POST['vs_auto_complete'] ) && vs_results_complete( $id ) ) {
		$status = 'hoan_thanh';
		update_post_meta( $id, '_vs_status', 'hoan_thanh' );
		update_post_meta( $id, '_vs_done_at', time() );
		delete_post_meta( $id, '_vs_auto_pending' );
		do_action( 'vs_order_status', $id, 'hoan_thanh', 'dang_xu_ly' );
		$_POST['vs_notify'] = 1;
	}
	if ( 'hoan_thanh' === $status && 'hoan_thanh' !== $old && ! empty( $_POST['vs_notify'] ) ) {
		update_post_meta( $id, '_vs_done_at', time() );
		vs_auto_mail_done( $id );
	}
} );

/* Tiêu đề đơn luôn là mã đơn; trạng thái bài viết luôn "publish" nội bộ. */
add_filter( 'wp_insert_post_data', function ( $data, $arr ) {
	if ( 'vs_order' === $data['post_type'] && ! empty( $arr['ID'] ) ) {
		$code = get_post_meta( $arr['ID'], '_vs_code', true );
		if ( $code ) {
			$data['post_title'] = $code;
		}
		if ( 'trash' !== $data['post_status'] ) {
			$data['post_status'] = 'publish';
		}
	}
	return $data;
}, 10, 2 );

/* Đếm đơn chờ xử lý trên menu. */
add_action( 'admin_menu', function () {
	global $menu;
	$n = count( get_posts( array(
		'post_type'      => 'vs_order',
		'posts_per_page' => 99,
		'fields'         => 'ids',
		'meta_query'     => array( array( 'key' => '_vs_status', 'value' => array( 'cho_thanh_toan', 'dang_xu_ly' ), 'compare' => 'IN' ) ),
	) ) );
	if ( ! $n ) {
		return;
	}
	foreach ( $menu as &$m ) {
		if ( 'edit.php?post_type=vs_order' === $m[2] ) {
			$m[0] .= ' <span class="awaiting-mod">' . $n . '</span>';
		}
	}
}, 99 );

/* ---------- Email ---------- */

function vs_mail_admin_new( $id ) {
	$to = vs_opt( 'notify_email' );
	$body  = "Có đơn hàng mới trên " . get_bloginfo( 'name' ) . "\n\n";
	$body .= 'Mã đơn: ' . vs_meta( $id, 'code' ) . "\n";
	$body .= 'Dịch vụ: ' . vs_order_summary( $id ) . "\n";
	$body .= 'Thành tiền: ' . vs_money( vs_meta( $id, 'price' ) ) . "\n";
	$body .= 'Khách: ' . vs_meta( $id, 'name' ) . ' - ' . vs_meta( $id, 'phone' ) . ' - ' . vs_meta( $id, 'email' ) . "\n";
	$body .= 'Ghi chú: ' . vs_meta( $id, 'note' ) . "\n\n";
	$body .= 'Xem đơn: ' . admin_url( 'post.php?post=' . $id . '&action=edit' );
	wp_mail( $to, '[Đơn mới] ' . vs_meta( $id, 'code' ) . ' - ' . vs_money( vs_meta( $id, 'price' ) ), $body );
}

function vs_mail_customer_new( $id ) {
	$email = vs_meta( $id, 'email' );
	if ( ! is_email( $email ) ) {
		return;
	}
	$body  = 'Chào ' . vs_meta( $id, 'name' ) . ",\n\n";
	$body .= 'Cảm ơn bạn đã đặt hàng tại ' . get_bloginfo( 'name' ) . ".\n\n";
	$body .= 'Mã đơn: ' . vs_meta( $id, 'code' ) . "\n";
	$body .= 'Dịch vụ: ' . vs_order_summary( $id ) . "\n";
	$body .= 'Số tiền: ' . vs_money( vs_meta( $id, 'price' ) ) . "\n\n";
	if ( vs_opt( 'bank_account' ) ) {
		$body .= 'Chuyển khoản: ' . vs_opt( 'bank_name' ) . ' - ' . vs_opt( 'bank_account' ) . ' - ' . vs_opt( 'bank_holder' ) . "\n";
		$body .= 'Nội dung chuyển khoản: ' . vs_meta( $id, 'code' ) . "\n\n";
	}
	$body .= 'Tra cứu đơn tại: ' . vs_page_url( 'tra-cuu-don-hang' ) . "\n";
	wp_mail( $email, 'Xác nhận đơn ' . vs_meta( $id, 'code' ), $body );
}

function vs_mail_customer_done( $id ) {
	$email = vs_meta( $id, 'email' );
	if ( ! is_email( $email ) ) {
		return;
	}
	$body  = 'Chào ' . vs_meta( $id, 'name' ) . ",\n\n";
	$body .= 'Đơn ' . vs_meta( $id, 'code' ) . ' (' . vs_order_summary( $id ) . ") đã hoàn thành.\n\n";
	$body .= 'Xem kết quả và tải báo cáo tại: ' . vs_page_url( 'tra-cuu-don-hang', array( 'ma' => vs_meta( $id, 'code' ) ) ) . "\n";
	$body .= "(Nhập số điện thoại đặt hàng để xem.)\n\n";
	if ( 'check' === vs_meta( $id, 'type' ) ) {
		$body .= "Nếu tỷ lệ đạo văn còn cao, bạn có thể đặt dịch vụ giảm xuống dưới 30% hoặc 20% ngay trên trang tra cứu, được giảm " . vs_pricing()['reduce']['linked_pct'] . "%.\n";
	}
	wp_mail( $email, 'Đơn ' . vs_meta( $id, 'code' ) . ' đã có kết quả', $body );
}

/* ---------- Tự xóa file gốc của khách sau 30 ngày ---------- */

add_action( 'init', function () {
	if ( ! wp_next_scheduled( 'vs_cleanup_files' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'vs_cleanup_files' );
	}
} );

add_action( 'vs_cleanup_files', function () {
	$days = (int) apply_filters( 'vs_file_retention_days', 30 );
	foreach ( glob( vs_private_dir() . '/VS*' ) ?: array() as $f ) {
		if ( is_file( $f ) && filemtime( $f ) < time() - $days * DAY_IN_SECONDS ) {
			unlink( $f );
		}
	}
} );

add_action( 'switch_theme', function () {
	wp_clear_scheduled_hook( 'vs_cleanup_files' );
} );
