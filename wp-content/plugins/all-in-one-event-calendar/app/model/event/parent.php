<?php
/**
 * Class which represnt event parent/child relationship.
 *
 * @author       Time.ly Network, Inc.
 * @since        2.0
 * @package      Ai1EC
 * @subpackage   Ai1EC.Model
 */
class Ai1ec_Event_Parent extends Ai1ec_Base {
	
	/**
	 * event_parent method
	 *
	 * Get/set event parent
	 *
	 * @param int $event_id    ID of checked event
	 * @param int $parent_id   ID of new parent [optional=NULL, acts as getter]
	 * @param int $instance_id ID of old instance id
	 *
	 * @return int|bool Value depends on mode:
	 *     Getter: {@see self::get_parent_event()} for details
	 *     Setter: true on success.
	 */
	public function event_parent(
		$event_id,
		$parent_id   = null,
		$instance_id = null
	) {
		$meta_key = '_ai1ec_event_parent';
		if ( null === $parent_id ) {
			return $this->get_parent_event( $event_id );
		}
		$meta_value = json_encode( array(
			'created'  => $this->_registry->get( 'date.system' )->current_time(),
			'instance' => $instance_id,
		) );
		return add_post_meta( $event_id, $meta_key, $meta_value, true );
	}
	
	/**
	 * Get parent ID for given event
	 *
	 * @param int $current_id Current event ID
	 *
	 * @return int|bool ID of parent event or bool(false)
	 */
	public function get_parent_event( $current_id ) {
		static $parents = null;
		if ( null === $parents ) {
			$parents = $this->_registry->get( 'cache.memory' );
		}
		$current_id = (int)$current_id;
		if ( null === ( $parent_id = $parents->get( $current_id ) ) ) {
			$db = $this->_registry->get( 'dbi.dbi' );
			/* @var $db Ai1ec_Dbi */
			$query      = '
				SELECT parent.ID, parent.post_status
				FROM
					' . $db->get_table_name( 'posts' ) . ' AS child
					INNER JOIN ' . $db->get_table_name( 'posts' ) . ' AS parent
						ON ( parent.ID = child.post_parent )
				WHERE child.ID = ' . $current_id;
			$parent     = $db->get_row( $query );
			if (
				empty( $parent ) ||
				'trash' === $parent->post_status
			) {
				$parent_id = false;
			} else {
				$parent_id = $parent->ID;
			}
			$parents->set( $current_id, $parent_id );
			unset( $query );
		}
		return $parent_id;
	}
	
	/**
	 * Returns a list of modified (children) event objects
	 *
	 * @param int  $parent_id     ID of parent event
	 * @param bool $include_trash Includes trashed when `true` [optional=false]
	 *
	 * @return array List (might be empty) of Ai1ec_Event objects
	 */
	public function get_child_event_objects(
		$parent_id,
		$include_trash = false
	) {
		$db = $this->_registry->get( 'dbi.dbi' );
		/* @var $db Ai1ec_Dbi */
		$parent_id = (int)$parent_id;
		$sql_query = 'SELECT ID FROM ' . $db->get_table_name( 'posts' ) .
			' WHERE post_parent = ' . $parent_id;
		$childs    = (array)$db->get_col( $sql_query );
		$objects = array();
		foreach ( $childs as $child_id ) {
			try {
				$instance = $this->_registry->get( 'model.event', $child_id );
				if (
					$include_trash ||
					'trash' !== $instance->get( 'post' )->post_status
				) {
					$objects[$child_id] = $instance;
				}
			} catch ( Ai1ec_Event_Not_Found_Exception $exception ) {
				// ignore
			}
		}
		return $objects;
	}
	
	/**
	 * admin_init_post method
	 *
	 * Bind to admin_action_editpost action to override default save
	 * method when user is editing single instance.
	 * New post is created with some fields unset.
	 */
	public function admin_init_post( ) {
		if (
			isset( $_POST['ai1ec_instance_id'] ) &&
			isset( $_POST['action'] ) &&
			'editpost' === $_POST['action']
		) {
			$old_post_id = $_POST['post_ID'];
			$instance_id = $_POST['ai1ec_instance_id'];
			$post_id = $this->_registry->get( 'model.event.creating' )
				->create_duplicate_post();
			if ( false !== $post_id ) {
				$created_event = $this->_registry->get( 'model.event', $post_id );
				$this->add_exception_date(
					$old_post_id,
					$created_event->get( 'start' )
				);
				$this->_registry->get( 'model.event.instance' )->clean(
					$old_post_id,
					$instance_id
				);
				$location = add_query_arg(
					'message',
					1,
					get_edit_post_link( $post_id, 'url' )
				);
				wp_redirect( 
					apply_filters(
						'redirect_post_location',
						$location,
						$post_id
					) 
				);
				exit();
			}
		}
	}
	
	/**
	 * Inject base event edit link for modified instances
	 *
	 * Modified instances are events, belonging to some parent having recurrence
	 * rule, and having some of it's properties altered.
	 *
	 * @param array    $actions List of defined actions
	 * @param stdClass $post Instance being rendered (WP_Post class instance in WP 3.5+)
	 *
	 * @return array Optionally modified $actions list
	 */
	public function post_row_actions( array $actions, $post ) {
		if ( $this->_registry->get( 'acl.aco' )->is_our_post_type( $post ) ) {
			$parent_post_id = $this->event_parent( $post->ID );
			if (
				$parent_post_id &&
				NULL !== ( $parent_post = get_post( $parent_post_id ) ) &&
				isset( $parent_post->post_status ) &&
				'trash' !== $parent_post->post_status
			) {
				$parent_link = get_edit_post_link(
					$parent_post_id,
					'display'
				);
				$actions['ai1ec_parent'] = sprintf(
					'<a href="%s" title="%s">%s</a>',
					wp_nonce_url( $parent_link ),
					sprintf(
						__( 'Edit &#8220;%s&#8221;', AI1EC_PLUGIN_NAME ),
						apply_filters(
							'the_title',
							$parent_post->post_title,
							$parent_post->ID
						)
					),
					__( 'Base Event', AI1EC_PLUGIN_NAME )
				);
			}
		}
		return $actions;
	}
	
	/**
	 * add_exception_date method
	 *
	 * Add exception (date) to event.
	 *
	 * @param int   $post_id Event edited post ID
	 * @param mixed $date    Parseable date representation to exclude
	 *
	 * @return bool Success
	 */
	public function add_exception_date( $post_id, Ai1ec_Date_Time $date ) {
		$event        = $this->_registry->get( 'model.event', $post_id );
		$dates_list   = explode( ',', $event->get( 'exception_dates' ) );
		if ( empty( $dates_list[0] ) ) {
			unset( $dates_list[0] );
		}
		$date->set_time( 0, 0, 0 );
		$dates_list[] = $date->format(
			'Ymd\THis\Z'
		);
		$event->set( 'exception_dates', implode( ',', $dates_list ) );
		return $event->save( true );
	}
}