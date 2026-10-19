<?php
/**
 * Bộ tạo PDF tối giản (không cần thư viện ngoài) có nhúng phông TrueType Unicode,
 * dùng để xuất báo cáo AI từ kết quả API Originality.ai.
 *
 * Phông: Be Vietnam Pro (SIL Open Font License, assets/fonts/OFL.txt).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Đọc các bảng cần thiết của file .ttf: bảng mã ký tự (cmap), độ rộng ký tự, thông số dòng. */
class VS_TTF {
	public $data;
	public $upm   = 1000;
	public $asc   = 800;
	public $desc  = -200;
	public $bbox  = array( 0, -200, 1000, 800 );
	public $map   = array(); // codepoint => glyph id
	public $adv   = array(); // glyph id => độ rộng (đơn vị 1/1000 em)
	private $tables = array();

	public function __construct( $file ) {
		$this->data = (string) file_get_contents( $file );
		$n          = $this->u16( 4 );
		for ( $i = 0; $i < $n; $i++ ) {
			$o = 12 + $i * 16;
			$this->tables[ substr( $this->data, $o, 4 ) ] = $this->u32( $o + 8 );
		}
		$head      = $this->tables['head'];
		$this->upm = $this->u16( $head + 18 );
		$k         = 1000 / $this->upm;
		$this->bbox = array( (int) round( $this->s16( $head + 36 ) * $k ), (int) round( $this->s16( $head + 38 ) * $k ), (int) round( $this->s16( $head + 40 ) * $k ), (int) round( $this->s16( $head + 42 ) * $k ) );
		$hhea       = $this->tables['hhea'];
		$this->asc  = (int) round( $this->s16( $hhea + 4 ) * $k );
		$this->desc = (int) round( $this->s16( $hhea + 6 ) * $k );
		$nh         = $this->u16( $hhea + 34 );
		$hmtx       = $this->tables['hmtx'];
		$last       = 0;
		$ng         = $this->u16( $this->tables['maxp'] + 4 );
		for ( $g = 0; $g < $ng; $g++ ) {
			if ( $g < $nh ) {
				$last = (int) round( $this->u16( $hmtx + $g * 4 ) * $k );
			}
			$this->adv[ $g ] = $last;
		}
		$this->read_cmap();
	}

	private function u16( $o ) {
		return unpack( 'n', substr( $this->data, $o, 2 ) )[1];
	}
	private function s16( $o ) {
		$v = $this->u16( $o );
		return $v >= 0x8000 ? $v - 0x10000 : $v;
	}
	private function u32( $o ) {
		return unpack( 'N', substr( $this->data, $o, 4 ) )[1];
	}

	private function read_cmap() {
		$c    = $this->tables['cmap'];
		$n    = $this->u16( $c + 2 );
		$best = 0;
		$fmt  = 0;
		for ( $i = 0; $i < $n; $i++ ) {
			$pid = $this->u16( $c + 4 + $i * 8 );
			$eid = $this->u16( $c + 6 + $i * 8 );
			$off = $c + $this->u32( $c + 8 + $i * 8 );
			$f   = $this->u16( $off );
			if ( 3 === $pid && 10 === $eid && 12 === $f ) {
				$best = $off;
				$fmt  = 12;
				break;
			}
			if ( ( 3 === $pid && 1 === $eid || 0 === $pid ) && 4 === $f && ! $best ) {
				$best = $off;
				$fmt  = 4;
			}
		}
		if ( 12 === $fmt ) {
			$groups = $this->u32( $best + 12 );
			for ( $i = 0; $i < $groups; $i++ ) {
				$o     = $best + 16 + $i * 12;
				$start = $this->u32( $o );
				$end   = min( $this->u32( $o + 4 ), 0x2FFFF );
				$gid   = $this->u32( $o + 8 );
				for ( $cp = $start; $cp <= $end; $cp++ ) {
					$this->map[ $cp ] = $gid + $cp - $start;
				}
			}
			return;
		}
		if ( 4 === $fmt ) {
			$seg = $this->u16( $best + 6 ) / 2;
			$ends = $best + 14;
			$starts = $ends + $seg * 2 + 2;
			$deltas = $starts + $seg * 2;
			$ranges = $deltas + $seg * 2;
			for ( $i = 0; $i < $seg; $i++ ) {
				$end   = $this->u16( $ends + $i * 2 );
				$start = $this->u16( $starts + $i * 2 );
				$delta = $this->u16( $deltas + $i * 2 );
				$ro    = $this->u16( $ranges + $i * 2 );
				for ( $cp = $start; $cp <= $end && $cp < 0xFFFF; $cp++ ) {
					if ( 0 === $ro ) {
						$g = ( $cp + $delta ) & 0xFFFF;
					} else {
						$g = $this->u16( $ranges + $i * 2 + $ro + ( $cp - $start ) * 2 );
						if ( $g ) {
							$g = ( $g + $delta ) & 0xFFFF;
						}
					}
					if ( $g ) {
						$this->map[ $cp ] = $g;
					}
				}
			}
		}
	}
}

/** Tài liệu PDF khổ A4, tọa độ tính từ góc trên bên trái (pt). */
class VS_PDF {
	const W = 595.28;
	const H = 841.89;

	private $fonts = array();
	private $used  = array(); // font => [gid => codepoint]
	private $pages = array();
	private $cur   = -1;

	public function __construct( $fonts ) {
		foreach ( $fonts as $key => $file ) {
			$this->fonts[ $key ] = new VS_TTF( $file );
			$this->used[ $key ]  = array();
		}
	}

	public function add_page() {
		$this->pages[] = '';
		$this->cur     = count( $this->pages ) - 1;
	}

	public function page_count() {
		return count( $this->pages );
	}

	public function set_page( $i ) {
		$this->cur = $i;
	}

	private function out( $s ) {
		$this->pages[ $this->cur ] .= $s . "\n";
	}

	private static function rgb( $hex ) {
		$hex = ltrim( $hex, '#' );
		return sprintf( '%.3F %.3F %.3F', hexdec( substr( $hex, 0, 2 ) ) / 255, hexdec( substr( $hex, 2, 2 ) ) / 255, hexdec( substr( $hex, 4, 2 ) ) / 255 );
	}

	private static function chars( $s ) {
		if ( class_exists( 'Normalizer' ) ) {
			$n = Normalizer::normalize( $s, Normalizer::FORM_C );
			$s = false === $n ? $s : $n;
		}
		$out = array();
		foreach ( preg_split( '//u', $s, -1, PREG_SPLIT_NO_EMPTY ) as $ch ) {
			$out[] = mb_ord( $ch, 'UTF-8' );
		}
		return $out;
	}

	public function width( $s, $font, $size ) {
		$f = $this->fonts[ $font ];
		$w = 0;
		foreach ( self::chars( $s ) as $cp ) {
			$g  = isset( $f->map[ $cp ] ) ? $f->map[ $cp ] : 0;
			$w += isset( $f->adv[ $g ] ) ? $f->adv[ $g ] : 500;
		}
		return $w * $size / 1000;
	}

	public function rect( $x, $y, $w, $h, $fill ) {
		$this->out( sprintf( '%s rg %.2F %.2F %.2F %.2F re f', self::rgb( $fill ), $x, self::H - $y - $h, $w, $h ) );
	}

	public function line( $x1, $y1, $x2, $y2, $color, $width = 0.6 ) {
		$this->out( sprintf( '%s RG %.2F w %.2F %.2F m %.2F %.2F l S', self::rgb( $color ), $width, $x1, self::H - $y1, $x2, self::H - $y2 ) );
	}

	/** Viết chữ; $y là đường chân chữ (baseline). $align: L | R | C. */
	public function text( $x, $y, $s, $font, $size, $color = '#1f2937', $align = 'L' ) {
		$f   = $this->fonts[ $font ];
		$hex = '';
		foreach ( self::chars( $s ) as $cp ) {
			$g = isset( $f->map[ $cp ] ) ? $f->map[ $cp ] : ( isset( $f->map[ 63 ] ) ? $f->map[ 63 ] : 0 );
			$this->used[ $font ][ $g ] = $cp;
			$hex .= sprintf( '%04X', $g );
		}
		if ( 'R' === $align ) {
			$x -= $this->width( $s, $font, $size );
		} elseif ( 'C' === $align ) {
			$x -= $this->width( $s, $font, $size ) / 2;
		}
		$this->out( sprintf( 'BT %s rg /%s %.2F Tf %.2F %.2F Td <%s> Tj ET', self::rgb( $color ), $font, $size, $x, self::H - $y, $hex ) );
	}

	/** Ngắt dòng theo độ rộng tối đa. */
	public function wrap( $s, $font, $size, $max ) {
		$lines = array();
		foreach ( preg_split( "/\r?\n/", (string) $s ) as $para ) {
			$line = '';
			foreach ( preg_split( '/\s+/u', trim( $para ), -1, PREG_SPLIT_NO_EMPTY ) as $word ) {
				$try = '' === $line ? $word : $line . ' ' . $word;
				if ( '' !== $line && $this->width( $try, $font, $size ) > $max ) {
					$lines[] = $line;
					$line    = $word;
				} else {
					$line = $try;
				}
			}
			$lines[] = $line;
		}
		return $lines;
	}

	/** Xuất toàn bộ tài liệu thành chuỗi byte PDF. */
	public function output( $title = '' ) {
		$objs = array();
		$add  = function ( $body ) use ( &$objs ) {
			$objs[] = $body;
			return count( $objs );
		};
		$stream = function ( $data, $extra = '' ) {
			$filter = '';
			if ( function_exists( 'gzcompress' ) ) {
				$data   = gzcompress( $data, 6 );
				$filter = ' /Filter /FlateDecode';
			}
			return '<< /Length ' . strlen( $data ) . $filter . $extra . " >>\nstream\n" . $data . "\nendstream";
		};

		$catalog = $add( '' );
		$pages   = $add( '' );

		// Phông Type0 / CIDFontType2, mã hóa Identity-H.
		$font_refs = array();
		foreach ( $this->fonts as $key => $f ) {
			$used = $this->used[ $key ];
			ksort( $used );
			$w = '';
			foreach ( $used as $g => $cp ) {
				$w .= $g . ' [' . ( isset( $f->adv[ $g ] ) ? $f->adv[ $g ] : 500 ) . '] ';
			}
			$file = $add( $stream( $f->data, ' /Length1 ' . strlen( $f->data ) ) );
			$desc = $add( sprintf( '<< /Type /FontDescriptor /FontName /VSFont%s /Flags 32 /FontBBox [%d %d %d %d] /ItalicAngle 0 /Ascent %d /Descent %d /CapHeight %d /StemV 80 /FontFile2 %d 0 R >>', $key, $f->bbox[0], $f->bbox[1], $f->bbox[2], $f->bbox[3], $f->asc, $f->desc, $f->asc, $file ) );
			$cid  = $add( sprintf( '<< /Type /Font /Subtype /CIDFontType2 /BaseFont /VSFont%s /CIDSystemInfo << /Registry (Adobe) /Ordering (Identity) /Supplement 0 >> /FontDescriptor %d 0 R /DW 500 /W [%s] /CIDToGIDMap /Identity >>', $key, $desc, $w ) );
			// Bảng ToUnicode để sao chép / tìm kiếm chữ trong PDF.
			$cmap  = "/CIDInit /ProcSet findresource begin\n12 dict begin\nbegincmap\n/CIDSystemInfo << /Registry (Adobe) /Ordering (UCS) /Supplement 0 >> def\n/CMapName /Adobe-Identity-UCS def\n/CMapType 2 def\n1 begincodespacerange\n<0000> <FFFF>\nendcodespacerange\n";
			foreach ( array_chunk( $used, 100, true ) as $chunk ) {
				$cmap .= count( $chunk ) . " beginbfchar\n";
				foreach ( $chunk as $g => $cp ) {
					$u     = mb_convert_encoding( mb_chr( $cp, 'UTF-8' ), 'UTF-16BE', 'UTF-8' );
					$cmap .= sprintf( "<%04X> <%s>\n", $g, strtoupper( bin2hex( $u ) ) );
				}
				$cmap .= "endbfchar\n";
			}
			$cmap .= "endcmap\nCMapName currentdict /CMap defineresource pop\nend\nend";
			$tu   = $add( $stream( $cmap ) );
			$font_refs[ $key ] = $add( sprintf( '<< /Type /Font /Subtype /Type0 /BaseFont /VSFont%s /Encoding /Identity-H /DescendantFonts [%d 0 R] /ToUnicode %d 0 R >>', $key, $cid, $tu ) );
		}
		$res = '<< /Font << ';
		foreach ( $font_refs as $key => $ref ) {
			$res .= '/' . $key . ' ' . $ref . ' 0 R ';
		}
		$res .= '>> >>';

		$kids = array();
		foreach ( $this->pages as $content ) {
			$c      = $add( $stream( $content ) );
			$kids[] = $add( sprintf( '<< /Type /Page /Parent %d 0 R /MediaBox [0 0 %.2F %.2F] /Resources %s /Contents %d 0 R >>', $pages, self::W, self::H, $res, $c ) );
		}
		$objs[ $pages - 1 ]   = '<< /Type /Pages /Kids [' . implode( ' 0 R ', $kids ) . ' 0 R] /Count ' . count( $kids ) . ' >>';
		$objs[ $catalog - 1 ] = '<< /Type /Catalog /Pages ' . $pages . ' 0 R >>';
		$t16  = mb_convert_encoding( $title, 'UTF-16BE', 'UTF-8' );
		$info = $add( '<< /Title <FEFF' . strtoupper( bin2hex( $t16 ) ) . '> /Producer (CheckDaoVan) /CreationDate (D:' . gmdate( 'YmdHis' ) . 'Z) >>' );

		$pdf  = "%PDF-1.7\n%\xE2\xE3\xCF\xD3\n";
		$offs = array();
		foreach ( $objs as $i => $body ) {
			$offs[] = strlen( $pdf );
			$pdf   .= ( $i + 1 ) . " 0 obj\n" . $body . "\nendobj\n";
		}
		$xref = strlen( $pdf );
		$pdf .= 'xref' . "\n0 " . ( count( $objs ) + 1 ) . "\n0000000000 65535 f \n";
		foreach ( $offs as $o ) {
			$pdf .= sprintf( "%010d 00000 n \n", $o );
		}
		$pdf .= 'trailer' . "\n<< /Size " . ( count( $objs ) + 1 ) . ' /Root ' . $catalog . ' 0 R /Info ' . $info . " 0 R >>\nstartxref\n" . $xref . "\n%%EOF";
		return $pdf;
	}
}

/**
 * Tạo báo cáo PDF từ phản hồi API Originality.ai (results.ai.confidence + results.ai.blocks).
 *
 * @param int   $id    Mã bài viết đơn hàng.
 * @param array $res   Phần "results" trong phản hồi API.
 * @param array $extra model, credits, file.
 * @return string Byte PDF.
 */
function vs_oai_report_pdf( $id, $res, $extra = array() ) {
	$dir = VS_DIR . '/assets/fonts/';
	$pdf = new VS_PDF( array(
		'R' => $dir . 'BeVietnamPro-Regular.ttf',
		'B' => $dir . 'BeVietnamPro-Bold.ttf',
	) );
	$code   = (string) vs_meta( $id, 'code' );
	$brand  = isset( $extra['brand'] ) ? (string) $extra['brand'] : 'Originality.ai';
	$ai     = isset( $res['ai']['confidence']['AI'] ) ? (float) $res['ai']['confidence']['AI'] * 100 : 0;
	$human  = isset( $res['ai']['confidence']['Original'] ) ? (float) $res['ai']['confidence']['Original'] * 100 : 100 - $ai;
	$blocks = isset( $res['ai']['blocks'] ) && is_array( $res['ai']['blocks'] ) ? $res['ai']['blocks'] : array();
	$fmt    = function ( $v ) {
		return rtrim( rtrim( number_format( $v, 1, ',', '.' ), '0' ), ',' ) . '%';
	};

	$M      = 48;
	$CW     = VS_PDF::W - 2 * $M;
	$BOTTOM = VS_PDF::H - 64;
	$ink    = '#1f2937';
	$muted  = '#6b7280';
	$brand  = '#4f46e5';
	$lvl_c  = $ai >= 50 ? '#dc2626' : ( $ai >= 20 ? '#d97706' : '#059669' );

	/* ---------- Trang 1: đầu trang ---------- */
	$pdf->add_page();
	$pdf->rect( 0, 0, VS_PDF::W, 92, '#1e1b4b' );
	$pdf->rect( 0, 92, VS_PDF::W, 4, $brand );
	$pdf->text( $M, 44, vs_opt( 'company', get_bloginfo( 'name' ) ), 'B', 22, '#ffffff' );
	$pdf->text( $M, 68, 'Báo cáo phát hiện nội dung AI', 'R', 12, '#c7d2fe' );
	$pdf->text( VS_PDF::W - $M, 44, $brand, 'B', 13, '#ffffff', 'R' );
	$pdf->text( VS_PDF::W - $M, 68, 'Mã đơn ' . $code, 'R', 10.5, '#c7d2fe', 'R' );

	/* ---------- Thông tin đơn ---------- */
	$text  = isset( $res['properties']['content'] ) ? (string) $res['properties']['content'] : implode( ' ', wp_list_pluck( $blocks, 'text' ) );
	$words = count( preg_split( '/\s+/u', trim( $text ), -1, PREG_SPLIT_NO_EMPTY ) );
	$file  = isset( $extra['file'] ) ? $extra['file'] : (string) vs_meta( $id, 'file_name' );
	$info  = array(
		array( 'Mã đơn hàng', $code ),
		array( 'Tệp đã kiểm tra', '' !== $file ? $file : '-' ),
		array( 'Thời gian kiểm tra', wp_date( 'H:i - d/m/Y' ) ),
		array( 'Mô hình phát hiện', $brand . ' ' . ( isset( $extra['model'] ) ? ucfirst( $extra['model'] ) : '' ) ),
		array( 'Số từ / số câu đánh giá', number_format( $words, 0, ',', '.' ) . ' từ / ' . count( $blocks ) . ' câu' ),
	);
	$y = 124;
	$pdf->rect( $M, $y, $CW, 22 * count( $info ) + 16, '#f8fafc' );
	$y += 24;
	foreach ( $info as $row ) {
		$pdf->text( $M + 16, $y, $row[0], 'R', 10, $muted );
		$val = $row[1];
		while ( mb_strlen( $val ) > 4 && $pdf->width( $val, 'B', 10 ) > $CW - 190 ) {
			$val = mb_substr( $val, 0, -4 ) . '…';
		}
		$pdf->text( $M + 170, $y, $val, 'B', 10, $ink );
		$y += 22;
	}

	/* ---------- Điểm ---------- */
	$y += 14;
	$half = ( $CW - 16 ) / 2;
	$pdf->rect( $M, $y, $half, 96, '#ffffff' );
	$pdf->rect( $M, $y, 4, 96, $lvl_c );
	$pdf->text( $M + 22, $y + 52, $fmt( $ai ), 'B', 34, $lvl_c );
	$pdf->text( $M + 22, $y + 76, 'Khả năng do AI tạo ra', 'R', 10.5, $muted );
	$x2 = $M + $half + 16;
	$pdf->rect( $x2, $y, $half, 96, '#ffffff' );
	$pdf->rect( $x2, $y, 4, 96, '#059669' );
	$pdf->text( $x2 + 22, $y + 52, $fmt( $human ), 'B', 34, '#059669' );
	$pdf->text( $x2 + 22, $y + 76, 'Khả năng do con người viết', 'R', 10.5, $muted );
	$pdf->line( $M, $y, $M + $CW, $y, '#e5e7eb' );
	$pdf->line( $M, $y + 96, $M + $CW, $y + 96, '#e5e7eb' );
	$y += 112;
	$pdf->rect( $M, $y, $CW, 8, '#d1fae5' );
	$pdf->rect( $M, $y, max( 0, min( 100, $ai ) ) / 100 * $CW, 8, $lvl_c );
	$y += 30;

	$note = 'Chỉ số trên là mức độ tin cậy (confidence) mà mô hình của ' . $brand . ' đánh giá văn bản do AI tạo ra, không phải tỷ lệ phần trăm số câu được viết bằng AI. Kết quả phát hiện AI là ước lượng xác suất và có thể sai lệch; vui lòng sử dụng như một tham khảo cùng với các bằng chứng khác.';
	foreach ( $pdf->wrap( $note, 'R', 9.5, $CW ) as $l ) {
		$pdf->text( $M, $y, $l, 'R', 9.5, $muted );
		$y += 14;
	}

	/* ---------- Chú thích màu ---------- */
	$y += 12;
	$legend = array(
		array( '#fecaca', 'Rất có thể do AI (≥ 80%)' ),
		array( '#fde68a', 'Nghi ngờ AI (50 - 80%)' ),
		array( '#ffffff', 'Có khả năng do người viết' ),
	);
	$lx = $M;
	foreach ( $legend as $lg ) {
		$pdf->rect( $lx, $y - 9, 12, 12, $lg[0] );
		$pdf->line( $lx, $y - 9, $lx + 12, $y - 9, '#d1d5db' );
		$pdf->line( $lx, $y + 3, $lx + 12, $y + 3, '#d1d5db' );
		$pdf->text( $lx + 18, $y, $lg[1], 'R', 9.5, $ink );
		$lx += 18 + $pdf->width( $lg[1], 'R', 9.5 ) + 22;
	}

	/* ---------- Nội dung tô màu theo từng câu ---------- */
	$y += 30;
	$pdf->text( $M, $y, 'Nội dung đã kiểm tra', 'B', 13, $ink );
	$y += 10;
	$pdf->line( $M, $y, $M + $CW, $y, '#e5e7eb' );
	$y += 20;

	$size  = 10.5;
	$lh    = 17;
	$space = $pdf->width( ' ', 'R', $size );
	$fills = array( 1 => '#fde68a', 2 => '#fecaca' );
	$words = array(); // [text, level] ; level -1 = xuống đoạn
	foreach ( $blocks as $b ) {
		$fake  = isset( $b['result']['fake'] ) ? (float) $b['result']['fake'] : 0;
		$level = $fake >= 0.8 ? 2 : ( $fake >= 0.5 ? 1 : 0 );
		$paras = preg_split( "/\r?\n+/", (string) ( isset( $b['text'] ) ? $b['text'] : '' ) );
		foreach ( $paras as $pi => $para ) {
			if ( $pi > 0 ) {
				$words[] = array( '', -1 );
			}
			foreach ( preg_split( '/\s+/u', trim( $para ), -1, PREG_SPLIT_NO_EMPTY ) as $w ) {
				$words[] = array( $w, $level );
			}
		}
	}
	if ( ! $words ) {
		$words[] = array( 'Không có dữ liệu chi tiết từng câu từ API.', 0 );
	}

	$flush = function ( $line, $y ) use ( $pdf, $M, $size, $space, $fills, $ink ) {
		// Gộp các từ liền nhau cùng mức để tô nền và viết một lần.
		$runs = array();
		foreach ( $line as $w ) {
			$n = count( $runs );
			if ( $n && $runs[ $n - 1 ]['lvl'] === $w['lvl'] ) {
				$runs[ $n - 1 ]['t'] .= ' ' . $w['t'];
				$runs[ $n - 1 ]['x2'] = $w['x'] + $w['w'];
			} else {
				$runs[] = array( 't' => $w['t'], 'lvl' => $w['lvl'], 'x' => $w['x'], 'x2' => $w['x'] + $w['w'] );
			}
		}
		foreach ( $runs as $r ) {
			if ( $r['lvl'] > 0 ) {
				$pdf->rect( $M + $r['x'] - 1.5, $y - $size - 1.5, $r['x2'] - $r['x'] + 3, $size + 6, $fills[ $r['lvl'] ] );
			}
		}
		foreach ( $runs as $r ) {
			$pdf->text( $M + $r['x'], $y, $r['t'], 'R', $size, $ink );
		}
	};
	$new_page = function () use ( $pdf, $M, $CW, $code ) {
		$pdf->add_page();
		$pdf->rect( 0, 0, VS_PDF::W, 4, '#4f46e5' );
		$pdf->text( $M, 34, 'Báo cáo phát hiện nội dung AI - ' . $brand, 'B', 10, '#4f46e5' );
		$pdf->text( $M + $CW, 34, 'Mã đơn ' . $code, 'R', 10, '#6b7280', 'R' );
		$pdf->line( $M, 44, $M + $CW, 44, '#e5e7eb' );
		return 70;
	};

	$line = array();
	$x    = 0;
	foreach ( $words as $w ) {
		if ( -1 === $w[1] ) {
			$flush( $line, $y );
			$line = array();
			$x    = 0;
			$y   += $lh + 6;
			if ( $y > $BOTTOM ) {
				$y = $new_page();
			}
			continue;
		}
		$ww = $pdf->width( $w[0], 'R', $size );
		if ( $line && $x + $ww > $CW ) {
			$flush( $line, $y );
			$line = array();
			$x    = 0;
			$y   += $lh;
			if ( $y > $BOTTOM ) {
				$y = $new_page();
			}
		}
		$line[] = array( 't' => $w[0], 'lvl' => $w[1], 'x' => $x, 'w' => $ww );
		$x     += $ww + $space;
	}
	$flush( $line, $y );

	/* ---------- Chân trang mọi trang ---------- */
	$total = $pdf->page_count();
	for ( $i = 0; $i < $total; $i++ ) {
		$pdf->set_page( $i );
		$fy = VS_PDF::H - 34;
		$pdf->line( $M, $fy - 14, $M + $CW, $fy - 14, '#e5e7eb' );
		$pdf->text( $M, $fy, 'Báo cáo được tạo tự động từ kết quả API ' . $brand . ' - ' . wp_parse_url( home_url(), PHP_URL_HOST ), 'R', 8.5, $muted );
		$pdf->text( $M + $CW, $fy, 'Trang ' . ( $i + 1 ) . '/' . $total, 'R', 8.5, $muted, 'R' );
	}

	return $pdf->output( 'Báo cáo AI ' . $brand . ' - ' . $code );
}
