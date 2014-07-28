<?php
abstract class FetchTweets_MetaBox_Template_ extends FetchTweets_AdminPageFramework_MetaBox {

	public function setUp() {
		
		$oTemplates = new FetchTweets_Templates;
		$this->addSettingFields(			
			array(
				'field_id'		=> 'fetch_tweets_template',
				'title'			=> __( 'Select Template', 'fetch-tweets' ),
				'description'	=> __( 'Set the default template for this rule. If a template is specified in a widget, the shortcode, or the function, this setting will be overridden.', 'fetch-tweets' ),
				'label'			=> $arr = $oTemplates->getTemplateArrayForSelectLabel(),
				'type'			=> 'select',
				// 'after_field' 	=> '<pre>' . print_r( $arr, true ) . '</pre>', // debug
				'default'			=> $oTemplates->getDefaultTemplateSlug(),
				'show_title_column'	=>	false,
			),							
			array()
		);
		
	}
	
}