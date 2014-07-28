<?php
/**
 * Creates a meta box with forms fields for Twitter home timeline.
 * 
 * @since			2
 * @filter			fetch_tweets_filter_authenticated_accounts			Receives an array of authenticated twitter accounts consisting of the values of screen names.
 */
class FetchTweets_MetaBox_Timeline_ extends FetchTweets_AdminPageFramework_MetaBox {
		
	/**
	 * Adds form fields for the options to fetch tweets by screen name to the meta box.
	 * 
	 * @since			2.0.0
	 */ 
	public function setUp() {
		
		$this->addSettingFields(
			array(
				'field_id'		=> 'tweet_type',
				'type'			=> 'hidden',
				'value'			=> 'home_timeline',
				'hidden'		=>	true,
			),						
			array(
				'field_id'		=> 'account_id',
				'title'			=> __( 'Account', 'fetch-tweets' ),
				'type'			=> 'select',				
				// 'label' => defined in the callback
			),	
			array(
				'field_id'		=> 'item_count',
				'title'			=> __( 'Item Count', 'fetch-tweets' ),
				'description'	=> __( 'Set how many items should be fetched.', 'fetch-tweets' ) . ' ' 
					. __( 'Max', 'fetch-tweets' ) . ': 200 '
					. __( 'Default', 'fetch-tweets' ) . ': 20',
				'type'			=> 'number',
				'default'			=> 20,
				'attributes'	=>	array(
					'max'	=>	200,
				),
			),						
			array(
				'field_id'		=> 'exclude_replies',
				'title'			=> __( 'Exclude Replies', 'fetch-tweets' ),
				'type'			=> 'checkbox',
				'label'			=> __( 'Replies will be excluded.', 'fetch-tweets' ),
				'description'	=> __( 'This prevents replies from appearing in the returned timeline.', 'fetch-tweets' ),
			),
			array(
				'field_id'		=> 'include_rts',
				'title'			=> __( 'Include Retweets', 'fetch-tweets' ),
				'type'			=> 'checkbox',
				'label'			=> __( 'Retweets will be included.', 'fetch-tweets' ),
			),					
			array()
		);	
	
	}
	
	/**
	 * Modify field definition arrays.
	 */ 
	public function field_definition_FetchTweets_MetaBox_Timeline_account_id( $aField ) {
		
		$aField['label'] = apply_filters( 'fetch_tweets_filter_authenticated_accounts', array( $this->_getScreenName() ) );
		return $aField;
		
	}
	
		/**
		 * Performs API request and retrieves the screen name
		 */
		protected function _getScreenName() {
			
			$_oOption = & $GLOBALS['oFetchTweets_Option'];
			$_aCredentials = $_oOption->getCredentialsByID( 0 );
			return isset( $_aCredentials['screen_name'] )
				? $_aCredentials['screen_name'] 
				: '';
								
		}
	
	/*
	 * Validation methods
	 */
	public function validation_FetchTweets_MetaBox_Timeline( $arrInput ) {	// validation_ + extended class name
			
		$arrInput['item_count'] = $this->oUtil->fixNumber( 
			$arrInput['item_count'], 	// number to sanitize
			20, 	// default
			1, 		// minimum
			200
		);
				
		return $arrInput;
		
	}
	
}
