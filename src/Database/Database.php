<?php
/**
 * Database API
 *
 * @package JesGs\Notion
 */

namespace JesGs\Notion\Database;

use JesGs\Notion\Bootstrap;
use JesGs\Notion\Cache;

/**
 * Database API class
 *
 * 30201d4defff43209f923801ea1a1f8f/query
 */
class Database {

	/**
	 * Notion's current API version
	 *
	 * @var array|string[]
	 */
	protected static array $api_version = array(
		'Notion-Version' => '2022-06-28',
	);

	/**
	 * Authorization token
	 *
	 * @var array|string[]
	 */
	protected static array $authorization = array(
		'Authorization' => 'Bearer %s',
	);

	/**
	 * Data-type to retrieve
	 *
	 * @var array|string[]
	 */
	protected static array $content_type = array(
		'Content-Type' => 'application/json',
	);

	/**
	 * Notion Databases endpoint
	 *
	 * @var string
	 */
	protected static string $api_query_endpoint = 'https://api.notion.com/v1/databases/%s/query';

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
			$url           = vsprintf( self::$api_query_endpoint, array( $id ) );
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
	 * Build the api query
	 *
	 * @return array
	 */
	private static function build_query_request_headers(): array {
		$secret        = Bootstrap::get_secret();
		$authorization = Bootstrap::get_authorization( $secret );

		$headers = array_merge(
			self::$api_version,
			self::$content_type,
			$authorization,
		);

		return array(
			'method'  => self::$method,
			'headers' => $headers,
		);
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
