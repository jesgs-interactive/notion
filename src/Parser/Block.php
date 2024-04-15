<?php
/**
 * Parse collection of Notion Blocks
 *
 * @package JesGs\Notion
 */
// @phpcs:disable WordPress.NamingConventions.ValidHookName.UseUnderscores

namespace JesGs\Notion\Parser;

use JesGs\Notion\Model\Embed;
use JesGs\Notion\Singleton;

/**
 * Notion Block parsing class
 */
class Block {
	use Singleton;

	/**
	 * Markup version of Notion content.
	 *
	 * @var string
	 */
	protected string $html = '';

	/**
	 * Line-ending constant
	 */
	const CRLF = "\r\n";

	/**
	 * Initialize class
	 *
	 * @return void
	 */
	public function init() {
		add_filter( 'jesgs_notion/parse/bookmark', array( $this, 'parse_bookmark' ), 11, 3 );
		add_filter( 'jesgs_notion/parse/bulleted_list_item', array( $this, 'parse_list_item' ), 11, 3 );
		add_filter( 'jesgs_notion/parse/numbered_list_item', array( $this, 'parse_list_item' ), 11, 3 );
		// add_filter( 'jesgs_notion/parse/columns', array( $this, 'parse_columns' ), 11, 3  )
		add_filter( 'jesgs_notion/parse/caption', array( $this, 'parse_caption' ), 11, 3 );
		add_filter( 'jesgs_notion/parse/heading_1', array( $this, 'parse_heading' ), 11, 3 );
		add_filter( 'jesgs_notion/parse/heading_2', array( $this, 'parse_heading' ), 11, 3 );
		add_filter( 'jesgs_notion/parse/heading_3', array( $this, 'parse_heading' ), 11, 3 );
		add_filter( 'jesgs_notion/parse/image', array( $this, 'parse_image' ), 11, 3 );
		add_filter( 'jesgs_notion/parse/paragraph', array( $this, 'parse_paragraph' ), 11, 3 );
		// add_filter( 'jesgs_notion/parse/table', array( $this, 'parse_table' ), 11, 3  );
		add_filter( 'jesgs_notion/parse/video', array( $this, 'parse_video' ), 11, 3 );
	}

	/**
	 * Parse array of blocks
	 *
	 * @param array $block_data Array of blocks to parse.
	 *
	 * @return string
	 */
	public static function pre_parse_blocks( array $block_data ): string {

		if ( empty( $block_data['results'] ) ) {
			return '';
		}

		$blocks = $block_data['results'];

		return self::parse_blocks( $blocks );
	}

	/**
	 * Parse list of blocks
	 *
	 * @param array  $blocks Blocks to be parsed.
	 * @param string $html Html string to append to.
	 *
	 * @return string
	 */
	public static function parse_blocks( array $blocks, string $html = '' ): string {
		$html         = '';
		$group_blocks = array( 'bulleted_list_item', 'numbered_list_item' );
		$list_items   = array();
		$list_output  = '';
		foreach ( $blocks as $c => $block ) {
			$type = $block['type'];
			if ( ! in_array( $type, $group_blocks, true ) ) {
				$html .= apply_filters( "jesgs_notion/parse/{$type}", '', $block, $type );
			} else {
				$list = self::get_instance()->wrap_block( '<ul>%s</ul>', 'list' );
				if ( str_contains( $type, 'numbered' ) ) {
					$list = self::get_instance()->wrap_block( '<ol>%s</ol>', 'list', array( 'ordered' => true ) );
				}

				$block_prev = ! empty( $blocks[ $c - 1 ] ) ? $blocks[ $c - 1 ] : false;
				if ( $block_prev && $block_prev['type'] !== $type ) {
					$list_output = '';
					$list_items  = array();
				}

				$list_items[] = apply_filters( "jesgs_notion/parse/{$type}", $list, $block, $type );

				$block_next = ! empty( $blocks[ $c + 1 ] ) ? $blocks[ $c + 1 ] : false;
				if ( $block_next && $block_next['type'] !== $type ) {
					$list_output = sprintf( $list, implode( '', $list_items ) );
				}

				$html .= $list_output;
			}
		}

		return $html;
	}

	/**
	 * Select block to parse
	 *
	 * @param array $block Block to parse.
	 *
	 * @return string
	 */
	public function select_block_type_to_parse( array $block ): string {
		$type          = $block['type'];
		$block_content = $block[ $type ];

		switch ( $type ) {
			case str_contains( $type, 'heading_' ):
				return self::parse_heading( $type, $block_content );
			case 'paragraph':
				return self::parse_paragraph( $block_content );
			case 'video':
				return self::parse_video( $block_content );
			case 'image':
				return self::parse_image( $block_content );
			case 'file':
				return self::parse_file( $block_content );
			default:
		}

		return '';
	}

	/**
	 * Parse the rich text portion of the Notion block
	 *
	 * @param array $rich_text Rich text array from block.
	 * @param bool  $plain_text_only Only return plain text.
	 *
	 * @return string
	 */
	public function parse_rich_text( array $rich_text, bool $plain_text_only = false ): string {
		$html = '';
		foreach ( $rich_text as $rt ) {
			if ( ! $plain_text_only ) {
				$html .= self::parse_annotations( $rt['plain_text'], $rt['annotations'] );
			} else {
				$html .= $rt['plain_text'];
			}
		}

		return $html;
	}

	/**
	 * Parse array of annotations and return a CSS string or markup
	 *
	 * @param string $text Text to format.
	 * @param array  $annotations Array of Rich Text annotations.
	 *
	 * @return string
	 */
	public function parse_annotations( string $text, array $annotations ): string {
		// grab color value first.
		$color = $annotations['color'];
		unset( $annotations['color'] );

		if ( $annotations['bold'] ) {
			$text = sprintf( '<strong>%s</strong>', $text );
		}

		if ( $annotations['italic'] ) {
			$text = sprintf( '<em>%s</em>', $text );
		}

		if ( $annotations['strikethrough'] ) {
			$text = sprintf( '<s>%s</s>', $text );
		}

		if ( $annotations['underline'] ) {
			$text = sprintf( '<u>%s</u>', $text );
		}

		if ( $annotations['code'] ) {
			$text = sprintf( '<code>%s</code>', $text );
		}

		return $text;
	}

	/**
	 * Parse heading block
	 *
	 * @param string $html String passed from filter init.
	 * @param array  $block Block data.
	 * @param string $type Block type.
	 * @return string
	 */
	public function parse_heading( string $html, array $block, string $type ): string {
		$block_content = $block[ $type ];

		if ( empty( $block_content['rich_text'] ) ) {
			return $html;
		}

		$text = $this->parse_rich_text( $block_content['rich_text'], true );

		// don't parse rich text, as headings have their own styles.
		list( , $size ) = explode( '_', $type );

		$json_string = ' ';
		if ( 1 === (int) $size ) {
			$json_string = ' {"level":1} ';
		}

		$html = '<!-- wp:heading%3$s-->' . self::CRLF
							. '<h%1$s class="wp-block-heading">%2$s</h%1$s>' . self::CRLF
							. '<!-- /wp:heading -->' . self::CRLF;

		return vsprintf( $html, array( $size, $text, $json_string ) );
	}

	/**
	 * Parse paragraph blocks
	 *
	 * @param string $html String passed from init.
	 * @param array  $block Block content to parse.
	 * @param string $type Block type.
	 *
	 * @return string
	 */
	public function parse_paragraph( string $html, array $block, string $type ): string {
		$block_content = $block[ $type ];

		if ( ! isset( $block_content['rich_text'] ) ) {
			return $html;
		}

		$block_code = '<!-- wp:paragraph -->' . self::CRLF
						. '<p>%s</p>' . self::CRLF
						. '<!-- /wp:paragraph -->';

		$text = $this->parse_rich_text( $block_content['rich_text'], false );

		return vsprintf( $block_code, array( $text ) ) . self::CRLF;
	}

	/**
	 * Parse block content for video
	 *
	 * @param string $html Empty string.
	 * @param array  $block Block content to parse.
	 * @param string $type Block type.
	 *
	 * @global \WP_Embed $wp_embed
	 * @return string
	 */
	public function parse_video( string $html, array $block, string $type ): string {
		if ( empty( $block[ $type ] ) ) {
			return $html;
		}

		$block_content = $block[ $type ];
		if ( 'external' === $block_content['type'] ) {
			$url  = $block_content['external']['url'];
			$data = self::get_oembed_data( $url );
			if ( is_wp_error( $data ) ) {
				return $html;
			}
			$caption = $this->parse_rich_text( $block_content['caption'] );
			if ( ! empty( $caption ) ) {
				$caption = sprintf( '<figcaption class="wp-element-caption">%s</figcaption>', $caption );
			}

			$video  = new Embed( (array) $data );
			$params = array(
				'url'              => $url,
				'type'             => $video->get_type(),
				'providerNameSlug' => sanitize_title( $video->get_provider_name() ),
				'responsive'       => true,
				'className'        => array(
					'wp-embed-aspect-16-9',
					'wp-has-aspect-ratio',
				),
			);

			$block_html = '<figure class="wp-block-embed is-type-%1$s is-provider-%2$s wp-block-embed-%2$s wp-embed-aspect-16-9 wp-has-aspect-ratio"><div class="wp-block-embed__wrapper">%3$s</div>%4$s</figure>';
			$block_html = vsprintf(
				$block_html,
				array(
					$params['type'],
					$params['providerNameSlug'],
					$url,
					$caption,
				)
			);

			$html = $this->wrap_block( $block_html, 'embed', $params );
		}

		return $html;
	}

	/**
	 * Parse image block
	 *
	 * @param string $html Empty string.
	 * @param array  $block Block content to parse.
	 * @param string $type Block type.
	 *
	 * @return string
	 */
	public function parse_image( string $html, array $block, string $type ): string {
		$block_content = $block[ $type ];

		$location = $block_content['type'];
		$img_src  = $block_content[ $location ]['url'];

		if ( 'file' === $location ) {
			// an import should happen here when the time comes
		}

		$img_tag = sprintf( '<img src="%s" alt="" />', $img_src );
		$html   .= '<figure class="wp-block-image size-large">' . $img_tag;

		if ( ! empty( $block_content['caption'] ) ) {
			$caption = $this->parse_rich_text( $block_content['caption'] );
			$html   .= sprintf( '<figcaption class="wp-element-caption">%s</figcaption>', $caption );
		}

		$html .= '</figure>';

		return $this->wrap_block( $html, 'image', array( 'sizeSlug' => 'large' ) );
	}

	/**
	 * Parse column data
	 *
	 * @param string $id ID of child-block to load.
	 *
	 * @return string
	 */
	public function parse_columns( string $id ): string {
		$children = \JesGs\Notion\Api\Block\Block::get_children( $id );
		if ( empty( $children ) ) {
			return '';
		}

		$html = '<!-- wp:columns -->'
				. '<div class="wp-block-columns">';

		foreach ( $children as $child ) {
			$type = $child['type'];
			if ( isset( $child[ $type ]['rich_text'] ) && empty( $child[ $type ]['rich_text'] ) ) {
				continue;
			}

			$html .= '<!-- wp:column -->'
					. '<div class="wp-block-column">';
			$html .= $this->select_block_type_to_parse( $child );
			$html .= '</div><!-- /wp:column -->';
		}

		$html .= '</div>'; // closes .wp-block-columns.
		$html .= '<!-- /wp:columns -->';

		return $html;
	}

	/**
	 * Generate bookmark markup
	 *
	 * @param string $html HTML string.
	 * @param array  $block Block content array to parse.
	 * @param string $type Block type.
	 *
	 * @return string
	 */
	public function parse_bookmark( string $html, array $block, string $type ): string {
		if ( empty( $block[ $type ] ) ) {
			return $html;
		}

		$block_content = $block[ $type ];

		$caption = '';
		if ( ! empty( $block_content['caption'] ) ) {
			$caption = '<span class="wp-caption">' . $this->parse_rich_text( $block_content['caption'] ) . '</span>';
		}

		$data          = $this->get_oembed_data( $block_content['url'] );
		$title         = $data->title ?? '';
		$thumbnail_url = $data->thumbnail_url ?? '';

		$html  = '<!-- wp:paragraph -->' . self::CRLF;
		$html .= '<p><a href="%1$s" rel="noopener" target="_blank">' . self::CRLF
				. '<strong>%2$s</strong>' . self::CRLF
				. '<strong>URL</strong>: %1$s' . self::CRLF
				. '</a>%3$s' . self::CRLF
				. '</p>' . self::CRLF;

		$html .= '<!-- /wp:paragraph -->' . self::CRLF;

		return vsprintf(
			$html,
			array(
				$block_content['url'],
				$title,
				$caption,
			)
		);
	}

	/**
	 * Grab oEmbed data from REST Api
	 *
	 * @param string $url URL to get oEmbed data for.
	 *
	 * @return array|\WP_Error
	 */
	public function get_oembed_data( string $url ): \stdClass|\WP_Error {
		$request = new \WP_REST_Request( 'GET', '/oembed/1.0/proxy' );
		$request->set_query_params(
			array(
				'url' => $url,
			)
		);

		$response = rest_do_request( $request );
		if ( 200 !== $response->get_status() ) {
			return new \WP_Error(
				$response->get_status(),
				__( 'No data returned', 'jesgs_notion' )
			);
		}

		return $response->get_data();
	}

	/**
	 * Create Gutenberg-compatible block
	 *
	 * @param string $html Markup for block.
	 * @param string $block_name Block name.
	 * @param array  $attrs Block attributes.
	 *
	 * @return string
	 */
	public function wrap_block( string $html, string $block_name, array $attrs = array() ): string {
		$opening_block_tag            = "\r\n" . '<!-- wp:%s';
		$closing_of_opening_block_tag = '%s-->' . "\r\n";
		$closing_block_tag            = "\r\n" . '<!-- /wp:%s -->';

		$attrs_json = ' ';
		if ( ! empty( $attrs ) ) {
			$attrs_json = ' ' . wp_json_encode( $attrs ) . ' ';
		}

		$content  = sprintf( $opening_block_tag, $block_name );
		$content .= sprintf( $closing_of_opening_block_tag, $attrs_json );
		$content .= $html;
		$content .= sprintf( $closing_block_tag, $block_name );

		return $content;
	}

	/**
	 * Parse file block
	 *
	 * @param array $block_content Content block to parse.
	 *
	 * @return string
	 */
	public function parse_file( array $block_content ): string {
		$type = $block_content['type'];
		if ( 'external' !== $type ) {
			return '';
		}

		$url     = $block_content[ $type ]['url'];
		$caption = $this->parse_rich_text( $block_content['caption'] );
		$name    = $block_content['name'];

		$caption_formatted = $caption ? ' <span>' . $caption . '</span>' : '';

		$html  = '<!-- wp:paragraph -->';
		$html .= '<p>';
		$html .= '<a href="%1$s" rel="noopener" target="_blank" title="%2$s">%2$s</a>%3$s';
		$html .= '</p>';
		$html .= '<!-- /wp:paragraph -->';

		return vsprintf(
			$html,
			array(
				$url,
				$name,
				$caption_formatted,
			)
		);
	}

	/**
	 * Parse list items.
	 *
	 * @param string $list_html HTML string.
	 * @param array  $block Block content to parse.
	 * @param string $type Block data type.
	 *
	 * @return string
	 */
	public function parse_list_item( string $list_html, array $block, string $type ): string {
		$list_item         = $this->wrap_block( '<li>%1$s%2$s</li>', 'list-item' ) . "\r\n";
		$list_item_content = $this->parse_rich_text( $block[ $type ]['rich_text'] );

		$list_items = array();
		$sub_list   = '';

		if ( $block['has_children'] ) {
			$wrap_html = $this->wrap_block( '<ul>%s</ul>', 'list' );
			if ( str_contains( $type, 'numbered' ) ) {
				$wrap_html = $this->wrap_block( '<ol>%s</ol>', 'list', array( 'ordered' => true ) );
			}

			$children = \JesGs\Notion\Api\Block\Block::get_children( $block['id'] );
			foreach ( $children as $child ) {
				$child_type   = $child['type'];
				$list_items[] = sprintf( $list_item, $this->parse_rich_text( $child[ $child_type ]['rich_text'] ), '' );
			}

			$list_items_string = implode( '', $list_items );
			$sub_list          = sprintf( $wrap_html, $list_items_string );
		}

		return vsprintf( $list_item, array( $list_item_content, $sub_list ) );
	}
}
