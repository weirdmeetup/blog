<?php

/**
 * Checks configurations and notifies admin.
 *
 * @author     Time.ly Network Inc.
 * @since      2.0
 *
 * @package    AI1EC
 * @subpackage AI1EC.Lib
 */
class Ai1ec_Environment_Checks extends Ai1ec_Base {

	/**
	 * Runs checks for necessary config options.
	 *
	 * @return void Method does not return.
	 */
	public function run_checks() {
		$role         = get_role( 'administrator' );
		$current_user = get_userdata( get_current_user_id() );
		if ( 
			! is_object( $role ) ||
			! is_object( $current_user ) ||
			! $role->has_cap( 'manage_ai1ec_options' ) ||
			(
				defined( 'DOING_AJAX' ) &&
				DOING_AJAX
			)
		) {
			return;
		}
		global $plugin_page;
		$settings      = $this->_registry->get( 'model.settings' );
		$notification  = $this->_registry->get( 'notification.admin' );
		$notifications = array();

		// check if is set calendar page
		if ( ! $settings->get( 'calendar_page_id' ) ) {
			$msg = Ai1ec_I18n::__(
				'Select an option in the <strong>Calendar page</strong> dropdown list.'
			);
			$notifications[] = $msg;
		}
		if (
			$plugin_page !== AI1EC_PLUGIN_NAME . '-settings' &&
			! empty( $notifications )
		) {
			if (
				$current_user->has_cap( 'manage_ai1ec_options' )
			) {
				$msg = sprintf(
					Ai1ec_I18n::__( 'The plugin is installed, but has not been configured. <a href="%s">Click here to set it up now &raquo;</a>' ),
					admin_url( AI1EC_SETTINGS_BASE_URL )
				);
				$notification->store(
					$msg,
					'updated',
					2,
					array( Ai1ec_Notification_Admin::RCPT_ADMIN )
				);
			} else {
				$msg = Ai1ec_I18n::__(
					'The plugin is installed, but has not been configured. Please log in as an Administrator to set it up.'
				);
				$notification->store(
					$msg,
					'updated',
					2,
					array( Ai1ec_Notification_Admin::RCPT_ALL )
				);
			}
			return;
		}
		foreach ( $notifications as $msg ) {
			$notification->store(
				$msg,
				'updated',
				2,
				array( Ai1ec_Notification_Admin::RCPT_ADMIN )
			);
		}
	}

}