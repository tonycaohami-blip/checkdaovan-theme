<?php
/**
 * Đăng nhập / Đăng ký.
 */
$register = ! empty( $_GET['dang-ky'] ) || ( ! empty( $_GET['vs_msg'] ) && 0 === strpos( sanitize_key( $_GET['vs_msg'] ), 'reg_' ) );
$redirect = isset( $_GET['redirect_to'] ) ? esc_url_raw( rawurldecode( wp_unslash( $_GET['redirect_to'] ) ) ) : '';
$post_url = admin_url( 'admin-post.php' );
?>
<section class="auth">
	<div class="auth__side">
		<div class="auth__side-inner">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="brand"><?php get_template_part( 'template-parts/logo', null, array( 'variant' => 'light' ) ); ?></a>
			<h2>Quản lý mọi tài liệu của bạn ở một nơi</h2>
			<ul class="auth__benefits">
				<li><?php echo vs_icon( 'list', 22 ); // phpcs:ignore ?><span><b>Theo dõi đơn hàng</b>Trạng thái, kết quả, tải báo cáo mọi lúc</span></li>
				<li><?php echo vs_icon( 'pen', 22 ); // phpcs:ignore ?><span><b>Giảm tỷ lệ 1 chạm</b>Từ đơn kiểm tra, giảm thêm <?php echo (int) vs_pricing()['reduce']['linked_pct']; ?>%</span></li>
				<li><?php echo vs_icon( 'gift', 22 ); // phpcs:ignore ?><span><b>Cộng tác viên</b>Nhận <?php echo esc_html( vs_opt( 'aff_pct' ) ); ?>% hoa hồng khi giới thiệu bạn bè</span></li>
				<li><?php echo vs_icon( 'shield', 22 ); // phpcs:ignore ?><span><b>Bảo mật</b>Tài liệu chỉ bạn và người xử lý được xem</span></li>
			</ul>
		</div>
	</div>

	<div class="auth__main">
		<div class="auth__card" data-auth>
			<div class="auth__tabs" role="tablist">
				<button type="button" class="<?php echo $register ? '' : 'is-active'; ?>" data-auth-tab="login">Đăng nhập</button>
				<button type="button" class="<?php echo $register ? 'is-active' : ''; ?>" data-auth-tab="register">Đăng ký</button>
			</div>
			<?php vs_notice(); ?>

			<form method="post" action="<?php echo esc_url( $post_url ); ?>" class="auth__form" data-auth-panel="login" <?php echo $register ? 'hidden' : ''; ?>>
				<h1>Chào mừng trở lại 👋</h1>
				<p class="muted">Đăng nhập bằng email hoặc số điện thoại.</p>
				<input type="hidden" name="action" value="vs_login">
				<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect ); ?>">
				<?php wp_nonce_field( 'vs_login', '_vsn' ); ?>
				<label class="field"><span class="label">Email hoặc số điện thoại</span><input type="text" name="login" required autocomplete="username"></label>
				<label class="field"><span class="label">Mật khẩu</span><span class="pw"><input type="password" name="password" required autocomplete="current-password"><button type="button" class="pw__toggle" data-pw aria-label="Hiện mật khẩu">Hiện</button></span></label>
				<div class="auth__row">
					<label class="check"><input type="checkbox" name="remember" value="1" checked> Ghi nhớ đăng nhập</label>
					<a href="<?php echo esc_url( wp_lostpassword_url( vs_account_url() ) ); ?>">Quên mật khẩu?</a>
				</div>
				<button class="btn btn--primary btn--lg btn--block" type="submit">Đăng nhập</button>
				<p class="auth__switch">Chưa có tài khoản? <a href="#" data-auth-tab="register">Đăng ký miễn phí</a></p>
			</form>

			<form method="post" action="<?php echo esc_url( $post_url ); ?>" class="auth__form" data-auth-panel="register" <?php echo $register ? '' : 'hidden'; ?>>
				<h1>Tạo tài khoản miễn phí</h1>
				<p class="muted">Các đơn đã đặt bằng số điện thoại này sẽ tự gắn vào tài khoản.</p>
				<input type="hidden" name="action" value="vs_register">
				<?php wp_nonce_field( 'vs_register', '_vsn' ); ?>
				<label class="field"><span class="label">Họ và tên</span><input type="text" name="name" required autocomplete="name" placeholder="Nguyễn Văn A"></label>
				<div class="row">
					<label class="field"><span class="label">Số điện thoại</span><input type="tel" name="phone" required autocomplete="tel" placeholder="09xx xxx xxx"></label>
					<label class="field"><span class="label">Email</span><input type="email" name="email" required autocomplete="email" placeholder="ban@email.com"></label>
				</div>
				<label class="field"><span class="label">Mật khẩu <small>(ít nhất 8 ký tự)</small></span><span class="pw"><input type="password" name="password" required minlength="8" autocomplete="new-password"><button type="button" class="pw__toggle" data-pw aria-label="Hiện mật khẩu">Hiện</button></span></label>
				<?php if ( vs_ref_user_from_cookie() ) : ?>
					<div class="notice notice--ok">🎁 Bạn được giới thiệu bởi cộng tác viên - nhận ưu đãi <?php echo esc_html( vs_opt( 'ref_discount' ) ); ?>% cho các đơn hàng.</div>
				<?php endif; ?>
				<label class="check"><input type="checkbox" name="terms" value="1" required> Tôi đồng ý với <a href="<?php echo esc_url( vs_page_url( 'dieu-khoan-dich-vu' ) ); ?>" target="_blank">Điều khoản dịch vụ</a> và đồng ý cho xử lý dữ liệu cá nhân theo <a href="<?php echo esc_url( vs_page_url( 'chinh-sach-bao-mat' ) ); ?>" target="_blank">Chính sách bảo mật</a></label>
				<label class="hp" aria-hidden="true">Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
				<button class="btn btn--accent btn--lg btn--block" type="submit">Tạo tài khoản</button>
				<p class="auth__switch">Đã có tài khoản? <a href="#" data-auth-tab="login">Đăng nhập</a></p>
			</form>
		</div>
	</div>
</section>
