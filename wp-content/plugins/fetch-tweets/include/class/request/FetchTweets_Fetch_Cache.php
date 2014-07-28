<?php
/**
 * Handles caching of fetched data.
 * 
 * @package			Fetch Tweets
 * @subpackage		
 * @copyright		Michael Uno
 * @since			1.3.4
 * 
 * @filter			fetch_tweets_filter_random_credentials
 */
abstract class FetchTweets_Fetch_Cache {

	protected $arrExpiredTransientsRequestURIs = array(); // stores the expired transients' request URIs
	
	public function __construct( $sConsumerKey='', $sConsumerSecret='', $sAccessToken='', $sAccessSecret='' ) {
	
		// Set up the connection.
		$this->oOption = & $GLOBALS['oFetchTweets_Option'];		
		
		$_aApplicationKeys = $this->_getApplicationKeys( $sConsumerKey, $sConsumerSecret, $sAccessToken, $sAccessSecret );
		$this->oTwitterOAuth =  new FetchTweets_TwitterOAuth( 
			$_aApplicationKeys['consumer_key'],
			$_aApplicationKeys['consumer_secret'],
			$_aApplicationKeys['access_token'],
			$_aApplicationKeys['access_secret']
		);
						
		$this->oBase64 = new FetchTweets_Base64;	
		
		// Schedule the transient update task.
		add_action( 'shutdown', array( $this, '_replyToUpdateCacheItems' ) );
		
	}
		
		/**
		 * Returns the application keys for the Twitter API credentials.
		 * 
		 * @since			2
		 */
		protected function _getApplicationKeys( $sConsumerKey, $sConsumerSecret, $sAccessToken, $sAccessSecret ) {
		
			// If the keys are directly given to the class constructor, use them.
			if ( $sConsumerKey && $sConsumerSecret && $sAccessToken && $sAccessSecret ) {
				return array(
					'consumer_key' => $sConsumerKey,
					'consumer_secret' => $sConsumerSecret,
					'access_token' => $sAccessToken,
					'access_secret' => $sAccessSecret,
				);
			}
			
			return apply_filters( 
				'fetch_tweets_filter_random_credentials',  
				$this->oOption->getCredentialsByID( 0 )
			);
			
		}

	/**
	 * Performs HTTP GET request with the given URL and sets the cache.
	 * 
	 * @since			2.1
	 * @remark			The scope is public as the event class calls it.
	 */
	public function setGETRequestCache( $sRequestURI ) {
	
		$_aResponse = wp_remote_get( $sRequestURI, array( 'timeout' => 15, 'sslverify' => false ) );

		if ( is_wp_error( $_aResponse ) ) {
			return array();
		}
		
		$_aTweets = json_decode( wp_remote_retrieve_body( $_aResponse ), true );	
				
		if ( empty( $_aTweets ) ) return array();
		
		if ( is_string( $_aTweets ) ) return array();
		
		// If the result is not an array, something went wrong.
		if ( ! is_array( $_aTweets ) ) return ( array ) $_aTweets;		
		
		$this->setTransient( trim( $sRequestURI ), $_aTweets );
		return $_aTweets;
		
	}
		
	/**
	 * Performs the API request and sets the cache.
	 * 
	 * @access			public
	 * @remark			The scope is public since the cache renewal event also uses it.
	 */
	public function setAPIGETRequestCache( $strRequestURI, $strArrayKey=null ) {

		// Check if a custom access keys are set.
		$_aAccessKeys = $this->_getAccessKeysFromQueryURI( $strRequestURI );
		$_sSanitizedRequestURI = $this->_sanitizeRequstURI( $strRequestURI );
		$_oOriginalTwitterOAuth = $this->oTwitterOAuth;
		if ( ! empty( $_aAccessKeys ) ) {
			$this->oTwitterOAuth = new FetchTweets_TwitterOAuth( 
				$_aAccessKeys['consumer_key'],
				$_aAccessKeys['consumer_secret'], 
				$_aAccessKeys['access_token'], 
				$_aAccessKeys['access_secret']
			);
		}
		
		// Perform the API request.
		$arrTweets =  $this->oTwitterOAuth->get( $_sSanitizedRequestURI );		
			
		// Restore the original Twitter oAuth object.
		$this->oTwitterOAuth = $_oOriginalTwitterOAuth;
		
		// If the array key is specified, return the contents of the key element. Otherwise, return the retrieved array intact.
		if ( ! is_null( $strArrayKey ) && isset( $arrTweets[ $strArrayKey ] ) )
			$arrTweets = $arrTweets[ $strArrayKey ];
					
		// If empty, return an empty array.
		if ( empty( $arrTweets ) ) return array();
		
		// If the result is not an array, something went wrong.
		if ( ! is_array( $arrTweets ) )
			return ( array ) $arrTweets;
		
		// If an error occurs, do not set the cache.	
		if ( ! $this->oOption->aOptions['cache_settings']['cache_for_errors'] ) {
			if ( isset( $arrTweets['errors'][ 0 ]['message'], $arrTweets['errors'][ 0 ]['code'] ) ) {
				$arrTweets['errors'][ 0 ]['message'] .= "<!-- Request URI: {$_sSanitizedRequestURI} -->";	
				return ( array ) $arrTweets;
			}
		}
		
		// Save the cache		
		$this->setTransient( trim( $strRequestURI ), $arrTweets );

		return ( array ) $arrTweets;
		
	}
		
		/**
		 * Returns an array of access keys from the given request URI.
		 * 
		 * @since			2
		 */
		protected function _getAccessKeysFromQueryURI( $sRequestURI ){
					
			parse_str( parse_url( $sRequestURI, PHP_URL_QUERY ), $aQuery );
			$_aAccessKeys = array(
				'consumer_key' => isset( $aQuery['consumer_key'] ) ? $aQuery['consumer_key'] : null,
				'consumer_secret' => isset( $aQuery['consumer_secret'] ) ? $aQuery['consumer_secret'] : null,
				'access_token' => isset( $aQuery['access_token'] ) ? $aQuery['access_token'] : null,
				'access_secret' => isset( $aQuery['access_secret'] ) ? $aQuery['access_secret'] : null,
			);	
			
			return isset( $_aAccessKeys['consumer_key'], $_aAccessKeys['consumer_secret'], $_aAccessKeys['access_token'], $_aAccessKeys['access_secret'] )
				? $_aAccessKeys
				: array();
			
		}
		
		/**
		 * Sanitizes the given Twitter request URI
		 * 
		 * The plugin request URI may contain unnecessary query keys to make transient name unique as the plugin generates transient ID from the request URI.
		 * So this method will remove unsupported query keys from the given URI. If unsupported ones are present, Twitter API will return an error.
		 * 
		 * @since			2
		 */
		protected function _sanitizeRequstURI( $sRequestURI ) {
			return remove_query_arg( array( 'consumer_key', 'consumer_secret', 'access_token', 'access_secret' ), $sRequestURI );
		}
	
	/**
	 * A wrapper method for the set_transient() function.
	 * 
	 * @since			1.2.0
	 * @since			1.3.0			Made it public as the event method uses it.
	 */
	public function setTransient( $sRequestURI, $vData, $iTime=null, $fIgnoreLock=false ) {

		$_sTransientKey = FetchTweets_Commons::TransientPrefix . "_" . md5( $sRequestURI );
		$_sLockTransient = FetchTweets_Commons::TransientPrefix . '_' . md5( "Lock_" . $_sTransientKey );
		
		// Give some time to the server to store transients in case simultaneous accesses cursors.
		if ( FetchTweets_Cron::isBackground() ) {
			sleep( 1 );
		}
		
		// Check if the transient is locked
		if ( ! $fIgnoreLock && false !== get_transient( $_sLockTransient ) ) {
			return;	// it means the cache is being modified.
		}
		
		// Set a lock flag transient that indicates the transient is being renewed.
		if ( ! $fIgnoreLock ) {			
			set_transient(
				$_sLockTransient, 
				'locked', // the value can be anything that yields true
				10
			);	
		}
	
		// Store the cache
		set_transient(
			$_sTransientKey, 
			array( 'mod' => $iTime ? $iTime : time(), 'data' => $this->oBase64->encode( $vData ) )
		);
// FetchTweets_Debug::logArray( 'set transient: ' . $sRequestURI );

		// Schedules the action to run in the background with WP Cron. If already scheduled, skip.
		// This adds the embedding elements which takes some time to process.
		$_aArgs = array( $sRequestURI );
		if ( $iTime || wp_next_scheduled( 'fetch_tweets_action_transient_add_oembed_elements', $_aArgs ) ) return;
		wp_schedule_single_event( 
			time(), 
			'fetch_tweets_action_transient_add_oembed_elements', 	// the FetchTweets_Event class will check this action hook and executes it with WP Cron.
			$_aArgs	// must be enclosed in an array.
		);	
		
		if ( 'intense' == $this->oOption->aOptions['cache_settings']['caching_mode'] ) {
			FetchTweets_Cron::see( array(), true );
		} else {
			wp_remote_get( site_url( "/wp-cron.php" ), array( 'timeout' => 0.01, 'sslverify'   => false, ) );
		}
		

		// Delete the lock transient
		// delete_transient( $_sLockTransient );

	}
	
	/**
	 * A wrapper method for the get_transient() function.
	 * 
	 * This method does retrieves the transient with the given transient key. In addition, it checks if it is an array; otherwise, it makes it an array.
	 * 
	 * @access			public
	 * @since			1.2.0
	 * @since			1.3.0				Made it public as the event method uses it.
	 */ 
	public function getTransient( $sTransientKey, $fForceArray=true ) {
		
		$_vData = get_transient( $sTransientKey );
		
		// if it's false, no transient is stored. Otherwise, some values are in there.
		if ( $_vData === false ) return false;
							
		// If it does not have to be an array. Return the raw result.
		if ( ! $fForceArray ) return $_vData;
		
		// If it's array, okay.
		if ( is_array( $_vData ) ) return $_vData;
		
		
		// Maybe it's encoded
		if ( is_string( $_vData ) && is_serialized( $_vData ) ) 
			return unserialize( $_vData );
		
			
		// Maybe it's an object. In that case, convert it to an associative array.
		if ( is_object( $_vData ) )
			return get_object_vars( $_vData );
			
		// It's an unknown type. So cast array and return it.
		return ( array ) $_vData;
			
	}
	
	/*
	 * Callbacks
	 * */
	public function _replyToUpdateCacheItems() {	// for the shutdown hook
		
		if ( empty( $this->arrExpiredTransientsRequestURIs ) ) return;
		
		// Perform multi-dimensional array_unique()
		$this->arrExpiredTransientsRequestURIs = array_map( "unserialize", array_unique( array_map( "serialize", $this->arrExpiredTransientsRequestURIs ) ) );
		
		$_iScheduled = 0;
		foreach( $this->arrExpiredTransientsRequestURIs as $_aExpiredCacheRequest ) {
			
			/* the structure of $_aExpiredCacheRequest = array(
				'URI'	=> the API request URI
				'key'	=> the array key that holds the result. e.g. for search results, the 'statuses' key holds the fetched tweets.
			*/
			
			// Check if the URI key holds a valid url.
			if ( ! filter_var( $_aExpiredCacheRequest['URI'], FILTER_VALIDATE_URL ) ) continue;
			
			// Schedules the action to run in the background with WP Cron.
			// If already scheduled, skip.
			$_aArgs = array( $_aExpiredCacheRequest );
			if ( wp_next_scheduled( 'fetch_tweets_action_transient_renewal', $_aArgs ) ) continue; 
			wp_schedule_single_event( 
				time(), 
				'fetch_tweets_action_transient_renewal', 	// the FetchTweets_Event class will check this action hook and executes it with WP Cron.
				$_aArgs	// must be enclosed in an array.
			);	
			$_iScheduled++;
			
		}
		if ( $_iScheduled && 'intense' == $this->oOption->aOptions['cache_settings']['caching_mode'] ) {
			FetchTweets_Cron::see();	
		} else {
			wp_remote_get( site_url( "/wp-cron.php" ), array( 'timeout' => 0.01, 'sslverify'   => false, ) );
		}
				
	}
	
	
		/**
		 * Retrieves the server set allowed maximum PHP script execution time.
		 * 
		 */
		protected function _getAllowedMaxExecutionTime( $iDefault=30, $iMax=120 ) {
			
			$_iSetTime = function_exists( 'ini_get' ) && ini_get( 'max_execution_time' ) 
				? ( int ) ini_get( 'max_execution_time' ) 
				: $iDefault;
			
			return $_iSetTime > $iMax
				? $iMax
				: $_iSetTime;
			
		}
}