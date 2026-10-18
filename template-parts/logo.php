<?php
/**
 * Logo Check Đạo Văn: biểu tượng tài liệu + dấu tích xanh, kèm chữ.
 * $args['variant'] = 'dark' (nền sáng) | 'light' (nền tối).
 */
$GLOBALS['vs_logo_n'] = isset( $GLOBALS['vs_logo_n'] ) ? $GLOBALS['vs_logo_n'] + 1 : 1;
$gid     = 'vsg' . $GLOBALS['vs_logo_n'];
$variant = isset( $args['variant'] ) ? $args['variant'] : 'dark';
$name    = get_bloginfo( 'name' );
// Tô màu phần "Đạo Văn" khi dùng tên thương hiệu mặc định (Check Đạo Văn).
$word = esc_html( $name );
foreach ( array( 'Đạo Văn', 'ĐạoVăn', 'Sạch' ) as $accent ) {
	if ( false !== mb_strpos( $name, $accent ) ) {
		$word = str_replace( $accent, '<span class="logo__accent">' . $accent . '</span>', esc_html( $name ) );
		break;
	}
}
?>
<span class="logo logo--<?php echo esc_attr( $variant ); ?>">
	<svg class="logo__mark" viewBox="0 0 44 44" width="40" height="40" aria-hidden="true">
		<defs>
			<linearGradient id="<?php echo esc_attr( $gid ); ?>" x1="0" y1="0" x2="1" y2="1">
				<stop offset="0" stop-color="#3d6bff"/>
				<stop offset="1" stop-color="#5a32f0"/>
			</linearGradient>
		</defs>
		<rect width="44" height="44" rx="12" fill="url(#<?php echo esc_attr( $gid ); ?>)"/>
		<path d="M14 8.5h10.5l7.5 7.5v17.5a2 2 0 0 1-2 2H14a2 2 0 0 1-2-2v-23a2 2 0 0 1 2-2z" fill="#fff"/>
		<path d="M24.5 8.5V14a2 2 0 0 0 2 2H32" fill="#cfdaff"/>
		<rect x="15.5" y="19" width="11" height="2.2" rx="1.1" fill="#cfdaff"/>
		<rect x="15.5" y="23.5" width="7.5" height="2.2" rx="1.1" fill="#cfdaff"/>
		<circle cx="31.5" cy="31.5" r="8" fill="#10b981" stroke="#fff" stroke-width="2.5"/>
		<path d="M27.8 31.6l2.5 2.5 4.8-5" fill="none" stroke="#fff" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
	</svg>
	<span class="logo__text">
		<span class="logo__name"><?php echo $word; // phpcs:ignore -- đã escape. ?></span>
		<span class="logo__tag">Kiểm tra đạo văn · AI</span>
	</span>
</span>
