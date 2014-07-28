<?php
/**
 *	Methods used for debugging
 *
 * @package     Fetch Tweets
 * @copyright   Copyright (c) 2013, Michael Uno
 * @authorurl	http://michaeluno.jp
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since		1.0.0
 *
 */

final class FetchTweets_Debug {

	static public function dumpArray( $arr, $strFilePath=null ) {
		
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) return;
		
		echo self::getArray( $arr, $strFilePath );
		
	}

	static public function getArray( $arr, $strFilePath=null ) {
		
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) return;
		
		if ( $strFilePath ) 
			self::logArray( $arr, $strFilePath );			
			
		// esc_html() has a bug that breaks with complex HTML code.
		return "<div><pre class='dump-array'>" . htmlspecialchars( print_r( $arr, true ) ) . "</pre><div>";	
		
	}
					
	static public function logArray( $asArray, $sFilePath=null ) {
		
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) return;
		
		static $_iPageLoadID;
		$_iPageLoadID = $_iPageLoadID ? $_iPageLoadID : uniqid();		
		
		$_oCallerInfo = debug_backtrace();
		$_sCallerFunction = isset( $_oCallerInfo[ 1 ]['function'] ) ? $_oCallerInfo[ 1 ]['function'] : '';
		$_sCallerClasss = isset( $_oCallerInfo[ 1 ]['class'] ) ? $_oCallerInfo[ 1 ]['class'] : '';
		$sFilePath = $sFilePath
			? $sFilePath
			: WP_CONTENT_DIR . DIRECTORY_SEPARATOR . get_class() . '_' . date( "Ymd" ) . '.log';
		file_put_contents( 
			$sFilePath, 
			date( "Y/m/d H:i:s", current_time( 'timestamp' ) ) . ' ' . "{$_iPageLoadID} {$_sCallerClasss}::{$_sCallerFunction} " . self::getCurrentURL() . PHP_EOL	
			. print_r( $asArray, true ) . PHP_EOL . PHP_EOL,
			FILE_APPEND 
		);			
	}	
	
	static public function echoMemoryUsage() {
		
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) return;
				   
		echo self::getMemoryUsage() . "<br/>";
		
	} 		

    static public function getMemoryUsage( $intType=1 ) {
       
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) return;
	   
		$intMemoryUsage = $intType == 1 ? memory_get_usage( true ) : memory_get_peak_usage( true );
       
        if ( $intMemoryUsage < 1024 ) return $intMemoryUsage . " bytes";
        
		if ( $intMemoryUsage < 1048576 ) return round( $intMemoryUsage/1024,2 ) . " kilobytes";
        
        return round( $intMemoryUsage / 1048576,2 ) . " megabytes";
           
    } 		
	
	static public function getOption( $strKey ) {

		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) return;
		
		$oOption = & $GLOBALS['oFetchTweets_Option'];		
		if ( ! isset( $oOption->aOptions[ $strKey ] ) ) return;
		
		self::dumpArray( $oOption->aOptions[ $strKey ] );
		
	}
	
	/**
	 * Retrieves the currently loaded page url.
	 * 
	 * @since			1.3.3.11
	 */
	static public function getCurrentURL() {
		$sSSL = ( !empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ) ? true:false;
		$sServerProtocol = strtolower( $_SERVER['SERVER_PROTOCOL'] );
		$sProtocol = substr( $sServerProtocol, 0, strpos( $sServerProtocol, '/' ) ) . ( ( $sSSL ) ? 's' : '' );
		$sPort = $_SERVER['SERVER_PORT'];
		$sPort = ( ( !$sSSL && $sPort=='80' ) || ( $sSSL && $sPort=='443' ) ) ? '' : ':' . $sPort;
		$sHost = isset( $_SERVER['HTTP_X_FORWARDED_HOST'] ) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : isset( $_SERVER['HTTP_HOST'] ) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
		return $sProtocol . '://' . $sHost . $sPort . $_SERVER['REQUEST_URI'];
	}

}