<?php
abstract class FetchTweets_AdminPage_SetUp extends FetchTweets_AdminPage_SetUp_Form {

    public function setUp() {
    	
		// Show the warning message if the authentication key is not set.
		$this->_checkAPIKeys(); 
	
		if ( isset( $this->oProp->aOptions['capabilities']['setting_page_capability'] ) 
			&& ! empty( $this->oProp->aOptions['capabilities']['setting_page_capability'] )
		) {
			$this->setCapability( $this->oProp->aOptions['capabilities']['setting_page_capability'] );
		}
	
		$this->_setUpPages();
		$this->_setUpStyles();
		$this->_setUpForm();

		$this->addLinkToPluginDescription(  
			'<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=J4UJHETVAZX34">' . __( 'Donate', 'fetch-tweets' ) . '</a>',
			'<a href="http://en.michaeluno.jp/contact/custom-order/?lang=' . ( WPLANG ? WPLANG : 'en' ) . '">' . __( 'Order custom plugin', 'fetch-tweets' ) . '</a>'
		);						

	}
		protected function _checkAPIKeys() {
			
			if ( $this->oOption->isAuthKeysManuallySet() || $this->oOption->isAuthKeysAutomaticallySet() ) return;

			add_action( 'admin_notices', array( $this, '_replyToShowAdminNotice' ) );
			
		}
		public function _replyToShowAdminNotice() {
				
			if ( ! (
				( isset( $_GET['page'] ) && $this->oProp->isPageAdded( $_GET['page'] ) ) 
				|| ( isset( $_GET['post_type'] ) && $_GET['post_type'] == FetchTweets_Commons::PostTypeSlug )
			) ) return; 
			
			// http://.../wp-admin/edit.php?post_type=fetch_tweets&page=fetch_tweets_settings
			$strSettingPageURL = add_query_arg( array( 'post_type' => 'fetch_tweets', 'page' => 'fetch_tweets_settings', 'tab' => 'twitter_redirect' ), admin_url( 'edit.php' ) ); 
			echo "<div class='error'>"
					. "<p>" 
						. "<strong>" . FetchTweets_Commons::PluginName . "</strong>: "
						. sprintf( __( '<a href="%1$s">The API authentication keys need to be set</a> in order to use this plugin.', 'fetch-tweets' ), $strSettingPageURL ) 
					. "</p>"
				. "</div>";		
				
		}
			
}