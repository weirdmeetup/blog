<?php
/**
 * Uninstall hook called automatically by WP (recognized by its
 * filename).
 */
 
if (!defined('WP_UNINSTALL_PLUGIN')) exit();
 
delete_option('fpp_installed_version');
delete_option('fpp_options');
delete_option('fpp_object_access_token');
delete_option('fpp_profile_access_token');
delete_option('fpp_error');
?>