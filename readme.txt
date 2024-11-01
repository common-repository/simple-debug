=== Simple Debug ===
Name: Simple Debug
Contributors: MyWebsiteAdvisor, ChrisHurst
Tags: Admin, Plugin, Debug, Performance, Profiling, Query, Hooks, SEO, Speed, Utility, Simple
Requires at least: 2.9
Tested up to: 3.5
Stable tag: 1.5
Donate link: http://MyWebsiteAdvisor.com/donations

Analyzes WordPress website performance, helps to locate slow function hooks.


== Description ==

Simple Debug Plugin for WordPress analyzes the performance of your WordPress website and shows you the slowest performing functions.
Also has the ability to display other useful information such as error log, PHP.ini variables, and DB performance varbailes.
MySQL Database Optimization tool is also included.  
You can also enable and view the debug.log with no editing of wp-config.php file necessary!



Developer Website: http://MyWebsiteAdvisor.com/

Plugin Page: http://MyWebsiteAdvisor.com/tools/wordpress-plugins/simple-debug/

Video Tutorial: http://mywebsiteadvisor.com/learning/video-tutorials/simple-debug-tutorial/

We are looking for testimonials and live examples of our plugins on your website!  
Please submit your website or testimonial here: http://MyWebsiteAdvisor.com/testimonials/  
If we choose your testimonial or website we can link to your site and generate some free traffic for you!


Requirements:

* PHP 5.3


To-do:





== Installation ==

1. Upload `simple-debug/` to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to Simple Debug Plugin settings and enable Simple Debug Plugin.


== Frequently Asked Questions ==

= Plugin doesn't work ... =

Please specify as much information as you can to help us debug the problem. 
Check in your error.log if you can. 
Please send screenshots as well as a detailed description of the problem.





Developer Website: http://MyWebsiteAdvisor.com/

Plugin Page: http://MyWebsiteAdvisor.com/tools/wordpress-plugins/simple-debug/

Video Tutorial: http://mywebsiteadvisor.com/learning/video-tutorials/simple-debug-tutorial/

We are looking for testimonials and live examples of our plugins on your website!
Please submit your website or testimonial here: http://MyWebsiteAdvisor.com/testimonials/
If we choose your testimonial or website we can link to your site and generate some free traffic for you!





== Screenshots ==

1. This website is badly in need of optimization, one function call takes almost 10 seconds!
2. After using the plugin to locate the slow function, our developers have resolved the issue, now the site loads in under one second!
3. Plugin settings screen
4. Example output for the display error log function
5. Example output for the display PHP.ini variables function
6. Example output for the optimize DB function
7. Example output for the display MySQL DB performance variables function.





== Changelog ==

= 1.5 =
* updated plugin to use WordPress settings API
* consolidated the plugin settings so they are all stored in one main setting, rather than individual settings in wp-options table
* updated plugin settings page with tabs, rather than scrolling down the page
* reorgainzed entire plugin file layout


= 1.4.5 =
* added label elements around checkboxes to make the label text clickable.


= 1.4.4 =
* added function exists check for the sys_getloadavg function so it does not cause fatal errors on MS Windows Servers
* added check to see if debug log is enabled before displaying link to debug log screen.


= 1.4.3 =
* fixed plugin settings screen


= 1.4.2 =
* fixed debug log screen


= 1.4.1 =
* fixed plugin loader


= 1.4 =
* added a session_start in the plugin loader, the plugin uses session to track and log function execution time.
* added debug.log system without needing to edit wp-config.php!
* updated readme file
* updated plugin settings page to display better on smaller screens


= 1.3 =
* added check for PHP 5.3 or greater due to the use of anonymous php functions.
* updated readme file

= 1.2.3 =
* fixed improper opening php tag

= 1.2.2 =
* Added link to rate and review this plugin on WordPress.org.

= 1.2.1 =
* updated plugin activation php version check which was causing out of place errors.

= 1.2 =
* Added phpinfo display function
* Added contextual help menu with Plufin FAQ's and Support Link


= 1.1 =
* added error log display function
* added display PHP.ini variables function
* added DB optimization function
* added DB performance analysis tools


= 1.0 =
* Initial release


