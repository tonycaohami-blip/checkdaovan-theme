<?php
/**
 * Template Name: Bảng giá
 */
get_header();
$p     = vs_pricing();
$check = $p['check'];
$r     = $p['reduce'];
$meta  = vs_tool_meta();
get_template_part( 'template-parts/page-hero', null, array(
	'title'   => 'Bảng giá dịch vụ',
	'eyebrow' => 'Minh bạch · Không phí ẩn',
	'lead'    => 'Giá được tính tự động khi bạn đặt hàng. Thanh toán bằng chuyển khoản VietQR sau khi đã xem giá.',
	'size'    => 'sm',
) );
?>
<section class="section section--tight">
	<div class="container">
		<div class="price-head reveal">
			<h2>Kiểm tra đạo văn &amp; tỷ lệ AI</h2>
			<p>Giá cho 1 file, tối đa <?php echo (int) $check['pages_included']; ?> trang. Đã gồm báo cáo PDF gốc.</p>
		</div>
		<div class="plan-grid">
			<?php foreach ( $p['tools'] as $k => $t ) : ?>
				<article class="plan reveal<?php echo 'turnitin' === $k ? ' plan--hot' : ''; ?>">
					<?php if ( 'turnitin' === $k ) : ?><span class="badge">Nhiều người chọn</span><?php endif; ?>
					<div class="plan__head">
						<div class="tool__logo tool__logo--sm<?php echo esc_attr( vs_tool_mark_class( $k ) ); ?>" style="--c:<?php echo esc_attr( $meta[ $k ][1] ); ?>"><?php echo vs_tool_mark( $k ); // phpcs:ignore ?></div>
						<div><h3><?php echo esc_html( $t['name'] ); ?></h3><small><?php echo esc_html( $meta[ $k ][2] ); ?></small> <?php echo vs_tool_mode_badge( $k ); // phpcs:ignore ?></div>
					</div>
					<?php
					if ( $t['combo'] > 0 ) {
						$head = array( $t['combo'], 'Đạo văn + AI' );
					} elseif ( $t['plag'] > 0 ) {
						$head = array( $t['plag'], 'Kiểm tra đạo văn' );
					} else {
						$head = array( $t['ai'], 'Kiểm tra tỷ lệ AI' );
					}
					?>
					<div class="plan__price"><b><?php echo esc_html( vs_money( $head[0] ) ); ?></b><span><?php echo esc_html( $head[1] ); ?></span></div>
					<ul class="plan__list">
						<li><span>Chỉ đạo văn</span><b class="<?php echo $t['plag'] > 0 ? '' : 'is-na'; ?>"><?php echo esc_html( vs_price_label( $t['plag'] ) ); ?></b></li>
						<li><span>Chỉ tỷ lệ AI</span><b class="<?php echo $t['ai'] > 0 ? '' : 'is-na'; ?>"><?php echo esc_html( vs_price_label( $t['ai'] ) ); ?></b></li>
						<?php if ( $t['combo'] > 0 ) : ?>
							<li><span>Tiết kiệm khi chọn combo</span><b class="lvl-ok"><?php echo esc_html( vs_money( $t['plag'] + $t['ai'] - $t['combo'] ) ); ?></b></li>
						<?php elseif ( ! empty( $t['note'] ) ) : ?>
							<li><span><?php echo esc_html( $t['note'][0] ); ?></span><b><?php echo esc_html( $t['note'][1] ); ?></b></li>
						<?php endif; ?>
					</ul>
					<a class="btn <?php echo 'turnitin' === $k ? 'btn--accent' : 'btn--outline'; ?> btn--block" href="<?php echo esc_url( vs_page_url( 'dat-hang', array( 'tab' => 'check', 'tool' => $k ) ) ); ?>">Đặt <?php echo esc_html( $t['name'] ); ?></a>
				</article>
			<?php endforeach; ?>
		</div>
		<div class="extras reveal">
			<div><b>Combo</b><span>Đạo văn + AI rẻ hơn mua lẻ</span></div>
			<div><b>1</b><span>phần mềm cho mỗi đơn - báo cáo riêng, rõ ràng</span></div>
			<div><b>+<?php echo (int) $check['express_pct']; ?>%</b><span>hỏa tốc trả trong 30 phút</span></div>
			<div><b>+<?php echo (int) $check['extra_pct']; ?>%</b><span>mỗi <?php echo (int) $check['pages_included']; ?> trang vượt thêm</span></div>
		</div>
	</div>
</section>

<section class="section section--tint">
	<div class="container">
		<div class="price-head reveal">
			<h2>Giảm tỷ lệ đạo văn &amp; AI</h2>
			<p>Bạn tự chọn mục tiêu (thấp hơn tỷ lệ hiện tại). Tính theo số trang cần chỉnh sửa. Mục tiêu là mức tham khảo: chưa đạt trên cùng phần mềm và thiết lập thì biên tập bổ sung miễn phí hoặc hoàn phí theo chính sách. Dịch vụ chỉ dành cho bài do chính bạn viết.</p>
		</div>
		<div class="pricing">
			<div class="price-card reveal">
				<div class="targets targets--3">
					<div class="target"><span>Đạo văn dưới</span><b>30%</b><div class="target__price"><?php echo esc_html( vs_money( $r['t30'] ) ); ?><small>/trang</small></div></div>
					<div class="target target--hot"><span>Đạo văn dưới</span><b>20%</b><div class="target__price"><?php echo esc_html( vs_money( $r['t20'] ) ); ?><small>/trang</small></div></div>
					<div class="target"><span>Tỷ lệ AI dưới</span><b>20%</b><div class="target__price"><?php echo esc_html( vs_money( $r['ai_unit'] ) ); ?><small>/trang</small></div></div>
				</div>
				<p class="muted" style="font-size:13.5px;margin:10px 0 0">Mục tiêu tự chọn: từ 20-30% tính theo tỷ lệ giữa hai mức; thấp hơn 20% cộng thêm <?php echo (int) $r['step_pct']; ?>% đơn giá cho mỗi 1%.</p>
				<h4 class="mini-title">Hệ số theo tỷ lệ hiện tại</h4>
				<div class="tiers">
					<?php
					$prev = 0;
					foreach ( $r['tiers'] as $tier ) :
						$label = 0 === $prev ? '≤ ' . $tier[0] . '%' : ( $tier[0] >= 100 ? '> ' . $prev . '%' : ( $prev + 1 ) . '-' . $tier[0] . '%' );
						$prev  = $tier[0];
						?>
						<div class="tier"><span><?php echo esc_html( $label ); ?></span><b>×<?php echo esc_html( rtrim( rtrim( number_format( $tier[1], 2, ',', '' ), '0' ), ',' ) ); ?></b></div>
					<?php endforeach; ?>
				</div>
			</div>
			<div class="price-card reveal">
				<h4 class="mini-title">Thời hạn &amp; tùy chọn</h4>
				<table class="ptable">
					<tbody>
					<?php foreach ( $r['deadlines'] as $d ) : ?>
						<tr><td><?php echo esc_html( $d['label'] ); ?></td><td><?php echo $d['mult'] > 1 ? '+' . (int) round( ( $d['mult'] - 1 ) * 100 ) . '%' : 'Giá gốc'; ?></td></tr>
					<?php endforeach; ?>
						<tr><td>Giảm cả đạo văn + AI</td><td class="lvl-ok">Phần AI -<?php echo (int) ( 100 - $r['both_pct'] ); ?>%</td></tr>
						<tr><td>Đặt từ đơn kiểm tra đã hoàn thành</td><td class="lvl-ok">-<?php echo (int) $r['linked_pct']; ?>%</td></tr>
						<tr><td>Đơn tối thiểu</td><td><?php echo esc_html( vs_money( $r['min'] ) ); ?></td></tr>
					</tbody>
				</table>
				<div class="example-calc">
					<?php $ex = vs_calc_reduce( array( 'goal' => 'plag', 'current' => 45, 'target' => 20, 'pages' => 60, 'deadline' => 'standard' ) ); ?>
					<span>Ví dụ: 60 trang, đạo văn 45% → dưới 20%, tiêu chuẩn</span>
					<b>60 × <?php echo esc_html( vs_money( vs_reduce_unit_plag( 20 ) * vs_reduce_tier( 45 ) ) ); ?> = <?php echo esc_html( vs_money( $ex['total'] ) ); ?></b>
				</div>
				<a class="btn btn--accent btn--block" href="<?php echo esc_url( vs_page_url( 'dat-hang', array( 'tab' => 'reduce' ) ) ); ?>">Tính giá cho bài của bạn</a>
			</div>
		</div>
	</div>
</section>

<section class="section">
	<div class="container split split--center">
		<div class="reveal">
			<span class="eyebrow">Ưu đãi</span>
			<h2>Tiết kiệm hơn với tài khoản</h2>
			<ul class="ticks ticks--lg">
				<li>Quản lý mọi đơn hàng, tải báo cáo bất cứ lúc nào</li>
				<li>Đặt giảm tỷ lệ từ đơn kiểm tra chỉ 1 chạm, giảm thêm <?php echo (int) $r['linked_pct']; ?>%</li>
				<li>Giới thiệu bạn bè nhận hoa hồng <?php echo esc_html( vs_opt( 'aff_pct' ) ); ?>% mỗi đơn</li>
			</ul>
		</div>
		<div class="cta-card reveal">
			<?php echo vs_icon( 'gift', 36 ); // phpcs:ignore ?>
			<h3>Tạo tài khoản miễn phí</h3>
			<p>Chỉ cần số điện thoại và email.</p>
			<a class="btn btn--primary btn--block" href="<?php echo esc_url( vs_account_url( '', array( 'dang-ky' => 1 ) ) ); ?>">Đăng ký ngay</a>
		</div>
	</div>
</section>
<?php
get_template_part( 'template-parts/cta-band' );
get_footer();
