<?php
/**
 *	Provides utility methods.
 *
 * @package     Fetch Tweets
 * @copyright   Copyright (c) 2013, Michael Uno
 * @authorurl	http://michaeluno.jp
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since		1.2.0
 * 
 */

final class FetchTweets_Utilities {
	
	/**
	 * Merges multiple multi-dimensional array recursively.
	 * 
	 * The advantage of using this method over the array unite operator or array_merge() is that it merges recursively and the null values of the preceding array will be overridden.
	 * 
	 * @since			1.2.0
	 * @static
	 * @access			public
	 * @remark			The parameters are variadic and can add arrays as many as necessary.
	 * @return			array			the united array.
	 */
	static public function uniteArrays( $arrPrecedence, $arrDefault1 ) {
				
		$arrArgs = array_reverse( func_get_args() );
		$arrArray = array();
		foreach( $arrArgs as $arrArg ) 
			$arrArray = self::uniteArraysRecursive( $arrArg, $arrArray );
			
		return $arrArray;
		
	}
	/**
	 * Merges two multi-dimensional arrays recursively.
	 * 
	 * The first parameter array takes its precedence. This is useful to merge default option values. 
	 * An alternative to <em>array_replace_recursive()</em>; it is not supported PHP 5.2.x or below.
	 * 
	 * @since			1.2.0
	 * @static
	 * @access			public
	 * @remark			null values will be overwritten. 	
	 * @param			array			$arrPrecedence			the array that overrides the same keys.
	 * @param			array			$arrDefault				the array that is going to be overridden.
	 * @return			array			the united array.
	 */ 
	static public function uniteArraysRecursive( $arrPrecedence, $arrDefault ) {
				
		if ( is_null( $arrPrecedence ) ) $arrPrecedence = array();
		
		if ( ! is_array( $arrDefault ) || ! is_array( $arrPrecedence ) ) return $arrPrecedence;
			
		foreach( $arrDefault as $strKey => $v ) {
			
			// If the precedence does not have the key, assign the default's value.
			if ( ! array_key_exists( $strKey, $arrPrecedence ) || is_null( $arrPrecedence[ $strKey ] ) )
				$arrPrecedence[ $strKey ] = $v;
			else {
				
				// if the both are arrays, do the recursive process.
				if ( is_array( $arrPrecedence[ $strKey ] ) && is_array( $v ) ) 
					$arrPrecedence[ $strKey ] = self::uniteArraysRecursive( $arrPrecedence[ $strKey ], $v );
			
			}
		}
		return $arrPrecedence;		
	}
	
	/**
	 * Converts the given string with delimiters to a multi-dimensional array.
	 * 
	 * Parameters: 
	 * 1: haystack string
	 * 2, 3, 4...: delimiter
	 * e.g. $arr = convertStringToArray( 'a-1,b-2,c,d|e,f,g', "|", ',', '-' );
	 * 
	 * @since			1.3.3
	 */
	static public function convertStringToArray() {
		
		$intArgs = func_num_args();
		$arrArgs = func_get_args();
		$strInput = $arrArgs[ 0 ];			
		$strDelimiter = $arrArgs[ 1 ];
		
		if ( ! is_string( $strDelimiter ) || $strDelimiter == '' ) return $strInput;
		if ( is_array( $strInput ) ) return $strInput;	// note that is_string( 1 ) yields false.
			
		$arrElems = preg_split( "/[{$strDelimiter}]\s*/", trim( $strInput ), 0, PREG_SPLIT_NO_EMPTY );
		if ( ! is_array( $arrElems ) ) return array();
		
		foreach( $arrElems as &$strElem ) {
			
			$arrParams = $arrArgs;
			$arrParams[0] = $strElem;
			unset( $arrParams[ 1 ] );	// remove the used delimiter.
			// now $strElem becomes an array.
			if ( count( $arrParams ) > 1 ) // if the delimiters are gone, 
				$strElem = call_user_func_array( 'FetchTweets_Utilities::convertStringToArray', $arrParams );
			
			// Added this because the function was not trimming the elements sometimes... not fully tested with multi-dimensional arrays. 
			if ( is_string( $strElem ) )
				$strElem = trim( $strElem );
			
		}

		return $arrElems;

	}	

	/**
	 * Calculates the relative path from the given path.
	 * 
	 * This function is used to generate a template path.
	 * 
	 * @since			1.3.3.2
	 * @author			Gordon
	 * @see				http://stackoverflow.com/questions/2637945/getting-relative-path-from-absolute-path-in-php/2638272#2638272
	 */
	static public function getRelativePath( $from, $to ) {
		
		// some compatibility fixes for Windows paths
		$from = is_dir($from) ? rtrim($from, '\/') . '/' : $from;
		$to   = is_dir($to)   ? rtrim($to, '\/') . '/'   : $to;
		$from = str_replace('\\', '/', $from);
		$to   = str_replace('\\', '/', $to);

		$from     = explode('/', $from);
		$to       = explode('/', $to);
		$relPath  = $to;

		foreach($from as $depth => $dir) {
			// find first non-matching dir
			if($dir === $to[$depth]) {
				// ignore this directory
				array_shift($relPath);
			} else {
				// get number of remaining dirs to $from
				$remaining = count($from) - $depth;
				if($remaining > 1) {
					// add traversals up to first matching dir
					$padLength = (count($relPath) + $remaining - 1) * -1;
					$relPath = array_pad($relPath, $padLength, '..');
					break;
				} else {
					$relPath[0] = './' . $relPath[0];
				}
			}
		}
		return implode('/', $relPath);
	}	
	
	/**
	 * Retrieves the server set allowed maximum PHP script execution time.
	 * 
	 * @since			1.3.4
	 */
	static public function getAllowedMaxExecutionTime( $iDefault=30, $iMax=120 ) {
		
		$iSetTime = function_exists( 'ini_get' ) && ini_get( 'max_execution_time' ) 
			? ( int ) ini_get( 'max_execution_time' ) 
			: $iDefault;
		
		return $iSetTime > $iMax
			? $iMax
			: $iSetTime;
		
	}
	
	/**
	 * Trims each sub-string element delimited by commas in the given string.
	 * 
	 * Used in form-validation methods.
	 * 
	 * @since			2.3
	 */
	static public function sanitizeCommaDelimitedString( $sInput, $fIncludeWhiteSpace=true ) {
		
		$_aElements =  preg_split( "/[,]\s*/", trim( ( string ) $sInput ), 0, PREG_SPLIT_NO_EMPTY );
		return $fIncludeWhiteSpace
			? implode( ', ', $_aElements )
			: implode( ',', $_aElements )
		;
	}
	
}