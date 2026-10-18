<?php
/**
 * Template Name: Dịch vụ - Giảm tỷ lệ đạo văn
 */
get_header();
$r     = vs_pricing()['reduce'];
$order = vs_page_url( 'dat-hang', array( 'tab' => 'reduce' ) );

get_template_part( 'template-parts/page-hero', null, array(
	'title'   => 'Giảm tỷ lệ đạo văn + AI<br><span class="hl">bằng biên tập học thuật</span>',
	'eyebrow' => 'Dịch vụ · Hỗ trợ biên tập',
	'crumbs'  => array( 'Dịch vụ' => vs_page_url( 'bang-gia' ) ),
	'lead'    => 'Biên tập viên theo chuyên ngành hỗ trợ diễn đạt lại, bổ sung trích dẫn đúng chuẩn cho <strong>bài do chính bạn nghiên cứu và viết</strong>, giữ nguyên ý và số liệu. Bạn vẫn là tác giả và rà soát mọi chỉnh sửa trước khi nộp.',
	'actions' => '<a class="btn btn--accent btn--lg" href="' . esc_url( $order ) . '">Tính giá &amp; đặt hàng</a><a class="btn btn--glass btn--lg" href="' . esc_url( vs_page_url( 'ket-qua-thuc-te' ) ) . '">Xem kết quả</a>',
	'aside'   => '<div class="goal-hero"><div class="goal-hero__card"><span>Mục tiêu dưới</span><b>30%</b><em>từ ' . esc_html( vs_money( $r['t30'] ) ) . '/trang</em></div><div class="goal-hero__card goal-hero__card--hot"><span>Mục tiêu dưới</span><b>20%</b><em>từ ' . esc_html( vs_money( $r['t20'] ) ) . '/trang</em></div><div class="goal-hero__card"><span>Tỷ lệ AI dưới</span><b>20%</b><em>từ ' . esc_html( vs_money( $r['ai_unit'] ) ) . '/trang</em></div></div>',
) );
?>

<section class="section">
	<div class="container">
		<div class="section__head reveal">
			<span class="eyebrow">Nguyên tắc</span>
			<h2>Biên tập minh bạch, đúng liêm chính học thuật</h2>
			<p>Dịch vụ không viết thay, không tạo nội dung mới, không bịa nguồn và không dùng mẹo che giấu. <a href="<?php echo esc_url( vs_page_url( 'liem-chinh-hoc-thuat' ) ); ?>">Đọc chính sách liêm chính học thuật</a>.</p>
		</div>
		<div class="features">
			<?php
			foreach ( array(
				array( 'target', 'Mục tiêu tham khảo', 'Kiểm tra lại trên cùng phần mềm sau khi biên tập. Chưa đạt thì biên tập bổ sung miễn phí.' ),
				array( 'refund', 'Hoàn phí rõ ràng', 'Chưa đạt sau khi đã biên tập bổ sung đúng hạn: hoàn phí theo Chính sách hoàn tiền.' ),
				array( 'pen', 'Bạn vẫn là tác giả', 'Không thay đổi số liệu, kết quả nghiên cứu, lập luận; bạn có thể yêu cầu bản Track Changes để rà soát.' ),
				array( 'file', 'Kèm báo cáo sau sửa', 'Bạn nhận file Word đã biên tập và báo cáo kiểm tra lại để đối chiếu.' ),
				array( 'lock', 'Bảo mật tài liệu', 'Chỉ người xử lý được xem, tự xóa sau 30 ngày.' ),
				array( 'clock', 'Đúng hạn', 'Tiêu chuẩn 3-5 ngày, nhanh 48 giờ, hỏa tốc 24 giờ.' ),
			) as $f ) :
				?>
				<div class="feature reveal">
					<div class="feature__icon"><?php echo vs_icon( $f[0] ); // phpcs:ignore ?></div>
					<h3><?php echo esc_html( $f[1] ); ?></h3>
					<p><?php echo esc_html( $f[2] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="section section--tint">
	<div class="container">
		<div class="section__head reveal">
			<span class="eyebrow">Ví dụ minh họa</span>
			<h2>Chúng tôi chỉnh sửa như thế nào?</h2>
			<p>Không thay từ máy móc. Câu văn được tái cấu trúc, tổng hợp nguồn và trích dẫn đúng chuẩn.</p>
		</div>
		<div class="rewrite reveal">
			<div class="rewrite__col rewrite__col--before">
				<div class="rewrite__label"><span>Trước</span><b class="lvl-bad">Trùng 100% với 1 nguồn</b></div>
				<p><mark>Chuyển đổi số là quá trình thay đổi tổng thể và toàn diện của cá nhân, tổ chức về cách sống, cách làm việc và phương thức sản xuất dựa trên các công nghệ số.</mark> <mark>Chuyển đổi số giúp doanh nghiệp nâng cao năng suất, giảm chi phí và mở rộng thị trường.</mark></p>
			</div>
			<div class="rewrite__arrow"><?php echo vs_icon( 'arrow', 28 ); // phpcs:ignore ?></div>
			<div class="rewrite__col rewrite__col--after">
				<div class="rewrite__label"><span>Sau</span><b class="lvl-ok">Diễn đạt mới + trích dẫn</b></div>
				<p>Theo Bộ Thông tin và Truyền thông (2021), chuyển đổi số không chỉ là việc ứng dụng công nghệ mà là sự thay đổi đồng bộ trong cách con người và tổ chức vận hành. Đối với doanh nghiệp vừa và nhỏ, lợi ích dễ thấy nhất nằm ở việc tối ưu chi phí vận hành và tiếp cận khách hàng ở kênh trực tuyến (Tác giả A &amp; Tác giả B, 2022).</p>
			</div>
		</div>
		<p class="center-note">Đoạn văn trên là ví dụ minh họa phương pháp biên tập, không phải tài liệu của khách hàng.</p>
	</div>
</section>

<?php $cases = vs_cases( 3, 'reduce' ); ?>
<?php if ( $cases ) : ?>
	<section class="section">
		<div class="container">
			<div class="section__head section__head--row reveal">
				<div><span class="eyebrow">Kết quả</span><h2>Trước và sau khi chỉnh sửa</h2></div>
				<a class="btn btn--outline" href="<?php echo esc_url( vs_page_url( 'ket-qua-thuc-te' ) ); ?>">Xem tất cả</a>
			</div>
			<div class="cases">
				<?php foreach ( $cases as $c ) { get_template_part( 'template-parts/case-card', null, array( 'case' => $c ) ); } ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<section class="section section--dark">
	<div class="container">
		<div class="section__head section__head--light reveal"><span class="eyebrow eyebrow--light">Bảng giá</span><h2>Giá theo trang, tính tự động</h2></div>
		<div class="price-strip reveal">
			<div><span>Dưới 30%</span><b><?php echo esc_html( vs_money( $r['t30'] ) ); ?></b><small>/trang</small></div>
			<div><span>Dưới 20%</span><b><?php echo esc_html( vs_money( $r['t20'] ) ); ?></b><small>/trang</small></div>
			<div><span>Nhanh 48 giờ</span><b>+<?php echo (int) round( ( $r['deadlines']['fast']['mult'] - 1 ) * 100 ); ?>%</b></div>
			<div><span>Hỏa tốc 24 giờ</span><b>+<?php echo (int) round( ( $r['deadlines']['urgent']['mult'] - 1 ) * 100 ); ?>%</b></div>
			<div><span>Giảm tỷ lệ AI</span><b><?php echo esc_html( vs_money( $r['ai_unit'] ) ); ?></b><small>/trang</small></div>
		</div>
		<p class="center-note center-note--light">Mục tiêu tự chọn (thấp hơn tỷ lệ hiện tại) · dưới 20%: +<?php echo (int) $r['step_pct']; ?>% cho mỗi 1% · giảm cả hai: phần AI -<?php echo (int) ( 100 - $r['both_pct'] ); ?>% · hệ số theo tỷ lệ hiện tại: ≤40% ×1 · 41-60% ×1,25 · 61-80% ×1,5 · trên 80% ×1,8 · Đơn tối thiểu <?php echo esc_html( vs_money( $r['min'] ) ); ?></p>
		<div class="center-actions"><a class="btn btn--accent btn--lg" href="<?php echo esc_url( $order ); ?>">Tính giá cho bài của bạn</a></div>
	</div>
</section>

<?php get_template_part( 'template-parts/cta-band' ); ?>
<?php
get_footer();
