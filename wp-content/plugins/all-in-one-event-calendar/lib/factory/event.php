<?php

/**
 * A factory class for events.
 *
 * @author     Time.ly Network, Inc.
 * @since      2.0
 * @package    Ai1EC
 * @subpackage Ai1EC.Factory
 */
class Ai1ec_Factory_Event extends Ai1ec_Base {
	
	/**
	 * @var bool whether the theme is legacy
	 */
	protected $_legacy;

	/**
	 * Public constructor
	 *
	 * @param Ai1ec_Registry_Object $registry
	 */
	public function __construct( Ai1ec_Registry_Object $registry ) {
		parent::__construct( $registry );
		$this->_legacy = $registry->get( 'theme.loader' )->is_legacy_theme();
	}

	/**
	 * Factory method for events
	 * 
	 * @param string $data
	 * @param string $instance
	 * 
	 * @return Ai1ec_Event
	 */
	public function create_event_instance(
		Ai1ec_Registry_Object $registry,
		$data     = null,
		$instance = false
	) {
		if ( true === $this->_legacy ) {
			return new Ai1ec_Event_Legacy(
				$registry,
				$data,
				$instance
			);
		}
		return new Ai1ec_Event(
			$registry,
			$data,
			$instance
		);
	}

}