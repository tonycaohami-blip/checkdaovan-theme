<?php
/**
 * Trang chủ.
 */
get_header();

$p         = vs_pricing();
$tools     = $p['tools'];
$reduce    = $p['reduce'];
$meta      = vs_tool_meta();
$min_check = vs_min_check_price();
$done      = vs_completed_count();
?>

<!-- ================= HERO ================= -->
<section class="hero">
	<div class="hero__bg" aria-hidden="true"><span class="hero__glow hero__glow--1"></span><span class="hero__glow hero__glow--2"></span></div>
	<div class="container hero__inner">
		<div class="hero__copy reveal">
			<span class="pill pill--light"><span class="dot"></span> Hoạt động 24/7 · Không lưu bài · Bảo mật tài liệu</span>
			<h1>Nền tảng kiểm tra <span class="hl">đạo văn</span> &amp; <span class="hl">tỷ lệ AI</span> cho người làm học thuật</h1>
			<p class="hero__lead">Kiểm tra bằng Turnitin, iThenticate, DoIT, Copyleaks, Originality.ai, GPTZero và nhiều phần mềm khác. Tỷ lệ cao? Biên tập viên chuyên ngành giúp bạn đưa bài xuống <strong>dưới 30%</strong> hoặc <strong>dưới 20%</strong> - không đạt hoàn tiền.</p>
			<div class="hero__actions">
				<a href="<?php echo esc_url( vs_page_url( 'dat-hang', array( 'tab' => 'check' ) ) ); ?>" class="btn btn--accent btn--lg">Đặt kiểm tra ngay <?php echo vs_icon( 'arrow', 18 ); // phpcs:ignore ?></a>
				<a href="<?php echo esc_url( vs_page_url( 'giam-ty-le-dao-van' ) ); ?>" class="btn btn--glass btn--lg">Giảm tỷ lệ đạo văn</a>
			</div>
			<ul class="hero__trust">
				<li><?php echo vs_icon( 'shield', 19 ); // phpcs:ignore ?>Chế độ No Repository</li>
				<li><?php echo vs_icon( 'qr', 19 ); // phpcs:ignore ?>Thanh toán VietQR</li>
				<li><?php echo vs_icon( 'refund', 19 ); // phpcs:ignore ?>Không đạt hoàn tiền</li>
			</ul>
		</div>

		<div class="quote reveal" data-quote>
			<div class="quote__head">
				<b>Báo giá nhanh</b>
				<span class="summary__live"><i></i> Tự động</span>
			</div>
			<label class="quote__label">Phần mềm</label>
			<div class="quote__tools">
				<?php foreach ( $tools as $k => $t ) : ?>
					<label><input type="radio" name="q_tool" value="<?php echo esc_attr( $k ); ?>" <?php checked( 'turnitin', $k ); ?>><span><i class="<?php echo esc_attr( trim( vs_tool_mark_class( $k ) ) ); ?>" style="--c:<?php echo esc_attr( $meta[ $k ][1] ); ?>"><?php echo vs_tool_mark( $k ); // phpcs:ignore ?></i><?php echo esc_html( $t['name'] ); ?></span></label>
				<?php endforeach; ?>
			</div>
			<label class="quote__label">Hình thức</label>
			<div class="seg seg--dark">
				<?php foreach ( vs_check_modes() as $k => $l ) : ?>
					<label><input type="radio" name="q_mode" value="<?php echo esc_attr( $k ); ?>" <?php checked( 'combo', $k ); ?>><span><?php echo esc_html( $l ); ?></span></label>
				<?php endforeach; ?>
			</div>
			<div class="quote__total"><span>Giá / file (≤ <?php echo (int) $p['check']['pages_included']; ?> trang)</span><b data-quote-total><?php echo esc_html( vs_money( $tools['turnitin']['combo'] ) ); ?></b></div>
			<a class="btn btn--accent btn--block btn--lg" data-quote-go href="<?php echo esc_url( vs_page_url( 'dat-hang', array( 'tab' => 'check', 'tool' => 'turnitin', 'mode' => 'combo' ) ) ); ?>">Tiếp tục đặt hàng</a>
			<p class="quote__note">Chọn “Đạo văn + AI” để tiết kiệm hơn so với kiểm tra lẻ từng loại</p>
		</div>
	</div>

	<div class="container">
		<div class="logos reveal">
			<span class="logos__label">Kiểm tra trên <?php echo count( $tools ); ?> phần mềm hàng đầu</span>
			<div class="logos__list">
				<?php foreach ( $tools as $k => $t ) : ?>
					<span class="logos__item"><i class="<?php echo esc_attr( trim( vs_tool_mark_class( $k ) ) ); ?>" style="--c:<?php echo esc_attr( $meta[ $k ][1] ); ?>"><?php echo vs_tool_mark( $k ); // phpcs:ignore ?></i><?php echo esc_html( $t['name'] ); ?></span>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>

<!-- ================= SỐ LIỆU ================= -->
<section class="facts">
	<div class="container facts__grid">
		<div class="fact reveal"><b>4</b><span>phần mềm kiểm tra phổ biến nhất</span></div>
		<div class="fact reveal"><b>24/7</b><span>nhận đơn, thanh toán &amp; xử lý</span></div>
		<?php if ( $done >= 100 ) : ?>
			<div class="fact reveal"><b><?php echo esc_html( number_format( $done, 0, ',', '.' ) ); ?>+</b><span>đơn hàng đã hoàn thành</span></div>
		<?php else : ?>
			<div class="fact reveal"><b>100<small>%</small></b><span>kiểm tra ở chế độ không lưu bài</span></div>
		<?php endif; ?>
		<div class="fact reveal"><b>&lt;20<small>%</small></b><span>mục tiêu cam kết khi giảm tỷ lệ</span></div>
	</div>
</section>

<?php get_template_part( 'template-parts/home-247' ); ?>

<!-- ================= DỊCH VỤ ================= -->
<section class="section">
	<div class="container">
		<div class="section__head reveal">
			<span class="eyebrow">Dịch vụ</span>
			<h2>Mọi thứ bạn cần trước khi nộp bài</h2>
			<p>Từ kiểm tra đến xử lý, trên cùng một tài khoản.</p>
		</div>
		<div class="svc-grid">
			<?php
			$svcs = array(
				array( 'target', 'Kiểm tra đạo văn', 'Báo cáo Similarity gốc từ Turnitin, iThenticate, Copyleaks.', vs_page_url( 'kiem-tra-dao-van' ), 'Từ ' . vs_money( vs_min_price( 'plag' ) ) ),
				array( 'chart', 'Kiểm tra tỷ lệ AI', 'Phát hiện văn bản do ChatGPT, Gemini, Claude… tạo ra.', vs_page_url( 'kiem-tra-dao-van' ), 'Từ ' . vs_money( vs_min_price( 'ai' ) ) ),
				array( 'pen', 'Giảm tỷ lệ đạo văn', 'Giảm đạo văn và/hoặc tỷ lệ AI xuống đúng mức bạn chọn, cam kết kết quả.', vs_page_url( 'giam-ty-le-dao-van' ), 'Từ ' . vs_money( $reduce['t30'] ) . '/trang' ),
				array( 'shield', 'Giảm tỷ lệ AI', 'Viết lại theo giọng văn tự nhiên, đưa tỷ lệ AI dưới 20%.', vs_page_url( 'giam-ty-le-dao-van' ), 'Tùy chọn kèm đơn giảm tỷ lệ' ),
			);
			foreach ( $svcs as $i => $s ) :
				?>
				<a class="svc reveal<?php echo 2 === $i ? ' svc--hot' : ''; ?>" href="<?php echo esc_url( $s[3] ); ?>">
					<span class="svc__icon"><?php echo vs_icon( $s[0], 26 ); // phpcs:ignore ?></span>
					<h3><?php echo esc_html( $s[1] ); ?></h3>
					<p><?php echo esc_html( $s[2] ); ?></p>
					<span class="svc__foot"><span><?php echo esc_html( $s[4] ); ?></span><?php echo vs_icon( 'arrow', 18 ); // phpcs:ignore ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- ================= KẾT QUẢ ================= -->
<?php $cases = vs_cases( 3 ); ?>
<?php if ( $cases ) : ?>
	<section class="section section--tint">
		<div class="container">
			<div class="section__head section__head--row reveal">
				<div><span class="eyebrow">Kết quả thực tế</span><h2>Trước và sau khi xử lý</h2></div>
				<a class="btn btn--outline" href="<?php echo esc_url( vs_page_url( 'ket-qua-thuc-te' ) ); ?>">Xem tất cả kết quả</a>
			</div>
			<div class="cases">
				<?php foreach ( $cases as $c ) { get_template_part( 'template-parts/case-card', null, array( 'case' => $c ) ); } ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<!-- ================= VÌ SAO CHỌN ================= -->
<section class="section">
	<div class="container split split--center">
		<div class="reveal">
			<span class="eyebrow">Vì sao chọn <?php bloginfo( 'name' ); ?></span>
			<h2>Chuyên nghiệp, minh bạch, bảo mật</h2>
			<p class="muted">Chúng tôi hiểu áp lực trước hạn nộp. Mọi quy trình được thiết kế để bạn biết chính xác mình đang ở đâu và cần làm gì.</p>
			<a class="btn btn--primary" href="<?php echo esc_url( vs_account_url( '', array( 'dang-ky' => 1 ) ) ); ?>">Tạo tài khoản miễn phí</a>
		</div>
		<div class="features features--2">
			<?php
			foreach ( array(
				array( 'shield', 'Không lưu bài', 'Nộp chính thức không bị trùng với chính bản nháp.' ),
				array( 'lock', 'Bảo mật tài liệu', 'Thư mục riêng, tự xóa sau 30 ngày.' ),
				array( 'file', 'Báo cáo gốc', 'PDF xuất trực tiếp, đủ nguồn và đoạn trùng.' ),
				array( 'user', 'Tài khoản riêng', 'Quản lý đơn, tải báo cáo mọi lúc.' ),
			) as $f ) :
				?>
				<div class="feature reveal">
					<div class="feature__icon"><?php echo vs_icon( $f[0] ); // phpcs:ignore ?></div>
					<h3><?php echo esc_html( $f[1] ); ?></h3>
					<p><?php echo esc_html( $f[2] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php get_template_part( 'template-parts/home-security' ); ?>

<!-- ================= CỘNG TÁC VIÊN ================= -->
<section class="section">
	<div class="container">
		<div class="aff-band reveal">
			<div class="aff-band__icon"><?php echo vs_icon( 'gift', 40 ); // phpcs:ignore ?></div>
			<div class="aff-band__copy">
				<span class="eyebrow">Chương trình cộng tác viên</span>
				<h2>Giới thiệu bạn bè - nhận <?php echo esc_html( vs_opt( 'aff_pct' ) ); ?>% hoa hồng</h2>
				<p>Chia sẻ link riêng của bạn. Bạn bè được giảm <?php echo esc_html( vs_opt( 'ref_discount' ) ); ?>%, bạn nhận hoa hồng trên mọi đơn hoàn thành, rút tiền về ngân hàng.</p>
			</div>
			<a class="btn btn--accent btn--lg" href="<?php echo esc_url( vs_page_url( 'cong-tac-vien' ) ); ?>">Tìm hiểu thêm</a>
		</div>
	</div>
</section>

<!-- ================= BLOG ================= -->
<?php
$latest = new WP_Query( array( 'posts_per_page' => 3, 'ignore_sticky_posts' => true, 'no_found_rows' => true ) );
if ( $latest->have_posts() ) :
	?>
	<section class="section section--tint">
		<div class="container">
			<div class="section__head section__head--row reveal">
				<div><span class="eyebrow">Blog kiến thức</span><h2>Hiểu đúng về đạo văn &amp; AI</h2></div>
				<?php if ( get_option( 'page_for_posts' ) ) : ?>
					<a class="btn btn--outline" href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>">Xem tất cả bài viết</a>
				<?php endif; ?>
			</div>
			<div class="posts">
				<?php
				while ( $latest->have_posts() ) :
					$latest->the_post();
					get_template_part( 'template-parts/post', 'card' );
				endwhile;
				wp_reset_postdata();
				?>
			</div>
		</div>
	</section>
<?php endif; ?>

<!-- ================= HỎI ĐÁP ================= -->
<section class="section" id="hoi-dap">
	<div class="container faq-wrap">
		<div class="section__head section__head--left reveal">
			<span class="eyebrow">Hỏi đáp</span>
			<h2>Câu hỏi thường gặp</h2>
			<p>Chưa thấy câu trả lời bạn cần? Nhắn Zalo <?php echo esc_html( vs_opt( 'zalo' ) ); ?>, phản hồi trong vài phút.</p>
			<a class="btn btn--primary" href="<?php echo esc_url( vs_zalo( vs_opt( 'zalo' ) ) ); ?>" target="_blank" rel="noopener">Chat Zalo ngay</a>
		</div>
		<div class="faq">
			<?php foreach ( vs_faqs() as $q => $a ) : ?>
				<details class="faq__item reveal">
					<summary><?php echo esc_html( $q ); ?></summary>
					<p><?php echo esc_html( $a ); ?></p>
				</details>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php
get_template_part( 'template-parts/cta-band' );
get_footer();
