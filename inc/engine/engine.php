<?php
/**
 * CDV Scan: bộ kiểm tra trùng lặp (đạo văn) riêng của website.
 *
 * Cách hoạt động (giống nguyên lý của các phần mềm kiểm tra đạo văn):
 * 1. Kho đối chiếu: tài liệu quản trị tải lên, bài khách đồng ý lưu, trang web đã tìm được.
 *    Mỗi tài liệu được cắt thành các cụm 8 từ liên tiếp, băm (hash) và chọn đại diện bằng thuật toán
 *    winnowing để lưu chỉ mục (bảng {prefix}vs_cdv_fp). Không cần thư viện hay dịch vụ ngoài.
 * 2. Internet (tùy chọn): lấy các câu đặc trưng của bài, tìm qua API tìm kiếm chính thức
 *    (Google Programmable Search hoặc Serper), tải các trang kết quả về kho rồi so khớp.
 * 3. So khớp: tìm mọi cụm 8 từ của bài trong chỉ mục, mở rộng thành đoạn trùng dài nhất,
 *    mỗi từ chỉ tính cho 1 nguồn (nguồn có đoạn trùng dài nhất) như Turnitin.
 *    Tỷ lệ = số từ trùng / tổng số từ. Có thể loại trừ phần tài liệu tham khảo và trích dẫn trong ngoặc kép.
 * 4. Báo cáo: trang xem trực tuyến tô màu theo từng nguồn + file PDF.
 *
 * Giới hạn (hiển thị rõ cho khách): chỉ phát hiện trùng với những gì có trong kho và trên Internet
 * tìm được; không truy cập kho bài nộp của Turnitin hay cơ sở dữ liệu trả phí; không phát hiện AI.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'VS_CDV_DB', 1 );
define( 'VS_CDV_K', 8 ); // Số từ trong 1 cụm băm.
define( 'VS_CDV_W', 4 ); // Cửa sổ winnowing: bảo đảm bắt mọi đoạn trùng từ K + W - 1 = 11 từ.

/* =========================================================
 * Cấu hình
 * ======================================================= */

function vs_cdv_defaults() {
	return array(
		'on'      => '',          // Bán cho khách trong trang đặt đơn.
		'name'    => 'CDV Scan',
		'price'   => 29000,      // Giá kiểm tra đạo văn (bộ đầu: 50 trang).
		'web'     => 'none',      // none | google | serper
		'gkey'    => '',
		'gcx'     => '',
		'skey'    => '',
		'queries' => 20,          // Số câu tìm trên Internet mỗi bài.
		'pages'   => 30,          // Số trang web tải về tối đa mỗi bài.
		'minw'    => 12,          // Đoạn trùng tối thiểu (từ).
		'bib'     => 1,           // Loại trừ tài liệu tham khảo.
		'quote'   => 0,           // Loại trừ trích dẫn trong ngoặc kép.
		'repo'    => 1,           // Cho khách chọn lưu bài vào kho.
	);
}

function vs_cdv_cfg( $k = null ) {
	$c = wp_parse_args( (array) get_option( 'vs_cdv', array() ), vs_cdv_defaults() );
	return null === $k ? $c : ( isset( $c[ $k ] ) ? $c[ $k ] : null );
}

function vs_cdv_name() {
	$n = trim( (string) vs_cdv_cfg( 'name' ) );
	return '' !== $n ? $n : 'CDV Scan';
}

/** Đã cấu hình tìm kiếm Internet chưa. */
function vs_cdv_web_ready() {
	$c = vs_cdv_cfg();
	return ( 'google' === $c['web'] && $c['gkey'] && $c['gcx'] ) || ( 'serper' === $c['web'] && $c['skey'] );
}

/* =========================================================
 * Cơ sở dữ liệu (bảng riêng của website này, không đụng web khác)
 * ======================================================= */

function vs_cdv_t( $t ) {
	global $wpdb;
	return $wpdb->prefix . 'vs_cdv_' . $t;
}

add_action( 'init', function () {
	if ( (int) get_option( 'vs_cdv_db' ) < VS_CDV_DB ) {
		vs_cdv_install();
	}
}, 5 );

function vs_cdv_install() {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$cs = $wpdb->get_charset_collate();
	dbDelta( 'CREATE TABLE ' . vs_cdv_t( 'docs' ) . " (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  kind varchar(10) NOT NULL DEFAULT 'upload',
  title varchar(255) NOT NULL DEFAULT '',
  url varchar(700) NOT NULL DEFAULT '',
  uhash char(32) NOT NULL DEFAULT '',
  sha char(32) NOT NULL DEFAULT '',
  words int(10) unsigned NOT NULL DEFAULT 0,
  ref bigint(20) unsigned NOT NULL DEFAULT 0,
  created int(10) unsigned NOT NULL DEFAULT 0,
  body longtext NOT NULL,
  PRIMARY KEY  (id),
  KEY kind (kind),
  KEY uhash (uhash),
  KEY sha (sha)
) $cs;" );
	dbDelta( 'CREATE TABLE ' . vs_cdv_t( 'fp' ) . " (
  h int(10) unsigned NOT NULL,
  d bigint(20) unsigned NOT NULL,
  p int(10) unsigned NOT NULL,
  KEY h (h),
  KEY d (d)
) $cs;" );
	update_option( 'vs_cdv_db', VS_CDV_DB );
}

/* =========================================================
 * Xử lý văn bản
 * ======================================================= */

/** Chuẩn hóa: Unicode dựng sẵn (NFC), bỏ ký tự điều khiển, gộp khoảng trắng. */
function vs_cdv_clean( $text ) {
	$text = (string) $text;
	if ( ! preg_match( '//u', $text ) ) {
		$text = mb_convert_encoding( $text, 'UTF-8', 'UTF-8' );
	}
	if ( class_exists( 'Normalizer' ) ) {
		$n = Normalizer::normalize( $text, Normalizer::FORM_C );
		if ( is_string( $n ) ) {
			$text = $n;
		}
	}
	$text = str_replace( array( "\r\n", "\r", "\xc2\xa0", "\xe2\x80\x8b" ), array( "\n", "\n", ' ', '' ), $text );
	$text = preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text );
	$text = preg_replace( '/[ \t]+/u', ' ', $text );
	$text = preg_replace( "/ *\n */u", "\n", $text );
	$text = preg_replace( "/\n{3,}/u", "\n\n", $text );
	return trim( $text );
}

/**
 * Tách từ. Trả về [ từ viết thường ] hoặc, khi $pos = true, [ [từ thường], [vị trí byte], [từ gốc] ].
 */
function vs_cdv_tokens( $text, $pos = false ) {
	preg_match_all( '/[\p{L}\p{M}\p{N}]+/u', $text, $m, $pos ? PREG_OFFSET_CAPTURE : 0 );
	if ( ! $pos ) {
		return array_map( 'mb_strtolower', $m[0] );
	}
	$low = array();
	$off = array();
	$raw = array();
	foreach ( $m[0] as $t ) {
		$low[] = mb_strtolower( $t[0] );
		$off[] = $t[1];
		$raw[] = $t[0];
	}
	return array( $low, $off, $raw );
}

/** Băm mọi cụm K từ liên tiếp. */
function vs_cdv_hashes( $tok ) {
	$out = array();
	$n   = count( $tok ) - VS_CDV_K + 1;
	for ( $i = 0; $i < $n; $i++ ) {
		$out[ $i ] = crc32( implode( ' ', array_slice( $tok, $i, VS_CDV_K ) ) );
	}
	return $out;
}

/** Winnowing: chọn hash nhỏ nhất trong mỗi cửa sổ W (giảm ~3 lần dung lượng chỉ mục). */
function vs_cdv_winnow( $hs ) {
	$n   = count( $hs );
	$out = array();
	if ( $n <= VS_CDV_W ) {
		return $hs;
	}
	$last = -1;
	for ( $i = 0; $i <= $n - VS_CDV_W; $i++ ) {
		$mi = $i;
		for ( $j = $i; $j < $i + VS_CDV_W; $j++ ) {
			if ( $hs[ $j ] <= $hs[ $mi ] ) {
				$mi = $j;
			}
		}
		if ( $mi !== $last ) {
			$out[ $mi ] = $hs[ $mi ];
			$last       = $mi;
		}
	}
	return $out;
}

/* =========================================================
 * Kho đối chiếu
 * ======================================================= */

/**
 * Thêm tài liệu vào kho. Trả về id tài liệu (hoặc id bản đã có nếu trùng nội dung) hay WP_Error.
 *
 * @param string $kind upload | order | web
 */
function vs_cdv_add_doc( $kind, $title, $text, $url = '', $ref = 0 ) {
	global $wpdb;
	$text = vs_cdv_clean( $text );
	$tok  = vs_cdv_tokens( $text );
	if ( count( $tok ) < 30 ) {
		return new WP_Error( 'short', 'Tài liệu quá ngắn (dưới 30 từ) hoặc không đọc được chữ.' );
	}
	$sha = md5( implode( ' ', $tok ) );
	$dup = $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . vs_cdv_t( 'docs' ) . ' WHERE sha = %s LIMIT 1', $sha ) );
	if ( $dup ) {
		return (int) $dup;
	}
	$ok = $wpdb->insert( vs_cdv_t( 'docs' ), array(
		'kind'    => $kind,
		'title'   => mb_substr( wp_strip_all_tags( (string) $title ), 0, 250 ),
		'url'     => substr( (string) $url, 0, 700 ),
		'uhash'   => $url ? md5( $url ) : '',
		'sha'     => $sha,
		'words'   => count( $tok ),
		'ref'     => (int) $ref,
		'created' => time(),
		'body'    => $text,
	) );
	if ( ! $ok ) {
		return new WP_Error( 'db', 'Không lưu được tài liệu vào kho.' );
	}
	$id   = (int) $wpdb->insert_id;
	$rows = array();
	foreach ( vs_cdv_winnow( vs_cdv_hashes( $tok ) ) as $p => $h ) {
		$rows[] = '(' . (int) $h . ',' . $id . ',' . (int) $p . ')';
		if ( count( $rows ) >= 400 ) {
			$wpdb->query( 'INSERT INTO ' . vs_cdv_t( 'fp' ) . ' (h,d,p) VALUES ' . implode( ',', $rows ) ); // phpcs:ignore
			$rows = array();
		}
	}
	if ( $rows ) {
		$wpdb->query( 'INSERT INTO ' . vs_cdv_t( 'fp' ) . ' (h,d,p) VALUES ' . implode( ',', $rows ) ); // phpcs:ignore
	}
	wp_cache_delete( 'vs_cdv_stats' );
	return $id;
}

function vs_cdv_del_doc( $id ) {
	global $wpdb;
	$id = (int) $id;
	$wpdb->delete( vs_cdv_t( 'fp' ), array( 'd' => $id ) );
	wp_cache_delete( 'vs_cdv_stats' );
	return (bool) $wpdb->delete( vs_cdv_t( 'docs' ), array( 'id' => $id ) );
}

function vs_cdv_doc( $id ) {
	global $wpdb;
	return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . vs_cdv_t( 'docs' ) . ' WHERE id = %d', $id ), ARRAY_A );
}

function vs_cdv_stats() {
	global $wpdb;
	$s = array( 'docs' => 0, 'words' => 0, 'upload' => 0, 'order' => 0, 'web' => 0 );
	foreach ( (array) $wpdb->get_results( 'SELECT kind, COUNT(*) n, SUM(words) w FROM ' . vs_cdv_t( 'docs' ) . ' GROUP BY kind', ARRAY_A ) as $r ) {
		$s[ $r['kind'] ] = (int) $r['n'];
		$s['docs']      += (int) $r['n'];
		$s['words']     += (int) $r['w'];
	}
	return $s;
}

/* =========================================================
 * Tìm trên Internet (API tìm kiếm chính thức)
 * ======================================================= */

/** Chọn các câu đặc trưng (rải đều khắp bài) để tìm. */
function vs_cdv_queries( $text, $max ) {
	$sents = preg_split( '/(?<=[\.\!\?…;:])\s+|\n+/u', $text );
	$pool  = array();
	foreach ( $sents as $s ) {
		$w = preg_split( '/\s+/u', trim( $s ), -1, PREG_SPLIT_NO_EMPTY );
		if ( count( $w ) >= 12 && count( $w ) <= 80 ) {
			$mid    = (int) floor( ( count( $w ) - 10 ) / 2 );
			$pool[] = implode( ' ', array_slice( $w, $mid, 10 ) );
		}
	}
	$pool = array_values( array_unique( $pool ) );
	if ( count( $pool ) <= $max ) {
		return $pool;
	}
	$out  = array();
	$step = count( $pool ) / $max;
	for ( $i = 0; $i < $max; $i++ ) {
		$out[] = $pool[ (int) floor( $i * $step ) ];
	}
	return $out;
}

/** Tìm 1 câu. Trả về [ [url, title] ] hoặc WP_Error. */
function vs_cdv_search( $q ) {
	$c = vs_cdv_cfg();
	$q = '"' . trim( preg_replace( '/["“”]/u', '', $q ) ) . '"';
	if ( 'google' === $c['web'] ) {
		$r = wp_remote_get( add_query_arg( array( 'key' => $c['gkey'], 'cx' => $c['gcx'], 'q' => rawurlencode( $q ), 'num' => 5 ), 'https://www.googleapis.com/customsearch/v1' ), array( 'timeout' => 12 ) );
		if ( is_wp_error( $r ) ) {
			return $r;
		}
		$j = json_decode( wp_remote_retrieve_body( $r ), true );
		if ( 200 !== wp_remote_retrieve_response_code( $r ) ) {
			return new WP_Error( 'search', 'Google: ' . ( isset( $j['error']['message'] ) ? $j['error']['message'] : 'mã ' . wp_remote_retrieve_response_code( $r ) ) );
		}
		$items = isset( $j['items'] ) ? (array) $j['items'] : array();
		return array_map( function ( $i ) {
			return array( isset( $i['link'] ) ? $i['link'] : '', isset( $i['title'] ) ? $i['title'] : '' );
		}, $items );
	}
	if ( 'serper' === $c['web'] ) {
		$r = wp_remote_post( 'https://google.serper.dev/search', array(
			'timeout' => 12,
			'headers' => array( 'X-API-KEY' => $c['skey'], 'Content-Type' => 'application/json' ),
			'body'    => wp_json_encode( array( 'q' => $q, 'num' => 5, 'gl' => 'vn', 'hl' => 'vi' ) ),
		) );
		if ( is_wp_error( $r ) ) {
			return $r;
		}
		$j = json_decode( wp_remote_retrieve_body( $r ), true );
		if ( 200 !== wp_remote_retrieve_response_code( $r ) ) {
			return new WP_Error( 'search', 'Serper: ' . ( isset( $j['message'] ) ? $j['message'] : 'mã ' . wp_remote_retrieve_response_code( $r ) ) );
		}
		$items = isset( $j['organic'] ) ? (array) $j['organic'] : array();
		return array_map( function ( $i ) {
			return array( isset( $i['link'] ) ? $i['link'] : '', isset( $i['title'] ) ? $i['title'] : '' );
		}, $items );
	}
	return array();
}

/** Tải 1 trang web về kho (dùng lại bản đã tải trong 180 ngày). Trả về id tài liệu hoặc WP_Error. */
function vs_cdv_fetch( $url, $title = '' ) {
	global $wpdb;
	$have = $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . vs_cdv_t( 'docs' ) . ' WHERE uhash = %s AND created > %d LIMIT 1', md5( $url ), time() - 180 * DAY_IN_SECONDS ) );
	if ( $have ) {
		return (int) $have;
	}
	if ( preg_match( '/\.(pdf|docx?|pptx?|xlsx?|zip|rar)(\?|$)/i', $url ) ) {
		return new WP_Error( 'type', 'Bỏ qua tệp không phải trang web' );
	}
	// wp_safe_remote_get chặn địa chỉ nội bộ (không thể dùng để truy cập máy chủ của web khác).
	$r = wp_safe_remote_get( $url, array(
		'timeout'             => 8,
		'redirection'         => 3,
		'limit_response_size' => 2 * MB_IN_BYTES,
		'user-agent'          => 'Mozilla/5.0 (compatible; CDVScan/1.0; +' . home_url( '/' ) . ')',
		'headers'             => array( 'Accept' => 'text/html,application/xhtml+xml', 'Accept-Language' => 'vi,en;q=0.8' ),
	) );
	if ( is_wp_error( $r ) ) {
		return $r;
	}
	$type = (string) wp_remote_retrieve_header( $r, 'content-type' );
	if ( 200 !== wp_remote_retrieve_response_code( $r ) || ( $type && false === stripos( $type, 'html' ) ) ) {
		return new WP_Error( 'http', 'Trang không tải được' );
	}
	$html = (string) wp_remote_retrieve_body( $r );
	if ( preg_match( '/charset=["\']?([\w-]+)/i', $type . ' ' . substr( $html, 0, 2000 ), $m ) && ! in_array( strtolower( $m[1] ), array( 'utf-8', 'utf8' ), true ) ) {
		$conv = @mb_convert_encoding( $html, 'UTF-8', $m[1] );
		if ( $conv ) {
			$html = $conv;
		}
	}
	if ( ! $title && preg_match( '#<title[^>]*>(.*?)</title>#is', $html, $m ) ) {
		$title = html_entity_decode( trim( $m[1] ), ENT_QUOTES, 'UTF-8' );
	}
	$html = preg_replace( '#<(script|style|noscript|svg|nav|footer|header|form|iframe|template)\b.*?</\1>#is', ' ', $html );
	$html = preg_replace( '#<(br|/p|/div|/li|/h[1-6]|/tr|/section|/article)[^>]*>#i', "\n", $html );
	$text = html_entity_decode( wp_strip_all_tags( $html ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	return vs_cdv_add_doc( 'web', $title ? $title : wp_parse_url( $url, PHP_URL_HOST ), $text, $url );
}

/** Kiểm tra kết nối API tìm kiếm. */
function vs_cdv_web_test() {
	if ( ! vs_cdv_web_ready() ) {
		return new WP_Error( 'cfg', 'Chưa nhập khóa API tìm kiếm.' );
	}
	$r = vs_cdv_search( 'kiểm tra đạo văn luận văn' );
	return is_wp_error( $r ) ? $r : 'Kết nối thành công: tìm thấy ' . count( $r ) . ' kết quả mẫu.';
}

/* =========================================================
 * So khớp
 * ======================================================= */

/** Vị trí byte bắt đầu phần Tài liệu tham khảo (hoặc -1). */
function vs_cdv_bib_offset( $text ) {
	if ( preg_match_all( '/^\s*(?:[IVX\d\.]+\s*)?(danh mục tài liệu tham khảo|tài liệu tham khảo|references|bibliography|works cited)\s*:?\s*$/imu', $text, $m, PREG_OFFSET_CAPTURE ) ) {
		$last = end( $m[0] );
		// Chỉ nhận khi nằm ở nửa sau của bài (tránh mục lục ở đầu bài).
		if ( $last[1] > strlen( $text ) * 0.4 ) {
			return $last[1];
		}
	}
	return -1;
}

/**
 * So khớp văn bản với kho đối chiếu.
 *
 * @param string $text Văn bản đã chuẩn hóa.
 * @param array  $opt  bib, quote, minw, skip (id tài liệu bỏ qua).
 * @return array
 */
function vs_cdv_match( $text, $opt ) {
	global $wpdb;
	$minw = max( VS_CDV_K + 2, (int) $opt['minw'] );
	list( $q, $off, $raw ) = vs_cdv_tokens( $text, true );
	$n = count( $q );

	// Phần loại trừ.
	$ex  = array_fill( 0, $n, false );
	$bib = ! empty( $opt['bib'] ) ? vs_cdv_bib_offset( $text ) : -1;
	$qr  = array();
	if ( ! empty( $opt['quote'] ) && preg_match_all( '/“[^”]{1,1500}”|"[^"\n]{1,1500}"|«[^»]{1,1500}»/u', $text, $mm, PREG_OFFSET_CAPTURE ) ) {
		foreach ( $mm[0] as $x ) {
			$qr[] = array( $x[1], $x[1] + strlen( $x[0] ) );
		}
	}
	$excluded = 0;
	for ( $i = 0; $i < $n; $i++ ) {
		if ( $bib >= 0 && $off[ $i ] >= $bib ) {
			$ex[ $i ] = true;
		} else {
			foreach ( $qr as $r ) {
				if ( $off[ $i ] >= $r[0] && $off[ $i ] < $r[1] ) {
					$ex[ $i ] = true;
					break;
				}
			}
		}
		$excluded += $ex[ $i ] ? 1 : 0;
	}

	// Tìm các cụm trùng trong chỉ mục.
	$hs  = vs_cdv_hashes( $q );
	$byh = array();
	foreach ( $hs as $i => $h ) {
		$byh[ $h ][] = $i;
	}
	$skip  = array_flip( array_map( 'intval', isset( $opt['skip'] ) ? (array) $opt['skip'] : array() ) );
	$same  = $wpdb->get_col( $wpdb->prepare( 'SELECT id FROM ' . vs_cdv_t( 'docs' ) . ' WHERE sha = %s', md5( implode( ' ', $q ) ) ) );
	foreach ( $same as $sid ) {
		$skip[ (int) $sid ] = 1; // Chính bài này đã có trong kho (nộp lại): không so với chính nó.
	}
	$rows = array();
	$per  = array();
	foreach ( array_chunk( array_keys( $byh ), 800 ) as $chunk ) {
		$res = $wpdb->get_results( 'SELECT h, d, p FROM ' . vs_cdv_t( 'fp' ) . ' WHERE h IN (' . implode( ',', array_map( 'intval', $chunk ) ) . ')', ARRAY_N ); // phpcs:ignore
		foreach ( (array) $res as $r ) {
			$d = (int) $r[1];
			if ( isset( $skip[ $d ] ) ) {
				continue;
			}
			$rows[]                  = array( (int) $r[0], $d, (int) $r[2] );
			$per[ (int) $r[0] ][ $d ] = 1;
		}
	}
	$seeds = array();
	foreach ( $rows as $r ) {
		if ( count( $per[ $r[0] ] ) > 40 || ! isset( $byh[ $r[0] ] ) ) {
			continue; // Cụm từ quá phổ biến (xuất hiện ở rất nhiều tài liệu): bỏ qua.
		}
		foreach ( $byh[ $r[0] ] as $qi ) {
			$seeds[ $r[1] ][] = array( $qi, $r[2] );
		}
	}
	uasort( $seeds, function ( $a, $b ) {
		return count( $b ) - count( $a );
	} );
	$seeds = array_slice( $seeds, 0, 60, true );

	// Mở rộng thành đoạn trùng dài nhất với từng nguồn.
	$spans = array();
	$docs  = array();
	foreach ( $seeds as $d => $list ) {
		$doc = vs_cdv_doc( $d );
		if ( ! $doc ) {
			continue;
		}
		$s  = vs_cdv_tokens( $doc['body'] );
		$ns = count( $s );
		usort( $list, function ( $a, $b ) {
			return $a[0] - $b[0];
		} );
		$out = array();
		foreach ( $list as $sd ) {
			list( $qi, $dp ) = $sd;
			$dup = false;
			foreach ( $out as $o ) {
				if ( $qi >= $o[0] && $qi < $o[1] && $dp - $qi === $o[2] - $o[0] ) {
					$dup = true;
					break;
				}
			}
			if ( $dup || $dp + VS_CDV_K > $ns || array_slice( $q, $qi, VS_CDV_K ) !== array_slice( $s, $dp, VS_CDV_K ) ) {
				continue; // Trùng mã băm nhưng khác chữ, hoặc đã nằm trong đoạn đã tìm.
			}
			$a = $qi;
			$b = $dp;
			while ( $a > 0 && $b > 0 && $q[ $a - 1 ] === $s[ $b - 1 ] ) {
				$a--;
				$b--;
			}
			$e  = $qi + VS_CDV_K;
			$de = $dp + VS_CDV_K;
			while ( $e < $n && $de < $ns && $q[ $e ] === $s[ $de ] ) {
				$e++;
				$de++;
			}
			$out[] = array( $a, $e, $b, $de );
		}
		// Gộp các đoạn gần nhau (cách nhau ≤ 3 từ ở cả 2 bên: thường là sửa vài chữ).
		usort( $out, function ( $x, $y ) {
			return $x[0] - $y[0];
		} );
		$merged = array();
		foreach ( $out as $o ) {
			$k = count( $merged ) - 1;
			if ( $k >= 0 && $o[0] - $merged[ $k ][1] <= 3 && $o[0] >= $merged[ $k ][0] && abs( ( $o[2] - $merged[ $k ][3] ) - ( $o[0] - $merged[ $k ][1] ) ) <= 3 ) {
				$merged[ $k ][1] = max( $merged[ $k ][1], $o[1] );
				$merged[ $k ][3] = max( $merged[ $k ][3], $o[3] );
			} else {
				$merged[] = $o;
			}
		}
		$merged = array_values( array_filter( $merged, function ( $o ) use ( $minw ) {
			return $o[1] - $o[0] >= $minw;
		} ) );
		if ( $merged ) {
			$spans[ $d ] = $merged;
			$docs[ $d ]  = array( 'doc' => $doc, 'tok' => $s );
		}
	}

	// Mỗi từ thuộc về nguồn có đoạn trùng dài nhất.
	$owner = array_fill( 0, $n, 0 );
	$olen  = array_fill( 0, $n, 0 );
	foreach ( $spans as $d => $list ) {
		foreach ( $list as $o ) {
			$len = $o[1] - $o[0];
			for ( $i = $o[0]; $i < $o[1]; $i++ ) {
				if ( ! $ex[ $i ] && $len > $olen[ $i ] ) {
					$owner[ $i ] = $d;
					$olen[ $i ]  = $len;
				}
			}
		}
	}
	$count = array();
	foreach ( $owner as $d ) {
		if ( $d ) {
			$count[ $d ] = isset( $count[ $d ] ) ? $count[ $d ] + 1 : 1;
		}
	}
	arsort( $count );
	$num     = array();
	$sources = array();
	foreach ( $count as $d => $c ) {
		if ( $c < $minw / 2 ) {
			continue; // Nguồn chỉ còn vài từ lẻ sau khi chia cho nguồn khác: bỏ.
		}
		$num[ $d ] = count( $sources ) + 1;
		$doc       = $docs[ $d ]['doc'];
		// Đoạn trùng tiêu biểu ở nguồn (đoạn dài nhất).
		$best = $spans[ $d ][0];
		foreach ( $spans[ $d ] as $o ) {
			if ( $o[1] - $o[0] > $best[1] - $best[0] ) {
				$best = $o;
			}
		}
		$snip      = implode( ' ', array_slice( $docs[ $d ]['tok'], $best[2], min( 40, $best[3] - $best[2] ) ) );
		$sources[] = array(
			'n'     => $num[ $d ],
			'id'    => (int) $d,
			'kind'  => $doc['kind'],
			'title' => $doc['title'] ? $doc['title'] : ( $doc['url'] ? wp_parse_url( $doc['url'], PHP_URL_HOST ) : 'Tài liệu #' . $d ),
			'url'   => 'web' === $doc['kind'] ? $doc['url'] : '',
			'host'  => $doc['url'] ? preg_replace( '/^www\./', '', (string) wp_parse_url( $doc['url'], PHP_URL_HOST ) ) : '',
			'words' => $c,
			'pct'   => $n ? round( $c / $n * 100, 1 ) : 0,
			'snip'  => $snip . ( $best[3] - $best[2] > 40 ? '…' : '' ),
		);
		if ( count( $sources ) >= 40 ) {
			break;
		}
	}

	// Đoạn tô màu (vị trí ký tự để trang báo cáo hiển thị).
	$segs    = array();
	$matched = 0;
	$cpos    = 0;
	$bprev   = 0;
	$cur     = null;
	for ( $i = 0; $i < $n; $i++ ) {
		$cpos += mb_strlen( substr( $text, $bprev, $off[ $i ] - $bprev ) );
		$cs    = $cpos;
		$cpos += mb_strlen( $raw[ $i ] );
		$bprev = $off[ $i ] + strlen( $raw[ $i ] );
		$sn    = $owner[ $i ] && isset( $num[ $owner[ $i ] ] ) ? $num[ $owner[ $i ] ] : 0;
		if ( $sn ) {
			$matched++;
		}
		if ( $cur && $cur[2] === $sn && ( $sn || false ) ) {
			$cur[1] = $cpos;
			continue;
		}
		if ( $cur ) {
			$segs[] = $cur;
			$cur    = null;
		}
		if ( $sn ) {
			$cur = array( $cs, $cpos, $sn );
		}
	}
	if ( $cur ) {
		$segs[] = $cur;
	}
	$exs = array();
	if ( $bib >= 0 ) {
		$exs[] = array( mb_strlen( substr( $text, 0, $bib ) ), mb_strlen( $text ), 'bib' );
	}
	foreach ( $qr as $r ) {
		$exs[] = array( mb_strlen( substr( $text, 0, $r[0] ) ), mb_strlen( substr( $text, 0, $r[1] ) ), 'quote' );
	}

	return array(
		'score'    => $n ? (int) round( $matched / $n * 100 ) : 0,
		'exact'    => $n ? round( $matched / $n * 100, 1 ) : 0,
		'words'    => $n,
		'matched'  => $matched,
		'excluded' => $excluded,
		'sources'  => $sources,
		'segs'     => $segs,
		'exs'      => $exs,
		'by_kind'  => array_reduce( $sources, function ( $a, $s ) {
			$a[ $s['kind'] ] = round( ( isset( $a[ $s['kind'] ] ) ? $a[ $s['kind'] ] : 0 ) + $s['pct'], 1 );
			return $a;
		}, array() ),
	);
}

/* =========================================================
 * Lượt kiểm tra (dùng chung cho đơn hàng và "Kiểm tra nhanh" của quản trị)
 * ======================================================= */

function vs_cdv_dir() {
	$dir = vs_private_dir() . '/cdv';
	if ( ! is_dir( $dir ) ) {
		wp_mkdir_p( $dir );
		file_put_contents( $dir . '/index.php', "<?php // Silence.\n" );
	}
	return $dir;
}

function vs_cdv_scan_path( $id ) {
	return vs_cdv_dir() . '/' . preg_replace( '/[^a-z0-9]/', '', strtolower( $id ) ) . '.json';
}

function vs_cdv_scan( $id ) {
	$p = vs_cdv_scan_path( $id );
	if ( ! is_file( $p ) ) {
		return null;
	}
	$s = json_decode( (string) file_get_contents( $p ), true );
	return is_array( $s ) ? $s : null;
}

function vs_cdv_scan_save( $s ) {
	file_put_contents( vs_cdv_scan_path( $s['id'] ), wp_json_encode( $s, JSON_UNESCAPED_UNICODE ) );
}

/** Tạo lượt kiểm tra mới. Trả về id hoặc WP_Error. */
function vs_cdv_scan_new( $text, $opt = array() ) {
	$text = vs_cdv_clean( $text );
	$tok  = vs_cdv_tokens( $text );
	if ( count( $tok ) < 30 ) {
		return new WP_Error( 'short', 'Văn bản quá ngắn (cần ít nhất 30 từ).' );
	}
	$c   = vs_cdv_cfg();
	$opt = wp_parse_args( $opt, array(
		'title' => 'Văn bản',
		'bib'   => $c['bib'],
		'quote' => $c['quote'],
		'minw'  => $c['minw'],
		'web'   => vs_cdv_web_ready(),
		'repo'  => 0,
		'ref'   => 0,
		'by'    => get_current_user_id(),
	) );
	$s = array(
		'id'      => strtolower( wp_generate_password( 16, false ) ),
		'created' => time(),
		'state'   => $opt['web'] ? 'search' : 'match',
		'opt'     => $opt,
		'text'    => $text,
		'queries' => $opt['web'] ? vs_cdv_queries( $text, max( 1, min( 60, (int) $c['queries'] ) ) ) : array(),
		'qi'      => 0,
		'urls'    => array(),
		'ui'      => 0,
		'webdocs' => 0,
		'errors'  => array(),
		'result'  => null,
	);
	vs_cdv_scan_save( $s );
	return $s['id'];
}

/** Tiến độ 0-100 và mô tả bước hiện tại. */
function vs_cdv_progress( $s ) {
	$nq = max( 1, count( $s['queries'] ) );
	$nu = max( 1, count( $s['urls'] ) );
	switch ( $s['state'] ) {
		case 'search':
			return array( 5 + (int) ( 35 * $s['qi'] / $nq ), 'Đang tìm trên Internet (' . $s['qi'] . '/' . count( $s['queries'] ) . ' câu)' );
		case 'fetch':
			return array( 40 + (int) ( 40 * $s['ui'] / $nu ), 'Đang tải nguồn để đối chiếu (' . $s['ui'] . '/' . count( $s['urls'] ) . ' trang)' );
		case 'match':
			return array( 85, 'Đang so khớp với kho đối chiếu' );
		case 'done':
			return array( 100, 'Hoàn tất' );
	}
	return array( 0, 'Lỗi' );
}

/**
 * Chạy tiếp lượt kiểm tra trong tối đa $budget giây. Trả về lượt kiểm tra đã cập nhật.
 */
function vs_cdv_scan_step( $id, $budget = 20 ) {
	$s = vs_cdv_scan( $id );
	if ( ! $s || in_array( $s['state'], array( 'done', 'failed' ), true ) ) {
		return $s;
	}
	$lock = 'vs_cdv_lock_' . $id;
	if ( get_transient( $lock ) ) {
		return $s; // Đang được 1 tiến trình khác xử lý.
	}
	set_transient( $lock, 1, 120 );
	$t0 = microtime( true );
	$c  = vs_cdv_cfg();
	@set_time_limit( 120 ); // phpcs:ignore

	while ( microtime( true ) - $t0 < $budget && in_array( $s['state'], array( 'search', 'fetch', 'match' ), true ) ) {
		if ( 'search' === $s['state'] ) {
			if ( $s['qi'] >= count( $s['queries'] ) ) {
				$s['state'] = $s['urls'] ? 'fetch' : 'match';
				continue;
			}
			$r = vs_cdv_search( $s['queries'][ $s['qi'] ] );
			$s['qi']++;
			if ( is_wp_error( $r ) ) {
				$s['errors'][] = $r->get_error_message();
				if ( count( $s['errors'] ) >= 3 && ! $s['urls'] ) {
					$s['state'] = 'match'; // API tìm kiếm lỗi liên tục: vẫn so với kho nội bộ.
				}
				continue;
			}
			foreach ( $r as $it ) {
				$u = esc_url_raw( $it[0] );
				if ( $u && count( $s['urls'] ) < (int) $c['pages'] && ! in_array( $u, wp_list_pluck( $s['urls'], 0 ), true ) && false === strpos( $u, (string) wp_parse_url( home_url(), PHP_URL_HOST ) ) ) {
					$s['urls'][] = array( $u, wp_strip_all_tags( (string) $it[1] ) );
				}
			}
		} elseif ( 'fetch' === $s['state'] ) {
			if ( $s['ui'] >= count( $s['urls'] ) ) {
				$s['state'] = 'match';
				continue;
			}
			$u = $s['urls'][ $s['ui'] ];
			$s['ui']++;
			$r = vs_cdv_fetch( $u[0], $u[1] );
			if ( ! is_wp_error( $r ) ) {
				$s['webdocs']++;
			}
		} else {
			$skip = array();
			if ( ! empty( $s['opt']['ref'] ) ) {
				global $wpdb;
				$skip = $wpdb->get_col( $wpdb->prepare( 'SELECT id FROM ' . vs_cdv_t( 'docs' ) . ' WHERE kind = %s AND ref = %d', 'order', (int) $s['opt']['ref'] ) );
			}
			$s['result']   = vs_cdv_match( $s['text'], array( 'bib' => $s['opt']['bib'], 'quote' => $s['opt']['quote'], 'minw' => $s['opt']['minw'], 'skip' => $skip ) );
			$s['state']    = 'done';
			$s['finished'] = time();
			// Khách đồng ý: lưu bài vào kho để phát hiện trùng giữa các bài nộp sau này (sau khi đã so khớp).
			if ( ! empty( $s['opt']['repo'] ) ) {
				vs_cdv_add_doc( 'order', $s['opt']['title'], $s['text'], '', (int) $s['opt']['ref'] );
			}
		}
	}
	vs_cdv_scan_save( $s );
	delete_transient( $lock );
	return $s;
}

/* =========================================================
 * Liên kết xem báo cáo (ký bằng khóa bí mật của website)
 * ======================================================= */

function vs_cdv_key( $id ) {
	return substr( wp_hash( 'cdv|' . $id, 'auth' ), 0, 20 );
}

function vs_cdv_view_url( $id, $pdf = false ) {
	return add_query_arg( array_filter( array( 'vs_scan' => $id, 'k' => vs_cdv_key( $id ), 'pdf' => $pdf ? 1 : 0 ) ), home_url( '/' ) );
}

/** Dọn lượt kiểm tra cũ (theo chính sách lưu file 30 ngày). */
add_action( 'vs_auto_tick', function () {
	if ( get_transient( 'vs_cdv_gc' ) ) {
		return;
	}
	set_transient( 'vs_cdv_gc', 1, DAY_IN_SECONDS );
	foreach ( (array) glob( vs_cdv_dir() . '/*.json' ) as $f ) {
		if ( filemtime( $f ) < time() - 30 * DAY_IN_SECONDS ) {
			@unlink( $f ); // phpcs:ignore
		}
	}
} );

require __DIR__ . '/report.php';
require __DIR__ . '/integrate.php';
require __DIR__ . '/admin.php';
