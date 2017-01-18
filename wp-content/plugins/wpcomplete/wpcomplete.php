<?php

/**
 * @link              https://wpcomplete.co
 * @since             1.0.0
 * @package           WPComplete
 *
 * @wordpress-plugin
 * Plugin Name:       WPComplete (PRO)
 * Description:       A WordPress plugin that helps your students keep track of their progress through your course or membership site.
 * Version:           1.4.7
 * Author:            Zack Gilbert and Paul Jarvis
 * Author URI:        https://wpcomplete.co
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wpcomplete
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
  die;
}

// Define some variables we will use throughout the plugin:
define( 'WPCOMPLETE_STORE_URL', 'https://wpcomplete.co' );
define( 'WPCOMPLETE_PRODUCT_NAME', 'WPComplete' );
define( 'WPCOMPLETE_PREFIX', 'wpcomplete' );
define( 'WPCOMPLETE_IS_ACTIVATED', wpcomplete_license_is_valid() );

/**
 * PREMIUM:
 * The code that runs to determine if a premium license is valid.
 */
function wpcomplete_license_is_valid() {
  if ( !is_production() ) return true;
  
  $result = get_option( WPCOMPLETE_PREFIX . '_license_status' );

  if ( ( false === $result ) || ( $result === 'valid' ) ) {
    $store_url = WPCOMPLETE_STORE_URL;
    $item_name = WPCOMPLETE_PRODUCT_NAME;
    $license = get_option( WPCOMPLETE_PREFIX . '_license_key' );

    if ( !$license || empty( $license ) )
      return false;

    $api_params = array(
      'edd_action' => 'check_license',
      'license' => $license,
      'item_name' => urlencode( $item_name )
    );

    $response = wp_remote_get( add_query_arg( $api_params, $store_url ), array( 'timeout' => 15, 'sslverify' => false ) );
    
    if ( is_wp_error( $response ) )
      return false;

    $license_data = json_decode( wp_remote_retrieve_body( $response ) );
    $result = false;

    if ($license_data->license == 'valid') {
      update_option( WPCOMPLETE_PREFIX . '_license_status', $license_data->expires);
      $result = $license_data->expires;
    }
  }

  return ( $result !== false ) && ( strtotime($result) > time() );
}

function is_production() {
  if ( defined( 'WPCOM_IS_VIP_ENV' ) && ( true === WPCOM_IS_VIP_ENV ) ) return true;
  if ( $_SERVER['SERVER_NAME'] == 'localhost' ) {
    return false;
  }
  if ( substr( $_SERVER['SERVER_NAME'], -4 ) == '.dev' ) {
    return false;
  }
  return true;
}

/**
 * The code that checks for plugin updates.
 * Borrowed from: https://github.com/YahnisElsts/plugin-update-checker
 */
require plugin_dir_path( __FILE__ ) . 'includes/plugin-update-checker-3.1.php';
$myUpdateChecker = PucFactory::buildUpdateChecker(
    'https://wpcomplete.co/premium.json',
    //'https://wpcomplete.co/free.json',
    __FILE__
);

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wpcomplete-activator.php
 */
function activate_wpcomplete() {
  require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpcomplete-activator.php';
  WPComplete_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wpcomplete-deactivator.php
 */
function deactivate_wpcomplete() {
  require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpcomplete-deactivator.php';
  WPComplete_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wpcomplete' );
register_deactivation_hook( __FILE__, 'deactivate_wpcomplete' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wpcomplete.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wpcomplete() {

  $plugin = new WPComplete();
  $plugin->run();

}
run_wpcomplete();
