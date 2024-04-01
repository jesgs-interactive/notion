<?php
/**
 * Database API
 *
 * @package JesGs\Notion
 */

namespace JesGs\Notion\Api\Database;

use JesGs\Notion\Api\Api;
use JesGs\Notion\Bootstrap;
use JesGs\Notion\Cache;
use JesGs\Notion\Model\ObjectList;

/**
 * Database API class
 *
 * 30201d4defff43209f923801ea1a1f8f/query
 */
class Database {

	use Api;

	/**
	 * Notion Databases endpoint
	 *
	 * @var string
	 */
	protected static string $api_endpoint = 'https://api.notion.com/v1/databases/%s/query';

	/**
	 * Request type
	 *
	 * @var string
	 */
	protected static string $method = 'POST';

	/**
	 * Query Notion API for database
	 *
	 * @param string $id ID of Database being queried.
	 *
	 * @return ObjectList
	 */
	public static function query( string $id ): ObjectList {
		$cached_results = Cache::get_results_cache( $id );
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

		return self::model_data( json_decode( $cached_results, true ) );
	}

	/**
	 * Build data model
	 *
	 * @param array $data Data to be modelled.
	 *
	 * @return ObjectList
	 */
	private static function model_data( array $data ): ObjectList {
		return new ObjectList( $data['results'] );
	}
}
