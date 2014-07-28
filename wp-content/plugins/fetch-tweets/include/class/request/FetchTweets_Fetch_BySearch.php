<?php
/**
 * Provides methods to fetch tweets by search.
 * 
 * @package			Fetch Tweets
 * @subpackage		
 * @copyright		Michael Uno
 * @since			2
 */
abstract class FetchTweets_Fetch_BySearch extends FetchTweets_Fetch_ByTag {
	
	/**
	 * Fetches tweets by search keyword.
	 * 
	 * @see			https://dev.twitter.com/docs/api/1.1/get/search/tweets
	 * @ramark		This request type does not support the 'include_rts' option. The Twitter API does not support it.
	 */ 
	protected function getTweetsBySearch( $strKeyword, $intCount=100, $strLang='en', $strResultType='mixed', $strUntil='', $strGeoCode='', $intCacheDuration=600 ) {

		// Compose the request URI.
		$intCount = 100;	// as of v1.3.4, request will be performed with the maximum count so that the caches will be reused for ones with lesser counts.
		$strRequestURI = "https://api.twitter.com/1.1/search/tweets.json"
			. "?q=" . urlencode_deep( $strKeyword )	// . "?q=" . urlencode_deep( 'from:personA+OR+from:personB+OR+from:personC+OR+from:personC' )  
			. "&result_type={$strResultType}"	//  mixed, recent, popular
			. "&count={$intCount}"
			. ( $strLang == 'none' ? "" : "&lang={$strLang}" )
			. ( empty( $strUntil ) ? "" : "&until={$strUntil}" )
			. ( empty( $strGeoCode ) ? "" : "&geocode={$strGeoCode}" )
			. "&include_entities=1";		
		return $this->doAPIRequest_Get( $strRequestURI, 'statuses', $intCacheDuration );
					
	}
	
	
}