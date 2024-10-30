<?php defined( 'ABSPATH' ) or exit;

$tabs = array(
	'fields'     => esc_html__( 'Form Layout', 'iys-panel-wp-form' ),
	'messages'   => esc_html__( 'Messages', 'iys-panel-wp-form' ),
	'settings'   => esc_html__( 'Settings', 'iys-panel-wp-form' ),
	'appearance' => esc_html__( 'Appearance', 'iys-panel-wp-form' ),
);

/**
 * Filters the setting tabs on the "edit form" screen.
 *
 * @param array $tabs
 * @ignore
 */
$tabs = apply_filters( 'im4wp_admin_edit_form_tabs', $tabs );

?>
<div id="im4wp-admin" class="wrap im4wp-settings">

	<p class="im4wp-breadcrumbs">
		<span class="prefix"><?php echo esc_html__( 'You are here: ', 'iys-panel-wp-form' ); ?></span>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=iys-panel-wp-form' ) ); ?>">İYS Panel WP Form</a> &rsaquo;
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=iys-panel-wp-form-forms' ) ); ?>"><?php echo esc_html__( 'Forms', 'iys-panel-wp-form' ); ?></a>
		&rsaquo;
		<span class="current-crumb"><strong><?php echo esc_html__( 'Form', 'iys-panel-wp-form' ); ?> <?php echo $form_id; ?>
				| <?php echo esc_html( $form->name ); ?></strong></span>
	</p>

	<div class="im4wp-row">

		<!-- Main Content -->
		<div class="main-content im4wp-col im4wp-col-5">

			<h1>
				<?php echo esc_html__( 'Form Information', 'iys-panel-wp-form' ); ?>

				<!-- Form actions -->
				<?php

				/**
				 * @ignore
				 */
				do_action( 'im4wp_admin_edit_form_after_title' );
				?>
			</h1>
            <p><?php echo esc_html__( 'Enter the name and the description of your form. Form name will be visible to visitors on the webpage. Form description is for your use only, in order to take notes and such.', 'iys-panel-wp-form' ); ?></p>

			<h2 style="display: none;"></h2><?php // fake h2 for admin notices ?>

			<!-- Wrap entire page in <form> -->
			<form method="post">
				<?php // default submit button to prevent opening preview ?>
				<input type="submit" style="display: none;" />
				<input type="hidden" name="_im4wp_action" value="edit_form"/>
				<?php wp_nonce_field( '_im4wp_action', '_wpnonce' ); ?>
				<input type="hidden" name="im4wp_form_id" value="<?php echo esc_attr( $form->ID ); ?>"/>

				<div id="titlediv" class="im4wp-margin-s">
					<div id="titlewrap">
						<label for="title"><b><?php echo esc_html__( 'Form Name:', 'iys-panel-wp-form' ); ?></b></label>
						<input type="text" name="im4wp_form[name]" size="30"
							   value="<?php echo esc_attr( $form->name ); ?>" id="title" spellcheck="true"
							   autocomplete="off"
							   placeholder="<?php echo esc_html__( 'Enter the title of your sign-up form', 'iys-panel-wp-form' ); ?>"
							   style="line-height: initial;">
					</div>
                    
                    <div id="descriptionwrap">
						<label for="description"><b><?php echo esc_html__( 'Form Description:', 'iys-panel-wp-form' ); ?></b></label>
                        <br />
						<textarea name="im4wp_form[description]"
                                  cols="30" rows="4"
							      value="" id="description" spellcheck="true"
							      autocomplete="off"
							      placeholder=""
							      style="line-height: initial;"><?php echo esc_attr( $form->description ); ?></textarea>
					</div>
					<div>
						<?php echo sprintf( esc_html__( 'Use the shortcode %s to display this form inside a post, page or text widget.', 'iys-panel-wp-form' ), '<input type="text" onfocus="this.select();" readonly="readonly" value="' . esc_attr( sprintf( '[im4wp_form id="%d"]', $form->ID ) ) . '" size="' . ( strlen( $form->ID ) + 18 ) . '">' ); ?>
					</div>
				</div>


				<div>
					<h2 class="nav-tab-wrapper" id="im4wp-tabs-nav">
						<?php
						foreach ( $tabs as $tab => $name ) {
							$class = ( $active_tab === $tab ) ? 'nav-tab-active' : '';
							echo sprintf( '<a class="nav-tab nav-tab-%s %s" href="%s">%s</a>', $tab, $class, esc_attr( $this->tab_url( $tab ) ), $name );
						}
						?>
					</h2>

					<div id="im4wp-tabs">

						<?php

						foreach ( $tabs as $tab => $name ) :
							$class = ( $active_tab === $tab ) ? 'im4wp-tab-active' : '';

							// start of .tab
							echo sprintf( '<div class="im4wp-tab %s" id="im4wp-tab-%s">', $class, $tab );

							/**
							 * Runs when outputting a tab section on the "edit form" screen
							 *
							 * @param string $tab
							 * @ignore
							 */
							do_action( 'im4wp_admin_edit_form_output_' . $tab . '_tab', $opts, $form );

							$tab_file = __DIR__ . '/tabs/form-' . $tab . '.php';
							if ( file_exists( $tab_file ) ) {
								include $tab_file;
							}

							// end of .tab
							echo '</div>';

						endforeach; // foreach tabs
						?>

					</div><!-- / tabs -->
				</div>

			</form><!-- Entire page form wrap -->


			<?php include IM4WP_PLUGIN_DIRDIR . '/includes/views/parts/admin-footer.php'; ?>

		</div>
	</div>

</div>
