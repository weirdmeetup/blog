<?php
class FetchTweets_MetaBox_TweetID extends FetchTweets_AdminPageFramework_MetaBox {
	
	public function start_FetchTweets_MetaBox_TweetID() {
	}
				
	/**
	 * Adds form fields for the options to fetch tweets by keyword search to the meta box.
	 * 
	 * @since			2.3.0
	 */ 
	public function setup() {
		
		$this->addSettingFields(		
			array(
				'field_id'		=> 'tweet_type',
				'type'			=> 'hidden',
				'value'			=> 'tweet_id',
				'hidden'		=>	true,
			),				
			array(
				'field_id'		=> 'tweet_id',
				'title'			=> __( 'Target Tweet IDs', 'fetch-tweets' ),
				'description'	=> __( 'Enter here the target Tweet IDs separated by commas', 'fetch-tweets' )
					. ' e.g. <code>210462857140252672, 456502643738030080</code>',
				'type'			=> 'text',
			),
			array()
		);
		
	}	

	public function validation_FetchTweets_MetaBox_TweetID( $aInput, $aOldInput ) {	// validation_ + extended class name
				
		$aInput['tweet_id'] = FetchTweets_Utilities::sanitizeCommaDelimitedString( $aInput['tweet_id'] );
		
		return $aInput;
		
	}	
	
}
