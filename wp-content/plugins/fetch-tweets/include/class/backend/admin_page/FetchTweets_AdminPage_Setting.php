<?php
/**
 * 
 * @action	fetch_tweets_action_updated_credentials - triggered when the main credentials are updated.
 */
abstract class FetchTweets_AdminPage_Setting extends FetchTweets_AdminPage_Connect {
				
	// The connect page
	public function do_form_fetch_tweets_settings_twitter_connect() {	// do_form_ + page slug + _ + tab slug
		
		$this->_renderAuthenticationStatus( $this->_getVerificationStatus() );
				
	}

	
	public function do_form_fetch_tweets_settings_authentication() {	// do_form_ + page slug + _ + tab slug
		
		$this->_renderAuthenticationStatus( $this->_getVerificationStatus() );
		
	}
		
	/**
	 * Filters the output of the Connect To Twitter button.
	 * 
	 * If it's not authenticated yet, the label becomes "Connect"; otherwise, "Disconnect"
	 */
	public function field_FetchTweets_AdminPage_twitter_connect_connect_to_twitter( $sField ) {		// field_{instantiated class name}_{section id}_{field id}
		
		return ( ! $this->oOption->isConnected() )
			? $sField		// the connect button
			: '<span style="display: inline-block; min-width:120px;">'
					. '<input id="twitter_connect_connect_to_twitter__0" class="button button-primary" type="submit" name="disconnect_from_twitter" value="' . __( 'Disconnect', 'fetch-tweets' ) . '">&nbsp;&nbsp;'
				.'</span>'; // the disconnect button
				
	}
	
	public function validation_FetchTweets_AdminPage( $aInput, $aOriginal ) {
		
		// If the Disconnect button is pressed, delete the authentication keys.
		if ( isset( $_POST['disconnect_from_twitter'] ) ) {

			$aInput = is_array( $aInput ) ? $aInput : array();	// in WP v3.4.2, when the Disconnect button is pressed an $aInput was passed as an empty string. Something went wrong.
			
			// the transient needs to be removed 
			delete_transient( FetchTweets_Commons::TransientPrefix . '_' . md5( serialize( array( $this->oOption->getConsumerKey(), $this->oOption->getConsumerSecret(), $this->oOption->getAccessToken(), $this->oOption->getAccessTokenSecret() ) ) ) );
			delete_transient( FetchTweets_Commons::TransientPrefix . '_' . md5( serialize( array( FetchTweets_Commons::ConsumerKey, FetchTweets_Commons::ConsumerSecret, $this->oOption->getAccessTokenAuto(), $this->oOption->getAccessTokenSecretAuto() ) ) ) );
			
			$aInput['authentication_keys'] = array();
			$aInput['twitter_connect'] = array();
			do_action( 'fetch_tweets_action_updated_credentials', array() );
			
		}

		return $aInput;
		
	}
	
	/**
	 * Triggered when the manual keys are set and submitted.
	 * 
	 * @since			2
	 */
	public function validation_FetchTweets_AdminPage_authentication_keys( $aInput, $aOldInput ) {	// valiudation_{class name}_{section id}

		// Check the connection
		$_oConnect = new FetchTweets_TwitterAPI_Verification( 
			$aInput['consumer_key'],
			$aInput['consumer_secret'],
			$aInput['access_token'],
			$aInput['access_secret']
		);
		$_aStatus = $_oConnect->getStatus();	
		
		// If it's connected, add the connection status
		if ( isset( $_aStatus['id_str'] ) ) {
			
			$aInput['user_id'] = $_aStatus['id_str'];
			$aInput['screen_name'] = $_aStatus['screen_name'];
			$aInput['is_connected'] = true;
			$aInput['connect_method'] = 'manual';
			
		} else {
			$aInput['is_connected'] = false;
			$aInput['connect_method'] = 'manual';
		}
		
		do_action( 'fetch_tweets_action_updated_credentials', $aInput );
		return $aInput;
		
	}
	

	
	/*
	 * Settings Page
	 */
	public function do_before_fetch_tweets_settings() {	// do_before_ + page slug
		$this->setPageTitleVisibility( false );
	}
			
	public function validation_fetch_tweets_settings_general( $arrInput, $arrOriginal ) {
		
		$arrInput['default_values']['count'] = $this->oUtil->fixNumber(
			$arrInput['default_values']['count'],
			$GLOBALS['oFetchTweets_Option']->aStructure_DefaultParams['count'],
			1
		);
		
		return $arrInput;
		
	}
	public function validation_fetch_tweets_settings_reset( $arrInput, $arrOriginal ) {
				
		// Variables
		$fChanged = false;
				
		// Make it one dimensional.
		$arrSubmit = array();
		foreach ( $arrInput as $strSection => $arrFields ) 
			$arrSubmit = $arrSubmit + $arrFields;				
			
		// If the Perform button is not set, return.
		if ( ! isset( $arrSubmit['submit_reset_settings'] ) ) {
			$this->setSettingNotice( __( 'Nothing changed.', 'fetch-tweets' ) );	
			return $arrOriginal;
		}

		if ( isset( $arrSubmit['clear_caches'] ) && $arrSubmit['clear_caches'] ) {
			FetchTweets_Transient::clearTransients();
			$fChanged = true;
			$this->setSettingNotice( __( 'The caches have been cleared.', 'fetch-tweets' ) );
		}
		
		// $this->oDebug->getArray( $arrSubmit, dirname( __FILE__ ) . '/submit.txt' );
		// $this->oDebug->getArray( $GLOBALS['oFetchTweets_Option']->aOptions, dirname( __FILE__ ) . '/options.txt' );
		
		if ( isset( $arrSubmit['option_sections'] ) ) {
			if ( isset( $arrSubmit['option_sections']['all'] ) && $arrSubmit['option_sections']['all'] ) {
				$fChanged = true;
				add_action( 'shutdown', array( $this, 'deleteOptions_All' ), 999 );
			}
			if ( isset( $arrSubmit['option_sections']['genaral'] ) && $arrSubmit['option_sections']['general'] ) {
				$fChanged = true;
				add_action( 'shutdown', array( $this, 'deleteOptions_General' ), 999 );
			}
			if ( isset( $arrSubmit['option_sections']['template'] ) && $arrSubmit['option_sections']['template'] ) {
				$fChanged = true;
				add_action( 'shutdown', array( $this, 'deleteOptions_Template' ), 999 );
			}		
		}
		
		if ( ! $fChanged ) {
			$this->setSettingNotice( __( 'Nothing changed.', 'fetch-tweets' ) );	
		}
		return $arrOriginal;	// no need to update the options.
		
	}
	public function deleteOptions_All() {
		delete_option( FetchTweets_Commons::AdminOptionKey );
	}
	public function deleteOptions_General() {
		// Currently not working: Somehow the options get recovered.
		unset( $GLOBALS['oFetchTweets_Option']->aOptions );
		$GLOBALS['oFetchTweets_Option']->saveOptions();		
	}
	public function deleteOptions_Template() {		
		// Currently not working: Somehow the options get recovered.

		unset( $GLOBALS['oFetchTweets_Option']->aOptions['arrTemplates'] );
		unset( $GLOBALS['oFetchTweets_Option']->aOptions['arrDefaultTemplate'] );		
		$GLOBALS['oFetchTweets_Option']->saveOptions();

	}
					
}