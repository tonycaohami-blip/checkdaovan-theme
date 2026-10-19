<?php
/**
 * AJAX: đặt hàng & tra cứu đơn.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function vs_norm_phone( $p ) {
	$p = preg_replace( '/\D/', '', (string) $p );
	return 0 === strpos( $p, '84' ) && strlen( $p ) > 10 ? '0' . substr( $p, 2 ) : $p;
}

/** Giới hạn số lần gọi theo IP để chống spam / dò mã đơn. */
function vs_rate_limit( $bucket, $max ) {
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : 'x';
	$key = 'vs_rl_' . $bucket . '_' . md5( $ip );
	$n   = (int) get_transient( $key );
	if ( $n >= $max ) {
		wp_send_json_error( array( 'message' => 'Bạn thao tác quá nhiều lần. Vui lòng thử lại sau ít phút hoặc liên hệ hotline.' ), 429 );
	}
	set_transient( $key, $n + 1, HOUR_IN_SECONDS );
}

function vs_payment_info( $code, $amount ) {
	$acc = vs_opt( 'bank_account' );
	if ( ! $acc ) {
		return null;
	}
	$bank = vs_opt( 'bank_code' );
	$qr   = sprintf(
		'https://img.vietqr.io/image/%s-%s-compact2.png?amount=%d&addInfo=%s&accountName=%s',
		rawurlencode( $bank ),
		rawurlencode( $acc ),
		(int) $amount,
		rawurlencode( $code ),
		rawurlencode( vs_opt( 'bank_holder' ) )
	);
	return array(
		'bank'    => vs_opt( 'bank_name' ),
		'account' => $acc,
		'holder'  => vs_opt( 'bank_holder' ),
		'content' => $code,
		'amount'  => (int) $amount,
		'qr'      => $qr,
	);
}

function vs_contact_info() {
	return array(
		'hotline' => vs_opt( 'hotline' ),
		'zalo'    => vs_opt( 'zalo' ),
	);
}

/* ---------- Đặt hàng ---------- */

add_action( 'wp_ajax_vs_submit_order', 'vs_ajax_submit' );
add_action( 'wp_ajax_nopriv_vs_submit_order', 'vs_ajax_submit' );

/* Nonce mới cho form đặt hàng (không bao giờ bị lưu đệm vì là POST tới admin-ajax). */
add_action( 'wp_ajax_vs_fresh_nonce', 'vs_ajax_fresh_nonce' );
add_action( 'wp_ajax_nopriv_vs_fresh_nonce', 'vs_ajax_fresh_nonce' );
function vs_ajax_fresh_nonce() {
	nocache_headers();
	wp_send_json_success( array( 'nonce' => wp_create_nonce( 'vs_order' ) ) );
}

function vs_ajax_submit() {
	if ( ! check_ajax_referer( 'vs_order', 'nonce', false ) ) {
		wp_send_json_error( array( 'message' => 'Phiên làm việc đã hết hạn, vui lòng thử lại.', 'code' => 'nonce' ), 403 );
	}
	if ( ! empty( $_POST['website'] ) ) { // Honeypot.
		wp_send_json_error( array( 'message' => 'Yêu cầu không hợp lệ.' ), 400 );
	}
	vs_rate_limit( 'order', 15 );

	$post  = wp_unslash( $_POST );
	$type  = isset( $post['type'] ) && 'reduce' === $post['type'] ? 'reduce' : 'check';
	$name  = sanitize_text_field( isset( $post['name'] ) ? $post['name'] : '' );
	$phone = vs_norm_phone( isset( $post['phone'] ) ? $post['phone'] : '' );
	$email = sanitize_email( isset( $post['email'] ) ? $post['email'] : '' );
	$note  = sanitize_textarea_field( isset( $post['note'] ) ? $post['note'] : '' );

	if ( mb_strlen( $name ) < 2 ) {
		wp_send_json_error( array( 'message' => 'Vui lòng nhập họ tên.' ) );
	}
	if ( empty( $post['consent'] ) ) {
		wp_send_json_error( array( 'message' => 'Vui lòng đồng ý Điều khoản dịch vụ và việc xử lý dữ liệu cá nhân theo Chính sách bảo mật.' ) );
	}
	if ( 'reduce' === $type && empty( $post['integrity'] ) ) {
		wp_send_json_error( array( 'message' => 'Vui lòng xác nhận cam kết liêm chính học thuật: bạn là tác giả của bài và dịch vụ chỉ hỗ trợ biên tập.' ) );
	}
	if ( ! preg_match( '/^0\d{9,10}$/', $phone ) ) {
		wp_send_json_error( array( 'message' => 'Số điện thoại không hợp lệ.' ) );
	}
	if ( ! empty( $post['email'] ) && ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => 'Email không hợp lệ.' ) );
	}

	$linked = '';
	if ( 'check' === $type ) {
		$data = array(
			'tools'   => isset( $post['tools'] ) ? array_map( 'sanitize_key', (array) $post['tools'] ) : array(),
			'mode'    => sanitize_key( isset( $post['mode'] ) ? $post['mode'] : 'combo' ),
			'pages'   => (int) ( isset( $post['pages'] ) ? $post['pages'] : 1 ),
			'express' => ! empty( $post['express'] ),
		);
		$calc = vs_calc_check( $data['tools'], $data['mode'], $data['pages'], $data['express'] );
		if ( ! is_wp_error( $calc ) ) {
			$data['tools'] = $calc['tools'];
			$data['pages'] = $calc['pages'];
		}
	} else {
		$linked_id = 0;
		if ( ! empty( $post['linked'] ) ) {
			$linked    = strtoupper( sanitize_text_field( $post['linked'] ) );
			$linked_id = vs_find_order( $linked );
			$owner_ok  = $linked_id && ( vs_meta( $linked_id, 'phone' ) === $phone || vs_user_owns_order( $linked_id ) );
			if ( ! $linked_id || 'check' !== vs_meta( $linked_id, 'type' ) || ! $owner_ok ) {
				wp_send_json_error( array( 'message' => 'Mã đơn kiểm tra không đúng hoặc không khớp số điện thoại.' ) );
			}
		}
		$data = array(
			'goal'       => isset( $post['goal'] ) && in_array( $post['goal'], array( 'plag', 'ai', 'both' ), true ) ? $post['goal'] : 'plag',
			'target'     => round( (float) str_replace( ',', '.', isset( $post['target'] ) ? $post['target'] : 20 ), 1 ),
			'current'    => round( (float) str_replace( ',', '.', isset( $post['current'] ) ? $post['current'] : 0 ), 1 ),
			'target_ai'  => round( (float) str_replace( ',', '.', isset( $post['target_ai'] ) ? $post['target_ai'] : 20 ), 1 ),
			'current_ai' => round( (float) str_replace( ',', '.', isset( $post['current_ai'] ) ? $post['current_ai'] : 0 ), 1 ),
			'pages'    => (int) ( isset( $post['pages'] ) ? $post['pages'] : 0 ),
			'deadline' => sanitize_key( isset( $post['deadline'] ) ? $post['deadline'] : 'standard' ),
			'with_ai'  => isset( $post['goal'] ) && in_array( $post['goal'], array( 'ai', 'both' ), true ), // Tương thích dữ liệu cũ.
		);
		$calc = vs_calc_reduce( $data + array( 'linked' => (bool) $linked_id ) );
	}
	if ( is_wp_error( $calc ) ) {
		wp_send_json_error( array( 'message' => $calc->get_error_message() ) );
	}

	$coupon = strtoupper( sanitize_text_field( isset( $post['coupon'] ) ? $post['coupon'] : '' ) );
	$pct    = vs_coupon_pct( $coupon, $type );
	if ( is_wp_error( $pct ) ) {
		wp_send_json_error( array( 'message' => $pct->get_error_message() ) );
	}
	if ( $pct ) {
		$floor          = 'reduce' === $type ? vs_pricing()['reduce']['min'] : 0;
		$calc['total']  = max( $floor, vs_round_k( $calc['total'] * ( 1 - $pct / 100 ) ) );
	} else {
		$coupon = '';
		// Ưu đãi cho khách vào qua link cộng tác viên (không cộng dồn với mã giảm giá).
		if ( vs_ref_discount_active() ) {
			$floor         = 'reduce' === $type ? vs_pricing()['reduce']['min'] : 0;
			$calc['total'] = max( $floor, vs_round_k( $calc['total'] * ( 1 - (float) vs_opt( 'ref_discount' ) / 100 ) ) );
			$coupon        = 'CTV-' . vs_aff_code( vs_current_ref_user() );
		}
	}

	$code   = vs_new_code();
	$stored = null;
	$report = null;
	if ( 'reduce' === $type && ! $linked ) {
		// Giảm tỷ lệ: bắt buộc file Word gốc + báo cáo PDF thể hiện phần đạo văn/AI.
		if ( empty( $_FILES['file']['name'] ) || empty( $_FILES['report']['name'] ) ) {
			wp_send_json_error( array( 'message' => 'Vui lòng tải đủ 2 file: Word bản gốc và báo cáo PDF đạo văn/AI. Chưa có báo cáo? Hãy đặt kiểm tra trước.' ) );
		}
		$stored = vs_store_upload( $_FILES['file'], $code, array( 'docx' ) );
		if ( is_wp_error( $stored ) ) {
			wp_send_json_error( array( 'message' => 'File bản gốc: ' . $stored->get_error_message() ) );
		}
		$report = vs_store_upload( $_FILES['report'], $code . '-report', array( 'pdf' ) );
		if ( is_wp_error( $report ) ) {
			wp_send_json_error( array( 'message' => 'Báo cáo: ' . $report->get_error_message() ) );
		}
	} elseif ( ! empty( $_FILES['file']['name'] ) ) {
		$stored = vs_store_upload( $_FILES['file'], $code, array( 'docx', 'pdf', 'txt', 'rtf' ), (bool) array_intersect( (array) $data['tools'], vs_word_limited_tools() ) );
		if ( is_wp_error( $stored ) ) {
			wp_send_json_error( array( 'message' => $stored->get_error_message() ) );
		}
	} elseif ( $linked ) {
		// Dùng lại file của đơn kiểm tra.
		$lid    = vs_find_order( $linked );
		$stored = array( 'path' => vs_meta( $lid, 'file' ), 'name' => vs_meta( $lid, 'file_name' ) );
	} else {
		wp_send_json_error( array( 'message' => 'Vui lòng tải lên file tài liệu.' ) );
	}

	$id = wp_insert_post( array(
		'post_type'   => 'vs_order',
		'post_status' => 'publish',
		'post_title'  => $code,
	), true );
	if ( is_wp_error( $id ) ) {
		wp_send_json_error( array( 'message' => 'Không tạo được đơn, vui lòng thử lại.' ) );
	}

	$meta = array(
		'code'      => $code,
		'type'      => $type,
		'name'      => $name,
		'phone'     => $phone,
		'email'     => $email,
		'note'      => $note,
		'consent'   => array( 'time' => time(), 'integrity' => 'reduce' === $type ),
		'price'     => $calc['total'],
		'status'    => 'cho_thanh_toan',
		'data'      => $data,
		'file'      => $stored['path'],
		'file_name' => $stored['name'],
		'linked'    => $linked,
		'coupon'    => $coupon,
		'user'      => get_current_user_id(),
		'src_report'      => $report ? $report['path'] : '',
		'src_report_name' => $report ? $report['name'] : '',
	);
	foreach ( $meta as $k => $v ) {
		update_post_meta( $id, '_vs_' . $k, $v );
	}

	vs_mail_admin_new( $id );
	vs_mail_customer_new( $id );
	do_action( 'vs_order_created', $id );

	wp_send_json_success( array(
		'code'    => $code,
		'total'   => $calc['total'],
		'summary' => vs_order_summary( $id ),
		'pay'     => vs_payment_info( $code, $calc['total'] ),
		'contact' => vs_contact_info(),
		'account'  => vs_account_url( 'don-hang' ),
		'trackUrl' => vs_page_url( 'tra-cuu-don-hang', array( 'ma' => $code ) ),
		'register' => vs_account_url( '', array( 'dang-ky' => 1 ) ),
	) );
}

/* ---------- Tra cứu đơn ---------- */

add_action( 'wp_ajax_vs_track_order', 'vs_ajax_track' );
add_action( 'wp_ajax_nopriv_vs_track_order', 'vs_ajax_track' );

function vs_ajax_track() {
	vs_rate_limit( 'track', 40 );
	$code  = strtoupper( sanitize_text_field( wp_unslash( isset( $_POST['code'] ) ? $_POST['code'] : '' ) ) );
	$phone = vs_norm_phone( wp_unslash( isset( $_POST['phone'] ) ? $_POST['phone'] : '' ) );
	$id    = $code ? vs_find_order( $code ) : 0;
	if ( ! $id || vs_meta( $id, 'phone' ) !== $phone ) {
		wp_send_json_error( array( 'message' => 'Không tìm thấy đơn hàng. Vui lòng kiểm tra lại mã đơn và số điện thoại.' ) );
	}

	wp_send_json_success( vs_order_view( $id ) );
}

/** Các kết quả chưa có (đơn đang xử lý): [ [tool => tên, kinds => [Đạo văn, Tỷ lệ AI]] ]. */
function vs_order_waiting( $id ) {
	$p    = vs_pricing();
	$data = (array) vs_meta( $id, 'data', array() );
	$res  = (array) vs_meta( $id, 'results', array() );
	$out  = array();
	if ( 'check' === vs_meta( $id, 'type' ) ) {
		$kinds = 'combo' === $data['mode'] ? array( 'plag', 'ai' ) : array( $data['mode'] );
		foreach ( (array) $data['tools'] as $t ) {
			$miss = array();
			foreach ( $kinds as $k ) {
				if ( ! isset( $res[ $t ][ $k ] ) ) {
					$miss[] = 'ai' === $k ? 'Tỷ lệ AI' : 'Đạo văn';
				}
			}
			if ( $miss ) {
				$out[] = array( 'tool' => isset( $p['tools'][ $t ] ) ? $p['tools'][ $t ]['name'] : $t, 'kinds' => $miss );
			}
		}
	} elseif ( ! isset( $res['final'][ 'ai' === vs_reduce_goal( $data ) ? 'ai' : 'plag' ] ) ) {
		$g     = vs_reduce_goal( $data );
		$out[] = array( 'tool' => 'Sau chỉnh sửa', 'kinds' => 'both' === $g ? array( 'Đạo văn', 'Tỷ lệ AI' ) : ( 'ai' === $g ? array( 'Tỷ lệ AI' ) : array( 'Đạo văn' ) ) );
	}
	return $out;
}

/** Thông tin chi tiết đơn hàng dạng [nhãn => giá trị] để hiển thị cho khách. */
function vs_order_details( $id ) {
	$p    = vs_pricing();
	$data = (array) vs_meta( $id, 'data', array() );
	$d    = array();
	if ( 'check' === vs_meta( $id, 'type' ) ) {
		$names = array();
		foreach ( (array) ( isset( $data['tools'] ) ? $data['tools'] : array() ) as $t ) {
			$names[] = isset( $p['tools'][ $t ] ) ? $p['tools'][ $t ]['name'] : $t;
		}
		$modes             = vs_check_modes();
		$d['Phần mềm']     = implode( ', ', $names );
		$d['Hình thức']    = isset( $modes[ $data['mode'] ] ) ? $modes[ $data['mode'] ] : '';
		$d['Số trang']     = (int) $data['pages'] . ' trang';
		$d['Thời gian trả'] = ! empty( $data['express'] ) ? 'Hỏa tốc (≤ 30 phút)' : 'Tiêu chuẩn';
	} else {
		$dl                 = isset( $p['reduce']['deadlines'][ $data['deadline'] ] ) ? $p['reduce']['deadlines'][ $data['deadline'] ]['label'] : '';
		$goals              = array( 'plag' => 'Giảm tỷ lệ đạo văn', 'ai' => 'Giảm tỷ lệ AI', 'both' => 'Giảm đạo văn + AI' );
		$d['Dịch vụ']       = $goals[ vs_reduce_goal( $data ) ];
		$d['Mục tiêu']      = vs_reduce_goal_text( $data );
		$d['Số trang']      = (int) $data['pages'] . ' trang';
		$d['Thời hạn']      = $dl;
		if ( vs_meta( $id, 'linked' ) ) {
			$d['Từ đơn kiểm tra'] = vs_meta( $id, 'linked' );
		}
	}
	if ( vs_meta( $id, 'coupon' ) ) {
		$d['Ưu đãi'] = vs_meta( $id, 'coupon' );
	}
	$d['Ngày đặt'] = get_the_date( 'H:i d/m/Y', $id );
	return array_filter( $d );
}

/** Rút gọn tên file dài, giữ đuôi file: "Luan-van-thac-si-nguyen…v3.docx". */
function vs_short_filename( $name, $max = 26 ) {
	$name = (string) $name;
	if ( mb_strlen( $name ) <= $max ) {
		return $name;
	}
	$ext  = pathinfo( $name, PATHINFO_EXTENSION );
	$base = $ext ? mb_substr( $name, 0, -( mb_strlen( $ext ) + 1 ) ) : $name;
	$keep = max( 6, $max - mb_strlen( $ext ) - 4 );
	$head = (int) ceil( $keep * 0.7 );
	$tail = $keep - $head;
	return mb_substr( $base, 0, $head ) . '…' . ( $tail ? mb_substr( $base, -$tail ) : '' ) . ( $ext ? '.' . $ext : '' );
}

/** File khách đã gửi kèm đơn: tên đầy đủ, tên ngắn, dung lượng, link tải có chữ ký. */
function vs_order_sent_files( $id ) {
	$out = array();
	foreach ( array( 'file' => 'Tài liệu', 'src_report' => 'Báo cáo PDF' ) as $which => $label ) {
		$stored = vs_meta( $id, $which );
		if ( ! $stored ) {
			continue;
		}
		$name = (string) vs_meta( $id, $which . '_name', basename( $stored ) );
		$path = vs_private_dir() . '/' . basename( $stored );
		$out[] = array(
			'label' => $label,
			'name'  => $name,
			'short' => vs_short_filename( $name ),
			'ext'   => strtoupper( pathinfo( $name, PATHINFO_EXTENSION ) ),
			'size'  => is_file( $path ) ? size_format( filesize( $path ), filesize( $path ) < 10240 ? 1 : 0 ) : '',
			'url'   => is_file( $path ) ? vs_src_url( $id, $which ) : '',
		);
	}
	return $out;
}

/**
 * Thẻ gợi ý giảm tỷ lệ (hiện cạnh kết quả khi đạo văn hoặc AI > 20%).
 * Trả về HTML (dùng chung cho trang tài khoản, tra cứu và cửa sổ đặt hàng) hoặc chuỗi rỗng.
 */
function vs_order_upsell_html( $id ) {
	$status = vs_meta( $id, 'status' );
	if ( 'check' !== vs_meta( $id, 'type' ) || ! in_array( $status, array( 'dang_xu_ly', 'hoan_thanh' ), true ) ) {
		return '';
	}
	$max = array( 'plag' => null, 'ai' => null );
	foreach ( (array) vs_meta( $id, 'results', array() ) as $r ) {
		foreach ( array( 'plag', 'ai' ) as $k ) {
			if ( isset( $r[ $k ] ) && '' !== $r[ $k ] ) {
				$max[ $k ] = max( (float) $max[ $k ], (float) $r[ $k ] );
			}
		}
	}
	$high = array_keys( array_filter( $max, function ( $v ) {
		return null !== $v && $v > 20;
	} ) );
	if ( ! $high ) {
		return '';
	}
	$data  = (array) vs_meta( $id, 'data', array() );
	$pages = max( 1, (int) ( isset( $data['pages'] ) ? $data['pages'] : 1 ) );
	$code  = vs_meta( $id, 'code' );
	$r     = vs_pricing()['reduce'];
	$names = array( 'plag' => 'đạo văn', 'ai' => 'tỷ lệ AI', 'both' => 'đạo văn + AI' );
	$goals = 2 === count( $high ) ? array( 'both', 'plag', 'ai' ) : $high;
	$opts  = array();
	foreach ( $goals as $g ) {
		$args = array(
			'tab'    => 'reduce',
			'linked' => $code,
			'goal'   => $g,
			'pages'  => $pages,
		);
		if ( 'ai' !== $g ) {
			$args['current'] = $max['plag'];
			$args['target']  = 20;
		}
		if ( 'plag' !== $g ) {
			$args['current_ai'] = $max['ai'];
			$args['target_ai']  = 20;
		}
		$calc   = vs_calc_reduce( $args + array( 'deadline' => 'standard', 'linked' => true ) );
		$opts[] = array(
			'goal'  => $g,
			'label' => 'Giảm ' . $names[ $g ] . ' dưới 20%',
			'url'   => vs_page_url( 'dat-hang', $args ),
			'price' => is_wp_error( $calc ) ? '' : vs_money( $calc['total'] ),
		);
	}
	$fmt  = function ( $v ) {
		return str_replace( '.', ',', (string) (float) $v ) . '%';
	};
	$head = array();
	foreach ( $high as $k ) {
		$head[] = ( 'plag' === $k ? 'Đạo văn ' : 'AI ' ) . $fmt( $max[ $k ] );
	}
	ob_start();
	?>
	<div class="rs__card rs__card--up">
		<span class="up__badge"><?php echo vs_icon( 'target', 14 ); // phpcs:ignore ?> Trên mức 20% (tham khảo)</span>
		<b class="up__title"><?php echo esc_html( implode( ' · ', $head ) ); ?></b>
		<div class="up__meters">
			<?php foreach ( $high as $k ) : ?>
				<div class="up__meter"><span><?php echo 'plag' === $k ? 'Đạo văn' : 'Tỷ lệ AI'; ?></span><s><?php echo esc_html( $fmt( $max[ $k ] ) ); ?></s><i aria-hidden="true">→</i><b>&lt; 20%</b></div>
			<?php endforeach; ?>
		</div>
		<?php $main = array_shift( $opts ); ?>
		<a class="btn btn--accent btn--sm btn--block up__cta" href="<?php echo esc_url( $main['url'] ); ?>"><span><?php echo esc_html( $main['label'] ); ?></span><?php if ( $main['price'] ) : ?><small>từ <?php echo esc_html( $main['price'] ); ?></small><?php endif; ?></a>
		<?php if ( $opts ) : ?>
			<div class="up__alt">
				<?php foreach ( $opts as $op ) : ?>
					<a href="<?php echo esc_url( $op['url'] ); ?>"><?php echo esc_html( 'Chỉ ' . str_replace( 'Giảm ', 'giảm ', $op['label'] ) ); ?></a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
		<small class="up__note">Hỗ trợ biên tập cho bài do bạn viết · không cần gửi lại file · giảm thêm <?php echo (int) $r['linked_pct']; ?>%</small>
	</div>
	<?php
	return trim( ob_get_clean() );
}

/** Dữ liệu đơn hàng hiển thị cho khách (tra cứu, trang tài khoản). */
function vs_order_view( $id ) {
	$code     = vs_meta( $id, 'code' );
	$p        = vs_pricing();
	$status   = vs_meta( $id, 'status', 'cho_thanh_toan' );
	$labels   = vs_statuses();
	$type     = vs_meta( $id, 'type' );
	$data     = (array) vs_meta( $id, 'data', array() );
	$results  = array();
	$max_plag = 0;
	foreach ( (array) vs_meta( $id, 'results', array() ) as $k => $r ) {
		$results[] = array(
			'tool' => 'final' === $k ? 'Sau chỉnh sửa' : ( isset( $p['tools'][ $k ] ) ? $p['tools'][ $k ]['name'] : $k ),
			'plag' => isset( $r['plag'] ) ? $r['plag'] : null,
			'ai'   => isset( $r['ai'] ) ? $r['ai'] : null,
		);
		if ( isset( $r['plag'] ) ) {
			$max_plag = max( $max_plag, (float) $r['plag'] );
		}
	}

	// Kết quả & báo cáo hiện ngay khi có (kể cả khi đơn còn phần đang xử lý).
	$visible = in_array( $status, array( 'dang_xu_ly', 'hoan_thanh' ), true );
	$files   = array();
	if ( 'hoan_thanh' === $status ) {
		foreach ( array_filter( array_map( 'intval', explode( ',', (string) vs_meta( $id, 'report_ids' ) ) ) ) as $fid ) {
			$url = wp_get_attachment_url( $fid );
			if ( $url ) {
				$n       = basename( (string) get_attached_file( $fid ) );
				$files[] = array( 'url' => $url, 'name' => $n, 'short' => vs_short_filename( $n, 34 ) );
			}
		}
	}
	if ( $visible ) {
		foreach ( vs_auto_files( $id ) as $i => $f ) {
			$files[] = array( 'url' => vs_file_url( $id, $i ), 'name' => $f['name'], 'short' => vs_short_filename( $f['name'], 34 ) );
		}
	}

	return array(
		'code'    => $code,
		'id'      => $id,
		'type'    => $type,
		'status'  => $status,
		'label'   => isset( $labels[ $status ] ) ? $labels[ $status ] : $status,
		'summary' => vs_order_summary( $id ),
		'total'   => (int) vs_meta( $id, 'price' ),
		'date'    => get_the_date( 'H:i d/m/Y', $id ),
		'results' => $visible ? $results : array(),
		'partial' => 'dang_xu_ly' === $status && ( $results || $files ),
		'waiting' => 'dang_xu_ly' === $status ? vs_order_waiting( $id ) : array(),
		'title'   => 'check' === $type ? 'Kiểm tra đạo văn & AI' : 'Giảm tỷ lệ đạo văn + AI',
		'details' => vs_order_details( $id ),
		'sent'    => vs_order_sent_files( $id ),
		'paid_at' => get_post_meta( $id, '_vs_paid_at', true ) ? wp_date( 'H:i d/m/Y', (int) get_post_meta( $id, '_vs_paid_at', true ) ) : '',
		'done_at' => get_post_meta( $id, '_vs_done_at', true ) ? wp_date( 'H:i d/m/Y', (int) get_post_meta( $id, '_vs_done_at', true ) ) : '',
		'files'   => $files,
		'auto'    => 'check' === $type ? vs_auto_view( $id ) : null,
		'pay'     => 'cho_thanh_toan' === $status ? vs_payment_info( $code, vs_meta( $id, 'price' ) ) : null,
		// Gợi ý đặt giảm tỷ lệ khi đơn kiểm tra đã xong và tỷ lệ còn trên 20%.
		'reduce'  => null, // Thay bằng thẻ gợi ý 'upsell' (đạo văn và/hoặc AI > 20%).
		'upsell'  => $visible ? vs_order_upsell_html( $id ) : '',
	);
}
