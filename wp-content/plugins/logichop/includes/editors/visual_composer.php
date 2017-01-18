<?php

if (!defined('ABSPATH')) die;

/**
 * Visual Composer functionality.
 *
 * Provides Visual Composer functionality.
 *
 * @since      1.1.0
 * @package    LogicHop
 * @subpackage LogicHop/includes/editors
 */
	
class LogicHop_Visual_Composer {
	
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
		
		if (function_exists('vc_map')) {
			$this->register_parameters();
			$this->register_components();
		}
	}
	
	/**
	 * Register Visual Composer Parameters
	 *
	 * Reference: vc_map() – https://wpbakery.atlassian.net/wiki/display/VC/Create+New+Param+Type
	 *
	 * @since    	1.1.0
	 */
	public function register_parameters () {
		vc_add_shortcode_param('logichop_datalist', array($this, 'parameter_datalist_input'));
	}
	
	/**
	 * Visual Composer Datalist Input
	 *
	 * @since    	1.1.0
	 */
	public function parameter_datalist_input ($settings, $value) {		
		$options = '';
		if (isset($settings['value']) && is_array($settings['value'])) {
			foreach ($settings['value'] as $k => $v) {
				$options .= sprintf('<option value="%s">', $v);
			}
		}
		
		$datalist = sprintf('<input list="%s_list" name="%s" class="wpb_vc_param_value wpb-textinput %s %s_field" value="%s">
							<datalist id="%s_list">
								%s
							</datalist>',
							esc_attr($settings['param_name']),
							esc_attr($settings['param_name']),
							esc_attr($settings['param_name']),
							esc_attr($settings['type']),
							esc_attr($value),
							esc_attr($settings['param_name']),
							$options
						);
		return $datalist;
	}
	
	/**
	 * Register Visual Composer Components
	 *
	 * Reference: vc_map() – https://wpbakery.atlassian.net/wiki/pages/viewpage.action?pageId=524332#
	 *
	 * @since    	1.1.0
	 */
	public function register_components () {
		
		$conditions = $this->logic->conditions_get();
		$icon = sprintf('%simages/icon-visual-composer.png', plugin_dir_url(__FILE__));
		
		vc_map(array(
			'name' => __('Logic Hop Condition', 'logichop'), 
			'description' => __('Display when Condition is met.', 'logichop'), 
			'category' => 'Logic Hop',
			'base' => 'logichop_condition',
			'icon' => $icon,
			'content_element' => true,
			'is_container' => true,
			'js_view' => 'VcColumnView',
			'params' => array(
				array(
					'type' => 'dropdown',
					'heading' => __('Condition', 'logichop'),
					'description' => __('Content area is displayed when the Condition is met.', 'logichop'), 
					'param_name' => 'id',
					'value' => $conditions,
					'save_always' => true
				),
				array(
					'type' => 'textfield',
					'heading' => __('Condition', 'logichop'),
					'description' => __('Visual Composer editor label.', 'logichop'), 
					'param_name' => 'label',
					'admin_label' => true,
					'save_always' => true
				)
			)
		));
		
		vc_map(array(
			'name' => __('Logic Hop Condition Not', 'logichop'), 
			'description' => __('Display when Condition is NOT met.', 'logichop'), 
			'category' => 'Logic Hop',
			'base' => 'logichop_condition_not',
			'icon' => $icon,
			'content_element' => true,
			'is_container' => true,
			'js_view' => 'VcColumnView',
			'params' => array(
				array(
					'type' => 'dropdown',
					'heading' => __('Condition Not', 'logichop'),
					'description' => __('Content area is displayed when the Condition is NOT met.', 'logichop'), 
					'param_name' => 'id',
					'value' => $conditions,
					'save_always' => true,
				),
				array(
					'type' => 'textfield',
					'heading' => __('Condition Not', 'logichop'),
					'description' => __('Visual Composer editor label. ', 'logichop'), 
					'param_name' => 'label',
					'admin_label' => true,
					'save_always' => true
				)
			)
		));
		
		vc_map(array(
			'name' => __('Logic Hop Data', 'logichop'), 
			'description' => __('Display Logic Hop data.', 'logichop'), 
			'category' => 'Logic Hop',
			'base' => 'logichop_data',
			'content_element' => true,
			'is_container' => false,
			'icon' => $icon,
			'params' => array(
				array(
					'type' => 'logichop_datalist',
					'heading' => __('Variable', 'logichop'),
					'description' => __('Displays Logic Hop data if available.', 'logichop'), 
					'param_name' => 'var',
					'admin_label' => true,
					'save_always' => true,
					'value' => array (
						'' => '',
						'FirstVisit' => 'FirstVisit',
						'Location.City' => 'Location.City',
						'Location.RegionCode' => 'Location.RegionCode',
					)
				)
			)
		));
		
		if ($this->logic->convertkit->active()) {
			$options = $this->logic->convertkit->shortcode_variables_data(true); 
			vc_map(array(
				'name' => __('Logic Hop ConvertKit Data', 'logichop'), 
				'description' => __('Display ConvertKit data.', 'logichop'), 
				'category' => 'Logic Hop',
				'base' => 'logichop_data_ck',
				'content_element' => true,
				'is_container' => false,
				'icon' => $icon,
				'params' => array(
					array(
						'type' => 'dropdown',
						'heading' => __('Display', 'logichop'),
						'description' => __('Displays ConvertKit data if available.', 'logichop'), 
						'param_name' => 'var',
						'admin_label' => true,
						'value' => $options,
						'save_always' => true,
					)
				)
			));
		}
		
		if ($this->logic->drip->active()) {
			$options = $this->logic->drip->shortcode_variables_data(true); 
			vc_map(array(
				'name' => __('Logic Hop Drip Data', 'logichop'), 
				'description' => __('Display Drip data.', 'logichop'), 
				'category' => 'Logic Hop',
				'base' => 'logichop_data_drip',
				'content_element' => true,
				'is_container' => false,
				'icon' => $icon,
				'params' => array(
					array(
						'type' => 'dropdown',
						'heading' => __('Display', 'logichop'),
						'description' => __('Displays Drip data if available.', 'logichop'), 
						'param_name' => 'var',
						'admin_label' => true,
						'value' => $options,
						'save_always' => true,
					)
				)
			));
		}
	}
}

if (class_exists('WPBakeryShortCodesContainer')) {
	class WPBakeryShortCode_Logichop_Condition extends WPBakeryShortCodesContainer {}
	class WPBakeryShortCode_Logichop_Condition_Not extends WPBakeryShortCodesContainer {}
}

