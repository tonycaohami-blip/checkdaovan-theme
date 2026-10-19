<?php
/**
 * Bảng điều khiển tài khoản khách hàng.
 */
$user   = wp_get_current_user();
$uid    = $user->ID;
$tab    = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'tong-quan';
$tabs   = array(
	'tong-quan'     => array( 'Tổng quan', 'home' ),
	'don-hang'      => array( 'Đơn hàng', 'list' ),
	'thong-bao'     => array( 'Thông báo', 'bell' ),
	'cong-tac-vien' => array( 'Cộng tác viên', 'gift' ),
	'thong-tin'     => array( 'Thông tin tài khoản', 'user' ),
);
$tab    = isset( $tabs[ $tab ] ) ? $tab : 'tong-quan';
$orders = array_map( 'vs_order_view', vs_user_orders( $uid ) );
$words  = preg_split( '/\s+/u', trim( $user->display_name ) );
$init   = mb_strtoupper( mb_substr( end( $words ), 0, 1 ) );

/** Khối chi tiết 1 đơn hàng. */
$render_order = function ( $o, $open = false ) {
	$is_check = 'check' === $o['type'];
	// Mỗi bước: [tên khi chưa tới, tên khi đang làm, tên khi đã xong, thời gian, đã xong?]
	$steps    = array(
		array( 'Đặt hàng', 'Đặt hàng', 'Đã đặt hàng', $o['date'], true ),
		array( 'Thanh toán', 'Chờ thanh toán', 'Đã thanh toán', $o['paid_at'], in_array( $o['status'], array( 'dang_xu_ly', 'hoan_thanh' ), true ) ),
		array( 'Xử lý', 'Đang xử lý', 'Đã xử lý', '', 'hoan_thanh' === $o['status'] ),
		array( 'Hoàn thành', 'Hoàn thành', 'Đã hoàn thành', $o['done_at'], 'hoan_thanh' === $o['status'] ),
	);
	$current  = array( 'cho_thanh_toan' => 2, 'dang_xu_ly' => 3, 'hoan_thanh' => 4, 'huy' => -1 );
	$cur_step = isset( $current[ $o['status'] ] ) ? $current[ $o['status'] ] : 0;
	?>
	<?php $open = $open || ( isset( $_GET['don'] ) && sanitize_text_field( wp_unslash( $_GET['don'] ) ) === $o['code'] ); ?>
	<details id="<?php echo esc_attr( $o['code'] ); ?>" class="oc oc--<?php echo esc_attr( $o['status'] ); ?><?php echo $o['files'] ? ' has-files' : ''; ?>" <?php echo ( $open || ( $o['files'] && 'dang_xu_ly' === $o['status'] ) ) ? 'open' : ''; ?>>
		<summary class="oc__head">
			<span class="oc__icon oc__icon--<?php echo esc_attr( $o['type'] ); ?>"><?php echo vs_icon( $is_check ? 'target' : 'pen', 22 ); // phpcs:ignore ?></span>
			<span class="oc__main">
				<span class="oc__title"><?php echo esc_html( $o['title'] ); ?></span>
				<span class="oc__meta"><b><?php echo esc_html( $o['code'] ); ?></b> · <?php echo esc_html( $o['date'] ); ?><?php echo $o['files'] ? ' · <em class="oc__ready">' . count( $o['files'] ) . ' báo cáo sẵn sàng</em>' : ''; ?></span>
			</span>
			<span class="oc__side">
				<span class="oc__price"><?php echo esc_html( vs_money( $o['total'] ) ); ?></span>
				<span class="status status--<?php echo esc_attr( $o['status'] ); ?>"><?php echo esc_html( $o['label'] ); ?></span>
			</span>
			<span class="oc__chev" aria-hidden="true"></span>
		</summary>

		<div class="oc__body">
			<?php if ( 'huy' !== $o['status'] ) : ?>
				<ol class="stepper">
					<?php foreach ( $steps as $i => $s ) : ?>
						<?php $is_cur = ( $i + 1 ) === $cur_step; ?>
						<li class="<?php echo $s[4] ? 'is-done' : ''; ?><?php echo $is_cur ? ' is-current' : ''; ?>">
							<span class="stepper__dot"><?php echo $s[4] ? vs_icon( 'check', 14 ) : (int) ( $i + 1 ); // phpcs:ignore ?></span>
							<b><?php echo esc_html( $s[4] ? $s[2] : ( $is_cur ? $s[1] : $s[0] ) ); ?></b>
							<?php if ( $s[3] ) : ?><small><?php echo esc_html( $s[3] ); ?></small><?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ol>
			<?php endif; ?>

			<div class="oc__grid">
				<div class="oc__left">
					<?php if ( ! empty( $o['auto'] ) && $o['auto']['jobs'] && 'huy' !== $o['status'] ) : $a = $o['auto']; ?>
						<div class="oc__block">
							<h4 class="oc__h">Tiến độ kiểm tra</h4>
							<ul class="auto-steps">
								<?php foreach ( $a['jobs'] as $j ) : ?>
									<li class="<?php echo 'done' === $j['state'] ? 'is-done' : ( 'failed' === $j['state'] ? 'is-manual' : 'is-run' ); ?>"><?php echo vs_icon( 'done' === $j['state'] ? 'check' : 'clock', 16 ); // phpcs:ignore ?><span><?php echo esc_html( $j['name'] ); ?> <small><?php echo esc_html( $j['label'] ); ?></small></span></li>
								<?php endforeach; ?>
								<?php foreach ( $a['manual'] as $m ) : ?>
									<li class="is-manual"><?php echo vs_icon( 'user', 16 ); // phpcs:ignore ?><span><?php echo esc_html( $m ); ?> <small>Đang chờ kết quả...</small></span></li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endif; ?>

					<?php if ( $o['results'] || ! empty( $o['waiting'] ) ) : ?>
						<div class="oc__block">
							<h4 class="oc__h">Kết quả</h4>
							<div class="rs">
								<?php foreach ( (array) $o['waiting'] as $wt ) : ?>
									<div class="rs__card rs__card--wait" aria-live="polite">
										<span class="rs__tool"><?php echo esc_html( $wt['tool'] ); ?></span>
										<div class="wait">
											<span class="wait__ring" aria-hidden="true"></span>
											<span class="wait__text">Đang chờ kết quả<span class="wait__dots" aria-hidden="true"><i></i><i></i><i></i></span></span>
										</div>
										<?php foreach ( $wt['kinds'] as $kname ) : ?>
											<div class="rs__row"><span><?php echo esc_html( $kname ); ?></span><i class="skel skel--num"></i></div>
											<i class="skel skel--bar"></i>
										<?php endforeach; ?>
									</div>
								<?php endforeach; ?>
								<?php foreach ( $o['results'] as $r ) : ?>
									<div class="rs__card">
										<span class="rs__tool"><?php echo esc_html( $r['tool'] ); ?></span>
										<?php foreach ( array( 'plag' => 'Đạo văn', 'ai' => 'Tỷ lệ AI' ) as $k => $lbl ) : if ( null === $r[ $k ] ) { continue; } ?>
											<div class="rs__row">
												<span><?php echo esc_html( $lbl ); ?></span>
												<b class="<?php echo esc_attr( vs_pct_class( $r[ $k ] ) ); ?>"><?php echo esc_html( vs_pct( $r[ $k ] ) ); ?></b>
											</div>
											<div class="bar"><i class="bar__fill <?php echo (float) $r[ $k ] < 20 ? 'bar__fill--ok' : 'bar__fill--bad'; ?>" style="width:<?php echo esc_attr( min( 100, max( 2, (float) $r[ $k ] ) ) ); ?>%"></i></div>
										<?php endforeach; ?>
									</div>
								<?php endforeach; ?>
								<?php echo $o['upsell']; // phpcs:ignore -- HTML dựng sẵn, đã escape. ?>
							</div>
						</div>
					<?php endif; ?>

					<?php if ( $o['files'] ) : ?>
						<div class="oc__block">
							<h4 class="oc__h oc__h--ok"><?php echo vs_icon( 'check', 16 ); // phpcs:ignore ?> Báo cáo đã sẵn sàng<?php echo ! empty( $o['partial'] ) ? '<small>Phần còn lại đang xử lý</small>' : ''; ?></h4>
							<div class="fcards">
								<?php foreach ( $o['files'] as $f ) : ?>
									<a class="fcard" href="<?php echo esc_url( $f['url'] ); ?>" target="_blank" rel="noopener" download>
										<span class="fcard__icon">PDF</span>
										<span class="fcard__name" title="<?php echo esc_attr( $f['name'] ); ?>"><?php echo esc_html( isset( $f['short'] ) ? $f['short'] : $f['name'] ); ?></span>
										<span class="fcard__btn"><?php echo vs_icon( 'arrow', 16 ); // phpcs:ignore ?> Tải xuống</span>
									</a>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endif; ?>

					<?php if ( 'cho_thanh_toan' === $o['status'] && ! $o['results'] ) : ?>
						<div class="oc__note"><?php echo vs_icon( 'clock', 18 ); // phpcs:ignore ?><span>Đơn sẽ được xử lý ngay khi hệ thống nhận được thanh toán. Thông tin chuyển khoản ở bên cạnh.</span></div>
					<?php elseif ( 'dang_xu_ly' === $o['status'] && ! $o['files'] && empty( $o['auto']['jobs'] ) ) : ?>
						<div class="oc__note"><?php echo vs_icon( 'user', 18 ); // phpcs:ignore ?><span><?php echo $is_check ? 'Đang chờ kết quả... Kết quả sẽ hiện tại đây và gửi qua email ngay khi có.' : 'Đang chờ kết quả... Biên tập viên đang chỉnh sửa, bạn sẽ nhận file và báo cáo sau chỉnh sửa tại đây.'; ?></span></div>
					<?php elseif ( 'huy' === $o['status'] ) : ?>
						<div class="oc__note"><?php echo vs_icon( 'chat', 18 ); // phpcs:ignore ?><span>Đơn đã hủy. Liên hệ Zalo <?php echo esc_html( vs_opt( 'zalo' ) ); ?> nếu bạn cần hỗ trợ.</span></div>
					<?php endif; ?>

				</div>

				<aside class="oc__right">
					<?php if ( $o['pay'] ) : $pay = $o['pay']; ?>
						<div class="paybox">
							<h4 class="oc__h">Thanh toán</h4>
							<img src="<?php echo esc_url( $pay['qr'] ); ?>" alt="Mã QR thanh toán đơn <?php echo esc_attr( $o['code'] ); ?>" loading="lazy">
							<dl class="kv">
								<dt>Ngân hàng</dt><dd><?php echo esc_html( $pay['bank'] ); ?></dd>
								<dt>Số TK</dt><dd><?php echo esc_html( $pay['account'] ); ?> <button type="button" class="copy" data-copy="<?php echo esc_attr( $pay['account'] ); ?>">Chép</button></dd>
								<dt>Chủ TK</dt><dd><?php echo esc_html( $pay['holder'] ); ?></dd>
								<dt>Số tiền</dt><dd class="big"><?php echo esc_html( vs_money( $pay['amount'] ) ); ?></dd>
								<dt>Nội dung</dt><dd><?php echo esc_html( $pay['content'] ); ?> <button type="button" class="copy" data-copy="<?php echo esc_attr( $pay['content'] ); ?>">Chép</button></dd>
							</dl>
							<div class="pay-verify"><button type="button" class="btn btn--primary btn--sm btn--block" data-pay-verify data-code="<?php echo esc_attr( $o['code'] ); ?>" data-phone="<?php echo esc_attr( vs_user_phone() ); ?>">Tôi đã chuyển khoản</button><span data-pay-verify-msg></span></div>
						</div>
					<?php endif; ?>
					<div class="oc__info">
						<h4 class="oc__h">Thông tin đơn hàng</h4>
						<dl class="kv">
							<dt>Mã đơn</dt><dd><?php echo esc_html( $o['code'] ); ?> <button type="button" class="copy" data-copy="<?php echo esc_attr( $o['code'] ); ?>">Chép</button></dd>
							<?php foreach ( $o['details'] as $k => $v ) : ?>
								<dt><?php echo esc_html( $k ); ?></dt><dd><?php echo esc_html( $v ); ?></dd>
							<?php endforeach; ?>
							<?php if ( ! empty( $o['sent'] ) ) : ?>
								<dt class="sent-files__dt">File đã gửi</dt>
								<dd class="sent-files">
									<?php foreach ( $o['sent'] as $sf ) : ?>
										<?php $tag = $sf['url'] ? 'a' : 'span'; ?>
										<<?php echo $tag; // phpcs:ignore ?> class="sent-file<?php echo $sf['url'] ? '' : ' is-gone'; ?>"<?php echo $sf['url'] ? ' href="' . esc_url( $sf['url'] ) . '" download' : ''; ?> title="<?php echo esc_attr( $sf['label'] . ': ' . $sf['name'] . ( $sf['url'] ? ' - bấm để tải xuống' : ' - file đã được xóa theo chính sách lưu trữ' ) ); ?>">
											<span class="sent-file__ext sent-file__ext--<?php echo esc_attr( strtolower( $sf['ext'] ) ); ?>"><?php echo esc_html( $sf['ext'] ? $sf['ext'] : 'FILE' ); ?></span>
											<span class="sent-file__body">
												<span class="sent-file__name"><?php echo esc_html( $sf['short'] ); ?></span>
												<span class="sent-file__meta"><?php echo esc_html( $sf['label'] . ( $sf['size'] ? ' · ' . $sf['size'] : '' ) ); ?></span>
											</span>
											<?php if ( $sf['url'] ) : ?><span class="sent-file__dl" aria-hidden="true"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 4v11M7 10l5 5 5-5M5 20h14"/></svg></span><?php endif; ?>
										</<?php echo $tag; // phpcs:ignore ?>>
									<?php endforeach; ?>
								</dd>
							<?php endif; ?>
							<dt>Thành tiền</dt><dd><b><?php echo esc_html( vs_money( $o['total'] ) ); ?></b></dd>
						</dl>
						<a class="oc__help" href="<?php echo esc_url( vs_zalo( vs_opt( 'zalo' ) ) ); ?>" target="_blank" rel="noopener"><?php echo vs_icon( 'chat', 16 ); // phpcs:ignore ?> Cần hỗ trợ đơn này? Nhắn Zalo</a>
					</div>
				</aside>
			</div>
		</div>
	</details>
	<?php
};
?>
<?php
$watch = false; // Chỉ tự làm mới ở tab có danh sách đơn.
foreach ( $orders as $o ) {
	if ( in_array( $tab, array( 'tong-quan', 'don-hang' ), true ) && ( ( 'cho_thanh_toan' === $o['status'] && vs_auto_pay_on() ) || ! empty( $o['auto']['pending'] ) ) ) {
		$watch = true;
	}
}
?>
<section class="account"<?php echo $watch ? ' data-autorefresh="30"' : ''; ?>>
	<div class="container account__layout">
		<aside class="account__nav">
			<div class="account__me">
				<span class="avatar"><?php echo esc_html( $init ); ?></span>
				<div><b><?php echo esc_html( $user->display_name ); ?></b><small><?php echo esc_html( vs_user_phone( $uid ) ? vs_user_phone( $uid ) : $user->user_email ); ?></small></div>
			</div>
			<nav>
				<?php foreach ( $tabs as $k => $t ) : ?>
					<a href="<?php echo esc_url( vs_account_url( $k ) ); ?>" class="<?php echo $tab === $k ? 'is-active' : ''; ?>"><?php echo vs_icon( $t[1], 20 ); // phpcs:ignore ?><?php echo esc_html( $t[0] ); ?></a>
				<?php endforeach; ?>
				<a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>"><?php echo vs_icon( 'logout', 20 ); // phpcs:ignore ?>Đăng xuất</a>
			</nav>
			<a class="btn btn--accent btn--block" href="<?php echo esc_url( vs_page_url( 'dat-hang' ) ); ?>">+ Đặt đơn mới</a>
		</aside>

		<div class="account__main">
			<?php vs_notice(); ?>

			<?php if ( 'tong-quan' === $tab ) : ?>
				<?php
				$count = array( 'all' => count( $orders ), 'dang_xu_ly' => 0, 'hoan_thanh' => 0, 'spent' => 0 );
				foreach ( $orders as $o ) {
					if ( isset( $count[ $o['status'] ] ) ) {
						$count[ $o['status'] ]++;
					}
					if ( in_array( $o['status'], array( 'dang_xu_ly', 'hoan_thanh' ), true ) ) {
						$count['spent'] += $o['total'];
					}
				}
				?>
				<div class="acc-head">
					<h1>Xin chào, <?php echo esc_html( $user->display_name ); ?>!</h1>
					<p class="muted">Đây là tổng quan tài khoản của bạn.</p>
				</div>
				<div class="stat-grid">
					<div class="stat"><span>Tổng đơn hàng</span><b><?php echo (int) $count['all']; ?></b></div>
					<div class="stat"><span>Đang xử lý</span><b><?php echo (int) $count['dang_xu_ly']; ?></b></div>
					<div class="stat"><span>Hoàn thành</span><b><?php echo (int) $count['hoan_thanh']; ?></b></div>
					<div class="stat"><span>Đã thanh toán</span><b><?php echo esc_html( vs_money( $count['spent'] ) ); ?></b></div>
				</div>
				<div class="quick">
					<a href="<?php echo esc_url( vs_page_url( 'dat-hang', array( 'tab' => 'check' ) ) ); ?>"><?php echo vs_icon( 'target', 26 ); // phpcs:ignore ?><b>Kiểm tra đạo văn &amp; AI</b><span>Từ <?php echo esc_html( vs_money( vs_min_check_price() ) ); ?></span></a>
					<a href="<?php echo esc_url( vs_page_url( 'dat-hang', array( 'tab' => 'reduce' ) ) ); ?>"><?php echo vs_icon( 'pen', 26 ); // phpcs:ignore ?><b>Giảm tỷ lệ đạo văn</b><span>Dưới 30% / 20%</span></a>
					<a href="<?php echo esc_url( vs_account_url( 'cong-tac-vien' ) ); ?>"><?php echo vs_icon( 'gift', 26 ); // phpcs:ignore ?><b>Giới thiệu bạn bè</b><span>Nhận <?php echo esc_html( vs_opt( 'aff_pct' ) ); ?>% hoa hồng</span></a>
				</div>
				<div class="panel">
					<div class="panel__head"><h2>Đơn hàng gần đây</h2><a href="<?php echo esc_url( vs_account_url( 'don-hang' ) ); ?>">Xem tất cả →</a></div>
					<?php if ( $orders ) : ?>
						<?php foreach ( array_slice( $orders, 0, 3 ) as $i => $o ) { $render_order( $o, 0 === $i ); } ?>
					<?php else : ?>
						<div class="empty-box"><?php echo vs_icon( 'file', 40 ); // phpcs:ignore ?><p>Bạn chưa có đơn hàng nào.</p><a class="btn btn--primary" href="<?php echo esc_url( vs_page_url( 'dat-hang' ) ); ?>">Đặt đơn đầu tiên</a></div>
					<?php endif; ?>
				</div>

			<?php elseif ( 'don-hang' === $tab ) : ?>
				<div class="acc-head"><h1>Đơn hàng của tôi</h1><p class="muted">Bấm vào từng đơn để xem kết quả, tải báo cáo hoặc thanh toán.</p></div>
				<?php if ( $orders ) : ?>
					<div class="o-list"><?php foreach ( $orders as $i => $o ) { $render_order( $o, 0 === $i ); } ?></div>
				<?php else : ?>
					<div class="empty-box"><?php echo vs_icon( 'file', 40 ); // phpcs:ignore ?><p>Bạn chưa có đơn hàng nào.</p><a class="btn btn--primary" href="<?php echo esc_url( vs_page_url( 'dat-hang' ) ); ?>">Đặt đơn đầu tiên</a></div>
				<?php endif; ?>

			<?php elseif ( 'thong-bao' === $tab ) : ?>
				<?php
				$notices = vs_user_notices( $uid );
				// Mở tab là đã xem tất cả.
				if ( vs_user_unread( $uid ) ) {
					update_user_meta( $uid, '_vs_notices', array_map( function ( $n ) {
						$n['was_unread'] = empty( $n['read'] );
						$n['read']       = 1;
						return $n;
					}, $notices ) );
				}
				?>
				<div class="acc-head">
					<h1>Thông báo</h1>
					<p class="muted">Cập nhật về đơn hàng, kết quả kiểm tra và hoa hồng cộng tác viên.</p>
				</div>
				<?php if ( ! $notices ) : ?>
					<div class="empty-box"><?php echo vs_icon( 'bell', 32 ); // phpcs:ignore ?><p>Chưa có thông báo nào.</p></div>
				<?php else : ?>
					<ul class="notif-list">
						<?php foreach ( $notices as $n ) : ?>
							<li class="notif notif--<?php echo esc_attr( $n['type'] ); ?><?php echo empty( $n['read'] ) ? ' is-new' : ''; ?>">
								<span class="notif__icon" aria-hidden="true"></span>
								<div class="notif__body">
									<?php if ( ! empty( $n['url'] ) ) : ?>
										<a class="notif__title" href="<?php echo esc_url( $n['url'] ); ?>"><?php echo esc_html( $n['title'] ); ?></a>
									<?php else : ?>
										<b class="notif__title"><?php echo esc_html( $n['title'] ); ?></b>
									<?php endif; ?>
									<?php if ( $n['text'] ) : ?><p><?php echo esc_html( $n['text'] ); ?></p><?php endif; ?>
									<small><?php echo esc_html( vs_time_ago( $n['t'] ) ); ?></small>
								</div>
								<?php if ( empty( $n['read'] ) ) : ?><span class="notif__dot" title="Mới"></span><?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

			<?php elseif ( 'cong-tac-vien' === $tab ) : ?>
				<?php $s = vs_aff_stats( $uid ); $link = vs_aff_link( $uid ); ?>
				<div class="acc-head"><h1>Cộng tác viên</h1><p class="muted">Chia sẻ link, nhận <?php echo esc_html( vs_opt( 'aff_pct' ) ); ?>% hoa hồng mỗi đơn hoàn thành. Bạn bè được giảm <?php echo esc_html( vs_opt( 'ref_discount' ) ); ?>%.</p></div>
				<div class="ref-box">
					<div class="ref-box__item"><span>Link giới thiệu</span><div class="copyfield"><input type="text" readonly value="<?php echo esc_attr( $link ); ?>"><button class="btn btn--primary btn--sm" data-copy="<?php echo esc_attr( $link ); ?>">Sao chép</button></div></div>
					<div class="ref-box__item"><span>Mã giới thiệu</span><div class="copyfield"><input type="text" readonly value="<?php echo esc_attr( vs_aff_code( $uid ) ); ?>"><button class="btn btn--outline btn--sm" data-copy="<?php echo esc_attr( vs_aff_code( $uid ) ); ?>">Sao chép</button></div></div>
					<div class="share-row">
						<a class="btn btn--sm btn--outline" target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo rawurlencode( $link ); ?>">Chia sẻ Facebook</a>
						<a class="btn btn--sm btn--outline" target="_blank" rel="noopener" href="https://sp.zalo.me/share_inline?d=<?php echo rawurlencode( base64_encode( wp_json_encode( array( 'url' => $link ) ) ) ); ?>">Chia sẻ Zalo</a>
					</div>
				</div>
				<div class="stat-grid stat-grid--3">
					<div class="stat"><span>Lượt click</span><b><?php echo (int) $s['clicks']; ?></b></div>
					<div class="stat"><span>Khách đăng ký</span><b><?php echo (int) $s['customers']; ?></b></div>
					<div class="stat"><span>Đơn được giới thiệu</span><b><?php echo (int) $s['orders']; ?></b></div>
					<div class="stat"><span>Hoa hồng chờ duyệt</span><b><?php echo esc_html( vs_money( $s['pending'] ) ); ?></b></div>
					<div class="stat"><span>Đã nhận</span><b><?php echo esc_html( vs_money( $s['paid'] ) ); ?></b></div>
					<div class="stat stat--hl"><span>Có thể rút</span><b><?php echo esc_html( vs_money( $s['available'] ) ); ?></b></div>
				</div>
				<div class="panel">
					<div class="panel__head"><h2>Rút tiền</h2><span class="muted">Tối thiểu <?php echo esc_html( vs_money( vs_opt( 'payout_min' ) ) ); ?></span></div>
					<?php $has_bank = get_user_meta( $uid, '_vs_bank_account', true ); ?>
					<?php if ( ! $has_bank ) : ?>
						<p class="muted">Bạn cần <a href="<?php echo esc_url( vs_account_url( 'thong-tin' ) ); ?>">cập nhật thông tin ngân hàng</a> để nhận tiền.</p>
					<?php elseif ( $s['requested'] ) : ?>
						<p>Đang chờ chuyển khoản: <b><?php echo esc_html( vs_money( $s['requested'] ) ); ?></b></p>
					<?php else : ?>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="inline-form">
							<input type="hidden" name="action" value="vs_payout"><?php wp_nonce_field( 'vs_payout', '_vsn' ); ?>
							<span>Chuyển về: <b><?php echo esc_html( get_user_meta( $uid, '_vs_bank_name', true ) . ' · ' . $has_bank . ' · ' . get_user_meta( $uid, '_vs_bank_holder', true ) ); ?></b></span>
							<button class="btn btn--accent" <?php disabled( $s['available'] < (int) vs_opt( 'payout_min' ) ); ?>>Yêu cầu rút <?php echo esc_html( vs_money( $s['available'] ) ); ?></button>
						</form>
					<?php endif; ?>
				</div>
				<?php $ref_orders = vs_aff_orders( $uid ); ?>
				<div class="panel">
					<div class="panel__head"><h2>Đơn hàng được giới thiệu</h2></div>
					<?php if ( $ref_orders ) : ?>
						<div class="table-wrap"><table class="ptable">
							<thead><tr><th>Ngày</th><th>Khách hàng</th><th>Giá trị đơn</th><th>Hoa hồng</th><th>Trạng thái</th></tr></thead>
							<tbody>
							<?php foreach ( $ref_orders as $oid ) : $nm = vs_meta( $oid, 'name' ); ?>
								<tr>
									<td><?php echo esc_html( vs_post_date( 'd/m/Y', $oid ) ); ?></td>
									<td><?php echo esc_html( mb_substr( $nm, 0, 1 ) . '***' . mb_substr( $nm, -2 ) ); ?></td>
									<td><?php echo esc_html( vs_money( vs_meta( $oid, 'price' ) ) ); ?></td>
									<td><b><?php echo esc_html( vs_money( vs_meta( $oid, 'commission' ) ) ); ?></b></td>
									<td><?php echo esc_html( vs_comm_labels()[ vs_meta( $oid, 'comm_status', 'pending' ) ] ); ?></td>
								</tr>
							<?php endforeach; ?>
							</tbody>
						</table></div>
					<?php else : ?>
						<p class="muted">Chưa có đơn nào qua link của bạn. Hãy chia sẻ link ở trên để bắt đầu!</p>
					<?php endif; ?>
				</div>
				<?php $payouts = vs_aff_payouts( $uid ); ?>
				<?php if ( $payouts ) : ?>
					<div class="panel">
						<div class="panel__head"><h2>Lịch sử rút tiền</h2></div>
						<div class="table-wrap"><table class="ptable"><thead><tr><th>Ngày</th><th>Số tiền</th><th>Trạng thái</th></tr></thead><tbody>
							<?php foreach ( $payouts as $pid ) : ?>
								<tr><td><?php echo esc_html( vs_post_date( 'd/m/Y', $pid ) ); ?></td><td><?php echo esc_html( vs_money( vs_meta( $pid, 'amount' ) ) ); ?></td><td><?php echo esc_html( vs_payout_labels()[ vs_meta( $pid, 'status', 'pending' ) ] ); ?></td></tr>
							<?php endforeach; ?>
						</tbody></table></div>
					</div>
				<?php endif; ?>

			<?php else : ?>
				<div class="acc-head"><h1>Thông tin tài khoản</h1></div>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="panel form-grid">
					<input type="hidden" name="action" value="vs_profile"><?php wp_nonce_field( 'vs_profile', '_vsn' ); ?>
					<div class="panel__head"><h2>Thông tin cá nhân</h2></div>
					<label class="field"><span class="label">Họ và tên</span><input type="text" name="name" required value="<?php echo esc_attr( $user->display_name ); ?>"></label>
					<div class="row">
						<label class="field"><span class="label">Số điện thoại</span><input type="tel" name="phone" required value="<?php echo esc_attr( vs_user_phone( $uid ) ); ?>"></label>
						<label class="field"><span class="label">Email</span><input type="email" name="email" required value="<?php echo esc_attr( $user->user_email ); ?>"></label>
					</div>
					<div class="panel__head panel__head--sub"><h2>Tài khoản nhận hoa hồng</h2></div>
					<div class="row row--3">
						<label class="field"><span class="label">Ngân hàng</span><input type="text" name="bank_name" placeholder="VD: Vietcombank" value="<?php echo esc_attr( get_user_meta( $uid, '_vs_bank_name', true ) ); ?>"></label>
						<label class="field"><span class="label">Số tài khoản</span><input type="text" name="bank_account" value="<?php echo esc_attr( get_user_meta( $uid, '_vs_bank_account', true ) ); ?>"></label>
						<label class="field"><span class="label">Chủ tài khoản</span><input type="text" name="bank_holder" placeholder="IN HOA KHÔNG DẤU" value="<?php echo esc_attr( get_user_meta( $uid, '_vs_bank_holder', true ) ); ?>"></label>
					</div>
					<div><button class="btn btn--primary">Lưu thông tin</button></div>
				</form>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="panel form-grid">
					<input type="hidden" name="action" value="vs_password"><?php wp_nonce_field( 'vs_password', '_vsn' ); ?>
					<div class="panel__head"><h2>Đổi mật khẩu</h2></div>
					<div class="row">
						<label class="field"><span class="label">Mật khẩu hiện tại</span><input type="password" name="current" required autocomplete="current-password"></label>
						<label class="field"><span class="label">Mật khẩu mới (ít nhất 8 ký tự)</span><input type="password" name="new" required minlength="8" autocomplete="new-password"></label>
					</div>
					<div><button class="btn btn--outline">Đổi mật khẩu</button></div>
				</form>
			<?php endif; ?>
		</div>
	</div>
</section>
