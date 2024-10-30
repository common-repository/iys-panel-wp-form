<?php

/**
 * Class IM4WP_Forms_Admin
 *
 * @ignore
 * @access private
 */
class IM4WP_Forms_Admin {


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
		add_action( 'register_shortcode_ui', array( $this, 'register_shortcake_ui' ) );
		add_action( 'im4wp_save_form', array( $this, 'update_form_stylesheets' ) );
		add_action( 'im4wp_admin_edit_form', array( $this, 'process_save_form' ) );
		add_action( 'im4wp_admin_add_form', array( $this, 'process_add_form' ) );
		add_filter( 'im4wp_admin_menu_items', array( $this, 'add_menu_item' ), 5 );
		add_action( 'im4wp_admin_show_forms_page-edit-form', array( $this, 'show_edit_page' ) );
		add_action( 'im4wp_admin_show_forms_page-add-form', array( $this, 'show_add_page' ) );
		add_action( 'im4wp_admin_enqueue_assets', array( $this, 'enqueue_assets' ), 10, 2 );

		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_gutenberg_assets' ) );
	}


	public function enqueue_gutenberg_assets() {
		wp_enqueue_script( 'im4wp-form-block', im4wp_plugin_url( 'assets/js/forms-block.js' ), array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-components' ) );

		$forms = im4wp_get_forms();
		$data  = array();
		foreach ( $forms as $form ) {
			$data[] = array(
				'name' => $form->name,
				'id'   => $form->ID,
			);
		}
		wp_localize_script( 'im4wp-form-block', 'im4wp_forms', $data );
	}

	/**
	 * @param string $suffix
	 * @param string $page
	 */
	public function enqueue_assets( $suffix, $page = '' ) {
		if ( $page !== 'forms' || empty( $_GET['view'] ) || $_GET['view'] !== 'edit-form' ) {
			return;
		}

		wp_register_script( 'im4wp-forms-admin', im4wp_plugin_url( 'assets/js/forms-admin.js' ), array( 'im4wp-admin' ), IM4WP_VERSION, true );
		wp_enqueue_script( 'im4wp-forms-admin' );
		wp_localize_script(
			'im4wp-forms-admin',
			'im4wp_forms_i18n',
			array(
				'addToForm'             => __( 'Add to form', 'iys-panel-wp-form' ),
				'agreeToTerms'          => __( 'I have read and agree to the terms & conditions', 'iys-panel-wp-form' ),
				'agreeToTermsShort'     => __( 'Agree to terms', 'iys-panel-wp-form' ),
				'agreeToTermsLink'      => __( 'Link to your terms & conditions page', 'iys-panel-wp-form' ),
				'city'                  => __( 'City', 'iys-panel-wp-form' ),
				'checkboxes'            => __( 'Checkboxes', 'iys-panel-wp-form' ),
				'choices'               => __( 'Choices', 'iys-panel-wp-form' ),
				'choiceType'            => __( 'Choice type', 'iys-panel-wp-form' ),
				'chooseField'           => __( 'Select the fields below to put on your form.', 'iys-panel-wp-form' ),
				'close'                 => __( 'Close', 'iys-panel-wp-form' ),
				'country'               => __( 'Country', 'iys-panel-wp-form' ),
				'dropdown'              => __( 'Dropdown', 'iys-panel-wp-form' ),
				'emailAddress'          => __( 'Email address', 'iys-panel-wp-form' ),
				'fieldType'             => __( 'Field type', 'iys-panel-wp-form' ),
				'fieldLabel'            => __( 'Field label', 'iys-panel-wp-form' ),
				'formAction'            => __( 'Form action', 'iys-panel-wp-form' ),
				'formActionDescription' => __( 'This field will allow your visitors to choose whether they would like to subscribe or unsubscribe', 'iys-panel-wp-form' ),
				'formFields'            => __( 'Form fields', 'iys-panel-wp-form' ),
				'forceRequired'         => __( 'This field is marked as required in İYS Panel.', 'iys-panel-wp-form' ),
				'initialValue'          => __( 'Initial value', 'iys-panel-wp-form' ),
				'interestCategories'    => __( 'Interest categories', 'iys-panel-wp-form' ),
				'isFieldRequired'       => __( 'Is this field required?', 'iys-panel-wp-form' ),
				'listChoice'            => __( 'List choice', 'iys-panel-wp-form' ),
				'listChoiceDescription' => __( 'This field will allow your visitors to choose a list to subscribe to.', 'iys-panel-wp-form' ),
				'listFields'            => __( 'List fields', 'iys-panel-wp-form' ),
				'min'                   => __( 'Min', 'iys-panel-wp-form' ),
				'max'                   => __( 'Max', 'iys-panel-wp-form' ),
				'noAvailableFields'     => __( 'No available fields. Did you select a İYS Panel list in the form settings?', 'iys-panel-wp-form' ),
				'optional'              => __( 'Optional', 'iys-panel-wp-form' ),
				'placeholder'           => __( 'Placeholder', 'iys-panel-wp-form' ),
				'placeholderHelp'       => __( 'Text to show when field has no value.', 'iys-panel-wp-form' ),
				'placeholderDateHelp'   => __( 'Date formats should be in (DD.MM.YYYY) (DD/MM/YYYY) or (DD-MM-YYYY) format. You may clarify them on the placeholder.', 'iys-panel-wp-form' ),
				'preselect'             => __( 'Preselect', 'iys-panel-wp-form' ),
				'remove'                => __( 'Remove', 'iys-panel-wp-form' ),
				'radioButtons'          => __( 'Radio buttons', 'iys-panel-wp-form' ),
				'streetAddress'         => __( 'Street Address', 'iys-panel-wp-form' ),
				'state'                 => __( 'State', 'iys-panel-wp-form' ),
				'subscribe'             => __( 'Subscribe', 'iys-panel-wp-form' ),
				'submitButton'          => __( 'Submit button', 'iys-panel-wp-form' ),
				'wrapInParagraphTags'   => __( 'Wrap in paragraph tags?', 'iys-panel-wp-form' ),
				'value'                 => __( 'Value', 'iys-panel-wp-form' ),
				'valueHelp'             => __( 'Text to prefill this field with.', 'iys-panel-wp-form' ),
				'zip'                   => __( 'ZIP', 'iys-panel-wp-form' ),
			)
		);
	}

	/**
	 * @param $items
	 *
	 * @return mixed
	 */
	public function add_menu_item( $items ) {
		$items['forms'] = array(
			'title'         => esc_html__( 'Create Form', 'iys-panel-wp-form' ),
			'text'          => esc_html__( 'Create Form', 'iys-panel-wp-form' ),
			'slug'          => 'forms',
			'callback'      => array( $this, 'show_forms_page' ),
			'load_callback' => array( $this, 'redirect_to_form_action' ),
			'position'      => 10,
		);

		return $items;
	}

	/**
	 * Act on the "add form" form
	 */
	public function process_add_form() {
		$form_data    = $_POST['im4wp_form'];
		$form_content = include IM4WP_PLUGIN_DIRDIR . '/config/default-form-content.php';

		// Fix for MultiSite stripping KSES for roles other than administrator
		remove_all_filters( 'content_save_pre' );

		$form_id = wp_insert_post(
			array(
				'post_type'    => 'im4wp-form',
				'post_status'  => 'publish',
				'post_title'   => sanitize_text_field($form_data['name']),
				'post_content' => $form_content,
			)
		);

		// if settings were passed, save those too.
		if ( isset( $form_data['settings'] ) ) {
			update_post_meta( $form_id, '_im4wp_settings', $form_data['settings'] );
		}

		// set default form ID
		$this->set_default_form_id( $form_id );

		$this->messages->flash( esc_html__( 'Form saved.', 'iys-panel-wp-form' ) );
		$edit_form_url = im4wp_get_edit_form_url( $form_id );
		wp_redirect( $edit_form_url );
		exit;
	}

	/**
	 * Saves a form to the database
	 * @param int $form_id
	 * @param array $data
	 * @return int
	 */
	private function save_form( $form_id, array $data ) {
		$keys = array(
			'settings' => array(),
			'messages' => array(),
			'name'     => '',
			'content'  => '',
		);
		$data = array_merge( $keys, $data );
		$data = $this->sanitize_form_data( $data );

		$post_data = array(
			'ID' => $form_id,
			'post_type'    => 'im4wp-form',
			'post_status'  => ! empty( $data['status'] ) ? $data['status'] : 'publish',
			'post_title'   => $data['name'],
			'post_content' => $data['content'],
            'post_excerpt' => $data['description']
		);

		// Fix for MultiSite stripping KSES for roles other than administrator
		remove_all_filters( 'content_save_pre' );
		wp_insert_post( $post_data );

		// merge new settings  with current settings to allow passing partial data
		$current_settings = get_post_meta( $form_id, '_im4wp_settings', true );
        if(!isset($current_settigs['wordpressId'])) {
            $current_settigs['wordpressId'] = uniqid();
        }

		if ( is_array( $current_settings ) ) {
			$data['settings'] = array_merge( $current_settings, $data['settings'] );
            if (!isset($data['settings']['wordpressId'])) {
                $data['settings']['wordpressId'] = uniqid();
            }
		}
		update_post_meta( $form_id, '_im4wp_settings', $data['settings'] );

        $recipientGroupIds = implode(',', $data['settings']['lists']);
        $iysBrandCodes = implode (',', $data['settings']['brands']);


        $client = im4wp_get_api_v1()->get_client();
        // FIXME: Burda bir hata olursa ne olacagina karar ver

        $params = array(
            'formName' => $data['name'],
            'wordpressId' => $data['settings']['wordpressId'],
            'successfulRedirectURL' => $data['settings']['redirect_after_double_optin'],
            'subscribeRedirectURL' => $data['settings']['redirect'],
            'recipientGroupIds' => $recipientGroupIds,
            'iysBrandCodes' => $iysBrandCodes,
        );

        // Originator Id ile Payment Profile Id birlikte geliyor
        if (strpos($data['settings']['originator'], '_') !== false) {
            $_exp = explode('_', $data['settings']['originator']);
            $params['smsOriginatorId'] = $_exp[0];
            $params['smsPaymentProfileId'] = $_exp[1];
        } else{
            $params['smsOriginatorId'] = $data['settings']['originator'];
        }

        if (isset($data['settings']['lang']) && $data['settings']['lang'] == 'en') {
            $params['useEnglishMessages'] = 'true';
        } else {
            $params['useEnglishMessages'] = 'false';
        }

        try {
            $response = $client->get( 'updateForm', $params );

            if ($response->status->code == "21") {
                $response = $client->get( 'createForm', $params );
                // FIXME: Burdaki donusu de kontrol et
            }

        } catch ( IM4WP_API_Connection_Exception $e ) {
            // ignore timeout errors as this is likely due to iyspanel being slow to calculate the lists.stats.member_count property
            // keep going so we can at least pull-in all other lists
            // FIXME: Birseyler hatali
        } catch ( IM4WP_API_Exception $e ) {
            // break on other errors, like "API key missing"etc.
            // FIXME: Birseyler hatali
        }



		// save form messages in individual meta keys
		foreach ( $data['messages'] as $key => $message ) {
			update_post_meta( $form_id, 'text_' . $key, $message );
		}

		/**
		 * Runs right after a form is updated.
		 *
		 * @since 3.0
		 *
		 * @param int $form_id
		 */
		do_action( 'im4wp_save_form', $form_id );

		return $form_id;
	}

	/**
	 * @param array $data
	 * @return array
	 */
	public function sanitize_form_data( $data ) {
		$raw_data = $data;

		// strip <form> tags from content
		$data['content'] = preg_replace( '/<\/?form(.|\s)*?>/i', '', $data['content'] );

		// replace lowercased name="name" to prevent 404
		$data['content'] = str_ireplace( ' name=\"name\"', ' name=\"NAME\"', $data['content'] );

		// sanitize text fields
		$data['settings']['redirect'] = sanitize_text_field( $data['settings']['redirect'] );

		// strip tags from messages
		foreach ( $data['messages'] as $key => $message ) {
			$data['messages'][ $key ] = strip_tags( $message, '<strong><b><br><a><script><u><em><i><span><img>' );
		}

		// make sure lists is an array
		if ( ! isset( $data['settings']['lists'] ) ) {
			$data['settings']['lists'] = array();
		}

		$data['settings']['lists'] = array_filter( (array) $data['settings']['lists'] );

		/**
		 * Filters the form data just before it is saved.
		 *
		 * @param array $data Sanitized array of form data.
		 * @param array $raw_data Raw array of form data.
		 *
		 * @since 3.0.8
		 * @ignore
		 */
		$data = (array) apply_filters( 'im4wp_form_sanitized_data', $data, $raw_data );

		return $data;
	}

	/**
	 * Saves a form
	 */
	public function process_save_form() {
		// save global settings (if submitted)
		if ( isset( $_POST['im4wp'] ) && is_array( $_POST['im4wp'] ) ) {
			$options = get_option( 'im4wp', array() );
			$posted  = $_POST['im4wp'];
			foreach ( $posted as $key => $value ) {
				$options[ $key ] = trim( $value );
			}
			update_option( 'im4wp', $options );
		}

		// update form, settings and messages
		$form_id         = (int) $_POST['im4wp_form_id'];
		$form_data       = $_POST['im4wp_form'];
		$this->save_form( $form_id, $form_data );
		$this->set_default_form_id( $form_id );
		$this->messages->flash( esc_html__( 'Form saved.', 'iys-panel-wp-form' ) );
	}

	/**
	 * @param int $form_id
	 */
	private function set_default_form_id( $form_id ) {
		$default_form_id = get_option( 'im4wp_default_form_id', 0 );

		if ( empty( $default_form_id ) ) {
			update_option( 'im4wp_default_form_id', $form_id );
		}
	}

	/**
	 * Goes through each form and aggregates array of stylesheet slugs to load.
	 *
	 * @hooked `im4wp_save_form`
	 */
	public function update_form_stylesheets() {
		$stylesheets = array();

		$forms = im4wp_get_forms();
		foreach ( $forms as $form ) {
			$stylesheet = $form->get_stylesheet();

			if ( ! empty( $stylesheet ) && ! in_array( $stylesheet, $stylesheets, true ) ) {
				$stylesheets[] = $stylesheet;
			}
		}

		update_option( 'im4wp_form_stylesheets', $stylesheets );
	}

	/**
	 * Redirect to correct form action
	 *
	 * @ignore
	 */
	public function redirect_to_form_action() {
		if ( ! empty( $_GET['view'] ) ) {
			return;
		}

        $redirect_url = im4wp_get_add_form_url();

		if ( headers_sent() ) {
			echo sprintf( '<meta http-equiv="refresh" content="0;url=%s" />', $redirect_url );
		} else {
			wp_redirect( $redirect_url );
		}

		exit;
	}

	/**
	 * Show the Forms Settings page
	 *
	 * @internal
	 */
	public function show_forms_page() {
		$view = ! empty( $_GET['view'] ) ? $_GET['view'] : '';

		/**
		 * @ignore
		 */
		do_action( 'im4wp_admin_show_forms_page', $view );

		/**
		 * @ignore
		 */
		do_action( 'im4wp_admin_show_forms_page-' . $view );
	}

	/**
	 * Show the "Edit Form" page
	 *
	 * @internal
	 */
	public function show_edit_page() {
		$form_id     = ( ! empty( $_GET['form_id'] ) ) ? (int) $_GET['form_id'] : 0;
		$iyspanel   = new IM4WP_MailChimp();
		$lists       = $iyspanel->get_lists();
		$brands      = $iyspanel->get_brands();
		$originators = $iyspanel->get_originators();

		try {
			$form = im4wp_get_form( $form_id );
		} catch ( Exception $e ) {
			echo '<h2>', esc_html__( 'Form not found.', 'iys-panel-wp-form' ), '</h2>';
			echo '<p>', esc_html($e->getMessage()), '</p>';
			echo '<p><a href="javascript:history.go(-1);"> &lsaquo; ', esc_html__( 'Go back', 'iys-panel-wp-form' ), '</a></p>';
			return;
		}

		$opts       = $form->settings;
		$active_tab = ( isset( $_GET['tab'] ) ) ? $_GET['tab'] : 'fields';

		$form_preview_url = add_query_arg(
			array(
				'im4wp_preview_form' => $form_id,
			),
			site_url( '/', 'admin' )
		);

		require __DIR__ . '/views/edit-form.php';
	}

	/**
	 * Shows the "Add Form" page
	 *
	 * @internal
	 */
	public function show_add_page() {
		$iyspanel       = new IM4WP_MailChimp();
		$lists           = $iyspanel->get_lists();
		$brands           = $iyspanel->get_brands();
		$number_of_lists = count( $lists );
		require __DIR__ . '/views/add-form.php';
	}

	/**
	 * Get URL for a tab on the current page.
	 *
	 * @since 3.0
	 * @internal
	 * @param $tab
	 * @return string
	 */
	public function tab_url( $tab ) {
		return add_query_arg( array( 'tab' => $tab ), remove_query_arg( 'tab' ) );
	}

	/**
	 * Registers UI for when shortcake is activated
	 */
	public function register_shortcake_ui() {
		$assets = new IM4WP_Form_Asset_Manager();
		$assets->load_stylesheets();

		$forms   = im4wp_get_forms();
		$options = array();
		foreach ( $forms as $form ) {
			$options[ $form->ID ] = $form->name;
		}

		/**
		 * Register UI for your shortcode
		 *
		 * @param string $shortcode_tag
		 * @param array $ui_args
		 */
		shortcode_ui_register_for_shortcode(
			'im4wp_form',
			array(
				'label'         => esc_html__( 'İYS Panel Sign-Up Form', 'iys-panel-wp-form' ),
				'listItemImage' => 'dashicons-feedback',
				'attrs'         => array(
					array(
						'label'   => esc_html__( 'Select the form to show', 'iys-panel-wp-form' ),
						'attr'    => 'id',
						'type'    => 'select',
						'options' => $options,
					),
				),
			)
		);
	}
}
