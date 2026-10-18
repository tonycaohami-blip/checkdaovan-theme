<?php
/**
 * Bài viết 6-10 (bản 2: ≈800+ từ, chuẩn SEO). {home} được thay bằng địa chỉ trang chủ khi nhập.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(

	/* ---------------------------------------------------------- 6 */
	array(
		'slug'    => 'cach-giam-ty-le-dao-van-hop-le',
		'cat'     => 'huong-dan',
		'tags'    => array( 'cách giảm tỷ lệ đạo văn', 'giảm trùng lặp', 'Turnitin' ),
		'title'   => '7 cách giảm tỷ lệ đạo văn hợp lệ, hiệu quả mà vẫn giữ nguyên chất lượng bài',
		'excerpt' => 'Giảm tỷ lệ trùng lặp không phải là “lách” phần mềm. 7 cách làm hợp lệ, được giảng viên chấp nhận, giúp bài vừa đạt ngưỡng vừa tốt hơn về nội dung - kèm ví dụ cụ thể.',
		'content' => <<<'HTML'
<div class="tldr"><b>Tóm tắt nhanh</b><ul>
	<li>Bắt đầu từ báo cáo: sửa nguồn lớn nhất trước, đừng sửa tràn lan.</li>
	<li>Bổ sung trích dẫn còn thiếu là cách giảm “rủi ro” nhanh và đúng nhất.</li>
	<li>Viết lại theo hướng tổng hợp, so sánh, phân tích - không chỉ đổi từ đồng nghĩa.</li>
	<li>Tránh các mẹo lách như thay ký tự, chèn chữ trắng: dễ bị phát hiện và bị xử lý nặng hơn.</li>
</ul></div>

<p>Khi báo cáo Turnitin trả về con số cao hơn ngưỡng của trường, phản xạ đầu tiên của nhiều bạn là tìm “mẹo” để phần mềm không nhận ra. Đó là hướng đi sai và nhiều rủi ro. Cách đúng là <strong>giảm tỷ lệ đạo văn bằng việc cải thiện chính bài viết</strong>: trích dẫn đúng, diễn đạt bằng giọng của mình và tăng phần phân tích. Dưới đây là 7 cách hợp lệ, được giảng viên chấp nhận và thực sự làm bài tốt hơn.</p>

<h2>1. Đọc báo cáo và sửa theo thứ tự ưu tiên</h2>
<p>Đừng mở file Word và sửa từ trang đầu. Hãy mở báo cáo, xem danh sách nguồn và bắt đầu từ <strong>nguồn chiếm tỷ lệ cao nhất</strong>. Thường chỉ 3-5 nguồn đầu đã chiếm hơn một nửa tổng số phần trăm. Sửa đúng các đoạn đó, bạn giảm được nhiều nhất với ít công sức nhất. Xem lại <a href="{home}cach-doc-bao-cao-turnitin/">cách đọc báo cáo Turnitin</a> nếu bạn chưa quen.</p>

<h2>2. Bổ sung trích dẫn còn thiếu</h2>
<p>Rất nhiều đoạn bị tô màu thực ra là ý của tác giả khác mà bạn quên ghi nguồn. Thêm trích dẫn không làm tỷ lệ trên phần mềm giảm ngay (trừ khi trường bật lọc trích dẫn), nhưng nó biến đoạn văn từ “đạo văn” thành “sử dụng tài liệu hợp lệ” - đây mới là điều hội đồng quan tâm.</p>

<h2>3. Diễn đạt lại thật sự, không chỉ thay từ</h2>
<p>Đọc hiểu đoạn gốc, gấp tài liệu lại, rồi viết theo cách bạn giải thích cho một người bạn. Thay đổi cấu trúc câu, trật tự ý, và kết nối với nội dung nghiên cứu của bạn.</p>
<div class="example">
	<div class="ex-bad"><b>Chưa đúng</b>“Động lực làm việc là yếu tố then chốt quyết định hiệu suất của nhân viên” → “Động lực làm việc là nhân tố chủ chốt quyết định năng suất của người lao động.” (chỉ thay từ đồng nghĩa)</div>
	<div class="ex-good"><b>Đúng</b>“Theo Nguyễn (2021), khi nhân viên có động lực, hiệu suất của họ tăng rõ rệt - nhận định này cũng phù hợp với kết quả khảo sát tại doanh nghiệp X trong nghiên cứu này.”</div>
</div>

<h2>4. Tổng hợp nhiều nguồn thay vì tóm tắt từng nguồn</h2>
<p>Phần tổng quan tài liệu thường trùng nhiều nhất vì người viết tóm tắt lần lượt từng nghiên cứu. Thay vào đó, hãy nhóm theo chủ đề: “Các nghiên cứu của A (2019), B (2020) và C (2022) đều cho thấy…, tuy nhiên D (2023) lại…”. Cách viết này vừa giảm trùng lặp vừa thể hiện tư duy phản biện.</p>

<h2>5. Tăng phần phân tích và bình luận của riêng bạn</h2>
<p>Sau mỗi ý lấy từ tài liệu, hãy thêm một hoặc hai câu: ý đó liên quan thế nào đến đề tài của bạn, bạn đồng ý hay không, áp dụng vào bối cảnh Việt Nam ra sao. Phần này là của riêng bạn nên không trùng với bất kỳ nguồn nào - và đây cũng là phần giảng viên đánh giá cao nhất.</p>

<h2>6. Trình bày lại thông tin bằng bảng, sơ đồ</h2>
<p>Các đoạn liệt kê định nghĩa, phân loại, so sánh có thể chuyển thành bảng tổng hợp do bạn tự thiết kế. Nội dung rõ ràng hơn, và cấu trúc bảng khác hẳn văn bản gốc.</p>

<h2>7. Kiểm tra lại các phần “theo mẫu”</h2>
<p>Lời cam đoan, trang bìa, mục lục, danh mục tài liệu tham khảo, phụ lục (bảng hỏi, văn bản pháp luật) thường trùng rất nhiều. Hãy hỏi trường xem các phần này có được loại trừ khi kiểm tra không. Nếu có, bạn chỉ cần nộp phần chính văn.</p>

<div class="callout callout--warn"><b>Không nên làm</b>Thay chữ cái bằng ký tự tương tự, chèn chữ màu trắng, chuyển văn bản thành ảnh hay dùng công cụ “xoay từ” tự động. Các phần mềm hiện đại phát hiện được nhiều kỹ thuật này, và bị phát hiện cố tình gian lận thường bị xử lý nặng hơn cả đạo văn thông thường.</div>

<h2>Quy trình gợi ý</h2>
<ol>
	<li>Kiểm tra lần 1 bằng Turnitin (không lưu bài) để có báo cáo gốc.</li>
	<li>Sửa theo 7 cách trên, ưu tiên nguồn lớn.</li>
	<li>Kiểm tra lại lần 2 để xác nhận tỷ lệ đã đạt và còn biên an toàn.</li>
	<li>Đọc lại toàn bài để đảm bảo văn phong mạch lạc, nhất quán.</li>
</ol>

<div class="callout callout--tip"><b>Mẹo</b>Nếu đặt dịch vụ giảm tỷ lệ từ một đơn kiểm tra đã hoàn thành, bạn được giảm thêm phí và không cần tải lại báo cáo.</div>

<h2>Câu hỏi thường gặp</h2>
<h3>Giảm từ 45% xuống dưới 20% mất bao lâu?</h3>
<p>Tùy độ dài bài. Với khóa luận khoảng 60 trang, tự sửa thường mất 3-7 ngày. Dịch vụ chuyên nghiệp có thể hoàn thành nhanh hơn với các gói thời hạn khác nhau.</p>
<h3>Sửa xong có cần kiểm tra lại không?</h3>
<p>Nên có. Chỉ có báo cáo mới xác nhận tỷ lệ thực tế sau khi sửa.</p>
<h3>Dùng công cụ paraphrase tự động có được không?</h3>
<p>Không nên. Văn bản thường thiếu tự nhiên, sai nghĩa chuyên ngành và có thể làm tỷ lệ AI tăng.</p>

<blockquote>Không đủ thời gian tự sửa? <a href="{home}giam-ty-le-dao-van/">Dịch vụ giảm tỷ lệ đạo văn + AI</a> hỗ trợ biên tập diễn đạt và trích dẫn cho bài do chính bạn viết, hướng tới mức dưới 30% hoặc 20%.</blockquote>
HTML
		,
	),

	/* ---------------------------------------------------------- 7 */
	array(
		'slug'    => 'huong-dan-trich-dan-apa-7',
		'cat'     => 'huong-dan',
		'tags'    => array( 'trích dẫn APA 7', 'tài liệu tham khảo', 'cách trích dẫn' ),
		'title'   => 'Hướng dẫn trích dẫn APA 7 chi tiết kèm ví dụ cho sinh viên Việt Nam',
		'excerpt' => 'Cách trích dẫn trong bài và lập danh mục tài liệu tham khảo theo chuẩn APA 7: sách, bài báo, website, luận văn, tài liệu tiếng Việt. Kèm ví dụ đúng - sai và các lỗi thường gặp.',
		'content' => <<<'HTML'
<div class="tldr"><b>Tóm tắt nhanh</b><ul>
	<li>APA 7 dùng kiểu “tác giả - năm”: (Nguyễn, 2022) hoặc Nguyễn (2022).</li>
	<li>Từ 3 tác giả trở lên chỉ ghi tác giả đầu + “và cộng sự” (et al.).</li>
	<li>Mọi trích dẫn trong bài phải có trong danh mục tham khảo và ngược lại.</li>
	<li>Trích dẫn nguyên văn cần ngoặc kép và số trang.</li>
</ul></div>

<p>Trích dẫn đúng chuẩn là cách đơn giản nhất để tránh đạo văn và thể hiện sự chuyên nghiệp trong học thuật. <strong>APA 7</strong> (phiên bản thứ 7 của Hiệp hội Tâm lý học Hoa Kỳ) là chuẩn trích dẫn được nhiều trường đại học tại Việt Nam áp dụng, đặc biệt trong các ngành kinh tế, giáo dục, xã hội. Bài viết này hướng dẫn chi tiết cách trích dẫn trong bài và lập danh mục tài liệu tham khảo, kèm ví dụ cụ thể.</p>

<h2>Trích dẫn trong bài (in-text citation)</h2>
<p>APA 7 có hai dạng trích dẫn trong bài:</p>
<ul>
	<li><strong>Trích dẫn trong ngoặc:</strong> Động lực ảnh hưởng tích cực đến hiệu suất (Nguyễn, 2022).</li>
	<li><strong>Trích dẫn dạng tường thuật:</strong> Nguyễn (2022) cho rằng động lực ảnh hưởng tích cực đến hiệu suất.</li>
</ul>

<h3>Quy tắc theo số lượng tác giả</h3>
<table>
	<thead><tr><th>Số tác giả</th><th>Trong ngoặc</th><th>Tường thuật</th></tr></thead>
	<tbody>
		<tr><td>1 tác giả</td><td>(Trần, 2021)</td><td>Trần (2021)</td></tr>
		<tr><td>2 tác giả</td><td>(Trần &amp; Lê, 2021)</td><td>Trần và Lê (2021)</td></tr>
		<tr><td>Từ 3 tác giả</td><td>(Trần và cộng sự, 2021)</td><td>Trần và cộng sự (2021)</td></tr>
		<tr><td>Tổ chức</td><td>(Tổng cục Thống kê, 2023)</td><td>Tổng cục Thống kê (2023)</td></tr>
	</tbody>
</table>
<p>Với tài liệu tiếng Anh, dùng “et al.” thay cho “và cộng sự”: (Smith et al., 2020).</p>

<h3>Trích dẫn nguyên văn</h3>
<p>Đặt đoạn trích trong ngoặc kép và ghi số trang: (Nguyễn, 2022, tr. 45). Với đoạn trích từ 40 từ trở lên, trình bày thành khối thụt lề riêng, không dùng ngoặc kép.</p>

<div class="callout callout--info"><b>Tác giả Việt Nam ghi thế nào?</b>Thông lệ phổ biến ở Việt Nam là ghi họ tên đầy đủ hoặc theo hướng dẫn riêng của trường, ví dụ (Nguyễn Văn An, 2022). Hãy kiểm tra quy định của khoa vì đây là điểm các trường hướng dẫn khác nhau.</div>

<h2>Danh mục tài liệu tham khảo</h2>
<p>Danh mục sắp xếp theo thứ tự chữ cái của tên tác giả đầu tiên, thụt lề treo (hanging indent) 1,27 cm.</p>

<h3>Sách</h3>
<p>Tác giả. (Năm). <em>Tên sách in nghiêng</em> (lần xuất bản). Nhà xuất bản.</p>
<p>Ví dụ: Nguyễn, V. A. (2020). <em>Quản trị nguồn nhân lực</em> (tái bản lần 3). NXB Kinh tế Quốc dân.</p>

<h3>Bài báo khoa học</h3>
<p>Tác giả. (Năm). Tên bài báo. <em>Tên tạp chí, số tập</em>(số), trang. DOI</p>
<p>Ví dụ: Smith, J., &amp; Brown, K. (2021). Work motivation and performance. <em>Journal of Management, 47</em>(3), 112-130. https://doi.org/10.xxxx/xxxxx</p>

<h3>Trang web</h3>
<p>Tác giả hoặc tổ chức. (Ngày, tháng, năm). <em>Tiêu đề trang</em>. Tên website. URL</p>

<h3>Luận văn, luận án</h3>
<p>Tác giả. (Năm). <em>Tên luận văn</em> [Luận văn thạc sĩ, Tên trường]. Nguồn lưu trữ (nếu có).</p>

<h2>Lỗi thường gặp khi trích dẫn APA</h2>
<ul class="checklist">
	<li>Trích dẫn trong bài nhưng thiếu trong danh mục (hoặc ngược lại).</li>
	<li>Năm trong bài và trong danh mục không khớp nhau.</li>
	<li>Dùng “và cộng sự” cho bài chỉ có 2 tác giả.</li>
	<li>Quên số trang khi trích dẫn nguyên văn.</li>
	<li>Trộn lẫn nhiều chuẩn (APA, Harvard, IEEE) trong cùng một bài.</li>
	<li>Trích dẫn tài liệu chưa đọc - chỉ chép lại từ danh mục của bài khác.</li>
</ul>

<div class="callout callout--tip"><b>Mẹo</b>Dùng Zotero (miễn phí) hoặc tính năng References trong Word để tạo trích dẫn APA 7 tự động. Bạn chỉ cần kiểm tra lại thông tin đầu vào cho chính xác.</div>

<h2>Ví dụ trích dẫn đúng và chưa đúng</h2>
<div class="example">
	<div class="ex-bad"><b>Chưa đúng</b>Động lực làm việc chịu ảnh hưởng của môi trường, lương thưởng và cơ hội thăng tiến. (không ghi nguồn, dù ý lấy từ tài liệu)</div>
	<div class="ex-good"><b>Đúng</b>Động lực làm việc chịu ảnh hưởng của môi trường, lương thưởng và cơ hội thăng tiến (Trần &amp; Lê, 2021); kết quả khảo sát trong nghiên cứu này cũng cho thấy yếu tố thăng tiến có tác động mạnh nhất.</div>
</div>
<p>Ở ví dụ đúng, người viết vừa ghi nhận nguồn, vừa nối ý tài liệu với kết quả của chính mình. Đây là cách viết vừa an toàn về mặt trích dẫn, vừa tạo ra phần nội dung riêng không trùng với bất kỳ nguồn nào.</p>

<h2>Trích dẫn đúng có giúp giảm tỷ lệ trùng lặp?</h2>
<p>Trích dẫn đúng không phải lúc nào cũng làm con số trên Turnitin giảm, nhưng nếu trường bật bộ lọc “Exclude quotes” và “Exclude bibliography” thì phần trích dẫn nguyên văn và danh mục sẽ không bị tính. Quan trọng hơn, trích dẫn đúng biến những đoạn trùng lặp thành sử dụng tài liệu hợp lệ. Tham khảo thêm <a href="{home}cach-giam-ty-le-dao-van-hop-le/">cách giảm tỷ lệ đạo văn hợp lệ</a>.</p>

<h2>Câu hỏi thường gặp</h2>
<h3>Không có năm xuất bản thì ghi thế nào?</h3>
<p>Ghi “(Tên tác giả, k.n.)” - viết tắt của “không năm”, hoặc “n.d.” với tài liệu tiếng Anh.</p>
<h3>Có cần ghi số trang khi diễn đạt lại không?</h3>
<p>APA 7 không bắt buộc nhưng khuyến khích, nhất là khi tài liệu dài.</p>
<h3>Trường dùng chuẩn khác APA thì sao?</h3>
<p>Luôn ưu tiên hướng dẫn của trường. Nguyên tắc chung về ghi nhận nguồn vẫn giống nhau.</p>

<blockquote>Đã trích dẫn xong? <a href="{home}dat-hang/">Kiểm tra lại với Turnitin</a> để chắc chắn không còn đoạn nào bị sót nguồn.</blockquote>
HTML
		,
	),

	/* ---------------------------------------------------------- 8 */
	array(
		'slug'    => 'no-repository-la-gi',
		'cat'     => 'kien-thuc',
		'tags'    => array( 'no repository', 'Turnitin không lưu bài', 'bảo mật' ),
		'title'   => 'No Repository là gì? Vì sao cần kiểm tra Turnitin không lưu bài trước khi nộp',
		'excerpt' => 'Kiểm tra Turnitin ở chế độ lưu bài có thể khiến bài của bạn trùng 100% khi nộp chính thức. Giải thích No Repository là gì, cách hoạt động và vì sao bạn nên chọn chế độ này.',
		'content' => <<<'HTML'
<div class="tldr"><b>Tóm tắt nhanh</b><ul>
	<li>No Repository là chế độ kiểm tra không lưu bài vào kho dữ liệu của Turnitin.</li>
	<li>Nếu kiểm tra có lưu bài, lần nộp chính thức có thể bị báo trùng với chính bài của bạn.</li>
	<li>Mọi đơn kiểm tra trước khi nộp nên dùng chế độ không lưu.</li>
	<li>Hãy hỏi rõ nhà cung cấp dịch vụ về chế độ lưu bài trước khi gửi file.</li>
</ul></div>

<p>Đây là tình huống mà rất nhiều sinh viên đã gặp: kiểm tra thử bài ở một nơi nào đó được 15%, nhưng khi nộp chính thức lên hệ thống của trường thì báo trùng 90-100%, nguồn trùng chính là… bài của mình. Nguyên nhân nằm ở <strong>chế độ lưu bài</strong> của Turnitin. Bài viết này giải thích <strong>No Repository là gì</strong> và vì sao đó là điều kiện bắt buộc khi kiểm tra trước khi nộp.</p>

<h2>Kho lưu trữ bài nộp (Repository) là gì?</h2>
<p>Turnitin duy trì một kho dữ liệu rất lớn gồm các bài đã từng được nộp qua hệ thống, gọi là <strong>Student Paper Repository</strong>. Mỗi khi một bài được nộp ở chế độ lưu, bài đó được thêm vào kho. Lần sau, bất kỳ bài nào - kể cả chính bài đó - được kiểm tra đều sẽ được so sánh với kho này.</p>
<p>Đây là tính năng có ích cho nhà trường: nó giúp phát hiện sinh viên nộp lại bài của người khác hoặc của khóa trước. Nhưng nó cũng là “cái bẫy” nếu bạn kiểm tra thử ở chế độ lưu.</p>

<h2>No Repository là gì?</h2>
<p><strong>No Repository</strong> (không lưu bài) là thiết lập mà bài được kiểm tra, có báo cáo đầy đủ, nhưng <strong>không được thêm vào kho dữ liệu</strong>. Sau khi kiểm tra, bài của bạn không để lại dấu vết trong kho so sánh, vì vậy lần nộp chính thức sẽ không bị trùng với chính nó.</p>

<table>
	<thead><tr><th></th><th>Có lưu bài (Standard)</th><th>Không lưu bài (No Repository)</th></tr></thead>
	<tbody>
		<tr><td>Có báo cáo đầy đủ</td><td>Có</td><td>Có</td></tr>
		<tr><td>Bài được thêm vào kho</td><td>Có</td><td>Không</td></tr>
		<tr><td>Rủi ro khi nộp chính thức</td><td>Có thể trùng với chính bài mình</td><td>Không</td></tr>
		<tr><td>Phù hợp</td><td>Lần nộp chính thức cho trường</td><td>Kiểm tra trước khi nộp</td></tr>
	</tbody>
</table>

<div class="callout callout--warn"><b>Cảnh báo</b>Các tài khoản Turnitin không rõ nguồn gốc, được chia sẻ trên mạng thường để mặc định chế độ lưu bài. Một lần kiểm tra “miễn phí” có thể khiến bạn phải làm việc với phòng đào tạo để giải trình.</div>

<h2>Nếu bài đã bị lưu nhầm thì sao?</h2>
<p>Bài đã nằm trong kho chỉ có thể được gỡ bỏ thông qua quản trị viên Turnitin của tổ chức đã lưu bài - sinh viên không tự làm được. Bạn có thể:</p>
<ol>
	<li>Liên hệ đơn vị đã kiểm tra để yêu cầu gửi đề nghị xóa bài khỏi kho.</li>
	<li>Thông báo trước với giảng viên hướng dẫn, kèm bằng chứng về lần kiểm tra trước đó.</li>
	<li>Khi nộp chính thức, giảng viên có thể loại trừ nguồn trùng là bài của chính bạn.</li>
</ol>

<h2>Những đơn nào cần No Repository?</h2>
<ul class="checklist">
	<li>Kiểm tra tiểu luận, khóa luận, luận văn trước khi nộp cho trường.</li>
	<li>Kiểm tra lại sau khi đã chỉnh sửa giảm tỷ lệ.</li>
	<li>Kiểm tra bài báo trước khi gửi tạp chí (với iThenticate, bài cũng không được lưu vào kho bài nộp).</li>
	<li>Kiểm tra tài liệu nội bộ, đề tài chưa công bố.</li>
</ul>

<h2>Vì sao kho lưu trữ lại quan trọng với nhà trường?</h2>
<p>Kho bài nộp là điểm khác biệt lớn nhất giữa Turnitin và nhiều công cụ khác. Nhờ kho này, giảng viên phát hiện được việc sinh viên dùng lại bài của bạn cùng lớp, của khóa trước hoặc của sinh viên trường khác - những nguồn không bao giờ xuất hiện trên Internet. Vì thế, khi nộp chính thức, trường thường để chế độ lưu bài. Còn khi bạn chỉ muốn biết trước kết quả, chế độ không lưu là lựa chọn duy nhất đúng: bạn nhận được báo cáo như thật mà không để lại bản sao nào ảnh hưởng đến lần nộp chính thức.</p>

<h2>Cách nhận biết dịch vụ có thật sự không lưu bài</h2>
<p>Hãy hỏi rõ trước khi gửi file. Một dịch vụ uy tín sẽ cam kết bằng văn bản, mô tả quy trình kiểm tra và chính sách xóa file sau khi trả kết quả. Bạn có thể đọc thêm về cách <a href="{home}bao-mat-tai-lieu-khi-kiem-tra-dao-van/">bảo mật tài liệu khi kiểm tra đạo văn</a> và tiêu chí <a href="{home}chon-dich-vu-kiem-tra-dao-van-uy-tin/">chọn dịch vụ kiểm tra uy tín</a>.</p>

<div class="callout callout--tip"><b>Mẹo</b>Lưu lại email xác nhận đơn và báo cáo PDF. Nếu có sự cố, đây là bằng chứng giúp bạn giải trình với trường.</div>

<h2>Câu hỏi thường gặp</h2>
<h3>Báo cáo No Repository có khác báo cáo thường không?</h3>
<p>Không. Báo cáo vẫn đầy đủ chỉ số Similarity, danh sách nguồn và phần tô màu. Khác biệt duy nhất là bài không được lưu vào kho.</p>
<h3>Kiểm tra nhiều lần ở chế độ không lưu có ảnh hưởng gì không?</h3>
<p>Không. Bạn có thể kiểm tra bao nhiêu lần tùy ý mà kết quả không bị “cộng dồn”.</p>
<h3>Bài báo đã đăng có bị tính trùng khi nộp luận án?</h3>
<p>Có thể, vì bài đã công bố nằm trong nguồn ấn phẩm. Bạn cần trích dẫn bài báo đó - xem thêm về <a href="{home}tu-dao-van-la-gi/">tự đạo văn</a>.</p>

<blockquote>Mọi đơn kiểm tra tại đây đều ở chế độ <strong>không lưu bài</strong>. <a href="{home}dat-hang/">Đặt kiểm tra ngay</a>.</blockquote>
HTML
		,
	),

	/* ---------------------------------------------------------- 9 */
	array(
		'slug'    => 'sai-lam-khien-ty-le-dao-van-cao',
		'cat'     => 'huong-dan',
		'tags'    => array( 'tỷ lệ đạo văn cao', 'lỗi trích dẫn', 'Turnitin' ),
		'title'   => '8 sai lầm khiến tỷ lệ đạo văn cao bất ngờ và cách khắc phục',
		'excerpt' => 'Tự viết nhưng tỷ lệ Turnitin vẫn cao? 8 sai lầm phổ biến từ tổng quan tài liệu, trích dẫn đến định dạng file khiến tỷ lệ trùng lặp tăng vọt, kèm cách khắc phục từng lỗi.',
		'content' => <<<'HTML'
<div class="tldr"><b>Tóm tắt nhanh</b><ul>
	<li>Phần tổng quan tài liệu và cơ sở lý thuyết là nơi trùng nhiều nhất.</li>
	<li>Dùng lại đề cương, bài cũ hay mẫu của khóa trước làm tỷ lệ tăng mạnh.</li>
	<li>Kiểm tra cả phụ lục, bảng hỏi, lời cam đoan khi không cần thiết làm “phồng” con số.</li>
	<li>Hầu hết lỗi có thể sửa nhanh nếu phát hiện sớm.</li>
</ul></div>

<p>“Em tự viết hết mà sao Turnitin báo 38%?” - đây là câu hỏi chúng tôi nhận được gần như mỗi ngày. Sự thật là tỷ lệ trùng lặp cao không nhất thiết do bạn sao chép, mà thường do <strong>thói quen viết và trình bày</strong> chưa đúng. Dưới đây là 8 sai lầm phổ biến nhất khiến tỷ lệ đạo văn cao bất ngờ, kèm cách khắc phục cụ thể.</p>

<h2>1. Tóm tắt từng tài liệu trong phần tổng quan</h2>
<p>Viết “Nghiên cứu của A cho rằng… Nghiên cứu của B chỉ ra…” lần lượt từng đoạn khiến câu chữ rất gần với phần tóm tắt (abstract) của các bài gốc - vốn đã có trên Internet.</p>
<p><strong>Khắc phục:</strong> nhóm tài liệu theo chủ đề, so sánh điểm giống và khác, rút ra khoảng trống nghiên cứu bằng lời của bạn.</p>

<h2>2. Chép định nghĩa nguyên văn mà không để trong ngoặc kép</h2>
<p>Định nghĩa khái niệm thường được lấy nguyên văn từ sách giáo trình. Nếu không đặt trong ngoặc kép, đó vừa là trùng lặp vừa là lỗi trích dẫn.</p>
<p><strong>Khắc phục:</strong> chỉ trích nguyên văn những định nghĩa thật sự cần thiết, đặt trong ngoặc kép kèm số trang; còn lại hãy diễn giải và so sánh các định nghĩa.</p>

<h2>3. Dùng lại đề cương, tiểu luận hoặc bài báo cũ của chính mình</h2>
<p>Nếu đề cương hay tiểu luận trước đó đã từng được nộp qua Turnitin ở chế độ lưu bài, phần mềm sẽ nhận ra ngay. Xem thêm về <a href="{home}tu-dao-van-la-gi/">tự đạo văn</a> và <a href="{home}no-repository-la-gi/">chế độ không lưu bài</a>.</p>
<p><strong>Khắc phục:</strong> trích dẫn bài cũ của mình hoặc viết lại, và hỏi giảng viên về việc tái sử dụng.</p>

<h2>4. Tham khảo quá nhiều luận văn của khóa trước</h2>
<p>Luận văn cùng trường, cùng đề tài gần như chắc chắn đã có trong kho của Turnitin. Mượn khung chương, cách diễn đạt từ các bài này là nguồn trùng lớn nhất trong nhiều báo cáo.</p>
<p><strong>Khắc phục:</strong> dùng luận văn khóa trước để tham khảo cấu trúc, còn nội dung hãy dựa vào tài liệu gốc và dữ liệu của chính bạn.</p>

<h2>5. Dịch nguyên văn từ tài liệu tiếng Anh</h2>
<p>Dịch máy hoặc dịch sát từng câu khiến cấu trúc giống hệt bản gốc. Các công cụ như Copyleaks có khả năng so khớp đa ngôn ngữ.</p>
<p><strong>Khắc phục:</strong> đọc hiểu, ghi chú ý chính rồi viết bằng tiếng Việt tự nhiên, luôn ghi nguồn bản gốc.</p>

<h2>6. Nộp kèm cả phụ lục, bảng hỏi, văn bản pháp luật</h2>
<p>Bảng hỏi theo thang đo chuẩn, trích dẫn điều luật, mẫu biểu… chắc chắn trùng với nhiều nguồn và có thể chiếm 5-10% tổng tỷ lệ.</p>
<p><strong>Khắc phục:</strong> hỏi trường phần nào cần kiểm tra. Nhiều trường chỉ kiểm tra phần chính văn.</p>

<h2>7. Sai định dạng file khiến phần mềm đọc sai</h2>
<p>File PDF xuất từ ảnh scan, file có nhiều hộp văn bản (text box) hoặc font lỗi có thể khiến phần mềm đọc không đúng, đôi khi làm báo cáo sai lệch.</p>
<p><strong>Khắc phục:</strong> nộp file Word (.docx) hoặc PDF xuất trực tiếp từ Word, không dùng ảnh chụp văn bản.</p>

<h2>8. Kiểm tra ở chế độ có lưu bài</h2>
<p>Kiểm tra thử ở một tài khoản lưu bài, sau đó nộp chính thức sẽ bị trùng với chính mình - có khi lên đến 90-100%.</p>
<p><strong>Khắc phục:</strong> luôn yêu cầu chế độ <strong>không lưu bài</strong> khi kiểm tra trước khi nộp.</p>

<div class="callout callout--info"><b>Thống kê từ các đơn kiểm tra</b>Trong các bài có tỷ lệ trên 30%, phần lớn tỷ lệ thường tập trung ở chương tổng quan và cơ sở lý thuyết. Sửa riêng hai chương này thường đã đủ đưa bài về ngưỡng an toàn.</div>

<h2>Checklist tự rà soát nhanh</h2>
<ul class="checklist">
	<li>Phần tổng quan đã được viết theo chủ đề, không tóm tắt từng bài.</li>
	<li>Mọi định nghĩa nguyên văn đều có ngoặc kép và số trang.</li>
	<li>Không dùng lại đoạn nào từ bài cũ mà chưa trích dẫn.</li>
	<li>File nộp là .docx hoặc PDF xuất từ Word.</li>
	<li>Đã xác định phần nào được loại trừ khi kiểm tra.</li>
</ul>

<h2>Câu hỏi thường gặp</h2>
<h3>Tại sao hai lần kiểm tra cùng một bài lại khác nhau?</h3>
<p>Do bộ lọc khác nhau hoặc cơ sở dữ liệu đã được cập nhật giữa hai lần kiểm tra.</p>
<h3>Bài có nhiều công thức, bảng số liệu có bị tính trùng không?</h3>
<p>Ít khi. Phần mềm chủ yếu so khớp chuỗi văn bản, nên số liệu và công thức thường không ảnh hưởng nhiều.</p>

<blockquote>Muốn biết chính xác đoạn nào đang làm tỷ lệ tăng? <a href="{home}dat-hang/">Đặt kiểm tra Turnitin</a> và nhận báo cáo chi tiết từng nguồn.</blockquote>
HTML
		,
	),

	/* ---------------------------------------------------------- 10 */
	array(
		'slug'    => 'checklist-truoc-khi-nop-luan-van',
		'cat'     => 'huong-dan',
		'tags'    => array( 'checklist nộp luận văn', 'luận văn thạc sĩ', 'khóa luận tốt nghiệp' ),
		'title'   => 'Checklist 15 bước trước khi nộp luận văn, khóa luận: Đừng bỏ sót bước nào',
		'excerpt' => 'Danh sách 15 bước kiểm tra trước khi nộp luận văn, khóa luận: từ nội dung, trích dẫn, định dạng đến kiểm tra đạo văn và tỷ lệ AI. Lưu lại để dùng cho mùa bảo vệ.',
		'content' => <<<'HTML'
<div class="tldr"><b>Tóm tắt nhanh</b><ul>
	<li>Chia việc rà soát thành 4 nhóm: nội dung, trích dẫn, định dạng và kiểm tra trùng lặp.</li>
	<li>Bắt đầu rà soát ít nhất 2 tuần trước hạn nộp.</li>
	<li>Kiểm tra đạo văn và tỷ lệ AI bằng đúng phần mềm của trường, chế độ không lưu bài.</li>
	<li>Lưu lại báo cáo cuối cùng để đối chiếu khi cần.</li>
</ul></div>

<p>Sau nhiều tháng nghiên cứu, những ngày cuối trước hạn nộp luận văn thường là lúc dễ mắc lỗi nhất: vội vàng, mệt mỏi và bỏ sót những chi tiết nhỏ nhưng quan trọng. Một lỗi định dạng hay một đoạn quên trích dẫn có thể khiến bạn phải nộp lại. Dưới đây là <strong>checklist 15 bước trước khi nộp luận văn, khóa luận</strong>, chia theo 4 nhóm để bạn rà soát có hệ thống.</p>

<h2>Nhóm 1: Nội dung</h2>
<ul class="checklist">
	<li><strong>Bước 1 - Tên đề tài thống nhất</strong> giữa bìa, trang phụ bìa, quyết định giao đề tài và nội dung.</li>
	<li><strong>Bước 2 - Mục tiêu và câu hỏi nghiên cứu được trả lời</strong> đầy đủ ở phần kết quả và kết luận.</li>
	<li><strong>Bước 3 - Số liệu nhất quán</strong> giữa bảng biểu, văn bản và tóm tắt.</li>
	<li><strong>Bước 4 - Kết luận không đưa thông tin mới</strong> chưa được trình bày ở các chương trước.</li>
</ul>

<h2>Nhóm 2: Trích dẫn và tài liệu tham khảo</h2>
<ul class="checklist">
	<li><strong>Bước 5 - Mọi trích dẫn trong bài đều có trong danh mục</strong> và ngược lại.</li>
	<li><strong>Bước 6 - Thống nhất một chuẩn trích dẫn</strong> (APA, Harvard, IEEE… theo yêu cầu của trường). Xem <a href="{home}huong-dan-trich-dan-apa-7/">hướng dẫn trích dẫn APA 7</a>.</li>
	<li><strong>Bước 7 - Trích dẫn nguyên văn có ngoặc kép và số trang.</strong></li>
	<li><strong>Bước 8 - Hình ảnh, bảng biểu lấy từ nguồn khác có ghi “Nguồn:”</strong> ngay bên dưới.</li>
</ul>

<h2>Nhóm 3: Định dạng</h2>
<ul class="checklist">
	<li><strong>Bước 9 - Font, cỡ chữ, giãn dòng, lề</strong> đúng quy định của trường.</li>
	<li><strong>Bước 10 - Mục lục, danh mục bảng, hình tự động</strong> và đã cập nhật số trang.</li>
	<li><strong>Bước 11 - Đánh số trang đúng</strong> (phần đầu thường dùng số La Mã, chính văn dùng số Ả Rập).</li>
	<li><strong>Bước 12 - Rà soát chính tả</strong>, đặc biệt tên riêng, thuật ngữ và tên tác giả.</li>
</ul>

<h2>Nhóm 4: Kiểm tra trùng lặp và AI</h2>
<ul class="checklist">
	<li><strong>Bước 13 - Kiểm tra đạo văn</strong> bằng đúng phần mềm trường dùng, chế độ <a href="{home}no-repository-la-gi/">không lưu bài</a>.</li>
	<li><strong>Bước 14 - Kiểm tra tỷ lệ AI</strong> nếu trường có yêu cầu hoặc nếu bạn có dùng công cụ AI hỗ trợ.</li>
	<li><strong>Bước 15 - Lưu lại báo cáo PDF cuối cùng</strong> và bản Word tương ứng.</li>
</ul>

<div class="callout callout--warn"><b>Lưu ý</b>Sau khi kiểm tra đạo văn, nếu bạn còn sửa nội dung đáng kể (thêm cả một mục, thay phần tổng quan), hãy kiểm tra lại. Báo cáo chỉ đúng với phiên bản đã kiểm tra.</div>

<h2>Lịch trình gợi ý trong 2 tuần cuối</h2>
<table>
	<thead><tr><th>Thời điểm</th><th>Việc cần làm</th></tr></thead>
	<tbody>
		<tr><td>14 ngày trước hạn</td><td>Hoàn thiện nội dung, rà soát nhóm 1</td></tr>
		<tr><td>10 ngày trước hạn</td><td>Kiểm tra đạo văn + AI lần 1</td></tr>
		<tr><td>7-10 ngày</td><td>Chỉnh sửa theo báo cáo, rà soát trích dẫn (nhóm 2)</td></tr>
		<tr><td>5 ngày trước hạn</td><td>Kiểm tra lại lần 2, xác nhận đạt ngưỡng</td></tr>
		<tr><td>3 ngày trước hạn</td><td>Định dạng và in ấn (nhóm 3)</td></tr>
		<tr><td>1 ngày trước hạn</td><td>Đọc lại toàn bộ, chuẩn bị file nộp</td></tr>
	</tbody>
</table>

<div class="callout callout--tip"><b>Mẹo</b>Nếu sát hạn, hãy chọn kiểm tra hỏa tốc - báo cáo được trả trong khoảng 30 phút. Xem thêm <a href="{home}check-turnitin-gap-truoc-han-nop/">kinh nghiệm check Turnitin gấp trước hạn nộp</a>.</div>

<h2>3 lỗi “phút cuối” hay gặp nhất</h2>
<ol>
	<li><strong>Nộp nhầm phiên bản:</strong> file nộp không phải file đã kiểm tra đạo văn. Hãy đặt tên file theo ngày và số phiên bản, ví dụ <em>LuanVan_v7_1810.docx</em>.</li>
	<li><strong>Mục lục chưa cập nhật:</strong> sau khi chỉnh sửa, số trang trong mục lục bị lệch. Nhấn “Update Table” trước khi xuất file.</li>
	<li><strong>Chèn thêm nội dung sau khi kiểm tra:</strong> thêm một mục mới mà không kiểm tra lại có thể làm tỷ lệ thay đổi đáng kể.</li>
</ol>

<h2>Nếu tỷ lệ chưa đạt thì sao?</h2>
<p>Đừng hoảng. Xem lại <a href="{home}ty-le-dao-van-bao-nhieu-la-dat/">ngưỡng tỷ lệ của trường</a>, xác định nguồn trùng lớn nhất và sửa theo <a href="{home}cach-giam-ty-le-dao-van-hop-le/">7 cách giảm tỷ lệ hợp lệ</a>. Nếu không đủ thời gian, bạn có thể đặt dịch vụ giảm tỷ lệ từ chính đơn kiểm tra vừa hoàn thành.</p>

<h2>Câu hỏi thường gặp</h2>
<h3>Nên kiểm tra đạo văn vào lúc nào?</h3>
<p>Khoảng 10 ngày trước hạn nộp - đủ sớm để sửa, đủ muộn để nội dung đã gần hoàn chỉnh.</p>
<h3>Có cần kiểm tra cả phần phụ lục không?</h3>
<p>Tùy quy định trường. Nếu không bắt buộc, chỉ cần kiểm tra phần chính văn để kết quả sát thực tế.</p>
<h3>Bản in và bản điện tử có cần giống nhau hoàn toàn?</h3>
<p>Có. Hội đồng có thể đối chiếu bản nộp điện tử với báo cáo kiểm tra và bản in.</p>

<blockquote>Hoàn thành bước 13-14 ngay hôm nay: <a href="{home}dat-hang/?tab=check&amp;tool=turnitin&amp;mode=combo">kiểm tra Đạo văn + AI trên Turnitin</a>, trả kết quả tự động 24/7.</blockquote>
HTML
		,
	),
);
