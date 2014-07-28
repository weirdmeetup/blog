<?php
/**
	An event handler class.
	
 * @package     Fetch Tweets
 * @copyright   Copyright (c) 2013, Michael Uno
 * @authorurl	http://michaeluno.jp
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since		1.0.0
 * @action			hook			fetch_tweets_action_setup_transients
 * @action			hook			fetch_tweets_action_simplepie_renew_cache
 * @action			hook			fetch_tweets_action_transient_renewal
 * @filter			apply			fetch_tweets_filter_plugin_cron_actions			Applies to the action arrays that the plugin Cron triggers.
	
*/
abstract class FetchTweets_Event_ {

	public function __construct() {
		
		// Objects
		$this->oBase64 = new FetchTweets_Base64;
		
		// For transient (cache) renewal events
		add_action( 'fetch_tweets_action_transient_renewal', array( $this, '_replyToRenewTransients' ) );	
		
		// For transient (cache) formatting events - adds oEmbed elements.
		add_action( 'fetch_tweets_action_transient_add_oembed_elements', array( $this, '_replyToAddOEmbedElements' ) );
		
		// For SimplePie cache renewal events 
		add_action( 'fetch_tweets_action_simplepie_renew_cache', array( $this, '_replyToRenewSimplePieCaches' ) );		

		// This must be called after the above action hooks are added.
		if ( 'intense' == $GLOBALS['oFetchTweets_Option']->aOptions['cache_settings']['caching_mode'] ) {			
			new FetchTweets_Cron(
				apply_filters(
					'fetch_tweets_filter_plugin_cron_actions',
					array(
						'fetch_tweets_action_transient_renewal',
						'fetch_tweets_action_transient_add_oembed_elements',
						'fetch_tweets_action_simplepie_renew_cache',
					)
				)
			);	
		} else {
			if ( FetchTweets_Cron::isBackground() ) {
				exit;
			}
		}
				
		// Redirects
		if ( isset( $_GET['fetch_tweets_link'] ) && $_GET['fetch_tweets_link'] ) {			
			$_oRedirect = new FetchTweets_Redirects;
			$_oRedirect->go( $_GET['fetch_tweets_link'] );	// will exit there.
		}
			
		// Draw the cached image.
		if ( isset( $_GET['fetch_tweets_image'] ) && $_GET['fetch_tweets_image'] && is_user_logged_in() ) {
			
			$_oImageLoader = new FetchTweets_ImageHandler( 'FTWS' );
			$_oImageLoader->draw( $_GET['fetch_tweets_image'] );
			exit;
			
		}			
		
		// For the activation hook
		add_action( 'fetch_tweets_action_setup_transients', array( $this, '_replyToSetUpTransients' ) );
			
	}
		
	
	public function _replyToSetUpTransients() {
		
		$_oUA = new FetchTweets_UserAds();
		$_oUA->setupTransients();		
		
	}

	public function _replyToRenewSimplePieCaches( $vURLs ) {
		
		// Setup Caches
		$_oFeed = new FetchTweets_SimplePie();

		// Set urls
		$_oFeed->set_feed_url( $vURLs );	
		
		// this should be set after defining $vURLs
		$_oFeed->set_cache_duration( 0 );	// 0 seconds, means renew the cache right away.
	
		// Set the background flag to True so that it won't trigger the event action recursively.
		$_oFeed->setBackground( true );
		$_oFeed->init();	
		
	}
	
	/**
	 * Renew the cache of the given request URI
	 * 
	 */
	public function _replyToRenewTransients( $aRequest ) {
		
		// Perform the cache renewal.
		$_oFetch = new FetchTweets_Fetch;
		if ( '_not_api_request' == $aRequest['key'] ) {
			$_oFetch->setGETRequestCache( $aRequest['URI'] );
		} else {
			$_oFetch->setAPIGETRequestCache( $aRequest['URI'], $aRequest['key'] );
		}
		
	}
		
	/**
	 * Re-saves the cache after adding oEmbed elements.
	 * 
	 * @since			1.3.0
	 */
	public function _replyToAddOEmbedElements( $sRequestURI ) {

		$strTransientKey = FetchTweets_Commons::TransientPrefix . "_" . md5( $sRequestURI );

		// Check if the transient is locked
		$strLockTransient = FetchTweets_Commons::TransientPrefix . '_' . md5( "LockOEm_" . trim( $strTransientKey ) );	// up to 40 characters, the prefix can be up to 8 characters
		if ( false !== get_transient( $strLockTransient ) ) {
			return;	// it means the cache is being modified.
		}	
		
		// Set a lock flag transient that indicates the transient is being renewed.
		set_transient(
			$strLockTransient, 
			true, // the value can be anything that yields true
			FetchTweets_Utilities::getAllowedMaxExecutionTime()	
		);	
	
		// Perform oEmbed caching - no API request will be performed
		$oFetch = new FetchTweets_Fetch;
		
		// structure: array( 'mod' => time(), 'data' => $this->oBase64->encode( $vData ) ), 
		$arrTransient = $oFetch->getTransient( $strTransientKey );			
	
		// If the mandatory keys are not set, it's broken.
		if ( ! isset( $arrTransient['mod'], $arrTransient['data'] ) ) {
			delete_transient( $strTransientKey );
			return;
		}
		
		$arrTweets = ( array ) $this->oBase64->decode( $arrTransient['data'] );		
		$oFetch->addEmbeddableMediaElements( $arrTweets );		// the array is passed as reference.
		
		// Re-save the cache.
// FetchTweets_Debug::logArray( 'saving oembed transient' );
		$oFetch->setTransient( $sRequestURI, $arrTweets, $arrTransient['mod'], true );	// the method handles the encoding.
	
		// Delete the lock transient
		delete_transient( $strLockTransient );

	}
				
					
}