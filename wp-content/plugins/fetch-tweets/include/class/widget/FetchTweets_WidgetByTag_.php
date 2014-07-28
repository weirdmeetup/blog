<?php

abstract class FetchTweets_WidgetByTag_ extends FetchTweets_Widget_Base {
	
	public static function registerWidget() {
		return register_widget( 'FetchTweets_WidgetByTag' );	// the class name - get_class( self ) does not work.
	}	
	
	public function __construct() {
				
		$this->_aStructure_FormElements = $this->_aStructure_FormElements + array(
			'selected_tag_slugs' => array(),
			'operator' => 'AND',		
		);
				
		parent::__construct(
	 		'fetch_tweets_widget_by_tag', // base ID
			'Fetch Tweets by Tag', 	// widget name
			array( 'description' => __( 'A widget that fetches tweets by tag.', 'fetch-tweets' ), ) 
		);
		
	}
	
	protected function echoTweets( $aInstance ) {
		
		$aInstance = $aInstance + $this->_aStructure_FormElements;
		fetchTweets( 
			array( 	
				'tag_field_type'		=>	'slug',
				'tags'					=>	$aInstance['selected_tag_slugs'],
				'count'					=>	$aInstance['count'],
				'operator'				=>	$aInstance['operator'],
				'twitter_media'			=>	$aInstance['twitter_media'],
				'external_media'		=>	$aInstance['external_media'],
				// Template Options
				'template'				=>	$aInstance['template'],
				'avatar_size'			=>	$aInstance['avatar_size'],
				'height'				=>	$aInstance['height'],
				'height_unit'			=>	$aInstance['height_unit'],
				'width'					=>	$aInstance['width'],
				'width_unit'			=>	$aInstance['width_unit'],						
			) 
		);	
		
	}
	
	protected function echoFormElements( $aInstance, $aIDs, $aNames ) {
		
		?>
		<label for="<?php echo $aIDs['title']; ?>">
			<?php _e( 'Title', 'fetch-tweets' ); ?>:
		</label>
		<p>
			<input type="text" name="<?php echo $aNames['title']; ?>" id="<?php echo $aIDs['title']; ?>" value="<?php echo $aInstance['title']?>"/>
		</p>
		
		<label for="<?php echo $aIDs['selected_tag_slugs']; ?>">
			<?php _e( 'Select Tags', 'fetch-tweets' ); ?>:
		</label>
		<br />
		<select name="<?php echo $aNames['selected_tag_slugs']; ?>[]" id="<?php echo $aIDs['selected_tag_slugs']; ?>"  multiple style="min-width: 220px;">
			<?php 
			foreach( $this->getTagSlugArrays() as $sTagSlug => $sTagName ) 
				echo "<option value='{$sTagSlug}' "				
					. ( in_array( $sTagSlug, $aInstance['selected_tag_slugs'] ) ? 'selected="Selected"' : '' )
					. ">"
					. $sTagName
					. "</option>";
			?>
		</select>
		<p class="description" style="margin-top: 10px;">
			<?php _e( 'Hold down the Ctrl (windows) / Command (Mac) key to select multiple items.', 'fetch-tweets' ); ?>
		</p>	 
		
		<p>
		<?php _e( 'Apply the rule sets that have:', 'fetch-tweets' ); ?>
			<span style="display: block; margin: 8px;">
				<input id="<?php echo $aIDs['operator']; ?>[0]" type="radio" name="<?php echo $aNames['operator']; ?>" value="AND" <?php echo $aInstance['operator'] == 'AND' ? "Checked" : ""; ?> />
				<label for="<?php echo $aIDs['operator']; ?>[0]">&nbsp;<?php _e( 'All', 'fetch-tweets' ); ?></label>
				&nbsp;&nbsp;
				<input id="<?php echo $aIDs['operator']; ?>[1]" type="radio" name="<?php echo $aNames['operator']; ?>" value="IN" <?php echo $aInstance['operator'] == 'IN' ? "Checked" : ""; ?> />
				<label for="<?php echo $aIDs['operator']; ?>[1]">&nbsp;<?php _e( 'Any', 'fetch-tweets' ); ?></label>
				&nbsp;&nbsp;
				<input id="<?php echo $aIDs['operator']; ?>[2]" type="radio" name="<?php echo $aNames['operator']; ?>" value="NOT IN" <?php echo $aInstance['operator'] == 'NOT IN' ? "Checked" : ""; ?> />
				<label for="<?php echo $aIDs['operator']; ?>[2]">&nbsp;<?php _e( 'None', 'fetch-tweets' ); ?></label>
				
			</span>
			<?php _e( 'of the selected tags.', 'fetch-tweets' ); ?>
		</p>
		
		<label for="<?php echo $aIDs['count']; ?>">
			<?php _e( 'The maximum number of tweets to show', 'fetch-tweets' ); ?>:
		</label>
		<br />
		<p>
			<input type="number" id="<?php echo $aIDs['count']; ?>" name="<?php echo $aNames['count']; ?>" min="1" value="<?php echo $aInstance['count']?>"/>
		</p>
		<p class="description" style="margin-top: 10px; padding-bottom: 5px;">	
			<?php _e( 'Default', 'fetch-tweets' ); ?>: 20
		</p>

		<p>
			<label for="<?php echo $aIDs['twitter_media']; ?>">
				<input type="hidden" name="<?php echo $aNames['twitter_media']; ?>" value=0 />
				<input type="checkbox" id="<?php echo $aIDs['twitter_media']; ?>" name="<?php echo $aNames['twitter_media']; ?>" value="1" <?php echo $aInstance['twitter_media'] ? 'checked="Checked"': ''; ?> />
				<?php _e( 'Show Twitter media.', 'fetch-tweets' ); ?>
			</label>
		</p>
		
		<p>
			<label for="<?php echo $aIDs['external_media']; ?>">
				<input type="hidden" name="<?php echo $aNames['external_media']; ?>" value=0 />
				<input type="checkbox" id="<?php echo $aIDs['external_media']; ?>" name="<?php echo $aNames['external_media']; ?>" value="1" <?php echo $aInstance['external_media'] ? 'checked="Checked"': ''; ?> />
				<?php _e( 'Show external media.', 'fetch-tweets' ); ?>
			</label>
		</p>		
				
		
		
		<p>
			<label for="<?php echo $aIDs['template']; ?>">
				<?php _e( 'Select a Template', 'fetch-tweets' ); ?>:
			</label>
			<br />
			<select name="<?php echo $aNames['template']; ?>" id="<?php echo $aIDs['template']; ?>" >
				<?php 
				foreach( $GLOBALS['oFetchTweets_Templates']->getTemplateArrayForSelectLabel() as $sTemplateSlug => $sTemplateName ) 
					echo "<option value='{$sTemplateSlug}' "				
						. ( $aInstance['template'] == $sTemplateSlug ? 'selected="Selected"' : '' )
						. ">"
						. $sTemplateName
						. "</option>";
				?>
			</select>
		</p>
		
		<label for="<?php echo $aIDs['avatar_size']; ?>">
			<?php _e( 'The profile image size in pixel.', 'fetch-tweets' ); ?>:
		</label>
		<p>
			<input type="number" id="<?php echo $aIDs['avatar_size']; ?>" name="<?php echo $aNames['avatar_size']; ?>" min="0" value="<?php echo $aInstance['avatar_size']?>" />
		</p>
		<p class="description" style="margin-top: 10px;">	
			<?php _e( 'Set 0 for no avatar.', 'fetch-tweets' ); ?> <?php _e( 'Default', 'fetch-tweets' ); ?>: 48
		</p>
		
		<label for="<?php echo $aIDs['width']; ?>">
			<?php _e( 'The width of the output.', 'fetch-tweets' ); ?>:
		</label>
		<p>
			<input type="number" id="<?php echo $aIDs['width']; ?>" name="<?php echo $aNames['width']; ?>" min="0" value="<?php echo $aInstance['width']?>"/>
			<select name="<?php echo $aNames['width_unit']; ?>" id="<?php echo $aIDs['width_unit']; ?>" >
				<?php 
				foreach( array( 'px' => 'px', '%' => '%', 'em' => 'em' ) as $sUnitKey => $sUnitName ) 
					echo "<option value='{$sUnitKey}' "				
						. ( $aInstance['width_unit'] == $sUnitKey ? 'selected="Selected"' : '' )
						. ">"
						. $sUnitName
						. "</option>";
				?>
			</select>						
		</p>
		<p class="description" style="margin-top: 10px;">	
			<?php _e( 'Set 0 for no limit.', 'fetch-tweets' ); ?> <?php _e( 'Default', 'fetch-tweets' ); ?>: <code>100 %</code>.
		</p>		
			
		<label for="<?php echo $aIDs['height']; ?>">
			<?php _e( 'The height of the output.', 'fetch-tweets' ); ?>:
		</label>
		<p>
			<input type="number" id="<?php echo $aIDs['height']; ?>" name="<?php echo $aNames['height']; ?>" min="0" value="<?php echo $aInstance['height']?>"/>
			<select name="<?php echo $aNames['height_unit']; ?>" id="<?php echo $aIDs['height_unit']; ?>" >
				<?php 
				foreach( array( 'px' => 'px', '%' => '%', 'em' => 'em' ) as $sUnitKey => $sUnitName ) 
					echo "<option value='{$sUnitKey}' "				
						. ( $aInstance['height_unit'] == $sUnitKey ? 'selected="Selected"' : '' )
						. ">"
						. $sUnitName
						. "</option>";
				?>
			</select>						
		</p>
		<p class="description" style="margin-top: 10px;">	
			<?php _e( 'Set 0 for no limit.', 'fetch-tweets' ); ?> <?php _e( 'Default', 'fetch-tweets' ); ?>: <code>400 px</code>.
		</p>	
			
		<?php
		
	}
	
	public function update( $aNewInstance, $aOldInstance ) {
		
		$aNewInstance['count'] = $this->fixNumber( $aNewInstance['count'], 20, 1 );
		$aNewInstance['avatar_size'] = $this->fixNumber( $aNewInstance['avatar_size'], 48, 0 );

        return $aNewInstance;
    }
	
	
	/*
	 * Private methods
	 * */
	protected function getTagSlugArrays() {
		$_aTagSlugs = array();
		$_aTagObjects = get_terms( 
			FetchTweets_Commons::TagSlug,			// taxonomy slug
			array(
				'hide_empty' => true,
			) 
		);
		foreach( $_aTagObjects as $__oTerm ) 
			$_aTagSlugs[ $__oTerm->slug ] = $__oTerm->name;		
		return $_aTagSlugs;
	}
	
}