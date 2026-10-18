<?php
/**
 * Bài viết chi tiết.
 */
get_header();
the_post();

$cat       = get_the_category();
$min_check = vs_min_check_price();
$url       = get_permalink();
?>
<article class="article">
	<header class="article__hero">
		<div class="container container--mid">
			<nav class="crumbs" aria-label="Breadcrumb">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Trang chủ</a><span>/</span>
				<?php if ( get_option( 'page_for_posts' ) ) : ?><a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>">Blog</a><span>/</span><?php endif; ?>
				<?php if ( $cat ) : ?><a href="<?php echo esc_url( get_category_link( $cat[0] ) ); ?>"><?php echo esc_html( $cat[0]->name ); ?></a><?php endif; ?>
			</nav>
			<h1><?php the_title(); ?></h1>
			<?php if ( has_excerpt() ) : ?><p class="article__lead"><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?>
			<div class="article__meta">
				<span class="article__author"><?php get_template_part( 'template-parts/logo-mark' ); ?><?php bloginfo( 'name' ); ?></span>
				<span>Cập nhật <?php echo esc_html( get_the_modified_date( 'd/m/Y' ) ); ?></span>
				<span><?php echo (int) vs_reading_time(); ?> phút đọc</span>
			</div>
		</div>
	</header>

	<div class="container article__layout">
		<aside class="article__toc" data-toc hidden>
			<div class="toc">
				<b>Nội dung bài viết</b>
				<ol data-toc-list></ol>
			</div>
		</aside>

		<div class="article__main">
			<div class="article__cover"><?php echo vs_post_cover( get_the_ID(), 'large' ); // phpcs:ignore ?></div>
			<div class="prose" data-prose>
				<?php the_content(); ?>
			</div>

			<?php $tags = get_the_tags(); ?>
			<?php if ( $tags ) : ?>
				<div class="article__tags">
					<?php foreach ( $tags as $t ) : ?>
						<a href="<?php echo esc_url( get_tag_link( $t ) ); ?>">#<?php echo esc_html( $t->name ); ?></a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<div class="share">
				<span>Chia sẻ bài viết</span>
				<a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo rawurlencode( $url ); ?>" target="_blank" rel="noopener">Facebook</a>
				<a href="https://sp.zalo.me/share_inline?d=<?php echo rawurlencode( base64_encode( wp_json_encode( array( 'url' => $url ) ) ) ); ?>" target="_blank" rel="noopener">Zalo</a>
				<button type="button" data-copy="<?php echo esc_attr( $url ); ?>">Sao chép link</button>
			</div>

			<div class="inline-cta">
				<div>
					<b>Kiểm tra bài của bạn trước khi nộp</b>
					<span><?php echo count( vs_pricing()['tools'] ); ?> phần mềm: Turnitin, DoIT, GPTZero… · không lưu bài · từ <?php echo esc_html( vs_money( $min_check ) ); ?></span>
				</div>
				<a class="btn btn--accent" href="<?php echo esc_url( vs_page_url( 'dat-hang' ) ); ?>">Đặt kiểm tra</a>
			</div>
		</div>

		<aside class="article__side">
			<div class="side-card">
				<span class="side-card__badge">Dịch vụ</span>
				<h3>Bài của bạn đang bao nhiêu %?</h3>
				<p>Nhận báo cáo Turnitin gốc kèm tỷ lệ AI. Kết quả từ 30 phút.</p>
				<a class="btn btn--accent btn--block" href="<?php echo esc_url( vs_page_url( 'dat-hang', array( 'tab' => 'check' ) ) ); ?>">Kiểm tra từ <?php echo esc_html( vs_money( $min_check ) ); ?></a>
				<a class="btn btn--outline btn--block" href="<?php echo esc_url( vs_page_url( 'giam-ty-le-dao-van' ) ); ?>">Giảm tỷ lệ đạo văn</a>
				<?php if ( vs_opt( 'zalo' ) ) : ?>
					<a class="side-card__zalo" href="<?php echo esc_url( vs_zalo( vs_opt( 'zalo' ) ) ); ?>" target="_blank" rel="noopener">Tư vấn qua Zalo <?php echo esc_html( vs_opt( 'zalo' ) ); ?></a>
				<?php endif; ?>
			</div>
		</aside>
	</div>
</article>

<?php
$related = new WP_Query( array(
	'posts_per_page'      => 3,
	'post__not_in'        => array( get_the_ID() ),
	'category__in'        => $cat ? array( $cat[0]->term_id ) : array(),
	'ignore_sticky_posts' => true,
	'no_found_rows'       => true,
) );
if ( $related->post_count < 3 ) {
	$related = new WP_Query( array( 'posts_per_page' => 3, 'post__not_in' => array( get_the_ID() ), 'ignore_sticky_posts' => true, 'no_found_rows' => true ) );
}
if ( $related->have_posts() ) :
	?>
	<section class="section section--tint">
		<div class="container">
			<div class="section__head section__head--left"><span class="eyebrow">Đọc thêm</span><h2>Bài viết liên quan</h2></div>
			<div class="posts">
				<?php
				while ( $related->have_posts() ) :
					$related->the_post();
					get_template_part( 'template-parts/post', 'card' );
				endwhile;
				wp_reset_postdata();
				?>
			</div>
		</div>
	</section>
<?php endif; ?>
<?php
get_footer();
