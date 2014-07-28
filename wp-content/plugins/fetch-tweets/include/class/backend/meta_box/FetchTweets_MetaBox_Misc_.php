<?php
abstract class FetchTweets_MetaBox_Misc_ {

	public function __construct() {
		
		add_action( 'add_meta_boxes', array( $this, 'addCustomMetaBoxes' ) );	
		
	}
	
	public function addCustomMetaBoxes() {

		// Sponsors' box.
		add_meta_box( 
			'miunosoft-sponsors', 		// id
			__( 'Information', 'fetch-tweets' ), 	// title
			array( $this, 'callSponsors' ), 	// callback
			FetchTweets_Commons::PostTypeSlug,		// post type
			'side', 	// context ('normal', 'advanced', or 'side'). 
			'low',	// priority ('high', 'core', 'default' or 'low') 
			null // argument
		);	
	}
	
	public function callSponsors() {
		
		$oUserAds = isset( $GLOBALS['oFetchTweetsUserAds'] ) ? $GLOBALS['oFetchTweetsUserAds'] : new FetchTweets_UserAds;
		echo rand ( 0 , 1 )
			? $oUserAds->get250xNTopRight() 
			: $oUserAds->get250xN( 2 );
			
	}	
	
}