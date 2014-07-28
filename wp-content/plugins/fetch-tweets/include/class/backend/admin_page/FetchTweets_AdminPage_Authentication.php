<?php
abstract class FetchTweets_AdminPage_Authentication extends FetchTweets_AdminPage_Menu {
		
	/**
	 * Retrieves the verification status with the saved access keys.
	 * 
	 * This method first checks with the manually set authentication keys and if it fails, it checks with the automatically set authentication keys.
	 * 
	 * @since			1.3.0
	 * @return			array			The array which contains the verification status.
	 */
	protected function _getVerificationStatus() {

		// If it is disconnected, return an empty array.
		if ( ! $this->oOption->isConnected() ) {			
			return array();
		}
		
		// If the access token and access secret keys have been manually set,
		$_aStatus = $this->oOption->isAuthKeysManuallySet()
			? $this->_getAuthenticationStatus( $this->oOption->getConsumerKey(), $this->oOption->getConsumerSecret(), $this->oOption->getAccessToken(), $this->oOption->getAccessTokenSecret() )
			: array();
			
		if ( ! empty( $_aStatus ) ) return $_aStatus;
			
		// If the access token and secret keys have been automatically set,
		if ( $this->oOption->isAuthKeysAutomaticallySet() ) {
			$_aStatus = $this->_getAuthenticationStatus( FetchTweets_Commons::ConsumerKey, FetchTweets_Commons::ConsumerSecret, $this->oOption->getAccessTokenAuto(), $this->oOption->getAccessTokenSecretAuto() );
		}
	
		return $_aStatus;
	
	}
	
		/**
		 * Checks the API credential is valid or not.
		 * 	 
		 * @since			1.3.0
		 * @return			array			the retrieved data.
		 * @remark			The returned data is a merged result of 'account/verify_credientials' and 'rate_limit_status'.
		 */
		protected function _getAuthenticationStatus( $sConsumerKey, $sConsumerSecret, $sAccessToken, $sAccessSecret ) {
			
			$oTwitterOAuth_Verification = new FetchTweets_TwitterAPI_Verification( $sConsumerKey, $sConsumerSecret, $sAccessToken, $sAccessSecret );
			return $oTwitterOAuth_Verification->getStatus();
			
		}

		
	/**
	 * Renders the authentication status table.
	 * 
	 * @since			1.3.0
	 * @remark			$aStatus can be null when a cache for the API request is not stored.
	 * @param			array			$aStatus			This arrays should be the merged array of the results of 'account/verify_credientials' and 'rate_limit_status' requests.
	 * 
	 */
	protected function _renderAuthenticationStatus( $aStatus ) {
		
		FetchTweets_TwitterAPI_Verification::renderStatus( $aStatus );
		
	}
		
}