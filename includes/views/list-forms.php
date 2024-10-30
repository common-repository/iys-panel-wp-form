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
				İYS Panel WP Form | <?php echo esc_html__( 'Forms', 'iys-panel-wp-form' ); ?>
			</h1>

			<?php

            if ( ! empty( $opts['api_key'] ) ) {
				echo '<hr />';
				include __DIR__ . '/parts/lists-overview.php';
			}

			?>
		</div>
	</div>

</div>

