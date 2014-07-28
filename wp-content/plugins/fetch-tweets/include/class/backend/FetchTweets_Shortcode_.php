<?php
abstract class FetchTweets_Shortcode_ {

	public function __construct( $strShortCode ) {
						
		// Add the shortcode.
		add_shortcode( $strShortCode, array( $this, 'getOutput' ) );
		
	}
	
	public function getOutput( $arrArgs ) {
			
		$this->oFetch = isset( $this->oFetch ) ? $this->oFetch : new FetchTweets_Fetch();
		
		if ( isset( $arrArgs['id'] ) || isset( $arrArgs['ids'] ) ) 
			return $this->oFetch->getTweetsOutput( $arrArgs );
		else if ( isset( $arrArgs['tag'] ) || isset( $arrArgs['tags'] ) ) 
			return $this->oFetch->getTweetsOutputByTag( $arrArgs );
			
		if ( isset( $arrArgs['q'] ) || isset( $arrArgs['screen_name'] ) ) 
			return $this->oFetch->getTweetsOutput( $arrArgs );
				
	}	

}