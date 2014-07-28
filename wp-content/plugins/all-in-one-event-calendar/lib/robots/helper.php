<?php

/**
 * File robots.txt helper.
 *
 * @author     Time.ly Network Inc.
 * @since      2.0
 *
 * @package    AI1EC
 * @subpackage AI1EC.query
 */
class Ai1ec_Robots_Helper extends Ai1ec_Base {

	/**
	 * Activation status.
	 *
	 * @return bool Whereas activation must be triggered.
	 */
	public function pre_check() {
		if ( defined( 'FS_METHOD' ) && 'direct' === FS_METHOD ) {
			return true;
		}
		return false; // disable until FS is properly resolved
	}

	/**
	 * Install robotx.txt into current Wordpress instance
	 *
	 * @return void
	 */
	public function install() {
		$option   = $this->_registry->get( 'model.option' );
		$settings = $this->_registry->get( 'model.settings' );
		$robots   = $option->get( 'ai1ec_robots_txt' );

		if ( isset( $robots['page_id'] ) &&
				! empty( $robots['is_installed'] ) &&
					$robots['page_id'] == $settings->get( 'calendar_page_id' ) ) {
			return;
		}

		$robots_file   = ABSPATH . 'robots.txt';
		$robots_txt    = array();
		$is_installed  = false;
		$current_rules = null;
		$custom_rules  = $this->rules( null, null );

		$url = wp_nonce_url(
			'edit.php?post_type=ai1ec_event&page=all-in-one-event-calendar-settings',
			'ai1ec-nonce'
		);

		if ( ! function_exists( 'request_filesystem_credentials' )  ) {
			return;
		}

		$creds = request_filesystem_credentials( $url, '', false, false, null );
		if ( ! WP_Filesystem( $creds ) ) {
			request_filesystem_credentials( $url, '', true, false, null );
		}

		global $wp_filesystem;
		if ( $wp_filesystem->exists( $robots_file )
				&& $wp_filesystem->is_readable( $robots_file )
					&& $wp_filesystem->is_writable( $robots_file ) ) {
			// Get current robots txt content
			$current_rules = $wp_filesystem->get_contents( $robots_file );

			// Update robots.txt
			$custom_rules = $this->rules( $current_rules, null );
			$is_installed = $wp_filesystem->put_contents(
				$robots_file,
				$custom_rules,
				FS_CHMOD_FILE
			);

			if ( $is_installed ) {
				$robots_txt['is_installed'] = true;
			}
		} else {
			$robots_txt['is_installed'] = false;
		}

		// Set Page ID
		$robots_txt['page_id'] = $settings->get( 'calendar_page_id' );

		// Update Robots Txt
		$option->set( 'ai1ec_robots_txt', $robots_txt );

		// Update settings textarea
		$settings->set( 'edit_robots_txt', $custom_rules );
	}

	/**
	 * Get default robots rules for the calendar
	 *
	 * @param  string $output Current robots rules
	 * @param  string $public Public flag
	 * @return array
	 */
	public function rules( $output, $public ) {
		// Current rules
		$current_rules = array_map(
			'trim',
			explode( PHP_EOL, $output )
		);

		// Get calendar page URI
		$calendar_page_id = $this->_registry->get( 'model.settings' )
											->get( 'calendar_page_id' );
		$page_base = get_page_uri( $calendar_page_id );

		// Custom rules
		$custom_rules = array();
		if ( $page_base ) {
			$custom_rules += array(
				"User-agent: *",
				"Disallow: /$page_base/action~posterboard/",
				"Disallow: /$page_base/action~agenda/",
				"Disallow: /$page_base/action~oneday/",
				"Disallow: /$page_base/action~month/",
				"Disallow: /$page_base/action~week/",
				"Disallow: /$page_base/action~stream/",
			);
		}

		$robots = array_merge( $current_rules, $custom_rules );

		return implode(
			PHP_EOL,
			array_filter( array_unique( $robots ) )
		);
	}
}
