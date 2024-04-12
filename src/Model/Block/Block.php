<?php
/**
 * Block model object
 *
 * @package JesGs\Notion
 */

namespace JesGs\Notion\Model\Block;

use JesGs\Notion\Model\Model;

/**
 * Block model object
 */
class Block extends Model {

	/**
	 * Object type
	 *
	 * @var string
	 */
	protected string $object = 'block';

	/**
	 * Block ID (for API)
	 *
	 * @var string
	 */
	protected string $id;

	/**
	 * Parent ID data
	 *
	 * @var array
	 */
	protected array $parent = array();

	/**
	 * Time page was created
	 *
	 * @var string
	 */
	protected string $created_time = '';

	/**
	 * User object
	 *
	 * @var array
	 */
	protected array $created_by = array();

	/**
	 * Time page was last edited
	 *
	 * @var string
	 */
	protected string $last_edited_time = '';

	/**
	 * Last edited by
	 *
	 * @var array
	 */
	protected array $last_edited_by = array();

	/**
	 * Block has children
	 *
	 * @var bool
	 */
	protected bool $has_children = false;

	/**
	 * Block has been archived
	 *
	 * @var bool
	 */
	protected bool $archived = false;

	/**
	 * Has block been trashed?
	 *
	 * @var bool
	 */
	protected bool $in_trash = false;

	/**
	 * Block data type
	 *
	 * @var string
	 */
	protected string $type = '';

	/**
	 * Block data
	 *
	 * @var array
	 */
	protected array $data = array();
}
