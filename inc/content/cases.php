<?php
/**
 * Kết quả mẫu - tự đánh dấu "Ví dụ minh họa" khi nhập.
 * Hãy sửa/xóa trong Quản trị > Kết quả thực tế và thêm kết quả thật của bạn.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	array(
		'title' => 'Luận văn thạc sĩ Quản trị kinh doanh',
		'note'  => 'Tổng quan tài liệu dài, trích nhiều nguồn tiếng Anh dịch sát. Viết lại theo hướng tổng hợp, bổ sung trích dẫn APA.',
		'meta'  => array( 'service' => 'reduce', 'doc_type' => 'Luận văn thạc sĩ', 'field' => 'Quản trị kinh doanh', 'tool' => 'Turnitin', 'pages' => 96, 'before' => 47, 'after' => 13, 'ai_before' => '', 'ai_after' => '', 'days' => '48 giờ' ),
	),
	array(
		'title' => 'Khóa luận tốt nghiệp ngành Kế toán',
		'note'  => 'Phần cơ sở lý thuyết trùng với khóa luận cùng trường. Diễn đạt lại khái niệm, thêm số liệu doanh nghiệp thực tập.',
		'meta'  => array( 'service' => 'reduce', 'doc_type' => 'Khóa luận tốt nghiệp', 'field' => 'Kế toán', 'tool' => 'Turnitin', 'pages' => 62, 'before' => 38, 'after' => 21, 'ai_before' => '', 'ai_after' => '', 'days' => '3 ngày' ),
	),
	array(
		'title' => 'Bài báo gửi tạp chí quốc tế',
		'note'  => 'Phần phương pháp nghiên cứu trùng với bài đã công bố của nhóm. Viết lại, trích dẫn bài trước theo yêu cầu tạp chí.',
		'meta'  => array( 'service' => 'reduce', 'doc_type' => 'Bài báo khoa học', 'field' => 'Kinh tế phát triển', 'tool' => 'iThenticate', 'pages' => 18, 'before' => 29, 'after' => 9, 'ai_before' => '', 'ai_after' => '', 'days' => '24 giờ' ),
	),
	array(
		'title' => 'Luận án tiến sĩ Giáo dục học',
		'note'  => 'Chương 1 và 2 có nhiều đoạn trích từ văn bản chính sách. Rút gọn trích dẫn nguyên văn, chuyển sang phân tích.',
		'meta'  => array( 'service' => 'reduce', 'doc_type' => 'Luận án tiến sĩ', 'field' => 'Giáo dục học', 'tool' => 'Turnitin', 'pages' => 180, 'before' => 34, 'after' => 12, 'ai_before' => '', 'ai_after' => '', 'days' => '5 ngày' ),
	),
	array(
		'title' => 'Tiểu luận môn Marketing căn bản',
		'note'  => 'Bài do sinh viên tự viết nhưng nhiều câu khuôn mẫu bị đánh dấu AI. Biên tập viên chỉ ra đoạn cần làm rõ, sinh viên bổ sung ví dụ thị trường Việt Nam từ khảo sát của mình.',
		'meta'  => array( 'service' => 'reduce', 'doc_type' => 'Tiểu luận', 'field' => 'Marketing', 'tool' => 'Turnitin + Originality.ai', 'pages' => 24, 'before' => 31, 'after' => 16, 'ai_before' => 41, 'ai_after' => 12, 'days' => '48 giờ' ),
	),
	array(
		'title' => 'Kiểm tra trước khi nộp: Luận văn Luật kinh tế',
		'note'  => 'Kiểm tra Turnitin combo Đạo văn + AI trước hạn nộp. Tỷ lệ đạt yêu cầu, không cần chỉnh sửa.',
		'meta'  => array( 'service' => 'check', 'doc_type' => 'Luận văn thạc sĩ', 'field' => 'Luật kinh tế', 'tool' => 'Turnitin', 'pages' => 88, 'before' => 14, 'after' => '', 'ai_before' => 0, 'ai_after' => '', 'days' => '2 giờ' ),
	),
);
