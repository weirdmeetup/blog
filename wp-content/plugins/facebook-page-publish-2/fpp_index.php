<?php
	/**
		* Facebook Page Publish 2 - publishes your blog posts to your fan page.
		* Copyright (C) 2012 Dean Williams, Copyright (C) 2011 Martin Tschirsich
		* 
		* This program is free software: you can redistribute it and/or modify
		* it under the terms of the GNU General Public License as published by
		* the Free Software Foundation, either version 3 of the License, or
		* (at your option) any later version.
		* 
		* This program is distributed in the hope that it will be useful,
		* but WITHOUT ANY WARRANTY; without even the implied warranty of
		* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
		* GNU General Public License for more details.
		* 
		* You should have received a copy of the GNU General Public License
		* along with this program. If not, see <http://www.gnu.org/licenses/>.
		* 
		* 
		* Plugin Name: Facebook Page Publish 2
		* Plugin URI:  http://wordpress.org/extend/plugins/facebook-page-publish-2/
		* Description: Publishes your posts on the wall of a Facebook profile or page.
		* Author:      Dean Williams, Martin Tschirsich
		* Version:     0.4.1
		* Author URI:  http://software.resplace.net/WordPress/facebook-page-publish
	*/
	
	/**********************************************************************
		* Constants
	**********************************************************************/
	
	//get plugin version
	if(function_exists('get_plugin_data')) {
		define('FPP_VERSION', FFP_version());
	}
	define('FPP_BASE_DIR', dirname(__file__));
	define('FPP_BASE_URL', WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__), '', plugin_basename(__FILE__)));
	define('FPP_ADMIN_URL', admin_url('admin.php?page='.urlencode(plugin_basename(__FILE__))));
	define('FPP_DEFAULT_POST_TO_FACEBOOK', false); // Checkbox admin panel always preselected
	define('FPP_FACEBOOK_LINK_DESCRIPTION_MAX_LENGTH', 340); // Facebook link description allows max. 420 characters, 340 are always shown
	define('FPP_REQUEST_TIMEOUT', 20); // The default 5s are not sufficient on some servers
	define('FPP_TEXT_DOMAIN', 'facebook-page-publish');
	
	/**********************************************************************
		* Exceptions
	**********************************************************************/
	class CommunicationException extends Exception {
        public function __construct($message = null, $code = 0) {
			if (!empty($message)) $message .= '<br />';
			$message .= '[Error occured at line '.$this->getLine().']';
			parent::__construct($message, $code);
		}
	}
	
	class FacebookUnreachableException extends CommunicationException {
        public function __construct($message = null, $code = 0) {
			if (empty($message)) {
				$message = __('Facebook is not reachable from your server.', FPP_TEXT_DOMAIN).' <a target="_blank" href="'.FPP_BASE_URL.'diagnosis.php">'.__('Check the connection!', FPP_TEXT_DOMAIN).'</a>';
                } else {
				$message = sprintf(__('Facebook is not reachable from your server: %s', FPP_TEXT_DOMAIN), $message).'<br /><a target="_blank" href="'.FPP_BASE_URL.'diagnosis.php">'.__('Check your connection!', FPP_TEXT_DOMAIN).'</a>';
			}
			parent::__construct($message, $code);
		}
	}
	
	class FacebookErrorException extends CommunicationException {
        public function __construct($message, $code = 0) {
			$message = sprintf(__('Facebook returned an error: %s', FPP_TEXT_DOMAIN), $message);
			parent::__construct($message, $code);
		}
	}
	
	class FacebookUnexpectedErrorException extends CommunicationException {
        public function __construct($message = null, $code = 0) {
			if (empty($message)) {
				$message = __('Facebook returned an unexpected error.', FPP_TEXT_DOMAIN).' '.__('Try to resolve this issue, update the plugin or <a target="_blank" href="http://wordpress.org/tags/facebook-page-publish">inform the author</a> about the problem.', FPP_TEXT_DOMAIN);
                } else {
				$message = sprintf(__('Facebook returned an unexpected error: %s', FPP_TEXT_DOMAIN), $message).'<br />'.__('Try to resolve this issue, update the plugin or <a target="_blank" href="http://wordpress.org/tags/facebook-page-publish">inform the author</a> about the problem.', FPP_TEXT_DOMAIN);
			}
			parent::__construct($message, $code);
		}
	}
	
	class FacebookUnexpectedDataException extends CommunicationException {
        public function __construct($message = null, $code = 0) {
			if (empty($message)) {
				$message = __('Facebook returned an unexpected dataset.', FPP_TEXT_DOMAIN).' '.__('Try to resolve this issue, update the plugin or <a target="_blank" href="http://wordpress.org/tags/facebook-page-publish">inform the author</a> about the problem.', FPP_TEXT_DOMAIN);
                } else {
				$message = sprintf(__('Facebook returned an unexpected dataset: %s', FPP_TEXT_DOMAIN), $message).'<br />'.__('Try to resolve this issue, update the plugin or <a target="_blank" href="http://wordpress.org/tags/facebook-page-publish">inform the author</a> about the problem.', FPP_TEXT_DOMAIN);
			}
			parent::__construct($message, $code);
		}
	}
	
	/**********************************************************************
		* Action handler
	**********************************************************************/
	
	# Add publish actions for all post types (posts, pages, attachements, custom post types):
	$post_types = get_post_types(array('exclude_from_search' => false), 'objects');
	foreach ($post_types as $post_type) {
        add_action('future_'.$post_type->name, 'fpp_future_action');
        add_action('publish_'.$post_type->name, 'fpp_publish_action');
	}
	
	add_action('admin_init', 'fpp_admin_init_action');
	add_action('admin_menu', 'fpp_admin_menu_action');
	add_action('wp_head', 'fpp_head_action');
	add_action('post_submitbox_start', 'fpp_post_submitbox_start_action');
	add_action('init', 'fpp_init_action');
	
	function fpp_init_action() {
        //global $locale; $locale='de_DE';
        load_plugin_textdomain(FPP_TEXT_DOMAIN, false, dirname(plugin_basename(__FILE__)));
	}
	
	/**
		* Called on html head rendering. Prints meta tags to make posts appear
		* correctly in Facebook. 
	*/
	function fpp_head_action() {
        global $post;
		
        if (is_object($post) /*&& ($post->post_type == 'post') */ && is_singular()) {
			fpp_render_meta_tags($post);
		}
	}
	
	/**
		* Called on admin menu rendering, adds an options page and its
		* rendering callback.
		*
		* @see fpp_render_options_page()
	*/
	function fpp_admin_menu_action() {
        $page = add_options_page(__('Facebook Page Publish Options', FPP_TEXT_DOMAIN), __('Facebook Page Publish', FPP_TEXT_DOMAIN), 'manage_options', __FILE__, 'fpp_render_options_page');
        
        add_action('admin_print_scripts-'.$page, 'fpp_admin_print_scripts_action');
        add_action('admin_print_styles-'.$page, 'fpp_admin_print_styles_action');
	}
	
	/**
		* Called when a user accesses the admin area. Registers settings and a
		* sanitization callback. Initializes the plugin options when a version
		* update has been detected.
		*
		* @see fpp_validate_options
	*/
	function fpp_admin_init_action() {
        register_setting('fpp_options_group', 'fpp_options', 'fpp_validate_options');
        
        $version = get_option('fpp_installed_version');
        if ($version != FPP_VERSION) {
			fpp_initialize_options();
		}
	}
	
	
	/**
		* Loads media gallery scripts.
		*
		* @last_review 0.3.0
	*/
	function fpp_admin_print_scripts_action() {
        wp_enqueue_script('media-upload');
        wp_enqueue_script('thickbox');
        wp_register_script('fpp-upload', FPP_BASE_URL.'fpp_script.js', array('jquery','media-upload','thickbox'));
        wp_enqueue_script('fpp-upload');
	}
	
	/**
		* Loads media gallery styles.
		*
		* @last_review 0.3.0
	*/
	function fpp_admin_print_styles_action() {
        wp_enqueue_style('thickbox');
	}
	
	/**
		* Called when the submitbox is rendered. Renders a publish to Facebook
		* button if the current user has the 'publish_posts' permission.
		*
		* @last_review 0.3.0
	*/
	function fpp_post_submitbox_start_action() {
        global $post;
		
        if (is_object($post) and /*($post->post_type == 'post') and*/ current_user_can('publish_posts')) {
			fpp_render_post_button();
		}
	}
	
	/**
		* Called on any_to_future post state transition (scheduled post).
		* Marks any given post for publishing on Facebook
		* - if it was send directly from the admin panel and the 
		*   post-to-facebook checkbox was checked.
		* Marks any given post for NOT publishing on Facebook
		* - if it was send directly from the admin panel and the
		*   post-to-facebook checkbox was NOT checked.
		*
		* @last_review 0.3.0
	*/
	function fpp_future_action($post_id) {
        $send_from_admin = isset($_REQUEST['fpp_send_from_admin']);
        
        if ($send_from_admin) {
			$post = get_post($post_id);
			if (/*($post->post_type == 'post')*/ true) {
				if (!empty($_REQUEST['fpp_post_to_facebook'])) { // Directly send from the user (admin panel) with active checkbox
					$options = get_option('fpp_options');
					update_post_meta($post->ID, '_fpp_is_scheduled', true);
					} else {
					update_post_meta($post->ID, '_fpp_is_scheduled', false);
				}
			}
		}
	}
	
	/**
		* Called on any_to_publish post state transition (published post).
		* Publishes any given non password protected post to Facebook
		* - if it was send directly from the admin panel and the 
		*   post-to-facebook checkbox was checked.
		* - if it was previously marked for publishing on Facebook (scheduled).
		* - if it was NOT send directly from the admin panel and the plugin
		*   is set to always post on Facebook and the post is not already
		*   published (on wordpress or Facebook).
		*
		* @see fpp_render_post_button()
	*/
	function fpp_publish_action($post_id) {
		$post = get_post($post_id);
		
		
		$object_access_token = get_option('fpp_object_access_token');
		if (!empty($object_access_token)) { // Incomplete plugin configuration, do nothing, report no error
			
			$send_from_admin = isset($_REQUEST['fpp_send_from_admin']);
			unset($_REQUEST['fpp_send_from_admin']); // Sometimes fpp_publish_action is called twice in a row
			
			$options = get_option('fpp_options');
			try {
				if ($send_from_admin && !empty($_REQUEST['fpp_post_to_facebook'])) { // Directly send from the user (admin panel) with active post checkbox
					// If post was already published, delete it from FB:
					$post_id = get_post_meta($post->ID, '_fpp_post_id', true);
					if (!empty($post_id)) { // Old posts (version <= 0.3.7) do not contain a post_id 
						fpp_unpublish_from_facebook($post_id, $options['object_id'], get_option('fpp_object_access_token'));
					}
					
					$post_id = fpp_publish_to_facebook($post, $options['object_id'], get_option('fpp_object_access_token'));
					update_post_meta($post->ID, '_fpp_is_published', true);
					update_post_meta($post->ID, '_fpp_post_id', $post_id);
				}
				else if (get_post_meta($post->ID, '_fpp_is_scheduled', true) == true) { // Scheduled post previously marked for Facebook publishing by the user
					// If post was already published, delete it from FB:
					$post_id = get_post_meta($post->ID, '_fpp_post_id', true);
					if (!empty($post_id)) { // Old posts (version <= 0.3.7) do not contain a post_id 
						fpp_unpublish_from_facebook($post_id, $options['object_id'], get_option('fpp_object_access_token'));
					}
					
					$post_id = fpp_publish_to_facebook($post, $options['object_id'], get_option('fpp_object_access_token'));
					update_post_meta($post->ID, '_fpp_is_published', true);
					delete_post_meta($post->ID, '_fpp_is_scheduled');
					update_post_meta($post->ID, '_fpp_post_id', $post_id);
				}
				else if (!$send_from_admin && (array_search('_fpp_is_scheduled', get_post_custom_keys($post->ID)) === false) && !get_post_meta($post->ID, '_fpp_is_published', true) && ($_POST['original_post_status'] != 'publish')) { // not send from admin panel, not already published (FB and WP), user never decided for or against publishing
					if (empty($post->post_password) and fpp_get_default_publishing($post)) { // Dont post password protected posts without the users consient
						// If post was already published, delete it from FB:
						$post_id = get_post_meta($post->ID, '_fpp_post_id', true);
						if (!empty($post_id)) { // Old posts (version <= 0.3.7) do not contain a post_id 
							fpp_unpublish_from_facebook($post_id, $options['object_id'], get_option('fpp_object_access_token'));
						}
						
						$post_id = fpp_publish_to_facebook($post, $options['object_id'], get_option('fpp_object_access_token'));
						update_post_meta($post->ID, '_fpp_is_published', true);
						update_post_meta($post->ID, '_fpp_post_id', $post_id);
					}
				}
				} catch (CommunicationException $exception) {
				update_option('fpp_error', sprintf(__('<p>While publishing "%1$s" to Facebook, an error occured: </p><p><strong>%2$s</strong></p>', FPP_TEXT_DOMAIN), $post->post_title, $exception->getMessage()));
			}
		}
		
	}
	
	/**********************************************************************
		* Facebook functions
	**********************************************************************/
	/**
		* Publishes the given post to a Facebook page.
		* 
		* @param post Wordpress post to publish
		* @param object_id Facebook page or wall ID
		* @param object_acces_token Access token for the given object
		* @return Facebook(!) post id
		*
		* @last_review 0.3.8
	*/
	function fpp_publish_to_facebook($post, $object_id, $object_acces_token) {
		$options = get_option('fpp_options');
		
		if (empty($post->post_password) and $options['show_post_text']) {
			$message = fpp_extract_message(apply_filters('the_excerpt', $post->post_excerpt), $options['show_post_link']);
			if (empty($message)) $message = fpp_extract_message(apply_filters('the_content', $post->post_content), $options['show_post_link']);
			
			if ($options['show_post_link']) {
				$message = wp_kses($message, array('a' => array('href' => array())));
				$message = preg_replace('/<a[^>]*?href *= *["|\']([^"|\']+).[^>]*?>(.*?)<\/a>\s*/is', '${2} ${1} ', $message);
				$message = trim(stripslashes(html_entity_decode($message, ENT_QUOTES, 'UTF-8')));
			}
			else {
				$message = trim(stripslashes(html_entity_decode(wp_filter_nohtml_kses($message), ENT_QUOTES, 'UTF-8')));
			}
			
			// Remove empty lines:
			$message = preg_replace("/\n\s*\n(\n|\s)*/s", "\n",$message);
			
			if (strpos($message, '<!--more-->') !== false) {
				$message = substr($message, 0, strpos($message, '<!--more-->'));
			}
			
			// Remove HTML Comments:
			$message = preg_replace('/<!--(.*)-->/Uis', '', $message);
			
			if (strlen($message) >= FPP_FACEBOOK_LINK_DESCRIPTION_MAX_LENGTH) {
				$last_space_pos = strrpos(substr($message, 0, FPP_FACEBOOK_LINK_DESCRIPTION_MAX_LENGTH - 3), ' ');
				$message = substr($message, 0, !empty($last_space_pos) ? $last_space_pos : FPP_FACEBOOK_LINK_DESCRIPTION_MAX_LENGTH - 3).__('...', FPP_TEXT_DOMAIN);
			}
			} else {
			$message = ''; // Password protected, no content displayed.
		}
		
		// Publish:
		$request = new WP_Http;
		
		// Facebook bug 19336 is resolved, so /links should work (usage of /feed no longer necessary):
		$api_url = 'https://graph.facebook.com/'.urlencode($object_id).'/links';
		
		$body = array('message' => $message, 'link' => get_permalink($post->ID), 'access_token' => $object_acces_token);
		$response = $request->request($api_url, array('method' => 'POST', 'body' => $body, 'timeout' => FPP_REQUEST_TIMEOUT, 'sslverify' => fpp_get_ssl_verify())); 
		
		// Error detection:
		if (array_key_exists('errors', $response))
		throw new FacebookUnreachableException(!empty($response->errors) ? array_pop(array_pop($response->errors)) : '');
		
		$json_response = json_decode($response['body']);
		if (is_object($json_response) and property_exists($json_response, 'error')) {
			throw new FacebookUnexpectedErrorException((is_object($json_response->error) and property_exists($json_response->error, 'message')) ? $json_response->error->message : '');
		}
		
		return $json_response->id; // Return ID of the published link (usefull to delete it later).
	}
	
	/**
		* Extract a message to be posted on Facebook from a text string that
		* may contain html, WP shortcodes etc.
		*
		* @param string 
		* @param include_links Include URLs from HTML links if true
		* @return extracted message ready to be posted on Facebook
	*/
	function fpp_extract_message($string, $include_links) {
		$message = do_shortcode($string);
		
		$message = preg_replace('~<\s*\bscript\b[^>]*>(.*?)<\s*\/\s*script\s*>~is', '', $message); // Remove javascript code (not only tags)
		
		$message = preg_replace('/<!--(.*)-->/Uis', '', $message); // Remove HTML comments (not only tags)
		
		if ($include_links) {
			$message = wp_kses($message, array('a' => array('href' => array())));
			$message = preg_replace('/<a[^>]*?href *= *["|\']([^"|\']+).[^>]*?>(.*?)<\/a>\s*/is', '${2} ${1} ', $message);
			$message = trim(stripslashes(html_entity_decode($message, ENT_QUOTES, 'UTF-8')));
		}
		else {
			$message = trim(stripslashes(html_entity_decode(wp_filter_nohtml_kses($message), ENT_QUOTES, 'UTF-8')));
		}
		
		// Remove empty lines:
		$message = preg_replace("/\n\s*\n(\n|\s)*/s", "\n",$message);
		
		if (strpos($message, '<!--more-->') !== false) {
			$message = substr($message, 0, strpos($message, '<!--more-->'));
		}
		
		if (strlen($message) >= FPP_FACEBOOK_LINK_DESCRIPTION_MAX_LENGTH) {
			$last_space_pos = strrpos(substr($message, 0, FPP_FACEBOOK_LINK_DESCRIPTION_MAX_LENGTH - 3), ' ');
			$message = substr($message, 0, !empty($last_space_pos) ? $last_space_pos : FPP_FACEBOOK_LINK_DESCRIPTION_MAX_LENGTH - 3).__('...', FPP_TEXT_DOMAIN);
		}
		
		return $message;
	}
	
	/**
		* Removes the given post from a Facebook page. If a post with the given
		* ID does not exist on FB, the return value is 'false.
		* 
		* @param post_id Facebook(!) post ID
		* @param object_id Facebook page or wall ID
		* @param object_acces_token Access token for the given object
		* @return True if post was found and removed
		*
		* @last_review 0.3.8
	*/
	function fpp_unpublish_from_facebook($post_id, $object_id, $object_acces_token) {
		$request = new WP_Http;
		$api_url = 'https://graph.facebook.com/'.urlencode($object_id).'_'.urlencode($post_id).'?method=delete';
		$body = array('access_token' => $object_acces_token);
		$response = $request->request($api_url, array('method' => 'POST', 'body' => $body, 'timeout' => FPP_REQUEST_TIMEOUT, 'sslverify' => fpp_get_ssl_verify())); 
		
		// Error detection:
		if (array_key_exists('errors', $response))
		throw new FacebookUnreachableException(!empty($response->errors) ? array_pop(array_pop($response->errors)) : '');
		
		$object = json_decode($response['body']);
		
		if (is_object($object) and property_exists($object, 'error')) {
			if (property_exists($object->error, 'message')) {
				if (strpos($object->error->message, 'Invalid parameter') !== false)
				return false;
				
				throw new FacebookUnexpectedErrorException($object->error->message);
			}
			throw new FacebookUnexpectedErrorException('');
		}
		
		return $response['body'] === 'true';
	}
	
	/**
		* Checks whether a given access_token is valid and has the give 
		* permissions.
		*
		* @param object_id Page or profile ID
		* @param object_type Either 'page' or 'profile'
		* @param object_access_token The access token to validate
		* @param permissions Array of permission strings to validate
		* @return True if access_token valid and all permissions granted
		*
		* @last_review 0.3.1
	*/
	function fpp_verify_profile_access_permissions($profile_access_token, $permissions) {
		$request = new WP_Http;
		$api_url =  'https://api.facebook.com/method/fql.query?access_token='.urlencode($profile_access_token).'&format=json&query='.urlencode('SELECT '.implode(',', $permissions).' FROM permissions WHERE uid = me()'); 
		$response = $request->get($api_url, array('timeout' => FPP_REQUEST_TIMEOUT, 'sslverify' => fpp_get_ssl_verify()));
		
		if (array_key_exists('errors', $response))
		throw new FacebookUnreachableException((!empty($response->errors) ? array_pop(array_pop($response->errors)) : ''));
		
		$json_response = json_decode($response['body']);
		if (is_object($json_response) and property_exists($json_response, 'error_msg')) {
			if (property_exists($json_response, 'error_code') and ($json_response->error_code == 190)) // Access token expired or invalid
			return false;
			if (property_exists($json_response, 'error_code') and ($json_response->error_code == 104)) // 'Requires valid signature'-error
			return false;
			throw new FacebookUnexpectedErrorException($json_response->error_msg);
		}
		if (!is_array($json_response)) {
			throw new FacebookUnexpectedDataException();
		}
		$json_response = array_pop($json_response);
		if (!is_object($json_response)) {
			throw new FacebookUnexpectedDataException();
		}
		
		foreach ($permissions as $permission) {
			if (!property_exists($json_response, $permission) or empty($json_response->$permission)) return false;
		}
		return true;
	}
	
	function fpp_verify_page_access_token($page_id, $page_access_token) {
		$request = new WP_Http;
		$api_url = 'https://graph.facebook.com/'.urlencode($page_id).'?access_token='.urlencode($page_access_token);
		$response = $request->get($api_url, array('timeout' => FPP_REQUEST_TIMEOUT, 'sslverify' => fpp_get_ssl_verify()));
		
		if (array_key_exists('errors', $response))
		throw new FacebookUnreachableException(!empty($response->errors) ? array_pop(array_pop($response->errors)) : '');
		
		# TODO: verify access_permissions...
		
		return ($response['response']['code'] == 200); 
	}
	
	/**
		* Classifies Facebook object ids.
		*
		* @param object_ids Array of Facebook object ids
		* @return map with a type string for each Facebook object id
		* 
		* @last_review 0.3.0
	*/
	function fpp_classify_facebook_objects($object_ids) {
		$numerical_object_ids = array_filter($object_ids, 'is_numeric'); // Alphabetical id's produce a Facebook error.
		
		$request = new WP_Http;
		$api_url = 'https://api.facebook.com/method/fql.query?format=json&query='.urlencode('SELECT id, type FROM object_url WHERE id IN ('.implode(',', $numerical_object_ids).')'); 
		$response = $request->get($api_url, array('timeout' => FPP_REQUEST_TIMEOUT, 'sslverify' => fpp_get_ssl_verify()));
		
		if (array_key_exists('errors', $response))
		throw new FacebookUnreachableException(!empty($response->errors) ? array_pop(array_pop($response->errors)) : '');
		
		$json_response = json_decode($response['body']);
		if (is_object($json_response) and property_exists($json_response, 'error')) {
			throw new FacebookUnexpectedErrorException((is_object($json_response->error) and property_exists($json_response->error, 'message')) ? $json_response->error->message : '');
		}
		if (!is_array($json_response)) {
			throw new FacebookUnexpectedDataException();
		}
		
		$result = array();
		$float_object_ids = array_map('floatval', $numerical_object_ids); // PHP <= 5.2 is missing JSON_BIGINT
		foreach ($json_response as $json_response_entry) {
			if (!property_exists($json_response_entry, 'type') or !property_exists($json_response_entry, 'id'))
			throw new FacebookUnexpectedDataException();
			$result[$numerical_object_ids[array_search($json_response_entry->id, $float_object_ids)]] = $json_response_entry->type;
		}
		
		foreach ($object_ids as $object_id) {
			if (!array_key_exists($object_id, $result))
			$result[$object_id]= '';
		}
		
		return $result;
	}
	
	/**
		* Checks whether a given Facebook application id and its secret are
		* valid.
		*
		* @param app_id Application id to verify
		* @param app_secret Application secret
		* @param redirect_uri URL equal to the URL in the Facebook app settings
		* @return True if the given application id and secret are valid
		*
		* @last_review 0.3.0
	*/
	function fpp_is_valid_facebook_application($app_id, $app_secret, $redirect_uri) {
		$request = new WP_Http;
		$api_url = 'https://graph.facebook.com/oauth/access_token?client_id='.urlencode($app_id).'&client_secret='.urlencode($app_secret).'&redirect_uri='.urlencode($redirect_uri).'&code=SOME_INVALID_CODE';
		$response = $request->get($api_url, array('timeout' => FPP_REQUEST_TIMEOUT, 'sslverify' => fpp_get_ssl_verify()));
		
		if (array_key_exists('errors', $response))
		throw new FacebookUnreachableException(!empty($response->errors) ? array_pop(array_pop($response->errors)) : '');
		
		$object = json_decode($response['body']);
		
		if (property_exists($object, 'error')) {
			if (property_exists($object->error, 'message')) {
				if (strpos($object->error->message, 'Error validating client secret') !== false)
				return false;
				
				if (strpos($object->error->message, 'Invalid verification code format') !== false)
				return true;
				
				if (strpos($object->error->message, 'Invalid redirect_uri') !== false)  
				throw new FacebookErrorException(sprintf(__('The site URL in your Facebook application settings does not match your wordpress blog URL. Please refer to the <a target="_blank" href="%s">detailed setup instructions</a>.', FPP_TEXT_DOMAIN), FPP_BASE_URL.'setup.htm#site_url'));
				
				throw new FacebookUnexpectedErrorException($object->error->message);
			}
			throw new FacebookUnexpectedErrorException();
		}
		throw new FacebookUnexpectedDataException();
	}
	
	/**
		* Acquires an object access token with all these permissions that
		* were specified when retrieving the code.
		*
		* @param app_id Application ID
		* @param app_secret Application secret
		* @param object_id Facebook page or profile ID
		* @param object_type Either 'profile' or 'page'
		* @param redirect_uri URL used to get the transaction code
		* @param code Transaction code (refer to the OAuth protocoll docs)
		*
		* @last_rewiev 0.3.1
	*/
	function fpp_acquire_profile_access_token($app_id, $app_secret, $redirect_uri, $code) {
		$request = new WP_Http;
		$api_url = 'https://graph.facebook.com/oauth/access_token?client_id='.urlencode($app_id).'&redirect_uri='.urlencode($redirect_uri).'&client_secret='.urlencode($app_secret).'&code='.urlencode($code);
		$response = $request->get($api_url, array('timeout' => FPP_REQUEST_TIMEOUT, 'sslverify' => fpp_get_ssl_verify()));
		
		if (array_key_exists('errors', $response))
		throw new FacebookUnreachableException(!empty($response->errors) ? array_pop(array_pop($response->errors)) : '');
		
		$json_response = json_decode($response['body']);
		if (is_object($json_response) and property_exists($json_response, 'error') and property_exists($json_response->error, 'message')) {
			if (is_string($json_response->error->message) and (strpos($json_response->error->message, 'Code was invalid or expired') !== false)) {
				throw new FacebookErrorException(__('Your authorization code was invalid or expired. Please try again. If the problem persists update the plugin or <a target="_blank" href="http://wordpress.org/tags/facebook-page-publish">inform the author</a>.', FPP_TEXT_DOMAIN));
			}
			else throw new FacebookUnexpectedErrorException($json_response->error->message);
		}
		$access_token_url = $response['body'];
		
		preg_match('/access_token=(.*)&expires/', $access_token_url, $matches);
		
		if (!empty($matches[1])) return $matches[1];
		
		throw new FacebookUnexpectedDataException();
	}
	
	function fpp_acquire_page_access_token($page_id, $profile_access_token) {
		$request = new WP_Http;
		$api_url = 'https://graph.facebook.com/me/accounts?access_token='.urlencode($profile_access_token);
		$response = $request->get($api_url, array('timeout' => FPP_REQUEST_TIMEOUT, 'sslverify' => fpp_get_ssl_verify()));
		
		if (array_key_exists('errors', $response))
		throw new FacebookUnreachableException(!empty($response->errors) ? array_pop(array_pop($response->errors)): '');
		
		$json_response = json_decode($response['body']);
		if (!is_object($json_response) || !property_exists($json_response, 'data'))
		throw new FacebookUnexpectedErrorException(__('Can\'t access Facebook user account information.', FPP_TEXT_DOMAIN));
		
		foreach ($json_response->data as $account) {
			if ($account->id == $page_id) {
				if (!property_exists($account, 'access_token'))
				throw new FacebookUnexpectedErrorException(__('Some or all access permissions for your page are missing.', FPP_TEXT_DOMAIN));
				$page_access_token = $account->access_token;
				break;
			}
		}
		if (!isset($page_access_token))
		throw new FacebookErrorException(__('Your Facebook user account data contains no page with the given ID. You have to be administrator of the given page.', FPP_TEXT_DOMAIN));
		
		return $page_access_token;
	}
	
	/**********************************************************************
		* Getter
	**********************************************************************/
	/**
		* Determines whether a post should be pulished by default or not, 
		* depending on the plugin options and the post category.
	*/
	function fpp_get_default_publishing($post) {
		$options = get_option('fpp_options');
		
		if ($options['default_publishing'] == 'all') return true;
		if ($options['default_publishing'] == 'category') {
			$categories = get_the_category($post->ID);
			foreach ($categories as $category) {
				if (array_search($category->cat_ID, $options['default_publishing_categories']) !== false)
				return true;
			}
		}
		return false;
	}
	
	/**
		* @return True if SSL certificates should be checked
	*/
	function fpp_get_ssl_verify() {
		$options = get_option('fpp_options');
		return !$options['ignore_ssl'];
	}
	
	/**
		* @param object_type Array of string or string, either 'page' or 'profile'
		* @return Write permissions for a given Facebook object as string
		* @last_review 0.3.1
	*/
	function fpp_get_required_permissions($object_type) {
		if (!is_array($object_type)) $object_type = array($object_type);
		
		$permissions = array('share_item');
		if (array_search('page', $object_type) !== false) {
			$permissions[] = 'manage_pages';
		} 
		if (array_search('group', $object_type) !== false) {
			$permissions[] = 'user_groups';
		}
		
		return $permissions;
	}
	
	/**
		* @return Post author name as a string
		* @last_review 0.3.1
	*/
	function fpp_get_post_author($post) {
		$user_info = get_userdata($post->post_author);
		$author = trim(apply_filters('the_author', $user_info->display_name));
		if (empty($author)) {
			$author = $user_info->user_login;
		}
		return $author;
	}
	
	/**
		* @return Post categories as csv string
		* @last_review 0.3.0
	*/
	function fpp_get_post_categories($post) {
		$categories = get_the_category($post->ID);
		$description = '';
		if (!empty($categories)) {
			$description = $categories[0]->cat_name;
			for ($i = 1; $i < sizeof($categories); ++$i)
			$description .= ', '.apply_filters('the_category', $categories[$i]->cat_name);
		}
		return $description;
	}
	
	/**
		* @return URL of the featured or first embedded or attachement image as string
		* @last_review 0.3.1
	*/
	function fpp_get_post_image($post) {
		$image_url = '';
		
		if (current_theme_supports('post-thumbnails')) { // get_post_thumbnail_id must be supported by the theme!
			$thumbnail_id = get_post_thumbnail_id($post->ID);
			if ($thumbnail_id !== null) {
				$image_url = wp_get_attachment_image_src($thumbnail_id);
				$image_url = $image_url[0];
			}
		}
		
		if (empty($image_url)) { // Image from post content and/or excerpt
			preg_match('/<img .*?src=["|\']([^"|\']+)/i', $post->post_excerpt.$post->post_content, $matches);
			if (!empty($matches[1])) $image_url = $matches[1];
		}
		
		if (empty($image_url)) {
			$images = get_children('post_type=attachment&post_mime_type=image&post_parent='.$post->ID);
			if (!empty($images)) {
				foreach ($images as $image_id => $value) {
					$image = wp_get_attachment_image_src($image_id);
					$image_url = $image[0];
					break;
				}
			}
		}
		return $image_url;
	}
	
	/**********************************************************************
		* HTML rendering
	**********************************************************************/
	/**
		* Renders the options page. Uses the settings API (options validation, checking and storing by WP).
		* Also validates certain options (Facebook access) that need redirecting.
		* @last_review 0.3.1
	*/
	function fpp_render_options_page() {
		$options = get_option('fpp_options');
		
		$error = get_option('fpp_error');
		if (!empty($error)) {
			echo '<div class="error">'.$error.'</div>';  
			update_option('fpp_error', '');
		}
		
		$profile_access_token = get_option('fpp_profile_access_token');
		
		if ($options['app_id_valid'] and $options['app_secret_valid'] and empty($profile_access_token) and array_key_exists('code', $_GET)) {
			// User clicked the authorize button, get profile_access_token:
			try {
				$profile_access_token = fpp_acquire_profile_access_token($options['app_id'], $options['app_secret'], FPP_ADMIN_URL, $_GET['code']);
				update_option('fpp_profile_access_token', $profile_access_token);
				update_option('fpp_object_access_token', '');
				} catch (CommunicationException $exception) {
				echo '<div class="error"><p><strong>'.$exception->getMessage().'</strong></p><p>'.__('Your application\'s access permissions could not be granted.', FPP_TEXT_DOMAIN).'</p></div>';
			}
		}
		
		// Check if access tokens are valid:
		if ($options['app_id_valid'] and $options['app_secret_valid']) {
			try {
				// Verify only profile access token (== object access token):
				if (!fpp_verify_profile_access_permissions($profile_access_token, fpp_get_required_permissions(array('page', 'profile', 'group')))) {
					$profile_access_token = '';
					$object_access_token = '';
					update_option('fpp_object_access_token', $object_access_token);
					update_option('fpp_profile_access_token', $profile_access_token);
					throw new CommunicationException(__('Some or all access permissions are missing. Please click the button <em>Grant access rights!</em> and authorize the plugin to post to your Facebook profile or page.', FPP_TEXT_DOMAIN));
				}
				
				$object_access_token = get_option('fpp_object_access_token');
				
				// Acquire object access token if empty:
				if (!empty($profile_access_token) and $options['object_id_valid'] and empty($object_access_token)) {
					if ($options['object_type'] == 'page') {
						$object_access_token = fpp_acquire_page_access_token($options['object_id'], $profile_access_token);
						update_option('fpp_object_access_token', $object_access_token);
						} else {
						update_option('fpp_object_access_token', $profile_access_token);
					}
				}
				
				// Verify page access and profile access token together:
				if ($options['object_id_valid'] and ($options['object_type'] == 'page')) {
					if (!fpp_verify_page_access_token($options['object_id'], $object_access_token)) {
						$profile_access_token = '';
						$object_access_token = '';
						update_option('fpp_object_access_token', $object_access_token);
						update_option('fpp_profile_access_token', $profile_access_token);
						throw new CommunicationException(__('Some or all access permissions are missing. Please click the button <em>Grant access rights!</em> and authorize the plugin to post to your Facebook profile or page.', FPP_TEXT_DOMAIN));
					}
				}
				} catch (CommunicationException $exception) {
				echo '<div class="error"><p><strong>'.$exception->getMessage().'</strong></p><p>'.__('Your page or profile\'s access permissions could not be verified.', FPP_TEXT_DOMAIN).'</p></div>';
			}
		}
	?>
	<div class="wrap">
		<div class="icon32" id="icon-options-general"><br /></div>
		<h2>
			<?php _e('Facebook Page Publish v'.FFP_version(), FPP_TEXT_DOMAIN) ?>
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
				<input type="hidden" name="cmd" value="_s-xclick">
				<input type="hidden" name="hosted_button_id" value="YK2VNAAC3Y83S">
				<input type="image" src="https://www.paypalobjects.com/en_US/GB/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal — The safer, easier way to pay online.">
				<img alt="" border="0" src="https://www.paypalobjects.com/en_GB/i/scr/pixel.gif" width="1" height="1">
			</form>

		</h2>
		<form method="post" action="options.php">
			<?php settings_fields('fpp_options_group'); ?>
			<h3><?php _e('1. Facebook Connection', FPP_TEXT_DOMAIN) ?></h3>
			<p><?php printf(__('Connect your blog to Facebook. See <a target="_blank" href="%s">detailed setup instructions</a> for help.', FPP_TEXT_DOMAIN), FPP_BASE_URL.'setup.htm') ?></p>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="fpp_options-app_id"><?php _e('Application ID', FPP_TEXT_DOMAIN) ?></label></th>
					<td>
						<input style="color:<?php echo $options['app_id_valid'] ? 'green' : (empty($options['app_id']) ? 'black' : 'red') ?>" id="fpp_options-app_id" name="fpp_options[app_id]" type="text" value="<?php echo htmlentities($options['app_id']); ?>" />
						<a style="font-size:1.3em" target="_blank" href="<?php echo FPP_BASE_URL ?>setup.htm#app_id">?</a>
						<?php 
							if ($options['app_id_valid'] and $options['app_secret_valid']) {
								$profile_access_token = get_option('fpp_profile_access_token');
								if (empty($profile_access_token)) {
									echo '<a class="button-secondary" style="color:red" href="https://www.facebook.com/dialog/oauth?client_id='.urlencode($options['app_id']).'&redirect_uri='.urlencode(FPP_ADMIN_URL).'&scope='.urlencode(implode(',', fpp_get_required_permissions(array('page', 'profile', 'group')))).'">'.__('Grant access rights!', FPP_TEXT_DOMAIN).'</a>';
								}
								else echo '<span style="color:green">'.__('Access granted.', FPP_TEXT_DOMAIN).'</span>';// <a class="button-secondary" style="color:green" href="https://www.facebook.com/dialog/oauth?client_id='.urlencode($options['app_id']).'&redirect_uri='.urlencode(FPP_ADMIN_URL).'&scope='.urlencode(implode(',', fpp_get_required_permissions(array('page', 'profile')))).'">Renew for '.$options['app_id'].'</a>';
							}
							else echo '<a class="button-secondary" disabled="disabled">'.__('Grant access rights!', FPP_TEXT_DOMAIN).'</a>';      
						?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="fpp_options-app_secret"><?php _e('Application Secret', FPP_TEXT_DOMAIN) ?></label></th>
					<td><input style="color:<?php echo $options['app_secret_valid'] ? 'green' : ($options['app_id_valid'] ? 'red' : 'black') ?>" id="fpp_options-app_secret" name="fpp_options[app_secret]" type="text" value="<?php echo htmlentities($options['app_secret']); ?>" />
					<a style="font-size:1.3em" target="_blank" href="<?php echo FPP_BASE_URL ?>setup.htm#app_secret">?</a></td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e('Compatibility', FPP_TEXT_DOMAIN) ?></th>
					<td>
						<fieldset>
							<label style="<?php echo (!fpp_get_ssl_verify()) ? 'color:#aa6600' : '' ?>"><input id="fpp_options-ignore_ssl" type="checkbox" name="fpp_options[ignore_ssl]" value="1" <?php checked('1', $options['ignore_ssl']); ?> /> <span><?php _e('Ignore SSL Certificate', FPP_TEXT_DOMAIN) ?></span></label><br />
						</fieldset>
					</td>
				</tr>
			</table>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			</p>
			
			<h3><?php _e('2. Publishing', FPP_TEXT_DOMAIN) ?></h3>
			<p><?php _e('Specify the posts to publish and decide on which Facebook wall they will appear.', FPP_TEXT_DOMAIN) ?></p>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="fpp_options-object_id"><?php _e('Page or profile ID', FPP_TEXT_DOMAIN) ?></label></th>
					<td><input style="color:<?php echo $options['object_id_valid'] ? 'green' : (empty($options['object_id']) ? 'black' : 'red') ?>" id="fpp_options-object_id" name="fpp_options[object_id]" type="text" value="<?php echo htmlentities($options['object_id']); ?>" />
						<a style="font-size:1.3em" target="_blank" href="<?php echo FPP_BASE_URL ?>setup.htm#object_id">?</a>
						<?php
							$profile_access_token = get_option('fpp_profile_access_token');
							if (!empty($profile_access_token)) {
								echo '<div id="object_id_list"></div>';
								echo '<script type="text/javascript">jQuery("#object_id_list").show_object_id_list("#fpp_options-object_id", "'.urlencode($profile_access_token).'");</script>';
							}
						?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e('Publish by default', FPP_TEXT_DOMAIN) ?></th>
					<td>
						<div style="float:left">
							<fieldset style="line-height:20px">
								<label style="vertical-align:middle"><input name="fpp_options[default_publishing]" value="all" type="radio" <?php checked('1', $options['default_publishing'] == 'all'); ?> /> <span><?php _e('all posts', FPP_TEXT_DOMAIN) ?></span></label><br />
								<label style="vertical-align:middle"><input name="fpp_options[default_publishing]" value="category" type="radio" <?php checked('1', $options['default_publishing'] == 'category'); ?> /> <span><?php _e('posts from selected categories', FPP_TEXT_DOMAIN) ?></span></label><br />
								<label style="vertical-align:middle"><input name="fpp_options[default_publishing]" value="none" type="radio" <?php checked('1', $options['default_publishing'] == 'none'); ?> /> <span><?php _e('nothing', FPP_TEXT_DOMAIN) ?></span></label><br />
							</fieldset>
						</div>
						<div style="margin-left:230px; width:200px; text-align:center">
							<select name="fpp_options[default_publishing_categories][]" multiple="multiple" style="height:60px; width:200px" size="4">
								<?php
									$categories = get_categories(array('hide_empty' => false, 'orderby' => 'name', 'order' => 'ASC'));
									foreach ($categories as $category) { 
										echo '<option style="height:8pt" value="'.$category->cat_ID.'" '.((array_search($category->cat_ID, $options['default_publishing_categories']) !== false) ? 'selected="selected"' : '').'>'.$category->name.'</option>';
									}
								?>
							</select><br />
							<span style="color:#999; font-size:7pt; line-height:9pt"><?php _e('Hold [Ctrl] to select multiple categories', FPP_TEXT_DOMAIN) ?></span>
						</div>
					</td>
				</tr>
			</table>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			</p>
			
			<h3><?php _e('3. Customization', FPP_TEXT_DOMAIN) ?></h3>
			<p><?php _e('Customize the appearance of your posts on Facebook.', FPP_TEXT_DOMAIN) ?></p>
			<div style="width:450px; padding:5px; background-color:#FFF">
				<div style="float:left; width:40px; height:40px; padding:5px; background-color:#EEE; font-size:7pt; line-height:9pt; overflow:hidden"><?php _e('Page or profile photo', FPP_TEXT_DOMAIN) ?></div>
				<div style="margin-left:55px">
					<span style="font-weight:bold; color:#3B5998"><?php _e('Page or profile name', FPP_TEXT_DOMAIN) ?></span>
					<div style="margin-bottom:10px; font-size:9pt; line-height:11pt"><?php _e('This is a short excerpt of your post with an example link to Wordpress <a target="_blank" href="http://wordpress.org">http://wordpress.org</a>. Lorem ipsum dolor...', FPP_TEXT_DOMAIN) ?><br /><label><input id="fpp_options-show_post_text" type="checkbox" name="fpp_options[show_post_text]" value="1" <?php checked('1', $options['show_post_text']); ?> /> <?php _e('Post excerpt', FPP_TEXT_DOMAIN) ?></label> <label><input id="fpp_options-show_post_link" type="checkbox" name="fpp_options[show_post_link]" value="1" <?php checked('1', $options['show_post_link']); ?> /> <?php _e('Include link URLs', FPP_TEXT_DOMAIN) ?></label></div>
					<div style="float:left;  padding:0 3px 3px 3px; background-color:#EEE; font-size:8pt;">
						<?php _e('Thumbnail', FPP_TEXT_DOMAIN) ?><br />
						<fieldset>
							<label><input name="fpp_options[show_thumbnail]" value="gravatar" type="radio" <?php checked('1', $options['show_thumbnail'] == 'gravatar'); ?>/> <span>Gravatar <a target="_blank" href="http://gravatar.com">?</a></span></label><br />
							<label><input name="fpp_options[show_thumbnail]" value="post" type="radio" <?php checked('1', $options['show_thumbnail'] == 'post'); ?> /> <span><?php _e('From post', FPP_TEXT_DOMAIN) ?></span></label><br />
							<label><input name="fpp_options[show_thumbnail]" value="default" type="radio" <?php checked('1', $options['show_thumbnail'] == 'default'); ?> /> <span><?php _e('Default', FPP_TEXT_DOMAIN) ?></span></label><br />
							<label><input name="fpp_options[show_thumbnail]" value="none" type="radio" <?php checked('1', $options['show_thumbnail'] == 'none'); ?> /> <span><?php _e('None', FPP_TEXT_DOMAIN) ?></span></label><br />
						</fieldset>
					</div>
					<div style="margin-left:95px">
						<div style="font-weight:bold; color:#3B5998"><?php _e('Post title linking to your post', FPP_TEXT_DOMAIN) ?></div>
						<div style="color:gray; font-size:8pt; line-height:8pt"><?php _e('Blog domain', FPP_TEXT_DOMAIN) ?></div>
						<div style="color:gray; font-size:8pt; line-height:20pt">
							<label><input id="fpp_options-show_post_author" type="checkbox" name="fpp_options[show_post_author]" value="1" <?php checked('1', $options['show_post_author']); ?> /> <?php _e('Post author', FPP_TEXT_DOMAIN) ?></label> |
							<label><input id="fpp_options-show_post_categories" type="checkbox" name="fpp_options[show_post_categories]" value="1" <?php checked('1', $options['show_post_categories']); ?> /> <?php _e('Post categories', FPP_TEXT_DOMAIN) ?></label>
						</div>
					</div>
					<div style="clear:left"></div>
				</div>
			</div>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><?php _e('Default thumbnail', FPP_TEXT_DOMAIN) ?></th>
					<td>
						<input style="text-align:left" id="upload_image" type="text" size="36" name="fpp_options[default_thumbnail_url]" value="<?php echo htmlentities($options['default_thumbnail_url']); ?>" />
						<input id="upload_image_button" type="button" value="Media gallery" /><br />
						<span style="color:#999; font-size:7pt; line-height:9pt"><?php _e('Enter an URL or upload an image', FPP_TEXT_DOMAIN) ?></span>
					</td>
				</tr>
			</table>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			</p>
		</form>
	</div>
	<?php
	}
	
	/**
		* Render Facebook recognized meta tags (Open Graph protocol).
		* Facebooks uses them to refine shared links for example.
		*
		* @last_review 0.3.0
	*/
	function fpp_render_meta_tags($post) {
		$options = get_option('fpp_options');
		
		echo '<meta property="og:type" content="article"/>'; // Required by FB
		echo '<meta property="og:url" content="'.esc_attr(get_permalink($post->ID)).'"/>'; // Required by FB
		
		echo '<meta property="og:title" content="'.esc_attr(apply_filters('the_title', $post->post_title))/*, ENT_COMPAT, 'UTF-8')*/.'"/>';
		
		$description = array();
		if ($options['show_post_author']) {
			$description[] = esc_attr(fpp_get_post_author($post));/*, ENT_COMPAT, 'UTF-8')*/
		}
		if ($options['show_post_categories']) {
			$categories = esc_attr(fpp_get_post_categories($post));/*, ENT_COMPAT, 'UTF-8')*/
			if (!empty($categories)) $description[] = $categories;
		}
		echo '<meta property="og:description" content="'.implode(' | ', $description).'"/>';
		
		if (($options['show_thumbnail'] == 'post') and empty($post->post_password)) {
			$image_url = fpp_get_post_image($post);
		}
		else if ($options['show_thumbnail'] == 'gravatar') {
			preg_match('/<img .*?src=["|\']([^"|\']+)/i', get_avatar($post->post_author), $matches);
			if (!empty($matches[1])) $image_url = $matches[1];
		}
		else if (($options['show_thumbnail'] == 'none') or empty($options['default_thumbnail_url'])) {
			if ($options['show_post_categories'] or $options['show_post_author']) {
				$image_url = FPP_BASE_URL.'line.png';
				} else {
				$image_url = FPP_BASE_URL.'line_small.png';
			}
		}
		if (!isset($image_url) or empty($image_url)) {
			if (!empty($options['default_thumbnail_url'])) {
				$image_url = $options['default_thumbnail_url'];
				} else {
				if ($options['show_post_categories'] or $options['show_post_author']) {
					$image_url = FPP_BASE_URL.'line.png';
					} else {
					$image_url = FPP_BASE_URL.'line_small.png';
				}
			}
		}
		echo '<meta property="og:image" content="'.esc_attr($image_url)/*, ENT_COMPAT, 'UTF-8')*/.'"/>';
	}
	
	/**
		* Renders a 'publish to facebook' checkbox. Renders the box only if 
		* the current post is a real post, not a page or something else.
	*/
	function fpp_render_post_button() {
		global $post;
		
		$object_access_token = get_option('fpp_object_access_token');
		
		if (!array_pop(get_post_meta($post->ID, '_fpp_is_published'))) {
			echo '<label for="fpp_post_to_facebook"><img style="vertical-align:middle; margin:2px" src="'.FPP_BASE_URL.'publish_icon.png" alt="'.__('Publish to Facebook', FPP_TEXT_DOMAIN).'" /> '.__('Publish to Facebook', FPP_TEXT_DOMAIN).' </label><input '.(((FPP_DEFAULT_POST_TO_FACEBOOK or fpp_get_default_publishing($post)) and !empty($object_access_token)) ? 'checked="checked"' : '').' type="checkbox" value="1" id="fpp_post_to_facebook" name="fpp_post_to_facebook" '.(empty($object_access_token) ? 'disabled="disabled"' : '').' />';
			} else {
			echo '<label for="fpp_post_to_facebook"><img style="vertical-align:middle; margin:2px" src="'.FPP_BASE_URL.'publish_icon.png" alt="'.__('Publish to Facebook', FPP_TEXT_DOMAIN).'" /> '.__('Post <em>again</em> to Facebook', FPP_TEXT_DOMAIN).' </label><input type="checkbox" value="1" id="fpp_post_to_facebook" name="fpp_post_to_facebook" '.(empty($object_access_token) ? 'disabled="disabled"' : '').' />';
		}
		if (empty($object_access_token)) {
			echo '<div><em style="color:#aa6600">'.sprintf(__('Facebook Page Publish is not set up.<br />Please check your <a href="%s">plugin settings</a>.', FPP_TEXT_DOMAIN), 'options-general.php?page='.plugin_basename(__FILE__)).'</em></div>';
		}
		if ($post->post_status == "private") {
			echo '<div><em style="color:#aa6600">'.__('Private posts are not published', FPP_TEXT_DOMAIN).'</em></div>';
		}
		echo '<input type="hidden" name="fpp_send_from_admin" value="1" />';
		
		$error = get_option('fpp_error');
		if (!empty($error)) {
			echo '<div class="error"><strong>'.sprintf(__('Your Facebook Page Publish plugin reports an error. Please check your <a href="%s">plugin settings</a>.', FPP_TEXT_DOMAIN), 'options-general.php?page='.plugin_basename(__FILE__)).'</strong></div>';
		}
	}
	
	/**********************************************************************
		* Others
	**********************************************************************/
	/**
		* @last_review 0.3.0
	*/
	function fpp_validate_options($input) {
		$options = get_option('fpp_options');
		
		// Customization options:
		$options['show_thumbnail'] = $input['show_thumbnail'];
		$options['show_post_author'] = array_key_exists('show_post_author', $input) && !empty($input['show_post_author']);
		$options['show_post_categories'] = array_key_exists('show_post_categories', $input) && !empty($input['show_post_categories']);
		$options['show_post_text'] = array_key_exists('show_post_text', $input) && !empty($input['show_post_text']);
		$options['show_post_link'] = array_key_exists('show_post_link', $input) && !empty($input['show_post_link']);
		$options['default_thumbnail_url'] = trim($input['default_thumbnail_url']);
		
		// Validate customization options:
		if (($options['show_thumbnail'] == 'default') and (substr($options['default_thumbnail_url'], 0, 4) !== 'http'))
		add_settings_error('fpp_options', 'customization_error', __('The given default thumbnail URL is not valid. Any valid URL has to start with http:// or https://.', FPP_TEXT_DOMAIN).'</p><p><font style="font-weight:normal">'.__('Facebook won\'t be able to display the choosen default thumbnail.', FPP_TEXT_DOMAIN).'</font></p>');
		
		// Connection options:
		if ($options['app_id'] != $input['app_id']) {
			update_option('fpp_profile_access_token', '');
			update_option('fpp_object_access_token', '');
			} else if ($options['object_id'] != $input['object_id']) {
			update_option('fpp_object_access_token', '');
		}
		
		$options['app_id'] = $input['app_id'];
		$options['object_id'] = $input['object_id'];
		$options['app_secret'] = $input['app_secret'];
		$options['app_id_valid'] = false;
		$options['object_id_valid'] = false;
		$options['object_type'] = '';
		$options['app_secret_valid'] = false;
		$options['ignore_ssl'] = array_key_exists('ignore_ssl', $input) && !empty($input['ignore_ssl']);
		$options['default_publishing'] = $input['default_publishing'];
		$options['default_publishing_categories'] = array_key_exists('default_publishing_categories', $input) ? $input['default_publishing_categories'] : array();
		
		// Validate connection options:
		try {
			if (!empty($options['app_id']) or !empty($options['object_id'])) {
				$object_classification = fpp_classify_facebook_objects(array($options['app_id'], $options['object_id']));
				$options['app_id_valid'] = $object_classification[$options['app_id']] == 'application';
				$options['object_type'] = $object_classification[$options['object_id']];
				$options['object_id_valid'] = (($options['object_type'] == 'profile') or ($options['object_type'] == 'page') or ($options['object_type'] == 'group') or ($options['object_type'] == 'application')); 
			}
			$options['app_secret_valid'] = (!empty($options['app_secret']) and $options['app_id_valid']) ? fpp_is_valid_facebook_application($options['app_id'], $options['app_secret'], FPP_ADMIN_URL) : false;
			
			if (!empty($options['app_id']) and !$options['app_id_valid']) {
				throw new FacebookErrorException(__('Invalid application ID.', FPP_TEXT_DOMAIN).' '.sprintf(__('Please refer to the <a target="_blank" href="%s">detailed setup instructions</a>.', FPP_TEXT_DOMAIN), FPP_BASE_URL.'setup.htm#app_id'));
			}
			if (!empty($options['object_id']) and !$options['object_id_valid'])  {
				throw new FacebookErrorException(__('Invalid user or page ID.', FPP_TEXT_DOMAIN).' '.sprintf(__('Please refer to the <a target="_blank" href="%s">detailed setup instructions</a>.', FPP_TEXT_DOMAIN), FPP_BASE_URL.'setup.htm#object_id'));
			}
			if (!$options['app_secret_valid'] and $options['app_id_valid'])
			throw new FacebookErrorException(__('Invalid application secret.', FPP_TEXT_DOMAIN).' '.sprintf(__('Please refer to the <a target="_blank" href="%s">detailed setup instructions</a>.', FPP_TEXT_DOMAIN), FPP_BASE_URL.'setup.htm#app_secret'));
			
			} catch (CommunicationException $exception) {
			add_settings_error('fpp_options', 'connection_error', $exception->getMessage().'<p><font style="font-weight:normal">'.__('Your connection options could not be validated.', FPP_TEXT_DOMAIN).'</font></p>');
		}
		return $options;
	}
	
	/**
		* Initializes the plugin options with either default or existing
		* values (in case there is already a version installed).
	*/
	function fpp_initialize_options() {
		// default options:
		$options = array(
		'app_id' => '',
		'app_id_valid' => false,
		'app_secret' => '',
		'app_secret_valid' => false,
		'object_id' => '',
		'object_id_valid' => false,
		'object_type' => '',
		'ignore_ssl' => false,
		'default_publishing' => 'all',
		'default_publishing_categories' => array(),
		'default_thumbnail_url' => '',
		'show_post_categories' => true,
		'show_post_author' => true,
		'show_post_text' => true,
		'show_post_link' => true,
		'show_thumbnail' => 'gravatar');
		
		$current_version = get_option('fpp_installed_version');
		$current_options = get_option('fpp_options');
		
		$object_access_token = get_option('fpp_object_access_token');
		$profile_access_token = get_option('fpp_profile_access_token');
		
		// Plugin previously installed:
		if (is_array($current_options)) {
			foreach ($options as $key => $value) {
				if (array_key_exists($key, $current_options)) {
					$options[$key] = $current_options[$key];
				}
			}
			
			if (empty($current_version)) { // version <= 0.2.2
				$options['app_id_valid'] = !empty($current_options['page_id']);
				$options['app_secret_valid'] = !empty($current_options['page_id']);
				$options['object_id'] = $current_options['page_id'];
				$options['object_id_valid'] = !empty($current_options['page_id']);
				$options['object_type'] = 'page';
				$options['show_thumbnail'] = $current_options['show_gravatar'] ? 'gravatar' : 'post';
				
				$object_access_token = get_option('fpp_page_access_token');
				
				delete_option('fpp_page_access_token');
				delete_option('fpp_post_to_facebook');
			}
		}
		update_option('fpp_options', $options);
		update_option('fpp_object_access_token', $object_access_token);
		update_option('fpp_profile_access_token', $profile_access_token);
		update_option('fpp_error', '');
		update_option('fpp_installed_version', FPP_VERSION);
	}
	
	function FFP_version() {
		if(function_exists('get_plugin_data')) {
			$plugin_data = get_plugin_data( __FILE__ );
			return $plugin_data['Version'];
		} else {
			return '0.0.0';
		}
	}
?>