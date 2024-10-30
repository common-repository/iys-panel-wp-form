<?php
defined( 'ABSPATH' ) or exit;

/** @var IM4WP_Debug_Log $log */
/** @var IM4WP_Debug_Log_Reader $log_reader */

/**
 * @ignore
 * @param array $opts
 */
function im4wp_usage_tracking_setting( $opts ) {
	?>
	<div class="im4wp-margin-m" >
		<h3><?php echo esc_html__( 'Miscellaneous settings', 'iys-panel-wp-form' ); ?></h3>
		<table class="form-table">
			<tr>
				<th><?php echo esc_html__( 'Usage Tracking', 'iys-panel-wp-form' ); ?></th>
				<td>
					<label>
						<input type="radio" name="im4wp[allow_usage_tracking]" value="1" <?php checked( $opts['allow_usage_tracking'], 1 ); ?> />
						<?php echo esc_html__( 'Yes', 'iys-panel-wp-form' ); ?>
					</label> &nbsp;
					<label>
						<input type="radio" name="im4wp[allow_usage_tracking]" value="0" <?php checked( $opts['allow_usage_tracking'], 0 ); ?>  />
						<?php echo esc_html__( 'No', 'iys-panel-wp-form' ); ?>
					</label>

					<p class="description">
						<?php echo esc_html__( 'Allow us to anonymously track how this plugin is used to help us make it better fit your needs.', 'iys-panel-wp-form' ); ?>
						<a href="https://hermesiletisim.net/kb/what-is-usage-tracking/#utm_source=wp-plugin&utm_medium=iys-panel-wp-form&utm_campaign=settings-page" target="_blank">
							<?php echo esc_html__( 'This is what we track.', 'iys-panel-wp-form' ); ?>
						</a>
					</p>
				</td>
			</tr>
			<tr>
				<th><?php echo esc_html__( 'Logging', 'iys-panel-wp-form' ); ?></th>
				<td>
					<select name="im4wp[debug_log_level]">
						<option value="warning" <?php selected( 'warning', $opts['debug_log_level'] ); ?>><?php echo esc_html__( 'Errors & warnings only', 'iys-panel-wp-form' ); ?></option>
						<option value="debug" <?php selected( 'debug', $opts['debug_log_level'] ); ?>><?php echo esc_html__( 'Everything', 'iys-panel-wp-form' ); ?></option>
					</select>
					<p class="description">
						<?php echo sprintf( wp_kses( __( 'Determines what events should be written to <a href="%s">the debug log</a> (see below).', 'iys-panel-wp-form' ), array( 'a' => array( 'href' => array() ) ) ), 'https://hermesiletisim.net/kb/how-to-enable-log-debugging/#utm_source=wp-plugin&utm_medium=iys-panel-wp-form&utm_campaign=settings-page' ); ?>
					</p>
				</td>
			</tr>
		</table>
	</div>
	<?php
}

add_action( 'im4wp_admin_other_settings', 'im4wp_usage_tracking_setting', 70 );
?>
<div id="im4wp-admin" class="wrap im4wp-settings">

	<p class="im4wp-breadcrumbs">
		<span class="prefix"><?php echo esc_html__( 'You are here: ', 'iys-panel-wp-form' ); ?></span>
		<a href="<?php echo admin_url( 'admin.php?page=iys-panel-wp-form' ); ?>">İYS Panel WP Form</a> &rsaquo;
		<span class="current-crumb"><strong><?php echo esc_html__( 'Other Settings', 'iys-panel-wp-form' ); ?></strong></span>
	</p>


	<div class="im4wp-row">

		<!-- Main Content -->
		<div class="main-content im4wp-col im4wp-col-4">

			<h1 class="im4wp-page-title">
				<?php echo esc_html__( 'Other Settings', 'iys-panel-wp-form' ); ?>
			</h1>

			<h2 style="display: none;"></h2>
			<?php settings_errors(); ?>

			<?php
			/**
			 * @ignore
			 */
			do_action( 'im4wp_admin_before_other_settings', $opts );
			?>

			<!-- Settings -->
			<form action="<?php echo admin_url( 'options.php' ); ?>" method="post">
				<?php settings_fields( 'im4wp_settings' ); ?>

				<?php
				/**
				 * @ignore
				 */
				do_action( 'im4wp_admin_other_settings', $opts );
				?>

				<div style="margin-top: -20px;"><?php submit_button(); ?></div>
			</form>

			<!-- Debug Log -->
			<div class="im4wp-margin-m">
				<h3><?php echo esc_html__( 'Debug Log', 'iys-panel-wp-form' ); ?> <input type="text" id="debug-log-filter" class="alignright regular-text" placeholder="<?php echo esc_attr__( 'Filter..', 'iys-panel-wp-form' ); ?>" /></h3>

				<?php
				if ( ! $log->test() ) {
					echo '<p>';
					echo esc_html__( 'Log file is not writable.', 'iys-panel-wp-form' ) . ' ';
					echo sprintf( wp_kses( __( 'Please ensure %1$s has the proper <a href="%2$s">file permissions</a>.', 'iys-panel-wp-form' ), array( 'a' => array( 'href' => array() ) ) ), '<code>' . $log->file . '</code>', 'https://codex.wordpress.org/Changing_File_Permissions' );
					echo '</p>';

					// hack to hide filter input
					echo '<style type="text/css">#debug-log-filter { display: none; }</style>';
				} else {
					?>
					<div id="debug-log" class="im4wp-log widefat">
						<?php
						$line = $log_reader->read_as_html();

						if ( ! empty( $line ) ) {
							while ( is_string( $line ) ) {
								if ( ! empty( $line ) ) {
									echo '<div class="debug-log-line">' . esc_html($line) . '</div>';
								}

								$line = $log_reader->read_as_html();
							}
						} else {
							echo '<div class="debug-log-empty">';
							echo '-- ', esc_html__( 'Nothing here. Which means there are no errors!', 'iys-panel-wp-form' );
							echo '</div>';
						}
						?>
					</div>

					<form method="post">
						<input type="hidden" name="_im4wp_action" value="empty_debug_log">
						<?php wp_nonce_field( '_im4wp_action', '_wpnonce' ); ?>
						<p>
							<input type="submit" class="button" value="<?php echo esc_attr__( 'Empty Log', 'iys-panel-wp-form' ); ?>"/>
						</p>
					</form>
					<?php
				} // end if is writable

				if ( $log->level >= 300 ) {
					echo '<p>';
					echo esc_html__( 'Right now, the plugin is configured to only log errors and warnings.', 'iys-panel-wp-form' );
					echo '</p>';
				}
				?>

				<script>
					(function() {
						'use strict';
						// scroll to bottom of log
						var log = document.getElementById("debug-log"),
							logItems;
						log.scrollTop = log.scrollHeight;
						log.style.minHeight = '';
						log.style.maxHeight = '';
						log.style.height = log.clientHeight + "px";

						// add filter
						var logFilter = document.getElementById('debug-log-filter');
						logFilter.addEventListener('keydown', function(evt) {
							if(evt.keyCode === 13 ) {
								searchLog(evt.target.value.trim());
							}
						});

						// search log for query
						function searchLog(query) {
							if( ! logItems ) {
								logItems = [].map.call(log.children, function(node) {
									return node.cloneNode(true);
								})
							}

							var ri = new RegExp(query.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&"), 'i');
							var newLog = log.cloneNode();
							logItems.forEach(function(node) {
								if( ! node.textContent ) { return ; }
								if( ! query.length || ri.test(node.textContent) ) {
									newLog.appendChild(node);
								}
							});

							log.parentNode.replaceChild(newLog,log);
							log = newLog;
							log.scrollTop = log.scrollHeight;
						}
					})();
				</script>
			</div>
			<!-- / Debug Log -->
			<?php include __DIR__ . '/parts/admin-footer.php'; ?>
		</div>
	</div>

</div>

