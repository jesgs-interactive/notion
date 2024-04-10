<?php
/**
 * Run the plugin
 *
 * @package JesGs\Notion
 */

namespace JesGs\Notion;

use JesGs\Notion\Admin\Admin;
use JesGs\Notion\Options\Options;
use JesGs\Notion\Options\Settings;
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
		Settings::get_instance()->init();
		Admin::get_instance()->init();
		Options::get_instance()->init();
	}
}
