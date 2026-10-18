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
					<?php if ( 'turnitin' === $k ) : ?><span class="badge">Phổ biến nhất</span><?php endif; ?>
					<div class="plan__head">
						<div class="tool__logo tool__logo--sm" style="--c:<?php echo esc_attr( $meta[ $k ][1] ); ?>"><?php echo esc_html( $meta[ $k ][0] ); ?></div>
						<div><h3><?php echo esc_html( $t['name'] ); ?></h3><small><?php echo esc_html( $meta[ $k ][2] ); ?></small></div>
					</div>
					<?php if ( $t['combo'] > 0 ) : ?>
						<div class="plan__price"><b><?php echo esc_html( vs_money( $t['combo'] ) ); ?></b><span>Đạo văn + AI</span></div>
					<?php else : ?>
						<div class="plan__price"><b><?php echo esc_html( vs_money( $t['plag'] ) ); ?></b><span>Kiểm tra đạo văn</span></div>
					<?php endif; ?>
					<ul class="plan__list">
						<li><span>Chỉ đạo văn</span><b><?php echo esc_html( vs_price_label( $t['plag'] ) ); ?></b></li>
						<li><span>Chỉ tỷ lệ AI</span><b class="<?php echo $t['ai'] > 0 ? '' : 'is-na'; ?>"><?php echo esc_html( vs_price_label( $t['ai'] ) ); ?></b></li>
						<?php if ( $t['combo'] > 0 ) : ?>
							<li><span>Tiết kiệm khi chọn combo</span><b class="lvl-ok"><?php echo esc_html( vs_money( $t['plag'] + $t['ai'] - $t['combo'] ) ); ?></b></li>
						<?php else : ?>
							<li><span>Cơ sở dữ liệu</span><b>Tài liệu tiếng Việt</b></li>
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
			<h2>Giảm tỷ lệ đạo văn</h2>
			<p>Tính theo số trang cần chỉnh sửa. Cam kết đạt mục tiêu trên Turnitin, không đạt hoàn tiền 100%.</p>
		</div>
		<div class="pricing">
			<div class="price-card reveal">
				<div class="targets">
					<div class="target"><span>Mục tiêu dưới</span><b>30%</b><div class="target__price"><?php echo esc_html( vs_money( $r['t30'] ) ); ?><small>/trang</small></div></div>
					<div class="target target--hot"><span>Mục tiêu dưới</span><b>20%</b><div class="target__price"><?php echo esc_html( vs_money( $r['t20'] ) ); ?><small>/trang</small></div></div>
				</div>
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
						<tr><td>Kèm giảm tỷ lệ AI dưới 20%</td><td>+<?php echo (int) $r['ai_pct']; ?>%</td></tr>
						<tr><td>Đặt từ đơn kiểm tra đã hoàn thành</td><td class="lvl-ok">-<?php echo (int) $r['linked_pct']; ?>%</td></tr>
						<tr><td>Đơn tối thiểu</td><td><?php echo esc_html( vs_money( $r['min'] ) ); ?></td></tr>
					</tbody>
				</table>
				<div class="example-calc">
					<span>Ví dụ: 60 trang, đang 45%, mục tiêu dưới 20%, tiêu chuẩn</span>
					<b>60 × <?php echo esc_html( vs_money( $r['t20'] * 1.25 ) ); ?> = <?php echo esc_html( vs_money( vs_round_k( 60 * $r['t20'] * 1.25 ) ) ); ?></b>
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
