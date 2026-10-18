<?php
/**
 * Template Name: Dịch vụ - Kiểm tra đạo văn & AI
 */
get_header();
$p     = vs_pricing();
$meta  = vs_tool_meta();
$order = vs_page_url( 'dat-hang', array( 'tab' => 'check' ) );

get_template_part( 'template-parts/page-hero', null, array(
	'title'   => 'Kiểm tra đạo văn &amp; tỷ lệ AI',
	'eyebrow' => 'Dịch vụ',
	'crumbs'  => array( 'Dịch vụ' => vs_page_url( 'bang-gia' ) ),
	'lead'    => 'Nhận báo cáo PDF gốc từ <strong>Turnitin, iThenticate, DoIT, Copyleaks, Originality.ai, GPTZero, Winston AI, Grammarly, QuillBot, Undetectable.AI</strong>. Kiểm tra ở chế độ không lưu bài, kết quả từ 30 phút.',
	'actions' => '<a class="btn btn--accent btn--lg" href="' . esc_url( $order ) . '">Đặt kiểm tra - từ ' . esc_html( vs_money( vs_min_check_price() ) ) . '</a><a class="btn btn--glass btn--lg" href="' . esc_url( vs_page_url( 'bang-gia' ) ) . '">Xem bảng giá</a>',
	'aside'   => '<div class="mini-report"><div class="mini-report__head"><span>Báo cáo kiểm tra</span><em>Minh họa</em></div><div class="mini-report__grid"><div><b class="lvl-ok">14%</b><span>Similarity</span></div><div><b style="color:#3d5cff">0%</b><span>AI writing</span></div></div><ul><li><i style="width:64%"></i>Internet Sources<b>9%</b></li><li><i style="width:30%"></i>Publications<b>4%</b></li><li><i style="width:10%"></i>Student Papers<b>1%</b></li></ul></div>',
) );
?>

<section class="section">
	<div class="container">
		<div class="section__head reveal">
			<span class="eyebrow"><?php echo count( $p['tools'] ); ?> phần mềm</span>
			<h2>Chọn công cụ phù hợp với nơi bạn nộp bài</h2>
		</div>
		<div class="tool-rows">
			<?php foreach ( $p['tools'] as $k => $t ) : ?>
				<article class="tool-row reveal">
					<div class="tool__logo<?php echo esc_attr( vs_tool_mark_class( $k ) ); ?>" style="--c:<?php echo esc_attr( $meta[ $k ][1] ); ?>"><?php echo vs_tool_mark( $k ); // phpcs:ignore ?></div>
					<div class="tool-row__body">
						<h3><?php echo esc_html( $t['name'] ); ?> <span class="tool__tag"><?php echo esc_html( $meta[ $k ][2] ); ?></span></h3>
						<p><?php echo esc_html( $t['desc'] ); ?></p>
					</div>
					<div class="tool-row__prices">
						<span class="<?php echo $t['plag'] > 0 ? '' : 'is-na'; ?>">Đạo văn <b><?php echo esc_html( vs_price_label( $t['plag'] ) ); ?></b></span>
						<span class="<?php echo $t['ai'] > 0 ? '' : 'is-na'; ?>">AI <b><?php echo esc_html( vs_price_label( $t['ai'] ) ); ?></b></span>
						<?php if ( $t['combo'] > 0 ) : ?><span class="is-best">Combo <b><?php echo esc_html( vs_money( $t['combo'] ) ); ?></b></span><?php endif; ?>
					</div>
					<a class="btn btn--outline" href="<?php echo esc_url( vs_page_url( 'dat-hang', array( 'tab' => 'check', 'tool' => $k ) ) ); ?>">Chọn</a>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="section section--tint">
	<div class="container split">
		<div class="reveal">
			<span class="eyebrow">Bạn nhận được gì</span>
			<h2>Báo cáo đầy đủ, dễ hiểu</h2>
			<ul class="ticks ticks--lg">
				<li><b>File PDF gốc</b> xuất trực tiếp từ phần mềm, giữ nguyên định dạng báo cáo.</li>
				<li><b>Tỷ lệ tương đồng</b> tổng và theo từng nhóm nguồn: Internet, ấn phẩm, bài sinh viên.</li>
				<li><b>Danh sách nguồn trùng</b> và các đoạn được tô màu để bạn biết chính xác cần sửa ở đâu.</li>
				<li><b>Tỷ lệ văn bản AI</b> (gói AI hoặc combo) kèm các đoạn bị đánh dấu.</li>
				<li><b>Thiết lập theo yêu cầu:</b> loại trừ trích dẫn, tài liệu tham khảo, đoạn khớp ngắn - ghi rõ trong phần ghi chú.</li>
			</ul>
			<a class="btn btn--primary" href="<?php echo esc_url( $order ); ?>">Đặt kiểm tra ngay</a>
		</div>
		<div class="reveal">
			<div class="doc-mock">
				<div class="doc-mock__bar"><span></span><span></span><span></span><em>Minh họa báo cáo</em></div>
				<div class="doc-mock__body">
					<p><mark class="m1">Chuyển đổi số là quá trình thay đổi tổng thể và toàn diện</mark> của cá nhân, tổ chức về cách sống, cách làm việc và phương thức sản xuất dựa trên các công nghệ số.<sup>1</sup> Trong bối cảnh đó, doanh nghiệp vừa và nhỏ tại Việt Nam đứng trước nhiều cơ hội nhưng cũng không ít thách thức.</p>
					<p>Nghiên cứu này khảo sát 214 doanh nghiệp tại Hà Nội nhằm đánh giá <mark class="m2">mức độ sẵn sàng chuyển đổi số theo bộ chỉ số</mark><sup>2</sup> được đề xuất bởi nhóm tác giả, kết hợp phỏng vấn sâu với 12 nhà quản lý.</p>
					<div class="doc-mock__src"><span class="m1">1</span> Internet source · 3%<span class="m2">2</span> Publication · 2%</div>
				</div>
			</div>
		</div>
	</div>
</section>

<?php get_template_part( 'template-parts/home-247' ); ?>
<?php get_template_part( 'template-parts/home-security' ); ?>

<?php get_template_part( 'template-parts/cta-band' ); ?>
<?php
get_footer();
