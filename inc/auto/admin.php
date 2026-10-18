<?php
/**
 * Quản trị > Đơn hàng > Tự động 24/7: cấu hình API, webhook thanh toán, kiểm tra kết nối, nhật ký.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_init', function () {
	register_setting( 'vs_auto_group', 'vs_auto', array(
		'sanitize_callback' => function ( $in ) {
			$out = array( 'enabled' => empty( $in['enabled'] ) ? '' : '1' );
			foreach ( array( 'sepay_key', 'sepay_token', 'casso_token' ) as $k ) {
				$out[ $k ] = sanitize_text_field( isset( $in[ $k ] ) ? $in[ $k ] : '' );
			}
			foreach ( array( 'turnitin', 'ithenticate', 'copyleaks', 'originality' ) as $t ) {
				$out[ $t ] = array();
				foreach ( (array) ( isset( $in[ $t ] ) ? $in[ $t ] : array() ) as $k => $v ) {
					$out[ $t ][ sanitize_key( $k ) ] = 'base' === $k ? untrailingslashit( preg_replace( '#/api/v1/?$#', '', esc_url_raw( trim( $v ) ) ) ) : sanitize_text_field( trim( $v ) );
				}
			}
			delete_transient( 'vs_cl_token' );
			return $out;
		},
	) );
} );

add_action( 'admin_menu', function () {
	add_submenu_page( 'edit.php?post_type=vs_order', 'Tự động 24/7', 'Tự động 24/7', 'manage_options', 'vs-auto', 'vs_auto_admin_page' );
}, 15 );

/* Nút "Kiểm tra kết nối". */
add_action( 'admin_post_vs_auto_test', function () {
	check_admin_referer( 'vs_auto_test' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die();
	}
	$p = sanitize_key( $_GET['p'] );
	$r = new WP_Error( 'x', 'Chưa cấu hình' );
	if ( in_array( $p, array( 'turnitin', 'ithenticate' ), true ) ) {
		$r = vs_tca_test( $p );
	} elseif ( 'copyleaks' === $p ) {
		$r = vs_cl_test();
	} elseif ( 'originality' === $p ) {
		$r = vs_oai_test();
	}
	$msg = is_wp_error( $r ) ? '❌ ' . $p . ': ' . $r->get_error_message() : '✅ ' . $p . ': ' . ( is_string( $r ) ? $r : 'kết nối thành công' );
	vs_auto_log( 'Kiểm tra kết nối - ' . $msg );
	set_transient( 'vs_auto_test_msg', $msg, 60 );
	wp_safe_redirect( admin_url( 'edit.php?post_type=vs_order&page=vs-auto' ) );
	exit;
} );

/* Chạy hàng đợi ngay (không chờ cron). */
add_action( 'admin_post_vs_auto_run', function () {
	check_admin_referer( 'vs_auto_run' );
	if ( current_user_can( 'manage_options' ) ) {
		delete_transient( 'vs_auto_lock' );
		vs_auto_tick();
	}
	wp_safe_redirect( admin_url( 'edit.php?post_type=vs_order&page=vs-auto' ) );
	exit;
} );

/* Chạy lại tự động cho 1 đơn. */
add_action( 'admin_post_vs_auto_retry', function () {
	$id = (int) $_GET['order'];
	check_admin_referer( 'vs_auto_retry_' . $id );
	if ( ! current_user_can( 'edit_post', $id ) ) {
		wp_die();
	}
	if ( 'dang_xu_ly' === vs_meta( $id, 'status' ) ) {
		vs_auto_enqueue( $id );
	}
	wp_safe_redirect( admin_url( 'post.php?post=' . $id . '&action=edit' ) );
	exit;
} );

function vs_auto_admin_page() {
	$c    = vs_auto_cfg();
	$name = function ( $t, $k ) {
		return 'vs_auto[' . $t . '][' . $k . ']';
	};
	$val  = function ( $t, $k ) use ( $c ) {
		return isset( $c[ $t ][ $k ] ) ? $c[ $t ][ $k ] : '';
	};
	$last = (int) get_option( 'vs_auto_last_tick' );
	$msg  = get_transient( 'vs_auto_test_msg' );
	$test = function ( $p ) {
		return '<a class="button" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=vs_auto_test&p=' . $p ), 'vs_auto_test' ) ) . '">Kiểm tra kết nối</a>';
	};
	?>
	<div class="wrap vs-auto">
		<h1>Tự động 24/7</h1>
		<?php if ( $msg ) : ?><div class="notice notice-info"><p><?php echo esc_html( $msg ); ?></p></div><?php endif; ?>

		<div class="vs-flow">
			<span>Khách đặt hàng</span>→<span class="<?php echo vs_auto_pay_on() ? 'on' : ''; ?>">Tự xác nhận chuyển khoản</span>→<span class="<?php echo vs_auto_on() ? 'on' : ''; ?>">Kiểm tra qua API</span>→<span class="<?php echo vs_auto_on() ? 'on' : ''; ?>">Gửi PDF qua email + tài khoản</span>
		</div>
		<p>
			Cron gần nhất: <strong><?php echo $last ? esc_html( human_time_diff( $last ) . ' trước' ) : 'chưa chạy'; ?></strong>
			<?php $next = wp_next_scheduled( 'vs_auto_tick' ); ?>
			· Lượt kế tiếp: <strong><?php echo $next ? esc_html( wp_date( 'H:i:s', $next ) ) : 'chưa lên lịch'; ?></strong>
			<?php if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) : ?>· <em>WP-Cron mặc định đang tắt (cần cron máy chủ)</em><?php endif; ?>
			· <a class="button button-small" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=vs_auto_run' ), 'vs_auto_run' ) ); ?>">Chạy hàng đợi ngay</a><br>
			Để chạy đều mỗi phút trên hosting thật: thêm <code>define( 'DISABLE_WP_CRON', true );</code> vào wp-config.php và tạo cron máy chủ:
			<code>* * * * * wget -q -O - <?php echo esc_html( site_url( 'wp-cron.php?doing_wp_cron' ) ); ?> &gt;/dev/null 2>&amp;1</code>
		</p>

		<form method="post" action="options.php">
			<?php settings_fields( 'vs_auto_group' ); ?>
			<h2>Bật tự động</h2>
			<label><input type="checkbox" name="vs_auto[enabled]" value="1" <?php checked( $c['enabled'], '1' ); ?>> Bật xử lý tự động cho các phần mềm đã cấu hình bên dưới</label>
			<p class="description">Phần mềm/loại kiểm tra chưa cấu hình sẽ tự chuyển sang hàng đợi xử lý tay và báo Telegram.</p>

			<h2>1. Tự xác nhận thanh toán</h2>
			<table class="form-table">
				<tr><th>SePay - API Key</th><td><input class="regular-text" name="vs_auto[sepay_key]" value="<?php echo esc_attr( $c['sepay_key'] ); ?>"><p class="description">Webhook URL: <code><?php echo esc_html( rest_url( 'vansach/v1/pay/sepay' ) ); ?></code> · Chứng thực: API Key</p></td></tr>
				<tr><th>SePay - API Token</th><td><input class="regular-text" type="password" autocomplete="off" name="vs_auto[sepay_token]" value="<?php echo esc_attr( $c['sepay_token'] ); ?>"><p class="description">Dò giao dịch mới mỗi phút và khi khách bấm “Tôi đã chuyển khoản” (dự phòng khi webhook bị lỡ). Tạo tại my.sepay.vn &gt; API Access.</p></td></tr>
				<tr><th>Casso - Secure token</th><td><input class="regular-text" name="vs_auto[casso_token]" value="<?php echo esc_attr( $c['casso_token'] ); ?>"><p class="description">Webhook URL: <code><?php echo esc_html( rest_url( 'vansach/v1/pay/casso' ) ); ?></code></p></td></tr>
			</table>

			<h2>2. Turnitin &amp; iThenticate (Turnitin Core API)</h2>
			<div class="notice notice-warning inline"><p><strong>Chỉ dùng API chính thức.</strong> Cần hợp đồng tích hợp với Turnitin để nhận địa chỉ tenant và API key. Không dùng tài khoản giảng viên/sinh viên để đăng nhập tự động: vi phạm điều khoản và có thể bị khóa tài khoản.</p></div>
			<?php foreach ( array( 'turnitin' => 'Turnitin', 'ithenticate' => 'iThenticate' ) as $t => $label ) : ?>
				<table class="form-table">
					<tr><th><?php echo esc_html( $label ); ?></th><td><label><input type="checkbox" name="<?php echo esc_attr( $name( $t, 'on' ) ); ?>" value="1" <?php checked( $val( $t, 'on' ), '1' ); ?>> Bật tự động <?php echo vs_auto_tool_ready( $t ) ? '<span class="vs-ok">● Sẵn sàng</span>' : ''; ?></label> &nbsp; <?php echo $test( $t ); // phpcs:ignore ?></td></tr>
					<tr><th>Địa chỉ tenant</th><td><input class="regular-text" placeholder="https://ten-cua-ban.turnitin.com" name="<?php echo esc_attr( $name( $t, 'base' ) ); ?>" value="<?php echo esc_attr( $val( $t, 'base' ) ); ?>"><p class="description">Chỉ phần gốc, không thêm /api/v1 (hệ thống tự thêm). VD tài khoản Crossref: https://crossref-1234.turnitin.com</p></td></tr>
					<tr><th>API key</th><td><input class="regular-text" type="password" autocomplete="off" name="<?php echo esc_attr( $name( $t, 'key' ) ); ?>" value="<?php echo esc_attr( $val( $t, 'key' ) ); ?>"></td></tr>
					<tr><th>Owner ID</th><td><input class="regular-text" placeholder="VD: checkdaovan-he-thong" name="<?php echo esc_attr( $name( $t, 'owner' ) ); ?>" value="<?php echo esc_attr( $val( $t, 'owner' ) ); ?>"><p class="description">Mã định danh cố định do bạn tự đặt cho người nộp bài (không dấu, không khoảng trắng). Bài nộp được tạo ở chế độ không lưu vào kho (add_to_index = false).</p></td></tr>
				</table>
			<?php endforeach; ?>

			<h2>3. Copyleaks (API v3)</h2>
			<table class="form-table">
				<tr><th>Copyleaks</th><td><label><input type="checkbox" name="<?php echo esc_attr( $name( 'copyleaks', 'on' ) ); ?>" value="1" <?php checked( $val( 'copyleaks', 'on' ), '1' ); ?>> Bật tự động <?php echo vs_auto_tool_ready( 'copyleaks' ) ? '<span class="vs-ok">● Sẵn sàng</span>' : ''; ?></label> &nbsp; <?php echo $test( 'copyleaks' ); // phpcs:ignore ?></td></tr>
				<tr><th>Email tài khoản</th><td><input class="regular-text" name="<?php echo esc_attr( $name( 'copyleaks', 'email' ) ); ?>" value="<?php echo esc_attr( $val( 'copyleaks', 'email' ) ); ?>"></td></tr>
				<tr><th>API key</th><td><input class="regular-text" type="password" autocomplete="off" name="<?php echo esc_attr( $name( 'copyleaks', 'key' ) ); ?>" value="<?php echo esc_attr( $val( 'copyleaks', 'key' ) ); ?>"></td></tr>
				<tr><th>Chế độ thử (sandbox)</th><td><label><input type="checkbox" name="<?php echo esc_attr( $name( 'copyleaks', 'sandbox' ) ); ?>" value="1" <?php checked( $val( 'copyleaks', 'sandbox' ), '1' ); ?>> Không trừ credit, kết quả giả lập - chỉ để thử</label></td></tr>
			</table>

			<h2>4. Originality.ai (phát hiện AI)</h2>
			<table class="form-table">
				<tr><th>Originality.ai</th><td><label><input type="checkbox" name="<?php echo esc_attr( $name( 'originality', 'on' ) ); ?>" value="1" <?php checked( $val( 'originality', 'on' ), '1' ); ?>> Bật tự động <?php echo vs_auto_tool_ready( 'originality' ) ? '<span class="vs-ok">● Sẵn sàng</span>' : ''; ?></label> &nbsp; <?php echo $test( 'originality' ); // phpcs:ignore ?></td></tr>
				<tr><th>API key</th><td><input class="regular-text" type="password" autocomplete="off" name="<?php echo esc_attr( $name( 'originality', 'key' ) ); ?>" value="<?php echo esc_attr( $val( 'originality', 'key' ) ); ?>"><p class="description">Hỗ trợ tự động file .docx và .txt. Cần gói Enterprise. Tạo key tại <a href="https://app.originality.ai/home/api-token-dashboard" target="_blank" rel="noopener">app.originality.ai › API Token</a>.</p></td></tr>
				<tr><th>Model AI</th><td><select name="<?php echo esc_attr( $name( 'originality', 'model' ) ); ?>">
					<?php foreach ( array( 'multilang' => 'Multilang - đa ngôn ngữ (khuyên dùng cho tiếng Việt)', 'lite' => 'Lite - tiếng Anh, ít báo nhầm', 'turbo' => 'Turbo - tiếng Anh, nhạy nhất' ) as $mk => $ml ) : ?>
						<option value="<?php echo esc_attr( $mk ); ?>" <?php selected( $val( 'originality', 'model' ) ? $val( 'originality', 'model' ) : 'multilang', $mk ); ?>><?php echo esc_html( $ml ); ?></option>
					<?php endforeach; ?>
				</select></td></tr>
			</table>

			<h2>Phạm vi tự động</h2>
			<table class="widefat striped" style="max-width:720px">
				<thead><tr><th>Phần mềm</th><th>Đạo văn</th><th>Tỷ lệ AI</th></tr></thead>
				<tbody>
				<?php foreach ( vs_auto_capabilities() as $t => $cap ) : ?>
					<tr><td><?php echo esc_html( vs_pricing()['tools'][ $t ]['name'] ); ?></td><td><?php echo isset( $cap['plag'] ) ? 'Tự động' : 'Xử lý tay'; ?></td><td><?php echo isset( $cap['ai'] ) ? 'Tự động' : 'Xử lý tay'; ?></td></tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<?php submit_button( 'Lưu cấu hình' ); ?>
		</form>

		<h2>Nhật ký</h2>
		<div class="vs-log"><?php foreach ( (array) get_option( 'vs_auto_log', array() ) as $l ) { echo esc_html( $l ) . "\n"; } ?></div>
	</div>
	<style>
		.vs-flow{display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin:16px 0;font-weight:600}
		.vs-flow span{padding:8px 14px;border-radius:99px;background:#f0f0f1;color:#50575e}
		.vs-flow span.on{background:#e7f8f1;color:#047857}
		.vs-ok{color:#047857;font-weight:600;margin-left:6px}
		.vs-log{white-space:pre;font:12px/1.6 monospace;background:#1d2327;color:#c3c4c7;padding:14px;border-radius:8px;max-height:380px;overflow:auto;max-width:1000px}
	</style>
	<?php
}

/* Hộp "Tự động" trong trang chi tiết đơn. */
add_action( 'add_meta_boxes_vs_order', function ( $post ) {
	if ( 'check' !== vs_meta( $post->ID, 'type' ) ) {
		return;
	}
	add_meta_box( 'vs_auto_box', 'Tự động 24/7', function ( $post ) {
		$v = vs_auto_view( $post->ID );
		echo '<p>' . ( $v['paid_at'] ? 'Thanh toán: <strong>' . esc_html( $v['paid_at'] . ' · ' . $v['paid_via'] ) . '</strong>' : 'Chưa thanh toán' ) . '</p>';
		if ( $v['jobs'] ) {
			echo '<ul style="margin:0 0 10px">';
			foreach ( $v['jobs'] as $j ) {
				echo '<li>• ' . esc_html( $j['name'] . ': ' . ( 'failed' === $j['state'] ? 'Lỗi - cần xử lý tay' : $j['label'] ) ) . '</li>';
			}
			echo '</ul>';
		}
		if ( $v['manual'] ) {
			echo '<p style="color:#b45309">Xử lý tay: ' . esc_html( implode( ', ', $v['manual'] ) ) . '</p>';
		}
		if ( 'dang_xu_ly' === vs_meta( $post->ID, 'status' ) ) {
			echo '<p><a class="button" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=vs_auto_retry&order=' . $post->ID ), 'vs_auto_retry_' . $post->ID ) ) . '">Chạy lại tự động</a></p>';
		}
		$log = (array) get_post_meta( $post->ID, '_vs_auto_log', true );
		if ( array_filter( $log ) ) {
			echo '<details><summary>Nhật ký</summary><pre style="white-space:pre-wrap;font-size:11px">' . esc_html( implode( "\n", $log ) ) . '</pre></details>';
		}
	}, 'vs_order', 'side', 'default' );
} );

/* ---------- Kết quả hiện ngay trong quản trị ---------- */

/** HTML tóm tắt tỷ lệ + link tải báo cáo của 1 đơn (dùng cho cột danh sách và hộp chi tiết). */
function vs_admin_results_html( $id, $compact = false ) {
	$p    = vs_pricing();
	$html = '';
	foreach ( (array) vs_meta( $id, 'results', array() ) as $tool => $r ) {
		$parts = array();
		if ( isset( $r['plag'] ) ) {
			$parts[] = 'ĐV <b>' . esc_html( $r['plag'] ) . '%</b>';
		}
		if ( isset( $r['ai'] ) ) {
			$parts[] = 'AI <b>' . esc_html( $r['ai'] ) . '%</b>';
		}
		if ( $parts ) {
			$name  = 'final' === $tool ? 'Sau sửa' : ( isset( $p['tools'][ $tool ] ) ? $p['tools'][ $tool ]['name'] : $tool );
			$html .= '<div>' . esc_html( $name ) . ': ' . implode( ' · ', $parts ) . '</div>';
		}
	}
	foreach ( vs_auto_files( $id ) as $i => $f ) {
		$html .= '<div><a href="' . esc_url( vs_file_url( $id, $i ) ) . '">⬇ ' . ( $compact ? 'PDF ' . esc_html( preg_replace( '/^Bao-cao-|-VS.*$/', '', $f['name'] ) ) : esc_html( $f['name'] ) ) . '</a></div>';
	}
	return $html;
}

add_filter( 'manage_vs_order_posts_columns', function ( $cols ) {
	$out = array();
	foreach ( $cols as $k => $v ) {
		$out[ $k ] = $v;
		if ( 'vs_price' === $k ) {
			$out['vs_result'] = 'Kết quả';
		}
	}
	return $out;
}, 20 );

add_action( 'manage_vs_order_posts_custom_column', function ( $col, $id ) {
	if ( 'vs_result' === $col ) {
		$h = vs_admin_results_html( $id, true );
		echo $h ? '<div class="vs-res">' . $h . '</div>' : '<span style="color:#999">—</span>'; // phpcs:ignore -- đã escape.
	}
}, 10, 2 );

add_action( 'admin_head', function () {
	$s = get_current_screen();
	if ( $s && 'vs_order' === $s->post_type ) {
		echo '<style>.vs-res{font-size:12px;line-height:1.6}.vs-res a{font-weight:600}.vs-res-box{background:#ecfdf5;border:1px solid #a7f3d0;border-radius:6px;padding:8px 10px;margin:0 0 10px;line-height:1.7}</style>';
	}
} );

/* Hộp "Tự động 24/7" trong chi tiết đơn: hiện tỷ lệ và nút tải PDF ngay khi có. */
add_action( 'add_meta_boxes_vs_order', function ( $post ) {
	$h = vs_admin_results_html( $post->ID );
	if ( ! $h ) {
		return;
	}
	add_meta_box( 'vs_auto_result_box', 'Kết quả tự động (khách đã tải được)', function () use ( $h ) {
		echo '<div class="vs-res-box">' . $h . '</div>'; // phpcs:ignore -- đã escape.
	}, 'vs_order', 'side', 'high' );
} );
