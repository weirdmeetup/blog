<?php
/**
 * 
 * @filter			fetch_tweets_filter_authenticated_accounts - receives an array holding accounts IDs and the screen name connected to Twitter.
 * @filter			fetch_tweets_filter_credentials - receives an array holding accounts credentials and the account ID. 
 */
abstract class FetchTweets_AdminPage_AddRuleByList extends FetchTweets_AdminPage_Setting {
		
	/**
	 * Field definition callbacks
	 */
	public function field_definition_FetchTweets_AdminPage_add_rule_by_list_list_owner_accounts( $aField ) {	// field_definition_{class name}_{section id}_{field id}
	
		$_aCredentials = $this->oOption->getCredentials();
		if ( ! ( $_aCredentials['consumer_key'] && $_aCredentials['consumer_secret'] && $_aCredentials['access_token'] && $_aCredentials['access_secret'] ) ) {
			$aField['before_field'] = '<p class="error">* ' . __( 'The plugin is not connected to Twitter.', 'fetch-tweets' ) . '</p>';
			return $aField;
		}
		
		$_aCredentials['screen_name'] = isset( $_aCredentials['screen_name'] ) && $_aCredentials['screen_name'] 
			? $_aCredentials['screen_name'] 
			: $this->_getScreenName( $_aCredentials );	// for backward compatibility
		
		$_aLabels = array(
				-1 => '--- ' . __( 'Select Account', 'fetch-tweets' ) . ' ---',
			)
			+ apply_filters( 
				'fetch_tweets_filter_authenticated_accounts', 
				array(
					// account id => screen name
					0 => $_aCredentials['screen_name'],
				) 
			);
		
		$aField['label'] = $_aLabels;
		return $aField;
		
	}
		protected function _getScreenName( $aCredentials ) {
			
			/* Create TwitteroAuth object with app key/secret and token key/secret from default phase */
			$_oConnect = new FetchTweets_TwitterAPI_Verification( 
				$aCredentials['consumer_key'], 
				$aCredentials['consumer_secret'], 
				$aCredentials['access_token'], 
				$aCredentials['access_secret']
			);
            
			/* Request access tokens from twitter */
			$_aResponse = $_oConnect->getStatus();
			return isset( $_aResponse['screen_name'] )
				? $_aResponse['screen_name']
				: null;
			
		}
	
	/*
	 * Add Rule by List Page
	 */

	public function validation_fetch_tweets_add_rule_by_list( $aInput, $aOldInput ) {	// validation_{page slug}
				
		// Check if the input has been properly sent.		
		if ( ! isset( $aInput['add_rule_by_list']['list_owner_screen_name'], $aInput['add_rule_by_list']['list_owner_accounts'] ) ) {
			$this->setSettingNotice( __( 'Something went wrong. Your input could not be received. Try again and if this happens again, contact the developer.', 'fetch-tweets' ) );
			return $aOldInput;
		}
		
		$_aCredentials = $this->_getCredentiaslByAccountID( $aInput['add_rule_by_list']['list_owner_accounts'] );
		
		// Variables
		$_aErrors = array();	// error array
		$_iAccountID = $aInput['add_rule_by_list']['list_owner_accounts'] == -1 ? 0 : $aInput['add_rule_by_list']['list_owner_accounts'];						
		$_sOwnerScreenName = $aInput['add_rule_by_list']['list_owner_accounts'] == '-1'
			? $aInput['add_rule_by_list']['list_owner_screen_name']	// the manually typed
			: $_aCredentials['screen_name'];
		
		// The list owner screen name must be provided.
		if ( empty( $_sOwnerScreenName ) ) {
			$_aErrors['add_rule_by_list']['list_owner_screen_name'] = __( 'The screen name of the list owner must be specified: ' ) . $_sOwnerScreenName;
			$this->setFieldErrors( $_aErrors );		
			$this->setSettingNotice( __( 'There was an error in your input.', 'fetch-tweets' ) );
			return $aOldInput;						
		}
		
		// Fetch the lists by the screen name.
		$_oFetch = new FetchTweets_Fetch(
			$_aCredentials['consumer_key'],
			$_aCredentials['consumer_secret'],
			$_aCredentials['access_token'],
			$_aCredentials['access_secret']
		);
		$_aLists = $_oFetch->getListNamesFromScreenName( $_sOwnerScreenName, $_iAccountID );
		if ( empty( $_aLists ) ) {
			$this->setSettingNotice( __( 'No list found.', 'fetch-tweets' ) );
			return $aOldInput;			
		}

		// Set the transient of the fetched IDs. This will be used right next page load.
		$_sListCacheID = uniqid();
		set_transient( $_sListCacheID, $_aLists, 60 );		
		die( 
			wp_redirect( 
				add_query_arg( 	// go to the Manage Accounts page. 
					array( 
						'post_type' => FetchTweets_Commons::PostTypeSlug, 
						'tweet_type' => 'list',
						'list_cache' => $_sListCacheID,
						'screen_name' => $_sOwnerScreenName,
						'account_id' => $_iAccountID,
					), 
					admin_url( 'post-new.php' ) 
				) 			
				// "post-new.php?post_type=fetch_tweets&tweet_type=list&list_cache={$_sListCacheID}&screen_name={$_sOwnerScreenName}" 
			) 
		);
		
	}
		/**
		 * Retrieves the Twitter account credentials by the given account ID.
		 * @since			2
		 */
		private function _getCredentiaslByAccountID( $iAccountID ) {
			
			return $this->oOption->getCredentialsByID( $iAccountID == -1 ? 0 : $iAccountID );
			
		}
							
}