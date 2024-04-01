<?php

namespace JesGs\Notion\Api\Page;

use JesGs\Notion\Api\Api;
use JesGs\Notion\Cache;

class Page {
	use Api;

	/**
	 * API endpoint for Pages
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
	 * Get Page blocks
	 *
	 * @param string $page_id ID of page that we're querying blocks for.
	 *
	 * @return array
	 */
	public static function query( string $page_id ) {
		$cached_results = Cache::get_results_cache( $page_id );
		if ( empty( $cached_results ) ) {
			$url           = vsprintf( self::$api_endpoint, array( $page_id ) );
			$response      = wp_remote_get( $url, self::build_query_request_headers() );
			$response_code = wp_remote_retrieve_response_code( $response );
			if ( 200 !== intval( $response_code ) ) {
				return json_decode( '{[]}' ); // return empty object.
			}

			$cached_results = wp_remote_retrieve_body( $response );
			Cache::set_results_cache( $page_id, $cached_results );
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
	public static function get_children( string $block_id ) {
		$data = self::query( $block_id );
		if ( empty( $data['results'] ) ) {
			return array();
		}

		$new_data_array = array();
		foreach ( $data['results'] as $result ) {
			if ( false === $result['has_children'] ) {
				var_dump($result);
			}
			self::get_children( $result['id'] );
		}

		return $data;
	}
}
