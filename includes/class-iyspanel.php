<?php

/**
 * Internal class for dealing with common API requests.
 * Please don't use directly as this code can be subject to backwards incompatible changes.
 *
 * @access private
 * @ignore
 * @internal
 */
class IM4WP_MailChimp {


	/**
	 * @var string
	 */
	public $error_code = '';

	/**
	 * @var string
	 */
	public $error_message = '';

	/**
	 *
	 * Sends a subscription request to the İYS Panel API
	 *
	 * @param string  $list_id           The list id to subscribe to
	 * @param string  $email_address             The email address to subscribe
	 * @param array    $args
	 * @param bool $update_existing   Update information if this email is already on list?
	 * @param bool $replace_interests Replace interest groupings, only if update_existing is true.
	 * @return object
	 * @throws Exception
	 */
	public function list_subscribe( $list_id, $email_address, array $form_data = array(), array $extra_args = array(), array $settings = array())  {
		$this->reset_error();

        $update_existing = $settings['update_existing'];
        $replace_interests = $settings['replace_interests'];

		$args = array(
			'email' => $email_address,
            'wordpressId' => $settings['wordpressId'],
		);
        $args = array_merge($args, $form_data);

        $api  = $this->get_api();

		try {
			$data                      = $api->add_new_list_member( $list_id, $args );
			$data->was_already_on_list = false;
		} catch ( IM4WP_API_Exception $e ) {
			$this->error_code    = $e->getCode();
			$this->error_message = $e;
			return null;
		}

		return $data;
	}

	/**
	 * Format tags to send to İYS Panel.
	 *
	 * @since 4.7.9
	 * @param $iyspanel_tags array existent user tags
	 * @param $new_tags array new tags to add
	 * @return array
	 */
	private function merge_and_format_member_tags( $iyspanel_tags, $new_tags ) {
		$iyspanel_tags = array_map(
			function ( $tag ) {
				return $tag->name;
			},
			$iyspanel_tags
		);

		$tags = array_unique( array_merge( $iyspanel_tags, $new_tags ), SORT_REGULAR );

		return array_map(
			function ( $tag ) {
				return array(
				    'name' => $tag,
				    'status' => 'active',
				);
			},
			$tags
		);
	}

	/**
	 *  Post the tags on a list member.
	 *
	 * @param $iyspanel_list_id string The list id to subscribe to
	 * @param $iyspanel_member stdClass iyspanel user informations
	 * @param $new_tags array tags to add to the user
	 *
	 * @return bool
	 * @throws Exception
	 * @since 4.7.9
	 */
	private function list_add_tags_to_subscriber( $iyspanel_list_id, $iyspanel_member, array $new_tags ) {
		// do nothing if no tags given
		if ( count( $new_tags ) === 0 ) {
			return true;
		}

		$api = $this->get_api();
		$data = array(
			'tags' => $this->merge_and_format_member_tags( $iyspanel_member->tags, $new_tags ),
		);

		try {
			$api->update_list_member_tags( $iyspanel_list_id, $iyspanel_member->email_address, $data );
		} catch ( IM4WP_API_Exception $ex ) {
			// fail silently
			return false;
		}

		return true;
	}

	/**
	 * Changes the subscriber status to "unsubscribed"
	 *
	 * @param string $list_id
	 * @param string $email_address
	 *
	 * @return boolean
	 */
	public function list_unsubscribe( $list_id, $email_address ) {
		$this->reset_error();

		try {
			$this->get_api()->update_list_member( $list_id, $email_address, array( 'status' => 'unsubscribed' ) );
		} catch ( IM4WP_API_Resource_Not_Found_Exception $e ) {
			// if email wasn't even on the list: great.
			return true;
		} catch ( IM4WP_API_Exception $e ) {
			$this->error_code    = $e->getCode();
			$this->error_message = $e;
			return false;
		}

		return true;
	}

	/**
	 * Checks if an email address is on a given list with status "subscribed"
	 *
	 * @param string $list_id
	 * @param string $email_address
	 *
	 * @return boolean
	 * @throws Exception
	 */
	public function list_has_subscriber( $list_id, $email_address ) {
		try {
			$data = $this->get_api()->get_list_member( $list_id, $email_address );
		} catch ( IM4WP_API_Resource_Not_Found_Exception $e ) {
			return false;
		}

		return ! empty( $data->id ) && $data->status === 'subscribed';
	}

	/**
	 * @param string $list_id
	 * @return array
	 * @throws Exception
	 */
	public function get_list_interest_categories( $list_id ) {
		$transient_key = sprintf( 'im4wp_list_%s_ic', $list_id );
		$cached        = get_transient( $transient_key );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		$api = $this->get_api();

		try {
			// fetch list interest categories
			$interest_categories = $api->get_list_interest_categories(
				$list_id,
				array(
					'count'  => 100,
					'fields' => 'categories.id,categories.title,categories.type',
				)
			);
		} catch ( IM4WP_API_Exception $e ) {
			return array();
		}

		foreach ( $interest_categories as $interest_category ) {
			$interest_category->interests = array();

			try {
				// fetch groups for this interest
				$interests_data = $api->get_list_interest_category_interests(
					$list_id,
					$interest_category->id,
					array(
						'count'  => 100,
						'fields' => 'interests.id,interests.name',
					)
				);
				foreach ( $interests_data as $interest_data ) {
					$interest_category->interests[ (string) $interest_data->id ] = $interest_data->name;
				}
			} catch ( IM4WP_API_Exception $e ) {
				// ignore
			}
		}

		set_transient( $transient_key, $interest_categories, HOUR_IN_SECONDS * 24 );
		return $interest_categories;
	}

	/**
	 * Get İYS Panel lists, from cache or remote API.
	 *
	 * @param boolean $skip_cache Whether to force a result by hitting İYS Panel API
	 * @return array
	 */
	public function get_lists( $skip_cache = false ) {
		$cache_key = 'im4wp_iyspanel_lists';
		$cached    = get_transient( $cache_key );

		if ( is_array( $cached ) && ! $skip_cache ) {
			return $cached;
		}

		$lists = $this->fetch_lists();

		/**
		 * Filters the cache time for İYS Panel lists configuration, in seconds. Defaults to 24 hours.
		 */
		$cache_ttl = (int) apply_filters( 'im4wp_lists_count_cache_time', HOUR_IN_SECONDS * 24 );

		// make sure cache ttl is not lower than 60 seconds
		$cache_ttl = max( 60, $cache_ttl );
		set_transient( $cache_key, $lists, $cache_ttl );
		return $lists;
	}

	private function fetch_lists() {
        // $client = new IM4WP_API_V1_Client( $api_key );
        $client = im4wp_get_api_v1()->get_client();
		// $client = $this->get_api()->get_client();
		$data = array();

		// increase time limits
		@set_time_limit( 180 );
		add_filter(
			'im4wp_http_request_args',
			function( $args ) {
				$args['timeout'] = 30;
				return $args;
			}
		);

        try {
            $data = $client->get( 'getDetailsForAccount', array( ) );
        } catch ( IM4WP_API_Connection_Exception $e ) {
            // ignore timeout errors as this is likely due to iyspanel being slow to calculate the lists.stats.member_count property
            // keep going so we can at least pull-in all other lists
            // FIXME: Birseyler hatali
        } catch ( IM4WP_API_Exception $e ) {
            // break on other errors, like "API key missing"etc.
            // FIXME: Birseyler hatali
        }

		// key by list ID
		$lists = array();
        // FIXME: Verinin duzgun geldigine emin ol
        // $lists_data = $data['content']['recipient_groups'];
        $lists_data = $data->content->recipient_groups;
		foreach ( $lists_data as $list_data ) {
            $list =  array('id' => $list_data->group_id,
                           'name' => $list_data->group_name,
                           'web_id' => 0,
                           'marketing_permissions' => false,
                           'stats' => array('member_count' => 0));
			$lists[ "$list_data->group_id" ] = (object) $list;
		}

		return $lists;
	}

	/**
	 * @param string $list_id
	 * @return object|null
	 */
	public function get_list( $list_id ) {
		$lists = $this->get_lists();
		return isset( $lists[ "$list_id" ] ) ? $lists[ "$list_id" ] : null;
	}

	/**
	 * Fetch lists data from İYS Panel.
	 */
	public function refresh_lists() {
		$lists = $this->get_lists( true );

		foreach ( $lists as $list_id => $list ) {
			$transient_key = sprintf( 'im4wp_list_%s_mf', $list_id );
			delete_transient( $transient_key );

			$transient_key = sprintf( 'im4wp_list_%s_ic', $list_id );
			delete_transient( $transient_key );
		}

		return ! empty( $lists );
	}

    /**
	 * Get brands, from cache or remote API.
	 *
	 * @param boolean $skip_cache Whether to force a result by hitting API
	 * @return array
	 */
	public function get_brands( $skip_cache = false ) {
		$cache_key = 'im4wp_brands';
		$cached    = get_transient( $cache_key );

		if ( is_array( $cached ) && ! $skip_cache ) {
			return $cached;
		}

		$brands = $this->fetch_brands();

		/**
		 * Filters the cache time for İYS Panel lists configuration, in seconds. Defaults to 24 hours.
		 */
		$cache_ttl = (int) apply_filters( 'im4wp_brands_count_cache_time', HOUR_IN_SECONDS * 24 );

		// make sure cache ttl is not lower than 60 seconds
		$cache_ttl = max( 60, $cache_ttl );
		set_transient( $cache_key, $brands, $cache_ttl );
		return $brands;
	}

    private function fetch_brands() {
        $client = im4wp_get_api_v1()->get_client();
		$data = array();

		// increase time limits
		@set_time_limit( 180 );
		add_filter(
			'im4wp_http_request_args',
			function( $args ) {
				$args['timeout'] = 30;
				return $args;
			}
		);

        try {
            $data = $client->get( 'getDetailsForAccount', array( ) );
        } catch ( IM4WP_API_Connection_Exception $e ) {
            // ignore timeout errors as this is likely due to iyspanel being slow to calculate the lists.stats.member_count property
            // keep going so we can at least pull-in all other lists
            // FIXME: Birseyler hatali
        } catch ( IM4WP_API_Exception $e ) {
            // break on other errors, like "API key missing"etc.
            // FIXME: Birseyler hatali
        }

		// key by list ID
		$brands = array();
        // FIXME: Verinin duzgun geldigine emin ol
        // $lists_data = $data['content']['recipient_groups'];
        $brands_data = $data->content->iys_details->brands;
		foreach ( $brands_data as $brand_data ) {
            $brand =  array('id'   => $brand_data->brand_code,
                            'name' => $brand_data->brand_name,
                            'originators' => array());

            foreach($brand_data->related_originators as $originator) {
                $_orig = array(
                    'id'      => $originator->originator_id,
                    'name'    => $originator->originator_value,
                    'service' => $originator->service
                );

                if ($originator->payment_profile_id) {
                    $_orig['payment_profile_id'] = $originator->payment_profile_id;
                }
                $brand['originators'][] = (object) $_orig;
            }

			$brands[ "$brand_data->brand_code" ] = (object) $brand;
		}

		return $brands;
	}

    /**
	 * @param string $list_id
	 * @return object|null
	 */
	public function get_brand( $brand_id ) {
		$brands = $this->get_brands();
		return isset( $brands[ "$brand_id" ] ) ? $brands[ "$brand_id" ] : null;
	}

	/**
	 * Fetch brands data from API.
	 */
	public function refresh_brands() {
		$brands = $this->get_brands( true );

		foreach ( $brands as $brand_id => $brand ) {
			$transient_key = sprintf( 'im4wp_brand_%s_mf', $brand_id );
			delete_transient( $transient_key );

			$transient_key = sprintf( 'im4wp_brand_%s_ic', $brand_id );
			delete_transient( $transient_key );
		}

		return ! empty( $brands );
	}

    /**
	 * Get originators, from cache or remote API.
	 *
	 * @param boolean $skip_cache Whether to force a result by hitting API
	 * @return array
	 */
	public function get_originators( $skip_cache = false ) {
		$cache_key = 'im4wp_originators';
		$cached    = get_transient( $cache_key );

		if ( is_array( $cached ) && ! $skip_cache ) {
			return $cached;
		}

		$originators = $this->fetch_originators();

		/**
		 * Filters the cache time for İYS Panel lists configuration, in seconds. Defaults to 24 hours.
		 */
		$cache_ttl = (int) apply_filters( 'im4wp_originators_count_cache_time', HOUR_IN_SECONDS * 24 );

		// make sure cache ttl is not lower than 60 seconds
		$cache_ttl = max( 60, $cache_ttl );
		set_transient( $cache_key, $originators, $cache_ttl );
		return $originators;
	}

    private function fetch_originators() {
        $client = im4wp_get_api_v1()->get_client();
		$data = array();

		// increase time limits
		@set_time_limit( 180 );
		add_filter(
		    'im4wp_http_request_args',
			function( $args ) {
				$args['timeout'] = 30;
				return $args;
			}
		);

        try {
            $data = $client->get( 'getDetailsForAccount', array( ) );
        } catch ( IM4WP_API_Connection_Exception $e ) {
            // ignore timeout errors as this is likely due to iyspanel being slow to calculate the lists.stats.member_count property
            // keep going so we can at least pull-in all other lists
            // FIXME: Birseyler hatali
        } catch ( IM4WP_API_Exception $e ) {
            // break on other errors, like "API key missing"etc.
            // FIXME: Birseyler hatali
        }

		// key by list ID
		$originators = array();
        // FIXME: Verinin duzgun geldigine emin ol
        // $lists_data = $data['content']['recipient_groups'];
        $originators_data = $data->content->originators;
		foreach ( $originators_data as $originator_data ) {
            $originator = array('id'   => $originator_data->originator_id,
                                'name' => $originator_data->originator_value);
			$originators[ "$originator_data->originator_id" ] = (object) $originator;
		}

		return $originators;
	}

    /**
	 * @param string $list_id
	 * @return object|null
	 */
	public function get_originator( $originator_id ) {
		$originators = $this->get_originators();
		return isset( $originators[ "$originator_id" ] ) ? $originators[ "$originator_id" ] : null;
	}

	/**
	 * Fetch originators data from API.
	 */
	public function refresh_originators() {
		$originators = $this->get_originators( true );

		foreach ( $originators as $originator_id => $originator ) {
			$transient_key = sprintf( 'im4wp_originator_%s_mf', $originator_id );
			delete_transient( $transient_key );

			$transient_key = sprintf( 'im4wp_originator_%s_ic', $originator_id );
			delete_transient( $transient_key );
		}

		return ! empty( $originators );
	}

	/**
	 * Returns number of subscribers on given lists.
	 *
	 * @param array|string $list_ids Array of list ID's, or single string.
	 * @return int Total # subscribers for given lists.
	 */
	public function get_subscriber_count( $list_ids ) {
		// make sure we're getting an array
		if ( ! is_array( $list_ids ) ) {
			$list_ids = array( $list_ids );
		}

		// if we got an empty array, return 0
		if ( empty( $list_ids ) ) {
			return 0;
		}

		$lists = $this->get_lists();

		// start calculating subscribers count for all given list ID's combined
		$count = 0;
		foreach ( $list_ids as $list_id ) {

			if ( ! isset( $lists[ "$list_id" ] ) ) {
				continue;
			}

			$list   = $lists[ "$list_id" ];
			$count += $list->stats->member_count;
		}

		/**
		 * Filters the total subscriber_count for the given List ID's.
		 *
		 * @since 2.0
		 * @param string $count
		 * @param array $list_ids
		 */
		return apply_filters( 'im4wp_subscriber_count', $count, $list_ids );
	}

	/**
	 * Resets error properties.
	 */
	public function reset_error() {
		$this->error_message = '';
		$this->error_code    = '';
	}

	/**
	 * @return bool
	 */
	public function has_error() {
		return ! empty( $this->error_code );
	}

	/**
	 * @return string
	 */
	public function get_error_message() {
		return $this->error_message;
	}

	/**
	 * @return string
	 */
	public function get_error_code() {
		return $this->error_code;
	}

	/**
	 * @return IM4WP_API_V1
	 * @throws Exception
	 */
	private function get_api() {
		return im4wp( 'api' );
	}

    public function do_2fa($key, $smsKey) {
        // $client = new IM4WP_API_V1_Client( $api_key );
        $client = im4wp_get_api_v1()->get_client();
		// $client = $this->get_api()->get_client();
		$data = array();

		// increase time limits
		@set_time_limit( 180 );
		add_filter(
			'im4wp_http_request_args',
			function( $args ) {
				$args['timeout'] = 30;
				return $args;
			}
		);

        $params = array( 'activationKey' => $key, 'k' => $smsKey );

        try {
            $data = $client->get( 'confirmOptInViaSMS', $params);
        } catch ( IM4WP_API_Connection_Exception $e ) {
            // ignore timeout errors as this is likely due to iyspanel being slow to calculate the lists.stats.member_count property
            // keep going so we can at least pull-in all other lists
            // FIXME: Birseyler hatali
        } catch ( IM4WP_API_Exception $e ) {
            // break on other errors, like "API key missing"etc.
            // FIXME: Birseyler hatali
        }

		return $data;
	}

    public function re_send_2fa($wordpressId, $activationKey) {
        // $client = new IM4WP_API_V1_Client( $api_key );
        $client = im4wp_get_api_v1()->get_client();
		// $client = $this->get_api()->get_client();
		$data = array();

		// increase time limits
		@set_time_limit( 180 );
		add_filter(
			'im4wp_http_request_args',
			function( $args ) {
				$args['timeout'] = 30;
				return $args;
			}
		);

        $params = array( 'activationKey' => $activationKey, 'wordpressId' => $wordpressId );

        try {
            $data = $client->get( 'reSend2FACode', $params );
        } catch ( IM4WP_API_Connection_Exception $e ) {
            // ignore timeout errors as this is likely due to iyspanel being slow to calculate the lists.stats.member_count property
            // keep going so we can at least pull-in all other lists
            // FIXME: Birseyler hatali
        } catch ( IM4WP_API_Exception $e ) {
            // break on other errors, like "API key missing"etc.
            // FIXME: Birseyler hatali
        }

		return $data;
	}
}
