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

/**
 * Token GitHub (chỉ đọc) để truy cập kho riêng tư. Ưu tiên hằng số VS_GITHUB_TOKEN trong wp-config.php,
 * sau đó là token lưu trong trang Cập nhật theme. Token chỉ lưu trong cơ sở dữ liệu website, không nằm trong code.
 */
function vs_su_token() {
	if ( defined( 'VS_GITHUB_TOKEN' ) && VS_GITHUB_TOKEN ) {
		return (string) VS_GITHUB_TOKEN;
	}
	return (string) get_option( 'vs_su_token', '' );
}

function vs_su_headers( $accept ) {
	$h = array( 'Accept' => $accept, 'User-Agent' => 'CheckDaoVan-Theme', 'X-GitHub-Api-Version' => '2022-11-28' );
	if ( vs_su_token() ) {
		$h['Authorization'] = 'Bearer ' . vs_su_token();
	}
	return $h;
}

/** Mã phiên bản (commit) mới nhất trên GitHub. */
function vs_su_remote_sha() {
	$r = wp_remote_get( 'https://api.github.com/repos/' . VS_REPO . '/commits/' . VS_REPO_BRANCH, array(
		'timeout' => 15,
		'headers' => vs_su_headers( 'application/vnd.github.sha' ),
	) );
	if ( is_wp_error( $r ) ) {
		return $r;
	}
	$sha = trim( wp_remote_retrieve_body( $r ) );
	if ( 200 !== (int) wp_remote_retrieve_response_code( $r ) || ! preg_match( '/^[0-9a-f]{40}$/', $sha ) ) {
		$code = (int) wp_remote_retrieve_response_code( $r );
		return new WP_Error( 'sha', 'GitHub trả về HTTP ' . $code . ( 404 === $code ? ( vs_su_token() ? ' (token không có quyền đọc kho)' : ' (kho riêng tư: cần nhập token GitHub)' ) : ( 401 === $code ? ' (token sai hoặc đã hết hạn)' : '' ) ) );
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

/** Tải gói zip của commit và chép đè lên thư mục theme (giống cách WordPress cập nhật plugin). */
function vs_su_install( $sha ) {
	require_once ABSPATH . 'wp-admin/includes/file.php';
	if ( ! WP_Filesystem() ) {
		return new WP_Error( 'fs', 'Không truy cập được hệ thống tệp.' );
	}
	global $wp_filesystem;

	if ( vs_su_token() ) {
		// Kho riêng tư: API trả về địa chỉ tải tạm thời (đã kèm quyền, hết hạn sau vài phút).
		// Tự xử lý chuyển hướng để không gửi token sang máy chủ tải file.
		$r = wp_remote_get( 'https://api.github.com/repos/' . VS_REPO . '/zipball/' . $sha, array(
			'timeout'     => 30,
			'redirection' => 0,
			'headers'     => vs_su_headers( 'application/vnd.github+json' ),
		) );
		if ( is_wp_error( $r ) ) {
			return $r;
		}
		$loc = wp_remote_retrieve_header( $r, 'location' );
		if ( ! $loc || 0 !== strpos( $loc, 'https://' ) ) {
			return new WP_Error( 'zip', 'GitHub không trả địa chỉ tải gói (HTTP ' . wp_remote_retrieve_response_code( $r ) . ')' );
		}
		$zip = download_url( $loc, 120 );
		if ( is_wp_error( $zip ) ) {
			return $zip;
		}
	} else {
		$zip = download_url( 'https://codeload.github.com/' . VS_REPO . '/zip/' . $sha, 120 );
		if ( is_wp_error( $zip ) ) {
			return $zip;
		}
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

	// Chép đè từng tệp, KHÔNG đổi tên thư mục đang chạy (đổi tên có thể bị kẹt khi website đang đọc tệp,
	// và $wp_filesystem->move() có thể "chép" thư mục thành tệp rỗng khi khác ổ đĩa).
	// Mỗi tệp được ghi ra tệp tạm rồi đổi tên đè lên tệp cũ; functions.php và style.css được chép sau cùng.
	$dest  = untrailingslashit( get_template_directory() );
	$files = vs_su_list( $src );
	if ( count( $files ) < 20 || ! in_array( 'functions.php', $files, true ) ) {
		$wp_filesystem->delete( $work, true );
		return new WP_Error( 'pkg', 'Gói tải về thiếu tệp, website vẫn dùng bản cũ.' );
	}
	usort( $files, function ( $x, $y ) {
		$last = array( 'style.css' => 1, 'functions.php' => 2 );
		return ( isset( $last[ $x ] ) ? $last[ $x ] : 0 ) - ( isset( $last[ $y ] ) ? $last[ $y ] : 0 );
	} );
	$failed = array();
	foreach ( $files as $rel ) {
		$from = $src . '/' . $rel;
		$to   = $dest . '/' . $rel;
		if ( is_file( $to ) && filesize( $to ) === filesize( $from ) && md5_file( $to ) === md5_file( $from ) ) {
			continue; // Không đổi.
		}
		wp_mkdir_p( dirname( $to ) );
		$tmp = $to . '.vs-new';
		$ok  = @copy( $from, $tmp ) && filesize( $tmp ) === filesize( $from ); // phpcs:ignore
		if ( $ok && ! @rename( $tmp, $to ) ) { // phpcs:ignore
			$ok = @copy( $from, $to ) && filesize( $to ) === filesize( $from ); // phpcs:ignore
		}
		if ( file_exists( $tmp ) ) {
			@unlink( $tmp ); // phpcs:ignore
		}
		if ( ! $ok ) {
			$failed[] = $rel;
		}
	}
	if ( $failed ) {
		$wp_filesystem->delete( $work, true );
		return new WP_Error( 'copy', 'Chưa chép được ' . count( $failed ) . ' tệp (VD ' . $failed[0] . '), sẽ thử lại ở lần kiểm tra sau.' );
	}

	// Xóa tệp không còn trong bản mới (chỉ trong thư mục theme).
	$keep = array_flip( $files );
	foreach ( vs_su_list( $dest ) as $rel ) {
		if ( ! isset( $keep[ $rel ] ) ) {
			@unlink( $dest . '/' . $rel ); // phpcs:ignore
		}
	}
	$wp_filesystem->delete( $work, true );
	// Dọn thư mục tạm của cách cập nhật cũ (nếu có).
	foreach ( (array) glob( $dest . '-new-*', GLOB_ONLYDIR ) as $d ) {
		$wp_filesystem->delete( $d, true );
	}
	return true;
}

/** Danh sách tệp (đường dẫn tương đối) trong thư mục, bỏ qua .git. */
function vs_su_list( $dir ) {
	$out = array();
	$it  = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS ) );
	foreach ( $it as $file ) {
		if ( ! $file->isFile() ) {
			continue;
		}
		$rel = str_replace( '\\', '/', substr( $file->getPathname(), strlen( $dir ) + 1 ) );
		if ( 0 === strpos( $rel, '.git/' ) ) {
			continue;
		}
		$out[] = $rel;
	}
	return $out;
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
	// Trạng thái công khai (không chứa dữ liệu nhạy cảm). Nếu nội dung mẫu của bản mới chưa nhập thì nhập ngay.
	register_rest_route( 'vansach/v1', '/status', array(
		'methods'             => 'GET',
		'permission_callback' => '__return_true',
		'callback'            => function () {
			$ran = false;
			if ( function_exists( 'vs_maybe_import' ) && ! get_transient( 'vs_import_busy' ) ) {
				$before = (int) get_option( 'vs_content_imported' );
				set_transient( 'vs_import_busy', 1, 120 );
				ignore_user_abort( true );
				if ( function_exists( 'set_time_limit' ) ) {
					@set_time_limit( 300 ); // phpcs:ignore
				}
				vs_maybe_import();
				delete_transient( 'vs_import_busy' );
				$ran = (int) get_option( 'vs_content_imported' ) !== $before;
			}
			nocache_headers();
			return array(
				'version' => VS_VERSION,
				'tz'      => wp_timezone_string(),
				'now'     => wp_date( 'H:i d/m/Y' ),
				'content' => (int) get_option( 'vs_content_imported' ),
				'import'  => get_option( 'vs_import_log' ),
				'ran'     => $ran,
				'su_lock' => (bool) get_transient( 'vs_su_lock' ),
				'su_error' => vs_su_state()['error'],
				'su_checked' => vs_su_state()['checked'],
				'su_token' => (bool) vs_su_token(),
				'next'    => wp_next_scheduled( 'vs_run_import' ),
			);
		},
	) );

	register_rest_route( 'vansach/v1', '/deploy', array(
		'methods'             => 'POST',
		'permission_callback' => '__return_true', // Chỉ kích hoạt việc kiểm tra kho cố định; không nhận dữ liệu.
		'callback'            => function () {
			if ( get_transient( 'vs_su_hook' ) ) {
				return array( 'ok' => true, 'skipped' => 'Đang kiểm tra cập nhật, thử lại sau ít phút.' );
			}
			set_transient( 'vs_su_hook', 1, 150 ); // Chỉ 1 lượt chạy cùng lúc (mỗi lượt tối đa ~100 giây): chống gọi dồn làm nghẽn máy chủ.
			// Chạy trực tiếp (không phụ thuộc WP-Cron); vẫn chạy tiếp nếu GitHub ngắt kết nối sớm.
			ignore_user_abort( true );
			if ( function_exists( 'set_time_limit' ) ) {
				@set_time_limit( 180 ); // phpcs:ignore
			}
			// API GitHub có thể còn trả bản cũ (lưu đệm ~60 giây) ngay sau khi push: thử lại tối đa 4 lần, mỗi lần cách 20 giây.
			// Không dùng mã commit trong nội dung webhook vì ai cũng gọi được địa chỉ này.
			$before = vs_su_state()['sha'];
			for ( $i = 0; $i < 5; $i++ ) {
				$result = vs_su_run( true );
				if ( vs_su_state()['sha'] !== $before || 0 === strpos( $result, 'Lỗi' ) ) {
					break;
				}
				sleep( 20 );
			}
			return array( 'ok' => true, 'result' => $result, 'content' => (int) get_option( 'vs_content_imported' ), 'import' => get_option( 'vs_import_log' ) );
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
	if ( isset( $_POST['save_token'] ) || isset( $_POST['clear_token'] ) ) {
		$tok = isset( $_POST['clear_token'] ) ? '' : preg_replace( '/[^A-Za-z0-9_]/', '', wp_unslash( isset( $_POST['token'] ) ? $_POST['token'] : '' ) );
		if ( isset( $_POST['clear_token'] ) || '' !== $tok ) {
			update_option( 'vs_su_token', $tok, false );
		}
		if ( isset( $_POST['save_token'] ) && '' === $tok ) {
			$msg = 'Chưa lưu: ô token đang trống. Hãy dán token (bắt đầu bằng github_pat_) rồi bấm Lưu & kiểm tra.';
		} else {
			$test = vs_su_remote_sha();
			$what = vs_su_token() ? 'Đã lưu token (…' . substr( vs_su_token(), -4 ) . '). ' : 'Đã xóa token. ';
			$msg  = $what . ( is_wp_error( $test ) ? 'Kết nối GitHub lỗi: ' . $test->get_error_message() : 'Kết nối GitHub thành công (bản mới nhất ' . substr( $test, 0, 7 ) . ').' );
		}
	} elseif ( isset( $_POST['toggle'] ) ) {
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
				<tr><th>Token GitHub</th><td><?php echo defined( 'VS_GITHUB_TOKEN' ) && VS_GITHUB_TOKEN ? '✅ Đặt trong wp-config.php' : ( get_option( 'vs_su_token' ) ? '✅ Đã lưu (…' . esc_html( substr( get_option( 'vs_su_token' ), -4 ) ) . ')' : 'Chưa có - chỉ cần khi kho GitHub để chế độ riêng tư' ); ?></td></tr>
				<tr><th>Webhook (tùy chọn)</th><td><code><?php echo esc_html( rest_url( 'vansach/v1/deploy' ) ); ?></code><br><small>Thêm vào GitHub → Settings → Webhooks để cập nhật ngay khi có thay đổi thay vì chờ tối đa 5 phút.</small></td></tr>
			</tbody>
		</table>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:16px">
			<?php wp_nonce_field( 'vs_su' ); ?>
			<input type="hidden" name="action" value="vs_su">
			<button class="button button-primary" <?php disabled( ! vs_su_allowed() ); ?>>🔄 Kiểm tra &amp; cập nhật ngay</button>
			<button class="button" name="toggle" value="1"><?php echo $s['enabled'] ? 'Tắt tự cập nhật' : 'Bật tự cập nhật'; ?></button>
		</form>

		<h2>Kho GitHub riêng tư</h2>
		<p>Để kho ở chế độ riêng tư, tạo một <strong>Fine-grained personal access token</strong> tại GitHub → Settings → Developer settings, chỉ chọn kho <code><?php echo esc_html( VS_REPO ); ?></code>, quyền <strong>Contents: Read-only</strong>, rồi dán vào đây. Lưu token và thấy “Kết nối GitHub thành công” <em>trước</em> khi chuyển kho sang riêng tư.</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'vs_su' ); ?>
			<input type="hidden" name="action" value="vs_su">
			<input type="password" class="regular-text" name="token" autocomplete="off" placeholder="github_pat_…">
			<button class="button button-primary" name="save_token" value="1">Lưu &amp; kiểm tra</button>
			<?php if ( get_option( 'vs_su_token' ) ) : ?><button class="button" name="clear_token" value="1">Xóa token</button><?php endif; ?>
		</form>
	</div>
	<?php
}
