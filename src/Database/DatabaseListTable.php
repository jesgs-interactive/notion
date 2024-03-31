<?php
/**
 * Database list table
 *
 * @package JesGs\Notion
 */

namespace JesGs\Notion\Database;

use Illuminate\Support\Arr;

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
		// Database::query( '30201d4defff43209f923801ea1a1f8f' );
		$database_rows = wp_json_file_decode(
			JESGS_NOTION_ABSPATH . 'tests/test--database-query-data.json',
			array(
				'associative' => true,
			)
		)['results'];

		$normalized_rows = array();
		foreach ( $database_rows as $database_row ) {
			if ( empty( $database_row ) ) {
				continue;
			}

			$row['id'] = $database_row['id'];
			foreach ( $database_row['properties'] as $field => $content ) {
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
					$database_row['url'],
				)
			);

			$normalized_rows[] = $row;
		}

		$this->table_data = $normalized_rows;
	}

	/**
	 * Prepare items for display
	 *
	 * @return void
	 */
	public function prepare_items() {
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

	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="element[]" value="%s" />',
			$item['id']
		);
	}
}
