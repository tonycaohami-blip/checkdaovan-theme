<?php
/**
 * Kết quả thực tế (case study): Quản trị > Kết quả thực tế.
 * Mục đánh dấu "Ví dụ minh họa" luôn hiện nhãn "Minh họa" ngoài trang web
 * và không được tính vào số liệu tổng hợp - hãy thay bằng kết quả thật của bạn.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', function () {
	register_post_type( 'vs_case', array(
		'labels'        => array(
			'name'          => 'Kết quả thực tế',
			'singular_name' => 'Kết quả',
			'add_new'       => 'Thêm kết quả',
			'add_new_item'  => 'Thêm kết quả mới',
			'edit_item'     => 'Sửa kết quả',
			'all_items'     => 'Tất cả kết quả',
		),
		'public'        => false,
		'show_ui'       => true,
		'menu_icon'     => 'dashicons-chart-bar',
		'menu_position' => 26,
		'supports'      => array( 'title', 'excerpt' ),
	) );
} );

function vs_case_fields() {
	return array(
		'service'   => array( 'Dịch vụ', 'select', array( 'reduce' => 'Giảm tỷ lệ đạo văn', 'check' => 'Kiểm tra đạo văn & AI' ) ),
		'doc_type'  => array( 'Loại tài liệu', 'text', 'VD: Luận văn thạc sĩ' ),
		'field'     => array( 'Chuyên ngành', 'text', 'VD: Quản trị kinh doanh' ),
		'tool'      => array( 'Phần mềm kiểm tra', 'text', 'Turnitin' ),
		'pages'     => array( 'Số trang', 'number', '' ),
		'before'    => array( 'Đạo văn trước (%)', 'number', '' ),
		'after'     => array( 'Đạo văn sau (%)', 'number', '' ),
		'ai_before' => array( 'AI trước (%)', 'number', '' ),
		'ai_after'  => array( 'AI sau (%)', 'number', '' ),
		'days'      => array( 'Thời gian hoàn thành', 'text', 'VD: 48 giờ' ),
		'sample'    => array( 'Ví dụ minh họa (không phải kết quả thật)', 'checkbox', '' ),
	);
}

add_action( 'add_meta_boxes_vs_case', function () {
	add_meta_box( 'vs_case_box', 'Số liệu', function ( $post ) {
		wp_nonce_field( 'vs_case_save', 'vs_case_nonce' );
		echo '<p style="color:#666">Phần “Tóm tắt” (excerpt) bên dưới dùng làm mô tả ngắn cho thẻ kết quả.</p><table class="form-table">';
		foreach ( vs_case_fields() as $k => $f ) {
			$v = get_post_meta( $post->ID, '_vs_' . $k, true );
			echo '<tr><th>' . esc_html( $f[0] ) . '</th><td>';
			if ( 'select' === $f[1] ) {
				echo '<select name="vs_case[' . esc_attr( $k ) . ']">';
				foreach ( $f[2] as $ok => $ol ) {
					printf( '<option value="%s"%s>%s</option>', esc_attr( $ok ), selected( $v, $ok, false ), esc_html( $ol ) );
				}
				echo '</select>';
			} elseif ( 'checkbox' === $f[1] ) {
				printf( '<input type="checkbox" name="vs_case[%s]" value="1"%s>', esc_attr( $k ), checked( $v, '1', false ) );
			} else {
				printf( '<input type="%s" step="0.1" class="regular-text" name="vs_case[%s]" value="%s" placeholder="%s">', esc_attr( $f[1] ), esc_attr( $k ), esc_attr( $v ), esc_attr( $f[2] ) );
			}
			echo '</td></tr>';
		}
		echo '</table>';
	}, 'vs_case', 'normal', 'high' );
} );

add_action( 'save_post_vs_case', function ( $id ) {
	if ( ! isset( $_POST['vs_case_nonce'] ) || ! wp_verify_nonce( $_POST['vs_case_nonce'], 'vs_case_save' ) || ! current_user_can( 'edit_post', $id ) ) {
		return;
	}
	$in = isset( $_POST['vs_case'] ) ? wp_unslash( (array) $_POST['vs_case'] ) : array();
	foreach ( vs_case_fields() as $k => $f ) {
		$v = isset( $in[ $k ] ) ? sanitize_text_field( $in[ $k ] ) : '';
		update_post_meta( $id, '_vs_' . $k, 'checkbox' === $f[1] ? ( $v ? '1' : '' ) : $v );
	}
} );

add_filter( 'manage_vs_case_posts_columns', function ( $c ) {
	return array( 'cb' => $c['cb'], 'title' => 'Tiêu đề', 'vs_res' => 'Kết quả', 'vs_sample' => 'Loại', 'date' => 'Ngày' );
} );
add_action( 'manage_vs_case_posts_custom_column', function ( $col, $id ) {
	if ( 'vs_res' === $col ) {
		echo esc_html( get_post_meta( $id, '_vs_before', true ) . '% → ' . get_post_meta( $id, '_vs_after', true ) . '%' );
	} elseif ( 'vs_sample' === $col ) {
		echo get_post_meta( $id, '_vs_sample', true ) ? '<span style="color:#b45309">Ví dụ minh họa</span>' : '<span style="color:#047857">Kết quả thật</span>';
	}
}, 10, 2 );

/** Lấy danh sách kết quả. */
function vs_cases( $limit = -1, $service = '' ) {
	$args = array( 'post_type' => 'vs_case', 'post_status' => 'publish', 'posts_per_page' => $limit, 'orderby' => 'date', 'order' => 'DESC' );
	if ( $service ) {
		$args['meta_key']   = '_vs_service';
		$args['meta_value'] = $service;
	}
	return get_posts( $args );
}

/** Số liệu tổng hợp - chỉ tính kết quả thật. */
function vs_case_stats() {
	$real = array_filter( vs_cases(), function ( $c ) {
		return ! get_post_meta( $c->ID, '_vs_sample', true ) && 'reduce' === get_post_meta( $c->ID, '_vs_service', true );
	} );
	if ( count( $real ) < 3 ) {
		return null;
	}
	$drop = 0;
	$fast = 0;
	foreach ( $real as $c ) {
		$drop += (float) get_post_meta( $c->ID, '_vs_before', true ) - (float) get_post_meta( $c->ID, '_vs_after', true );
	}
	return array( 'count' => count( $real ), 'avg_drop' => round( $drop / count( $real ), 1 ) );
}

function vs_pct( $v ) {
	return '' === $v || null === $v ? '—' : str_replace( '.', ',', (string) (float) $v ) . '%';
}

function vs_pct_class( $v ) {
	if ( '' === $v || null === $v ) {
		return '';
	}
	return $v < 20 ? 'lvl-ok' : ( $v < 30 ? 'lvl-mid' : 'lvl-bad' );
}
