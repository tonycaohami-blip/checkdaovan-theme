<?php
/**
 * Nội dung trang chính sách của bản cũ (6.8 trở về trước) - chỉ dùng để nhận biết trang chưa bị quản trị viên sửa.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'chinh-sach-bao-mat'   => array(
		'title'   => 'Chính sách bảo mật',
		'content' => <<<'HTML'
<p>{company} cam kết bảo vệ tài liệu và thông tin cá nhân của khách hàng. Chính sách này mô tả cách chúng tôi thu thập, sử dụng và bảo vệ dữ liệu khi bạn sử dụng dịch vụ.</p>
<h2>1. Thông tin chúng tôi thu thập</h2>
<ul>
	<li>Họ tên, số điện thoại, email bạn cung cấp khi đặt hàng.</li>
	<li>Tài liệu bạn tải lên để kiểm tra hoặc chỉnh sửa.</li>
	<li>Thông tin đơn hàng: dịch vụ, số tiền, trạng thái thanh toán.</li>
</ul>
<h2>2. Mục đích sử dụng</h2>
<ul>
	<li>Thực hiện dịch vụ kiểm tra, chỉnh sửa và trả kết quả.</li>
	<li>Liên hệ xác nhận đơn hàng, hỗ trợ khách hàng.</li>
	<li>Chúng tôi <strong>không</strong> bán, cho thuê hay chia sẻ tài liệu của bạn cho bên thứ ba vì mục đích thương mại.</li>
</ul>
<h2>3. Bảo mật tài liệu</h2>
<ul>
	<li>Tài liệu được lưu trong thư mục được chặn truy cập công khai trên máy chủ.</li>
	<li>Kiểm tra Turnitin được thực hiện ở chế độ không lưu bài (No Repository).</li>
	<li>File gốc tự động xóa sau 30 ngày kể từ ngày đặt hàng. Bạn có thể yêu cầu xóa sớm hơn bất kỳ lúc nào.</li>
	<li>Chỉ nhân sự trực tiếp xử lý đơn mới được truy cập tài liệu.</li>
</ul>
<h2>4. Quyền của khách hàng</h2>
<p>Bạn có quyền yêu cầu xem, chỉnh sửa hoặc xóa thông tin cá nhân và tài liệu của mình bằng cách liên hệ {email} hoặc hotline {hotline}.</p>
<h2>5. Thay đổi chính sách</h2>
<p>Chính sách có thể được cập nhật. Phiên bản mới nhất luôn được đăng tại trang này.</p>
HTML
		,
	),

	'dieu-khoan-dich-vu'   => array(
		'title'   => 'Điều khoản dịch vụ',
		'content' => <<<'HTML'
<p>Khi đặt hàng tại {company}, bạn đồng ý với các điều khoản dưới đây.</p>
<h2>1. Phạm vi dịch vụ</h2>
<ul>
	<li><strong>Kiểm tra:</strong> cung cấp báo cáo tỷ lệ trùng lặp và/hoặc tỷ lệ AI từ phần mềm khách hàng chọn.</li>
	<li><strong>Giảm tỷ lệ đạo văn:</strong> biên tập, diễn đạt lại, bổ sung trích dẫn để đưa tỷ lệ trùng lặp trên Turnitin về dưới mức mục tiêu đã chọn, giữ nguyên ý và số liệu gốc.</li>
</ul>
<h2>2. Trách nhiệm của khách hàng</h2>
<ul>
	<li>Cung cấp đúng thông tin liên hệ và tài liệu cần xử lý.</li>
	<li>Tự chịu trách nhiệm tuân thủ quy định học thuật của cơ sở đào tạo hoặc đơn vị xuất bản nơi bạn nộp bài.</li>
	<li>Kết quả kiểm tra mang tính tham khảo tại thời điểm kiểm tra; cơ sở dữ liệu của các phần mềm liên tục được cập nhật nên kết quả có thể dao động.</li>
</ul>
<h2>3. Giá và thanh toán</h2>
<ul>
	<li>Giá hiển thị tự động khi đặt hàng và được xác nhận trong email/trang tra cứu.</li>
	<li>Đơn được xử lý sau khi nhận thanh toán đúng số tiền và nội dung chuyển khoản là mã đơn.</li>
</ul>
<h2>4. Thời gian trả kết quả</h2>
<p>Theo thời hạn khách hàng chọn khi đặt hàng, tính từ lúc xác nhận thanh toán. Trường hợp chậm trễ do lỗi hệ thống của phần mềm bên thứ ba, chúng tôi sẽ thông báo và ưu tiên xử lý ngay khi có thể.</p>
<h2>5. Hoàn tiền</h2>
<p>Xem chi tiết tại <a href="{home}chinh-sach-hoan-tien/">Chính sách hoàn tiền</a>.</p>
HTML
		,
	),

	'chinh-sach-hoan-tien' => array(
		'title'   => 'Chính sách hoàn tiền',
		'content' => <<<'HTML'
<h2>1. Dịch vụ kiểm tra đạo văn & AI</h2>
<ul>
	<li>Hoàn 100% nếu chúng tôi không thể thực hiện kiểm tra trên phần mềm bạn đã chọn.</li>
	<li>Kiểm tra lại miễn phí nếu báo cáo bị lỗi hiển thị hoặc thiếu trang do lỗi từ phía chúng tôi.</li>
	<li>Không hoàn tiền khi báo cáo đã được trả đúng yêu cầu.</li>
</ul>
<h2>2. Dịch vụ giảm tỷ lệ đạo văn</h2>
<ul>
	<li>Nếu tỷ lệ sau chỉnh sửa (kiểm tra trên Turnitin, cùng thiết lập bộ lọc ghi trong đơn) chưa đạt mục tiêu, chúng tôi chỉnh sửa tiếp miễn phí.</li>
	<li>Nếu vẫn không đạt trong thời hạn cam kết, bạn được hoàn 100% phí dịch vụ.</li>
	<li>Không áp dụng khi khách hàng tự chỉnh sửa thêm nội dung sau khi nhận file, hoặc kiểm tra bằng tài khoản có lưu bài.</li>
</ul>
<h2>3. Cách yêu cầu hoàn tiền</h2>
<p>Liên hệ {email} hoặc hotline {hotline} kèm mã đơn hàng. Tiền được hoàn qua chuyển khoản trong vòng 1-3 ngày làm việc.</p>
HTML
		,
	),
);
