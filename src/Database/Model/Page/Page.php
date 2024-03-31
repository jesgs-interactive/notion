<?php
/**
 * Page Model
 *
 * @package JesGs\Notion
 */

namespace JesGs\Notion\Database\Model\Page;

use JesGs\Notion\Database\Model\Model;

/**
 * Page data model class
 */
class Page extends Model {

	/**
	 * Page ID in Database
	 *
	 * @var string
	 */
	protected string $id;

	/**
	 * Page ID on Notion
	 *
	 * @var string
	 */
	protected string $page_id = '';

	/**
	 * Notion Object type
	 *
	 * @var string
	 */
	protected string $object = 'page';

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
	 * Cover image object
	 *
	 * @var ?array
	 */
	protected ?array $cover = array();

	/**
	 * Page icon image object
	 *
	 * @var ?array
	 */
	protected ?array $icon = array();

	/**
	 * Parent object
	 *
	 * @var array|null
	 */
	protected ?array $parent = array();

	/**
	 * Data type
	 *
	 * @var string
	 */
	protected string $url = '';

	/**
	 * Array of page properties
	 *
	 * @var array
	 */
	protected array $properties = array();

	/**
	 * Return page id
	 *
	 * @return string
	 */
	public function get_id(): string {
		return $this->id;
	}

	/**
	 * Return page id (may not be needed)
	 *
	 * @return string
	 */
	public function get_page_id(): string {
		return $this->page_id;
	}

	/**
	 * Return object type
	 *
	 * @return string
	 */
	public function get_object(): string {
		return $this->object;
	}

	/**
	 * Get time created
	 *
	 * @return string
	 */
	public function get_created_time(): string {
		return $this->created_time;
	}

	/**
	 * Get Created by object
	 *
	 * @return array
	 */
	public function get_created_by(): array {
		return $this->created_by;
	}

	/**
	 * Get last edited time
	 *
	 * @return string
	 */
	public function get_last_edited_time(): string {
		return $this->last_edited_time;
	}

	/**
	 * Get last edited by
	 *
	 * @return array
	 */
	public function get_last_edited_by(): array {
		return $this->last_edited_by;
	}

	/**
	 * Get cover image object
	 *
	 * @return array|null
	 */
	public function get_cover(): ?array {
		return $this->cover;
	}

	/**
	 * Get icon image object
	 *
	 * @return array|null
	 */
	public function get_icon(): ?array {
		return $this->icon;
	}

	/**
	 * Get parent object
	 *
	 * @return array|null
	 */
	public function get_parent(): ?array {
		return $this->parent;
	}

	/**
	 * Get page url on Notion
	 *
	 * @return string
	 */
	public function get_url(): string {
		return $this->url;
	}

	/**
	 * Get page properties
	 *
	 * @return array
	 */
	public function get_properties(): array {
		return $this->properties;
	}
}
