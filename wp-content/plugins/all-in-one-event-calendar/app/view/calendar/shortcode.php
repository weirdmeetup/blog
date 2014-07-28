<?php
/**
 * The class that handles rendering the shortcode.
 *
 * @author     Time.ly Network Inc.
 * @since      2.0
 *
 * @package    AI1EC
 * @subpackage AI1EC.View
 */
class Ai1ec_View_Calendar_Shortcode extends Ai1ec_Base {
	
	/**
	 * Generate replacement content for [ai1ec] shortcode.
	 *
	 * @param array	 $atts	  Attributes provided on shortcode
	 * @param string $content Tag internal content (shall be empty)
	 * @param string $tag	  Used tag name (must be 'ai1ec' always)
	 *
	 * @staticvar $call_count Used to restrict to single calendar per page
	 *
	 * @return string Replacement for shortcode entry
	 */
	public function shortcode( $atts, $content = '', $tag = 'ai1ec' ) {
		static $call_count = 0;

		++$call_count;
		if ( $call_count > 1 ) { // not implemented
			return false; // so far process only first request
		}

		$settings_view   = $this->_registry->get( 'model.settings-view' );
		$view_names_list = array_keys( $settings_view->get_all() );
		$default_view    = $settings_view->get_default();

		$view_names      = array();
		foreach ( $view_names_list as $view_name ) {
			$view_names[$view_name] = true;
		}

		$view       = $default_view;
		$categories = $tags = $post_ids = array();
		if ( isset( $atts['view'] ) ) {
			if ( 'ly' === substr( $atts['view'], -2 ) ) {
				$atts['view'] = substr( $atts['view'], 0, -2 );
			}
			if ( ! isset( $view_names[$atts['view']] ) ) {
				return false;
			}
			$view = $atts['view'];
		}

		$mappings = array(
			'cat_name' => 'categories',
			'cat_id'   => 'categories',
			'tag_name' => 'tags',
			'tag_id'   => 'tags',
			'post_id'  => 'post_ids',
		);
		foreach ( $mappings as $att_name => $type ) {
			if ( ! isset( $atts[$att_name] ) ) {
				continue;
			}
			$raw_values = explode( ',', $atts[$att_name] );
			foreach ( $raw_values as $argument ) {
				if ( 'post_id' === $att_name ) {
					if ( ( $argument = (int)$argument ) > 0 ) {
						$post_ids[] = $argument;
					}
				} else {
					if ( ! is_numeric( $argument ) ) {
						$search_val = trim( $argument );
						$argument   = false;
						foreach ( array( 'name', 'slug' ) as $field ) {
							$record = get_term_by(
								$field,
								$search_val,
								'events_' . $type
							);
							if ( false !== $record ) {
								$argument = $record;
								break;
							}
						}
						unset( $search_val, $record, $field );
						if ( false === $argument ) {
							continue;
						}
						$argument = (int)$argument->term_id;
					} else {
						if ( ( $argument = (int)$argument ) <= 0 ) {
							continue;
						}
					}
					${$type}[] = $argument;
				}
			}
		}
		$query = array(
			'ai1ec_cat_ids'	 => implode( ',', $categories ),
			'ai1ec_tag_ids'	 => implode( ',', $tags ),
			'ai1ec_post_ids' => implode( ',', $post_ids ),
			'action'         => $view,
			'request_type'   => 'jsonp',
			'shortcode'      => 'true',
		);
		if ( isset( $atts['exact_date'] ) ) {
			$query['exact_date'] = $atts['exact_date'];
		}
		$request = $this->_registry->get(
			'http.request.parser',
			$query,
			$default_view
		);
		$request->parse();
		$page_content = $this->_registry->get( 'view.calendar.page' )
			->get_content( $request );
		$css      = $this->_registry->get( 'css.frontend' )
						->add_link_to_html_for_frontend();
		$js       = $this->_registry->get( 'controller.javascript' )
						->load_frontend_js( true );
		return $page_content;
	}

}