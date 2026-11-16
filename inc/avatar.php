<?php
/**
 * Ảnh đại diện (avatar) của tài khoản: tải lên 1 lần, hiển thị đồng bộ ở mọi nơi
 * (header website, trang Tài khoản, app quản trị app.<tên miền>, WordPress, bình luận).
 *
 * - Ảnh được cắt vuông, thu nhỏ còn 400x400 và lưu tại uploads/vs-avatars/.
 * - Không dùng Thư viện Media (không làm rối thư viện ảnh).
 * - Thay thế Gravatar qua bộ lọc pre_get_avatar_data nên mọi chỗ gọi get_avatar() đều dùng ảnh mới.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Thư mục lưu ảnh đại diện. */
function vs_avatar_dir() {
	$up = wp_upload_dir();
	return array(
		'path' => trailingslashit( $up['basedir'] ) . 'vs-avatars',
		'url'  => trailingslashit( $up['baseurl'] ) . 'vs-avatars',
	);
}

/** URL ảnh đại diện riêng của tài khoản ('' nếu chưa có). */
function vs_avatar_url( $uid ) {
	$a = get_user_meta( (int) $uid, '_vs_avatar', true );
	if ( ! is_array( $a ) || empty( $a['file'] ) ) {
		return '';
	}
	$d = vs_avatar_dir();
	if ( ! file_exists( $d['path'] . '/' . basename( $a['file'] ) ) ) {
		return '';
	}
	return set_url_scheme( $d['url'] . '/' . rawurlencode( basename( $a['file'] ) ) );
}

/** Chữ cái đầu của tên (dùng khi chưa có ảnh). */
function vs_avatar_initial( $uid ) {
	$u = get_userdata( (int) $uid );
	$w = preg_split( '/\s+/u', trim( $u ? $u->display_name : '' ) );
	$c = $w ? end( $w ) : '';
	return $c ? mb_strtoupper( mb_substr( $c, 0, 1 ) ) : '?';
}

/** HTML ảnh đại diện: ảnh nếu có, ngược lại là chữ cái đầu. */
function vs_avatar_html( $uid, $class = 'avatar' ) {
	$url = vs_avatar_url( $uid );
	$ini = vs_avatar_initial( $uid );
	return '<span class="' . esc_attr( $class ) . ( $url ? ' has-img' : '' ) . '" data-avatar data-initial="' . esc_attr( $ini ) . '">' .
		( $url ? '<img src="' . esc_url( $url ) . '" alt="" loading="lazy" decoding="async">' : esc_html( $ini ) ) . '</span>';
}

/* Dùng ảnh riêng thay Gravatar ở mọi nơi trong WordPress. */
add_filter( 'pre_get_avatar_data', function ( $args, $id_or_email ) {
	$uid = 0;
	if ( is_numeric( $id_or_email ) ) {
		$uid = (int) $id_or_email;
	} elseif ( $id_or_email instanceof WP_User ) {
		$uid = $id_or_email->ID;
	} elseif ( $id_or_email instanceof WP_Post ) {
		$uid = (int) $id_or_email->post_author;
	} elseif ( $id_or_email instanceof WP_Comment ) {
		$uid = (int) $id_or_email->user_id;
	} elseif ( is_string( $id_or_email ) && is_email( $id_or_email ) ) {
		$u   = get_user_by( 'email', $id_or_email );
		$uid = $u ? $u->ID : 0;
	}
	$url = $uid ? vs_avatar_url( $uid ) : '';
	if ( $url ) {
		$args['url']          = $url;
		$args['found_avatar'] = true;
	}
	return $args;
}, 10, 2 );

/** Xóa file ảnh cũ của tài khoản. */
function vs_avatar_delete_file( $uid ) {
	$a = get_user_meta( (int) $uid, '_vs_avatar', true );
	if ( is_array( $a ) && ! empty( $a['file'] ) ) {
		$f = vs_avatar_dir()['path'] . '/' . basename( $a['file'] );
		if ( file_exists( $f ) ) {
			wp_delete_file( $f );
		}
	}
	delete_user_meta( (int) $uid, '_vs_avatar' );
}

/* ---------- Tải lên / xóa ảnh đại diện (website và app dùng chung) ---------- */
add_action( 'wp_ajax_vs_avatar', function () {
	nocache_headers();
	$uid = get_current_user_id();
	if ( ! $uid || ! check_ajax_referer( 'vs_avatar', 'nonce', false ) ) {
		wp_send_json_error( array( 'message' => 'Phiên đăng nhập đã hết hạn, vui lòng tải lại trang.' ), 403 );
	}
	$op = isset( $_POST['op'] ) ? sanitize_key( $_POST['op'] ) : 'upload';

	if ( 'remove' === $op ) {
		vs_avatar_delete_file( $uid );
		wp_send_json_success( array( 'url' => '', 'initial' => vs_avatar_initial( $uid ), 'message' => 'Đã xóa ảnh đại diện.' ) );
	}

	// Giới hạn: tối đa 10 lần đổi ảnh mỗi giờ cho 1 tài khoản.
	$rk = 'vs_rl_avatar_' . $uid;
	$n  = (int) get_transient( $rk );
	if ( $n >= 10 ) {
		wp_send_json_error( array( 'message' => 'Bạn đổi ảnh quá nhiều lần. Vui lòng thử lại sau.' ), 429 );
	}

	if ( empty( $_FILES['file'] ) || ! is_uploaded_file( $_FILES['file']['tmp_name'] ) || UPLOAD_ERR_OK !== (int) $_FILES['file']['error'] ) {
		wp_send_json_error( array( 'message' => 'Chưa nhận được ảnh. Vui lòng chọn lại.' ) );
	}
	$file = $_FILES['file'];
	if ( $file['size'] > 5 * MB_IN_BYTES ) {
		wp_send_json_error( array( 'message' => 'Ảnh quá lớn (tối đa 5 MB).' ) );
	}
	$mimes = array( 'jpg|jpeg|jpe' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'gif' => 'image/gif' );
	$check = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'], $mimes );
	$info  = @getimagesize( $file['tmp_name'] ); // phpcs:ignore
	if ( empty( $check['type'] ) || ! $info || ! in_array( $info['mime'], $mimes, true ) ) {
		wp_send_json_error( array( 'message' => 'Chỉ nhận ảnh JPG, PNG, WEBP hoặc GIF.' ) );
	}
	if ( $info[0] < 64 || $info[1] < 64 ) {
		wp_send_json_error( array( 'message' => 'Ảnh quá nhỏ (tối thiểu 64 x 64 điểm ảnh).' ) );
	}
	if ( $info[0] * $info[1] > 40000000 ) {
		wp_send_json_error( array( 'message' => 'Ảnh có kích thước quá lớn, vui lòng chọn ảnh khác.' ) );
	}

	$ed = wp_get_image_editor( $file['tmp_name'] );
	if ( is_wp_error( $ed ) ) {
		wp_send_json_error( array( 'message' => 'Máy chủ không xử lý được ảnh này. Vui lòng thử ảnh khác.' ) );
	}
	if ( method_exists( $ed, 'maybe_exif_rotate' ) ) {
		$ed->maybe_exif_rotate(); // Ảnh chụp điện thoại: xoay đúng chiều.
	}
	$ed->resize( 400, 400, true );
	$ed->set_quality( 86 );

	$d = vs_avatar_dir();
	if ( ! wp_mkdir_p( $d['path'] ) ) {
		wp_send_json_error( array( 'message' => 'Không tạo được thư mục lưu ảnh.' ) );
	}
	if ( ! file_exists( $d['path'] . '/index.html' ) ) {
		file_put_contents( $d['path'] . '/index.html', '' ); // Không cho liệt kê thư mục.
	}
	$name  = $uid . '-' . strtolower( wp_generate_password( 12, false ) ) . '.jpg';
	$saved = $ed->save( $d['path'] . '/' . $name, 'image/jpeg' );
	if ( is_wp_error( $saved ) || empty( $saved['path'] ) ) {
		wp_send_json_error( array( 'message' => 'Không lưu được ảnh. Vui lòng thử lại.' ) );
	}
	vs_avatar_delete_file( $uid );
	update_user_meta( $uid, '_vs_avatar', array( 'file' => basename( $saved['path'] ), 't' => time() ) );
	set_transient( $rk, $n + 1, HOUR_IN_SECONDS );
	wp_send_json_success( array( 'url' => vs_avatar_url( $uid ), 'initial' => vs_avatar_initial( $uid ), 'message' => 'Đã cập nhật ảnh đại diện.' ) );
} );

/* Xóa tài khoản: xóa luôn file ảnh. */
add_action( 'delete_user', 'vs_avatar_delete_file' );
