<?php
/**
 * Trang mẫu. {company}, {email}, {hotline}, {home} được thay khi nhập.
 * Lưu ý: đây là mẫu tham khảo - hãy điều chỉnh cho đúng chính sách thực tế của bạn.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'trang-chu'            => array( 'title' => 'Trang chủ', 'content' => '' ),
	'blog'                 => array( 'title' => 'Blog', 'content' => '' ),
	'dat-hang'             => array( 'title' => 'Đặt hàng', 'content' => '', 'template' => 'order' ),
	'kiem-tra-dao-van'     => array( 'title' => 'Kiểm tra đạo văn & tỷ lệ AI', 'content' => '', 'template' => 'service-check' ),
	'giam-ty-le-dao-van'   => array( 'title' => 'Giảm tỷ lệ đạo văn + AI', 'content' => '', 'template' => 'service-reduce' ),
	'bang-gia'             => array( 'title' => 'Bảng giá', 'content' => '', 'template' => 'pricing' ),
	'ket-qua-thuc-te'      => array( 'title' => 'Kết quả thực tế', 'content' => '', 'template' => 'results' ),
	'cong-tac-vien'        => array( 'title' => 'Chương trình cộng tác viên', 'content' => '', 'template' => 'affiliate' ),
	'tai-khoan'            => array( 'title' => 'Tài khoản', 'content' => '', 'template' => 'account' ),
	'tra-cuu-don-hang'     => array( 'title' => 'Tra cứu đơn hàng', 'content' => '', 'template' => 'track' ),
	'lien-he'              => array( 'title' => 'Liên hệ', 'content' => '', 'template' => 'contact' ),
	'quan-tri'             => array( 'title' => 'Quản trị đơn hàng', 'content' => '', 'template' => 'admin-orders' ),

	'liem-chinh-hoc-thuat' => array(
		'title'   => 'Chính sách liêm chính học thuật',
		'content' => <<<'HTML'
<p>{company} cung cấp dịch vụ <strong>kiểm tra</strong> tỷ lệ trùng lặp, tỷ lệ AI và <strong>hỗ trợ biên tập</strong> ngôn ngữ, trích dẫn cho tài liệu học thuật. Chúng tôi tôn trọng các quy định về liêm chính học thuật của cơ sở giáo dục, tổ chức khoa học và đơn vị xuất bản. Khi đặt dịch vụ, bạn đồng ý với các nguyên tắc dưới đây.</p>
<h2>1. Chúng tôi làm gì</h2>
<ul>
	<li>Cung cấp báo cáo kiểm tra từ phần mềm bạn chọn để bạn tự đánh giá bài trước khi nộp.</li>
	<li>Biên tập ngôn ngữ: diễn đạt lại câu chữ, sửa cấu trúc câu, chuẩn hóa thuật ngữ cho bài <strong>do chính bạn nghiên cứu và viết</strong>.</li>
	<li>Rà soát và bổ sung trích dẫn đúng chuẩn (APA, IEEE, Harvard…) cho những ý đã lấy từ tài liệu khác; chỉ dẫn nguồn có thật, do bạn sử dụng.</li>
	<li>Chỉ ra các đoạn bị phần mềm đánh dấu (trùng lặp hoặc AI) và góp ý để bạn bổ sung dữ liệu, ví dụ, lập luận của chính mình.</li>
</ul>
<h2>2. Chúng tôi không làm</h2>
<ul>
	<li>Không viết thuê, không viết thay toàn bộ hoặc một phần luận văn, luận án, tiểu luận, bài báo; không làm bài thi, bài kiểm tra thay người học.</li>
	<li>Không tạo, sửa hoặc làm giả số liệu, kết quả nghiên cứu, khảo sát.</li>
	<li>Không bịa đặt tài liệu tham khảo hay trích dẫn nguồn không có thật.</li>
	<li>Không dùng mẹo kỹ thuật để đánh lừa phần mềm: thay ký tự, chèn chữ ẩn hoặc chữ trắng, chuyển văn bản thành ảnh, xoay từ tự động…</li>
	<li>Không viết lại nội dung do AI tạo ra nhằm che giấu nguồn gốc, và không cung cấp công cụ “làm người hóa” văn bản AI (humanizer).</li>
	<li>Không cam kết bài sẽ được chấp nhận, được bảo vệ thành công hay được đăng.</li>
</ul>
<h2>3. Trách nhiệm của người đặt dịch vụ</h2>
<ul>
	<li>Bạn là tác giả và chịu trách nhiệm toàn bộ về nội dung tài liệu.</li>
	<li>Bạn tìm hiểu và tuân thủ quy định của trường, khoa hoặc tạp chí về hỗ trợ biên tập và sử dụng công cụ AI; khai báo trung thực khi được yêu cầu.</li>
	<li>Bạn đọc và rà soát toàn bộ bản biên tập trước khi nộp; có thể yêu cầu bản Track Changes để thấy rõ mọi chỉnh sửa.</li>
	<li>Bạn chỉ gửi tài liệu mà mình có quyền sử dụng; không gửi tài liệu của người khác khi chưa được đồng ý.</li>
</ul>
<h2>4. Về kết quả kiểm tra và mức mục tiêu</h2>
<ul>
	<li>Tỷ lệ trùng lặp và tỷ lệ AI là kết quả của phần mềm tại thời điểm kiểm tra, có thể thay đổi khi cơ sở dữ liệu được cập nhật hoặc dùng thiết lập khác.</li>
	<li>Tỷ lệ AI là ước lượng xác suất, có thể sai, và không phải bằng chứng để kết luận vi phạm.</li>
	<li>Mức mục tiêu khi đặt biên tập là <strong>mức tham khảo</strong>; đánh giá cuối cùng thuộc về cơ sở đào tạo, hội đồng hoặc tạp chí.</li>
</ul>
<h2>5. Từ chối và dừng dịch vụ</h2>
<p>Chúng tôi có quyền từ chối hoặc dừng xử lý (và hoàn phí phần chưa thực hiện) khi yêu cầu vi phạm các nguyên tắc trên, ví dụ yêu cầu viết thay, làm giả số liệu, hoặc tài liệu có dấu hiệu được tạo phần lớn bằng AI mà người đặt yêu cầu che giấu.</p>
<p>Câu hỏi về chính sách này, vui lòng liên hệ {email} hoặc hotline {hotline}.</p>
HTML
		,
	),

	'chinh-sach-bao-mat'   => array(
		'title'   => 'Chính sách bảo mật và xử lý dữ liệu cá nhân',
		'content' => <<<'HTML'
<p>Chính sách này mô tả cách {company} (“chúng tôi”) xử lý dữ liệu cá nhân và tài liệu của khách hàng, phù hợp với Nghị định 13/2023/NĐ-CP về bảo vệ dữ liệu cá nhân, Luật Bảo vệ dữ liệu cá nhân và các quy định liên quan. Thông tin bên kiểm soát và xử lý dữ liệu:</p>
[vs_thong_tin_dn]
<h2>1. Dữ liệu chúng tôi thu thập</h2>
<ul>
	<li><strong>Dữ liệu cá nhân cơ bản:</strong> họ tên, số điện thoại/Zalo, email; mật khẩu (được mã hóa một chiều) nếu bạn tạo tài khoản.</li>
	<li><strong>Tài liệu:</strong> file bạn tải lên để kiểm tra hoặc biên tập, báo cáo kết quả.</li>
	<li><strong>Dữ liệu giao dịch:</strong> mã đơn, dịch vụ, số tiền, trạng thái thanh toán, nội dung chuyển khoản.</li>
	<li><strong>Dữ liệu kỹ thuật:</strong> cookie đăng nhập, cookie giới thiệu (mã cộng tác viên), địa chỉ IP dùng để chống spam.</li>
	<li><strong>Cộng tác viên:</strong> thêm thông tin tài khoản ngân hàng và thông tin cần thiết để khấu trừ, kê khai thuế thu nhập cá nhân.</li>
</ul>
<p>Chúng tôi không yêu cầu dữ liệu cá nhân nhạy cảm. Vui lòng không đưa thông tin nhạy cảm không cần thiết (số CCCD, sức khỏe…) vào tài liệu hoặc ghi chú.</p>
<h2>2. Mục đích xử lý</h2>
<ul>
	<li>Thực hiện đơn hàng: kiểm tra, biên tập, trả kết quả, thông báo trạng thái.</li>
	<li>Xác nhận thanh toán, xuất hóa đơn, hoàn tiền, đối soát và nghĩa vụ kế toán, thuế.</li>
	<li>Hỗ trợ khách hàng, giải quyết khiếu nại.</li>
	<li>Bảo mật hệ thống, chống gian lận và spam.</li>
</ul>
<p>Chúng tôi chỉ gửi tin quảng cáo khi bạn đồng ý riêng và bạn có thể từ chối bất kỳ lúc nào.</p>
<h2>3. Cơ sở xử lý</h2>
<p>Dữ liệu được xử lý trên cơ sở <strong>sự đồng ý</strong> của bạn (đánh dấu khi đặt hàng hoặc đăng ký) và để <strong>thực hiện hợp đồng dịch vụ</strong> bạn yêu cầu, cũng như nghĩa vụ theo quy định pháp luật (kế toán, thuế).</p>
<h2>4. Chia sẻ dữ liệu</h2>
<ul>
	<li>Tài liệu được gửi đến phần mềm kiểm tra bạn chọn (ví dụ Turnitin, Originality.ai, Copyleaks…) chỉ để tạo báo cáo. Các nhà cung cấp này có thể đặt máy chủ ở nước ngoài và xử lý theo chính sách của họ; với Turnitin/iThenticate, chúng tôi dùng chế độ không lưu bài.</li>
	<li>Nhân sự biên tập được giao đơn, có cam kết bảo mật.</li>
	<li>Ngân hàng, đơn vị hóa đơn điện tử, nhà cung cấp hosting, email, thông báo (Telegram) ở mức tối thiểu cần thiết.</li>
	<li>Cơ quan nhà nước có thẩm quyền khi có yêu cầu hợp pháp.</li>
</ul>
<p>Chúng tôi <strong>không</strong> bán, cho thuê dữ liệu cá nhân hay tài liệu của bạn.</p>
<h2>5. Thời gian lưu trữ</h2>
<ul>
	<li>File tài liệu và báo cáo: tự động xóa sau 30 ngày kể từ ngày đặt hàng, hoặc sớm hơn khi bạn yêu cầu.</li>
	<li>Thông tin đơn hàng và giao dịch: lưu theo thời hạn của pháp luật kế toán, thuế.</li>
	<li>Tài khoản: đến khi bạn yêu cầu xóa.</li>
</ul>
<h2>6. Biện pháp bảo vệ</h2>
<ul>
	<li>Kết nối mã hóa HTTPS; tài liệu lưu trong thư mục chặn truy cập công khai, tải qua liên kết có chữ ký.</li>
	<li>Chỉ nhân sự trực tiếp xử lý đơn được truy cập tài liệu.</li>
	<li>Khi phát hiện sự cố lộ lọt dữ liệu, chúng tôi thông báo cho cơ quan chức năng và người bị ảnh hưởng theo quy định.</li>
</ul>
<h2>7. Quyền của bạn</h2>
<p>Bạn có quyền được biết, đồng ý hoặc rút lại sự đồng ý, truy cập, chỉnh sửa, yêu cầu xóa, hạn chế xử lý, phản đối xử lý, yêu cầu cung cấp dữ liệu, khiếu nại và yêu cầu bồi thường theo quy định. Việc rút lại đồng ý không ảnh hưởng tới phần xử lý đã thực hiện trước đó; nếu rút lại khi đơn đang xử lý, chúng tôi có thể không tiếp tục thực hiện được đơn.</p>
<p>Gửi yêu cầu qua {email} hoặc hotline {hotline}, kèm mã đơn hoặc số điện thoại đã dùng. Chúng tôi phản hồi trong thời hạn luật định (thường trong vòng 72 giờ làm việc).</p>
<h2>8. Thay đổi chính sách</h2>
<p>Chính sách có thể được cập nhật khi pháp luật hoặc dịch vụ thay đổi. Phiên bản mới nhất luôn được đăng tại trang này.</p>
HTML
		,
	),

	'dieu-khoan-dich-vu'   => array(
		'title'   => 'Điều khoản dịch vụ',
		'content' => <<<'HTML'
<p>Khi đặt hàng tại website, bạn đồng ý với các điều khoản dưới đây. Thông tin đơn vị cung cấp dịch vụ:</p>
[vs_thong_tin_dn]
<h2>1. Phạm vi dịch vụ</h2>
<ul>
	<li><strong>Kiểm tra:</strong> cung cấp báo cáo tỷ lệ trùng lặp và/hoặc tỷ lệ AI từ phần mềm khách hàng chọn.</li>
	<li><strong>Hỗ trợ biên tập (giảm tỷ lệ):</strong> biên tập ngôn ngữ, diễn đạt lại và bổ sung trích dẫn cho tài liệu do khách hàng là tác giả, hướng tới mức tỷ lệ tham khảo khách hàng chọn, giữ nguyên ý, số liệu và kết quả nghiên cứu.</li>
	<li>Dịch vụ tuân theo <a href="{home}liem-chinh-hoc-thuat/">Chính sách liêm chính học thuật</a>. Chúng tôi không viết thuê, không làm giả số liệu hay nguồn và không hỗ trợ che giấu nội dung do AI tạo.</li>
	<li>Turnitin, iThenticate, Copyleaks, Originality.ai, GPTZero, Winston AI, Grammarly, DoIT, QuillBot, Undetectable.AI là nhãn hiệu của các chủ sở hữu tương ứng. Chúng tôi là đơn vị dịch vụ độc lập, không phải đại lý hay đại diện của các hãng.</li>
</ul>
<h2>2. Trách nhiệm của khách hàng</h2>
<ul>
	<li>Cung cấp đúng thông tin liên hệ và tài liệu mình có quyền sử dụng.</li>
	<li>Là tác giả của tài liệu đặt biên tập, rà soát bản biên tập trước khi nộp và tuân thủ quy định học thuật của cơ sở đào tạo hoặc đơn vị xuất bản, kể cả việc khai báo hỗ trợ biên tập và sử dụng AI nếu được yêu cầu.</li>
	<li>Không sử dụng dịch vụ cho mục đích gian lận thi cử, làm giả văn bằng hoặc vi phạm pháp luật.</li>
</ul>
<h2>3. Kết quả</h2>
<ul>
	<li>Kết quả kiểm tra phản ánh dữ liệu của phần mềm tại thời điểm kiểm tra và có thể dao động theo cơ sở dữ liệu, thiết lập bộ lọc.</li>
	<li>Tỷ lệ AI là ước lượng xác suất, không phải bằng chứng kết luận vi phạm.</li>
	<li>Mức mục tiêu của dịch vụ biên tập là mức tham khảo đo trên cùng phần mềm và thiết lập ghi trong đơn; chúng tôi không bảo đảm bài được chấp nhận, bảo vệ thành công hay được đăng.</li>
</ul>
<h2>4. Giá, thanh toán và hóa đơn</h2>
<ul>
	<li>Giá hiển thị đầy đủ trước khi thanh toán, đã gồm các khoản phí (nếu có), không phí ẩn.</li>
	<li>Đơn được xử lý sau khi nhận thanh toán đúng số tiền với nội dung chuyển khoản là mã đơn.</li>
	<li>Khách hàng cần hóa đơn vui lòng ghi thông tin xuất hóa đơn trong phần ghi chú hoặc liên hệ hỗ trợ.</li>
</ul>
<h2>5. Thời gian trả kết quả</h2>
<p>Theo thời hạn khách hàng chọn khi đặt hàng, tính từ lúc xác nhận thanh toán. Trường hợp chậm trễ do lỗi của phần mềm bên thứ ba, chúng tôi sẽ thông báo và ưu tiên xử lý ngay khi có thể.</p>
<h2>6. Hoàn tiền và khiếu nại</h2>
<p>Xem <a href="{home}chinh-sach-hoan-tien/">Chính sách hoàn tiền</a>. Khiếu nại gửi qua {email} hoặc hotline {hotline}; chúng tôi phản hồi trong vòng 3 ngày làm việc. Tranh chấp được giải quyết trên cơ sở thương lượng; nếu không thành, theo quy định pháp luật Việt Nam.</p>
<h2>7. Chương trình cộng tác viên</h2>
<p>Hoa hồng cộng tác viên là thu nhập chịu thuế thu nhập cá nhân; chúng tôi khấu trừ, kê khai theo quy định. Chương trình chỉ có một cấp, không thu phí tham gia và không trả hoa hồng cho việc tuyển thêm người.</p>
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
	<li>Không hoàn tiền khi báo cáo đã được trả đúng yêu cầu, kể cả khi tỷ lệ cao hơn mong đợi.</li>
</ul>
<h2>2. Dịch vụ hỗ trợ biên tập (giảm tỷ lệ)</h2>
<ul>
	<li>Mức mục tiêu là mức tham khảo. Nếu bản bàn giao, khi kiểm tra trên cùng phần mềm và cùng thiết lập bộ lọc ghi trong đơn, chưa đạt mức đã chọn, chúng tôi biên tập bổ sung miễn phí.</li>
	<li>Nếu sau khi biên tập bổ sung vẫn chưa đạt trong thời hạn đã thỏa thuận, bạn được hoàn phí dịch vụ của đơn đó.</li>
	<li>Hoàn phí phần chưa thực hiện nếu chúng tôi từ chối hoặc dừng đơn theo Chính sách liêm chính học thuật.</li>
	<li>Không áp dụng khi khách hàng tự chỉnh sửa thêm nội dung sau khi nhận file, kiểm tra bằng phần mềm/thiết lập khác, hoặc kiểm tra bằng tài khoản có lưu bài.</li>
</ul>
<h2>3. Cách yêu cầu hoàn tiền</h2>
<p>Liên hệ {email} hoặc hotline {hotline} kèm mã đơn hàng. Tiền được hoàn qua chuyển khoản vào tài khoản đã thanh toán trong vòng 1-3 ngày làm việc kể từ khi xác nhận.</p>
HTML
		,
	),
);
