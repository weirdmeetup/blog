<?php
abstract class FetchTweets_AdminPage_SetUp_Page extends FetchTweets_AdminPage_Start {
			
	protected function _setUpPages() {
		
		$_sPostTypeSlug = FetchTweets_Commons::PostTypeSlug;
		$this->setRootMenuPageBySlug( "edit.php?post_type={$_sPostTypeSlug}"  );
		$this->addSubMenuItems(
			array(
				'title'	=>	__( 'Add Rule by Screen Name', 'fetch-tweets' ),
				'href'	=>	admin_url( "post-new.php?post_type={$_sPostTypeSlug}&tweet_type=screen_name" ),
				'order'	=>	1,
			),
			array(
				'title' => __( 'Add Rule by Timeline', 'fetch-tweets' ),
				'href' => admin_url( "post-new.php?post_type={$_sPostTypeSlug}&tweet_type=home_timeline" ),
			),			
			array(
				'title' => __( 'Add Rule by Search', 'fetch-tweets' ),
				'href'	=>	admin_url( "post-new.php?post_type={$_sPostTypeSlug}&tweet_type=search" ),
			),			
			array(
				'title'	=> __( 'Add Rule by List', 'fetch-tweets' ),
				'page_slug'	=> 'fetch_tweets_add_rule_by_list',
				'screen_icon'	=> FetchTweets_Commons::getPluginURL( "/asset/image/screen_icon_32x32.png" ),
			),			
			array(
				'title'	=> __( 'Add Rule by Feed', 'fetch-tweets' ),
				'href'	=>	admin_url( "post-new.php?post_type={$_sPostTypeSlug}&tweet_type=feed" ),
			),				
			array(
				'title'	=> __( 'Add Rule by Tweet ID', 'fetch-tweets' ),
				'href'	=>	admin_url( "post-new.php?post_type={$_sPostTypeSlug}&tweet_type=tweet_id" ),
			),			
			array(
				'title'	=> __( 'Add Rule by Custom Query', 'fetch-tweets' ),
				'href'	=>	admin_url( "post-new.php?post_type={$_sPostTypeSlug}&tweet_type=custom_query" ),
			),
			array(
				'title'	=>	__( 'Settings', 'fetch-tweets' ),
				'page_slug'	=>	'fetch_tweets_settings',
				'screen_icon'	=> FetchTweets_Commons::getPluginURL( "/asset/image/screen_icon_32x32.png" ),
			),
			array(
				'title' => __( 'Extensions', 'fetch-tweets' ),
				'page_slug' => 'fetch_tweets_extensions',
				'screen_icon'	=> FetchTweets_Commons::getPluginURL( "/asset/image/screen_icon_32x32.png" ),
			),			
			array(
				'title' => __( 'Templates', 'fetch-tweets' ),
				'page_slug' => 'fetch_tweets_templates',
				'screen_icon'	=> FetchTweets_Commons::getPluginURL( "/asset/image/screen_icon_32x32.png" ),
			)
		);
		$this->addInPageTabs(
			'fetch_tweets_settings',
			array(
				'tab_slug'	=> 'authentication',	// the manual auth keys input page
				'title'		=> __( 'Authentication', 'fetch-tweets' ),
				'parent_tab_slug' => 'twitter_connect',
				'show_in_page_tab'	=> false,	
			),
			array(
				'tab_slug'	=> 'twitter_connect',	// the oAuth connection page
				'title'		=> __( 'Authentication', 'fetch-tweets' ),
				'order'		=> 1,				
			),			
			array(
				'tab_slug'	=> 'twitter_redirect',
				'title'		=> 'Redirect',
				'show_in_page_tab'			=> false,
			),					
			array(
				'tab_slug'	=> 'twitter_callback',
				'title'		=> 'Callback',
				'show_in_page_tab'			=> false,
			),								
			array(
				'tab_slug'	=> 'general',
				'title'		=> __( 'General', 'fetch-tweets' ),
				'order'		=> 2,				
			),				
			array(
				'tab_slug'	=> 'misc',
				'title'		=> __( 'Misc', 'fetch-tweets' ),
				'order'		=> 3,				
			),			
			array(
				'tab_slug'	=> 'reset',
				'title'		=> __( 'Reset', 'fetch-tweets' ),
				'order'		=> 4,				
			)					
		);
		$this->addInPageTabs(
			array(
				'page_slug'	=> 'fetch_tweets_extensions',
				'tab_slug'	=> 'get_extensions',
				'title'		=> __( 'Get Extensions', 'fetch-tweets' ),
				'order'		=> 10,				
			)		
		);
		$this->addInPageTabs(
			array(
				'page_slug'	=> 'fetch_tweets_templates',
				'tab_slug'	=> 'list_template_table',
				'title'		=> __( 'Installed Templates', 'fetch-tweets' ),
				'order'		=> 1,				
			),
			array(
				'page_slug'	=> 'fetch_tweets_templates',
				'tab_slug'	=> 'get_templates',
				'title'		=> __( 'Get Templates', 'fetch-tweets' ),
				'order'		=> 10,				
			)			
			// array(
				// 'page_slug'	=> 'fetch_tweets_settings',
				// 'tab_slug'	=> 'management',
				// 'title'		=> __( 'Management', 'fetch-tweets' ),
			// )
		);		
	}		
	
	protected function _setUpStyles() {
		
		/*
		 * Page Styling
		 */
		$this->setPageHeadingTabsVisibility( false );		// disables the page heading tabs by passing false.
		$this->setInPageTabTag( 'h2' );				
		$this->enqueueStyle(  FetchTweets_Commons::getPluginURL( '/asset/css/admin.css' ) );
		$this->enqueueStyle(  FetchTweets_Commons::getPluginURL( '/asset/css/fetch_tweets_templates.css' ), 'fetch_tweets_templates' );
		$this->enqueueStyle(  FetchTweets_Commons::getPluginURL( '/asset/css/fetch_tweets_settings.css' ), 'fetch_tweets_settings' );
		$this->enqueueStyle(  FetchTweets_Commons::getPluginURL( '/asset/css/fetch_tweets_add_rule_by_list.css' ), 'fetch_tweets_add_rule_by_list' );

	}	
				
}