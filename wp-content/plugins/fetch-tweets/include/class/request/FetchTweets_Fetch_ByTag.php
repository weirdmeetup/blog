<?php
/**
 * Provides methods to fetch tweets by tag.
 * 
 * @package			Fetch Tweets
 * @subpackage		
 * @copyright		Michael Uno
 * @since			2
 */
abstract class FetchTweets_Fetch_ByTag extends FetchTweets_Fetch_Template {
	
	public function getTweetsOutputByTag( $arrArgs ) {
		
		// Called from the shortcode callback.
		// Capture the output buffer
		ob_start(); // start buffer
		$this->drawTweetsByTag( $arrArgs );
		$strContent = ob_get_contents(); // assign the content buffer to a variable
		ob_end_clean(); // end buffer and remove the buffer		
		return $strContent;
		
	}	
	public function drawTweetsByTag( $arrArgs ) {

		// Called from either the above getTweetsOutputByTag() method for the shortcode callbeck or fetchTweets() function.
		$arrArgs['tag'] = isset( $arrArgs['tags'] ) && ! empty( $arrArgs['tags'] ) 
			? $arrArgs['tags'] 
			: ( isset( $arrArgs['tag'] ) 
				? $arrArgs['tag']
				: null );	// backward compatibility
		$arrArgs['tag'] = is_array( $arrArgs['tag'] ) ? $arrArgs['tag'] : preg_split( "/[,]\s*/", trim( ( string ) $arrArgs['tag'] ), 0, PREG_SPLIT_NO_EMPTY );
		$arrArgs = ( array ) $arrArgs + $this->oOption->aOptions['default_values'] + $this->oOption->aStructure_DefaultParams + $this->oOption->aStructure_DefaultTemplateOptions;
		$arrArgs['id'] = isset( $arrArgs['tag_field_type'] ) && in_array( strtolower( $arrArgs['tag_field_type'] ), array( 'id', 'slug' ) )
			? $this->getPostIDsByTag( $arrArgs['tag'], $arrArgs['tag_field_type'], trim( $arrArgs['operator'] ) )
			: $this->getPostIDsByTagName( $arrArgs['tag'], trim( $arrArgs['operator'] ) );
	
		$this->drawTweets( $arrArgs );
			
	}
	public function getPostIDsByTagName( $vTermNames, $strOperator='AND' ) {	// public as the feeder extension uses it.
		
		$arrTermSlugs = array();
		foreach( ( array ) $vTermNames as $strTermName ) {
			
			$arrTerm = get_term_by( 'name', $strTermName, FetchTweets_Commons::TagSlug, ARRAY_A );
			$arrTermSlugs[] = $arrTerm['slug'];
			
		}
		return $this->getPostIDsByTag( $arrTermSlugs, 'slug', $strOperator );
				
	}
	
	/**
	 * 
	 * @remark			The scope is public as the feeder extension uses it.
	 */
	public function getPostIDsByTag( $arrTermSlugs, $strFieldType='slug', $strOperator='AND' ) {	

		if ( empty( $arrTermSlugs ) )
			return array();
			
		$strFieldType = $this->sanitizeFieldKey( $strFieldType );

		$arrPostObjects = get_posts( 
			array(
				'post_type' => FetchTweets_Commons::PostTypeSlug,	// fetch_tweets
				'posts_per_page' => -1, // ALL posts
				'tax_query' => array(
					array(
						'taxonomy' => FetchTweets_Commons::TagSlug,	// fetch_tweets_tag
						'field' => $strFieldType,	// id or slug
						'terms' => $arrTermSlugs,	// the array of term slugs
						'operator' => $this->sanitizeOperator( $strOperator ),	// 'IN', 'NOT IN', 'AND. If the item is only one, use AND.
					)
				)
			)
		);
		$arrIDs = array();
		foreach( $arrPostObjects as $oPost )
			$arrIDs[] = $oPost->ID;
		return array_unique( $arrIDs );
		
	}
		protected function sanitizeFieldKey( $strField ) {
			switch( strtolower( trim( $strField ) ) ) {
				case 'id':
					return 'id';
				default:
				case 'slug':
					return 'slug';
			}		
		}
		protected function sanitizeOperator( $strOperator ) {
			switch( strtoupper( trim( $strOperator ) ) ) {
				case 'NOT IN':
					return 'NOT IN';
				case 'IN':
					return 'IN';
				default:
				case 'AND':
					return 'AND';
			}
		}
	
}