<?php
/**
 * Template Name: Kết quả thực tế
 */
get_header();
$cases   = vs_cases();
$stats   = vs_case_stats();
$samples = array_filter( $cases, function ( $c ) { return get_post_meta( $c->ID, '_vs_sample', true ); } );
get_template_part( 'template-parts/page-hero', null, array(
	'title'   => 'Kết quả thực tế',
	'eyebrow' => 'Trước & sau',
	'lead'    => 'Tỷ lệ đạo văn và tỷ lệ AI trước và sau khi xử lý, theo từng loại tài liệu và chuyên ngành.',
	'size'    => 'sm',
) );
?>
<section class="section section--tight">
	<div class="container">
		<?php if ( $stats ) : ?>
			<div class="extras reveal">
				<div><b><?php echo (int) $stats['count']; ?></b><span>tài liệu đã giảm tỷ lệ</span></div>
				<div><b><?php echo esc_html( str_replace( '.', ',', (string) $stats['avg_drop'] ) ); ?></b><span>điểm % giảm trung bình</span></div>
			</div>
		<?php endif; ?>

		<div class="filter reveal" data-filter>
			<button class="is-active" data-f="all">Tất cả</button>
			<button data-f="reduce">Giảm tỷ lệ</button>
			<button data-f="check">Kiểm tra</button>
		</div>

		<?php if ( $cases ) : ?>
			<div class="cases" data-filter-list>
				<?php foreach ( $cases as $c ) : ?>
					<div data-cat="<?php echo esc_attr( get_post_meta( $c->ID, '_vs_service', true ) ); ?>">
						<?php get_template_part( 'template-parts/case-card', null, array( 'case' => $c ) ); ?>
					</div>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<p class="empty">Chưa có kết quả nào được đăng.</p>
		<?php endif; ?>

		<?php if ( $samples ) : ?>
			<p class="center-note">Các thẻ có nhãn “Minh họa” là ví dụ mô phỏng quy trình xử lý thường gặp, không phải tài liệu của một khách hàng cụ thể.</p>
		<?php endif; ?>
	</div>
</section>
<?php
get_template_part( 'template-parts/cta-band' );
get_footer();
