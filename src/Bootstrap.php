<?php
/**
 * Run the plugin
 *
 * @package JesGs\Notion
 */

namespace JesGs\Notion;

use JesGs\Notion\Admin\Admin;
use Symfony\Component\Dotenv\Dotenv;

/**
 * Plugin startup class
 */
class Bootstrap {

	/**
	 * Environment variables loaded from .env
	 *
	 * @var array
	 */
	protected static array $env;

	/**
	 * Run on plugin init
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'plugins_loaded', array( __CLASS__, 'plugin_loaded' ) );
	}

	/**
	 * Do things when the plugin is loaded
	 *
	 * @return void
	 */
	public static function plugin_loaded(): void {
		$dotenv = new Dotenv();
		$dotenv->loadEnv( JESGS_NOTION_ABSPATH . '.env' );
		self::$env = $_ENV;

		Admin::get_instance()->init();
	}

	/**
	 * Get environment variable
	 *
	 * @param string $env_var Environment variable to grab.
	 *
	 * @return false|mixed
	 */
	public static function get_env( string $env_var ): mixed {
		// @phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		return $_ENV[ $env_var ] ?? false;
	}
}
