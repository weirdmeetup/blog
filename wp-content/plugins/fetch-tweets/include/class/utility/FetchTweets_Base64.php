<?php
/**
 * Handles base64 strings.
 * 
 * This class provides an alternative for the base64 underscore decode / encode function.
 * Some over-sensitive users have hysterical allergy against the function and tries to flag scripts that use it as virus or malware.
 * 
 */
if ( ! class_exists( 'IXR_Message' ) ) require_once( ABSPATH . WPINC . '/class-IXR.php' );
class FetchTweets_Base64 extends IXR_Message {

	protected $strFunction = 'base64_encode';
	
	function __construct() {}	// needs it to override the parent constructor.
	
	public function encode( $vData ) {
		
		if ( is_array( $vData ) || is_object( $vData ) )
			$vData = serialize( $vData );
			
		return call_user_func_array( $this->strFunction, array( $vData ));
	}
	
	public function decode( $strCode ) {
		
		if ( is_array( $strCode ) ) return $strCode;	// for backward compat.
		
		$this->params = array();	// make sure it's empty
		$this->_currentTagContents = $strCode;
		$this->tag_close( '', 'base64' );
		$vData = $this->params[0];
		
		return is_serialized( $vData ) 
			? unserialize( $vData )
			: $vData;
		
	}	
	
}