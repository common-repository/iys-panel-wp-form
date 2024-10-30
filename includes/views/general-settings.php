<?php
defined( 'ABSPATH' ) or exit;
?>
<div id="im4wp-admin" class="wrap im4wp-settings">

	<p class="im4wp-breadcrumbs">
		<span class="prefix"><?php echo esc_html__( 'You are here: ', 'iys-panel-wp-form' ); ?></span>
		<span class="current-crumb"><strong>İYS Panel WP Form</strong></span>
	</p>


	<div class="im4wp-row">

		<!-- Main Content -->
		<div class="main-content im4wp-col im4wp-col-4">

			<h1 class="im4wp-page-title">
                İYS Panel WP Form | <?php echo esc_html__( 'Connections', 'iys-panel-wp-form' ); ?>
			</h1>

            <!-- FIXME: Cevirisini ekle -->
            <p><?php echo esc_html__( 'Connect the plug-in with your İYS Panel and İletişim Makinesi accounts to get your data from the forms transferred to these systems.', 'iys-panel-wp-form' ); ?></p>

            <?php
			if ( !$connected ) {
			?>
                <div id="purchase">
                    <hr />
                    <?php
                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['view']) && $_GET['view'] == 'purchase') {
                        $purchase_form_name    = sanitize_text_field($_POST['name']);
                        $purchase_form_email   = sanitize_email($_POST['email']);
                        $purchase_form_gsm     = sanitize_text_field($_POST['gsm']);
                        $purchase_form_message = sanitize_text_field($_POST['message']);

                        $url = 'https://iyspanel.com/hgf/iyspanel-wp-purchase';

                        $args   = array(
			                'method'    => 'POST',
			                'timeout'   => 20,
			                'sslverify' => apply_filters( 'im4wp_use_sslverify', true ),
		                );

                        $data = array(
                            'name' => $purchase_form_name,
                            'email' => $purchase_form_email,
                            'gsm' => $purchase_form_gsm,
                            'message' => $purchase_form_message
                        );

				        $args['headers']['Content-Type'] = 'application/x-www-form-urlencoded';
				        $args['body']                    = http_build_query($data);;
		                $response = wp_remote_request( $url, $args );

                        $code    = (int) wp_remote_retrieve_response_code( $response );
		                $message = wp_remote_retrieve_response_message( $response );
		                $body    = wp_remote_retrieve_body( $response );

		                if ( $code < 300 ) {
                            $jsonData = json_decode( $body );
                            if ($jsonData->status == 'OK') {
                    ?>
                        <p><?php echo esc_html__( 'Your message has been received. We will respond soon!', 'iys-panel-wp-form' ); ?></p>
                        <h4><?php echo esc_html__( 'Informations:', 'iys-panel-wp-form' ); ?></h4>
                        <ul style="list-style-type: disc; margin-left: 24px;">
                            <li><?php echo(esc_html($purchase_form_name)); ?></li>
                            <li><?php echo(esc_html($purchase_form_email)); ?></li>
                            <li><?php echo(esc_html($purchase_form_gsm)); ?></li>
                            <li><?php echo(esc_html($purchase_form_message)); ?></li>
                        </ul>
                        <hr />
                    <?php
                    } else {
                    ?>
                        <p><b><?php echo esc_html__( 'There is an error. Your informations can not be sent!', 'iys-panel-wp-form' ); ?></b></p>
                    <?php
                    }
                    } else {
                    ?>
                        <p><b><?php echo esc_html__( 'There is an error. Your informations can not be sent!', 'iys-panel-wp-form' ); ?></b></p>
                    <?php
                    }
                    }
                    ?>

                    <p><?php echo esc_html__( 'You have to make the payment to use "İYS Panel WP Form" plug-in.', 'iys-panel-wp-form' ); ?></p>
                    <p><?php echo esc_html__( 'You can start enjoying the benefits of using "İYS Panel WP Form" plug-in for a reasonable annual payment.', 'iys-panel-wp-form' ); ?></p>
                    <h4><?php echo esc_html__( 'With "İYS Panel WP Form" plug-in you can;', 'iys-panel-wp-form' ); ?></h4>
                    <ul style="list-style-type: disc; margin-left: 24px;">
                        <li><?php echo esc_html__( 'Easily create forms for your website.', 'iys-panel-wp-form' ); ?></li>
                        <li><?php echo esc_html__( 'Use double opt-in to confirm the information gathered via forms.', 'iys-panel-wp-form' ); ?></li>
                        <li><?php echo esc_html__( 'Transfer the information gathered in the forms such as email address and gsm number automatically to a designated group in İletişim Makinesi.', 'iys-panel-wp-form' ); ?></li>
                        <li><?php echo esc_html__( 'Get communication permission from your visitors via forms and transfer that information to the national consent database via İYS Panel APIs.', 'iys-panel-wp-form' ); ?></li>
                        <li><?php echo esc_html__( 'You get rid of the long development process and expense in order to integrate your forms with Commercial İYS (Electronic Message Management System)', 'iys-panel-wp-form' ); ?></li>
                    </ul>
                    <a href="#" id="showapikey"><?php echo esc_html__( 'I\'ve made my purchase', 'iys-panel-wp-form' ); ?></a>
                    <a href="#TB_inline?width=0&height=0&inlineId=purchase-popup" class="thickbox button-primary" style="margin-left: 40px;"><?php echo esc_html__( 'Purchase', 'iys-panel-wp-form' ); ?></a>
                </div>

                <?php add_thickbox(); ?>
                <div id="purchase-popup" style="display: none;">
			        <form action="<?php echo admin_url( 'admin.php?page=iys-panel-wp-form&view=purchase' ); ?>" method="post">
				        <table class="form-table">
					        <tr valign="top">
						        <td>
							        <input type="text" class="widefat" placeholder="<?php echo esc_html__( 'Name Surname', 'iys-panel-wp-form' ); ?>" id="name" name="name" />
						        </td>
					        </tr>
                            <tr valign="top">
						        <td>
							        <input type="text" class="widefat" placeholder="<?php echo esc_html__( 'Email Address', 'iys-panel-wp-form' ); ?>" id="email" name="email" />
						        </td>
					        </tr>
                            <tr valign="top">
						        <td>
							        <input type="text" class="widefat" placeholder="<?php echo esc_html__( 'GSM', 'iys-panel-wp-form' ); ?>" id="gsm" name="gsm" />
						        </td>
					        </tr>
                            <tr valign="top">
						        <td>
							        <input type="text" class="widefat" placeholder="<?php echo esc_html__( 'Message', 'iys-panel-wp-form' ); ?>" id="message" name="message" />
						        </td>
					        </tr>
                            <tr valign="top">
						        <td>
                                    <?php submit_button($text=esc_html__( 'Call Me!', 'iys-panel-wp-form' )); ?>
						        </td>
					        </tr>
				        </table>

			        </form>
                </div>
                <hr />
            <?php
            }
            ?>

            <div id="apikeydiv" <?php if (!$connected) echo 'style="display: none;"'?>>
            <a id="go-api-key" />
            <h1><?php echo esc_html__( 'Connect via API Key', 'iys-panel-wp-form' ); ?></h1>

			<h2 style="display: none;"></h2>
			<?php
			settings_errors();
			$this->messages->show();
			?>

			<form action="<?php echo admin_url( 'options.php' ); ?>" method="post">
				<?php settings_fields( 'im4wp_settings' ); ?>
                <input type="hidden" id="custom_option" name="im4wp[connection_cancelled]" value="false" />

				<table class="form-table">

					<tr valign="top">
						<th scope="row">
							<?php echo esc_html__( 'Status', 'iys-panel-wp-form' ); ?>
						</th>
						<td>
							<?php
							if ( $connected ) {
							?>
							    <span class="im4wp-status positive"><?php echo esc_html__( 'Connected', 'iys-panel-wp-form' ); ?></span>
                            <?php
                            } else if ( $connected == 'failed' ) {
							?>
								<span class="im4wp-status neutral"><?php echo esc_html__( 'Connection Failed', 'iys-panel-wp-form' ); ?></span>
                                <p><?php echo esc_html__( 'A problem had occurred with your connection. Get in touch with our tech support. 0850 483 04 44', 'iys-panel-wp-form' ); ?></p>
							<?php
                            } else if ( !$connected && isset($opts['connection_cancelled']) && $opts['connection_cancelled'] ) {
							?>
								<span class="im4wp-status negative"><?php echo esc_html__( 'Connection Cancelled', 'iys-panel-wp-form' ); ?></span>
                                <p><?php echo esc_html__( 'A problem had occurred with your connection. Get in touch with our tech support. 0850 483 04 44', 'iys-panel-wp-form' ); ?></p>
							<?php
							} else {
							?>
								<span class="im4wp-status neutral"><?php echo esc_html__( 'Not Connected', 'iys-panel-wp-form' ); ?></span>
							<?php
							}
							?>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row"><label for="iyspanel_api_key"><?php echo esc_html__( 'API Key', 'iys-panel-wp-form' ); ?></label></th>
						<td>
							<input type="text" class="widefat" placeholder="<?php echo esc_html__( 'Your İYS Panel API key', 'iys-panel-wp-form' ); ?>" id="iyspanel_api_key" name="im4wp[api_key]" value="<?php echo esc_attr( $obfuscated_api_key ); ?>" />
							<p class="description">
							    <?php echo esc_html__( 'If you did not receive your API Key or having another problem with theactivation, please get in touch with our tech support. 0850 483 0444', 'iys-panel-wp-form' ); ?>
							</p>
						</td>

					</tr>

				</table>

				<?php submit_button(); ?>

			</form>


            <?php
			if ( $connected ) {
			?>
                <form action="<?php echo admin_url( 'options.php' ); ?>" method="post">
				    <?php settings_fields( 'im4wp_settings' ); ?>
					<input type="hidden" id="iyspanel_api_key" name="im4wp[api_key]" value="" />
					<input type="hidden" id="custom_option" name="im4wp[connection_cancelled]" value="true" />
                    <?php submit_button($text=esc_html__( 'Cancel Connection', 'iys-panel-wp-form' ), $other_attributes=array('is-destructive')); ?>
			    </form>
            <?php
			}
			?>
			<?php

			/**
			 * Runs right after general settings are outputted in admin.
			 *
			 * @since 3.0
			 * @ignore
			 */
			do_action( 'im4wp_admin_after_general_settings' );

			// if ( ! empty( $opts['api_key'] ) ) {
			// 	echo '<hr />';
			// 	include __DIR__ . '/parts/lists-overview.php';
			// }

			// include __DIR__ . '/parts/admin-footer.php';

			?>
            </div>
		</div>
	</div>

</div>
<script>
 jQuery(document).ready(function($) {
     $("#showapikey").click(function(event){
         $("#purchase").hide();
         $("#apikeydiv").show();
     });
 });
</script>
