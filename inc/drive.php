<?php
/**
 * Trả kết quả bằng link Google Drive (cho báo cáo PDF dung lượng lớn).
 *
 * Quản trị dán link chia sẻ của Google Drive (mỗi dòng 1 link, có thể ghi tên sau dấu |).
 * Khách xem trực tiếp (trình xem của Google Drive) hoặc tải xuống từ Drive, không qua máy chủ website.
 * Lưu ý: file/thư mục trên Drive phải chia sẻ "Bất kỳ ai có đường liên kết - Người xem".
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Đọc link Google Drive: trả về [kind => file|folder, id] hoặc null nếu không phải link Drive hợp lệ.
 * Hỗ trợ: drive.google.com/file/d/ID/..., open?id=ID, uc?id=ID, /drive/folders/ID, docs.google.com/.../d/ID.
 */
function vs_drive_parse( $url ) {
	$url  = trim( (string) $url );
	$host = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );
	if ( ! in_array( $host, array( 'drive.google.com', 'docs.google.com', 'drive.usercontent.google.com' ), true ) ) {
		return null;
	}
	$path = (string) wp_parse_url( $url, PHP_URL_PATH );
	if ( preg_match( '#/folders/([A-Za-z0-9_-]{10,})#', $path, $m ) ) {
		return array( 'kind' => 'folder', 'id' => $m[1] );
	}
	if ( preg_match( '#/d/([A-Za-z0-9_-]{10,})#', $path, $m ) ) {
		return array( 'kind' => 'file', 'id' => $m[1] );
	}
	parse_str( (string) wp_parse_url( $url, PHP_URL_QUERY ), $q );
	if ( ! empty( $q['id'] ) && preg_match( '/^[A-Za-z0-9_-]{10,}$/', $q['id'] ) ) {
		return array( 'kind' => 'file', 'id' => $q['id'] );
	}
	return null;
}

/** Link xem / tải của một mục Drive đã lưu. */
function vs_drive_urls( $item ) {
	$id = rawurlencode( $item['id'] );
	if ( 'folder' === $item['kind'] ) {
		$view = 'https://drive.google.com/drive/folders/' . $id;
		return array( 'view' => $view, 'download' => $view ); // Thư mục: tải từng file hoặc "Tải xuống tất cả" ngay trên Drive.
	}
	return array(
		'view'     => 'https://drive.google.com/file/d/' . $id . '/view',
		'download' => 'https://drive.google.com/uc?export=download&id=' . $id,
	);
}

/** Danh sách link Drive của đơn (đã chuẩn hóa) để hiển thị. */
function vs_drive_links( $id ) {
	$out = array();
	foreach ( (array) get_post_meta( $id, '_vs_drive', true ) as $i => $it ) {
		if ( empty( $it['id'] ) ) {
			continue;
		}
		$name  = '' !== $it['name'] ? $it['name'] : ( 'folder' === $it['kind'] ? 'Thư mục kết quả ' . ( $i + 1 ) : 'Báo cáo ' . ( $i + 1 ) );
		$out[] = array_merge( vs_drive_urls( $it ), array(
			'kind'  => $it['kind'],
			'name'  => $name,
			'short' => vs_short_filename( $name, 34 ),
			'drive' => true,
		) );
	}
	return $out;
}

/** Nội dung ô nhập (mỗi dòng: link | tên). */
function vs_drive_text( $id ) {
	$lines = array();
	foreach ( (array) get_post_meta( $id, '_vs_drive', true ) as $it ) {
		if ( ! empty( $it['url'] ) ) {
			$lines[] = $it['url'] . ( '' !== $it['name'] ? ' | ' . $it['name'] : '' );
		}
	}
	return implode( "\n", $lines );
}

/**
 * Lưu link Drive từ ô nhập. Trả về [số link đã lưu, danh sách dòng lỗi].
 * Có link mới khi đơn đang xử lý/hoàn thành: báo cho khách (chuông thông báo).
 */
function vs_drive_save( $id, $text ) {
	$items = array();
	$bad   = array();
	foreach ( preg_split( '/\r?\n/', (string) $text ) as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}
		$parts = array_map( 'trim', explode( '|', $line, 2 ) );
		$p     = vs_drive_parse( $parts[0] );
		if ( ! $p ) {
			$bad[] = $line;
			continue;
		}
		$items[] = array(
			'url'  => esc_url_raw( $parts[0] ),
			'id'   => $p['id'],
			'kind' => $p['kind'],
			'name' => isset( $parts[1] ) ? sanitize_text_field( $parts[1] ) : '',
		);
	}
	$old_ids = wp_list_pluck( (array) get_post_meta( $id, '_vs_drive', true ), 'id' );
	update_post_meta( $id, '_vs_drive', $items );
	$new = array_diff( wp_list_pluck( $items, 'id' ), $old_ids );
	if ( $new && in_array( vs_meta( $id, 'status' ), array( 'dang_xu_ly', 'hoan_thanh' ), true ) ) {
		$uid = (int) vs_meta( $id, 'user' );
		if ( $uid && function_exists( 'vs_notify_user' ) ) {
			vs_notify_user( $uid, 'done', 'Đơn ' . vs_meta( $id, 'code' ) . ' có file kết quả mới', 'Bấm để xem hoặc tải báo cáo từ Google Drive.', vs_order_link_for_user( $id ), 'drive-' . $id . '-' . md5( implode( ',', $new ) ) );
		}
		if ( function_exists( 'vs_auto_log' ) ) {
			vs_auto_log( 'Đã thêm ' . count( $new ) . ' link Google Drive trả kết quả', $id );
		}
	}
	return array( count( $items ), $bad );
}

/** Ô nhập link Drive trong trang sửa đơn (WP admin). */
function vs_drive_admin_field( $id ) {
	?>
	<p><strong>Link Google Drive trả khách</strong> <span class="description">(dùng cho file PDF nặng - không cần tải lên website)</span></p>
	<textarea name="vs_drive" rows="3" style="width:100%;font-family:monospace" placeholder="https://drive.google.com/file/d/…/view | Báo cáo Turnitin&#10;https://drive.google.com/drive/folders/… | Thư mục kết quả"><?php echo esc_textarea( vs_drive_text( $id ) ); ?></textarea>
	<p class="description">Mỗi dòng 1 link file hoặc thư mục. Tên hiển thị (không bắt buộc) ghi sau dấu <code>|</code>. Trên Google Drive nhớ chọn <b>Chia sẻ → Bất kỳ ai có đường liên kết → Người xem</b>, nếu không khách sẽ không mở được.</p>
	<?php
	$bad = get_transient( 'vs_drive_bad_' . $id );
	if ( $bad ) {
		delete_transient( 'vs_drive_bad_' . $id );
		echo '<div class="notice notice-error inline"><p>Không nhận các dòng không phải link Google Drive: ' . esc_html( implode( ' ; ', $bad ) ) . '</p></div>';
	}
}

add_action( 'save_post_vs_order', function ( $id ) {
	if ( ! isset( $_POST['vs_order_nonce'], $_POST['vs_drive'] ) || ! wp_verify_nonce( $_POST['vs_order_nonce'], 'vs_save_order' ) || ! current_user_can( 'edit_post', $id ) ) {
		return;
	}
	list( , $bad ) = vs_drive_save( $id, sanitize_textarea_field( wp_unslash( $_POST['vs_drive'] ) ) );
	if ( $bad ) {
		set_transient( 'vs_drive_bad_' . $id, $bad, 120 );
	}
}, 5 ); // Chạy trước phần tự chuyển "Hoàn thành" để email có kèm link.
