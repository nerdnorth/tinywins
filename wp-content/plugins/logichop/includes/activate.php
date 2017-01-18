<?php

/**
 * @since      1.0.0
 * @package    LogicHop
 * @subpackage LogicHop/includes
 */
class LogicHop_Activate {

	/**
	 * Activation Routine
	 *
	 * Called during plugin activation.
	 * Check PHP & Wordpress versions
	 *
	 * @since    1.0.0
	 */
	public static function activate () {
		global $wp_version;

		$php 	= '5.4';
		$wp  	= '4.5';
		$error 	= false;
		
		if (version_compare($wp_version, $wp, '<')) {
			deactivate_plugins( basename( __FILE__ ) );
			
			$error = sprintf('<h1>%s</h1>
							<p>
								%s %s. 
								%s
							</p>
							<a href="%s">%s</a>',
							__('Important: Incompatible Wordpress Version', 'logichop'), // H1
							__('This plugin can not be activated because it requires a WordPress version greater than', 'logichop'), // P1
							$wp,
							__('Please update to the latest version of Wordpress.', 'logichop'), // P2
							admin_url('plugins.php'),
							__('Back to Plugins', 'logichop') // A
						);
		}
		
		if (version_compare(PHP_VERSION, $php, '<')) {
			deactivate_plugins( basename( __FILE__ ) );
			
			$error = sprintf('<h1>%s</h1>
							<p>
								%s %s. 
								%s
							</p>
							<a href="%s">%s</a>',
							__('Important: Incompatible PHP Version', 'logichop'), // H1
							__('This plugin can not be activated because it requires a PHP version greater than', 'logichop'), // P1
							$php,
							__('Your PHP version can be updated by your hosting company.', 'logichop'), // P2
							admin_url('plugins.php'),
							__('Back to Plugins', 'logichop') // A
						);
		}
		
		if ($error) {
			deactivate_plugins(basename(__FILE__));
			wp_die($error);
		}
	}
}
