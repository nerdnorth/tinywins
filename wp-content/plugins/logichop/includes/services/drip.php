<?php

if (!defined('ABSPATH')) die;

/**
 * Drip functionality.
 *
 * Provides Drip functionality.
 *
 * @since      1.1.0
 * @package    LogicHop
 * @subpackage LogicHop/includes/services
 */
	
class LogicHop_Drip {
	
	/**
	 * Core functionality & logic class
	 *
	 * @since    1.1.0
	 * @access   private
	 * @var      LogicHop_Core    $logic    Core functionality & logic.
	 */
	private $logic;
	
	/**
	 * Drip API URL
	 *
	 * @since    1.1.0
	 * @access   private
	 * @var      string    $drip_url    Drip API URL
	 */
	private $drip_url;
	
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    	1.1.0
	 * @param       object    $logic	LogicHop_Core functionality & logic.
	 */
	public function __construct( $logic ) {
		$this->logic		= $logic;
		$this->drip_url		= 'https://api.getdrip.com/v2/';
	}
			
	/**
	 * Check if Drip has been set
	 *
	 * @since    	1.1.0
	 * @return      boolean     If Drip variables have been set
	 */
	public function active () {
		if ($this->logic->get_option('drip_account_id') !='' && $this->logic->get_option('drip_api_token') !='') return true;
		return false;
	}
	
	/**
	 * If Drip enabled and ID or Email & Token are present, than retrieve data
	 *
	 * @since    	1.1.0
	 * @return      boolean     If Drip variables have been set
	 */
	public function data_check () {
		if (!$this->active() || !isset($_SESSION['logichop-data']->DripID)) return false;
		if ($_SESSION['logichop-data']->DripID != '') return $this->data_retrieve();
		if (isset($_GET['drip_email'])) return $this->data_retrieve($_GET['drip_email']);
		return false;
	}
	
	/**
	 * Retrieve Drip Data
	 *
	 * @since    	1.1.0
	 * @param      	string     	$email       Optional Email Address
	 * @return      boolean     If Drip variables have been set
	 */
	public function data_retrieve ($email = false) {
		$args = array(
					'headers' => array(
    					'Authorization' => 'Basic ' . base64_encode($this->logic->get_option('drip_api_token') . ':')
  						)
					);
		$url = sprintf('%s%s/subscribers/%s', 
							$this->drip_url,
							$this->logic->get_option('drip_account_id'),
							($email) ? $email : $_SESSION['logichop-data']->DripID 
						);
		
		$response = wp_remote_get($url, $args);
		
		if (!is_wp_error($response)) {
			if (isset($response['body'])) $data = json_decode($response['body'], false);
		} else {
			return $response->get_error_message();
		}
		
		$drip_data = isset($data->subscribers[0]) ? $data->subscribers[0] : false;
		
		if ($drip_data) {
			$_SESSION['logichop-data']->Drip = $drip_data;
			
			$tags = array();
			if (isset($drip_data->tags)) {
				foreach ($drip_data->tags as $tag) {
					$tags[sanitize_key($tag)] = $tag;
				}
			}
			$_SESSION['logichop-data']->Drip->tags = $tags;
			
			if ($email && isset($drip_data->id)) { // STORE Drip ID
				$this->logic->data_remote_put('drip', $drip_data->id);
				$_SESSION['logichop-data']->DripID = $drip_data->id;
				$uid = (isset($_COOKIE['logichop'])) ? $_COOKIE['logichop'] : $this->logic->hash;
				$this->update_field('logichop', $uid);	
				$_SESSION['logichop-data']->Drip->custom_fields->logichop = $uid;
			}
			
			$this->logic->gravatar_object('Drip', $drip_data->email);				
			return true;
		}
		return false;
	}
	
	/**
	 * Drip Update Field
	 * Updated Drip custom field value
	 *
	 * @since    	1.1.0
	 * @param      	string     $field      Field Name
	 * @param      	string     $value      Field Value
	 */
	public function update_field ($field, $value) {
		if ($_SESSION['logichop-data']->DripID == '') return false;
		$data = array (
						'subscribers'	=> array (
							0 => array ( 
								'id' => $_SESSION['logichop-data']->DripID,
								'custom_fields' => array (
									$field => $value
								)
							)
						)
					);
		$args = array(
						'headers' => array(
							'method' => 'POST',
    						'Authorization' => 'Basic ' . base64_encode($this->logic->get_option('drip_api_token') . ':'),
    						'Content-Type' => 'application/json'
  							),
						'body' => json_encode($data)
					);
		$url = sprintf('%s%s/subscribers', 
							$this->drip_url,
							$this->logic->get_option('drip_account_id')
						);
		$response = wp_remote_post($url, $args);
	}
	
	/**
	 * Drip Track Event
	 * Checks for tracking actions
	 *
	 * @since    	1.1.0
	 * @param      	integer     Post ID
	 */
	public function track_event ($id) {
		if ($this->active() && isset($_SESSION['logichop-data']->Drip->email)) {
			$values	= get_post_custom($id);
			
			if (isset($values['logichop_goal_drip_tag'][0])) {
				$tag = $values['logichop_goal_drip_tag'][0];
				if ($tag && $_SESSION['logichop-data']->DripID) {
					if ($values['logichop_goal_drip_tag_action'][0] == 'add') {
						$this->add_tag($tag, $_SESSION['logichop-data']->Drip->email);
					} else {
						$this->remove_tag($tag, $_SESSION['logichop-data']->Drip->email);
					}
				}
			}
			
			if (isset($values['logichop_goal_drip_event'][0])) {
				$event = $values['logichop_goal_drip_event'][0];
				if ($event && $_SESSION['logichop-data']->DripID) {
					if ($values['logichop_goal_drip_add_event'][0] == 'add') {
						$this->add_event($event, $_SESSION['logichop-data']->Drip->email);
					}
				}
			}
		}
	}
	
	/**
	 * Send Add Event request to Drip
	 *
	 * @since    	1.1.0
	 * @param      	string     $event     	Event
	 * @param      	string     $email 		Email
	 * @return     	boolean     			Success state
	 */
	public function add_event ($event, $email) {
		$data = array (
						'events'	=> array (
							0 => array ( 
								'email' => $email,
								'action' => $event
							)
						)
					);
		$url = sprintf('%s%s/events', 
								$this->drip_url,
								$this->logic->get_option('drip_account_id')
							);
		$args = array (
						'headers' => array(
							'Authorization' => 'Basic ' . base64_encode($this->logic->get_option('drip_api_token') . ':'),
							'Content-Type' => 'application/json'
  							),
						'body' => json_encode($data)
					);
		$response = wp_remote_post($url, $args);
		
		if (!is_wp_error($response)) return true;
		return false;
	}
	
	/**
	 * Send Add Tag request to Drip
	 *
	 * @since    	1.1.0
	 * @param      	string     $tag     	Tag
	 * @param      	string     $email 		Email
	 * @return     	boolean     			Success state
	 */
	public function add_tag ($tag, $email) {
		$data = array (
						'tags'	=> array (
							0 => array ( 
								'email' => $email,
								'tag' => $tag
							)
						)
					);
		$url = sprintf('%s%s/tags', 
								$this->drip_url,
								$this->logic->get_option('drip_account_id')
							);
		$args = array (
						'headers' => array(
							'Authorization' => 'Basic ' . base64_encode($this->logic->get_option('drip_api_token') . ':'),
							'Content-Type' => 'application/json'
  							),
						'body' => json_encode($data)
					);
		$response = wp_remote_post($url, $args);
		
		if (!is_wp_error($response)) {
			$this->data_retrieve();
			return true;
		}
		return false;
	}
	
	/**
	 * Send Remove Tag request to Drip
	 *
	 * @since    	1.1.0
	 * @param      	string     $tag     	Tag
	 * @param      	string     $email 		Email
	 * @return     	boolean     			Success state
	 */
	public function remove_tag ($tag, $email) {
		$url = sprintf('%s%s/subscribers/%s/tags/%s', 
								$this->drip_url,
								$this->logic->get_option('drip_account_id'),
								$email,
								urlencode($tag)
							);
		$args = array (
						'method' => 'DELETE',
						'headers' => array(
							'method' => 'DELETE',
							'Authorization' => 'Basic ' . base64_encode($this->logic->get_option('drip_api_token') . ':')
  						)
					);
		$response = wp_remote_request($url, $args);
		
		if (!is_wp_error($response)) {
			$this->data_retrieve();
			return true;
		}
		return false;
	}
	
	/**
	 * Get Drip Tags
	 *
	 * @since    	1.1.0
	 * @param      	string     $api_token      	Optional API Token
	 * @param      	string     $account_id      Optional Account ID
	 * @return      object    					Drip Tags
	 */
	public function tags_get ($api_token = false, $account_id = false) {		
		if ($this->active() || $api_token && $account_id) {
			$api_token = ($api_token) ? $api_token : $this->logic->get_option('drip_api_token');
			$account_id = ($account_id) ? $account_id : $this->logic->get_option('drip_account_id');
			
			if (!preg_match('/\d{1,10}$/i', $account_id)) return false;
			
			$args = array(
					'headers' => array(
    					'Authorization' => 'Basic ' . base64_encode($api_token . ':')
  						)
					);
			$url = sprintf('%s%s/tags', 
							$this->drip_url,
							$account_id
						);
			$response = wp_remote_get($url, $args);
			
			if (!is_wp_error($response)) {
				if (isset($response['body'])) $data = json_decode($response['body'], false);
				if (isset($data->tags)) return $data->tags;
			}
		}
		return false;
	}
	
	/**
	 * Get Drip Tags as JSON object
	 *
	 * @since    	1.1.0
	 * @return      json object    JSON encoded tags
	 */
	public function tags_get_json () {		
		$tags = array();
		
		if ($data = $this->tags_get() ) {
			foreach ($data as $tag) {
				$tags[sanitize_key($tag)] = $tag;
			}
		}
		return json_encode($tags);
	}
	
	/**
	 * Get Drip Tags as options for select input
	 *
	 * @since    	1.1.0
	 * @param		string		$id		Selected option value
	 * @return      string		Goal options
	 */
	public function tags_get_options ($id = false) {		
		$options = '';
		if ($data = $this->tags_get() ) {
			foreach ($data as $tag) {
				$options .= sprintf('<option value="%s" %s>%s</option>', 
								$tag,
								($tag == $id) ? 'selected' : '',
								$tag
							);
			}
		}
		return $options;
	}
	
	/**
	 * Get Drip Custom Fields
	 *
	 * @since    	1.1.0
	 * @param      	string     $api_token      	Optional API Token
	 * @param      	string     $account_id      Optional Account ID
	 * @return      object    					Custom Fields
	 */
	public function fields_get ($api_token = false, $account_id = false) {		
		if ($this->active() || $api_token && $account_id) {
			$api_token = ($api_token) ? $api_token : $this->logic->get_option('drip_api_token');
			$account_id = ($account_id) ? $account_id : $this->logic->get_option('drip_account_id');
			
			if (!preg_match('/\d{1,10}$/i', $account_id)) return false;
			
			$args = array(
					'headers' => array(
    					'Authorization' => 'Basic ' . base64_encode($api_token . ':')
  						)
					);
			$url = sprintf('%s%s/custom_field_identifiers', 
							$this->drip_url,
							$account_id
						);
			$response = wp_remote_get($url, $args);
			
			if (!is_wp_error($response)) {
				if (isset($response['body'])) $data = json_decode($response['body'], false);
				if (isset($data->custom_field_identifiers)) return $data->custom_field_identifiers;
			}
		}
		return false;
	}
	
	/**
	 * Get Drip variables as array of options for shortcodes
	 *
	 * @since    	1.1.0
	 * @return      array		Drip custom fields
	 */
	public function shortcode_variables_data ($invert = false) {
		$vars = array (
			'Drip.email' => 'Email Address',
			'Drip.gravatar.img.fullsize' => 'Gravatar Full Size (2048px)',
			'Drip.gravatar.img.large' => 'Gravatar Large (1024px)',
			'Drip.gravatar.img.medium' => 'Gravatar Medium (512px)',
			'Drip.gravatar.img.small' => 'Gravatar Small (256px)',
			'Drip.gravatar.img.thumb' => 'Gravatar Thumbnail (100px)',
			'Drip.landing_url' => 'Landing URL',
			'Drip.original_referrer' => 'Original Referrer',
			'Drip.created_at' => 'Created At',
			'Drip.prospect' => 'Prospect',
			'Drip.lifetime_value' => 'Lifetime Value',
			'Drip.lead_score' => 'Lead Score',
			'Drip.base_lead_score' => 'Base Lead Score',
			'Drip.time_zone' => 'Time Zone',
			'Drip.utc_offset' => 'UTC Offset'
		);
		
		if ($data = $this->fields_get()) {
			foreach ($data as $f) {
				$key = sprintf('Drip.custom_fields.%s', $f);
				$vars[$key] = sprintf('Custom Field: %s', $f);
			}
		}
		
		if ($invert) {
			$inverted = array();
			foreach ($vars as $k => $v) $inverted[$v] = $k;
			return $inverted;
		}
		
		return $vars;
	}
	
	/**
	 * Get Drip variables as options for shortcodes
	 *
	 * @since    	1.1.0
	 * @return      string		Drip options
	 */
	public function shortcode_variables () {
		$options = '';
		if ($data = $this->shortcode_variables_data()) {
			foreach ($data as $k => $v) {
				$options .= sprintf('<option value="%s">%s</option>', $k, $v);
			}
		}
		return $options;
	}
	
	/**
	 * Get Drip Fields as JSON object
	 *
	 * @since    	1.1.0
	 * @return      json object    JSON encoded fields
	 */
	public function fields_get_json () {		
		$fields = array();
		
		if ($data = $this->fields_get() ) {
			foreach ($data as $field) {
				$fields[$field] = $field;
			}
		}
		return json_encode($fields);
	}
	
	/**
	 * Displays Drip Tag metabox on Goal editor
	 *
	 * @since    	1.1.0
	 * @param		object		$post		Wordpress Post object
	 * @return		string					Echos metabox form
	 */
	public function goal_tag_display ($post) {
	
		$values	= get_post_custom($post->ID);
		$drip_tag_action = isset($values['logichop_goal_drip_tag_action']) ? esc_attr($values['logichop_goal_drip_tag_action'][0]) : '';
		$drip_tag = isset($values['logichop_goal_drip_tag']) ? esc_attr($values['logichop_goal_drip_tag'][0]) : '';
		
		$drip_add_event = isset($values['logichop_goal_drip_add_event']) ? esc_attr($values['logichop_goal_drip_add_event'][0]) : '';
		$drip_event = isset($values['logichop_goal_drip_event']) ? esc_attr($values['logichop_goal_drip_event'][0]) : '';
		
		$options = $this->tags_get_options($drip_tag);
		
		if ($this->active()) {
			printf('<div>
						<p>
							<label for="logichop_goal_drip_tag" class="">%s</label><br>
							<select id="logichop_goal_drip_tag_action" name="logichop_goal_drip_tag_action">
								<option value=""></option>
								<option value="add" %s>Add Tag</option>
								<option value="remove" %s>Remove Tag</option>
							</select>
							<select id="logichop_goal_drip_tag" name="logichop_goal_drip_tag">
								<option value=""></option>
								%s
							</select>
						</p>
					</div>',
					__('Drip Tag Action', 'logichop'),
					($drip_tag_action == 'add') ? 'selected' : '',
					($drip_tag_action == 'remove') ? 'selected' : '',
					$options
				);
			printf('<div>
						<label for="logichop_goal_drip_event" class="">%s</label><br>
						<select id="logichop_goal_drip_add_event" name="logichop_goal_drip_add_event">
							<option value=""></option>
							<option value="add" %s>Add Event</option>
						</select>
						<input type="text" id="logichop_goal_drip_event" name="logichop_goal_drip_event" value="%s" placeholder="%s">
					</div>',
					__('Drip Add Event Action', 'logichop'),
					($drip_add_event == 'add') ? 'selected' : '',
					$drip_event,
					__('Event Action', 'logichop')
				);
		} else {
			printf('<div>
						<h4>%s</h4>
						<p>
							%s
						</p>
					</div>',
					__('Drip is currently disabled.', 'logichop'),
					sprintf(__('To enable, add a valid Drip Account ID & API Token on the <a href="%s">Settings page</a>.', 'logichop'),
							admin_url('admin.php?page=logichop-settings')
						)
				);
		}
	}
}


