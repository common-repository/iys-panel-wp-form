<?php

/**
 * Class IM4WP_Queue_Job
 *
 * @ignore
 */
class IM4WP_Queue_Job {


	/**
	 * @var string
	 */
	public $id;

	/**
	 * @var mixed
	 */
	public $data;

	/**
	 * @var int
	 */
	public $max_attempts = 1;

	/**
	 * @var int
	 */
	public $attempts = 0;

	/**
	 * IM4WP_Queue_Job constructor.
	 *
	 * @param mixed $data
	 */
	public function __construct( $data ) {
		$this->id   = (string) microtime( true ) . rand( 1, 10000 );
		$this->data = $data;
	}
}
