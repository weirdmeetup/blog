<?php
/**
 * Provides methods to get response with a custom request.
 * 
 * @package			Fetch Tweets
 * @subpackage		
 * @copyright		Michael Uno
 * @since			2.2
 */
abstract class FetchTweets_Fetch_ByCustomRequest extends FetchTweets_Fetch_ByFeed {
	
	/**
	 * Retrieves tweets of the given feed.
	 * 
	 * @since			2.1
	 */
	protected function _getResponseWithCustomRequest( $sRequestURI, $sResponseKey, $iCacheDuration ) {
		
		// Sanitize and validate the url.
		$sRequestURI = trim( $sRequestURI );
		if ( ! filter_var( $sRequestURI, FILTER_VALIDATE_URL ) ) {
			return array();			
		}
		
		$_aResponse = $this->doAPIRequest_Get( $sRequestURI, $sResponseKey, $iCacheDuration );
		return $_aResponse;
		
		
	}
	
	
}