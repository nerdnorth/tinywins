<?php
/**
 * Plugin Name: Logic Hop
 * Plugin URI:	https://logichop.com
 * Description: Personalization for Wordpress.
 * Version:		1.1.0
 * Author:		Logic Hop
 * Author URI:	https://logichop.com
 * License:     Logic Hop Split License
 * Text Domain: logichop
 * Domain Path: languages
 */

if (!defined('ABSPATH')) { header('location: /'); die; }

function logichop_activate () {
	require_once plugin_dir_path(__FILE__) . 'includes/activate.php';
	LogicHop_Activate::activate();
}

function logichop_deactivate () {
	require_once plugin_dir_path(__FILE__) . 'includes/deactivate.php';
	LogicHop_Deactivate::deactivate();
}

register_activation_hook(__FILE__, 'logichop_activate');
register_deactivation_hook(__FILE__, 'logichop_deactivate');

require plugin_dir_path(__FILE__) . 'includes/LogicHop.php';

function logichop_init () {
	$tz_string = get_option('timezone_string');
	$timezone = ($tz_string) ? $tz_string : 'UTC';
	date_default_timezone_set($timezone);
	
	$logichop = new LogicHop(plugin_basename(__FILE__));
	$logichop->init();
	
	return $logichop;
}

$logichop = logichop_init();
