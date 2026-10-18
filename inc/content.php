<?php
/**
 * Nhập nội dung mẫu: 30 bài viết (kèm ảnh đại diện), trang chính sách, trang chủ / blog.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Tự nhập 1 lần khi kích hoạt theme. */
/** Tên website là tên mặc định của WordPress hoặc chỉ là tên miền (Hostinger tự đặt khi cài)? */
function vs_is_placeholder_title( $name ) {
	$name = trim( (string) $name );
	return '' === $name
		|| in_array( $name, array( 'WordPress', 'My WordPress Site', 'My WordPress Website', 'My Blog', 'Một trang web WordPress khác' ), true )
		|| (bool) preg_match( '/^(https?:\/\/)?(www\.)?[a-z0-9-]+(\.[a-z0-9-]+)*\.[a-z]{2,}\/?$/i', $name );
}

/** Đặt tên website theo thương hiệu khi tên hiện tại chỉ là tên mặc định / tên miền. */
function vs_fix_site_title() {
	if ( vs_is_placeholder_title( get_option( 'blogname' ) ) ) {
		update_option( 'blogname', VS_BRAND );
	}
	$s = get_option( 'vs_settings', array() );
	if ( is_array( $s ) && isset( $s['company'] ) && vs_is_placeholder_title( $s['company'] ) ) {
		$s['company'] = VS_BRAND;
		update_option( 'vs_settings', $s );
	}
}

add_action( 'after_switch_theme', function () {
	vs_fix_site_title();
	vs_default_tagline();
	vs_maybe_import();
} );

// Chạy 1 lần sau khi theme tự cập nhật (không cần vào trang quản trị).
add_action( 'init', function () {
	if ( (int) get_option( 'vs_title_fixed' ) < 1 ) {
		vs_fix_site_title();
		vs_default_tagline();
		update_option( 'vs_title_fixed', 1 );
	}
} );

add_action( 'init', function () {
	if ( get_option( 'vs_flush' ) ) {
		delete_option( 'vs_flush' );
		flush_rewrite_rules();
	}
}, 99 );

/** Nhập nội dung khi theme được cập nhật lên phiên bản có trang mới. */
add_action( 'admin_init', function () {
	if ( current_user_can( 'manage_options' ) ) {
		vs_maybe_import();
	}
} );
/* Có bài mẫu / nội dung mới sau khi theme tự cập nhật: nhập ở nền (WP-Cron), không cần mở trang quản trị. */
add_action( 'init', function () {
	if ( (int) get_option( 'vs_content_imported' ) < 15 && ! wp_next_scheduled( 'vs_run_import' ) ) {
		wp_schedule_single_event( time() + 5, 'vs_run_import' );
	}
} );
add_action( 'vs_run_import', function () {
	if ( function_exists( 'set_time_limit' ) ) {
		@set_time_limit( 300 ); // phpcs:ignore
	}
	vs_maybe_import();
} );

function vs_maybe_import() {
	if ( (int) get_option( 'vs_content_imported' ) < 15 ) {
		$old_mail = vs_opt( 'email', get_option( 'admin_email' ) );
		vs_contact_migrate( $old_mail );
		vs_import_content();
		vs_brand_migrate( 'VănSạch', VS_BRAND );
		vs_brand_migrate( 'checkdaovan.vn', VS_DOMAIN );
		if ( $old_mail && VS_EMAIL !== $old_mail ) {
			vs_brand_migrate( $old_mail, VS_EMAIL );
		}
		vs_brand_migrate( 'CheckĐạoVăn', VS_BRAND );
		update_option( 'vs_content_imported', 15 );
	}
}

add_action( 'admin_post_vs_import', function () {
	if ( ! current_user_can( 'manage_options' ) || ! wp_verify_nonce( isset( $_GET['_wpnonce'] ) ? $_GET['_wpnonce'] : '', 'vs_import' ) ) {
		wp_die( 'Không có quyền.' );
	}
	$n = vs_import_content( ! empty( $_GET['force'] ) );
	wp_safe_redirect( admin_url( 'edit.php?post_type=vs_order&page=vs-settings&vs_imported=' . $n ) );
	exit;
} );

/**
 * Đổi tên thương hiệu cũ trong tên website, cài đặt và nội dung bài viết / trang (chạy 1 lần khi nâng cấp).
 * Bài mẫu chưa bị sửa vẫn giữ trạng thái "chưa sửa" để lần nâng cấp sau tiếp tục được cập nhật.
 */
function vs_brand_migrate( $old, $new ) {
	if ( get_option( 'blogname' ) === $old ) {
		update_option( 'blogname', $new );
	}
	vs_default_tagline();
	$s = get_option( 'vs_settings', array() );
	if ( is_array( $s ) && isset( $s['company'] ) && $s['company'] === $old ) {
		$s['company'] = $new;
		update_option( 'vs_settings', $s );
	}
	global $wpdb;
	$ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type IN ('post','page','vs_case') AND ( post_content LIKE %s OR post_title LIKE %s OR post_excerpt LIKE %s )", '%' . $wpdb->esc_like( $old ) . '%', '%' . $wpdb->esc_like( $old ) . '%', '%' . $wpdb->esc_like( $old ) . '%' ) );
	foreach ( $ids as $id ) {
		$p    = get_post( $id );
		$keep = 'post' === $p->post_type && get_post_meta( $id, '_vs_import_hash', true ) && vs_sample_untouched( $p );
		wp_update_post( wp_slash( array(
			'ID'           => $id,
			'post_title'   => str_replace( $old, $new, $p->post_title ),
			'post_excerpt' => str_replace( $old, $new, $p->post_excerpt ),
			'post_content' => str_replace( $old, $new, $p->post_content ),
		) ) );
		if ( $keep ) {
			clean_post_cache( $id );
			update_post_meta( $id, '_vs_import_hash', md5( get_post_field( 'post_content', $id, 'raw' ) ) );
		}
	}
}

/** Đặt email hỗ trợ và email nhận thông báo về địa chỉ mặc định của tên miền. */
function vs_contact_migrate( $old_mail ) {
	$s = get_option( 'vs_settings', array() );
	$s = is_array( $s ) ? $s : array();
	$s['email']        = VS_EMAIL;
	$s['notify_email'] = VS_EMAIL;
	update_option( 'vs_settings', $s );
}

/** Khẩu hiệu chứa từ khóa chính (dùng cho tiêu đề trang chủ) khi chưa đặt. */
function vs_default_tagline() {
	$t = trim( (string) get_option( 'blogdescription' ) );
	if ( '' === $t || in_array( $t, array( 'Just another WordPress site', 'Một website mới sử dụng WordPress' ), true ) ) {
		update_option( 'blogdescription', 'Check đạo văn Turnitin, DoIT & AI 24/7' );
	}
}

function vs_fill_placeholders( $html ) {
	return strtr( $html, array(
		'{home}'    => home_url( '/' ),
		'{theme}'   => trailingslashit( get_template_directory_uri() ),
		'{company}' => esc_html( vs_opt( 'company', get_bloginfo( 'name' ) ) ),
		'{email}'   => esc_html( vs_opt( 'email' ) ),
		'{hotline}' => esc_html( vs_opt( 'hotline', '' ) ),
	) );
}

/** Danh sách bài viết mẫu, mới nhất trước. */
function vs_sample_articles() {
	return array_merge(
		require VS_DIR . '/inc/content/articles-6.php',
		require VS_DIR . '/inc/content/articles-7.php',
		require VS_DIR . '/inc/content/articles-4.php',
		require VS_DIR . '/inc/content/articles-5.php',
		require VS_DIR . '/inc/content/articles-3.php',
		require VS_DIR . '/inc/content/articles-2.php',
		require VS_DIR . '/inc/content/articles.php',
		require VS_DIR . '/inc/content/articles-1b.php'
	);
}

/**
 * Bài mẫu đã có: chỉ cập nhật khi chưa bị sửa từ lần nhập trước (hoặc khi $force).
 * Bài nhập từ bản cũ chưa có mã kiểm tra: coi là chưa sửa nếu không có bản lưu (revision) nào.
 */
function vs_sample_untouched( $post ) {
	$hash = get_post_meta( $post->ID, '_vs_import_hash', true );
	if ( $hash ) {
		return md5( $post->post_content ) === $hash;
	}
	return ! wp_get_post_revisions( $post->ID, array( 'fields' => 'ids', 'posts_per_page' => 1 ) );
}

/**
 * Trang chính sách mẫu còn nguyên: có mã kiểm tra khớp, hoặc (bản cũ chưa có mã) nội dung gần như
 * trùng với mẫu của bản 6.8 - chỉ khác do đổi tên thương hiệu/email.
 */
function vs_page_untouched( $post, $slug ) {
	if ( get_post_meta( $post->ID, '_vs_import_hash', true ) ) {
		return vs_sample_untouched( $post );
	}
	static $legacy = null;
	if ( null === $legacy ) {
		$legacy = require VS_DIR . '/inc/content/pages-legacy.php';
	}
	if ( empty( $legacy[ $slug ] ) ) {
		return false;
	}
	$norm = function ( $h ) {
		return preg_replace( '/\s+/u', ' ', trim( wp_strip_all_tags( $h ) ) );
	};
	$a = $norm( $post->post_content );
	$b = $norm( vs_fill_placeholders( $legacy[ $slug ]['content'] ) );
	similar_text( $a, $b, $pct );
	return $pct >= 92;
}

/** Gắn ảnh đại diện từ assets/img/blog của theme (nhập vào Thư viện ảnh 1 lần). */
function vs_sample_cover( $post_id, $file, $title ) {
	$src = VS_DIR . '/assets/img/blog/' . $file;
	if ( ! $file || ! is_file( $src ) ) {
		return;
	}
	$thumb = get_post_thumbnail_id( $post_id );
	if ( $thumb && get_post_meta( $thumb, '_vs_cover_src', true ) === $file ) {
		return;
	}
	if ( $thumb && ! get_post_meta( $thumb, '_vs_cover_src', true ) ) {
		return; // Ảnh đại diện do quản trị viên tự chọn: giữ nguyên.
	}
	$found = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'meta_key' => '_vs_cover_src', 'meta_value' => $file, 'fields' => 'ids', 'posts_per_page' => 1 ) );
	if ( $found ) {
		set_post_thumbnail( $post_id, $found[0] );
		return;
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$tmp = wp_tempnam( $file );
	if ( ! $tmp || ! copy( $src, $tmp ) ) {
		return;
	}
	$att = media_handle_sideload( array( 'name' => $file, 'tmp_name' => $tmp ), $post_id, $title );
	if ( is_wp_error( $att ) ) {
		@unlink( $tmp ); // phpcs:ignore
		return;
	}
	update_post_meta( $att, '_vs_cover_src', $file );
	update_post_meta( $att, '_wp_attachment_image_alt', $title );
	set_post_thumbnail( $post_id, $att );
}

function vs_import_content( $force = false ) {
	$created = 0;

	// Đường dẫn đẹp cho bài viết (chỉ khi đang dùng dạng mặc định ?p=123).
	if ( '' === get_option( 'permalink_structure' ) ) {
		update_option( 'permalink_structure', '/%postname%/' );
		flush_rewrite_rules();
	}

	// Chuyên mục.
	$cats = array(
		'kien-thuc' => 'Kiến thức đạo văn',
		'huong-dan' => 'Hướng dẫn',
		'cong-cu'   => 'Công cụ kiểm tra',
		'ai'        => 'AI & học thuật',
		'dich-vu'   => 'Dịch vụ 24/7',
	);
	$cat_ids = array();
	foreach ( $cats as $slug => $name ) {
		$term = get_term_by( 'slug', $slug, 'category' );
		if ( ! $term ) {
			$res = wp_insert_term( $name, 'category', array( 'slug' => $slug ) );
			$cat_ids[ $slug ] = is_wp_error( $res ) ? 0 : $res['term_id'];
		} else {
			$cat_ids[ $slug ] = $term->term_id;
		}
	}

	// Bài viết - mỗi bài cách nhau 3 ngày cho tự nhiên.
	$articles = vs_sample_articles();
	$time     = time();
	// Website đã có bài mẫu từ bản trước: bài mới được xếp lên đầu blog (mỗi bài cách 3 giờ).
	$fresh = ! get_page_by_path( 'dao-van-la-gi', OBJECT, 'post' );
	$new_i = 0;
	foreach ( $articles as $i => $a ) {
		$old = get_page_by_path( $a['slug'], OBJECT, 'post' );
		if ( $old ) {
			vs_sample_cover( $old->ID, isset( $a['cover'] ) ? $a['cover'] : '', $a['title'] );
			if ( $force || vs_sample_untouched( $old ) ) {
				$res = wp_update_post( array(
					'ID'            => $old->ID,
					'post_title'    => $a['title'],
					'post_excerpt'  => $a['excerpt'],
					'post_content'  => vs_fill_placeholders( trim( $a['content'] ) ),
					'post_category' => array( $cat_ids[ $a['cat'] ] ),
					'tags_input'    => isset( $a['tags'] ) ? $a['tags'] : array(),
				) );
				if ( $res && ! is_wp_error( $res ) ) {
					clean_post_cache( $old->ID );
					update_post_meta( $old->ID, '_vs_import_hash', md5( get_post_field( 'post_content', $old->ID, 'raw' ) ) );
					$created++;
				}
			}
			continue;
		}
		$id = wp_insert_post( array(
			'post_type'     => 'post',
			'post_status'   => 'publish',
			'post_title'    => $a['title'],
			'post_name'     => $a['slug'],
			'post_excerpt'  => $a['excerpt'],
			'post_content'  => vs_fill_placeholders( trim( $a['content'] ) ),
			'post_category' => array( $cat_ids[ $a['cat'] ] ),
			'post_date'     => wp_date( 'Y-m-d H:i:s', $fresh ? $time - ( $i * 3 + 1 ) * DAY_IN_SECONDS : $time - ( $new_i++ * 3 + 1 ) * HOUR_IN_SECONDS ),
			'tags_input'    => isset( $a['tags'] ) ? $a['tags'] : array(),
		) );
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, '_vs_import_hash', md5( get_post_field( 'post_content', $id, 'raw' ) ) );
			vs_sample_cover( $id, isset( $a['cover'] ) ? $a['cover'] : '', $a['title'] );
			$created++;
		}
	}

	// Trang.
	$pages = require VS_DIR . '/inc/content/pages.php';
	$ids   = array();
	foreach ( $pages as $slug => $p ) {
		$existing = get_page_by_path( $slug );
		if ( $existing ) {
			$ids[ $slug ] = $existing->ID;
			// Trang chính sách mẫu: cập nhật nội dung mới nếu quản trị viên chưa tự sửa.
			if ( '' !== $p['content'] && ( $force || vs_page_untouched( $existing, $slug ) ) ) {
				$new = vs_fill_placeholders( trim( $p['content'] ) );
				if ( $new !== $existing->post_content ) {
					wp_update_post( array( 'ID' => $existing->ID, 'post_title' => $p['title'], 'post_content' => $new ) );
					clean_post_cache( $existing->ID );
					$created++;
				}
				update_post_meta( $existing->ID, '_vs_import_hash', md5( get_post_field( 'post_content', $existing->ID, 'raw' ) ) );
			}
			continue;
		}
		$ids[ $slug ] = wp_insert_post( array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => $p['title'],
			'post_name'    => $slug,
			'post_content' => vs_fill_placeholders( trim( $p['content'] ) ),
		) );
		if ( ! empty( $p['template'] ) ) {
			update_post_meta( $ids[ $slug ], '_wp_page_template', 'page-templates/' . $p['template'] . '.php' );
		}
		if ( $ids[ $slug ] && ! is_wp_error( $ids[ $slug ] ) ) {
			update_post_meta( $ids[ $slug ], '_vs_import_hash', md5( get_post_field( 'post_content', $ids[ $slug ], 'raw' ) ) );
		}
		$created++;
	}

	// Kết quả mẫu đã nhập từ bản cũ: cập nhật mô tả và số liệu theo bản mới (chỉ mục còn đánh dấu "Ví dụ minh họa").
	foreach ( require VS_DIR . '/inc/content/cases.php' as $cs ) {
		$old = get_posts( array( 'post_type' => 'vs_case', 'post_status' => 'any', 'title' => $cs['title'], 'posts_per_page' => 1, 'meta_key' => '_vs_sample', 'meta_value' => '1' ) );
		if ( $old ) {
			wp_update_post( array( 'ID' => $old[0]->ID, 'post_excerpt' => $cs['note'] ) );
			foreach ( $cs['meta'] as $k => $v ) {
				update_post_meta( $old[0]->ID, '_vs_' . $k, $v );
			}
		}
	}

	// Kết quả mẫu (đánh dấu "Ví dụ minh họa").
	if ( ! get_posts( array( 'post_type' => 'vs_case', 'posts_per_page' => 1, 'post_status' => 'any', 'fields' => 'ids' ) ) ) {
		foreach ( require VS_DIR . '/inc/content/cases.php' as $i => $cs ) {
			$cid = wp_insert_post( array(
				'post_type'    => 'vs_case',
				'post_status'  => 'publish',
				'post_title'   => $cs['title'],
				'post_excerpt' => $cs['note'],
				'post_date'    => wp_date( 'Y-m-d H:i:s', $time - $i * HOUR_IN_SECONDS ),
			) );
			foreach ( $cs['meta'] as $k => $v ) {
				update_post_meta( $cid, '_vs_' . $k, $v );
			}
			update_post_meta( $cid, '_vs_sample', '1' );
			$created++;
		}
	}

	// Trang chủ tĩnh + trang Blog.
	if ( 'page' !== get_option( 'show_on_front' ) && ! empty( $ids['trang-chu'] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $ids['trang-chu'] );
		update_option( 'page_for_posts', $ids['blog'] );
	}

	// Ẩn bài mẫu "Hello world!" của WordPress (chuyển về bản nháp, không xóa).
	$hello = get_page_by_path( 'hello-world', OBJECT, 'post' );
	if ( $hello && 'publish' === $hello->post_status ) {
		wp_update_post( array( 'ID' => $hello->ID, 'post_status' => 'draft' ) );
	}

	// Làm mới đường dẫn ở lần tải trang kế tiếp để các trang mới (dat-hang, tai-khoan…) hoạt động.
	update_option( 'vs_flush', 1 );

	return $created;
}
