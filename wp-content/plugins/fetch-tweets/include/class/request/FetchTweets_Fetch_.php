<?php
/**
 * Fetches and displays tweets.
 * 
 * @package			Fetch Tweets
 * @subpackage		
 * @copyright		Michael Uno
 * @filter			fetch_tweets_template_path - specifies the template path.
 * @action			fetch_tweets_action_transient_renewal - for WP Cron single event.
 * @action			fetch_tweets_action_transient_add_oembed_elements - for WP Cron single event.
 */
abstract class FetchTweets_Fetch_ extends FetchTweets_Fetch_ByTweetID {
	
	/**
	 * Returns the output of tweets by the given arguments.
	 * 
	 */
	public function getTweetsOutput( $aArgs ) {	// called from the shortcode callback.
		
		// Capture the output buffer.
		ob_start(); // Start buffer.
		$this->drawTweets( $aArgs );
		$_sContent = ob_get_contents(); // Assign the content buffer to a variable.
		ob_end_clean(); // End buffer and remove the buffer.
		return $_sContent;
		
	}

	/**
	 * Prints tweets based on the given arguments.
	 * 
	 * @param			array			$aArgs 
	 * 	id - The post id. default: null. e.g. 125  or 124, 235
	 * 	tag - default: null. e.g. php or php, WordPress. In this method this tag is only used to pass the argument to the template filter.
	 *  sort - default: descending. Either ascending, descending, or random can be used.
	 * 	count - default: 20
	 * 	operator - default: AND. Either AND or IN or NOT IN is used.
	 *  q - default: null e.g. WordPress
	 *  screen_name - default: null e.g. miunosoft
	 *  include_rts - default: 0. Either 1 or 0.
	 *  exclude_replies - default: 0. Either 1 or 0.
	 *  cache - default: 1200
	 *	lang - default: null.  
	 *	result_type - default: mixed
	 *	list_id - default: null. e.g. 8044403
	 *	twitter_media - ( boolean ) determines whether the Twitter media should be displayed or not. Currently only photos are supported by the Twitter API.
	 *	external_media - ( boolean ) determines whether the plugin attempts to replace external media links to embedded elements.
	 *
	 * Template options
	 *	template - the template slug.
	 *	width - 
	 *	width_unit - 
	 *	height	- 
	 *	height_unit - 
	 *	avatar_size - default: 48 
	 * 
	 * */	
	public function drawTweets( $aArgs ) {
		
		$aRawArgs = ( array ) $aArgs; 
		$aArgs = FetchTweets_Utilities::uniteArrays( $aRawArgs, $this->oOption->aOptions['default_values'], $this->oOption->aStructure_DefaultParams, $this->oOption->aStructure_DefaultTemplateOptions );
		$aArgs['id'] = isset( $aArgs['ids'] ) && ! empty( $aArgs['ids'] ) ? $aArgs['ids'] : $aArgs['id'];	// backward compatibility
		$aArgs['id'] = is_array( $aArgs['id'] ) ? $aArgs['id'] : preg_split( "/[,]\s*/", trim( ( string ) $aArgs['id'] ), 0, PREG_SPLIT_NO_EMPTY );

		// Debug
		// echo var_dump( $aArgs );
		// echo "<pre>" . htmlspecialchars( print_r( $aArgs, true ) ) . "</pre>";	
		// echo "<pre>" . htmlspecialchars( print_r( $this->oOption->aOptions, true ) ) . "</pre>";	
		// return;		

		$_aTweets = $this->getTweetsAsArray( $aArgs, $aRawArgs );
		if ( isset( $_aTweets['_debug'] ) && $_aTweets['_debug'] ) {
			$this->_includeTemplate( $_aTweets, $aArgs, $this->oOption->aOptions );
			return;			
		}
		if ( empty( $_aTweets ) || ! is_array( $_aTweets ) ) {
			_e( 'No result could be fetched.', 'fetch-tweets' );
			return;
		}
		if ( isset( $_aTweets['errors'][ 0 ]['message'], $_aTweets['errors'][ 0 ]['code'] ) ) {
			echo '<strong>Fetch Tweets</strong>: ' . $_aTweets['errors'][ 0 ]['message'] . ' ' . __( 'Code', 'fetch-tweets' ) . ':' . $_aTweets['errors'][ 0 ]['code'];	
			return;
		}
		else if ( isset( $_aTweets['error'], $_aTweets['request'] ) && $_aTweets['error'] && is_string( $_aTweets['error'] ) ) {
			echo '<strong>Fetch Tweets</strong>: ' . $_aTweets['error'];	
			return;
		}

		// Format the tweet response array.
		$this->_formatTweetArrays( $_aTweets, $aArgs ); // the array is passed as reference.
	
		/* Include the template to render the output - this method is also called from filter callbacks( which requires a return value ) but go ahead and render the output. */		
		$this->_includeTemplate( $_aTweets, $aArgs, $this->oOption->aOptions );
 		
	}

	
	/**
	 * Fetches tweets based on the argument.
	 * 
	 * @remark			The scope is public as the feed extension uses it.
	 * @param			array			$aArgs				The argument array that is merged with the default option values. It is passed by reference to let assign post meta options.
	 * @param			array			$aRawArgs			The raw argument array that is not merged with any. Used by the _getTweetsAsArrayByPostIDs() method that fetches tweets by post ID.
	 */
	public function getTweetsAsArray( & $aArgs, $aRawArgs ) {	

		if ( isset( $aArgs['q'] ) )	// custom call by search keyword
			return $this->getTweetsBySearch( $aArgs['q'], $aArgs['count'], $aArgs['lang'], $aArgs['result_type'], $aArgs['until'], $aArgs['geocode'], $aArgs['cache'] );
		else if ( isset( $aArgs['screen_name'] ) )	// custom call by screen name
			return $this->getTweetsByScreenNames( $aArgs['screen_name'], $aArgs['count'], $aArgs['include_rts'], $aArgs['exclude_replies'], $aArgs['cache'] );
		else if ( isset( $aArgs['list_id'] ) ) 	// only public list can be fetched with this method
			return $this->_getTweetsByListID( $aArgs['list_id'], $aArgs['include_rts'], $aArgs['cache'] );
		else if ( isset( $aArgs['account_id'] ) )
			return $this->_getTweetsByHomeTimeline( $aArgs['account_id'], $aArgs['exclude_replies'], $aArgs['include_rts'] );
		else if ( isset( $aArgs['tweet_id'] ) ) {
			return $this->_getResponseByTweetID( $aArgs['tweet_id'], $aArgs['cache'] );
		}
		else	// normal
			return $this->_getTweetsAsArrayByPostIDs( $aArgs['id'], $aArgs, $aRawArgs );
		
	}
		/**
		 * 
		 * @param			array|integer			$vPostIDs			The target post ID of the Fetch Tweet rule post type.
		 * @param			array					$aArgs				The argument array. It is passed by reference to let assign post meta options.
		 */
		protected function _getTweetsAsArrayByPostIDs( $vPostIDs, & $aArgs, $aRawArgs ) {	
		
			$_aTweets = array();
			$_fDebug = false;
			foreach( ( array ) $vPostIDs as $_iPostID ) {
				
				$aArgs['tweet_type'] = get_post_meta( $_iPostID, 'tweet_type', true );
				$aArgs['count'] = get_post_meta( $_iPostID, 'item_count', true );
				$aArgs['include_rts'] = get_post_meta( $_iPostID, 'include_rts', true );
				$aArgs['cache'] = get_post_meta( $_iPostID, 'cache', true );
				
				$_aRetrievedTweets = array();
				switch ( $aArgs['tweet_type'] ) {
					case 'search':
						$aArgs['q'] = get_post_meta( $_iPostID, 'search_keyword', true );	
						$aArgs['result_type'] = get_post_meta( $_iPostID, 'result_type', true );
						$aArgs['lang'] = get_post_meta( $_iPostID, 'language', true );
						$aArgs['until'] = get_post_meta( $_iPostID, 'until', true );
						$aArgs['geocentric_coordinate'] = get_post_meta( $_iPostID, 'geocentric_coordinate', true );
						$aArgs['geocentric_radius'] = get_post_meta( $_iPostID, 'geocentric_radius', true );
						$_sGeoCode = '';
						if ( 
							is_array( $aArgs['geocentric_coordinate'] ) && is_array( $aArgs['geocentric_radius'] )
							&& isset( $aArgs['geocentric_coordinate']['latitude'], $aArgs['geocentric_radius']['size'] ) 
							&& $aArgs['geocentric_coordinate']['latitude'] !== '' && $aArgs['geocentric_coordinate']['longitude'] !== ''	// the coordinate can be 0
							&& $aArgs['geocentric_radius']['size'] !== '' 
						) {
							// "latitude,longitude,radius",
							$_sGeoCode = trim( $aArgs['geocentric_coordinate']['latitude'] ) . "," . trim( $aArgs['geocentric_coordinate']['longitude'] ) 
								. "," . trim( $aArgs['geocentric_radius']['size'] ) . $aArgs['geocentric_radius']['unit'] ;
						}						
						$aArgs = FetchTweets_Utilities::uniteArrays( $aRawArgs, $aArgs ); // The direct input takes its precedence.
						$_aRetrievedTweets = $this->getTweetsBySearch( $aArgs['q'], $aArgs['count'], $aArgs['lang'], $aArgs['result_type'], $aArgs['until'], $_sGeoCode, $aArgs['cache'] );
						break;
					case 'list':
						$aArgs['account_id'] = get_post_meta( $_iPostID, 'account_id', true );
						$aArgs['mode'] = get_post_meta( $_iPostID, 'mode', true );
						$aArgs['list_id'] = get_post_meta( $_iPostID, 'list_id', true );
						$aArgs = FetchTweets_Utilities::uniteArrays( $aRawArgs, $aArgs ); // The direct input takes its precedence.
						$_aRetrievedTweets = $this->_getTweetsByListID( $aArgs['list_id'], $aArgs['include_rts'], $aArgs['cache'], $aArgs['account_id'], $aArgs['mode'] );
						break;
					case 'home_timeline':
						$aArgs['account_id'] = get_post_meta( $_iPostID, 'account_id', true );
						$aArgs['exclude_replies'] = get_post_meta( $_iPostID, 'exclude_replies', true );
						$aArgs = FetchTweets_Utilities::uniteArrays( $aRawArgs, $aArgs ); // The direct input takes its precedence.
						$_aRetrievedTweets = $this->_getTweetsByHomeTimeline( $aArgs['account_id'], $aArgs['exclude_replies'], $aArgs['include_rts'], $aArgs['cache'] );
						break;
					case 'feed':
						$aArgs['json_url'] = get_post_meta( $_iPostID, 'json_url', true );
						$aArgs = FetchTweets_Utilities::uniteArrays( $aRawArgs, $aArgs ); // The direct input takes its precedence.
						$_aRetrievedTweets = $this->_getTweetsByJSONFeed( $aArgs['json_url'], $aArgs['cache'] );
						break;
					case 'custom_query':
						$aArgs['custom_query'] = get_post_meta( $_iPostID, 'custom_query', true );
						$aArgs['response_key'] = get_post_meta( $_iPostID, 'response_key', true );
						$aArgs = FetchTweets_Utilities::uniteArrays( $aRawArgs, $aArgs ); // The direct input takes its precedence.
						$_aRetrievedTweets = $this->_getResponseWithCustomRequest( $aArgs['custom_query'], $aArgs['response_key'], $aArgs['cache'] );
						$_fDebug = true;
						break;
					case 'tweet_id':
						$aArgs['tweet_id'] = get_post_meta( $_iPostID, 'tweet_id', true );
						$aArgs = FetchTweets_Utilities::uniteArrays( $aRawArgs, $aArgs ); // The direct input takes its precedence.
						$_aRetrievedTweets = $this->_getResponseByTweetID( $aArgs['tweet_id'], $aArgs['cache'] );
						break;
					case 'screen_name':
					default:	
						$aArgs['screen_name'] = get_post_meta( $_iPostID, 'screen_name', true );	
						$aArgs['exclude_replies'] = get_post_meta( $_iPostID, 'exclude_replies', true );	
						$aArgs = FetchTweets_Utilities::uniteArrays( $aRawArgs, $aArgs ); // The direct input takes its precedence.
						$_aRetrievedTweets = $this->getTweetsByScreenNames( $aArgs['screen_name'], $aArgs['count'], $aArgs['include_rts'], $aArgs['exclude_replies'], $aArgs['cache'] );
						break;				
				}	

				$_aTweets = array_merge( $_aRetrievedTweets, $_aTweets );
					
			}
			
			if ( $_fDebug ) {
				$_aTweets['_debug'] = true;
			}
			
			return $_aTweets;
			
		}
	
}