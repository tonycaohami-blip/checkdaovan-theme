<?php
/**
 * Thẻ kết quả: tỷ lệ trước → sau.
 */
$c      = $args['case'];
$m      = function ( $k ) use ( $c ) { return get_post_meta( $c->ID, '_vs_' . $k, true ); };
$before = (float) $m( 'before' );
$after  = (float) $m( 'after' );
$reduce = 'reduce' === $m( 'service' );
?>
<article class="case reveal">
	<div class="case__top">
		<span class="case__type"><?php echo esc_html( $m( 'doc_type' ) ); ?></span>
		<?php if ( $m( 'sample' ) ) : ?><span class="case__sample" title="Ví dụ minh họa quy trình, không phải kết quả của một khách hàng cụ thể">Minh họa</span><?php endif; ?>
	</div>
	<h3><?php echo esc_html( get_the_title( $c ) ); ?></h3>
	<p class="case__meta"><?php echo esc_html( implode( ' · ', array_filter( array( $m( 'field' ), $m( 'tool' ), $m( 'pages' ) ? $m( 'pages' ) . ' trang' : '', $m( 'days' ) ) ) ) ); ?></p>

	<div class="case__bars">
		<div class="case__row">
			<span>Trước</span>
			<div class="bar"><i class="bar__fill bar__fill--bad" style="width:<?php echo esc_attr( min( 100, $before ) ); ?>%"></i></div>
			<b class="<?php echo esc_attr( vs_pct_class( $before ) ); ?>"><?php echo esc_html( vs_pct( $m( 'before' ) ) ); ?></b>
		</div>
		<?php if ( $reduce ) : ?>
			<div class="case__row">
				<span>Sau</span>
				<div class="bar"><i class="bar__fill bar__fill--ok" style="width:<?php echo esc_attr( min( 100, $after ) ); ?>%"></i></div>
				<b class="<?php echo esc_attr( vs_pct_class( $after ) ); ?>"><?php echo esc_html( vs_pct( $m( 'after' ) ) ); ?></b>
			</div>
		<?php endif; ?>
	</div>

	<?php if ( '' !== $m( 'ai_before' ) ) : ?>
		<p class="case__ai">Tỷ lệ AI: <b><?php echo esc_html( vs_pct( $m( 'ai_before' ) ) ); ?></b><?php echo '' !== $m( 'ai_after' ) ? ' → <b class="lvl-ok">' . esc_html( vs_pct( $m( 'ai_after' ) ) ) . '</b>' : ''; ?></p>
	<?php endif; ?>
	<?php if ( has_excerpt( $c ) ) : ?><p class="case__note"><?php echo esc_html( get_the_excerpt( $c ) ); ?></p><?php endif; ?>
	<?php if ( $reduce && $before > $after ) : ?>
		<div class="case__drop">Giảm <b><?php echo esc_html( str_replace( '.', ',', (string) round( $before - $after, 1 ) ) ); ?></b> điểm %</div>
	<?php endif; ?>
</article>
