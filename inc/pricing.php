<?php
/**
 * Bảng giá & công thức tính giá.
 *
 * Giá mặc định tham khảo mặt bằng thị trường Việt Nam 2025-2026 (bản 3.0: đã nâng +50% rồi thêm +30%).
 * Giá 0 = phần mềm không hỗ trợ hình thức đó (VD DoIT chỉ kiểm tra đạo văn).
 * Có thể chỉnh trong Quản trị > Đơn hàng > Cài đặt & Bảng giá.
 * Công thức ở đây phải khớp với calcCheck()/calcReduce() trong assets/js/main.js.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function vs_pricing_defaults() {
	return array(
		'tools'  => array(
			'turnitin'    => array(
				'name'  => 'Turnitin',
				'desc'  => 'Chuẩn kiểm tra của phần lớn trường đại học trong và ngoài nước. Chế độ không lưu bài (No Repository).',
				'plag'  => 58500,
				'ai'    => 58500,
				'combo' => 97500,
			),
			'ithenticate' => array(
				'name'  => 'iThenticate',
				'desc'  => 'Dùng cho bài báo khoa học, luận án gửi tạp chí. Cơ sở dữ liệu Crossref lớn nhất thế giới.',
				'plag'  => 136500,
				'ai'    => 78000,
				'combo' => 195000,
			),
			'copyleaks'   => array(
				'name'  => 'Copyleaks',
				'desc'  => 'So khớp đa ngôn ngữ, phát hiện cả đoạn văn bị diễn đạt lại (paraphrase).',
				'plag'  => 49000,
				'ai'    => 49000,
				'combo' => 78000,
			),
			'originality' => array(
				'name'  => 'Originality.ai',
				'desc'  => 'Chuyên phát hiện nội dung tạo bởi AI (ChatGPT, Gemini, Claude…) với độ nhạy cao.',
				'plag'  => 49000,
				'ai'    => 58500,
				'combo' => 88000,
			),
			'gptzero'     => array(
				'name'  => 'GPTZero',
				'desc'  => 'Công cụ phát hiện AI được nhiều giảng viên sử dụng: đánh giá từng câu, kèm kiểm tra trùng lặp với nguồn web.',
				'plag'  => 49000,
				'ai'    => 45500,
				'combo' => 78000,
			),
			'winston'     => array(
				'name'  => 'Winston AI',
				'desc'  => 'Phát hiện AI độ chính xác cao cho giáo dục và xuất bản, có kiểm tra đạo văn và báo cáo chi tiết từng đoạn.',
				'plag'  => 58500,
				'ai'    => 49000,
				'combo' => 88000,
			),
			'grammarly'   => array(
				'name'  => 'Grammarly',
				'desc'  => 'Kiểm tra đạo văn với hàng tỷ trang web và ấn phẩm học thuật, kèm bộ phát hiện AI - quen thuộc với bài tiếng Anh.',
				'plag'  => 39000,
				'ai'    => 39000,
				'combo' => 65000,
			),
			'doit'        => array(
				'name'  => 'DoIT',
				'desc'  => 'Phần mềm kiểm tra trùng lặp của Việt Nam, được nhiều trường đại học trong nước sử dụng, mạnh với tài liệu tiếng Việt.',
				'plag'  => 150000,
				'ai'    => 0,
				'combo' => 0,
				'note'  => array( 'Cơ sở dữ liệu', 'Tài liệu tiếng Việt' ),
			),
			'quillbot'    => array(
				'name'  => 'QuillBot',
				'desc'  => 'Kiểm tra đạo văn và phát hiện AI của QuillBot - quen thuộc với sinh viên viết bài tiếng Anh, kết quả nhanh, chi phí thấp.',
				'plag'  => 39000,
				'ai'    => 29000,
				'combo' => 59000,
			),
			'undetectable' => array(
				'name'  => 'Undetectable.AI',
				'desc'  => 'Bộ phát hiện AI tổng hợp: đối chiếu văn bản với nhiều công cụ phát hiện AI phổ biến trong một lần kiểm tra.',
				'plag'  => 0,
				'ai'    => 39000,
				'combo' => 0,
				'note'  => array( 'Đối chiếu', 'Nhiều bộ phát hiện AI' ),
			),
		),
		'check'  => array(
			'pages_included' => 100, // Số trang được tính trong giá cơ bản.
			'extra_pct'      => 50,  // Mỗi 100 trang vượt thêm +50%.
			'express_pct'    => 50,  // Trả kết quả hỏa tốc (≤ 30 phút).
			'disc2'          => 10,  // Giảm khi chọn 2 phần mềm.
			'disc3'          => 15,  // Giảm khi chọn từ 3 phần mềm.
		),
		'reduce' => array(
			't30'        => 23500, // Đơn giá / trang, mục tiêu đạo văn 30% (từ 30% trở lên giữ mức này).
			't20'        => 35000, // Đơn giá / trang, mục tiêu đạo văn 20% (từ 20-30% nội suy giữa 2 mức).
			'step_pct'   => 4,      // Mục tiêu thấp hơn 20%: cộng thêm % đơn giá cho mỗi 1 điểm phần trăm.
			'ai_unit'    => 25000,  // Đơn giá / trang để đưa tỷ lệ AI về 20%.
			'both_pct'   => 50,     // Giảm cả đạo văn + AI: phần AI chỉ tính % này.
			'tiers'      => array( // Hệ số theo tỷ lệ hiện tại (đạo văn hoặc AI): [tối đa %, hệ số].
				array( 40, 1.0 ),
				array( 60, 1.25 ),
				array( 80, 1.5 ),
				array( 100, 1.8 ),
			),
			'deadlines'  => array(
				'standard' => array( 'label' => 'Tiêu chuẩn (3-5 ngày)', 'mult' => 1.0 ),
				'fast'     => array( 'label' => 'Nhanh (48 giờ)', 'mult' => 1.3 ),
				'urgent'   => array( 'label' => 'Hỏa tốc (24 giờ)', 'mult' => 1.6 ),
			),
			'linked_pct' => 10,     // Giảm giá khi đặt từ đơn check đã hoàn thành.
			'min'        => 292500, // Giá tối thiểu một đơn.
			'max_pages'  => 500,
		),
	);
}

/** Bảng giá hiện hành (mặc định + ghi đè từ trang cài đặt). */
function vs_pricing() {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}
	$p   = vs_pricing_defaults();
	$ovr = get_option( 'vs_prices', array() );
	if ( is_array( $ovr ) ) {
		foreach ( $p['tools'] as $k => $t ) {
			foreach ( array( 'plag', 'ai', 'combo' ) as $f ) {
				if ( isset( $ovr['tools'][ $k ][ $f ] ) && '' !== $ovr['tools'][ $k ][ $f ] ) {
					$p['tools'][ $k ][ $f ] = (int) $ovr['tools'][ $k ][ $f ];
				}
			}
		}
		foreach ( array( 'pages_included', 'extra_pct', 'express_pct', 'disc2', 'disc3' ) as $f ) {
			if ( isset( $ovr['check'][ $f ] ) && '' !== $ovr['check'][ $f ] ) {
				$p['check'][ $f ] = (int) $ovr['check'][ $f ];
			}
		}
		foreach ( array( 't30', 't20', 'step_pct', 'ai_unit', 'both_pct', 'linked_pct', 'min' ) as $f ) {
			if ( isset( $ovr['reduce'][ $f ] ) && '' !== $ovr['reduce'][ $f ] ) {
				$p['reduce'][ $f ] = (int) $ovr['reduce'][ $f ];
			}
		}
		foreach ( $p['reduce']['deadlines'] as $k => $d ) {
			if ( isset( $ovr['reduce']['deadlines'][ $k ] ) && '' !== $ovr['reduce']['deadlines'][ $k ] ) {
				$p['reduce']['deadlines'][ $k ]['mult'] = (float) $ovr['reduce']['deadlines'][ $k ];
			}
		}
	}
	$cache = apply_filters( 'vs_pricing', $p );
	return $cache;
}

/** Giá hiển thị: "Không hỗ trợ" khi bằng 0. */
function vs_price_label( $v ) {
	return (int) $v > 0 ? vs_money( $v ) : 'Không hỗ trợ';
}

/** Giá thấp nhất (> 0) của một loại kiểm tra trên mọi phần mềm. */
function vs_min_price( $kind ) {
	$v = array_filter( array_map( 'intval', wp_list_pluck( vs_pricing()['tools'], $kind ) ) );
	return $v ? min( $v ) : 0;
}

function vs_round_k( $n ) {
	return (int) ( round( $n / 500 ) * 500 ); // Làm tròn tới 500đ.
}

function vs_check_modes() {
	return array(
		'plag'  => 'Chỉ đạo văn',
		'ai'    => 'Chỉ tỷ lệ AI',
		'combo' => 'Đạo văn + AI',
	);
}

/**
 * Tính giá đơn kiểm tra.
 *
 * @return array|WP_Error ['total'=>int, 'lines'=>array]
 */
function vs_calc_check( $tools, $mode, $pages, $express ) {
	$p     = vs_pricing();
	$modes = vs_check_modes();
	if ( ! isset( $modes[ $mode ] ) ) {
		return new WP_Error( 'mode', 'Hình thức kiểm tra không hợp lệ.' );
	}
	$tools = array_values( array_unique( array_filter( (array) $tools, function ( $t ) use ( $p ) {
		return isset( $p['tools'][ $t ] );
	} ) ) );
	if ( ! $tools ) {
		return new WP_Error( 'tools', 'Vui lòng chọn 1 phần mềm.' );
	}
	if ( count( $tools ) > 1 ) {
		return new WP_Error( 'tools', 'Mỗi đơn chỉ được chọn 1 phần mềm. Vui lòng đặt đơn riêng cho phần mềm khác.' );
	}
	$pages = max( 1, min( 2000, (int) $pages ) );

	$sum = 0;
	foreach ( $tools as $t ) {
		if ( (int) $p['tools'][ $t ][ $mode ] <= 0 ) {
			return new WP_Error( 'mode', $p['tools'][ $t ]['name'] . ' không hỗ trợ hình thức "' . $modes[ $mode ] . '". Vui lòng chọn hình thức khác.' );
		}
		$sum += $p['tools'][ $t ][ $mode ];
	}
	$c      = $p['check'];
	$blocks = max( 0, (int) ceil( $pages / max( 1, $c['pages_included'] ) ) - 1 );
	$factor = 1 + $blocks * $c['extra_pct'] / 100;
	$n      = count( $tools );
	$disc   = 0; // Mỗi đơn 1 phần mềm nên không còn ưu đãi chọn nhiều phần mềm.
	$total  = $sum * $factor * ( 1 - $disc / 100 ) * ( $express ? 1 + $c['express_pct'] / 100 : 1 );

	return array(
		'total' => vs_round_k( $total ),
		'tools' => $tools,
		'pages' => $pages,
		'disc'  => $disc,
	);
}

/** Mục tiêu giảm của đơn: plag (đạo văn) | ai | both. Đơn cũ (trước v6.7) suy ra từ with_ai. */
function vs_reduce_goal( $data ) {
	if ( isset( $data['goal'] ) && in_array( $data['goal'], array( 'plag', 'ai', 'both' ), true ) ) {
		return $data['goal'];
	}
	return ! empty( $data['with_ai'] ) ? 'both' : 'plag';
}

/** Hệ số theo tỷ lệ hiện tại. */
function vs_reduce_tier( $current ) {
	$r    = vs_pricing()['reduce'];
	$mult = end( $r['tiers'] )[1];
	foreach ( $r['tiers'] as $tier ) {
		if ( $current <= $tier[0] ) {
			return $tier[1];
		}
	}
	return $mult;
}

/** Đơn giá / trang để đưa tỷ lệ đạo văn về mục tiêu $t (%). */
function vs_reduce_unit_plag( $t ) {
	$r = vs_pricing()['reduce'];
	if ( $t >= 30 ) {
		return $r['t30'];
	}
	if ( $t >= 20 ) {
		return $r['t20'] + ( $r['t30'] - $r['t20'] ) * ( $t - 20 ) / 10;
	}
	return $r['t20'] * ( 1 + ( 20 - $t ) * $r['step_pct'] / 100 );
}

/** Đơn giá / trang để đưa tỷ lệ AI về mục tiêu $t (%). */
function vs_reduce_unit_ai( $t ) {
	$r = vs_pricing()['reduce'];
	$f = $t >= 20 ? max( 0.7, 1 - ( $t - 20 ) * 0.02 ) : 1 + ( 20 - $t ) * $r['step_pct'] / 100;
	return $r['ai_unit'] * $f;
}

/**
 * Tính giá đơn giảm tỷ lệ (đạo văn / AI / cả hai) với mục tiêu do khách nhập.
 *
 * @param array $a goal, current, target, current_ai, target_ai, pages, deadline, linked.
 * @return array|WP_Error ['total', 'plag' => phần đạo văn, 'ai' => phần AI]
 */
function vs_calc_reduce( $a ) {
	$r     = vs_pricing()['reduce'];
	$goal  = vs_reduce_goal( $a );
	$pages = (int) ( isset( $a['pages'] ) ? $a['pages'] : 0 );
	if ( $pages < 1 || $pages > $r['max_pages'] ) {
		return new WP_Error( 'pages', 'Số trang phải từ 1 đến ' . $r['max_pages'] . '.' );
	}
	$deadline = isset( $a['deadline'] ) ? $a['deadline'] : 'standard';
	if ( ! isset( $r['deadlines'][ $deadline ] ) ) {
		return new WP_Error( 'deadline', 'Thời hạn không hợp lệ.' );
	}
	$parts = array( 'plag' => 0, 'ai' => 0 );
	foreach ( array( 'plag' => array( 'current', 'target', 'đạo văn' ), 'ai' => array( 'current_ai', 'target_ai', 'AI' ) ) as $k => $f ) {
		if ( 'both' !== $goal && $goal !== $k ) {
			continue;
		}
		$cur = isset( $a[ $f[0] ] ) ? (float) $a[ $f[0] ] : 0;
		$tgt = isset( $a[ $f[1] ] ) && '' !== $a[ $f[1] ] ? (float) $a[ $f[1] ] : -1;
		if ( $cur <= 0 || $cur > 100 ) {
			return new WP_Error( 'current', 'Vui lòng nhập tỷ lệ ' . $f[2] . ' hiện tại (1-100%).' );
		}
		if ( $tgt < 0 || $tgt >= $cur ) {
			return new WP_Error( 'target', 'Mục tiêu tỷ lệ ' . $f[2] . ' phải từ 0% và nhỏ hơn tỷ lệ hiện tại (' . $cur . '%).' );
		}
		$unit        = ( 'plag' === $k ? vs_reduce_unit_plag( $tgt ) : vs_reduce_unit_ai( $tgt ) ) * vs_reduce_tier( $cur );
		$parts[ $k ] = $pages * $unit;
	}
	$total = $parts['plag'] + $parts['ai'] * ( 'both' === $goal ? $r['both_pct'] / 100 : 1 );
	$total *= $r['deadlines'][ $deadline ]['mult'];
	if ( ! empty( $a['linked'] ) ) {
		$total *= 1 - $r['linked_pct'] / 100;
	}
	return array(
		'total' => max( $r['min'], vs_round_k( $total ) ),
		'goal'  => $goal,
		'plag'  => (int) round( $parts['plag'] ),
		'ai'    => (int) round( $parts['ai'] ),
	);
}

/** Mô tả mục tiêu của đơn giảm tỷ lệ: "Đạo văn 45% → dưới 15% · AI 60% → dưới 20%". */
function vs_reduce_goal_text( $data, $sep = ' · ' ) {
	$goal = vs_reduce_goal( $data );
	$fmt  = function ( $v ) {
		return str_replace( '.', ',', (string) (float) $v ) . '%';
	};
	$out  = array();
	if ( 'ai' !== $goal ) {
		$out[] = 'Đạo văn ' . $fmt( $data['current'] ) . ' → dưới ' . $fmt( isset( $data['target'] ) ? $data['target'] : 30 );
	}
	if ( 'plag' !== $goal ) {
		$out[] = 'AI ' . ( isset( $data['current_ai'] ) && $data['current_ai'] ? $fmt( $data['current_ai'] ) . ' ' : '' ) . '→ dưới ' . $fmt( isset( $data['target_ai'] ) ? $data['target_ai'] : 20 );
	}
	return implode( $sep, $out );
}
