<?php
if ( ! class_exists( 'TwitterFetchTweetsOAuth' ) )
	require_once( dirname( FetchTweets_Commons::$sPluginPath ) . '/include/library/TwitterOAuth/twitteroauth.php' );

class FetchTweets_TwitterOAuth extends TwitterFetchTweetsOAuth {
	
	public $host = "https://api.twitter.com/1.1/";

	/**
	* Get the authorize URL
	*
	* @returns a string
	* @remark			Modified the original method to add the force_login query key-value pair.
	*/
	function getAuthorizeURL($token, $sign_in_with_twitter = TRUE) {
		if (is_array($token)) {
			$token = $token['oauth_token'];
		}
		if (empty($sign_in_with_twitter)) {
			return $this->authorizeURL() . "?oauth_token={$token}&force_login=true";
		} else {
			return $this->authenticateURL() . "?oauth_token={$token}&force_login=true";
		}
	}	
	
	/**
	* GET wrapper for oAuthRequest.
	* 
	* @remark			Modified the original method to returns the response as an associative array.
	*/
	function get($url, $parameters = array()) {
		$response = $this->oAuthRequest($url, 'GET', $parameters);
		if ($this->format === 'json' && $this->decode_json) {
			return json_decode($response, true);	// return as associative array
		}
		return $response;
	}
  
	/**
	* POST wrapper for oAuthRequest.
	* 
	* @remark			Modified the original method to returns the response as an associative array.
	*/
	function post($url, $parameters = array()) {
		$response = $this->oAuthRequest($url, 'POST', $parameters);
		if ($this->format === 'json' && $this->decode_json) {
			return json_decode($response, true);	// return as associative array
		}
		return $response;
	}

	/**
	* DELETE wrapper for oAuthReqeust.
	*
	* @remark			Modified the original method to returns the response as an associative array.
	*/
	function delete($url, $parameters = array()) {
		$response = $this->oAuthRequest($url, 'DELETE', $parameters);
		if ($this->format === 'json' && $this->decode_json) {
			return json_decode($response, true);	// return as associative array
		}
		return $response;
	}
}