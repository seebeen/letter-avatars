<?php
/*
 * Plugin Name: 	  Letter Avatars
 * Plugin URI:  	  https://wordpress.org/plugins/letter-avatars/
 * Description: 	  Letter Avatars enable you to use Letters from commenters names instead of generic avatars.
 * Version: 		  3.1.0
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

!defined(__NAMESPACE__ . '\FILE')     && define(__NAMESPACE__ . '\FILE', __FILE__);                   // Define Main plugin file
!defined(__NAMESPACE__ . '\BASENAME') && define(__NAMESPACE__ . '\BASENAME', plugin_basename(FILE));  //Define Basename
!defined(__NAMESPACE__ . '\PATH')     && define(__NAMESPACE__ . '\PATH', plugin_dir_path( FILE ));    //Define internal path
!defined(__NAMESPACE__ . '\VERSION')  && define (__NAMESPACE__ . '\VERSION', '3.1.0');                // Define internal version
!defined(__NAMESPACE__ . '\DOMAIN')   && define (__NAMESPACE__ . '\DOMAIN', 'letter-avatars');        // Define Text domain

// Bootstrap the plugin
require (PATH . '/vendor/autoload.php');

// Run the plugin
function run_ltrav()
{

    global $wp_version;

    if (version_compare( PHP_VERSION, '7.0.0', '<' ))
        throw new \Exception(__('Letter Avatars plugin requires PHP 7.0 or greater.', DOMAIN));

    if (version_compare($wp_version, '4.8', '<'))
        throw new \Exception(__('Letter Avatars plugin requires WordPress 4.8.0.', DOMAIN));

    return new Letter_Avatars();

}

// And awaaaaay we goooo
try {

    run_ltrav();

} catch (\Exception $e) {


    require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    deactivate_plugins( __FILE__ );
    wp_die($e->getMessage());

}