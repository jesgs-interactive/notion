<?php

namespace JesGs\Notion\Database\Model\Page;

use JesGs\Notion\Database\Model\Model;

class Page extends Model {

	/**
	 * Page ID in Database
	 *
	 * @var int
	 */
	protected int $id;

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
	protected string $type = '';

	/**
	 * Array of page properties
	 *
	 * @var array
	 */
	protected array $properties = array();
}
