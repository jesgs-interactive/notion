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
	public static function parse_blocks( array $block_data ): string {

		if ( empty( $block_data['results'] ) ) {
			return array();
		}

		$blocks = $block_data['results'];
		$html   = '';
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

			if ( 'column_list' === $type ) {
//				$children = \JesGs\Notion\Api\Block\Block::get_children( $block['id'] );
//				var_dump( $children );
				$html .= '<p>columns</p>';
			}
		}

		return $html;
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

		$heading_markup = '<!-- wp:heading {"level":%1$d} -->' . self::CRLF
							. '<h%1$s>%2$s</h%1$s>' . self::CRLF
							. '<!-- /wp:heading-->' . self::CRLF;

		// don't parse rich text, as headings have their own styles.
		list( , $size ) = explode( '_', $heading );

		return vsprintf( $heading_markup, array( $size, $text ) );
	}

	/**
	 * Parse paragraph blocks
	 *
	 * @param array $block_content Block content to parse.
	 *
	 * @return string
	 */
	public static function parse_paragraph( array $block_content ) {
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
			$request = new \WP_REST_Request( 'GET', '/oembed/1.0/proxy' );
			$request->set_query_params(
				array(
					'url' => $block_content[ $type ]['url'],
				)
			);

			$response = rest_do_request( $request );
			$data     = $response->get_data();
			$html     = $data->html;
		}

		return $html;
	}

	/**
	 * Get Page html
	 *
	 * @return string
	 */
	public function get_html() {
		return $this->html;
	}
}
