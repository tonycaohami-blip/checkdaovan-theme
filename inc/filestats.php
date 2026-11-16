<?php
/**
 * Thông tin file toàn web: định dạng, dung lượng và số lượt tải.
 *
 * Mọi lượt tải đều đi qua máy chủ để đếm:
 *  - File khách gửi (tài liệu / báo cáo gốc): ?vs_src=… (khách) và admin-post vs_download (quản trị).
 *  - Báo cáo PDF tự động: ?vs_file=…
 *  - Báo cáo PDF quản trị tải lên (Thư viện Media): ?vs_att=… (trước đây dẫn thẳng tới file).
 *  - Link Google Drive: ?vs_go=… đếm rồi chuyển sang Drive (trước đây dẫn thẳng tới Drive).
 * Số lượt lưu trong meta _vs_dl của đơn: [ khóa file => số lượt ].
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Số lượt tải của 1 file trong đơn. */
function vs_dl_count( $id, $key ) {
	$m = get_post_meta( (int) $id, '_vs_dl', true );
	return is_array( $m ) && isset( $m[ $key ] ) ? (int) $m[ $key ] : 0;
}

/** Ghi nhận 1 lượt tải (bỏ qua yêu cầu kiểm tra HEAD và trình thu thập). */
function vs_dl_hit( $id, $key ) {
	if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'HEAD' === $_SERVER['REQUEST_METHOD'] ) {
		return;
	}
	$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? (string) $_SERVER['HTTP_USER_AGENT'] : '';
	if ( '' === $ua || preg_match( '/bot|crawl|spider|facebookexternalhit|slurp|curl\/|wget|python-requests|headless/i', $ua ) ) {
		return; // Xem trước link trong Zalo/Facebook, bot: không tính.
	}
	$m         = get_post_meta( (int) $id, '_vs_dl', true );
	$m         = is_array( $m ) ? $m : array();
	$m[ $key ] = ( isset( $m[ $key ] ) ? (int) $m[ $key ] : 0 ) + 1;
	update_post_meta( (int) $id, '_vs_dl', $m );
}

/** Dung lượng dễ đọc: 845 KB, 2,4 MB. */
function vs_size_h( $bytes ) {
	$bytes = (int) $bytes;
	if ( $bytes <= 0 ) {
		return '';
	}
	if ( $bytes < 1024 ) {
		return $bytes . ' B';
	}
	if ( $bytes < MB_IN_BYTES ) {
		return number_format_i18n( $bytes / 1024, $bytes < 10240 ? 1 : 0 ) . ' KB';
	}
	if ( $bytes < GB_IN_BYTES ) {
		return number_format( $bytes / MB_IN_BYTES, 1, ',', '.' ) . ' MB';
	}
	return number_format( $bytes / GB_IN_BYTES, 2, ',', '.' ) . ' GB';
}

/** Định dạng (đuôi file, viết hoa) từ tên file. */
function vs_file_ext( $name, $fallback = 'FILE' ) {
	$e = strtoupper( (string) pathinfo( (string) $name, PATHINFO_EXTENSION ) );
	return preg_match( '/^[A-Z0-9]{1,5}$/', $e ) ? $e : $fallback;
}

/** Thông tin chung của 1 file: ext, size (chữ), bytes, dl (số lượt), dl_text. */
function vs_file_stats( $id, $key, $name, $path = '', $ext_fallback = 'FILE' ) {
	$bytes = $path && is_file( $path ) ? (int) filesize( $path ) : 0;
	$n     = vs_dl_count( $id, $key );
	return array(
		'ext'     => vs_file_ext( $name, $ext_fallback ),
		'size'    => vs_size_h( $bytes ),
		'bytes'   => $bytes,
		'dl'      => $n,
		'dl_text' => $n . ' lượt tải',
		'key'     => $key,
	);
}

/** Dòng mô tả gọn: "PDF · 2,4 MB · 3 lượt tải". */
function vs_file_meta_text( $s, $extra = '' ) {
	return implode( ' · ', array_filter( array( $s['ext'], $extra ? $extra : $s['size'], $s['dl_text'] ) ) );
}

/* ---------- Liên kết có chữ ký: báo cáo PDF quản trị tải lên ---------- */
function vs_att_key( $id, $fid ) {
	return substr( wp_hash( $id . '|att|' . $fid . '|' . vs_meta( $id, 'code' ), 'auth' ), 0, 24 );
}
function vs_att_url( $id, $fid ) {
	return add_query_arg( array( 'vs_att' => (int) $fid, 'o' => (int) $id, 'k' => vs_att_key( $id, $fid ) ), home_url( '/' ) );
}

/* ---------- Liên kết có chữ ký: Google Drive (đếm rồi chuyển hướng) ---------- */
function vs_go_key( $id, $did, $mode ) {
	return substr( wp_hash( $id . '|go|' . $did . '|' . $mode . '|' . vs_meta( $id, 'code' ), 'auth' ), 0, 24 );
}
function vs_go_url( $id, $did, $mode ) {
	return add_query_arg( array( 'vs_go' => (int) $id, 'd' => rawurlencode( $did ), 'm' => $mode, 'k' => vs_go_key( $id, $did, $mode ) ), home_url( '/' ) );
}

add_action( 'template_redirect', function () {
	// Báo cáo PDF trong Thư viện Media.
	if ( ! empty( $_GET['vs_att'] ) ) {
		$fid = (int) $_GET['vs_att'];
		$id  = isset( $_GET['o'] ) ? (int) $_GET['o'] : 0;
		$k   = isset( $_GET['k'] ) ? (string) $_GET['k'] : '';
		$ids = array_filter( array_map( 'intval', explode( ',', (string) vs_meta( $id, 'report_ids' ) ) ) );
		if ( 'vs_order' !== get_post_type( $id ) || ! in_array( $fid, $ids, true ) || ! hash_equals( vs_att_key( $id, $fid ), $k ) ) {
			wp_die( 'Liên kết không hợp lệ hoặc đã hết hạn.', 'Không tải được file', array( 'response' => 403 ) );
		}
		$path = (string) get_attached_file( $fid );
		if ( ! $path || ! is_file( $path ) ) {
			wp_die( 'File đã được xóa theo chính sách lưu trữ.', 'Không tìm thấy file', array( 'response' => 404 ) );
		}
		vs_dl_hit( $id, 'att:' . $fid );
		$name = basename( $path );
		$type = wp_check_filetype( $name );
		nocache_headers();
		header( 'Content-Type: ' . ( $type['type'] ? $type['type'] : 'application/octet-stream' ) );
		header( "Content-Disposition: attachment; filename=\"" . sanitize_file_name( remove_accents( $name ) ) . "\"; filename*=UTF-8''" . rawurlencode( $name ) );
		header( 'Content-Length: ' . filesize( $path ) );
		header( 'X-Robots-Tag: noindex' );
		readfile( $path );
		exit;
	}
	// Google Drive: đếm rồi chuyển sang Drive.
	if ( ! empty( $_GET['vs_go'] ) ) {
		$id   = (int) $_GET['vs_go'];
		$did  = isset( $_GET['d'] ) ? rawurldecode( (string) $_GET['d'] ) : '';
		$mode = isset( $_GET['m'] ) && 'view' === $_GET['m'] ? 'view' : 'dl';
		$k    = isset( $_GET['k'] ) ? (string) $_GET['k'] : '';
		$item = null;
		foreach ( (array) get_post_meta( $id, '_vs_drive', true ) as $it ) {
			if ( ! empty( $it['id'] ) && $it['id'] === $did ) {
				$item = $it;
			}
		}
		if ( ! $item || ! hash_equals( vs_go_key( $id, $did, $mode ), $k ) ) {
			wp_die( 'Liên kết không hợp lệ hoặc đã hết hạn.', 'Không mở được file', array( 'response' => 403 ) );
		}
		vs_dl_hit( $id, 'drive:' . $did );
		$u = vs_drive_urls( $item );
		nocache_headers();
		header( 'X-Robots-Tag: noindex' );
		wp_redirect( 'view' === $mode ? $u['view'] : $u['download'] ); // phpcs:ignore -- đích luôn là drive.google.com.
		exit;
	}
}, 5 );

/* Đếm lượt tải ở các đường tải đã có sẵn (chạy trước trình xử lý gốc). */
add_action( 'template_redirect', function () {
	if ( ! empty( $_GET['vs_file'] ) ) {
		$id    = (int) $_GET['vs_file'];
		$i     = isset( $_GET['i'] ) ? (int) $_GET['i'] : -1;
		$k     = isset( $_GET['k'] ) ? (string) $_GET['k'] : '';
		$files = function_exists( 'vs_auto_files' ) ? vs_auto_files( $id ) : array();
		if ( isset( $files[ $i ] ) && hash_equals( vs_file_key( $id, $i ), $k ) && in_array( vs_meta( $id, 'status' ), array( 'dang_xu_ly', 'hoan_thanh' ), true ) && is_file( vs_report_dir() . '/' . basename( $files[ $i ]['path'] ) ) ) {
			vs_dl_hit( $id, 'auto:' . basename( $files[ $i ]['path'] ) );
		}
	}
	if ( ! empty( $_GET['vs_src'] ) ) {
		$id    = (int) $_GET['vs_src'];
		$which = isset( $_GET['w'] ) && 'src_report' === $_GET['w'] ? 'src_report' : 'file';
		$k     = isset( $_GET['k'] ) ? (string) $_GET['k'] : '';
		if ( 'vs_order' === get_post_type( $id ) && vs_meta( $id, $which ) && hash_equals( vs_src_key( $id, $which ), $k ) && is_file( vs_private_dir() . '/' . basename( vs_meta( $id, $which ) ) ) ) {
			vs_dl_hit( $id, 'src:' . $which );
		}
	}
}, 1 );

add_action( 'admin_post_vs_download', function () {
	$id    = isset( $_GET['order'] ) ? (int) $_GET['order'] : 0;
	$which = isset( $_GET['which'] ) && 'report' === $_GET['which'] ? 'src_report' : 'file';
	if ( $id && current_user_can( 'edit_post', $id ) && wp_verify_nonce( isset( $_GET['_wpnonce'] ) ? $_GET['_wpnonce'] : '', 'vs_dl_' . $id ) && vs_meta( $id, $which ) && is_file( vs_private_dir() . '/' . basename( vs_meta( $id, $which ) ) ) ) {
		vs_dl_hit( $id, 'src:' . $which );
	}
}, 1 );
