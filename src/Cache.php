<?php
/**
 * Caching class
 *
 * @package JesGs\Notion
 */

namespace JesGs\Notion;

/**
 * Handle caching for Notion queries
 */
class Cache {

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
	 * Cache results of body response
	 *
	 * @param string $id    Database ID.
	 * @param string $body JSON-encoded string to cache.
	 *
	 * @global \wpdb $wpdb
	 *
	 * @return bool
	 */
	public static function set_results_cache( string $id, string $body ): bool {
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

		return ! empty( $results );
	}

	/**
	 * Retrieve cached results
	 *
	 * @param string $id Database ID.
	 *
	 * @return string|null
	 */
	public static function get_results_cache( string $id ): ?string {
		global $wpdb;

		$transient_name         = '_transient-' . self::$transient_name . $id;
		$transient_timeout_name = '_transient_timeout_' . self::$transient_name . $id;

		// @phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- I wouldn't have to do this if WP's option functions didn't eff up JSON
		$cached_time = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT option_value FROM $wpdb->options WHERE option_name = %s LIMIT 1",
				$transient_timeout_name
			)
		);

		if ( time() > (int) $cached_time ) {
			return null;
		}

		// @phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- I wouldn't have to do this if WP's option functions didn't eff up JSON
		$row_value = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT option_value FROM $wpdb->options WHERE option_name = %s LIMIT 1",
				$transient_name
			)
		);

		if ( ! is_object( $row_value ) ) {
			return false;
		}

		return $row_value->option_value;
	}
}
