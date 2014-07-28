<?php

abstract class FetchTweets_WidgetByID_ extends FetchTweets_Widget_Base {


	public static function registerWidget() {
		return register_widget( 'FetchTweets_WidgetByID' );	// the class name - get_class( self ) does not work.
	}	
	
	public function __construct() {
						
		parent::__construct(
	 		'fetch_tweets_widget_by_id', // base ID
			'Fetch Tweets by Rule Set', 	// widget name
			array( 'description' => __( 'A widget that fetches tweets by rule set.', 'fetch-tweets' ), ) 
		);
		
	}

	protected function echoTweets( $aInstance ) {
		
		$aInstance = $aInstance + $this->_aStructure_FormElements;
		fetchTweets( 
			array( 	// $aArgs
				'ids'					=>	$aInstance['selected_ids'],
				'count'					=>	$aInstance['count'],
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
		
		<label for="<?php echo $aIDs['selected_ids']; ?>">
			<?php _e( 'Select Rules', 'fetch-tweets' ); ?>:
		</label>
		<br />
		<select name="<?php echo $aNames['selected_ids']; ?>[]" id="<?php echo $aIDs['selected_ids']; ?>"  multiple style="min-width: 220px;">
			<?php 
			$oQuery = new WP_Query(
				array(
					'post_status' => 'publish', 	// optional
					'post_type' => FetchTweets_Commons::PostTypeSlug,// 'fetch_tweets', //  post_type
					'posts_per_page' => -1, // ALL posts
				)
			);			
			foreach( $oQuery->posts as $oPost ) 
				echo "<option value='{$oPost->ID}' "				
					. ( in_array( $oPost->ID, $aInstance['selected_ids'] ) ? 'selected="Selected"' : '' )
					. ">"
					. $oPost->post_title
					. "</option>";
			?>
		</select>
		<p class="description" style="margin-top: 10px;">
			<?php _e( 'Hold down the Ctrl (windows) / Command (Mac) key to select multiple items.', 'fetch-tweets' ); ?>
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
			<input type="number" id="<?php echo $aIDs['avatar_size']; ?>" name="<?php echo $aNames['avatar_size']; ?>" min="0" value="<?php echo $aInstance['avatar_size']?>"/>
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
	
}