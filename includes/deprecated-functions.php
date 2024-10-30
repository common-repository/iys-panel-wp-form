<?php

/**
 * @use im4wp_add_name_merge_vars()
 * @deprecated 4.0
 * @ignore
 *
 * @param array $merge_vars
 * @return array
 */
function im4wp_guess_merge_vars( $merge_vars = array() ) {
	_deprecated_function( __FUNCTION__, 'İYS Panel WP Form v4.0' );
	$merge_vars = im4wp_add_name_data( $merge_vars );
	$merge_vars = im4wp_update_groupings_data( $merge_vars );
	return $merge_vars;
}

/**
 * Echoes a sign-up checkbox.
 *
 * @ignore
 * @deprecated 3.0
 *
 * @use im4wp_get_integration()
 */
function im4wp_checkbox() {
	_deprecated_function( __FUNCTION__, 'İYS Panel WP Form v3.0' );
	im4wp_get_integration( 'wp-comment-form' )->output_checkbox();
}

/**
 * Echoes a İYS Panel WP Form form
 *
 * @ignore
 * @deprecated 3.0
 * @use im4wp_show_form()
 *
 * @param int $id
 * @param array $attributes
 *
 * @return string
 *
 */
function im4wp_form( $id = 0, $attributes = array() ) {
	_deprecated_function( __FUNCTION__, 'İYS Panel WP Form v3.0', 'im4wp_show_form' );
	return im4wp_show_form( $id, $attributes );
}

/**
 * @deprecated 4.1.12
 * @return string
 */
function im4wp_get_current_url() {
	return $im4wp_get_current_url();
}
