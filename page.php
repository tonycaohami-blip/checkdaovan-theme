<?php
/**
 * Trang tĩnh (chính sách, giới thiệu…).
 */
get_header();
the_post();
?>
<section class="page-hero page-hero--sm">
	<div class="container container--mid">
		<nav class="crumbs" aria-label="Breadcrumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Trang chủ</a><span>/</span><?php the_title(); ?></nav>
		<h1><?php the_title(); ?></h1>
		<p>Cập nhật ngày <?php echo esc_html( get_the_modified_date( 'd/m/Y' ) ); ?></p>
	</div>
</section>
<section class="section section--tight">
	<div class="container container--mid">
		<div class="prose prose--card"><?php the_content(); ?></div>
	</div>
</section>
<?php
get_template_part( 'template-parts/cta-band' );
get_footer();
