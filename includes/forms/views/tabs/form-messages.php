<?php defined( 'ABSPATH' ) or exit;

/** @var IM4WP_Form $form */
?>

<h2><?php echo esc_html__( 'Form Messages', 'iys-panel-wp-form' ); ?></h2>
<p><?php echo esc_html__( 'Customize the response messages for this form.', 'iys-panel-wp-form' ); ?></p>

<table class="form-table im4wp-form-messages">

	<?php
	/** @ignore */
	do_action( 'im4wp_admin_form_before_messages_settings_rows', $opts, $form );
	?>

	<tr valign="top">
		<th scope="row"><label for="im4wp_form_subscribed"><?php echo esc_html__( 'Successfully subscribed', 'iys-panel-wp-form' ); ?></label></th>
		<td>
			<input type="text" class="widefat" id="im4wp_form_subscribed" name="im4wp_form[messages][subscribed]" value="<?php echo esc_attr( $form->messages['subscribed'] ); ?>" />
			<p class="description"><?php echo esc_html__( 'The text that shows when an email address is successfully subscribed to the selected list(s).', 'iys-panel-wp-form' ); ?></p>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><label for="im4wp_form_invalid_email"><?php echo esc_html__( 'Invalid email address', 'iys-panel-wp-form' ); ?></label></th>
		<td>
			<input type="text" class="widefat" id="im4wp_form_invalid_email" name="im4wp_form[messages][invalid_email]" value="<?php echo esc_attr( $form->messages['invalid_email'] ); ?>" required />
			<p class="description"><?php echo esc_html__( 'The text that shows when an invalid email address is given.', 'iys-panel-wp-form' ); ?></p>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><label for="im4wp_form_required_field_missing"><?php echo esc_html__( 'Required field missing', 'iys-panel-wp-form' ); ?></label></th>
		<td>
			<input type="text" class="widefat" id="im4wp_form_required_field_missing" name="im4wp_form[messages][required_field_missing]" value="<?php echo esc_attr( $form->messages['required_field_missing'] ); ?>" required />
			<p class="description"><?php echo esc_html__( 'The text that shows when a required field for the selected list(s) is missing.', 'iys-panel-wp-form' ); ?></p>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><label for="im4wp_form_error"><?php echo esc_html__( 'General error', 'iys-panel-wp-form' ); ?></label></th>
		<td>
			<input type="text" class="widefat" id="im4wp_form_error" name="im4wp_form[messages][error]" value="<?php echo esc_attr( $form->messages['error'] ); ?>" required />
			<p class="description"><?php echo esc_html__( 'The text that shows when a general error occured.', 'iys-panel-wp-form' ); ?></p>
		</td>
	</tr>

	<?php
	/** @ignore */
	do_action( 'im4wp_admin_form_after_messages_settings_rows', array(), $form );
	?>

</table>

<?php submit_button(); ?>
