<?php
class FetchTweets_MetaBox_List_ extends FetchTweets_AdminPageFramework_MetaBox {
	
	/**
	 * Adds form fields for the options to fetch tweets by list to the meta box.
	 * 
	 * @since			1.2.0
	 */ 
	public function setUp() {
				
		$this->addSettingFields(		
			array(
				'field_id'		=> 'tweet_type',
				'type'			=> 'hidden',
				'value'			=> 'list',
				'hidden'		=>	true,
			),			
			array(
				'field_id'		=> 'list_id',
				'title'			=> __( 'Lists', 'fetch-tweets' ),
				'type'			=> 'select',
			),
			array(	// non-used fields must be set as hidden since the callback function will assign a value.
				'field_id'		=> 'screen_name',
				'type'			=> 'hidden',
				'hidden'		=>	true,
			),				
			array(
				'field_id'		=> 'account_id',
				'type'			=> 'hidden',
				'hidden'		=>	true,
			),
			array(	// stores private or public in the validation method.
				'field_id'		=> 'mode',	
				'type'			=> 'hidden',
				'hidden'		=>	true,
			),			
			array(
				'field_id'		=> 'item_count',
				'title'			=> __( 'Item Count', 'fetch-tweets' ),
				'description'	=> __( 'Set how many items should be fetched.', 'fetch-tweets' ) . ' ' 
					. __( 'Max', 'fetch-tweets' ) . ': 100 '
					. __( 'Default', 'fetch-tweets' ) . ': 20',
				'type'			=> 'number',
				'default'			=> 20,
				'attributes'	=>	array(
					'max'	=>	100,
				),				
			),				
			array(
				'field_id'		=> 'include_rts',
				'title'			=> __( 'Include Retweets', 'fetch-tweets' ),
				'label'			=> __( 'Retweets will be included.', 'fetch-tweets' ),
				'type'			=> 'checkbox',
			),							
			array()
		);
				
		
	}
	
	/**
	 * Modify the field definition arrays.
	 */
	public function field_definition_FetchTweets_MetaBox_List_screen_name( $aField ) {	// field_definition_{class name}_{field id}
		$aField['value'] = $this->getScreenName();
		return $aField;
	}
	
	public function field_definition_FetchTweets_MetaBox_List_list_id( $aField ) {	// field_definition_{class name}_{field id}
		
		$_sScreenName = $this->getScreenName();
		$_aLists = $this->_getLists( $_sScreenName, $this->_getAccountID() );
		$aField['label'] = $_aLists;
		return $aField;
		
	}
	public function field_definition_FetchTweets_MetaBox_List_account_id( $aField ) {	// field_definition_{class name}_{field id}
		$aField['value'] = $this->_getAccountID();
		return $aField;
	}
		/**
		 * Retrieves the authenticated account ID of this post(list definition).
		 * 
		 * @since			2
		 */
		protected function _getAccountID() {
			
			if ( isset( $_GET['account_id'] ) ) {
				return $_GET['account_id'];
			}
			if ( ! isset( $_GET['post'] ) ) {
				return 0;
			}
			$_iAccountID = get_post_meta( $_GET['post'], 'account_id', true );
			return $_iAccountID
				? $_iAccountID
				: 0;
							
		}
		
		/**
		 * Returns an array of lists received from the previous page; otherwise, fetches lists from the set screen name.
		 * 
		 */	 
		protected function _getLists( $sScreenName='', $iAccountID=0 ) {
			
			// If the cache is set from the previous page, use that.
			$sListTransient = isset( $_GET['list_cache'] ) ? $_GET['list_cache'] : '';
			if ( ! empty( $sListTransient ) ) {
				$aLists = ( array ) get_transient( $sListTransient );
				delete_transient( $sListTransient );
				return $aLists;
			}
			
			if ( empty( $sScreenName ) ) return array();	
			
			// Fetch lists from the given screen name.
			$_oOption = & $GLOBALS['oFetchTweets_Option'];
			$_aCredentials = $_oOption->getCredentialsByID( $iAccountID );
			$oFetch = new FetchTweets_Fetch(
				$_aCredentials['consumer_key'],
				$_aCredentials['consumer_secret'],
				$_aCredentials['access_token'],
				$_aCredentials['access_secret']
			);
			$aLists = $oFetch->getListNamesFromScreenName( $sScreenName, $iAccountID );
			return $aLists;
			
		}
		/**
		 * Returns the associated screen name (twitter user name) of the post.
		 * 
		 * @return			string				The screen name associated with the post.
		 * @since			1.2.0
		 */
		protected function getScreenName() {
			
			// If the 'action' query value is edit, search for the meta field value which previously set when it is saved.
			if ( isset( $_GET['action'], $_GET['post'] ) && $_GET['action'] == 'edit' ) 
				return get_post_meta( $_GET['post'], 'screen_name', true );
		
			// If the GET 'tweet_type' query value is set, use it.
			if ( isset( $_GET['screen_name'] ) && $_GET['screen_name'] ) return $_GET['screen_name'];
			
			return '';
			
		}
		
	/*
	 * Validation Methods
	 */
	public function validation_FetchTweets_MetaBox_List( $aInput ) {	// validation_ + extended class name
			
		$aInput['item_count'] = $this->oUtil->fixNumber( 
			$aInput['item_count'], 	// number to sanitize
			20, 	// default
			1, 		// minimum
			200
		);
		
		$_oOption = & $GLOBALS['oFetchTweets_Option'];
		$_aCredentials = $_oOption->getCredentialsByID( $aInput['account_id'] );
		$_oFetch = new FetchTweets_Fetch(
			$_aCredentials['consumer_key'],
			$_aCredentials['consumer_secret'],
			$_aCredentials['access_token'],
			$_aCredentials['access_secret']
		);
		$_aLists = $_oFetch->getListsByScreenName( $aInput['screen_name'], $aInput['account_id'] );
		
		foreach( $_aLists as $_aList ) {
			if ( $_aList['id'] == $aInput['list_id'] ) {
				$aInput['mode'] = $_aList['mode'];
			}
		}
	
		return $aInput;
		
	}
	
}
