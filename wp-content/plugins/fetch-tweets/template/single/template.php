<?php
/*
 * Available variables passed from the caller script
 * - $aTweets : the fetched tweet arrays.
 * - $aArgs	: the passed arguments such as item count etc.
 * - $aOptions : the plugin options saved in the database.
 * */
 
/*
 * Prepare variables for options.
 */
// Retrieve the user avatar and the screen name.
$sUserAvatarURL = null;
$sUserScreenName = null;
$sUserName = null;
$sRetweetClassProperty = '';
$sUserLang = null;
$sUserDescription = null;
$fIsSSL = is_ssl();
foreach( $aTweets as $_aDetail ) {
	if ( ! isset( $_aDetail['user']['profile_image_url'] ) ) continue;
	$sUserAvatarURL = $fIsSSL ? $_aDetail['user']['profile_image_url_https'] : $_aDetail['user']['profile_image_url'];
	$sUserScreenName = $_aDetail['user']['screen_name'];
	$sUserName = $_aDetail['user']['name'];
	$sUserLang = $_aDetail['user']['lang'];
	$sDescription = $_aDetail['user']['description'];
	break;	// the first iteration item is only necessary 
}

// Set the default template option values.
$aDefaultTemplateValues = array(
	'fetch_tweets_template_single_avatar_size' => 48,
	'fetch_tweets_template_single_avatar_position' => 'left',
	'fetch_tweets_template_single_width' => array( 'size' => 100, 'unit' => '%' ),
	'fetch_tweets_template_single_height' => array( 'size' => 400, 'unit' => 'px' ),
	'fetch_tweets_template_single_background_color' => 'transparent',
	'fetch_tweets_template_single_intent_buttons' => 2,
	'fetch_tweets_template_single_intent_script' => 1,	
	'fetch_tweets_template_single_follow_button_elements' => array(
		'screen_name' => 0,
		'follower_count' => 0,	
	),
	'fetch_tweets_template_single_visibilities' => array(
		'avatar' => true,
		'user_name' => true,
		'follow_button' => true,
		'user_description' => true,
		'time' => true,	
		'intent_buttons' => true,
	),
	'fetch_tweets_template_single_margins' => array(
		0 => array( 'size' => '', 'unit' => 'px' ),
		1 => array( 'size' => '', 'unit' => 'px' ),
		2 => array( 'size' => '', 'unit' => 'px' ),
		3 => array( 'size' => '', 'unit' => 'px' ),
	),
	'fetch_tweets_template_single_paddings' => array(
		0 => array( 'size' => '', 'unit' => 'px' ),
		1 => array( 'size' => '', 'unit' => 'px' ),
		2 => array( 'size' => '', 'unit' => 'px' ),
		3 => array( 'size' => '', 'unit' => 'px' ),
	),	
);
// Retrieve the template option values.
if ( ! isset( $aOptions['fetch_tweets_template_single'] ) ) {	// for the fist time of calling the template.
	$aOptions['fetch_tweets_template_single'] = $aDefaultTemplateValues;
	update_option( FetchTweets_Commons::AdminOptionKey, $aOptions );
}
// Some new setting items are not be stored in the database, so merge the saved options with the defined default values.
$aTemplateOptions = FetchTweets_Utilities::uniteArrays( $aOptions['fetch_tweets_template_single'], $aDefaultTemplateValues );	// unites arrays recursively.

// Set the template option values.
$aArgs['avatar_size']				= isset( $aArgs['avatar_size'] ) ? $aArgs['avatar_size'] : $aTemplateOptions['fetch_tweets_template_single_avatar_size'];
$aArgs['avatar_position']			= isset( $aArgs['avatar_position'] ) ? $aArgs['avatar_position'] : $aTemplateOptions['fetch_tweets_template_single_avatar_position'];
$aArgs['width']						= isset( $aArgs['width'] ) ? $aArgs['width'] : $aTemplateOptions['fetch_tweets_template_single_width']['size'];
$aArgs['width_unit']				= isset( $aArgs['width_unit'] ) ? $aArgs['width_unit'] : $aTemplateOptions['fetch_tweets_template_single_width']['unit'];
$aArgs['height']					= isset( $aArgs['height'] ) ? $aArgs['height']: $aTemplateOptions['fetch_tweets_template_single_height']['size'];
$aArgs['height_unit']				= isset( $aArgs['height_unit'] ) ? $aArgs['height_unit'] : $aTemplateOptions['fetch_tweets_template_single_height']['unit'];
$aArgs['background_color']			= isset( $aArgs['background_color'] ) ? $aArgs['background_color'] : $aTemplateOptions['fetch_tweets_template_single_background_color'];
$aArgs['visibilities']				= isset( $aArgs['visibilities'] ) ? $aArgs['visibilities'] : $aTemplateOptions['fetch_tweets_template_single_visibilities'];
$aArgs['margin_top']				= isset( $aArgs['margin_top'] ) ? $aArgs['margin_top'] : $aTemplateOptions['fetch_tweets_template_single_margins'][0]['size'];
$aArgs['margin_top_unit']			= isset( $aArgs['margin_top_unit'] ) ? $aArgs['margin_top_unit'] : $aTemplateOptions['fetch_tweets_template_single_margins'][0]['unit'];
$aArgs['margin_right']				= isset( $aArgs['margin_right'] ) ? $aArgs['margin_right'] : $aTemplateOptions['fetch_tweets_template_single_margins'][1]['size'];
$aArgs['margin_right_unit']			= isset( $aArgs['margin_right_unit'] ) ? $aArgs['margin_right_unit'] : $aTemplateOptions['fetch_tweets_template_single_margins'][1]['unit'];
$aArgs['margin_bottom']				= isset( $aArgs['margin_bottom'] ) ? $aArgs['margin_bottom'] : $aTemplateOptions['fetch_tweets_template_single_margins'][2]['size'];
$aArgs['margin_bottom_unit']		= isset( $aArgs['margin_bottom_unit'] ) ? $aArgs['margin_bottom_unit'] : $aTemplateOptions['fetch_tweets_template_single_margins'][2]['unit'];
$aArgs['margin_left']				= isset( $aArgs['margin_left'] ) ? $aArgs['margin_left'] : $aTemplateOptions['fetch_tweets_template_single_margins'][3]['size'];
$aArgs['margin_left_unit']			= isset( $aArgs['margin_left_unit'] ) ? $aArgs['margin_left_unit'] : $aTemplateOptions['fetch_tweets_template_single_margins'][3]['unit'];
$aArgs['padding_top']				= isset( $aArgs['padding_top'] ) ? $aArgs['padding_top'] : $aTemplateOptions['fetch_tweets_template_single_paddings'][0]['size'];
$aArgs['padding_top_unit']			= isset( $aArgs['padding_top_unit'] ) ? $aArgs['padding_top_unit'] : $aTemplateOptions['fetch_tweets_template_single_paddings'][0]['unit'];
$aArgs['padding_right']				= isset( $aArgs['padding_right'] ) ? $aArgs['padding_right'] : $aTemplateOptions['fetch_tweets_template_single_paddings'][1]['size'];
$aArgs['padding_right_unit']		= isset( $aArgs['padding_right_unit'] ) ? $aArgs['padding_right_unit'] : $aTemplateOptions['fetch_tweets_template_single_paddings'][1]['unit'];
$aArgs['padding_bottom']			= isset( $aArgs['padding_bottom'] ) ? $aArgs['padding_bottom'] : $aTemplateOptions['fetch_tweets_template_single_paddings'][2]['size'];
$aArgs['padding_bottom_unit']		= isset( $aArgs['padding_bottom_unit'] ) ? $aArgs['padding_bottom_unit'] : $aTemplateOptions['fetch_tweets_template_single_paddings'][2]['unit'];
$aArgs['padding_left']				= isset( $aArgs['padding_left'] ) ? $aArgs['padding_left'] : $aTemplateOptions['fetch_tweets_template_single_paddings'][3]['size'];
$aArgs['padding_left_unit']			= isset( $aArgs['padding_left_unit'] ) ? $aArgs['padding_left_unit'] : $aTemplateOptions['fetch_tweets_template_single_paddings'][3]['unit'];
$aArgs['intent_buttons']			= isset( $aArgs['intent_buttons'] ) ? $aArgs['intent_buttons'] : ( ! $aArgs['visibilities']['intent_buttons'] ? 0 : $aTemplateOptions['fetch_tweets_template_single_intent_buttons'] );	// 0: do not show, 1: icons and text, 2: only icons, 3: only text.
$aArgs['intent_button_script']		= isset( $aArgs['intent_button_script'] ) ? $aArgs['intent_button_script'] : $aTemplateOptions['fetch_tweets_template_single_intent_script'];
$aArgs['follow_button_elements']	= isset( $aArgs['follow_button_elements'] ) ? $aArgs['follow_button_elements'] : $aTemplateOptions['fetch_tweets_template_single_follow_button_elements'];
$aArgs['follow_button_screen_name'] = isset( $aArgs['follow_button_screen_name'] ) ? $aArgs['follow_button_screen_name'] : ( $aArgs['follow_button_elements']['screen_name'] ? $aArgs['follow_button_elements']['screen_name'] : "false" );
$aArgs['follow_button_count'] 		= isset( $aArgs['follow_button_count'] ) ? $aArgs['follow_button_count'] : ( $aArgs['follow_button_elements']['follower_count'] ? $aArgs['follow_button_elements']['follower_count'] : "false" );

$sWidth = $aArgs['width'] ? "max-width: " . $aArgs['width'] . $aArgs['width_unit'] . "; " : '';
$sHeight =  $aArgs['height'] ? "max-height: " . $aArgs['height'] . $aArgs['height_unit'] . "; " : '';
$sMarginTop = empty( $aArgs['margin_top'] ) ? "" : $aArgs['margin_top'] . $aArgs['margin_top_unit'];
$sMarginRight = empty( $aArgs['margin_right'] ) ? "" : $aArgs['margin_right'] . $aArgs['margin_right_unit'];
$sMarginBottom = empty( $aArgs['margin_bottom'] ) ? "" : $aArgs['margin_bottom'] . $aArgs['margin_bottom_unit'];
$sMarginLeft = empty( $aArgs['margin_left'] ) ? "" : $aArgs['margin_left'] . $aArgs['margin_left_unit'];
$sPaddingTop = empty( $aArgs['padding_top'] ) ? "" : $aArgs['padding_top'] . $aArgs['padding_top_unit'];
$sPaddingRight = empty( $aArgs['padding_right'] ) ? "" : $aArgs['padding_right'] . $aArgs['padding_right_unit'];
$sPaddingBottom = empty( $aArgs['padding_bottom'] ) ? "" : $aArgs['padding_bottom'] . $aArgs['padding_bottom_unit'];
$sPaddingLeft = empty( $aArgs['padding_left'] ) ? "" : $aArgs['padding_left'] . $aArgs['padding_left_unit'];
$sMargins = ( $sMarginTop ? "margin-top: {$sMarginTop}; " : "" ) . ( $sMarginRight ? "margin-right: {$sMarginRight}; " : "" ) . ( $sMarginBottom ? "margin-bottom: {$sMarginBottom}; " : "" ) . ( $sMarginLeft ? "margin-left: {$sMarginLeft}; " : "" );
$sPaddings = ( $sPaddingTop ? "padding-top: {$sPaddingTop}; " : "" ) . ( $sPaddingRight ? "padding-right: {$sPaddingRight}; " : "" ) . ( $sPaddingBottom ? "padding-bottom: {$sPaddingBottom}; " : "" ) . ( $sPaddingLeft ? "padding-left: {$sPaddingLeft}; " : "" );
$sMarginForImage = $aArgs['visibilities']['avatar'] ? ( ( $aArgs['avatar_position'] == 'left' ? "margin-left: " : "margin-right: " ) . ( ( int ) $aArgs['avatar_size'] + 10 ) . "px" ) : "";
$sGMTOffset = ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
$sUserAvatarURL = getTwitterProfileImageURLBySize( $sUserAvatarURL, $aArgs['avatar_size'] );
/*
 * For debugs - uncomment the below line to see the contents of the array.
 */  
// echo "<pre>" . htmlspecialchars( print_r( $aTweets, true ) ) . "</pre>";	
// echo "<pre>" . htmlspecialchars( print_r( $aArgs, true ) ) . "</pre>";	
// return;

/*
 * Start rendering
 */ 

?>

<div class="fetch-tweets-single-container" style="<?php echo $sWidth; ?><?php echo $sHeight; ?>background-color: <?php echo $aArgs['background_color']; ?>; <?php echo $sMargins; ?> <?php echo $sPaddings; ?>">
	
	<div class='fetch-tweets-single-heading'>
		<?php if ( $aArgs['avatar_size'] > 0  && $aArgs['visibilities']['avatar'] ) : ?>
		<div class='fetch-tweets-single-profile-image' style="max-width:<?php echo $aArgs['avatar_size'];?>px; float:<?php echo $aArgs['avatar_position']; ?>; clear:<?php echo $aArgs['avatar_position']; ?>;">
			<a href='https://twitter.com/<?php echo $sUserScreenName; ?>' target='_blank'>
				<img src='<?php echo $sUserAvatarURL; ?>' style="max-width:<?php echo $aArgs['avatar_size'];?>px; border-radius: 5px;" />
			</a>		
		</div>
		<?php endif; ?>
		
		<?php if ( $aArgs['visibilities']['user_name'] || $aArgs['visibilities']['follow_button'] || $aArgs['visibilities']['user_description'] ) : ?>
		<div class='fetch-tweets-single-user-profile' style='<?php echo $sMarginForImage; ?>;'>
		<?php endif; ?>
		
			<?php if ( $aArgs['visibilities']['user_name'] ) : ?>
			<span class='fetch-tweets-single-user-name'>
				<strong>
					<a href='https://twitter.com/<?php echo $sUserScreenName; ?>' target='_blank'>
						<?php echo $sUserName; ?>
					</a>
				</strong>
			</span>	
			<?php endif; ?>
			
			<?php if ( $aArgs['visibilities']['follow_button'] ) : ?>
			<div class='fetch-tweets-single-follow-button'>
				<a href="https://twitter.com/<?php echo $sUserScreenName;?>" class="twitter-follow-button" target="_blank" data-lang="<?php echo $sUserLang; ?>" data-show-count="<?php echo $aArgs['follow_button_count']; ?>" data-show-screen-name="<?php echo $aArgs['follow_button_screen_name']; ?>"><?php echo __( 'Follow', 'fetch-tweets' ) . '@' . $sUserScreenName; ?></a>
				<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>				
			</div>		
			<?php endif; ?>
			
			<?php if ( $aArgs['visibilities']['user_description'] ) : ?>
			<p class='fetch-tweets-single-user-description'>
				<?php echo $sDescription; ?>
			</p>
			<?php endif; ?>
		
		<?php if ( $aArgs['visibilities']['user_name'] || $aArgs['visibilities']['follow_button'] || $aArgs['visibilities']['user_description'] ) : ?>
		</div>
		<?php endif; ?>
		
	</div>
	
	<?php foreach ( $aTweets as $_aDetail ) : ?>
	<?php 
	
		// If the necessary key is not set, skip.
		if ( ! isset( $_aDetail['user'] ) ) continue;
		
		// Check if it's a .retweet
		$_fIsRetweet = isset( $_aDetail['retweeted_status']['text'] );		
		if ( $_fIsRetweet && ! $aArgs['include_rts'] ) continue;
		$aTweet = $_fIsRetweet ? $_aDetail['retweeted_status'] : $_aDetail;
		$sRetweetClassProperty = $_fIsRetweet ? 'fetch-tweets-single-retweet' : '';
		
	?>
    <div class='fetch-tweets-single-item <?php echo $sRetweetClassProperty; ?>' >
		<div class='fetch-tweets-single-body'>
			<p class='fetch-tweets-single-text'>
				<?php echo trim( $aTweet['text'] ); ?>
				<span class='fetch-tweets-single-credit'>
					<?php if ( $_fIsRetweet ) : ?>
					<span class='fetch-tweets-single-retweet-credit'>
						<?php echo _e( 'Retweeted by', 'fetch-tweets' ) . ' '; ?>
						<a href='https://twitter.com/<?php echo $_aDetail['user']['screen_name']; ?>' target='_blank'>
							<?php echo $_aDetail['user']['name']; ?>
						</a>
					</span>
					<?php endif; ?>
					
					<?php if ( $aArgs['visibilities']['time'] ) : ?>
					<span class='fetch-tweets-single-tweet-created-at'>
						<a href='https://twitter.com/<?php echo $aTweet['user']['screen_name']; ?>/status/<?php echo $aTweet['id_str'] ;?>' target='_blank'>
							<?php echo human_time_diff( $aTweet['created_at'] , current_time('timestamp') - $sGMTOffset ) . ' ' . __( 'ago', 'fetch-tweets' ); ?>
						</a>			
					</span>
					<?php endif; ?>
					
				</span>
			</p>

			<?php if ( $aArgs['intent_buttons'] ) : ?>
				<?php if ( $aArgs['intent_button_script'] ) : ?>
				<script type="text/javascript" src="//platform.twitter.com/widgets.js"></script>
				<?php endif; ?>
				<ul class='fetch-tweets-single-intent-buttons'>
					<li class='fetch-tweets-single-intent-reply'>
						<a href='https://twitter.com/intent/tweet?in_reply_to=<?php echo $_aDetail['id_str']; ?>' rel='nofollow' target='_blank' title='<?php _e( 'Reply', 'fetch-tweets' ); ?>'>
							<?php if ( $aArgs['intent_buttons'] == 1 || $aArgs['intent_buttons'] == 2 ) : ?>
							<span class='fetch-tweets-single-intent-icon' style='background-image: url("<?php echo FetchTweets_Commons::getPluginURL( 'asset/image/reply_48x16.png' ); ?>");' ></span>
							<?php endif; ?>
							<?php if ( $aArgs['intent_buttons'] == 1 || $aArgs['intent_buttons'] == 3 ) : ?>
							<span class='fetch-tweets-single-intent-buttons-text'><?php _e( 'Reply', 'fetch-tweets' ); ?></span>
							<?php endif; ?>
						</a>
					</li>
					<li class='fetch-tweets-single-intent-retweet'>
						<a href='https://twitter.com/intent/retweet?tweet_id=<?php echo $_aDetail['id_str'];?>' rel='nofollow' target='_blank' title='<?php _e( 'Retweet', 'fetch-tweets' ); ?>'>
							<?php if ( $aArgs['intent_buttons'] == 1 || $aArgs['intent_buttons'] == 2 ) : ?>
							<span class='fetch-tweets-single-intent-icon' style='background-image: url("<?php echo FetchTweets_Commons::getPluginURL( 'asset/image/retweet_48x16.png' ); ?>");' ></span>
							<?php endif; ?>
							<?php if ( $aArgs['intent_buttons'] == 1 || $aArgs['intent_buttons'] == 3 ) : ?>
							<span class='fetch-tweets-single-intent-buttons-text'><?php _e( 'Retweet', 'fetch-tweets' ); ?></span>
							<?php endif; ?>
						</a>
					</li>
					<li class='fetch-tweets-single-intent-favorite'>
						<a href='https://twitter.com/intent/favorite?tweet_id=<?php echo $_aDetail['id_str'];?>' rel='nofollow' target='_blank' title='<?php _e( 'Favorite', 'fetch-tweets' ); ?>'>
							<?php if ( $aArgs['intent_buttons'] == 1 || $aArgs['intent_buttons'] == 2 ) : ?>
							<span class='fetch-tweets-single-intent-icon' style='background-image: url("<?php echo FetchTweets_Commons::getPluginURL( 'asset/image/favorite_48x16.png' ); ?>");' ></span>
							<?php endif; ?>
							<?php if ( $aArgs['intent_buttons'] == 1 || $aArgs['intent_buttons'] == 3 ) : ?>
							<span class='fetch-tweets-single-intent-buttons-text'><?php _e( 'Favorite', 'fetch-tweets' ); ?></span>
							<?php endif; ?>
						</a>
					</li>
				</ul>
			<?php endif; ?>			
			
		</div>
    </div>
	<?php endforeach; ?>	
</div>