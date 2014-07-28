<?php
abstract class FetchTweets_AdminPage_Template extends FetchTweets_AdminPage_Extension {
		
	protected $arrColumnOption = array (
		'strClassAttr' 				=>	'fetch_tweets_multiple_columns',
		'strClassAttrGroup' 		=>	'fetch_tweets_multiple_columns_box',
		'strClassAttrRow' 			=>	'fetch_tweets_multiple_columns_row',
		'strClassAttrCol' 			=>	'fetch_tweets_multiple_columns_col',
		'strClassAttrFirstCol' 		=>	'fetch_tweets_multiple_columns_first_col',
	);	
	protected $arrColumnInfoDefault = array (	// this will be modified as the items get rendered
		'fIsRowTagClosed'	=>	False,
		'numCurrRowPos'		=>	0,
		'numCurrColPos'		=> 	0,
	);	
	
	/*
	 * Template Page
	 */ 
	public function do_before_fetch_tweets_templates() {
		$this->setPageTitleVisibility( false );
	}
	public function do_fetch_tweets_templates_list_template_table() {	// do_ + page slug + tab slug
			
		$this->oTemplateListTable->prepare_items();
		?>
        <form id="template-filter" method="get">
            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
            <input type="hidden" name="page" value="<?php echo isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : 'fetch_tweets_templates'; ?>" />
            <input type="hidden" name="tab" value="<?php echo isset( $_REQUEST['tab'] ) ? $_REQUEST['tab'] : 'list_template_table'; ?>" />
            <input type="hidden" name="post_type" value="<?php echo isset( $_REQUEST['post_type'] ) ? $_REQUEST['post_type'] : FetchTweets_Commons::PostTypeSlug; ?>" />
            <!-- Now we can render the completed list table -->
            <?php $this->oTemplateListTable->display() ?>
        </form>		
		<?php
	
// echo "<h3>Properties</h3>";			
// echo $this->oDebug->getArray( $this->oProp );
			
	}
	public function do_fetch_tweets_templates_get_templates() {
		
		echo "<p>" . sprintf( __( 'Want your template to be listed here? Send the file to %1$s.', 'fetch-tweets' ), 'wpplugins@michaeluno.jp' ) . "</p>";

		$oExtensionLoader = new FetchTweets_Extensions();
		$arrFeedItems = $oExtensionLoader->fetchFeed( 'http://feeds.feedburner.com/MiunosoftFetchTweetsTemplate' );
		if ( empty( $arrFeedItems ) ) {
			echo "<h3>" . __( 'No extension has been found.', 'fetch-tweets' ) . "</h3>";
			return;
		}
		
		$arrOutput = array();
		$intMaxCols = 4;
		$this->arrColumnInfo = $this->arrColumnInfoDefault;
		foreach( $arrFeedItems as $strTitle => $arrItem ) {
			
			// Increment the position
			$this->arrColumnInfo['numCurrColPos']++;
			
			// Enclose the item buffer into the item container
			$strItem = '<div class="' . $this->arrColumnOption['strClassAttrCol'] 
				. ' ftws_col_element_of_' . $intMaxCols . ' '
				. ' ftws_extension '
				. ( ( $this->arrColumnInfo['numCurrColPos'] == 1 ) ?  $this->arrColumnOption['strClassAttrFirstCol']  : '' )
				. '"'
				. '>' 
				. '<div class="ftws_extension_item">' 
					. "<h4>{$arrItem['title']}</h4>"
					. $arrItem['description'] 
					. "<div class='get-now'><a href='{$arrItem['strLink']}' target='_blank' rel='nofollow'>" 
						. "<input class='button button-secondary' type='submit' value='" . __( 'Get it Now', 'fetch-tweets' ) . "' />"
					. "</a></div>"
				. '</div>'
				. '</div>';	
				
			// If it's the first item in the row, add the class attribute. 
			// Be aware that at this point, the tag will be unclosed. Therefore, it must be closed somewhere. 
			if ( $this->arrColumnInfo['numCurrColPos'] == 1 ) 
				$strItem = '<div class="' . $this->arrColumnOption['strClassAttrRow']  . '">' . $strItem;
		
			// If the current column position reached the set max column, increment the current position of row
			if ( $this->arrColumnInfo['numCurrColPos'] % $intMaxCols == 0 ) {
				$this->arrColumnInfo['numCurrRowPos']++;		// increment the row number
				$this->arrColumnInfo['numCurrColPos'] = 0;		// reset the current column position
				$strItem .= '</div>';  // close the section(row) div tag
				$this->arrColumnInfo['fIsRowTagClosed'] = 	True;
			}		
			
			$arrOutput[] = $strItem;
		
		}
		
		// if the section(row) tag is not closed, close it
		if ( ! $this->arrColumnInfo['fIsRowTagClosed'] ) $arrOutput[] .= '</div>';	
		$this->arrColumnInfo['fIsRowTagClosed'] = true;
		
		// enclose the output in the group tag
		$strOut = '<div class="' . $this->arrColumnOption['strClassAttr'] . ' '
				.  $this->arrColumnOption['strClassAttrGroup'] . ' '
				. '"'
				// . ' style="min-width:' . 200 * $intMaxCols . 'px;"'
				. '>'
				. implode( '', $arrOutput )
				. '</div>';
		
		echo '<div class="ftws_extension_container">' . $strOut . '</div>';
		
	}
	protected function getTemplateArray( $strDefaultTemplateSlug ) {
				
		return $GLOBALS['oFetchTweets_Templates']->getActiveTemplates() + $GLOBALS['oFetchTweets_Templates']->getUploadedTemplates();
				
		$arrActiveTemplates = $GLOBALS['oFetchTweets_Templates']->getActiveTemplates();
		$arrUploadedTemplates = $GLOBALS['oFetchTweets_Templates']->getUploadedTemplates();
		$arrData = $arrActiveTemplates + $arrUploadedTemplates;
		// $arrData = $GLOBALS['oFetchTweets_Option']->aOptions['arrTemplates'] + $GLOBALS['oFetchTweets_Templates']->getTemplates();
		foreach ( $arrData as $arrDatum ) 	// set all default flags to false.
			$arrDatum['fIsDefault'] = false;		
			
		$arrData[ $strDefaultTemplateSlug ]['fIsDefault'] = true;	// set the default template.
		$arrData[ $strDefaultTemplateSlug ]['fIsActive'] = true;	// set the default template to be activated.
		
		return $arrData;
			
	}	
					
}