<?php
/*
 * Plugin Name: 	  Letter Avatars
 * Plugin URI:  	  https://wordpress.org/plugins/letter-avatars/
 * Description: 	  Letter Avatars enable you to use Letters from commenters names instead of generic avatars.
 * Version: 		  3.0
 * Requires at least: 4.8
 * Requires PHP:      7.0
 * Author: 	 	      Sibin Grasic
 * Author URI:        https://sgi.io
 * Text Domain:       letter-avatars
 */

namespace SGI\LtrAv;
use \SGI\LtrAv\Core\Bootstrap as Letter_Avatars;

// Prevent direct access
!defined('WPINC') && die;

// Define Main plugin file
!defined(__NAMESPACE__ . '\FILE') && define(__NAMESPACE__ . '\FILE', __FILE__);

//Define Basename
!defined(__NAMESPACE__ . '\BASENAME') && define(__NAMESPACE__ . '\BASENAME', plugin_basename(FILE));

//Define internal path
!defined(__NAMESPACE__ . '\PATH') && define(__NAMESPACE__ . '\PATH', plugin_dir_path( FILE ));

// Define internal version
!defined(__NAMESPACE__ . '\VERSION') && define (__NAMESPACE__ . '\VERSION', '3.0.0');

!defined(__NAMESPACE__ . '\DOMAIN') && define (__NAMESPACE__ . '\DOMAIN', 'letter-avatars');

// Bootstrap the plugin
require_once (PATH . '/lib/core/Bootstrap.php');

// Run the plugin
function run_letter_avatars()
{

    global $wp_version;

    if ( version_compare( PHP_VERSION, '7.0.0', '<' ) || version_compare($wp_version, '5.0', '<') ) :

        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        deactivate_plugins( __FILE__ );
        wp_die(__('Letter Avatars plugin requires WordPress 5.3.0 and PHP 7.0 or greater. The plugin has now disabled itself', DOMAIN));

    endif;

    $ltrav = new Letter_Avatars();

}

// And awaaaaay we goooo
run_letter_avatars();