<?php


class Simple_Debug_Tools{

	private $_opt = array();
	
	private $_option_name = "simple-debug-settings";
	
	//used for counting number of actions
	private $_hook_count = 0;	
	
	
	
	//setup the class
	public function __construct(){
		$this->_opt = get_option($this->_option_name, array());
	}
	
	
	
	
	
	
	


	public function enable_wp_debug_mode(){
		error_reporting( E_ALL );
		ini_set( 'display_errors', 0 );
		ini_set( 'log_errors', 1 );
		ini_set( 'error_log', WP_CONTENT_DIR . '/debug.log' );	
	}
 
  
  
  
  	public function log_hook_debug($hook){
		
		global $wpdb;
		
		if($this->_hook_count == 0){
			$_SESSION['simple_debug'] = NULL;
			unset($_SESSION['simple_debug']);
		}
		
		$hook_log['name'] = $hook;
		$hook_log['order'] = $this->_hook_count;
				
		$hook_log['mem_useage'] = memory_get_usage();
		$hook_log['max_mem_useage'] = memory_get_peak_usage();
		
		//log query count as well as calculate elapsed 'new' queries
		$hook_log['query_count'] = get_num_queries();
		
		$previous_hook_query_count = ($this->_hook_count > 0) ? $_SESSION['simple_debug'][($this->_hook_count - 1)]['query_count'] : $hook_log['query_count'];
		$hook_log['elapsed_query_count'] = $hook_log['query_count'] - $previous_hook_query_count;
		
		//log time as well as calculate elapsed time since last hook
		$hook_log['time'] =  (float) microtime(true); 
		
		$previous_hook_time = ($this->_hook_count > 0) ? $_SESSION['simple_debug'][($this->_hook_count - 1)]['time'] : $hook_log['time'];
		$hook_log['elapsed_time'] =  (float) $hook_log['time'] - $previous_hook_time;
		
		$_SESSION['simple_debug'][$this->_hook_count] = $hook_log;
		$this->_hook_count++;
	}
	
	
	
	
	public function process_shutdown(){
	
		// Only show the data if the user is an administrator
		global $user_ID;

		if( $user_ID ){
			if( current_user_can('manage_options') ){
			
				echo "<div style='padding:20px;'>";
				$this->print_debug_data();
				echo "</div >";
				
			}
		}
	}
	
	
	
	
	
	public function print_debug_data(){
	
		global $wpdb;
		
		$original_list = $_SESSION['simple_debug'];
		
		usort($_SESSION['simple_debug'], function($a, $b){
			if ($b['elapsed_time'] == $a['elapsed_time']) {
        		return 0;
    		}
    		return ($b['elapsed_time'] < $a['elapsed_time']) ? -1 : 1;
			//return  $b['elapsed_time'] - $a['elapsed_time'];
	
		});
	
		
		

	
	echo "<h2>Simple Debug</h2>";
		echo "<hr>";
			?>
		
		<div  style="height:20px; vertical-align:top; width:50%; float:right; text-align:right; margin-top:5px; padding-right:16px; position:relative;">

		<div id="fb-root"></div>
		<script>(function(d, s, id) {
		  var js, fjs = d.getElementsByTagName(s)[0];
		  if (d.getElementById(id)) return;
		  js = d.createElement(s); js.id = id;
		  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=253053091425708";
		  fjs.parentNode.insertBefore(js, fjs);
		}(document, 'script', 'facebook-jssdk'));</script>
		
		<div class="fb-like" data-href="http://www.facebook.com/MyWebsiteAdvisor" data-send="true" data-layout="button_count" data-width="450" data-show-faces="false"></div>
		
		
		<a href="https://twitter.com/MWebsiteAdvisor" class="twitter-follow-button" data-show-count="false"  >Follow @MWebsiteAdvisor</a>
		<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
	
	
	</div>
	
	<?php
	
	
		
		
		$debug_count = count($_SESSION['simple_debug']);
		echo "<p>Analyzed ".$debug_count." functions.</p>";
		
		$final_hook_number = ($debug_count-1);
		$total_et = number_format(($original_list[$final_hook_number]['time'] - $original_list[0]['time']), 6);
		
		//echo "<p>Page Start Time: ". $original_list[0]['time'] ." seconds.</p>";
		//echo "<p>Page Finish Time: ". $original_list[$final_hook_number]['time'] ." seconds.</p>";
		
		
		echo "<table border=1 width='400px'>";
		
	
		echo "<tr>";
		echo "<td>Total Page Execution Time</td><td>". $total_et ." seconds.</td>";
		echo "</tr>";
	
	
		
		
		//if ( QUERY_CACHE_TYPE_OFF )
			//$wpdb->query( 'SET SESSION query_cache_type = 0;' );
			
		if ( $wpdb->queries ) {
			$total_query_time = 0;
			
			foreach ( $wpdb->queries as $q ) {

				$total_query_time += $q[1];
				
			}
		}
	
		echo "<tr>";
		echo "<td>Total Query Time </td><td>" . number_format($total_query_time, 6)." seconds.</td>";
		echo "</tr>";
		
		echo "<tr>";
		echo "<td>Average Hook Execution Time</td><td>". number_format(($total_et / $debug_count),8) ." seconds. </td>";
		echo "</tr>";
		echo "</table>";
		
		
		
		echo "<br>";
		echo "<h2>Slowest Function Call</h2>";
		echo "<table border=1 width='400px'>";
		
		echo "<tr>";
		echo "<th>Function Name</th>";
		echo "<th>Execution Order #</th>";
		echo "<th>Time to Execute Function</th>";
		echo "</tr>";
		
		echo "<tr>";
		echo "<td>". $_SESSION['simple_debug'][0]['name'] . "</td>";
		echo "<td>" . $_SESSION['simple_debug'][0]['order'] . "</td>";
		echo "<td>" . number_format($_SESSION['simple_debug'][0]['elapsed_time'], 6) ." seconds. </td>";
		echo "</tr>";
		echo "</table>";
		
		
		
		echo "<br>";
		echo "<h2>Top 5 Slowest Function/Hooks</h2>";
		echo "<table border=1 width='400px'>";
		
		echo "<tr>";
		echo "<th>Function Name</th>";
		echo "<th>Time to Execute Function</th>";
		echo "</tr>";
		
		foreach(array_slice($_SESSION['simple_debug'], 0, 5) as $slow_hook){
		
			echo "<tr>";
			echo "<td>" . $slow_hook['name'] . " </td>";
			echo "<td>" . number_format($slow_hook['elapsed_time'], 6) . " seconds. </td>";
			echo "</tr>";
		
		}
		echo "</table>";
		
		
	}

	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	

	
	//reads debug log
	public function get_debug_log(){
	
		$log_txt = $this->exec_tail_file($log_file, 100);
		$log_contents = explode("\n", $log_txt);
		
		$messages = array();
		foreach($log_contents as $item){
			$messages[] = $item;
		}
		
		return $messages;
	
	}
	
	
	//shows error log if it exists
	public function show_error_log(){
					
		$error_logging_enabled = ini_get('log_errors') && (ini_get('log_errors') != 'Off');
		if(!$error_logging_enabled){
			echo "<p>Error Logging is Disabled!<p>";
		}else{
			if(ini_get('error_log')){
				$log_file = ini_get('error_log');
				$this->display_error_log($log_file);
			}else{
				echo "<p>Error Log not found, please set the 'error_log' in php.ini<p>";
				echo "<p>You can also try using the WordPress Debug Log!<p>";
			}
		}
	}
	
	
	//worker function to display error log file
	private function display_error_log($log_file){
	
		if( ini_get('safe_mode') ){
			echo "<p><font color='red'>PHP Safe Mode is enabled!<br><b>Disable Safe Mode in php.ini!</b></font></p>";
		}

		if(strpos(ini_get('disable_functions'), 'passthru')  !== false){
			echo "<p><font color='red'>Disabled Functions: ".ini_get('disable_functions')."<br><b>Please enable 'exec' function in php.ini!</b></font></p>";
		}
				
		echo "<p>Reading Log File: $log_file<p>";
		
		echo '<div style="overflow-y:scroll; height:600px; border: 1px solid #ccc; margin:5px; padding:5px;">';
		
		$log_txt = $this->exec_tail_file($log_file, 50);
		$log_contents = explode("\n", $log_txt);
		
		$log_contents = array_reverse($log_contents);
		
		foreach($log_contents as $item){
			echo "<p>$item</p>";
		}
		
		echo "</div>";
		
	}
	
	//worker function to read end of a log file using linux tail command
	private function exec_tail_file($filename, $count = 10){
		
		ob_start();
		passthru("tail -n $count $filename");
		$result=ob_get_contents();
		
		ob_end_clean();
		
		return $result;
	}
	
	
	


	// displays the plugin options array
	public function show_plugin_settings(){
				
		echo "<pre>";
		
			print_r($this->_opt);
		
		echo "</pre>";
			
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	

	//
	//PHP Tools
	//

	// displays php.ini config info
	public function show_phpini(){		
	?>
		<div style="overflow: scroll; height:600px;">
			<table border="1" width="400px">
			
			<tr>
			<th>Setting Name</th>
			<th>Value</th>
			
			</tr>
			
			<?php 
			foreach(ini_get_all() as $key=>$data){
				echo "<tr>";
				echo "<td>".$key."</td>";
				echo "<td>".$data['local_value']."</td>";
				echo "<tr>";
			}

			?>
			</table>
		</div>
	<?php	
	}


	//fidplays phpinfo funciton output from webserver
	public function show_phpinfo(){			
	?>

		<div style="overflow:scroll; height:600px;">
		<style>
		
			.center {text-align: center;}
			.center table { margin-left: auto; margin-right: auto; text-align: left;}
			.center th { text-align: center !important; }
			
			.p {text-align: left;}
			.e {background-color: #ccccff; font-weight: bold; color: #000000;}
			.h {background-color: #9999cc; font-weight: bold; color: #000000;}
			.v {background-color: #cccccc; color: #000000;}
			.vr {background-color: #cccccc; text-align: right; color: #000000;}

		</style>
		<?php
		
			ob_start();
			phpinfo();
			$pinfo = ob_get_contents();
			ob_end_clean();
			 
			$pinfo = preg_replace( '%^.*<body>(.*)</body>.*$%ms','$1',$pinfo);
			echo $pinfo;	
			
		?>
		</div>
		
	<?php
	}























	//
	//MySQL Database Tools
	//


	//show db optimizer button and process action when button is pushed
	public function show_db_optimization_tool(){
		echo "</form>";
		echo "<form method='post'>";
		echo '<div style="padding-left: 1.5em; margin-left:5px;">';
		echo "<p class='submit'>";
		echo "<input type='submit' name='Optimize_Database' value='&#10004; Optimize Database'  class='button-primary' />";
		echo "</p>";
		echo "</div>";
		echo "</form>";
		
		if(isset($_POST['Optimize_Database'])){
			$this->perform_database_optimization();	
		}
	
	}
	
	

	//db optimizer worker function
	private function perform_database_optimization(){
		
		$initial_table_size = 0;
		$final_table_size = 0;
			
		echo "<br>Optimizing Database...<br>";
		
		$local_query = 'SHOW TABLE STATUS FROM `'. DB_NAME.'`';
		$result = mysql_query($local_query);
		if (mysql_num_rows($result)){
			
			while ($row = mysql_fetch_array($result)){
				//var_dump($row);
				
				$table_size = ($row[ "Data_length" ] + $row[ "Index_length" ]) / 1024;
				
				$optimize_query = "OPTIMIZE TABLE ".$row['Name'];
				if(mysql_query($optimize_query)){
				
					//if( $debug_enabled == "true"){
						echo "Table: " . $row['Name'] . " optimized!";
						echo "<br>";
					//}
				}
				
				$initial_table_size += $table_size; 
				
			}
			
			echo "Done!<br>";
			
		}
		
		
		
		
		$local_query = 'SHOW TABLE STATUS FROM `'. DB_NAME.'`';
		$result = mysql_query($local_query);
		if (mysql_num_rows($result)){
			while ($row = mysql_fetch_array($result)){
				$table_size = ($row[ "Data_length" ] + $row[ "Index_length" ]) / 1024;
				$final_table_size += $table_size;
			}
		}
		
		
		
		echo "<br>";
		echo "Initial DB Size: " . number_format($initial_table_size, 2) . " KB<br>";
		echo "Final DB Size: " . number_format($final_table_size, 2) . " KB<br>";
		
		$space_saved = $initial_table_size - $final_table_size;
		$opt_pctg = 100 * ($space_saved / $initial_table_size);
		echo "Space Saved: " . number_format($space_saved,2) . " KB  (" .  number_format($opt_pctg, 2) . "%)<br>";
		echo "<br>";
	
	}
	
	
	


	
	//displays mysql performance variables
	public function show_db_performance_check(){
	
		$query = 'show variables where Variable_Name IN ("max_connections", "wait_timeout", "thread_cache_size", "table_cache", "key_buffer_size", "tmp_table_size");';
		$result = mysql_query($query);
		if (mysql_num_rows($result)){
			echo "DB Performance Variables<br>";
			$this->display_db_vars_table($result);
			echo "<br>";
		}


		$perf_query = "show variables like '%query_cache%'";
		$result = mysql_query($perf_query);
		if (mysql_num_rows($result)){
			//while ($row = mysql_fetch_assoc($result)){
				echo "DB Query Cache Variables<br>";
				$this->display_db_vars_table($result);
				echo "<br>";
	
			//}
		}
		
		$perf_query2 = "SHOW STATUS LIKE 'Qc%';";
		$result = mysql_query($perf_query2);
		if (mysql_num_rows($result)){
			//while ($row = mysql_fetch_assoc($result)){
				echo "DB Status Variables";
				$this->display_db_vars_table($result);
	
			//}
		}

	}

	//takes a mysql result and prints out a table
	private function display_db_vars_table($result){
		if (mysql_num_rows($result)){
			echo "<table border='1'>";
			echo "<tr><th>Variable Name</th><th>Value</th></tr>";
			while ($row = mysql_fetch_assoc($result)){
				echo "<tr>";
				
					echo "<td>" . $row['Variable_name'] . "</td>";
					echo "<td>" . $row['Value'] . "</td>";
				
				echo "<tr>";
			}
			echo "</table>";
		}
	}
	





}

?>