<?php
/**
 * Chức năng mở rộng: mã giảm giá, Telegram, thống kê, xuất CSV, SEO.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* =========================================================
 * Mã giảm giá
 * Mỗi dòng trong cài đặt: MÃ|phần trăm|hạn dùng dd/mm/yyyy (tùy chọn)|check hoặc reduce (tùy chọn)
 * ======================================================= */

function vs_coupons() {
	$out = array();
	foreach ( preg_split( '/\r?\n/', (string) vs_opt( 'coupons' ) ) as $line ) {
		$parts = array_map( 'trim', explode( '|', $line ) );
		if ( count( $parts ) < 2 || '' === $parts[0] ) {
			continue;
		}
		$out[ strtoupper( $parts[0] ) ] = array(
			'pct'     => max( 0, min( 90, (float) $parts[1] ) ),
			'expires' => isset( $parts[2] ) ? $parts[2] : '',
			'scope'   => isset( $parts[3] ) && in_array( $parts[3], array( 'check', 'reduce' ), true ) ? $parts[3] : 'all',
		);
	}
	return $out;
}

/**
 * @return float|WP_Error Phần trăm giảm.
 */
function vs_coupon_pct( $code, $type ) {
	$code = strtoupper( trim( (string) $code ) );
	if ( '' === $code ) {
		return 0;
	}
	$all = vs_coupons();
	if ( ! isset( $all[ $code ] ) ) {
		return new WP_Error( 'coupon', 'Mã giảm giá không tồn tại.' );
	}
	$c = $all[ $code ];
	if ( 'all' !== $c['scope'] && $c['scope'] !== $type ) {
		return new WP_Error( 'coupon', 'Mã này chỉ áp dụng cho dịch vụ ' . ( 'check' === $c['scope'] ? 'kiểm tra' : 'giảm tỷ lệ' ) . '.' );
	}
	if ( $c['expires'] ) {
		$d = DateTime::createFromFormat( 'd/m/Y H:i:s', $c['expires'] . ' 23:59:59', wp_timezone() );
		if ( $d && $d->getTimestamp() < time() ) {
			return new WP_Error( 'coupon', 'Mã giảm giá đã hết hạn.' );
		}
	}
	return $c['pct'];
}

add_action( 'wp_ajax_vs_coupon', 'vs_ajax_coupon' );
add_action( 'wp_ajax_nopriv_vs_coupon', 'vs_ajax_coupon' );
function vs_ajax_coupon() {
	vs_rate_limit( 'coupon', 30 );
	$pct = vs_coupon_pct( wp_unslash( isset( $_POST['code'] ) ? $_POST['code'] : '' ), sanitize_key( isset( $_POST['type'] ) ? $_POST['type'] : 'check' ) );
	if ( is_wp_error( $pct ) ) {
		wp_send_json_error( array( 'message' => $pct->get_error_message() ) );
	}
	wp_send_json_success( array( 'pct' => $pct ) );
}

/* =========================================================
 * Thông báo đơn mới qua Telegram
 * ======================================================= */

function vs_telegram( $text ) {
	do_action( 'vs_telegram', $text ); // Thông báo trong trang quản trị (inc/notify.php).
	$token = vs_opt( 'tg_token' );
	$chat  = vs_opt( 'tg_chat' );
	if ( ! $token || ! $chat ) {
		return;
	}
	wp_remote_post( 'https://api.telegram.org/bot' . rawurlencode( $token ) . '/sendMessage', array(
		'timeout'  => 5,
		'blocking' => false,
		'body'     => array( 'chat_id' => $chat, 'text' => $text, 'disable_web_page_preview' => 1 ),
	) );
}

add_action( 'vs_order_created', function ( $id ) {
	vs_telegram(
		"🆕 ĐƠN MỚI " . vs_meta( $id, 'code' ) . "\n" .
		vs_order_summary( $id ) . "\n" .
		'💰 ' . vs_money( vs_meta( $id, 'price' ) ) . "\n" .
		'👤 ' . vs_meta( $id, 'name' ) . ' - ' . vs_meta( $id, 'phone' ) . "\n" .
		admin_url( 'post.php?post=' . $id . '&action=edit' )
	);
} );

/* =========================================================
 * Bảng thống kê trên Dashboard
 * ======================================================= */

function vs_order_stats() {
	$ids   = get_posts( array( 'post_type' => 'vs_order', 'posts_per_page' => -1, 'fields' => 'ids', 'post_status' => 'publish' ) );
	$today = wp_date( 'Y-m-d' );
	$month = wp_date( 'Y-m' );
	$s     = array( 'today' => 0, 'month' => 0, 'total' => 0, 'by' => array_fill_keys( array_keys( vs_statuses() ), 0 ) );
	foreach ( $ids as $id ) {
		$st = vs_meta( $id, 'status', 'cho_thanh_toan' );
		if ( isset( $s['by'][ $st ] ) ) {
			$s['by'][ $st ]++;
		}
		if ( in_array( $st, array( 'dang_xu_ly', 'hoan_thanh' ), true ) ) {
			$price = (int) vs_meta( $id, 'price' );
			$date  = vs_post_date( 'Y-m-d', $id );
			$s['total'] += $price;
			if ( 0 === strpos( $date, $month ) ) {
				$s['month'] += $price;
			}
			if ( $date === $today ) {
				$s['today'] += $price;
			}
		}
	}
	return $s;
}

add_action( 'wp_dashboard_setup', function () {
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}
	wp_add_dashboard_widget( 'vs_stats', 'Doanh thu & đơn hàng', function () {
		$s      = vs_order_stats();
		$labels = vs_statuses();
		?>
		<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:14px">
			<?php foreach ( array( 'today' => 'Hôm nay', 'month' => 'Tháng này', 'total' => 'Tổng cộng' ) as $k => $l ) : ?>
				<div style="background:#f4f7fc;border-radius:8px;padding:10px">
					<div style="color:#666;font-size:12px"><?php echo esc_html( $l ); ?></div>
					<div style="font-size:17px;font-weight:700"><?php echo esc_html( vs_money( $s[ $k ] ) ); ?></div>
				</div>
			<?php endforeach; ?>
		</div>
		<p style="margin:0 0 10px">
			<?php foreach ( $s['by'] as $k => $n ) : ?>
				<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=vs_order&vs_status=' . $k ) ); ?>" class="vs-badge vs-badge--<?php echo esc_attr( $k ); ?>" style="margin:0 4px 4px 0;text-decoration:none"><?php echo esc_html( $labels[ $k ] . ': ' . $n ); ?></a>
			<?php endforeach; ?>
		</p>
		<p style="color:#888;font-size:12px;margin:0">Doanh thu tính các đơn “Đang xử lý” và “Hoàn thành”.</p>
		<style>.vs-badge{display:inline-block;padding:3px 10px;border-radius:99px;font-size:12px;font-weight:600;background:#eef2ff;color:#3730a3}.vs-badge--cho_thanh_toan{background:#fff7ed;color:#c2410c}.vs-badge--dang_xu_ly{background:#eff6ff;color:#1d4ed8}.vs-badge--hoan_thanh{background:#ecfdf5;color:#047857}.vs-badge--huy{background:#f3f4f6;color:#6b7280}</style>
		<?php
	} );
} );

/* =========================================================
 * Xuất đơn hàng ra CSV (mở được bằng Excel)
 * ======================================================= */

add_action( 'manage_posts_extra_tablenav', function ( $which ) {
	if ( 'top' === $which && 'vs_order' === get_current_screen()->post_type ) {
		printf( '<a class="button" href="%s">⬇ Xuất Excel (CSV)</a>', esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=vs_export' ), 'vs_export' ) ) );
	}
} );

add_action( 'admin_post_vs_export', function () {
	if ( ! current_user_can( 'edit_posts' ) || ! wp_verify_nonce( isset( $_GET['_wpnonce'] ) ? $_GET['_wpnonce'] : '', 'vs_export' ) ) {
		wp_die( 'Không có quyền.' );
	}
	$labels = vs_statuses();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="don-hang-' . wp_date( 'Ymd-His' ) . '.csv"' );
	$out = fopen( 'php://output', 'w' );
	fwrite( $out, "\xEF\xBB\xBF" ); // BOM để Excel đọc đúng tiếng Việt.
	fputcsv( $out, array( 'Mã đơn', 'Ngày đặt', 'Dịch vụ', 'Khách hàng', 'Điện thoại', 'Email', 'Mã giảm giá', 'Thành tiền', 'Trạng thái' ) );
	foreach ( get_posts( array( 'post_type' => 'vs_order', 'posts_per_page' => -1, 'fields' => 'ids' ) ) as $id ) {
		$st = vs_meta( $id, 'status', 'cho_thanh_toan' );
		fputcsv( $out, array(
			vs_meta( $id, 'code' ),
			vs_post_date( 'd/m/Y H:i', $id ),
			vs_order_summary( $id ),
			vs_meta( $id, 'name' ),
			"'" . vs_meta( $id, 'phone' ),
			vs_meta( $id, 'email' ),
			vs_meta( $id, 'coupon' ),
			(int) vs_meta( $id, 'price' ),
			isset( $labels[ $st ] ) ? $labels[ $st ] : $st,
		) );
	}
	fclose( $out );
	exit;
} );

/* =========================================================
 * SEO: favicon, meta, Open Graph, dữ liệu có cấu trúc
 * (tự tắt khi dùng Yoast SEO / Rank Math)
 * ======================================================= */

function vs_has_seo_plugin() {
	return defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' ) || defined( 'AIOSEO_VERSION' );
}

add_action( 'wp_head', function () {
	echo '<script>document.documentElement.classList.add("js")</script>' . "\n";
	if ( ! has_site_icon() ) {
		echo '<link rel="icon" type="image/svg+xml" href="' . esc_url( VS_URI . '/assets/img/favicon.svg' ) . '">' . "\n";
	}
	echo '<meta name="theme-color" content="#0a1a38">' . "\n";
	if ( vs_has_seo_plugin() ) {
		return;
	}

	$desc  = vs_opt( 'meta_desc', 'Kiểm tra tỷ lệ đạo văn và tỷ lệ AI bằng Turnitin, iThenticate, DoIT, Copyleaks, Originality.ai, GPTZero, Winston AI, Grammarly, QuillBot, Undetectable.AI. Không lưu bài, kết quả từ 30 phút. Dịch vụ giảm tỷ lệ đạo văn dưới 30%, dưới 20%.' );
	$title = wp_get_document_title();
	$url   = home_url( add_query_arg( array() ) );
	$type  = 'website';
	$image = '';
	if ( is_singular( 'post' ) ) {
		$post  = get_queried_object();
		$desc  = wp_trim_words( wp_strip_all_tags( $post->post_excerpt ? $post->post_excerpt : $post->post_content ), 30, '…' );
		$url   = get_permalink( $post );
		$type  = 'article';
		$image = get_the_post_thumbnail_url( $post, 'large' );
	}
	printf( '<meta name="description" content="%s">' . "\n", esc_attr( $desc ) );
	printf( '<meta property="og:type" content="%s"><meta property="og:title" content="%s"><meta property="og:description" content="%s"><meta property="og:url" content="%s"><meta property="og:locale" content="vi_VN"><meta property="og:site_name" content="%s">' . "\n", esc_attr( $type ), esc_attr( $title ), esc_attr( $desc ), esc_url( $url ), esc_attr( get_bloginfo( 'name' ) ) );
	if ( $image ) {
		printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $image ) );
	}

	$graph = array();
	if ( is_front_page() ) {
		$graph[] = array_filter( array(
			'@type'     => 'ProfessionalService',
			'name'      => vs_opt( 'company', get_bloginfo( 'name' ) ),
			'url'       => home_url( '/' ),
			'logo'      => VS_URI . '/assets/img/logo.svg',
			'telephone' => vs_opt( 'hotline' ),
			'email'     => vs_opt( 'email' ),
			'address'   => vs_opt( 'address' ),
			'priceRange' => '₫₫',
			'sameAs'    => array_values( array_filter( array( vs_opt( 'facebook' ), vs_opt( 'tiktok' ), vs_opt( 'youtube' ) ) ) ),
		) );
		$faq = array();
		foreach ( vs_faqs() as $q => $a ) {
			$faq[] = array( '@type' => 'Question', 'name' => $q, 'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $a ) );
		}
		$graph[] = array( '@type' => 'FAQPage', 'mainEntity' => $faq );
	} elseif ( is_singular( 'post' ) ) {
		$post    = get_queried_object();
		$graph[] = array(
			'@type'         => 'Article',
			'headline'      => get_the_title( $post ),
			'datePublished' => vs_post_date( 'c', $post ),
			'dateModified'  => get_the_modified_date( 'c', $post ),
			'author'        => array( '@type' => 'Organization', 'name' => get_bloginfo( 'name' ) ),
			'publisher'     => array( '@type' => 'Organization', 'name' => get_bloginfo( 'name' ), 'logo' => array( '@type' => 'ImageObject', 'url' => VS_URI . '/assets/img/logo.svg' ) ),
			'mainEntityOfPage' => get_permalink( $post ),
		);
		// Mục "Câu hỏi thường gặp" trong bài (H2 + các cặp H3/đoạn văn) -> FAQPage.
		if ( preg_match( '#<h2>Câu hỏi thường gặp</h2>(.*?)(?:<h2|<blockquote|$)#su', $post->post_content, $m )
			&& preg_match_all( '#<h3>(.*?)</h3>\s*<p>(.*?)</p>#su', $m[1], $qa, PREG_SET_ORDER ) ) {
			$graph[] = array(
				'@type'      => 'FAQPage',
				'mainEntity' => array_map( function ( $x ) {
					return array( '@type' => 'Question', 'name' => wp_strip_all_tags( $x[1] ), 'acceptedAnswer' => array( '@type' => 'Answer', 'text' => wp_strip_all_tags( $x[2] ) ) );
				}, $qa ),
			);
		}
	}
	if ( $graph ) {
		echo '<script type="application/ld+json">' . wp_json_encode( array( '@context' => 'https://schema.org', '@graph' => $graph ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
	}
}, 1 );

/* =========================================================
 * Tiện ích cho blog
 * ======================================================= */

function vs_reading_time( $post = null ) {
	$words = count( preg_split( '/\s+/u', trim( wp_strip_all_tags( get_post_field( 'post_content', $post ) ) ) ) );
	return max( 1, (int) round( $words / 220 ) );
}

/** Ảnh bìa tự sinh (gradient + biểu tượng) khi bài chưa có ảnh đại diện. */
function vs_post_cover( $post = null, $size = 'large' ) {
	$post = get_post( $post );
	if ( has_post_thumbnail( $post ) ) {
		return '<div class="cover">' . get_the_post_thumbnail( $post, $size, array( 'loading' => 'lazy' ) ) . '</div>';
	}
	$palettes = array(
		array( '#2350e0', '#5a32f0' ),
		array( '#0f766e', '#10b981' ),
		array( '#c2410c', '#ff6a3d' ),
		array( '#1e3a8a', '#0ea5e9' ),
		array( '#7c3aed', '#db2777' ),
	);
	$p     = $palettes[ $post->ID % count( $palettes ) ];
	$cat   = get_the_category( $post->ID );
	$label = $cat ? $cat[0]->name : 'Kiến thức';
	return sprintf(
		'<div class="cover cover--gen" style="--c1:%s;--c2:%s"><span class="cover__tag">%s</span><span class="cover__title">%s</span><svg class="cover__icon" viewBox="0 0 44 44" aria-hidden="true"><path d="M14 8.5h10.5l7.5 7.5v17.5a2 2 0 0 1-2 2H14a2 2 0 0 1-2-2v-23a2 2 0 0 1 2-2z" fill="rgba(255,255,255,.18)"/><circle cx="31.5" cy="31.5" r="8" fill="rgba(255,255,255,.28)"/><path d="M27.8 31.6l2.5 2.5 4.8-5" fill="none" stroke="#fff" stroke-width="2.4" stroke-linecap="round"/></svg></div>',
		esc_attr( $p[0] ),
		esc_attr( $p[1] ),
		esc_html( $label ),
		esc_html( get_the_title( $post ) )
	);
}

/** Câu hỏi thường gặp (dùng cho trang chủ và dữ liệu có cấu trúc). */
function vs_faqs() {
	$r = vs_pricing()['reduce'];
	return array(
		'Bài của tôi có bị lưu vào kho dữ liệu Turnitin không?' => 'Không. Chúng tôi kiểm tra bằng tài khoản ở chế độ No Repository, bài không được lưu, nên khi bạn nộp chính thức ở trường sẽ không bị trùng với chính bài của mình.',
		'Tỷ lệ AI được tính như thế nào?' => 'Mỗi phần mềm dùng mô hình riêng để ước lượng phần trăm văn bản có khả năng được viết bởi AI (ChatGPT, Gemini, Claude…). Turnitin và Originality.ai là hai công cụ được nhiều trường và tạp chí tham chiếu. Kết quả AI là ước lượng xác suất, không phải bằng chứng kết luận vi phạm.',
		'Nên chọn phần mềm nào?' => 'Hãy chọn đúng phần mềm mà trường hoặc tạp chí của bạn dùng. Phần lớn trường đại học dùng Turnitin; tạp chí khoa học thường dùng iThenticate. Mỗi đơn kiểm tra trên 1 phần mềm; nếu chưa rõ, hãy chọn Turnitin - phần mềm được nhiều trường đại học sử dụng. Muốn đối chiếu thêm, bạn có thể đặt một đơn riêng trên Copyleaks.',
		'Giảm tỷ lệ đạo văn có làm thay đổi nội dung không?' => 'Biên tập viên viết lại câu chữ, đổi cấu trúc diễn đạt, bổ sung trích dẫn đúng chuẩn nhưng giữ nguyên ý, số liệu và thuật ngữ chuyên ngành. Chúng tôi không viết thay nội dung, không bịa nguồn và không dùng mẹo che giấu. Bạn nhận file đã biên tập (có thể yêu cầu bản Track Changes để rà soát) kèm báo cáo kiểm tra lại.',
		'Nếu không đạt mục tiêu đã chọn thì sao?' => 'Mục tiêu là mức tham khảo vì kết quả phụ thuộc cơ sở dữ liệu của phần mềm. Nếu bản bàn giao chưa đạt khi kiểm tra trên cùng phần mềm và thiết lập, chúng tôi biên tập bổ sung miễn phí hoặc hoàn phí theo Chính sách hoàn tiền.',
		'Dịch vụ có vi phạm liêm chính học thuật không?' => 'Chúng tôi chỉ hỗ trợ biên tập ngôn ngữ và trích dẫn cho bài do chính bạn nghiên cứu, viết. Không nhận viết thuê, không tạo nội dung thay bạn, không bịa trích dẫn và không giúp che giấu nội dung do AI tạo. Bạn cần tuân thủ quy định của trường hoặc tạp chí, kể cả việc khai báo hỗ trợ biên tập và sử dụng AI nếu được yêu cầu.',
		'Số trang được tính thế nào?' => 'Tính theo trang A4 của file bạn gửi (khoảng 300-350 chữ/trang). Khi bạn tải file Word lên, hệ thống tự đọc số trang. Với dịch vụ giảm tỷ lệ, bạn có thể chỉ gửi những chương cần sửa để tiết kiệm chi phí.',
		'Bao lâu thì tài liệu của tôi bị xóa?' => 'File gốc được tự động xóa khỏi máy chủ sau 30 ngày kể từ ngày đặt. Bạn có thể yêu cầu xóa sớm hơn qua hotline/Zalo.',
		'Đặt giảm tỷ lệ sau khi kiểm tra có được ưu đãi không?' => 'Có. Khi đơn kiểm tra hoàn thành, bạn bấm “Giảm dưới 30%” hoặc “Giảm dưới 20%” ngay trên trang tra cứu để được giảm thêm ' . $r['linked_pct'] . '% và không cần gửi lại file.',
	);
}

/** Blog, chuyên mục, thẻ, tìm kiếm: 9 bài mỗi trang (lưới 3 cột). */
add_action( 'pre_get_posts', function ( $q ) {
	if ( ! is_admin() && $q->is_main_query() && ( $q->is_home() || $q->is_category() || $q->is_tag() || $q->is_search() || $q->is_author() || $q->is_date() ) ) {
		$q->set( 'posts_per_page', 9 );
	}
} );

/* =========================================================
 * Shortcode giá: luôn khớp bảng giá hiện hành
 *   [vs_gia tool="doit" mode="plag"]  -> 150.000đ
 *   [vs_bang_gia]                     -> bảng giá kiểm tra của mọi phần mềm
 *   [vs_bang_gia_giam]                -> đơn giá giảm tỷ lệ
 * ======================================================= */
/** [vs_thong_tin_dn]: thông tin đơn vị cung cấp dịch vụ (lấy từ Cài đặt, luôn cập nhật). */
add_shortcode( 'vs_thong_tin_dn', function () {
	$rows = array(
		'Đơn vị'              => vs_opt( 'company', get_bloginfo( 'name' ) ),
		'Người đại diện'      => vs_opt( 'owner' ),
		'Mã số thuế / ĐKKD'   => vs_opt( 'tax_code' ),
		'Giấy chứng nhận'     => vs_opt( 'license' ),
		'Địa chỉ'             => vs_opt( 'address' ),
		'Điện thoại'          => vs_opt( 'hotline' ),
		'Email'               => vs_opt( 'email' ),
	);
	$html = '<ul class="biz-info">';
	foreach ( array_filter( $rows ) as $k => $v ) {
		$html .= '<li><b>' . esc_html( $k ) . ':</b> ' . esc_html( $v ) . '</li>';
	}
	return $html . '</ul>';
} );

add_shortcode( 'vs_gia', function ( $a ) {
	$a     = shortcode_atts( array( 'tool' => 'turnitin', 'mode' => 'plag' ), $a );
	$tools = vs_pricing()['tools'];
	if ( ! isset( $tools[ $a['tool'] ][ $a['mode'] ] ) ) {
		return '';
	}
	return '<b class="price-inline">' . esc_html( vs_price_label( $tools[ $a['tool'] ][ $a['mode'] ] ) ) . '</b>';
} );

add_shortcode( 'vs_bang_gia', function ( $a ) {
	$a     = shortcode_atts( array( 'tools' => '' ), $a );
	$p     = vs_pricing();
	$meta  = vs_tool_meta();
	$only  = array_filter( array_map( 'trim', explode( ',', $a['tools'] ) ) );
	$rows  = '';
	foreach ( $p['tools'] as $k => $t ) {
		if ( $only && ! in_array( $k, $only, true ) ) {
			continue;
		}
		$rows .= sprintf(
			'<tr><td><span class="pt-tool"><i class="%s" style="--c:%s">%s</i>%s</span></td><td>%s</td><td>%s</td><td>%s</td><td><a href="%s">Đặt</a></td></tr>',
			esc_attr( trim( vs_tool_mark_class( $k ) ) ),
			esc_attr( $meta[ $k ][1] ),
			vs_tool_mark( $k ),
			esc_html( $t['name'] ),
			esc_html( vs_price_label( $t['plag'] ) ),
			esc_html( vs_price_label( $t['ai'] ) ),
			esc_html( vs_price_label( $t['combo'] ) ),
			esc_url( vs_page_url( 'dat-hang', array( 'tab' => 'check', 'tool' => $k ) ) )
		);
	}
	return '<div class="table-wrap"><table class="price-table"><thead><tr><th>Phần mềm</th><th>Chỉ đạo văn</th><th>Chỉ AI</th><th>Đạo văn + AI</th><th></th></tr></thead><tbody>' . $rows . '</tbody></table></div>'
		. '<p class="price-note">Giá cho 1 file tối đa ' . (int) $p['check']['pages_included'] . ' trang. Mỗi ' . (int) $p['check']['pages_included'] . ' trang vượt thêm +' . (int) $p['check']['extra_pct'] . '%, hỏa tốc 30 phút +' . (int) $p['check']['express_pct'] . '%.</p>';
} );

add_shortcode( 'vs_bang_gia_giam', function () {
	$r = vs_pricing()['reduce'];
	return '<div class="table-wrap"><table class="price-table"><thead><tr><th>Mục tiêu</th><th>Đơn giá / trang</th><th>Ghi chú</th></tr></thead><tbody>'
		. '<tr><td>Đạo văn dưới 30%</td><td><b>' . esc_html( vs_money( $r['t30'] ) ) . '</b></td><td>Tỷ lệ hiện tại đến 40%</td></tr>'
		. '<tr><td>Đạo văn dưới 20%</td><td><b>' . esc_html( vs_money( $r['t20'] ) ) . '</b></td><td>Tỷ lệ hiện tại đến 40%</td></tr>'
		. '<tr><td>Mục tiêu thấp hơn 20%</td><td>+' . (int) $r['step_pct'] . '% / mỗi 1%</td><td>Bạn tự chọn mục tiêu</td></tr>'
		. '<tr><td>Tỷ lệ AI dưới 20%</td><td><b>' . esc_html( vs_money( $r['ai_unit'] ) ) . '</b></td><td>Tỷ lệ AI hiện tại đến 40%</td></tr>'
		. implode( '', array_map( function ( $d ) {
			return '<tr><td>' . esc_html( $d['label'] ) . '</td><td>' . ( $d['mult'] > 1 ? '+' . (int) round( ( $d['mult'] - 1 ) * 100 ) . '%' : 'Giá gốc' ) . '</td><td>Thời hạn bàn giao</td></tr>';
		}, $r['deadlines'] ) )
		. '<tr><td>Giảm cả đạo văn + AI</td><td>Phần AI -' . (int) ( 100 - $r['both_pct'] ) . '%</td><td>Rẻ hơn đặt riêng</td></tr>'
		. '<tr><td>Đặt từ đơn kiểm tra</td><td>-' . (int) $r['linked_pct'] . '%</td><td>Không cần tải lại báo cáo</td></tr>'
		. '</tbody></table></div><p class="price-note">Tỷ lệ hiện tại cao hơn 40% áp dụng hệ số từ ×1,25. Đơn tối thiểu ' . esc_html( vs_money( $r['min'] ) ) . '.</p>';
} );

/* Email gửi đi mang tên thương hiệu và địa chỉ hỗ trợ (VD hotro@checkdaovan247.com).
 * Nếu dùng plugin SMTP (WP Mail SMTP…), hãy đặt cùng địa chỉ này trong plugin. */
add_filter( 'wp_mail_from', function ( $from ) {
	$mail = vs_opt( 'email' );
	return is_email( $mail ) ? $mail : $from;
} );
add_filter( 'wp_mail_from_name', function () {
	return wp_specialchars_decode( vs_opt( 'company', get_bloginfo( 'name' ) ), ENT_QUOTES );
} );

/* =========================================================
 * Bộ nhớ đệm (LiteSpeed / CDN của Hostinger)
 * Trang tài khoản, đặt hàng, tra cứu, quản trị có nội dung riêng của từng người và mã bảo mật:
 * không được lưu đệm. Người đã đăng nhập cũng không nhận bản lưu đệm.
 * ======================================================= */
function vs_is_private_page() {
	if ( is_user_logged_in() ) {
		return true;
	}
	if ( ! is_page() ) {
		return false;
	}
	$tpl = (string) get_page_template_slug();
	foreach ( array( 'account', 'order', 'track', 'admin-orders' ) as $t ) {
		if ( false !== strpos( $tpl, 'page-templates/' . $t . '.php' ) ) {
			return true;
		}
	}
	return false;
}

add_action( 'template_redirect', function () {
	if ( ! vs_is_private_page() ) {
		return;
	}
	if ( ! defined( 'DONOTCACHEPAGE' ) ) {
		define( 'DONOTCACHEPAGE', true );
	}
	do_action( 'litespeed_control_set_nocache', 'Check Dao Van: trang riêng' );
	nocache_headers();
	header( 'X-LiteSpeed-Cache-Control: no-cache' );
	header( 'CDN-Cache-Control: no-store' );
}, 1 );

// Sau mỗi lần cập nhật theme: xóa toàn bộ bộ nhớ đệm 1 lần để khách nhận giao diện / mã mới.
add_action( 'init', function () {
	if ( get_option( 'vs_cache_flushed' ) === VS_VERSION ) {
		return;
	}
	update_option( 'vs_cache_flushed', VS_VERSION );
	do_action( 'litespeed_purge_all' );
	if ( ! headers_sent() ) {
		header( 'X-LiteSpeed-Purge: *' );
	}
}, 99 );
