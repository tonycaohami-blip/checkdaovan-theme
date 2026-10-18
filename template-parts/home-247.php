<?php
/**
 * Khối "Hoạt động 24/7": quy trình tự động + dòng thời gian minh họa.
 * Nội dung tự điều chỉnh theo cấu hình tự động trong Quản trị > Tự động 24/7.
 */
$auto = vs_auto_on();
$pay  = vs_auto_pay_on();
$steps = array(
	array( 'file', 'Đặt hàng', 'Tải file, hệ thống tự đọc số trang và báo giá ngay.' ),
	array( 'qr', 'Thanh toán QR', $pay ? 'Tiền vào là đơn được xác nhận tự động, không cần gửi ảnh.' : 'Quét VietQR, số tiền và nội dung điền sẵn.' ),
	array( 'target', 'Kiểm tra', $auto ? 'Gửi thẳng đến phần mềm qua API chính thức, chế độ không lưu bài.' : 'Kiểm tra ở chế độ không lưu bài, ưu tiên xử lý ngay.' ),
	array( 'mail', 'Nhận báo cáo', 'PDF gốc gửi qua email và lưu trong tài khoản của bạn.' ),
);
?>
<section class="section section--247">
	<div class="container">
		<div class="split split--center">
			<div class="reveal">
				<span class="eyebrow">Hoạt động 24/7</span>
				<h2>Hạn nộp lúc 8 giờ sáng?<br>Đặt kiểm tra lúc 2 giờ đêm vẫn kịp.</h2>
				<p class="muted lead-sm">Hệ thống nhận đơn, xác nhận thanh toán<?php echo $auto ? ', gửi kiểm tra và trả kết quả' : ''; ?> hoàn toàn tự động - kể cả cuối tuần và ngày lễ. Bạn theo dõi từng bước ngay trong tài khoản.</p>
				<ol class="flow">
					<?php foreach ( $steps as $i => $s ) : ?>
						<li class="flow__step">
							<span class="flow__icon"><?php echo vs_icon( $s[0], 22 ); // phpcs:ignore ?></span>
							<div><b><?php echo esc_html( ( $i + 1 ) . '. ' . $s[1] ); ?></b><span><?php echo esc_html( $s[2] ); ?></span></div>
						</li>
					<?php endforeach; ?>
				</ol>
			</div>
			<div class="timeline-card reveal" aria-label="Minh họa tiến độ đơn hàng">
				<div class="timeline-card__head">
					<div><span class="live-dot"></span> Đơn VS1018K7QM</div>
					<em>Minh họa</em>
				</div>
				<ul class="timeline">
					<li class="is-done"><time>02:14</time><b>Đã đặt hàng</b><span>Luận văn · 86 trang · Turnitin</span></li>
					<li class="is-done"><time>02:15</time><b>Đã nhận thanh toán</b><span>Xác nhận tự động qua ngân hàng</span></li>
					<li class="is-done"><time>02:16</time><b>Đã gửi đến phần mềm</b><span>Chế độ không lưu bài</span></li>
					<li class="is-run"><time>02:29</time><b>Đang xuất báo cáo PDF</b><span>Similarity 14%</span></li>
					<li><time>-</time><b>Gửi email &amp; tài khoản</b><span>Kèm file PDF gốc</span></li>
				</ul>
				<div class="timeline-card__foot"><?php echo vs_icon( 'clock', 16 ); // phpcs:ignore ?> Thời gian thực tế phụ thuộc độ dài tài liệu và hàng đợi của phần mềm.</div>
			</div>
		</div>
	</div>
</section>
