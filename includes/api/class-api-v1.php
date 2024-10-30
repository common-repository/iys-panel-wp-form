<?php

/**
 * Class IM4WP_API_V1
 */
class IM4WP_API_V1 {

	/**
	 * @var IM4WP_API_V1_Client
	 */
	protected $client;

	/**
	 * Constructor
	 *
	 * @param string $api_key
	 */
	public function __construct( $api_key ) {
		$this->client = new IM4WP_API_V1_Client( $api_key );
	}

	/**
	 * Gets the API client to perform raw API calls.
	 *
	 * @return IM4WP_API_V1_Client
	 */
	public function get_client() {
		return $this->client;
	}

	/**
	 * Pings the İYS Panel API to see if we're connected
	 *
	 * @return boolean
	 * @throws IM4WP_API_Exception
	 */
	public function is_connected() {
		$data      = $this->client->get( 'getDetailsForAccount', array( ) );
        // FIXME: status 97 ise domain ekleme hakki bitmis
		$connected = is_object( $data ) && isset( $data->status->code ) && $data->status->code == "0";
		return $connected;
	}

    /**
	 * Pings the İYS Panel API to see if we're connected
	 *
	 * @return boolean
	 * @throws IM4WP_API_Exception
	 */
	public function register_to_api() {
		$data      = $this->client->get( 'registerDomain', array( ) );
        // FIXME: status 97 ise domain ekleme hakki bitmis
		$connected = is_object( $data ) && isset( $data->status->code ) && $data->status->code == "0";
		return $connected;
	}

	/**
	 * @param $email_address
	 *
	 * @return string
	 */
	public function get_subscriber_hash( $email_address ) {
		return md5( strtolower( trim( $email_address ) ) );
	}

	/**
	 * Add a new member to a İYS Panel list.
	 *
	 * @link https://developer.iyspanel.com/documentation/iyspanel/reference/lists/members/#create-post_lists_list_id_members
	 *
	 * @param string $list_id
	 * @param array $args
	 *
	 * @return object
	 * @throws IM4WP_API_Exception
	 */
	public function add_new_list_member( $list_id, array $args ) {
		// make sure we're sending an object as the İYS Panel schema requires this
		if ( isset( $args['merge_fields'] ) ) {
			$args['merge_fields'] = (object) $args['merge_fields'];
		}

		if ( isset( $args['interests'] ) ) {
			$args['interests'] = (object) $args['interests'];
		}

		return $this->client->get( 'createOptIn', $args );
	}

	/**
	 * @return string
	 */
	public function get_last_response_body() {
		return $this->client->get_last_response_body();
	}

	/**
	 * @return array
	 */
	public function get_last_response_headers() {
		return $this->client->get_last_response_headers();
	}
}
