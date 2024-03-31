<?php

namespace JesGs\Notion\Model;

/**
 * Data model class
 */
class Model {

	/**
	 * Database ID (local)
	 *
	 * @var string
	 */
	protected string $id;

	/**
	 * Notion Object type
	 *
	 * @var string
	 */
	protected string $object = '';

	/**
	 * Assign parameters on initialization
	 *
	 * @param array $params Array of parameters.
	 */
	public function __construct( array $params = array() ) {
		foreach ( $params as $param => $data ) {
			if ( ! property_exists( $this, $param ) ) {
				continue;
			}

			$this->$param = $data;
		}
	}
}
