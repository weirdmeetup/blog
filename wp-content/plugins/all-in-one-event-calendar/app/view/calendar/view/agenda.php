<?php

/**
 * The concrete class for agenda view.
 *
 * @author     Time.ly Network Inc.
 * @since      2.0
 *
 * @package    AI1EC
 * @subpackage AI1EC.View
 */
class Ai1ec_Calendar_View_Agenda extends Ai1ec_Calendar_View_Abstract {

	/* (non-PHPdoc)
	 * @see Ai1ec_Calendar_View_Abstract::get_name()
	*/
	public function get_name() {
		return 'agenda';
	}

	/* (non-PHPdoc)
	 * @see Ai1ec_Calendar_View_Abstract::get_content()
	*/
	public function get_content( array $view_args ) {
		$type = $this->get_name();
		$time = $this->_registry->get( 'date.system' );
		// Get localized time
		$timestamp = $time->current_time();

		// Get events, then classify into date array
		$per_page_setting = $type . '_events_per_page';
		$search = $this->_registry->get( 'model.search' );
		$settings = $this->_registry->get( 'model.settings' );
		$results = $search->get_events_relative_to(
			$timestamp,
			$settings->get( $per_page_setting ),
			$view_args['page_offset'],
			array(
				'post_ids' => $view_args['post_ids'],
				'auth_ids' => $view_args['auth_ids'],
				'cat_ids'  => $view_args['cat_ids'],
				'tag_ids'  => $view_args['tag_ids'],
			),
			$view_args['time_limit']
		);
		$this->_update_meta( $results['events'] );
		$dates = $this->get_agenda_like_date_array(
			$results['events'],
			$view_args['request']
		);

		// Create pagination links.
		$pagination_links = '';
		$loader  = $this->_registry->get( 'theme.loader' );
		if( ! $view_args['no_navigation'] ) {
			$pagination_links =
			$this->_get_agenda_like_pagination_links(
				$view_args,
				$results['prev'],
				$results['next'],
				$results['date_first'],
				$results['date_last']
			);

			$pagination_links = $loader->get_file(
				'pagination.twig',
				array(
					'links'      => $pagination_links,
					'data_type'  => $view_args['data_type'],
				),
				false
			)->get_content();
		}
		// Generate title of view based on date range month & year.
		$range_start = $results['date_first'] ? $results['date_first'] : $this->_registry->get( 'date.time', $timestamp );
		$range_end   = $results['date_last']  ? $results['date_last'] : $this->_registry->get( 'date.time', $timestamp );
		$range_start = $this->_registry->get( 'date.time', $range_start );
		$range_end   = $this->_registry->get( 'date.time', $range_end );
		$start_year  = $range_start->format_i18n( 'Y' );
		$end_year    = $range_end->format_i18n( 'Y' );
		$start_month = $range_start->format_i18n( 'F' );
		$end_month   = $range_end->format_i18n( 'F' );
		if ( $start_year === $end_year && $start_month === $end_month ) {
			$title_date_range = "$start_month $start_year";
		} elseif ( $start_year === $end_year ) {
			$title_date_range = "$start_month – $end_month $end_year";
		} else {
			$title_date_range = "$start_month $start_year – $end_month $end_year";
		}
		$is_ticket_button_enabled = apply_filters( 'ai1ec_' . $type . '_ticket_button', false );
		$args = array(
			'title'                     => $title_date_range,
			'dates'                     => $dates,
			'type'                      => $type,
			'show_year_in_agenda_dates' => $settings->get( 'show_year_in_agenda_dates' ),
			'expanded'                  => $settings->get( 'agenda_events_expanded' ),
			'show_location_in_title'    => $settings->get( 'show_location_in_title' ),
			'page_offset'               => $view_args['page_offset'],
			'pagination_links'          => $pagination_links,
			'post_ids'                  => join( ',', $view_args['post_ids'] ),
			'data_type'                 => $view_args['data_type'],
			'data_type_events'          => '',
			'is_ticket_button_enabled'  => $is_ticket_button_enabled,
			'text_collapse_all'         => __( 'Collapse All', AI1EC_PLUGIN_NAME ),
			'text_expand_all'           => __( 'Expand All', AI1EC_PLUGIN_NAME ),
			'text_upcoming_events'      => __( 'There are no upcoming events to display at this time.', AI1EC_PLUGIN_NAME ),
			'text_edit'                 => __( 'Edit', AI1EC_PLUGIN_NAME ),
			'text_read_more'            => __( 'Read more', AI1EC_PLUGIN_NAME ),
			'text_categories'           => __( 'Categories:', AI1EC_PLUGIN_NAME ),
			'text_tags'                 => __( 'Tags:', AI1EC_PLUGIN_NAME ),
			'text_venue_separator'      => __( '@ %s', AI1EC_PLUGIN_NAME ),
		);
		if( $settings->get( 'ajaxify_events_in_web_widget' ) ) {
			$args['data_type_events'] = $view_args['data_type'];
		}
		// Add extra buttons to Agenda view if events were returned.
		if ( $type === 'agenda' && $dates ) {
			$args['before_pagination'] =
				$loader->get_file( 'agenda-buttons.twig', $args, false )->get_content();
		}
		$navigation = '';
		if ( true !== $view_args['no_navigation'] ) {
			$navigation = $loader->get_file(
				'navigation.twig',
				$args,
				false
			)->get_content();
		}
		$args['navigation'] = $navigation;

		// Allow child views to modify arguments passed to template.
		$args = $this->get_extra_template_arguments( $args );

		$file = $loader->get_file( $type . '.twig', $args, false );

		return apply_filters(
			'ai1ec_get_' . $type . '_view',
			$file->get_content(),
			$view_args
		);
	}

	/* (non-PHPdoc)
	 * @see Ai1ec_Calendar_View_Abstract::get_extra_arguments()
	 */
	public function get_extra_arguments( array $view_args, $exact_date ) {
		$view_args += $this->_request->get_dict( array(
			'page_offset',
			'time_limit',
		) );
		if( false !== $exact_date ) {
			$view_args['time_limit'] = $exact_date;
		}
		return $view_args;
	}

	/**
	 * Breaks down the given ordered array of event objects into dates, and
	 * outputs an ordered array of two-element associative arrays in the
	 * following format:
	 *	key: localized UNIX timestamp of date
	 *	value:
	 *		['events'] => two-element associatative array broken down thus:
	 *			['allday'] => all-day events occurring on this day
	 *			['notallday'] => all other events occurring on this day
	 *		['today'] => whether or not this date is today
	 *
	 * @param array                     $events Event results
	 * @param Ai1ec_Abstract_Query|null $query  Current calendar page request, if
	 *                                          any (null for widget)
	 *
	 * @return array
	 */
	public function get_agenda_like_date_array(
		$events,
		Ai1ec_Abstract_Query $query = null
	) {
		$dates    = array();
		$time     = $this->_registry->get( 'date.system' );
		$settings = $this->_registry->get( 'model.settings' );
		// Classify each event into a date/allday category
		foreach ( $events as $event ) {
			$start_time    = $this->_registry
				->get(
					'date.time',
					$event->get( 'start' )->format( 'Y-m-d\T00:00:00' ),
					'sys.default'
				);
			$exact_date    = $time->format_datetime_for_url(
				$start_time,
				$settings->get( 'input_date_format' )
			);
			$href_for_date = $this->_create_link_for_day_view( $exact_date );
			// timestamp is used to have correctly sorted array as UNIX
			// timestamp never goes in decreasing order for increasing dates.
			$timestamp     = $start_time->format();
			// Ensure all-day & non all-day categories are created in correct
			// order: "allday" preceding "notallday".
			if ( ! isset( $dates[$timestamp]['events'] ) ) {
				$dates[$timestamp]['events'] = array(
					'allday'    => array(),
					'notallday' => array(),
				);
			}
			$this->_add_runtime_properties( $event );
			// Add the event.
			$category = $event->is_allday() ? 'allday' : 'notallday';
			$dates[$timestamp]['events'][$category][] = $event;
			$dates[$timestamp]['href'] = $href_for_date;
		}
		// Flag today
		$today = $this->_registry->get( 'date.time' )
			->set_time( 0, 0, 0 )
			->format();
		if ( isset( $dates[$today] ) ) {
			$dates[$today]['today'] = true;
		}
		return $dates;
	}

	/**
	 * Returns an associative array of two links for any agenda-like view of the
	 * calendar:
	 *    previous page (if previous events exist),
	 *    next page (if next events exist).
	 * Each element is an associative array containing the link's enabled status
	 * ['enabled'], CSS class ['class'], text ['text'] and value to assign to
	 * link's href ['href'].
	 *
	 * @param array $args Current request arguments
	 *
	 * @param bool  $prev   Whether there are more events before the current page
	 * @param bool  $next   Whether there are more events after the current page
	 * @param int|null $date_first
	 * @param int|null $date_last
	 *
	 * @return array      Array of links
	 */
	protected function _get_agenda_like_pagination_links(
		$args,
		$prev = false,
		$next = false,
		$date_first = null,
		$date_last  = null
	) {

		$links = array();

		$args['page_offset'] = -1;
		$args['time_limit']  = $this->_registry
			->get( 'date.time', $date_first )->set_time(
				$date_first->format( 'H' ),
				$date_first->format( 'i' ),
				$date_first->format( 's' ) - 1
			)->format_to_gmt();
		$href = $this->_registry->get(
			'html.element.href',
			$args
		);
		$links[] = array(
			'class'   => 'ai1ec-prev-page',
			'text'    => '<i class="ai1ec-fa ai1ec-fa-chevron-left"></i>',
			'href'    => $href->generate_href(),
			'enabled' => $prev,
		);

		// Minical datepicker.
		$factory = $this->_registry->get( 'factory.html' );
		$links[] = $factory->create_datepicker_link(
			$args,
			$date_first->format_to_gmt()
		);

		$args['page_offset'] = 1;
		$args['time_limit']  = $this->_registry
			->get( 'date.time', $date_last )->set_time(
				$date_last->format( 'H' ),
				$date_last->format( 'i' ),
				$date_last->format( 's' ) + 1
			)->format_to_gmt();
		$href = $this->_registry->get(
			'html.element.href',
			$args
		);
		$links[] = array(
			'class'   => 'ai1ec-next-page',
			'text'    => '<i class="ai1ec-fa ai1ec-fa-chevron-right"></i>',
			'href'    => $href->generate_href(),
			'enabled' => $next,
		);

		return $links;
	}

	/* (non-PHPdoc)
	 * @see Ai1ec_Calendar_View_Abstract::_add_view_specific_runtime_properties()
	*/
	protected function _add_view_specific_runtime_properties( Ai1ec_Event $event ) {
		$taxonomy = $this->_registry->get( 'view.event.taxonomy' );
		$event->set_runtime(
			'categories_html',
			$taxonomy->get_categories_html( $event )
		);
		$event->set_runtime(
			'tags_html',
			$taxonomy->get_tags_html( $event )
		);
	}
}
