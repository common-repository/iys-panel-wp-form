<?php

/**
* Class IM4WP_Field_Map
*
* @access private
* @since 4.0
* @ignore
*/
class IM4WP_List_Data_Mapper {


	/**
	* @var array
	*/
	private $data = array();

	/**
	* @var array
	*/
	private $list_ids = array();

	/**
	* @var IM4WP_Field_Formatter
	*/
	private $formatter;

	/**
	 * @var IM4WP_MailChimp
	 */
	private $iyspanel;

	/**
	* @param array $data
	* @param array $list_ids
	*/
	public function __construct( array $data, array $list_ids ) {
		$this->data = array_change_key_case( $data, CASE_UPPER );
		/* if ( ! isset( $this->data['EMAIL'] ) ) {
		   throw new InvalidArgumentException( 'Data needs at least an EMAIL key.' );
		   } */

		$this->list_ids  = $list_ids;
		$this->formatter = new IM4WP_Field_Formatter();
		$this->iyspanel = new IM4WP_MailChimp();
	}

	/**
	* @return IM4WP_MailChimp_Subscriber[]
	*/
	public function map() {
		$map = array();

		foreach ( $this->list_ids as $list_id ) {
			$map[ "$list_id" ] = $this->map_list( $list_id );
		}

		return $map;
	}

	/**
	* @param string $list_id
	* @return IM4WP_MailChimp_Subscriber
	* @throws Exception
	*/
	protected function map_list( $list_id ) {
		$subscriber                = new IM4WP_MailChimp_Subscriber();
		$subscriber->email_address = $this->data['EMAIL'];

		if ( ! empty( $this->data['MC_LANGUAGE'] ) ) {
			$subscriber->language = $this->formatter->language( $this->data['MC_LANGUAGE'] );
		}

		return $subscriber;
	}


	/**
	 * @param object $merge_field
	 * @param string $value
	 *
	 * @return mixed
	*/
	private function format_merge_field_value( $merge_field, $value ) {
		$field_type = strtolower( $merge_field->type );

		if ( method_exists( $this->formatter, $field_type ) ) {
			$value = call_user_func( array( $this->formatter, $field_type ), $value, $merge_field->options );
		}

		/**
		* Filters the value of a field after it is formatted.
		*
		* Use this to format a field value according to the field type (in İYS Panel).
		*
		* @since 3.0
		* @param string $value The value
		* @param string $field_type The type of the field (in İYS Panel)
		*/
		$value = apply_filters( 'im4wp_format_field_value', $value, $field_type );

		return $value;
	}
}
