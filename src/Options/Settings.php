<?php
/**
 * Settings class
 *
 * @package JesGs\Notion
 */

namespace JesGs\Notion\Options;

use JesGs\Notion\Singleton;

/**
 * Settings class
 */
class Settings {
	use Singleton;

	/**
	 * Array of current settings
	 *
	 * @var array
	 */
	protected static array $settings = array();

	/**
	 * Do things on init
	 *
	 * @return void
	 */
	public function init() {
		self::$settings = get_option( Options::OPTIONS_GROUP_NAME );
	}

	/**
	 * Get setting value
	 *
	 * @param string $name Name of option to retrieve.
	 *
	 * @return string|array
	 */
	public static function get_setting( string $name = '' ): string|array {
		if ( empty( $name ) ) {
			return self::$settings;
		}

		return self::$settings['main'][ $name ] ?? '';
	}

}
