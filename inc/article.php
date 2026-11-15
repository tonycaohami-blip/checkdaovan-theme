<?php
/**
 * Bài viết: neo tiêu đề (id) tạo sẵn từ máy chủ, khung tác giả, tích hợp Rank Math.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gắn id cho H2/H3 chưa có id trong nội dung bài viết, để mục lục và liên kết nhảy đoạn
 * hoạt động ngay cả khi chưa chạy JavaScript (Google cũng đọc được).
 */
add_filter( 'the_content', function ( $html ) {
	if ( ! is_singular( 'post' ) || false === stripos( $html, '<h' ) ) {
		return $html;
	}
	$used = array();
	return preg_replace_callback( '#<h([23])(\s[^>]*)?>(.*?)</h\1>#is', function ( $m ) use ( &$used ) {
		$attrs = isset( $m[2] ) ? $m[2] : '';
		if ( preg_match( '/\sid=/i', $attrs ) ) {
			return $m[0];
		}
		$base = sanitize_title( remove_accents( wp_strip_all_tags( $m[3] ) ) );
		$base = $base ? mb_substr( $base, 0, 60 ) : 'muc';
		$id   = $base;
		$i    = 2;
		while ( isset( $used[ $id ] ) ) {
			$id = $base . '-' . $i++;
		}
		$used[ $id ] = true;
		return '<h' . $m[1] . $attrs . ' id="' . esc_attr( $id ) . '">' . $m[3] . '</h' . $m[1] . '>';
	}, $html );
}, 12 );

/** Khung tác giả cuối bài (E-E-A-T). */
function vs_author_box() {
	?>
	<div class="author-box">
		<div class="author-box__avatar"><?php get_template_part( 'template-parts/logo-mark' ); ?></div>
		<div>
			<b class="author-box__name">Ban biên tập <?php echo esc_html( vs_opt( 'company', get_bloginfo( 'name' ) ) ); ?></b>
			<p>Nhóm biên tập viên có kinh nghiệm về kiểm tra đạo văn, phát hiện AI và trình bày luận văn, bài báo khoa học. Nội dung được rà soát theo <a href="<?php echo esc_url( vs_page_url( 'liem-chinh-hoc-thuat' ) ); ?>">chính sách liêm chính học thuật</a> và cập nhật định kỳ.</p>
		</div>
	</div>
	<?php
}

/*
 * Rank Math: theme có mục lục bài viết (khối .wp-block-rank-math-toc-block trong nội dung và mục lục bên cạnh),
 * khai báo để bài kiểm tra "Table of Contents" nhận diện đúng.
 */
add_filter( 'rank_math/researches/toc_plugins', function ( $plugins ) {
	$plugins['seo-by-rank-math/rank-math.php'] = 'Check Đạo Văn theme TOC';
	return $plugins;
} );
