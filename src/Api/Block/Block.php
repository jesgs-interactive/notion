<?php

namespace JesGs\Notion\Api\Block;

use JesGs\Notion\Api\Api;
use JesGs\Notion\Cache;
use Illuminate\Support\Arr;

class Block {
	use Api;

	/**
	 * API endpoint for Blocks
	 *
	 * @var string
	 */
	protected static string $api_endpoint = 'https://api.notion.com/v1/blocks/%s/children';

	/**
	 * API method
	 *
	 * @var string
	 */
	protected static $method = 'GET';

	/**
	 * Get Block blocks
	 *
	 * @param string $page_id ID of page that we're querying blocks for.
	 *
	 * @return array
	 */
	public static function query( string $page_id ) {
		$cached_results = Cache::get_results_cache( '-blocks-' . $page_id );
		if ( empty( $cached_results ) ) {
			$url           = vsprintf( self::$api_endpoint, array( $page_id ) );
			$response      = wp_remote_get( $url, self::build_query_request_headers() );
			$response_code = wp_remote_retrieve_response_code( $response );
			if ( 200 !== intval( $response_code ) ) {
				return json_decode( '{[]}' ); // return empty object.
			}

			$cached_results = wp_remote_retrieve_body( $response );
			Cache::set_results_cache( '-blocks-' . $page_id, $cached_results );
		}

		return json_decode( $cached_results, true );
	}

	/**
	 * Get children of block
	 *
	 * @param string $block_id ID of Block to query for.
	 *
	 * @return array
	 */
	public static function get_children( string $block_id ): array {
		$data = self::query( $block_id );
		if ( empty( $data['results'] ) ) {
			return array();
		}

		$found_children = array();
		$children       = array();
		foreach ( $data['results'] as $result ) {
			if ( ! $result['has_children'] ) {
				$children[] = $result;
			} else {
				$children = self::get_children_recursive( $result['id'], $found_children );
			}
		}

		return $children;
	}


	/**
	 * Iterate over array of results until child is found.
	 *
	 * @param string $id            Block to iterate over.
	 * @param array  $found_children Children found by iterating.
	 *
	 * @return array
	 */
	public static function get_children_recursive( string $id, array &$found_children = array() ): array {
		$data = self::query( $id );

		foreach ( $data['results'] as &$result ) {
			if ( false === $result['has_children'] ) {
				$found_children[] = $result;
			}

			$result = self::get_children_recursive( $result['id'] );
		}

		return $found_children;
	}
}
