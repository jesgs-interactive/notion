<?php
/**
 * Plugin Name:     Notion to WordPress
 * Plugin URI:      PLUGIN SITE HERE
 * Description:     PLUGIN DESCRIPTION HERE
 * Author:          YOUR NAME HERE
 * Author URI:      YOUR SITE HERE
 * Text Domain:     notion
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Notion
 */

// Your code starts here.
if ( ! defined( 'JESGS_NOTION_ABSPATH' ) ) {
	define( 'JESGS_NOTION_ABSPATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'JESGS_NOTION_URLPATH' ) ) {
	define( 'JESGS_NOTION_URLPATH', plugin_dir_url( __FILE__ ) );
}


require_once 'vendor/autoload.php';

if ( class_exists( 'JesGs\Notion\Bootstrap', ! wp_doing_ajax() ) ) {
	\JesGs\Notion\Bootstrap::init();
}
