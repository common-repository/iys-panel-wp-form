<?php
return array(
	'subscribed'             => array(
		'type' => 'success',
		'text' => esc_html__( 'Thank you, your sign-up request was successful! Please check your email inbox to confirm.', 'iys-panel-wp-form' ),
	),
	'updated'                => array(
		'type' => 'success',
		'text' => esc_html__( 'Thank you, your records have been updated!', 'iys-panel-wp-form' ),
	),
	'unsubscribed'           => array(
		'type' => 'success',
		'text' => esc_html__( 'You were successfully unsubscribed.', 'iys-panel-wp-form' ),
	),
	'not_subscribed'         => array(
		'type' => 'notice',
		'text' => esc_html__( 'Given email address is not subscribed.', 'iys-panel-wp-form' ),
	),
	'error'                  => array(
		'type' => 'error',
		'text' => esc_html__( 'Oops. Something went wrong. Please try again later.', 'iys-panel-wp-form' ),
	),
	'invalid_email'          => array(
		'type' => 'error',
		'text' => esc_html__( 'Please provide a valid email address.', 'iys-panel-wp-form' ),
	),
	'already_subscribed'     => array(
		'type' => 'notice',
		'text' => esc_html__( 'Given email address is already subscribed, thank you!', 'iys-panel-wp-form' ),
	),
	'required_field_missing' => array(
		'type' => 'error',
		'text' => esc_html__( 'Please fill in the required fields.', 'iys-panel-wp-form' ),
	),
	'no_lists_selected'      => array(
		'type' => 'error',
		'text' => esc_html__( 'Please select at least one list.', 'iys-panel-wp-form' ),
	),
    '2fa_failed'      => array(
		'type' => 'error',
		'text' => esc_html__( 'Code is wrong!', 'iys-panel-wp-form' ),
	),
    '2fa_success'             => array(
		'type' => 'success',
		'text' => esc_html__( 'Thank you, your sign-up request was successful!', 'iys-panel-wp-form' ),
	),
    'renew_2fa_failed'             => array(
		'type' => 'error',
		'text' => esc_html__( 'Code can not be renewed!', 'iys-panel-wp-form' ),
	),
    'renew_2fa_success'             => array(
		'type' => 'success',
		'text' => esc_html__( 'Code is renewed!', 'iys-panel-wp-form' ),
	),
);
