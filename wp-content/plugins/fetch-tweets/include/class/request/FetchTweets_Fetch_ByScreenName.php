<?php
/**
 * Provides methods to fetch tweets by screen name.
 * 
 * @package			Fetch Tweets
 * @subpackage		
 * @copyright		Michael Uno
 * @since			2
 */
abstract class FetchTweets_Fetch_ByScreenName extends FetchTweets_Fetch_ByList {
	
	/**
	 * Fetches tweets by screen names.
	 * 
	 * The plural form of the _getTweetsByScreenName() method. Multiple screen names can be passed separated by commas.
	 * 
	 * @since			1.3.3
	 */
	protected function getTweetsByScreenNames( $strUsers, $intCount, $fIncludeRetweets=false, $fExcludeReplies=false, $intCacheDuration=1200 ) {

		$arrTweets = array();
		$arrScreenNames = FetchTweets_Utilities::convertStringToArray( $strUsers, ',' );
		foreach( $arrScreenNames as $strScreenName ) 			
			$arrTweets = array_merge( $this->_getTweetsByScreenName( $strScreenName, $intCount, $fIncludeRetweets, $fExcludeReplies, $intCacheDuration ), $arrTweets );
			
		return $arrTweets;
		
	}
	
		/**
		 * Fetches tweets by screen name.
		 * 
		 * @see				https://dev.twitter.com/docs/api/1.1/get/statuses/user_timeline
		 * @since			1.0.0
		 */ 
		protected function _getTweetsByScreenName( $strUser, $intCount, $fIncludeRetweets=false, $fExcludeReplies=false, $intCacheDuration=1200 ) {

			// Compose the request URI.
			// $intCount = ( ( int ) $intCount ) > 200 ? 200 : $intCount;
			$intCount = 200;	// as of 1.3.4 the maximum number of tweets are fetched so that the data can be reused for different count requests.
			$strRequestURI = "https://api.twitter.com/1.1/statuses/user_timeline.json"
				. "?screen_name={$strUser}"
				. "&count={$intCount}"
				. "&include_rts=" . ( $fIncludeRetweets ? 1 : 0 )
				. "&exclude_replies=" . ( $fExcludeReplies ? 1 : 0 );
			
			return $this->doAPIRequest_Get( $strRequestURI, null, $intCacheDuration );
					
		}
	
	
}