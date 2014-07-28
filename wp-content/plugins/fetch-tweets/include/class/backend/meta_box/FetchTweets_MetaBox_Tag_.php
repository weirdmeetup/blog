<?php
abstract class FetchTweets_MetaBox_Tag_ {
	
	public function __construct() {
		
		// Remove the default tag meta box and add a custom meta box.
		// add_action( 'admin_menu', array( $this, 'removeDefaultMetaBoxes' ) );
		// add_action( 'add_meta_boxes', array( $this, 'addCustomMetaBoxes' ) );		
		
	}
	
	public function removeDefaultMetaBoxes() {
		
		// Remove 'Keywords' (like tags) metabox
		$strTaxonomySlug = FetchTweets_Commons::TagSlug;
		remove_meta_box( "tagsdiv-{$strTaxonomySlug}", FetchTweets_Commons::PostTypeSlug, 'side' );	

	}	
	
	public function addCustomMetaBoxes() {
		
		// The wider tag meta box.
		add_meta_box( 
			'tagsdiv-' . FetchTweets_Commons::TagSlug . '-2', 		// id
			__( 'Tags', 'fetch-tweets' ), 	// title
			array( $this, 'drawTagBox' ), 	// callback
			FetchTweets_Commons::PostTypeSlug,		// post type
			'advanced', 	// context ('normal', 'advanced', or 'side'). 
			'low',	// priority ('high', 'core', 'default' or 'low') 
			null // argument
		);
		
	}	
	
	public function drawTagBox() {
		?>
		<div class="keywords inside">
		<?php
			post_tags_meta_box( 
				get_post( $GLOBALS['post_ID'] ), 
				array(
					'title' => __( 'Tags', 'fetch-tweets' ),
					'args' => array(
						'taxonomy' => FetchTweets_Commons::TagSlug,
					)
				)
			);
		?>
		</div>
		<?php
	}
}