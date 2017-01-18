<?php

if (!defined('ABSPATH')) die;

/**
 * Google Analytics functionality.
 *
 * Provides Google Analytics functionality.
 *
 * @since      1.1.0
 * @package    LogicHop
 * @subpackage LogicHop/includes/services
 */
	
class LogicHop_Google_Analytics {
	
	/**
	 * Core functionality & logic class
	 *
	 * @since    1.1.0
	 * @access   private
	 * @var      LogicHop_Core    $logic    Core functionality & logic.
	 */
	private $logic;
	
	/**
	 * Google Analytics API URL
	 *
	 * @since    1.1.0
	 * @access   private
	 * @var      string    $google_ga_url    Google Analytics API URL
	 */
	private $google_ga_url;
	
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    	1.1.0
	 * @param       object    $logic	LogicHop_Core functionality & logic.
	 */
	public function __construct( $logic ) {
		$this->logic			= $logic;
		$this->google_ga_url 	= 'https://ssl.google-analytics.com/collect';
	}
	
	/**
	 * Check if Google Analytics has been set
	 *
	 * @since    	1.1.0
	 * @return      boolean     If google_ga_id is set
	 */
	public function active () {
		if ($this->logic->get_option('google_ga_id')) return true;
		return false;
	}
	
	/**
	 * Send Tracking Event to Google Analytics
	 *
	 * @since   	1.1.0
	 * @param		integer     $id         Post ID
	 * @return      object     				Tracking response
	 */
	public function track_event ($id) {
		if (!$this->active()) return false;
		$ec = $ea = false;
		$values	= get_post_custom($id);
		if (isset($values['logichop_goal_ga_cb'][0]) && $values['logichop_goal_ga_cb'][0]) {
			$ec = $values['logichop_goal_ga_ec'][0];
			$ea = $values['logichop_goal_ga_ea'][0];
			$el = ($values['logichop_goal_ga_el'][0] != '') ? $values['logichop_goal_ga_el'][0] : null;
			$ev = ($values['logichop_goal_ga_ev'][0] != '') ? (int) $values['logichop_goal_ga_ev'][0] : null;
		}
		
		if (!$ec || !$ea) return false;
		
		$ga_id 		= $this->logic->get_option('google_ga_id');
		$client_ip	= $this->logic->get_client_IP();
		
		$data = array (
					'v' 	=> 1, 											// Version
					'tid' 	=> urlencode($ga_id), 							// Tracking ID
					'cid' 	=> urlencode($_SESSION['logichop-data']->UID), 	// Anonymous Client ID
					't' 	=> urlencode('event'), 							// Event hit type
					'ec' 	=> urlencode($ec), 								// Event Category
					'ea' 	=> urlencode($ea), 								// Event Action
					'uip'	=> urlencode($client_ip) 						// User IP
				);
		if ($el) $data['el'] = urlencode($el); 								// Event Label - Optional
		if ($ev) $data['ev'] = urlencode($ev); 								// Event Value - Optional
		$post_args = array (
						'headers' => array (
							'User-Agent' => $_SERVER['HTTP_USER_AGENT']
							),
						'body' => $data
					);
		$response = wp_remote_post($this->google_ga_url, $post_args);
		return $response;
	}
}


