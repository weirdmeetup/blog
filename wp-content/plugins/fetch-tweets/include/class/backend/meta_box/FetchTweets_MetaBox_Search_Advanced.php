<?php
class FetchTweets_MetaBox_Search_Advanced extends FetchTweets_AdminPageFramework_MetaBox {
	
	/**
	 * Adds form fields for the options to fetch tweets by keyword search to the meta box.
	 * 
	 */ 
	public function setup() {
		
		$this->addSettingFields(
			array(
				'field_id'		=> 'include_rts',
				'title'			=> __( 'Include Retweets', 'fetch-tweets' ),
				'type'			=> 'checkbox',
				'label'			=> __( 'Retweets will be included.', 'fetch-tweets' ),
			),		
			array()
		);
		
	}	

	public function validation_FetchTweets_MetaBox_Search_Advanced( $aInput, $aOldInput ) {	// validation_ + extended class name
			
		return $aInput;
		
	}	
	
}
