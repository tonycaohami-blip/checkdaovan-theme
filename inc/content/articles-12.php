<?php
/**
 * Bài viết 57: hướng dẫn chi tiết chương trình cộng tác viên (18 ảnh chụp giao diện thật).
 * {home} = trang chủ, {theme} = thư mục theme, {company} = tên thương hiệu.
 * Thông số (hoa hồng, ưu đãi, số ngày, mức rút) lấy từ Cài đặt qua shortcode [vs_ctv].
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(

	/* ---------------------------------------------------------- 57 */
	array(
		'slug'    => 'huong-dan-chuong-trinh-cong-tac-vien',
		'cat'     => 'dich-vu',
		'cover'   => 'huong-dan-chuong-trinh-cong-tac-vien-check-dao-van.jpg',
		'tags'    => array( 'cộng tác viên', 'tiếp thị liên kết', 'kiếm tiền online', 'hoa hồng giới thiệu', 'affiliate' ),
		'title'   => 'Hướng dẫn chương trình cộng tác viên Check Đạo Văn: Đăng ký, chia sẻ link, nhận hoa hồng và rút tiền',
		'excerpt' => 'Hướng dẫn từng bước tham gia chương trình cộng tác viên của Check Đạo Văn: tạo tài khoản, lấy link giới thiệu, theo dõi đơn hàng và hoa hồng, cập nhật ngân hàng và rút tiền - kèm 18 ảnh chụp giao diện thật.',
		'content' => <<<'HTML'
<div class="tldr"><b>Tóm tắt nhanh</b><ul>
	<li>Tham gia <strong>miễn phí</strong>, chỉ cần tạo tài khoản là có ngay link và mã giới thiệu riêng.</li>
	<li>Nhận <strong>[vs_ctv k="pct"] hoa hồng</strong> trên giá trị mỗi đơn hoàn thành; bạn bè của bạn được <strong>giảm [vs_ctv k="discount"]</strong>.</li>
	<li>Lượt giới thiệu được ghi nhận trong <strong>[vs_ctv k="days"]</strong> kể từ lần bấm link; khách đăng ký tài khoản qua link được gắn với bạn lâu dài.</li>
	<li>Rút tiền về ngân hàng khi số dư đạt <strong>[vs_ctv k="min"]</strong>, chuyển khoản trong 1-3 ngày làm việc.</li>
</ul></div>

<p>Bạn là sinh viên, học viên cao học, giảng viên hay quản trị một nhóm học tập? Mỗi mùa làm khóa luận, luận văn, rất nhiều người xung quanh bạn cần <a href="{home}kiem-tra-dao-van/">kiểm tra đạo văn và tỷ lệ AI</a> trước khi nộp. Chương trình cộng tác viên của {company} giúp bạn biến lời giới thiệu hữu ích ấy thành thu nhập: bạn chia sẻ link, bạn bè được giảm giá, còn bạn nhận hoa hồng trên mỗi đơn hoàn thành.</p>
<p>Bài viết này hướng dẫn <strong>chi tiết từng bước</strong>, từ lúc đăng ký đến khi tiền về tài khoản ngân hàng. Mọi hình ảnh đều được chụp từ giao diện thật của website; tên, số liệu trong ảnh là dữ liệu minh họa.</p>

<h2>1. Chương trình cộng tác viên là gì?</h2>
<p>Đây là chương trình giới thiệu (tiếp thị liên kết) <strong>một cấp</strong>: mỗi tài khoản trên {company} có một link và mã giới thiệu riêng. Khi ai đó vào website qua link của bạn và đặt dịch vụ, đơn hàng được ghi nhận cho bạn. Khi đơn hoàn thành, bạn nhận hoa hồng theo tỷ lệ phần trăm giá trị đơn. Chương trình không thu phí tham gia, không yêu cầu doanh số tối thiểu và không có hoa hồng từ việc tuyển thêm người - bạn chỉ nhận tiền từ dịch vụ thực tế mà khách hàng sử dụng.</p>

<figure><img src="{theme}assets/img/blog/huong-dan-cong-tac-vien-01-trang-cong-tac-vien.jpg" alt="Trang chương trình cộng tác viên Check Đạo Văn với tỷ lệ hoa hồng, thời gian ghi nhận và mức rút tối thiểu" width="1280" height="519" loading="lazy"><figcaption>Ảnh 1: Trang giới thiệu chương trình cộng tác viên tại {home}cong-tac-vien/ - thông số chính hiển thị ngay đầu trang.</figcaption></figure>

<p>Ba con số cần nhớ hiển thị ngay trên trang chương trình: <strong>tỷ lệ hoa hồng</strong>, <strong>số ngày ghi nhận</strong> và <strong>số dư tối thiểu để rút</strong>. Các thông số này có thể được điều chỉnh theo từng thời kỳ; con số trên trang luôn là chính sách đang áp dụng.</p>

<h2>2. Cách hoạt động trong 3 bước</h2>
<figure><img src="{theme}assets/img/blog/huong-dan-cong-tac-vien-02-cach-hoat-dong.jpg" alt="3 bước tham gia chương trình cộng tác viên: tạo tài khoản, chia sẻ link, nhận hoa hồng" width="1280" height="593" loading="lazy"><figcaption>Ảnh 2: Ba bước để bắt đầu: tạo tài khoản, chia sẻ link, nhận hoa hồng.</figcaption></figure>
<ol>
	<li><strong>Tạo tài khoản:</strong> đăng ký miễn phí, hệ thống cấp ngay mã và link giới thiệu.</li>
	<li><strong>Chia sẻ link:</strong> gửi cho bạn bè, đăng nhóm lớp, Facebook, Zalo.</li>
	<li><strong>Nhận hoa hồng:</strong> mỗi đơn hoàn thành của khách được giới thiệu cộng hoa hồng vào tài khoản của bạn; đủ mức tối thiểu là rút về ngân hàng.</li>
</ol>

<h2>3. Bạn có thể nhận được bao nhiêu?</h2>
<p>Trên trang chương trình có công cụ <strong>ước tính thu nhập</strong>: kéo thanh trượt số đơn được giới thiệu mỗi tháng và giá trị đơn trung bình, hệ thống tính ngay thu nhập dự kiến. Ví dụ, nếu mỗi tháng có 20 đơn với giá trị trung bình 500.000đ, thu nhập ước tính là 1.000.000đ với mức hoa hồng 10%.</p>
<figure><img src="{theme}assets/img/blog/huong-dan-cong-tac-vien-03-tinh-thu-nhap.jpg" alt="Công cụ ước tính thu nhập cộng tác viên theo số đơn và giá trị đơn trung bình" width="1280" height="644" loading="lazy"><figcaption>Ảnh 3: Công cụ ước tính thu nhập - con số chỉ mang tính tham khảo.</figcaption></figure>
<p>Lưu ý: đây chỉ là ước tính. Thu nhập thực tế phụ thuộc số khách bạn giới thiệu, dịch vụ họ chọn (kiểm tra đạo văn có giá thấp hơn dịch vụ biên tập) và số đơn hoàn thành. Hãy coi đây là nguồn thu nhập thêm từ việc giới thiệu dịch vụ hữu ích, không phải một cam kết thu nhập.</p>

<h2>Bước 1: Tạo tài khoản miễn phí</h2>
<p>Vào <a href="{home}tai-khoan/?dang-ky=1">trang đăng ký tài khoản</a> hoặc bấm nút “Đăng ký làm cộng tác viên” trên trang chương trình. Điền họ tên, số điện thoại, email và mật khẩu (ít nhất 8 ký tự), đánh dấu đồng ý điều khoản rồi bấm <strong>Tạo tài khoản</strong>.</p>
<figure><img src="{theme}assets/img/blog/huong-dan-cong-tac-vien-05-dang-ky-tai-khoan.jpg" alt="Form tạo tài khoản miễn phí trên Check Đạo Văn" width="693" height="899" loading="lazy"><figcaption>Ảnh 4: Form tạo tài khoản - chỉ mất khoảng 30 giây.</figcaption></figure>
<div class="callout callout--tip"><b>Mẹo</b>Dùng đúng số điện thoại của bạn. Số điện thoại vừa là thông tin đăng nhập, vừa giúp hệ thống nhận biết và không tính hoa hồng cho đơn bạn tự đặt cho chính mình.</div>

<h2>Bước 2: Mở trang Cộng tác viên trong tài khoản</h2>
<p>Sau khi đăng nhập, bạn vào trang tổng quan tài khoản. Có hai cách mở khu vực cộng tác viên: bấm thẻ <strong>“Giới thiệu bạn bè”</strong> ở giữa trang, hoặc chọn mục <strong>“Cộng tác viên”</strong> ở menu bên trái.</p>
<figure><img src="{theme}assets/img/blog/huong-dan-cong-tac-vien-06-tai-khoan-tong-quan.jpg" alt="Trang tổng quan tài khoản với thẻ Giới thiệu bạn bè và menu Cộng tác viên" width="1280" height="780" loading="lazy"><figcaption>Ảnh 5: Trang tổng quan tài khoản - thẻ “Giới thiệu bạn bè” và menu “Cộng tác viên”.</figcaption></figure>
<figure><img src="{theme}assets/img/blog/huong-dan-cong-tac-vien-07a-tab-cong-tac-vien.jpg" alt="Khu vực Cộng tác viên trong tài khoản với link, mã giới thiệu, thống kê và rút tiền" width="1280" height="860" loading="lazy"><figcaption>Ảnh 6: Toàn cảnh khu vực Cộng tác viên: link, mã, thống kê và rút tiền trên cùng một màn hình.</figcaption></figure>

<h2>Bước 3: Lấy link, mã giới thiệu và chia sẻ</h2>
<p>Ở đầu trang Cộng tác viên là <strong>link giới thiệu</strong> và <strong>mã giới thiệu</strong> riêng của bạn. Bấm <strong>Sao chép</strong> để copy, hoặc dùng nút <strong>Chia sẻ Facebook</strong>, <strong>Chia sẻ Zalo</strong> để đăng nhanh.</p>
<figure><img src="{theme}assets/img/blog/huong-dan-cong-tac-vien-07-link-va-ma-gioi-thieu.jpg" alt="Link giới thiệu và mã giới thiệu cộng tác viên với nút sao chép và chia sẻ" width="1280" height="280" loading="lazy"><figcaption>Ảnh 7: Link và mã giới thiệu - bấm “Sao chép” hoặc chia sẻ thẳng lên Facebook, Zalo.</figcaption></figure>
<p>Bạn có thể gắn mã giới thiệu vào bất kỳ trang nào của website, không chỉ trang chủ. Ví dụ, khi chia sẻ một bài hướng dẫn hữu ích, chỉ cần thêm <code>?ref=MÃ-CỦA-BẠN</code> vào cuối đường dẫn:</p>
<ul>
	<li><code>{home}?ref=MÃ-CỦA-BẠN</code> - trang chủ</li>
	<li><code>{home}bang-gia/?ref=MÃ-CỦA-BẠN</code> - bảng giá</li>
	<li><code>{home}kiem-tra-ai-la-gi/?ref=MÃ-CỦA-BẠN</code> - bài hướng dẫn kiểm tra AI</li>
</ul>
<p>Giao diện cộng tác viên hiển thị tốt trên điện thoại, nên bạn có thể sao chép và gửi link ngay trong lúc nhắn tin.</p>
<figure><img src="{theme}assets/img/blog/huong-dan-cong-tac-vien-16-dien-thoai.jpg" alt="Giao diện cộng tác viên trên điện thoại" width="780" height="1688" loading="lazy"><figcaption>Ảnh 8: Khu vực Cộng tác viên trên điện thoại.</figcaption></figure>

<h2>Bước 4: Bạn bè của bạn nhận được gì?</h2>
<p>Khi bạn bè bấm vào link của bạn, hệ thống ghi nhận lượt giới thiệu trong [vs_ctv k="days"]. Trong thời gian đó, họ được ưu đãi [vs_ctv k="discount"] cho đơn hàng. Nếu họ tạo tài khoản, form đăng ký hiển thị rõ thông báo đang được giới thiệu bởi cộng tác viên, và tài khoản đó được gắn với bạn lâu dài cho các đơn sau.</p>
<figure><img src="{theme}assets/img/blog/huong-dan-cong-tac-vien-10-khach-duoc-gioi-thieu-dang-ky.jpg" alt="Thông báo ưu đãi khi khách được giới thiệu tạo tài khoản" width="693" height="1061" loading="lazy"><figcaption>Ảnh 9: Khách vào qua link giới thiệu thấy thông báo ưu đãi ngay khi đăng ký.</figcaption></figure>
<p>Ở trang đặt hàng, khoản ưu đãi được tự động trừ trong phần <strong>Tạm tính</strong>, khách không cần nhập mã. Lưu ý ưu đãi giới thiệu không cộng dồn với mã giảm giá khác.</p>
<figure><img src="{theme}assets/img/blog/huong-dan-cong-tac-vien-11-uu-dai-khi-dat-hang.jpg" alt="Ưu đãi giới thiệu tự động trừ trong phần tạm tính đơn hàng" width="618" height="794" loading="lazy"><figcaption>Ảnh 10: Dòng “Ưu đãi giới thiệu” tự động xuất hiện khi khách đặt hàng.</figcaption></figure>

<h2>Bước 5: Theo dõi thống kê theo thời gian thực</h2>
<p>Ngay dưới link giới thiệu là 6 ô thống kê giúp bạn biết hiệu quả chia sẻ:</p>
<figure><img src="{theme}assets/img/blog/huong-dan-cong-tac-vien-08-thong-ke-hoa-hong.jpg" alt="Bảng thống kê lượt click, khách đăng ký, đơn được giới thiệu và hoa hồng" width="1280" height="367" loading="lazy"><figcaption>Ảnh 11: Thống kê lượt click, khách đăng ký, đơn hàng và hoa hồng.</figcaption></figure>
<ul>
	<li><strong>Lượt click:</strong> số lần link của bạn được bấm (mỗi người được tính một lần trong thời gian ghi nhận).</li>
	<li><strong>Khách đăng ký:</strong> số người tạo tài khoản qua link của bạn.</li>
	<li><strong>Đơn được giới thiệu:</strong> số đơn hợp lệ gắn với bạn.</li>
	<li><strong>Hoa hồng chờ duyệt:</strong> hoa hồng của các đơn chưa hoàn thành.</li>
	<li><strong>Đã nhận:</strong> tổng tiền đã được chuyển khoản cho bạn.</li>
	<li><strong>Có thể rút:</strong> số dư đã duyệt, chưa rút - đây là con số bạn có thể yêu cầu chuyển về ngân hàng.</li>
</ul>

<h2>Bước 6: Theo dõi từng đơn hàng và trạng thái hoa hồng</h2>
<p>Bảng <strong>Đơn hàng được giới thiệu</strong> liệt kê từng đơn: ngày đặt, khách hàng (tên được ẩn một phần để bảo vệ thông tin cá nhân), giá trị đơn, hoa hồng và trạng thái.</p>
<figure><img src="{theme}assets/img/blog/huong-dan-cong-tac-vien-12-don-hang-duoc-gioi-thieu.jpg" alt="Danh sách đơn hàng được giới thiệu với hoa hồng và trạng thái" width="1280" height="739" loading="lazy"><figcaption>Ảnh 12: Danh sách đơn được giới thiệu - tên khách được ẩn một phần để bảo mật.</figcaption></figure>
<table>
	<thead><tr><th>Trạng thái</th><th>Ý nghĩa</th></tr></thead>
	<tbody>
		<tr><td>Chờ duyệt</td><td>Khách đã đặt đơn, đơn đang chờ thanh toán hoặc đang xử lý.</td></tr>
		<tr><td>Đã duyệt</td><td>Đơn đã hoàn thành, hoa hồng được cộng vào số dư có thể rút.</td></tr>
		<tr><td>Đã hủy</td><td>Đơn bị hủy hoặc hoàn tiền, hoa hồng không được tính.</td></tr>
	</tbody>
</table>

<h2>Bước 7: Cập nhật tài khoản ngân hàng nhận hoa hồng</h2>
<p>Trước khi rút tiền lần đầu, vào <strong>Thông tin tài khoản</strong> và điền mục <strong>Tài khoản nhận hoa hồng</strong>: tên ngân hàng, số tài khoản và tên chủ tài khoản (in hoa, không dấu, trùng với tên trên ngân hàng). Bấm <strong>Lưu thông tin</strong>.</p>
<figure><img src="{theme}assets/img/blog/huong-dan-cong-tac-vien-09-thong-tin-ngan-hang.jpg" alt="Cập nhật tài khoản ngân hàng nhận hoa hồng cộng tác viên" width="1280" height="704" loading="lazy"><figcaption>Ảnh 13: Điền thông tin ngân hàng nhận hoa hồng trong mục Thông tin tài khoản.</figcaption></figure>
<div class="callout callout--info"><b>Lưu ý</b>Chủ tài khoản ngân hàng nên là chính bạn. Thông tin sai có thể khiến việc chuyển khoản bị chậm hoặc bị trả lại.</div>

<h2>Bước 8: Gửi yêu cầu rút tiền</h2>
<p>Khi số dư <strong>Có thể rút</strong> đạt mức tối thiểu [vs_ctv k="min"], khung <strong>Rút tiền</strong> hiển thị tài khoản nhận và nút <strong>Yêu cầu rút</strong> kèm số tiền. Bấm nút để gửi yêu cầu.</p>
<figure><img src="{theme}assets/img/blog/huong-dan-cong-tac-vien-13-yeu-cau-rut-tien.jpg" alt="Khung rút tiền với nút yêu cầu rút hoa hồng" width="1280" height="237" loading="lazy"><figcaption>Ảnh 14: Khung Rút tiền - kiểm tra lại tài khoản nhận trước khi bấm.</figcaption></figure>
<p>Hệ thống xác nhận đã nhận yêu cầu và thông báo thời gian xử lý:</p>
<figure><img src="{theme}assets/img/blog/huong-dan-cong-tac-vien-14a-thong-bao-da-gui.jpg" alt="Thông báo đã gửi yêu cầu rút tiền thành công" width="1280" height="115" loading="lazy"><figcaption>Ảnh 15: Thông báo gửi yêu cầu rút tiền thành công.</figcaption></figure>
<p>Trong lúc chờ, khung Rút tiền hiển thị số tiền <strong>đang chờ chuyển khoản</strong>. Mỗi lần chỉ có một yêu cầu đang chờ; khi yêu cầu này được xử lý, bạn có thể gửi yêu cầu mới.</p>
<figure><img src="{theme}assets/img/blog/huong-dan-cong-tac-vien-14-cho-chuyen-khoan.jpg" alt="Trạng thái đang chờ chuyển khoản hoa hồng" width="1280" height="235" loading="lazy"><figcaption>Ảnh 16: Trạng thái đang chờ chuyển khoản.</figcaption></figure>

<h2>Bước 9: Theo dõi lịch sử rút tiền</h2>
<p>Bảng <strong>Lịch sử rút tiền</strong> ghi lại mọi yêu cầu cùng trạng thái: <em>Chờ chuyển khoản</em>, <em>Đã chuyển</em> hoặc <em>Từ chối</em> (ví dụ khi thông tin ngân hàng không hợp lệ). Thời gian chuyển khoản thường trong 1-3 ngày làm việc.</p>
<figure><img src="{theme}assets/img/blog/huong-dan-cong-tac-vien-15-lich-su-rut-tien.jpg" alt="Lịch sử rút tiền hoa hồng cộng tác viên" width="1280" height="375" loading="lazy"><figcaption>Ảnh 17: Lịch sử rút tiền với trạng thái từng lần.</figcaption></figure>

<h2>Quy định quan trọng cần nắm</h2>
<ul class="checklist">
	<li><strong>Không tự giới thiệu:</strong> đơn có số điện thoại trùng với tài khoản cộng tác viên không được tính hoa hồng.</li>
	<li><strong>Chỉ tính đơn hoàn thành:</strong> đơn bị hủy hoặc hoàn tiền không được tính.</li>
	<li><strong>Không chạy quảng cáo từ khóa</strong> bằng tên thương hiệu {company}.</li>
	<li><strong>Không đưa thông tin sai</strong> về dịch vụ, ví dụ “cam kết qua Turnitin”, “lách AI”, “làm luận văn trọn gói”. Dịch vụ của chúng tôi tuân thủ <a href="{home}liem-chinh-hoc-thuat/">chính sách liêm chính học thuật</a>.</li>
	<li><strong>Một cấp, không thu phí:</strong> không có hoa hồng từ việc tuyển thêm cộng tác viên.</li>
	<li><strong>Thuế thu nhập cá nhân:</strong> hoa hồng là thu nhập chịu thuế theo quy định; khi chi trả có thể được khấu trừ thuế và cần thông tin để kê khai.</li>
</ul>
<figure><img src="{theme}assets/img/blog/huong-dan-cong-tac-vien-04-cau-hoi-thuong-gap.jpg" alt="Câu hỏi thường gặp về chương trình cộng tác viên" width="1280" height="982" loading="lazy"><figcaption>Ảnh 18: Mục câu hỏi về chương trình trên trang cộng tác viên.</figcaption></figure>

<h2>Mẹo chia sẻ hiệu quả và có trách nhiệm</h2>
<ol>
	<li><strong>Chia sẻ đúng lúc:</strong> thời điểm trước hạn nộp khóa luận, luận văn, bài tập lớn là lúc bạn bè cần kiểm tra nhất.</li>
	<li><strong>Chia sẻ kiến thức, không chỉ link:</strong> gửi kèm bài viết hữu ích như <a href="{home}ty-le-dao-van-bao-nhieu-la-dat/">tỷ lệ đạo văn bao nhiêu là đạt</a> hay <a href="{home}check-ai-online-truoc-khi-nop-bai/">check AI trước khi nộp bài</a> - người nhận thấy giá trị và tin tưởng hơn.</li>
	<li><strong>Nói đúng sự thật:</strong> giới thiệu đúng dịch vụ và ưu đãi, không hứa hẹn kết quả.</li>
	<li><strong>Chọn đúng nhóm:</strong> nhóm lớp, nhóm ôn thi, cộng đồng học thuật nơi mọi người thực sự cần dịch vụ; tránh spam.</li>
	<li><strong>Theo dõi thống kê:</strong> so sánh lượt click và số đơn để biết kênh nào hiệu quả.</li>
</ol>
<blockquote>Sẵn sàng bắt đầu? <a href="{home}tai-khoan/?dang-ky=1">Tạo tài khoản miễn phí</a> và lấy link giới thiệu của bạn ngay hôm nay. Xem thông tin chương trình tại <a href="{home}cong-tac-vien/">trang cộng tác viên</a>.</blockquote>

<h2>Câu hỏi thường gặp</h2>
<h3>Tham gia chương trình cộng tác viên có mất phí không?</h3>
<p>Không. Bạn chỉ cần tạo tài khoản miễn phí là có ngay link và mã giới thiệu.</p>
<h3>Khi nào hoa hồng được duyệt?</h3>
<p>Hoa hồng ở trạng thái “Chờ duyệt” khi khách đặt đơn và chuyển sang “Đã duyệt” khi đơn hoàn thành. Đơn bị hủy hoặc hoàn tiền không được tính.</p>
<h3>Khách bấm link nhưng vài ngày sau mới đặt hàng thì có được tính không?</h3>
<p>Có, nếu khách đặt hàng trong thời gian ghi nhận [vs_ctv k="days"] trên cùng thiết bị và trình duyệt. Nếu khách tạo tài khoản qua link của bạn, các đơn sau của tài khoản đó cũng được ghi nhận cho bạn.</p>
<h3>Bao lâu thì nhận được tiền sau khi yêu cầu rút?</h3>
<p>Thường trong 1-3 ngày làm việc. Bạn theo dõi trạng thái trong bảng Lịch sử rút tiền.</p>
<h3>Có giới hạn số người được giới thiệu không?</h3>
<p>Không giới hạn. Bạn có thể giới thiệu bao nhiêu người tùy thích, miễn tuân thủ quy định của chương trình.</p>
HTML
		,
	),
);
