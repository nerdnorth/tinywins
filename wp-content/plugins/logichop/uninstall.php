<?php

/**
 * Uninstall Logic Hop Plugin
 *
 * @link       http://logichop.com
 * @since      1.0.0
 *
 * @package    logichop
 */

if (!defined('WP_UNINSTALL_PLUGIN')) exit;

if (WP_UNINSTALL_PLUGIN == 'logichop/logichop.php') {
	if (function_exists('is_multisite') && is_multisite()) {
		if (is_super_admin() == false) return;
		
		$blogs = wp_get_sites();
		foreach ($blogs as $blog) {
			switch_to_blog( $blog['blog_id'] );
			logichop_delete_plugin();
			restore_current_blog();
		}
	} else {
		if (!current_user_can( 'activate_plugins' )) return;
		logichop_delete_plugin();
	}
}

function logichop_delete_plugin () {
	delete_option('logichop-settings');
	
	$goals = get_posts(array('numberposts' => -1, 'post_type' => 'logichop-goals'));
	if ($goals)foreach ($goals as $post) wp_delete_post($post->ID, true);
			
	$conditions = get_posts(array('numberposts' => -1, 'post_type' => 'logichop-conditions'));
	if ($conditions)foreach ($conditions as $post) wp_delete_post($post->ID, true);
	
	unregister_post_type('logichop-goals');
	unregister_post_type('logichop-conditions');
}