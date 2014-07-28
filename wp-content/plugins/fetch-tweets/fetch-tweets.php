<?php
/* 
	Plugin Name: Fetch Tweets
	Plugin URI: http://en.michaeluno.jp/fetch-tweets
	Description: Fetches and displays tweets from twitter.com with the the Twitter REST API v1.1.
	Author: miunosoft (Michael Uno)
	Author URI: http://michaeluno.jp
	Version: 2.3.4
	Requirements: PHP 5.2.4 or above, WordPress 3.3 or above.
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

include_once( dirname( __FILE__ ). '/include/class/boot/FetchTweets_Bootstrap.php' );
new FetchTweets_Bootstrap( __FILE__ );

include_once( dirname( __FILE__ ). '/include/function/functions.php' );