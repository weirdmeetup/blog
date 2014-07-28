<?php
/*
 * User functions - users may use them in their templates.
 * */
function fetchTweets( $aArgs, $bEcho=true ) {
	
	$_sOutput = '';
	if ( ! class_exists( 'FetchTweets_Fetch' ) ) {
		$_sOutput = __( 'The class has not been loaded yet. Use this function after the <code>plugins_loaded</code> hook.', 'fetch-tweets' );
		if ( $bEcho ) {
			echo $_sOutput;
		} else {
			return $_sOutput;
		}
	}
	
	$_oFetch = new FetchTweets_Fetch();
	if ( isset( $aArgs['id'] ) || isset( $aArgs['ids'] ) || isset( $aArgs['q'] ) || isset( $aArgs['screen_name'] ) ) {
		$_sOutput = $_oFetch->getTweetsOutput( $aArgs );
	} else if ( isset( $aArgs['tag'] ) || isset( $aArgs['tags'] ) ) {
		$_sOutput = $_oFetch->getTweetsOutputByTag( $aArgs );
	}

	if ( $bEcho ) {
		echo $_sOutput;
	} else {
		return $_sOutput;
	}
		
}

/**
 * Returns the image url of the specified size.
 * 
 * The image URL format is as bellows.
 * 	original:	http(s)://pbs.twimg.com/profile_images/.../[...].jpg
 * 	73px by 73px: http(s)://pbs.twimg.com/profile_images/.../[...]_bigger.jpg
 *	48px by 48px: http(s)://pbs.twimg.com/profile_images/.../[...]_normal.jpg 
 *	24px by 24px: http(s)://pbs.twimg.com/profile_images/.../[...]_mini.jpg
 * @see			https://dev.twitter.com/docs/api/1/get/users/profile_image/%3Ascreen_name
 * @see			https://dev.twitter.com/docs/user-profile-images-and-banners
 * @remark		Assume this function is called in loops of tweet element outputs in templates.
 * @since		2.2.1
 */
if ( ! function_exists( 'getTwitterProfileImageURLBySize' ) ) :
function getTwitterProfileImageURLBySize( $sProfileImageURLNormal, $iImageSize ) {
		
	// Parts
	$_aURLParts = parse_url( $sProfileImageURLNormal );
	$_aPathParts = pathinfo( $_aURLParts['path'] ) + array( 'extension' => null );

	// Path
	$_sPathPartWOFileName = preg_replace( '/[^\/]*$/', '', $_aURLParts['path'] );	// remove sub-string after the last slash
	
	// File name without extension.
	$_sFileNameWOExt = preg_replace( '/_[^_]*$/', '', $_aPathParts['filename'] );	// remove sub-string after the last underscore including the underscore.
	
	// File name suffix
	$_sSuffix = '';
	if ( $iImageSize <= 24 ) {
		$_sSuffix = '_mini';
	} else if ( $iImageSize <= 48 ) {
		$_sSuffix = '_normal';
	} else if ( $iImageSize <= 73 ) {
		$_sSuffix = '_bigger';
	} 
	if ( ! $_aPathParts['extension'] ) {
		$_sSuffix = '';
	}
	
	// Result
	return $_aURLParts['scheme'] . "://"
		. $_aURLParts['host']
		. $_sPathPartWOFileName
		. $_sFileNameWOExt
		. $_sSuffix
		. ( $_aPathParts['extension'] ? '.' . $_aPathParts['extension'] : '' );
		
}
endif;