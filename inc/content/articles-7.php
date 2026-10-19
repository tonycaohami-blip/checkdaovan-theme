<?php
/**
 * Bài viết 35-38: báo cáo AI, viết lại trung thực, chuẩn bị file, chọn mục tiêu tỷ lệ (>1000 từ/bài, có ảnh).
 * {home} = trang chủ, {theme} = thư mục theme, {company} = tên thương hiệu. Giá dùng shortcode.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(

	/* ---------------------------------------------------------- 35 */
	array(
		'slug'    => 'cach-doc-bao-cao-ai-turnitin',
		'cat'     => 'ai',
		'cover'   => 'cach-doc-bao-cao-ai-turnitin-check-dao-van.jpg',
		'tags'    => array( 'báo cáo AI Turnitin', 'Turnitin AI writing', 'phát hiện AI', 'đọc báo cáo' ),
		'title'   => 'Cách đọc báo cáo AI của Turnitin: Hiểu đúng tỷ lệ, màu đánh dấu và cách phản hồi',
		'excerpt' => 'Báo cáo AI của Turnitin cho biết gì, các màu đánh dấu có ý nghĩa ra sao, vì sao mức thấp được hiển thị khác đi và nên làm gì khi tỷ lệ cao. Hướng dẫn đọc báo cáo AI từng bước cho sinh viên.',
		'content' => <<<'HTML'
<div class="tldr"><b>Tóm tắt nhanh</b><ul>
	<li>Báo cáo AI của Turnitin là báo cáo riêng, độc lập với chỉ số trùng lặp (Similarity).</li>
	<li>Con số tổng là tỷ lệ phần văn bản được mô hình đánh giá có khả năng do AI tạo ra.</li>
	<li>Các phiên bản gần đây phân biệt văn bản do AI tạo ra và văn bản AI tạo rồi được viết lại bằng công cụ.</li>
	<li>Kết quả là tín hiệu để xem xét, không phải bằng chứng duy nhất; chỉ áp dụng cho tiếng Anh, Tây Ban Nha, Nhật.</li>
</ul></div>

<p>Nhiều trường đại học hiện yêu cầu nộp kèm cả báo cáo trùng lặp và báo cáo AI. Trong khi báo cáo trùng lặp đã quen thuộc, <strong>báo cáo AI của Turnitin</strong> còn khá mới với nhiều sinh viên: con số có nghĩa là gì, vì sao có đoạn được tô màu khác nhau, vì sao đôi khi chỉ thấy dấu sao thay vì con số? Hiểu sai báo cáo có thể khiến bạn lo lắng không cần thiết - hoặc ngược lại, chủ quan với một vấn đề thật. Bài viết này hướng dẫn bạn đọc báo cáo AI từng phần.</p>

<h2>Báo cáo AI khác báo cáo trùng lặp thế nào?</h2>
<table>
	<thead><tr><th></th><th>Báo cáo trùng lặp (Similarity)</th><th>Báo cáo AI</th></tr></thead>
	<tbody>
		<tr><td>Trả lời câu hỏi</td><td>Văn bản có khớp với nguồn có sẵn không?</td><td>Văn bản có khả năng do AI tạo ra không?</td></tr>
		<tr><td>Cách hoạt động</td><td>So khớp với cơ sở dữ liệu</td><td>Mô hình đánh giá đặc điểm thống kê của câu chữ</td></tr>
		<tr><td>Có chỉ ra nguồn</td><td>Có, kèm đường dẫn</td><td>Không - chỉ tô sáng đoạn nghi ngờ</td></tr>
		<tr><td>Ngôn ngữ</td><td>Hầu hết ngôn ngữ</td><td>Tiếng Anh, Tây Ban Nha, Nhật</td></tr>
	</tbody>
</table>
<p>Một bài có thể có tỷ lệ trùng lặp rất thấp nhưng tỷ lệ AI cao (vì văn bản AI thường là văn bản “mới”), hoặc ngược lại. Hai con số này không cộng vào nhau và cần đọc riêng.</p>

<figure><img src="{theme}assets/img/blog/cach-doc-bao-cao-ai-turnitin-report.svg" alt="Mô phỏng báo cáo AI của Turnitin với tỷ lệ tổng và các đoạn được tô sáng" width="1200" height="675" loading="lazy"><figcaption>Mô phỏng báo cáo AI: tỷ lệ tổng và các đoạn được tô sáng theo loại (minh họa).</figcaption></figure>

<h2>Đọc báo cáo AI từng phần</h2>
<h3>1. Tỷ lệ tổng</h3>
<p>Con số phần trăm ở đầu báo cáo cho biết tỷ lệ văn bản được mô hình đánh giá là có khả năng do AI tạo ra, tính trên phần nội dung đủ điều kiện đánh giá (văn bản dạng câu, đoạn liền mạch). Ví dụ 34% nghĩa là khoảng một phần ba nội dung đủ điều kiện bị đánh giá là có khả năng do AI tạo ra - không có nghĩa là bạn “gian lận 34%”.</p>
<h3>2. Các màu đánh dấu</h3>
<p>Các phiên bản báo cáo gần đây của Turnitin có thể tách thành hai nhóm đánh dấu: <strong>văn bản có khả năng do AI tạo ra</strong>, và <strong>văn bản AI tạo ra rồi được viết lại bằng công cụ diễn đạt lại</strong> (ví dụ các công cụ “paraphrase” tự động). Mỗi nhóm có màu riêng trong phần văn bản. Hãy đọc chú thích màu trên chính báo cáo của bạn, vì giao diện có thể thay đổi theo phiên bản.</p>
<h3>3. Mức thấp được hiển thị khác đi</h3>
<p>Với tỷ lệ thấp, độ tin cậy của mô hình giảm, nên Turnitin hiển thị khác đi (ví dụ bằng dấu sao thay vì con số chính xác) để người đọc thận trọng. Gặp trường hợp này, bạn không nên coi đó là kết luận bài có dùng AI.</p>
<h3>4. Phần không được đánh giá</h3>
<p>Danh mục tài liệu tham khảo, bảng biểu, gạch đầu dòng ngắn, công thức thường không đủ điều kiện để mô hình đánh giá. Vì vậy tỷ lệ AI được tính trên phần văn bản liền mạch, không phải toàn bộ số trang.</p>

<div class="callout callout--warn"><b>Ngôn ngữ được hỗ trợ</b>Tính năng phát hiện AI của Turnitin và iThenticate chỉ áp dụng cho tài liệu tiếng Anh, tiếng Tây Ban Nha và tiếng Nhật. Bài tiếng Việt nên kiểm tra AI bằng công cụ khác - xem <a href="{home}kiem-tra-ty-le-ai-bai-tieng-viet/">kiểm tra AI cho bài tiếng Việt</a>.</div>

<h2>Những hiểu lầm thường gặp</h2>
<ul>
	<li><strong>“Tỷ lệ AI cao = chắc chắn gian lận.”</strong> Sai. Turnitin khuyến nghị dùng báo cáo AI như một tín hiệu để trao đổi, không phải căn cứ duy nhất.</li>
	<li><strong>“Tỷ lệ AI thấp = an toàn tuyệt đối.”</strong> Không hẳn. Quy định của trường về việc dùng AI vẫn áp dụng dù công cụ không phát hiện.</li>
	<li><strong>“Dùng công cụ viết lại sẽ làm mất dấu AI.”</strong> Các phiên bản mới có thể nhận diện văn bản AI đã qua công cụ viết lại, và việc cố tình che giấu thường bị xử lý nặng hơn.</li>
	<li><strong>“Sửa chính tả bằng AI cũng bị tính.”</strong> Sửa lỗi nhỏ ít ảnh hưởng; viết lại cả đoạn mới là điều làm thay đổi đặc điểm văn bản.</li>
</ul>

<h2>Nên làm gì khi tỷ lệ AI cao?</h2>
<ol>
	<li><strong>Bình tĩnh xác định đoạn bị đánh dấu.</strong> Thường chỉ tập trung ở vài phần: mở đầu, cơ sở lý thuyết, kết luận.</li>
	<li><strong>Tự đánh giá trung thực:</strong> đoạn đó bạn tự viết, dùng AI hỗ trợ, hay AI viết thay?</li>
	<li><strong>Viết lại bằng nội dung của chính bạn:</strong> số liệu, ví dụ, lập luận cụ thể - xem <a href="{home}giam-ty-le-ai-trung-thuc/">cách giảm tỷ lệ AI một cách trung thực</a>.</li>
	<li><strong>Đối chiếu công cụ khác</strong> nếu bạn tin kết quả là dương tính giả.</li>
	<li><strong>Chuẩn bị bằng chứng quá trình viết</strong> và trao đổi thẳng thắn với giảng viên khi cần.</li>
</ol>

<div class="callout callout--tip"><b>Mẹo</b>Bật lịch sử phiên bản (Version History) trong Word hoặc Google Docs ngay từ đầu. Đây là bằng chứng rõ ràng nhất cho thấy bài được bạn viết qua nhiều giai đoạn.</div>

<h2>Đọc báo cáo AI cùng báo cáo trùng lặp</h2>
<p>Khi có cả hai báo cáo, hãy đọc theo thứ tự: trùng lặp trước, AI sau. Xử lý trùng lặp (bổ sung trích dẫn, viết lại) thường đã thay đổi một phần câu chữ; sau đó mới xem phần AI còn lại để quyết định chỉnh sửa tiếp. Nếu cả hai tỷ lệ đều cao, dịch vụ <a href="{home}dich-vu-giam-ty-le-dao-van-ai/">giảm tỷ lệ đạo văn + AI</a> cho phép bạn chọn giảm cả hai cùng lúc với chi phí thấp hơn đặt riêng.</p>

<h2>Câu hỏi thường gặp</h2>
<h3>Báo cáo AI có cho biết tôi dùng công cụ AI nào không?</h3>
<p>Không. Báo cáo chỉ ước lượng khả năng văn bản do AI tạo ra, không xác định công cụ cụ thể.</p>
<h3>Tài liệu ngắn có được đánh giá AI không?</h3>
<p>Tài liệu quá ngắn có thể không đủ điều kiện đánh giá. Tại {company}, tài liệu kiểm tra bằng Turnitin hoặc iThenticate cần tối thiểu 300 từ.</p>
<h3>Có thể kiểm tra AI trước khi nộp cho trường không?</h3>
<p>Có. Kiểm tra trước ở chế độ không lưu bài giúp bạn chủ động chỉnh sửa và chuẩn bị giải trình nếu cần.</p>

<blockquote>Biết trước tỷ lệ AI trước khi nộp: <a href="{home}dat-hang/?tab=check&amp;tool=turnitin&amp;mode=combo">đặt kiểm tra Turnitin Đạo văn + AI</a> cho bài tiếng Anh, hoặc <a href="{home}dat-hang/?tab=check&amp;tool=originality&amp;mode=ai">Originality.ai</a> cho bài tiếng Việt.</blockquote>
HTML
		,
	),

	/* ---------------------------------------------------------- 36 */
	array(
		'slug'    => 'giam-ty-le-ai-trung-thuc',
		'cat'     => 'ai',
		'cover'   => 'giam-ty-le-ai-trung-thuc-check-dao-van.jpg',
		'tags'    => array( 'giảm tỷ lệ AI', 'viết lại bài', 'liêm chính học thuật', 'văn phong học thuật' ),
		'title'   => 'Cách giảm tỷ lệ AI trong bài viết học thuật một cách trung thực: 7 kỹ thuật biên tập',
		'excerpt' => 'Giảm tỷ lệ AI không phải là “qua mặt” phần mềm mà là làm cho bài viết cụ thể, có dấu ấn cá nhân. 7 kỹ thuật biên tập trung thực, ví dụ trước - sau, và những cách làm nên tránh để không vi phạm quy định.',
		'content' => <<<'HTML'
<div class="tldr"><b>Tóm tắt nhanh</b><ul>
	<li>Văn bản bị đánh giá là AI thường chung chung, đều nhịp, thiếu chi tiết cụ thể.</li>
	<li>Cách giảm tỷ lệ AI bền vững là bổ sung nội dung thật: số liệu, ví dụ, lập luận của bạn.</li>
	<li>Tránh dùng công cụ “humanizer” hay viết lại tự động - dễ bị phát hiện và bị coi là che giấu.</li>
	<li>Luôn tuân thủ quy định về AI của trường và khai báo khi được yêu cầu.</li>
</ul></div>

<p>Bạn nhận báo cáo và thấy tỷ lệ AI 40%, dù phần lớn bài là do mình viết - có thể có vài đoạn dùng AI để gợi ý ý tưởng hoặc sửa câu. Phản xạ đầu tiên của nhiều người là tìm công cụ “làm người hóa” văn bản để hạ con số. Đây là hướng đi rủi ro: các phiên bản phát hiện AI mới đã nhận diện được văn bản qua công cụ viết lại, và việc cố ý che giấu có thể bị xử lý nặng hơn chính việc dùng AI. Bài viết này giới thiệu cách <strong>giảm tỷ lệ AI một cách trung thực</strong>: biên tập để bài viết thực sự tốt hơn và mang dấu ấn của bạn.</p>

<h2>Vì sao văn bản bị đánh giá là AI?</h2>
<p>Bộ phát hiện AI đánh giá đặc điểm thống kê của văn bản. Văn bản do mô hình ngôn ngữ tạo ra thường có:</p>
<ul>
	<li><strong>Từ ngữ “an toàn”, dễ đoán:</strong> các cụm như “đóng vai trò vô cùng quan trọng”, “trong bối cảnh hiện nay”.</li>
	<li><strong>Nhịp câu đều:</strong> câu dài tương đương nhau, cấu trúc lặp lại.</li>
	<li><strong>Nhận định chung chung:</strong> đúng nhưng không gắn với dữ liệu hay bối cảnh cụ thể nào.</li>
	<li><strong>Cấu trúc liệt kê hoàn hảo:</strong> “Thứ nhất… Thứ hai… Thứ ba… Tóm lại…” ở mọi đoạn.</li>
</ul>
<p>Hiểu điều này, bạn sẽ thấy cách chỉnh sửa hiệu quả nhất cũng chính là cách làm bài viết hay hơn: cụ thể hơn, sâu hơn, có giọng riêng hơn.</p>

<figure><img src="{theme}assets/img/blog/giam-ty-le-ai-trung-thuc-steps.svg" alt="5 bước viết lại để bài viết mang dấu ấn cá nhân" width="1200" height="675" loading="lazy"><figcaption>Quy trình viết lại trung thực: từ báo cáo đến bản cuối.</figcaption></figure>

<h2>7 kỹ thuật biên tập trung thực</h2>
<h3>1. Thay nhận định chung bằng dữ liệu của bạn</h3>
<p>Mỗi khi gặp một câu khẳng định chung, hãy hỏi: “Nghiên cứu của mình có số liệu nào minh họa điều này?”. Đưa con số, tỷ lệ, kết quả khảo sát của bạn vào câu.</p>
<div class="example">
	<div class="ex-bad"><b>Trước</b>“Mạng xã hội đóng vai trò ngày càng quan trọng trong việc học tập của sinh viên hiện nay.”</div>
	<div class="ex-good"><b>Sau</b>“Trong 312 sinh viên được khảo sát, 71% cho biết dùng nhóm Facebook của lớp để trao đổi bài tập ít nhất ba lần mỗi tuần - cao hơn hẳn email (18%).”</div>
</div>
<h3>2. Thêm ví dụ từ bối cảnh cụ thể</h3>
<p>Ví dụ về doanh nghiệp, địa phương, ngành nghề mà bạn nghiên cứu là thứ mô hình AI không thể tự biết. Một ví dụ cụ thể giá trị hơn ba câu khái quát.</p>
<h3>3. Nêu quan điểm và phản biện của bạn</h3>
<p>Sau khi trình bày một lý thuyết hay kết quả, hãy thêm nhận định: bạn đồng ý không, áp dụng vào bối cảnh Việt Nam có khác không, hạn chế là gì. Đây là phần thể hiện tư duy của bạn - và cũng là phần hội đồng đánh giá cao nhất.</p>
<h3>4. Thay đổi nhịp câu</h3>
<p>Đan xen câu ngắn với câu dài. Một câu ngắn đặt đúng chỗ có thể nhấn mạnh ý quan trọng. Tránh để mọi câu đều có cùng độ dài và cấu trúc.</p>
<h3>5. Bỏ cụm từ sáo rỗng</h3>
<p>Rà soát và lược bỏ các cụm “bên cạnh đó”, “có thể thấy rằng”, “đóng vai trò vô cùng quan trọng”, “không thể phủ nhận”. Phần lớn câu sẽ rõ nghĩa hơn khi bỏ chúng đi.</p>
<h3>6. Dùng thuật ngữ chính xác của chuyên ngành</h3>
<p>Văn bản AI thường dùng từ phổ thông thay cho thuật ngữ chuyên môn. Thay bằng thuật ngữ đúng ngành và giải thích theo cách bạn hiểu vừa tăng tính học thuật vừa giảm tính khuôn mẫu.</p>
<h3>7. Kết nối các phần trong bài</h3>
<p>Nhắc lại kết quả ở chương trước, đối chiếu với giả thuyết, chỉ ra điểm bất ngờ. Những liên kết này chỉ người hiểu toàn bộ nghiên cứu mới viết được.</p>

<div class="callout callout--warn"><b>Những cách nên tránh</b>Công cụ “humanizer”, xoay từ tự động, chèn lỗi chính tả cố ý, thay ký tự bằng ký tự giống nhau. Chúng làm bài kém chất lượng, có thể bị phát hiện, và bị coi là hành vi che giấu.</div>

<h2>Khi nào cần khai báo việc dùng AI?</h2>
<p>Nhiều trường cho phép dùng AI ở một số khâu (gợi ý ý tưởng, sửa ngữ pháp) với điều kiện khai báo. Nếu bạn đã dùng AI hỗ trợ, hãy đọc quy định và khai báo trung thực trong lời cam đoan hoặc phụ lục. Tham khảo <a href="{home}dung-chatgpt-viet-luan-van/">ranh giới giữa hỗ trợ và vi phạm khi dùng ChatGPT</a>.</p>

<h2>Quy trình gợi ý</h2>
<ol>
	<li>Kiểm tra AI để biết đoạn nào bị đánh dấu (bài tiếng Việt dùng Originality.ai hoặc Copyleaks).</li>
	<li>Áp dụng 7 kỹ thuật trên cho từng đoạn, ưu tiên đoạn dài.</li>
	<li>Đọc lại toàn bài để giọng văn nhất quán.</li>
	<li>Kiểm tra lại và lưu cả báo cáo trước, sau chỉnh sửa.</li>
</ol>

<h2>Kiểm tra lại sau khi chỉnh sửa</h2>
<p>Sau khi biên tập, hãy kiểm tra lại bằng cùng công cụ đã dùng lần đầu để so sánh công bằng. Lưu cả báo cáo trước và sau chỉnh sửa cùng các bản nháp - đây vừa là bằng chứng quá trình làm việc, vừa giúp bạn thấy kỹ thuật nào hiệu quả nhất với bài của mình.</p>

<h2>Câu hỏi thường gặp</h2>
<h3>Viết lại bao nhiêu thì tỷ lệ AI giảm?</h3>
<p>Không có con số cố định. Đoạn được bổ sung dữ liệu và lập luận riêng thường thay đổi rõ rệt; đổi vài từ đồng nghĩa thì gần như không.</p>
<h3>Có dịch vụ hỗ trợ giảm tỷ lệ AI hợp lệ không?</h3>
<p>Có, khi đó là biên tập nội dung thật sự: viết lại bằng chi tiết cụ thể, giữ nguyên ý và số liệu của bạn, tuân thủ quy định của trường.</p>
<h3>Mục tiêu tỷ lệ AI nên là bao nhiêu?</h3>
<p>Phần lớn trường chưa đặt ngưỡng cứng. Nhiều người chọn dưới 20% làm mốc an toàn; xem <a href="{home}chon-muc-tieu-ty-le-dao-van-ai/">cách chọn mục tiêu tỷ lệ phù hợp</a>.</p>

<blockquote>Không đủ thời gian tự biên tập? <a href="{home}dat-hang/?tab=reduce&amp;goal=ai">Đặt giảm tỷ lệ AI</a> - bạn tự chọn mức mục tiêu, biên tập viên chuyên ngành thực hiện.</blockquote>
HTML
		,
	),

	/* ---------------------------------------------------------- 37 */
	array(
		'slug'    => 'chuan-bi-file-truoc-khi-kiem-tra-dao-van',
		'cat'     => 'huong-dan',
		'cover'   => 'chuan-bi-file-truoc-khi-kiem-tra-dao-van-check-dao-van.jpg',
		'tags'    => array( 'chuẩn bị file kiểm tra đạo văn', 'định dạng file', 'số từ tối thiểu', 'Turnitin' ),
		'title'   => 'Chuẩn bị file trước khi kiểm tra đạo văn: Định dạng, dung lượng, số từ và lỗi thường gặp',
		'excerpt' => 'File sai định dạng, quá ít từ hay PDF scan có thể khiến kết quả kiểm tra sai lệch hoặc không chạy được. Checklist chuẩn bị file: .docx, .pdf, .txt, .rtf, dưới 80 MB, 300-30.000 từ khi dùng Turnitin/iThenticate, và lưu ý ngôn ngữ khi kiểm tra AI.',
		'content' => <<<'HTML'
<div class="tldr"><b>Tóm tắt nhanh</b><ul>
	<li>Định dạng nhận: <strong>.docx, .pdf, .txt, .rtf</strong>. PDF nên xuất trực tiếp từ Word, không dùng bản scan.</li>
	<li>Dung lượng: <strong>mỗi file dưới 80 MB</strong>.</li>
	<li>Số từ (Turnitin, iThenticate): <strong>tối thiểu 300 từ</strong>, tối đa 30.000 từ mỗi đơn. Các phần mềm khác không giới hạn.</li>
	<li>Kiểm tra AI trên Turnitin/iThenticate chỉ áp dụng cho tiếng Anh, tiếng Tây Ban Nha, tiếng Nhật.</li>
</ul></div>

<p>Nhiều trường hợp kết quả kiểm tra đạo văn “lạ” - tỷ lệ 0%, hoặc báo cáo không có nội dung - không phải do bài viết mà do <strong>file gửi kiểm tra</strong>. Một file PDF scan không có lớp chữ, một tài liệu chỉ 200 từ, hay một file Word chứa toàn hộp văn bản có thể khiến phần mềm không đọc được nội dung. Bài viết này là checklist giúp bạn chuẩn bị file đúng chuẩn ngay từ đầu, tránh mất thời gian và chi phí kiểm tra lại.</p>

<figure><img src="{theme}assets/img/blog/chuan-bi-file-truoc-khi-kiem-tra-dao-van-cards.svg" alt="Quy định file khi gửi kiểm tra đạo văn: định dạng, dung lượng, số từ, ngôn ngữ" width="1200" height="675" loading="lazy"><figcaption>4 quy định cần nhớ khi gửi file kiểm tra.</figcaption></figure>

<h2>1. Định dạng file được chấp nhận</h2>
<table>
	<thead><tr><th>Định dạng</th><th>Khi nào dùng</th><th>Lưu ý</th></tr></thead>
	<tbody>
		<tr><td>.docx</td><td>Khuyên dùng cho mọi bài viết</td><td>Đọc chính xác nhất, đếm được số từ ngay</td></tr>
		<tr><td>.pdf</td><td>Khi chỉ có bản PDF</td><td>Phải là PDF có chữ (xuất từ Word), không phải ảnh scan</td></tr>
		<tr><td>.txt</td><td>Văn bản thuần, không định dạng</td><td>Lưu với mã hóa UTF-8 để giữ dấu tiếng Việt</td></tr>
		<tr><td>.rtf</td><td>Xuất từ một số phần mềm soạn thảo</td><td>Kiểm tra lại dấu tiếng Việt sau khi xuất</td></tr>
	</tbody>
</table>
<p>File <strong>.doc</strong> (Word đời cũ) không được nhận trực tiếp - hãy mở bằng Word và lưu lại thành .docx. Với dịch vụ giảm tỷ lệ, file bản gốc bắt buộc là .docx để biên tập viên chỉnh sửa trực tiếp, kèm báo cáo .pdf.</p>

<div class="callout callout--warn"><b>PDF scan là lỗi phổ biến nhất</b>Nếu bạn không bôi đen được chữ trong file PDF, đó là ảnh scan. Phần mềm sẽ không đọc được nội dung và kết quả gần như 0% - không phản ánh thực tế. Hãy dùng file Word gốc.</div>

<h2>2. Dung lượng: dưới 80 MB</h2>
<p>Mỗi file gửi kiểm tra cần nhỏ hơn 80 MB. Luận văn vượt dung lượng thường do chứa nhiều ảnh độ phân giải cao. Cách giảm:</p>
<ul class="checklist">
	<li>Trong Word: chọn một ảnh → <em>Picture Format → Compress Pictures</em> → bỏ chọn “Apply only to this picture” → chọn độ phân giải 150 ppi.</li>
	<li>Xóa ảnh, phụ lục không cần kiểm tra (nếu trường chỉ kiểm tra chính văn).</li>
	<li>Với PDF: xuất lại từ Word với tùy chọn “Minimum size”.</li>
</ul>

<h2>3. Số từ với Turnitin và iThenticate: tối thiểu 300, tối đa 30.000</h2>
<p>Giới hạn số từ dưới đây chỉ áp dụng khi bạn chọn <strong>Turnitin</strong> hoặc <strong>iThenticate</strong>. Các phần mềm khác (Copyleaks, Originality.ai, GPTZero, DoIT…) không giới hạn số từ.</p>
<p><strong>Tối thiểu 300 từ:</strong> tài liệu quá ngắn không cho kết quả đáng tin cậy, đặc biệt với phát hiện AI - mô hình cần đủ văn bản để đánh giá. Nếu bạn chỉ cần kiểm tra một đoạn ngắn, hãy gộp nhiều phần vào cùng một file.</p>
<p><strong>Tối đa 30.000 từ mỗi đơn:</strong> tương đương khoảng 80-100 trang A4. Luận văn, luận án dài hơn nên tách thành nhiều file theo chương và đặt thành các đơn riêng. Cách này còn giúp bạn đọc báo cáo dễ hơn vì mỗi báo cáo tập trung vào một phần.</p>
<p>Khi bạn tải file .docx, .txt hoặc .rtf lên trang đặt hàng, hệ thống tự đếm số từ và báo ngay nếu file chưa đạt yêu cầu của Turnitin/iThenticate. Với PDF, số từ được đọc trực tiếp trên trình duyệt.</p>

<div class="callout callout--tip"><b>Mẹo đếm số từ trong Word</b>Nhìn ở góc dưới bên trái cửa sổ Word, hoặc vào <em>Review → Word Count</em>. Với tiếng Việt, mỗi âm tiết được tính là một từ.</div>

<h2>4. Ngôn ngữ khi kiểm tra tỷ lệ AI</h2>
<p>Kiểm tra trùng lặp hoạt động với hầu hết ngôn ngữ. Riêng <strong>kiểm tra tỷ lệ AI trên Turnitin và iThenticate chỉ áp dụng cho tài liệu tiếng Anh, tiếng Tây Ban Nha và tiếng Nhật</strong>. Nếu bài của bạn viết bằng tiếng Việt, hãy chọn Originality.ai hoặc Copyleaks để kiểm tra AI. Chi tiết tại <a href="{home}kiem-tra-ty-le-ai-bai-tieng-viet/">kiểm tra tỷ lệ AI cho bài tiếng Việt</a>.</p>

<h2>5. Nội dung nên và không nên đưa vào file</h2>
<ul>
	<li><strong>Nên:</strong> đúng phiên bản bạn định nộp, đầy đủ chính văn.</li>
	<li><strong>Cân nhắc loại bỏ</strong> (nếu trường không kiểm tra): trang bìa, lời cam đoan, mục lục, danh mục tài liệu tham khảo, phụ lục bảng hỏi.</li>
	<li><strong>Không nên:</strong> văn bản trong hộp text box, chữ màu trắng, ký tự đặc biệt chèn giữa chữ - có thể làm phần mềm đọc sai và bị coi là bất thường.</li>
</ul>

<h2>Checklist 1 phút trước khi bấm “Đặt hàng”</h2>
<ul class="checklist">
	<li>File là .docx, .pdf (có chữ), .txt hoặc .rtf.</li>
	<li>Dung lượng dưới 80 MB.</li>
	<li>Từ 300 đến 30.000 từ (nếu dùng Turnitin/iThenticate).</li>
	<li>Đúng phiên bản cuối cùng; tên file dễ nhận biết (ví dụ <em>LuanVan_Chuong2_v5.docx</em>).</li>
	<li>Chọn phần mềm và hình thức phù hợp với ngôn ngữ bài viết.</li>
	<li>Ghi chú thiết lập bộ lọc trường yêu cầu (loại trừ trích dẫn, tài liệu tham khảo…).</li>
</ul>

<h2>6. Đặt tên file và quản lý phiên bản</h2>
<p>Một lỗi tưởng nhỏ nhưng rất hay gặp: kiểm tra nhầm phiên bản. Bạn sửa bài ở file “LuanVan_final.docx”, nhưng lại gửi kiểm tra file “LuanVan_final_moi.docx” từ hôm trước. Kết quả không phản ánh bản bạn sẽ nộp, và bạn có thể phải trả phí kiểm tra lại. Hãy đặt tên file theo quy tắc rõ ràng gồm nội dung, số phiên bản và ngày, ví dụ <em>LuanVan_Chuong2_v5_1810.docx</em>, và chỉ giữ một thư mục “bản đang dùng”.</p>
<p>Sau khi nhận báo cáo, lưu báo cáo cạnh đúng file đã kiểm tra. Khi giảng viên hoặc phòng đào tạo hỏi, bạn có thể đối chiếu ngay bản nào đã được kiểm tra, vào thời điểm nào và với kết quả ra sao.</p>

<h2>Câu hỏi thường gặp</h2>
<h3>File Word có nhiều bảng, hình có ảnh hưởng kết quả không?</h3>
<p>Bảng và hình không ảnh hưởng nhiều; phần mềm chủ yếu so khớp văn bản. Chỉ cần đảm bảo dung lượng dưới 80 MB.</p>
<h3>Tôi chỉ có bản PDF scan thì làm sao?</h3>
<p>Hãy tìm lại file Word gốc. Nếu không có, cần chuyển PDF sang văn bản bằng công cụ nhận dạng chữ (OCR) và kiểm tra kỹ lỗi trước khi gửi.</p>
<h3>Bài hơn 30.000 từ có kiểm tra được không?</h3>
<p>Với Turnitin và iThenticate, hãy tách thành nhiều file theo chương và đặt nhiều đơn. Các phần mềm khác không giới hạn số từ.</p>

<blockquote>File đã sẵn sàng? <a href="{home}dat-hang/">Đặt kiểm tra ngay</a> - hệ thống tự đếm số trang, số từ và báo giá tức thì.</blockquote>
HTML
		,
	),

	/* ---------------------------------------------------------- 38 */
	array(
		'slug'    => 'chon-muc-tieu-ty-le-dao-van-ai',
		'cat'     => 'kien-thuc',
		'cover'   => 'chon-muc-tieu-ty-le-dao-van-ai-check-dao-van.jpg',
		'tags'    => array( 'mục tiêu tỷ lệ đạo văn', 'giảm tỷ lệ đạo văn', 'tỷ lệ AI', 'luận văn' ),
		'title'   => 'Chọn mục tiêu tỷ lệ đạo văn và AI phù hợp: 30%, 20% hay thấp hơn?',
		'excerpt' => 'Nên đặt mục tiêu giảm tỷ lệ đạo văn xuống bao nhiêu? Cách xác định mục tiêu dựa trên quy định của trường, loại bài, biên an toàn và chi phí - kèm bảng mức tham khảo và cách chọn mục tiêu cho tỷ lệ AI.',
		'content' => <<<'HTML'
<div class="tldr"><b>Tóm tắt nhanh</b><ul>
	<li>Mục tiêu nên thấp hơn ngưỡng của trường khoảng 5-10 điểm phần trăm để có biên an toàn.</li>
	<li>Mục tiêu càng thấp, khối lượng chỉnh sửa và chi phí càng tăng - đặt thấp quá mức cần thiết là lãng phí.</li>
	<li>Với tỷ lệ AI, đa số trường chưa có ngưỡng cứng; dưới 20% là mốc phổ biến.</li>
	<li>Tại {company}, bạn tự nhập mục tiêu (nhỏ hơn tỷ lệ hiện tại) và thấy giá ngay.</li>
</ul></div>

<p>Khi đặt dịch vụ giảm tỷ lệ đạo văn hoặc tự chỉnh sửa bài, câu hỏi đầu tiên là: <strong>nên giảm xuống bao nhiêu?</strong> Chọn mục tiêu quá cao, bạn có nguy cơ vẫn vượt ngưỡng khi nộp chính thức. Chọn quá thấp, bạn tốn thêm thời gian, chi phí và có thể làm bài bị biến đổi nhiều hơn cần thiết. Bài viết này giúp bạn xác định mục tiêu hợp lý cho cả tỷ lệ đạo văn và tỷ lệ AI.</p>

<h2>Bước 1: Xác định ngưỡng chính thức</h2>
<p>Mục tiêu phải bắt đầu từ quy định của nơi bạn nộp bài: quy chế đào tạo của trường, hướng dẫn của khoa, hoặc chính sách của tạp chí. Hãy ghi lại ba thông tin: <strong>phần mềm</strong> dùng để kiểm tra, <strong>ngưỡng tối đa</strong> và <strong>các phần được loại trừ</strong> (trích dẫn, tài liệu tham khảo, phụ lục). Xem thêm <a href="{home}ty-le-dao-van-bao-nhieu-la-dat/">tỷ lệ đạo văn bao nhiêu là đạt</a>.</p>

<figure><img src="{theme}assets/img/blog/chon-muc-tieu-ty-le-dao-van-ai-bars.svg" alt="Mức tỷ lệ đạo văn thường gặp theo loại bài" width="1200" height="675" loading="lazy"><figcaption>Mức tỷ lệ thường gặp theo loại bài - luôn đối chiếu quy định của trường.</figcaption></figure>

<h2>Bước 2: Cộng thêm biên an toàn</h2>
<p>Kết quả giữa các lần kiểm tra có thể dao động vài phần trăm vì cơ sở dữ liệu liên tục được cập nhật, thiết lập bộ lọc có thể khác nhau, và bản nộp cuối có thể được chỉnh sửa thêm. Vì vậy, mục tiêu nên thấp hơn ngưỡng chính thức <strong>5-10 điểm phần trăm</strong>.</p>
<table>
	<thead><tr><th>Ngưỡng của trường</th><th>Mục tiêu khuyến nghị</th><th>Ghi chú</th></tr></thead>
	<tbody>
		<tr><td>Dưới 30%</td><td>20-25%</td><td>Khóa luận, tiểu luận</td></tr>
		<tr><td>Dưới 25%</td><td>15-20%</td><td>Nhiều chương trình thạc sĩ</td></tr>
		<tr><td>Dưới 20%</td><td>12-15%</td><td>Luận văn thạc sĩ, luận án</td></tr>
		<tr><td>Dưới 15%</td><td>8-10%</td><td>Luận án tiến sĩ, bài báo</td></tr>
	</tbody>
</table>

<div class="callout callout--info"><b>Đừng quên nguồn đơn lẻ</b>Ngoài tỷ lệ tổng, nhiều hội đồng và tạp chí xem cả nguồn lớn nhất. Khi đặt mục tiêu, hãy yêu cầu xử lý triệt để các nguồn chiếm tỷ lệ lớn thay vì chỉ hạ con số tổng.</div>

<h2>Bước 3: Cân nhắc chi phí và khối lượng chỉnh sửa</h2>
<p>Khối lượng chỉnh sửa không tăng tuyến tính. Đưa một bài từ 45% xuống 30% chủ yếu là xử lý các đoạn trùng dài, rõ ràng. Nhưng từ 20% xuống 10% đòi hỏi viết lại cả những đoạn trùng rải rác, thuật ngữ, cụm từ thông dụng - tốn công hơn nhiều. Vì vậy giá dịch vụ cũng tăng dần khi mục tiêu thấp hơn:</p>
[vs_bang_gia_giam]
<p>Mục tiêu hợp lý là mục tiêu <em>đủ an toàn</em>, không phải mục tiêu thấp nhất có thể.</p>

<h2>Chọn mục tiêu cho tỷ lệ AI</h2>
<p>Khác với trùng lặp, đa số trường hiện chưa đặt ngưỡng cứng cho tỷ lệ AI; báo cáo AI thường được dùng làm tín hiệu để giảng viên trao đổi với sinh viên. Một số gợi ý:</p>
<ul class="checklist">
	<li>Nếu trường có quy định cụ thể - đặt mục tiêu thấp hơn quy định 5-10 điểm.</li>
	<li>Nếu chưa có quy định - dưới 20% là mốc phổ biến, nhiều người chọn để yên tâm.</li>
	<li>Kiểm tra AI bằng công cụ hỗ trợ đúng ngôn ngữ của bài (Turnitin/iThenticate: tiếng Anh, Tây Ban Nha, Nhật).</li>
	<li>Quan trọng hơn con số: bài viết phải thể hiện rõ đóng góp và lập luận của bạn.</li>
</ul>
<p>Khi cả tỷ lệ đạo văn và AI đều cao, chọn giảm cả hai cùng lúc thường tiết kiệm hơn đặt riêng, vì biên tập viên xử lý đồng thời trên cùng một bản. Xem <a href="{home}giam-ty-le-ai-trung-thuc/">cách giảm tỷ lệ AI trung thực</a> nếu bạn muốn tự chỉnh sửa.</p>

<h2>Ví dụ chọn mục tiêu</h2>
<div class="example">
	<div class="ex-bad"><b>Chưa hợp lý</b>Luận văn thạc sĩ, trường yêu cầu dưới 20%, hiện 42%. Chọn mục tiêu 19% - sát ngưỡng, dễ vượt khi nộp chính thức.</div>
	<div class="ex-good"><b>Hợp lý</b>Cùng trường hợp, chọn mục tiêu 15% cho đạo văn và 20% cho AI. Có biên an toàn 5 điểm, chi phí vừa phải.</div>
</div>

<h2>Cách đặt mục tiêu tại {company}</h2>
<ol>
	<li>Vào <a href="{home}dat-hang/?tab=reduce">trang đặt giảm tỷ lệ</a>, chọn giảm <strong>đạo văn</strong>, <strong>tỷ lệ AI</strong> hoặc <strong>cả hai</strong>.</li>
	<li>Nhập tỷ lệ hiện tại theo báo cáo và <strong>mục tiêu mong muốn</strong> (phải nhỏ hơn tỷ lệ hiện tại), hoặc bấm chọn nhanh 30%, 20%, 15%, 10%.</li>
	<li>Giá được tính lại ngay khi bạn thay đổi mục tiêu.</li>
	<li>Nếu đặt từ một đơn kiểm tra đã có kết quả, bạn không cần tải lại file và được giảm thêm phí.</li>
</ol>

<div class="callout callout--tip"><b>Mẹo</b>Khi kết quả kiểm tra của bạn trên 20%, ngay cạnh thẻ kết quả sẽ có gợi ý đặt giảm tỷ lệ với các thông số đã điền sẵn - bạn chỉ cần điều chỉnh mục tiêu.</div>

<h2>Những sai lầm khi chọn mục tiêu</h2>
<ul>
	<li><strong>Chọn sát ngưỡng:</strong> mục tiêu 29% cho ngưỡng 30% gần như không có biên an toàn.</li>
	<li><strong>Chọn theo phần mềm khác:</strong> đạt 15% trên một công cụ chưa chắc đạt trên phần mềm trường dùng.</li>
	<li><strong>Chỉ nhìn tỷ lệ tổng:</strong> bỏ qua một nguồn đơn lẻ chiếm 8-10% có thể khiến bài bị đánh giá nặng.</li>
	<li><strong>Quên tỷ lệ AI:</strong> chỉ giảm đạo văn trong khi trường cũng xem báo cáo AI, phải làm thêm một lần nữa.</li>
	<li><strong>Đặt mục tiêu quá thấp:</strong> tốn chi phí, thời gian và có thể khiến văn phong bị thay đổi nhiều hơn mong muốn.</li>
</ul>

<h2>Câu hỏi thường gặp</h2>
<h3>Có thể đặt mục tiêu 0% không?</h3>
<p>Về kỹ thuật là có thể nhập, nhưng không cần thiết và rất tốn kém. Bài nghiên cứu nào cũng có thuật ngữ, tên riêng, trích dẫn trùng với nguồn khác.</p>
<h3>Nếu không đạt mục tiêu thì sao?</h3>
<p>Mục tiêu là mức tham khảo. Nếu bản bàn giao chưa đạt trên cùng phần mềm và thiết lập, dịch vụ biên tập bổ sung miễn phí hoặc hoàn phí theo chính sách.</p>
<h3>Mục tiêu tính trên phần mềm nào?</h3>
<p>Kết quả được xác nhận bằng báo cáo của phần mềm kiểm tra sau chỉnh sửa - nên chọn đúng phần mềm mà trường bạn sử dụng.</p>

<blockquote>Đã xác định được mục tiêu? <a href="{home}dat-hang/?tab=reduce">Đặt giảm tỷ lệ đạo văn + AI</a> - tự chọn mức mong muốn, xem giá ngay.</blockquote>
HTML
		,
	),
);
