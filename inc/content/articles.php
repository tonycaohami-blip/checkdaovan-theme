<?php
/**
 * Bài viết 1-5 (bản 2: ≈800+ từ, chuẩn SEO). {home} được thay bằng địa chỉ trang chủ khi nhập.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(

	/* ---------------------------------------------------------- 1 */
	array(
		'slug'    => 'dao-van-la-gi',
		'cat'     => 'kien-thuc',
		'tags'    => array( 'đạo văn là gì', 'liêm chính học thuật', 'hình thức đạo văn' ),
		'title'   => 'Đạo văn là gì? 6 hình thức đạo văn phổ biến và cách tránh cho sinh viên',
		'excerpt' => 'Đạo văn là gì, vì sao bài tự viết vẫn có thể bị coi là đạo văn? Giải thích dễ hiểu 6 hình thức đạo văn phổ biến trong học thuật và cách phòng tránh hiệu quả.',
		'content' => <<<'HTML'
<div class="tldr"><b>Tóm tắt nhanh</b><ul>
	<li>Đạo văn là dùng ý tưởng, câu chữ, số liệu của người khác mà không ghi nhận nguồn phù hợp.</li>
	<li>Có ít nhất 6 hình thức: sao chép nguyên văn, diễn đạt lại không trích dẫn, ghép mảnh, tự đạo văn, trích dẫn sai và dùng AI không khai báo.</li>
	<li>Tỷ lệ trùng lặp trên phần mềm không đồng nghĩa với đạo văn - cần đọc kỹ báo cáo.</li>
	<li>Cách phòng tránh tốt nhất: ghi nguồn ngay khi đọc tài liệu và kiểm tra trước khi nộp.</li>
</ul></div>

<p>Trong môi trường đại học, <strong>đạo văn</strong> là một trong những vi phạm bị xử lý nghiêm khắc nhất: bài có thể bị trừ điểm, bị yêu cầu viết lại, thậm chí bị hủy kết quả bảo vệ. Điều đáng nói là phần lớn sinh viên vi phạm <strong>không cố ý</strong> - họ đơn giản không biết rằng cách mình viết đang bị coi là đạo văn. Bài viết này giúp bạn hiểu đúng khái niệm, nhận diện 6 hình thức thường gặp và biết cách phòng tránh ngay từ khi bắt đầu viết.</p>

<h2>Đạo văn là gì?</h2>
<p>Đạo văn (plagiarism) là việc sử dụng ý tưởng, câu chữ, số liệu, hình ảnh hoặc kết quả nghiên cứu của người khác mà <strong>không ghi nhận nguồn một cách phù hợp</strong>, khiến người đọc hiểu rằng đó là sản phẩm của chính mình. Điểm mấu chốt nằm ở cụm từ “ghi nhận nguồn”: bạn hoàn toàn được phép dùng tài liệu của người khác - đó là bản chất của nghiên cứu khoa học - miễn là trích dẫn đầy đủ và đúng quy cách.</p>
<p>Nói cách khác, cùng một đoạn văn có thể là <em>trích dẫn hợp lệ</em> hoặc <em>đạo văn</em>, chỉ khác nhau ở chỗ bạn có ghi rõ tác giả, năm và nguồn hay không.</p>

<h2>6 hình thức đạo văn phổ biến</h2>

<h3>1. Sao chép nguyên văn (direct plagiarism)</h3>
<p>Copy một đoạn văn từ sách, bài báo, website rồi dán vào bài mà không đặt trong ngoặc kép, không ghi nguồn. Đây là dạng dễ bị phần mềm phát hiện nhất, vì câu chữ khớp hoàn toàn với nguồn gốc.</p>

<h3>2. Diễn đạt lại nhưng không trích dẫn</h3>
<p>Nhiều bạn nghĩ chỉ cần đổi vài từ đồng nghĩa là “thành của mình”. Thực tế, dù đã viết lại câu chữ, <em>ý tưởng</em> vẫn thuộc về tác giả gốc và vẫn phải trích dẫn. Các công cụ hiện đại như Copyleaks còn có khả năng nhận diện đoạn văn được diễn đạt lại. Xem thêm <a href="{home}paraphrase-dung-chuan-hoc-thuat/">cách paraphrase đúng chuẩn học thuật</a>.</p>

<h3>3. Đạo văn ghép mảnh (mosaic plagiarism)</h3>
<p>Lấy câu này từ nguồn A, câu kia từ nguồn B, đan xen với vài câu tự viết. Kiểu này thường xuất hiện ở phần tổng quan tài liệu và khiến báo cáo Turnitin có rất nhiều nguồn nhỏ lẻ, mỗi nguồn 1-2%.</p>

<h3>4. Tự đạo văn (self-plagiarism)</h3>
<p>Dùng lại tiểu luận, báo cáo hay bài báo cũ của chính mình mà không xin phép giảng viên hoặc không trích dẫn. Nếu bài cũ từng được nộp qua hệ thống có lưu bài, phần mềm sẽ báo trùng ngay. Xem chi tiết tại bài <a href="{home}tu-dao-van-la-gi/">Tự đạo văn là gì</a>.</p>

<h3>5. Trích dẫn sai hoặc trích dẫn “ma”</h3>
<p>Ghi nguồn không đúng tác giả, sai năm, hoặc dẫn một tài liệu mà mình chưa từng đọc (chỉ chép lại danh mục tham khảo từ bài khác). Phần mềm không phải lúc nào cũng phát hiện được lỗi này, nhưng hội đồng chấm thì có thể.</p>

<h3>6. Dùng AI viết thay mà không khai báo</h3>
<p>Từ năm 2023, nhiều trường bổ sung quy định về công cụ AI tạo sinh. Nộp văn bản do ChatGPT hay Gemini viết như thể tự viết có thể bị xem là vi phạm liêm chính học thuật, tùy quy chế từng trường. Vì vậy nhiều nơi kiểm tra song song cả <strong>tỷ lệ trùng lặp</strong> và <strong>tỷ lệ AI</strong>.</p>

<div class="callout callout--info"><b>Tổng hợp nhanh</b>Dạng 1-3 liên quan đến câu chữ và ý tưởng của người khác; dạng 4 liên quan đến bài của chính bạn; dạng 5-6 liên quan đến sự trung thực trong cách trình bày nguồn và quá trình viết.</div>

<h2>Tỷ lệ trùng lặp không đồng nghĩa với đạo văn</h2>
<p>Một điểm hay bị hiểu nhầm: con số phần trăm trên báo cáo Turnitin là <strong>mức độ tương đồng</strong> với các nguồn trong cơ sở dữ liệu, không phải “phần trăm đạo văn”. Một bài có 25% trùng lặp nhưng toàn bộ là trích dẫn đúng chuẩn, thuật ngữ chuyên ngành và tên văn bản pháp luật vẫn có thể hoàn toàn hợp lệ. Ngược lại, một bài 8% vẫn có thể bị đánh giá là đạo văn nếu một đoạn quan trọng bị sao chép không ghi nguồn. Tìm hiểu thêm cách <a href="{home}cach-doc-bao-cao-turnitin/">đọc báo cáo Turnitin</a>.</p>

<h2>Hậu quả của đạo văn</h2>
<ul>
	<li><strong>Với tiểu luận, bài tập:</strong> bị trừ điểm hoặc nhận điểm 0, phải làm lại.</li>
	<li><strong>Với khóa luận, luận văn:</strong> không đủ điều kiện bảo vệ, phải chỉnh sửa và kiểm tra lại; trường hợp nghiêm trọng có thể bị hủy kết quả.</li>
	<li><strong>Với bài báo khoa học:</strong> bị từ chối, bị rút bài sau khi đăng, ảnh hưởng uy tín lâu dài.</li>
</ul>

<h2>Làm sao để tránh đạo văn?</h2>
<ul class="checklist">
	<li>Ghi chép nguồn ngay khi đọc tài liệu, đừng để đến cuối mới đi tìm lại.</li>
	<li>Đặt trích dẫn nguyên văn trong ngoặc kép và ghi số trang.</li>
	<li>Khi diễn đạt lại, đọc hiểu rồi gấp tài liệu lại và viết theo cách của mình - sau đó vẫn ghi nguồn.</li>
	<li>Dùng phần mềm quản lý tài liệu như Zotero hoặc Mendeley để tạo trích dẫn tự động.</li>
	<li>Khai báo việc sử dụng AI nếu trường yêu cầu.</li>
	<li><strong>Kiểm tra trước khi nộp</strong> bằng đúng phần mềm mà trường sử dụng, ở chế độ không lưu bài.</li>
</ul>

<div class="callout callout--tip"><b>Mẹo</b>Hãy kiểm tra sớm, ít nhất 1-2 tuần trước hạn nộp, để còn thời gian chỉnh sửa những đoạn bị sót trích dẫn.</div>

<h2>Câu hỏi thường gặp</h2>
<h3>Trích dẫn đúng chuẩn có bị tính vào tỷ lệ trùng lặp không?</h3>
<p>Có thể có. Phần mềm vẫn so khớp các đoạn trích dẫn, trừ khi bộ lọc loại trừ trích dẫn được bật. Điều quan trọng là hội đồng sẽ không coi phần trích dẫn đúng chuẩn là đạo văn.</p>
<h3>Đạo văn vô ý có bị xử lý không?</h3>
<p>Phần lớn quy chế không phân biệt cố ý hay vô ý khi xét kết quả. Vì vậy hãy chủ động kiểm tra và sửa trước khi nộp.</p>
<h3>Dịch tài liệu tiếng Anh sang tiếng Việt có phải đạo văn không?</h3>
<p>Nếu dịch ý của tác giả khác mà không ghi nguồn thì vẫn là đạo văn, dù câu chữ đã ở ngôn ngữ khác.</p>

<blockquote>Bạn muốn biết bài của mình đang ở mức nào trước khi nộp? <a href="{home}dat-hang/">Đặt kiểm tra Turnitin không lưu bài</a> - nhận báo cáo PDF gốc kèm tỷ lệ AI.</blockquote>
HTML
		,
	),

	/* ---------------------------------------------------------- 2 */
	array(
		'slug'    => 'cach-doc-bao-cao-turnitin',
		'cat'     => 'huong-dan',
		'tags'    => array( 'cách đọc báo cáo Turnitin', 'Similarity Report', 'Turnitin' ),
		'title'   => 'Cách đọc báo cáo Turnitin: Hiểu đúng chỉ số Similarity và từng phần của báo cáo',
		'excerpt' => 'Similarity 18% nghĩa là gì? Vì sao tổng các nguồn không bằng con số tổng? Hướng dẫn đọc báo cáo Turnitin chi tiết cho người mới, kèm cách xác định đoạn cần sửa.',
		'content' => <<<'HTML'
<div class="tldr"><b>Tóm tắt nhanh</b><ul>
	<li>Similarity Index là tỷ lệ văn bản khớp với cơ sở dữ liệu, không phải “tỷ lệ đạo văn”.</li>
	<li>Ưu tiên xem các nguồn lớn nhất trước - chúng thường chiếm phần lớn tỷ lệ.</li>
	<li>Bộ lọc loại trừ trích dẫn, tài liệu tham khảo có thể làm con số thay đổi đáng kể.</li>
	<li>Báo cáo AI là báo cáo riêng, độc lập với Similarity.</li>
</ul></div>

<p>Nhận được file PDF báo cáo Turnitin với rất nhiều màu sắc và con số, không ít bạn lo lắng mà không biết thực sự bài mình “có vấn đề” ở đâu. Bài viết này hướng dẫn bạn <strong>cách đọc báo cáo Turnitin</strong> một cách chính xác: hiểu từng chỉ số, từng phần của báo cáo và biết chính xác cần sửa đoạn nào.</p>

<h2>Similarity Index là gì?</h2>
<p><strong>Similarity Index</strong> (chỉ số tương đồng, các báo cáo mới gọi là <em>Overall Similarity</em>) là tỷ lệ phần trăm văn bản trong bài của bạn khớp với các nguồn trong cơ sở dữ liệu của Turnitin. Cơ sở dữ liệu này gồm ba nhóm chính:</p>
<ul>
	<li><strong>Internet Sources</strong> - các trang web đang hoặc đã từng công khai.</li>
	<li><strong>Publications</strong> - sách, bài báo khoa học, tạp chí.</li>
	<li><strong>Student Papers</strong> - bài của sinh viên đã nộp vào kho lưu trữ.</li>
</ul>
<p>Mỗi nhóm có tỷ lệ riêng. Vì một đoạn văn có thể khớp với cả web lẫn ấn phẩm, tổng ba con số này thường <strong>lớn hơn</strong> chỉ số chung - điều này hoàn toàn bình thường, không phải lỗi.</p>

<h2>Các phần chính của báo cáo</h2>
<h3>Trang tổng quan</h3>
<p>Hiển thị chỉ số Similarity chung, tỷ lệ theo từng nhóm nguồn và danh sách “Primary Sources” - các nguồn khớp nhiều nhất, xếp theo phần trăm giảm dần. Đây là trang bạn nên đọc đầu tiên.</p>
<h3>Phần văn bản được tô màu</h3>
<p>Mỗi nguồn có một màu và một số thứ tự. Đoạn văn trong bài được tô cùng màu với nguồn mà nó khớp. Nhìn vào đây bạn biết chính xác câu nào cần sửa hoặc cần bổ sung trích dẫn.</p>
<h3>Danh sách nguồn</h3>
<p>Cuối báo cáo là toàn bộ danh sách nguồn kèm phần trăm. Hãy chú ý các nguồn chiếm từ 1% trở lên: đó thường là những đoạn dài bị trùng.</p>

<h2>Các bộ lọc ảnh hưởng đến con số</h2>
<p>Cùng một bài nhưng hai lần kiểm tra có thể cho kết quả khác nhau vì thiết lập bộ lọc:</p>
<table>
	<thead><tr><th>Bộ lọc</th><th>Ý nghĩa</th><th>Ảnh hưởng</th></tr></thead>
	<tbody>
		<tr><td>Exclude quotes</td><td>Bỏ qua các đoạn trong ngoặc kép</td><td>Giảm tỷ lệ nếu bạn trích dẫn nguyên văn nhiều</td></tr>
		<tr><td>Exclude bibliography</td><td>Bỏ qua danh mục tài liệu tham khảo</td><td>Thường giảm vài phần trăm</td></tr>
		<tr><td>Exclude small matches</td><td>Bỏ qua đoạn khớp ngắn (VD dưới 8 từ)</td><td>Loại bớt các cụm từ thông dụng</td></tr>
	</tbody>
</table>
<p>Nhiều trường quy định rõ có bật các bộ lọc này hay không. Khi đặt kiểm tra, hãy ghi chú thiết lập trường yêu cầu để kết quả sát thực tế nhất.</p>

<div class="callout callout--warn"><b>Lưu ý</b>Đừng so sánh hai báo cáo có thiết lập bộ lọc khác nhau. Chênh lệch 5-10% giữa hai lần kiểm tra thường đến từ bộ lọc chứ không phải do bài thay đổi.</div>

<h2>Đọc báo cáo thế nào cho đúng? 4 bước</h2>
<ol>
	<li><strong>Đừng chỉ nhìn con số tổng.</strong> 20% rải rác ở các cụm thuật ngữ khác hẳn 20% đến từ một nguồn duy nhất.</li>
	<li><strong>Xem nguồn lớn nhất trước.</strong> Nếu một nguồn chiếm 8-10%, gần như chắc chắn có một đoạn dài bị sao chép.</li>
	<li><strong>Phân loại đoạn trùng:</strong> trích dẫn hợp lệ, thuật ngữ/tên riêng không thể thay đổi, hay đoạn cần viết lại.</li>
	<li><strong>Kiểm tra các phần theo mẫu</strong> như lời cam đoan, mục lục, phụ lục - có thể được loại trừ theo quy định trường.</li>
</ol>

<h2>Ví dụ đọc một báo cáo</h2>
<p>Giả sử báo cáo của bạn có Similarity 27%, trong đó nguồn số 1 là một luận văn cùng trường chiếm 9%, nguồn số 2 là một trang web chiếm 4%, còn lại là hơn 40 nguồn nhỏ dưới 1%. Ưu tiên của bạn là đoạn khớp với nguồn số 1: chỉ cần viết lại và trích dẫn đúng đoạn đó, tỷ lệ có thể giảm gần 9 điểm phần trăm. Các nguồn nhỏ thường là cụm thuật ngữ, khó và không cần sửa hết.</p>

<div class="callout callout--tip"><b>Mẹo</b>Đánh dấu các đoạn cần sửa ngay trên file PDF, sau đó sửa trực tiếp trong file Word theo thứ tự nguồn lớn đến nhỏ.</div>

<h2>Còn tỷ lệ AI thì sao?</h2>
<p>Turnitin có báo cáo riêng cho việc phát hiện văn bản do AI viết (AI writing detection), tách biệt hoàn toàn với Similarity. Một bài có thể 5% trùng lặp nhưng tỷ lệ AI cao, hoặc ngược lại. Xem thêm bài <a href="{home}phat-hien-noi-dung-ai-turnitin/">Turnitin phát hiện AI như thế nào</a>.</p>

<h2>Câu hỏi thường gặp</h2>
<h3>Similarity bao nhiêu thì an toàn?</h3>
<p>Tùy quy định từng trường và loại bài. Tham khảo bài <a href="{home}ty-le-dao-van-bao-nhieu-la-dat/">tỷ lệ đạo văn bao nhiêu là đạt</a> để biết các mức thường gặp.</p>
<h3>Vì sao tổng các nguồn lớn hơn con số Similarity?</h3>
<p>Một đoạn văn có thể khớp với nhiều nguồn cùng lúc, nhưng chỉ được tính một lần trong chỉ số tổng.</p>
<h3>Có cần sửa hết mọi đoạn được tô màu không?</h3>
<p>Không. Hãy ưu tiên các đoạn dài, các nguồn lớn và những đoạn chưa có trích dẫn.</p>

<blockquote>Nhận báo cáo Turnitin gốc kèm tỷ lệ AI: <a href="{home}dat-hang/?tab=check&amp;tool=turnitin&amp;mode=combo">đặt kiểm tra combo Đạo văn + AI</a>.</blockquote>
HTML
		,
	),

	/* ---------------------------------------------------------- 3 */
	array(
		'slug'    => 'ty-le-dao-van-bao-nhieu-la-dat',
		'cat'     => 'kien-thuc',
		'tags'    => array( 'tỷ lệ đạo văn bao nhiêu là đạt', 'luận văn', 'khóa luận' ),
		'title'   => 'Tỷ lệ đạo văn bao nhiêu là đạt? Mức thường gặp với khóa luận, luận văn và bài báo',
		'excerpt' => 'Không có một con số chung cho mọi trường. Tổng hợp các mức tỷ lệ trùng lặp thường được áp dụng cho tiểu luận, khóa luận, luận văn, luận án, bài báo và cách xác định ngưỡng của trường bạn.',
		'content' => <<<'HTML'
<div class="tldr"><b>Tóm tắt nhanh</b><ul>
	<li>Không có ngưỡng chung toàn quốc - mỗi trường, mỗi tạp chí tự quy định.</li>
	<li>Mức thường gặp: khóa luận dưới 25-30%, luận văn thạc sĩ dưới 20-25%, luận án dưới 15-20%.</li>
	<li>Ngoài tỷ lệ tổng, hội đồng còn xem nguồn lớn nhất và phần nào bị trùng.</li>
	<li>Nên chừa biên an toàn 5-10 điểm phần trăm dưới ngưỡng.</li>
</ul></div>

<p>“Bài em 27% thì có được bảo vệ không?” - đây là câu hỏi phổ biến nhất trước mỗi mùa bảo vệ. Câu trả lời ngắn gọn: <strong>tùy quy định của trường và loại bài</strong>. Tuy vậy, có những mức phổ biến mà bạn có thể tham khảo, cùng với những yếu tố quan trọng không kém con số tổng. Bài viết này giúp bạn trả lời câu hỏi <strong>tỷ lệ đạo văn bao nhiêu là đạt</strong> một cách đầy đủ.</p>

<h2>Không có ngưỡng chung trên toàn quốc</h2>
<p>Mỗi cơ sở đào tạo tự ban hành quy định kiểm tra trùng lặp, thường nằm trong quy chế đào tạo hoặc quy định về liêm chính học thuật. Các văn bản này nêu rõ: phần mềm dùng để kiểm tra, các phần được loại trừ (trích dẫn, tài liệu tham khảo, phụ lục…) và ngưỡng tối đa cho từng loại sản phẩm. Vì vậy cùng một con số, bài có thể đạt ở trường này nhưng không đạt ở trường khác.</p>

<h2>Các mức thường gặp</h2>
<table>
	<thead><tr><th>Loại bài</th><th>Ngưỡng thường gặp</th><th>Ghi chú</th></tr></thead>
	<tbody>
		<tr><td>Tiểu luận, báo cáo môn học</td><td>dưới 30% - 40%</td><td>Tùy giảng viên phụ trách</td></tr>
		<tr><td>Khóa luận / đồ án tốt nghiệp</td><td>dưới 25% - 30%</td><td>Thường loại trừ tài liệu tham khảo</td></tr>
		<tr><td>Luận văn thạc sĩ</td><td>dưới 20% - 25%</td><td>Một số trường xét thêm từng chương</td></tr>
		<tr><td>Luận án tiến sĩ</td><td>dưới 15% - 20%</td><td>Yêu cầu nghiêm ngặt nhất</td></tr>
		<tr><td>Bài báo tạp chí quốc tế</td><td>thường dưới 15% - 20%</td><td>Một nguồn đơn lẻ không quá 1-5%</td></tr>
	</tbody>
</table>
<div class="callout callout--warn"><b>Lưu ý</b>Bảng trên chỉ mang tính tham khảo theo kinh nghiệm thực tế. Hãy luôn đối chiếu với văn bản chính thức của trường hoặc hướng dẫn tác giả của tạp chí.</div>

<h2>Ngoài tỷ lệ tổng, cần chú ý điều gì?</h2>
<h3>Tỷ lệ của từng nguồn</h3>
<p>Nhiều hội đồng và tạp chí không chỉ xét con số tổng mà còn xem <strong>nguồn lớn nhất</strong>. Một bài 15% nhưng 12% đến từ một luận văn khác sẽ bị đánh giá nặng hơn bài 22% gồm hàng chục nguồn rất nhỏ.</p>
<h3>Phần nào bị trùng</h3>
<p>Trùng ở phần cơ sở lý thuyết thường được chấp nhận hơn trùng ở phần kết quả nghiên cứu, thảo luận hay kết luận - nơi thể hiện đóng góp của chính tác giả.</p>
<h3>Tỷ lệ AI</h3>
<p>Một số trường đã bắt đầu xem xét thêm báo cáo phát hiện AI. Dù đa số chưa đặt ngưỡng cứng, tỷ lệ AI cao vẫn có thể khiến giảng viên yêu cầu giải trình.</p>

<h2>Làm sao biết chính xác ngưỡng của trường mình?</h2>
<ul class="checklist">
	<li>Đọc quy chế đào tạo hoặc quyết định về kiểm tra trùng lặp trên website trường/khoa.</li>
	<li>Hỏi trực tiếp giảng viên hướng dẫn hoặc phòng đào tạo sau đại học.</li>
	<li>Hỏi khóa trước về phần mềm và bộ lọc thực tế trường sử dụng.</li>
	<li>Với tạp chí, xem mục “Author Guidelines” hoặc “Publication Ethics”.</li>
</ul>

<h2>Lời khuyên: hãy chừa “biên an toàn”</h2>
<p>Kết quả giữa các lần kiểm tra có thể dao động vài phần trăm do cơ sở dữ liệu liên tục được cập nhật. Nếu trường yêu cầu dưới 30%, bạn nên đưa bài về khoảng 20-25% trước khi nộp. Với luận văn thạc sĩ yêu cầu dưới 20%, mục tiêu an toàn là khoảng 12-15%.</p>
<div class="callout callout--tip"><b>Mẹo</b>Kiểm tra bằng đúng phần mềm và đúng thiết lập bộ lọc của trường. Kết quả trên một phần mềm khác chỉ mang tính tham khảo.</div>

<h2>Nếu tỷ lệ vượt ngưỡng thì làm gì?</h2>
<ol>
	<li>Xác định các nguồn lớn nhất trong báo cáo.</li>
	<li>Bổ sung trích dẫn cho các đoạn đã lấy ý nhưng quên ghi nguồn.</li>
	<li>Viết lại theo hướng tổng hợp, phân tích thay vì tóm tắt từng tài liệu.</li>
	<li>Kiểm tra lại sau khi sửa. Tham khảo <a href="{home}cach-giam-ty-le-dao-van-hop-le/">7 cách giảm tỷ lệ đạo văn hợp lệ</a>.</li>
</ol>

<h2>Câu hỏi thường gặp</h2>
<h3>Tỷ lệ 0% có tốt không?</h3>
<p>Rất hiếm và không cần thiết. Bài nghiên cứu nào cũng có thuật ngữ, trích dẫn và tên riêng trùng với nguồn khác. Một tỷ lệ thấp, hợp lý là đủ.</p>
<h3>Trường không quy định ngưỡng thì sao?</h3>
<p>Hãy hỏi giảng viên hướng dẫn. Nếu không có quy định, mức dưới 20-25% thường được coi là an toàn với đa số loại bài.</p>
<h3>Kiểm tra ở ngoài và ở trường có chênh lệch không?</h3>
<p>Có thể chênh vài phần trăm do bộ lọc và thời điểm kiểm tra. Đó là lý do nên chừa biên an toàn.</p>

<blockquote>Bài đang cao hơn ngưỡng? Dịch vụ <a href="{home}giam-ty-le-dao-van/">giảm tỷ lệ đạo văn + AI</a> xuống dưới 30% hoặc dưới 20%, có cam kết kết quả.</blockquote>
HTML
		,
	),

	/* ---------------------------------------------------------- 4 */
	array(
		'slug'    => 'so-sanh-turnitin-ithenticate-copyleaks-originality',
		'cat'     => 'cong-cu',
		'tags'    => array( 'so sánh phần mềm kiểm tra đạo văn', 'Turnitin', 'iThenticate', 'Copyleaks', 'Originality.ai' ),
		'title'   => 'So sánh Turnitin, iThenticate, Copyleaks và Originality.ai: Nên chọn phần mềm nào?',
		'excerpt' => 'Bốn phần mềm kiểm tra đạo văn và AI phổ biến nhất khác nhau ở cơ sở dữ liệu, đối tượng và điểm mạnh. Bảng so sánh chi tiết giúp bạn chọn đúng phần mềm cho bài của mình.',
		'content' => <<<'HTML'
<div class="tldr"><b>Tóm tắt nhanh</b><ul>
	<li>Turnitin: chuẩn của trường đại học, có kho bài nộp của sinh viên.</li>
	<li>iThenticate: dành cho bài báo khoa học và tạp chí.</li>
	<li>Copyleaks: mạnh về đa ngôn ngữ và phát hiện diễn đạt lại.</li>
	<li>Originality.ai: chuyên phát hiện văn bản do AI tạo ra.</li>
</ul></div>

<p>Cùng một bài viết, kiểm tra trên các phần mềm khác nhau có thể cho ra những con số rất khác. Lý do nằm ở <strong>cơ sở dữ liệu</strong> và <strong>thuật toán so khớp</strong> của mỗi công cụ. Bài viết này so sánh chi tiết 4 phần mềm phổ biến nhất hiện nay để bạn chọn đúng công cụ và biết trước kết quả khi nộp bài.</p>

<h2>Turnitin - tiêu chuẩn của trường đại học</h2>
<p>Turnitin được rất nhiều trường đại học trên thế giới và tại Việt Nam sử dụng. Điểm mạnh lớn nhất là kho <strong>bài nộp của sinh viên</strong> (Student Papers) rất lớn - thứ mà các công cụ khác không có - cùng với nguồn Internet và ấn phẩm học thuật. Turnitin cũng tích hợp tính năng phát hiện văn bản do AI viết.</p>
<p><strong>Phù hợp:</strong> tiểu luận, khóa luận, luận văn, luận án nộp cho trường.</p>

<h2>iThenticate - dành cho xuất bản khoa học</h2>
<p>iThenticate cùng công ty phát triển với Turnitin nhưng hướng tới nhà nghiên cứu và nhà xuất bản. Công cụ này là nền tảng của dịch vụ Crossref Similarity Check mà rất nhiều tạp chí khoa học sử dụng để sàng lọc bản thảo. Cơ sở dữ liệu tập trung vào bài báo, kỷ yếu, sách chuyên khảo. Xem thêm <a href="{home}kiem-tra-dao-van-bai-bao-khoa-hoc/">quy trình kiểm tra bài báo trước khi gửi tạp chí</a>.</p>
<p><strong>Phù hợp:</strong> bài báo gửi tạp chí trong nước và quốc tế, luận án tiến sĩ, đề tài nghiên cứu.</p>

<h2>Copyleaks - mạnh về đa ngôn ngữ và diễn đạt lại</h2>
<p>Copyleaks nổi bật ở khả năng so khớp giữa nhiều ngôn ngữ và nhận diện các đoạn văn đã được diễn đạt lại (paraphrase), không chỉ sao chép nguyên văn. Copyleaks cũng có bộ phát hiện nội dung AI riêng.</p>
<p><strong>Phù hợp:</strong> kiểm tra đối chiếu sau khi đã chỉnh sửa, bài có dịch từ tài liệu nước ngoài, nội dung web.</p>

<h2>Originality.ai - chuyên gia phát hiện AI</h2>
<p>Originality.ai ra đời khi nội dung AI bùng nổ và được thiết kế chủ yếu để phát hiện văn bản do mô hình ngôn ngữ tạo ra, đồng thời có kiểm tra trùng lặp với nguồn web. Công cụ có mô hình riêng cho văn bản đa ngôn ngữ, phù hợp với bài tiếng Việt.</p>
<p><strong>Phù hợp:</strong> kiểm tra tỷ lệ AI, bài viết blog, nội dung marketing.</p>

<h2>Bảng so sánh chi tiết</h2>
<table>
	<thead><tr><th></th><th>Turnitin</th><th>iThenticate</th><th>Copyleaks</th><th>Originality.ai</th></tr></thead>
	<tbody>
		<tr><td>Đối tượng chính</td><td>Sinh viên, giảng viên</td><td>Nhà nghiên cứu, tạp chí</td><td>Đa dạng</td><td>Biên tập, nội dung web</td></tr>
		<tr><td>Kho bài sinh viên</td><td>Có</td><td>Không</td><td>Hạn chế</td><td>Không</td></tr>
		<tr><td>Ấn phẩm khoa học</td><td>Có</td><td>Rất mạnh</td><td>Có</td><td>Hạn chế</td></tr>
		<tr><td>Phát hiện diễn đạt lại</td><td>Trung bình</td><td>Trung bình</td><td>Mạnh</td><td>Trung bình</td></tr>
		<tr><td>Phát hiện AI</td><td>Có</td><td>Có</td><td>Có</td><td>Rất mạnh</td></tr>
	</tbody>
</table>

<div class="callout callout--info"><b>Vì sao kết quả khác nhau?</b>Mỗi phần mềm so khớp với kho dữ liệu riêng. Một đoạn trùng với luận văn đã nộp ở trường khác sẽ hiện trên Turnitin nhưng có thể không hiện trên Copyleaks - và ngược lại với các trang web ít phổ biến.</div>

<h2>Gợi ý chọn nhanh</h2>
<ul class="checklist">
	<li><strong>Nộp cho trường:</strong> Turnitin, hình thức combo Đạo văn + AI.</li>
	<li><strong>Gửi tạp chí:</strong> iThenticate.</li>
	<li><strong>Muốn chắc chắn hơn:</strong> sau khi kiểm tra Turnitin, đặt thêm một đơn Copyleaks để đối chiếu các đoạn diễn đạt lại.</li>
	<li><strong>Lo ngại tỷ lệ AI:</strong> Turnitin AI hoặc Originality.ai.</li>
</ul>

<h2>Những hiểu lầm thường gặp</h2>
<h3>“Phần mềm nào cho tỷ lệ thấp nhất thì tốt nhất”</h3>
<p>Sai. Điều quan trọng là kết quả trên phần mềm <em>mà trường hoặc tạp chí sử dụng</em>. Kiểm tra trên công cụ khác chỉ để tham khảo.</p>
<h3>“Turnitin và iThenticate cho kết quả giống nhau”</h3>
<p>Không hẳn. Hai công cụ chung công nghệ nhưng khác kho dữ liệu, đặc biệt iThenticate thường không so với bài nộp của sinh viên.</p>

<div class="callout callout--tip"><b>Mẹo</b>Hỏi giảng viên hoặc xem quy chế để biết chính xác trường dùng phần mềm nào và bật những bộ lọc gì, rồi ghi chú khi đặt kiểm tra.</div>

<h2>Câu hỏi thường gặp</h2>
<h3>Có thể chọn nhiều phần mềm trong một đơn không?</h3>
<p>Mỗi đơn kiểm tra trên 1 phần mềm để báo cáo rõ ràng. Nếu muốn đối chiếu, bạn đặt thêm một đơn riêng cho phần mềm thứ hai.</p>
<h3>Phần mềm nào phát hiện AI tốt nhất?</h3>
<p>Turnitin được nhiều trường tham chiếu; Originality.ai có độ nhạy cao và hỗ trợ tốt văn bản đa ngôn ngữ.</p>

<blockquote>Chọn phần mềm phù hợp và xem giá ngay: <a href="{home}dat-hang/">đặt kiểm tra</a>. Chọn hình thức “Đạo văn + AI” để tiết kiệm hơn.</blockquote>
HTML
		,
	),

	/* ---------------------------------------------------------- 5 */
	array(
		'slug'    => 'phat-hien-noi-dung-ai-turnitin',
		'cat'     => 'ai',
		'tags'    => array( 'Turnitin phát hiện AI', 'AI detection', 'ChatGPT' ),
		'title'   => 'Turnitin phát hiện nội dung AI như thế nào? Hiểu đúng để không lo lắng thừa',
		'excerpt' => 'Báo cáo AI của Turnitin đo điều gì, vì sao bài tự viết vẫn có thể bị gắn cờ, những giới hạn của công nghệ phát hiện AI và bạn nên làm gì khi tỷ lệ AI cao.',
		'content' => <<<'HTML'
<div class="tldr"><b>Tóm tắt nhanh</b><ul>
	<li>Báo cáo AI ước lượng phần văn bản có khả năng do mô hình ngôn ngữ tạo ra.</li>
	<li>Đây là báo cáo riêng, không liên quan đến tỷ lệ trùng lặp.</li>
	<li>Công cụ có thể dương tính giả, đặc biệt với văn bản khuôn mẫu hoặc quá ngắn.</li>
	<li>Hãy lưu lại bằng chứng quá trình viết để chủ động giải trình khi cần.</li>
</ul></div>

<p>Từ khi ChatGPT xuất hiện, các phần mềm kiểm tra học thuật nhanh chóng bổ sung tính năng phát hiện văn bản do AI tạo ra. Turnitin là một trong những đơn vị triển khai sớm nhất cho các trường đại học. Tuy nhiên, rất nhiều người hiểu sai về con số “AI %” trên báo cáo. Bài viết này giải thích <strong>Turnitin phát hiện AI như thế nào</strong>, giới hạn của công nghệ và cách xử lý khi kết quả cao.</p>

<h2>Báo cáo AI đo điều gì?</h2>
<p>Báo cáo AI của Turnitin ước lượng <strong>tỷ lệ phần văn bản có khả năng được tạo bởi mô hình ngôn ngữ lớn</strong> như ChatGPT, Gemini, Claude. Hệ thống chia bài thành từng đoạn, đánh giá từng đoạn rồi tổng hợp thành phần trăm, kèm phần tô sáng các đoạn bị đánh giá là AI.</p>
<p>Đây là báo cáo <strong>riêng biệt</strong> với chỉ số Similarity. Một bài hoàn toàn không trùng lặp vẫn có thể có tỷ lệ AI cao, vì văn bản AI thường là văn bản mới, không sao chép từ đâu.</p>

<h2>Cơ chế hoạt động (đơn giản hóa)</h2>
<p>Văn bản do AI tạo ra có xu hướng chọn từ “dễ đoán” nhất về mặt thống kê và có nhịp câu khá đều. Văn bản do con người viết thường có nhiều biến động hơn: câu dài ngắn đan xen, cách dùng từ cá nhân, đôi khi “lạ” hoặc không hoàn hảo. Bộ phát hiện được huấn luyện để phân biệt hai kiểu phân bố này.</p>

<h2>Những giới hạn cần biết</h2>
<ul>
	<li><strong>Có thể dương tính giả:</strong> văn bản theo văn phong khuôn mẫu (định nghĩa, cơ sở lý thuyết) đôi khi bị đánh giá là AI dù tự viết.</li>
	<li><strong>Bài ngắn kém tin cậy:</strong> công cụ cần độ dài tối thiểu mới đưa ra kết quả; càng ngắn sai số càng lớn.</li>
	<li><strong>Tỷ lệ thấp kém tin cậy hơn:</strong> Turnitin hiển thị các mức thấp khác đi (ví dụ dấu *) để người đọc thận trọng khi đánh giá.</li>
	<li><strong>Không phải bằng chứng duy nhất:</strong> Turnitin khuyến nghị giảng viên dùng báo cáo AI như một tín hiệu để trao đổi với sinh viên, không phải căn cứ duy nhất để kết luận vi phạm.</li>
</ul>
<div class="callout callout--info"><b>Hiểu đúng con số</b>“30% AI” nghĩa là khoảng 30% số đoạn văn được mô hình đánh giá có khả năng do AI tạo ra - không có nghĩa là bạn đã “gian lận 30%”.</div>

<h2>Dùng AI hỗ trợ có bị coi là vi phạm?</h2>
<p>Điều này phụ thuộc vào quy định của trường và môn học. Một số nơi cho phép dùng AI để gợi ý ý tưởng, sửa ngữ pháp nhưng yêu cầu khai báo; một số khác cấm hoàn toàn trong bài đánh giá. Hãy đọc kỹ đề cương môn học hoặc hỏi giảng viên trước khi sử dụng. Đọc thêm: <a href="{home}dung-chatgpt-viet-luan-van/">Dùng ChatGPT viết luận văn: ranh giới giữa hỗ trợ và vi phạm</a>.</p>

<h2>Nên làm gì khi tỷ lệ AI cao?</h2>
<ol>
	<li><strong>Kiểm tra đối chiếu</strong> trên một công cụ khác (ví dụ Originality.ai) để xem kết quả có nhất quán không.</li>
	<li><strong>Xem đoạn nào bị đánh dấu</strong> và viết lại bằng giọng văn của chính mình: thêm ví dụ thực tế, số liệu từ nghiên cứu của bạn, nhận định cá nhân.</li>
	<li><strong>Lưu lại bằng chứng quá trình viết:</strong> bản nháp, lịch sử chỉnh sửa trên Google Docs/Word, ghi chú đọc tài liệu.</li>
	<li><strong>Trao đổi thẳng thắn với giảng viên</strong> nếu bạn tin rằng kết quả là dương tính giả.</li>
</ol>
<div class="callout callout--tip"><b>Mẹo</b>Bật tính năng lưu lịch sử phiên bản ngay từ khi bắt đầu viết. Đây là bằng chứng mạnh nhất cho thấy bài do bạn tự viết qua nhiều giai đoạn.</div>

<h2>So sánh nhanh: tỷ lệ trùng lặp và tỷ lệ AI</h2>
<table>
	<thead><tr><th></th><th>Tỷ lệ trùng lặp</th><th>Tỷ lệ AI</th></tr></thead>
	<tbody>
		<tr><td>Đo điều gì</td><td>Văn bản khớp với nguồn có sẵn</td><td>Khả năng văn bản do AI tạo ra</td></tr>
		<tr><td>Có chỉ ra nguồn</td><td>Có, kèm đường dẫn</td><td>Không, chỉ tô sáng đoạn nghi ngờ</td></tr>
		<tr><td>Độ chắc chắn</td><td>Cao (so khớp trực tiếp)</td><td>Là ước lượng xác suất</td></tr>
	</tbody>
</table>

<h2>Câu hỏi thường gặp</h2>
<h3>Bài tự viết hoàn toàn có thể bị báo AI không?</h3>
<p>Có thể, dù không phổ biến. Xem cách xử lý chi tiết trong bài <a href="{home}bai-tu-viet-bi-bao-ai-cao/">bài tự viết bị Turnitin báo AI cao</a>.</p>
<h3>Dùng công cụ sửa ngữ pháp có làm tăng tỷ lệ AI?</h3>
<p>Các công cụ viết lại cả câu có thể làm văn bản đều và “dễ đoán” hơn. Sửa lỗi chính tả đơn thuần ít ảnh hưởng.</p>

<blockquote>Kiểm tra tỷ lệ AI trước khi nộp: <a href="{home}dat-hang/?tab=check&amp;mode=ai">đặt kiểm tra AI</a> trên Turnitin hoặc Originality.ai.</blockquote>
HTML
		,
	),
);
