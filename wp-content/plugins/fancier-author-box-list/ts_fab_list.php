<?php
/**
 * @package Fancier Author Box - List shortcode
 * @version 0.2
 */
/*
Plugin Name: Fancier Author Box - List shortcode
Description: Display lists of user avatars using shortcode with Fancier Author Box.
Author: haruair
Version: 0.2
Author URI: http://haruair.com/
*/

function ts_fab_list_func($atts) {

    $default_atts = array(
        'roles' => 'administrator',
        'hiddenusers' => 'weirdmeetup',
        'show_title' => 'true',
        'column' => 2
    );

    extract( shortcode_atts( $default_atts, $atts ) );

    $hiddenusers = explode(",", $hiddenusers);

    $user_query = new WP_User_Query( array( 'role' => $roles ) );
    
    $users = $user_query->get_results();

    if($show_title == 'true'){
	    $result = '<h3 class="ts-fab-list-title">' . ucfirst($roles) . '</h3>';
	}

    $result .= '<div class="ts-fab-list ts-fab-list-col-'. $column .'">';

    foreach ($users as $user) {
        if( ! in_array($user->user_login, $hiddenusers) ){

            $result .= "<div class='ts-fab-list-item'>";
	            $result .= "<div class='ts-fab-list-social'>" . get_avatar( $user->ID, 80 ) . get_social_meta_by_user($user) . "</div>";
                    $result .= "<div class='ts-fab-list-user'>";
		            $result .= get_username_meta_by_user($user);
		            $result .= get_company_meta_by_user($user);
		            $result .= "<div class='ts-fab-list-desc'>" . $user->user_description . "</div>";
		   $result .= "</div>";
            $result .= "</div>";

        }
    }

    $result .= '</div>';
    return $result;
}

function get_company_meta_by_user($user){

	$result = '';
	if( get_user_meta( $user->ID, 'ts_fab_position', true) ) {
	    $result .= '<div class="ts-fab-list-description"><span>' . get_user_meta( $user->ID, 'ts_fab_position', true) . '</span>';
	    
	    if( get_user_meta( $user->ID, 'ts_fab_company', true) ) {
	        if( get_user_meta( $user->ID, 'ts_fab_company_url', true) ) {
	            $result .= ' ' . __( 'at', 'ts-fab' ) . ' <a href="' . esc_url( get_user_meta( $user->ID, 'ts_fab_company_url', true) ) . '">';
	                $result .= '<span>' . get_user_meta( $user->ID, 'ts_fab_company', true) . '</span>';
	            $result .= '</a>';
	        } else {
	            $result .= ' ' . __( 'at', 'ts-fab' ) . ' <span>' . get_user_meta( $user->ID, 'ts_fab_company', true) . '</span>';
	        }
	    }
	    
	    $result .= '</div>';
	}

	return $result;
}

function get_username_meta_by_user($user){

	$username = '';

    if( $user->user_url ) {
        $username .= '<h4 class="ts-fab-list-username"><a href="' . $user->user_url . '">' . $user->display_name . '</a></h4>';
    } else {
        $username .= '<h4 class="ts-fab-list-username">' . $user->display_name . '</h4>';
    }

    return $username;
    
}

function get_social_meta_by_user($user){


	$social = '<div class="ts-fab-list-social-links ts-fab-social-links">';

	// Twitter
	if( get_user_meta( $user->ID, 'ts_fab_twitter', true) )
	    $social .= '<a href="http://twitter.com/' . get_user_meta( $user->ID, 'ts_fab_twitter', true ) . '" title="Twitter"><img src="' . plugins_url( 'images/twitter.png', __FILE__ ) . '" width="24" height="24" alt="' . __( 'My Twitter profile', 'ts-fab' ) . '" /></a>';

	// Facebook
	if( get_user_meta( $user->ID, 'ts_fab_facebook', true) )
	    $social .= '<a href="http://facebook.com/' . get_user_meta( $user->ID, 'ts_fab_facebook', true ) . '" title="Facebook"><img src="' . plugins_url( 'images/facebook.png', __FILE__ ) . '" width="24" height="24" alt="' . __( 'My Facebook profile', 'ts-fab' ) . '" /></a>';

/*@minieetea*/
	// Github
	if( get_user_meta( $user->ID, 'ts_fab_github', true) )
	    $social .= '<a href="http://github.com/' . get_user_meta( $user->ID, 'ts_fab_github', true ) . '" title="Github Repository"><img src="' . plugins_url( 'http://we.weirdmeetup.com/wp-content/uploads/2013/12/github_icon.png', __FILE__ ) . '" width="24" height="24" alt="' . __( 'My Github Repository', 'ts-fab' ) . '" /></a>';

	// Google+
	if( get_user_meta( $user->ID, 'ts_fab_googleplus', true) )
	    $social .= '<a href="http://plus.google.com/' . get_user_meta( $user->ID, 'ts_fab_googleplus', true ) . '?rel=author" title="Google+"><img src="' . plugins_url( 'images/googleplus.png', __FILE__ ) . '" width="24" height="24" alt="' . __( 'My Google+ profile', 'ts-fab' ) . '" /></a>';

	// LinkedIn
	if( get_user_meta( $user->ID, 'ts_fab_linkedin', true) )
	    $social .= '<a href="http://www.linkedin.com/in/' . get_user_meta( $user->ID, 'ts_fab_linkedin', true ) . '" title="LinkedIn"><img src="' . plugins_url( 'images/linkedin.png', __FILE__ ) . '" width="24" height="24" alt="' . __( 'My LinkedIn profile', 'ts-fab' ) . '" /></a>';

	// Instagram
	if( get_user_meta( $user->ID, 'ts_fab_instagram', true) )
	    $social .= '<a href="http://instagram.com/' . get_user_meta( $user->ID, 'ts_fab_instagram', true ) . '" title="Instagram"><img src="' . plugins_url( 'images/instagram.png', __FILE__ ) . '" width="24" height="24" alt="' . __( 'My Instagram profile', 'ts-fab' ) . '" /></a>';

	// Flickr
	if( get_user_meta( $user->ID, 'ts_fab_flickr', true) )
	    $social .= '<a href="http://www.flickr.com/photos/' . get_user_meta( $user->ID, 'ts_fab_flickr', true ) . '" title="Flickr"><img src="' . plugins_url( 'images/flickr.png', __FILE__ ) . '" width="24" height="24" alt="' . __( 'My Flickr profile', 'ts-fab' ) . '" /></a>';

	// Pinterest
	if( get_user_meta( $user->ID, 'ts_fab_pinterest', true) )
	    $social .= '<a href="http://pinterest.com/' . get_user_meta( $user->ID, 'ts_fab_pinterest', true ) . '" title="Pinterest"><img src="' . plugins_url( 'images/pinterest.png', __FILE__ ) . '" width="24" height="24" alt="' . __( 'My Pinterest profile', 'ts-fab' ) . '" /></a>';

	// Tumblr
	if( get_user_meta( $user->ID, 'ts_fab_tumblr', true) )
	    $social .= '<a href="http://' . get_user_meta( $user->ID, 'ts_fab_tumblr', true ) . '.tumblr.com/" title="Tumblr"><img src="' . plugins_url( 'images/tumblr.png', __FILE__ ) . '" width="24" height="24" alt="' . __( 'My Tumblr blog', 'ts-fab' ) . '" /></a>';

	// YouTube
	if( get_user_meta( $user->ID, 'ts_fab_youtube', true) )
	    $social .= '<a href="http://www.youtube.com/user/' . get_user_meta( $user->ID, 'ts_fab_youtube', true ) . '" title="YouTube"><img src="' . plugins_url( 'images/youtube.png', __FILE__ ) . '" width="24" height="24" alt="' . __( 'My YouTube channel', 'ts-fab' ) . '" /></a>';

	// Vimeo
	if( get_user_meta( $user->ID, 'ts_fab_vimeo', true) )
	    $social .= '<a href="http://vimeo.com/' . get_user_meta( $user->ID, 'ts_fab_vimeo', true ) . '" title="Vimeo"><img src="' . plugins_url( 'images/vimeo.png', __FILE__ ) . '" width="24" height="24" alt="' . __( 'My Vimeo channel', 'ts-fab' ) . '" /></a>';

	$social .= '</div>';

	return $social;
}

function ts_fab_list_add_css(){
	$css_url = plugins_url( 'css/ts-fab-list.css', __FILE__ );
	wp_register_style( 'ts_fab_list_css', $css_url, '', '1.0' );
	wp_enqueue_style( 'ts_fab_list_css' );
}

add_shortcode( 'ts_fab_list', 'ts_fab_list_func' );
add_action( 'wp_enqueue_scripts', 'ts_fab_list_add_css' );