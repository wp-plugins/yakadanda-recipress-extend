<?php
/*
Plugin Name: Yakadanda ReciPress Extended
Plugin URI: http://www.yakadanda.com/plugins/yakadanda-recipress-extended/
Description: A WordPress plugin that extends the Recipress Plugin to allow for data in main RSS Feed, cleans output for Recipes with no Instructions, allows for printable Recipes.
Version: 0.0.4
Author: Peter Ricci
Author URI: http://www.yakadanda.com/
License: GPLv2
*/

/* Put setup procedures to be run when the plugin is activated in the following function */
function recipressextend_activate() {
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

function recipressextend_recipress_version() {
  if (!function_exists('get_plugins')) require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
  $plugin_folder = get_plugins('/recipress');
  if ($plugin_folder['recipress.php']['Version'] == '1.9.5') return true;
  else return false;
}
add_action('init', 'recipressextend_recipress_version');

require_once( 'rss.php' );
require_once( 'print.php' );
