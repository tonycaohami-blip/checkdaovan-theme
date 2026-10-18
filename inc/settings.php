<?php
/**
 * Trang cài đặt: liên hệ, thanh toán, bảng giá.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', function () {
	add_submenu_page( 'edit.php?post_type=vs_order', 'Cài đặt & Bảng giá', 'Cài đặt & Bảng giá', 'manage_options', 'vs-settings', 'vs_render_settings' );
} );

add_action( 'admin_init', function () {
	register_setting( 'vs_settings_group', 'vs_settings', array( 'sanitize_callback' => 'vs_sanitize_settings' ) );
	register_setting( 'vs_settings_group', 'vs_prices', array( 'sanitize_callback' => 'vs_sanitize_prices' ) );
} );

function vs_settings_fields() {
	return array(
		'Liên hệ'               => array(
			'hotline'      => array( 'Hotline', '0944 580 600' ),
			'zalo'         => array( 'Số Zalo', '0944 580 600' ),
			'email'        => array( 'Email hỗ trợ', VS_EMAIL ),
			'notify_email' => array( 'Email nhận thông báo đơn mới', VS_EMAIL ),
			'hours'        => array( 'Giờ làm việc', '7:00 - 23:00, tất cả các ngày' ),
		),
		'Thanh toán (VietQR)'   => array(
			'bank_code'    => array( 'Mã ngân hàng VietQR (VD: NCB, VCB, MB, TCB…)', 'NCB' ),
			'bank_name'    => array( 'Tên ngân hàng hiển thị', 'NCB Bank (Quốc Dân)' ),
			'bank_account' => array( 'Số tài khoản', '106936837029' ),
			'bank_holder'  => array( 'Chủ tài khoản (IN HOA, không dấu)', 'PHAM VAN KHANH' ),
		),
		'Mã giảm giá'           => array(
			'coupons' => array( 'Danh sách mã (mỗi dòng 1 mã)', "SINHVIEN10|10\nTET2027|15|28/02/2027\nGIAM20|20||reduce", 'textarea', 'Cú pháp: MÃ|phần trăm giảm|hạn dùng dd/mm/yyyy (tùy chọn)|check hoặc reduce (tùy chọn - chỉ áp dụng cho 1 dịch vụ)' ),
		),
		'Cộng tác viên (Affiliate)' => array(
			'aff_pct'      => array( 'Hoa hồng cho cộng tác viên (%)', '10', 'text', 'Tính trên giá trị đơn hàng hoàn thành của khách được giới thiệu.' ),
			'ref_discount' => array( 'Ưu đãi cho khách được giới thiệu (%)', '5', 'text', 'Tự áp dụng khi khách vào web qua link giới thiệu (nếu không dùng mã giảm giá khác).' ),
			'payout_min'   => array( 'Số dư tối thiểu để rút (đ)', '200000' ),
			'ref_days'     => array( 'Thời hạn ghi nhận giới thiệu (ngày)', '30' ),
		),
		'Thông báo Telegram'    => array(
			'tg_token' => array( 'Bot token', '123456:ABC-DEF…', 'text', 'Tạo bot qua @BotFather trên Telegram rồi dán token vào đây.' ),
			'tg_chat'  => array( 'Chat ID nhận thông báo', 'VD: 123456789', 'text', 'Nhắn /start cho bot, sau đó lấy chat id qua @userinfobot.' ),
		),
		'Thông tin doanh nghiệp & SEO' => array(
			'company'   => array( 'Tên đơn vị', get_bloginfo( 'name' ) ),
			'address'   => array( 'Địa chỉ', '' ),
			'tax_code'  => array( 'Mã số thuế / ĐKKD', '' ),
			'meta_desc' => array( 'Mô tả trang chủ (SEO, ~155 ký tự)', 'Kiểm tra tỷ lệ đạo văn và AI…', 'textarea' ),
			'facebook'  => array( 'Link Facebook', 'https://facebook.com/…' ),
			'tiktok'    => array( 'Link TikTok', 'https://tiktok.com/@…' ),
			'youtube'   => array( 'Link YouTube', 'https://youtube.com/@…' ),
		),
	);
}

function vs_sanitize_settings( $in ) {
	$out = array();
	foreach ( vs_settings_fields() as $group ) {
		foreach ( $group as $key => $f ) {
			$v           = isset( $in[ $key ] ) ? wp_unslash( $in[ $key ] ) : '';
			if ( false !== strpos( $key, 'email' ) ) {
				$out[ $key ] = sanitize_email( $v );
			} elseif ( isset( $f[2] ) && 'textarea' === $f[2] ) {
				$out[ $key ] = sanitize_textarea_field( $v );
			} elseif ( in_array( $key, array( 'facebook', 'tiktok', 'youtube' ), true ) ) {
				$out[ $key ] = esc_url_raw( $v );
			} else {
				$out[ $key ] = sanitize_text_field( $v );
			}
		}
	}
	return $out;
}

function vs_sanitize_prices( $in ) {
	$out = array();
	if ( ! is_array( $in ) ) {
		return $out;
	}
	array_walk_recursive( $in, function ( &$v ) {
		$v = preg_replace( '/[^\d.,]/', '', (string) $v );
		if ( '' === $v ) {
			return;
		}
		// "30.000" hoặc "30,000" là phân cách hàng nghìn; "1,3" / "1.3" là số thập phân.
		$v = preg_match( '/^\d{1,3}([.,]\d{3})+$/', $v ) ? str_replace( array( '.', ',' ), '', $v ) : str_replace( ',', '.', $v );
		$v = (string) (float) $v;
	} );
	return $in;
}

function vs_render_settings() {
	$opts  = get_option( 'vs_settings', array() );
	$ovr   = get_option( 'vs_prices', array() );
	$def   = vs_pricing_defaults();
	$cur   = vs_pricing();
	$input = function ( $name, $value, $placeholder ) {
		printf( '<input type="text" class="regular-text" name="%s" value="%s" placeholder="%s">', esc_attr( $name ), esc_attr( $value ), esc_attr( $placeholder ) );
	};
	$ov = function ( $path ) use ( $ovr ) {
		$v = $ovr;
		foreach ( $path as $k ) {
			if ( ! isset( $v[ $k ] ) ) {
				return '';
			}
			$v = $v[ $k ];
		}
		return $v;
	};
	?>
	<div class="wrap">
		<h1>Cài đặt & Bảng giá</h1>
		<p>Để trống ô giá để dùng giá mặc định (hiển thị mờ trong ô). Giá tính bằng VNĐ.</p>
		<?php if ( isset( $_GET['vs_imported'] ) ) : ?>
			<div class="notice notice-success"><p>Đã nhập / cập nhật <?php echo (int) $_GET['vs_imported']; ?> bài viết / trang mẫu.</p></div>
		<?php endif; ?>
		<p><a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=vs_import' ), 'vs_import' ) ); ?>">📄 Nhập / cập nhật 30 bài viết mẫu & trang chính sách</a> <span class="description">Tạo bài còn thiếu và cập nhật bài mẫu chưa bị sửa. Bài bạn đã sửa được giữ nguyên.</span></p>
		<p><a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=vs_import&force=1' ), 'vs_import' ) ); ?>" onclick="return confirm('Ghi đè toàn bộ 30 bài viết mẫu bằng bản mới nhất? Những chỉnh sửa của bạn trên các bài này sẽ mất (WordPress vẫn lưu bản cũ trong mục Revisions).');">♻️ Ghi đè bằng bản mới nhất</a> <span class="description">Chỉ áp dụng cho 30 bài mẫu theo đường dẫn, không ảnh hưởng bài khác.</span></p>
		<form method="post" action="options.php">
			<?php settings_fields( 'vs_settings_group' ); ?>

			<?php foreach ( vs_settings_fields() as $title => $group ) : ?>
				<h2><?php echo esc_html( $title ); ?></h2>
				<table class="form-table">
					<?php foreach ( $group as $key => $f ) : ?>
						<tr>
							<th><?php echo esc_html( $f[0] ); ?></th>
							<td>
								<?php
								$val = isset( $opts[ $key ] ) ? $opts[ $key ] : '';
								if ( isset( $f[2] ) && 'textarea' === $f[2] ) {
									printf( '<textarea class="large-text code" rows="4" name="%s" placeholder="%s">%s</textarea>', esc_attr( "vs_settings[$key]" ), esc_attr( $f[1] ), esc_textarea( $val ) );
								} else {
									$input( "vs_settings[$key]", $val, $f[1] );
								}
								if ( ! empty( $f[3] ) ) {
									echo '<p class="description">' . esc_html( $f[3] ) . '</p>';
								}
								?>
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
			<?php endforeach; ?>

			<h2>Giá kiểm tra (mỗi file, tối đa <?php echo (int) $cur['check']['pages_included']; ?> trang)</h2>
			<table class="widefat striped" style="max-width:820px">
				<thead><tr><th>Phần mềm</th><th>Chỉ đạo văn</th><th>Chỉ AI</th><th>Đạo văn + AI</th></tr></thead>
				<tbody>
				<?php foreach ( $def['tools'] as $k => $t ) : ?>
					<tr>
						<td><strong><?php echo esc_html( $t['name'] ); ?></strong></td>
						<?php foreach ( array( 'plag', 'ai', 'combo' ) as $f ) : ?>
							<td><?php $input( "vs_prices[tools][$k][$f]", $ov( array( 'tools', $k, $f ) ), $t[ $f ] ); ?></td>
						<?php endforeach; ?>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<p class="description">Nhập <b>0</b> nếu phần mềm không hỗ trợ hình thức đó (VD DoIT chỉ kiểm tra đạo văn) - khách sẽ không chọn được hình thức này.</p>

			<table class="form-table">
				<?php
				$check_labels = array(
					'pages_included' => 'Số trang trong giá cơ bản',
					'extra_pct'      => 'Phụ phí mỗi block trang vượt (%)',
					'express_pct'    => 'Phụ phí hỏa tốc ≤ 30 phút (%)',
				);
				foreach ( $check_labels as $k => $label ) :
					?>
					<tr><th><?php echo esc_html( $label ); ?></th><td><?php $input( "vs_prices[check][$k]", $ov( array( 'check', $k ) ), $def['check'][ $k ] ); ?></td></tr>
				<?php endforeach; ?>
			</table>

			<h2>Giá giảm tỷ lệ đạo văn</h2>
			<table class="form-table">
				<?php
				$reduce_labels = array(
					't30'        => 'Đơn giá / trang - mục tiêu dưới 30%',
					't20'        => 'Đơn giá / trang - mục tiêu dưới 20%',
					'ai_pct'     => 'Phụ phí giảm thêm tỷ lệ AI (%)',
					'linked_pct' => 'Giảm giá khi đặt từ đơn check (%)',
					'min'        => 'Giá tối thiểu một đơn',
				);
				foreach ( $reduce_labels as $k => $label ) :
					?>
					<tr><th><?php echo esc_html( $label ); ?></th><td><?php $input( "vs_prices[reduce][$k]", $ov( array( 'reduce', $k ) ), $def['reduce'][ $k ] ); ?></td></tr>
				<?php endforeach; ?>
				<?php foreach ( $def['reduce']['deadlines'] as $k => $d ) : ?>
					<tr><th>Hệ số thời hạn: <?php echo esc_html( $d['label'] ); ?></th><td><?php $input( "vs_prices[reduce][deadlines][$k]", $ov( array( 'reduce', 'deadlines', $k ) ), $d['mult'] ); ?></td></tr>
				<?php endforeach; ?>
			</table>
			<p class="description">Hệ số theo tỷ lệ hiện tại (cố định): ≤40% ×1.0 · 41-60% ×1.25 · 61-80% ×1.5 · trên 80% ×1.8. Có thể đổi qua filter <code>vs_pricing</code>.</p>

			<?php submit_button( 'Lưu cài đặt' ); ?>
		</form>
	</div>
	<?php
}
