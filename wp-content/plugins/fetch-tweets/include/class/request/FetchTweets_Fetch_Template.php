<?php
/**
 * Provides methods for output templates
 * 
 * @package			Fetch Tweets
 * @subpackage		
 * @copyright		Michael Uno
 * @since			2
 * @filter			fetch_tweets_template_path
 */
abstract class FetchTweets_Fetch_Template extends FetchTweets_Fetch_Format {
	
	/**
	 * Includes the template.
	 * 
	 * @since			2
	 * @param			array			$aTweets			the fetched tweet arrays.
	 * @param			array			$aArgs			the passed arguments such as item count etc.
	 * @param			array			$aOptions			the plugin options saved in the database.
	 */
	protected function _includeTemplate( $aTweets, $aArgs, $aOptions ) {

		// For backward compatibility for v1 - these variables will be accessible from the included template file.
		$arrTweets = & $aTweets;
		$arrArgs = & $aArgs;
		$arrOptions = & $aOptions;
		
		// Retrieve the template slug we are going to use.
		$aArgs['template'] = $this->_getTemplateSlug( ( array ) $aArgs['id'], $aArgs['template'] );
		
		// Call the template. ( template.php )
		include( apply_filters( "fetch_tweets_template_path", $this->_getTemplatePath( $aArgs['id'], $aArgs['template'] ), $aArgs ) );		
		
	}
	
		protected function _getTemplateSlug( $arrPostIDs, $strTemplateSlug='' ) {

			// Return the one defined in the caller argument.
			if ( $strTemplateSlug && isset( $this->oOption->aOptions['arrTemplates'][ $strTemplateSlug ] ) )
				return $this->_checkNecessaryFileExists( $strTemplateSlug );
			
			// Return the one defined in the custom post rule.
			if ( isset( $arrPostIDs[ 0 ] ) )
				$strTemplateSlug = get_post_meta( $arrPostIDs[ 0 ], 'fetch_tweets_template', true );

			$strTemplateSlug = $this->_checkNecessaryFileExists( $strTemplateSlug );
			
			// Find the default template slug.
			if ( 
				empty( $strTemplateSlug ) 
				|| ! isset( $this->oOption->aOptions['arrTemplates'][ $strTemplateSlug ] ) 
			)
				return $GLOBALS['oFetchTweets_Templates']->getDefaultTemplateSlug();
			
			// Something wrong happened.
			return $strTemplateSlug;
			
		}
			protected function _checkNecessaryFileExists( $strTemplateSlug ) {
				
				// Check if the necessary file is present. Otherwise, return the default template slug.
				if ( 
					( ! empty( $strTemplateSlug ) || $strTemplateSlug != '' ) 
					&& ( 
						! isset( $this->oOption->aOptions['arrTemplates'][ $strTemplateSlug ] )	// this happens when the options have been reset.
						|| ! file_exists( $this->oOption->aOptions['arrTemplates'][ $strTemplateSlug ]['strDirPath'] . '/template.php' )
						|| ! file_exists( $this->oOption->aOptions['arrTemplates'][ $strTemplateSlug ]['strDirPath'] . '/style.css' )
					)
				)
					return $GLOBALS['oFetchTweets_Templates']->getDefaultTemplateSlug();		
				
				return $strTemplateSlug;
				
			}
			
		protected function _getTemplatePath( $arrPostIDs, $strTemplateSlug ) {
			
			if ( empty( $strTemplateSlug ) && isset( $arrPostIDs[ 0 ] ) ) {
				$strTemplateSlug = get_post_meta( $arrPostIDs[ 0 ], 'fetch_tweets_template', true );
			}
			
			if ( empty( $strTemplateSlug ) || ! isset( $this->oOption->aOptions['arrTemplates'][ $strTemplateSlug ] ) ) {
				return $GLOBALS['oFetchTweets_Templates']->getDefaultTemplatePath();
			}
				
			$strTemplatePath = $this->oOption->aOptions['arrTemplates'][ $strTemplateSlug ]['strTemplatePath'];
			$strTemplatePath = ( ! $strTemplatePath || ! file_exists( $strTemplatePath ) )
				? dirname( $this->oOption->aOptions['arrTemplates'][ $strTemplateSlug ]['strCSSPath'] ) . '/template.php'
				: $strTemplatePath;
				
			return $strTemplatePath;			
			
		}	
	
}