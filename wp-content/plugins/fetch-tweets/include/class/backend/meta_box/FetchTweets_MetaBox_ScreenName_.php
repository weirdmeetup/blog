<?php
class FetchTweets_MetaBox_ScreenName_ extends FetchTweets_AdminPageFramework_MetaBox {
		
	/**
	 * Adds form fields for the options to fetch tweets by screen name to the meta box.
	 * 
	 * @since			1.0.0
	 */ 
	public function setUp() {
		$this->addSettingFields(
			array(
				'field_id'		=> 'tweet_type',
				'type'			=> 'hidden',
				'value'			=> 'screen_name',
				'hidden'		=>	true,
			),						
			array(
				'field_id'		=> 'screen_name',
				'title'			=> __( 'User Name', 'fetch-tweets' ),
				'description'	=> __( 'The user name (screen name) that is used after the @ mark or the end of the twitter url.', 'fetch-tweets' ) . '',
				'type'			=> 'text',
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
			// array(
				// 'field_id'		=> 'search_keyword',
				// 'type'			=> 'hidden',
				// 'hidden'		=>	true,
			// ),		
			// array(
				// 'field_id'		=> 'language',
				// 'type'			=> 'hidden',
				// 'hidden'		=>	true,
			// ),				
			// array (
				// 'field_id'		=> 'result_type',
				// 'type'			=> 'hidden',
				// 'hidden'		=>	true,
			// ),			
			array(
				'field_id'		=> 'exclude_replies',
				'title'			=> 'Exclude Replies',
				'type'			=> 'checkbox',
				'label'			=> __( 'Replies will be excluded.', 'fetch-tweets' ),
				'description'	=> __( 'This prevents replies from appearing in the returned timeline.', 'fetch-tweets' ),
			),		
			// array(	// since 1.2.0
				// 'field_id'		=> 'list_id',			
				// 'type'			=> 'hidden',
				// 'hidden'		=>	true,
			// ),			
			array(
				'field_id'		=> 'include_rts',
				'title'			=> __( 'Include Retweets', 'fetch-tweets' ),
				'label'			=> __( 'Retweets will be included.', 'fetch-tweets' ),
				'type'			=> 'checkbox',
			),			
			// array(	// since 1.3.3
				// 'field_id'		=> 'until',
				// 'type'			=> 'hidden',
				// 'hidden'		=>	true,
			// ),			
			// array(	// since 1.3.3
				// 'field_id'		=> 'geocentric_coordinate',
				// 'type'			=> 'hidden',
				// 'hidden'		=>	true,
			// ),
			// array(	// since 1.3.3
				// 'field_id'		=> 'geocentric_radius',
				// 'type'			=> 'hidden',
				// 'hidden'		=>	true,
			// ),					
			array()
		);	
	
	}
	
	public function validation_FetchTweets_MetaBox_ScreenName( $arrInput ) {	// validation_ + extended class name
			
		$arrInput['item_count'] = $this->oUtil->fixNumber( 
			$arrInput['item_count'], 	// number to sanitize
			20, 	// default
			1, 		// minimum
			200
		);
				
		return $arrInput;
		
	}
	
}
