<?php

abstract class FetchTweets_Widget_Base extends WP_Widget {
	
	/**
	 * Represents the array structure and its default values of the widget form elements.
	 * 
	 */
	protected $_aStructure_FormElements = array(
		'title'				=>	null,
		'selected_ids'		=>	array(),
		'count'				=>	20,			// default
		'twitter_media'		=>	1,			// [2.3.3+] 1 or 0
		'external_media'	=>	1,			// [2.3.3+] 1 or 0		
		// template options
		'template'			=>	null,
		'avatar_size'		=>	48,
		'width'				=>	100,
		'width_unit'		=>	'%', 	
		'height'			=>	400,
		'height_unit'		=>	'px',
	);

	
	public static function registerWidget() {
		return register_widget( 'Put_The_Extended_Class_Name_Here' );	// the class name - get_class( self ) does not work.
	}	
	
	/*
	 * Front end methods
	 */
	
	/**
	 * Called when the widget is rendered.
	 */
	public function widget( $aWidgetInfo, $aInstance ) {	// must be public, the protected scope will cause fatal error.
		
		echo $aWidgetInfo['before_widget']; 
		
		// Avoid undefined index warnings.
		$aInstance = $aInstance + $this->_aStructure_FormElements;
		if ( $aInstance['title'] )
			echo "<h3 class='fetch-tweets-widget widget-title'>{$aInstance['title']}</h3>";
		
		$this->echoTweets( $aInstance );
		
		echo $aWidgetInfo['after_widget'];
		
	}	

	/**
	 * Prints the output in the front-end.
	 * 
	 * This method should be overridden in the extended class.
	 * 
	 */
	protected function echoTweets( $aInstance ) {}
	
	
	/*
	 * Back-end methods 
	 */
	
	/**
	 * Called when the widget form element is rendered.
	 */
	public function form( $aInstance ) {	
		
		// Avoid undefined index warnings.
		$aInstance = $aInstance + $this->_aStructure_FormElements;
		$aInstance['template'] = isset( $aInstance['template'] ) 
			? $aInstance['template']
			: $GLOBALS['oFetchTweets_Templates']->getDefaultTemplateSlug();
		$aIDs = $this->_getFieldValues( 'id' );
		$aNames = $this->_getFieldValues( 'name' );
		
		$this->echoFormElements( $aInstance, $aIDs, $aNames );
		
	}
		
		/**
		 * Returns an array of filed values by a specified field.
		 * $sField can be either name or id.
		 */
		private function _getFieldValues( $sField='id' ) {
			
			$_aFields = array();
			foreach( $this->_aStructure_FormElements as $__sFieldKey => $__v )  
				$_aFields[ $__sFieldKey ] = 'id' == $sField
					? $this->get_field_id( $__sFieldKey )
					: $this->get_field_name( $__sFieldKey );
		
			return $_aFields;
			
		}
		
	/**
	 * Renders form elements in the extended class method.
	 * 
	 * This method should be overridden in the extended class.
	 */
	protected function echoFormElements( $aInstance, $aIDs, $aNames ) {
	}		
	
	public function update( $aNewInstance, $aOldInstance ) {
		
		$aNewInstance['count'] = $this->fixNumber( $aNewInstance['count'], 20, 1 );
		$aNewInstance['avatar_size'] = $this->fixNumber( $aNewInstance['avatar_size'], 48, 0 );

        return $aNewInstance;
    }
		protected function fixNumber( $numToFix, $numDefault, $numMin="", $numMax="" ) {
				
			if ( ! is_numeric( trim( $numToFix ) ) ) return $numDefault;
			if ( $numMin !== "" && $numToFix < $numMin ) return $numMin;
			if ( $numMax !== "" && $numToFix > $numMax ) return $numMax;
			return $numToFix;
			
		}	

}