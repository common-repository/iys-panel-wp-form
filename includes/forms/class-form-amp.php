<?php

/**
 * Class IM4WP_Form_AMP
 */
class IM4WP_Form_AMP {

	/**
	 * Hook!
	 */
	public function add_hooks() {
		add_filter( 'im4wp_form_content', array( $this, 'add_response_templates' ), 10, 2 );
		add_filter( 'im4wp_form_element_attributes', array( $this, 'add_amp_request' ) );
		add_filter( 'im4wp_load_form_scripts', array( $this, 'suppress_scripts' ) );
	}

	/**
	 * Add AMP templates for submit/success/error.
	 *
	 * @param string     $content The form content.
	 * @param IM4WP_Form $form The form object.
	 * @return string    Modified $content.
	 */
	public function add_response_templates( $content, $form ) {
		if ( ! function_exists( 'is_amp_endpoint' ) || ! is_amp_endpoint() ) {
			return $content;
		}

		ob_start();
		?>
		<div submitting>
			<template type="amp-mustache">
				<?php echo esc_html__( 'Submitting...', 'iys-panel-wp-form' ); ?>
			</template>
		</div>
		<div submit-success>
			<template type="amp-mustache">
				<?php
				echo wp_kses(
					$form->get_message( 'subscribed' ),
					array(
					'a' => array(),
					'strong' => array(),
					'em' => array(),
					)
				);
				?>
			</template>
		</div>
		<div submit-error>
			<template type="amp-mustache">
				{{message}}
			</template>
		</div>
		<?php
		$content .= ob_get_clean();

		return $content;
	}

	/**
	 * Add 'action-xhr' to AMP forms.
	 *
	 * @param array $attributes Key-Value pairs of attributes output on form.
	 * @return array Modified $attributes.
	 */
	public function add_amp_request( $attributes ) {
		if ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) {
			$attributes['action-xhr'] = get_rest_url( null, 'im4wp/v1/form' );
		}

		return $attributes;
	}

	/**
	 * Suppress form scripts on AMP pages.
	 *
	 * @param bool $load_scripts Whether scripts should be loaded.
	 * @return bool Modified $load_scripts.
	 */
	public function suppress_scripts( $load_scripts ) {
		if ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) {
			return false;
		}

		return $load_scripts;
	}
}
