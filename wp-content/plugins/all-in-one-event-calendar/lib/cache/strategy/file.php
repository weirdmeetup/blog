<?php

/**
 * Concrete class for file caching strategy.
 *
 * @instantiator new
 * @author       Time.ly Network, Inc.
 * @since        2.0
 * @package      Ai1EC
 * @subpackage   Ai1EC.Cache.Strategy
 */
class Ai1ec_Cache_Strategy_File extends Ai1ec_Cache_Strategy {

	/**
	 * @var string
	 */
	private $cache_dir;

	public function __construct( Ai1ec_Registry_Object $registry, $cache_dir ) {
		parent::__construct( $registry );
		$this->cache_dir = $cache_dir;
	}

	/**
	 *
	 * @see Ai1ec_Get_Data_From_Cache::get_data()
	 *
	 */
	public function get_data( $file ) {
		$file = $this->_safe_file_name( $file );
		if ( ! file_exists( $this->cache_dir . $file ) ) {
			throw new Ai1ec_Cache_Not_Set_Exception(
				'File \'' . $file . '\' does not exist'
			);
		}
		return maybe_unserialize(
			file_get_contents( $this->cache_dir . $file )
		);
	}

	/**
	 *
	 * @see Ai1ec_Write_Data_To_Cache::write_data()
	 *
	 */
	public function write_data( $filename, $value ) {
		$filename = $this->_safe_file_name( $filename );
		$value    = maybe_serialize( $value );
		$result = $this->_registry->get( 'filesystem.checker' )->put_contents(
			$this->cache_dir . $filename,
			$value
		);
		if ( false === $result ) {
			$message = 'An error occured while saving data to \'' .
				$this->cache_dir . $filename . '\'';
			throw new Ai1ec_Cache_Write_Exception( $message );
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see Ai1ec_Write_Data_To_Cache::delete_data()
	 */
	public function delete_data( $filename ) {
		// Check if file exists. It might not exists if you switch themes
		// twice without never rendering the CSS
		$filename = $this->_safe_file_name( $filename );
		if (
			file_exists( $this->cache_dir . $filename ) &&
			false === unlink( $this->cache_dir . $filename )
		) {
			return false;
		}
		return true;
	}

	/**
	 *
	 * @see Ai1ec_Write_Data_To_Cache::delete_matching()
	 */
	public function delete_matching( $pattern ) {
		$dirhandle = opendir( $this->cache_dir );
		if ( false === $dirhandle ) {
			return 0;
		}
		$count = 0;
		while ( false !== ( $entry = readdir( $dirhandle ) ) ) {
			if ( '.' !== $entry{0} && false !== strpos( $entry, $pattern ) ) {
				if ( unlink( $this->cache_dir . $entry ) ) {
					++$count;
				}
			}
		}
		closedir( $dirhandle );
		return $count;
	}

	/**
	 * _safe_file_name method
	 *
	 * Generate safe file name for any storage case.
	 *
	 * @param string $file File name currently supplied
	 *
	 * @return string Sanitized file name
	 */
	protected function _safe_file_name( $file ) {
		static $prefix = NULL;
		if ( NULL === $prefix ) {
			$prefix = substr( md5( site_url() ), 0, 8 );
		}
		$length = strlen( $file );
		if ( ! ctype_alnum( $file ) ) {
			$file = preg_replace(
				'|_+|',
				'_',
				preg_replace( '|[^a-z0-9\-,_]|', '_', $file )
			);
		}
		if ( 0 !== strncmp( $file, $prefix, 8 ) ) {
			$file = $prefix . '_' . $file;
		}
		return $file;
	}

}
