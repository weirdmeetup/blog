<?php

/**
 * A factory class for caching strategy.
 *
 * @author     Time.ly Network, Inc.
 * @since      2.0
 * @package    Ai1EC
 * @subpackage Ai1EC.Factory
 */
class Ai1ec_Factory_Strategy extends Ai1ec_Base {

	/**
	 * create_cache_strategy_instance method
	 *
	 * Method to instantiate new cache strategy object
	 *
	 * @param string $cache_directory Cache directory to use
	 * @param bool   $skip_small_bits Set to true, to ignore small entities
	 *                                cache engines, as APC [optional=false]
	 *
	 * @return Ai1ec_Cache_Strategy Instantiated writer
	 */
	public function create_cache_strategy_instance(
		$cache_directory = NULL,
		$skip_small_bits = false
	) {
		$engine = NULL;
		$name   = '';
		if ( true !== $skip_small_bits && Ai1ec_Cache_Strategy_Apc::is_available() ) {
			$engine = $this->_registry->get( 'cache.strategy.apc' );
		} else if (
			NULL !== $cache_directory &&
			$this->_is_cache_dir_writable( $cache_directory )
		) {
			$engine = $this->_registry->get( 'cache.strategy.file', $cache_directory );
		} else if ( true !== $skip_small_bits ) {
			$engine = $this->_registry->get(
				'cache.strategy.db',
				$this->_registry->get( 'model.option' )
			);
		} else {
			$engine = $this->_registry->get( 'cache.strategy.void' );
		}
		return $engine;
	}

	/**
	 * create_persistence_context method
	 *
	 * @param string               $key_for_persistance
	 * @param Ai1ec_Cache_Strategy $cache_strategy
	 * @param string               $cache_directory
	 *
	 * @return Ai1ec_Persistence_Context Instance of persistance context
	 */
	public function create_persistence_context(
		$key_for_persistance,
		$cache_directory = null
	) {
		return new Ai1ec_Persistence_Context(
			$key_for_persistance,
			$this->create_cache_strategy_instance( $cache_directory )
		);
	}

	/**
	 * _is_cache_dir_writable method
	 *
	 * Check if given cache directory is writable.
	 *
	 * @param string $directory A path to check for writability
	 *
	 * @return bool Writability
	 */
	protected function _is_cache_dir_writable( $directory ) {
		static $cache_directories = array();
		if ( ! isset( $cache_directories[$directory] ) ) {
			$filesystem = $this->_registry->get( 'filesystem.checker' );
			$cache_directories[$directory] = $filesystem->is_writable(
				$directory
			);
		}
		return $cache_directories[$directory];
	}
}
