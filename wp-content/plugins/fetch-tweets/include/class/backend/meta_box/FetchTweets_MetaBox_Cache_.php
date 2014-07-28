<?php
class FetchTweets_MetaBox_Cache_ extends FetchTweets_AdminPageFramework_MetaBox {
	
	public function setUp() {
		
		$this->addSettingFields(									
			array(
				'field_id'		=> 'cache',
				'title'			=> __( 'Cache Duration', 'fetch-tweets' ),
				'description'	=> __( 'The cache lifespan in seconds. For no cache, set 0.', 'fetch-tweets' ) . ' ' . __( 'Default:', 'fetch-tweets' ) . ': 1200',
				'type'			=> 'number',
				'default'			=> 60 * 20,	// 20 minutes
			),			
			array()
		);
		
	}
		
}
