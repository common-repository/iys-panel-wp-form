<?php defined( 'ABSPATH' ) or exit; ?>
<div id="im4wp-admin" class="wrap im4wp-settings">

	<div class="im4wp-row">

		<!-- Main Content -->
		<div class="main-content im4wp-col im4wp-col-4">

			<h1 class="im4wp-page-title">
				<?php echo esc_html__( 'Add new form', 'iys-panel-wp-form' ); ?>
			</h1>

			<h2 style="display: none;"></h2><?php // fake h2 for admin notices ?>

			<div style="max-width: 480px;">

				<!-- Wrap entire page in <form> -->
				<form method="post">

					<input type="hidden" name="_im4wp_action" value="add_form" />
					<?php wp_nonce_field('_im4wp_action', '_wpnonce' ); ?>

					<div class="im4wp-margin-s">
						<h3>
							<label>
								<?php echo esc_html__( 'What is the name of this form?', 'iys-panel-wp-form' ); ?>
							</label>
						</h3>
						<input type="text" name="im4wp_form[name]" class="widefat" value="" spellcheck="true" autocomplete="off" placeholder="<?php echo esc_attr__( 'Enter your form title..', 'iys-panel-wp-form' ); ?>">
					</div>

					<div class="im4wp-margin-s" style="height: 300px; overflow: auto"> 

						<h3>
							<label>
								<?php echo esc_html__( 'Associated İletişim Makinesi Group:', 'iys-panel-wp-form' ); ?>
							</label>
						</h3>

						<?php
						if ( ! empty( $lists ) ) {
							?>
						<ul id="im4wp-lists">
							<?php
							foreach ( $lists as $list ) {
								?>
								<li>
									<label>
										<input type="checkbox" name="im4wp_form[settings][lists][<?php echo esc_attr( $list->id ); ?>]" value="<?php echo esc_attr( $list->id ); ?>" <?php checked( $number_of_lists, 1 ); ?> >
										<?php echo esc_html( $list->name ); ?>
									</label>
								</li>
								<?php
							}
							?>
						</ul>
							<?php
						} else {
							?>
						<p class="im4wp-notice">
							<?php echo sprintf( wp_kses( __( 'No lists found. Did you <a href="%s">connect with İYS Panel</a>?', 'iys-panel-wp-form' ), array( 'a' => array( 'href' => array() ) ) ), admin_url( 'admin.php?page=iys-panel-wp-form' ) ); ?>
						</p>
							<?php
						}
						?>

					</div>

					<?php submit_button( esc_html__( 'Add new form', 'iys-panel-wp-form' ) ); ?>


				</form><!-- Entire page form wrap -->

			</div>


			<?php include IM4WP_PLUGIN_DIRDIR . '/includes/views/parts/admin-footer.php'; ?>

		</div><!-- / Main content -->
	</div>

</div>
