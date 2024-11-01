<?php

 
 
class Simple_Debug_Plugin{

	//plugin version number
	public $version = "1.5";
	
	
	//for the plugin settings page
	private $settings_page;
	
	//for the debug log table
	private $debug_log_manager;
	
	//for the plugin debug tools
	private $debug_tools;
	

	//settings page title, to be displayed in menu and page headline
	private $page_title = "Simple Debug";
	
	//page name, also will be used as option name to save all options
	private $plugin_name = "simple-debug-settings";
	
	//options are: edit, upload, link-manager, pages, comments, themes, plugins, users, tools, options-general
	private $page_icon = "options-general"; 
	
	//store plugin options
	private $opt = array();
	
	

	public function __construct() {
	
		$this->opt = get_option($this->plugin_name);
	
		$this->settings_page  = new Simple_Debug_Settings_Page( $this->plugin_name );
		
		
		add_action( 'admin_init', array($this, 'admin_init') );
		
		//create menu in wp admin menu
		add_action( 'admin_menu', array($this, 'admin_menu') );
		
		//add help menu to settings page
		add_filter('contextual_help', array(&$this,'admin_help'), 10, 3);
		
		// add plugin "Settings" action on plugin list
		add_action('plugin_action_links_' . plugin_basename(SD_LOADER), array(&$this, 'add_plugin_actions'));
		
		// add links for plugin help, donations,...
		add_filter('plugin_row_meta', array(&$this, 'add_plugin_links'), 10, 2);
		
		
		
		$this->debug_tools = new Simple_Debug_Tools;
		
		
		
		$opt = get_option($this->plugin_name); 
		
		if( "true" === $opt['debug_settings']['enable_debug_log'] ){
		
			// basically the same as setting WP_DEBUG = true in wp-config.php
			// will create a debug.log file in the wp-content directory
			add_action('init', array(&$this->debug_tools, 'enable_wp_debug_mode'));
			
			$debug_log_manager = new Simple_Debug_Log_Manager();
			$this->debug_log_manager = $debug_log_manager;

			add_action( 'admin_head', array($debug_log_manager, 'screen_options') );
			add_action( 'admin_menu', array($debug_log_manager, 'simple_debug_log_admin_menu') );
		
		}
		
		

		if( "true" === $opt['debug_settings']['enable_wordpress_debug'] ){
		
			if ( !defined( 'SAVEQUERIES' ) )
				define( 'SAVEQUERIES', TRUE );
			
			//enable logging for ALL actions
			add_action( 'all', array(&$this->debug_tools, 'log_hook_debug'),10,1);
			
			//adds debug output below the footer
			add_action("shutdown", array(&$this->debug_tools, 'process_shutdown'));
			
		}
		
	}






	/**
	 * Add "Settings" action on installed plugin list
	 */
	public function add_plugin_actions($links) {
		array_unshift($links, '<a href="options-general.php?page='. $this->plugin_name .'">' . __('Settings') . '</a>');
		
		return $links;
	}
	
	/**
	 * Add links on installed plugin list
	 */
	public function add_plugin_links($links, $file) {
		if($file == plugin_basename(SD_LOADER)) {
			$rate_url = 'http://wordpress.org/support/view/plugin-reviews/' . basename(dirname(__FILE__)) . '?rate=5#postform';
			$links[] = '<a href="'.$rate_url.'" target="_blank" title="Click Here to Rate and Review this Plugin on WordPress.org">Rate This Plugin</a>';

		}
		
		return $links;
	}
	
	
	

    /**
     * Returns all of the settings sections
     *
     * @return array settings sections
     */
    private function get_settings_sections() {
	
		$settings_sections = array(
			array(
				'id' => 'debug_settings',
				'title' => __( 'Debug Settings', $this->plugin_name )
			),
			array(
				'id' => 'php_settings',
				'title' => __( 'PHP Settings', $this->plugin_name )
			),
			array(
				'id' => 'db_settings',
				'title' => __( 'MySQL Settings', $this->plugin_name )
			)
		
		);

								
        return $settings_sections;
    }


    /**
     * Returns all of the settings fields
     *
     * @return array settings fields
     */
    private function get_settings_fields() {
	
		$debug_log_desc = "Enable and Display the <a href='".get_option('siteurl')."/wp-admin/tools.php?page=debug_log' >WordPress Debug.log</a>";
	
		$settings_fields = array(
		
			'debug_settings' => array(
                array(
                    'name' 		=> 'enable_debug_log',
                    'label' 	=> __( 'WordPress Debug Log', $this->plugin_name ),
                    'desc' 		=> $debug_log_desc,
                    'type' 		=> 'radio',
					'default'	=> 'false',
					'options' 	=> array(
                        'true' 		=> 'Enabled',
                        'false' 	=> 'Disabled'
                    )
                ),
				array(
                    'name' 		=> 'enable_error_log',
                    'label' 	=> __( 'Web Server Error Log', $this->plugin_name ),
                    'desc' 		=> __( 'Enable and Display the Web Server Error.log', $this->plugin_name ),
                    'type' 		=> 'radio',
					'default'	=> 'false',
					'options' 	=> array(
                        'true' 		=> 'Enabled',
                        'false' 	=> 'Disabled'
                    )
                ),
				array(
                    'name' 		=> 'enable_wordpress_debug',
                    'label' 	=> __( 'WordPress Site Debug', $this->plugin_name ),
					'desc' 		=> __( 'Enable Debugging of All WordPress Functions<br>WARNING: Do not leave this enabled all the time, it will cause a slight decrease in performance because it is checking every function that runs.', $this->plugin_name ),
                    'type' 		=> 'radio',
					'default'	=> 'false',
					'options' 	=> array(
                        'true' 		=> 'Enabled',
                        'false' 	=> 'Disabled'
                    )
                ),
				array(
                    'name' => 'enable_display_plugin_settings',
                    'label' => __( 'Plugin Settings Debug', $this->plugin_name ),
                    'desc' => __( 'Display Plugin Settings Data for Simple Debug Plugin', $this->plugin_name ),
                    'type' => 'radio',
					'default'=> 'false',
					'options' => array(
                        'true' => 'Enabled',
                        'false' => 'Disabled'
                    )
                )
			),
			
			'php_settings' => array(
				array(
                    'name' => 'enable_phpini',
                    'label' => __( 'PHP.ini', $this->plugin_name ),
                    'desc' => __( 'Display PHP.ini File Info', $this->plugin_name ),
                    'type' => 'radio',
					'default'=> 'false',
					'options' => array(
                        'true' => 'Enabled',
                        'false' => 'Disabled'
                    )
                ),
				array(
                    'name' => 'enable_phpinfo',
                    'label' => __( 'PHPinfo()', $this->plugin_name ),
                    'desc' => __( 'Display PHPinfo() Function', $this->plugin_name ),
                    'type' => 'radio',
					'default'=> 'false',
					'options' => array(
                        'true' => 'Enabled',
                        'false' => 'Disabled'
                    )
                )
			),
			
			'db_settings' => array(
				array(
                    'name' => 'enable_mysql_performance_tool',
                    'label' => __( 'MySQL Performance', $this->plugin_name ),
                    'desc' => __( 'Enable MySQL Performance Tool', $this->plugin_name ),
                   	'type' => 'radio',
					'default'=> 'false',
					'options' => array(
                        'true' => 'Enabled',
                        'false' => 'Disabled'
                    )
                ),
				array(
                    'name' => 'enable_mysql_optimiztion_tool',
                    'label' => __( 'MySQL Optimization', $this->plugin_name ),
                    'desc' => __( 'Enable MySQL Optimization Tool', $this->plugin_name ),
                    'type' => 'radio',
					'default'=> 'false',
					'options' => array(
                        'true' => 'Enabled',
                        'false' => 'Disabled'
                    )
                )
			)
		);
		
        return $settings_fields;
    }





	private function get_settings_sidebar(){
	
		$plugin_resources = "<p><a href='http://mywebsiteadvisor.com/tools/wordpress-plugins/simple-debug/' target='_blank'>Plugin Homepage</a></p>
			<p><a href='http://mywebsiteadvisor.com/contact-us/'  target='_blank'>Plugin Support</a></p>
			<p><a href='http://mywebsiteadvisor.com/contact-us/'  target='_blank'>Contact Us</a></p>
			<p><a href='http://wordpress.org/support/view/plugin-reviews/simple-debug?rate=5#postform'  target='_blank'>Rate and Review This Plugin</a></p>";
	
	
		$more_plugins = "<p><a href='http://mywebsiteadvisor.com/tools/premium-wordpress-plugins/'  target='_blank'>Premium WordPress Plugins!</a></p>
			<p><a href='http://profiles.wordpress.org/MyWebsiteAdvisor/'  target='_blank'>Free Plugins on Wordpress.org!</a></p>
			<p><a href='http://mywebsiteadvisor.com/tools/wordpress-plugins/'  target='_blank'>Free Plugins on MyWebsiteAdvisor.com!</a></p>";
	
		$follow_us = "<p><a href='http://facebook.com/MyWebsiteAdvisor/'  target='_blank'>Follow us on Facebook!</a></p>
			<p><a href='http://twitter.com/MWebsiteAdvisor/'  target='_blank'>Follow us on Twitter!</a></p>
			<p><a href='http://www.youtube.com/mywebsiteadvisor'  target='_blank'>Watch us on YouTube!</a></p>
			<p><a href='http://MyWebsiteAdvisor.com/'  target='_blank'>Visit our Website!</a></p>";
	
		$sidebar_info = array(
			array(
				'id' => 'diagnostic',
				'title' => 'Plugin Diagnostic Check',
				'content' => $this->do_diagnostic_sidebar()		
			),
			array(
				'id' => 'resources',
				'title' => 'Plugin Resources',
				'content' => $plugin_resources	
			),
			array(
				'id' => 'more_plugins',
				'title' => 'More Plugins',
				'content' => $more_plugins	
			),
			array(
				'id' => 'follow_us',
				'title' => 'Follow MyWebsiteAdvisor',
				'content' => $follow_us	
			)
		);
		
		return $sidebar_info;

	}


	private function show_debug_log_link(){
		if(isset($this->opt['debug_settings']['enable_debug_log']) && 'true' === $this->opt['debug_settings']['enable_debug_log']){
			echo "<p float='left'><a  href='".get_option('siteurl')."/wp-admin/tools.php?page=debug_log' >View Simple Debug Log</a></p>";
		}
	}


	//plugin settings page template
    public function plugin_settings_page(){
	
		echo "<style> 
		.form-table{ clear:left; } 
		.nav-tab-wrapper{ margin-bottom:0px; }
		</style>";
		
		echo $this->display_social_media(); 
		 
        echo '<div class="wrap" >';
		
			echo '<div id="icon-'.$this->page_icon.'" class="icon32"><br /></div>';
			
			echo "<h2>".$this->page_title." Plugin Settings</h2>";
			
			$this->show_debug_log_link();
			
			$this->settings_page->show_tab_nav();
			
			echo '<div id="poststuff" class="metabox-holder has-right-sidebar">';
			
				echo '<div class="inner-sidebar">';
					echo '<div id="side-sortables" class="meta-box-sortabless ui-sortable" style="position:relative;">';
					
						$this->settings_page->show_sidebar();
					
					echo '</div>';
				echo '</div>';
			
				echo '<div class="has-sidebar" >';			
					echo '<div id="post-body-content" class="has-sidebar-content">';
						
						$this->settings_page->show_settings_forms();
						
					echo '</div>';
				echo '</div>';
				
			echo '</div>';
			
        echo '</div>';
		
    }





   	public function admin_menu() {
		
        $this->page_menu = add_options_page( $this->page_title, $this->page_title, 'manage_options',  $this->plugin_name, array($this, 'plugin_settings_page') );
    }


	public function admin_help($contextual_help, $screen_id, $screen){
	
		
		global $simple_debug_log_manager_page;
		
		if ($screen_id == $this->page_menu || $screen_id == $simple_debug_log_manager_page) {
				
			$support_the_dev = $this->display_support_us();
			$screen->add_help_tab(array(
				'id' => 'developer-support',
				'title' => "Support the Developer",
				'content' => "<h2>Support the Developer</h2><p>".$support_the_dev."</p>"
			));
			
			$screen->add_help_tab(array(
				'id' => 'plugin-support',
				'title' => "Plugin Support",
				'content' => "<h2>{$this->page_title} Support</h2><p>For {$this->page_title} Plugin Support please visit <a href='http://mywebsiteadvisor.com/support/' target='_blank'>MyWebsiteAdvisor.com</a></p>"
			));
			
			

			$screen->set_help_sidebar("<p>Please Visit us online for more Free WordPress Plugins!</p><p><a href='http://mywebsiteadvisor.com/tools/wordpress-plugins/' target='_blank'>MyWebsiteAdvisor.com</a></p><br>");
			
		}
			
		

	}
	
	
	
	private function do_diagnostic_sidebar(){
	
		ob_start();
		
			echo "<p>Plugin Version: ".$this->version."</p>";
			
			echo "<p>Server OS: ".PHP_OS."</p>";
			
			echo "<p>Required PHP Version: 5.3+<br>";
			echo "Current PHP Version: " . phpversion() . "</p>";
				
			echo "<p>Memory Use: " . number_format(memory_get_usage()/1024/1024, 1) . " / " . ini_get('memory_limit') . "</p>";
			
			echo "<p>Peak Memory Use: " . number_format(memory_get_peak_usage()/1024/1024, 1) . " / " . ini_get('memory_limit') . "</p>";
			
			if(function_exists('sys_getloadavg')){
				$lav = sys_getloadavg();
				echo "<p>Server Load Average: ".$lav[0].", ".$lav[1].", ".$lav[2]."</p>";
			}
		
		return ob_get_clean();
				
	}
	
	
	//build optional tabs, using debug tools class worker methods as callbacks
	private function build_optional_tabs(){

		//db settings
		$db_performance = array(
			'id' => 'db_performance',
			'title' => __( 'MySQL Performance Tool', $this->plugin_name ),
			'callback' => array($this->debug_tools, 'show_db_performance_check')
		);
		
		$db_optimiation = array(
			'id' => 'db_optimization',
			'title' => __( 'MySQL Optimization Tool', $this->plugin_name ),
			'callback' => array($this->debug_tools, 'show_db_optimization_tool')
		);
			
		$enabled = isset($this->opt['db_settings']['enable_mysql_optimiztion_tool']) ? $this->opt['db_settings']['enable_mysql_optimiztion_tool'] : 'false';
		if( $enabled === 'true' ){ 	
			$this->settings_page->add_section( $db_optimiation );
		}
		
		$enabled = isset($this->opt['db_settings']['enable_mysql_performance_tool']) ? $this->opt['db_settings']['enable_mysql_performance_tool'] : 'false';
		if( $enabled === 'true' ){ 	
			$this->settings_page->add_section( $db_performance );
		}	
		
		
		
		
		
		//php settings
		$phpini = array(
			'id' => 'phpini',
			'title' => __( 'PHP.ini Info', $this->plugin_name ),
			'callback' => array($this->debug_tools, 'show_phpini')
		);
		
		$phpinfo = array(
			'id' => 'phpinfo',
			'title' => __( 'PHPinfo Function', $this->plugin_name ),
			'callback' => array($this->debug_tools, 'show_phpinfo')
		);
	
		$enabled = isset($this->opt['php_settings']['enable_phpini']) ? $this->opt['php_settings']['enable_phpini'] : 'false';
		if( $enabled === 'true' ){ 	
			$this->settings_page->add_section( $phpini );
		}
		
		$enabled = isset($this->opt['php_settings']['enable_phpinfo']) ? $this->opt['php_settings']['enable_phpinfo'] : 'false';
		if( $enabled === 'true' ){ 	
			$this->settings_page->add_section( $phpinfo );
		}		
		
		
		
		
		
			
		//general debug settings
		$plugin_debug = array(
			'id' => 'plugin_debug',
			'title' => __( 'Plugin Settings Debug', $this->plugin_name ),
			'callback' => array($this->debug_tools, 'show_plugin_settings')
		);

		$error_log = array(
			'id' => 'error_log',
			'title' => __( 'Web Server Error Log', $this->plugin_name ),
			'callback' => array($this->debug_tools, 'show_error_log')
		);

		$enabled = isset($this->opt['debug_settings']['enable_error_log']) ? $this->opt['debug_settings']['enable_error_log'] : 'false';
		if( $enabled === 'true' ){ 	
			$this->settings_page->add_section( $error_log );
		}
		
		$enabled = isset($this->opt['debug_settings']['enable_display_plugin_settings']) ? $this->opt['debug_settings']['enable_display_plugin_settings'] : 'false';
		if( $enabled === 'true' ){ 	
			$this->settings_page->add_section( $plugin_debug );
		}
		
	}
	
	
	
	
	
    public function admin_init() {

        //set the settings
        $this->settings_page->set_sections( $this->get_settings_sections() );
        $this->settings_page->set_fields( $this->get_settings_fields() );
		$this->settings_page->set_sidebar( $this->get_settings_sidebar() );

		//build optional tabs for the tools which are enabled
		$this->build_optional_tabs();

        //initialize settings
        $this->settings_page->admin_init();
    }




	public function display_support_us(){
				
		$string = '<p><b>Thank You for using the '.$this->page_title.' Plugin for WordPress!</b></p>';
		$string .= "<p>Please take a moment to <b>Support the Developer</b> by doing some of the following items:</p>";
		
		$rate_url = 'http://wordpress.org/support/view/plugin-reviews/' . basename(dirname(__FILE__)) . '?rate=5#postform';
		$string .= "<li><a href='$rate_url' target='_blank' title='Click Here to Rate and Review this Plugin on WordPress.org'>Click Here</a> to Rate and Review this Plugin on WordPress.org!</li>";
		
		$string .= "<li><a href='http://facebook.com/MyWebsiteAdvisor' target='_blank' title='Click Here to Follow us on Facebook'>Click Here</a> to Follow MyWebsiteAdvisor on Facebook!</li>";
		$string .= "<li><a href='http://twitter.com/MWebsiteAdvisor' target='_blank' title='Click Here to Follow us on Twitter'>Click Here</a> to Follow MyWebsiteAdvisor on Twitter!</li>";
		$string .= "<li><a href='http://mywebsiteadvisor.com/tools/premium-wordpress-plugins/' target='_blank' title='Click Here to Purchase one of our Premium WordPress Plugins'>Click Here</a> to Purchase Premium WordPress Plugins!</li>";
	
		return $string;
	}
	
	
	
	
	
	public function display_social_media(){
	
		$social = '<style>
	
		.fb_edge_widget_with_comment {
			position: absolute;
			top: 0px;
			right: 200px;
		}
		
		</style>
		
		<div  style="height:20px; vertical-align:top; width:50%; float:right; text-align:right; margin-top:5px; padding-right:16px; position:relative;">
		
			<div id="fb-root"></div>
			<script>(function(d, s, id) {
			  var js, fjs = d.getElementsByTagName(s)[0];
			  if (d.getElementById(id)) return;
			  js = d.createElement(s); js.id = id;
			  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=253053091425708";
			  fjs.parentNode.insertBefore(js, fjs);
			}(document, "script", "facebook-jssdk"));</script>
			
			<div class="fb-like" data-href="http://www.facebook.com/MyWebsiteAdvisor" data-send="true" data-layout="button_count" data-width="450" data-show-faces="false"></div>
			
			
			<a href="https://twitter.com/MWebsiteAdvisor" class="twitter-follow-button" data-show-count="false"  >Follow @MWebsiteAdvisor</a>
			<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
		
		
		</div>';
		
		return $social;

	}	
	
	
	
	public function display_box_header($id, $title, $right = false) {
	?>
		<div id="<?php echo $id; ?>" class="postbox">
			<h3 class="hndle"><span><?php echo $title ?></span></h3>
			<div class="inside">
	<?php	
	}
	
	public function display_box_footer( $right = false) {
	?>
			</div>
		</div>
	<?php
	}
		

}
 
?>