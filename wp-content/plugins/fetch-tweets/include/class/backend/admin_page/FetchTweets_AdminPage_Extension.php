<?php
abstract class FetchTweets_AdminPage_Extension extends FetchTweets_AdminPage_Authentication {
				
	/*
	 * Extension page
	 */ 
	public function do_before_fetch_tweets_extensions() {	// do_before_ + page slug
		$this->setPageTitleVisibility( false );
	}
	public function do_fetch_tweets_extensions_get_extensions() {
				
		$oExtensionLoader = new FetchTweets_Extensions();
		$arrFeedItems = $oExtensionLoader->fetchFeed( 'http://feeds.feedburner.com/MiunosoftFetchTweetsExtension' );
		if ( empty( $arrFeedItems ) ) {
			echo "<h3>" . __( 'No extension has been found.', 'fetch-tweets' ) . "</h3>";
			return;
		}
		
		$arrOutput = array();
		$intMaxCols = 4;
		$this->arrColumnInfo = $this->arrColumnInfoDefault;
		foreach( $arrFeedItems as $strTitle => $arrItem ) {
			
			if ( ! isset( $arrItem['title'] ) ) continue;
			
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
			
}