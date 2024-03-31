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
	 * Do things when the plugin is loaded
	 *
	 * @return void
	 */
	public static function plugin_loaded() {
		$dotenv = new Dotenv();
		$dotenv->loadEnv( JESGS_NOTION_ABSPATH . '.env' );
		self::$env = $_ENV;

		Admin::get_instance()->init();
	}

	/**
	 * Get the secret key for Notion
	 *
	 * @return string
	 */
	public static function get_secret(): string {
		if ( empty( self::$env['SECRET'] ) ) {
			return '';
		}

		return self::$env['SECRET'];
	}
}
