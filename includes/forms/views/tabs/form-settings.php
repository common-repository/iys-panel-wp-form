<h2><?php echo esc_html__( 'Settings', 'iys-panel-wp-form' ); ?></h2>
<p><?php echo esc_html__( 'Arrange the settings regarding the form.', 'iys-panel-wp-form' ); ?><p/>

<div class="im4wp-margin-m"></div>

<table class="form-table" style="table-layout: fixed;">

	<?php
	/** @ignore */
	do_action( 'im4wp_admin_form_after_iyspanel_settings_rows', $opts, $form );
	?>

	<tr valign="top">
		<th scope="row"><?php echo esc_html__( 'Choose Opt-in Method:', 'iys-panel-wp-form' ); ?></th>
		<td class="nowrap">
			<label>
				<input type="radio"  name="im4wp_form[settings][double_optin]" value="1" <?php checked( $opts['double_optin'], 1 ); ?> />&rlm;
				<?php echo esc_html__( 'Double Opt-in', 'iys-panel-wp-form' ); ?>
			</label> &nbsp;
			<label>
				<input type="radio" name="im4wp_form[settings][double_optin]" value="0" <?php checked( $opts['double_optin'], 0 ); ?> onclick="return confirm('<?php echo esc_attr__( 'Are you sure you want to disable double opt-in?', 'iys-panel-wp-form' ); ?>');" />&rlm;
				<?php echo esc_html__( 'Single Opt-in', 'iys-panel-wp-form' ); ?>
			</label>
			<p class="description"><?php echo esc_html__( 'With double opt-in, the confirmation of the submitted information is asked via email or SMS.', 'iys-panel-wp-form' ); ?></p>
		</td>
	</tr>

	<?php
	/** @ignore */
	do_action( 'im4wp_admin_form_before_behaviour_settings_rows', $opts, $form );
	?>

	<tr valign="top">
		<th scope="row"><?php echo esc_html__( 'Hide Form After Sign Up?', 'iys-panel-wp-form' ); ?></th>
		<td class="nowrap">
			<label>
				<input type="radio" name="im4wp_form[settings][hide_after_success]" value="1" <?php checked( $opts['hide_after_success'], 1 ); ?> />&rlm;
				<?php echo esc_html__( 'Yes', 'iys-panel-wp-form' ); ?>
			</label> &nbsp;
			<label>
				<input type="radio" name="im4wp_form[settings][hide_after_success]" value="0" <?php checked( $opts['hide_after_success'], 0 ); ?> />&rlm;
				<?php echo esc_html__( 'No', 'iys-panel-wp-form' ); ?>
			</label>
			<p class="description">
				<?php echo esc_html__( 'Select "yes" to hide the form fields after a successful sign-up.', 'iys-panel-wp-form' ); ?>
			</p>
		</td>
	</tr>

    <tr valign="top">
		<th scope="row"><?php echo esc_html__( 'Select Form Language', 'iys-panel-wp-form' ); ?></th>
		<td class="nowrap">

            <select name="im4wp_form[settings][lang]">
                <option disabled selected value><?php echo esc_html__( '--select--', 'iys-panel-wp-form' ); ?></option>
				<option value="tr" <?php selected( 'tr' == $opts['lang'], true ); ?>><?php echo esc_html__( 'Turkish', 'iys-panel-wp-form' ); ?></option>
				<option value="en" <?php selected( 'en' == $opts['lang'], true ); ?>><?php echo esc_html__( 'English', 'iys-panel-wp-form' ); ?></option>
            </select>
			<p class="description">
				<?php echo esc_html__( 'In what language visitors will see the form.', 'iys-panel-wp-form' ); ?>
			</p>
		</td>
	</tr>

    <tr valign="top">
		<th scope="row"><label for="im4wp_form_redirect"><?php echo esc_html__( 'Redirect to a selected URL after submission', 'iys-panel-wp-form' ); ?></label></th>
		<td>
			<input type="text" class="widefat" name="im4wp_form[settings][redirect]" id="im4wp_form_redirect" placeholder="<?php echo sprintf( esc_attr__( 'Example: %s', 'iys-panel-wp-form' ), esc_attr( site_url( '/thank-you/' ) ) ); ?>" value="<?php echo esc_attr( $opts['redirect'] ); ?>" />
			<p class="description">
				<?php echo wp_kses( __( 'Leave blank if you do not want users to be redirected. Otherwise, enter the full URL including <code>https://</code> for the redirect.', 'iys-panel-wp-form' ), array( 'code' => array() ) ); ?>
			</p>
		</td>
	</tr>

    <tr valign="top">
		<th scope="row"><label for="im4wp_form_redirect_after_double_optin"><?php echo esc_html__( 'Redirect to a selected URL after double opt-in email confirmation.', 'iys-panel-wp-form' ); ?></label></th>
		<td>
			<input type="text" class="widefat" name="im4wp_form[settings][redirect_after_double_optin]" id="im4wp_form_redirect_after_double_optin" placeholder="<?php echo sprintf( esc_attr__( 'Example: %s', 'iys-panel-wp-form' ), esc_attr( site_url( '/thank-you/' ) ) ); ?>" value="<?php echo esc_attr( $opts['redirect_after_double_optin'] ); ?>" />
            <p class="description">
				<?php echo wp_kses( __( 'Visitor will be redirected to this site after clicking the confirm button on the double opt-in email.', 'iys-panel-wp-form' ), array( 'code' => array() ) ); ?>
			</p>
			<p class="description">
				<?php echo wp_kses( __( 'Leave blank if you do not want users to be redirected. Otherwise, enter the full URL including <code>https://</code> for the redirect.', 'iys-panel-wp-form' ), array( 'code' => array() ) ); ?>
			</p>
		</td>
	</tr>

	<?php
	/** @ignore */
	do_action( 'im4wp_admin_form_after_behaviour_settings_rows', $opts, $form );
	?>

	<tr valign="top">
		<th scope="row" style="width: 250px;"><?php echo esc_html__( 'Associated İletişim Makinesi Group:', 'iys-panel-wp-form' ); ?></th>
		<?php
		// loop through lists
		if ( empty( $lists ) ) {
			?>
			<td colspan="2"><?php echo sprintf( wp_kses( __( 'No group found, <a href="%s">are you connected</a>?', 'iys-panel-wp-form' ), array( 'a' => array( 'href' => array() ) ) ), admin_url( 'admin.php?page=iys-panel-wp-form' ) ); ?></td>
			<?php
		} else {
			?>
			<td >

				<ul id="im4wp-lists" style="margin-bottom: 20px; max-height: 300px; overflow-y: auto;">
					<?php
					foreach ( $lists as $list ) {
						?>
						<li>
							<label>
								<input class="im4wp-list-input" type="checkbox" name="im4wp_form[settings][lists][]" value="<?php echo esc_attr( $list->id ); ?>" <?php checked( in_array( $list->id, $opts['lists'] ), true ); ?>> <?php echo esc_html( $list->name ); ?>
							</label>
						</li>
						<?php
					}
					?>
				</ul>
				<p class="description"><?php echo esc_html__( 'Users who sign up via this form will be added to the group selected above.', 'iys-panel-wp-form' ); ?></p>
			</td>
			<?php
		}
		?>

	</tr>

    <tr valign="top">
		<th scope="row" style="width: 250px;"><?php echo esc_html__( 'Associated İYS Panel Brand:', 'iys-panel-wp-form' ); ?></th>
		<?php
		// loop through lists
		if ( empty( $brands ) ) {
			?>
			<td colspan="2"><?php echo sprintf( wp_kses( __( 'No brands found, <a href="%s">are you connected</a>?', 'iys-panel-wp-form' ), array( 'a' => array( 'href' => array() ) ) ), admin_url( 'admin.php?page=iys-panel-wp-form' ) ); ?></td>
			<?php
		} else {
			?>
			<td >

				<ul id="im4wp-brands" style="margin-bottom: 20px; max-height: 300px; overflow-y: auto;">
					<?php
					foreach ( $brands as $brand ) {
						?>
						<li>
							<label>
								<input class="im4wp-brand-input" type="checkbox" name="im4wp_form[settings][brands][]" value="<?php echo esc_attr( $brand->id ); ?>" <?php checked( in_array( $brand->id, $opts['brands'] ), true ); ?>> <?php echo esc_html( $brand->name ); ?>
							</label>
						</li>
						<?php
					}
					?>
				</ul>
				<p class="description"><?php echo esc_html__( 'Choose to which of your brands on İYS Panel will the communication consents of the filled out forms will be transferred.', 'iys-panel-wp-form' ); ?></p>
			</td>
		<?php
		}
		?>

    </tr>

    <tr valign="top">
		<th scope="row" style="width: 250px;"></th>
		<td >
			<p class="description">
                <?php echo esc_html__( 'If there is only email address field on the form then double opt-in is done via email.', 'iys-panel-wp-form' ); ?><br />
                <?php echo esc_html__( 'If there is both email address and GSM fields on the form then double opt-in is done via email.', 'iys-panel-wp-form' ); ?><br />
                <?php echo esc_html__( 'If there is only GSM field on the form then double opt-in is done via SMS (text message).', 'iys-panel-wp-form' ); ?><br />
                <?php echo esc_html__( 'Given the double opt-in will be done via SMS, you should select the SMS sender name that is registered on İletişim Makinesi to enable the delivery of double opt-in messages. The SMS sender names are listed according to the brand you have chosen previously.', 'iys-panel-wp-form' ); ?><br />

                <b><?php echo esc_html__( 'Given the double opt-in will be done via Email or you have selected the single opt-in, then you must choose "SMS will not be used." option from the drop-down list.', 'iys-panel-wp-form' ); ?></b>
            </p>
		</td>
    </tr>

    <tr valign="top">
        <th scope="row" style="width: 250px;"><?php echo esc_html__( 'Choose SMS sender name:', 'iys-panel-wp-form' ); ?></th>
        <td >
            <select name="im4wp_form[settings][originator]"></select>
		</td>
    </tr>
</table>

<script>

 jQuery(document).ready(function($) {

     function populateOriginators(originators_with_brand, selected) {

         var originators = [];
         var boxes = $('.im4wp-brand-input');
         for(var i = 0; i < boxes.length; i++) {
             if(boxes[i].checked) {
                 originators = originators.concat(originators_with_brand[boxes[i].value]);
             }
         }

         $("select[name='im4wp_form[settings][originator]'] option").remove();
         var originatorInput = $("select[name='im4wp_form[settings][originator]']");

         var op = '<option value="" selected="selected"><?php echo esc_html__( 'SMS will not be used', 'iys-panel-wp-form' ); ?></option>';
         $(op).appendTo(originatorInput);

         for (var i = 0; i < originators.length; i++) {
             var val = originators[i].id;
             if (originators[i].payment_profile_id) {
                 val = val + '_' + originators[i].payment_profile_id;
             }

             if (val == selected) {
                 var op = '<option value="' + val + '" selected="selected">' + originators[i].name + '</option>';
             } else {
                 var op = '<option value="' + val + '">' + originators[i].name + '</option>';
             }

             $(op).appendTo(originatorInput);
         }
     }

     <?php
     $bb = array();
     foreach($brands as $brand) {
         $oo = array();
         foreach ($brand->originators as $originator) {
             if (!in_array($originator->service, array('SMS', 'VDF_SMS', 'TT_SMS', 'SOL_SMS', 'VDF_T_SMS', 'TT_NET_SMS'))) continue;
             $oo[] = $originator;
         }
         $bb["$brand->id"] = $oo;
     }
     ?>

     var originators = <?php echo(json_encode($bb)); ?>;
     var selected = '<?php echo esc_html($opts['originator']) ?>';

     var ad_condition = $("select[name='im4wp_form[settings][brands][]']");


     $(document).on('change', '.im4wp-brand-input', function() {
         populateOriginators(originators, selected);
     });

     populateOriginators(originators, selected);
 });
</script>
<?php submit_button(); ?>
