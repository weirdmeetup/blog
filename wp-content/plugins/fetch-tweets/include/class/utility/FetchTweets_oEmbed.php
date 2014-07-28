<?php
require_once( ABSPATH . WPINC . '/class-oembed.php' );
/**
 * Fixes issues of the WordPress built-in oEmbed class.
 * 
 * 
 * @since			1.2.0
 * @since			1.3.3.1
 */ 
class FetchTweets_oEmbed extends WP_oEmbed {

	function __construct() {
		
		parent::__construct();
		
		// This should be done by the bootstrap script but just in case.
		if ( ! isset( $GLOBALS['arrFetchTweets_oEmbed'] ) ) $GLOBALS['arrFetchTweets_oEmbed'] = array();
		
		// Apply a fix for recent Instagram's image url format change.
		add_filter( 'oembed_result', array( $this, 'sanitizeOEmbedResult' ), 10, 3 );
		
	}
	
	/**
	 * The do-it-all function that takes a URL and attempts to return the HTML.
	 *
	 * @see WP_oEmbed::discover()
	 * @see WP_oEmbed::fetch()
	 * @see WP_oEmbed::data2html()
	 *
	 * @param string $url The URL to the content that should be attempted to be embedded.
	 * @param array $args Optional arguments. Usually passed from a shortcode.
	 * @return bool|string False on failure, otherwise the UNSANITIZED (and potentially unsafe) HTML that should be used to embed.
	 */
	public function get_html( $strURL, $arrArgs=array() ) {
		
		if ( isset( $GLOBALS['arrFetchTweets_oEmbed'][ $strURL ] ) )
			return $GLOBALS['arrFetchTweets_oEmbed'][ $strURL ];
			
		$strHTML = parent::get_html( $strURL, $arrArgs );
		
		// Store the result in the global array.
		$GLOBALS['arrFetchTweets_oEmbed'][ $strURL ] = $strHTML;
		
		return $strHTML;
		
	}

	/**
	 * Attempts to find oEmbed provider discovery <link> tags at the given URL.
	 *
	 * @param string $url The URL that should be inspected for discovery <link> tags.
	 * @return bool|string False on failure, otherwise the oEmbed provider URL.
	 */
	function discover( $url ) {
		$providers = array();

		// Fetch URL content
		$strHTML = wp_remote_get( $url );	
		if ( $html = wp_remote_retrieve_body( wp_remote_get( $strHTML ) ) ) {	// this is the part that causes a strict standard warning as an expression is passed as reference 

			// <link> types that contain oEmbed provider URLs
			$linktypes = apply_filters( 'oembed_linktypes', array(
				'application/json+oembed' => 'json',
				'text/xml+oembed' => 'xml',
				'application/xml+oembed' => 'xml', // Incorrect, but used by at least Vimeo
			) );

			// Strip <body>
			$html = substr( $html, 0, stripos( $html, '</head>' ) );

			// Do a quick check
			$tagfound = false;
			foreach ( $linktypes as $linktype => $format ) {
				if ( stripos($html, $linktype) ) {
					$tagfound = true;
					break;
				}
			}

			if ( $tagfound && preg_match_all( '/<link([^<>]+)>/i', $html, $links ) ) {
				foreach ( $links[1] as $link ) {
					$atts = shortcode_parse_atts( $link );

					if ( !empty($atts['type']) && !empty($linktypes[$atts['type']]) && !empty($atts['href']) ) {
						$providers[$linktypes[$atts['type']]] = $atts['href'];

						// Stop here if it's JSON (that's all we need)
						if ( 'json' == $linktypes[$atts['type']] )
							break;
					}
				}
			}
		}

		// JSON is preferred to XML
		if ( !empty($providers['json']) )
			return $providers['json'];
		elseif ( !empty($providers['xml']) )
			return $providers['xml'];
		else
			return false;
	}
	
	/**
	 * Sanitizes oEmebed results.
	 * 
	 * @remark			Currently only Instagram's images will be checked.
	 * @since			1.3.3.1
	 */
	public function sanitizeOEmbedResult( $strHTML, $strURL, $arrArgs ) {
		
		// A fix for Instagram - src="http://distilleryimage7.ak.instagram.com/0a0e19404b4a11e38add1240bd2c384e_7.jpg  ---> <img src="http://distilleryimage7.ak.instagram.com/0a0e19404b4a11e38add1240bd2c384e_8.jpg
		if ( preg_match( "/:\/\/instagr/", $strURL ) ) 	
			$strHTML = preg_replace_callback( '/(\ssrc=(["\']))(.+_)(\d)(\..+?)(\2)/i', array( $this, 'callBackForInstagramImageURLReplacement' ) , $strHTML );	// ' syntax fixer
			// $strHTML = preg_replace( '/\ssrc=(["\']).+_\K\d(?=\..+?\1)/i', '8' , $strHTML );	// ' syntax fixer
			
		return $strHTML;
		
	}
		/**
		 * A helper function for the above sanitizeOEmbedResult() method.
		 * 
		 * @since			1.3.3.2
		 */
		public function callBackForInstagramImageURLReplacement( $arrMatches ) {
			
			if ( count( $arrMatches ) != 7 ) return $arrMatches[ 0 ];
			
			/* 
				Array (
					[0] =>  src="http://distilleryimage0.ak.instagram.com/16d3f5fac72411e2822f22000a9f09ca_7.jpg"
					[1] =>  src="
					[2] => "
					[3] => http://distilleryimage0.ak.instagram.com/16d3f5fac72411e2822f22000a9f09ca_
					[4] => 7
					[5] => .jpg
					[6] => "
				)				
			*/
			
			// If the image exists, 
			if ( @getimagesize( $arrMatches[ 3 ] . $arrMatches[ 4 ] . $arrMatches[ 5 ] ) )
				return $arrMatches[ 0 ];	// do not change
			
			// Increment the suffixed number. e.g. _7 -> _8.
			return $arrMatches[ 1 ] . $arrMatches[ 3 ] . ( $arrMatches[ 4 ] + 1 ) . $arrMatches[ 5 ] . $arrMatches[ 6 ] ;	
			
		}
	
}