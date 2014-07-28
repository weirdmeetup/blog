<?php
/**
 * Render the request as html.
 *
 * @author     Time.ly Network Inc.
 * @since      2.0
 *
 * @package    AI1EC
 * @subpackage AI1EC.Http.Response.Render.Strategy
 */
class Ai1ec_Render_Strategy_Html extends Ai1ec_Http_Response_Render_Strategy {
	
	/**
	 * @var string the event html.
	 */
	protected $_html;

	/**
	 * @var string The html for the footer of the event.
	 */
	protected $_html_footer;
	
	public function render( array $params ) {
		$this->_html = $params['data'];
		if ( isset( $params['is_event'] ) ) {
			// Filter event post content, in single- and multi-post views
			add_filter( 'the_content', array( $this, 'event_content' ), PHP_INT_MAX - 1 );
			return;
		}
		// Replace page content - make sure it happens at (almost) the very end of
		// page content filters (some themes are overly ambitious here)
		add_filter( 'the_content', array( $this, 'append_content' ), PHP_INT_MAX - 1 );
	}
	
	/**
	 * Append locally generated content to normal page content. By default,
	 * first checks if we are in The Loop before outputting to prevent multiple
	 * calendar display - unless setting is turned on to skip this check.
	 *
	 * @param  string $content Post/Page content
	 * @return string          Modified Post/Page content
	 */
	public function append_content( $content ) {
		$settings = $this->_registry->get( 'model.settings' );
	
		// Include any admin-provided page content in the placeholder specified in
		// the calendar theme template.
		if ( $settings->get( 'skip_in_the_loop_check' ) || in_the_loop() ) {
			$content = str_replace(
				'<!-- AI1EC_PAGE_CONTENT_PLACEHOLDER -->',
				$content,
				$this->_html
			);
		}
		return $content;
	}

	/**
	 * event_content function
	 *
	 * Filter event post content by inserting relevant details of the event
	 * alongside the regular post content.
	 *
	 * @param string $content Post/Page content
	 *
	 * @return string         Post/Page content
	 **/
	function event_content( $content ) {

		// if we have modified the content, we return the modified version.
		$to_return = $this->_html . $content;
		if ( isset( $this->_html_footer ) ) {
			$to_return .= $this->_html_footer;
		}
		// Pass the orginal content to the filter so that it can be modified
		return apply_filters(
			'ai1ec_event_content',
			$to_return,
			$content
		);
	}
}