<?php
/**
 * Khung tạm tính bên phải form đặt hàng.
 */
$note = isset( $args['note'] ) ? $args['note'] : '';
?>
<aside class="order__side">
	<div class="summary">
		<div class="summary__head">
			<h3>Tạm tính</h3>
			<span class="summary__live"><i></i> Cập nhật tự động</span>
		</div>
		<ul class="summary__lines" data-lines></ul>
		<div class="coupon" data-coupon>
			<input type="text" name="coupon" placeholder="Mã giảm giá" autocomplete="off" aria-label="Mã giảm giá">
			<button type="button" class="coupon__btn" data-coupon-apply>Áp dụng</button>
		</div>
		<p class="coupon__msg" data-coupon-msg hidden></p>
		<div class="summary__total"><span>Tổng cộng</span><b data-total>0đ</b></div>
		<p class="summary__error" data-error hidden></p>
		<button type="submit" class="btn btn--accent btn--lg btn--block">
			Đặt hàng &amp; thanh toán
			<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
		</button>
		<p class="summary__note"><?php echo esc_html( $note ); ?></p>
		<ul class="summary__trust">
			<li>🔒 Bảo mật tài liệu</li>
			<li>⚡ Xử lý ngay sau thanh toán</li>
		</ul>
	</div>
</aside>
