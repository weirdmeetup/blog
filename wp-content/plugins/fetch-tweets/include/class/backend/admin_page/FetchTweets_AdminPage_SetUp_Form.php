<?php
abstract class FetchTweets_AdminPage_SetUp_Form extends FetchTweets_AdminPage_SetUp_Page {
			
	protected function _setUpForm() {
	
		/*
		 * Form Elements
		 */
		$this->addSettingSections(
			'fetch_tweets_settings',
			array(
				'section_id'		=> 'twitter_connect',
				'tab_slug'		=> 'twitter_connect',
				'title'			=> __( 'Authenticate', 'fetch-tweets' ),
			),		
			array(
				'section_id'		=> 'authentication_keys',
				'tab_slug'		=> 'authentication',
				'title'			=> __( 'Authentication Keys', 'fetch-tweets' ),
				'description'	=> __( 'These keys are required to process oAuth requests of the twitter API.', 'fetch-tweets' ),
			),
			array(
				'section_id'		=> 'default_values',
				'tab_slug'		=> 'general',
				'title'			=> __( 'Default Values', 'fetch-tweets' ),
				'help'			=> __( 'Set the default option values which will be applied when the argument values are not set.', 'fetch-tweets' )
										. __( 'These values will be overridden by the argument set directly to the widget options or shortcode.', 'fetch-tweets' ),
			),			
			array(
				'section_id'		=> 'cache_settings',
				'tab_slug'		=> 'general',
				'title'			=> __( 'Cache Settings', 'fetch-tweets' ),
			),
			array(
				'section_id'		=> 'search',
				'tab_slug'		=> 'general',
				'title'			=> __( 'Search', 'fetch-tweets' ),
			),				
			array(
				'section_id'		=> 'capabilities',
				'capability'		=> 'manage_options',
				'tab_slug'		=> 'misc',
				'title'			=> __( 'Access Rights', 'fetch-tweets' ),
				'description'	=> __( 'Set the access levels to the plugin setting pages.', 'fetch-tweets' ),
			),			
			array(
				'section_id'		=> 'reset_settings',
				'capability'		=> 'manage_options',
				'tab_slug'		=> 'reset',
				'title'			=> __( 'Reset Settings', 'fetch-tweets' ),
				'description'	=> __( 'If you get broken options, initialize them by performing reset.', 'fetch-tweets' ),
			),
			array(
				'section_id'		=> 'caches',
				'tab_slug'		=> 'reset',
				'title'			=> __( 'Caches', 'fetch-tweets' ),
				'description'	=> __( 'If you need to refresh the fetched tweets, clear the caches.', 'fetch-tweets' ),
			)			
		);	
		$this->addSettingSections(
			'fetch_tweets_add_rule_by_list',
			array(
				'section_id'	=> 'add_rule_by_list',
				'title'			=> __( 'Specify the Screen Name', 'fetch-tweets' ),
				'description'	=> __( 'In order to select list, the user name(screen name) of the account that owns the list must be specified.', 'fetch-tweets' ),
			)		
		);
		
		$this->addSettingFields(
			'add_rule_by_list',	// target section id
			array(	
				'field_id' => 'list_owner_accounts',
				'title' => __( 'Owner Accounts', 'fetch-tweets' ),
				'description' => __( 'Select the screen name that owns the list.', 'fetch-tweets' ),
				'type' => 'select',
				'value' => '',
			),			
			array(	
				'field_id' => 'list_owner_screen_name',
				'title' => __( 'Owner Screen Name', 'fetch-tweets' ) . ' <span class="optional">(' . __( 'optional', 'fetch-tweets' ) . ')</span>',
				'description' => __( 'The screen name(user name) that owns the list. When the target screen name is not listed above, specify here.', 'fetch-tweets' ) . '<br />'
					. 'e.g. miunosoft',
				'type' => 'text',
				'value' => '',
				'attributes'	=>	array(
					'size'	=>	40,
				),				
			),
			array(  // single button
				'field_id' => 'list_proceed',
				'type' => 'submit',
				'before_field' => "<div class='right-button'>",
				'after_field' => "</div>",
				'label' => __( 'Proceed', 'fetch-tweets' ),
				'attributes'	=>	array(
					'class'	=>	'button button-primary',
				),					
			)		
		);		
		
		// Add setting fields
 		$this->addSettingFields(
			'twitter_connect',
			array(	
				'field_id' => 'connect_to_twitter',
				'title' => __( 'Connect to Twitter', 'fetch-tweets' ),
				'label' => __( 'Connect', 'fetch-tweets' ),
				'href' => add_query_arg( array( 'post_type' => 'fetch_tweets', 'page' => 'fetch_tweets_settings', 'tab' => 'twitter_redirect' ), admin_url( $GLOBALS['pagenow'] ) ),
				'type' => 'submit',
			),	
			array(	
				'field_id' => 'manual_authentication',
				'title' => __( 'Manual', 'fetch-tweets' ),
				'label' => __( 'Set Keys Manually', 'fetch-tweets' ),
				'href' => add_query_arg( array( 'post_type' => 'fetch_tweets', 'page' => 'fetch_tweets_settings', 'tab' => 'authentication', 'settings-updated' => false ) ),
				'type' => 'submit',
				'attributes'	=>	array(
					'class'	=>	'button button-secondary',
				),
			)			
		); 
		$this->addSettingFields(
			'authentication_keys',
			array(	
				'field_id' => 'consumer_key',

				'title' => __( 'Consumer Key', 'fetch-tweets' ),
				'type' => 'text',
				'attributes'	=>	array(
					'size'	=>	60,
				),				
			),
			array(	
				'field_id' => 'consumer_secret',
				'title' => __( 'Consumer Secret', 'fetch-tweets' ),
				'type' => 'text',
				'attributes'	=>	array(
					'size'	=>	60,
				),				
			),
			array(	
				'field_id' => 'access_token',
				'title' => __( 'Access Token', 'fetch-tweets' ),
				'type' => 'text',
				'attributes'	=>	array(
					'size'	=>	60,
				),
			),
			array(	
				'field_id' => 'access_secret',
				'title' => __( 'Access Secret', 'fetch-tweets' ),
				'type' => 'text',
				'attributes'	=>	array(
					'size'	=>	60,
				),
				'description' => '<p class="description">' 
					. sprintf( __( 'You can obtain those keys by logging in to <a href="%1$s" target="_blank">Twitter Developers</a>', 'fetch-tweets' ), 'https://dev.twitter.com/apps' )
					. '</p>',
			),
			array(
				'field_id' => 'connect_method',
				'type' => 'hidden',
				'value' => 'manual',
				'is_hidden' => true,
				'attributes'	=>	array(
					'fieldrow'	=>	array(
						'style'	=>	'display:none',
					)
				),
			),
			array(  // single button
				'field_id' => 'submit_authentication_keys',
				'type' => 'submit',
				'before_field' => "<div class='right-button'>",
				'after_field' => "</div>",
				'label' => __( 'Authenticate', 'fetch-tweets' ),
				'attributes'	=>	array(
					'class'	=>	'button button-primary',
				),
			)		
		);
		// default_values
		$this->addSettingFields(
			'default_values',
			array(
				'field_id' => 'count',
				'title' => __( 'Number of Items', 'fetch-tweets' ),
				'help' => __( 'The number of tweets to display.', 'fetch-tweets' )
					. __( 'Default', 'fetch-tweets' ) . ': ' . $GLOBALS['oFetchTweets_Option']->aStructure_DefaultParams['count']
					. __( 'This option corresponds to the <code>count</code> argument value. For instance, with this shortcode, <code>[fetch_tweets id="10" count="30"]</code>, the count value, 30, will override this option. If the <code>count</code> parameter is not set, this option value will be used.', 'fetch-tweets' ),
				'default'	=> $GLOBALS['oFetchTweets_Option']->aStructure_DefaultParams['count'],
				'type' => 'number',
			),
			array(
				'field_id'		=> 'twitter_media',
				'title'			=> __( 'Twitter Media', 'fetch-tweets' ),
				'type'			=> 'checkbox',
				'label'			=> __( 'Display media images posted in the tweet that are recognized as media file by Twitter.' ),
				'help'	=> __( 'This option corresponds to the <code>twitter_media</code> argument value. For instance, with this shortcode, <code>[fetch_tweets id="10" count="30"]</code>, the count value, 30, will override this option. If the <code>count</code> parameter is not set, this option value will be used.', 'fetch-tweets' ) . ' '
					. __( 'Currently only photos are supported by the Twitter API.' ),
				'default'			=> $GLOBALS['oFetchTweets_Option']->aStructure_DefaultParams['twitter_media'],
			),
			array(
				'field_id'		=> 'external_media',
				'title'			=> __( 'External Media', 'fetch-tweets' ),
				'type'			=> 'checkbox',
				'label'			=> __( 'Replace media links of external sources to an embedded element.', 'fetch-tweets' ),
				'help'			=> __( 'This option corresponds to the <code>external_media</code> argument value. For instance, with this shortcode, <code>[fetch_tweets id="10" count="30"]</code>, the count value, 30, will override this option. If the <code>count</code> parameter is not set, this option value will be used.', 'fetch-tweets' ) . ' '
					. __( 'Unlike the above media images, there are media links that are not categorized as media by the Twitter API. Thus, enabling this option will attempt to replace them to the embedded elements.', 'fetch-tweets' ) . ' e.g. youtube, vimeo, dailymotion etc.',
				'default'			=> $GLOBALS['oFetchTweets_Option']->aStructure_DefaultParams['external_media'],
			),
			array()
		);
		$this->addSettingFields(
			'cache_settings',
			array(
				'field_id'		=> 'cache_for_errors',
				'title'			=> __( 'Cache for Errors', 'fetch-tweets' ),
				'type'			=> 'checkbox',
				'label'			=> __( 'Cache fetched results even for an error.', 'fetch-tweets' ),
				'description'	=> __( 'This reduces the chances to reach the Twitter API rate limit.', 'fetch-tweets' ),
			),
			array(
				'field_id'		=> 'caching_mode',
				'title'			=> __( 'Caching Mode', 'fetch-tweets' ),
				'type'			=> 'radio',
				'label'			=> array(
					'normal'	=>	__( 'Normal', 'fetch-tweets' ) . ' - ' . __( 'uses WP Cron.', 'fetch-tweets' ),
					'intense'	=>	__( 'Intense', 'fetch-tweets' ) . ' - ' . __( 'uses the plugin caching method.', 'fetch-tweets' ),
				),
				'after_label'	=>	'<br />',
				'default'		=>	'normal',
			)
		);
		$this->addSettingFields(
			'search',
			array(
				'field_id'		=> 'is_searchable',
				'title'			=> __( 'Rules', 'fetch-tweets' ),
				'type'			=> 'checkbox',
				'label'			=> __( 'Make the preview pages of created rules searchable with the WordPress search form.', 'fetch-tweets' ),
			),		
			array(  // single button
				'field_id' => 'submit_cache_settings',
				'type' => 'submit',
				'before_field' => "<div class='right-button'>",
				'after_field' => "</div>",
				'label_min_width'	=>	0,
				'label' => __( 'Save Changes', 'fetch-tweets' ),
				'attributes'	=>	array(
					'class'	=>	'button button-primary',
				),
			)			
		);
		$this->addSettingFields(
			'capabilities',
			array(
				'field_id' => 'setting_page_capability',
				'title' => __( 'Capability', 'fetch-tweets' ),
				'description' => __( 'Select the user role that is allowed to access the plugin setting pages.', 'fetch-tweets' )
					. __( 'Default', 'fetch-tweets' ) . ': ' . __( 'Administrator', 'fetch-tweets' ),
				'type' => 'select',
				'capability' => 'manage_options',
				'label' => array(						
					'manage_options' => __( 'Administrator', 'responsive-column-widgets' ),
					'edit_pages' => __( 'Editor', 'responsive-column-widgets' ),
					'publish_posts' => __( 'Author', 'responsive-column-widgets' ),
					'edit_posts' => __( 'Contributor', 'responsive-column-widgets' ),
					'read' => __( 'Subscriber', 'responsive-column-widgets' ),
				),
			),
			array(  // single button
				'field_id' => 'submit_misc',
				'type' => 'submit',
				'before_field' => "<div class='right-button'>",
				'after_field' => "</div>",
				'label_min_width'	=>	0,
				'label' => __( 'Save Changes', 'fetch-tweets' ),
				'attributes'	=>	array(
					'class'	=>	'button button-primary',
				),
			)			
		);
		$this->addSettingFields(
			'reset_settings',
			array(	
				'field_id' => 'option_sections',
				'title' => __( 'Options to Delete', 'fetch-tweets' ),
				'type' => 'checkbox',
				'delimiter' => '<br />',
				'label' => array(
					'all' => __( 'Reset', 'fetch-tweets' ), 
					// the followings are not supported yet
					// 'general' => __( 'General options', 'fetch-tweets' ), 	
					// 'template' => __( 'Template related options', 'fetch-tweets' ),
				),
			),
			// array(  // single button
				// 'field_id' => 'submit_reset_settings',
				// 'section_id' => 'reset_settings',
				// 'type' => 'submit',
				// 'before_field' => "<div class='right-button'>",
				// 'after_field' => "</div>",
				// 'vLabelMinWidth' => 0,
				// 'label' => __( 'Perform', 'fetch-tweets' ),
				// 'vClassAttribute' => 'button button-primary',
			// ),
			array()
		);
		$this->addSettingFields(
			'caches',
			array(	
				'field_id' => 'clear_caches',
				'title' => __( 'Clear Caches', 'fetch-tweets' ),
				'type' => 'checkbox',
				'label' => __( 'Clear tweet caches', 'fetch-tweets' ),
			),
			array(  // single button
				'field_id' => 'submit_reset_settings',
				'type' => 'submit',
				'before_field' => "<div class='right-button'>",
				'after_field' => "</div>",
				'label_min_width'	=>	0,
				'label' => __( 'Perform', 'fetch-tweets' ),
				'attributes'	=>	array(
					'class'	=>	'button button-primary',
				),
			)			
		);

	}	
				
}