<?php

/**
 * The concrete command that save settings.
 *
 * @author     Time.ly Network Inc.
 * @since      2.0
 *
 * @package    AI1EC
 * @subpackage AI1EC.Command
 */
class Ai1ec_Command_Save_Settings extends Ai1ec_Command_Save_Abstract {

	/* (non-PHPdoc)
	 * @see Ai1ec_Command::do_execute()
	 */
	public function do_execute() {
		$settings = $this->_registry->get( 'model.settings' );
		$options  = $settings->get_options();
		$_POST['default_tags_categories'] = (
			isset( $_POST['default_tags'] ) ||
			isset( $_POST['default_categories'] )
		);
		$_POST['enabled_views'] = true;
		foreach ( $options as $name => $data ) {
			$value = null;
			if ( isset( $_POST[$name] ) ) {
				// if a validator is pecified, use it.
				if ( isset( $data['renderer']['validator'] ) ) {
					$validator = $this->_registry->get(
						'validator.' . $data['renderer']['validator'],
						$_POST[$name]
					);
					try {
						$value = $validator->validate();
					} catch ( Ai1ec_Value_Not_Valid_Exception $e ) {
						// don't save
						continue;
					}
				} else {

					switch ( $data['type'] ) {
						case 'bool':
							$value  = true;
							break;
						case 'int':
							$value  = (int)$_POST[$name];
							break;
						case 'string':
							$value  = (string)$_POST[$name];
							break;
						case 'array':
							$method = '_handle_saving_' . $name;
							$value  = $this->$method();
							break;
						case 'mixed':
							$method = '_handle_saving_' . $name;
							$value  = $this->$method( $_POST[$name] );
							break;
						case 'wp_option': // set the corresponding WP option
							$this->_registry->get( 'model.option' )
								->set( $name, $_POST[$name], true );
							$value = null;
					}
				}
			} else {
				if ( isset( $data['type'] ) && 'bool' === $data['type'] ) {
					$value = false;
				}
			}
			if ( null !== $value ) {
				$settings->set( $name, stripslashes_deep( $value ) );
			}
		}
		$new_options = $settings->get_options();
		// let extension manipulate things if needed.
		do_action( 'ai1ec_settings_updated', $options, $new_options );

		return array(
			'url' => admin_url(
				'edit.php?post_type=ai1ec_event&page=all-in-one-event-calendar-settings'
			),
			'query_args' => array(
				'updated' => 1
			)
		);
	}

	/**
	 * Handle saving enabled_views.
	 *
	 * @return array
	 */
	protected function _handle_saving_enabled_views() {
		$settings      = $this->_registry->get( 'model.settings' );
		$enabled_views = $settings->get( 'enabled_views' );
		foreach ( $enabled_views as $view => &$options ) {
			$options['enabled'] = isset( $_POST['view_' . $view . '_enabled'] );
			$options['default'] = $_POST['default_calendar_view'] === $view;
		}
		return $enabled_views;
	}

	/**
	 * Handle saving default_tag_categories option
	 *
	 * @return array
	 */
	protected function _handle_saving_default_tags_categories() {
		return array(
			'tags' => isset( $_POST['default_tags'] ) ?
				$_POST['default_tags'] :
				array(),
			'categories' => isset( $_POST['default_categories'] ) ?
				$_POST['default_categories'] :
				array(),
		);
	}

	/**
	 * Creates the calendar page if a string is passed.
	 *
	 * @param int|string $calendar_page
	 *
	 * @return int
	 */
	protected function _handle_saving_calendar_page_id( $calendar_page ) {
		if (
			! is_numeric( $calendar_page ) &&
			preg_match( '#^__auto_page:(.*?)$#', $calendar_page, $matches )
		) {
			return wp_insert_post(
				array(
					'post_title'     => $matches[1],
					'post_type'      => 'page',
					'post_status'    => 'publish',
					'comment_status' => 'closed'
				)
			);
		} else {
			return (int)$calendar_page;
		}
	}

}