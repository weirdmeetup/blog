<?php
/**
 * 
 * @filter			fetch_tweets_filter_credentials - receives an array holding accounts credentials and the account ID.  
 * @action			fetch_tweets_action_updated_credentials - triggered when updating the main credentials.
 */
abstract class FetchTweets_Option_ {
	
	protected static $aStructure_Options = array(		
		'authentication_keys' => array(
			'consumer_key' => '',
			'consumer_secret' => '',
			'access_token' => '',
			'access_secret' => '',
			'screen_name'	=>	'',
			'user_id'	=>	'',		
			'is_connected' => null,	// do not set a default value here as it will be checked if the value is set or not later
			'connect_method' => 'manual',
		),
		'twitter_connect' => array(
			// do not set 'consumer_key' and the 'consumer_secret' key so that third-party scripts can determine the connection method by checking the existence of the keys.
			'access_token' => '',
			'access_secret' => '',
			'screen_name'	=>	'',
			'user_id'	=>	'',
			'is_connected' => null,	// do not set a default value here as it will be checked if the value is set or not later
			'connect_method' => 'oauth',
		),
		'default_values' => array(),
		'capabilities' => array(),
		'cache_settings' => array(
			'cache_for_errors' => false,
			'caching_mode'	=>	'normal',	// [2.1+]
		),
		'search'	=>	array(
			'is_searchable'	=> false,
		),
		'arrTemplates' => array(),	// stores template info arrays.
		'arrDefaultTemplate' => array(),	// stores the default template info.
	);
	
	public $aStructure_DefaultParams = array(
		'tweet_type'		=> null,	// this will be set in the add/edit rule page
		
		'id'				=> null,
		'ids'				=> null,	// deprecated as of 1.0.0.4 - but extension plugins may use it
		'tag'				=> null,
		'tags'				=> null,	// deprecated as of 1.0.0.4 - but extension plugins may use it
		'count'				=> 20,
		// 'avatar_size'		=> 48,
		'operator'			=> 'AND',
		'tag_field_type'	=> 'slug',				// used internally. slug or id.
		'sort'				=> 'descending',		//  ascending, descending, or random 
		// 'template'			=> null,	// the template slug
		
		// for custom function calls
		'q'					=> null,	
		'screen_name'		=> null,	// 
		'include_rts'		=> 0,		// 
		'exclude_replies'	=> 0,		// 
		'cache'				=> 1200,	// Cache lifespan in seconds.
		'lang'				=> null,	// 
		'result_type'		=> 'mixed',	// 
		'until'				=> '',		// since 1.3.3
		'geocode'			=> '',		// since 1.3.3 - this is for shortcode parametrs while geocentric_coordinate and geocentric_radius are for the meta box options.
		'geocentric_coordinate'	=> array(	// since 1.3.3
			'latitude' => '',
			'longitude' => '',
		),
		'geocentric_radius' => array(	// since 1.3.3
			'size' => '',
			'unit' => 'mi',
		),
		
		// [1.2.0+]
		'list_id'			=> null,	
		'twitter_media'		=> true,
		'external_media'	=> true,
		
		// [2+]
		'account_id'		=> null,	// do not set the default ID of 0 here. The fetching method will check if the value is set and if so, it considers as the home timeline tweet type.
		
	);
	public $aStructure_DefaultTemplateOptions = array(
		// leave them null and let each template define default values.
		'template'			=> null,	// the template slug
		'avatar_size'		=> null,	// 48, 
		'width'				=> null,	// 100,	
		'width_unit'		=> null,	// '%',	
		'height'			=> null,	// 800,
		'height_unit'		=> null,	// 'px',
	);		 
	public $aOptions = array();	// stores the option values.
		 
	protected $sOptionKey = '';	// stores the option key for this plugin. 
		 
	public function __construct( $sOptionKey ) {
		
		$this->sOptionKey = $sOptionKey;
		$this->aOptions = $this->setOption( $sOptionKey );
		
	}	
	
	/*
	 * 
	 * Back end methods
	 * */
	private function setOption( $sOptionKey ) {
		
		// Flags
		$_fOptionsModified = false;
		
		// Set up the options array.
		$vOption = get_option( $sOptionKey );
		$vOption = ( false === $vOption ) ? array() : $vOption;		// Avoid casting array because it causes a zero key when the subject is null.
		$aOptions = FetchTweets_Utilities::uniteArrays( $vOption, self::$aStructure_Options ); 	// Now $vOption is an array so merge with the default option to avoid undefined index warnings.
		
		// If the v1 option array structure is present, format the options for backward compatibility
		if ( isset( $aOptions['fetch_tweets_settings'] ) || isset( $aOptions['fetch_tweets_templates'] ) ) {
			$aOptions = $this->_convertV1OptionsToV2( $aOptions );
			$_fOptionsModified = true;
			
		}
		
		// If the template option array is empty, retrieve the active template arrays.
		if ( empty( $aOptions['arrTemplates'] ) ) {
			
			$oTemplate = new FetchTweets_Templates;
			$arrDefaultTemplate = $oTemplate->findDefaultTemplateDetails();
			$aOptions['arrTemplates'][ $arrDefaultTemplate['strSlug'] ] = $arrDefaultTemplate;
			$aOptions['arrDefaultTemplate'] = $arrDefaultTemplate;
			$_fOptionsModified = true;
		}
		
		if ( $_fOptionsModified ) {
			$this->saveOptions( $aOptions );
		}
		
		return $aOptions;
				
	}
		protected function _convertV1OptionsToV2( $aOptions ) {

			// Drop the page slug dimension.
			$_aOptions = FetchTweets_Utilities::uniteArrays(
				isset( $aOptions['fetch_tweets_settings'] ) ? $aOptions['fetch_tweets_settings'] : array(),
				isset( $aOptions['fetch_tweets_templates'] ) ? $aOptions['fetch_tweets_templates'] : array()
			);
			unset( $aOptions['fetch_tweets_settings'], $aOptions['fetch_tweets_templates'] );

			// For template options
			if ( isset( $_aOptions['fetch_tweets_template_plain']['fetch_tweets_template_plain_paddings']['top'] ) ) {
				$_aOptions['fetch_tweets_template_plain']['fetch_tweets_template_plain_paddings'][ 0 ] = $_aOptions['fetch_tweets_template_plain']['fetch_tweets_template_plain_paddings']['top'];
				unset( $_aOptions['fetch_tweets_template_plain']['fetch_tweets_template_plain_paddings']['top'] );
			}
			if ( isset( $_aOptions['fetch_tweets_template_plain']['fetch_tweets_template_plain_paddings']['right'] ) ) {
				$_aOptions['fetch_tweets_template_plain']['fetch_tweets_template_plain_paddings'][ 1 ] = $_aOptions['fetch_tweets_template_plain']['fetch_tweets_template_plain_paddings']['right'];
				unset( $_aOptions['fetch_tweets_template_plain']['fetch_tweets_template_plain_paddings']['right'] );
			}
			if ( isset( $_aOptions['fetch_tweets_template_plain']['fetch_tweets_template_plain_paddings']['bottom'] ) ) {
				$_aOptions['fetch_tweets_template_plain']['fetch_tweets_template_plain_paddings'][ 2 ] = $_aOptions['fetch_tweets_template_plain']['fetch_tweets_template_plain_paddings']['bottom'];
				unset( $_aOptions['fetch_tweets_template_plain']['fetch_tweets_template_plain_paddings']['bottom'] );
			}
			if ( isset( $_aOptions['fetch_tweets_template_plain']['fetch_tweets_template_plain_paddings']['left'] ) ) {
				$_aOptions['fetch_tweets_template_plain']['fetch_tweets_template_plain_paddings'][ 3 ] = $_aOptions['fetch_tweets_template_plain']['fetch_tweets_template_plain_paddings']['left'];
				unset( $_aOptions['fetch_tweets_template_plain']['fetch_tweets_template_plain_paddings']['left'] );
			}
			if ( isset( $_aOptions['fetch_tweets_template_single']['fetch_tweets_template_single_paddings']['top'] ) ) {
				$_aOptions['fetch_tweets_template_single']['fetch_tweets_template_single_paddings'][ 0 ] = $_aOptions['fetch_tweets_template_single']['fetch_tweets_template_single_paddings']['top'];
				unset( $_aOptions['fetch_tweets_template_single']['fetch_tweets_template_single_paddings']['top'] );
			}
			if ( isset( $_aOptions['fetch_tweets_template_single']['fetch_tweets_template_single_paddings']['right'] ) ) {
				$_aOptions['fetch_tweets_template_single']['fetch_tweets_template_single_paddings'][ 1 ] = $_aOptions['fetch_tweets_template_single']['fetch_tweets_template_single_paddings']['right'];
				unset( $_aOptions['fetch_tweets_template_single']['fetch_tweets_template_single_paddings']['right'] );
			}
			if ( isset( $_aOptions['fetch_tweets_template_single']['fetch_tweets_template_single_paddings']['bottom'] ) ) {
				$_aOptions['fetch_tweets_template_single']['fetch_tweets_template_single_paddings'][ 2 ] = $_aOptions['fetch_tweets_template_single']['fetch_tweets_template_single_paddings']['bottom'];
				unset( $_aOptions['fetch_tweets_template_single']['fetch_tweets_template_single_paddings']['bottom'] );
			}
			if ( isset( $_aOptions['fetch_tweets_template_single']['fetch_tweets_template_single_paddings']['left'] ) ) {
				$_aOptions['fetch_tweets_template_single']['fetch_tweets_template_single_paddings'][ 3 ] = $_aOptions['fetch_tweets_template_single']['fetch_tweets_template_single_paddings']['left'];
				unset( $_aOptions['fetch_tweets_template_single']['fetch_tweets_template_single_paddings']['left'] );
			}		

			// Credentials
			if ( isset( $_aOptions['authentication_keys'] ) ) {				
				$_aOptions['authentication_keys']['is_connected'] = ( $_aOptions['authentication_keys']['consumer_key'] && $_aOptions['authentication_keys']['consumer_secret'] && $_aOptions['authentication_keys']['access_token'] && $_aOptions['authentication_keys']['access_secret'] );
				$_aOptions['authentication_keys']['connect_method'] = 'manual';
			}
			if ( isset( $_aOptions['twitter_connect'] ) ) {
				$_aOptions['twitter_connect']['is_connected'] = ( $_aOptions['twitter_connect']['access_token'] && $_aOptions['twitter_connect']['access_secret'] );
				$_aOptions['twitter_connect']['connect_method'] = 'oauth';
			}
			
			return $_aOptions + $aOptions;
			
		}
	/*
	 * Front end methods
	 * */
	 
	 
	public function getAccessTokenAuto() {
		return $this->aOptions['twitter_connect']['access_token'];
	}	
	public function getAccessTokenSecretAuto() {
		return $this->aOptions['twitter_connect']['access_secret'];
	}		 
		
	/**
	 * Saves Twitter Account credentials.
	 * 
	 * @since			2
	 */
	public function saveCredentials( array $aCredentials ) {

		$this->aOptions['twitter_connect'] = $aCredentials;
		do_action( 'fetch_tweets_action_updated_credentials', $aCredentials );		
		$this->saveOptions();	
		
	}
	
	/**
	 * Returns the credentials array by the given account ID.
	 * 
	 * The account ID does not refer to the Twitter user ID. It is just a post ID stored in the WordPress database.
	 * 
	 * @since			2
	 */
	public function getCredentialsByID( $iAccountID ) {

		if ( $iAccountID <= 0 ) {
			return $this->getCredentials();	// will returns the main one.
		}		
		return apply_filters( 
			'fetch_tweets_filter_credentials', 
			array(
				'consumer_key' => '',
				'consumer_secret' => '',
				'access_token' => '',
				'access_secret' => '',
				'screen_name' => '',
			), 
			$iAccountID 
		);	
		
	}
	
	/**
	 * Returns the credentials array.
	 * 
	 * @since			2
	 */
	public function getCredentials() {
		
		if ( $this->isAuthKeysManuallySet() ) {
			return $this->aOptions['authentication_keys'];
		}
		
		$_aCredentials = $this->aOptions['twitter_connect'] + self::$aStructure_Options['twitter_connect'];
		$_aCredentials['consumer_key'] = FetchTweets_Commons::ConsumerKey;
		$_aCredentials['consumer_secret'] = FetchTweets_Commons::ConsumerSecret;

		// Check mandatory keys have a value
		if ( $_aCredentials['access_token'] && $_aCredentials['access_secret'] && $_aCredentials['screen_name'] ) {
			return $_aCredentials;
		}
		
		// If the user has not connected to twitter, the access token and secret key is not set. In that case, return in the incomplete array.
		if ( ! ( $_aCredentials['access_token'] && $_aCredentials['access_secret'] ) ) {
			return $_aCredentials;
		}
		
		// Otherwise, fetch the status to fill the user id and the screen name elements.
		$_oConnect = new FetchTweets_TwitterAPI_Verification( 
			FetchTweets_Commons::ConsumerKey, 
			FetchTweets_Commons::ConsumerSecret, 
			$this->aOptions['twitter_connect']['access_token'], 
			$this->aOptions['twitter_connect']['access_secret']
		);
		$_aStatus = $_oConnect->getStatus();
		$_aCredentials['screen_name'] = $_aStatus['screen_name'];
		$_aCredentials['user_id'] = $_aStatus['id'];
		$_aCredentials['is_connected'] = true;
		$_aCredentials['connect_method'] = 'oauth';
		$this->saveCredentials( $_aCredentials );	// update the options
		return $_aCredentials;
		
	}
	
	/**
	 * Checks if the plugin is connected to Twitter.
	 * 
	 * @since			2
	 */
	public function isConnected() {
		
		// The keys are manually set
		if ( $this->isAuthKeysManuallySet() ) {
			if ( isset( $this->aOptions['authentication_keys']['is_connected'] ) ) {
				return $this->aOptions['authentication_keys']['is_connected'];
			}
			$_oConnect = new FetchTweets_TwitterAPI_Verification( 
				$this->aOptions['authentication_keys']['consumer_key'], 
				$this->aOptions['authentication_keys']['consumer_secret'],
				$this->aOptions['authentication_keys']['access_token'], 
				$this->aOptions['authentication_keys']['access_secret']
			);
			$_aStatus = $_oConnect->getStatus();				
			return isset( $_aStatus['screen_name'] );
		}
		
		// The keys are automatically retrieved
		if ( isset( $this->aOptions['twitter_connect']['is_connected'] ) ) {
			return $this->aOptions['twitter_connect']['is_connected'];
		}
		$_oConnect = new FetchTweets_TwitterAPI_Verification( 
			FetchTweets_Commons::ConsumerKey,
			FetchTweets_Commons::ConsumerSecret,
			$this->aOptions['twitter_connect']['access_token'], 
			$this->aOptions['twitter_connect']['access_secret']
		);
		$_aStatus = $_oConnect->getStatus();				
		return isset( $_aStatus['screen_name'] );		
		
	}
	
	/**
	 * Returns whether the plugin has set the API authentication keys automatically.
	 * 
	 * since			1.3.0
	 */
	public function isAuthKeysAutomaticallySet() {
		return ( $this->getAccessTokenAuto() && $this->getAccessTokenSecretAuto() )
			? true
			: false;
	}
	/**
	 * Returns whether the user has set the API authentication keys manually.
	 * 
	 * As of v1.3.0, automatic authentication is supported. If the user already sets the keys by themselves already, no need to re-authorize. 
	 * Also if the consumer key and consumer secret are provided by miunosoft, if they become invalid for some reasons, the user can set them by themselves.
	 * 
	 * since			1.3.0
	 * return			boolean
	 */
	public function isAuthKeysManuallySet() {
		return ( $this->getConsumerKey() && $this->getConsumerSecret() && $this->getAccessToken() && $this->getAccessTokenSecret() )
			? true 
			: false;
	}
	
	public function getConsumerKey() {
		return $this->aOptions['authentication_keys']['consumer_key'];
	}
	public function getConsumerSecret() {
		return $this->aOptions['authentication_keys']['consumer_secret'];
	}
	public function getAccessToken() {
		return $this->aOptions['authentication_keys']['access_token'];
	}	
	public function getAccessTokenSecret() {
		return $this->aOptions['authentication_keys']['access_secret'];
	}	
	
	public function saveOptions( $aOptions=null ) {
		
		update_option( $this->sOptionKey, $aOptions ? $aOptions : $this->aOptions );

	}
	
}