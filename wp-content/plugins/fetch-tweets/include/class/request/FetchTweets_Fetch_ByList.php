<?php
/**
 * Provides methods to fetch tweets by list.
 * 
 * @package			Fetch Tweets
 * @subpackage		
 * @copyright		Michael Uno
 * @since			2
 */
abstract class FetchTweets_Fetch_ByList extends FetchTweets_Fetch_BySearch {
	
	/**
	 * Returns an array holding the list IDs and names from the given owner screen name.
	 * 
	 * @remark			This is used for the form field select option.
	 * @since			1.2.0
	 */
	public function getListNamesFromScreenName( $sScreenName, $iAccountID=0 ) {
		
		$aListDetails = $this->getListsByScreenName( $sScreenName, $iAccountID );
		$aListIDs = array();
		foreach( $aListDetails as $aListDetail ) 
			$aListIDs[ $aListDetail['id'] ] = $aListDetail['name'];
		return $aListIDs;
		
	}
	
	/**
	 * Fetches lists and their details owned by the specified user.
	 * 
	 * @see				https://dev.twitter.com/docs/api/1.1/get/lists/list
	 * @remark			Insert credentials in to the request URI to identify the cache per authenticated account since lists can contain private.
	 * @since			1.2.0
	 * @since			2.0.0			Changed the scope to public as the meta box option class uses it.
	 */ 
	public function getListsByScreenName( $sScreenName, $iAccountID=0, $iCacheDuration=600 ) {
		
		$_aAccessKeys = $this->oOption->getCredentialsByID( $iAccountID );
		
		// Compose the request URI - e.g. https://api.twitter.com/1.1/lists/list.json?screen_name=twitterapi
		$_sRequestURI = "https://api.twitter.com/1.1/lists/list.json?"
			. "screen_name={$sScreenName}"
			. "&consumer_key=" . $_aAccessKeys['consumer_key']			//	this is not an API parameter but for the plugin transient ID
			. "&consumer_secret=" . $_aAccessKeys['consumer_secret']	//	this is not an API parameter but for the plugin transient ID
			. "&access_token=" . $_aAccessKeys['access_token']			//	this is not an API parameter but for the plugin transient ID
			. "&access_secret=" . $_aAccessKeys['access_secret']		// 	this is not an API parameter but for the plugin transient ID
		;
			
		return $this->doAPIRequest_Get( $_sRequestURI, null, $iCacheDuration );
		
	}
	
	/**
	 * Fetches tweets by list ID.
	 * 
	 * @see				https://dev.twitter.com/docs/api/1.1/get/lists/statuses
	 * @since			1.2.0
	 * @since			1.3.4			The 'count' parameter is fixed to the max number so that the data can be reused for different count requests.
	 * @since			2				Dropped the $iCount parameter.
	 * 
	 * @param			string			$sListID
	 * @param			boolean			$fIncludeRetweets
	 * @param			integer			$iCacheDuration			
	 * @param			integer			$iAccountID				The credentials ID stored in the plugin. 0 is the main one.
	 * @param			string			$sMode					Either 'public' or 'private'.
	 */ 
	protected function _getTweetsByListID( $sListID, $fIncludeRetweets=false, $iCacheDuration=600, $iAccountID=0, $sMode='public' ) {
					
		// e.g. https://api.twitter.com/1.1/lists/statuses.json?slug=teams&owner_screen_name=MLS&count=1 
		$_aQueryArgs = array(
			'list_id'	=>	$sListID,
			'count'		=>	200,	// 200 is the max
			'include_rts'	=>	( $fIncludeRetweets ? 1 : 0 ),
			// . "&slug={$sListSlug}"
			// . "&owner_screen_name={$sOwnerScreenName}"
			// . "&owner_id={$sOwnerID}"
			// . "&since_id={$sSinceID}"
			// . "&max_id={$sMaxID}"			
		);
		if ( $sMode == 'private' ) {
			$_aAccessKeys = $this->oOption->getCredentialsByID( $iAccountID );	
			// These keys will be removed when performing the actual request as they are not part of Twitter API specifications but for generating unique cache ID. 
			$_aQueryArgs['consumer_key'] = $_aAccessKeys['consumer_key'];
			$_aQueryArgs['consumer_secret'] = $_aAccessKeys['consumer_secret'];
			$_aQueryArgs['access_token'] = $_aAccessKeys['access_token'];
			$_aQueryArgs['access_secret'] = $_aAccessKeys['access_secret'];
		}
		$_sRequestURI = add_query_arg( $_aQueryArgs, "https://api.twitter.com/1.1/lists/statuses.json" );		
		return $this->doAPIRequest_Get( $_sRequestURI, null, $iCacheDuration );
	
	}
	
	
}