<?php

/**
 * Returns a Form instance
 *
 * @access public
 *
 * @param int|WP_Post $form_id.
 *
 * @return IM4WP_Form
 */
function im4wp_get_form( $form_id = 0 ) {
	return IM4WP_Form::get_instance( $form_id );
}

/**
 * Get an array of Form instances
 *
 * @access public
 *
 * @param array $args Array of parameters
 *
 * @return IM4WP_Form[]
 */
function im4wp_get_forms( array $args = array() ) {
	// parse function arguments
	$default_args      = array(
		'post_status'         => 'publish',
		'posts_per_page'      => -1,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	);
	$args              = array_merge( $default_args, $args );

	// set post_type here so it can't be overwritten using function arguments
	$args['post_type'] = 'im4wp-form';

	$q                 = new WP_Query();
	$posts             = $q->query( $args );
	$forms = array();
	foreach ( $posts as $post ) {
		try {
			$form = im4wp_get_form( $post );
		} catch ( Exception $e ) {
			continue;
		}

		$forms[] = $form;
	}
	return $forms;
}

/**
 * Echoes the given form
 *
 * @access public
 *
 * @param int $form_id
 * @param array $config
 * @param bool $echo
 *
 * @return string
 */
function im4wp_show_form( $form_id = 0, $config = array(), $echo = true ) {
	/** @var IM4WP_Form_Manager $forms */
	$forms = im4wp( 'forms' );
	return $forms->output_form( $form_id, $config, $echo );
}

/**
 * Check whether a form was submitted
 *
 * @ignore
 * @since 2.3.8
 * @deprecated 3.0
 * @use im4wp_get_form
 *
 * @param int $form_id The ID of the form you want to check. (optional)
 * @param string $element_id The ID of the form element you want to check, eg id="im4wp-form-1" (optional)
 *
 * @return boolean
 */
function im4wp_form_is_submitted( $form_id = 0, $element_id = null ) {
	try {
		$form = im4wp_get_form( $form_id );
	} catch ( Exception $e ) {
		return false;
	}

	if ( $element_id ) {
		$form_element = new IM4WP_Form_Element( $form, array( 'element_id' => $element_id ) );
		return $form_element->is_submitted;
	}

	return $form->is_submitted;
}

/**
 * @since 2.3.8
 * @deprecated 3.0
 * @ignore
 * @use im4wp_get_form
 *
 * @param int $form_id (optional)
 *
 * @return string
 */
function im4wp_form_get_response_html( $form_id = 0 ) {
	try {
		$form = im4wp_get_form( $form_id );
	} catch ( Exception $e ) {
		return '';
	}

	return $form->get_response_html();
}

/**
 * Gets an instance of the submitted form, if any.
 *
 * @access public
 *
 * @return IM4WP_Form|null
 */
function im4wp_get_submitted_form() {
	return im4wp( 'forms' )->get_submitted_form();
}
