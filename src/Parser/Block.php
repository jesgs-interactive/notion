<?php
/**
 * Parse collection of Notion Blocks
 *
 * @package JesGs\Notion
 */

namespace JesGs\Notion\Parser;

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
		// TODO: Implement init() method.
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
		foreach ( $blocks as $block ) {
			$type          = $block['type'];
			$block_content = $block[ $type ];

			if ( str_contains( $type, 'heading_' ) ) {
				$html .= self::parse_heading( $type, $block_content ) . self::CRLF;
			}

			if ( 'paragraph' === $type ) {
				$html .= self::parse_paragraph( $block_content );
			}

			if ( 'video' === $type ) {
				$html .= self::parse_video( $block_content );
			}

			if ( 'image' === $type ) {
				$html .= self::parse_image( $block_content );
			}

			if ( 'file' === $type ) {
				$html .= self::parse_file( $block_content );
			}

			if ( 'column_list' === $type ) {
				$html .= self::parse_columns( $block['id'] );
			}

			if ( 'bookmark' === $type ) {
				$html .= self::parse_bookmark( $block_content );
			}

			if ( 'table' === $type ) {
				$html .= self::parse_table( $block['id'] );
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
	public static function select_block_type_to_parse( array $block ): string {
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
	public static function parse_rich_text( array $rich_text, bool $plain_text_only = false ): string {
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
	public static function parse_annotations( string $text, array $annotations ): string {
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
	 * @param string $heading Heading type.
	 * @param array  $heading_content Heading block data.
	 *
	 * @return string
	 */
	public static function parse_heading( string $heading, array $heading_content ): string {
		if ( empty( $heading_content['rich_text'] ) ) {
			return '';
		}

		$text = self::parse_rich_text( $heading_content['rich_text'], true );

		// don't parse rich text, as headings have their own styles.
		list( , $size ) = explode( '_', $heading );

		$json_string = ' ';
		if ( 1 === (int) $size ) {
			$json_string = ' {"level":1} ';
		}

		$heading_markup = '<!-- wp:heading%3$s-->' . self::CRLF
							. '<h%1$s class="wp-block-heading">%2$s</h%1$s>' . self::CRLF
							. '<!-- /wp:heading -->' . self::CRLF;

		return vsprintf( $heading_markup, array( $size, $text, $json_string ) );
	}

	/**
	 * Parse paragraph blocks
	 *
	 * @param array $block_content Block content to parse.
	 *
	 * @return string
	 */
	public static function parse_paragraph( array $block_content ): string {
		if ( ! isset( $block_content['rich_text'] ) ) {
			return '';
		}

		$block_code = '<!-- wp:paragraph -->' . self::CRLF
						. '<p>%s</p>' . self::CRLF
						. '<!-- /wp:paragraph -->';

		$text = self::parse_rich_text( $block_content['rich_text'], false );

		return vsprintf( $block_code, array( $text ) ) . self::CRLF;
	}

	/**
	 * Parse block content for video
	 *
	 * @param array $block_content Block content to parse.
	 *
	 * @global \WP_Embed $wp_embed
	 * @return string
	 */
	public static function parse_video( array $block_content ): string {
		$type = $block_content['type'];
		$html = '';
		if ( 'external' === $type ) {
			$url  = $block_content[ $type ]['url'];
			$data = self::get_oembed_data( $url );
			$html = $data->html;
		}

		return $html;
	}

	/**
	 * Parse image block
	 *
	 * @param array $block_content Block content to parse.
	 *
	 * @return string
	 */
	public static function parse_image( array $block_content ): string {
		$type = $block_content['type'];
		$html = '';

		if ( 'file' === $type ) {
			// here, we'll want some preparations for importing image from Notion.
			$img_src = $block_content[ $type ]['url'];
			// importing will need to happen here.
		}

		$img_src = $block_content[ $type ]['url'];
		$img_tag = sprintf( '<img src="%s" alt="" />', $img_src );

		$html .= '<figure class="wp-block-image size-large">' . $img_tag;

		if ( ! empty( $block_content['caption'] ) ) {
			$caption = self::parse_rich_text( $block_content['caption'] );
			$html   .= sprintf( '<figcaption class="wp-element-caption">%s</figcaption>', $caption );
		}

		$html .= '</figure>';
		return self::wrap_block( $html, 'image', array( 'sizeSlug' => 'large' ) );
	}

	/**
	 * Parse column data
	 *
	 * @param string $id ID of child-block to load.
	 *
	 * @return string
	 */
	public static function parse_columns( string $id ): string {
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
			$html .= self::select_block_type_to_parse( $child );
			$html .= '</div>'
					. '<!-- /wp:column -->';
		}

		$html .= '</div>'; // closes .wp-block-columns.
		$html .= '<!-- /wp:columns -->';

		return $html;
	}

	/**
	 * Parse table data
	 *
	 * @param string $id ID of child-block to load.
	 *
	 * @return string
	 */
	public static function parse_table( string $id ): string { // @phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
		return '';
	}

	/**
	 * Generate bookmark markup
	 *
	 * @param array $block_content Block content array to parse.
	 *
	 * @return string
	 */
	public static function parse_bookmark( array $block_content ): string {
		$caption = '';
		if ( ! empty( $block_content['caption'] ) ) {
			$caption = '<span class="wp-caption">' . self::parse_rich_text( $block_content['caption'] ) . '</span>';
		}

		$data          = self::get_oembed_data( $block_content['url'] );
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
	 * @return mixed
	 */
	public static function get_oembed_data( string $url ): \stdClass {
		$request = new \WP_REST_Request( 'GET', '/oembed/1.0/proxy' );
		$request->set_query_params(
			array(
				'url' => $url,
			)
		);

		$response = rest_do_request( $request );
		$data     = $response->get_data();
		return $data;
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
	public static function wrap_block( $html, $block_name, $attrs = array() ): string {
		$opening_block_tag            = '<!-- wp:%s';
		$closing_of_opening_block_tag = ' %s -->';
		$closing_block_tag            = '<!-- /wp:%s -->';

		$attrs_json = wp_json_encode( $attrs );
		$content    = sprintf( $opening_block_tag, $block_name );
		$content   .= sprintf( $closing_of_opening_block_tag, $attrs_json );
		$content   .= $html;
		$content   .= sprintf( $closing_block_tag, $block_name );

		return $content;
	}

	/**
	 * Parse file block
	 *
	 * @param array $block_content Content block to parse.
	 *
	 * @return string
	 */
	public static function parse_file( array $block_content ): string {
		$type = $block_content['type'];
		if ( 'external' !== $type ) {
			return '';
		}

		$url     = $block_content[ $type ]['url'];
		$caption = self::parse_rich_text( $block_content['caption'] );
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
}
