<?php
/* 
 Plugin Name: Rewind WP
 Plugin URI: http://wordpress.org/extend/plugins/rewindwp/
 Description: Automatically rewind your WordPress website. 
 Version: 1.0.3
 Author: NoteToServices
 Author URI: https://notetoservices.com/
 License: GPL2
 Text Domain: RewindWP
*/

 //no direct access
if ( ! defined( 'ABSPATH' ) ) { die(); }

global $upload_base_dir;
//WP default upload paths
$uploads = wp_upload_dir();
$upload_base_dir = $uploads['basedir'];

// Set defined global paths
define('RWP_SITE_URL', get_home_url());
define('RWP_DATA_URL', 'https://data.rewindwp.com/'.rwp_remove_protocol(get_home_url()).'/');
define('RWP_WP_ROOT', ABSPATH);
define('RWP_PLUGIN_FILE', __FILE__);
define('RWP_PLUGIN_DIR', dirname(__FILE__));
define('RWP_CONTENT_DIR', basename(content_url()));
define('RWP_PLUGIN_URLDIR', plugin_dir_url(__DIR__));
define('RWP_PLUGIN_BASE', plugin_basename(__FILE__));
define('RWP_PLUGIN_NAME', plugin_basename(RWP_PLUGIN_DIR));
define('RWP_THEME_DIR', get_template_directory());
define('RWP_UPLOAD_DIR', $upload_base_dir);

//adding a rewind now on the admin bar
add_action( 'admin_bar_menu', 'rwp_custom_admin_bar_link', 100 );


// load the plugin settings
spl_autoload_register('RWPLoad');
function RWPLoad($class) 
{
	require_once(RWP_PLUGIN_DIR.'/inc/RWPFunctions.php');
	require_once(RWP_PLUGIN_DIR.'/inc/RWPSettings.php');
}

// Register the settings page and menu
add_action("admin_menu", array("RWPSettings", "initialize"), 30);



        /**
         * rwp_remove_protocol
         *
         * Removes any protocol from a url
         *
         * @since 1.0.0
         * @param @url
         */


function rwp_remove_protocol($url){
    $remove = array("https://","http://");
    return str_replace($remove,"",$url);
}


        /**
         * rwp_custom_admin_link
         *
         * Adds custom Rewind Now link to admin bar that calls the force rewind function
         * Additional function is when the timer is running, it displays the next rewind
         * @since 1.0.0
         * @param @url $adminbar
         */


function rwp_custom_admin_bar_link( $admin_bar ) {

   //get next rewind timestamp
   $nextrewind = rwp_Functions::rwp_get_nextrewind();
   if(!empty($nextrewind)) { 
       //get current time of site
       $dtinit = strtotime(current_time( 'mysql' ));
       //current rewind time
       $dtend = strtotime($nextrewind);
       //get difference
       $interval = abs($dtend - $dtinit);
        //round some minutes
       $minutes = round($interval / 60);
   } else { 
       $minutes = 0; 
   }
   if($minutes == 1) { $mins = ' minute'; } else { $mins = ' minutes'; }
   if($minutes > 0) { 
	  // if we somehow manage to go over 1440 minutes, then anything more than this is invalid
	if($minutes > 1440) { $minutes = 0; }
	$cuslink = 'Rewind Now ('.$minutes.$mins.')';
   } else {
	$cuslink = 'Rewind Now';
   }

   $hreflink = (is_admin() ? 'tools.php?page=rewindwp&forcerewind' : RWP_SITE_URL.'/wp-admin/tools.php?page=rewindwp&forcerewind');

   $admin_bar->add_menu( array(
	'id'    => 'rewindwp',
	'title' => $cuslink,
	'href'  => $hreflink,
	'meta'  => array(
             'title' => __('Rewind Now'),
	),
    ));
}
