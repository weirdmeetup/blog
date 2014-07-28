<?php

/**
 * The abstract class for a admin page.
 *
 * @author     Time.ly Network Inc.
 * @since      2.0
 *
 * @package    AI1EC
 * @subpackage AI1EC.View
 */
abstract class Ai1ec_View_Admin_Abstract extends Ai1ec_Base {

	/**
	 * Adds the page to the correct menu.
	 */
	abstract public function add_page();

	/**
	 * Adds the page to the correct menu.
	 */
	abstract public function add_meta_box();
	
	/**
	 * Display the page html
	 */
	abstract public function display_page();

	/**
	 * Handle post, likely to be deprecated to use commands.
	 */
	abstract public function handle_post();

}