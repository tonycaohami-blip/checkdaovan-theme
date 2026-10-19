<?php
/**
 * Kết nối API chính thức của các phần mềm kiểm tra.
 *
 * - Turnitin / iThenticate: Turnitin Core API (TCA). Cần hợp đồng tích hợp với Turnitin để có
 *   địa chỉ tenant (VD https://ten-cua-ban.turnitin.com) và API key. KHÔNG dùng tài khoản giảng viên/
 *   sinh viên đăng nhập tự động - điều đó vi phạm điều khoản của Turnitin.
 * - Copyleaks: Copyleaks API v3 (email + API key trong trang quản lý Copyleaks).
 * - Originality.ai: API key trong trang tài khoản Originality.ai (phát hiện AI).
 *
 * Mỗi provider có hàm vs_prov_{name}_step( $order_id, $job ) trả về $job đã cập nhật hoặc WP_Error.
 * Trạng thái job: queued -> (uploaded -> similarity | processing) -> pdf -> done.
 * Tên endpoint/trường dữ liệu theo tài liệu công khai của từng hãng; hãy chạy "Kiểm tra kết nối"
 * trong Quản trị > Đơn hàng > Tự động 24/7 và thử 1 đơn trước khi mở cho khách.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Đường dẫn & tên file gốc của khách. */
function vs_order_file( $id ) {
	$path = vs_private_dir() . '/' . basename( (string) vs_meta( $id, 'file' ) );
	return is_file( $path ) ? $path : '';
}

function vs_http_json( $res ) {
	if ( is_wp_error( $res ) ) {
		return $res;
	}
	return array(
		'code' => (int) wp_remote_retrieve_response_code( $res ),
		'body' => json_decode( wp_remote_retrieve_body( $res ), true ),
		'raw'  => wp_remote_retrieve_body( $res ),
	);
}

function vs_http_fail( $r, $what ) {
	if ( is_wp_error( $r ) ) {
		return $r;
	}
	$b   = is_array( $r['body'] ) ? $r['body'] : array();
	$msg = isset( $b['message'] ) ? $b['message'] : ( isset( $b['error'] ) && is_string( $b['error'] ) ? $b['error'] : mb_substr( (string) $r['raw'], 0, 160 ) );
	return new WP_Error( 'http', $what . ' (HTTP ' . $r['code'] . '): ' . $msg );
}

/* =========================================================
 * Turnitin Core API (Turnitin & iThenticate)
 * ======================================================= */

function vs_tca_req( $tool, $method, $path, $body = null, $raw_headers = null ) {
	$c       = (array) vs_auto_cfg( $tool );
	$headers = array(
		'Authorization'                    => 'Bearer ' . $c['key'],
		'X-Turnitin-Integration-Name'      => 'CheckDaoVan',
		'X-Turnitin-Integration-Version'   => VS_VERSION,
	);
	if ( null !== $raw_headers ) {
		$headers += $raw_headers;
	} else {
		$headers['Content-Type'] = 'application/json';
		$body                    = null === $body ? null : wp_json_encode( $body );
	}
	return vs_http_json( wp_remote_request( untrailingslashit( $c['base'] ) . '/api/v1' . $path, array(
		'method'  => $method,
		'headers' => $headers,
		'body'    => $body,
		'timeout' => 45,
	) ) );
}

/** Chấp nhận EULA cho tài khoản chủ sở hữu (bắt buộc trước khi nộp bài qua TCA). */
function vs_tca_ensure_eula( $tool ) {
	$c   = (array) vs_auto_cfg( $tool );
	$opt = 'vs_tca_eula_' . $tool . '_' . md5( $c['owner'] );
	if ( get_option( $opt ) ) {
		return true;
	}
	// Theo hướng dẫn TCA: kiểm tra tenant.require_eula; nhiều tài khoản iThenticate không yêu cầu EULA.
	$f = vs_tca_req( $tool, 'GET', '/features-enabled' );
	if ( ! is_wp_error( $f ) && 200 === $f['code'] && isset( $f['body']['tenant']['require_eula'] ) && ! $f['body']['tenant']['require_eula'] ) {
		update_option( $opt, 'not-required', false );
		return true;
	}
	$r = vs_tca_req( $tool, 'GET', '/eula/latest' );
	if ( is_wp_error( $r ) || 200 !== $r['code'] || empty( $r['body']['version'] ) ) {
		return vs_http_fail( $r, 'Lấy EULA' );
	}
	$v = $r['body']['version'];
	$a = vs_tca_req( $tool, 'POST', '/eula/' . rawurlencode( $v ) . '/accept', array(
		'user_id'            => $c['owner'],
		'accepted_timestamp' => gmdate( 'Y-m-d\TH:i:s\Z' ),
		'language'           => 'en-US',
	) );
	if ( is_wp_error( $a ) || $a['code'] >= 300 ) {
		return vs_http_fail( $a, 'Chấp nhận EULA' );
	}
	update_option( $opt, $v, false );
	return true;
}

function vs_prov_tca_step( $id, $job ) {
	$tool = $job['tool'];
	$c    = (array) vs_auto_cfg( $tool );
	$sid  = isset( $job['ext']['sid'] ) ? $job['ext']['sid'] : '';

	switch ( $job['state'] ) {
		case 'queued':
			$file = vs_order_file( $id );
			if ( ! $file ) {
				return new WP_Error( 'file', 'Không tìm thấy file gốc' );
			}
			$e = vs_tca_ensure_eula( $tool );
			if ( is_wp_error( $e ) ) {
				return $e;
			}
			$r = vs_tca_req( $tool, 'POST', '/submissions', array(
				'owner'                            => $c['owner'],
				'submitter'                        => $c['owner'],
				'title'                            => vs_meta( $id, 'code' ),
				'owner_default_permission_set'     => 'INSTRUCTOR',
				'submitter_default_permission_set' => 'INSTRUCTOR',
				'metadata'                         => array( 'custom' => wp_json_encode( array( 'order' => vs_meta( $id, 'code' ) ) ) ),
			) );
			if ( is_wp_error( $r ) || ! in_array( $r['code'], array( 200, 201 ), true ) || empty( $r['body']['id'] ) ) {
				return vs_http_fail( $r, 'Tạo submission' );
			}
			$sid = $r['body']['id'];
			$up  = vs_tca_req( $tool, 'PUT', '/submissions/' . $sid . '/original', file_get_contents( $file ), array(
				'Content-Type'        => 'binary/octet-stream',
				'Content-Disposition' => 'inline; filename="' . vs_meta( $id, 'code' ) . '.' . pathinfo( $file, PATHINFO_EXTENSION ) . '"',
			) );
			if ( is_wp_error( $up ) || $up['code'] >= 300 ) {
				return vs_http_fail( $up, 'Tải file lên' );
			}
			$job['ext']['sid'] = $sid;
			$job['state']      = 'uploaded';
			vs_auto_log( $tool . ': đã tải file lên (submission ' . $sid . ')', $id );
			return $job;

		case 'uploaded':
			$r = vs_tca_req( $tool, 'GET', '/submissions/' . $sid );
			if ( is_wp_error( $r ) || 200 !== $r['code'] ) {
				return vs_http_fail( $r, 'Kiểm tra submission' );
			}
			$st = isset( $r['body']['status'] ) ? $r['body']['status'] : '';
			if ( 'ERROR' === $st ) {
				$job['state'] = 'failed';
				$job['error'] = 'Turnitin không xử lý được file' . ( isset( $r['body']['error_code'] ) ? ' (' . $r['body']['error_code'] . ')' : '' );
				return $job;
			}
			if ( 'COMPLETE' !== $st ) {
				return $job; // Chờ lượt sau.
			}
			$repos = 'ithenticate' === $tool
				? array( 'INTERNET', 'PUBLICATION', 'CROSSREF', 'CROSSREF_POSTED_CONTENT' )
				: array( 'INTERNET', 'SUBMITTED_WORK', 'PUBLICATION', 'CROSSREF', 'CROSSREF_POSTED_CONTENT' );
			$s = vs_tca_req( $tool, 'PUT', '/submissions/' . $sid . '/similarity', array(
				'generation_settings' => array( 'search_repositories' => $repos, 'auto_exclude_self_matching_scope' => 'ALL' ),
				'indexing_settings'   => array( 'add_to_index' => false ), // Không lưu bài vào kho (No Repository).
			) );
			if ( is_wp_error( $s ) || $s['code'] >= 300 ) {
				return vs_http_fail( $s, 'Yêu cầu báo cáo' );
			}
			$job['state'] = 'similarity';
			return $job;

		case 'similarity':
			$r = vs_tca_req( $tool, 'GET', '/submissions/' . $sid . '/similarity' );
			if ( is_wp_error( $r ) || 200 !== $r['code'] ) {
				return vs_http_fail( $r, 'Lấy báo cáo' );
			}
			if ( ! isset( $r['body']['status'] ) || 'COMPLETE' !== $r['body']['status'] ) {
				return $job;
			}
			$job['result'] = (float) $r['body']['overall_match_percentage'];
			vs_auto_set_result( $id, $tool, 'plag', $job['result'] );
			$p = vs_tca_req( $tool, 'POST', '/submissions/' . $sid . '/similarity/pdf', array( 'locale' => 'en-US' ) );
			if ( is_wp_error( $p ) || $p['code'] >= 300 || empty( $p['body']['id'] ) ) {
				vs_auto_log( $tool . ': có tỷ lệ nhưng không xuất được PDF', $id );
				$job['state'] = 'done';
				return $job;
			}
			$job['ext']['pdf'] = $p['body']['id'];
			$job['state']      = 'pdf';
			vs_auto_log( $tool . ': tỷ lệ ' . $job['result'] . '%, đang xuất PDF', $id );
			return $job;

		case 'pdf':
			$pid = $job['ext']['pdf'];
			$r   = vs_tca_req( $tool, 'GET', '/submissions/' . $sid . '/similarity/pdf/' . $pid . '/status' );
			if ( is_wp_error( $r ) || 200 !== $r['code'] ) {
				return vs_http_fail( $r, 'Trạng thái PDF' );
			}
			$st = isset( $r['body']['status'] ) ? $r['body']['status'] : '';
			if ( 'FAILED' === $st ) {
				$job['state'] = 'done';
				return $job;
			}
			if ( 'SUCCESS' !== $st ) {
				return $job;
			}
			$f = wp_remote_get( untrailingslashit( $c['base'] ) . '/api/v1/submissions/' . $sid . '/similarity/pdf/' . $pid, array(
				'timeout' => 60,
				'headers' => array( 'Authorization' => 'Bearer ' . $c['key'], 'X-Turnitin-Integration-Name' => 'CheckDaoVan', 'X-Turnitin-Integration-Version' => VS_VERSION ),
			) );
			if ( is_wp_error( $f ) || 200 !== wp_remote_retrieve_response_code( $f ) ) {
				return new WP_Error( 'pdf', 'Tải PDF thất bại' );
			}
			$job['file']  = vs_save_report( $id, $tool, wp_remote_retrieve_body( $f ) );
			$job['state'] = 'done';
			return $job;
	}
	return $job;
}

/** Kiểm tra kết nối TCA. */
function vs_tca_test( $tool ) {
	$c = (array) vs_auto_cfg( $tool );
	if ( empty( $c['base'] ) || empty( $c['key'] ) ) {
		return new WP_Error( 'cfg', 'Chưa nhập địa chỉ tài khoản hoặc API key' );
	}
	$r = vs_tca_req( $tool, 'GET', '/features-enabled' );
	if ( is_wp_error( $r ) || 200 !== $r['code'] ) {
		return vs_http_fail( $r, 'Kết nối' );
	}
	$eula = isset( $r['body']['tenant']['require_eula'] ) ? ( $r['body']['tenant']['require_eula'] ? 'có yêu cầu EULA (hệ thống tự chấp nhận)' : 'không yêu cầu EULA' ) : 'không rõ EULA';
	return 'kết nối thành công - ' . $eula;
}

/* =========================================================
 * Copyleaks API v3
 * ======================================================= */

function vs_cl_token( $force = false ) {
	$t = get_transient( 'vs_cl_token' );
	if ( $t && ! $force ) {
		return $t;
	}
	$c = (array) vs_auto_cfg( 'copyleaks' );
	$r = vs_http_json( wp_remote_post( 'https://id.copyleaks.com/v3/account/login/api', array(
		'timeout' => 30,
		'headers' => array( 'Content-Type' => 'application/json' ),
		'body'    => wp_json_encode( array( 'email' => $c['email'], 'key' => $c['key'] ) ),
	) ) );
	if ( is_wp_error( $r ) || 200 !== $r['code'] || empty( $r['body']['access_token'] ) ) {
		return vs_http_fail( $r, 'Đăng nhập Copyleaks' );
	}
	set_transient( 'vs_cl_token', $r['body']['access_token'], DAY_IN_SECONDS );
	return $r['body']['access_token'];
}

function vs_cl_scan_id( $id, $job ) {
	return strtolower( vs_meta( $id, 'code' ) . '-' . $job['tool'] . '-' . $job['kind'] );
}

function vs_prov_copyleaks_step( $id, $job ) {
	$c = (array) vs_auto_cfg( 'copyleaks' );
	switch ( $job['state'] ) {
		case 'queued':
			$file = vs_order_file( $id );
			if ( ! $file ) {
				return new WP_Error( 'file', 'Không tìm thấy file gốc' );
			}
			$token = vs_cl_token();
			if ( is_wp_error( $token ) ) {
				return $token;
			}
			$scan = vs_cl_scan_id( $id, $job );
			$hook = add_query_arg( array( 'o' => $id, 'j' => $job['tool'] . '-' . $job['kind'], 'k' => substr( wp_hash( 'cl|' . $id ), 0, 20 ) ), rest_url( 'vansach/v1/copyleaks/{STATUS}' ) );
			$r = vs_http_json( wp_remote_request( 'https://api.copyleaks.com/v3/scans/submit/file/' . $scan, array(
				'method'  => 'PUT',
				'timeout' => 60,
				'headers' => array( 'Authorization' => 'Bearer ' . $token, 'Content-Type' => 'application/json' ),
				'body'    => wp_json_encode( array(
					'base64'     => base64_encode( file_get_contents( $file ) ),
					'filename'   => vs_meta( $id, 'code' ) . '.' . pathinfo( $file, PATHINFO_EXTENSION ),
					'properties' => array(
						'sandbox'  => ! empty( $c['sandbox'] ),
						'webhooks' => array( 'status' => str_replace( '%7BSTATUS%7D', '{STATUS}', $hook ) ),
						'pdf'      => array( 'create' => true ),
					),
				) ),
			) ) );
			if ( ! is_wp_error( $r ) && 401 === $r['code'] ) {
				delete_transient( 'vs_cl_token' );
			}
			if ( is_wp_error( $r ) || ! in_array( $r['code'], array( 200, 201 ), true ) ) {
				return vs_http_fail( $r, 'Gửi file Copyleaks' );
			}
			$job['ext']['scan'] = $scan;
			$job['state']       = 'processing';
			vs_auto_log( 'copyleaks: đã gửi file, chờ kết quả', $id );
			return $job;

		case 'processing':
			return $job; // Chờ webhook "completed".

		case 'pdf':
			$token = vs_cl_token();
			if ( is_wp_error( $token ) ) {
				return $token;
			}
			$f = wp_remote_get( 'https://api.copyleaks.com/v3/downloads/' . $job['ext']['scan'] . '/report.pdf', array(
				'timeout' => 60,
				'headers' => array( 'Authorization' => 'Bearer ' . $token ),
			) );
			$code = is_wp_error( $f ) ? 0 : wp_remote_retrieve_response_code( $f );
			if ( 200 === $code ) {
				$job['file']  = vs_save_report( $id, 'copyleaks', wp_remote_retrieve_body( $f ) );
				$job['state'] = 'done';
			} elseif ( ++$job['ext']['pdf_tries'] > 10 ) {
				vs_auto_log( 'copyleaks: không tải được PDF, gửi kết quả không kèm file', $id );
				$job['state'] = 'done';
			}
			return $job;
	}
	return $job;
}

add_action( 'rest_api_init', function () {
	register_rest_route( 'vansach/v1', '/copyleaks/(?P<status>[A-Za-z]+)', array(
		'methods'             => 'POST',
		'permission_callback' => '__return_true', // Xác thực bằng khóa k ký theo secret của website.
		'callback'            => function ( WP_REST_Request $req ) {
			$id = (int) $req->get_param( 'o' );
			if ( ! $id || ! hash_equals( substr( wp_hash( 'cl|' . $id ), 0, 20 ), (string) $req->get_param( 'k' ) ) ) {
				return new WP_REST_Response( null, 401 );
			}
			list( $tool, $kind ) = array_pad( explode( '-', (string) $req->get_param( 'j' ) ), 2, '' );
			$status = strtolower( $req['status'] );
			$body   = (array) $req->get_json_params();
			$jobs   = (array) get_post_meta( $id, '_vs_jobs', true );
			foreach ( $jobs as $i => $j ) {
				if ( $j['tool'] !== $tool || $j['kind'] !== $kind || 'processing' !== $j['state'] ) {
					continue;
				}
				if ( 'completed' === $status ) {
					$score = isset( $body['results']['score']['aggregatedScore'] ) ? (float) $body['results']['score']['aggregatedScore'] : null;
					if ( null === $score ) {
						$jobs[ $i ]['state'] = 'failed';
						$jobs[ $i ]['error'] = 'Webhook không có điểm';
					} else {
						$jobs[ $i ]['result']           = $score;
						$jobs[ $i ]['state']            = 'pdf';
						$jobs[ $i ]['ext']['pdf_tries'] = 0;
						vs_auto_set_result( $id, $tool, $kind, $score );
						vs_auto_log( 'copyleaks: tỷ lệ ' . $score . '%', $id );
					}
				} elseif ( 'error' === $status ) {
					$jobs[ $i ]['state'] = 'failed';
					$jobs[ $i ]['error'] = 'Copyleaks báo lỗi';
				}
			}
			update_post_meta( $id, '_vs_jobs', $jobs );
			vs_auto_kick();
			return new WP_REST_Response( null, 200 );
		},
	) );
} );

function vs_cl_test() {
	$t = vs_cl_token( true );
	return is_wp_error( $t ) ? $t : true;
}

/* =========================================================
 * Originality.ai (phát hiện AI)
 * ======================================================= */

/** Trích chữ từ .docx / .txt để gửi API dạng văn bản. */
function vs_extract_text( $file ) {
	$ext = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
	if ( 'txt' === $ext ) {
		return (string) file_get_contents( $file );
	}
	if ( 'docx' === $ext && class_exists( 'ZipArchive' ) ) {
		$z = new ZipArchive();
		if ( true === $z->open( $file ) ) {
			$xml = (string) $z->getFromName( 'word/document.xml' );
			$z->close();
			$xml = str_replace( array( '</w:p>', '<w:tab/>', '<w:br/>' ), array( "\n", ' ', "\n" ), $xml );
			return html_entity_decode( wp_strip_all_tags( $xml ), ENT_QUOTES | ENT_XML1, 'UTF-8' );
		}
	}
	return '';
}

/*
 * Originality.ai API v3 (docs.originality.ai):
 *   POST https://api.originality.ai/api/v3/scan       header X-OAI-API-KEY
 *        body: title, content, check_ai, check_plagiarism, check_facts, check_readability,
 *              check_grammar, storeScan, aiModelVersion ("lite" | "turbo" | "multilang")
 *        trả về: results.ai.confidence.AI (0-1), results.ai.blocks[] {text, result.fake}, results.credits.used
 *        (không có PDF - báo cáo PDF được tự tạo bởi vs_oai_report_pdf() trong inc/auto/pdf.php)
 *   GET  https://api.originality.ai/api/v3/account/balance -> credits, subscriptionCredits
 * Cần gói Enterprise; tạo key tại app.originality.ai/home/api-token-dashboard.
 */
function vs_oai_headers() {
	return array(
		'X-OAI-API-KEY' => (string) vs_auto_cfg( 'originality' )['key'],
		'Content-Type'  => 'application/json',
		'Accept'        => 'application/json',
	);
}

function vs_prov_originality_step( $id, $job ) {
	if ( 'queued' !== $job['state'] ) {
		return $job;
	}
	$file = vs_order_file( $id );
	$text = $file ? trim( vs_extract_text( $file ) ) : '';
	if ( mb_strlen( $text ) < 100 ) {
		$job['state'] = 'failed';
		$job['error'] = 'Chỉ hỗ trợ tự động file .docx/.txt có nội dung';
		return $job;
	}
	$c     = (array) vs_auto_cfg( 'originality' );
	$model = isset( $c['model'] ) && in_array( $c['model'], array( 'lite', 'turbo', 'multilang' ), true ) ? $c['model'] : 'multilang';
	$r     = vs_http_json( wp_remote_post( 'https://api.originality.ai/api/v3/scan', array(
		'timeout' => 120,
		'headers' => vs_oai_headers(),
		'body'    => wp_json_encode( array(
			'title'             => vs_meta( $id, 'code' ),
			'content'           => $text,
			'check_ai'          => true,
			'check_plagiarism'  => false,
			'check_facts'       => false,
			'check_readability' => false,
			'check_grammar'     => false,
			'storeScan'         => false, // Không lưu bài trên Originality.ai.
			'aiModelVersion'    => $model,
		) ),
	) ) );
	if ( ! is_wp_error( $r ) && 429 === $r['code'] ) {
		return new WP_Error( 'rate', 'Originality.ai đang giới hạn tốc độ, sẽ thử lại' );
	}
	$ai = is_array( $r ) && isset( $r['body']['results']['ai']['confidence']['AI'] ) ? $r['body']['results']['ai']['confidence']['AI'] : null;
	if ( is_wp_error( $r ) || 200 !== $r['code'] || null === $ai ) {
		return vs_http_fail( $r, 'Originality.ai' );
	}
	$job['result'] = round( (float) $ai * 100, 1 );
	vs_auto_set_result( $id, 'originality', 'ai', $job['result'] );
	$used = isset( $r['body']['results']['credits']['used'] ) ? (int) $r['body']['results']['credits']['used'] : 0;
	vs_auto_log( 'originality: tỷ lệ AI ' . $job['result'] . '% (model ' . $model . ', dùng ' . $used . ' credit)', $id );
	// Originality.ai không có API tải PDF: tự tạo báo cáo PDF từ điểm tổng và điểm từng câu.
	try {
		$job['file'] = vs_save_report( $id, 'originality', vs_oai_report_pdf( $id, $r['body']['results'], array( 'model' => $model ) ) );
	} catch ( \Throwable $e ) {
		vs_auto_log( 'originality: không tạo được PDF (' . $e->getMessage() . '), gửi kết quả không kèm file', $id );
	}
	$job['state'] = 'done';
	return $job;
}

/** Kiểm tra kết nối: trả về chuỗi số dư credit hoặc WP_Error. */
function vs_oai_test() {
	$r = vs_http_json( wp_remote_get( 'https://api.originality.ai/api/v3/account/balance', array(
		'timeout' => 20,
		'headers' => vs_oai_headers(),
	) ) );
	if ( is_wp_error( $r ) || 200 !== $r['code'] ) {
		return vs_http_fail( $r, 'Kết nối' );
	}
	$credits = isset( $r['body']['credits'] ) ? (int) $r['body']['credits'] : 0;
	$sub     = isset( $r['body']['subscriptionCredits'] ) ? (int) $r['body']['subscriptionCredits'] : 0;
	return 'kết nối thành công - còn ' . number_format( $credits + $sub, 0, ',', '.' ) . ' credit';
}

/* =====================================================================
 * Các bộ kết nối phát hiện AI qua API chính thức (dùng chung khung xử lý).
 * Mỗi bộ: lấy văn bản từ file, gọi API, quy về dạng chung
 *   array( 'ai' => 0-100, 'blocks' => array( array( 'text' => câu, 'p' => 0-1 ) ) )
 * rồi tạo báo cáo PDF bằng vs_oai_report_pdf() với tên phần mềm tương ứng.
 * ===================================================================== */

/** Văn bản của đơn ('' khi file không đọc được hoặc quá ngắn). */
function vs_job_text( $id ) {
	$file = vs_order_file( $id );
	$text = $file ? trim( vs_extract_text( $file ) ) : '';
	return mb_strlen( $text ) < 100 ? '' : $text;
}

/** Lưu kết quả AI dạng chung + PDF, đánh dấu job hoàn tất. */
function vs_ai_job_finish( $id, $job, $tool, $brand, $out, $model = '' ) {
	$job['result'] = round( max( 0, min( 100, (float) $out['ai'] ) ), 1 );
	vs_auto_set_result( $id, $tool, 'ai', $job['result'] );
	vs_auto_log( $tool . ': tỷ lệ AI ' . $job['result'] . '%', $id );
	$blocks = array();
	foreach ( (array) $out['blocks'] as $b ) {
		$blocks[] = array( 'text' => $b['text'], 'result' => array( 'fake' => (float) $b['p'] ) );
	}
	$res = array( 'ai' => array( 'confidence' => array( 'AI' => $job['result'] / 100, 'Original' => 1 - $job['result'] / 100 ), 'blocks' => $blocks ) );
	try {
		$job['file'] = vs_save_report( $id, $tool, vs_oai_report_pdf( $id, $res, array( 'brand' => $brand, 'model' => $model ) ) );
	} catch ( \Throwable $e ) {
		vs_auto_log( $tool . ': không tạo được PDF (' . $e->getMessage() . '), gửi kết quả không kèm file', $id );
	}
	$job['state'] = 'done';
	return $job;
}

/** Job không có văn bản đọc được: chuyển xử lý tay. */
function vs_job_no_text( $job ) {
	$job['state'] = 'failed';
	$job['error'] = 'Chỉ hỗ trợ tự động file .docx/.txt có nội dung';
	return $job;
}

/* ---------- GPTZero (api.gptzero.me) ----------
 * POST /v2/predict/text  header x-api-key  body {document, multilingual}
 * -> documents[0].class_probabilities.ai (0-1), sentences[].sentence / generated_prob
 * Tạo key tại app.gptzero.me > API.
 */
function vs_gptzero_call( $text ) {
	return vs_http_json( wp_remote_post( 'https://api.gptzero.me/v2/predict/text', array(
		'timeout' => 120,
		'headers' => array(
			'x-api-key'    => (string) vs_auto_cfg( 'gptzero' )['key'],
			'Content-Type' => 'application/json',
			'Accept'       => 'application/json',
		),
		'body'    => wp_json_encode( array( 'document' => $text, 'multilingual' => true ) ),
	) ) );
}

function vs_prov_gptzero_step( $id, $job ) {
	if ( 'queued' !== $job['state'] ) {
		return $job;
	}
	$text = vs_job_text( $id );
	if ( '' === $text ) {
		return vs_job_no_text( $job );
	}
	$r   = vs_gptzero_call( $text );
	$doc = is_array( $r ) && isset( $r['body']['documents'][0] ) ? $r['body']['documents'][0] : null;
	if ( is_wp_error( $r ) || 200 !== $r['code'] || ! isset( $doc['class_probabilities']['ai'] ) ) {
		return vs_http_fail( $r, 'GPTZero' );
	}
	$blocks = array();
	foreach ( (array) ( isset( $doc['sentences'] ) ? $doc['sentences'] : array() ) as $s ) {
		$blocks[] = array( 'text' => isset( $s['sentence'] ) ? $s['sentence'] : '', 'p' => isset( $s['generated_prob'] ) ? $s['generated_prob'] : 0 );
	}
	return vs_ai_job_finish( $id, $job, 'gptzero', 'GPTZero', array( 'ai' => (float) $doc['class_probabilities']['ai'] * 100, 'blocks' => $blocks ), 'multilingual' );
}

function vs_gptzero_test() {
	$r = vs_gptzero_call( 'This short paragraph is only used to verify that the API key works correctly for the connection test of the website.' );
	if ( is_wp_error( $r ) || 200 !== $r['code'] || ! isset( $r['body']['documents'][0] ) ) {
		return vs_http_fail( $r, 'Kết nối' );
	}
	return 'kết nối thành công';
}

/* ---------- Winston AI (api.gowinston.ai) ----------
 * POST /v2/ai-content-detection  Bearer token  body {text, version, sentences, language}
 * -> score (0-100, điểm "do người viết"), sentences[].text / score
 * Tạo token tại app.gowinston.ai > API.
 */
function vs_winston_call( $text ) {
	return vs_http_json( wp_remote_post( 'https://api.gowinston.ai/v2/ai-content-detection', array(
		'timeout' => 120,
		'headers' => array(
			'Authorization' => 'Bearer ' . (string) vs_auto_cfg( 'winston' )['key'],
			'Content-Type'  => 'application/json',
			'Accept'        => 'application/json',
		),
		'body'    => wp_json_encode( array( 'text' => $text, 'version' => 'latest', 'sentences' => true, 'language' => 'auto' ) ),
	) ) );
}

function vs_prov_winston_step( $id, $job ) {
	if ( 'queued' !== $job['state'] ) {
		return $job;
	}
	$text = vs_job_text( $id );
	if ( '' === $text ) {
		return vs_job_no_text( $job );
	}
	$r = vs_winston_call( $text );
	if ( is_wp_error( $r ) || 200 !== $r['code'] || ! isset( $r['body']['score'] ) ) {
		return vs_http_fail( $r, 'Winston AI' );
	}
	$blocks = array();
	$sents  = isset( $r['body']['sentences'] ) ? $r['body']['sentences'] : array();
	foreach ( (array) $sents as $s ) {
		$blocks[] = array( 'text' => isset( $s['text'] ) ? $s['text'] : '', 'p' => isset( $s['score'] ) ? 1 - (float) $s['score'] / 100 : 0 );
	}
	return vs_ai_job_finish( $id, $job, 'winston', 'Winston AI', array( 'ai' => 100 - (float) $r['body']['score'], 'blocks' => $blocks ), 'latest' );
}

function vs_winston_test() {
	$r = vs_winston_call( str_repeat( 'This paragraph is only used to verify that the API key works for the website connection test. ', 4 ) );
	if ( is_wp_error( $r ) || 200 !== $r['code'] || ! isset( $r['body']['score'] ) ) {
		return vs_http_fail( $r, 'Kết nối' );
	}
	return 'kết nối thành công' . ( isset( $r['body']['credits_remaining'] ) ? ' - còn ' . (int) $r['body']['credits_remaining'] . ' credit' : '' );
}

/* ---------- Undetectable.AI - chỉ dùng phần PHÁT HIỆN AI (detector) ----------
 * POST https://ai-detect.undetectable.ai/detect {text, key, model} -> id
 * POST https://ai-detect.undetectable.ai/query  {id} -> status "done", result (0-100, khả năng AI)
 * Không dùng tính năng "humanizer" (viết lại để né phát hiện).
 */
function vs_prov_undetectable_step( $id, $job ) {
	$base = 'https://ai-detect.undetectable.ai';
	$key  = (string) vs_auto_cfg( 'undetectable' )['key'];
	if ( 'queued' === $job['state'] ) {
		$text = vs_job_text( $id );
		if ( '' === $text ) {
			return vs_job_no_text( $job );
		}
		$r = vs_http_json( wp_remote_post( $base . '/detect', array(
			'timeout' => 60,
			'headers' => array( 'Content-Type' => 'application/json', 'Accept' => 'application/json' ),
			'body'    => wp_json_encode( array( 'text' => $text, 'key' => $key, 'model' => 'xlm_ud_detector', 'retry_count' => 0 ) ),
		) ) );
		if ( is_wp_error( $r ) || 200 !== $r['code'] || empty( $r['body']['id'] ) ) {
			return vs_http_fail( $r, 'Undetectable.AI' );
		}
		$job['ext']['uid'] = $r['body']['id'];
		$job['state']      = 'processing';
		return $job;
	}
	$r = vs_http_json( wp_remote_post( $base . '/query', array(
		'timeout' => 30,
		'headers' => array( 'Content-Type' => 'application/json', 'Accept' => 'application/json' ),
		'body'    => wp_json_encode( array( 'id' => $job['ext']['uid'] ) ),
	) ) );
	if ( is_wp_error( $r ) || 200 !== $r['code'] ) {
		return vs_http_fail( $r, 'Undetectable.AI' );
	}
	if ( 'done' !== ( isset( $r['body']['status'] ) ? $r['body']['status'] : '' ) || ! isset( $r['body']['result'] ) ) {
		return $job; // Chưa xong, lượt cron sau hỏi lại.
	}
	return vs_ai_job_finish( $id, $job, 'undetectable', 'Undetectable.AI', array( 'ai' => (float) $r['body']['result'], 'blocks' => array() ) );
}

function vs_undetectable_test() {
	$r = vs_http_json( wp_remote_get( 'https://ai-detect.undetectable.ai/check-user-credits', array(
		'timeout' => 20,
		'headers' => array( 'Accept' => 'application/json', 'apikey' => (string) vs_auto_cfg( 'undetectable' )['key'] ),
	) ) );
	if ( is_wp_error( $r ) || 200 !== $r['code'] ) {
		return vs_http_fail( $r, 'Kết nối' );
	}
	return 'kết nối thành công' . ( isset( $r['body']['credits'] ) ? ' - còn ' . (int) $r['body']['credits'] . ' credit' : '' );
}
