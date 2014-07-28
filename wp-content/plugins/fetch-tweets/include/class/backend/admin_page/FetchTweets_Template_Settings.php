<?php
/*
 * No need to modify the following class.
 * */
abstract class FetchTweets_Template_Settings {
	
	// Do not modify these properties.
	protected $sParentAdminPaggeClassName = 'FetchTweets_AdminPage';
	protected $sTemplateID = '';	// assigned in the constructor.
	protected $sParentPageSlug = 'fetch_tweets_templates';	// in the url, the ... part in ?page=... 
	
	/*
	 * These must be overridden in the extended class.
	 * */
	protected $sParentTabSlug = '';	// in the url, the ... part in &tab=...
	protected $sTemplateName = '';	// the template name
	protected $sSectionID = '';	// sets the main section ID; it's okay to have more than one section IDs though. 
	
	/*
	 * No need to modify the constructor.
	 * */
	public function __construct( $sTemplateDirPath='' ) {
		
		$_sTemplateDirPath = $sTemplateDirPath ? $sTemplateDirPath : dirname( __FILE__ );
		$this->sTemplateID = md5( $_sTemplateDirPath );
		
		// tabs_{instantiated class name}_{page slug}
		add_filter( 'tabs_' . $this->sParentAdminPaggeClassName . "_" . $this->sParentPageSlug, array( $this, '_replyToAddInPageTab' ) );
		
		// sections_{instantiated class name}
		add_filter( "sections_" . $this->sParentAdminPaggeClassName, array( $this, 'addSettingSections' ) );
		
		// fields_{instantiated class name}
		add_filter( "fields_" . $this->sParentAdminPaggeClassName, array( $this, 'addSettingFields' ) );

		// validation_{page slug}_{tab slug}
		add_filter( "validation_{$this->sParentPageSlug}_{$this->sParentTabSlug}", array( $this, 'validateSettings' ), 10, 2 );
			
		// Adds the Settings link in the template listing table.
		add_filter( 'fetch_tweets_filter_template_listing_table_action_links', array( $this, '_replyToAddSettingsLink' ), 10, 2 );
		
	}
	
	/*
	 * 	No need to modify these method.
	 * */
	public function _replyToAddInPageTab( $aTabs ) {
	
		return array(
			$this->sParentTabSlug => array(
				'page_slug'	=> $this->sParentPageSlug,
				'title'		=> $this->sTemplateName,
				'tab_slug'	=> $this->sParentTabSlug,
				'order'		=> 20
			)
		) + $aTabs;
		
	}
	public function _replyToAddSettingsLink( $aLinks, $sTemplateID ) {
				
		if ( $sTemplateID != $this->sTemplateID ) return $aLinks;

		array_unshift(	
			$aLinks,
			"<a href='?post_type=" . FetchTweets_Commons::PostTypeSlug . "&page={$this->sParentPageSlug}&tab={$this->sParentTabSlug}'>" 
				. __( 'Settings', 'fetch-tweets' ) 
			. "</a>" 
		); 
		return $aLinks;			
		
	}

	/*
	 * The following methods should be overridden in the extended class.
	 */
	public function addSettingSections( $aSections ) { return $aSections; }
	public function addSettingFields( $aFields ) { return $aFields; }
	public function validateSettings( $aInput, $aOldInput ) { return $aInput; }
	
	
}