<?php

/**
 * Class IM4WP_Form_Element
 *
 * @since 3.0
 * @ignore
 * @access private
 */
class IM4WP_Form_Element {


	/**
	 * @var string
	 */
	public $ID;

	/**
	 * @var IM4WP_Form
	 */
	public $form;

	/**
	 * @var array
	 *
	 * Can be used to set element-specific config settings. Accepts the following keys.
	 *
	 * - lists: Customized number of İYS Panel list ID's to subscribe to.
	 * - email_type: The email type
	 */
	public $config = array();

	/**
	 * @var bool
	 */
	public $is_submitted = false;

	/**
	 * @param IM4WP_Form $form
	 * @param string $id
	 * @param array $config
	 */
	public function __construct( IM4WP_Form $form, $id, array $config = array() ) {
		$this->form   = $form;
		$this->ID     = $id;
		$this->config = $config;

		$this->is_submitted = $this->form->is_submitted
						   && $this->form->config['element_id'] === $this->ID;
	}


	/**
	 * @return string
	 */
	protected function get_visible_fields() {
		$content = $this->form->content;
		$form    = $this->form;
		$element = $this;

		/**
		 * Filters the HTML for the form fields.
		 *
		 * Use this filter to add custom HTML to a form programmatically
		 *
		 * @param string $content
		 * @param IM4WP_Form $form
		 * @param IM4WP_Form_Element $element
		 * @since 2.0
		 */
		$visible_fields = (string) apply_filters( 'im4wp_form_content', $content, $form, $element );

		return $visible_fields;
	}

    protected function get_2fa_html() {
        $header = sprintf( '<div><h4>%s</h4></div>', __( 'Confirm your submission', 'iys-panel-wp-form' ));
        $body = sprintf( '<p>%s</p>',  __( 'You will receive an SMS with the confirmation code to the GSM number you provided in the form. Enter the confirmation code to the field below in order to verify your submission.', 'iys-panel-wp-form' ));
        $inputs = array();
        $inputs[] = '<input type="text" name="_im4wp_form_2fa_sms_key" value="" />';
        $inputs[] = '<input type="hidden" name="_im4wp_form_2fa_key" value="' . esc_attr( $this->form->two_fa_sms_key ) . '" />';
        $submit = sprintf( '<input type="submit" name="submit" id="submit" class="button button-primary" value="%s" style="font-size: small; margin-left: 20px; margin-top: 20px;">', __( 'Enter', 'iys-panel-wp-form' ) );
        $resend = sprintf( '<input type="submit" name="resend2fa" id="resend2fa" class="button button-secondary" value="%s" style="font-size: small; margin-right: 20px; margin-top: 20px;">',  __( 'Resend the code', 'iys-panel-wp-form' ));

        return $header .
               $body .
               join('', $inputs) .
               '<br />' .
               $resend .
               $submit;
    }

	/**
	 * @return string
	 */
	protected function get_hidden_fields() {

		// hidden fields
		$hidden_fields  = '<label style="display: none !important;">' . __( 'Leave this field empty if you\'re human:', 'iys-panel-wp-form' ) . ' ' . '<input type="text" name="_im4wp_honeypot" value="" tabindex="-1" autocomplete="off" /></label>';
		$hidden_fields .= '<input type="hidden" name="_im4wp_timestamp" value="' . time() . '" />';
		$hidden_fields .= '<input type="hidden" name="_im4wp_form_id" value="' . esc_attr( $this->form->ID ) . '" />';
		$hidden_fields .= '<input type="hidden" name="_im4wp_form_element_id" value="' . esc_attr( $this->ID ) . '" />';

		// was "lists" parameter passed in shortcode arguments?
		if ( ! empty( $this->config['lists'] ) ) {
			$lists_string   = is_array( $this->config['lists'] ) ? join( ',', $this->config['lists'] ) : $this->config['lists'];
			$hidden_fields .= '<input type="hidden" name="_im4wp_lists" value="' . esc_attr( $lists_string ) . '" />';
		}

		// was "lists" parameter passed in shortcode arguments?
		if ( ! empty( $this->config['email_type'] ) ) {
			$hidden_fields .= '<input type="hidden" name="_im4wp_email_type" value="' . esc_attr( $this->config['email_type'] ) . '" />';
		}

		return (string) $hidden_fields;
	}

	/**
	 * Get HTML string for a notice, including wrapper element.
	 *
	 * @param IM4WP_Form_Notice $notice
	 *
	 * @return string
	 */
	protected function get_notice_html( IM4WP_Form_Notice $notice ) {
		if ( $notice->text === '' ) {
			return '';
		}

        $html = sprintf( '<div class="im4wp-alert im4wp-%s" role="alert"><p>%s</p></div>', esc_attr( $notice->type ), $notice->text );
		return $html;
	}

	/**
	 * Gets the form response string
	 *
	 * @param boolean $force_show
	 * @return string
	 */
	public function get_response_html( $force_show = false ) {
		$html = '';
		$form = $this->form;

		if ( $this->is_submitted || $force_show ) {
			foreach ( $this->form->notices as $notice ) {
				$html .= $this->get_notice_html( $notice );
			}
		}

		/**
		 * Filter the form response HTML
		 *
		 * Use this to add your own HTML to the form response. The form instance is passed to the callback function.
		 *
		 * @since 3.0
		 *
		 * @param string $html The complete HTML string of the response, excluding the wrapper element.
		 * @param IM4WP_Form $form The form object
		 */
		$html = (string) apply_filters( 'im4wp_form_response_html', $html, $form );

		// wrap entire response in div, regardless of a form was submitted
		$html = '<div class="im4wp-response">' . $html . '</div>';
		return $html;
	}

	/**
	 * @return string
	 */
	protected function get_response_position() {
		$position = 'after';
		$form     = $this->form;

		// check if content contains {response} tag
		if ( stripos( $this->form->content, '{response}' ) !== false ) {
			return '';
		}

		/**
		 * Filters the position for the form response.
		 *
		 * Valid values are "before" and "after". Will have no effect if `{response}` is used in the form content.
		 *
		 * @param string $position
		 * @param IM4WP_Form $form
		 * @since 2.0
		 */
		$response_position = (string) apply_filters( 'im4wp_form_response_position', $position, $form );

		return $response_position;
	}

	/**
	 * Get HTML to be added _before_ the HTML of the form fields.
	 *
	 * @return string
	 */
	protected function get_html_before_fields() {
		$html = '';
		$form = $this->form;

		/**
		 * Filters the HTML before the form fields.
		 *
		 * @param string $html
		 * @param IM4WP_Form $form
		 * @ignore
		 */
		$html = (string) apply_filters( 'im4wp_form_before_fields', $html, $form );

		if ( $this->get_response_position() === 'before' ) {
			$html = $html . $this->get_response_html();
		}

		return $html;
	}

	/**
	 * Get HTML to be added _after_ the HTML of the form fields.
	 *
	 * @return string
	 */
	protected function get_html_after_fields() {
		$html = '';
		$form = $this->form;

		/**
		 * Filters the HTML after the form fields.
		 *
		 * @param string $html
		 * @param IM4WP_Form $form
		 * @ignore
		 */
		$html = (string) apply_filters( 'im4wp_form_after_fields', $html, $form );

		if ( $this->get_response_position() === 'after' ) {
			$html = $this->get_response_html() . $html;
		}

		return $html;
	}

	/**
	 * Get all HTMl attributes for the form element
	 *
	 * @return string
	 */
	protected function get_form_element_attributes() {
		$form                  = $this;
		$form_action_attribute = null;

		$attributes = array(
			'id'    => $this->ID,
			'class' => $this->get_css_classes(),
		);

		/**
		 * Filters the `action` attribute of the `<form>` element.
		 *
		 * Defaults to `null`, which means no `action` attribute will be printed.
		 *
		 * @param string $form_action_attribute
		 * @param IM4WP_Form $form
		 */
		$form_action_attribute = apply_filters( 'im4wp_form_action', $form_action_attribute, $form );
		if ( is_string( $form_action_attribute ) ) {
			$attributes['action'] = $form_action_attribute;
		}

		/**
		 * Filters all attributes to be added to the `<form>` element
		 *
		 * @param array $attributes Key-value pairs of attributes.
		 * @param IM4WP_Form $form
		 */
		$attributes = (array) apply_filters( 'im4wp_form_element_attributes', $attributes, $form );

		// hardcoded attributes, can not be changed.
		$attributes['method']    = 'post';
		$attributes['data-id']   = $this->form->ID;
		$attributes['data-name'] = $this->form->name;

		// build string of key="value" from array
		$string = '';
		foreach ( $attributes as $name => $value ) {
			$string .= sprintf( '%s="%s" ', $name, esc_attr( $value ) );
		}

		return $string;
	}

	/**
	 * @param array|null $config Use this to override the configuration for this form element
	 * @return string
	 */
	public function generate_html( array $config = null ) {
		if ( $config ) {
			$this->config = $config;
		}

		// return empty string if form is in trash
		if ( $this->form->status !== 'publish' ) {
			return '';
		}

		// Start building content string
		$opening_html  = '<!-- İYS Panel WP Form v' . IM4WP_VERSION . ' - https://wordpress.org/plugins/iys-panel-wp-form/ -->';
		$opening_html .= '<form ' . $this->get_form_element_attributes() . '>';
		$before_fields = $this->get_html_before_fields();
		$fields        = '';
		$after_fields  = $this->get_html_after_fields();
		$closing_html  = '</form><!-- / İYS Panel WP Form Plugin -->';

		if ( ! $this->is_submitted
		  || ! $this->form->settings['hide_after_success']
		  || $this->form->has_errors() ) {

			// add HTML for fields + wrapper element.
			$fields = '<div class="im4wp-form-fields">' .
				      $this->get_visible_fields() .
				      '</div>' .
				      $this->get_hidden_fields();
		}

        $twoFa = '<div class="im4wp-form-fields">' .
				 $this->get_2fa_html() .
				 '</div>' .
				 $this->get_hidden_fields();


        if ($this->form->is_2fa && !empty($this->form->two_fa_sms_key)) {
            $content = $twoFa;
        } else {
            $content = $fields;
        }

		// concatenate everything
		$output = $opening_html .
				  $before_fields .
				  $content .
				  $after_fields .
				  $closing_html;

		return $output;
        // return '<p>Yeaaa</p>';
	}

	/**
	 * Get a space separated list of CSS classes for this form
	 *
	 * @return string
	 */
	protected function get_css_classes() {
		$classes = array();
		$form    = $this->form;

		$classes[] = 'im4wp-form';
		$classes[] = 'im4wp-form-' . $form->ID;

		// Add form classes if this specific form element was submitted
		if ( $this->is_submitted ) {
			$classes[] = 'im4wp-form-submitted';

			if ( ! $form->has_errors() ) {
				$classes[] = 'im4wp-form-success';
			} else {
				$classes[] = 'im4wp-form-error';
			}
		}

		// add class for CSS targeting in custom stylesheets
		if ( ! empty( $form->settings['css'] ) ) {
			if ( strpos( $form->settings['css'], 'theme-' ) === 0 ) {
				$classes[] = 'im4wp-form-theme';
			}

			$classes[] = 'im4wp-form-' . $form->settings['css'];
		}

		// add classes from config array
		if ( ! empty( $this->config['element_class'] ) ) {
			$classes = array_merge( $classes, explode( ' ', $this->config['element_class'] ) );
		}

		/**
		 * Filters `class` attributes for the `<form>` element.
		 *
		 * @param array $classes
		 * @param IM4WP_Form $form
		 */
		$classes = apply_filters( 'im4wp_form_css_classes', $classes, $form );

		return implode( ' ', $classes );
	}
}
