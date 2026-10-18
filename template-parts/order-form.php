<?php
/**
 * Hai form đặt hàng (kiểm tra / giảm tỷ lệ) + tạm tính. Dùng ở trang Đặt hàng.
 */
$p         = vs_pricing();
$tools     = $p['tools'];
$check     = $p['check'];
$reduce    = $p['reduce'];
$modes     = vs_check_modes();
$tool_meta = vs_tool_meta();
$min_check = vs_min_check_price();
?>
<div class="tabs" role="tablist">
	<button class="tabs__btn is-active" role="tab" aria-selected="true" data-tab="check">
		<span class="tabs__icon"><?php echo vs_icon( 'target' ); // phpcs:ignore ?></span>
		<span><b>Kiểm tra đạo văn &amp; AI</b><small><?php echo count( $tools ); ?> phần mềm · từ <?php echo esc_html( vs_money( $min_check ) ); ?></small></span>
	</button>
	<button class="tabs__btn" role="tab" aria-selected="false" data-tab="reduce">
		<span class="tabs__icon"><?php echo vs_icon( 'pen' ); // phpcs:ignore ?></span>
		<span><b>Giảm tỷ lệ đạo văn + AI</b><small>Đạo văn / AI · tự chọn mục tiêu</small></span>
	</button>
</div>

<!-- ---- Form kiểm tra ---- -->
<form class="order is-active" id="form-check" data-type="check" novalidate>
	<div class="order__main">
		<fieldset class="field">
			<legend><span class="num">1</span> Chọn phần mềm <small>(mỗi đơn 1 phần mềm)</small></legend>
			<div class="pick-grid">
				<?php foreach ( $tools as $k => $t ) : ?>
					<label class="pick">
						<input type="radio" name="tools[]" value="<?php echo esc_attr( $k ); ?>" <?php checked( 'turnitin', $k ); ?>>
						<span class="pick__box">
							<span class="tool__logo tool__logo--sm<?php echo esc_attr( vs_tool_mark_class( $k ) ); ?>" style="--c:<?php echo esc_attr( $tool_meta[ $k ][1] ); ?>"><?php echo vs_tool_mark( $k ); // phpcs:ignore -- đã escape. ?></span>
							<span class="pick__name"><?php echo esc_html( $t['name'] ); ?></span>
							<span class="pick__sub"><?php echo esc_html( $tool_meta[ $k ][2] ); ?></span>
							<span class="pick__check" aria-hidden="true"></span>
						</span>
					</label>
				<?php endforeach; ?>
			</div>
		</fieldset>

		<fieldset class="field">
			<legend><span class="num">2</span> Hình thức kiểm tra</legend>
			<div class="seg">
				<?php foreach ( $modes as $k => $l ) : ?>
					<label><input type="radio" name="mode" value="<?php echo esc_attr( $k ); ?>" <?php checked( 'combo', $k ); ?>><span><?php echo esc_html( $l ); ?><?php echo 'combo' === $k ? ' <small>tiết kiệm</small>' : ''; ?></span></label>
				<?php endforeach; ?>
			</div>
		</fieldset>

		<fieldset class="field">
			<legend><span class="num">3</span> Tải tài liệu</legend>
			<?php get_template_part( 'template-parts/order', 'file' ); ?>
			<?php get_template_part( 'template-parts/order', 'rules' ); ?>
		</fieldset>

		<div class="row">
			<label class="field">
				<span class="label">Số trang của tài liệu</span>
				<input type="number" name="pages" min="1" max="2000" value="50" required>
				<small class="hint" data-pages-hint>Tự động đọc khi bạn tải file Word / PDF</small>
			</label>
			<label class="field switch">
				<input type="checkbox" name="express" value="1">
				<span class="switch__ui" aria-hidden="true"></span>
				<span><b>Hỏa tốc</b> - trả trong 30 phút<br><small>+<?php echo (int) $check['express_pct']; ?>% phí</small></span>
			</label>
		</div>

		<?php get_template_part( 'template-parts/order', 'common' ); ?>
	</div>

	<?php get_template_part( 'template-parts/order', 'summary', array( 'note' => 'Bạn chỉ thanh toán sau khi xem giá. Kết quả gửi qua trang tra cứu, email và Zalo.' ) ); ?>
</form>

<!-- ---- Form giảm tỷ lệ ---- -->
<form class="order" id="form-reduce" data-type="reduce" novalidate hidden>
	<div class="order__main">
		<div class="linked-box" data-linked-box hidden>
			<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><path d="M20 6L9 17l-5-5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
			<div>Đặt từ đơn kiểm tra <b data-linked-code></b> - được giảm <?php echo (int) $reduce['linked_pct']; ?>%. Dùng file gốc và báo cáo của đơn này, không cần tải lại.</div>
			<button type="button" class="link-btn" data-unlink>Bỏ liên kết</button>
		</div>

		<fieldset class="field">
			<legend><span class="num">1</span> Bạn muốn giảm</legend>
			<div class="goal-tabs">
				<label><input type="radio" name="goal" value="plag" checked><span><b>Tỷ lệ đạo văn</b><small>Viết lại, bổ sung trích dẫn</small></span></label>
				<label><input type="radio" name="goal" value="ai"><span><b>Tỷ lệ AI</b><small>Rà soát đoạn bị đánh dấu</small></span></label>
				<label><input type="radio" name="goal" value="both"><span><b>Đạo văn + AI</b><small>Tiết kiệm <?php echo (int) ( 100 - $reduce['both_pct'] ); ?>% phần AI</small></span></label>
			</div>
		</fieldset>

		<fieldset class="field">
			<legend><span class="num">2</span> Tỷ lệ hiện tại &amp; mục tiêu mong muốn</legend>
			<div class="goal-cards">
				<div class="goal-card" data-goal-box="plag">
					<div class="goal-card__head"><b>Đạo văn</b><span class="goal-card__arrow" data-goal-arrow="plag"></span></div>
					<div class="goal-card__row">
						<label><span>Hiện tại</span><span class="pct-in"><input type="number" name="current" min="1" max="100" step="0.1" value="45" inputmode="decimal" required><i>%</i></span></label>
						<label><span>Mục tiêu (nhỏ hơn hiện tại)</span><span class="pct-in pct-in--target"><input type="number" name="target" min="0" max="99" step="1" value="20" inputmode="numeric" required><i>%</i></span></label>
					</div>
					<div class="chips" data-chips="target"><span>Chọn nhanh:</span><button type="button" data-v="30">30%</button><button type="button" data-v="20">20%</button><button type="button" data-v="15">15%</button><button type="button" data-v="10">10%</button></div>
				</div>
				<div class="goal-card" data-goal-box="ai" hidden>
					<div class="goal-card__head"><b>Tỷ lệ AI</b><span class="goal-card__arrow" data-goal-arrow="ai"></span></div>
					<div class="goal-card__row">
						<label><span>Hiện tại</span><span class="pct-in"><input type="number" name="current_ai" min="1" max="100" step="0.1" value="50" inputmode="decimal"><i>%</i></span></label>
						<label><span>Mục tiêu (nhỏ hơn hiện tại)</span><span class="pct-in pct-in--target"><input type="number" name="target_ai" min="0" max="99" step="1" value="20" inputmode="numeric"><i>%</i></span></label>
					</div>
					<div class="chips" data-chips="target_ai"><span>Chọn nhanh:</span><button type="button" data-v="20">20%</button><button type="button" data-v="15">15%</button><button type="button" data-v="10">10%</button><button type="button" data-v="5">5%</button></div>
				</div>
			</div>
			<small class="hint">Mục tiêu càng thấp, khối lượng chỉnh sửa càng lớn. Giá được tính lại ngay khi bạn thay đổi.</small>
		</fieldset>

		<div class="row">
			<label class="field">
				<span class="label">Số trang cần chỉnh sửa</span>
				<input type="number" name="pages" min="1" max="<?php echo (int) $reduce['max_pages']; ?>" value="50" required>
				<small class="hint" data-pages-hint>Chỉ gửi chương cần sửa để tiết kiệm chi phí</small>
			</label>
		</div>

		<fieldset class="field">
			<legend><span class="num">3</span> Thời hạn hoàn thành</legend>
			<div class="seg seg--3">
				<?php foreach ( $reduce['deadlines'] as $k => $d ) : ?>
					<label><input type="radio" name="deadline" value="<?php echo esc_attr( $k ); ?>" <?php checked( 'standard', $k ); ?>><span><?php echo esc_html( $d['label'] ); ?><?php echo $d['mult'] > 1 ? ' <small>+' . (int) round( ( $d['mult'] - 1 ) * 100 ) . '%</small>' : ''; ?></span></label>
				<?php endforeach; ?>
			</div>
		</fieldset>

		<fieldset class="field" data-file-field>
			<legend><span class="num">4</span> Tải tài liệu <small>(bắt buộc đủ 2 file)</small></legend>
			<div class="need-report">
				<span class="need-report__icon"><?php echo vs_icon( 'file', 20 ); // phpcs:ignore ?></span>
				<div><b>Chưa có báo cáo PDF thể hiện phần đạo văn / AI?</b><span>Bạn cần kiểm tra trước để biên tập viên biết chính xác đoạn nào cần sửa. Khi có kết quả, bấm nút “Giảm tỷ lệ” ngay cạnh kết quả để chuyển sang đây, không cần tải lại file và được giảm thêm <?php echo (int) $reduce['linked_pct']; ?>%.</span></div>
				<button type="button" class="btn btn--primary btn--sm" data-goto-tab="check">Kiểm tra trước</button>
			</div>
			<div class="drop-pair">
				<?php
				get_template_part( 'template-parts/order', 'file', array(
					'name'   => 'file',
					'accept' => '.docx',
					'title'  => '1. File Word bản gốc',
					'sub'    => '.docx · tối thiểu 300 từ, tối đa 30.000 từ',
					'icon'   => 'word',
				) );
				get_template_part( 'template-parts/order', 'file', array(
					'name'   => 'report',
					'accept' => '.pdf',
					'title'  => '2. Báo cáo PDF đạo văn / AI',
					'sub'    => '.pdf · báo cáo có tô màu các đoạn trùng hoặc AI · dưới ' . vs_max_upload_mb() . ' MB',
					'icon'   => 'pdf',
				) );
				?>
			</div>
			<?php get_template_part( 'template-parts/order', 'rules', array( 'reduce' => true ) ); ?>
		</fieldset>

		<label class="field" data-linked-input>
			<span class="label">Mã đơn kiểm tra trước đó <small>(không bắt buộc - nhập để được giảm <?php echo (int) $reduce['linked_pct']; ?>%)</small></span>
			<input type="text" name="linked" placeholder="VD: VS1018AB2C" autocomplete="off">
		</label>

		<div class="integrity">
			<b>Cam kết liêm chính học thuật</b>
			<ul>
				<li>Dịch vụ là <strong>hỗ trợ biên tập ngôn ngữ và trích dẫn</strong>; bạn là tác giả và chịu trách nhiệm về nội dung.</li>
				<li>Chúng tôi không viết thay, không tạo số liệu hay nguồn giả, không dùng mẹo che giấu (chèn ký tự, chữ ẩn, chuyển ảnh…).</li>
				<li>Không hỗ trợ che giấu nội dung do AI tạo ra. Hãy khai báo việc sử dụng AI và hỗ trợ biên tập nếu trường hoặc tạp chí yêu cầu.</li>
			</ul>
			<label class="check check--req"><input type="checkbox" name="integrity" value="1" required data-msg="Vui lòng xác nhận cam kết liêm chính học thuật."> Tôi xác nhận bài là do tôi nghiên cứu và viết, đã đọc <a href="<?php echo esc_url( vs_page_url( 'liem-chinh-hoc-thuat' ) ); ?>" target="_blank">Chính sách liêm chính học thuật</a> và sẽ rà soát bản biên tập trước khi nộp.</label>
		</div>

		<?php get_template_part( 'template-parts/order', 'common' ); ?>
	</div>

	<?php get_template_part( 'template-parts/order', 'summary', array( 'note' => 'Mục tiêu là mức tham khảo. Chưa đạt trên cùng phần mềm và thiết lập: biên tập bổ sung miễn phí hoặc hoàn phí theo chính sách.' ) ); ?>
</form>
