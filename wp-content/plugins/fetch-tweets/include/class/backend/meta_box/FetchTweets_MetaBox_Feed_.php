<?php
class FetchTweets_MetaBox_Feed_ extends FetchTweets_AdminPageFramework_MetaBox {
	

	/**
	 * Adds form fields for the options to fetch tweets by feed.
	 * 
	 * @since			2.1
	 */ 
	public function setup() {
		
		$this->addSettingFields(		
			array(
				'field_id'		=> 'tweet_type',
				'type'			=> 'hidden',
				'value'			=> 'feed',
				'hidden'		=>	true,
			),						
			array(
				'field_id'		=> 'json_url',
				'title'			=> __( 'JSON URL', 'fetch-tweets' ),
				'description'	=> __( 'The URL of the JSON feed.', 'fetch-tweets' ) 
					. ' ' . sprintf( __( 'In order to get JSON feeds, the <a href="%1$s" target="_blank">Feeder</a> extension is required.', 'fetch-tweets' ), 'http://en.michaeluno.jp/fetch-tweets/extensions/feeder/' ),
				'type'			=> 'text',
			),	
			array()
		);
		
	}	

	public function validation_FetchTweets_MetaBox_Feed( $aSubmitData, $aOldSubmitData ) {	// validation_ + extended class name
			
		return $aSubmitData;
		
	}	
	
}
