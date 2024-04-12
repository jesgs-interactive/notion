<?php
/**
 * OEmbed Data Model
 *
 * @package JesGs\Notion
 */

namespace JesGs\Notion\Model;

/**
 * OEmbed data model
 */
class Embed extends Model {

	/**
	 * Embed title
	 *
	 * @var string
	 */
	protected $title;

	/**
	 * Author name
	 *
	 * @var string
	 */
	protected $author_name;

	/**
	 * Author url
	 *
	 * @var string
	 */
	protected $author_url;

	/**
	 * Embed type
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * Embed height
	 *
	 * @var int
	 */
	protected $height;

	/**
	 * Embed width
	 *
	 * @var int
	 */
	protected $width;

	/**
	 * Embed version number
	 *
	 * @var string
	 */
	protected $version;

	/**
	 * Provider
	 *
	 * @var string
	 */
	protected $provider_name;

	/**
	 * Provider url
	 *
	 * @var string
	 */
	protected $provider_url;

	/**
	 * Thumbnail height
	 *
	 * @var int
	 */
	protected $thumbnail_height;

	/**
	 * Thumbnail width
	 *
	 * @var int
	 */
	protected $thumbnail_width;

	/**
	 * Thumbnail url
	 *
	 * @var string
	 */
	protected $thumbnail_url;

	/**
	 * Embed html
	 *
	 * @var string
	 */
	protected $html;

	/**
	 * Return embed title
	 *
	 * @return string
	 */
	public function get_title(): string {
		return $this->title;
	}

	/**
	 * Return author name
	 *
	 * @return string
	 */
	public function get_author_name(): string {
		return $this->author_name;
	}

	/**
	 * Return author url
	 *
	 * @return string
	 */
	public function get_author_url(): string {
		return $this->author_url;
	}

	/**
	 * Return data type
	 *
	 * @return string
	 */
	public function get_type(): string {
		return $this->type;
	}

	/**
	 * Return embed height
	 *
	 * @return int
	 */
	public function get_height(): int {
		return $this->height;
	}

	/**
	 * Return embed width
	 *
	 * @return int
	 */
	public function get_width(): int {
		return $this->width;
	}

	/**
	 * Return api version
	 *
	 * @return string
	 */
	public function get_version(): string {
		return $this->version;
	}

	/**
	 * Return provider name
	 *
	 * @return string
	 */
	public function get_provider_name(): string {
		return $this->provider_name;
	}

	/**
	 * Return provider url
	 *
	 * @return string
	 */
	public function get_provider_url(): string {
		return $this->provider_url;
	}

	/**
	 * Return thumbnail height
	 *
	 * @return int
	 */
	public function get_thumbnail_height(): int {
		return $this->thumbnail_height;
	}

	/**
	 * Return thumbnail width
	 *
	 * @return int
	 */
	public function get_thumbnail_width(): int {
		return $this->thumbnail_width;
	}

	/**
	 * Return thumbnail url
	 *
	 * @return string
	 */
	public function get_thumbnail_url(): string {
		return $this->thumbnail_url;
	}

	/**
	 * Return embed html
	 *
	 * @return string
	 */
	public function get_html(): string {
		return $this->html;
	}
}
