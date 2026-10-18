<?php
/**
 * Thẻ bài viết trong danh sách.
 */
$cat = get_the_category();
?>
<article <?php post_class( 'post-card reveal' ); ?>>
	<a class="post-card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true"><?php echo vs_post_cover( get_the_ID(), 'medium_large' ); // phpcs:ignore ?></a>
	<div class="post-card__body">
		<div class="post-card__meta">
			<?php if ( $cat ) : ?><a href="<?php echo esc_url( get_category_link( $cat[0] ) ); ?>" class="chip-cat"><?php echo esc_html( $cat[0]->name ); ?></a><?php endif; ?>
			<span><?php echo (int) vs_reading_time(); ?> phút đọc</span>
		</div>
		<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<p><?php echo esc_html( get_the_excerpt() ); ?></p>
		<a class="post-card__more" href="<?php the_permalink(); ?>">Đọc tiếp →</a>
	</div>
</article>
