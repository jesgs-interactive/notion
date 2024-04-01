<?php

namespace JesGs\Notion\Api\Page;

use JesGs\Notion\Api\Api;
use JesGs\Notion\Cache;

class Page {
	use Api;

	/**
	 * Pages API endpoint
	 *
	 * @var string
	 */
	protected static string $api_endpoint = 'https://api.notion.com/v1/pages/%s';

	/**
	 * API method
	 *
	 * @var string
	 */
	protected static string $method = 'GET';

	/**
	 * Retrieve page data
	 *
	 * @param string $id ID of page being queried.
	 *
	 * @return \JesGs\Notion\Model\Page\Page
	 */
	public static function query( string $id ): \JesGs\Notion\Model\Page\Page {
		$cached_results = Cache::get_results_cache( 'page-' . $id );
		if ( empty( $cached_results ) ) {
			$url           = vsprintf( self::$api_endpoint, array( $id ) );
			$response      = wp_remote_get( $url, self::build_query_request_headers() );
			$response_code = wp_remote_retrieve_response_code( $response );
			if ( 200 !== intval( $response_code ) ) {
				return json_decode( '{[]}' ); // return empty object.
			}

			$cached_results = wp_remote_retrieve_body( $response );
			Cache::set_results_cache( $id, $cached_results );
		}

		return new \JesGs\Notion\Model\Page\Page( json_decode( $cached_results, true ) );
	}
}
