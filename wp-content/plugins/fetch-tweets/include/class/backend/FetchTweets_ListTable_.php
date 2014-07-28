<?php
/**
	Handles the list table of Fetch Tweets templates. 
	
 * @package     Fetch Tweets
 * @copyright   Copyright (c) 2013, Michael Uno
 * @authorurl	http://michaeluno.jp
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since		1.0.0
 * @filters
 *  - fetch_tweets_filter_template_listing_table_action_links
 
*/

if ( ! class_exists( 'WP_List_Table' ) ) require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );


class FetchTweets_ListTable_ extends WP_List_Table {
    

    public function __construct( $arrData, $strDefaultTemplateSlug ){
              
		$this->arrData = $arrData;
		$this->strDefaultTemplateSlug = $strDefaultTemplateSlug;
		
        //Set parent defaults
		$this->arrArgs = array(
			'singular'  => 'template',		// singular name of the listed items
			'plural'    => 'templates',		// plural name of the listed items
			'ajax'      => false,			// does this table support ajax?
			'screen' 	=> null,			// not sure what this is for... 
		);
		if ( ! headers_sent() )
			add_action( 'admin_notices', array( $this, 'delayConstructor' ) );
		else 
			parent::__construct( $this->arrArgs );
        
    }
	public function delayConstructor() {
		
		parent::__construct( $this->arrArgs );
		
	}    

    public function column_default( $arrItem, $strColumnName ) {	// 'column_' + 'default'
	
        switch( $strColumnName ){

            case 'description':
							
				//Build row actions
				$arrActions = array(
					'version'	=> sprintf( __( 'Version', 'fetch-tweets' ) . '&nbsp;' . $arrItem['strVersion'] ),
					'author'	=> sprintf( '<a href="%s">' . $arrItem['strAuthor'] . '</a>', $arrItem['strAuthorURI'] ),
					// 'css'		=> sprintf( '<a href="%s">' . __( 'CSS', 'fetch-tweets' ) . '</a>', site_url() . "?fetch_tweets_style={$arrItem['strSlug']}" ),	// deprecated as of v1.3.3.2
					'css'		=> sprintf( '<a href="%s">' . __( 'CSS', 'fetch-tweets' ) . '</a>', FetchTweets_WPUtilities::getSRCFromPath( $arrItem['strCSSPath'] ) ),
				);
				
				//Return the title contents
				return sprintf('%1$s <div class="active second">%2$s</div>',
					/*$1%s*/ $arrItem['strDescription'],
					/*$2%s*/ $this->row_actions( $arrActions )
				);
            case 'thumbnail':
				if ( ! file_exists( $arrItem['strThumbnailPath'] ) ) return;
				// $strImageURL = site_url() . "?fetch_tweets_image=" . base64_encode( $arrItem['strThumbnailPath'] );	// deprecated as of v1.3.3.2
				$strImageURL = FetchTweets_WPUtilities::getSRCFromPath( $arrItem['strThumbnailPath'] );
				return "<a class='template-thumbnail' href='#thumb'>"
					. "<img src='{$strImageURL}' style='max-width:80px; max-height:80px;' />"
					. "<span>"
					. "<div>"
					. "<img src='{$strImageURL}' /><br />"
					. $arrItem['strName']
					. "</div>"
					. "</span>"
					. "</a>";                
            default:
                return print_r( $arrItem, true ); //Show the whole array for troubleshooting purposes
        }
		
    }
    
        
    public function column_name( $arrItem ){	// column_{$column_title}
        
        //Build row actions
		$arrActions = array();
		if ( $arrItem['fIsActive']  ) {
						
			$arrActions[ 'deactivate' ] = $arrItem['fIsDefault'] 
				? '<span class="disabled">' . __( 'Deactivate', 'fetch-tweets' ) . '</span>'
				: sprintf( '<a href="?post_type=%s&page=%s&action=%s&template=%s">' . __( 'Deactivate', 'fetch-tweets' ) . '</a>', FetchTweets_Commons::PostTypeSlug, $_REQUEST['page'], 'deactivate', $arrItem['strSlug'] );				
			$arrActions[ 'set_default' ] = $arrItem['fIsDefault'] 
				? '<span class="disabled">' . __( 'Set Default', 'fetch-tweets' ) . '</span>'
				: sprintf( '<a href="?post_type=%s&page=%s&action=%s&template=%s">' . __( 'Set Default', 'fetch-tweets' ) . '</a>', FetchTweets_Commons::PostTypeSlug, $_REQUEST['page'], 'set_default', $arrItem['strSlug'] );	
				
		} else  
			$arrActions[ 'activate' ] = sprintf( '<a href="?post_type=%s&page=%s&action=%s&template=%s">' . __( 'Activate', 'fetch-tweets' ) . '</a>', FetchTweets_Commons::PostTypeSlug, $_REQUEST['page'], 'activate', $arrItem['strSlug'] );
		$arrActions = apply_filters( 'fetch_tweets_filter_template_listing_table_action_links', $arrActions, $arrItem['strSlug'] );	

        //Return the title contents
        return sprintf('%1$s %2$s %3$s',	// <span style="color:silver">(id:%2$s)</span>
            /*$1%s*/ $arrItem['fIsActive'] ? "<strong>{$arrItem['strName']}</strong>" : $arrItem['strName'],
            /*$2%s*/ $arrItem['fIsDefault'] ? "<strong>(" . __( 'Default', 'fetch-tweets' ) . ")</strong>" : '',
            /*$3%s*/ $this->row_actions( $arrActions )
        );
		
    }
    
    public function column_cb( $arrItem ){	// column_ + cb
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  
            /*$2%s*/ $arrItem['strSlug']                //The value of the checkbox should be the record's id
        );
    }
    
    
    public function get_columns() {
        
		return array(
            'cb'        	=> '<input type="checkbox" />', //Render a checkbox instead of text
            'name'			=> __( 'Template Name', 'fetch-tweets' ),
            'thumbnail'		=> __( 'Thumbnail', 'fetch-tweets' ),
            'description'	=> __( 'Description', 'fetch-tweets' ),
        );
		
    }
    
    public function get_sortable_columns() {
		
        return array(
            'name'				=> array( 'name', false ),     //true means it's already sorted
            // 'thumbnail'		=> array( 'thumbnail', false ),
            'description'		=> array( 'description', false ),
        );
        
    }
    
    public function get_bulk_actions() {
       
		return array(
            // 'delete'    => 'Delete',
			'activate'		=> __( 'Activate', 'fetch-tweets' ),
			'deactivate'	=> __( 'Deactivate', 'fetch-tweets' ),
        );
        
    }
    
    public function process_bulk_action() {
		
// echo '<h3>Template Options before Updating</h3>';
// echo '<pre>'. print_r( $GLOBALS['oFetchTweets_Option']->aOptions['arrTemplates'], true ) . '</pre>';	

// echo '<h3>Set Data</h3>';
// echo '<pre>'. print_r( $this->arrData, true ) . '</pre>';	

		if ( ! isset( $_REQUEST['template'] ) ) return;
		
        switch( strtolower( $this->current_action() ) ){

            case 'activate':
				foreach( ( array ) $_REQUEST['template'] as $strDirSlug ) {
					$this->arrData[ $strDirSlug ]['fIsActive'] = true;
					$GLOBALS['oFetchTweets_Option']->aOptions['arrTemplates'][ $strDirSlug ] = $this->arrData[ $strDirSlug ];
				}
				$GLOBALS['oFetchTweets_Option']->saveOptions();
				wp_redirect( admin_url( 'edit.php?post_type=fetch_tweets&page=fetch_tweets_templates' ) );
				break;
            case 'deactivate':
				foreach( ( array ) $_REQUEST['template'] as $strDirSlug ) {
					$this->arrData[ $strDirSlug ]['fIsActive'] = false;
					unset( $GLOBALS['oFetchTweets_Option']->aOptions['arrTemplates'][ $strDirSlug ] );	// the option array only stores active templates.
				}
				$GLOBALS['oFetchTweets_Option']->saveOptions();
				wp_redirect( admin_url( 'edit.php?post_type=fetch_tweets&page=fetch_tweets_templates' ) );
				break;			
            case 'set_default':

				if ( ! is_string( $_REQUEST['template'] ) ) break;
			
				// Set the other templates not to be default.
				foreach( $this->arrData as &$arrTemplate ) // the passed template data array
					$arrTemplate['fIsDefault'] = false;
				unset( $arrTemplate ); // release the reference in foreach(), to be safe.
				foreach( $GLOBALS['oFetchTweets_Option']->aOptions['arrTemplates'] as &$arrTemplate )	// the saved template option array
					$arrTemplate['fIsDefault'] = false;
				unset( $arrTemplate ); // release the reference in foreach(), to be safe.
				
				// Enable the selected default template.
				$this->arrData[ $_REQUEST['template'] ]['fIsDefault'] = true;				
				$GLOBALS['oFetchTweets_Option']->aOptions['arrTemplates'][ $_REQUEST['template'] ] = $this->arrData[ $_REQUEST['template'] ];
				$GLOBALS['oFetchTweets_Option']->aOptions['arrDefaultTemplate'] = $this->arrData[ $_REQUEST['template'] ];
				
				break;	
            default:
				return;	// do nothing.
				
        }   

		// Save the options.
		// unset( $GLOBALS['oFetchTweets_Option']->aOptions['arrTemplates'] );	// just in case.
		$GLOBALS['oFetchTweets_Option']->saveOptions();

// echo '<h3>List Data Array</h3>';
// echo '<pre>'. print_r( $this->arrData, true ) . '</pre>';	
// echo '<h3>Template Options</h3>';
// echo '<pre>'. print_r( $GLOBALS['oFetchTweets_Option']->aOptions['arrTemplates'], true ) . '</pre>';	
	
		        
    }

    function prepare_items() {
		
// echo '<h3>List Data Array</h3>';
// echo '<pre>'. print_r( $this->arrData, true ) . '</pre>';	
// echo '<h3>Template Options</h3>';
// echo '<pre>'. print_r( $GLOBALS['oFetchTweets_Option']->aOptions['arrTemplates'], true ) . '</pre>';	
	
        /**
         * Set how many records per page to show
         */
        $intItemsPerPage = 20;
        
    
        /**
         * Define our column headers. 
         */
        $this->_column_headers = array( 
			$this->get_columns(), 	// $arrColumns
			array(),	// $arrHidden
			$this->get_sortable_columns()	// $arrSortable
		);
        
     
        /**
         * Process bulk actions.
         */
        // $this->process_bulk_action(); // in our case, it is dealt before the header is sent. ( with the Admin page class )
              
        /**
         * Variables
         */
        $arrData = $this->arrData;
                
        
        /**
         * Sort the array.
         */
        usort( $arrData, array( $this, 'usort_reorder' ) );
           
                
        /**
         * For pagination.
         */
        $intCurrentPageNumber = $this->get_pagenum();
        $intTotalItems = count( $arrData );
        $this->set_pagination_args( 
			array(
				'total_items' => $intTotalItems,                  	// calculate the total number of items
				'per_page'    => $intItemsPerPage,                     // determine how many items to show on a page
				'total_pages' => ceil( $intTotalItems / $intItemsPerPage )   // calculate the total number of pages
			)
		);
		$arrData = array_slice( $arrData, ( ( $intCurrentPageNumber -1 ) * $intItemsPerPage ), $intItemsPerPage );
		
		/*
		 * Set data
		 * */
        $this->items = $arrData;
        
    }
        public function usort_reorder( $a, $b ) {
			
            $strOrderBy = ( !empty( $_REQUEST['orderby'] ) ) ? $_REQUEST['orderby'] : 'intIndex'; //If no sort, default to title
			if ( $strOrderBy == 'description' )
				$strOrderBy = 'description';
			else if ( $strOrderBy == 'name' )
				$strOrderBy = 'strName';
						
            $strOrder = ( !empty( $_REQUEST['order'] ) ) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
            $result = strcmp( $a[ $strOrderBy ], $b[ $strOrderBy ] ); //Determine sort order
            return ( $strOrder === 'asc' ) ? $result : -$result; //Send final sort direction to usort
			
        }
    
}
