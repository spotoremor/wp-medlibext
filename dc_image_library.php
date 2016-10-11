<?php
/**
 * @wordpress-plugin
 * Plugin Name:       DC Image Library
 * Plugin URI:        http://www.nowcom.com/
 * Description:       Dealer Center image library extension
 * Version:           1.0.0
 * Author:            Nowcom Corporation
 * Author URI:        http://www.nowcom.com/
 * License:           proprietary
 * License URI:       
 * Text Domain:       dc_image_library
  */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Load core class
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/dc_image_library_core.php';


/** This action is documented in includes/class-plugin-name-activator.php */
register_activation_hook( __FILE__, array( 'DC_Image_Library_Core', 'activate' ) );

/** This action is documented in includes/class-plugin-name-deactivator.php */
register_deactivation_hook( __FILE__, array( 'DC_Image_Library_Core', 'deactivate' ) );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_dc_image_library() {
	$plugin = new DC_Image_Library_Core();
	$plugin->run();
}
run_dc_image_library();