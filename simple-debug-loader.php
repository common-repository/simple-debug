<?php
/*
Plugin Name: Simple Debug
Plugin URI: http://MyWebsiteAdvisor.com/tools/wordpress-plugins/simple-debug/
Description: Simple Debug Plugin, You can analyze your WordPress Website to locate any slow functions, plugins or themes.
Version: 1.5
Author: MyWebsiteAdvisor
Author URI: http://MyWebsiteAdvisor.com
*/

if( !session_id() ){
	session_start();
}

register_activation_hook(__FILE__, 'simple_debug_activate');
register_activation_hook(__FILE__, 'simple_debug_cleanup_old_options');

function simple_debug_activate() {

	// display error message to users
	if ($_GET['action'] == 'error_scrape') {                                                                                                   
		die("Sorry, Simple Debug Plugin requires PHP 5.3 or higher. Please deactivate Simple Debug Plugin.");                                 
	}

	if ( version_compare( phpversion(), '5.3', '<' ) ) {
		trigger_error('', E_USER_ERROR);
	}
}



// require simple debug Plugin if PHP 5.3 installed
if ( version_compare( phpversion(), '5.3', '>=') ) {
	define('SD_LOADER', __FILE__);
	
	require_once(dirname(__FILE__) . '/simple-debug-log-manager.php');
	require_once(dirname(__FILE__) . '/simple-debug-log-table.php');
	
	require_once(dirname(__FILE__) . '/simple-debug-tools.php');
		
	require_once(dirname(__FILE__) . '/simple-debug-settings-page.php');
	require_once(dirname(__FILE__) . '/simple-debug-plugin.php');	
	
	$simple_debug = new Simple_Debug_Plugin;
}





function simple_debug_cleanup_old_options(){

	$old_options = array(
		'simple_debug_enabled' => true,
		'simple_debug_log_enabled' => false,
		'simple_debug_phpini_enabled' => false,
		'simple_debug_phpinfo_enabled' => false,
		'simple_debug_db_optimization_enabled' => false,
		'simple_debug_db_performance_enabled' => false,
		'simple_debug_display_error_log' => false,
		'simple_debug_error_log_location' => false
	);
	
	foreach($old_options as $key => $val){
		if(get_option($key)){
			delete_option($key);
		}
	}
	
}





?>