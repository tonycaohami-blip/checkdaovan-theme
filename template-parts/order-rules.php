<?php
/**
 * Quy định file tải lên (hiện dưới ô tải file).
 * $args['reduce'] = true: form giảm tỷ lệ (bản gốc .docx + báo cáo .pdf).
 */
$reduce = ! empty( $args['reduce'] );
?>
<ul class="file-rules">
	<li><span class="file-rules__k">Định dạng</span><span><?php echo $reduce ? 'Bản gốc <b>.docx</b> · báo cáo <b>.pdf</b>' : '<b>.docx</b>, <b>.pdf</b>, <b>.txt</b>, <b>.rtf</b>'; ?></span></li>
	<li><span class="file-rules__k">Dung lượng</span><span>Mỗi file <b>dưới <?php echo (int) vs_max_upload_mb(); ?> MB</b></span></li>
	<?php if ( ! $reduce ) : ?>
		<li data-word-rule><span class="file-rules__k">Số từ</span><span><b>Turnitin</b>, <b>iThenticate</b>: <b class="file-rules__req">tối thiểu <?php echo (int) VS_MIN_WORDS; ?> từ</b> · tối đa <?php echo esc_html( number_format( VS_MAX_WORDS, 0, ',', '.' ) ); ?> từ. Các phần mềm khác: không giới hạn số từ.</span></li>
	<?php else : ?>
		<li><span class="file-rules__k">Số từ</span><span>Không giới hạn số từ</span></li>
	<?php endif; ?>
	<?php if ( ! $reduce ) : ?>
		<li class="file-rules__lang" data-ai-lang><span class="file-rules__k">Lưu ý AI</span><span><b>Turnitin</b> và <b>iThenticate</b> chỉ kiểm tra tỷ lệ AI với tài liệu <b>tiếng Anh, tiếng Tây Ban Nha, tiếng Nhật</b>. Bài tiếng Việt nên chọn Originality.ai, GPTZero hoặc Copyleaks để kiểm tra AI.</span></li>
	<?php endif; ?>
</ul>
