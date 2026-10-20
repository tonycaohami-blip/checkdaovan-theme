<?php
/**
 * Template Name: Chương trình cộng tác viên
 */
get_header();
$pct   = (float) vs_opt( 'aff_pct' );
$disc  = (float) vs_opt( 'ref_discount' );
$min   = (int) vs_opt( 'payout_min' );
$days  = (int) vs_opt( 'ref_days' );
$cta   = is_user_logged_in()
	? '<a class="btn btn--accent btn--lg" href="' . esc_url( vs_account_url( 'cong-tac-vien' ) ) . '">Lấy link giới thiệu của tôi</a>'
	: '<a class="btn btn--accent btn--lg" href="' . esc_url( vs_account_url( '', array( 'dang-ky' => 1, 'redirect_to' => rawurlencode( vs_account_url( 'cong-tac-vien' ) ) ) ) ) . '">Đăng ký làm cộng tác viên</a>';
get_template_part( 'template-parts/page-hero', null, array(
	'title'   => 'Giới thiệu bạn bè,<br><span class="hl">nhận ' . esc_html( rtrim( rtrim( number_format( $pct, 1, ',', '' ), '0' ), ',' ) ) . '% hoa hồng</span>',
	'eyebrow' => 'Chương trình cộng tác viên',
	'lead'    => 'Miễn phí tham gia. Chia sẻ link cho bạn bè, nhóm lớp, hội nhóm học thuật - nhận hoa hồng trên mọi đơn hàng hoàn thành. Bạn bè của bạn cũng được giảm ' . esc_html( $disc ) . '%.',
	'actions' => $cta . '<a class="btn btn--glass btn--lg" href="#tinh-thu-nhap">Tính thu nhập</a><a class="btn btn--glass btn--lg" href="' . esc_url( home_url( '/huong-dan-chuong-trinh-cong-tac-vien/' ) ) . '">Hướng dẫn chi tiết</a>',
	'aside'   => '<div class="aff-hero"><div class="aff-hero__row"><span>Link của bạn</span><code>' . esc_html( home_url( '/?ref=MA-CUA-BAN' ) ) . '</code></div><div class="aff-hero__grid"><div><b>' . esc_html( $pct ) . '%</b><span>Hoa hồng</span></div><div><b>' . esc_html( $days ) . '</b><span>ngày ghi nhận</span></div><div><b>' . esc_html( vs_money( $min ) ) . '</b><span>rút tối thiểu</span></div></div></div>',
) );
?>

<section class="section">
	<div class="container">
		<div class="section__head reveal"><span class="eyebrow">Cách hoạt động</span><h2>3 bước để bắt đầu kiếm tiền</h2></div>
		<div class="how">
			<div class="how__item reveal"><span class="how__num">1</span><?php echo vs_icon( 'user', 30 ); // phpcs:ignore ?><h3>Tạo tài khoản</h3><p>Đăng ký miễn phí, hệ thống cấp ngay mã và link giới thiệu riêng.</p></div>
			<div class="how__item reveal"><span class="how__num">2</span><?php echo vs_icon( 'link', 30 ); // phpcs:ignore ?><h3>Chia sẻ link</h3><p>Gửi cho bạn bè, đăng nhóm lớp, Facebook, Zalo. Ghi nhận trong <?php echo (int) $days; ?> ngày kể từ lần bấm.</p></div>
			<div class="how__item reveal"><span class="how__num">3</span><?php echo vs_icon( 'wallet', 30 ); // phpcs:ignore ?><h3>Nhận hoa hồng</h3><p><?php echo esc_html( $pct ); ?>% giá trị mỗi đơn hoàn thành. Rút về ngân hàng khi đạt <?php echo esc_html( vs_money( $min ) ); ?>.</p></div>
		</div>
	</div>
</section>

<section class="section section--tint" id="tinh-thu-nhap">
	<div class="container split split--center">
		<div class="reveal">
			<span class="eyebrow">Ước tính</span>
			<h2>Bạn có thể nhận bao nhiêu?</h2>
			<p class="muted">Kéo thanh trượt để ước tính thu nhập hàng tháng. Con số chỉ mang tính tham khảo, phụ thuộc giá trị đơn thực tế.</p>
			<ul class="ticks ticks--lg">
				<li>Khách đăng ký tài khoản qua link của bạn được ghi nhận <b>lâu dài</b> cho các đơn sau</li>
				<li>Theo dõi lượt click, đơn hàng, hoa hồng theo thời gian thực</li>
				<li>Không giới hạn số người giới thiệu</li>
			</ul>
		</div>
		<div class="calc reveal" data-calc data-pct="<?php echo esc_attr( $pct ); ?>">
			<label><span>Số đơn được giới thiệu / tháng <b data-out="n">20</b></span><input type="range" min="1" max="200" value="20" data-in="n"></label>
			<label><span>Giá trị đơn trung bình <b data-out="v">500.000đ</b></span><input type="range" min="50000" max="3000000" step="50000" value="500000" data-in="v"></label>
			<div class="calc__result"><span>Thu nhập ước tính / tháng</span><b data-out="r">1.000.000đ</b></div>
			<?php echo $cta; // phpcs:ignore ?>
		</div>
	</div>
</section>

<section class="section">
	<div class="container container--narrow">
		<div class="section__head reveal"><span class="eyebrow">Quy định</span><h2>Câu hỏi về chương trình</h2></div>
		<div class="faq">
			<?php
			$faq = array(
				'Khi nào hoa hồng được duyệt?' => 'Hoa hồng ở trạng thái “Chờ duyệt” khi khách đặt đơn và chuyển sang “Đã duyệt” khi đơn hoàn thành. Đơn bị hủy hoặc hoàn tiền sẽ không được tính.',
				'Rút tiền như thế nào?' => 'Khi số dư đã duyệt đạt ' . vs_money( $min ) . ', bạn bấm “Yêu cầu rút tiền” trong trang tài khoản. Chúng tôi chuyển khoản trong 1-3 ngày làm việc.',
				'Tôi có được tự đặt đơn qua link của mình không?' => 'Không. Đơn có số điện thoại trùng với tài khoản cộng tác viên sẽ không được tính hoa hồng.',
				'Khách được giới thiệu nhận ưu đãi gì?' => 'Khách vào web qua link của bạn được giảm ' . $disc . '% (không cộng dồn với mã giảm giá khác).',
				'Hoa hồng có phải nộp thuế không?' => 'Có. Hoa hồng là thu nhập chịu thuế thu nhập cá nhân theo quy định. Khi chi trả, chúng tôi có thể khấu trừ thuế theo mức luật định và cần bạn cung cấp họ tên, số CCCD/mã số thuế để kê khai. Bạn tự chịu trách nhiệm quyết toán thuế phần thu nhập của mình.',
				'Chương trình có nhiều cấp không?' => 'Không. Bạn chỉ nhận hoa hồng từ đơn của khách mua dịch vụ qua link của bạn; không có hoa hồng từ việc tuyển thêm cộng tác viên và không thu phí tham gia.',
				'Có được chạy quảng cáo bằng tên thương hiệu không?' => 'Bạn được chia sẻ tự do trên mạng xã hội, nhóm học tập. Không được chạy quảng cáo từ khóa bằng tên thương hiệu hoặc đưa thông tin sai về dịch vụ (ví dụ “cam kết qua Turnitin”, “lách AI”, “làm luận văn trọn gói”).',
			);
			foreach ( $faq as $q => $a ) :
				?>
				<details class="faq__item reveal"><summary><?php echo esc_html( $q ); ?></summary><p><?php echo esc_html( $a ); ?></p></details>
			<?php endforeach; ?>
		</div>
		<p class="center-note">Xem <a href="<?php echo esc_url( home_url( '/huong-dan-chuong-trinh-cong-tac-vien/' ) ); ?>">hướng dẫn chi tiết từng bước kèm hình ảnh</a>: đăng ký, chia sẻ link, theo dõi hoa hồng và rút tiền.</p>
	</div>
</section>
<?php
get_footer();
