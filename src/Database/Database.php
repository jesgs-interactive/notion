<?php
/**
 * Database API
 *
 * @package JesGs\Notion
 */

namespace JesGs\Notion\Database;

use JesGs\Notion\Bootstrap;

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
	 * @return void
	 */
	public static function query( string $id ) {
		$url           = vsprintf( self::$api_query_endpoint, array( $id ) );
		$secret        = Bootstrap::get_secret();
		$authorization = self::get_authorization( $secret );

		$headers = array_merge(
			self::$api_version,
			self::$content_type,
			$authorization,
		);

		$args = array(
			'method'  => self::$method,
			'headers' => $headers,
		);

		$response = wp_remote_get( $url, $args );
		$body     = wp_remote_retrieve_body( $response );
		var_dump( json_decode( $body, true ) );
	}

	protected static function cache_body( $body ) {
		wp_cache_set(
			'jesgs_notion_query',
			$body
		);
	}

	/**
	 * Set up the authorization bearer token
	 *
	 * @param string $secret Secret key.
	 *
	 * @return array|string[]
	 */
	public static function get_authorization( string $secret ): array {
		$auth_string                          = self::$authorization['Authorization'];
		self::$authorization['Authorization'] = sprintf( $auth_string, $secret );

		return self::$authorization;
	}
}
