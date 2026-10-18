<?php
/**
 * Phần đầu trang con: breadcrumb, tiêu đề, mô tả, nút.
 * $args: title, lead, eyebrow, crumbs (mảng [nhãn => url]), actions (HTML), size ('sm'|'lg'), aside (HTML)
 */
$a = wp_parse_args( $args, array( 'title' => get_the_title(), 'lead' => '', 'eyebrow' => '', 'crumbs' => array(), 'actions' => '', 'size' => '', 'aside' => '' ) );
?>
<section class="page-hero<?php echo $a['size'] ? ' page-hero--' . esc_attr( $a['size'] ) : ''; ?><?php echo $a['aside'] ? ' page-hero--split' : ''; ?>">
	<div class="container page-hero__inner">
		<div class="page-hero__copy">
			<nav class="crumbs" aria-label="Breadcrumb">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Trang chủ</a>
				<?php foreach ( $a['crumbs'] as $label => $url ) : ?>
					<span>/</span><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a>
				<?php endforeach; ?>
				<span>/</span><?php echo esc_html( wp_strip_all_tags( str_replace( '<br>', ' ', html_entity_decode( $a['title'] ) ) ) ); ?>
			</nav>
			<?php if ( $a['eyebrow'] ) : ?><span class="eyebrow eyebrow--light"><?php echo esc_html( $a['eyebrow'] ); ?></span><?php endif; ?>
			<h1><?php echo wp_kses( $a['title'], array( 'span' => array( 'class' => array() ), 'br' => array() ) ); ?></h1>
			<?php if ( $a['lead'] ) : ?><p class="page-hero__lead"><?php echo wp_kses( $a['lead'], array( 'strong' => array(), 'b' => array() ) ); ?></p><?php endif; ?>
			<?php if ( $a['actions'] ) : ?><div class="page-hero__actions"><?php echo $a['actions']; // phpcs:ignore -- HTML do theme tạo. ?></div><?php endif; ?>
		</div>
		<?php if ( $a['aside'] ) : ?><div class="page-hero__aside"><?php echo $a['aside']; // phpcs:ignore ?></div><?php endif; ?>
	</div>
</section>
