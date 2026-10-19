<?php
/**
 * Bài viết 43-46: Zotero, dịch tài liệu nước ngoài, kiểm tra miễn phí và trả phí, trả lời hội đồng (>1000 từ/bài, có ảnh).
 * {home} = trang chủ, {theme} = thư mục theme, {company} = tên thương hiệu.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(

	/* ---------------------------------------------------------- 43 */
	array(
		'slug'    => 'huong-dan-dung-zotero-quan-ly-trich-dan',
		'cat'     => 'huong-dan',
		'cover'   => 'huong-dan-dung-zotero-quan-ly-trich-dan-check-dao-van.jpg',
		'tags'    => array( 'Zotero', 'quản lý tài liệu tham khảo', 'trích dẫn tự động', 'phần mềm trích dẫn' ),
		'title'   => 'Hướng dẫn dùng Zotero quản lý tài liệu và trích dẫn tự động trong Word từ A đến Z',
		'excerpt' => 'Zotero là phần mềm miễn phí giúp lưu tài liệu, chèn trích dẫn và tạo danh mục tài liệu tham khảo tự động trong Word theo APA, IEEE, Harvard. Hướng dẫn cài đặt, thu thập nguồn, tổ chức thư viện và các mẹo tránh lỗi trích dẫn.',
		'content' => <<<'HTML'
<div class="tldr"><b>Tóm tắt nhanh</b><ul>
	<li>Zotero miễn phí, chạy trên Windows, macOS, Linux; có tiện ích cho trình duyệt và plugin cho Word, LibreOffice, Google Docs.</li>
	<li>Một cú nhấp trên trình duyệt là lưu đủ thông tin tài liệu; chèn trích dẫn trong Word chỉ mất vài giây.</li>
	<li>Đổi chuẩn trích dẫn (APA, IEEE, Harvard…) cho cả bài chỉ bằng một lần chọn.</li>
	<li>Dùng Zotero từ đầu giúp bạn không bỏ sót nguồn - nguyên nhân phổ biến của đạo văn vô ý.</li>
</ul></div>

<p>Bạn đã bao giờ mất cả buổi tối để sửa lại danh mục tài liệu tham khảo vì khoa yêu cầu đổi từ APA sang IEEE? Hay nhớ mang máng đã đọc ý này ở đâu đó nhưng không tìm lại được nguồn? Đó là những lý do khiến các nhà nghiên cứu dùng phần mềm quản lý tài liệu. Trong số đó, <strong>Zotero</strong> được ưa chuộng vì miễn phí, mã nguồn mở và đủ mạnh cho mọi bậc học. Bài viết này hướng dẫn bạn bắt đầu với Zotero và áp dụng ngay cho khóa luận, luận văn.</p>

<figure><img src="{theme}assets/img/blog/huong-dan-dung-zotero-quan-ly-trich-dan-steps.svg" alt="5 bước dùng Zotero: cài đặt, thu thập, tổ chức, trích dẫn trong Word, tạo danh mục" width="1200" height="675" loading="lazy"><figcaption>Quy trình 5 bước làm việc với Zotero từ lúc tìm tài liệu đến khi có danh mục tài liệu tham khảo.</figcaption></figure>

<h2>Bước 1: Cài đặt Zotero và tiện ích trình duyệt</h2>
<p>Tải Zotero từ trang chính thức zotero.org, cài như phần mềm thông thường. Tiếp theo cài <strong>Zotero Connector</strong> cho trình duyệt bạn dùng (Chrome, Edge, Firefox, Safari). Khi cài Zotero bản máy tính, plugin cho Microsoft Word thường được cài tự động; nếu chưa thấy tab Zotero trong Word, vào phần cài đặt của Zotero để cài lại plugin.</p>
<p>Bạn nên tạo tài khoản Zotero miễn phí và bật đồng bộ. Thư viện sẽ được sao lưu, dùng được trên nhiều máy - rất hữu ích khi bạn làm luận văn ở cả nhà và thư viện trường.</p>

<h2>Bước 2: Thu thập tài liệu</h2>
<p>Có nhiều cách đưa tài liệu vào Zotero:</p>
<ul>
	<li><strong>Từ trình duyệt:</strong> khi đang ở trang bài báo trên Google Scholar, ScienceDirect, cổng tạp chí trong nước…, bấm biểu tượng Zotero Connector để lưu thông tin (và file PDF nếu có quyền truy cập).</li>
	<li><strong>Bằng mã định danh:</strong> bấm nút cây đũa thần trong Zotero, dán DOI hoặc ISBN, phần mềm tự điền thông tin.</li>
	<li><strong>Kéo thả file PDF:</strong> Zotero cố gắng nhận diện thông tin từ file; hãy kiểm tra lại cho chính xác.</li>
	<li><strong>Nhập tay:</strong> với giáo trình, luận văn, văn bản pháp luật tiếng Việt, bạn thường phải nhập thủ công các trường tác giả, năm, nhà xuất bản.</li>
</ul>
<div class="callout callout--tip"><b>Mẹo với tên tiếng Việt</b>Zotero mặc định tách tên thành họ và tên theo kiểu phương Tây. Với tác giả Việt Nam, bấm vào biểu tượng chuyển sang chế độ “một ô” (single field) và nhập đầy đủ họ tên để trích dẫn hiển thị đúng, ví dụ (Nguyễn Văn An, 2022).</div>

<h2>Bước 3: Tổ chức thư viện</h2>
<p>Thư viện gọn gàng giúp bạn tìm lại nguồn nhanh khi viết. Một cách tổ chức đơn giản:</p>
<ul>
	<li>Tạo một <strong>Collection</strong> cho luận văn, bên trong chia theo chương hoặc chủ đề (lý thuyết, phương pháp, bối cảnh Việt Nam…).</li>
	<li>Dùng <strong>Tag</strong> để đánh dấu trạng thái như “đã đọc”, “quan trọng”, “cần trích”.</li>
	<li>Ghi <strong>Note</strong> ngay trong Zotero: tóm tắt ý chính bằng lời của bạn và số trang. Đây là thói quen giúp phần viết sau này ít trùng lặp, vì bạn diễn đạt từ ghi chú của mình chứ không chép từ bản gốc.</li>
</ul>

<h2>Bước 4: Chèn trích dẫn trong Word</h2>
<p>Mở file luận văn, vào tab <strong>Zotero</strong>, bấm <em>Add/Edit Citation</em>. Lần đầu, Zotero hỏi chuẩn trích dẫn: chọn đúng chuẩn khoa yêu cầu, ví dụ “American Psychological Association 7th edition” hoặc “IEEE”. Sau đó gõ tên tác giả hoặc từ khóa, chọn tài liệu, nhấn Enter - trích dẫn được chèn vào vị trí con trỏ.</p>
<p>Muốn thêm số trang cho trích dẫn nguyên văn, bấm vào tài liệu trong hộp tìm kiếm và điền trường “Page”. Muốn gộp nhiều nguồn trong một ngoặc, tiếp tục gõ và chọn thêm trước khi nhấn Enter.</p>

<h2>Bước 5: Tạo danh mục tài liệu tham khảo</h2>
<p>Đặt con trỏ ở cuối bài, bấm <em>Add/Edit Bibliography</em>. Zotero tạo danh mục gồm đúng những tài liệu đã trích trong bài, định dạng theo chuẩn đã chọn. Khi bạn thêm hoặc xóa trích dẫn, bấm <em>Refresh</em> để cập nhật. Nếu khoa yêu cầu đổi chuẩn, bấm <em>Document Preferences</em> và chọn chuẩn mới - toàn bộ trích dẫn và danh mục đổi theo.</p>
<p>Với yêu cầu tách tài liệu tiếng Việt và tiếng nước ngoài, bạn có thể tạo danh mục, chuyển thành văn bản thường ở bản cuối cùng (<em>Unlink Citations</em>) rồi sắp xếp lại thủ công. Chỉ làm bước này khi bài đã hoàn chỉnh, và nhớ lưu một bản còn liên kết để sửa sau.</p>

<h2>Những lỗi thường gặp và cách tránh</h2>
<ul class="checklist">
	<li><strong>Thông tin tài liệu sai hoặc thiếu</strong> do lấy tự động từ trang web: luôn kiểm tra tác giả, năm, tên tạp chí.</li>
	<li><strong>Sửa tay trích dẫn trong Word:</strong> lần Refresh sau sẽ mất phần sửa; hãy sửa trong Zotero.</li>
	<li><strong>Hai người làm chung file nhưng khác thư viện:</strong> dùng Group Library để cùng chia sẻ nguồn.</li>
	<li><strong>Danh mục không được phần mềm kiểm tra nhận diện:</strong> giữ đề mục “Tài liệu tham khảo” hoặc “References” rõ ràng để bộ lọc loại trừ hoạt động.</li>
</ul>

<h2>Zotero và việc kiểm tra đạo văn</h2>
<p>Zotero không tự giảm tỷ lệ trùng lặp, nhưng giúp bạn làm đúng hai việc quan trọng: không bỏ sót nguồn và trình bày danh mục chuẩn. Khi nộp kiểm tra, phần mềm như Turnitin có thể loại trừ danh mục tài liệu tham khảo nếu được thiết lập, nên danh mục định dạng đúng sẽ giúp kết quả phản ánh chính xác phần nội dung. Đọc thêm <a href="{home}cach-doc-bao-cao-turnitin/">cách đọc báo cáo Turnitin</a> để hiểu từng phần của báo cáo.</p>
<blockquote>Trích dẫn xong và muốn kiểm tra lần cuối? <a href="{home}dat-hang/?tab=check">Đặt kiểm tra Turnitin không lưu bài</a>, nhận báo cáo PDF trong tài khoản.</blockquote>

<h2>Câu hỏi thường gặp</h2>
<h3>Zotero có miễn phí hoàn toàn không?</h3>
<p>Phần mềm miễn phí. Bộ nhớ đồng bộ trực tuyến có dung lượng miễn phí giới hạn cho file PDF; thông tin trích dẫn đồng bộ không giới hạn.</p>
<h3>Zotero có dùng với Google Docs được không?</h3>
<p>Có, thông qua Zotero Connector trên trình duyệt Chrome hoặc Firefox.</p>
<h3>Nên chọn Zotero hay Mendeley?</h3>
<p>Cả hai đều tốt. Zotero miễn phí, mã nguồn mở và linh hoạt với chuẩn trích dẫn; hãy chọn công cụ mà bạn và giảng viên hướng dẫn thấy thuận tiện.</p>
HTML
		,
	),

	/* ---------------------------------------------------------- 44 */
	array(
		'slug'    => 'dich-tai-lieu-nuoc-ngoai-co-phai-dao-van',
		'cat'     => 'kien-thuc',
		'cover'   => 'dich-tai-lieu-nuoc-ngoai-co-phai-dao-van-check-dao-van.jpg',
		'tags'    => array( 'đạo văn dịch thuật', 'dịch tài liệu tiếng Anh', 'trích dẫn tài liệu nước ngoài', 'Copyleaks' ),
		'title'   => 'Dịch tài liệu tiếng Anh sang tiếng Việt có phải đạo văn? Cách dùng nguồn nước ngoài đúng',
		'excerpt' => 'Dịch nguyên văn tài liệu nước ngoài rồi đưa vào luận văn mà không ghi nguồn vẫn là đạo văn, dù phần mềm có thể không phát hiện. Phân biệt 4 tình huống thường gặp và cách sử dụng nguồn nước ngoài trung thực, hiệu quả.',
		'content' => <<<'HTML'
<div class="tldr"><b>Tóm tắt nhanh</b><ul>
	<li>Dịch ý tưởng, lập luận hay đoạn văn của người khác mà không ghi nguồn là đạo văn, bất kể ngôn ngữ.</li>
	<li>Một số phần mềm đã có khả năng phát hiện trùng lặp xuyên ngôn ngữ; “dịch cho phần mềm không thấy” không còn an toàn.</li>
	<li>Dịch và ghi nguồn đầy đủ là hợp lệ, nhưng nên tổng hợp và phân tích thay vì dịch cả trang.</li>
	<li>Trích nguyên văn bản dịch cần ghi rõ nguồn gốc và nên giữ ngắn.</li>
</ul></div>

<p>Nguồn tài liệu tiếng Anh phong phú hơn rất nhiều so với tiếng Việt, nên việc đọc và sử dụng nghiên cứu nước ngoài là điều tất yếu khi làm luận văn. Tuy nhiên, có một hiểu lầm khá phổ biến: “Dịch từ tiếng Anh sang tiếng Việt thì không bị coi là đạo văn vì phần mềm không phát hiện được”. Hiểu lầm này sai ở cả hai vế - về nguyên tắc học thuật lẫn về công nghệ. Bài viết giải thích vì sao và hướng dẫn cách dùng nguồn nước ngoài đúng.</p>

<h2>1. Vì sao dịch không ghi nguồn vẫn là đạo văn?</h2>
<p>Đạo văn là việc trình bày ý tưởng, lập luận, câu chữ hoặc kết quả của người khác như thể của mình. Ngôn ngữ chỉ là hình thức thể hiện. Khi bạn dịch một đoạn phân tích của tác giả nước ngoài và đưa vào luận văn mà không ghi nguồn, người đọc sẽ hiểu đó là suy nghĩ của bạn - đây chính là bản chất của đạo văn. Nhiều tài liệu về liêm chính học thuật gọi đây là <em>translation plagiarism</em> hay <em>cross-language plagiarism</em>.</p>
<p>Điều này cũng áp dụng cho việc dịch bằng công cụ như Google Dịch hay ChatGPT: dù công cụ dịch câu chữ, ý tưởng gốc vẫn là của tác giả ban đầu.</p>

<h2>2. Phần mềm có phát hiện được không?</h2>
<p>Các phần mềm so khớp truyền thống so sánh văn bản cùng ngôn ngữ, nên một đoạn dịch có thể không hiện trùng với bản gốc tiếng Anh. Nhưng tình hình đang thay đổi:</p>
<ul>
	<li>Một số công cụ như Copyleaks đã giới thiệu tính năng phát hiện trùng lặp xuyên ngôn ngữ, so sánh ý nghĩa thay vì chỉ so câu chữ.</li>
	<li>Nhiều bản dịch tiếng Việt của cùng một tài liệu nổi tiếng đã được đăng trên mạng hoặc trong luận văn khác, nên bản dịch của bạn vẫn có thể trùng với những bản đó.</li>
	<li>Giảng viên và phản biện đọc nhiều tài liệu chuyên ngành; văn phong “dịch” khác hẳn phần còn lại của bài rất dễ nhận ra.</li>
</ul>
<p>Quan trọng hơn, không bị phần mềm phát hiện không có nghĩa là không vi phạm. Đạo văn có thể bị phát hiện cả sau khi bảo vệ, khi luận văn được công bố và đọc rộng rãi. Tham khảo thêm <a href="{home}kiem-tra-dao-van-copyleaks/">Copyleaks phát hiện diễn đạt lại và trùng lặp đa ngôn ngữ</a>.</p>

<figure><img src="{theme}assets/img/blog/dich-tai-lieu-nuoc-ngoai-co-phai-dao-van-cards.svg" alt="4 tình huống dùng tài liệu nước ngoài: hợp lệ và không hợp lệ" width="1200" height="675" loading="lazy"><figcaption>Bốn tình huống thường gặp khi sử dụng tài liệu nước ngoài và cách đánh giá.</figcaption></figure>

<h2>3. Bốn tình huống thường gặp</h2>
<h3>Tình huống 1: Dịch nguyên đoạn, không ghi nguồn</h3>
<p>Đây là đạo văn rõ ràng. Dù chỉ là một đoạn ngắn trong phần cơ sở lý thuyết, hãy luôn ghi nguồn.</p>
<h3>Tình huống 2: Dịch nguyên đoạn, có ghi nguồn</h3>
<p>Về nguyên tắc là hợp lệ, nhưng không phải cách viết tốt. Một luận văn gồm nhiều đoạn dịch nối tiếp nhau cho thấy ít đóng góp của tác giả. Nếu thực sự cần giữ nguyên ý một câu quan trọng, hãy trích dẫn ngắn, ghi rõ tác giả, năm, số trang và nên nêu đây là bản dịch của bạn (ví dụ: “bản dịch của tác giả luận văn”).</p>
<h3>Tình huống 3: Tổng hợp ý từ nhiều nguồn nước ngoài, có ghi nguồn</h3>
<p>Đây là cách làm tốt nhất. Bạn đọc nhiều nghiên cứu, rút ra điểm chung và điểm khác biệt, rồi viết bằng lời của mình kèm trích dẫn cho từng ý. Phần này vừa hợp lệ, vừa thể hiện năng lực phân tích.</p>
<h3>Tình huống 4: Dùng thang đo, mô hình đã được dịch</h3>
<p>Trong nghiên cứu định lượng, việc dịch thang đo từ nghiên cứu nước ngoài là phổ biến. Hãy ghi rõ nguồn gốc thang đo, cách bạn dịch và hiệu chỉnh (ví dụ dịch xuôi - dịch ngược, tham khảo ý kiến chuyên gia), và trích dẫn nghiên cứu đã sử dụng thang đo đó tại Việt Nam nếu có.</p>

<h2>4. Cách dùng nguồn nước ngoài hiệu quả</h2>
<ol>
	<li><strong>Đọc hiểu trước, viết sau:</strong> đọc toàn bộ đoạn hoặc bài, gấp tài liệu lại và ghi chú ý chính bằng tiếng Việt theo cách bạn hiểu.</li>
	<li><strong>Ghi nguồn ngay khi ghi chú:</strong> lưu tài liệu vào phần mềm quản lý như <a href="{home}huong-dan-dung-zotero-quan-ly-trich-dan/">Zotero</a> để không quên nguồn.</li>
	<li><strong>Đối chiếu nhiều nguồn:</strong> so sánh quan điểm của các tác giả thay vì dựa vào một tài liệu duy nhất.</li>
	<li><strong>Liên hệ với bối cảnh Việt Nam:</strong> phân tích xem kết quả nước ngoài có phù hợp với điều kiện trong nước không - đây là phần đóng góp riêng của bạn.</li>
	<li><strong>Giữ thuật ngữ nhất quán:</strong> lần đầu dùng thuật ngữ dịch, có thể ghi kèm thuật ngữ gốc trong ngoặc.</li>
</ol>

<h2>5. Còn dùng công cụ dịch và AI thì sao?</h2>
<p>Dùng công cụ dịch để <em>hiểu</em> tài liệu là hoàn toàn bình thường. Vấn đề chỉ nảy sinh khi bạn sao chép bản dịch máy vào luận văn như văn bản của mình. Ngoài vấn đề nguồn gốc ý tưởng, văn bản dịch hoặc viết lại bằng công cụ AI còn có thể bị công cụ phát hiện AI đánh dấu. Hãy tuân thủ quy định của trường về sử dụng AI và luôn viết lại bằng hiểu biết của mình. Xem thêm <a href="{home}dung-chatgpt-viet-luan-van/">ranh giới giữa hỗ trợ hợp lệ và vi phạm khi dùng ChatGPT</a>.</p>
<blockquote>Muốn biết bài có trùng với nguồn trên web và các bản dịch đã công bố? <a href="{home}dat-hang/?tab=check">Kiểm tra bằng Copyleaks hoặc Turnitin</a> trước khi nộp.</blockquote>

<h2>Câu hỏi thường gặp</h2>
<h3>Dịch một đoạn ngắn có cần ghi nguồn không?</h3>
<p>Có. Bất kỳ ý tưởng, số liệu, lập luận nào không phải của bạn đều cần ghi nguồn, không phụ thuộc độ dài hay ngôn ngữ.</p>
<h3>Dịch sách nước ngoài làm giáo trình tham khảo có được không?</h3>
<p>Việc dịch và phát hành tác phẩm còn liên quan đến quyền tác giả. Trong luận văn, bạn chỉ nên trích dẫn và phân tích, không đưa vào bản dịch dài của tác phẩm.</p>
<h3>Phần mềm nào phát hiện đạo văn xuyên ngôn ngữ?</h3>
<p>Một số công cụ như Copyleaks đã có tính năng này. Tuy vậy, cách an toàn nhất vẫn là trích dẫn đầy đủ thay vì trông chờ phần mềm không phát hiện.</p>
HTML
		,
	),

	/* ---------------------------------------------------------- 45 */
	array(
		'slug'    => 'kiem-tra-dao-van-mien-phi-va-tra-phi',
		'cat'     => 'cong-cu',
		'cover'   => 'kiem-tra-dao-van-mien-phi-va-tra-phi-check-dao-van.jpg',
		'tags'    => array( 'kiểm tra đạo văn miễn phí', 'check đạo văn free', 'Turnitin', 'so sánh công cụ' ),
		'title'   => 'Kiểm tra đạo văn miễn phí và trả phí: Khác nhau thế nào, khi nào nên dùng loại nào?',
		'excerpt' => 'Công cụ kiểm tra đạo văn miễn phí tiện nhưng khác xa phần mềm trường dùng về cơ sở dữ liệu, độ chính xác và bảo mật. So sánh 5 khác biệt quan trọng và cách kết hợp hợp lý để tiết kiệm mà vẫn an toàn khi nộp bài.',
		'content' => <<<'HTML'
<div class="tldr"><b>Tóm tắt nhanh</b><ul>
	<li>Công cụ miễn phí chủ yếu so khớp với trang web công khai; phần mềm của trường còn so với kho bài nộp, luận văn và ấn phẩm khoa học.</li>
	<li>Kết quả miễn phí thấp không đảm bảo kết quả trên Turnitin, DoIT hay iThenticate cũng thấp.</li>
	<li>Một số công cụ miễn phí có thể lưu hoặc dùng lại văn bản bạn dán vào; hãy đọc điều khoản.</li>
	<li>Hợp lý nhất: dùng công cụ miễn phí khi đang viết, kiểm tra bằng đúng phần mềm của trường trước khi nộp.</li>
</ul></div>

<p>Tìm “kiểm tra đạo văn miễn phí” trên mạng sẽ cho ra hàng chục công cụ. Chúng tiện lợi, không cần đăng ký, kết quả có ngay. Vậy vì sao nhiều sinh viên vẫn bị báo tỷ lệ cao khi nộp chính thức, dù đã “check free” và thấy chỉ vài phần trăm? Câu trả lời nằm ở sự khác biệt về cơ sở dữ liệu, thuật toán và mục đích của từng loại công cụ. Bài viết giúp bạn hiểu rõ để chọn đúng công cụ cho từng giai đoạn.</p>

<figure><img src="{theme}assets/img/blog/kiem-tra-dao-van-mien-phi-va-tra-phi-cards.svg" alt="So sánh công cụ kiểm tra đạo văn miễn phí và phần mềm trả phí" width="1200" height="675" loading="lazy"><figcaption>Bốn khác biệt chính giữa công cụ miễn phí và phần mềm kiểm tra mà trường, tạp chí sử dụng.</figcaption></figure>

<h2>1. Cơ sở dữ liệu: khác biệt lớn nhất</h2>
<p>Phần mềm kiểm tra chỉ phát hiện được trùng lặp với những gì có trong cơ sở dữ liệu của nó. Công cụ miễn phí thường dựa vào kết quả tìm kiếm web công khai. Trong khi đó:</p>
<ul>
	<li><strong>Turnitin</strong> so khớp với web, kho bài đã nộp qua hệ thống của rất nhiều trường và các ấn phẩm học thuật.</li>
	<li><strong>iThenticate</strong> tập trung vào các bài báo, sách đã xuất bản của nhiều nhà xuất bản khoa học.</li>
	<li><strong>DoIT</strong> có cơ sở dữ liệu tài liệu tiếng Việt, gồm luận văn, luận án và tài liệu của nhiều cơ sở đào tạo trong nước.</li>
</ul>
<p>Một đoạn bạn dùng từ luận văn của khóa trước có thể không xuất hiện trên web công khai, nên công cụ miễn phí báo “sạch”, nhưng phần mềm của trường lại tìm thấy ngay. Đây là nguyên nhân phổ biến nhất của các bất ngờ khi nộp bài.</p>

<h2>2. Độ chính xác và khả năng phát hiện diễn đạt lại</h2>
<p>Công cụ miễn phí thường so khớp chuỗi từ giống hệt. Các phần mềm chuyên dụng phát hiện được cả đoạn được thay từ đồng nghĩa, đảo cấu trúc câu ở mức độ nhất định. Ngoài ra, báo cáo chuyên dụng cho biết chính xác nguồn nào, tỷ lệ bao nhiêu, tô màu từng đoạn - đủ để bạn sửa đúng chỗ thay vì đoán.</p>

<h2>3. Bảo mật và quyền với văn bản của bạn</h2>
<p>Khi dán luận văn vào một trang miễn phí, văn bản của bạn đi đâu? Một số dịch vụ ghi rõ có thể lưu, phân tích hoặc dùng văn bản để cải thiện sản phẩm; một số không nói gì. Rủi ro là tài liệu chưa công bố của bạn bị lưu lại hoặc xuất hiện ở nơi khác, khiến bài trùng với chính nó khi nộp. Với luận văn, luận án hay bản thảo bài báo, hãy ưu tiên dịch vụ cam kết rõ về bảo mật và chế độ không lưu bài. Đọc thêm <a href="{home}bao-mat-tai-lieu-khi-kiem-tra-dao-van/">8 điều cần biết về bảo mật tài liệu khi kiểm tra đạo văn</a>.</p>

<h2>4. Giới hạn độ dài và định dạng</h2>
<p>Đa số công cụ miễn phí giới hạn vài trăm đến vài nghìn từ mỗi lần, không nhận file Word hoặc PDF, không có báo cáo để lưu. Với một luận văn hàng chục nghìn từ, bạn sẽ phải cắt thành nhiều lần dán, mất thời gian mà vẫn không có cái nhìn tổng thể.</p>

<h2>5. Kiểm tra AI miễn phí</h2>
<p>Tương tự, nhiều trang cho kiểm tra AI miễn phí với giới hạn từ. Kết quả giữa các công cụ có thể chênh lệch lớn, đặc biệt với tiếng Việt. Nếu trường yêu cầu báo cáo AI của một phần mềm cụ thể, kết quả từ công cụ khác chỉ mang tính tham khảo. Xem <a href="{home}kiem-tra-ty-le-ai-bai-tieng-viet/">nên chọn công cụ nào để kiểm tra AI bài tiếng Việt</a>.</p>

<h2>Khi nào dùng miễn phí, khi nào trả phí?</h2>
<table>
	<thead><tr><th>Giai đoạn</th><th>Nên dùng</th><th>Lý do</th></tr></thead>
	<tbody>
		<tr><td>Đang viết, kiểm tra từng đoạn</td><td>Công cụ miễn phí</td><td>Phát hiện nhanh đoạn chép từ web, tiết kiệm chi phí</td></tr>
		<tr><td>Hoàn thành bản nháp</td><td>Phần mềm trường dùng</td><td>Biết tỷ lệ gần với thực tế, có báo cáo để sửa</td></tr>
		<tr><td>Sau khi chỉnh sửa</td><td>Kiểm tra lại cùng phần mềm</td><td>Xác nhận đã đạt yêu cầu trước khi nộp</td></tr>
		<tr><td>Gửi tạp chí quốc tế</td><td>iThenticate</td><td>Phần mềm phổ biến của các nhà xuất bản</td></tr>
	</tbody>
</table>

<h2>Cách tiết kiệm chi phí kiểm tra</h2>
<ul class="checklist">
	<li>Chỉ kiểm tra bằng phần mềm trả phí ở những mốc quan trọng thay vì sau mỗi lần sửa nhỏ.</li>
	<li>Chọn đúng phần mềm của trường ngay từ đầu để không phải kiểm tra nhiều lần trên nhiều công cụ.</li>
	<li>Chọn gói đạo văn + AI nếu trường yêu cầu cả hai, thường rẻ hơn mua lẻ.</li>
	<li>Chuẩn bị file đúng định dạng trước khi gửi để tránh phải kiểm tra lại - xem <a href="{home}chuan-bi-file-truoc-khi-kiem-tra-dao-van/">hướng dẫn chuẩn bị file</a>.</li>
</ul>
<p>Bạn có thể xem giá chi tiết từng phần mềm tại <a href="{home}bang-gia-kiem-tra-dao-van-ai/">bảng giá kiểm tra đạo văn và AI</a>.</p>
<blockquote>Kiểm tra bằng đúng phần mềm trường dùng: <a href="{home}dat-hang/?tab=check">đặt kiểm tra Turnitin, DoIT, iThenticate</a> ở chế độ không lưu bài.</blockquote>

<h2>Câu hỏi thường gặp</h2>
<h3>Công cụ miễn phí báo 5% thì Turnitin có báo 5% không?</h3>
<p>Không chắc. Cơ sở dữ liệu khác nhau nên kết quả có thể chênh lệch nhiều, thường là Turnitin cao hơn với bài dùng nhiều luận văn, tài liệu học thuật.</p>
<h3>Có cách nào kiểm tra Turnitin miễn phí không?</h3>
<p>Turnitin cấp quyền cho cơ sở giáo dục, không có bản miễn phí cho cá nhân. Một số trường cho sinh viên tự nộp kiểm tra qua hệ thống học tập; hãy hỏi khoa trước.</p>
<h3>Dán bài vào công cụ miễn phí có an toàn không?</h3>
<p>Tùy dịch vụ. Hãy đọc điều khoản về việc lưu trữ văn bản; với tài liệu quan trọng chưa công bố, nên dùng dịch vụ cam kết không lưu bài.</p>
HTML
		,
	),

	/* ---------------------------------------------------------- 46 */
	array(
		'slug'    => 'tra-loi-hoi-dong-ve-ty-le-trung-lap-ai',
		'cat'     => 'huong-dan',
		'cover'   => 'tra-loi-hoi-dong-ve-ty-le-trung-lap-ai-check-dao-van.jpg',
		'tags'    => array( 'bảo vệ luận văn', 'hội đồng chấm luận văn', 'tỷ lệ trùng lặp', 'tỷ lệ AI' ),
		'title'   => 'Bảo vệ luận văn: Cách trả lời hội đồng khi được hỏi về tỷ lệ trùng lặp và AI',
		'excerpt' => 'Hội đồng hỏi về một nguồn trùng lớn hay đoạn văn bị báo AI cao? Chuẩn bị trước bằng cách đọc kỹ báo cáo, lưu bằng chứng quá trình viết và trả lời trung thực theo 5 bước. Kèm các câu hỏi thường gặp và gợi ý cách trả lời.',
		'content' => <<<'HTML'
<div class="tldr"><b>Tóm tắt nhanh</b><ul>
	<li>Đọc kỹ báo cáo trùng lặp và AI của chính bài mình trước buổi bảo vệ; biết rõ nguồn lớn nhất là gì.</li>
	<li>Chuẩn bị bằng chứng quá trình viết: bản nháp theo phiên bản, ghi chú, dữ liệu gốc, lịch sử chỉnh sửa.</li>
	<li>Trả lời trung thực, ngắn gọn, dựa trên sự kiện; thừa nhận điểm chưa tốt và nêu cách đã khắc phục.</li>
	<li>Không phủ nhận chung chung hay đổ lỗi cho phần mềm; hãy giải thích bản chất của phần trùng.</li>
</ul></div>

<p>Khi các trường ngày càng chú trọng liêm chính học thuật, câu hỏi về tỷ lệ trùng lặp và tỷ lệ AI xuất hiện thường xuyên hơn trong buổi bảo vệ. Với nhiều học viên, đây là câu hỏi khiến họ lúng túng nhất, dù bài thực sự do mình viết. Tin tốt là bạn hoàn toàn có thể chuẩn bị trước. Bài viết hướng dẫn cách đọc báo cáo của chính mình, chuẩn bị bằng chứng và trả lời hội đồng một cách bình tĩnh, trung thực.</p>

<figure><img src="{theme}assets/img/blog/tra-loi-hoi-dong-ve-ty-le-trung-lap-ai-steps.svg" alt="5 bước chuẩn bị trả lời hội đồng về tỷ lệ trùng lặp và AI" width="1200" height="675" loading="lazy"><figcaption>Năm bước chuẩn bị trước buổi bảo vệ để trả lời câu hỏi về trùng lặp và AI.</figcaption></figure>

<h2>Bước 1: Đọc kỹ báo cáo của chính bài mình</h2>
<p>Đừng chỉ nhớ con số tổng. Hãy mở báo cáo và trả lời được các câu hỏi: ba nguồn chiếm tỷ lệ lớn nhất là gì? Phần trùng nằm ở chương nào? Đó là đoạn trích dẫn có ghi nguồn, thuật ngữ chuyên ngành, câu chữ hành chính lặp lại, hay một đoạn viết quá sát tài liệu gốc? Với báo cáo AI, đoạn nào được đánh dấu và vì sao có thể như vậy? Tham khảo <a href="{home}cach-doc-bao-cao-turnitin/">cách đọc báo cáo Turnitin</a> và <a href="{home}cach-doc-bao-cao-ai-turnitin/">cách đọc báo cáo AI của Turnitin</a>.</p>

<h2>Bước 2: Phân loại phần trùng</h2>
<p>Không phải phần trùng nào cũng giống nhau. Hãy chia thành các nhóm:</p>
<ul>
	<li><strong>Trùng hợp lý:</strong> trích dẫn có ngoặc kép và nguồn, tên văn bản pháp luật, thuật ngữ, tên thang đo, câu chữ chuẩn trong phần phương pháp.</li>
	<li><strong>Trùng do thiếu sót:</strong> đoạn diễn đạt lại quá sát hoặc quên ghi nguồn - cần thừa nhận và nói rõ đã chỉnh sửa thế nào.</li>
	<li><strong>Trùng với chính mình:</strong> bài báo bạn đã công bố từ kết quả luận văn - cần chứng minh bằng trích dẫn và đối chiếu quy định của trường về <a href="{home}tu-dao-van-la-gi/">tự đạo văn</a>.</li>
</ul>

<h2>Bước 3: Chuẩn bị bằng chứng quá trình viết</h2>
<p>Đây là phần nhiều học viên bỏ qua nhưng lại có giá trị nhất, đặc biệt khi bị hỏi về AI. Hãy tập hợp:</p>
<ul class="checklist">
	<li>Các bản nháp theo thời gian (lịch sử phiên bản trong Word, Google Docs hoặc các file lưu riêng).</li>
	<li>Ghi chú đọc tài liệu, đề cương, góp ý của giảng viên hướng dẫn qua các lần.</li>
	<li>Dữ liệu gốc: bảng hỏi, file dữ liệu, kết quả chạy phân tích, biên bản phỏng vấn.</li>
	<li>Báo cáo kiểm tra trước và sau khi chỉnh sửa nếu có.</li>
</ul>
<p>Những tài liệu này cho thấy bài viết được hình thành qua một quá trình thật, điều mà một con số trên báo cáo không thể hiện được.</p>

<h2>Bước 4: Luyện cách trả lời theo cấu trúc</h2>
<p>Một câu trả lời tốt thường có ba phần: <strong>sự kiện</strong> (phần trùng là gì), <strong>giải thích</strong> (vì sao trùng, đã ghi nguồn chưa) và <strong>hành động</strong> (đã hoặc sẽ chỉnh sửa thế nào). Ví dụ:</p>
<p><em>Câu hỏi:</em> “Nguồn số 1 trùng 6% là luận văn của trường khác, em giải thích thế nào?”</p>
<p><em>Gợi ý trả lời:</em> “Dạ, phần trùng nằm ở mục 1.2 về khái niệm động lực làm việc. Em có tham khảo luận văn này và đã trích dẫn ở trang 15, nhưng một số câu em diễn đạt còn sát bản gốc. Sau khi nhận báo cáo, em đã viết lại phần này theo hướng tổng hợp từ ba nguồn và bổ sung trích dẫn; em xin gửi hội đồng bản đã chỉnh sửa và báo cáo kiểm tra lại.”</p>
<p>Câu trả lời này trung thực, cụ thể và cho thấy trách nhiệm - thường được đánh giá cao hơn nhiều so với việc nói “em không đạo văn” mà không có giải thích.</p>

<h2>Bước 5: Khi được hỏi về tỷ lệ AI</h2>
<p>Công cụ phát hiện AI cho kết quả mang tính xác suất và có thể báo nhầm, đặc biệt với văn phong học thuật khuôn mẫu hoặc ngôn ngữ chưa được hỗ trợ tốt. Tuy nhiên, đừng mở đầu bằng việc phủ nhận giá trị của công cụ. Hãy:</p>
<ol>
	<li>Trình bày trung thực bạn đã dùng công cụ AI hay chưa và dùng vào việc gì (ví dụ kiểm tra ngữ pháp, gợi ý từ khóa tìm kiếm), đối chiếu với quy định của trường.</li>
	<li>Đưa ra bằng chứng quá trình viết đã chuẩn bị ở bước 3.</li>
	<li>Chỉ ra những nội dung chỉ bạn mới viết được: dữ liệu khảo sát riêng, phân tích bối cảnh đơn vị, nhận định cá nhân.</li>
	<li>Sẵn sàng giải thích sâu bất kỳ đoạn nào hội đồng chỉ ra - đây là cách thuyết phục nhất rằng bạn hiểu và làm chủ nội dung.</li>
</ol>
<p>Đọc thêm <a href="{home}bai-tu-viet-bi-bao-ai-cao/">bài tự viết bị Turnitin báo AI cao: nguyên nhân và cách xử lý</a>.</p>

<h2>Những điều nên tránh</h2>
<ul class="checklist">
	<li>Đổ lỗi hoàn toàn cho phần mềm hoặc cho người khác.</li>
	<li>Trả lời vòng vo, không trả lời đúng câu hỏi.</li>
	<li>Khẳng định điều không có bằng chứng, ví dụ “em không dùng bất kỳ công cụ nào” trong khi có dùng.</li>
	<li>Tranh luận gay gắt về con số; hãy tập trung vào nội dung và quá trình.</li>
</ul>

<h2>Chuẩn bị từ sớm là cách tốt nhất</h2>
<p>Cách tốt nhất để buổi bảo vệ suôn sẻ là xử lý vấn đề trước khi nộp: tự kiểm tra bằng đúng phần mềm của trường, đọc kỹ báo cáo, chỉnh sửa trung thực và lưu lại báo cáo trước - sau. Khi đó, nếu hội đồng hỏi, bạn đã có sẵn câu trả lời và bằng chứng.</p>
<blockquote>Kiểm tra trước khi nộp: <a href="{home}dat-hang/?tab=check">đặt kiểm tra đạo văn và AI</a> ở chế độ không lưu bài, nhận báo cáo PDF để chuẩn bị cho buổi bảo vệ.</blockquote>

<h2>Câu hỏi thường gặp</h2>
<h3>Hội đồng có được xem báo cáo trùng lặp của em không?</h3>
<p>Thường là có. Nhiều trường gửi kèm báo cáo cho hội đồng hoặc yêu cầu học viên nộp kèm. Hãy chắc chắn bạn đã đọc báo cáo trước họ.</p>
<h3>Nên mang theo bằng chứng gì khi bảo vệ?</h3>
<p>Báo cáo kiểm tra, các bản nháp quan trọng, dữ liệu gốc và kết quả phân tích. Bạn không cần trình chiếu tất cả, chỉ cần sẵn sàng khi được hỏi.</p>
<h3>Nếu thừa nhận có đoạn viết chưa tốt thì có bị đánh trượt không?</h3>
<p>Kết quả phụ thuộc mức độ và quy định của trường. Tuy nhiên, thái độ trung thực kèm hướng khắc phục cụ thể thường được hội đồng đánh giá tích cực hơn việc che giấu.</p>
HTML
		,
	),
);
