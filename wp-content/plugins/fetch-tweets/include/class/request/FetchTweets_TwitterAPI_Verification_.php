<?php
/**
 * Handles Twitter API verification
 * 
 * @package			Fetch Tweets
 * @subpackage		
 * @copyright		Michael Uno
 * @since			2
 * 
 * @filter			apply			fetch_tweets_filter_request_rate_limit_status_keys			[2.3.0+] Applies to the request query that specifies the retrieving status keys. Default: statuses, search, lists.
 * @filter			apply			fetch_tweets_filter_rate_limit_status_translation			[2.3.0+] Applies to the translation array for the rate limit status labels.
 */
abstract class FetchTweets_TwitterAPI_Verification_ {

	function __construct( $sConsumerKey, $sConsumerSecret, $sAccessToken, $sAccessSecret ) {
		
		$this->sConsumerKey = $sConsumerKey;
		$this->sConsumerSecret = $sConsumerSecret;
		$this->sAccessToken = $sAccessToken;
		$this->sAccessSecret = $sAccessSecret;
		
	}
	
	/**
	 * 
	 * @see			https://dev.twitter.com/docs/api/1.1/get/application/rate_limit_status
	 */
	public function getStatus() {
		
		// Return the cached response if available.
		$_sCacheID = FetchTweets_Commons::TransientPrefix . '_' . md5( serialize( array( $this->sConsumerKey, $this->sConsumerSecret, $this->sAccessToken, $this->sAccessSecret ) ) );
		$_vData = get_transient( $_sCacheID );
		if ( false !== $_vData ) return $_vData;
		
		// Perform the requests.
		$_oConnect =  new FetchTweets_TwitterOAuth( $this->sConsumerKey, $this->sConsumerSecret, $this->sAccessToken, $this->sAccessSecret );
		$_aUser = $_oConnect->get( 'account/verify_credentials' );
		
		// If the user id could not be retrieved, it means it failed.
		if ( ! isset( $_aUser['id'] ) || ! $_aUser['id'] ) return array();
			
		// Otherwise, it is okay. Retrieve the current status.
		$_aStatusKeys = apply_filters( 'fetch_tweets_filter_request_rate_limit_status_keys', array( 'statuses', 'search', 'lists' ) );	// keys can be added such as 'help', 'users' etc
		$_aStatus = $_oConnect->get( 'https://api.twitter.com/1.1/application/rate_limit_status.json?resources=' . implode( ',', $_aStatusKeys ) );
		
		// Set the cache.
		$_aData = is_array( $_aStatus ) ? $_aUser + $_aStatus : $_aUser;
		set_transient( $_sCacheID, $_aData, 60 );	// stores the cache only for 60 seconds. 
		
		return $_aData;	
		
	}
	
	/**
	 * Renders the output of status table.
	 */
	static public function renderStatus( $aStatus ) {
		
		self::_printConnectionStatus( $aStatus );
		self::_printRateLimitStatuses( $aStatus );
		
	}
	
		/**
		 * 
		 */
		static private function _printRateLimitStatuses( $aStatus ) {
			
			echo "<h3>" . PHP_EOL
					. __( 'Request Limits', 'fetch-tweets' ) . PHP_EOL
				. "</h3>" . PHP_EOL
				. "<table class='form-table auth-status'>" . PHP_EOL
					. "<tbody>" . PHP_EOL
					. self::_getRateLimitRows( isset( $aStatus['resources'] ) ? $aStatus['resources'] : array() )
					. "</tbody>" . PHP_EOL
				. "</table>" . PHP_EOL;
				
			
		}
			static private function _getRateLimitRows( array $aStatusResources ) {
				
				$_aTranslation = array(
					'statuses'						=>	__( 'Statuses', 'fetch-tweets' ),
					'lists'							=>	__( 'Lists', 'fetch-tweets' ),
					'search'						=>	__( 'Search', 'fetch-tweets' ),
					'/lists/subscribers'			=>	__( 'Subscribers', 'fetch-tweets' ),
					'/lists/list'					=>	__( 'List', 'fetch-tweets' ),
					'/lists/memberships'			=>	__( 'Memberships', 'fetch-tweets' ),
					'/lists/ownerships'				=>	__( 'Ownerships', 'fetch-tweets' ),
					'/lists/subscriptions'			=>	__( 'Subscriptions', 'fetch-tweets' ),
					'/lists/members'				=>	__( 'members', 'fetch-tweets' ),
					'/lists/subscribers/show'		=>	__( 'Subscribers Show', 'fetch-tweets' ),
					'/lists/statuses'				=>	__( 'Statuses', 'fetch-tweets' ),
					'/lists/members/show'			=>	__( 'Members Show', 'fetch-tweets' ),
					'/lists/show'					=>	__( 'Show', 'fetch-tweets' ),					
					'/search/tweets'				=>	__( 'Tweets', 'fetch-tweets' ),	
					'/statuses/mentions_timeline'	=>	__( 'Mentions Timeline', 'fetch-tweets' ),	
					'/statuses/lookup'				=>	__( 'Lookup', 'fetch-tweets' ),	
					'/statuses/show/:id'			=>	__( 'Show ID', 'fetch-tweets' ),	
					'/statuses/oembed'				=>	__( 'Oembed', 'fetch-tweets' ),	
					'/statuses/retweeters/ids'		=>	__( 'Retweeters IDs', 'fetch-tweets' ),	
					'/statuses/home_timeline'		=>	__( 'Home Timeline', 'fetch-tweets' ),	
					'/statuses/user_timeline'		=>	__( 'User Timeline', 'fetch-tweets' ),	
					'/statuses/retweets/:id'		=>	__( 'Retweets ID', 'fetch-tweets' ),	
					'/statuses/retweets_of_me' 		=>	__( 'Retweets Of Me', 'fetch-tweets' ),	
				);
				$_aTranslation = apply_filters( 'fetch_tweets_filter_rate_limit_status_translation', $_aTranslation );
				
				$_aOutput = array();
				foreach( $aStatusResources as $__sResourceKey => $__aStatusResource ) {					
					$_aOutput[] = self::_getRateLimitRow( $__sResourceKey, $__aStatusResource, $_aTranslation );
				}
				return implode( PHP_EOL, $_aOutput );
				
			}
		
			static private function _getRateLimitRow( $sResourceKey, $aStatusResource, $aTranslation ) {
								
				return "<tr valign='top'>" . PHP_EOL
						. "<th scope='row'>"	
							. ( isset( $aTranslation[ $sResourceKey ] ) ? $aTranslation[ $sResourceKey ] : $sResourceKey )
						. "</th>"
						. "<td>"
							. self::_getRateLimitResouceTable( $aStatusResource, $aTranslation )
						. "</td>"
					. "</tr>" . PHP_EOL;
								
			}
			static private function _getRateLimitResouceTable( $aStatusResource, $aTranslation ) {
				
				return "<table class='form-table status-resources'>" . PHP_EOL
						. "<tbody>" . PHP_EOL
							. self::_getRateLimitResouceRows( $aStatusResource, $aTranslation )
						. "</tbody>" . PHP_EOL
					. "</table>" . PHP_EOL;

				return print_r( $aStatusResource, true );
				
			}
			static private function _getRateLimitResouceRows( $aStatusResource, $aTranslation ) {
				
				$_aOutput = array();
				foreach( $aStatusResource as $__sStatusKey => $__aStatus ) {
					$_aOutput[] = self::_getRateLimitResourceRow( $__sStatusKey, $__aStatus, $aTranslation );
				}
				return implode( PHP_EOL, $_aOutput );
				
			}
			static private function _getRateLimitResourceRow( $sStatusKey, $aStatus, $aTranslation ) {
				
				return "<tr valign='top'>" . PHP_EOL
						. "<th scope='row'>"	
							. ( isset( $aTranslation[ $sStatusKey ] ) ? $aTranslation[ $sStatusKey ] : $sStatusKey )
						. "</th>"
						. "<td>"
							. self::_getReadableRateLimitStatus( $aStatus )
						. "</td>"
					. "</tr>" . PHP_EOL;
				
			}
			static private function _getReadableRateLimitStatus( array $aStatus ){
				
				static $_iOffsetSeconds;
				$_iOffsetSeconds = $_iOffsetSeconds ? $_iOffsetSeconds : get_option( 'gmt_offset' ) * HOUR_IN_SECONDS;				
				
				if ( ! isset( $aStatus['remaining'], $aStatus['limit'], $aStatus['reset'] ) ) {
					return __( 'n/a', 'fetch-tweets' );
				}
									
				return $aStatus['remaining'] . ' / ' . $aStatus['limit'] . "&nbsp;&nbsp;&nbsp;"
					. "( " 
						. __( 'Will be reset at', 'fetch-tweets' ) . ' ' . date( "F j, Y, g:i a" , $aStatus['reset'] + $_iOffsetSeconds )
					. " )"
					;
				
			}
			
		/**
		 * Displays the connection status table.
		 * 
		 * @since			2.3
		 */
		static private function _printConnectionStatus( $aStatus ) {
			
			$_fIsConnected = isset( $aStatus['id'] ) && $aStatus['id'];
			$_sScreenName = isset( $aStatus['screen_name'] ) ? $aStatus['screen_name'] : "";
			
			?>			
			<h3><?php _e( 'Status', 'fetch-tweets' ); ?></h3>
			<table class="form-table auth-status">
				<tbody>
					<tr valign="top">
						<th scope="row">
							<?php _e( 'Status', 'fetch-tweets' ); ?>
						</th>
						<td>
							<?php 
								echo $_fIsConnected 
									? '<span class="authenticated">' . __( 'Authenticated', 'fetch-tweets' ) . '</span>'
									: '<span class="unauthenticated">' . __( 'Not authenticated', 'fetch-tweets' ) . '</span>'; 
							?>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<?php _e( 'Screen Name', 'fetch-tweets' ); ?>
						</th>
						<td>
							<?php echo $_sScreenName; ?>
						</td>
					</tr>
				</tbody>
			</table>						
			<?php
		}
	
}