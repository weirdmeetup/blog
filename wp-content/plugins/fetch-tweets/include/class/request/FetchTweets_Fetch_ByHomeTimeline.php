<?php
/**
 * Provides methods to fetch tweets by home timeline.
 * 
 * @package			Fetch Tweets
 * @subpackage		
 * @copyright		Michael Uno
 * @since			2
 */
abstract class FetchTweets_Fetch_ByHomeTimeline extends FetchTweets_Fetch_ByScreenName {
	
	/**
	 * Retrieves tweets of the given account.
	 * 
	 * @since			2
	 * @see				https://dev.twitter.com/docs/api/1.1/get/statuses/home_timeline
	 * @param			integer			$iAccountID			The account ID. If 0, it means the main account.
	 * @param			boolean			$fExcludeReplies	Indicates whether replies should be excluded or not.
	 * @param			boolean			$fInludeRetweets	[2.2+] indicates whether retweets should be included or not.
	 */
	protected function _getTweetsByHomeTimeline( $iAccountID, $fExcludeReplies, $fInludeRetweets, $iCacheDuration=600 ) {
				
		$_aAccessKeys = $this->oOption->getCredentialsByID( $iAccountID );
		$_aQueryArgs = array(
			'count'				=>	200,	// 200 is the max
			'include_entities'	=>	1,
			'include_rts'		=> 	$fInludeRetweets ? 1 : 0,	// this is not documented but it seems to work
			'exclude_replies'	=>	$fExcludeReplies ? 1 : 0,
			// the following keys are for the plugin internal use, not part of Twitter API request.
			'consumer_key'		=>	$_aAccessKeys['consumer_key'],
			'consumer_secret'	=>	$_aAccessKeys['consumer_secret'],
			'access_token'		=>	$_aAccessKeys['access_token'],
			'access_secret'		=>	$_aAccessKeys['access_secret'],
		);
		$_sRequestURI = add_query_arg( $_aQueryArgs, "https://api.twitter.com/1.1/statuses/home_timeline.json" );
		return $this->doAPIRequest_Get( $_sRequestURI, null, $iCacheDuration );					
		
	}
	
	
	
}