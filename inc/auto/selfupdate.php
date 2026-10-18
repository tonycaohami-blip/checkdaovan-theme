<?php
/**
 * Tự cập nhật theme từ GitHub.
 *
 * Mỗi 5 phút (và ngay khi GitHub gọi webhook), website so sánh phiên bản mới nhất trên nhánh main
 * của kho VS_REPO với bản đang chạy. Nếu khác, tải gói zip của đúng phiên bản đó và thay thư mục theme.
 * Chỉ tải từ kho cố định bên dưới; không nhận địa chỉ tải từ bên ngoài.
 *
 * Tắt: Quản trị > Đơn hàng > Cập nhật theme, hoặc thêm define( 'VS_SELFUPDATE', false ) vào wp-config.php.
 * Thư mục theme có .git (máy lập trình) thì không bao giờ tự cập nhật, để không ghi đè code đang sửa.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const VS_REPO        = 'tonycaohami-blip/checkdaovan-theme';
const VS_REPO_BRANCH = 'main';

function vs_su_state() {
	return wp_parse_args( get_option( 'vs_selfupdate', array() ), array(
		'enabled' => 1,
		'sha'     => '',
		'checked' => 0,
		'updated' => 0,
		'error'   => '',
	) );
}

function vs_su_save( $s ) {
	update_option( 'vs_selfupdate', $s, false );
}

/** Có được tự cập nhật không (tắt trên máy lập trình). */
function vs_su_allowed() {
	if ( defined( 'VS_SELFUPDATE' ) && ! VS_SELFUPDATE ) {
		return false;
	}
	if ( file_exists( get_template_directory() . '/.git' ) ) {
		return false;
	}
	return (bool) vs_su_state()['enabled'];
}

/** Mã phiên bản (commit) mới nhất trên GitHub. */
function vs_su_remote_sha() {
	$r = wp_remote_get( 'https://api.github.com/repos/' . VS_REPO . '/commits/' . VS_REPO_BRANCH, array(
		'timeout' => 15,
		'headers' => array( 'Accept' => 'application/vnd.github.sha', 'User-Agent' => 'CheckDaoVan-Theme' ),
	) );
	if ( is_wp_error( $r ) ) {
		return $r;
	}
	$sha = trim( wp_remote_retrieve_body( $r ) );
	if ( 200 !== (int) wp_remote_retrieve_response_code( $r ) || ! preg_match( '/^[0-9a-f]{40}$/', $sha ) ) {
		return new WP_Error( 'sha', 'GitHub trả về HTTP ' . wp_remote_retrieve_response_code( $r ) );
	}
	return $sha;
}

/**
 * Kiểm tra và cập nhật nếu có bản mới.
 *
 * @param bool $force Bỏ qua giới hạn tần suất.
 * @return string Kết quả để hiển thị.
 */
function vs_su_run( $force = false ) {
	if ( ! vs_su_allowed() ) {
		return 'Tự cập nhật đang tắt.';
	}
	if ( ! $force && get_transient( 'vs_su_lock' ) ) {
		return 'Đang kiểm tra, thử lại sau ít phút.';
	}
	set_transient( 'vs_su_lock', 1, 60 );

	$s            = vs_su_state();
	$s['checked'] = time();
	$sha          = vs_su_remote_sha();
	if ( is_wp_error( $sha ) ) {
		$s['error'] = $sha->get_error_message();
		vs_su_save( $s );
		delete_transient( 'vs_su_lock' );
		return 'Lỗi: ' . $s['error'];
	}
	if ( $sha === $s['sha'] ) {
		$s['error'] = '';
		vs_su_save( $s );
		delete_transient( 'vs_su_lock' );
		return 'Đang dùng bản mới nhất.';
	}

	$res = vs_su_install( $sha );
	if ( is_wp_error( $res ) ) {
		$s['error'] = $res->get_error_message();
		vs_su_save( $s );
		delete_transient( 'vs_su_lock' );
		return 'Lỗi: ' . $s['error'];
	}
	$s['sha']     = $sha;
	$s['updated'] = time();
	$s['error']   = '';
	vs_su_save( $s );
	delete_transient( 'vs_su_lock' );
	if ( function_exists( 'opcache_reset' ) ) {
		@opcache_reset(); // phpcs:ignore
	}
	return 'Đã cập nhật lên bản ' . substr( $sha, 0, 7 ) . '.';
}

/** Tải gói zip của commit và thay thư mục theme (giữ bản cũ để khôi phục nếu lỗi). */
function vs_su_install( $sha ) {
	require_once ABSPATH . 'wp-admin/includes/file.php';
	if ( ! WP_Filesystem() ) {
		return new WP_Error( 'fs', 'Không truy cập được hệ thống tệp.' );
	}
	global $wp_filesystem;

	$zip = download_url( 'https://codeload.github.com/' . VS_REPO . '/zip/' . $sha, 120 );
	if ( is_wp_error( $zip ) ) {
		return $zip;
	}
	wp_mkdir_p( trailingslashit( WP_CONTENT_DIR ) . 'upgrade' );
	$work = trailingslashit( WP_CONTENT_DIR ) . 'upgrade/vs-theme-' . substr( $sha, 0, 12 );
	$wp_filesystem->delete( $work, true );
	$ok = unzip_file( $zip, $work );
	@unlink( $zip ); // phpcs:ignore
	if ( is_wp_error( $ok ) ) {
		return $ok;
	}
	$dirs = glob( $work . '/*', GLOB_ONLYDIR );
	$src  = $dirs ? $dirs[0] : '';
	if ( ! $src || ! file_exists( $src . '/style.css' ) || ! file_exists( $src . '/functions.php' ) ) {
		$wp_filesystem->delete( $work, true );
		return new WP_Error( 'pkg', 'Gói tải về không phải theme hợp lệ.' );
	}

	// Chép bản mới vào thư mục tạm NẰM CẠNH thư mục theme (cùng ổ đĩa) để đổi tên an toàn.
	// Không dùng $wp_filesystem->move() cho thư mục: khi khác ổ đĩa, hàm này có thể "chép" thư mục thành tệp rỗng.
	$dest  = untrailingslashit( get_template_directory() );
	$stamp = time();
	$new   = $dest . '-new-' . $stamp;
	$old   = $dest . '-old-' . $stamp;
	wp_mkdir_p( $new );
	$copied = copy_dir( $src, $new );
	$wp_filesystem->delete( $work, true );
	if ( is_wp_error( $copied ) || ! vs_su_valid_dir( $new, $src ) ) {
		$wp_filesystem->delete( $new, true );
		return new WP_Error( 'copy', 'Không chép được bản mới, website vẫn dùng bản cũ.' );
	}

	// Đổi chỗ: hiện tại -> old, new -> hiện tại. Kiểm tra sau mỗi bước, lỗi thì khôi phục.
	if ( ! @rename( $dest, $old ) || ! is_dir( $old ) || file_exists( $dest ) ) { // phpcs:ignore
		if ( ! is_dir( $dest ) && is_dir( $old ) ) {
			@rename( $old, $dest ); // phpcs:ignore
		}
		$wp_filesystem->delete( $new, true );
		return new WP_Error( 'swap', 'Không đổi tên được thư mục theme hiện tại, website vẫn dùng bản cũ.' );
	}
	if ( ! @rename( $new, $dest ) || ! vs_su_valid_dir( $dest ) ) { // phpcs:ignore
		if ( file_exists( $dest ) && ! is_dir( $dest ) ) {
			@unlink( $dest ); // phpcs:ignore
		}
		if ( ! is_dir( $dest ) ) {
			@rename( $old, $dest ); // phpcs:ignore -- khôi phục bản cũ.
		}
		$wp_filesystem->delete( $new, true );
		return new WP_Error( 'swap', 'Không thay được bản mới, đã khôi phục bản cũ.' );
	}
	$wp_filesystem->delete( $old, true );
	return true;
}

/** Thư mục theme hợp lệ: là thư mục, có style.css và functions.php không rỗng (và đủ số tệp nếu có $ref). */
function vs_su_valid_dir( $dir, $ref = '' ) {
	if ( ! is_dir( $dir ) ) {
		return false;
	}
	foreach ( array( 'style.css', 'functions.php' ) as $f ) {
		if ( ! is_file( $dir . '/' . $f ) || filesize( $dir . '/' . $f ) < 100 ) {
			return false;
		}
	}
	if ( $ref ) {
		$count = function ( $d ) {
			return iterator_count( new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $d, FilesystemIterator::SKIP_DOTS ) ) );
		};
		return $count( $dir ) === $count( $ref );
	}
	return true;
}

/* ---------- Lịch kiểm tra 5 phút ---------- */
add_filter( 'cron_schedules', function ( $s ) {
	$s['vs_five'] = array( 'interval' => 300, 'display' => 'Mỗi 5 phút (Check Đạo Văn)' );
	return $s;
} );
add_action( 'init', function () {
	if ( ! wp_next_scheduled( 'vs_su_tick' ) ) {
		wp_schedule_event( time() + 120, 'vs_five', 'vs_su_tick' );
	}
} );
add_action( 'vs_su_tick', function () {
	vs_su_run();
} );

/* ---------- Webhook GitHub: cập nhật ngay khi có push ---------- */
add_action( 'rest_api_init', function () {
	register_rest_route( 'vansach/v1', '/deploy', array(
		'methods'             => 'POST',
		'permission_callback' => '__return_true', // Chỉ kích hoạt việc kiểm tra kho cố định; không nhận dữ liệu.
		'callback'            => function () {
			if ( get_transient( 'vs_su_hook' ) ) {
				return array( 'ok' => true, 'queued' => true );
			}
			set_transient( 'vs_su_hook', 1, 20 );
			wp_schedule_single_event( time(), 'vs_su_tick' );
			spawn_cron();
			return array( 'ok' => true );
		},
	) );
} );

/* ---------- Trang quản trị ---------- */
add_action( 'admin_menu', function () {
	add_submenu_page( 'edit.php?post_type=vs_order', 'Cập nhật theme', 'Cập nhật theme', 'manage_options', 'vs-selfupdate', 'vs_su_page' );
} );

add_action( 'admin_post_vs_su', function () {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'vs_su' ) ) {
		wp_die( 'Không có quyền.' );
	}
	$s = vs_su_state();
	if ( isset( $_POST['toggle'] ) ) {
		$s['enabled'] = $s['enabled'] ? 0 : 1;
		vs_su_save( $s );
		$msg = $s['enabled'] ? 'Đã bật tự cập nhật.' : 'Đã tắt tự cập nhật.';
	} else {
		$msg = vs_su_run( true );
	}
	wp_safe_redirect( add_query_arg( 'vs_msg', rawurlencode( $msg ), admin_url( 'edit.php?post_type=vs_order&page=vs-selfupdate' ) ) );
	exit;
} );

function vs_su_page() {
	$s   = vs_su_state();
	$dev = file_exists( get_template_directory() . '/.git' );
	$fmt = function ( $t ) {
		return $t ? wp_date( 'H:i d/m/Y', $t ) : '-';
	};
	?>
	<div class="wrap">
		<h1>Cập nhật theme tự động từ GitHub</h1>
		<?php if ( isset( $_GET['vs_msg'] ) ) : ?>
			<div class="notice notice-info"><p><?php echo esc_html( wp_unslash( $_GET['vs_msg'] ) ); ?></p></div>
		<?php endif; ?>
		<p>Website tự kiểm tra kho <a href="<?php echo esc_url( 'https://github.com/' . VS_REPO ); ?>" target="_blank" rel="noopener"><code><?php echo esc_html( VS_REPO ); ?></code></a> (nhánh <code><?php echo esc_html( VS_REPO_BRANCH ); ?></code>) mỗi 5 phút và tự cài bản mới. Đơn hàng, bài viết, cài đặt không bị ảnh hưởng.</p>
		<table class="widefat striped" style="max-width:720px">
			<tbody>
				<tr><th>Trạng thái</th><td><?php echo $dev ? '⏸ Tắt trên máy lập trình (có thư mục .git)' : ( vs_su_allowed() ? '✅ Đang bật' : '⏸ Đang tắt' ); ?></td></tr>
				<tr><th>Phiên bản đang chạy</th><td><code><?php echo esc_html( $s['sha'] ? substr( $s['sha'], 0, 7 ) : 'chưa đồng bộ' ); ?></code> · theme <?php echo esc_html( VS_VERSION ); ?></td></tr>
				<tr><th>Kiểm tra gần nhất</th><td><?php echo esc_html( $fmt( $s['checked'] ) ); ?></td></tr>
				<tr><th>Cập nhật gần nhất</th><td><?php echo esc_html( $fmt( $s['updated'] ) ); ?></td></tr>
				<?php if ( $s['error'] ) : ?><tr><th>Lỗi</th><td style="color:#b32d2e"><?php echo esc_html( $s['error'] ); ?></td></tr><?php endif; ?>
				<tr><th>Webhook (tùy chọn)</th><td><code><?php echo esc_html( rest_url( 'vansach/v1/deploy' ) ); ?></code><br><small>Thêm vào GitHub → Settings → Webhooks để cập nhật ngay khi có thay đổi thay vì chờ tối đa 5 phút.</small></td></tr>
			</tbody>
		</table>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:16px">
			<?php wp_nonce_field( 'vs_su' ); ?>
			<input type="hidden" name="action" value="vs_su">
			<button class="button button-primary" <?php disabled( ! vs_su_allowed() ); ?>>🔄 Kiểm tra &amp; cập nhật ngay</button>
			<button class="button" name="toggle" value="1"><?php echo $s['enabled'] ? 'Tắt tự cập nhật' : 'Bật tự cập nhật'; ?></button>
		</form>
	</div>
	<?php
}
