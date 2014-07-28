<?php
/**
 * @package weiredmeetup
 * @version 1.0
 */
/*
Plugin Name: Feedwordpress for Tistory
Description: 티스토리에서 퍼온 글이 깨지는 현상을 방지
Author: haruair
Version: 1.0
Author URI: http://haruair.com
*/

function tistory_feed_fix($content) {
		$content = str_replace('<div class="entry-ccl" style="clear: both; text-align: right; margin-bottom: 10px">','',$content);
		$content .= "<!-- weirdmeetup -->";
       return $content;
}
add_filter('the_content', 'tistory_feed_fix', 10);
