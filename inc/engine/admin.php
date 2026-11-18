<?php
/**
 * CDV Scan: AJAX cho app quản trị (Kiểm tra nhanh, Kho đối chiếu, Cài đặt).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Lấy văn bản từ ô nhập hoặc file tải lên (.docx/.txt đọc trên máy chủ; PDF được app đọc sẵn thành chữ). */
function vs_cdv_input_text() {
	$text = isset( $_POST['text'] ) ? (string) wp_unslash( $_POST['text'] ) : ''; // phpcs:ignore -- văn bản thô, chỉ dùng để so khớp và hiển thị đã escape.
	if ( '' === trim( $text ) && ! empty( $_FILES['file']['tmp_name'] ) ) {
		$name = sanitize_file_name( (string) $_FILES['file']['name'] );
		$ext  = strtolower( pathinfo( $name, PATHINFO_EXTENSION ) );
		if ( ! in_array( $ext, array( 'docx', 'txt' ), true ) ) {
			return new WP_Error( 'type', 'Chỉ đọc được file .docx, .txt hoặc .pdf.' );
		}
		if ( (int) $_FILES['file']['size'] > 30 * MB_IN_BYTES ) {
			return new WP_Error( 'size', 'File quá lớn (tối đa 30 MB).' );
		}
		$tmp = wp_tempnam( $name );
		move_uploaded_file( $_FILES['file']['tmp_name'], $tmp );
		$dst = $tmp . '.' . $ext;
		rename( $tmp, $dst );
		$text = vs_extract_text( $dst );
		@unlink( $dst ); // phpcs:ignore
	}
	return mb_substr( $text, 0, 1500000 );
}

function vs_cdv_scan_out( $s ) {
	$pg  = vs_cdv_progress( $s );
	$out = array(
		'id'       => $s['id'],
		'state'    => $s['state'],
		'progress' => $pg[0],
		'label'    => $pg[1],
		'title'    => $s['opt']['title'],
		'web'      => (bool) $s['opt']['web'],
		'webdocs'  => (int) $s['webdocs'],
		'errors'   => array_slice( array_unique( (array) $s['errors'] ), 0, 3 ),
		'created'  => wp_date( 'H:i d/m/Y', $s['created'] ),
	);
	if ( 'done' === $s['state'] ) {
		$r            = $s['result'];
		$out['score'] = $r['score'];
		$out['words'] = $r['words'];
		$out['level'] = vs_cdv_level( $r['score'] );
		$out['n']     = count( $r['sources'] );
		$out['top']   = array_map( function ( $x ) {
			return array( 'n' => $x['n'], 'title' => $x['title'], 'kind' => vs_cdv_kind_label( $x['kind'] ), 'host' => $x['host'], 'pct' => $x['pct'], 'c' => vs_cdv_color( $x['n'] )[1] );
		}, array_slice( $r['sources'], 0, 6 ) );
		$out['view']  = vs_cdv_view_url( $s['id'] );
		$out['pdf']   = vs_cdv_view_url( $s['id'], true );
	}
	return $out;
}

/* Bắt đầu kiểm tra nhanh. */
add_action( 'wp_ajax_vs_cdv_start', function () {
	vs_adm_guard();
	$text = vs_cdv_input_text();
	if ( is_wp_error( $text ) ) {
		wp_send_json_error( array( 'message' => $text->get_error_message() ) );
	}
	$title = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
	$id    = vs_cdv_scan_new( $text, array(
		'title' => $title ? $title : wp_trim_words( $text, 8, '…' ),
		'bib'   => ! empty( $_POST['bib'] ) ? 1 : 0,
		'quote' => ! empty( $_POST['quote'] ) ? 1 : 0,
		'web'   => ! empty( $_POST['web'] ) && vs_cdv_web_ready(),
		'repo'  => ! empty( $_POST['repo'] ) ? 1 : 0,
	) );
	if ( is_wp_error( $id ) ) {
		wp_send_json_error( array( 'message' => $id->get_error_message() ) );
	}
	$recent = (array) get_option( 'vs_cdv_recent', array() );
	array_unshift( $recent, $id );
	update_option( 'vs_cdv_recent', array_slice( array_values( array_unique( $recent ) ), 0, 30 ), false );
	wp_send_json_success( vs_cdv_scan_out( vs_cdv_scan( $id ) ) );
} );

/* Chạy tiếp (app gọi lặp lại để hiện thanh tiến độ). */
add_action( 'wp_ajax_vs_cdv_step', function () {
	vs_adm_guard();
	$id = isset( $_POST['id'] ) ? sanitize_key( wp_unslash( $_POST['id'] ) ) : '';
	$s  = vs_cdv_scan_step( $id, 12 );
	if ( ! $s ) {
		wp_send_json_error( array( 'message' => 'Không tìm thấy lượt kiểm tra.' ) );
	}
	wp_send_json_success( vs_cdv_scan_out( $s ) );
} );

/* Tổng quan: thống kê kho + lượt kiểm tra gần đây + cấu hình. */
add_action( 'wp_ajax_vs_cdv_home', function () {
	vs_adm_guard();
	$recent = array();
	foreach ( (array) get_option( 'vs_cdv_recent', array() ) as $id ) {
		$s = vs_cdv_scan( $id );
		if ( $s ) {
			$recent[] = vs_cdv_scan_out( $s );
		}
	}
	$c = vs_cdv_cfg();
	foreach ( array( 'gkey', 'skey' ) as $k ) {
		$c[ $k ] = $c[ $k ] ? '••••' . substr( $c[ $k ], -4 ) : '';
	}
	wp_send_json_success( array(
		'stats'  => vs_cdv_stats(),
		'recent' => array_slice( $recent, 0, 12 ),
		'cfg'    => $c,
		'webok'  => vs_cdv_web_ready(),
		'admin'  => current_user_can( 'manage_options' ),
	) );
} );

/* Kho đối chiếu: danh sách. */
add_action( 'wp_ajax_vs_cdv_docs', function () {
	vs_adm_guard();
	global $wpdb;
	$kind  = isset( $_POST['kind'] ) ? sanitize_key( $_POST['kind'] ) : '';
	$q     = isset( $_POST['q'] ) ? sanitize_text_field( wp_unslash( $_POST['q'] ) ) : '';
	$page  = max( 1, (int) ( isset( $_POST['page'] ) ? $_POST['page'] : 1 ) );
	$where = '1=1';
	$args  = array();
	if ( in_array( $kind, array( 'upload', 'order', 'web' ), true ) ) {
		$where .= ' AND kind = %s';
		$args[] = $kind;
	}
	if ( '' !== $q ) {
		$where .= ' AND (title LIKE %s OR url LIKE %s)';
		$args[] = '%' . $wpdb->esc_like( $q ) . '%';
		$args[] = '%' . $wpdb->esc_like( $q ) . '%';
	}
	$t     = vs_cdv_t( 'docs' );
	$total = (int) $wpdb->get_var( $args ? $wpdb->prepare( "SELECT COUNT(*) FROM $t WHERE $where", $args ) : "SELECT COUNT(*) FROM $t WHERE $where" ); // phpcs:ignore
	$sql   = "SELECT id, kind, title, url, words, ref, created FROM $t WHERE $where ORDER BY id DESC LIMIT 20 OFFSET " . ( ( $page - 1 ) * 20 );
	$rows  = $wpdb->get_results( $args ? $wpdb->prepare( $sql, $args ) : $sql, ARRAY_A ); // phpcs:ignore
	wp_send_json_success( array(
		'total' => $total,
		'pages' => max( 1, (int) ceil( $total / 20 ) ),
		'page'  => $page,
		'items' => array_map( function ( $r ) {
			return array(
				'id'    => (int) $r['id'],
				'kind'  => $r['kind'],
				'kl'    => vs_cdv_kind_label( $r['kind'] ),
				'title' => $r['title'],
				'url'   => $r['url'],
				'words' => (int) $r['words'],
				'code'  => $r['ref'] ? (string) vs_meta( (int) $r['ref'], 'code' ) : '',
				'date'  => wp_date( 'd/m/Y', (int) $r['created'] ),
			);
		}, (array) $rows ),
		'stats' => vs_cdv_stats(),
	) );
} );

/* Kho đối chiếu: thêm tài liệu. */
add_action( 'wp_ajax_vs_cdv_doc_add', function () {
	vs_adm_guard();
	$text = vs_cdv_input_text();
	if ( is_wp_error( $text ) ) {
		wp_send_json_error( array( 'message' => $text->get_error_message() ) );
	}
	$title = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
	$id    = vs_cdv_add_doc( 'upload', $title ? $title : wp_trim_words( $text, 8, '…' ), $text );
	if ( is_wp_error( $id ) ) {
		wp_send_json_error( array( 'message' => $id->get_error_message() ) );
	}
	wp_send_json_success( array( 'id' => $id, 'stats' => vs_cdv_stats() ) );
} );

/* Kho đối chiếu: xóa. */
add_action( 'wp_ajax_vs_cdv_doc_del', function () {
	vs_adm_guard();
	vs_cdv_del_doc( isset( $_POST['id'] ) ? (int) $_POST['id'] : 0 );
	wp_send_json_success( array( 'stats' => vs_cdv_stats() ) );
} );

/* Cài đặt (chỉ quản trị viên). */
add_action( 'wp_ajax_vs_cdv_cfg', function () {
	vs_adm_guard();
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Chỉ quản trị viên được đổi cài đặt.' ), 403 );
	}
	$c   = vs_cdv_cfg();
	$in  = wp_unslash( $_POST );
	$new = array(
		'on'      => ! empty( $in['on'] ) ? 1 : '',
		'name'    => isset( $in['name'] ) && '' !== trim( $in['name'] ) ? sanitize_text_field( $in['name'] ) : 'CDV Scan',
		'price'   => isset( $in['price'] ) ? max( 1000, (int) $in['price'] ) : $c['price'],
		'web'     => isset( $in['web'] ) && in_array( $in['web'], array( 'none', 'google', 'serper' ), true ) ? $in['web'] : 'none',
		'gcx'     => isset( $in['gcx'] ) ? sanitize_text_field( $in['gcx'] ) : $c['gcx'],
		'queries' => isset( $in['queries'] ) ? max( 3, min( 60, (int) $in['queries'] ) ) : $c['queries'],
		'pages'   => isset( $in['pages'] ) ? max( 5, min( 100, (int) $in['pages'] ) ) : $c['pages'],
		'minw'    => isset( $in['minw'] ) ? max( 10, min( 40, (int) $in['minw'] ) ) : $c['minw'],
		'bib'     => ! empty( $in['bib'] ) ? 1 : 0,
		'quote'   => ! empty( $in['quote'] ) ? 1 : 0,
		'repo'    => ! empty( $in['repo'] ) ? 1 : 0,
	);
	// Khóa API: chỉ đổi khi nhập giá trị mới (ô hiện ••••1234).
	foreach ( array( 'gkey', 'skey' ) as $k ) {
		$v         = isset( $in[ $k ] ) ? trim( sanitize_text_field( $in[ $k ] ) ) : '';
		$new[ $k ] = '' === $v ? '' : ( 0 === strpos( $v, '••••' ) ? $c[ $k ] : $v );
	}
	update_option( 'vs_cdv', array_merge( $c, $new ), false );
	$test = 'none' !== $new['web'] ? vs_cdv_web_test() : '';
	wp_send_json_success( array(
		'message' => 'Đã lưu cài đặt ' . $new['name'] . '.',
		'test'    => is_wp_error( $test ) ? array( 'ok' => false, 'm' => $test->get_error_message() ) : ( $test ? array( 'ok' => true, 'm' => $test ) : null ),
	) );
} );
