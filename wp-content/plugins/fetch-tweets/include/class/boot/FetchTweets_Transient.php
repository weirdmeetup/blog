<?php
/**
 *	Handles plugin specific transients
 *	
 * @package     Fetch Tweets
 * @copyright   Copyright (c) 2013, Michael Uno
 * @authorurl	http://michaeluno.jp
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since		1.3.5
 * 
*/

final class FetchTweets_Transient {
	
	static public function clearTransients( $arrPrefixes=array( 'FTWS', 'FTWSFeedMs' ) ) {	// for the deactivation hook.

		// This method also serves for the deactivation callback and in that case, an empty value is passed to the first parameter.
		$arrPrefixes = empty( $arrPrefixes ) ? array( 'FTWS', 'FTWSFeedMs' ) : $arrPrefixes;		// 'FTWSAds'
			
		foreach( $arrPrefixes as $strPrefix ) {
			$GLOBALS['wpdb']->query( "DELETE FROM `" . $GLOBALS['table_prefix'] . "options` WHERE `option_name` LIKE ( '_transient_%{$strPrefix}%' )" );
			$GLOBALS['wpdb']->query( "DELETE FROM `" . $GLOBALS['table_prefix'] . "options` WHERE `option_name` LIKE ( '_transient_timeout_%{$strPrefix}%' )" );
		}
		
	
	}

}