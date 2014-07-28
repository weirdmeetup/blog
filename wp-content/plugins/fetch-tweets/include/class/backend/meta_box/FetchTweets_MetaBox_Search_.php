<?php
class FetchTweets_MetaBox_Search_ extends FetchTweets_AdminPageFramework_MetaBox {
	
	public function start_FetchTweets_MetaBox_Search() {
		
		// Register the date custom field type
		new FetchTweets_DateCustomFieldType( get_class( $this ) );	
		new FetchTweets_GeometryCustomFieldType( get_class( $this ) );	
		
	}
				
	/**
	 * Adds form fields for the options to fetch tweets by keyword search to the meta box.
	 * 
	 * @since			1.0.0
	 */ 
	public function setup() {
		
		$this->addSettingFields(		
			array(
				'field_id'		=> 'tweet_type',
				'type'			=> 'hidden',
				'value'			=> 'search',
				'hidden'		=>	true,
			),			
			// array(	// non-used fields must be set as hidden since the callback function will assign a value.
				// 'field_id'		=> 'screen_name',
				// 'type'			=> 'hidden',
				// 'hidden'		=>	true,
			// ),				
			array(
				'field_id'		=> 'search_keyword',
				'title'			=> __( 'Search Keyword', 'fetch-tweets' ),
				'description'	=> sprintf( __( 'The keyword to search. For a complex combination of terms and operators, refer to the <strong>Search Operators</strong> section of <a href="%1$s" target="_blank">Using the Twitter Search API</a>.', 'fetch-tweets' ), 'https://dev.twitter.com/docs/using-search' ) 
					. ' e.g. <code>love OR hate</code>, <code>#wordpress</code>',
				'type'			=> 'text',
			),
			array(
				'field_id'		=> 'item_count',
				'title'			=> __( 'Item Count', 'fetch-tweets' ),
				'description'	=> __( 'Set how many items should be fetched.', 'fetch-tweets' ) . ' ' 
					. __( 'Max', 'fetch-tweets' ) . ': 100 '
					. __( 'Default', 'fetch-tweets' ) . ': 20',
				'type'			=> 'number',
				'default'			=> 20,
				'attributes'	=>	array(
					'max'	=>	100,
				),
			),				
			array(
				'field_id'		=> 'language',
				'title'			=> __( 'Language ', 'fetch-tweets' ),
				'type'			=> 'select',
				'label' => array( 
					'none' => __( 'Any Language', 'fetch-tweets' ),
					'am' => __( 'Amharic (አማርኛ)', 'fetch-tweets' ),
					'ar' => __( 'Arabic (العربية)', 'fetch-tweets' ),
					'bg' => __( 'Bulgarian (Български)', 'fetch-tweets' ),
					'bn' => __( 'Bengali (বাংলা)', 'fetch-tweets' ),
					'bo' => __( 'Tibetan (བོད་སྐད)', 'fetch-tweets' ),
					'chr' => __( 'Cherokee (ᏣᎳᎩ)', 'fetch-tweets' ),
					'da' => __( 'Danish (Dansk)', 'fetch-tweets' ),
					'de' => __( 'German (Deutsch)', 'fetch-tweets' ),
					'dv' => __( 'Maldivian (ދިވެހި)', 'fetch-tweets' ),
					'el' => __( 'Greek (Ελληνικά)', 'fetch-tweets' ),
					'en' => __( 'English (English)', 'fetch-tweets' ),
					'es' => __( 'Spanish (Español)', 'fetch-tweets' ),
					'fa' => __( 'Persian (فارسی)', 'fetch-tweets' ),
					'fi' => __( 'Finnish (Suomi)', 'fetch-tweets' ),
					'fr' => __( 'French (Français)', 'fetch-tweets' ),
					'gu' => __( 'Gujarati (ગુજરાતી)', 'fetch-tweets' ),
					'iw' => __( 'Hebrew (עברית)', 'fetch-tweets' ),
					'hi' => __( 'Hindi (हिंदी)', 'fetch-tweets' ),
					'hu' => __( 'Hungarian (Magyar)', 'fetch-tweets' ),
					'hy' => __( 'Armenian (Հայերեն)', 'fetch-tweets' ),
					'in' => __( 'Indonesian (Bahasa Indonesia)', 'fetch-tweets' ),
					'is' => __( 'Icelandic (Íslenska)', 'fetch-tweets' ),
					'it' => __( 'Italian (Italiano)', 'fetch-tweets' ),
					'iu' => __( 'Inuktitut (ᐃᓄᒃᑎᑐᑦ)', 'fetch-tweets' ),
					'ja' => __( 'Japanese (日本語)', 'fetch-tweets' ),
					'ka' => __( 'Georgian (ქართული)', 'fetch-tweets' ),
					'km' => __( 'Khmer (ខ្មែរ)', 'fetch-tweets' ),
					'kn' => __( 'Kannada (ಕನ್ನಡ)', 'fetch-tweets' ),
					'ko' => __( 'Korean (한국어)', 'fetch-tweets' ),
					'lo' => __( 'Lao (ລາວ)', 'fetch-tweets' ),
					'lt' => __( 'Lithuanian (Lietuvių)', 'fetch-tweets' ),
					'ml' => __( 'Malayalam (മലയാളം)', 'fetch-tweets' ),
					'my' => __( 'Myanmar (မြန်မာဘာသာ)', 'fetch-tweets' ),
					'ne' => __( 'Nepali (नेपाली)', 'fetch-tweets' ),
					'nl' => __( 'Dutch (Nederlands)', 'fetch-tweets' ),
					'no' => __( 'Norwegian (Norsk)', 'fetch-tweets' ),
					'or' => __( 'Oriya (ଓଡ଼ିଆ)', 'fetch-tweets' ),
					'pa' => __( 'Panjabi (ਪੰਜਾਬੀ)', 'fetch-tweets' ),
					'pl' => __( 'Polish (Polski)', 'fetch-tweets' ),
					'pt' => __( 'Portuguese (Português)', 'fetch-tweets' ),
					'ru' => __( 'Russian (Русский)', 'fetch-tweets' ),
					'si' => __( 'Sinhala (සිංහල)', 'fetch-tweets' ),
					'sv' => __( 'Swedish (Svenska)', 'fetch-tweets' ),
					'ta' => __( 'Tamil (தமிழ்)', 'fetch-tweets' ),
					'te' => __( 'Telugu (తెలుగు)', 'fetch-tweets' ),
					'th' => __( 'Thai (ไทย)', 'fetch-tweets' ),
					'tl' => __( 'Tagalog (Tagalog)', 'fetch-tweets' ),
					'tr' => __( 'Turkish (Türkçe)', 'fetch-tweets' ),
					'ur' => __( 'Urdu (ﺍﺭﺩﻭ)', 'fetch-tweets' ),
					'vi' => __( 'Vietnamese (Tiếng Việt)', 'fetch-tweets' ),
					'zh' => __( 'Chinese (中文)', 'fetch-tweets' ),
				),		
				'default' 			=> 'none',	
			),				
			array(
				'field_id'		=> 'result_type',
				'title'			=> __( 'Result Type', 'fetch-tweets' ),
				'type'			=> 'radio',
				'label' => array( 
					'mixed' => 'mixed' . ' - ' . __( 'includes both popular and real time results in the response.', 'fetch-tweets' ),
					'recent' => 'recent' . ' - ' . __( 'returns only the most recent results in the response.', 'fetch-tweets' ),
					'popular' => 'popular' . ' - ' . __( 'return only the most popular results in the response.', 'fetch-tweets' ),
				),
				'default' => 'mixed',
			),
			array(	// since 1.3.3
				'field_id'		=> 'until',
				'title'			=> __( 'Date Until', 'fetch-tweets' ) . " <span class='description'>(" . __( 'optional', 'fetch-tweets' ) . ")</span>",
				'description'	=> __( 'Returns tweets generated before the given date. Set blank not to specify any date.', 'fetch-tweets' )
					. ' ' . __( 'Format', 'fetch-tweets' ) . ': ' . '<code>yy-mm-dd</code>',
				'type'			=> 'date',
				'date_format' 		=> 'yy-mm-dd',	// yy/mm/dd is the default format.
			),
			array(	// since 1.3.3
				'field_id'		=> 'geocentric_coordinate',
				'title'			=> __( 'Geometric Coordinate', 'fetch-tweets' ) . " <span class='description'>(" . __( 'optional', 'fetch-tweets' ) . ")</span>",
				'description'	=> __( 'Restricts tweets to users located within a given radius of the given latitude/longitude. Leave them empty not to set any.', 'fetch-tweets' ),
				'type'			=> 'geometry',

// TODO: convert the latitude key to 0 and the longitude key to 1 for APF v2
// 'label'			=> array(
	// 'latitude' => __( 'Latitude', 'fetch-tweets' ),
	// 'longitude' => __( 'Longitude', 'fetch-tweets' ),
// ),
			),
			array(	// since 1.3.3
				'field_id'		=> 'geocentric_radius',
				'title'			=> __( 'Geometric Radius', 'fetch-tweets' ) . " <span class='description'>(" . __( 'optional', 'fetch-tweets' ) . ")</span>",
				'type'			=> 'size',
				'default'		=> array( 'size' => '', 'unit' => 'mi' ),
				'units'			=> array(
					'mi'	=>	__( 'miles', 'fetch-tweets' ),
					'km' => __( 'kilometers', 'fetch-tweets' ),
				),
				'description'	=> __( 'Leave this empty not to set any. In order to perform the geometric search, this option and the above coordinate must be specified.', 'fetch-tweets' ),
			),			
			// array(
				// 'field_id'		=> 'exclude_replies',
				// 'type'			=> 'hidden',
				// 'hidden'		=>	true,
			// ),
			// array(	// since 1.2.0
				// 'field_id'		=> 'list_id',			
				// 'type'			=> 'hidden',
				// 'hidden'		=>	true,
			// ),		
			// array(
				// 'field_id'		=> 'include_rts',
				// 'type'			=> 'hidden',
				// 'hidden'		=>	true,
			// ),			
			array()
		);
		
	}	

	public function validation_FetchTweets_MetaBox_Search( $arrInput ) {	// validation_ + extended class name
			
		$arrInput['item_count'] = $this->oUtil->fixNumber( 
			$arrInput['item_count'], 	// number to sanitize
			20, 	// default
			1, 		// minimum
			100
		);
				
		return $arrInput;
		
	}	
	
}
