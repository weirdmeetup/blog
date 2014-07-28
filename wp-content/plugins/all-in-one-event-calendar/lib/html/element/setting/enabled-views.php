<?php

/**
 * Renderer of settings page Enabled views selection snippet.
 *
 * @author     Time.ly Network, Inc.
 * @since      2.0
 * @package    Ai1EC
 * @subpackage Ai1EC.Html
 */
class Ai1ec_Html_Element_Enabled_Views
    extends Ai1ec_Html_Element_Settings {

	/* (non-PHPdoc)
	 * @see Ai1ec_Html_Element_Settings::render()
	 */
	public function render( $output = '' ) {
		$this->_convert_values();
		$args = array(
			'views'        => $this->_args['value'],
			'label'        => $this->_args['renderer']['label'],
			'text_enabled' => __( 'Enabled', AI1EC_PLUGIN_NAME ),
			'text_default' => __( 'Default', AI1EC_PLUGIN_NAME ),
		);
		$loader = $this->_registry->get( 'theme.loader' );
		return $loader->get_file( 'setting/enabled-views.twig', $args, true )
			->get_content();
	}

	/**
	 * Convert values to bo used in rendering
	 */
	protected function _convert_values() {
		foreach( $this->_args['value'] as &$view ) {
			$view['enabled'] = $view['enabled'] ?
				'checked="checked"' :
				'';
			$view['default'] = $view['default'] ?
				'checked="checked"' :
				'';
		}
	}
}