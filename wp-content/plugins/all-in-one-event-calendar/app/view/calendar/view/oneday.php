<?php

/**
 * The concrete class for day view.
 *
 * @author     Time.ly Network Inc.
 * @since      2.0
 *
 * @package    AI1EC
 * @subpackage AI1EC.View
 */
class Ai1ec_Calendar_View_Oneday  extends Ai1ec_Calendar_View_Abstract {

	/* (non-PHPdoc)
	 * @see Ai1ec_Calendar_View_Abstract::get_name()
	 */
	public function get_name() {
		return 'oneday';
	}

	/* (non-PHPdoc)
	 * @see Ai1ec_Calendar_View_Abstract::get_content()
	 */
	public function get_content( array $view_args ) {
		$date_system = $this->_registry->get( 'date.system' );
		$settings    = $this->_registry->get( 'model.settings' );
		$defaults    = array(
			'oneday_offset' => 0,
			'cat_ids'       => array(),
			'tag_ids'       => array(),
			'auth_ids'      => array(),
			'post_ids'      => array(),
			'exact_date'    => $date_system->current_time(),
		);
		$args = wp_parse_args( $view_args, $defaults );

		$local_date = $this->_registry
			->get( 'date.time', $args['exact_date'], 'sys.default' )
			->adjust_day( 0 + $args['oneday_offset'] )
			->set_time( 0, 0, 0 );

		$cell_array = $this->get_oneday_cell_array(
			$local_date,
			array(
				'cat_ids'  => $args['cat_ids'],
				'tag_ids'  => $args['tag_ids'],
				'post_ids' => $args['post_ids'],
				'auth_ids' => $args['auth_ids'],
			)
		);
		// Create pagination links.
		$pagination_links = $this->_get_pagination( $args );

		$title    = $local_date->format_i18n(
			$this->_registry->get( 'model.option' )
				->get( 'date_format', 'l, M j, Y' )
		);

		// Calculate today marker's position.
		$now      = $date_system->current_time();
		$midnight = $this->_registry->get( 'date.time', $now )
			->set_time( 0, 0, 0 );
		$now      = $this->_registry->get( 'date.time', $now );
		$now_text = $this->_registry->get( 'view.event.time' )
			->get_short_time( $now );
		$now      = $now->diff_sec( $midnight );

		$is_ticket_button_enabled = apply_filters( 'ai1ec_oneday_ticket_button', false );
		$show_reveal_button       = apply_filters( 'ai1ec_oneday_reveal_button', false );

		$time_format              = $this->_registry->get( 'model.option' )
			->get( 'time_format', Ai1ec_I18n::__( 'g a' ) );

		$view_args = array(
			'title'                    => $title,
			'type'                     => 'oneday',
			'cell_array'               => $cell_array,
			'show_location_in_title'   => $settings->get( 'show_location_in_title' ),
			'now_top'                  => $now,
			'now_text'                 => $now_text,
			'pagination_links'         => $pagination_links,
			'time_format'              => $time_format,
			'done_allday_label'        => false,// legacy
			'done_grid'                => false,// legacy
			'data_type'                => $args['data_type'],
			'data_type_events'         => '',
			'is_ticket_button_enabled' => $is_ticket_button_enabled,
			'show_reveal_button'       => $show_reveal_button,
			'text_full_day'            => __( 'Reveal full day', AI1EC_PLUGIN_NAME ),
			'text_all_day'             => __( 'All-day', AI1EC_PLUGIN_NAME ),
			'text_now_label'           => __( 'Now:', AI1EC_PLUGIN_NAME ),
			'text_venue_separator'     => __( '@ %s', AI1EC_PLUGIN_NAME ),
		);

		// Add navigation if requested.
		$view_args['pagination_links'] = $pagination_links;
		// Add navigation if requested.
		$view_args['navigation'] = $this->_get_navigation( $args['no_navigation'], $view_args );

		return $this->_get_view( $view_args );
	}

	/**
	 * Produce an array of three links for the day view of the calendar.
	 *
	 * Each element is an associative array containing the link's enabled status
	 * ['enabled'], CSS class ['class'], text ['text'] and value to assign to
	 * link's href ['href'].
	 *
	 * @param array $args Current request arguments.
	 *
	 * @return array Array of links.
	 */
	function get_oneday_pagination_links( $args ) {
		$links = array();
		$orig_date = $args['exact_date'];

		// ================
		// = Previous day =
		// ================
		$local_date = $this->_registry
			->get( 'date.time', $args['exact_date'], 'sys.default' )
			->adjust_day( $args['oneday_offset'] - 1 )
			->set_time( 0, 0, 0 );
		$args['exact_date'] = $local_date->format();
		$href       = $this->_registry->get( 'html.element.href', $args );
		$links[]    = array(
			'enabled' => true,
			'class'=> 'ai1ec-prev-day',
			'text' => '<i class="ai1ec-fa ai1ec-fa-chevron-left"></i>',
			'href' => $href->generate_href(),
		);

		// ======================
		// = Minical datepicker =
		// ======================
		$args['exact_date'] = $orig_date;
		$factory = $this->_registry->get( 'factory.html' );
		$links[] = $factory->create_datepicker_link(
			$args,
			$args['exact_date']
		);

		// ============
		// = Next day =
		// ============
		$local_date->adjust_day( +2 ); // above was (-1), (+2) is to counteract
		$args['exact_date'] = $local_date->format();
		$href    = $this->_registry->get( 'html.element.href', $args );
		$links[] = array(
			'enabled' => true,
			'class'   => 'ai1ec-next-day',
			'text'    => '<i class="ai1ec-fa ai1ec-fa-chevron-right"></i>',
			'href'    => $href->generate_href(),
		);

		return $links;
	}

	/**
	 * get_oneday_cell_array function
	 *
	 * Return an associative array of weekdays, indexed by the day's date,
	 * starting the day given by $timestamp, each element an associative array
	 * containing three elements:
	 *   ['today']     => whether the day is today
	 *   ['allday']    => non-associative ordered array of events that are all-day
	 *   ['notallday'] => non-associative ordered array of non-all-day events to
	 *                    display for that day, each element another associative
	 *                    array like so:
	 *     ['top']       => how many minutes offset from the start of the day
	 *     ['height']    => how many minutes this event spans
	 *     ['indent']    => how much to indent this event to accommodate multiple
	 *                      events occurring at the same time (0, 1, 2, etc., to
	 *                      be multiplied by whatever desired px/em amount)
	 *     ['event']     => event data object
	 *
	 * @param int $timestamp    the UNIX timestamp of the first day of the week
	 * @param array $filter     Array of filters for the events returned:
	 *                          ['cat_ids']   => non-associatative array of category IDs
	 *                          ['tag_ids']   => non-associatative array of tag IDs
	 *                          ['post_ids']  => non-associatative array of post IDs
	 *                          ['auth_ids']  => non-associatative array of author IDs
	 *
	 * @return array            array of arrays as per function description
	 */
	function get_oneday_cell_array(
		Ai1ec_Date_Time $start_time,
		array $filter = array(),
		$legacy       = false
	) {
		$search      = $this->_registry->get( 'model.search' );
		$date_system = $this->_registry->get( 'date.system' );

		$loc_start_time = $this->_registry
			->get( 'date.time', $start_time, 'sys.default' )
			->set_time( 0, 0, 0 );
		$loc_end_time   = $this->_registry
			->get( 'date.time', $start_time, 'sys.default' )
			->adjust_day( +1 )
			->set_time( 0, 0, 0 );

		// expand search range to include dates that actually render on this day
		$search_start = $this->_registry->get( 'date.time', $loc_start_time )
			->adjust_day( -1 );
		$search_end    = $this->_registry->get( 'date.time', $loc_end_time )
			->adjust_day( 1 );

		$day_events = $search->get_events_between(
			$search_start,
			$search_end,
			$filter
		);
		$this->_update_meta( $day_events );
		// Split up events on a per-day basis
		$all_events = array();

		$day_start_ts = $loc_start_time->format();
		$day_end_ts   = $loc_end_time->format();

		foreach ( $day_events as $evt ) {
			list( $evt_start, $evt_end ) = $this->
				_get_view_specific_timestamps( $evt );

			// If event falls on this day, make a copy.
			if ( $evt_end > $day_start_ts && $evt_start < $day_end_ts ) {
				$_evt = clone $evt;
				if ( $evt_start < $day_start_ts ) {
					// If event starts before this day, adjust copy's start time
					$_evt->set( 'start', $day_start_ts );
					$_evt->set( 'start_truncated', true );
				}
				if ( $evt_end > $day_end_ts ) {
					// If event ends after this day, adjust copy's end time
					$_evt->set( 'end', $day_end_ts );
					$_evt->set( 'end_truncated', true );
				}

				// Store reference to original, unmodified event, required by view.
				$_evt->set( '_orig', $evt );
				$this->_add_runtime_properties( $_evt );
				// Place copy of event in appropriate category
				if ( $_evt->is_allday() ) {
					$all_events[$day_start_ts]['allday'][] = $_evt;
				} else {
					$all_events[$day_start_ts]['notallday'][] = $_evt;
				}
			}

		}

		// This will store the returned array
		$days = array();

		// Initialize empty arrays for this day if no events to minimize warnings
		if ( ! isset( $all_events[$day_start_ts]['allday'] ) ) {
			$all_events[$day_start_ts]['allday'] = array();
		}
		if ( ! isset( $all_events[$day_start_ts]['notallday'] ) ) {
			$all_events[$day_start_ts]['notallday'] = array();
		}

		$notallday = array();
		$evt_stack = array( 0 ); // Stack to keep track of indentation
		foreach ( $all_events[$day_start_ts]['notallday'] as $evt ) {
			// Calculate top and bottom edges of current event
			$top    = (int)(
				$evt->get( 'start' )->diff_sec( $loc_start_time ) / 60
			);
			$bottom = min(
				$top + ( $evt->get_duration() / 60 ),
				1440
			);

			// While there's more than one event in the stack and this event's
			// top position is beyond the last event's bottom, pop the stack
			while ( count( $evt_stack ) > 1 && $top >= end( $evt_stack ) ) {
				array_pop( $evt_stack );
			}
			// Indentation is number of stacked events minus 1
			$indent = count( $evt_stack ) - 1;
			// Push this event onto the top of the stack
			array_push( $evt_stack, $bottom );

			$notallday[] = array(
				'top'    => $top,
				'height' => $bottom - $top,
				'indent' => $indent,
				'event'  => $evt,
			);
		}

		$today_ymd = $this->_registry->get(
			'date.time',
			$this->_registry->get( 'date.system' )->current_time()
		)->format( 'Y-m-d' );

		$days[$day_start_ts] = array(
			'today'     => 0 === strcmp(
				$today_ymd,
				$start_time->format( 'Y-m-d' )
			),
			'allday'    => $all_events[$day_start_ts]['allday'],
			'notallday' => $notallday,
		);

		return apply_filters(
			'ai1ec_get_oneday_cell_array',
			$days,
			$start_time->format(),
			$filter
		);
	}

	/* (non-PHPdoc)
	 * @see Ai1ec_Calendar_View_Abstract::_add_view_specific_runtime_properties()
	 */
	protected function _add_view_specific_runtime_properties( Ai1ec_Event $event ) {
		$event->set_runtime(
			'multiday',
			$event->get( '_orig' )->is_multiday()
		);
	}

}
