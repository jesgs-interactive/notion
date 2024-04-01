<?php
/**
 * Admin Pages
 *
 * @package JesGs\Notion
 */

namespace JesGs\Notion\Admin;

use JesGs\Notion\Api\Page\Page;
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

		$this->show_notion_page_data();
	}

	/**
	 * Display data from page on Notion
	 *
	 * @return void
	 */
	public function show_notion_page_data(): void {
		$notion_page_id = filter_input( INPUT_GET, 'notion_page_id', FILTER_SANITIZE_URL );
		if ( ! $notion_page_id ) {
			return;
		}

		echo '<h2>Freaky Page Things</h2>';
		echo '<p>Import this page? [Import]</p>';
		echo '<p>' . $notion_page_id . '</p>';

		// start new query for page here.
		$data = Page::query( $notion_page_id );

		$blocks = $data['results'];
		foreach ( $blocks as $block ) {
			$type = $block['type'];
			if ( isset( $block['has_children'] ) && true === $block['has_children'] ) {
				$block[ $type ] = Page::get_children( $block['id'] );
			}
		}
	}
}
