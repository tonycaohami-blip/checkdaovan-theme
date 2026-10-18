<?php
/**
 * Thông tin liên hệ chung của 2 form đặt hàng (tự điền khi đã đăng nhập).
 */
$u     = wp_get_current_user();
$name  = $u->exists() ? $u->display_name : '';
$phone = $u->exists() ? vs_user_phone( $u->ID ) : '';
$email = $u->exists() ? $u->user_email : '';
?>
<fieldset class="field">
	<legend><span class="num">✓</span> Thông tin nhận kết quả</legend>
	<?php if ( ! $u->exists() ) : ?>
		<p class="login-hint">Đã có tài khoản? <a href="<?php echo esc_url( vs_account_url( '', array( 'redirect_to' => rawurlencode( vs_page_url( 'dat-hang' ) ) ) ) ); ?>">Đăng nhập</a> để tự điền thông tin và quản lý đơn hàng dễ dàng.</p>
	<?php endif; ?>
	<div class="row">
		<label class="field">
			<span class="label">Họ và tên *</span>
			<input type="text" name="name" required autocomplete="name" placeholder="Nguyễn Văn A" value="<?php echo esc_attr( $name ); ?>">
		</label>
		<label class="field">
			<span class="label">Số điện thoại / Zalo *</span>
			<input type="tel" name="phone" required autocomplete="tel" placeholder="09xx xxx xxx" value="<?php echo esc_attr( $phone ); ?>">
		</label>
	</div>
</fieldset>
<label class="field">
	<span class="label">Email nhận kết quả</span>
	<input type="email" name="email" autocomplete="email" placeholder="ban@email.com" value="<?php echo esc_attr( $email ); ?>">
</label>
<label class="field">
	<span class="label">Ghi chú <small>(phần mềm trường dùng, bộ lọc loại trừ trích dẫn/tài liệu tham khảo, chương cần sửa…)</small></span>
	<textarea name="note" rows="3"></textarea>
</label>
<label class="hp" aria-hidden="true">Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
