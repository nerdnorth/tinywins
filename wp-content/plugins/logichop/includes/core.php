<?php

if (!defined('ABSPATH')) die;

/**
 * Core functionality.
 *
 * Provides core functionality.
 *
 * @since      1.0.0
 * @package    LogicHop
 * @subpackage LogicHop/includes
 */
 
use JWadhams\JsonLogic as JsonLogic;
use Mobile_Detect as Mobile_Detect;
	
class LogicHop_Core {
	
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;
	
	/**
	 * Wordpress Option Settings
	 *
	 * @since    1.1.0
	 * @access   private
	 * @var      array    $options    Array of WP get_options('logichop-settings')
	 */
	private $options;
	
	/**
	 * Time modifier for cookie expiration.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $cookie_ttl    strtotime modifier.
	 */
	private $cookie_ttl;
	
	/**
	 * Cookie expiration setting.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $cookie_expires    Cookie expiration setting.
	 */
	private $cookie_expires;
	
	/**
	 * Cookie path.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $cookie_path    Cookie path.
	 */
	private $cookie_path = '/';
	
	/**
	 * Hash identifying user.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string    $hash    Hash identifying user.
	 */
	public $hash;
		
	/**
	 * API URL.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $api_url   	API URL.
	 */
	private $api_url;
	
	/**
	 * API KEY.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $api_key    API Key.
	 */
	private $api_key;
	
	/**
	 * Website domain name.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $domain    Website domain name.
	 */
	private $domain;
	
	/**
	 * Maximum number of pages in Path array.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      integer    $path_max    Maximum number of pages in Path array.
	 */
	private $path_max;
	
	/**
	 * Enable or Disable Javascript-based tracking
	 * Use when Wordpress content is cached
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      boolean    $js_tracking    
	 */
	private $js_tracking;
	
	/**
	 * Referrer to check during AJAX requests or Javascript-based tracking
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $ajax_referrer    
	 */
	private $ajax_referrer;
	
	/**
	 * Debug mode enabled
	 *
	 * @since    1.1.0
	 * @access   public
	 * @var      boolean    $debug    
	 */
	public $debug;
	
	/**
	 * Google Class
	 *
	 * @since    1.1.0
	 * @access   public
	 * @var      object    $google    LogicHop_Google_Analytics
	 */
	public $google;
	
	/**
	 * ConvertKit Class
	 *
	 * @since    1.1.0
	 * @access   public
	 * @var      object    $convertkit    LogicHop_ConvertKit
	 */
	public $convertkit;
	
	/**
	 * Drip Class
	 *
	 * @since    1.1.0
	 * @access   public
	 * @var      object    $drip    LogicHop_Drip
	 */
	public $drip;
	
	/**
	 * Provides core functionality.
	 *
	 * @since    	1.0.0
	 * @param      	string    $plugin_name   	The name of this plugin.
	 * @param      	string    $version    		The version of this plugin.
	 * @param      	string    $cookie_ttl    	Time modifier for cookie expiration.
	 * @param      	string    $path_max    		Maximum number of pages in path history
	 * @param      	string    $api_url    		The version of this plugin.
	 * @param      	string    $js_path    		Path to Conditional logic Javascript file
	 * @param      	boolean   $debug    		Enable debugging
	 */
	public function __construct ( $plugin_name, $version, $cookie_ttl, $path_max, $api_url, $js_path, $debug = false ) {
		$this->plugin_name	= $plugin_name;
		$this->version 		= $version;
		$this->cookie_ttl 	= $cookie_ttl;
		$this->path_max 	= $path_max;
		$this->api_url 		= $api_url;
		$this->js_path 		= $js_path;
		$this->debug 		= $debug;
		
		$this->google		= new LogicHop_Google_Analytics($this);
		$this->convertkit	= new LogicHop_ConvertKit($this);
		$this->drip			= new LogicHop_Drip($this);
	}
	
	/**
	 * Initialize core functionality.
	 * Update user data
	 *
	 * @since    1.0.0
	 */
	public function initialize_core () {
		$this->custom_post_types();
		
		if ( $this->options = get_option('logichop-settings') ) {
			$this->api_key 			= $this->get_option('api_key');
			$this->domain 			= $this->get_option('domain');
			$this->js_tracking  	= ($this->get_option('js_tracking') == 1) ? true : false;
			$this->ajax_referrer 	= $this->get_option('ajax_referrer');
			$this->cookie_ttl		= $this->get_option('cookie_ttl', $this->cookie_ttl);
		}
		
		if (!session_id()) session_start();
		$this->cookie_expires = strtotime($this->cookie_ttl);
		
		if (!isset($_SESSION['logichop'])) $this->validate_api(); // VALIDATE ONCE PER SESSION UNLESS UPDATED
		
		if (isset($_SESSION['logichop']) && $_SESSION['logichop']) {
			
			$bypass = false;
			
			if (isset($_GET['convertkit']) && isset($_GET['logichop']) && $this->convertkit->active()) {
				$this->hash = $_GET['logichop']; // LOGIC HOP HASH PASSED FROM CONVERTKIT LINK
				$this->session_create(); // CREATE THE SESSION :: NEW USER
				$data = $this->data_retrieve(); // LOAD USER DATA
				if ($_SESSION['logichop-data']->ConvertKitID != '') { // IF HASH/UID EXISTED --> CONTINUE
					$bypass = true;
					$this->cookie_create(); // CREATE THE COOKIE :: STORE HASH
				} else { // INVALID HASH/UID --> RESET
					$this->hash = null;
					$this->session_delete();
				}
			}
			
			if (isset($_GET['drip_email']) && isset($_GET['logichop']) && $this->drip->active()) {
				$this->hash = $_GET['logichop']; // LOGIC HOP HASH PASSED FROM DRIP LINK
				$this->session_create(); // CREATE THE SESSION :: NEW USER
				$data = $this->data_retrieve(); // LOAD USER DATA
				if ($_SESSION['logichop-data']->DripID != '') { // IF HASH/UID EXISTED --> CONTINUE
					$bypass = true;
					$this->cookie_create(); // CREATE THE COOKIE :: STORE HASH
				} else { // INVALID HASH/UID --> RESET
					$this->hash = null;
					$this->session_delete();
				}
			}
			
			if (!$bypass) {
				if (!isset($_COOKIE['logichop'])) { // NO COOKIE -> CREATE COOKIE & SESSION
					$this->hash = $this->generate_hash(); // GENERATE UID :: HASH
					$this->session_create(); // CREATE THE SESSION :: NEW USER
					$this->cookie_create(); // CREATE THE COOKIE :: STORE HASH
				}

				if (!isset($_SESSION['logichop-data'])) { // COOKIE EXISTS -> NO SESSION :: EXISTING USER
					$this->hash = $_COOKIE['logichop']; // LOAD UID :: HASH
					$this->session_create(); // CREATE THE SESSION :: EXISTING USER
					$this->data_retrieve(); // LOAD USER DATA
				}
			}
						
			$this->convertkit->data_check();
			$this->drip->data_check();
		}
	}
	
	/**
	 * Update core variable.
	 *
	 * @since    	1.0.0
	 * @param      	string    $prop       Property name.
	 * @param      	string    $value      Value to assign to property.
	 * @return     	$value    Value to assigned to property
	 */
	public function config_set ($prop, $value = null) {
    	$this->{$prop} = $value;
    	return $this->{$prop};
    }
	
	/**
	 * Validate API.
	 * Set logichop session boolean based on API validation status
	 *
	 * @since    1.0.0
	 */
	public function validate_api () {
		$valid = $this->api_post('validate');
		if (isset($valid['Client']) && $valid['Client']) {
			$_SESSION['logichop'] = true;
		} else {
			$_SESSION['logichop'] = false;
		}
	}
	
	/**
	 * Update Client Meta Data.
	 * Store client meta data for QA - No API Keys or Account informaiton.
	 *
	 * @since    1.1.0
	 */
	public function update_client_meta () {
		if ( $tmp_options = get_option('logichop-settings') ) {
			$data = array (
					'cookie_ttl'	=> $tmp_options['cookie_ttl'],
					'js_tracking'	=> (isset($tmp_options['js_tracking'])) ? 'Y' : 'N',
					'ga_enabled'	=> ($tmp_options['google_ga_id']) 		? 'Y' : 'N',
					'ck_enabled'	=> ($tmp_options['convertkit_key']) 	? 'Y' : 'N',
					'drip_enabled'	=> ($tmp_options['drip_api_token']) 	? 'Y' : 'N'
				);
				
			$meta_log = $this->api_post('meta-log', $data);
		}
	}
		
	/**
	 * Register Custom Post Types.
	 *
	 * @since    1.0.0
	 */
	public function custom_post_types () {	
		register_post_type(			
			$this->plugin_name . '-conditions', 
				array(				
					'label' => __('Conditions', 'logichop'),
					'labels' => array (
						'name' => __('Logic Hop Conditions', 'logichop'),
						'all_items' => __('Conditions', 'logichop'), // All Conditions
						'add_new_item' => __('Add New Condition', 'logichop'),
						'edit_item' => __('Edit Condition', 'logichop'),
						'not_found' => __('No conditions found', 'logichop'),
						'not_found_in_trash' => __('No conditions found', 'logichop'),
						'search_items' =>  __('Search Conditons', 'logichop')
						),
					'menu_position' => 20,
					'public' => false, 
					'show_ui' => true,
					'show_in_menu' => $this->plugin_name . '-menu',
					'exclude_from_search' => true,
					'hierarchical' => true,
					'capability_type' => 'post',
					'supports' => array(					
						'title',
						'excerpt'
					)
				)				
		);
		register_post_type(			
			$this->plugin_name . '-goals', 
				array(				
					'label' => __('Goals', 'logichop'),
					'labels' => array (
						'name' => __('Logic Hop Goals', 'logichop'),
						'all_items' => __('Goals', 'logichop'), // All Goals
						'add_new_item' => __('Add New Goal', 'logichop'),
						'edit_item' => __('Edit Goal', 'logichop'),
						'not_found' => __('No goals found', 'logichop'),
						'not_found_in_trash' => __('No goals found', 'logichop'),
						'search_items' =>  __('Search Goals', 'logichop')
						),
					'menu_position' => 20,
					'public' => false, 
					'show_ui' => true,
					'show_in_menu' => $this->plugin_name . '-menu',
					'exclude_from_search' => true,
					'hierarchical' => true,
					'capability_type' => 'post',
					'supports' => array(					
						'title',
						'excerpt'
					)
				)				
		);
	}
	
	/**
	 * Create session & data model.
	 *
	 * @since    1.0.0
	 */
	public function session_create () { 
		$_SESSION['logichop-data'] 				= new stdclass();
		$_SESSION['logichop-data']->UID 		= $this->hash;
		$_SESSION['logichop-data']->FirstVisit 	= true;
		
		$timestamp = new stdclass();
		$timestamp->FirstVisit 	= ''; // TIMESTAMP FIRST VISIT
		$timestamp->LastVisit 	= ''; // TIMESTAMP LAST SESSION
		$timestamp->ThisVisit 	= ''; // TIMESTAMP LAST SESSION
		$timestamp->LastPage 	= ''; // TIMESTAMP LAST PAGE
		$_SESSION['logichop-data']->Timestamp	= $timestamp;
		
		$mobile_detect = new Mobile_Detect();
		$_SESSION['logichop-data']->Mobile	= $mobile_detect->isMobile();
		$_SESSION['logichop-data']->Tablet	= $mobile_detect->isTablet();
		
		$_SESSION['logichop-data']->IP 			= $this->get_client_IP();
		$_SESSION['logichop-data']->Location 	= null;
		$_SESSION['logichop-data']->LandingPage = '';
		$_SESSION['logichop-data']->Source 		= '';
		$_SESSION['logichop-data']->LoggedIn	= is_user_logged_in();
		$_SESSION['logichop-data']->Page 		= 0;
		$_SESSION['logichop-data']->Views 		= 0;
		$_SESSION['logichop-data']->Pages 		= null;
		$_SESSION['logichop-data']->Goals 		= array();
		$_SESSION['logichop-data']->PagesSession	= null;
		$_SESSION['logichop-data']->GoalsSession	= array();
		$_SESSION['logichop-data']->ViewsSession 	= 0;
		$_SESSION['logichop-data']->Path 		= array();
		$_SESSION['logichop-data']->QueryStore 	= array();
		$_SESSION['logichop-data']->Query 		= array();
		$_SESSION['logichop-data']->Referrer	= '';
		$_SESSION['logichop-data']->Date		= null;
		$_SESSION['logichop-data']->Token 		= $this->generate_hash('token');
		
		$_SESSION['logichop-data']->ConvertKitID		= '';
		$_SESSION['logichop-data']->ConvertKit			= new stdclass();
		$_SESSION['logichop-data']->ConvertKit->tags	= array ();
		$_SESSION['logichop-data']->DripID				= '';
		$_SESSION['logichop-data']->Drip				= new stdclass();
		$_SESSION['logichop-data']->Drip->tags			= array ();		
	}
	
	/**
	 * Delete session & data model.
	 *
	 * @since    1.0.0
	 */
	public function session_delete () { 
		if (isset($_SESSION['logichop-data'])) unset($_SESSION['logichop-data']);
	}
	
	/**
	 * Get user data from session.
	 *
	 * @since    	1.0.0
	 * @return      object    User data from $_SESSION['logichop-data'].
	 */
	public function session_get () {
		return isset($_SESSION['logichop-data']) ? $_SESSION['logichop-data'] : null;
	}
	
	/**
	 * Get single variable from session.
	 *
	 * @since    	1.0.0
	 * @param      	string    	$var	Variable name
	 * @return      variable    Single variable from $_SESSION['logichop-data'].
	 */
	public function session_get_var ($var) {
		return isset($_SESSION['logichop-data']->{$var}) ? $_SESSION['logichop-data']->{$var} : null;
	}
	
	/**
	 * Sets logichop cookie
	 *
	 * @since    1.0.0
	 */
	public function cookie_create () {
		setcookie('logichop', $this->hash, $this->cookie_expires, $this->cookie_path);
	}
	
	/**
	 * Retrieve remote user data from 'events' API
	 *
	 * @since    	1.0.0
	 * @return		JSON object		User data from api_post response
	 */
	public function data_retrieve () {
		$args = array (
						'uid' => $_SESSION['logichop-data']->UID
					);
		$data = $this->api_post('events', $args);
		
		if ($data) {
			if (isset($data['Source'])) 		$_SESSION['logichop-data']->Source 			= $data['Source'];
			if (isset($data['LandingPage'])) 	$_SESSION['logichop-data']->LandingPage 	= $data['LandingPage'];
			if (isset($data['Pages'])) 			$_SESSION['logichop-data']->Pages 			= (array) $data['Pages'];
			if (isset($data['Goals'])) 			$_SESSION['logichop-data']->Goals 			= (array) $data['Goals'];
			if (isset($data['ConvertKit'])) 	$_SESSION['logichop-data']->ConvertKitID 	= $data['ConvertKit'];
			if (isset($data['Drip'])) 			$_SESSION['logichop-data']->DripID			= $data['Drip'];
			
			if (isset($data['FirstDate'])) 		$_SESSION['logichop-data']->Timestamp->FirstVisit 	= $data['FirstDate'];
			if (isset($data['LastDate'])) 		$_SESSION['logichop-data']->Timestamp->LastVisit	= $data['LastDate'];
			
			$_SESSION['logichop-data']->FirstVisit = false;
		}
		
		return $data;
	}
		
	/**
	 * Update user data on page load.
	 * Does not update for admin section.
	 *
	 *
	 * @since    1.0.0
	 */
	public function update_data ($pid = false) { 
		
		if (!$pid) { // $pid SET = EXPLICIT CALL VIA AJAX --> REQUEST METHOD CAN VARY - NOT CALLED FROM WP-ADMIN
			if (is_admin() || $_SERVER['REQUEST_METHOD'] == 'POST') {
				return;  // ONLY CHECK FOR ADMIN & POST ON STANDARD SUBMISSION
			}
		}
		
		if (!isset($_SESSION['logichop']) || !$_SESSION['logichop']) return false;
		
		$post_id 	= ($pid) ? $pid : $this->wordpress_post_get();
		$track_page = get_post_meta($post_id, '_logichop_track_page', true); // CHECK FOR 'PAGE TRACK' VALUE
		
		$_SESSION['logichop-data']->Page 		= $post_id;					// CURRENT PAGE
		
		if (isset($_SESSION['logichop-data']->Date->Timestamp)) $_SESSION['logichop-data']->Timestamp->LastPage = $_SESSION['logichop-data']->Date->Timestamp;
		$_SESSION['logichop-data']->Date = $this->date_object();		
		if ($_SESSION['logichop-data']->Timestamp->ThisVisit == '') $_SESSION['logichop-data']->Timestamp->ThisVisit = $_SESSION['logichop-data']->Date->Timestamp;
		if ($_SESSION['logichop-data']->Timestamp->LastPage == '') $_SESSION['logichop-data']->Timestamp->LastPage = $_SESSION['logichop-data']->Date->Timestamp;
		
		$_SESSION['logichop-data']->Query 		= $_GET;	// QUERY STRING
		if (is_array($_GET) && is_array($_SESSION['logichop-data']->QueryStore)) {
			$_SESSION['logichop-data']->QueryStore	= array_merge($_SESSION['logichop-data']->QueryStore, $_GET);	// STORE QUERY STRING VARS -- DUPLICATES ARE OVERWRITTEN
		}
		
		$_SESSION['logichop-data']->LoggedIn	= is_user_logged_in();		// USER STATE
		
		$this->get_referrer_query_string();
		
		array_unshift($_SESSION['logichop-data']->Path, $post_id); // TRACK VIEW PATH
		if (count($_SESSION['logichop-data']->Path) > $this->path_max) {
			$_SESSION['logichop-data']->Path = array_slice($_SESSION['logichop-data']->Path, 0, $this->path_max); // LIMITS VIEW PATH
		}
		
		if (isset($_SERVER['HTTP_REFERER'])) { // CHECK REFERRER
			$_SESSION['logichop-data']->Referrer = $_SERVER['HTTP_REFERER'];			
		}
		
		if (!$_SESSION['logichop-data']->Location) {
			$geo = new LogicHop_Geo_IP($this);
			$_SESSION['logichop-data']->Location = $geo->geolocate($_SESSION['logichop-data']->IP);
		}
		
		if (isset($_SESSION['logichop-data']->FirstVisit) && $_SESSION['logichop-data']->FirstVisit) {
			if ($_SESSION['logichop-data']->Source == '') {				
				$_SESSION['logichop-data']->Timestamp->FirstVisit 	= $_SESSION['logichop-data']->Date->Timestamp;
				$_SESSION['logichop-data']->Timestamp->LastVisit	= $_SESSION['logichop-data']->Date->Timestamp;
		
				$_SESSION['logichop-data']->Source = ($_SESSION['logichop-data']->Referrer != '') ? $_SESSION['logichop-data']->Referrer : 'direct';
				$this->data_remote_put('source', $_SESSION['logichop-data']->Source); // STORE FIRST VISIT
				
				$_SESSION['logichop-data']->LandingPage = strtok($_SERVER['REQUEST_URI'], '?');
				$this->data_remote_put('landing_page', $_SESSION['logichop-data']->LandingPage); // STORE LANDING PAGE
			}				
		}
		
		$value = (isset($_SESSION['logichop-data']->Pages[$post_id])) ? $_SESSION['logichop-data']->Pages[$post_id] : 0;
		$_SESSION['logichop-data']->Pages[$post_id] = $value + 1;	// TRACK PAGE VIEW
		
		$value = (isset($_SESSION['logichop-data']->PagesSession[$post_id])) ? $_SESSION['logichop-data']->PagesSession[$post_id] : 0;
		$_SESSION['logichop-data']->PagesSession[$post_id] = $value + 1;	// TRACK PAGE VIEW --> CURRENT SESSION ONLY
		
		$_SESSION['logichop-data']->Views = $_SESSION['logichop-data']->Pages[$post_id];	// UPDATE PAGE VIEWS
		$_SESSION['logichop-data']->ViewsSession = $_SESSION['logichop-data']->PagesSession[$post_id];	// UPDATE PAGE VIEWS --> CURRENT SESSION ONLY
		
		if ($track_page) $this->data_remote_put('page', $post_id); // STORE PAGE VIEW
		
		if ($this->get_option('session_debug', false) && isset($_GET['session'])) {
			if ($_GET['session'] == 'display') {
				$this->d($_SESSION['logichop-data']);
			} else {
				$this->d($_GET['session'] . ': ' . $this->data_return($_GET['session']));
			}
		}
	}
	
	/**
	 * Logic Hop return data
	 *
	 * Data extracted from $_SESSION['logichop-data']
	 * Accepts [logichop_data vars=""]
	 * Parameter 'vars' accepts '.' delimited object elements and ':' delimited array elements
	 * Example: Date.DateTime OR QueryStore:ref 
	 *
	 * @since    	1.0.9
	 * @param  		string	$var	Data path
	 * @return  	string			Data as a string
	 */
	public function data_return ($var) {
		$vars = explode('.', $var);
		$object = $_SESSION['logichop-data'];
		foreach ($vars as $key => $element) {
			$array_check = explode(':', $element);
			if (!isset($array_check[1])) {
				if (isset($object->$element)) {
					$object = $object->$element;
				} else {
					return;
				}
			} else {
				if (isset($object->{$array_check[0]}[$array_check[1]])) {
					$object = $object->{$array_check[0]}[$array_check[1]];
				} else {
					return;
				}
			}
		}
		if (isset($object)) return $object;
		return '';
	}
	
	/**
	 * Update query string from HTTP Referrer when JS Tracking is enabled
	 * Referrer should always be internal via WP/AJAX
	 *
	 *
	 * @since    1.0.5
	 */
	public function get_referrer_query_string () { 
		if ($this->js_tracking) {
			if (isset($_SERVER['HTTP_REFERER'])) {
				$get_vars = array();
				$referrer = parse_url($_SERVER['HTTP_REFERER']);
				if (isset($referrer['query'])) parse_str($referrer['query'], $get_vars);
				$_SESSION['logichop-data']->Query 		= $get_vars;
				$_SESSION['logichop-data']->QueryStore	= array_merge($_SESSION['logichop-data']->QueryStore, $get_vars);
			}
		}
	}
	
	/**
	 * Update Goal.
	 *
	 * @since    	1.0.0
	 * @param      	integer    Goal ID
	 * @return      boolean    Goal stored state.
	 */
	public function update_goal ($goal_id = null) {
		
		if (!isset($_SESSION['logichop']) || !$_SESSION['logichop']) return false;
		
		if (is_int($goal_id)) {
			$goal = get_post($goal_id); 
			
			if (isset($goal) && $goal->post_type == 'logichop-goals') {
				$this->check_track_event($goal_id);
			
				$value = (isset($_SESSION['logichop-data']->Goals[$goal_id])) ? $_SESSION['logichop-data']->Goals[$goal_id] : 0;
				$_SESSION['logichop-data']->Goals[$goal_id] = $value + 1;	// TRACK GOALS
				
				$value = (isset($_SESSION['logichop-data']->GoalsSession[$goal_id])) ? $_SESSION['logichop-data']->GoalsSession[$goal_id] : 0;
				$_SESSION['logichop-data']->GoalsSession[$goal_id] = $value + 1;	// TRACK GOALS --> CURRENT SESSION ONLY
				
				return $this->data_remote_put('goal', $goal_id);
			}
		}
		return false;
	}
		
	/**
	 * Get Wordpress Post ID or Post Data.
	 *
	 * @since    	1.0.0
	 * @param      	integer   	$pid 			Post ID.
	 * @param      	boolean   	$return_post 	Switch to determin return parameter
	 * @return      string    		Post ID - Defailt.
	 * @return      Post Object    	Wordpress Post Object - If $return_post is true.
	 */
	public function wordpress_post_get ($pid = false, $return_post = false) {
		$post_id = ($pid) ? $pid : url_to_postid(sprintf('http://%s%s', $_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI']));
		
		if ($post_id == 0) {
			if (is_404()) $post_id = -1;
			if (is_home() && !is_front_page()) $post_id = (int) get_option('page_for_posts');
		}
		
		if (!$return_post) return $post_id;
		
		return get_post($post_id);
	}
	
	/**
	 * Get Condition result
	 *
	 * @since   	1.0.0
	 * @param      	mixed		$condition_id   	Integer for Condition ID – String for default conditions.
	 * @return     	boolean    	Condition result
	 */
	public function condition_get ($condition_id = false) {
		
		if (!$this->session_get()) return false;
		
		$rule = false;
		
		if (is_numeric($condition_id)) {
			$condition_id = (int) $condition_id;
			$condition = get_post($condition_id);
			if ($condition) {
				$rule = json_decode($condition->post_excerpt, true);
			}
		} else {
			$condition = $this->condition_default_get(true, $condition_id);
			if ($condition) {
				$rule = json_decode($condition);
			}
		}
			
		if ($rule) {
			$result = $this->logic_apply($rule, $this->session_get());
			if ($result) return true;
		}
		return false;
	}
	
	/**
	 * Get Default Condition result
	 *
	 * @since   	1.1.0
	 * @param      	boolean		$single   			Return single condition rule or array of all conditions
	 * @param      	string		$condition_id   	Default condition key
	 * @return     	mixed    	Array of all condtions or single condition rule
	 */
	public function condition_default_get ($single = false, $condition_id = '') {
	
		$conditions = array (
			'first_visit' 	=> array (
								'title' => "User's First Visit",
								'rule'	=> '{"==": [ {"var": "FirstVisit" }, true ] }',
								'info'	=> "This is the user's first visit to the site."
							),
			'repeat_visit' 	=> array (
								'title' => "User Repeat Visit",
								'rule'	=> '{"==": [ {"var": "FirstVisit" }, false ] }',
								'info'	=> "This is not the user's first visit to the site."
							),
			'pages_gt_one' 	=> array (
								'title' => "User Has Viewed More Than 1 Page",
								'rule'	=> '{">": [ {"add_array": {"var": "Pages"}}, 1 ] }',
								'info'	=> "The user has viewed more than one page."
							),							
			'direct_visit' 	=> array (
								'title' => "User Visiting Site Directly - No Referrer",
								'rule'	=> '{"==": [{"var": "Source" }, "direct"]}',
								'info'	=> "The user visited the site directly."
							),
			'is_desktop' 	=> array (
								'title' => "User on a Desktop or Laptop Computer",
								'rule'	=> '{"==": [ {"var": "Mobile" }, false ] }',
								'info'	=> "The user is on a desktop or laptop."
							),
			'is_mobile' 	=> array (
								'title' => "User on a Mobile Device",
								'rule'	=> '{"==": [ {"var": "Mobile" }, true ] }',
								'info'	=> "The user is on a mobile device."
							),
			'is_tablet' 	=> array (
								'title' => "User on a Tablet",
								'rule'	=> '{"==": [ {"var": "Tablet" }, true ] }',
								'info'	=> "The user is on a tablet."
							),
			'logged_in' 	=> array (
								'title' => "User is Logged In",
								'rule'	=> '{"==": [ {"var": "LoggedIn" }, true ] }',
								'info'	=> "The user is logged in to WordPress."
							)
			);
		
		if ($this->convertkit->active()) {
			$conditions['convertkit'] = array (
					'title' => "ConvertKit Data Is Available for User",
					'rule'	=> '{"==": [ {"var": "ConvertKit.email_address" }, true ] }',
					'info'	=> "Is ConvertKit data available for the current user."
				);
		}
		
		if ($this->drip->active()) {
			$conditions['drip'] = array (
					'title' => "Drip Data Is Available for User",
					'rule'	=> '{"==": [ {"var": "Drip.email" }, true ] }',
					'info'	=> "Is Drip data available for the current user."
				);
		}
			
		if ($single) {
			if (array_key_exists($condition_id, $conditions)) {
				return $conditions[$condition_id]['rule'];
			} 
			return false;
		}
		return $conditions;
	}
	
	/**
	 * Apply conditional logic
	 *
	 * @since    1.0.0
	 * @param      object     $rule       Condition rule
	 * @param      object     $data       Condition data
	 * @return     boolean    Conditional logic result
	 */
	public function logic_apply ($rule, $data = false) {
		$jsonlogic = new JsonLogic();
		if ($data) return $jsonlogic->apply($rule, $data);
		return $jsonlogic->apply($rule);
	}
	
	/**
	 * Check if Javascript Tracking has been enabled
	 *
	 * @since    	1.0.0
	 * @return      boolean     If $this->js_tracking is set
	 */
	public function js_tracking () {
		if ($this->js_tracking) return true;
		return false;
	}
	
	/**
	 * Check if event should be tracked by external APIs
	 *
	 * @since    	1.0.0
	 * @param		integer     $id       Wordpress Post ID
	 */
	public function check_track_event ($id) {
		$this->google->track_event($id);
		$this->convertkit->track_event($id);
		$this->drip->track_event($id);
	}
	
	/**
	 * Create date object
	 *
	 * @since    	1.0.0
	 * @return      Object    Custom date object.
	 */
	public function date_object ($timestamp = null) {
		$d = new DateTime();
		$date = new stdclass();
		
		if ($timestamp) { // SET TIME FROM SPECIFIC TIMESTAMP
			$d->setTimestamp($timestamp);
		} else { // SET TIME FROM WP TIMESTAMP
			$d->setTimestamp(strtotime(current_time('mysql')));
		}
		
		$date->Timestamp	= $d->getTimestamp();
		$date->DateTime		= $d->format('Y-m-d H:i:s');
		$date->Date			= $d->format('m-d-Y');
		$date->Year 		= $d->format('Y');
		$date->LeapYear 	= ($d->format('L')) ? true : false;
		$date->Month 		= $d->format('n');
		$date->MonthName 	= $d->format('F');
		$date->Day			= $d->format('j');
		$date->DayName		= $d->format('l');
		$date->DayNumber	= $d->format('N');
		$date->DayYear		= $d->format('z');
		$date->Week			= $d->format('W');
		$date->Hour 		= $d->format('g');
		$date->Hour24 		= $d->format('H');
		$date->Minutes 		= $d->format('i');
		$date->Seconds 		= $d->format('s');
		$date->AM			= ($d->format('a') == 'am') ? true : false;
		$date->PM 			= ($d->format('a') == 'pm') ? true : false;
		return $date;
	}
	
	/**
	 * Store user data through API.
	 *
	 * @since    	1.0.0
	 * @return 		json object    API response.
	 */
	public function data_remote_put ($type, $value) {
		$data = array (
					'uid'		=> $_SESSION['logichop-data']->UID,
					'type'		=> $type,
					'value'		=> $value,
					'ip'		=> $this->get_client_IP()
				);
		return $this->api_post('event', $data);
	}
	
	/**
	 * Post data to API.
	 *
	 * @since		1.0.0
	 * @param		string		$endpoint		API endpoint
	 * @param		array		$data			Post data
	 * @param		string		$key			Override API Key
	 * @return      JSON object    API response.
	 */
	public function api_post ($endpoint, $data = array(), $key = false) {
		$data_default = array (
					'domain' 	=> $this->domain,
					'version' 	=> $this->version,
					'wp_domain' => isset($_SERVER['SERVER_NAME']) ? strtolower($_SERVER['SERVER_NAME']) : ''
				);
		$data_source = array_merge($data_default, $data);
				
		$url = sprintf('%s/%s.php', $this->api_url, $endpoint);
		
		$post_args = array (
						'headers' => array (
							'LOGIC-HOP-API-KEY' => ($key) ? $key : $this->api_key
							),
						'body' => $data_source
					);
					
		$response = wp_remote_post($url, $post_args);
		
		if (!is_wp_error($response)) {
			if (isset($response['body'])) return json_decode($response['body'], true);
		} else {
			$error['Client']['Message'] = sprintf('<h4 style="color: #f00;">ERROR: %s</h4>', $response->get_error_message());
			return $error;
		}
		return false;
	}
	
	/**
	 * Get Javascript events as options for select input
	 *
	 * @since    1.0.0
	 * @param		string		$id		Selected option value
	 * @return      string    	Javascript event options
	 */
	public function javascript_get_events ($event = false) {		
		$events = array (
						'click' 		=> __('On Click', 'logichop'),
						'dblclick' 		=> __('On Double Click', 'logichop'),
						'focus' 		=> __('On Focus', 'logichop'),
						'blur' 			=> __('On Blur', 'logichop'),
						'scroll' 		=> __('On Scroll', 'logichop'),
						'mousedown' 	=> __('On Mouse Down', 'logichop'),
						'mouseup' 		=> __('On Mouse Up', 'logichop'),
						'mouseover' 	=> __('On Mouse Over', 'logichop'),
						'mouseout' 		=> __('On Mouse Out', 'logichop'),
						'mouseenter' 	=> __('On Mouse Enter', 'logichop'),
						'mouseleave' 	=> __('On Mouse Leave', 'logichop'),
						'change' 		=> __('On Change', 'logichop'),
						'select' 		=> __('On Select', 'logichop'),
						'submit' 		=> __('On Submit', 'logichop'),
						'keydown' 		=> __('On Key Down', 'logichop'),
						'keypress' 		=> __('On Key Press', 'logichop'),
						'keyup' 		=> __('On Key Up', 'logichop'),
						'load' 			=> __('On Load', 'logichop'),
						'unload' 		=> __('On Unload', 'logichop'),
						'beforeunload' 	=> __('On Before Unload', 'logichop')
					);
					
		$options = '';
		
		foreach ($events as $value => $name) {
					$options .= sprintf('<option value="%s" %s>%s</option>', 
											$value,
											($event == $value) ? 'selected' : '',
											$name
										);
		}
		return $options;
	}
	
	/**
	 * Get Conditions
	 *
	 * @since    1.1.0
	 * @return      array    	Array of Conditions
	 */
	public function conditions_get () {		
		$conditions = array();
		
		$query = new WP_Query(array(
						'post_type' => $this->plugin_name . '-conditions',
						'post_status' => 'publish',
						'posts_per_page' => -1
					));
		
		if ($query) {
			foreach ($query->posts as $p) {
				$conditions[] = array (
										'id' => $p->ID,
										'name' => $p->post_title
									);
			}
		}
		
		$defaults = $this->condition_default_get();
		if ($defaults) {
			foreach ($defaults as $key => $c) {
				$conditions[] = array (
										'id' => $key,
										'name' => $c['title']
									);
			}
		}
		
		return $conditions;
	}
	
	/**
	 * Get Condition as options for select input
	 *
	 * @since    1.0.0
	 * @param		string		$id		Selected option value
	 * @return      string    	Condtion options
	 */
	public function conditions_get_options ($id = false) {		
		$options = '';
		
		$query = new WP_Query(array(
						'post_type' => $this->plugin_name . '-conditions',
						'post_status' => 'publish',
						'posts_per_page' => -1
					));
		
		if ($query) {
			foreach ($query->posts as $p) {
				$options .= sprintf('<option value="%s" data-slug="%s" %s>%s</option>', 
									$p->ID,
									$p->post_name,
									($p->ID == $id) ? 'selected' : '',
									$p->post_title
								);
			}
		}
		
		$conditions = $this->condition_default_get();
		if ($conditions) {
			$options .= '<option value="" data-slug=""> >> Default Conditions</option>';
			foreach ($conditions as $key => $c) {
				$options .= sprintf('<option value="%s" data-slug="%s" %s> > %s</option>', 
									$key,
									$key,
									($key == $id) ? 'selected' : '',
									$c['title']
								);
			}
		}
		
		return $options;
	}
	
	/**
	 * Get Goals as options for select input
	 *
	 * @since    	1.0.0
	 * @param		string		$id		Selected option value
	 * @return      string		Goal options
	 */
	public function goals_get_options ($id = false) {		
		$options = '';
		$query = new WP_Query(array(
						'post_type' => $this->plugin_name . '-goals',
						'post_status' => 'publish',
						'posts_per_page' => -1
					));
		
		if ($query) {
			foreach ($query->posts as $p) {
				$options .= sprintf('<option value="%s" data-slug="%s" %s>%s</option>', 
									$p->ID,
									$p->post_name,
									($p->ID == $id) ? 'selected' : '',
									$p->post_title
								);
			}
		}
		return $options;
	}
	
	/**
	 * Get Goals as JSON object
	 *
	 * @since    	1.0.0
	 * @return      json object    JSON encoded goals
	 */
	public function goals_get_json () {		
		$goals = new stdclass;
		
		$query = new WP_Query(array(
						'post_type' => $this->plugin_name . '-goals',
						'post_status' => 'publish',
						'posts_per_page' => -1
					));
		
		if ($query)
			foreach ($query->posts as $p)
				$goals->{$p->ID} = $p->post_title;
				
		return json_encode($goals);
	}
	
	/**
	 * Get Wordpress Pages & Posts as JSON object
	 *
	 * @since    	1.0.0
	 * @return      json object    JSON encoded pages & posts
	 */
	public function pages_get_json () {		
		$pages = new stdclass;
		
		$query = new WP_Query(array(
						'post_type' => 'page',
						'post_status' => 'publish',
						'posts_per_page' => -1,
						'order' => 'ASC',
						'orderby' > 'ID',
						'meta_query' => array(
							array(
								'key' => '_logichop_track_page',
								'value' => true,
							   	'compare' => '	='
							)
						)
					));
		
		if ($query)
			foreach ($query->posts as $p)
				$pages->{$p->ID} = $p->post_title;
		
		$query = new WP_Query(array(
						'post_type' => 'post',
						'post_status' => 'publish',
						'posts_per_page' => -1,
						'order' => 'ASC',
						'orderby' > 'ID',
						'meta_query' => array(
							array(
								'key' => '_logichop_track_page',
								'value' => true,
							   	'compare' => '='
							)
						)
					));
		
		if ($query)
			foreach ($query->posts as $p)
				$pages->{$p->ID} = $p->post_title;
						
		return json_encode($pages);
	}
	
	/**
	 * Get Gravatar URL
	 *
	 * @since    	1.1.0
	 * @param      	string    $email	Email address
	 * @param      	integer   $s		Image size
	 * @param      	string    $d		Default imageset [ 404 | mm | identicon | monsterid | wavatar ]
	 * @param      	string    $r		Maximum rating (inclusive) [ g | pg | r | x ]
	 * @param      	boolean   $img		True to return a complete IMG tag False for just the URL
	 * @param      	array     $atts		Optional, additional key/value attributes to include in the IMG tag
	 * @return      string    Gravatar URL or a complete image tag
	 */
	public function gravatar_get_url ($email, $s = 80, $img = false, $d = 'mm', $r = 'pg', $atts = array()) {
		$url = 'https://www.gravatar.com/avatar/';
    	$url .= md5(strtolower(trim($email)));
   		$url .= "?s=$s&d=$d&r=$r";
   		$gravatar = sprintf(' gravatar gravatar-%d', $s);
   		$atts['class'] = isset($atts['class']) ? $atts['class'] . $gravatar : $gravatar;
    	if ($img) {
        	$url = '<img src="' . $url . '"';
			foreach ($atts as $k => $v) $url .= ' ' . $k . '="' . $v . '"';
        	$url .= ' />';
    	}
    	return $url;
	}
	
	/**
	 * Build Gravatar Object
	 *
	 * @since    	1.1.0
	 * @param      	string    $name		Name of object to add Gravatar data to
	 * @param      	string    $email	Email Address
	 */
	public function gravatar_object ($object_name, $email) {
		$_SESSION['logichop-data']->{$object_name}->gravatar = new stdclass();
		$_SESSION['logichop-data']->{$object_name}->gravatar->url = new stdclass();
		$_SESSION['logichop-data']->{$object_name}->gravatar->img = new stdclass();
		$_SESSION['logichop-data']->{$object_name}->gravatar->url->fullsize 	= $this->gravatar_get_url($email, 2048);
		$_SESSION['logichop-data']->{$object_name}->gravatar->url->large 		= $this->gravatar_get_url($email, 1024);
		$_SESSION['logichop-data']->{$object_name}->gravatar->url->medium 		= $this->gravatar_get_url($email, 512);
		$_SESSION['logichop-data']->{$object_name}->gravatar->url->small 		= $this->gravatar_get_url($email, 256);
		$_SESSION['logichop-data']->{$object_name}->gravatar->url->thumb 		= $this->gravatar_get_url($email, 100);
		$_SESSION['logichop-data']->{$object_name}->gravatar->img->fullsize 	= $this->gravatar_get_url($email, 2048, true);
		$_SESSION['logichop-data']->{$object_name}->gravatar->img->large 		= $this->gravatar_get_url($email, 1024, true);
		$_SESSION['logichop-data']->{$object_name}->gravatar->img->medium 		= $this->gravatar_get_url($email, 512, true);
		$_SESSION['logichop-data']->{$object_name}->gravatar->img->small 		= $this->gravatar_get_url($email, 256, true);
		$_SESSION['logichop-data']->{$object_name}->gravatar->img->thumb 		= $this->gravatar_get_url($email, 100, true);
	}
	
	/**
	 * Returns host from current Referrer
	 * Example: domain.com
	 *
	 * @since    	1.0.0
	 * @return      string    Domain name
	 */
	public function get_referrer_host () {
		$referrer = isset($_SERVER['HTTP_REFERER']) ? parse_url($_SERVER['HTTP_REFERER']) : '';
		return isset($referrer['host']) ? $referrer['host'] : '';
	}
	
	/**
	 * Determines if the current referrer is valid based on the value of $ajax_referrer
	 * Always true if $ajax_referrer is NOT set
	 *
	 * @since    	1.0.0
	 * @return      boolean    If current referrer matches $ajax_referrer
	 */
	public function is_valid_referrer () {
		if ($this->ajax_referrer) {
			if ($this->ajax_referrer == $this->get_referrer_host()) {
				return true;
			}
			return false;
		}
		return true;
	}
	
	/**
	 * Generates hash
	 *
	 * @since    	1.0.0
	 * @param      	string    $salt			Optional salt for md5 hash 
	 * @return      string    md5 hash.
	 */
	public function generate_hash ($salt = '') {
		return md5($this->get_client_IP() . time() . $salt);
	}
	
	/**
	 * Detect client IP address
	 *
	 * @since		1.0.6
	 * @return     	string		IP Address
	 */
	public function get_client_IP () {
		$client_ip = '';
		if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
           $client_ip = $_SERVER["HTTP_X_FORWARDED_FOR"];  
        } else if (array_key_exists('REMOTE_ADDR', $_SERVER)) { 
            $client_ip = $_SERVER["REMOTE_ADDR"]; 
        } else if (array_key_exists('HTTP_CLIENT_IP', $_SERVER)) {
            $client_ip = $_SERVER["HTTP_CLIENT_IP"]; 
        } 
		if (filter_var ($client_ip, FILTER_VALIDATE_IP)) return $client_ip;
        return '0.0.0.0';
	}
	
	/**
	 * Utility to get Wordpress option from $options 
	 *
	 * @since		1.1.0
	 * @param      	string   	$option   			Option Name
	 * @param      	var			$default_return		Default return value - Optional
	 */
	public function get_option ($option, $default_return = '') {
		if (isset($this->options[$option])) return $this->options[$option];
		return $default_return;
	}
	
	/**
	 * Utility to echo and log data
	 *
	 * @since		1.0.0
	 * @param      	variable, string    $data       Variable to display 
	 * @param      	boolean    			$pad       	Switch for padding to accommodate for WP Dashboard nav
	 * @param      	boolean    			$log       	Switch to log data to error log
	 */
	public function d ($data, $pad = false, $log = false) {
		echo '<pre style="color: red;">';
		if ($pad) echo '>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> ';
		var_dump($data);
		echo '</pre>';
		if ($log) error_log($data);
	}
}
