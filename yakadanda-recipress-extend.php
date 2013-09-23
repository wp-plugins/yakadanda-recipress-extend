<?php
/*
Plugin Name: Yakadanda ReciPress Extended
Plugin URI: http://www.yakadanda.com/plugins/yakadanda-recipress-extended/
Description: A WordPress plugin that extends the Recipress Plugin to allow for data in main RSS Feed, cleans output for Recipes with no Instructions, allows for printable Recipes.
Version: 0.0.5
Author: Peter Ricci
Author URI: http://www.yakadanda.com/
License: GPLv2
*/

/* Put setup procedures to be run when the plugin is activated in the following function */
function recipressextend_activate() {
  if ( ! get_option('recipressextend_options') )
    add_option('recipressextend_options', null, false, false);
  if ( ! function_exists('recipress_recipe') ) {
    // Deactivate ourself
    deactivate_plugins(basename(__FILE__));
    
    wp_die("Sorry, but you can't run this plugin, it requires ReciPress activated.");
  }
}
register_activation_hook( __FILE__, 'recipressextend_activate' );

// On deacativation, clean up anything your component has added.
function recipressextend_deactivate() {
  // You might want to delete any options or tables that your component created.
  
}
register_deactivation_hook( __FILE__, 'recipressextend_deactivate' );

if( !defined('RECIPRESS_EXTEND_VER') ) { define('RECIPRESS_EXTEND_VER', '0.0.5'); }
if( !defined('RECIPRESS_EXTEND_PLUGIN_DIR') ) { define('RECIPRESS_EXTEND_PLUGIN_DIR', plugin_dir_path(__FILE__) ); }
if( !defined('RECIPRESS_EXTEND_PLUGIN_URL') ) { define('RECIPRESS_EXTEND_PLUGIN_URL', plugins_url(null, __FILE__) ); }
if( !defined('RECIPRESS_EXTEND_THEME_DIR') ) { define('RECIPRESS_EXTEND_THEME_DIR', get_stylesheet_directory() ); }
if( !defined('RECIPRESS_EXTEND_THEME_URL') ) { define('RECIPRESS_EXTEND_THEME_URL', get_stylesheet_directory_uri() ); }

// Register scripts & styles
add_action( 'init', 'recipressextend_register' );
function recipressextend_register() {
  /* Register styles */
  wp_register_style( 'recipressextend-print', RECIPRESS_EXTEND_PLUGIN_URL . '/css/print.css', false, RECIPRESS_EXTEND_VER, 'all' );
  
  /* Register scripts */
  wp_register_script( 'recipressextend-print', RECIPRESS_EXTEND_PLUGIN_URL . '/js/print.js', array('jquery'), RECIPRESS_EXTEND_VER, true );
}

add_action('init', 'recipressextend_recipress_version');
function recipressextend_recipress_version() {
  if (!function_exists('get_plugins')) require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
  $plugin_folder = get_plugins('/recipress');
  //if ($plugin_folder['recipress.php']['Version'] == '1.9.5') return true;
  //else return false;
  
  //return true;
  
  return $plugin_folder['recipress.php']['Version'] ;
}

function recipressextend_recipress_version_check() {
  $recipress_ver = recipressextend_recipress_version();
  
  if ( ($recipress_ver == '1.9.5') || ($recipress_ver == '1.9.4') ) return true;
  else return false;
}

require_once( 'rss.php' );
require_once( 'print.php' );
