<?php add_thickbox(); ?>

<div class="alignright">
	<a href="#TB_inline?width=0&height=550&inlineId=im4wp-form-variables" class="thickbox button-secondary">
		<span class="dashicons dashicons-info"></span>
		<?php echo esc_html__( 'Form variables', 'iys-panel-wp-form' ); ?>
	</a>
</div>
<h2><?php echo esc_html__( 'Form Layout', 'iys-panel-wp-form' ); ?></h2>

<!-- Placeholder for the field wizard -->
<div id="im4wp-field-wizard"></div>

<div class="im4wp-form-markup-wrap">
	<div class="im4wp-form-editor-wrap">
		<h4 style="margin: 0"><?php echo esc_html__( 'Form code', 'iys-panel-wp-form' ); ?> <span style="visibility: hidden;" class="dashicons dashicons-editor-help"></span></h4>
		<!-- Textarea for the actual form content HTML -->
		<textarea class="widefat" cols="160" rows="20" id="im4wp-form-content" name="im4wp_form[content]" placeholder="<?php echo esc_attr__( 'Enter the HTML code for your form fields..', 'iys-panel-wp-form' ); ?>" autocomplete="false" autocorrect="false" autocapitalize="false" spellcheck="false"><?php echo htmlspecialchars( $form->content, ENT_QUOTES, get_option( 'blog_charset' ) ); ?></textarea>
	</div>
	<div class="im4wp-form-preview-wrap">
		<h4 style="margin: 0;">
			<?php echo esc_html__( 'Form preview', 'iys-panel-wp-form' ); ?>
			<span class="dashicons dashicons-editor-help" title="<?php echo esc_attr__( 'The form may look slightly different than this when shown in a post, page or widget area.', 'iys-panel-wp-form' ); ?>"></span>
		</h4>
		<iframe id="im4wp-form-preview" src="<?php echo esc_attr( $form_preview_url ); ?>"></iframe>
	</div>
</div>


<!-- This field is updated by JavaScript as the form content changes -->
<input type="hidden" id="required-fields" name="im4wp_form[settings][required_fields]" value="<?php echo esc_attr( $form->settings['required_fields'] ); ?>" />

<?php submit_button(); ?>

<p class="im4wp-form-usage"><?php printf( esc_html__( 'Use the shortcode %s to display this form inside a post, page or text widget.', 'iys-panel-wp-form' ), '<input type="text" onfocus="this.select();" readonly="readonly" value="' . esc_attr( sprintf( '[im4wp_form id="%d"]', $form->ID ) ) . '" size="' . ( strlen( $form->ID ) + 18 ) . '">' ); ?></p>


<?php // Content for Thickboxes ?>
<div id="im4wp-form-variables" style="display: none;">
	<?php include __DIR__ . '/../parts/dynamic-content-tags.php'; ?>
</div>
