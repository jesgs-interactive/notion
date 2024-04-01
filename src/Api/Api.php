<?php

namespace JesGs\Notion\Api;

use JesGs\Notion\Bootstrap;

trait Api {

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
	 * Build the api query
	 *
	 * @return array
	 */
	private static function build_query_request_headers(): array {
		$secret        = self::get_secret();
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


	/**
	 * Get the secret key for Notion
	 *
	 * @return string
	 */
	public static function get_secret(): string {
		if ( empty( Bootstrap::get_env( 'SECRET' ) ) ) {
			return '';
		}

		return Bootstrap::get_env( 'SECRET' );
	}
}
