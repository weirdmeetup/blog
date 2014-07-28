<?php

/**
 * The class which handles Frontend CSS.
 *
 * @author     Time.ly Network Inc.
 * @since      2.0
 *
 * @package    AI1EC
 * @subpackage AI1EC.Css
 */
class Ai1ec_Css_Frontend extends Ai1ec_Base {

	const QUERY_STRING_PARAM                = 'ai1ec_render_css';

	// This is for testing purpose, set it to AI1EC_DEBUG value.
	const PARSE_LESS_FILES_AT_EVERY_REQUEST = AI1EC_DEBUG;

	const KEY_FOR_PERSISTANCE               = 'ai1ec_parsed_css';
	/**
	 * @var Ai1ec_Css_Persistence_Helper
	 */
	private $persistance_context;

	/**
	 * @var Ai1ec_Less_Lessphp
	 */
	private $lessphp_controller;

	/**
	 * @var Ai1ec_Wordpress_Db_Adapter
	 */
	private $db_adapter;

	/**
	 * @var boolean
	 */
	private $preview_mode;

	/**
	 * @var Ai1ec_Template_Adapter
	 */
	private $template_adapter;

	public function __construct(
		Ai1ec_Registry_Object $registry,
		$preview_mode = false
	) {
		parent::__construct( $registry );
		$this->persistance_context = $this->_registry->get(
			'cache.strategy.persistence-context',
			self::KEY_FOR_PERSISTANCE,
			AI1EC_CACHE_PATH
		);
		$this->lessphp_controller  = $this->_registry->get( 'less.lessphp' );
		$this->db_adapter          = $this->_registry->get( 'model.option' );
		$this->preview_mode        = $preview_mode;
	}

	/**
	 * Renders the css for our frontend.
	 *
	 * Sets etags to avoid sending not needed data
	 */
	public function render_css() {
		header( 'HTTP/1.1 200 OK' );
		header( 'Content-Type: text/css', true, 200 );
		// Aggressive caching to save future requests from the same client.
		$etag = '"' . md5( __FILE__ . $_GET[self::QUERY_STRING_PARAM] ) . '"';
		header( 'ETag: ' . $etag );
		$max_age = 31536000;
		$time_sys = $this->_registry->get( 'date.system' );
		header(
			'Expires: ' .
			gmdate(
				'D, d M Y H:i:s',
				$time_sys->current_time() + $max_age
			) .
			' GMT'
		);
		header( 'Cache-Control: public, max-age=' . $max_age );
		if (
			empty( $_SERVER['HTTP_IF_NONE_MATCH'] ) ||
			$etag !== stripslashes( $_SERVER['HTTP_IF_NONE_MATCH'] )
		) {
			// compress data if possible
			$compatibility_ob = $this->_registry->get( 'compatibility.ob' );
			if ( $this->_registry->get( 'http.request' )->client_use_gzip() ) {
				$compatibility_ob->start( 'ob_gzhandler' );
				header( 'Content-Encoding: gzip' );
			} else {
				$compatibility_ob->start();
			}
			$content = $this->get_compiled_css();
			echo $content;
			$compatibility_ob->end_flush();
		} else {
			// Not modified!
			status_header( 304 );
		}
		// We're done!
		Ai1ec_Http_Response_Helper::stop( 0 );
	}

	/**
	 *
	 * @param string $css
	 * @throws Ai1ec_Cache_Write_Exception
	 */
	public function update_persistence_layer( $css ) {
		$this->persistance_context->write_data_to_persistence( $css );
		$this->save_less_parse_time();
	}


	/**
	 * Get the url to retrieve the css
	 *
	 * @return string
	 */
	public function get_css_url() {
		$time = (int) $this->db_adapter->get( self::QUERY_STRING_PARAM );
		$template_helper = $this->_registry->get( 'template.link.helper' );
		return add_query_arg(
			array( self::QUERY_STRING_PARAM => $time, ),
			trailingslashit( $template_helper->get_site_url() )
		);
	}

	/**
	 * Create the link that will be added to the frontend
	 */
	public function add_link_to_html_for_frontend() {
		$preview = '';
		if( true === $this->preview_mode ) {
			// bypass browser caching of the css
			$now = strtotime( 'now' );
			$preview = "&preview=1&nocache={$now}&ai1ec_stylesheet=" . $_GET['ai1ec_stylesheet'];
		}
		$url = $this->get_css_url() . $preview;
		wp_enqueue_style( 'ai1ec_style', $url, array(), AI1EC_VERSION );
	}

	/**
	 * Invalidate the persistence layer only after a successful compile of the
	 * LESS files.
	 *
	 * @param  array   $variables          LESS variable array to use
	 * @param  boolean $update_persistence Whether the persist successful compile
	 *
	 * @return boolean                     Whether successful
	 */
	public function invalidate_cache(
		array $variables    = null,
		$update_persistence = false
	) {
		// Reset the parse time to force a browser reload of the CSS, whether we are
		// updating persistence or not.
		$this->save_less_parse_time();
		$notification = $this->_registry->get( 'notification.admin' );
		try {
			// Try to parse the css
			$css = $this->lessphp_controller->parse_less_files( $variables );
			if ( $update_persistence ) {
				$this->update_persistence_layer( $css );
			} else {
				$this->persistance_context->delete_data_from_persistence();
			}
		} catch ( Ai1ec_Cache_Write_Exception $e ) {
			// This means successful during parsing but problems persisting the CSS.
			$message = '<p>' . Ai1ec_I18n::__( "The LESS file compiled correctly but there was an error while saving the generated CSS to persistence." ) . '</p>';
			$notification->store( $message, 'error' );
			return false;
		} catch ( Exception $e ) {
			// An error from lessphp.
			$message = sprintf(
				Ai1ec_I18n::__( '<p><strong>There was an error while compiling CSS.</strong> The message returned was: <em>%s</em></p>' ),
				$e->getMessage()
			);
			$notification->store( $message, 'error', 1 );
			return false;
		}
		return true;
	}


	/**
	 * Update the less variables on the DB and recompile the CSS
	 *
	 * @param array $variables
	 * @param boolean $resetting are we resetting or updating variables?
	 */
	public function update_variables_and_compile_css( array $variables, $resetting ) {
		$no_parse_errors = $this->invalidate_cache( $variables, true );
		$notification    = $this->_registry->get( 'notification.admin' );

		if ( $no_parse_errors ) {
			$this->db_adapter->set(
				Ai1ec_Less_Lessphp::DB_KEY_FOR_LESS_VARIABLES,
				$variables
			);

			if ( true === $resetting ) {
				$message = sprintf(
					'<p>' . Ai1ec_I18n::__(
						"Theme options were successfully reset to their default values. <a href='%s'>Visit site</a>"
					) . '</p>',
					get_site_url()
				);
			} else {
				$message = sprintf(
					'<p>' .Ai1ec_I18n::__(
						"Theme options were updated successfully. <a href='%s'>Visit site</a>"
					) . '</p>',
					get_site_url()
				);
			}

			$notification->store( $message );
		}
	}
	/**
	 * Try to get the CSS from cache.
	 * If it's not there re-generate it and save it to cache
	 * If we are in preview mode, recompile the css using the theme present in the url.
	 *
	 */
	private function get_compiled_css() {
		try {
			// If we want to force a recompile, we throw an exception.
			if( $this->preview_mode === true || self::PARSE_LESS_FILES_AT_EVERY_REQUEST === true ) {
				throw new Ai1ec_Cache_Not_Set_Exception();
			}else {
				// This throws an exception if the key is not set
				$css = $this->persistance_context->get_data_from_persistence();
				return $css;
			}
		} catch ( Ai1ec_Cache_Not_Set_Exception $e ) {
			// If we are in preview mode we force a recompile and we pass the variables.
			if( $this->preview_mode ) {
				return $this->lessphp_controller->parse_less_files(
					$this->lessphp_controller->get_less_variable_data_from_config_file()
				);
			} else {
				$css = $this->lessphp_controller->parse_less_files();
			}
			try {
				$this->update_persistence_layer( $css );
				return $css;
			} catch ( Ai1ec_Cache_Write_Exception $e ) {
				// If something is really broken, still return the css.
				// This means we parse it every time. This should never happen.
				return $css;
			}
		}
	}

	/**
	 * Save the compile time to the db so that we can use it to build the link
	 */
	private function save_less_parse_time() {
		$this->db_adapter->set(
			self::QUERY_STRING_PARAM,
			$this->_registry->get( 'date.system' )->current_time(),
			true
		);
	}
}
