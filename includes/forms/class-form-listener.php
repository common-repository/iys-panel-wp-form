<?php

/**
 * Class IM4WP_Form_Listener
 *
 * @since 3.0
 * @access private
 */
class IM4WP_Form_Listener {


	/**
	 * @var IM4WP_Form The submitted form instance
	 */
	public $submitted_form;

	public function add_hooks() {
		add_action( 'init', array( $this, 'listen' ) );
	}

	/**
	 * Listen for submitted forms
	 * @return bool
	 */
	public function listen() {
		if ( empty( $_POST['_im4wp_form_id'] ) ) {
			return false;
		}

		// get form instance
		try {
			$form_id = (int) $_POST['_im4wp_form_id'];
			$form    = im4wp_get_form( $form_id );
		} catch ( Exception $e ) {
			return false;
		}

		// sanitize request data
		$request_data = $_POST;
		$request_data = im4wp_sanitize_deep( $request_data );
		$request_data = stripslashes_deep( $request_data );

		// bind request to form & validate
		$form->handle_request( $request_data );
		$form->validate();

		// store submitted form
		$this->submitted_form = $form;

        $two_fa = false;
		// did form have errors?
		if ( ! $form->has_errors() ) {
			switch ( $form->get_action() ) {
				case 'subscribe':
					$this->process_subscribe_form( $form );
					break;

				case 'unsubscribe':
					$this->process_unsubscribe_form( $form );
					break;
                case '2fa':
					$this->process_2fa_form( $form );
					break;
			}
		} else {
			foreach ( $form->errors as $error_code ) {
				$form->add_notice( $form->get_message( $error_code ), 'error' );
			}

			$this->get_log()->info( sprintf( 'Form %d > Submitted with errors: %s', $form->ID, join( ', ', $form->errors ) ) );
		}

		$this->respond( $form );
		return true;
	}

	/**
	 * Process a subscribe form.
	 *
	 * @param IM4WP_Form $form
	 */
	public function process_subscribe_form( IM4WP_Form $form ) {
		$result     = false;
		$iyspanel  = new IM4WP_MailChimp();
		$email_type = $form->get_email_type();
		$data       = $form->get_data();
		$ip_address = im4wp_get_request_ip_address();

		/** @var IM4WP_MailChimp_Subscriber $subscriber */
		$subscriber = null;

		/**
		 * @ignore
		 * @deprecated 4.0
		 */
		$data = apply_filters( 'im4wp_merge_vars', $data );

		/**
		 * @ignore
		 * @deprecated 4.0
		 */
		$data = (array) apply_filters( 'im4wp_form_merge_vars', $data, $form );

		// create a map of all lists with list-specific data
		$mapper = new IM4WP_List_Data_Mapper( $data, $form->get_lists() );

		/** @var IM4WP_MailChimp_Subscriber[] $map */
		$map = $mapper->map();

        $form_data = array();

        $custom_fields = array (
            "GSM"      => "gsm",
            "PHONE"    => "phone",
            "FAX"      => "fax",
            "BIRTHDAY" => "birthday",
            "TCKN"     => "tckn",
            "FNAME"    => "name",
            "LNAME"    => "surname",
            "ADDRESS"  => "address"
        );

        $address_fields = array(
            "addr1",
            "address",
            "city",
            "zip",
            "country"
        );

        foreach($custom_fields as $key => $value) {
            if (isset($data[$key])) {

                if ($key == "ADDRESS" ) {
                    foreach($address_fields as $adKey) {
                        if (array_key_exists($adKey, $data["ADDRESS"])) {
                            $form_data[$adKey] = $data["ADDRESS"][$adKey];
                        }
                    }
                } else {
                    $form_data[$value] = $data[$key];
                }
            }
        }

		// loop through lists
		foreach ( $map as $list_id => $subscriber ) {
			$subscriber->status     = $form->settings['double_optin'] ? 'pending' : 'subscribed';
			$subscriber->email_type = $email_type;
			$subscriber->ip_signup  = $ip_address;
			$subscriber->tags       = $form->get_subscriber_tags();

			/**
			 * Filters subscriber data before it is sent to İYS Panel. Fires for both form & integration requests.
			 *
			 * @param IM4WP_MailChimp_Subscriber $subscriber
			 */
			$subscriber = apply_filters( 'im4wp_subscriber_data', $subscriber );
			if ( ! $subscriber instanceof IM4WP_MailChimp_Subscriber ) {
				continue;
			}

			/**
			 * Filters subscriber data before it is sent to İYS Panel. Only fires for form requests.
			 *
			 * @param IM4WP_MailChimp_Subscriber $subscriber
			 */
			$subscriber = apply_filters( 'im4wp_form_subscriber_data', $subscriber );
			if ( ! $subscriber instanceof IM4WP_MailChimp_Subscriber ) {
				continue;
			}

			// send a subscribe request to İYS Panel for each list
			$result = $iyspanel->list_subscribe( $list_id, $subscriber->email_address, $form_data, $subscriber->to_array(), $form->settings );
            // FIXME: Sadece 1 mail gitsin
            break;
		}

		$log = $this->get_log();

		// do stuff on failure
		if ( ! is_object( $result ) || $result->status->code != "0" ) {
			$error_code    = $iyspanel->get_error_code();
			$error_message = $iyspanel->get_error_message();

			if ( (int) $iyspanel->get_error_code() === 214 ) {
				$form->add_error( 'already_subscribed' );
				$form->add_notice( $form->messages['already_subscribed'], 'notice' );
				$log->warning( sprintf( 'Form %d > %s is already subscribed to the selected list(s)', $form->ID, $data['EMAIL'] ) );
			} else {
				$form->add_error( $error_code );
				$form->add_notice( $form->messages['error'], 'error' );
				$log->error( sprintf( 'Form %d > İYS Panel API error: %s %s', $form->ID, $error_code, $error_message ) );

				/**
				 * Fire action hook so API errors can be hooked into.
				 *
				 * @param IM4WP_Form $form
				 * @param string $error_message
				 */
				do_action( 'im4wp_form_api_error', $form, $error_message );
			}

			// bail
			return;
		}

		// Success! Did we update or newly subscribe?
		if ( $result->status === 'subscribed' && $result->was_already_on_list ) {
			$form->last_event = 'updated_subscriber';
			$form->add_notice( $form->messages['updated'], 'success' );
			$log->info( sprintf( 'Form %d > Successfully updated %s', $form->ID, $data['EMAIL'] ) );

			/**
			 * Fires right after a form was used to update an existing subscriber.
			 *
			 * @since 3.0
			 *
			 * @param IM4WP_Form $form Instance of the submitted form
			 * @param string $email
			 * @param array $data
			 */
			do_action( 'im4wp_form_updated_subscriber', $form, $subscriber->email_address, $data );
		} else {
			$form->last_event = 'subscribed';
            if (array_key_exists('gsm', $form_data) && ! array_key_exists('email', $form_data)) {
                $form->is_2fa = true;
                $form->two_fa_sms_key = $result->content->activationKey;
            } else {
                $form->add_notice( $form->messages['subscribed'], 'success' );
            }
			$log->info( sprintf( 'Form %d > Successfully subscribed %s', $form->ID, $data['EMAIL'] ) );
		}

		/**
		 * Fires right after a form was used to add a new subscriber (or update an existing one).
		 *
		 * @since 3.0
		 *
		 * @param IM4WP_Form $form Instance of the submitted form
		 * @param string $email
		 * @param array $data
		 * @param IM4WP_MailChimp_Subscriber[] $subscriber
		 */
		do_action( 'im4wp_form_subscribed', $form, $subscriber->email_address, $data, $map );
	}

    /**
	 * Process a subscribe form.
	 *
	 * @param IM4WP_Form $form
	 */
	public function process_2fa_form( IM4WP_Form $form ) {
		$result     = false;
		$iyspanel  = new IM4WP_MailChimp();
		$email_type = $form->get_email_type();
		$data       = $form->get_data();
		$ip_address = im4wp_get_request_ip_address();

		/** @var IM4WP_MailChimp_Subscriber $subscriber */
		$subscriber = null;

		$request_data = $_POST;
		$request_data = im4wp_sanitize_deep( $request_data );
		$request_data = stripslashes_deep( $request_data );

        $smsKey = $request_data['_im4wp_form_2fa_sms_key'];
        $key = $request_data['_im4wp_form_2fa_key'];

        $renew2FA = false;
        if (array_key_exists('resend2fa', $request_data)) {
            $renew2FA = true;
            $result = $iyspanel->re_send_2fa($form->settings['wordpressId'], $key);
        } else {
            $result = $iyspanel->do_2fa($key, $smsKey);
        }

		$log = $this->get_log();

		// do stuff on failure
        if ( ! is_object( $result ) || $result->status->name == "AUTHENTICATION_FAILURE" ) {
            $error_code    = $iyspanel->get_error_code();
			$error_message = $iyspanel->get_error_message();

            $form->is_2fa = true;
            $form->two_fa_sms_key = $key;

            if ($renew2FA) {
                $form->add_notice( $form->messages['renew_2fa_failed'], 'error' );
            } else {
                $form->add_notice( $form->messages['2fa_failed'], 'error' );
            }


			$log->error( sprintf( 'Form %d > İYS Panel API error: %s %s', $form->ID, $error_code, $error_message ) );
			do_action( 'im4wp_form_api_error', $form, $error_message );

			return;
		} elseif ( ! is_object( $result ) || $result->status->code != "0" ) {
			$error_code    = $iyspanel->get_error_code();
			$error_message = $iyspanel->get_error_message();

            $form->is_2fa = true;
            $form->two_fa_sms_key = $key;

			$form->add_error( $error_code );
			$form->add_notice( $form->messages['error'], 'error' );
            if ($renew2FA) {
                $form->add_notice( $form->messages['renew_2fa_failed'], 'error' );
            }
			$log->error( sprintf( 'Form %d > İYS Panel API error: %s %s', $form->ID, $error_code, $error_message ) );
			do_action( 'im4wp_form_api_error', $form, $error_message );

			return;
		}

        if ($renew2FA) {
            $form->last_event = 'renew_2fa_success';
			$form->add_notice( $form->messages['renew_2fa_success'], 'success' );
            $form->is_2fa = true;
            $form->two_fa_sms_key = $key;
            $log->info( sprintf( '2FA Code renewed!') );
        } else {
			$form->last_event = '2fa_success';
			$form->add_notice( $form->messages['2fa_success'], 'success' );
            $form->is_2fa = false;
			$log->info( sprintf( 'Form %d > Successfully subscribed.', $form->ID ) );
		}

		/**
		 * Fires right after a form was used to add a new subscriber (or update an existing one).
		 *
		 * @since 3.0
		 *
		 * @param IM4WP_Form $form Instance of the submitted form
		 * @param string $email
		 * @param array $data
		 * @param IM4WP_MailChimp_Subscriber[] $subscriber
		 */
		do_action( 'im4wp_form_subscribed', $form, $subscriber->email_address, $data, $map );
	}

	/**
	 * @param IM4WP_Form $form
	 */
	public function process_unsubscribe_form( IM4WP_Form $form ) {
		$iyspanel = new IM4WP_MailChimp();
		$log       = $this->get_log();
		$result    = null;
		$data      = $form->get_data();

		// unsubscribe from each list
		foreach ( $form->get_lists() as $list_id ) {
			$result = $iyspanel->list_unsubscribe( $list_id, $data['EMAIL'] );
		}

		if ( ! $result ) {
			$form->add_notice( $form->messages['error'], 'error' );
			$log->error( sprintf( 'Form %d > İYS Panel API error: %s', $form->ID, $iyspanel->get_error_message() ) );

			// bail
			return;
		}

		// Success! Unsubscribed.
		$form->last_event = 'unsubscribed';
		$form->add_notice( $form->messages['unsubscribed'], 'notice' );
		$log->info( sprintf( 'Form %d > Successfully unsubscribed %s', $form->ID, $data['EMAIL'] ) );

		/**
		 * Fires right after a form was used to unsubscribe.
		 *
		 * @since 3.0
		 *
		 * @param IM4WP_Form $form Instance of the submitted form.
		 * @param string $email
		 */
		do_action( 'im4wp_form_unsubscribed', $form, $data['EMAIL'] );
	}

	/**
	 * @param IM4WP_Form $form
	 */
	public function respond( IM4WP_Form $form ) {
		$success = ! $form->has_errors();

		if ( $success ) {

			/**
			 * Fires right after a form is submitted without any errors (success).
			 *
			 * @since 3.0
			 *
			 * @param IM4WP_Form $form Instance of the submitted form
			 */
			do_action( 'im4wp_form_success', $form );
		} else {

			/**
			 * Fires right after a form is submitted with errors.
			 *
			 * @since 3.0
			 *
			 * @param IM4WP_Form $form The submitted form instance.
			 */
			do_action( 'im4wp_form_error', $form );

			// fire a dedicated event for each error
			foreach ( $form->errors as $error ) {

				/**
				 * Fires right after a form was submitted with errors.
				 *
				 * The dynamic portion of the hook, `$error`, refers to the error that occurred.
				 *
				 * Default errors give us the following possible hooks:
				 *
				 * - im4wp_form_error_error                     General errors
				 * - im4wp_form_error_spam
				 * - im4wp_form_error_invalid_email             Invalid email address
				 * - im4wp_form_error_already_subscribed        Email is already on selected list(s)
				 * - im4wp_form_error_required_field_missing    One or more required fields are missing
				 * - im4wp_form_error_no_lists_selected         No İYS Panel lists were selected
				 *
				 * @since 3.0
				 *
				 * @param   IM4WP_Form     $form        The form instance of the submitted form.
				 */
				do_action( 'im4wp_form_error_' . $error, $form );
			}
		}

		/**
		 * Fires right before responding to the form request.
		 *
		 * @since 3.0
		 *
		 * @param IM4WP_Form $form Instance of the submitted form.
		 */
		do_action( 'im4wp_form_respond', $form );

		// do stuff on success (if form was submitted over plain HTTP, not for AJAX or REST requests)
		if ( $success && ! $this->request_wants_json() ) {
			$redirect_url = $form->get_redirect_url();
			if ( ! empty( $redirect_url ) && ! $form->is_2fa ) {
				wp_redirect( $redirect_url );
				exit;
			}
		}
	}

	private function request_wants_json() {
		if ( isset( $_SERVER['HTTP_ACCEPT'] ) && false !== strpos( $_SERVER['HTTP_ACCEPT'], 'application/json' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @return IM4WP_API_V1
	 */
	protected function get_api() {
		return im4wp( 'api' );
	}

	/**
	 * @return IM4WP_Debug_Log
	 */
	protected function get_log() {
		return im4wp( 'log' );
	}
}
