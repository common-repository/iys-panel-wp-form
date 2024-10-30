<?php

/**
 * This class takes care of all form related functionality
 *
 * Do not interact with this class directly, use `im4wp_form` functions tagged with @access public instead.
 *
 * @class IM4WP_Form_Manager
 * @ignore
 * @access private
*/
class IM4WP_Form_Manager {


	/**
	 * @var IM4WP_Form_Output_Manager
	 */
	protected $output_manager;

	/**
	 * @var IM4WP_Form_Listener
	 */
	protected $listener;

	/**
	 * @var IM4WP_Form_Tags
	 */
	protected $tags;

	/**
	* @var IM4WP_Form_Previewer
	*/
	protected $previewer;

	/**
	 * @var IM4WP_Google_Recaptcha
	 */
	protected $recaptcha;

	/**
	 * @var IM4WP_Form_Asset_Manager
	 */
	protected $assets;

	/**
	 * @var IM4WP_Form_AMP
	 */
	protected $amp_compatibility;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->output_manager    = new IM4WP_Form_Output_Manager();
		$this->tags              = new IM4WP_Form_Tags();
		$this->listener          = new IM4WP_Form_Listener();
		$this->previewer         = new IM4WP_Form_Previewer();
		$this->recaptcha         = new IM4WP_Google_Recaptcha();
		$this->assets            = new IM4WP_Form_Asset_Manager();
		$this->amp_compatibility = new IM4WP_Form_AMP();
	}

	/**
	 * Hook!
	 */
	public function add_hooks() {
		add_action( 'init', array( $this, 'initialize' ) );
		add_action( 'widgets_init', array( $this, 'register_widget' ) );
		add_action( 'rest_api_init', array( $this, 'register_endpoint' ) );

		$this->listener->add_hooks();
		$this->output_manager->add_hooks();
		$this->assets->add_hooks();
		$this->tags->add_hooks();
		$this->previewer->add_hooks();
		$this->recaptcha->add_hooks();
		$this->amp_compatibility->add_hooks();
	}

	/**
	 * Initialize
	 */
	public function initialize() {
		$this->register_post_type();
		$this->register_block_type();
	}

	private function register_block_type() {
		// Bail if register_block_type does not exist (available since WP 5.0)
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		register_block_type(
			'iys-panel-wp-form/form',
			array(
				'render_callback' => array( $this->output_manager, 'shortcode' ),
			)
		);
	}

	/**
	 * Register post type "im4wp-form"
	 */
	private function register_post_type() {
		// register post type
		register_post_type(
			'im4wp-form',
			array(
				'labels' => array(
					'name'          => 'İYS Panel Sign-up Forms',
					'singular_name' => 'Sign-up Form',
				),
				'public' => false,
			)
		);
	}

	/**
	 * Register our Form widget
	 */
	public function register_widget() {
		register_widget( 'IM4WP_Form_Widget' );
	}

	/**
	 * Register an API endpoint for handling a form.
	 */
	public function register_endpoint() {
		register_rest_route(
			'im4wp/v1',
			'/form',
			array(
				'methods'  => 'POST',
				'permission_callback' => '__return_true',
				'callback' => array( $this, 'handle_endpoint' ),
			)
		);
	}

	/**
	 * Process requests to the form endpoint.
	 *
	 * A listener checks every request for a form submit, so we just need to fetch the listener and get its status.
	 */
	public function handle_endpoint() {
		$form = im4wp_get_submitted_form();
		if ( ! $form instanceof IM4WP_Form ) {
			return new WP_Error(
				'not_found',
				esc_html__( 'Resource does not exist.', 'iys-panel-wp-form' ),
				array(
					'status' => 404,
				)
			);
		}

		if ( $form->has_errors() ) {
			$message_key = $form->errors[0];
			$message     = $form->get_message( $message_key );
			return new WP_Error(
				$message_key,
				$message,
				array(
				'status' => 400,
				)
			);
		}

		return new WP_REST_Response( true, 200 );
	}

	/**
	 * @param       $form_id
	 * @param array $config
	 * @param bool  $echo
	 *
	 * @return string
	 */
	public function output_form( $form_id, $config = array(), $echo = true ) {
		return $this->output_manager->output_form( $form_id, $config, $echo );
	}

	/**
	 * Gets the currently submitted form
	 *
	 * @return IM4WP_Form|null
	 */
	public function get_submitted_form() {
		if ( $this->listener->submitted_form instanceof IM4WP_Form ) {
			return $this->listener->submitted_form;
		}

		return null;
	}

	/**
	 * Return all tags
	 *
	 * @return array
	 */
	public function get_tags() {
		return $this->tags->all();
	}
}
