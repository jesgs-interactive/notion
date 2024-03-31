<?php
/**
 * List Collection Object
 *
 * @package JesGs\Notion
 */

namespace JesGs\Notion\Database;

use Illuminate\Support\Collection;

/**
 * ObjectList
 */
class ObjectList extends Collection {

	/**
	 * Object type. Defaults to list
	 *
	 * @var string
	 */
	protected string $object = 'list';

	/**
	 * Results array
	 *
	 * @var array
	 */
	protected $items = array();

	public function __construct( $items = [] ) {
		$list = array();
		foreach ( $items as $item ) {
			if ( empty( $item['object'] ) ) {
				continue;
			}

			$object       = ucwords( $item['object'] );
			$object_class = 'JesGs\Notion\Database\Model\\' . $object . '\\' . $object;

			if ( class_exists( $object_class ) ) {
				$list[] = new $object_class( $item );
			}
		}

		parent::__construct( $list );
	}
}
