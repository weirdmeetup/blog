<?php
class FetchTweets_MetaBox_CustomQuery_ extends FetchTweets_AdminPageFramework_MetaBox {
	

	/**
	 * Adds form fields for the options to fetch tweets by feed.
	 * 
	 * @since			2.2.0
	 */ 
	public function setup() {
		
		$this->addSettingFields(		
			array(
				'field_id'		=> 'tweet_type',
				'type'			=> 'hidden',
				'value'			=> 'custom_query',
				'hidden'		=>	true,
			),						
			array(
				'field_id'		=> 'custom_query',
				'title'			=> __( 'API Request', 'fetch-tweets' ),
				'description'	=> __( 'The API request URI to send to Twitter.com.', 'fetch-tweets' ) . '<br />'
					. 'e.g. ' .  '<code>https://api.twitter.com/1.1/lists/members.json?slug=wordpress-community&owner_screen_name=miunosoft&cursor=-1</code>',
				'type'			=> 'text',
			),
			array(
				'field_id'		=> 'response_key',
				'title'			=> __( 'Response Key', 'fetch-tweets' ),
				'description'	=> __( 'The response element key to extract. For instance, the Search API returns tweets in an element of the key, <code>status</code>.', 'fetch-tweets' ),	// . '<br />'
					// . 'e.g. ' .  '<code>status</code>',
				'type'			=> 'text',
				'attributes'	=>	array(
					'size'			=> 10,
				),
			),			
			array()
		);
		
	}	

	public function validation_FetchTweets_MetaBox_CustomQuery( $aSubmitData, $aOldSubmitData ) {	// validation_ + extended class name
			
		return $aSubmitData;
		
	}	
	
}
