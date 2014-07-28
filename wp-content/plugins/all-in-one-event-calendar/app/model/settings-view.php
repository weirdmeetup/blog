<?php

/**
 * Settings extension for managing view-related settings.
 *
 * @author     Time.ly Network, Inc.
 * @since      2.0
 * @package    Ai1EC
 * @subpackage Ai1EC.Model
 */
class Ai1ec_Settings_View extends Ai1ec_App {

	/**
	 * @var string Name of settings option to use for views map.
	 */
	const SETTING_VIEWS_MAP = 'enabled_views';

	/**
	 * @var Ai1ec_Settings Instance
	 */
	protected $_settings = null;

	/**
	 * Acquire Settings model instance for future reference.
	 *
	 * @return void
	 */
	protected function _initialize() {
		$this->_settings = $this->_registry->get( 'model.settings' );
	}

	/**
	 * Add a view if not set.
	 *
	 * @param array $view
	 */
	public function add( array $view ) {
		$enabled_views = $this->_get();
		if ( isset( $enabled_views[$view['name']] ) ) {
			return;
		}
		$enabled_views[$view['name']] = array(
			'enabled'  => $view['enabled'],
			'default'  => $view['default'],
			'longname' => $view['longname'],
		);
		$this->_set( $enabled_views );
	}

	/**
	 * Remove a view.
	 *
	 * @param string $view
	 */
	public function remove( $view ) {
		$enabled_views = $this->_get();
		if ( isset( $enabled_views[$view] ) ) {
			unset( $enabled_views[$view] );
			$this->_set( $enabled_views );
		}
	}

	/**
	 * Retrieve all configured views.
	 *
	 * @return array Map of configured view aliases and their details.
	 */
	public function get_all() {
		return $this->_get();
	}

	/**
	 * Get name of view to be rendered for requested alias.
	 *
	 * @param string $view Name of view requested.
	 *
	 * @return string Name of view to be rendered.
	 *
	 * @throws Ai1ec_Settings_Exception If no views are configured.
	 */
	public function get_configured( $view ) {
		$enabled_views = $this->_get();
		if ( empty( $enabled_views ) ) {
			throw new Ai1ec_Settings_Exception( 'No view is enabled' );
		}
		if (
			isset( $enabled_views[$view] ) &&
			$enabled_views[$view]['enabled']
		) {
			return $view;
		}
		return $this->get_default();
	}

	/**
	 * Get default view to render.
	 * 
	 * 
	 * @return 
	 */
	public function get_default() {
		$enabled_views = $this->_get();
		$default       = null;
		foreach ( $enabled_views as $view => $details ) {
			if ( $details['default'] && $details['enabled'] ) {
				$default = $view;
				break;
			}
		}
		if ( null === $default ) {
			$default = (string)current( array_keys( $enabled_views ) );
		}
		return $default;
	}

	/**
	 * Retrieve views maps from storage.
	 *
	 * @return array Current views map.
	 */
	protected function _get() {
		return (array)$this->_settings->get( self::SETTING_VIEWS_MAP, array() );
	}

	/**
	 * Update views map.
	 *
	 * @param array $enabled_views Map of enabled views.
	 *
	 * @return bool Success.
	 */
	protected function _set( array $enabled_views ) {
		return $this->_settings->set( self::SETTING_VIEWS_MAP, $enabled_views );
	}

}