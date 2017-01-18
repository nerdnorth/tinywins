<?php

/**
 * @since      1.0.0
 * @package    LogicHop
 * @subpackage LogicHop/includes
 */
class LogicHop_i18n {

	/**
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    Plugin name.
	 */
	protected $plugin_name;

	/**
	 * Initialize the plugin name.
	 *
	 * @since    1.0.0
	 */
	public function __construct ($plugin_name) {
		$this->plugin_name = $plugin_name;
	}
	
	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain () {
		load_plugin_textdomain(
			$this->plugin_name,
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/includes/languages/'
		);
	}
}
