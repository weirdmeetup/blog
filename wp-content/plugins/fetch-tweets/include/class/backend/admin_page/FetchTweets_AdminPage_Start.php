<?php
abstract class FetchTweets_AdminPage_Start extends FetchTweets_AdminPageFramework {
	// abstract class FetchTweets_AdminPage_Start extends AdminPageFramework {

	public function start_FetchTweets_AdminPage() {
				
		// Set the option property.
		$this->oOption = & $GLOBALS['oFetchTweets_Option'];
		
		// Disable object caching in the plugin pages and the options.php (the page that stores the settings)
		if ( 
			is_admin() 
			&& (
				$GLOBALS['pagenow'] == 'options.php'
				|| isset( $_GET['post_type'] ) && ( $_GET['post_type'] == FetchTweets_Commons::PostTypeSlug || $_GET['post_type'] == FetchTweets_Commons::PostTypeSlugAccounts ) )
			)
		{
			// wp_suspend_cache_addition( true );	//<-- this causes too many database queries so comment it out
			$GLOBALS['_wp_using_ext_object_cache'] = false;	// this helps some caching plugins not to prevent the settings not to be saved
		}		
		
		// For the list table bulk actions. The WP_List_Table class does not set the post type query string in the redirected page.
		// http://.../wp-admin/edit.php?page=fetch_tweets_templates&tab=&_wpnonce=ebed1d5343&_wp_http_referer=%2Fwp360%2Fwp-admin%2Fedit.php%3Fpost_type%3Dfetch_tweets%26page%3Dfetch_tweets_templates&action=activate&paged=1&action2=-1
		if ( 
			( isset( $_POST['post_type'] ) && $_POST['post_type'] == FetchTweets_Commons::PostTypeSlug )	// the form is submitted 
			&& ( ! isset( $_GET['post_type'] ) )	// and post_type query string is not in the url
			&& ( isset( $_GET['page'] ) && $_GET['page'] == 'fetch_tweets_templates' ) // and the page is the template listing table page,
		)
			die( wp_redirect( add_query_arg( array( 'post_type' => FetchTweets_Commons::PostTypeSlug ) + $_GET, admin_url() . '' . $GLOBALS['pagenow'] ) ) );
	
		// Prepare the template array for the template listing table
		if ( isset( $_GET['page'] ) && $_GET['page'] == 'fetch_tweets_templates' ) 
			add_action( 'admin_menu', array( $this, '_replyToProcessBulkActionForTemplateListTable' ) );			
				
	}
	
		public function _replyToProcessBulkActionForTemplateListTable() {

			$this->oTemplateListTable = new FetchTweets_ListTable(
				$GLOBALS['oFetchTweets_Templates']->getActiveTemplates() + $GLOBALS['oFetchTweets_Templates']->getUploadedTemplates(),
				$GLOBALS['oFetchTweets_Templates']->getDefaultTemplateSlug()
			);
			$this->oTemplateListTable->process_bulk_action();
			
		}
	
			
}