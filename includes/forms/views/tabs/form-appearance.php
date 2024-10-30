<?php

$theme       = wp_get_theme();
$css_options = array(
	'0'                                     => sprintf( esc_html__( 'Inherit from %s theme', 'iys-panel-wp-form' ), $theme->Name ),
	'basic'                                 => esc_html__( 'Basic', 'iys-panel-wp-form' ),
	esc_html__( 'Form Themes', 'iys-panel-wp-form' ) => array(
		'theme-light' => esc_html__( 'Light Theme', 'iys-panel-wp-form' ),
		'theme-dark'  => esc_html__( 'Dark Theme', 'iys-panel-wp-form' ),
		'theme-red'   => esc_html__( 'Red Theme', 'iys-panel-wp-form' ),
		'theme-green' => esc_html__( 'Green Theme', 'iys-panel-wp-form' ),
		'theme-blue'  => esc_html__( 'Blue Theme', 'iys-panel-wp-form' ),
	),
);

/**
 * Filters the <option>'s in the "CSS Stylesheet" <select> box.
 *
 * @ignore
 */
$css_options = apply_filters( 'im4wp_admin_form_css_options', $css_options );

?>

<h2><?php echo esc_html__( 'Form Appearance', 'iys-panel-wp-form' ); ?></h2>

<table class="form-table">
	<tr valign="top">
		<th scope="row"><label for="im4wp_load_stylesheet_select"><?php echo esc_html__( 'Form Style', 'iys-panel-wp-form' ); ?></label></th>
		<td class="nowrap valigntop">
			<select name="im4wp_form[settings][css]" id="im4wp_load_stylesheet_select">

				<?php
				foreach ( $css_options as $key => $option ) {
					if ( is_array( $option ) ) {
						$label   = $key;
						$options = $option;
						printf( '<optgroup label="%s">', $label );
						foreach ( $options as $key => $option ) {
							printf( '<option value="%s" %s>%s</option>', $key, selected( $opts['css'], $key, false ), $option );
						}
						print( '</optgroup>' );
					} else {
						printf( '<option value="%s" %s>%s</option>', $key, selected( $opts['css'], $key, false ), $option );
					}
				}
				?>
			</select>
			<p class="description">
				<?php echo esc_html__( 'If you want to load some default CSS styles, select "basic formatting styles" or choose one of the color themes', 'iys-panel-wp-form' ); ?>
			</p>
		</td>
	</tr>

	<?php
	/** @ignore */
	do_action( 'im4wp_admin_form_after_appearance_settings_rows', $opts, $form );
	?>

</table>

<?php submit_button(); ?>
