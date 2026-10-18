<?php
get_header();
?>
<section class="page-hero">
	<div class="container container--mid" style="text-align:center">
		<h1>404 - Không tìm thấy trang</h1>
		<p style="margin:0 auto 28px">Trang bạn tìm có thể đã bị xóa hoặc đổi địa chỉ.</p>
		<a class="btn btn--accent" href="<?php echo esc_url( home_url( '/' ) ); ?>">Về trang chủ</a>
	</div>
</section>
<?php
get_template_part( 'template-parts/cta-band' );
get_footer();
