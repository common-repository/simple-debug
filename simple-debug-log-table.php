<?php

if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


class Debug_Log_List_Table extends WP_List_Table{


	function __construct(){
		
		$parent_args = array(
            'singular'  => 'debug_log',    //singular name of the listed records
            'plural'    => 'debug_logs',   	//plural name of the listed records
            'ajax'      => true        //does this table support ajax?
    	);
		
		parent::__construct($parent_args);
		 	 
	}

	


	function column_filename($item) {
	  	$actions = array(
				'Download'    => sprintf('<a href="%s">Download</a>',$item['link']),
				'Delete'    => sprintf('<a href="'.admin_url().'options-general.php?page=%s&delete_backup_file=%s">Delete</a>',"simple-backup/plugin-admin.php",$item['filename']),
				
			);
			$link = sprintf('<strong><a href="%s">%s</a></strong>',$item['link'], $item['filename']);
	  	return sprintf('%1$s %2$s', $link, $this->row_actions($actions) );
	}


	 function get_columns(){
	
        global $status;
        $columns = array(
			'date'      	=> __('Date', 'simple-debug'),
			'type'      	=> __('Type', 'simple-debug'),
            'message'      	=> __('Message', 'simple-debug'),
	
        );
        return $columns;
    }


    function get_sortable_columns(){
	
		$sortable_columns = array(
			'date'  => array('date',true),
			'type'  => array('type',false)
			
		);
		
		return $sortable_columns;
  
 		
    }
	
	
	function extra_tablenav($which){
		if ( 'top' == $which ){
			$link = "<div class='alignleft actions' >";
						
			$link .=  "<form method='post' action='".admin_url()."tools.php?page=debug_log'  style='display:inline;'>";
			$link .=  "<input type='submit' value=' &#8635; &nbsp; Reload Page ' class='button-primary'>";
			$link .=  "</form>";
			
			$link .= " &nbsp; ";
			
			$link .=  " <form method='post' action='".admin_url()."tools.php?page=debug_log'  style='display:inline;'>";
			$link .=  "<input type='hidden' value='clear_debug_log_file' name='clear_debug_log_file'>";
			$link .=  "<input type='submit' value=' &#10008; &nbsp; Clear Log File ' class='button-secondary'>";
			$link .=  "</form> ";
			
			$link .= " &nbsp; ";
			
			$link .=  " <form method='post' action='".admin_url()."tools.php?page=debug_log'  style='display:inline;'>";
			$link .=  "<input type='hidden' value='test_debug_log_file' name='test_debug_log_file'>";
			$link .=  "<input type='submit' value=' &#10004; &nbsp; Test Log File ' class='button-secondary'>";
			$link .=  "</form>";
			
			$link .= "</div>";
			echo $link;
		}	
		if ( 'bottom' == $which ){
			$link = "<div class='alignleft actions'>";
			
			$link .=  "<form method='post' action='".admin_url()."tools.php?page=debug_log'  style='display:inline;'>";
			$link .=  "<input type='submit' value=' &#8635; &nbsp; Reload Page ' class='button-primary'>";
			$link .=  "</form> ";
			
			$link .= " &nbsp; ";
			
			$link .=  " <form method='post' action='".admin_url()."tools.php?page=debug_log'  style='display:inline;'>";
			$link .=  "<input type='hidden' value='clear_debug_log_file' name='clear_debug_log_file'>";
			$link .=  "<input type='submit' value=' &#10008; &nbsp; Clear Log File ' class='button-secondary'>";
			$link .=  "</form> ";

			$link .= " &nbsp; ";
			
			$link .=  " <form method='post' action='".admin_url()."tools.php?page=debug_log'  style='display:inline;'>";
			$link .=  "<input type='hidden' value='test_debug_log_file' name='test_debug_log_file'>";
			$link .=  "<input type='submit' value=' &#10004; &nbsp; Test Log File ' class='button-secondary'>";
			$link .=  "</form>";
			
			$link .= "</div>";
			echo $link;
		}
	}
	
    function column_default($item, $column_name){
        $item = apply_filters('simple-debug-output-data', $item);

        //unset existing filter and pagination
        $args = wp_parse_args( parse_url($_SERVER["REQUEST_URI"], PHP_URL_QUERY) );
        unset($args['filter']);
        unset($args['paged']);

		$this_page = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

        switch($column_name){
            
            default:
                return $item[$column_name];
        }
    }


	
	
	
	function prepare_items(){
	
		$screen = get_current_screen();
		
		/**
		 * setup pagination default number per page
		 */
		$per_page_option = $screen->id . '_per_page';
		$per_page = get_option($per_page_option, 50);
		$per_page = ($per_page != false) ? $per_page : 50;
		
		
		/**
		 * Define column headers. This includes a complete
		 * array of columns to be displayed (slugs & titles), a list of columns
		 * to keep hidden, and a list of columns that are sortable. Each of these
		 * can be defined in another method (as we've done here) before being
		 * used to build the value for our _column_headers property.
		 */
		$columns = $this->get_columns();
		$hidden_cols = get_user_option( 'manage' . $screen->id . 'columnshidden' );
		$hidden = ( $hidden_cols ) ? $hidden_cols : array();
		$sortable = $this->get_sortable_columns();
		
		
		/**
		 * Build an array to be used by the class for column
		 * headers. The $this->_column_headers property takes an array which contains
		 * 3 other arrays. One for all columns, one for hidden columns, and one
		 * for sortable columns.
		 */
		$this->_column_headers = array($columns, $hidden, $sortable);
		$columns = get_column_headers( $screen );
		
		
		/**
		 * Fetch the data for use in this table. 
		 */
		$this->items = Simple_Debug_Log_Manager::get_debug_log();
		$data = $this->items;
		
		
		
		usort($data, array($this, 'usort_reorder') );
		
		
		/**
		 * Figure out what page the user is currently looking at. 
		 */
		$current_page = $this->get_pagenum();
		
		
		/**
		 * REQUIRED for pagination. Let's check how many items are in our data array.
		 * In real-world use, this would be the total number of items in your database,
		 * without filtering. We'll need this later, so you should always include it
		 * in your own package classes.
		 */
		$total_items = count($data);
		
		
		/**
		 * manual pagination
		 */
		$data = array_slice($data,(($current_page-1)*$per_page),$per_page);
		
		
		
		/**
		 * Add our *sorted* data to the items property
		 */
		$this->items = $data;
		
		
		/**
		 * Register our pagination options & calculations.
		 */
		$this->set_pagination_args( array(
			'total_items' => $total_items,                  //calculate the total number of items
			'per_page'    => $per_page,                     //determine how many items to show on a page
			'total_pages' => ceil($total_items/$per_page)   //calculate the total number of pages
		) );
	
	}


	/**
	 * This checks for sorting input and sorts the data in our array accordingly.
	 */
	function usort_reorder($a,$b){
		$orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'date'; //If no sort, default to title
		$order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'desc'; //If no order, default to asc
		$result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
		return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
	}


}

?>