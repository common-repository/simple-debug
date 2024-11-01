<?php


class Simple_Debug_Log_Manager{

	private $debug_log_table;

	function simple_debug_log_admin_menu(){
        global $simple_debug_log_manager_page;
		$simple_debug_log_manager_page = add_submenu_page( 'tools.php', __('Simple Debug Log Manager', 'simple-debug'), __('Debug Log', 'simple-debug'), 'manage_options', 'debug_log', array(&$this, 'debug_log_manager') );
		
    }
	
	
	

	


	function screen_options(){

        //execute only on login_log page, othewise return null
        $page = ( isset($_GET['page']) ) ? esc_attr($_GET['page']) : false;
        if( 'debug_log' != $page )
            return;

        $current_screen = get_current_screen();

        //define options
        $per_page_field = 'per_page';
        $per_page_option = $current_screen->id . '_' . $per_page_field;

        //Save options that were applied
        if( isset($_REQUEST['wp_screen_options']) && isset($_REQUEST['wp_screen_options']['value']) ){
            update_option( $per_page_option, esc_html($_REQUEST['wp_screen_options']['value']) );
        }

        //prepare options for display

        //if per page option is not set, use default
        $per_page_val = get_option($per_page_option, 50);
        $args = array('label' => __('Messages', 'simple-debug'), 'default' => $per_page_val );

        //display options
        add_screen_option($per_page_field, $args);
        $_per_page = get_option('backup_files_per_page');

        //create custom list table class to display  data
        $this->debug_log_table = new Debug_log_List_Table;
		
    }


	function debug_log_action_processor(){

		if( isset($_POST['test_debug_log_file']) && ('test_debug_log_file' == $_POST['test_debug_log_file']) ){
			
			$test = "TEST: Simple Debug: Debug Log Test!";
			error_log($test);
			echo "<div class='updated'><p>$test</p></div>";
		
		}
		
		if( isset($_POST['clear_debug_log_file']) && ('clear_debug_log_file' == $_POST['clear_debug_log_file']) ){
		
			$log_file = WP_CONTENT_DIR . '/debug.log';
			file_put_contents($log_file, '');
			
			$test = "Debug Log: Simple Debug: Debug Log Cleared!";
			error_log($test);
			echo "<div class='updated'><p>$test</p></div>";
		
		}
	}


	function debug_log_manager(){
	
		echo '<style type="text/css">';
		echo '.wp-list-table .column-date { width: 15%; }';
		echo '.wp-list-table .column-type { width: 10%; }';
		echo '.wp-list-table .column-message { width: 75%; }';
		echo '</style>';
		
		
		$tz = get_option('timezone_string') ? get_option('timezone_string') : "UTC+".get_option('gmt_offset');

		try {
			$date = new DateTime("@".time());
			$date->setTimezone(new DateTimeZone($tz)); 
		} catch (Exception $e) {
			echo '<div class="updated">';
			echo "<p>ERROR: <br />";
			echo "Your Timezone is currently set to: " . $tz. "<br />";
			echo "Please Choose A Timezone like 'Chicago' on the <a href='".admin_url()."options-general.php'>Settings Page</a></p>";
			echo "</div>";
		}
		
		
		$log_file = WP_CONTENT_DIR . '/debug.log';
		
		if(filesize($log_file) > (1024*1024*8)){
			echo '<div class="updated">';
			echo "<p>The debug.log file is currently: ".size_format(filesize($log_file))."<br />";
			echo "You may want to <strong>Clear the Log File</strong> before it grows too large!</p>";
			echo "</div>";
		}
		
		
		
		
		echo Simple_Debug_Plugin::display_social_media();
		
		
		echo '<div class="wrap" id="sm_div">';
		
		echo '<div id="icon-tools" class="icon32"><br /></div>';
        echo '<h2>' . __('Simple Debug Log Manager', 'simple-debug') . '</h2>';
		
		echo "<p float='left'><a  href='".get_option('siteurl')."/wp-admin/options-general.php?page=simple-debug-settings' >View Simple Debug Plugin Settings</a></p>";
				
		echo '<div id="poststuff" class="metabox-holder has-right-sidebar">';
		
		echo '<div id="post-body" class="metabox-holder columns-2">';

		$this->debug_log_action_processor();
		
		
		

		Simple_Debug_Plugin::display_box_header('debug_file_info',__('Debug File Information','simple-debug'),false);
		
		
		
		echo "<table class='form-table'>";
		echo "<tr>";
		echo "<th>Debug Log File Location:</th>";
		echo "<td>$log_file</td>";
		echo "</tr>";
		echo "<tr>";
		echo "<th>Debug Log File Size:</th>";
		echo "<td>".size_format(filesize($log_file));
		
		$link = " &nbsp; ";
		
		$link .=  " <form method='post' action='".admin_url()."tools.php?page=debug_log'  style='display:inline;'>";
		$link .=  "<input type='hidden' value='clear_debug_log_file' name='clear_debug_log_file'>";
		$link .=  "<input type='submit' value=' &#10008; &nbsp; Clear Log File ' class='button-secondary'>";
		$link .=  "</form> ";
		
		echo $link;
		
		echo "</td>";
		echo "</tr>";
		echo "</table>";
		
		Simple_Debug_Plugin::display_box_footer(false);
		
		
		

		$debug_log_table = $this->debug_log_table;

		$debug_log_table->prepare_items();
		$debug_log_table->display();
		
		
		echo "</div></div></div>";
		

		
	}
	
	
	
	
	private function exec_tail_file($filename, $count = 10){
		
		ob_start();
		passthru("tail -n $count $filename");
		$result=ob_get_contents();
		
		ob_end_clean();
		
		return $result;
	}
	
	
	
	public function get_debug_log(){
	
		$log_file = WP_CONTENT_DIR . '/debug.log';
		$log_txt = Simple_Debug_Log_Manager::exec_tail_file($log_file, 500);
		
		//this splits the log file using the [date-time] to split the messages, rather than using line breaks which do not work.
		//the array returned by this preg_split contains even elements which are the date-time and the odd elements are the error message.
		$log_contents = preg_split('/(\[\d\d-\w{3}-\d{4}\s+\d\d:\d\d:\d\d\])/', $log_txt, NULL, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);
		
		$i = 0;
		$parsed_logs = array();
		
		while($i <= count($log_contents)){
		
			if( isset($log_contents[ $i ]) ){
			
				//only work with even entries... 
				//even entries are the time-date
				//odd entries are the error messages
				if($i % 2 == 0){
				
					$new_log['date'] = $log_contents[ $i ];
					$new_log['date'] = str_replace("[", "", $new_log['date']);
					$new_log['date'] = str_replace("]", "", $new_log['date']);
					
					try {
					
						$date = new DateTime($new_log['date']);
						$date->setTimezone(new DateTimeZone(get_option('timezone_string'))); 
						$new_log['date'] = $date->format('Y-m-d g:i:s A');
					} catch (Exception $e) {
					
					}
					
					$i++;
					
					$new_log['message'] = $log_contents[ $i ];
					
					list($type, $description) = explode(": ", $new_log['message'], 2);
					
					if(isset($type) && '' !== $type){
					
						if("" == $description){
							$new_log['message'] = $type;
							$new_log['type'] = "Generic";
						}else{
							$new_log['message'] = $description;
							$new_log['type'] = $type;
						}
						
						$parsed_logs[] = $new_log;
					}
				}
			}	
			$i++;
		}
		
		
		return $parsed_logs;
	
	}


}


?>