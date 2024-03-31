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
	 * Transient Name for caching purposes
	 *
	 * @var string
	 */
	protected static string $transient_name = 'jesg_notion_db_results-';

	/**
	 * Transient Cache
	 *
	 * @var int
	 */
	protected static int $transient_cache_time = 3600; // cache for an hour.

	/**
	 * Query Notion API for database
	 *
	 * @param string $id ID of Database being queried.
	 *
	 * @return string
	 */
	public static function query( string $id ): array {
		$cached_results = self::get_results_cache( $id );
		if ( empty( $cached_results ) ) {
			$url           = vsprintf( self::$api_query_endpoint, array( $id ) );
			$response      = wp_remote_get( $url, self::build_query() );
			$response_code = wp_remote_retrieve_response_code( $response );
			if ( 200 !== intval( $response_code ) ) {
				return json_decode( '{[]}' ); // return empty object.
			}

			$cached_results = wp_remote_retrieve_body( $response );
			self::set_results_cache( $id, $cached_results );
		}

		return json_decode( $cached_results, true );
	}

	/**
	 * Build the api query
	 *
	 * @return array
	 */
	private static function build_query() {
		$secret        = Bootstrap::get_secret();
		$authorization = self::get_authorization( $secret );

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
	 * Cache results of body response
	 *
	 * @param string $id    Database ID.
	 * @param string $body JSON-encoded string to cache.
	 *
	 * @global \wpdb $wpdb
	 *
	 * @return bool
	 */
	protected static function set_results_cache( string $id, string $body ): bool {
		global $wpdb;
		$transient_name         = '_transient-' . self::$transient_name . $id;
		$transient_timeout_name = '_transient_timeout_' . self::$transient_name . $id;
		$transient_timeout      = time() + self::$transient_cache_time;

		// @phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- We're inserting here and we would rather use JSON
		$results = $wpdb->query(
			$wpdb->prepare(
				"INSERT INTO `$wpdb->options` (`option_name`, `option_value`, `autoload`) VALUES (%s, %s, %s) ON DUPLICATE KEY UPDATE `option_name` = VALUES(`option_name`), `option_value` = VALUES(`option_value`), `autoload` = VALUES(`autoload`)",
				array(
					$transient_name,
					$body,
					'no',
				)
			)
		);

		if ( ! $results ) {
			return false;
		}

		// @phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- We're inserting here and we would rather use JSON
		$results = $wpdb->query(
			$wpdb->prepare(
				"INSERT INTO `$wpdb->options` (`option_name`, `option_value`, `autoload`) VALUES (%s, %s, %s) ON DUPLICATE KEY UPDATE `option_name` = VALUES(`option_name`), `option_value` = VALUES(`option_value`), `autoload` = VALUES(`autoload`)",
				array(
					$transient_timeout_name,
					$transient_timeout,
					'no',
				)
			)
		);
	}

	/**
	 * Retrieve cached results
	 *
	 * @param string $id Database ID.
	 *
	 * @return bool|string
	 */
	protected static function get_results_cache( string $id ) {
		global $wpdb;

		$transient_name = '_transient-' . self::$transient_name . $id;

		// @phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- I wouldn't have to do this if WP's option functions didn't eff up JSON
		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT option_value FROM $wpdb->options WHERE option_name = %s LIMIT 1",
				$transient_name
			)
		);

		if ( ! is_object( $row ) ) {
			return false;
		}

		return $row->option_value;
	}

	/**
	 * Set up the authorization bearer token header
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
