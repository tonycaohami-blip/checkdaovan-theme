<?php
/**
 * Danh sách bài viết: trang Blog, chuyên mục, thẻ, tìm kiếm.
 */
get_header();

if ( is_home() ) {
	$title = 'Blog kiến thức';
	$desc  = 'Hướng dẫn kiểm tra đạo văn, đọc báo cáo Turnitin, trích dẫn chuẩn và sử dụng AI đúng cách trong học thuật.';
} elseif ( is_search() ) {
	$title = 'Kết quả tìm kiếm: ' . get_search_query();
	$desc  = '';
} else {
	$title = wp_strip_all_tags( get_the_archive_title() );
	$desc  = wp_strip_all_tags( get_the_archive_description() );
}
$cats = get_categories( array( 'hide_empty' => true ) );
?>
<section class="page-hero">
	<div class="container">
		<nav class="crumbs" aria-label="Breadcrumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Trang chủ</a><span>/</span><?php echo esc_html( $title ); ?></nav>
		<h1><?php echo esc_html( $title ); ?></h1>
		<?php if ( $desc ) : ?><p><?php echo esc_html( $desc ); ?></p><?php endif; ?>
		<div class="page-hero__tools">
			<?php if ( $cats ) : ?>
				<div class="cat-filter">
					<?php if ( get_option( 'page_for_posts' ) ) : ?>
						<a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>" class="<?php echo is_home() ? 'is-active' : ''; ?>">Tất cả</a>
					<?php endif; ?>
					<?php foreach ( $cats as $c ) : ?>
						<a href="<?php echo esc_url( get_category_link( $c ) ); ?>" class="<?php echo is_category( $c->term_id ) ? 'is-active' : ''; ?>"><?php echo esc_html( $c->name ); ?></a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<form class="search" role="search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<input type="search" name="s" placeholder="Tìm bài viết…" value="<?php echo esc_attr( get_search_query() ); ?>" aria-label="Tìm bài viết">
			</form>
		</div>
	</div>
</section>

<section class="section section--tight">
	<div class="container">
		<?php if ( have_posts() ) : ?>
			<div class="posts">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/post', 'card' );
				endwhile;
				?>
			</div>
			<div class="pager"><?php the_posts_pagination( array( 'prev_text' => '←', 'next_text' => '→', 'mid_size' => 1 ) ); ?></div>
		<?php else : ?>
			<div class="empty">
				<h2>Chưa có bài viết phù hợp</h2>
				<p>Thử từ khóa khác hoặc <a href="<?php echo esc_url( home_url( '/' ) ); ?>">quay về trang chủ</a>.</p>
			</div>
		<?php endif; ?>
	</div>
</section>
<?php
get_template_part( 'template-parts/cta-band' );
get_footer();
