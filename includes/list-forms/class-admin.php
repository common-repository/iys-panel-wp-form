<?php

/**
 * Class IM4WP_Forms_Admin
 *
 * @ignore
 * @access private
 */
class IM4WP_List_Forms {


	/**
	 * @var IM4WP_Admin_Messages
	 */
	protected $messages;

	/**
	 * @param IM4WP_Admin_Messages $messages
	 */
	public function __construct( IM4WP_Admin_Messages $messages ) {
		$this->messages = $messages;
	}

	/**
	 * Add hooks
	 */
	public function add_hooks() {
		// add_action( 'im4wp_list_forms_list', array( $this, 'enqueue_assets' ), 10, 2 );

        // add_action( 'register_shortcode_ui', array( $this, 'register_shortcake_ui' ) );
		// add_action( 'im4wp_save_form', array( $this, 'update_form_stylesheets' ) );
		// add_action( 'im4wp_admin_edit_form', array( $this, 'process_save_form' ) );
		// add_action( 'im4wp_admin_add_form', array( $this, 'process_add_form' ) );
		add_filter( 'im4wp_admin_menu_items', array( $this, 'add_menu_item' ), 5 );
		// add_action( 'im4wp_admin_show_forms_page-edit-form', array( $this, 'show_edit_page' ) );
		// add_action( 'im4wp_admin_show_forms_page-add-form', array( $this, 'show_add_page' ) );
		// add_action( 'im4wp_admin_enqueue_assets', array( $this, 'enqueue_assets' ), 10, 2 );

		// add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_gutenberg_assets' ) );
	}

    /**
	 * @param $items
	 *
	 * @return mixed
	 */
	public function add_menu_item( $items ) {
		$items['list'] = array(
			'title'         => esc_html__( 'Forms', 'iys-panel-wp-form' ),
			'text'          => esc_html__( 'Forms', 'iys-panel-wp-form' ),
			'slug'          => 'list',
			'callback'      => array( $this, 'show_generals_setting_page' ),
			// 'load_callback' => array( $this, 'redirect_to_form_action' ),
			'position'      => 11,
		);

		return $items;
	}

    /**
	* Show the API Settings page
	*/
	public function show_generals_setting_page() {
		$opts      = im4wp_get_options();
		$api_key   = im4wp_get_api_key();
		$lists     = array();
		$connected = ! empty( $api_key );

		if ( $connected ) {
			try {
				$connected = $this->get_api()->is_connected();
				$iyspanel = new IM4WP_MailChimp();
				$lists     = $iyspanel->get_lists();
			} catch ( IM4WP_API_Connection_Exception $e ) {
				$message = sprintf( '<strong>%s</strong> %s %s ', esc_html__( 'Error connecting to İYS Panel:', 'iys-panel-wp-form' ), $e->getCode(), $e->getMessage() );

				if ( is_object( $e->data ) && ! empty( $e->data->ref_no ) ) {
					$message .= '<br />' . sprintf( esc_html__( 'Looks like your server is blocked by İYS Panel\'s firewall. Please contact İYS Panel support and include the following reference number: %s', 'iys-panel-wp-form' ), $e->data->ref_no );
				}

				$message .= '<br /><br />' . sprintf( '<a href="%s">' . esc_html__( 'Here\'s some info on solving common connectivity issues.', 'iys-panel-wp-form' ) . '</a>', 'https://hermesiletisim.net/kb/solving-connectivity-issues/#utm_source=wp-plugin&utm_medium=iys-panel-wp-form&utm_campaign=settings-notice' );

				$this->messages->flash( $message, 'error' );
				$connected = false;
			} catch ( IM4WP_API_Exception $e ) {
				$message = sprintf( '<strong>%s</strong><br /> %s', esc_html__( 'İYS Panel returned the following error:', 'iys-panel-wp-form' ), nl2br( (string) $e ) );
				$this->messages->flash( $message, 'error' );
				$connected = false;
			}
		}

		$obfuscated_api_key = im4wp_obfuscate_string( $api_key );
		require IM4WP_PLUGIN_DIRDIR . '/includes/views/list-forms.php';
	}

    /**
	* @return IM4WP_API_V1
	*/
	protected function get_api() {
		return im4wp( 'api' );
	}
}
