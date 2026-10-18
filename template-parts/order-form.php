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
		<span><b>Giảm tỷ lệ đạo văn + AI</b><small>Dưới 30% / 20% · kèm giảm AI</small></span>
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
							<span class="tool__logo tool__logo--sm" style="--c:<?php echo esc_attr( $tool_meta[ $k ][1] ); ?>"><?php echo esc_html( $tool_meta[ $k ][0] ); ?></span>
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
			<legend><span class="num">1</span> Mục tiêu tỷ lệ đạo văn</legend>
			<div class="goal-grid">
				<label class="goal">
					<input type="radio" name="target" value="30" checked>
					<span class="goal__box"><small>Giảm xuống</small><b>&lt; 30%</b><em>từ <?php echo esc_html( vs_money( $reduce['t30'] ) ); ?>/trang</em><span>Đạt yêu cầu phần lớn khóa luận đại học</span></span>
				</label>
				<label class="goal">
					<input type="radio" name="target" value="20">
					<span class="goal__box"><small>Giảm xuống</small><b>&lt; 20%</b><em>từ <?php echo esc_html( vs_money( $reduce['t20'] ) ); ?>/trang</em><span>Luận văn thạc sĩ, luận án, bài báo</span></span>
				</label>
			</div>
		</fieldset>

		<div class="row">
			<label class="field">
				<span class="label">Tỷ lệ đạo văn hiện tại (%)</span>
				<input type="number" name="current" min="1" max="100" step="0.1" value="45" required>
			</label>
			<label class="field">
				<span class="label">Số trang cần chỉnh sửa</span>
				<input type="number" name="pages" min="1" max="<?php echo (int) $reduce['max_pages']; ?>" value="50" required>
				<small class="hint" data-pages-hint>Chỉ gửi chương cần sửa để tiết kiệm chi phí</small>
			</label>
		</div>

		<fieldset class="field">
			<legend><span class="num">2</span> Thời hạn hoàn thành</legend>
			<div class="seg seg--3">
				<?php foreach ( $reduce['deadlines'] as $k => $d ) : ?>
					<label><input type="radio" name="deadline" value="<?php echo esc_attr( $k ); ?>" <?php checked( 'standard', $k ); ?>><span><?php echo esc_html( $d['label'] ); ?><?php echo $d['mult'] > 1 ? ' <small>+' . (int) round( ( $d['mult'] - 1 ) * 100 ) . '%</small>' : ''; ?></span></label>
				<?php endforeach; ?>
			</div>
		</fieldset>

		<label class="field switch">
			<input type="checkbox" name="with_ai" value="1">
			<span class="switch__ui" aria-hidden="true"></span>
			<span><b>Giảm cả tỷ lệ AI</b> xuống dưới 20%<br><small>+<?php echo (int) $reduce['ai_pct']; ?>% phí</small></span>
		</label>

		<fieldset class="field" data-file-field>
			<legend><span class="num">3</span> Tải tài liệu <small>(bắt buộc đủ 2 file)</small></legend>
			<div class="need-report">
				<span class="need-report__icon"><?php echo vs_icon( 'file', 20 ); // phpcs:ignore ?></span>
				<div><b>Chưa có báo cáo PDF thể hiện phần đạo văn / AI?</b><span>Bạn cần kiểm tra trước để biên tập viên biết chính xác đoạn nào cần sửa. Khi đơn kiểm tra hoàn thành, bấm “Giảm dưới 30% / 20%” trong đơn để chuyển sang đây, không cần tải lại file và được giảm thêm <?php echo (int) $reduce['linked_pct']; ?>%.</span></div>
				<button type="button" class="btn btn--primary btn--sm" data-goto-tab="check">Kiểm tra trước</button>
			</div>
			<div class="drop-pair">
				<?php
				get_template_part( 'template-parts/order', 'file', array(
					'name'   => 'file',
					'accept' => '.doc,.docx',
					'title'  => '1. File Word bản gốc',
					'sub'    => '.doc, .docx · bản đầy đủ bạn sẽ nộp',
					'icon'   => 'word',
				) );
				get_template_part( 'template-parts/order', 'file', array(
					'name'   => 'report',
					'accept' => '.pdf',
					'title'  => '2. Báo cáo PDF đạo văn / AI',
					'sub'    => 'Báo cáo Turnitin, iThenticate… có tô màu các đoạn trùng hoặc AI',
					'icon'   => 'pdf',
				) );
				?>
			</div>
		</fieldset>

		<label class="field" data-linked-input>
			<span class="label">Mã đơn kiểm tra trước đó <small>(không bắt buộc - nhập để được giảm <?php echo (int) $reduce['linked_pct']; ?>%)</small></span>
			<input type="text" name="linked" placeholder="VD: VS1018AB2C" autocomplete="off">
		</label>

		<?php get_template_part( 'template-parts/order', 'common' ); ?>
	</div>

	<?php get_template_part( 'template-parts/order', 'summary', array( 'note' => 'Cam kết đạt tỷ lệ mục tiêu trên Turnitin. Không đạt - hoàn tiền 100%.' ) ); ?>
</form>
