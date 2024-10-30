<?php

class IM4WP_Admin_Ajax {


	/**
	 * @var IM4WP_Admin_Tools
	 */
	protected $tools;

	/**
	 * IM4WP_Admin_Ajax constructor.
	 *
	 * @param IM4WP_Admin_Tools $tools
	 */
	public function __construct( IM4WP_Admin_Tools $tools ) {
		$this->tools = $tools;
	}

	/**
	 * Hook AJAX actions
	 */
	public function add_hooks() {
		add_action( 'wp_ajax_im4wp_renew_iyspanel_lists', array( $this, 'refresh_iyspanel_lists' ) );
		add_action( 'wp_ajax_im4wp_renew_brands', array( $this, 'refresh_brands' ) );
		add_action( 'wp_ajax_im4wp_renew_originators', array( $this, 'refresh_originators' ) );
		add_action( 'wp_ajax_im4wp_get_list_details', array( $this, 'get_list_details' ) );
	}

	/**
	 * Empty lists cache & fetch lists again.
	 */
	public function refresh_iyspanel_lists() {
		if ( ! $this->tools->is_user_authorized() ) {
			wp_send_json( false );
		}

		$iyspanel = new IM4WP_MailChimp();
		$success   = $iyspanel->refresh_lists();
		wp_send_json( $success );
	}

    /**
	 * Empty lists cache & fetch lists again.
	 */
	public function refresh_brands() {
		if ( ! $this->tools->is_user_authorized() ) {
			wp_send_json( false );
		}

		$iyspanel = new IM4WP_MailChimp();
		$success   = $iyspanel->refresh_brands();
		wp_send_json( $success );
	}

    /**
	 * Empty originator cache & fetch originators again.
	 */
	public function refresh_originators() {
		if ( ! $this->tools->is_user_authorized() ) {
			wp_send_json( false );
		}

		$iyspanel = new IM4WP_MailChimp();
		$success   = $iyspanel->refresh_originators();
		wp_send_json( $success );
	}

	/**
	 * Retrieve details (merge fields and interest categories) for one or multiple lists in İYS Panel
	 * @throws IM4WP_API_Exception
	 */
	public function get_list_details() {
		$list_ids  = (array) explode( ',', $_GET['ids'] );
		$data      = array();

        $fields = array(
            array("tag" => "EMAIL", "name" => "Email address", "required" => false, "type" => "email", "options" => array(), "public" => true),
            array("tag" => "FNAME", "name" => esc_html__( 'First Name', 'iys-panel-wp-form' ), "type" => "text", "required" => false, "default_value" => "", "public" => true,"options" => array("size" => 25)),
            array("tag" => "LNAME", "name" => esc_html__( 'Last Name', 'iys-panel-wp-form' ), "type" => "text", "required" => false, "default_value" => "", "public" => true, "options" => array("size" => 25)),
            array("tag" => "TCKN", "name" => esc_html__( 'National ID Number', 'iys-panel-wp-form' ), "type" => "text", "required" => false, "default_value" => "", "public" => true, "options" => array("size" => 11)),
            array("tag" => "GSM", "name" => esc_html__( 'GSM', 'iys-panel-wp-form' ), "type" => "phone", "required" => false, "default_value" => "", "public" => false, "options" => array()),
            array("tag" => "PHONE", "name" => esc_html__( 'Landline', 'iys-panel-wp-form' ), "type" => "phone", "required" => false, "default_value" => "", "public" => false, "options" => array()),
            array("tag" => "FAX", "name" => esc_html__( 'Fax', 'iys-panel-wp-form' ), "type" => "phone", "required" => false, "default_value" => "", "public" => false, "options" => array()),
            array("tag" => "BIRTHDAY", "name" => esc_html__( 'Birthday', 'iys-panel-wp-form' ), "type" => "birthday", "required" => false, "default_value" => "", "public" => true, "options" => array("date_format" => "DD\/MM\/YYYY")),
            array("tag" => "ADDRESS", "name" => "Address", "type" => "address", "required" => false, "default_value" => "", "public" => false, "options" => array("default_country" => 164)),
        );
        
		foreach ( $list_ids as $list_id ) {
			$data[]              = (object) array(
				'id'                  => "d319bbaf38",
				'merge_fields'        => $fields,
				'interest_categories' => array(),
			);
		}
		wp_send_json( $data );
	}
}
