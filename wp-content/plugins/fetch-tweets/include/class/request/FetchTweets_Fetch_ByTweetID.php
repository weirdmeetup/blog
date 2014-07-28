<?php
/**
 * Provides methods to get responses with tweet IDs.
 * 
 * @package			Fetch Tweets
 * @subpackage		
 * @copyright		Michael Uno
 * @since			2.3
 */
abstract class FetchTweets_Fetch_ByTweetID extends FetchTweets_Fetch_ByCustomRequest {
	
	/**
	 * Retrieves tweets by the given tweet IDs.
	 * 
	 * @see				https://dev.twitter.com/docs/api/1.1/get/statuses/show/%3Aid
	 * @since			2.3
	 */
	protected function _getResponseByTweetID( $sTweetIDs, $iCacheDuration ) {
		
		$_aTweetIDs = preg_split( "/[,]\s*/", trim( ( string ) $sTweetIDs ), 0, PREG_SPLIT_NO_EMPTY );
		
		$_aResponse = array();
		foreach( $_aTweetIDs as $__sTweetID ) {
		
			$_aQueryArgs = array(
				'id'				=>	$__sTweetID,
			);
			$_sRequestURI = add_query_arg( $_aQueryArgs, "https://api.twitter.com/1.1/statuses/show.json" );			
			$_aResponse[] = $this->doAPIRequest_Get( $_sRequestURI, null, $iCacheDuration );
			
		}
		return $_aResponse;
		
		
	}
	
	
}