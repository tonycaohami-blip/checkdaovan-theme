<?php
/**
 * CDV Scan: báo cáo trùng lặp (trang xem trực tuyến + file PDF).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Bảng màu nguồn: [nền tô, màu đậm]. */
function vs_cdv_colors() {
	return array(
		array( '#fde2e2', '#d93d3d' ),
		array( '#dbeafe', '#2563eb' ),
		array( '#dcfce7', '#16a34a' ),
		array( '#fef3c7', '#d97706' ),
		array( '#ede9fe', '#7c3aed' ),
		array( '#cffafe', '#0891b2' ),
		array( '#fce7f3', '#db2777' ),
		array( '#ecfccb', '#65a30d' ),
		array( '#ffedd5', '#ea580c' ),
		array( '#e0e7ff', '#4f46e5' ),
		array( '#ccfbf1', '#0d9488' ),
		array( '#f5f5f4', '#57534e' ),
	);
}

function vs_cdv_color( $n ) {
	$c = vs_cdv_colors();
	return $c[ ( max( 1, (int) $n ) - 1 ) % count( $c ) ];
}

function vs_cdv_kind_label( $k ) {
	$m = array( 'web' => 'Internet', 'upload' => 'Kho tài liệu', 'order' => 'Bài đã nộp' );
	return isset( $m[ $k ] ) ? $m[ $k ] : $k;
}

/** Mức đánh giá theo tỷ lệ: [nhãn, lớp màu]. */
function vs_cdv_level( $score ) {
	if ( $score >= 40 ) {
		return array( 'Trùng lặp cao', 'hi' );
	}
	if ( $score >= 20 ) {
		return array( 'Trùng lặp trung bình', 'md' );
	}
	return array( 'Trùng lặp thấp', 'lo' );
}

/** Chia văn bản thành các đoạn [chữ, số nguồn (0 = không trùng), loại trừ?] theo kết quả so khớp. */
function vs_cdv_pieces( $text, $res ) {
	$chars = mb_str_split( $text );
	$n     = count( $chars );
	$marks = array();
	foreach ( $res['segs'] as $sg ) {
		$marks[] = array( (int) $sg[0], (int) $sg[1], (int) $sg[2], 0 );
	}
	foreach ( (array) $res['exs'] as $e ) {
		$marks[] = array( (int) $e[0], (int) $e[1], 0, 1 );
	}
	usort( $marks, function ( $a, $b ) {
		return $a[0] - $b[0];
	} );
	$out = array();
	$pos = 0;
	foreach ( $marks as $m ) {
		$s = max( $pos, min( $n, $m[0] ) );
		$e = max( $s, min( $n, $m[1] ) );
		if ( $s > $pos ) {
			$out[] = array( implode( '', array_slice( $chars, $pos, $s - $pos ) ), 0, 0 );
		}
		if ( $e > $s ) {
			$out[] = array( implode( '', array_slice( $chars, $s, $e - $s ) ), $m[2], $m[3] );
		}
		$pos = max( $pos, $e );
	}
	if ( $pos < $n ) {
		$out[] = array( implode( '', array_slice( $chars, $pos ) ), 0, 0 );
	}
	return $out;
}

/* =========================================================
 * Trang xem báo cáo: /?vs_scan=ID&k=KHÓA
 * ======================================================= */

add_action( 'template_redirect', function () {
	if ( empty( $_GET['vs_scan'] ) ) {
		return;
	}
	$id = sanitize_key( wp_unslash( $_GET['vs_scan'] ) );
	$k  = isset( $_GET['k'] ) ? sanitize_text_field( wp_unslash( $_GET['k'] ) ) : '';
	$s  = vs_cdv_scan( $id );
	if ( ! $s || ! hash_equals( vs_cdv_key( $id ), $k ) ) {
		wp_die( 'Liên kết báo cáo không hợp lệ hoặc đã hết hạn (báo cáo được lưu 30 ngày).', 'Không xem được báo cáo', array( 'response' => 404 ) );
	}
	nocache_headers();
	header( 'X-Robots-Tag: noindex, nofollow' );
	if ( 'done' !== $s['state'] ) {
		wp_die( 'Báo cáo đang được tạo, vui lòng quay lại sau ít phút.', 'Đang kiểm tra', array( 'response' => 202 ) );
	}
	if ( ! empty( $_GET['pdf'] ) ) {
		$bytes = vs_cdv_report_pdf( $s );
		header( 'Content-Type: application/pdf' );
		header( 'Content-Disposition: attachment; filename="' . rawurlencode( 'Bao-cao-' . sanitize_file_name( vs_cdv_name() ) . '-' . ( ! empty( $s['opt']['ref'] ) ? vs_meta( $s['opt']['ref'], 'code' ) : $id ) . '.pdf' ) . '"' );
		header( 'Content-Length: ' . strlen( $bytes ) );
		echo $bytes; // phpcs:ignore
		exit;
	}
	$GLOBALS['vs_cdv_view'] = $s;
	add_filter( 'pre_get_document_title', function () {
		return 'Báo cáo trùng lặp ' . vs_cdv_name() . ' - ' . get_bloginfo( 'name' );
	}, 99 );
	add_filter( 'wp_robots', function () {
		return array( 'noindex' => true, 'nofollow' => true );
	}, 99 );
	status_header( 200 );
	get_header();
	vs_cdv_render_report( $s );
	get_footer();
	exit;
} );

function vs_cdv_render_report( $s ) {
	$r     = $s['result'];
	$lvl   = vs_cdv_level( $r['score'] );
	$code  = ! empty( $s['opt']['ref'] ) ? (string) vs_meta( $s['opt']['ref'], 'code' ) : '';
	$name  = vs_cdv_name();
	$cols  = vs_cdv_colors();
	$kinds = array( 'web' => 0, 'upload' => 0, 'order' => 0 );
	foreach ( $r['sources'] as $src ) {
		$kinds[ $src['kind'] ] += $src['pct'];
	}
	$fmt = function ( $v ) {
		return str_replace( '.', ',', (string) round( $v, 1 ) ) . '%';
	};
	?>
	<style>
		<?php foreach ( $cols as $i => $c ) : ?>
		.rpt [data-n="<?php echo (int) ( $i + 1 ); ?>"]{--sb:<?php echo esc_html( $c[0] ); ?>;--sc:<?php echo esc_html( $c[1] ); ?>}
		<?php endforeach; ?>
	</style>
	<main class="rpt" id="main">
		<div class="container">
			<header class="rpt__head">
				<div class="rpt__brand"><span class="rpt__logo"><?php echo vs_icon( 'search', 20 ); // phpcs:ignore ?></span><div><b><?php echo esc_html( $name ); ?></b><small>Báo cáo kiểm tra trùng lặp</small></div></div>
				<h1><?php echo esc_html( $s['opt']['title'] ); ?></h1>
				<ul class="rpt__meta">
					<?php if ( $code ) : ?><li>Mã đơn <b><?php echo esc_html( $code ); ?></b></li><?php endif; ?>
					<li>Kiểm tra lúc <b><?php echo esc_html( wp_date( 'H:i d/m/Y', isset( $s['finished'] ) ? $s['finished'] : $s['created'] ) ); ?></b></li>
					<li><b><?php echo esc_html( number_format( $r['words'], 0, ',', '.' ) ); ?></b> từ</li>
					<li>Đoạn trùng tối thiểu <b><?php echo (int) $s['opt']['minw']; ?> từ</b></li>
				</ul>
				<div class="rpt__acts">
					<a class="btn btn--primary btn--sm" href="<?php echo esc_url( vs_cdv_view_url( $s['id'], true ) ); ?>"><?php echo vs_icon( 'file', 16 ); // phpcs:ignore ?>Tải báo cáo PDF</a>
					<button type="button" class="btn btn--outline btn--sm" onclick="window.print()"><?php echo vs_icon( 'book', 16 ); // phpcs:ignore ?>In báo cáo</button>
				</div>
			</header>

			<div class="rpt__grid">
				<aside class="rpt__side" aria-label="Tổng quan và danh sách nguồn">
					<div class="rpt-score rpt-score--<?php echo esc_attr( $lvl[1] ); ?>">
						<div class="rpt-score__ring" style="--p:<?php echo (int) $r['score']; ?>"><b><?php echo (int) $r['score']; ?><small>%</small></b></div>
						<div class="rpt-score__t"><b>Tỷ lệ trùng lặp</b><span class="rpt-lvl"><?php echo esc_html( $lvl[0] ); ?></span><small><?php echo esc_html( number_format( $r['matched'], 0, ',', '.' ) ); ?> / <?php echo esc_html( number_format( $r['words'], 0, ',', '.' ) ); ?> từ trùng với <?php echo count( $r['sources'] ); ?> nguồn</small></div>
					</div>
					<ul class="rpt-kinds">
						<?php foreach ( $kinds as $k => $v ) : ?>
							<li><span><?php echo esc_html( vs_cdv_kind_label( $k ) ); ?></span><b><?php echo esc_html( $fmt( $v ) ); ?></b></li>
						<?php endforeach; ?>
					</ul>
					<div class="rpt-src__hd"><b>Nguồn trùng lặp</b><button type="button" class="rpt-all" data-rpt-all hidden>Hiện tất cả</button></div>
					<?php if ( ! $r['sources'] ) : ?>
						<p class="rpt-none">Không tìm thấy đoạn trùng nào từ <?php echo (int) $s['opt']['minw']; ?> từ liên tiếp trở lên.</p>
					<?php else : ?>
						<ol class="rpt-src">
							<?php foreach ( $r['sources'] as $src ) : ?>
								<li class="rpt-src__i" data-n="<?php echo (int) $src['n']; ?>">
									<button type="button" class="rpt-src__b" data-rpt-src="<?php echo (int) $src['n']; ?>" aria-pressed="false">
										<span class="rpt-src__n"><?php echo (int) $src['n']; ?></span>
										<span class="rpt-src__t"><b><?php echo esc_html( $src['title'] ); ?></b><small><?php echo esc_html( vs_cdv_kind_label( $src['kind'] ) . ( $src['host'] ? ' | ' . $src['host'] : '' ) ); ?></small></span>
										<span class="rpt-src__p"><?php echo esc_html( $fmt( $src['pct'] ) ); ?></span>
									</button>
									<div class="rpt-src__more">
										<p>“<?php echo esc_html( $src['snip'] ); ?>”</p>
										<?php if ( $src['url'] ) : ?><a href="<?php echo esc_url( $src['url'] ); ?>" target="_blank" rel="nofollow noopener noreferrer">Mở nguồn <?php echo vs_icon( 'arrow', 14 ); // phpcs:ignore ?></a><?php endif; ?>
									</div>
								</li>
							<?php endforeach; ?>
						</ol>
					<?php endif; ?>
					<div class="rpt-note">
						<b>Về kết quả này</b>
						<p><?php echo esc_html( $name ); ?> so khớp với các trang web tìm được trên Internet và kho tài liệu của <?php echo esc_html( get_bloginfo( 'name' ) ); ?>. Kết quả có thể khác Turnitin, DoIT hay phần mềm trường bạn dùng, vì mỗi phần mềm có cơ sở dữ liệu riêng. Báo cáo không đánh giá nội dung do AI tạo.</p>
						<?php if ( $r['excluded'] ) : ?><p>Đã loại trừ <?php echo esc_html( number_format( $r['excluded'], 0, ',', '.' ) ); ?> từ (<?php echo esc_html( implode( ', ', array_filter( array( $s['opt']['bib'] ? 'tài liệu tham khảo' : '', $s['opt']['quote'] ? 'trích dẫn trong ngoặc kép' : '' ) ) ) ); ?>).</p><?php endif; ?>
					</div>
				</aside>

				<article class="rpt__doc" aria-label="Nội dung đã kiểm tra">
					<div class="rpt__doc-hd"><b>Nội dung đã kiểm tra</b><span class="rpt-legend"><i class="rpt-legend__m"></i>Trùng với nguồn (số ở đầu đoạn)<i class="rpt-legend__x"></i>Đã loại trừ</span></div>
					<div class="rpt__text">
						<?php
						$seen = array();
						foreach ( vs_cdv_pieces( $s['text'], $r ) as $pc ) {
							$h = nl2br( esc_html( $pc[0] ) );
							if ( $pc[1] ) {
								$first = ! isset( $seen[ $pc[1] ] );
								$seen[ $pc[1] ] = 1;
								echo '<mark class="rpt-m" data-n="' . (int) $pc[1] . '"' . ( $first ? ' id="rpt-s' . (int) $pc[1] . '"' : '' ) . '><sup>' . (int) $pc[1] . '</sup>' . $h . '</mark>'; // phpcs:ignore
							} elseif ( $pc[2] ) {
								echo '<span class="rpt-x" title="Đã loại trừ khỏi tỷ lệ">' . $h . '</span>'; // phpcs:ignore
							} else {
								echo $h; // phpcs:ignore
							}
						}
						?>
					</div>
				</article>
			</div>
		</div>
	</main>
	<script>
	(function () {
		var side = document.querySelector('.rpt__side'), text = document.querySelector('.rpt__text'), all = document.querySelector('[data-rpt-all]');
		if (!side || !text) return;
		function focus(n) {
			text.classList.toggle('is-focus', !!n);
			text.querySelectorAll('.rpt-m').forEach(function (m) { m.classList.toggle('is-on', m.getAttribute('data-n') === n); });
			side.querySelectorAll('[data-rpt-src]').forEach(function (b) { var on = b.getAttribute('data-rpt-src') === n; b.setAttribute('aria-pressed', on ? 'true' : 'false'); b.parentNode.classList.toggle('is-open', on); });
			all.hidden = !n;
			if (n) { var f = document.getElementById('rpt-s' + n); if (f) f.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
		}
		side.addEventListener('click', function (e) {
			var b = e.target.closest('[data-rpt-src]'); if (!b) return;
			focus(b.getAttribute('aria-pressed') === 'true' ? '' : b.getAttribute('data-rpt-src'));
		});
		all.addEventListener('click', function () { focus(''); });
		text.addEventListener('click', function (e) { var m = e.target.closest('.rpt-m'); if (m) focus(m.getAttribute('data-n')); });
	})();
	</script>
	<?php
}

/* =========================================================
 * Báo cáo PDF
 * ======================================================= */

function vs_cdv_report_pdf( $s ) {
	$dir = VS_DIR . '/assets/fonts/';
	$pdf = new VS_PDF( array(
		'R' => $dir . 'BeVietnamPro-Regular.ttf',
		'B' => $dir . 'BeVietnamPro-Bold.ttf',
	) );
	$r     = $s['result'];
	$name  = vs_cdv_name();
	$code  = ! empty( $s['opt']['ref'] ) ? (string) vs_meta( $s['opt']['ref'], 'code' ) : '';
	$M     = 48;
	$CW    = VS_PDF::W - 2 * $M;
	$BOT   = VS_PDF::H - 64;
	$ink   = '#1f2937';
	$muted = '#6b7280';
	$lvl   = vs_cdv_level( $r['score'] );
	$lc    = array( 'hi' => '#dc2626', 'md' => '#d97706', 'lo' => '#059669' );
	$lc    = $lc[ $lvl[1] ];
	$fmt   = function ( $v ) {
		return str_replace( '.', ',', (string) round( $v, 1 ) ) . '%';
	};
	$fit   = function ( $t, $font, $size, $w ) use ( $pdf ) {
		while ( mb_strlen( $t ) > 4 && $pdf->width( $t, $font, $size ) > $w ) {
			$t = mb_substr( $t, 0, -4 ) . '…';
		}
		return $t;
	};

	/* ---------- Trang 1: tổng quan ---------- */
	$pdf->add_page();
	$pdf->rect( 0, 0, VS_PDF::W, 92, '#0b1630' );
	$pdf->rect( 0, 92, VS_PDF::W, 4, '#3d5cff' );
	$pdf->text( $M, 44, vs_opt( 'company', get_bloginfo( 'name' ) ), 'B', 22, '#ffffff' );
	$pdf->text( $M, 68, 'Báo cáo kiểm tra trùng lặp', 'R', 12, '#c7d2fe' );
	$pdf->text( VS_PDF::W - $M, 44, $name, 'B', 13, '#ffffff', 'R' );
	$pdf->text( VS_PDF::W - $M, 68, $code ? 'Mã đơn ' . $code : 'Mã báo cáo ' . strtoupper( substr( $s['id'], 0, 8 ) ), 'R', 10.5, '#c7d2fe', 'R' );

	$info = array(
		array( 'Tài liệu', (string) $s['opt']['title'] ),
		array( 'Thời gian kiểm tra', wp_date( 'H:i - d/m/Y', isset( $s['finished'] ) ? $s['finished'] : $s['created'] ) ),
		array( 'Số từ', number_format( $r['words'], 0, ',', '.' ) . ' từ (' . number_format( $r['matched'], 0, ',', '.' ) . ' từ trùng)' ),
		array( 'Phạm vi so khớp', ( $s['opt']['web'] ? 'Internet + ' : '' ) . 'kho tài liệu nội bộ' ),
		array( 'Thiết lập', 'Đoạn trùng từ ' . (int) $s['opt']['minw'] . ' từ' . ( $s['opt']['bib'] ? ', loại trừ tài liệu tham khảo' : '' ) . ( $s['opt']['quote'] ? ', loại trừ trích dẫn' : '' ) ),
	);
	$y = 124;
	$pdf->rect( $M, $y, $CW, 22 * count( $info ) + 16, '#f8fafc' );
	$y += 24;
	foreach ( $info as $row ) {
		$pdf->text( $M + 16, $y, $row[0], 'R', 10, $muted );
		$pdf->text( $M + 150, $y, $fit( $row[1], 'B', 10, $CW - 170 ), 'B', 10, $ink );
		$y += 22;
	}

	$y += 16;
	$pdf->rect( $M, $y, $CW, 96, '#ffffff' );
	$pdf->rect( $M, $y, 4, 96, $lc );
	$pdf->line( $M, $y, $M + $CW, $y, '#e5e7eb' );
	$pdf->line( $M, $y + 96, $M + $CW, $y + 96, '#e5e7eb' );
	$pdf->text( $M + 22, $y + 56, $r['score'] . '%', 'B', 40, $lc );
	$pdf->text( $M + 22, $y + 80, 'Tỷ lệ trùng lặp | ' . $lvl[0], 'R', 10.5, $muted );
	$kx    = $M + 230;
	$kinds = array( 'web' => 0, 'upload' => 0, 'order' => 0 );
	foreach ( $r['sources'] as $src ) {
		$kinds[ $src['kind'] ] += $src['pct'];
	}
	$ky = $y + 30;
	foreach ( $kinds as $k => $v ) {
		$pdf->text( $kx, $ky, vs_cdv_kind_label( $k ), 'R', 10.5, $muted );
		$pdf->text( $M + $CW - 18, $ky, $fmt( $v ), 'B', 10.5, $ink, 'R' );
		$ky += 22;
	}
	$y += 112;
	$pdf->rect( $M, $y, $CW, 8, '#e5e7eb' );
	$pdf->rect( $M, $y, max( 0, min( 100, $r['score'] ) ) / 100 * $CW, 8, $lc );
	$y += 34;

	/* ---------- Danh sách nguồn ---------- */
	$new_page = function () use ( $pdf, $M, $CW, $name, $code ) {
		$pdf->add_page();
		$pdf->rect( 0, 0, VS_PDF::W, 4, '#3d5cff' );
		$pdf->text( $M, 34, 'Báo cáo trùng lặp - ' . $name, 'B', 10, '#3d5cff' );
		$pdf->text( $M + $CW, 34, $code ? 'Mã đơn ' . $code : '', 'R', 10, '#6b7280', 'R' );
		$pdf->line( $M, 44, $M + $CW, 44, '#e5e7eb' );
		return 70;
	};
	$pdf->text( $M, $y, 'Nguồn trùng lặp (' . count( $r['sources'] ) . ')', 'B', 13, $ink );
	$y += 12;
	$pdf->line( $M, $y, $M + $CW, $y, '#e5e7eb' );
	$y += 22;
	if ( ! $r['sources'] ) {
		$pdf->text( $M, $y, 'Không tìm thấy đoạn trùng nào từ ' . (int) $s['opt']['minw'] . ' từ liên tiếp trở lên.', 'R', 10.5, $muted );
		$y += 22;
	}
	foreach ( $r['sources'] as $src ) {
		if ( $y > $BOT - 30 ) {
			$y = $new_page();
		}
		$c = vs_cdv_color( $src['n'] );
		$pdf->rect( $M, $y - 13, 22, 18, $c[1] );
		$pdf->text( $M + 11, $y, (string) $src['n'], 'B', 9.5, '#ffffff', 'C' );
		$pdf->text( $M + 32, $y, $fit( $src['title'], 'B', 10.5, $CW - 110 ), 'B', 10.5, $ink );
		$pdf->text( $M + $CW, $y, $fmt( $src['pct'] ), 'B', 11, $c[1], 'R' );
		$sub = vs_cdv_kind_label( $src['kind'] ) . ( $src['url'] ? ' | ' . $src['url'] : '' );
		$pdf->text( $M + 32, $y + 15, $fit( $sub, 'R', 9, $CW - 110 ), 'R', 9, $muted );
		$y += 36;
	}

	/* ---------- Nội dung tô màu ---------- */
	$y = $y > $BOT - 120 ? $new_page() : $y + 14;
	$pdf->text( $M, $y, 'Nội dung đã kiểm tra', 'B', 13, $ink );
	$y += 10;
	$pdf->line( $M, $y, $M + $CW, $y, '#e5e7eb' );
	$y += 22;

	$size  = 10.5;
	$lh    = 17;
	$space = $pdf->width( ' ', 'R', $size );
	$words = array(); // [chữ, số nguồn, loại trừ, đầu đoạn trùng] ; null = xuống dòng
	foreach ( vs_cdv_pieces( $s['text'], $r ) as $pc ) {
		$paras = preg_split( "/\n/u", $pc[0] );
		$first = true;
		// Đoạn dính liền chữ trước (VD dấu chấm ngay sau đoạn tô màu): không chèn khoảng trắng.
		$glue = $words && null !== end( $words ) && ! preg_match( '/^\s/u', $pc[0] );
		foreach ( $paras as $pi => $para ) {
			if ( $pi > 0 ) {
				$words[] = null;
				$glue    = false;
			}
			foreach ( preg_split( '/\s+/u', $para, -1, PREG_SPLIT_NO_EMPTY ) as $wi => $w ) {
				$words[] = array( $w, (int) $pc[1], (int) $pc[2], $pc[1] && $first, $glue && 0 === $wi );
				$first   = false;
			}
			$glue = false;
		}
	}
	$flush = function ( $line, $y ) use ( $pdf, $M, $size, $ink, $muted ) {
		$runs = array();
		foreach ( $line as $w ) {
			$k = count( $runs ) - 1;
			if ( $k >= 0 && $runs[ $k ]['n'] === $w['n'] && $runs[ $k ]['x'] === $w['ex'] && ! $w['lead'] ) {
				$runs[ $k ]['t'] .= ' ' . $w['t'];
				$runs[ $k ]['x2'] = $w['x'] + $w['w'];
			} else {
				$runs[] = array( 't' => $w['t'], 'n' => $w['n'], 'x' => $w['ex'], 'x1' => $w['x'], 'x2' => $w['x'] + $w['w'], 'lead' => $w['lead'] );
			}
		}
		foreach ( $runs as $rn ) {
			if ( $rn['n'] ) {
				$c = vs_cdv_color( $rn['n'] );
				$pdf->rect( $M + $rn['x1'] - 1.5, $y - $size - 1.5, $rn['x2'] - $rn['x1'] + 3, $size + 6, $c[0] );
			}
		}
		foreach ( $runs as $rn ) {
			$pdf->text( $M + $rn['x1'], $y, $rn['t'], 'R', $size, $rn['x'] ? $muted : $ink );
			if ( $rn['lead'] && $rn['n'] ) {
				$c = vs_cdv_color( $rn['n'] );
				$pdf->text( $M + $rn['x1'] - 2, $y - $size + 1, (string) $rn['n'], 'B', 6.5, $c[1], 'R' );
			}
		}
	};
	$line = array();
	$x    = 0;
	foreach ( $words as $w ) {
		if ( null === $w ) {
			$flush( $line, $y );
			$line = array();
			$x    = 0;
			$y   += $lh + 5;
			if ( $y > $BOT ) {
				$y = $new_page();
			}
			continue;
		}
		$ww = $pdf->width( $w[0], 'R', $size );
		if ( $w[4] && $line ) {
			$x -= $space;
		}
		if ( $line && $x + $ww > $CW ) {
			$flush( $line, $y );
			$line = array();
			$x    = 0;
			$y   += $lh;
			if ( $y > $BOT ) {
				$y = $new_page();
			}
		}
		$line[] = array( 't' => $w[0], 'n' => $w[1], 'ex' => $w[2], 'lead' => $w[3], 'x' => $x, 'w' => $ww );
		$x     += $ww + $space;
	}
	$flush( $line, $y );

	/* ---------- Chân trang ---------- */
	$total = $pdf->page_count();
	$foot  = $name . ' so khớp với Internet và kho tài liệu nội bộ; kết quả có thể khác phần mềm khác. ' . wp_parse_url( home_url(), PHP_URL_HOST );
	for ( $i = 0; $i < $total; $i++ ) {
		$pdf->set_page( $i );
		$fy = VS_PDF::H - 34;
		$pdf->line( $M, $fy - 14, $M + $CW, $fy - 14, '#e5e7eb' );
		$pdf->text( $M, $fy, $fit( $foot, 'R', 8.5, $CW - 70 ), 'R', 8.5, $muted );
		$pdf->text( $M + $CW, $fy, 'Trang ' . ( $i + 1 ) . '/' . $total, 'R', 8.5, $muted, 'R' );
	}
	return $pdf->output( 'Báo cáo trùng lặp ' . $name . ( $code ? ' - ' . $code : '' ) );
}
