<?php

if (!defined('ABSPATH')) die;

/**
 * Divi Builder functionality.
 *
 * Provides Divi Builder Modules
 *
 * @since      1.1.0
 * @package    LogicHop
 * @subpackage LogicHop/includes/editors
 */
	
class LogicHop_Divi_Builder {
	
	/**
	 * Core functionality & logic class
	 *
	 * @since    1.1.0
	 * @access   private
	 * @var      LogicHop_Core    $logic    Core functionality & logic.
	 */
	private $logic;
	
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    	1.1.0
	 * @param       object    $logic	LogicHop_Core functionality & logic.
	 */
	public function __construct( $logic ) {
		$this->logic = $logic;
		
		// https://www.elegantthemes.com/plugins/divi-builder/
		// https://jonathanbossenger.com/building-your-own-divi-builder-modules/
		// https://jonathanbossenger.com/divi-page-builder-cache/
		// https://divi.space/blog/adding-custom-modules-to-divi/
	}
}
