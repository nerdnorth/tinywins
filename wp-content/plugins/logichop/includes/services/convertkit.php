<?php

if (!defined('ABSPATH')) die;

/**
 * ConvertKit functionality.
 *
 * Provides ConvertKit functionality.
 *
 * @since      1.1.0
 * @package    LogicHop
 * @subpackage LogicHop/includes/services
 */
	
class LogicHop_ConvertKit {
	
	/**
	 * Core functionality & logic class
	 *
	 * @since    1.1.0
	 * @access   private
	 * @var      LogicHop_Core    $logic    Core functionality & logic.
	 */
	private $logic;
	
	/**
	 * ConvertKit API URL
	 *
	 * @since    1.1.0
	 * @access   private
	 * @var      string    $convertkit_url    ConvertKit API URL
	 */
	private $convertkit_url;
	
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    	1.1.0
	 * @param       object    $logic	LogicHop_Core functionality & logic.
	 */
	public function __construct( $logic ) {
		$this->logic 				= $logic;
		$this->convertkit_url 		= 'https://api.convertkit.com/v3/';
	}
	
	/**
	 * Check if ConvertKit has been set
	 *
	 * @since    	1.1.0
	 * @return      boolean     If ConvertKit variables have been set
	 */
	public function active () {
		if ($this->logic->get_option('convertkit_key') !='' && $this->logic->get_option('convertkit_secret') !='') return true;
		return false;
	}
	
	/**
	 * ConvertKit Set up
	 * Adds LogicHop field to ConvertKit
	 *
	 * @since    	1.1.0
	 * @return      boolean     If ConvertKit variables have been set
	 */
	public function set_up ($api_secret = false) {
		$url = sprintf('%scustom_fields', $this->convertkit_url );
		$data = array (
						'api_secret'	=> ($api_secret) ? $api_secret : $this->logic->get_option('convertkit_secret'),
						'label' 		=> 'LogicHop'
					);
		$post_args = array (
						'headers' => array (
							'Content-Type' => 'application/json'
							),
						'body' => json_encode($data)
					);
		$response = wp_remote_post($url, $post_args);
		
		return ($response['response']['code'] == 201 || $response['response']['code'] == 422) ? true : false;
	}
	
	/**
	 * If ConvertKit enabled and ID or Email & Token are present, than retrieve data
	 *
	 * @since    	1.1.0
	 * @return      boolean     If ConvertKit variables have been set
	 */
	public function data_check () {
		if (!$this->active() || !isset($_SESSION['logichop-data']->ConvertKitID)) return false;
		if ($_SESSION['logichop-data']->ConvertKitID != '') return $this->data_retrieve();
		if (isset($_GET['convertkit']) && isset($_GET['email'])) return $this->data_retrieve($_GET['email']);
		return false;
	}
	
	/**
	 * Retrieve ConvertKit Data
	 *
	 * @since    	1.1.0
	 * @param      	string     $emai       Optional Email Address
	 * @return      boolean     If ConvertKit variables have been set
	 */
	public function data_retrieve ($email = false) {
		
		if ($email) {
			$url = sprintf('%ssubscribers/?api_secret=%s&email_address=%s', 
							$this->convertkit_url, 
							$this->logic->get_option('convertkit_secret'), 
							$email
						);
		} else {
			$url = sprintf('%ssubscribers/%s/?api_secret=%s', 
							$this->convertkit_url,
							$_SESSION['logichop-data']->ConvertKitID,
							$this->logic->get_option('convertkit_secret')
						);
		}
		
		$response = wp_remote_get($url);
		
		if (!is_wp_error($response)) {
			if (isset($response['body'])) $data = json_decode($response['body'], false);
		} else {
			return $response->get_error_message();
		}
		
		$ck_data = false;
		
		if ($email) {
			if (isset($data->subscribers) && count($data->subscribers) >= 1) $ck_data = $data->subscribers[0];
		} else {
			if (isset($data->subscriber)) $ck_data = $data->subscriber;
		}
		
		if ($ck_data) {
			$_SESSION['logichop-data']->ConvertKit			= $ck_data;
			$_SESSION['logichop-data']->ConvertKit->tags	= array ();
		
			if ($email && isset($ck_data->id)) { // STORE ConvertKit ID
				$this->logic->data_remote_put('convertkit', $ck_data->id);
				$_SESSION['logichop-data']->ConvertKitID = $ck_data->id;
				$uid = (isset($_COOKIE['logichop'])) ? $_COOKIE['logichop'] : $this->logic->hash;
				$this->update_field('logichop', $uid);	
				$_SESSION['logichop-data']->ConvertKit->fields->logichop = $uid;
			}
			$this->logic->gravatar_object('ConvertKit', $ck_data->email_address);		
			$this->retrieve_tags();
			return true;
		}
		return false;
	}
	
	/**
	 * Retrieve ConvertKit Subscriber Tags
	 *
	 * @since    	1.1.0
	 * @return      boolean     If ConvertKit variables have been set
	 */
	public function retrieve_tags () {
		if ($this->active()) {
			$url = sprintf('%ssubscribers/%s/tags?api_key=%s', 
							$this->convertkit_url,
							$_SESSION['logichop-data']->ConvertKitID,
							$this->logic->get_option('convertkit_key')
						);
			$response = wp_remote_get($url);
			
			if (!is_wp_error($response)) {
				if (isset($response['body'])) $tags = json_decode($response['body'], false);
				
				if (isset($tags->tags)) {
					foreach ($tags->tags as $tag) {
						$_SESSION['logichop-data']->ConvertKit->tags[$tag->id] = $tag->name;
					}
				}
			} else {
				return false;
			}
		}
		return true;
	}
	
	/**
	 * ConvertKit Track Event
	 * Checks for tracking actions
	 *
	 * @since    	1.1.0
	 * @param      	integer     Post ID
	 * @return      boolean     If tag has been added or removed
	 */
	public function track_event ($id) {
		if ($this->active()) {
			$values	= get_post_custom($id);
			
			if (isset($values['logichop_goal_ck_tag'][0])) {
				$tag_id = $values['logichop_goal_ck_tag'][0];
				if ($tag_id && $_SESSION['logichop-data']->ConvertKitID) {
					if ($values['logichop_goal_ck_tag_action'][0] == 'add') {
						return $this->add_tag($tag_id);
					} else {
						return $this->remove_tag($tag_id);
					}
				}
			}
		}
		return false;
	}
		
	/**
	 * Send Add Tag request to ConvertKit
	 *
	 * @since    	1.1.0
	 * @param      integer     $ck_id        ConvertKit ID
	 * @param      integer     $tag_id       Tag ID
	 */
	public function add_tag ($tag_id) {
		if (!isset($_SESSION['logichop-data']->ConvertKit->email_address)) return false;
		$url = sprintf('%stags/%s/subscribe', 
								$this->convertkit_url,
								$tag_id
							);
		$data = array (
					'api_key' 	=> $this->logic->get_option('convertkit_key'),
					'email' 	=> $_SESSION['logichop-data']->ConvertKit->email_address
				);
		$post_args = array (
						'headers' => array (
							'Content-Type' => 'application/json'
							),
						'body' => json_encode($data)
					);
		$response = wp_remote_post($url, $post_args);
		
		if (!is_wp_error($response)) {
			$this->retrieve_tags();
			return true;
		}
		return false;
	}
	
	/**
	 * Send Remove Tag request to ConvertKit
	 *
	 * @since    	1.1.0
	 * @param      integer     $ck_id        ConvertKit ID
	 * @param      integer     $tag_id       Tag ID
	 */
	public function remove_tag ($tag_id) {
		$url = sprintf('%ssubscribers/%s/tags/%s?api_key=%s', 
								$this->convertkit_url,
								$_SESSION['logichop-data']->ConvertKitID,
								$tag_id,
								$this->logic->get_option('convertkit_key')
							);
		$args = array (
						'method' => 'DELETE'
					);
		$response = wp_remote_request($url, $args);
		
		if (!is_wp_error($response)) {
			$this->retrieve_tags();
			return true;
		}
		return false;
	}
	
	/**
	 * Get ConvertKit Tags
	 *
	 * @since    	1.1.0
	 * @return      object    ConvertKit Tags
	 */
	public function tags_get ($api_key = false) {		
		if ($this->active() || $api_key) {
			$url = sprintf('%stags/?api_key=%s', 
								$this->convertkit_url,
								($api_key) ? $api_key : $this->logic->get_option('convertkit_key')
							);
			$response = wp_remote_get($url);
			
			if (!is_wp_error($response)) {
				if (isset($response['body'])) $data = json_decode($response['body'], false);
				if (isset($data->tags)) return $data->tags;
			}
		}
		return false;
	}
	
	/**
	 * Get ConvertKit Tags as JSON object
	 *
	 * @since    	1.1.0
	 * @return      json object    JSON encoded tags
	 */
	public function tags_get_json () {		
		$tags = new stdclass;
		
		if ($data = $this->tags_get()) {
			foreach ($data as $tag) {
				$tags->{$tag->id} = $tag->name;
			}
		}
		return json_encode($tags);
	}
	
	/**
	 * Get ConvertKit Tags as options for select input
	 *
	 * @since    	1.1.0
	 * @param		string		$id		Selected option value
	 * @return      string		Goal options
	 */
	public function tags_get_options ($id = false) {		
		$options = '';
		
		if ($data = $this->tags_get()) {
			foreach ($data as $tag) {
				$options .= sprintf('<option value="%s" %s>%s</option>', 
							$tag->id,
							($tag->id == $id) ? 'selected' : '',
							$tag->name
						);
			}
		}
		return $options;
	}
	
	/**
	 * ConvertKit Update Field
	 * Updated ConvertKit custom field value
	 *
	 * @since    	1.1.0
	 * @param      	string     $field      Field Name
	 * @param      	string     $value      Field Value
	 */
	public function update_field ($field, $value) {
		$url = sprintf('%ssubscribers/%s', 
						$this->convertkit_url,
						$_SESSION['logichop-data']->ConvertKitID
					);
		$data = array (
						'api_secret'	=> $this->logic->get_option('convertkit_secret'),
						'fields' 		=> array ( $field => $value )
					);
		$args = array (
						'method' => 'PUT',
						'headers' => array (
							'Content-Type' => 'application/json'
							),
						'body' => json_encode($data)
					);
		$response = wp_remote_request($url, $args);
	}
	
	/**
	 * Get ConvertKit Custom Fields
	 *
	 * @since    	1.1.0
	 * @return      object    ConvertKit Custom Fields
	 */
	public function custom_fields_get () {		
		if ($this->active()) {
			$url = sprintf('%scustom_fields/?api_key=%s', 
								$this->convertkit_url,
								$this->logic->get_option('convertkit_key')
							);
			$response = wp_remote_get($url);
			
			if (!is_wp_error($response)) {
				if (isset($response['body'])) $data = json_decode($response['body'], false);
				if (isset($data->custom_fields)) return $data->custom_fields;
			}
		}
		return false;
	}
	
	/**
	 * Get ConvertKit Fields as JSON object
	 *
	 * @since    	1.1.0
	 * @return      json object    JSON encoded fields
	 */
	public function fields_get_json () {		
		$fields = new stdclass;
		
		if ($data = $this->custom_fields_get()) {
			foreach ($data as $field) {
				$fields->{$field->key} = $field->label;
			}
		}
		return json_encode($fields);
	}
	
	/**
	 * Get ConvertKit variables as array of options for shortcodes
	 *
	 * @since    	1.1.0
	 * @return      array		Convertkit custom fields
	 */
	public function shortcode_variables_data ($invert = false) {
		$vars = array (
			'ConvertKit.first_name' => 'First Name',
			'ConvertKit.email_address' => 'Email Address',
			'ConvertKit.gravatar.img.fullsize' => 'Gravatar Full Size (2048px)',
			'ConvertKit.gravatar.img.large' => 'Gravatar Large (1024px)',
			'ConvertKit.gravatar.img.medium' => 'Gravatar Medium (512px)',
			'ConvertKit.gravatar.img.small' => 'Gravatar Small (256px)',
			'ConvertKit.gravatar.img.thumb' => 'Gravatar Thumbnail (100px)',
			'ConvertKit.id' => 'ConvertKit ID',
			'ConvertKit.created_at' => 'Created At'
		);
		
		if ($data = $this->custom_fields_get()) {
			foreach ($data as $field) {
				$key = sprintf('ConvertKit.fields.%s', $field->key);
				$vars[$key] = sprintf('Custom Field: %s', $field->label);
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
	 * Get ConvertKit variables as options for shortcodes
	 *
	 * @since    	1.1.0
	 * @return      string		Convertkit options
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
	 * Displays ConvertKit Tag metabox on Goal editor
	 *
	 * @since    	1.1.0
	 * @param		object		$post		Wordpress Post object
	 * @return		string					Echos metabox form
	 */
	public function goal_tag_display ($post) {
	
		$values	= get_post_custom($post->ID);
		$ck_tag_action = isset($values['logichop_goal_ck_tag_action']) ? esc_attr($values['logichop_goal_ck_tag_action'][0]) : '';
		$ck_tag = isset($values['logichop_goal_ck_tag']) ? esc_attr($values['logichop_goal_ck_tag'][0]) : '';
		
		$options = $this->tags_get_options($ck_tag);
		
		if ($this->active()) {
			
			printf('<div>
						<p>
							<label for="logichop_goal_ck_tag" class="">%s</label><br>
							<select id="logichop_goal_ck_tag_action" name="logichop_goal_ck_tag_action">
								<option value=""></option>
								<option value="add" %s>Add Tag</option>
								<option value="remove" %s>Remove Tag</option>
							</select>
							<select id="logichop_goal_ck_tag" name="logichop_goal_ck_tag">
								<option value=""></option>
								%s
							</select>
						</p>
					</div>',
					__('ConvertKit Tag Action', 'logichop'),
					($ck_tag_action == 'add') ? 'selected' : '',
					($ck_tag_action == 'remove') ? 'selected' : '',
					$options
				);
		} else {
			
			printf('<div>
						<h4>%s</h4>
						<p>
							%s
						</p>
					</div>',
					__('ConvertKit is currently disabled.', 'logichop'),
					sprintf(__('To enable, add a valid ConvertKit API Key & Secret on the <a href="%s">Settings page</a>.', 'logichop'),
							admin_url('admin.php?page=logichop-settings')
						)
				);
		}
	}
}


