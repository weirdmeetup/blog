<?php
abstract class FetchTweets_AdminPage_Menu extends FetchTweets_AdminPage_SetUp {
	
	/*
	 * Customize the Menu
	 */
	public function _replyToBuildMenu() {
	
		parent::_replyToBuildMenu();
		
		// Somehow the settings link in the plugin listing page points to the Create Rule by List page. So fix it to the Settings page.
		$this->oProp->sDefaultPageSlug = FetchTweets_Commons::PageSettingsSlug;

		// Remove the default post type menu item.
		$_sPageSlug = $this->oProp->aRootMenu['sPageSlug'];
		if ( ! isset( $GLOBALS['submenu'][ $_sPageSlug ] ) ) return;	// logged-in users of an insufficient access level don't have the menu to be registered.
		foreach ( $GLOBALS['submenu'][ $_sPageSlug ] as $intIndex => $arrSubMenu ) {
						
			if ( ! isset( $arrSubMenu[ 2 ] ) ) continue;
			
			// Remove the default Add New entry.
			if ( $arrSubMenu[ 2 ] == 'post-new.php?post_type=' . FetchTweets_Commons::PostTypeSlug ) {
				unset( $GLOBALS['submenu'][ $_sPageSlug ][ $intIndex ] );
				continue;
			}
			
			// Copy and remove the Tag menu element to change the position. 
			if ( $arrSubMenu[ 2 ] == 'edit-tags.php?taxonomy=' . FetchTweets_Commons::TagSlug . '&amp;post_type=' . FetchTweets_Commons::PostTypeSlug ) {
				$_aMenuEntry_Tag = array( $GLOBALS['submenu'][ $_sPageSlug ][ $intIndex ] );
				unset( $GLOBALS['submenu'][ $_sPageSlug ][ $intIndex ] );
				continue;				
			}

		}
		
		// Second iteration.
		$intMenuPos_Setting = -1;
		foreach ( $GLOBALS['submenu'][ $_sPageSlug ] as $intIndex => $arrSubMenu ) {
			
			$intMenuPos_Setting++;	
			if (  isset( $arrSubMenu[ 2 ] ) && $arrSubMenu[ 2 ] == 'fetch_tweets_settings' ) 
				break;	// the position variable will now contain the position of the Setting menu item.
	
		}
	
		// Insert the Tag menu item before the Setting menu item.
		if ( isset( $_aMenuEntry_Tag ) ) {
			array_splice( 
				$GLOBALS['submenu'][ $_sPageSlug ], // original array
				$intMenuPos_Setting, 	// position
				0, 	// offset - should be 0
				$_aMenuEntry_Tag 	// replacement array
			);		
		}

		// Unfortunately array_splice() will loose all the associated keys(index).
		
	}
				
}