<?php
/**
 * Options handler file
 *
 * @package JesGs\Notion\Options
 */

namespace JesGs\Notion\Options;

use JesGs\Notion\Singleton;

/**
 * Options handler class
 */
class Options {
	use Singleton;

	/**
	 * Options Group Name constant
	 */
	const OPTIONS_GROUP_NAME = 'jesgs_notion_options';

	/**
	 * Default options
	 *
	 * @var string[]
	 */
	protected static $default_options = array(
		'notion_api_key'     => '',
		'notion_database_id' => '',
	);

	/**
	 * Run hooks on class init
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'admin_init', array( $this, 'admin_init' ) );
	}

	/**
	 * Admin init
	 *
	 * @return void
	 */
	public function admin_init() {

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}

		register_setting(
			self::OPTIONS_GROUP_NAME,
			self::OPTIONS_GROUP_NAME
		);

		add_settings_section(
			self::OPTIONS_GROUP_NAME . '-main',
			__( 'Notion Options', 'jesgs_notion' ),
			null,
			self::OPTIONS_GROUP_NAME . '-main',
		);

		$this->output_settings_fields();
	}

	/**
	 * Output settings fields
	 *
	 * @return void
	 */
	public function output_settings_fields() {
		$fields = $this->options_fields();
		foreach ( $fields as $field_name => $field ) {
			add_settings_field(
				$field['id'],
				( $field['title'] ?? ' ' ),
				$field['callback'],
				self::OPTIONS_GROUP_NAME . '-main',
				self::OPTIONS_GROUP_NAME . '-main',
				array_merge(
					array(
						'name'    => $field_name,
						'section' => 'main',
					),
					$field
				)
			);
		}
	}

	/**
	 * Array of options field data
	 *
	 * @return array
	 */
	public function options_fields(): array {
		return array(
			'notion_api_key'     => array(
				'id'       => 'notion-api-key',
				'type'     => 'text',
				'title'    => __( 'Notion API Key', 'jesgs_notion' ),
				'valid'    => '',
				'default'  => '',
				'callback' => array( $this, 'settings_field_cb' ),
			),
			'notion_database_id' => array(
				'id'       => 'notion-database-id',
				'type'     => 'text',
				'title'    => __( 'Notion Database ID', 'jesgs_notion' ),
				'valid'    => '/[0-9a-f]{8}[0-9a-f]{4}4[0-9a-f]{3}[89ab][0-9a-f]{3}[0-9a-f]{12}/',
				'default'  => '',
				'callback' => array( $this, 'settings_field_cb' ),
			),
		);
	}

	/**
	 * Output settings
	 *
	 * @param array $setting Setting to output.
	 *
	 * @return void
	 */
	public function settings_field_cb( array $setting ) {
		$class   = ucwords( $setting['type'] );
		$options = Settings::get_setting();
		$value   = $options['main'][ $setting['name'] ] ?? $setting['default'];

		if ( '' !== $class ) {
			$attributes = array(
				'name'  => self::OPTIONS_GROUP_NAME . '[main][' . $setting['name'] . ']',
				'id'    => $setting['id'],
				'value' => $value,
				'type'  => strtolower( $class ),
			);

			$element      = 'JesGs\Notion\Options\Form\Element\\' . $class;
			$form_element = new $element(
				array(
					'attributes'  => $attributes,
					'description' => $setting['description'] ?? '',
					'default'     => $value ?? $setting['default'],
					'validation'  => $setting['valid'] ?? '',
				)
			);

			echo $form_element; // @phpcs:ignore -- escaping is handled in the element class
		}
	}

	/**
	 * Sanitize options array
	 *
	 * @param ?array $options Array of options to sanitize.
	 *
	 * @return array
	 */
	public function sanitize_options( ?array $options ): array {
		return $options;
	}
}
