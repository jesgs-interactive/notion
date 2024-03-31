<?php
/**
 * Admin Pages
 *
 * @package JesGs\Notion
 */

namespace JesGs\Notion\Admin;

use JesGs\Notion\Database\DatabaseListTable;
use JesGs\Notion\Singleton;

/**
 * Admin Pages class
 */
class Admin {
	use Singleton;

	const ADMIN_PAGE_SLUG = 'notion-database-list';

	/**
	 * Page hook
	 *
	 * @var ?string
	 */
	protected static ?string $page_hook;

	/**
	 * Initialize stuff
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}

	/**
	 * Register admin page
	 *
	 * @return void
	 */
	public function admin_menu() {
		self::$page_hook = add_options_page(
			'List of Pages in Notion',
			'Notion Pages',
			'activate_plugins',
			self::ADMIN_PAGE_SLUG,
			array( $this, 'output_admin_page' )
		);
	}

	/**
	 * Outputs the admin page
	 *
	 * @return void
	 */
	public function output_admin_page() {
		echo '<h1>' . esc_html__( 'List of Pages in Notion', 'jesgs_notion' ) . '</h1>';

		/**
		 * We'll want to store the database list somehow so we're not repeatedly hitting Notions's API.
		 * For now, we'll use our json files for test data
		 */
		$database_list = new DatabaseListTable();
		$database_list->prepare_items();
		$database_list->display();
	}
}
