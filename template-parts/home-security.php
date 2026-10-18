<?php
/**
 * Khối "Bảo mật" nền tối.
 */
$items = array(
	array( 'shield', 'Không lưu bài', 'Mọi lượt kiểm tra ở chế độ No Repository - bản nộp chính thức không bị trùng với bản kiểm tra.' ),
	array( 'lock', 'Kết nối mã hóa', 'Tải file và tải báo cáo qua HTTPS. Thanh toán trực tiếp từ app ngân hàng, website không lưu thông tin thẻ.' ),
	array( 'file', 'Thư mục riêng', 'File được lưu trong thư mục chặn truy cập công khai, không nằm trong thư viện media.' ),
	array( 'link', 'Liên kết có chữ ký', 'Báo cáo chỉ tải được qua liên kết ký bằng khóa bí mật riêng cho từng đơn.' ),
	array( 'clock', 'Tự xóa sau 30 ngày', 'File gốc và báo cáo tự động bị xóa khỏi máy chủ. Có thể yêu cầu xóa sớm hơn.' ),
	array( 'list', 'Nhật ký truy vết', 'Mỗi bước xử lý được ghi thời gian. Quy trình tự động hạn chế tối đa người chạm vào tài liệu.' ),
);
?>
<section class="section section--dark section--secure">
	<div class="container">
		<div class="section__head section__head--light reveal">
			<span class="eyebrow eyebrow--light">Bảo mật</span>
			<h2>Tài liệu của bạn được bảo vệ ở mọi bước</h2>
			<p>Luận văn là tài sản trí tuệ nhiều tháng nghiên cứu. Chúng tôi thiết kế hệ thống để nó chỉ đi đúng nơi cần đến.</p>
		</div>
		<div class="secure-grid">
			<?php foreach ( $items as $it ) : ?>
				<div class="secure reveal">
					<span class="secure__icon"><?php echo vs_icon( $it[0], 24 ); // phpcs:ignore ?></span>
					<h3><?php echo esc_html( $it[1] ); ?></h3>
					<p><?php echo esc_html( $it[2] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
		<div class="center-actions reveal"><a class="btn btn--glass" href="<?php echo esc_url( vs_page_url( 'chinh-sach-bao-mat' ) ); ?>">Đọc chính sách bảo mật</a></div>
	</div>
</section>
