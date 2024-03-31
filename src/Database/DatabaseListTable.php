<?php
/**
 * Database list table
 *
 * @package JesGs\Notion
 */

namespace JesGs\Notion\Database;

use Illuminate\Support\Arr;
use JesGs\Notion\Database\Model\Page\Page;

/**
 * List Table that extends WP's List Table class
 */
class DatabaseListTable extends \WP_List_Table {

	/**
	 * Table data array
	 *
	 * @var array
	 */
	private array $table_data = array();

	/**
	 * Constructor method
	 *
	 * @param array $args Array of arguments for building table.
	 */
	public function __construct( $args = array() ) {
		parent::__construct( $args );

		// ping Notion API or check transient for data.
		$query = Database::query( '30201d4defff43209f923801ea1a1f8f' );

		$this->table_data = $this->get_normalized_rows( $query->all() );
	}

	/**
	 * Prepare items for display
	 *
	 * @return void
	 */
	public function prepare_items(): void {
		$columns = $this->get_columns();
		$hidden  = array();
		$primary = 'title';

		$this->_column_headers = array( $columns, $hidden, $primary );
		$this->items           = $this->table_data;
	}

	/**
	 * Get table columns
	 *
	 * @return string[]
	 */
	public function get_columns(): array {
		return array(
			'cb'      => '<input type="checkbox" />',
			'name'    => 'Page Name (Title)',
			'summary' => 'Summary',
			'tags'    => 'Tags',
			'url'     => 'URL',
		);
	}

	/**
	 * Set default columns
	 *
	 * @param array  $item Item array.
	 * @param string $column_name Column name.
	 *
	 * @return mixed|void
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'title':
			case 'summary':
			case 'tags':
			case 'url':
			default:
				return $item[ $column_name ];
		}
	}

	/**
	 * Return checkbox with assigned value.
	 *
	 * @param string $item Page UUID on Notion.
	 *
	 * @return string|void
	 */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="element[]" value="%s" />',
			$item['id']
		);
	}

	/**
	 * Normalize data rows for table use
	 *
	 * @param mixed $database_rows Array of rows to be normalized.
	 *
	 * @return array
	 */
	public function get_normalized_rows( mixed $database_rows ): array {
		$normalized_rows = array();

		foreach ( $database_rows as $database_row ) {
			/**
			 * Database row object
			 *
			 * @var Page $database_row
			 */

			if ( empty( $database_row ) ) {
				continue;
			}

			$row['id']  = $database_row->get_id();
			$properties = $database_row->get_properties();

			foreach ( $properties as $field => $content ) {
				$type = $content['type'];

				$field_name         = strtolower( $field );
				$row[ $field_name ] = '';
				if ( ! is_array( $content[ $type ] ) ) {
					$row[ $field_name ] = $content[ $type ];
					continue;
				}

				if ( 'multi_select' === $type ) {
					$tags               = Arr::pluck( $content[ $type ], 'name' );
					$row[ $field_name ] = implode( ', ', $tags );
					continue;
				}

				// A fail-safe check if $content[ $type ] is an array but empty.
				if ( empty( $content[ $type ][0] ) ) {
					continue;
				}

				$field_content      = $content[ $type ][0];
				$field_type         = $field_content['type'];
				$row[ $field_name ] = $field_content[ $field_type ]['content'];
			}

			$title = $row['name'];

			$row['name'] = vsprintf(
				'<a href="%2$s" rel="noopener nofollow" target="_blank">%1$s</a>',
				array(
					$title,
					$database_row->get_url(),
				)
			);

			$normalized_rows[] = $row;
		}

		return $normalized_rows;
	}
}
