<?php
/**
 * Bài viết 39-42: trích dẫn nguồn tiếng Việt, đề cương luận văn, quy định về đạo văn, viết tóm tắt (>1000 từ/bài, có ảnh).
 * {home} = trang chủ, {theme} = thư mục theme, {company} = tên thương hiệu.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(

	/* ---------------------------------------------------------- 39 */
	array(
		'slug'    => 'trich-dan-tai-lieu-tieng-viet-van-ban-phap-luat',
		'cat'     => 'huong-dan',
		'cover'   => 'trich-dan-tai-lieu-tieng-viet-van-ban-phap-luat-check-dao-van.jpg',
		'tags'    => array( 'trích dẫn tài liệu tiếng Việt', 'trích dẫn văn bản pháp luật', 'trích dẫn nguồn Internet', 'APA 7' ),
		'title'   => 'Cách trích dẫn tài liệu tiếng Việt, văn bản pháp luật và nguồn Internet đúng chuẩn',
		'excerpt' => 'Hướng dẫn trích dẫn sách, giáo trình, luận văn tiếng Việt, văn bản quy phạm pháp luật, báo điện tử và trang web theo chuẩn APA 7, kèm ví dụ cụ thể và các lỗi khiến tỷ lệ trùng lặp tăng.',
		'content' => <<<'HTML'
<div class="tldr"><b>Tóm tắt nhanh</b><ul>
	<li>Tên tác giả Việt Nam ghi đầy đủ họ tên, không đảo như tên nước ngoài: (Nguyễn Văn An, 2022).</li>
	<li>Văn bản pháp luật được trích theo tên văn bản và năm ban hành, danh mục ghi đủ số hiệu, cơ quan ban hành, ngày ban hành.</li>
	<li>Nguồn Internet cần tác giả (hoặc tổ chức), năm, tiêu đề, tên trang và đường dẫn; tránh dùng trang không rõ nguồn gốc.</li>
	<li>Trích dẫn đúng không làm tỷ lệ trùng lặp biến mất, nhưng giúp hội đồng thấy rõ đâu là ý của người khác.</li>
</ul></div>

<p>Hầu hết hướng dẫn trích dẫn đang lưu hành được viết cho tài liệu tiếng Anh. Khi làm khóa luận, luận văn ở Việt Nam, bạn lại thường xuyên dùng giáo trình tiếng Việt, luận án của trường, nghị định, thông tư, số liệu của Tổng cục Thống kê hay bài báo điện tử. Những nguồn này có đặc thù riêng: tên người Việt không tách họ và tên như tên phương Tây, văn bản pháp luật có số hiệu, nhiều trang web không ghi tác giả. Bài viết này tổng hợp cách trích dẫn các loại nguồn phổ biến nhất theo tinh thần chuẩn <strong>APA 7</strong> - chuẩn được nhiều trường và tạp chí trong nước chấp nhận - kèm ví dụ bạn có thể áp dụng ngay.</p>
<p>Lưu ý quan trọng: mỗi trường, mỗi khoa có thể ban hành quy định trình bày riêng. Nếu hướng dẫn của trường khác với bài viết này, hãy luôn ưu tiên quy định của trường.</p>

<h2>1. Nguyên tắc chung với tên tác giả Việt Nam</h2>
<p>Tên người Việt có thứ tự họ - tên đệm - tên, và phần “tên” không đứng một mình để nhận diện như họ của người phương Tây. Vì vậy, cách làm phổ biến ở Việt Nam là <strong>ghi đầy đủ họ tên, giữ nguyên thứ tự</strong>, không đảo và không viết tắt.</p>
<ul>
	<li>Trong bài: (Nguyễn Thị Hoa, 2021) hoặc Nguyễn Thị Hoa (2021) cho rằng…</li>
	<li>Hai tác giả: (Trần Minh Đức &amp; Lê Thu Trang, 2020).</li>
	<li>Từ ba tác giả trở lên: (Phạm Quốc Bảo và cộng sự, 2023).</li>
	<li>Danh mục tài liệu tham khảo: tài liệu tiếng Việt thường xếp theo thứ tự ABC của tên tác giả (theo cách trường quy định), tách riêng phần tiếng Việt và tiếng nước ngoài nếu trường yêu cầu.</li>
</ul>
<p>Với tác giả nước ngoài, bạn vẫn dùng họ như bình thường: (Smith, 2019). Điều quan trọng là thống nhất một cách ghi trong toàn bộ bài.</p>

<figure><img src="{theme}assets/img/blog/trich-dan-tai-lieu-tieng-viet-van-ban-phap-luat-cards.svg" alt="Cách trích dẫn 4 loại nguồn phổ biến: sách tiếng Việt, văn bản pháp luật, báo điện tử, trang web tổ chức" width="1200" height="675" loading="lazy"><figcaption>Bốn loại nguồn phổ biến trong luận văn ở Việt Nam và cách trích dẫn tương ứng.</figcaption></figure>

<h2>2. Sách, giáo trình và chương sách</h2>
<p>Sách và giáo trình là nguồn được dùng nhiều nhất ở phần cơ sở lý thuyết. Thông tin cần có gồm tác giả, năm xuất bản, tên sách (in nghiêng), lần tái bản nếu có, và nhà xuất bản.</p>
<ul>
	<li><em>Trong bài:</em> (Nguyễn Văn An, 2022, tr. 45) khi trích nguyên văn; (Nguyễn Văn An, 2022) khi diễn đạt lại.</li>
	<li><em>Danh mục:</em> Nguyễn Văn An. (2022). <em>Giáo trình quản trị nhân lực</em> (tái bản lần 3). Nhà xuất bản Đại học Kinh tế Quốc dân.</li>
</ul>
<p>Với chương trong sách có chủ biên, ghi tác giả chương, tên chương, rồi “Trong” + tên chủ biên (Chủ biên), tên sách và số trang của chương. Nhiều sinh viên chỉ ghi tên chủ biên cho mọi chương, khiến người đọc không tìm lại được đúng phần đã trích.</p>

<h2>3. Luận văn, luận án và đề tài nghiên cứu</h2>
<p>Luận văn, luận án của các trường là nguồn tham khảo rất giá trị nhưng cũng là nguồn trùng lặp hàng đầu trong báo cáo Turnitin hoặc DoIT, vì nhiều trường đã đưa kho luận văn vào cơ sở dữ liệu so khớp. Khi dùng, hãy trích dẫn rõ ràng:</p>
<ul>
	<li><em>Danh mục:</em> Lê Thu Trang. (2020). <em>Ảnh hưởng của chuyển đổi số đến hiệu quả hoạt động của doanh nghiệp nhỏ và vừa tại Hà Nội</em> [Luận văn thạc sĩ, Trường Đại học X].</li>
</ul>
<p>Đặc biệt tránh chép nguyên khung lý thuyết hoặc phần tổng quan của một luận văn khác rồi chỉ thêm một dòng trích dẫn cuối đoạn. Dù có ghi nguồn, một đoạn dài trùng nguyên văn vẫn hiện màu trong báo cáo và có thể bị hội đồng đánh giá là thiếu đóng góp riêng. Cách đúng là tổng hợp nhiều nguồn và viết bằng lời của bạn - xem thêm <a href="{home}cach-viet-tong-quan-tai-lieu-khong-dao-van/">cách viết tổng quan tài liệu không bị đạo văn</a>.</p>

<h2>4. Văn bản quy phạm pháp luật</h2>
<p>Luật, nghị định, thông tư, quyết định xuất hiện nhiều trong các đề tài kinh tế, luật, quản lý công, giáo dục. Đây là nguồn có tác giả là cơ quan ban hành và có số hiệu riêng, nên cách trích dẫn hơi khác sách báo.</p>
<ul>
	<li><em>Trong bài:</em> có thể nêu trực tiếp tên văn bản trong câu, ví dụ “Theo Luật Giáo dục đại học năm 2012 (sửa đổi, bổ sung năm 2018)…”, hoặc ghi (Quốc hội, 2018).</li>
	<li><em>Danh mục:</em> Quốc hội. (2018). <em>Luật số 34/2018/QH14 sửa đổi, bổ sung một số điều của Luật Giáo dục đại học</em>.</li>
	<li><em>Nghị định, thông tư:</em> Chính phủ. (2023). <em>Nghị định số 13/2023/NĐ-CP về bảo vệ dữ liệu cá nhân</em>.</li>
</ul>
<p>Ba lưu ý giúp phần này chính xác hơn. Thứ nhất, luôn kiểm tra văn bản còn hiệu lực hay đã bị thay thế, sửa đổi. Thứ hai, khi trích một điều, khoản cụ thể, hãy ghi rõ “Điều 5, khoản 2” thay vì chỉ ghi tên văn bản. Thứ ba, không chép nguyên văn cả điều luật dài vào bài; chỉ trích phần cần thiết và phân tích ý nghĩa của nó với đề tài.</p>

<h2>5. Báo điện tử, trang web và số liệu thống kê</h2>
<p>Nguồn Internet tiện lợi nhưng dễ bị trích dẫn thiếu. Một trích dẫn web đầy đủ gồm: tác giả (người viết hoặc tổ chức), ngày đăng, tiêu đề bài, tên trang, đường dẫn.</p>
<ul>
	<li><em>Báo điện tử:</em> Hoàng Minh. (2024, ngày 12 tháng 3). Tiêu đề bài báo. <em>Tên báo</em>. https://…</li>
	<li><em>Trang của tổ chức, không ghi tác giả:</em> dùng tên tổ chức làm tác giả, ví dụ (Tổng cục Thống kê, 2024).</li>
	<li><em>Không có ngày:</em> ghi (n.d.) hoặc “không ngày” theo quy định của trường, và thêm ngày truy cập nếu trang có thể thay đổi.</li>
</ul>
<p>Với số liệu thống kê, hãy trích từ nguồn gốc (niên giám, báo cáo chính thức) thay vì trích lại từ một bài báo đã đăng lại con số đó. Trích nguồn gốc vừa chính xác hơn, vừa thể hiện bạn đã kiểm chứng dữ liệu.</p>
<div class="callout callout--tip"><b>Mẹo</b>Tránh trích dẫn Wikipedia, diễn đàn, trang tổng hợp không rõ tác giả hoặc các trang bán tài liệu. Hãy dùng chúng để tìm hướng, rồi lần về nguồn gốc mà trang đó dẫn để trích dẫn.</div>

<h2>6. Trích dẫn gián tiếp (trích lại qua tài liệu khác)</h2>
<p>Đôi khi bạn chỉ đọc được ý của tác giả A thông qua sách của tác giả B. Khi đó không được ghi như thể đã đọc trực tiếp tác giả A. Cách ghi trung thực là: “Theo A (được trích trong B, 2019)…”, và danh mục chỉ liệt kê tài liệu B - tài liệu bạn thực sự đọc. Tuy vậy, hãy hạn chế trích gián tiếp; nếu ý đó quan trọng với đề tài, nên tìm đọc nguồn gốc.</p>

<h2>7. Những lỗi trích dẫn khiến tỷ lệ trùng lặp tăng</h2>
<ul class="checklist">
	<li><strong>Trích nguyên văn quá dài:</strong> ngoặc kép và ghi nguồn đúng nhưng cả trang trích dẫn vẫn hiện màu; nên tóm lược và phân tích.</li>
	<li><strong>Danh mục tài liệu sai định dạng:</strong> khi bật bộ lọc loại trừ tài liệu tham khảo, phần mềm cần nhận diện được đề mục “Tài liệu tham khảo”.</li>
	<li><strong>Chỉ ghi nguồn ở cuối đoạn dài:</strong> người đọc không biết câu nào là ý của ai; ghi nguồn ngay sau ý được dùng.</li>
	<li><strong>Diễn đạt lại quá sát:</strong> chỉ thay vài từ đồng nghĩa vẫn bị so khớp; tham khảo <a href="{home}paraphrase-dung-chuan-hoc-thuat/">5 kỹ thuật paraphrase đúng chuẩn</a>.</li>
	<li><strong>Thiếu thống nhất:</strong> nửa bài theo APA, nửa bài theo IEEE gây khó đọc và dễ bị yêu cầu sửa.</li>
</ul>

<h2>8. Kiểm tra lại trước khi nộp</h2>
<p>Sau khi hoàn thiện trích dẫn, bạn nên tự kiểm tra bằng đúng phần mềm trường dùng để biết đoạn nào còn trùng. Với bài tiếng Việt, nhiều trường dùng DoIT hoặc Turnitin. Kiểm tra ở chế độ <a href="{home}no-repository-la-gi/">không lưu bài</a> để bản nháp không bị lưu vào kho, tránh trùng với chính mình khi nộp chính thức. Khi đọc báo cáo, ưu tiên các nguồn chiếm tỷ lệ lớn và kiểm tra xem đoạn trùng đã có trích dẫn đầy đủ hay chưa.</p>
<blockquote>Cần kiểm tra trước khi nộp? <a href="{home}dat-hang/?tab=check">Đặt kiểm tra Turnitin hoặc DoIT</a>, nhận báo cáo PDF gốc trong tài khoản.</blockquote>

<h2>Câu hỏi thường gặp</h2>
<h3>Tên tác giả Việt Nam có cần đảo họ ra sau không?</h3>
<p>Thông thường không. Cách phổ biến ở Việt Nam là giữ nguyên họ tên đầy đủ. Hãy làm theo hướng dẫn trình bày của trường nếu có quy định khác.</p>
<h3>Trích dẫn văn bản pháp luật có cần ghi trong danh mục tài liệu tham khảo không?</h3>
<p>Có, nếu bạn dùng nội dung của văn bản đó trong bài. Danh mục nên ghi số hiệu, tên văn bản, cơ quan ban hành và năm ban hành.</p>
<h3>Đã ghi nguồn đầy đủ sao báo cáo vẫn hiện trùng?</h3>
<p>Phần mềm so khớp câu chữ, không đánh giá việc ghi nguồn. Đoạn trích nguyên văn vẫn hiện màu; điều quan trọng là tỷ lệ của từng nguồn không quá lớn và hội đồng thấy rõ bạn đã ghi nguồn đúng.</p>
HTML
		,
	),

	/* ---------------------------------------------------------- 40 */
	array(
		'slug'    => 'cach-viet-de-cuong-luan-van',
		'cat'     => 'huong-dan',
		'cover'   => 'cach-viet-de-cuong-luan-van-check-dao-van.jpg',
		'tags'    => array( 'đề cương luận văn', 'đề cương nghiên cứu', 'luận văn thạc sĩ', 'câu hỏi nghiên cứu' ),
		'title'   => 'Cách viết đề cương luận văn thạc sĩ: Cấu trúc 6 phần và những lỗi khiến đề cương bị trả lại',
		'excerpt' => 'Đề cương tốt giúp bạn viết luận văn nhanh hơn và ít phải sửa. Hướng dẫn từng phần: tính cấp thiết, tổng quan, mục tiêu và câu hỏi nghiên cứu, phương pháp, kế hoạch, tài liệu tham khảo, kèm lỗi thường gặp.',
		'content' => <<<'HTML'
<div class="tldr"><b>Tóm tắt nhanh</b><ul>
	<li>Đề cương là bản thiết kế của luận văn: trả lời “nghiên cứu cái gì, vì sao, bằng cách nào, trong bao lâu”.</li>
	<li>Mục tiêu, câu hỏi nghiên cứu và phương pháp phải khớp nhau - đây là điểm hội đồng xét duyệt kiểm tra kỹ nhất.</li>
	<li>Phần tổng quan cần chỉ ra khoảng trống nghiên cứu, không phải tóm tắt lần lượt từng tài liệu.</li>
	<li>Viết bằng lời của bạn ngay từ đề cương giúp luận văn sau này ít trùng lặp hơn rất nhiều.</li>
</ul></div>

<p>Nhiều học viên cao học coi đề cương là thủ tục cần hoàn thành cho xong để được giao đề tài. Thực tế, một đề cương chất lượng là nửa chặng đường của luận văn: nó định hình câu hỏi, chọn phương pháp, khoanh vùng tài liệu và lên lịch cho cả quá trình. Ngược lại, đề cương sơ sài khiến bạn phải viết lại nhiều lần, đổi hướng giữa chừng, và dễ rơi vào tình trạng “gom” nội dung từ các luận văn khác. Bài viết này hướng dẫn viết đề cương luận văn thạc sĩ theo 6 phần cốt lõi mà hầu hết các trường đều yêu cầu, dù tên gọi có thể khác nhau đôi chút.</p>

<figure><img src="{theme}assets/img/blog/cach-viet-de-cuong-luan-van-cards.svg" alt="6 phần cốt lõi của đề cương luận văn thạc sĩ" width="1200" height="675" loading="lazy"><figcaption>Sáu phần cốt lõi của một đề cương luận văn và câu hỏi mà mỗi phần cần trả lời.</figcaption></figure>

<h2>1. Tên đề tài và tính cấp thiết</h2>
<p>Tên đề tài nên nêu rõ <strong>đối tượng</strong>, <strong>phạm vi</strong> và nếu có thể là <strong>mối quan hệ</strong> được nghiên cứu. So sánh hai tên: “Nghiên cứu về động lực làm việc” và “Các yếu tố ảnh hưởng đến động lực làm việc của nhân viên ngân hàng thương mại tại Đà Nẵng”. Tên thứ hai cho người đọc biết ngay bạn nghiên cứu ai, ở đâu, xem xét điều gì.</p>
<p>Phần tính cấp thiết trả lời câu hỏi: vì sao vấn đề này đáng nghiên cứu lúc này? Hãy kết hợp ba lớp lý do: thực tiễn (vấn đề đang xảy ra, có số liệu minh chứng), lý thuyết (các nghiên cứu trước còn thiếu gì) và ý nghĩa với đơn vị hoặc ngành. Tránh những câu chung chung như “trong bối cảnh hội nhập kinh tế quốc tế sâu rộng” lặp lại ở hàng nghìn luận văn - đây cũng là loại câu dễ bị phần mềm báo trùng và báo AI.</p>

<h2>2. Tổng quan tình hình nghiên cứu</h2>
<p>Mục tiêu của phần này không phải liệt kê thật nhiều tài liệu, mà là cho thấy bạn hiểu “bản đồ” nghiên cứu của chủ đề và tìm ra <strong>khoảng trống</strong> mà luận văn sẽ lấp. Một cấu trúc hiệu quả:</p>
<ol>
	<li>Nhóm các nghiên cứu theo chủ đề hoặc hướng tiếp cận, không theo từng tác giả.</li>
	<li>Với mỗi nhóm, nêu điểm thống nhất, điểm khác biệt và hạn chế.</li>
	<li>Kết thúc bằng đoạn “khoảng trống nghiên cứu”: bối cảnh chưa được xem xét, phương pháp chưa được dùng, hoặc kết quả còn mâu thuẫn.</li>
</ol>
<p>Đọc thêm hướng dẫn chi tiết tại <a href="{home}cach-viet-tong-quan-tai-lieu-khong-dao-van/">cách viết tổng quan tài liệu không bị đạo văn</a>.</p>

<h2>3. Mục tiêu, câu hỏi và giả thuyết nghiên cứu</h2>
<p>Đây là trái tim của đề cương. Mục tiêu tổng quát nêu điều bạn muốn đạt được; các mục tiêu cụ thể chia nhỏ thành những việc đo lường được. Mỗi mục tiêu cụ thể nên tương ứng với ít nhất một câu hỏi nghiên cứu.</p>
<ul>
	<li><em>Mục tiêu cụ thể:</em> Xác định mức độ ảnh hưởng của các yếu tố đến động lực làm việc của nhân viên.</li>
	<li><em>Câu hỏi tương ứng:</em> Những yếu tố nào ảnh hưởng và mức độ ảnh hưởng của từng yếu tố ra sao?</li>
	<li><em>Giả thuyết (với nghiên cứu định lượng):</em> H1 - Thu nhập có ảnh hưởng cùng chiều đến động lực làm việc.</li>
</ul>
<p>Lỗi thường gặp nhất là mục tiêu quá rộng (“hoàn thiện công tác quản trị nhân lực”) hoặc không khớp với phương pháp (đặt mục tiêu đo lường mức độ ảnh hưởng nhưng lại chỉ phỏng vấn vài người).</p>

<h2>4. Đối tượng, phạm vi và phương pháp nghiên cứu</h2>
<p>Đối tượng là vấn đề được nghiên cứu; khách thể hoặc đối tượng khảo sát là người hoặc đơn vị cung cấp dữ liệu. Phạm vi gồm không gian, thời gian và nội dung. Phạm vi rõ ràng giúp bạn tránh ôm đồm và giúp hội đồng đánh giá tính khả thi.</p>
<p>Phần phương pháp cần cụ thể đến mức người khác có thể hình dung lại quy trình: thiết kế nghiên cứu (định tính, định lượng hay hỗn hợp), cách chọn mẫu và cỡ mẫu dự kiến, công cụ thu thập (bảng hỏi kế thừa thang đo nào, phỏng vấn ai), cách phân tích (thống kê mô tả, hồi quy, phân tích nội dung…). Hướng dẫn chi tiết có trong bài <a href="{home}viet-phuong-phap-nghien-cuu-tranh-trung-lap/">viết phần phương pháp nghiên cứu đúng chuẩn</a>.</p>

<h2>5. Kết cấu dự kiến và kế hoạch thực hiện</h2>
<p>Kết cấu dự kiến thường gồm mở đầu, ba chương nội dung (cơ sở lý luận, thực trạng hoặc kết quả nghiên cứu, giải pháp hoặc thảo luận) và kết luận. Với mỗi chương, hãy ghi 2-3 ý chính sẽ trình bày thay vì chỉ ghi tiêu đề.</p>
<p>Kế hoạch thực hiện nên chia theo tuần hoặc tháng, có các mốc: hoàn thành tổng quan, thu thập dữ liệu, phân tích, viết bản thảo, nhận góp ý, chỉnh sửa và <strong>kiểm tra trùng lặp trước khi nộp</strong>. Hãy dự trù ít nhất một đến hai tuần cho việc kiểm tra và chỉnh sửa theo báo cáo - nhiều học viên bỏ qua bước này và rơi vào cảnh sửa gấp sát hạn.</p>

<h2>6. Tài liệu tham khảo</h2>
<p>Danh mục trong đề cương nên đủ để chứng minh bạn đã đọc nghiêm túc: kết hợp nghiên cứu trong và ngoài nước, ưu tiên công trình gần đây và các bài báo khoa học có phản biện. Trình bày thống nhất một chuẩn trích dẫn ngay từ đầu, tốt nhất là quản lý bằng phần mềm như Zotero để không mất công sửa về sau. Xem <a href="{home}huong-dan-trich-dan-apa-7/">hướng dẫn trích dẫn APA 7</a> nếu trường bạn dùng chuẩn này.</p>

<h2>Những lỗi khiến đề cương bị trả lại</h2>
<ul class="checklist">
	<li><strong>Đề tài trùng lặp hoặc quá quen thuộc</strong> mà không có điểm mới về bối cảnh hay phương pháp.</li>
	<li><strong>Mục tiêu - câu hỏi - phương pháp không khớp</strong>, ví dụ mục tiêu định lượng nhưng phương pháp định tính.</li>
	<li><strong>Tổng quan chỉ liệt kê</strong>, không chỉ ra khoảng trống nghiên cứu.</li>
	<li><strong>Phạm vi quá rộng</strong> so với thời gian và nguồn lực của một luận văn thạc sĩ.</li>
	<li><strong>Chép khung đề cương của người khác</strong>: dễ bị phát hiện vì nhiều trường kiểm tra trùng lặp ngay từ khâu duyệt đề cương.</li>
</ul>

<h2>Viết đề cương bằng lời của chính bạn</h2>
<p>Đề cương là nơi bạn hình thành tư duy cho đề tài, nên hãy viết bằng lời của mình ngay từ đầu. Bạn có thể dùng công cụ AI để gợi ý cách đặt câu hỏi hoặc tìm từ khóa tra cứu, nhưng nội dung đề cương phải là suy nghĩ của bạn và tuân thủ quy định của trường về sử dụng AI. Một đề cương viết thật sẽ giúp luận văn sau này mạch lạc, ít trùng lặp và bạn trả lời hội đồng tự tin hơn.</p>
<blockquote>Muốn biết đề cương có bị trùng với luận văn đã có? <a href="{home}dat-hang/?tab=check">Kiểm tra bằng Turnitin hoặc DoIT</a> ở chế độ không lưu bài.</blockquote>

<h2>Câu hỏi thường gặp</h2>
<h3>Đề cương luận văn thạc sĩ dài bao nhiêu trang?</h3>
<p>Tùy quy định từng trường, thường khoảng 10-20 trang. Hãy ưu tiên đủ ý và rõ ràng hơn là kéo dài số trang.</p>
<h3>Đề cương có cần kiểm tra đạo văn không?</h3>
<p>Nhiều trường đã kiểm tra trùng lặp từ khâu duyệt đề cương. Dù trường không yêu cầu, tự kiểm tra giúp bạn phát hiện sớm những đoạn viết quá sát tài liệu gốc.</p>
<h3>Có được thay đổi đề cương sau khi đã duyệt?</h3>
<p>Thường được điều chỉnh ở mức độ nhất định theo thủ tục của trường, nhưng thay đổi lớn về đề tài hoặc mục tiêu có thể phải duyệt lại. Hãy trao đổi với giảng viên hướng dẫn sớm.</p>
HTML
		,
	),

	/* ---------------------------------------------------------- 41 */
	array(
		'slug'    => 'quy-dinh-ve-dao-van-luan-van-luan-an',
		'cat'     => 'kien-thuc',
		'cover'   => 'quy-dinh-ve-dao-van-luan-van-luan-an-check-dao-van.jpg',
		'tags'    => array( 'quy định đạo văn', 'liêm chính học thuật', 'luận văn thạc sĩ', 'luận án tiến sĩ' ),
		'title'   => 'Quy định về đạo văn với luận văn, luận án ở Việt Nam: Những điều học viên cần biết',
		'excerpt' => 'Các trường đại học ở Việt Nam kiểm soát đạo văn như thế nào, ai quyết định ngưỡng tỷ lệ trùng lặp, hậu quả của vi phạm và cách bảo vệ mình bằng quy trình viết minh bạch. Tổng hợp dễ hiểu cho học viên cao học, nghiên cứu sinh.',
		'content' => <<<'HTML'
<div class="tldr"><b>Tóm tắt nhanh</b><ul>
	<li>Các quy chế đào tạo thạc sĩ, tiến sĩ của Bộ Giáo dục và Đào tạo yêu cầu luận văn, luận án tuân thủ quy định về trung thực học thuật; mỗi trường ban hành quy định chi tiết riêng.</li>
	<li>Ngưỡng tỷ lệ trùng lặp, phần mềm sử dụng và cách loại trừ do từng trường quy định - không có một con số chung cho cả nước.</li>
	<li>Hậu quả của vi phạm có thể từ yêu cầu chỉnh sửa đến không được bảo vệ hoặc bị xử lý cả sau khi đã cấp bằng.</li>
	<li>Cách bảo vệ mình tốt nhất là trích dẫn đầy đủ, lưu bản nháp và tự kiểm tra trước khi nộp.</li>
</ul></div>

<p>“Trường em yêu cầu dưới bao nhiêu phần trăm?”, “Nếu vượt ngưỡng thì có bị hủy kết quả không?”, “Dùng ChatGPT có bị coi là đạo văn?” - đây là những câu hỏi rất thường gặp của học viên cao học và nghiên cứu sinh. Câu trả lời chính xác luôn nằm trong <strong>quy định của chính trường bạn</strong>, nhưng hiểu khung chung sẽ giúp bạn đọc quy định đó đúng cách và chủ động hơn. Bài viết dưới đây tổng hợp những điểm cốt lõi theo hướng dễ hiểu. Đây là thông tin tham khảo, không thay thế văn bản chính thức của trường hay cơ quan quản lý.</p>

<h2>1. Khung quy định chung</h2>
<p>Quy chế tuyển sinh và đào tạo trình độ thạc sĩ (ban hành kèm Thông tư 23/2021/TT-BGDĐT) và quy chế tuyển sinh, đào tạo trình độ tiến sĩ (ban hành kèm Thông tư 18/2021/TT-BGDĐT) đặt ra yêu cầu về tính trung thực trong luận văn, luận án và giao cho cơ sở đào tạo trách nhiệm kiểm soát. Trên cơ sở đó, mỗi trường ban hành quy định riêng về:</p>
<ul>
	<li>Phần mềm kiểm tra sử dụng (DoIT, Turnitin, iThenticate hoặc hệ thống riêng).</li>
	<li>Thời điểm kiểm tra: trước khi bảo vệ cấp cơ sở, trước khi nộp chính thức, hoặc cả hai.</li>
	<li>Ngưỡng tỷ lệ trùng lặp chấp nhận và các phần được loại trừ (trích dẫn, tài liệu tham khảo, phụ lục…).</li>
	<li>Quy trình xử lý khi vượt ngưỡng và các hình thức xử lý vi phạm.</li>
</ul>
<p>Vì vậy, việc đầu tiên bạn cần làm là tìm đọc văn bản “quy định về kiểm tra trùng lặp”, “quy định về liêm chính học thuật” hoặc tên tương tự trên website phòng đào tạo sau đại học của trường.</p>

<figure><img src="{theme}assets/img/blog/quy-dinh-ve-dao-van-luan-van-luan-an-steps.svg" alt="Quy trình kiểm tra trùng lặp luận văn thường gặp tại các trường đại học" width="1200" height="675" loading="lazy"><figcaption>Quy trình kiểm tra trùng lặp thường gặp; thứ tự và tên gọi các bước có thể khác nhau giữa các trường.</figcaption></figure>

<h2>2. Ngưỡng tỷ lệ trùng lặp: ai quyết định?</h2>
<p>Không có một con số áp dụng chung cho mọi trường. Ngưỡng do từng cơ sở đào tạo quy định và có thể khác nhau theo bậc học, ngành học, thậm chí theo phần mềm. Một số trường xét tỷ lệ tổng, một số trường xét thêm tỷ lệ của từng nguồn đơn lẻ. Bạn có thể tham khảo các mức thường gặp trong bài <a href="{home}ty-le-dao-van-bao-nhieu-la-dat/">tỷ lệ đạo văn bao nhiêu là đạt</a>, nhưng chỉ quy định của trường mới có giá trị.</p>
<p>Điều quan trọng không kém con số là <strong>cách tính</strong>: có loại trừ phần trích dẫn trong ngoặc kép không, có loại trừ danh mục tài liệu tham khảo không, có bỏ qua các nguồn trùng dưới một tỷ lệ nhỏ không. Cùng một bài nhưng thiết lập khác nhau có thể cho kết quả chênh lệch đáng kể. Khi tự kiểm tra, hãy dùng thiết lập giống với trường để kết quả sát thực tế.</p>

<h2>3. Những hành vi thường bị coi là vi phạm</h2>
<ul class="checklist">
	<li>Sao chép nội dung của người khác mà không ghi nguồn, kể cả khi đã thay vài từ.</li>
	<li>Dùng lại luận văn, bài báo của chính mình mà không trích dẫn (<a href="{home}tu-dao-van-la-gi/">tự đạo văn</a>).</li>
	<li>Bịa đặt hoặc chỉnh sửa số liệu, kết quả nghiên cứu.</li>
	<li>Nhờ hoặc thuê người khác viết thay, nộp sản phẩm không phải của mình.</li>
	<li>Sử dụng công cụ AI tạo nội dung trái với quy định của trường, hoặc không khai báo khi trường yêu cầu.</li>
	<li>Dùng thủ thuật đánh lừa phần mềm như thay ký tự, chèn chữ ẩn, chuyển văn bản thành ảnh.</li>
</ul>
<p>Lưu ý rằng tỷ lệ trùng lặp thấp không có nghĩa là bài không vi phạm, và tỷ lệ cao chưa chắc là đạo văn. Hội đồng xem xét bản chất: phần trùng là gì, có ghi nguồn không, đóng góp riêng của tác giả ở đâu.</p>

<h2>4. Hậu quả có thể gặp</h2>
<p>Tùy mức độ và quy định của trường, hậu quả có thể bao gồm: yêu cầu chỉnh sửa và kiểm tra lại, lùi thời gian bảo vệ, không được bảo vệ, hủy kết quả, và trong trường hợp nghiêm trọng là xử lý cả khi đã được cấp bằng. Ngoài ra còn ảnh hưởng đến uy tín cá nhân và cơ hội công tác, nghiên cứu sau này. Đây là lý do nên coi việc tự kiểm tra là một bước bắt buộc trong kế hoạch, không phải việc làm vào phút chót.</p>

<h2>5. Còn việc dùng AI thì sao?</h2>
<p>Nhiều trường đang bổ sung quy định về sử dụng công cụ AI tạo sinh trong học tập và nghiên cứu. Xu hướng chung là: cho phép dùng ở mức hỗ trợ (gợi ý ý tưởng, kiểm tra ngữ pháp, dịch tham khảo) nếu khai báo minh bạch, và không chấp nhận nộp nội dung do AI viết như sản phẩm của mình. Một số trường đã yêu cầu kèm báo cáo phát hiện AI. Vì công cụ phát hiện AI có thể báo nhầm, bạn nên lưu lại bản nháp, ghi chú và lịch sử chỉnh sửa để chứng minh quá trình viết. Xem thêm <a href="{home}dung-chatgpt-viet-luan-van/">ranh giới giữa hỗ trợ hợp lệ và vi phạm khi dùng ChatGPT</a>.</p>

<h2>6. Cách tự bảo vệ mình</h2>
<ol>
	<li><strong>Đọc kỹ quy định của trường</strong> ngay khi bắt đầu viết, ghi lại ngưỡng, phần mềm và thiết lập loại trừ.</li>
	<li><strong>Quản lý tài liệu và trích dẫn ngay từ đầu</strong> bằng phần mềm như Zotero để không bỏ sót nguồn.</li>
	<li><strong>Lưu bản nháp theo phiên bản</strong> và ghi chép nghiên cứu - đây là bằng chứng quá trình làm việc.</li>
	<li><strong>Tự kiểm tra bằng đúng phần mềm của trường</strong>, ở chế độ không lưu bài, trước hạn nộp đủ lâu để kịp chỉnh sửa.</li>
	<li><strong>Chỉnh sửa trung thực</strong>: bổ sung trích dẫn, tổng hợp và phân tích bằng lời của mình, không dùng mẹo che giấu.</li>
</ol>
<blockquote>Tự kiểm tra trước khi nộp: <a href="{home}dat-hang/?tab=check">đặt kiểm tra DoIT, Turnitin hoặc iThenticate</a> ở chế độ không lưu bài. Đọc thêm <a href="{home}liem-chinh-hoc-thuat/">chính sách liêm chính học thuật</a> của chúng tôi.</blockquote>

<h2>Câu hỏi thường gặp</h2>
<h3>Có quy định chung cả nước về tỷ lệ trùng lặp không?</h3>
<p>Quy chế của Bộ đặt khung về tính trung thực; ngưỡng cụ thể và cách kiểm tra do từng cơ sở đào tạo quy định. Hãy đọc văn bản của trường bạn.</p>
<h3>Tỷ lệ thấp có chắc chắn được chấp nhận?</h3>
<p>Không hẳn. Hội đồng vẫn đánh giá nội dung, cách trích dẫn và đóng góp riêng. Một nguồn đơn lẻ chiếm tỷ lệ lớn có thể bị lưu ý dù tổng tỷ lệ thấp.</p>
<h3>Tự kiểm tra trước có làm bài bị lưu vào kho không?</h3>
<p>Có thể, nếu dùng tài khoản có lưu bài. Hãy chọn dịch vụ kiểm tra ở chế độ không lưu bài để tránh trùng với chính mình khi nộp chính thức.</p>
HTML
		,
	),

	/* ---------------------------------------------------------- 42 */
	array(
		'slug'    => 'cach-viet-tom-tat-abstract',
		'cat'     => 'huong-dan',
		'cover'   => 'cach-viet-tom-tat-abstract-check-dao-van.jpg',
		'tags'    => array( 'cách viết abstract', 'tóm tắt luận văn', 'tóm tắt bài báo khoa học', 'IMRaD' ),
		'title'   => 'Cách viết tóm tắt (abstract) luận văn và bài báo khoa học: Cấu trúc 5 phần kèm ví dụ',
		'excerpt' => 'Tóm tắt là phần được đọc nhiều nhất của một công trình. Hướng dẫn viết abstract theo cấu trúc bối cảnh - mục tiêu - phương pháp - kết quả - kết luận, phân bổ độ dài hợp lý, ví dụ trước và sau khi sửa, và lỗi thường gặp.',
		'content' => <<<'HTML'
<div class="tldr"><b>Tóm tắt nhanh</b><ul>
	<li>Abstract là bản thu nhỏ độc lập của cả công trình: đọc riêng vẫn hiểu bạn làm gì, làm thế nào và tìm ra gì.</li>
	<li>Cấu trúc 5 phần: bối cảnh, mục tiêu, phương pháp, kết quả chính, kết luận và hàm ý.</li>
	<li>Phần kết quả nên chiếm nhiều chữ nhất, có con số hoặc phát hiện cụ thể.</li>
	<li>Viết abstract sau cùng, bằng lời của bạn; không chép lại câu từ phần mở đầu.</li>
</ul></div>

<p>Hội đồng, phản biện và biên tập viên tạp chí thường đọc tóm tắt đầu tiên, và nhiều người quyết định có đọc tiếp hay không chỉ dựa vào đoạn văn vài trăm chữ này. Trên các cơ sở dữ liệu học thuật, abstract cũng là phần giúp công trình của bạn được tìm thấy. Thế nhưng đây lại thường là phần được viết vội nhất - nhiều bản tóm tắt chỉ nhắc lại lý do chọn đề tài mà không nói kết quả nghiên cứu là gì. Bài viết này giúp bạn viết một abstract rõ ràng, đủ ý, đúng độ dài.</p>

<h2>1. Abstract cần trả lời 5 câu hỏi</h2>
<ol>
	<li><strong>Bối cảnh:</strong> Vấn đề gì đang tồn tại và vì sao quan trọng? (1-2 câu)</li>
	<li><strong>Mục tiêu:</strong> Nghiên cứu này nhằm làm gì? (1 câu)</li>
	<li><strong>Phương pháp:</strong> Dữ liệu từ đâu, cỡ mẫu bao nhiêu, phân tích bằng cách nào? (1-2 câu)</li>
	<li><strong>Kết quả:</strong> Phát hiện chính là gì, có số liệu nào nổi bật? (2-3 câu)</li>
	<li><strong>Kết luận và hàm ý:</strong> Kết quả có ý nghĩa gì với lý thuyết hoặc thực tiễn? (1-2 câu)</li>
</ol>
<p>Cấu trúc này tương ứng với mô hình IMRaD (Introduction - Methods - Results - and Discussion) mà phần lớn tạp chí khoa học sử dụng. Một số tạp chí yêu cầu “structured abstract” với các tiêu đề nhỏ như Purpose, Design/methodology, Findings, Originality; khi đó bạn chỉ cần viết từng phần dưới đúng tiêu đề.</p>

<figure><img src="{theme}assets/img/blog/cach-viet-tom-tat-abstract-bars.svg" alt="Phân bổ độ dài gợi ý cho 5 phần của abstract" width="1200" height="675" loading="lazy"><figcaption>Phân bổ độ dài gợi ý: phần kết quả nên chiếm nhiều chữ nhất.</figcaption></figure>

<h2>2. Độ dài và cách phân bổ</h2>
<p>Tạp chí quốc tế thường giới hạn abstract khoảng 150-300 từ; luận văn, luận án ở Việt Nam có thể có bản tóm tắt dài hơn theo quy định của trường. Dù độ dài ra sao, hãy phân bổ hợp lý: bối cảnh và mục tiêu khoảng một phần tư, phương pháp khoảng một phần tư, và dành gần một nửa cho kết quả và kết luận. Lỗi phổ biến nhất là ngược lại - viết rất dài về bối cảnh rồi kết thúc bằng câu “kết quả nghiên cứu đã chỉ ra một số yếu tố ảnh hưởng” mà không nói yếu tố nào.</p>

<h2>3. Ví dụ trước và sau khi sửa</h2>
<p><strong>Bản chưa tốt:</strong> “Trong bối cảnh hội nhập, chuyển đổi số có vai trò ngày càng quan trọng. Nghiên cứu này tìm hiểu về chuyển đổi số tại doanh nghiệp. Bằng phương pháp định lượng, nghiên cứu đã chỉ ra một số yếu tố ảnh hưởng và đề xuất một số giải pháp.”</p>
<p><strong>Bản đã sửa (ví dụ minh họa):</strong> “Doanh nghiệp nhỏ và vừa chiếm đa số trong nền kinh tế nhưng mức độ chuyển đổi số còn thấp. Nghiên cứu nhằm xác định các yếu tố ảnh hưởng đến ý định chuyển đổi số của doanh nghiệp nhỏ và vừa tại một tỉnh đồng bằng sông Hồng. Dữ liệu khảo sát từ 312 doanh nghiệp được phân tích bằng mô hình cấu trúc tuyến tính. Kết quả cho thấy năng lực công nghệ của lãnh đạo và hỗ trợ từ chính quyền địa phương có ảnh hưởng mạnh nhất, trong khi chi phí đầu tư không có ý nghĩa thống kê. Nghiên cứu gợi ý các chương trình hỗ trợ nên tập trung vào đào tạo lãnh đạo doanh nghiệp thay vì chỉ hỗ trợ tài chính.”</p>
<p>Bản thứ hai cho người đọc biết đủ: ai, ở đâu, bao nhiêu mẫu, phương pháp gì, kết quả cụ thể và hàm ý. Các con số trong ví dụ chỉ để minh họa cách viết.</p>

<h2>4. Ngôn ngữ và văn phong</h2>
<ul>
	<li>Dùng câu ngắn, mỗi câu một ý; tránh câu ghép quá dài.</li>
	<li>Thì của động từ (khi viết tiếng Anh): thường dùng quá khứ cho phương pháp và kết quả (“data were collected”, “results showed”), hiện tại cho kết luận chung.</li>
	<li>Hạn chế viết tắt; nếu dùng, giải thích lần đầu xuất hiện.</li>
	<li>Không trích dẫn tài liệu trong abstract, trừ khi tạp chí cho phép.</li>
	<li>Không đưa thông tin không có trong bài.</li>
</ul>

<h2>5. Từ khóa (keywords)</h2>
<p>Sau abstract thường có 3-6 từ khóa. Hãy chọn những cụm từ người khác sẽ dùng để tìm nghiên cứu của bạn: chủ đề chính, phương pháp đặc trưng, bối cảnh. Tránh lặp lại nguyên tên đề tài; thay vào đó bổ sung các cụm từ đồng nghĩa phổ biến để tăng khả năng được tìm thấy.</p>

<h2>6. Abstract và vấn đề trùng lặp, AI</h2>
<p>Vì abstract ngắn và có cấu trúc khá giống nhau giữa các nghiên cứu cùng chủ đề, những câu mở đầu sáo rỗng rất dễ trùng với hàng loạt luận văn khác. Hãy viết abstract <strong>sau cùng</strong>, khi đã nắm chắc kết quả, và viết mới hoàn toàn bằng lời của bạn thay vì cắt ghép câu từ chương mở đầu.</p>
<p>Abstract cũng là đoạn văn hay bị công cụ phát hiện AI đánh giá cao nếu dùng nhiều cụm từ chung chung. Đưa con số, bối cảnh cụ thể và phát hiện riêng của bạn vào là cách tự nhiên nhất để đoạn văn mang dấu ấn cá nhân. Nếu bạn dùng công cụ hỗ trợ ngôn ngữ để chỉnh câu tiếng Anh, hãy tuân thủ quy định khai báo của trường hoặc tạp chí. Tham khảo thêm <a href="{home}giam-ty-le-ai-trung-thuc/">7 kỹ thuật biên tập giúp bài mang dấu ấn của bạn</a>.</p>

<h2>Checklist trước khi nộp</h2>
<ul class="checklist">
	<li>Đọc riêng abstract, người ngoài ngành có hiểu bạn làm gì và tìm ra gì không?</li>
	<li>Có đủ 5 phần, đặc biệt là kết quả cụ thể?</li>
	<li>Đúng giới hạn số từ của trường hoặc tạp chí?</li>
	<li>Thông tin khớp hoàn toàn với nội dung bài?</li>
	<li>Từ khóa phản ánh đúng chủ đề và phương pháp?</li>
</ul>
<blockquote>Gửi bài báo cho tạp chí quốc tế? <a href="{home}dich-vu-kiem-tra-ithenticate/">Kiểm tra iThenticate</a> trước khi nộp để biết tỷ lệ trùng lặp mà ban biên tập sẽ thấy.</blockquote>

<h2>Câu hỏi thường gặp</h2>
<h3>Nên viết abstract trước hay sau?</h3>
<p>Nên viết sau cùng, khi đã có kết quả và kết luận. Bạn có thể viết một bản nháp ở đầu để định hướng, nhưng phải viết lại khi hoàn thành.</p>
<h3>Abstract tiếng Anh có thể dịch từ bản tiếng Việt?</h3>
<p>Được, nhưng nên biên tập lại cho tự nhiên theo văn phong học thuật tiếng Anh và đúng giới hạn từ của tạp chí, thay vì dịch từng câu.</p>
<h3>Abstract có tính vào tỷ lệ trùng lặp không?</h3>
<p>Thông thường có, vì phần mềm quét toàn bộ văn bản bạn gửi. Hãy viết abstract bằng lời của bạn để tránh trùng với các công trình cùng chủ đề.</p>
HTML
		,
	),
);
