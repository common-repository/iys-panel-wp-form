<?php

/**
 * Class IM4WP_Admin
 *
 * @ignore
 * @access private
 */
class IM4WP_Admin {


	/**
	 * @var string The relative path to the main plugin file from the plugins dir
	 */
	protected $plugin_file;

	/**
	 * @var IM4WP_Admin_Messages
	 */
	protected $messages;

	/**
	 * @var IM4WP_Admin_Tools
	 */
	protected $tools;

	/**
	 * Constructor
	 *
	 * @param IM4WP_Admin_Tools $tools
	 * @param IM4WP_Admin_Messages $messages
	 */
	public function __construct( IM4WP_Admin_Tools $tools, IM4WP_Admin_Messages $messages ) {
		$this->tools         = $tools;
		$this->messages      = $messages;
		$this->plugin_file   = plugin_basename( IM4WP_PLUGIN_DIRFILE );
	}

	/**
	 * Registers all hooks
	 */
	public function add_hooks() {

		// Actions used globally throughout WP Admin
		add_action( 'admin_menu', array( $this, 'build_menu' ) );
		add_action( 'admin_init', array( $this, 'initialize' ) );

		add_action( 'current_screen', array( $this, 'customize_admin_texts' ) );
		add_action( 'wp_dashboard_setup', array( $this, 'register_dashboard_widgets' ) );
		add_action( 'im4wp_admin_empty_lists_cache', array( $this, 'renew_lists_cache' ) );
		add_action( 'im4wp_admin_empty_debug_log', array( $this, 'empty_debug_log' ) );

		add_action( 'admin_notices', array( $this, 'show_api_key_notice' ) );
		add_action( 'im4wp_admin_dismiss_api_key_notice', array( $this, 'dismiss_api_key_notice' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );

		$this->messages->add_hooks();
	}

	/**
	 * Initializes various stuff used in WP Admin
	 *
	 * - Registers settings
	 */
	public function initialize() {

		// register settings
		register_setting( 'im4wp_settings', 'im4wp', array( $this, 'save_general_settings' ) );

		// Load upgrader
		$this->init_upgrade_routines();

		// listen for custom actions
		$this->listen_for_actions();
	}


	/**
	 * Listen for `_im4wp_action` requests
	 */
	public function listen_for_actions() {
		// do nothing if _im4wp_action was not in the request parameters
		if ( ! isset( $_REQUEST['_im4wp_action'] ) ) {
			return;
		}

		// check if user is authorized
		if ( ! $this->tools->is_user_authorized() ) {
			return;
		}

		// verify nonce
		if( ! isset( $_REQUEST['_wpnonce'] ) || false === wp_verify_nonce( $_REQUEST['_wpnonce'], '_im4wp_action' ) ) {
			wp_nonce_ays( '_im4wp_action' );
			exit;
		}

		$action = (string) $_REQUEST['_im4wp_action'];

		/**
		 * Allows you to hook into requests containing `_im4wp_action` => action name.
		 *
		 * The dynamic portion of the hook name, `$action`, refers to the action name.
		 *
		 * By the time this hook is fired, the user is already authorized. After processing all the registered hooks,
		 * the request is redirected back to the referring URL.
		 *
		 * @since 3.0
		 */
		do_action( 'im4wp_admin_' . $action );

		// redirect back to where we came from (to prevent double submit)
		if ( isset( $_POST['_redirect_to'] ) ) {
			$redirect_url = esc_url_raw($_POST['_redirect_to']);
		} elseif ( isset( $_GET['_redirect_to'] ) ) {
			$redirect_url = esc_url_raw($_GET['_redirect_to']);
		} else {
			$redirect_url = remove_query_arg( '_im4wp_action' );
		}

		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Register dashboard widgets
	 */
	public function register_dashboard_widgets() {
		if ( ! $this->tools->is_user_authorized() ) {
			return false;
		}

		/**
		 * Setup dashboard widget, users are authorized by now.
		 *
		 * Use this hook to register your own dashboard widgets for users with the required capability.
		 *
		 * @since 3.0
		 * @ignore
		 */
		do_action( 'im4wp_dashboard_setup' );

		return true;
	}

	/**
	 * Upgrade routine
	 */
	private function init_upgrade_routines() {

		// upgrade routine for upgrade routine....
		$previous_version = get_option( 'im4wp_lite_version', 0 );
		if ( $previous_version ) {
			delete_option( 'im4wp_lite_version' );
			update_option( 'im4wp_version', $previous_version );
		}

		$previous_version = get_option( 'im4wp_version', 0 );

		// allow setting migration version from URL, to easily re-run previous migrations.
		if ( isset( $_GET['im4wp_run_migration'] ) ) {
			$previous_version = $_GET['im4wp_run_migration'];
		}

		// Ran upgrade routines before?
		if ( empty( $previous_version ) ) {
			update_option( 'im4wp_version', IM4WP_VERSION );

			// if we have at least one form, we're going to run upgrade routine for v3 => v4 anyway.
			$posts = get_posts(
				array(
					'post_type'   => 'im4wp-form',
					'posts_per_page' => 1,
				)
			);
			if ( empty( $posts ) ) {
				return false;
			}

			$previous_version = '3.9';
		}

		// Rollback'ed?
		if ( version_compare( $previous_version, IM4WP_VERSION, '>' ) ) {
			update_option( 'im4wp_version', IM4WP_VERSION );
			return false;
		}

		// This means we're good!
		if ( version_compare( $previous_version, IM4WP_VERSION ) > -1 ) {
			return false;
		}

		define( 'IM4WP_DOING_UPGRADE', true );
		$upgrade_routines = new IM4WP_Upgrade_Routines( $previous_version, IM4WP_VERSION, __DIR__ . '/migrations' );
		$upgrade_routines->run();
		update_option( 'im4wp_version', IM4WP_VERSION );
	}

	/**
	 * Renew İYS Panel lists cache
	 */
	public function renew_lists_cache() {
		// try getting new lists to fill cache again
		$iyspanel = new IM4WP_MailChimp();
		$lists     = $iyspanel->refresh_lists();

		if ( ! empty( $lists ) ) {
			$this->messages->flash( esc_html__( 'Success! The cached configuration for your groups has been renewed.', 'iys-panel-wp-form' ) );
		}
	}

    /**
	 * Renew brand lists cache
	 */
	public function renew_brands_cache() {
		// try getting new lists to fill cache again
		$iyspanel = new IM4WP_MailChimp();
		$brands     = $iyspanel->refresh_brands();

		if ( ! empty( $brands ) ) {
			$this->messages->flash( esc_html__( 'Success! The cached configuration for your brands has been renewed.', 'iys-panel-wp-form' ) );
		}
	}

    /**
	 * Renew originator lists cache
	 */
	public function renew_originators_cache() {
		// try getting new lists to fill cache again
		$iyspanel = new IM4WP_MailChimp();
		$originators     = $iyspanel->refresh_originators();

		if ( ! empty( $originators ) ) {
			$this->messages->flash( esc_html__( 'Success! The cached configuration for your originators has been renewed.', 'iys-panel-wp-form' ) );
		}
	}

	/**
	 * Customize texts throughout WP Admin
	 */
	public function customize_admin_texts() {
		$texts = new IM4WP_Admin_Texts( $this->plugin_file );
		$texts->add_hooks();
	}

	/**
	 * Validates the General settings
	 * @param array $settings
	 * @return array
	 */
	public function save_general_settings( array $settings ) {
		$current = im4wp_get_options();

		// merge with current settings to allow passing partial arrays to this method
		$settings = array_merge( $current, $settings );

		// toggle usage tracking
		if ( $settings['allow_usage_tracking'] !== $current['allow_usage_tracking'] ) {
			IM4WP_Usage_Tracking::instance()->toggle( $settings['allow_usage_tracking'] );
		}

		// Make sure not to use obfuscated key
		if ( strpos( $settings['api_key'], '*' ) !== false ) {
			$settings['api_key'] = $current['api_key'];
		}

        if ( $settings['connection_cancelled'] == "true" ) {
            $settings['connection_cancelled'] = true;
            $settings['api_key'] = '';
            $client = im4wp_get_api_v1()->get_client();
            $response = $client->get( 'unregisterDomain' );
        } else {
            $settings['connection_cancelled'] = false;
        }

		// Sanitize API key
		$settings['api_key'] = sanitize_text_field( $settings['api_key'] );

		// if API key changed, empty İYS Panel cache
		if ( $settings['api_key'] !== $current['api_key'] ) {
            delete_transient( 'im4wp_iyspanel_lists' );
            delete_transient( 'im4wp_iyspanel_brands' );
            delete_transient( 'im4wp_iyspanel_originators' );
		}

		/**
		 * Runs right before general settings are saved.
		 *
		 * @param array $settings The updated settings array
		 * @param array $current The old settings array
		 */
		do_action( 'im4wp_save_settings', $settings, $current );

		return $settings;
	}

	/**
	 * Load scripts and stylesheet on İYS Panel WP Form Admin pages
	 *
	 * @return bool
	 */
	public function enqueue_assets() {
		global $wp_scripts;

		if ( ! $this->tools->on_plugin_page() ) {
			return false;
		}

		$opts      = im4wp_get_options();
		$page      = $this->tools->get_plugin_page();
		$iyspanel = new IM4WP_MailChimp();

		// css
		wp_register_style( 'im4wp-admin', im4wp_plugin_url( 'assets/css/admin.css' ), array(), IM4WP_VERSION );
		wp_enqueue_style( 'im4wp-admin' );

		// js
		wp_register_script( 'im4wp-admin', im4wp_plugin_url( 'assets/js/admin.js' ), array(), IM4WP_VERSION, true );
		wp_enqueue_script( 'im4wp-admin' );
		$connected = ! empty( $opts['api_key'] );
		$iyspanel_lists = $connected ? $iyspanel->get_lists() : array();
		$brands = $connected ? $iyspanel->get_brands() : array();
		$originators = $connected ? $iyspanel->get_originators() : array();
		wp_localize_script(
			'im4wp-admin',
			'im4wp_vars',
			array(
				'ajaxurl'   => admin_url( 'admin-ajax.php' ),
				'iyspanel' => array(
					'api_connected' => $connected,
					'lists'         => $iyspanel_lists,
					'brands'        => $brands,
					'originators'   => $originators,
				),
				'countries' => IM4WP_Tools::get_countries(),
				'i18n'      => array(
					'invalid_api_key'                => __( 'The given value does not look like a valid API key.', 'iys-panel-wp-form' ),
                    'renew_iyspanel_lists'           => __( 'Renew Form Connections', 'iys-panel-wp-form' ),
					'fetching_iyspanel_lists'        => __( 'Fetching Groups', 'iys-panel-wp-form' ),
                    'fetching_iyspanel_lists_done'   => __( 'Done! Groups renewed.', 'iys-panel-wp-form' ),
                    'fetching_iyspanel_lists_error'  => __( 'Failed to renew your groups. An error occured.', 'iys-panel-wp-form' ),
                    'renew_brands'                   => __( 'Renew Brands', 'iys-panel-wp-form' ),
					'fetching_brands'                => __( 'Fetching Brands', 'iys-panel-wp-form' ),
					'fetching_brands_done'           => __( 'Done! Brands renewed.', 'iys-panel-wp-form' ),
                    'renew_originators'              => __( 'Renew Originators', 'iys-panel-wp-form' ),
					'fetching_originators'           => __( 'Fetching Originators', 'iys-panel-wp-form' ),
					'fetching_originators_done'      => __( 'Done! Originators renewed.', 'iys-panel-wp-form' ),
				),
			)
		);

		/**
		 * Hook to enqueue your own custom assets on the İYS Panel WP Form setting pages.
		 *
		 * @since 3.0
		 *
		 * @param string $suffix
		 * @param string $page
		 */
		do_action( 'im4wp_admin_enqueue_assets', '', $page );

		return true;
	}



	/**
	 * Register the setting pages and their menu items
	 */
	public function build_menu() {
		$required_cap = $this->tools->get_required_capability();

		$menu_items = array(
			array(
				'title'    => esc_html__( 'İYS Panel WP Form API Settings', 'iys-panel-wp-form' ),
				'text'     => esc_html__( 'Connections', 'iys-panel-wp-form' ),
				'slug'     => '',
				'callback' => array( $this, 'show_generals_setting_page' ),
				'position' => 0,
			),
		);

		/**
		 * Filters the menu items to appear under the main menu item.
		 *
		 * To add your own item, add an associative array in the following format.
		 *
		 * $menu_items[] = array(
		 *     'title' => 'Page title',
		 *     'text'  => 'Menu text',
		 *     'slug' => 'Page slug',
		 *     'callback' => 'my_page_function',
		 *     'position' => 50
		 * );
		 *
		 * @param array $menu_items
		 * @since 3.0
		 */
		$menu_items = (array) apply_filters( 'im4wp_admin_menu_items', $menu_items );

		// add top menu item
		$icon = file_get_contents( IM4WP_PLUGIN_DIRDIR . '/assets/img/icon.svg' );
		add_menu_page( 'İYS Panel WP Form', 'İYS Panel WP Form', $required_cap, 'iys-panel-wp-form', array( $this, 'show_generals_setting_page' ), 'data:image/svg+xml;base64,' . base64_encode( $icon ), '99.68491' );

		// sort submenu items by 'position'
		usort( $menu_items, array( $this, 'sort_menu_items_by_position' ) );

		// add sub-menu items
		foreach ( $menu_items as $item ) {
			$this->add_menu_item( $item );
		}
	}

	/**
	 * @param array $item
	 */
	public function add_menu_item( array $item ) {

		// generate menu slug
		$slug = 'iys-panel-wp-form';
		if ( ! empty( $item['slug'] ) ) {
			$slug .= '-' . $item['slug'];
		}

		// provide some defaults
		$parent_slug = ! empty( $item['parent_slug'] ) ? $item['parent_slug'] : 'iys-panel-wp-form';
		$capability  = ! empty( $item['capability'] ) ? $item['capability'] : $this->tools->get_required_capability();

		// register page
		$hook = add_submenu_page( $parent_slug, $item['title'] . ' - İYS Panel WP Form', $item['text'], $capability, $slug, $item['callback'] );

		// register callback for loading this page, if given
		if ( array_key_exists( 'load_callback', $item ) ) {
			add_action( 'load-' . $hook, $item['load_callback'] );
		}
	}

	/**
	 * Show the API Settings page
	 */
	public function show_generals_setting_page() {
		$opts      = im4wp_get_options();
		$api_key   = im4wp_get_api_key();
		$lists     = array();
		$brands    = array();
		$originators = array();
		$connected = ! empty( $api_key );

        if ( $connected ) {
			try {
                $settings_updated = ! empty( $_GET['settings-updated'] ) ? $_GET['settings-updated'] : 'false';
                if ($settings_updated == 'true') {
                    $connected = $this->get_api()->register_to_api();
                } else {
                    $connected = $this->get_api()->is_connected();
                }

				$iyspanel = new IM4WP_MailChimp();
				$lists     = $iyspanel->get_lists();
				$brands    = $iyspanel->get_brands();
				$originators    = $iyspanel->get_originators();
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
        require IM4WP_PLUGIN_DIRDIR . '/includes/views/general-settings.php';
    }

    /**
     * Show the Other Settings page
     */
    public function show_other_setting_page() {
	    $opts       = im4wp_get_options();
	    $log        = $this->get_log();
	    $log_reader = new IM4WP_Debug_Log_Reader( $log->file );
	    require IM4WP_PLUGIN_DIRDIR . '/includes/views/other-settings.php';
    }

    /**
     * @param $a
     * @param $b
     *
     * @return int
     */
    public function sort_menu_items_by_position( $a, $b ) {
	    $pos_a = isset( $a['position'] ) ? $a['position'] : 80;
	    $pos_b = isset( $b['position'] ) ? $b['position'] : 90;
	    return $pos_a < $pos_b ? -1 : 1;
    }

    /**
     * Empties the log file
     */
    public function empty_debug_log() {
	    $log = $this->get_log();
	    file_put_contents( $log->file, '' );

	    $this->messages->flash( esc_html__( 'Log successfully emptied.', 'iys-panel-wp-form' ) );
    }

    /**
     * Shows a notice when API key is not set.
     */
    public function show_api_key_notice() {

	    // don't show if on settings page already
	    if ( $this->tools->on_plugin_page( '' ) ) {
		    return;
	    }

	    // only show to user with proper permissions
	    if ( ! $this->tools->is_user_authorized() ) {
		    return;
	    }

	    // don't show if dismissed
	    if ( get_transient( 'im4wp_api_key_notice_dismissed' ) ) {
		    return;
	    }

	    // don't show if api key is set already
	    $api_key = im4wp_get_api_key();
	    if ( ! empty( $api_key ) ) {
		    return;
	    }

	    echo '<div class="notice notice-warning im4wp-is-dismissible">';
	    echo '<p>', sprintf( wp_kses( __( 'To get started with İYS Panel WP Form, please <a href="%s">enter your İYS Panel API key on the settings page of the plugin</a>.', 'iys-panel-wp-form' ), array( 'a' => array( 'href' => array() ) ) ), admin_url( 'admin.php?page=iys-panel-wp-form' ) ), '</p>';
	    echo '<form method="post"><input type="hidden" name="_im4wp_action" value="dismiss_api_key_notice" /><button type="submit" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></form>';
	    echo '</div>';
    }

    /**
     * Dismisses the API key notice for 1 week
     */
    public function dismiss_api_key_notice() {
	    set_transient( 'im4wp_api_key_notice_dismissed', 1, 3600 * 24 * 7 );
    }

    /**
     * @return IM4WP_Debug_Log
     */
    protected function get_log() {
	    return im4wp( 'log' );
    }

    /**
     * @return IM4WP_API_V1
     */
    protected function get_api() {
	    return im4wp( 'api' );
    }
}
