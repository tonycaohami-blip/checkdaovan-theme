<?php
/**
 * Bài viết 31-34: kỹ năng viết học thuật & phát hiện AI (>1000 từ/bài, chuẩn SEO, có ảnh).
 * {home} = trang chủ, {theme} = thư mục theme, {company} = tên thương hiệu. Giá dùng shortcode.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(

	/* ---------------------------------------------------------- 31 */
	array(
		'slug'    => 'cach-viet-tong-quan-tai-lieu-khong-dao-van',
		'cat'     => 'huong-dan',
		'cover'   => 'cach-viet-tong-quan-tai-lieu-khong-dao-van-check-dao-van.jpg',
		'tags'    => array( 'tổng quan tài liệu', 'literature review', 'viết luận văn', 'tránh đạo văn' ),
		'title'   => 'Cách viết tổng quan tài liệu (literature review) không bị đạo văn: Hướng dẫn 5 bước',
		'excerpt' => 'Phần tổng quan tài liệu thường chiếm phần lớn tỷ lệ trùng lặp của luận văn. Hướng dẫn 5 bước viết tổng quan theo hướng tổng hợp, phản biện, có ví dụ cụ thể và mẹo giữ tỷ lệ đạo văn ở mức an toàn.',
		'content' => <<<'HTML'
<div class="tldr"><b>Tóm tắt nhanh</b><ul>
	<li>Tổng quan tài liệu là nơi tỷ lệ trùng lặp cao nhất vì người viết hay tóm tắt lần lượt từng nghiên cứu.</li>
	<li>Hãy nhóm tài liệu theo chủ đề và viết theo hướng so sánh, phản biện, chỉ ra khoảng trống nghiên cứu.</li>
	<li>Ghi chú bằng lời của bạn ngay khi đọc, luôn kèm nguồn, để không “dính” câu chữ gốc.</li>
	<li>Kiểm tra trùng lặp trước khi nộp, ưu tiên xử lý các nguồn chiếm tỷ lệ lớn nhất.</li>
</ul></div>

<p>Nếu bạn đã từng nhận một báo cáo Turnitin với tỷ lệ 35-40% và phát hiện phần lớn màu sắc nằm ở chương 1 hoặc chương 2, bạn không đơn độc. <strong>Tổng quan tài liệu</strong> (literature review) là phần dễ bị trùng lặp nhất của khóa luận, luận văn và bài báo khoa học. Lý do rất đơn giản: đây là phần bạn phải trình bày lại kết quả của hàng chục nghiên cứu khác. Nếu cách viết chỉ là đọc từng bài rồi tóm tắt lại, câu chữ sẽ rất gần với phần tóm tắt (abstract) đã công bố của các bài đó - và phần mềm sẽ nhận ra ngay.</p>
<p>Tin tốt là có một cách viết tổng quan vừa giảm trùng lặp, vừa được giảng viên đánh giá cao hơn: <strong>viết theo hướng tổng hợp và phản biện</strong>. Bài viết này hướng dẫn chi tiết 5 bước, kèm ví dụ và những lỗi thường gặp.</p>

<h2>Tổng quan tài liệu dùng để làm gì?</h2>
<p>Trước khi bàn cách viết, cần hiểu đúng mục đích. Phần tổng quan không phải là “danh sách những gì người khác đã làm”. Nó có ba nhiệm vụ chính:</p>
<ul>
	<li><strong>Cho thấy bạn nắm được lĩnh vực:</strong> các lý thuyết nền tảng, các hướng nghiên cứu chính và các kết quả quan trọng.</li>
	<li><strong>Chỉ ra khoảng trống nghiên cứu:</strong> điều gì chưa được nghiên cứu, nghiên cứu chưa đủ, hoặc có kết quả mâu thuẫn.</li>
	<li><strong>Làm cơ sở cho nghiên cứu của bạn:</strong> lý giải vì sao bạn chọn mô hình, biến số, phương pháp như vậy.</li>
</ul>
<p>Khi hiểu tổng quan là một <em>lập luận</em> chứ không phải bản tóm tắt, bạn sẽ tự nhiên viết bằng giọng của mình nhiều hơn - và tỷ lệ trùng lặp giảm theo.</p>

<figure><img src="{theme}assets/img/blog/cach-viet-tong-quan-tai-lieu-khong-dao-van-steps.svg" alt="5 bước viết tổng quan tài liệu không bị đạo văn" width="1200" height="675" loading="lazy"><figcaption>5 bước viết tổng quan tài liệu theo hướng tổng hợp.</figcaption></figure>

<h2>Bước 1: Tìm và chọn lọc tài liệu</h2>
<p>Bắt đầu bằng việc xác định phạm vi: chủ đề, khoảng thời gian (ví dụ 10 năm gần nhất), loại tài liệu (bài báo, sách chuyên khảo, luận án). Tìm trên Google Scholar, cơ sở dữ liệu của thư viện trường, các tạp chí chuyên ngành trong và ngoài nước. Ưu tiên nguồn có uy tín, có phản biện và có thể trích dẫn đầy đủ thông tin.</p>
<p>Đừng cố đọc mọi thứ. Một tổng quan tốt cho luận văn thạc sĩ thường dựa trên 30-60 tài liệu được chọn lọc kỹ, thay vì 150 tài liệu chỉ lướt qua. Lưu tất cả vào phần mềm quản lý tài liệu như Zotero hoặc Mendeley ngay từ đầu.</p>

<h2>Bước 2: Ghi chú bằng lời của bạn - luôn kèm nguồn</h2>
<p>Đây là bước quyết định tỷ lệ trùng lặp sau này. Khi đọc một bài, đừng sao chép đoạn văn vào file nháp. Thay vào đó, hãy ghi lại theo mẫu:</p>
<ul class="checklist">
	<li><strong>Nguồn:</strong> tác giả, năm, trang.</li>
	<li><strong>Câu hỏi nghiên cứu / mục tiêu</strong> của bài, viết bằng một câu của bạn.</li>
	<li><strong>Phương pháp và dữ liệu:</strong> mẫu bao nhiêu, ở đâu, khi nào.</li>
	<li><strong>Kết quả chính</strong> - tóm lại bằng từ ngữ của bạn.</li>
	<li><strong>Nhận xét của bạn:</strong> điểm mạnh, hạn chế, liên hệ với đề tài.</li>
</ul>
<p>Nếu có một câu thật sự cần giữ nguyên văn (ví dụ một định nghĩa kinh điển), hãy đặt trong ngoặc kép và ghi số trang ngay trong ghi chú. Sau này bạn sẽ không phải tự hỏi “câu này là của mình hay của tác giả?”.</p>

<div class="callout callout--tip"><b>Mẹo</b>Đọc xong một bài, gấp tài liệu lại rồi mới ghi chú. Cách này buộc bạn diễn đạt bằng hiểu biết của mình thay vì “chép tay” câu chữ gốc.</div>

<h2>Bước 3: Nhóm tài liệu theo chủ đề, không theo tác giả</h2>
<p>Lỗi phổ biến nhất của tổng quan là cấu trúc kiểu “Nghiên cứu của A cho thấy… Nghiên cứu của B chỉ ra… Nghiên cứu của C kết luận…”. Cách viết này vừa nhàm chán, vừa dễ trùng lặp. Hãy sắp xếp ghi chú vào các nhóm chủ đề, ví dụ với đề tài về động lực làm việc:</p>
<table>
	<thead><tr><th>Nhóm chủ đề</th><th>Câu hỏi bạn trả lời</th></tr></thead>
	<tbody>
		<tr><td>Các lý thuyết về động lực</td><td>Có những lý thuyết nào, khác nhau ra sao?</td></tr>
		<tr><td>Yếu tố ảnh hưởng đến động lực</td><td>Những yếu tố nào được nhiều nghiên cứu xác nhận?</td></tr>
		<tr><td>Động lực và hiệu suất</td><td>Mối quan hệ có nhất quán giữa các bối cảnh không?</td></tr>
		<tr><td>Nghiên cứu tại Việt Nam</td><td>Kết quả trong nước có khác quốc tế không?</td></tr>
	</tbody>
</table>
<p>Mỗi nhóm chủ đề sẽ trở thành một mục nhỏ trong chương tổng quan.</p>

<h2>Bước 4: Viết tổng hợp - so sánh - phản biện</h2>
<p>Trong mỗi mục, hãy viết như một người đang dẫn dắt người đọc qua một cuộc tranh luận. Một đoạn tổng quan tốt thường có cấu trúc: <em>nhận định chung → bằng chứng từ nhiều nguồn → điểm khác biệt hoặc mâu thuẫn → ý nghĩa với nghiên cứu của bạn</em>.</p>
<div class="example">
	<div class="ex-bad"><b>Tóm tắt từng bài</b>“Nguyễn (2020) nghiên cứu 300 nhân viên ngân hàng và thấy lương thưởng ảnh hưởng đến động lực. Trần (2021) nghiên cứu 250 nhân viên bán lẻ và thấy môi trường làm việc ảnh hưởng đến động lực. Lê (2022) nghiên cứu…”</div>
	<div class="ex-good"><b>Tổng hợp, phản biện</b>“Phần lớn nghiên cứu trong nước đều xác nhận vai trò của lương thưởng và môi trường làm việc (Nguyễn, 2020; Trần, 2021). Tuy nhiên, các nghiên cứu này chủ yếu khảo sát khu vực tài chính và bán lẻ ở đô thị lớn; tác động của cơ hội thăng tiến trong doanh nghiệp nhỏ ở địa phương vẫn chưa được làm rõ - đây là khoảng trống mà luận văn hướng tới.”</div>
</div>
<p>Đoạn bên phải dài tương đương, nhưng gần như mọi câu là của bạn. Nó cũng cho hội đồng thấy bạn thực sự hiểu tài liệu.</p>

<h3>Những cụm từ giúp viết tổng hợp tự nhiên</h3>
<ul>
	<li><em>Nhiều nghiên cứu thống nhất rằng…, tuy nhiên…</em></li>
	<li><em>Kết quả này trái ngược với…, có thể do khác biệt về…</em></li>
	<li><em>Điểm chung của các nghiên cứu trên là…, nhưng hạn chế ở…</em></li>
	<li><em>Trong bối cảnh Việt Nam, bằng chứng còn hạn chế…</em></li>
</ul>

<h2>Bước 5: Kiểm tra trùng lặp và hoàn thiện</h2>
<p>Khi đã có bản thảo, hãy kiểm tra bằng đúng phần mềm mà trường bạn sử dụng, ở chế độ <a href="{home}no-repository-la-gi/">không lưu bài</a>. Đọc báo cáo theo thứ tự nguồn lớn đến nhỏ: thường chỉ 3-5 nguồn đầu tiên đã chiếm hơn nửa tỷ lệ. Với mỗi đoạn trùng, tự hỏi:</p>
<ol>
	<li>Đoạn này đã có trích dẫn chưa? Nếu chưa - bổ sung ngay.</li>
	<li>Có cần giữ nguyên văn không? Nếu không - diễn đạt lại theo <a href="{home}paraphrase-dung-chuan-hoc-thuat/">kỹ thuật paraphrase</a>.</li>
	<li>Đoạn này có thể gộp với nguồn khác để thành câu tổng hợp không?</li>
</ol>

<div class="callout callout--warn"><b>Đừng “lách” phần mềm</b>Thay ký tự, chèn chữ trắng hay dùng công cụ xoay từ tự động có thể bị phát hiện và bị xử lý nặng hơn cả đạo văn. Cách bền vững duy nhất là viết lại bằng hiểu biết của bạn.</div>

<h2>Những lỗi thường gặp khi viết tổng quan</h2>
<ul>
	<li><strong>Quá dài, thiếu trọng tâm:</strong> đưa vào mọi tài liệu đã đọc, kể cả những tài liệu không liên quan trực tiếp.</li>
	<li><strong>Chỉ trích dẫn nguồn thứ cấp:</strong> trích lại nhận định của tác giả A thông qua bài của tác giả B mà không đọc bản gốc.</li>
	<li><strong>Thiếu tài liệu mới:</strong> tổng quan chủ yếu là tài liệu trên 10 năm khiến hội đồng nghi ngờ tính cập nhật.</li>
	<li><strong>Không kết luận khoảng trống:</strong> đọc hết chương mà người đọc không biết đề tài của bạn đóng góp gì.</li>
	<li><strong>Dùng AI viết thay:</strong> văn bản khuôn mẫu, chung chung và có nguy cơ bị gắn cờ AI. Xem <a href="{home}dung-chatgpt-viet-luan-van/">ranh giới khi dùng ChatGPT</a>.</li>
</ul>

<h2>Câu hỏi thường gặp</h2>
<h3>Tổng quan tài liệu nên dài bao nhiêu?</h3>
<p>Thường chiếm khoảng 20-30% dung lượng luận văn, tùy quy định của trường và ngành. Quan trọng là đủ để chỉ ra khoảng trống nghiên cứu.</p>
<h3>Có được trích dẫn nguyên văn nhiều không?</h3>
<p>Nên hạn chế. Trích nguyên văn chỉ dùng cho định nghĩa hoặc nhận định quan trọng; phần lớn nội dung nên được tổng hợp bằng lời của bạn.</p>
<h3>Tỷ lệ trùng lặp ở chương tổng quan bao nhiêu là chấp nhận được?</h3>
<p>Không có ngưỡng riêng cho từng chương ở đa số trường, nhưng nên giữ tổng tỷ lệ dưới ngưỡng quy định và không có nguồn đơn lẻ chiếm tỷ lệ lớn. Tham khảo <a href="{home}ty-le-dao-van-bao-nhieu-la-dat/">tỷ lệ đạo văn bao nhiêu là đạt</a>.</p>

<blockquote>Viết xong chương tổng quan? <a href="{home}dat-hang/?tab=check&amp;tool=turnitin&amp;mode=combo">Kiểm tra Turnitin không lưu bài</a> để biết chính xác đoạn nào cần sửa - kèm tỷ lệ AI.</blockquote>
HTML
		,
	),

	/* ---------------------------------------------------------- 32 */
	array(
		'slug'    => 'huong-dan-trich-dan-ieee-harvard',
		'cat'     => 'huong-dan',
		'cover'   => 'huong-dan-trich-dan-ieee-harvard-check-dao-van.jpg',
		'tags'    => array( 'trích dẫn IEEE', 'trích dẫn Harvard', 'tài liệu tham khảo', 'cách trích dẫn' ),
		'title'   => 'Hướng dẫn trích dẫn IEEE và Harvard kèm ví dụ cho sinh viên kỹ thuật, kinh tế',
		'excerpt' => 'IEEE dùng số thứ tự, Harvard dùng tác giả - năm. Hướng dẫn chi tiết cách trích dẫn trong bài và lập danh mục tài liệu tham khảo theo hai chuẩn phổ biến, kèm ví dụ sách, bài báo, website và lỗi thường gặp.',
		'content' => <<<'HTML'
<div class="tldr"><b>Tóm tắt nhanh</b><ul>
	<li><strong>IEEE</strong> đánh số [1], [2] theo thứ tự xuất hiện, phổ biến trong kỹ thuật, công nghệ thông tin.</li>
	<li><strong>Harvard</strong> ghi (Tác giả, năm) ngay trong câu, phổ biến trong kinh tế, quản lý, khoa học xã hội.</li>
	<li>Chỉ dùng một chuẩn trong cả bài và tuân theo hướng dẫn của khoa hoặc tạp chí.</li>
	<li>Trích dẫn đầy đủ giúp phần trùng lặp được coi là sử dụng tài liệu hợp lệ.</li>
</ul></div>

<p>Sau APA, hai chuẩn trích dẫn sinh viên Việt Nam gặp nhiều nhất là <strong>IEEE</strong> và <strong>Harvard</strong>. Nếu bạn học ngành điện - điện tử, công nghệ thông tin, cơ khí, khả năng cao khoa sẽ yêu cầu IEEE. Nếu bạn học kinh tế, quản trị kinh doanh, tài chính, nhiều trường dùng Harvard. Trích dẫn sai chuẩn không chỉ bị trừ điểm hình thức mà còn có thể khiến các đoạn đã có nguồn vẫn bị coi là thiếu trích dẫn. Bài viết này giúp bạn nắm chắc cả hai chuẩn trong 10 phút.</p>

<figure><img src="{theme}assets/img/blog/huong-dan-trich-dan-ieee-harvard-cards.svg" alt="So sánh chuẩn trích dẫn IEEE và Harvard" width="1200" height="675" loading="lazy"><figcaption>IEEE đánh số theo thứ tự; Harvard ghi tác giả - năm ngay trong câu.</figcaption></figure>

<h2>Phần 1: Chuẩn trích dẫn IEEE</h2>
<h3>Trích dẫn trong bài</h3>
<p>IEEE (Institute of Electrical and Electronics Engineers) dùng <strong>số trong ngoặc vuông</strong>. Tài liệu được đánh số theo thứ tự lần đầu xuất hiện trong bài: tài liệu đầu tiên bạn trích là [1], tài liệu thứ hai là [2]… Nếu trích lại tài liệu [1] ở chương sau, bạn vẫn dùng [1].</p>
<ul>
	<li>Một nguồn: “Thuật toán này giảm 30% thời gian xử lý [3].”</li>
	<li>Nhiều nguồn: “…đã được nhiều nghiên cứu xác nhận [2], [5], [7].” hoặc dải liên tiếp [4]-[6].</li>
	<li>Nhắc tên tác giả: “Nguyễn và cộng sự [4] đề xuất…” - số vẫn đặt ngay sau tên.</li>
</ul>
<h3>Danh mục tài liệu tham khảo</h3>
<p>Danh mục xếp theo <strong>số thứ tự</strong>, không theo bảng chữ cái. Tên tác giả viết tắt tên riêng trước họ (với tài liệu tiếng Anh).</p>
<table>
	<thead><tr><th>Loại</th><th>Mẫu IEEE</th></tr></thead>
	<tbody>
		<tr><td>Bài báo</td><td>[1] A. Nguyen and B. Tran, “Tên bài báo,” <em>Tên tạp chí</em>, vol. 12, no. 3, pp. 45-56, 2022.</td></tr>
		<tr><td>Sách</td><td>[2] C. Le, <em>Tên sách</em>, 2nd ed. Hà Nội: NXB Khoa học và Kỹ thuật, 2020.</td></tr>
		<tr><td>Kỷ yếu hội thảo</td><td>[3] D. Pham, “Tên bài,” in <em>Proc. Tên hội thảo</em>, City, 2021, pp. 10-15.</td></tr>
		<tr><td>Website</td><td>[4] Tên tổ chức, “Tiêu đề trang,” Tên website. [Online]. Available: URL (truy cập: ngày/tháng/năm).</td></tr>
	</tbody>
</table>

<div class="callout callout--tip"><b>Mẹo IEEE</b>Dùng tính năng trích dẫn của Word hoặc Zotero với style “IEEE” - phần mềm tự đánh số lại khi bạn thêm, bớt tài liệu giữa chừng, tránh lệch số.</div>

<h2>Phần 2: Chuẩn trích dẫn Harvard</h2>
<h3>Trích dẫn trong bài</h3>
<p>Harvard thuộc nhóm “tác giả - năm”, gần giống APA nhưng có một số khác biệt nhỏ tùy phiên bản trường áp dụng. Hai dạng phổ biến:</p>
<ul>
	<li><strong>Trong ngoặc:</strong> “Động lực làm việc ảnh hưởng đến năng suất (Nguyễn, 2022).”</li>
	<li><strong>Tường thuật:</strong> “Nguyễn (2022) cho rằng động lực làm việc ảnh hưởng đến năng suất.”</li>
	<li><strong>Trích nguyên văn:</strong> thêm số trang: (Nguyễn, 2022, tr. 45) hoặc (Nguyen, 2022, p. 45).</li>
	<li><strong>Từ ba tác giả:</strong> (Trần và cộng sự, 2021) hoặc (Tran et al., 2021).</li>
</ul>
<h3>Danh mục tài liệu tham khảo</h3>
<p>Danh mục xếp theo <strong>bảng chữ cái</strong> của họ tác giả đầu tiên, năm đặt ngay sau tên tác giả.</p>
<table>
	<thead><tr><th>Loại</th><th>Mẫu Harvard</th></tr></thead>
	<tbody>
		<tr><td>Bài báo</td><td>Nguyen, A. and Tran, B. (2022) ‘Tên bài báo’, <em>Tên tạp chí</em>, 12(3), pp. 45-56.</td></tr>
		<tr><td>Sách</td><td>Le, C. (2020) <em>Tên sách</em>. 2nd edn. Hà Nội: NXB Kinh tế Quốc dân.</td></tr>
		<tr><td>Luận văn</td><td>Pham, D. (2021) <em>Tên luận văn</em>. Luận văn thạc sĩ. Tên trường.</td></tr>
		<tr><td>Website</td><td>Tên tổ chức (2023) <em>Tiêu đề trang</em>. Available at: URL (Accessed: ngày tháng năm).</td></tr>
	</tbody>
</table>

<h2>So sánh nhanh IEEE và Harvard</h2>
<table>
	<thead><tr><th></th><th>IEEE</th><th>Harvard</th></tr></thead>
	<tbody>
		<tr><td>Trong bài</td><td>[1], [2]</td><td>(Nguyễn, 2022)</td></tr>
		<tr><td>Thứ tự danh mục</td><td>Theo số xuất hiện</td><td>Theo bảng chữ cái</td></tr>
		<tr><td>Vị trí năm</td><td>Cuối mục tài liệu</td><td>Ngay sau tên tác giả</td></tr>
		<tr><td>Ngành phổ biến</td><td>Kỹ thuật, CNTT</td><td>Kinh tế, quản lý, xã hội</td></tr>
		<tr><td>Ưu điểm</td><td>Gọn, không làm đứt mạch câu</td><td>Người đọc biết ngay tác giả, năm</td></tr>
	</tbody>
</table>

<h2>Tài liệu tiếng Việt trích dẫn thế nào?</h2>
<p>Với tác giả Việt Nam, nhiều trường hướng dẫn giữ nguyên họ tên đầy đủ theo thứ tự tiếng Việt, ví dụ “Nguyễn Văn An (2022)” thay vì viết tắt. Đây là điểm các trường quy định khác nhau, vì vậy hãy đọc kỹ hướng dẫn trình bày luận văn của khoa. Nếu danh mục có cả tài liệu tiếng Việt và tiếng nước ngoài, một số trường yêu cầu tách thành hai phần riêng.</p>

<h2>Lỗi trích dẫn thường gặp</h2>
<ul class="checklist">
	<li>Trộn IEEE và Harvard trong cùng một bài.</li>
	<li>IEEE: số trong bài không khớp số trong danh mục sau khi thêm, bớt tài liệu.</li>
	<li>Harvard: năm trong bài khác năm trong danh mục.</li>
	<li>Thiếu số trang khi trích nguyên văn.</li>
	<li>Có trong danh mục nhưng không được trích trong bài (hoặc ngược lại).</li>
	<li>Trích tài liệu chưa từng đọc - chỉ chép từ danh mục của bài khác.</li>
</ul>

<div class="callout callout--info"><b>Trích dẫn và tỷ lệ trùng lặp</b>Trích dẫn đúng không phải lúc nào cũng làm con số trên Turnitin giảm, nhưng nó biến phần trùng thành sử dụng tài liệu hợp lệ. Nếu trường bật bộ lọc loại trừ trích dẫn và danh mục, tỷ lệ cũng sẽ giảm đáng kể. Xem thêm <a href="{home}cach-doc-bao-cao-turnitin/">cách đọc báo cáo Turnitin</a>.</div>

<h2>Quy trình trích dẫn gọn gàng ngay từ đầu</h2>
<p>Cách tiết kiệm thời gian nhất là thiết lập công cụ quản lý tài liệu trước khi viết. Tạo một thư mục riêng cho luận văn trong Zotero hoặc Mendeley, chọn style IEEE hoặc Harvard theo yêu cầu của khoa, rồi chèn trích dẫn trực tiếp từ plugin trong Word mỗi khi sử dụng tài liệu. Khi hoàn thành, chỉ cần một thao tác để tạo danh mục tài liệu tham khảo đầy đủ, đúng định dạng và đúng thứ tự.</p>

<h2>Câu hỏi thường gặp</h2>
<h3>Khoa không quy định chuẩn trích dẫn thì chọn chuẩn nào?</h3>
<p>Hãy hỏi giảng viên hướng dẫn. Nếu không có yêu cầu, chọn chuẩn phổ biến trong ngành của bạn và dùng nhất quán.</p>
<h3>Có công cụ tạo trích dẫn tự động không?</h3>
<p>Có: Zotero, Mendeley, EndNote và tính năng References trong Word. Bạn vẫn nên kiểm tra lại thông tin đầu vào.</p>
<h3>Harvard và APA khác nhau nhiều không?</h3>
<p>Khá giống về cách trích trong bài; khác chủ yếu ở dấu câu và định dạng danh mục. Xem <a href="{home}huong-dan-trich-dan-apa-7/">hướng dẫn APA 7</a> để so sánh.</p>

<blockquote>Trích dẫn xong rồi? <a href="{home}dat-hang/">Kiểm tra lại bằng Turnitin</a> để chắc không còn đoạn nào sót nguồn.</blockquote>
HTML
		,
	),

	/* ---------------------------------------------------------- 33 */
	array(
		'slug'    => 'viet-phuong-phap-nghien-cuu-tranh-trung-lap',
		'cat'     => 'huong-dan',
		'cover'   => 'viet-phuong-phap-nghien-cuu-tranh-trung-lap-check-dao-van.jpg',
		'tags'    => array( 'phương pháp nghiên cứu', 'viết luận văn', 'tránh trùng lặp', 'chương 3' ),
		'title'   => 'Viết phần phương pháp nghiên cứu luận văn: Đúng chuẩn và tránh trùng lặp',
		'excerpt' => 'Phần phương pháp nghiên cứu thường bị trùng vì mô tả quy trình chuẩn giống nhiều bài khác. Hướng dẫn cấu trúc 5 mục, cách viết gắn với bối cảnh riêng, cách trích dẫn thang đo và mẹo giữ tỷ lệ trùng lặp thấp.',
		'content' => <<<'HTML'
<div class="tldr"><b>Tóm tắt nhanh</b><ul>
	<li>Phần phương pháp gồm 5 mục: thiết kế, mẫu, công cụ, thu thập dữ liệu, phân tích dữ liệu.</li>
	<li>Trùng lặp thường đến từ định nghĩa phương pháp và mô tả kỹ thuật thống kê chép từ giáo trình.</li>
	<li>Hãy viết gắn với nghiên cứu của bạn: con số, địa điểm, thời gian, lý do lựa chọn.</li>
	<li>Thang đo, bảng hỏi kế thừa phải ghi rõ nguồn và cách điều chỉnh.</li>
</ul></div>

<p>Chương phương pháp nghiên cứu (thường là chương 3 của luận văn) có vẻ “dễ viết” vì nhiều nội dung mang tính quy trình. Nhưng chính vì vậy, đây cũng là chương rất dễ bị trùng lặp: định nghĩa nghiên cứu định lượng, mô tả kiểm định Cronbach’s Alpha, phân tích nhân tố khám phá (EFA), hồi quy… đã xuất hiện trong hàng nghìn luận văn trước đó. Nếu bạn chép lại các đoạn mô tả này từ giáo trình hoặc luận văn khóa trước, báo cáo Turnitin sẽ tô màu dày đặc. Bài viết này giúp bạn viết phần phương pháp <strong>đúng chuẩn học thuật</strong> mà vẫn giữ tỷ lệ trùng lặp ở mức an toàn.</p>

<h2>Vì sao phần phương pháp hay bị trùng lặp?</h2>
<ul>
	<li><strong>Định nghĩa giáo trình:</strong> “Nghiên cứu định lượng là…” - chép nguyên văn từ sách mà không đặt trong ngoặc kép.</li>
	<li><strong>Mô tả kỹ thuật thống kê:</strong> tiêu chuẩn Cronbach’s Alpha, hệ số KMO, điều kiện hồi quy - viết giống hệt nhau ở mọi luận văn.</li>
	<li><strong>Thang đo kế thừa:</strong> bảng câu hỏi lấy từ nghiên cứu khác, trùng gần như toàn bộ.</li>
	<li><strong>Mượn khung luận văn khóa trước</strong> cùng trường, cùng đề tài.</li>
</ul>
<p>Điều quan trọng cần nhớ: hội đồng không chấm phần phương pháp dựa trên việc bạn định nghĩa các khái niệm hay đến đâu, mà dựa trên việc bạn <strong>áp dụng</strong> chúng cho nghiên cứu của mình có hợp lý hay không.</p>

<figure><img src="{theme}assets/img/blog/viet-phuong-phap-nghien-cuu-tranh-trung-lap-steps.svg" alt="Cấu trúc 5 mục của phần phương pháp nghiên cứu" width="1200" height="675" loading="lazy"><figcaption>Cấu trúc 5 mục của phần phương pháp nghiên cứu.</figcaption></figure>

<h2>Cấu trúc 5 mục và cách viết từng mục</h2>
<h3>1. Thiết kế nghiên cứu</h3>
<p>Nêu bạn chọn nghiên cứu định tính, định lượng hay hỗn hợp, và <strong>vì sao</strong> thiết kế đó phù hợp với câu hỏi nghiên cứu. Không cần định nghĩa dài dòng các loại nghiên cứu - một câu trích dẫn là đủ, phần còn lại dành cho lập luận của bạn.</p>
<div class="example">
	<div class="ex-bad"><b>Dễ trùng</b>“Nghiên cứu định lượng là phương pháp thu thập dữ liệu dạng số nhằm kiểm định giả thuyết và khái quát hóa kết quả cho tổng thể…” (một đoạn dài chép từ giáo trình)</div>
	<div class="ex-good"><b>Gắn với đề tài</b>“Luận văn sử dụng thiết kế định lượng vì mục tiêu là đo lường mức độ tác động của năm yếu tố đến ý định nghỉ việc của nhân viên ngành bán lẻ tại Cần Thơ - điều cần một mẫu đủ lớn và phép kiểm định thống kê (Creswell, 2014).”</div>
</div>

<h3>2. Mẫu nghiên cứu</h3>
<p>Mô tả cụ thể: tổng thể là ai, cỡ mẫu bao nhiêu, cách tính cỡ mẫu, phương pháp chọn mẫu (thuận tiện, phân tầng…), tiêu chí chọn và loại trừ. Những con số và chi tiết này là của riêng bạn nên gần như không trùng với ai.</p>

<h3>3. Công cụ nghiên cứu (thang đo, bảng hỏi)</h3>
<p>Nếu kế thừa thang đo, hãy ghi rõ nguồn gốc, số biến quan sát, đã dịch và điều chỉnh thế nào, đã khảo sát thử (pilot) với bao nhiêu người. Bảng thang đo nên đặt trong phần phụ lục và ghi chú nguồn - nhiều trường loại trừ phụ lục khi kiểm tra trùng lặp.</p>
<div class="callout callout--info"><b>Trích dẫn thang đo</b>Một bảng thang đo kế thừa có ghi nguồn đầy đủ là sử dụng tài liệu hợp lệ. Hội đồng chỉ lo ngại khi thang đo trùng nguyên văn mà không có nguồn.</div>

<h3>4. Quy trình thu thập dữ liệu</h3>
<p>Mô tả thời gian, địa điểm, hình thức (trực tiếp, trực tuyến), số phiếu phát ra, số phiếu thu về hợp lệ và cách xử lý phiếu không hợp lệ. Đây là phần thể hiện tính minh bạch và thường được hội đồng hỏi kỹ.</p>

<h3>5. Phương pháp phân tích dữ liệu</h3>
<p>Liệt kê các bước phân tích theo đúng trình tự bạn thực hiện và tiêu chí đánh giá. Thay vì viết lại toàn bộ lý thuyết về từng kiểm định, hãy nêu ngắn gọn tiêu chí và trích dẫn nguồn:</p>
<table>
	<thead><tr><th>Bước</th><th>Cách viết gọn, có nguồn</th></tr></thead>
	<tbody>
		<tr><td>Độ tin cậy thang đo</td><td>Cronbach’s Alpha từ 0,6 trở lên được chấp nhận (Hair và cộng sự, 2010).</td></tr>
		<tr><td>Phân tích nhân tố</td><td>Sử dụng EFA với KMO ≥ 0,5 và phép xoay Varimax.</td></tr>
		<tr><td>Hồi quy</td><td>Kiểm tra đa cộng tuyến qua hệ số VIF trước khi kết luận.</td></tr>
	</tbody>
</table>

<h2>5 mẹo giữ tỷ lệ trùng lặp thấp ở chương phương pháp</h2>
<ul class="checklist">
	<li><strong>Thay định nghĩa dài bằng một câu có trích dẫn</strong> rồi chuyển ngay sang áp dụng.</li>
	<li><strong>Đưa số liệu của bạn vào mọi đoạn</strong>: cỡ mẫu, thời gian, địa điểm, tỷ lệ phản hồi.</li>
	<li><strong>Trình bày tiêu chí bằng bảng</strong> do bạn tự thiết kế thay vì đoạn văn chép từ giáo trình.</li>
	<li><strong>Chuyển bảng hỏi, thang đo sang phụ lục</strong> và hỏi trường có loại trừ phụ lục không.</li>
	<li><strong>Giải thích lý do lựa chọn</strong> - phần lập luận này luôn là của riêng bạn.</li>
</ul>

<div class="callout callout--warn"><b>Không mượn quy trình của luận văn khác</b>Mô tả quy trình khảo sát giống hệt luận văn khóa trước (kể cả con số) là dấu hiệu đáng ngờ. Hãy mô tả đúng những gì bạn thực sự đã làm.</div>

<h2>Câu hỏi thường gặp</h2>
<h3>Có cần trích dẫn khi mô tả kiểm định thống kê không?</h3>
<p>Có, với các tiêu chí và ngưỡng đánh giá. Nhưng chỉ cần mô tả ngắn gọn và trích nguồn, không cần trình bày lại lý thuyết.</p>
<h3>Phụ lục có bị tính vào tỷ lệ trùng lặp không?</h3>
<p>Tùy quy định của trường. Nhiều nơi chỉ kiểm tra chính văn; hãy hỏi trước để chuẩn bị file kiểm tra phù hợp.</p>
<h3>Chương phương pháp nên dài bao nhiêu?</h3>
<p>Thường khoảng 10-15% dung lượng luận văn - đủ để người khác có thể lặp lại nghiên cứu của bạn.</p>

<blockquote>Chương phương pháp vẫn trùng nhiều? Dịch vụ <a href="{home}dich-vu-giam-ty-le-dao-van-ai/">giảm tỷ lệ đạo văn + AI</a> cho phép bạn tự chọn mức mục tiêu mong muốn.</blockquote>
HTML
		,
	),

	/* ---------------------------------------------------------- 34 */
	array(
		'slug'    => 'kiem-tra-ty-le-ai-bai-tieng-viet',
		'cat'     => 'ai',
		'cover'   => 'kiem-tra-ty-le-ai-bai-tieng-viet-check-dao-van.jpg',
		'tags'    => array( 'kiểm tra AI tiếng Việt', 'phát hiện AI', 'Turnitin AI', 'Originality.ai' ),
		'title'   => 'Kiểm tra tỷ lệ AI cho bài tiếng Việt: Chọn công cụ nào cho chính xác?',
		'excerpt' => 'Không phải công cụ phát hiện AI nào cũng hỗ trợ tiếng Việt. Turnitin và iThenticate chỉ kiểm tra AI với tiếng Anh, Tây Ban Nha, Nhật. Hướng dẫn chọn công cụ phù hợp cho bài tiếng Việt và cách đọc kết quả.',
		'content' => <<<'HTML'
<div class="tldr"><b>Tóm tắt nhanh</b><ul>
	<li><strong>Turnitin và iThenticate</strong> hiện chỉ kiểm tra tỷ lệ AI với tài liệu <strong>tiếng Anh, tiếng Tây Ban Nha, tiếng Nhật</strong>.</li>
	<li>Bài tiếng Việt nên kiểm tra AI bằng <strong>Originality.ai</strong> hoặc <strong>Copyleaks</strong>, đối chiếu thêm nếu cần.</li>
	<li>Kiểm tra đạo văn (trùng lặp) với tiếng Việt vẫn dùng tốt Turnitin, DoIT, Copyleaks.</li>
	<li>Kết quả AI là ước lượng xác suất - hãy đọc cùng phần đánh dấu từng đoạn.</li>
</ul></div>

<p>“Em gửi bài tiếng Việt kiểm tra AI trên Turnitin mà không thấy kết quả AI?” - đây là câu hỏi chúng tôi nhận được rất thường xuyên. Câu trả lời nằm ở <strong>ngôn ngữ được hỗ trợ</strong>. Các mô hình phát hiện văn bản AI được huấn luyện trên dữ liệu của từng ngôn ngữ, và không phải ngôn ngữ nào cũng được hỗ trợ như nhau. Với bài viết tiếng Việt, chọn sai công cụ có thể khiến bạn không nhận được kết quả AI, hoặc nhận một con số kém tin cậy. Bài viết này giúp bạn chọn đúng công cụ <strong>kiểm tra tỷ lệ AI cho bài tiếng Việt</strong>.</p>

<h2>Vì sao ngôn ngữ quan trọng với phát hiện AI?</h2>
<p>Phát hiện trùng lặp hoạt động bằng cách so khớp chuỗi văn bản với cơ sở dữ liệu - về nguyên tắc, ngôn ngữ nào cũng so khớp được. Phát hiện AI thì khác: công cụ phải “học” đặc điểm thống kê của văn bản do người viết và do AI tạo ra <em>trong từng ngôn ngữ cụ thể</em>. Một mô hình được huấn luyện chủ yếu bằng tiếng Anh sẽ không đánh giá chính xác cách dùng từ, nhịp câu và cấu trúc ngữ pháp của tiếng Việt.</p>
<p>Vì vậy nhiều nhà cung cấp chỉ bật tính năng phát hiện AI cho một số ngôn ngữ nhất định, và nêu rõ điều này trong tài liệu hướng dẫn.</p>

<figure><img src="{theme}assets/img/blog/kiem-tra-ty-le-ai-bai-tieng-viet-cards.svg" alt="Các công cụ kiểm tra AI và ngôn ngữ được hỗ trợ" width="1200" height="675" loading="lazy"><figcaption>Công cụ kiểm tra AI và ngôn ngữ được hỗ trợ (tham khảo, có thể thay đổi theo phiên bản).</figcaption></figure>

<h2>Từng công cụ với bài tiếng Việt</h2>
<h3>Turnitin và iThenticate</h3>
<p>Đây là hai công cụ mạnh nhất về <strong>kiểm tra trùng lặp</strong>, hoạt động tốt với tài liệu tiếng Việt. Tuy nhiên, tính năng <strong>phát hiện AI</strong> của Turnitin và iThenticate hiện chỉ áp dụng cho tài liệu <strong>tiếng Anh, tiếng Tây Ban Nha và tiếng Nhật</strong>. Nếu bài của bạn viết bằng tiếng Việt, báo cáo AI sẽ không được tạo hoặc không có ý nghĩa tham khảo.</p>
<div class="callout callout--warn"><b>Lưu ý khi đặt hàng</b>Với bài tiếng Việt, nếu chọn Turnitin hình thức “Đạo văn + AI”, bạn chỉ nhận được phần đạo văn có giá trị. Hãy chọn “Chỉ đạo văn” trên Turnitin và đặt thêm một đơn kiểm tra AI trên công cụ hỗ trợ tiếng Việt.</div>

<h3>Originality.ai</h3>
<p>Originality.ai có mô hình phát hiện AI dành cho văn bản đa ngôn ngữ, phù hợp để kiểm tra bài tiếng Việt. Tại {company}, đơn Originality.ai được kiểm tra tự động 24/7 và trả về báo cáo PDF tô màu từng câu theo khả năng do AI tạo ra. Xem thêm <a href="{home}kiem-tra-ai-originality-ai/">kiểm tra AI bằng Originality.ai</a>.</p>

<h3>Copyleaks</h3>
<p>Copyleaks hỗ trợ nhiều ngôn ngữ cho cả kiểm tra trùng lặp và phát hiện AI, trong đó có tiếng Việt. Đây là lựa chọn tốt khi bạn muốn có cả hai kết quả trong một lần kiểm tra với bài tiếng Việt.</p>

<h3>GPTZero, Winston AI, Grammarly, QuillBot</h3>
<p>Các công cụ này hoạt động tốt nhất với tiếng Anh. Với bài tiếng Việt, kết quả nên được xem là tham khảo và đối chiếu thêm với Originality.ai hoặc Copyleaks.</p>

<h2>Bảng gợi ý chọn công cụ</h2>
<table>
	<thead><tr><th>Bài của bạn</th><th>Kiểm tra đạo văn</th><th>Kiểm tra AI</th></tr></thead>
	<tbody>
		<tr><td>Tiếng Việt</td><td>Turnitin, DoIT, Copyleaks</td><td>Originality.ai, Copyleaks</td></tr>
		<tr><td>Tiếng Anh</td><td>Turnitin, iThenticate, Grammarly</td><td>Turnitin, GPTZero, Originality.ai</td></tr>
		<tr><td>Song ngữ (Việt + Anh)</td><td>Turnitin hoặc Copyleaks</td><td>Originality.ai, đối chiếu thêm Copyleaks</td></tr>
		<tr><td>Bài báo quốc tế (tiếng Anh)</td><td>iThenticate</td><td>iThenticate hoặc Turnitin</td></tr>
	</tbody>
</table>

<h2>Đọc kết quả AI cho bài tiếng Việt thế nào?</h2>
<ol>
	<li><strong>Xem điểm tổng</strong> nhưng đừng dừng ở đó - đây là xác suất, không phải phần trăm số câu do AI viết.</li>
	<li><strong>Xem các đoạn được đánh dấu:</strong> thường tập trung ở phần định nghĩa, cơ sở lý thuyết, kết luận chung chung.</li>
	<li><strong>Đối chiếu công cụ thứ hai</strong> nếu kết quả cao bất ngờ.</li>
	<li><strong>Viết lại các đoạn bị đánh dấu</strong> bằng số liệu, ví dụ và nhận định của riêng bạn - xem <a href="{home}giam-ty-le-ai-trung-thuc/">cách giảm tỷ lệ AI một cách trung thực</a>.</li>
</ol>

<div class="callout callout--info"><b>Văn bản dịch có bị tính là AI?</b>Đoạn văn dịch bằng công cụ dịch máy hoặc AI rồi đưa thẳng vào bài có thể có đặc điểm giống văn bản AI. Hãy biên tập lại bằng giọng văn của bạn và ghi nguồn bản gốc.</div>

<h2>Quy trình kiểm tra AI khuyến nghị cho bài tiếng Việt</h2>
<p>Để có kết quả đáng tin cậy và tiết kiệm chi phí, bạn có thể làm theo trình tự sau. Đầu tiên, kiểm tra <strong>đạo văn</strong> bằng đúng phần mềm trường sử dụng (Turnitin hoặc DoIT) ở chế độ không lưu bài. Tiếp theo, kiểm tra <strong>tỷ lệ AI</strong> bằng Originality.ai - công cụ có mô hình đa ngôn ngữ. Nếu kết quả AI cao hơn dự kiến, đối chiếu thêm bằng Copyleaks trước khi quyết định chỉnh sửa. Cuối cùng, sau khi đã viết lại các đoạn bị đánh dấu, kiểm tra lại một lần nữa để xác nhận.</p>
<p>Cách làm này giúp bạn không phải trả tiền cho một báo cáo AI không có giá trị (ví dụ báo cáo AI của Turnitin với bài tiếng Việt), đồng thời có hai góc nhìn độc lập khi kết quả nằm ở vùng không chắc chắn.</p>

<h2>Câu hỏi thường gặp</h2>
<h3>Bài tiếng Việt có cần kiểm tra AI không?</h3>
<p>Nếu trường hoặc giảng viên có quy định về AI, hoặc bạn có dùng công cụ AI hỗ trợ, bạn nên kiểm tra để chủ động chỉnh sửa và giải trình.</p>
<h3>Vì sao Turnitin không hiện tỷ lệ AI cho bài của tôi?</h3>
<p>Thường do bài viết bằng ngôn ngữ chưa được hỗ trợ (ngoài tiếng Anh, Tây Ban Nha, Nhật), hoặc tài liệu quá ngắn. Tài liệu gửi kiểm tra cần tối thiểu 300 từ.</p>
<h3>Kiểm tra AI bài tiếng Việt ở đâu nhanh nhất?</h3>
<p>Originality.ai tại {company} được xử lý tự động 24/7, thường có kết quả trong vài phút với file Word.</p>

<blockquote>Bài tiếng Việt cần kiểm tra AI? <a href="{home}dat-hang/?tab=check&amp;tool=originality&amp;mode=ai">Đặt kiểm tra AI bằng Originality.ai</a> - tự động 24/7, báo cáo PDF tô màu từng câu.</blockquote>
HTML
		,
	),
);
