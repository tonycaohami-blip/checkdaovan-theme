<?php
/**
 * Ô tải file (kéo thả).
 * $args: name (file|report), accept, title, sub, icon
 */
$a = wp_parse_args( isset( $args ) ? $args : array(), array(
	'name'   => 'file',
	'accept' => '.docx,.pdf,.txt,.rtf',
	'title'  => 'Kéo thả hoặc bấm để chọn file',
	'sub'    => '.docx, .pdf, .txt, .rtf · dưới ' . vs_max_upload_mb() . ' MB · bảo mật tài liệu',
	'icon'   => 'upload',
) );
$icons = array(
	'upload' => '<path d="M12 16V4m0 0l-4.5 4.5M12 4l4.5 4.5M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2"/>',
	'word'   => '<path d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8z"/><path d="M14 3v5h5M8.5 12l1.2 5 1.8-4 1.8 4 1.2-5"/>',
	'pdf'    => '<path d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8z"/><path d="M14 3v5h5M9 17v-4h1.5a1.2 1.2 0 010 2.4H9"/>',
);
?>
<label class="drop" data-drop>
	<input type="file" name="<?php echo esc_attr( $a['name'] ); ?>" accept="<?php echo esc_attr( $a['accept'] ); ?>">
	<span class="drop__icon" aria-hidden="true">
		<svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><?php echo $icons[ $a['icon'] ]; // phpcs:ignore ?></svg>
	</span>
	<span class="drop__text"><b><?php echo esc_html( $a['title'] ); ?></b><small><?php echo esc_html( $a['sub'] ); ?></small></span>
	<span class="drop__file" data-file-name></span>
</label>
