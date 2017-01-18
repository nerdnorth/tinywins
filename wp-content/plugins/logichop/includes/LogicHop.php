<?php

/**
 * Core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    LogicHop
 * @subpackage LogicHop/includes
 * @author     LogicHop <info@logichop.com>
 */
class LogicHop {
	
	/**
	 * The class that's responsible for core functionality & logic
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      LogicHop_Core    $logic    Core functionality & logic.
	 */
	protected $logic;

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      LogicHop_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * Plugin basename
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_basename    Plugin basename.
	 */
	protected $plugin_basename;
	
	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;
	
	/**
	 * Time modifier for cookie expiration.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $cookie_ttl    strtotime modifier.
	 */
	protected $cookie_ttl;
	
	/**
	 * Maximum number of pages in path history.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      integer    $path_max    Maximum number of pages in path history.
	 */
	protected $path_max;
	
	/**
	 * API URL
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $api_url    API URL.
	 */
	protected $api_url;
	
	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $js_path    Path to remove Javascript files.
	 */
	protected $js_path;
	
	/**
	 * Debug mode enabled
	 *
	 * @since    1.1.0
	 * @access   public
	 * @var      boolean    $debug    
	 */
	public $debug;
	
	/**
	 * LogicHop core functionality.
	 *
	 * @since    1.0.0
	 */
	public function __construct ($plugin_basename) {
		$this->plugin_basename = $plugin_basename;
		$this->plugin_name 	= 'logichop';
		$this->version 		= '1.1.0';
		$this->cookie_ttl  	= '+ 1 year';
		$this->path_max 	= 5;
		$this->api_url 		= 'https://spf.logichop.com/v1.1/data';
		$this->js_path 		= 'https://spf.logichop.com/v1.1/js/condition-builder.min.js';
		$this->debug		= false;
		
		$this->load_dependencies();
		$this->set_locale();
		$this->define_core_hooks();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load dependencies.
	 *
	 * - Loader: Orchestrates the hooks of the plugin.
	 * - Core: Defines all hooks for core functionality.
	 * - i18n: Defines internationalization functionality.
	 * - Admin: Defines all hooks & filters for the admin area.
	 * - Public: Defines all hooks & filters for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies () {
		
		/**
		 * Core Functionality
		 * Required classes & libraries
		 */
		require_once plugin_dir_path( dirname(__FILE__) ) . 'includes/classes/JsonLogic.php';
		require_once plugin_dir_path( dirname(__FILE__) ) . 'includes/classes/Mobile_Detect.php';
		require_once plugin_dir_path( dirname(__FILE__) ) . 'includes/core.php';
		
		/**
		 * Services
		 */
		require_once plugin_dir_path( dirname(__FILE__) ) . 'includes/services/geo_ip.php';
		require_once plugin_dir_path( dirname(__FILE__) ) . 'includes/services/google_analytics.php';
		require_once plugin_dir_path( dirname(__FILE__) ) . 'includes/services/convertkit.php';
		require_once plugin_dir_path( dirname(__FILE__) ) . 'includes/services/drip.php';
		
		/**
		 * Visual Editors
		 */
		require_once plugin_dir_path( dirname(__FILE__) ) . 'includes/editors/visual_composer.php';
		
		/**
		 * Actions & Filters
		 */
		require_once plugin_dir_path( dirname(__FILE__) ) . 'includes/loader.php';

		/**
		 * Internationalization
		 */
		require_once plugin_dir_path( dirname(__FILE__) ) . 'includes/i18n.php';

		/**
		 * Admin Functionality
		 */
		require_once plugin_dir_path( dirname(__FILE__) ) . 'admin/admin.php';

		/**
		 * Public Functionality
		 */
		require_once plugin_dir_path( dirname(__FILE__) ) . 'public/public.php';
	
		$this->loader = new LogicHop_Loader();
		$this->logic = new LogicHop_Core( 	$this->get_plugin_name(), 
											$this->get_version(), 
											$this->cookie_ttl,
											$this->path_max, 
											$this->api_url, 
											$this->js_path, 
											$this->debug
										);
	}

	/**
	 * Define locale for internationalization.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale () {
		$i18n = new LogicHop_i18n($this->get_plugin_name());
		$this->loader->add_action( 'plugins_loaded', $i18n, 'load_plugin_textdomain' );
	}
	
	/**
	 * Register all of the hooks related to the core functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_core_hooks () {
		$this->loader->add_action( 'init', $this->logic, 'initialize_core' );
	}
	
	/**
	 * Register all of the hooks & filters related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks () {
		$plugin_admin = new LogicHop_Admin( $this->logic, 
											$this->plugin_basename, 
											$this->get_plugin_name(), 
											$this->get_version(), 
											$this->js_path
										);
		
		$this->loader->add_action( 'admin_init', $plugin_admin, 'settings_register' );
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'display_admin_notice' );
		$this->loader->add_action( 'after_plugin_row', $plugin_admin, 'display_plugin_notice' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'menu_pages' );
		$this->loader->add_action( 'admin_footer', $plugin_admin, 'editor_shortcode_modal' );
		$this->loader->add_action( 'updated_option', $plugin_admin, 'settings_updated', 10, 3 );
		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'configure_metaboxes' );
		$this->loader->add_action( 'save_post', $plugin_admin, 'condition_builder_save' );
		$this->loader->add_action( 'save_post', $plugin_admin, 'primary_metabox_save' );
		$this->loader->add_action( 'save_post', $plugin_admin, 'goal_google_event_save' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		
		$this->loader->add_filter( 'plugin_action_links_' . $this->plugin_basename, $plugin_admin, 'display_settings_link' );
		$this->loader->add_filter( 'contextual_help', $plugin_admin, 'help_menus', 10, 3 );
		$this->loader->add_filter( 'post_updated_messages', $plugin_admin, 'custom_post_messages' );
		$this->loader->add_filter( 'in_widget_form', $plugin_admin, 'widget_form_override', 10, 3 );
		$this->loader->add_filter( 'widget_update_callback', $plugin_admin, 'widget_save_override', 10, 2 );
		$this->loader->add_filter( 'media_buttons_context', $plugin_admin, 'editor_buttons' );
	}

	/**
	 * Register all of the hooks & filters related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks () {
		$plugin_public = new LogicHop_Public( $this->logic, $this->get_plugin_name(), $this->get_version() );
		
		$this->loader->add_action( 'init', $plugin_public, 'register_shortcodes' );
		$this->loader->add_action( 'wp_ajax_logichop_goal', $plugin_public, 'logichop_goal' );
		$this->loader->add_action( 'wp_ajax_nopriv_logichop_goal', $plugin_public, 'logichop_goal' );
		$this->loader->add_action( 'wp_ajax_logichop_page_view', $plugin_public, 'logichop_page_view' );
		$this->loader->add_action( 'wp_ajax_nopriv_logichop_page_view', $plugin_public, 'logichop_page_view' );
		$this->loader->add_action( 'wp_ajax_logichop_condition', $plugin_public, 'logichop_condition' );
		$this->loader->add_action( 'wp_ajax_nopriv_logichop_condition', $plugin_public, 'logichop_condition' );
		$this->loader->add_action( 'wp_ajax_logichop_conditional_css', $plugin_public, 'logichop_conditional_css' );
		$this->loader->add_action( 'wp_ajax_nopriv_logichop_conditional_css', $plugin_public, 'logichop_conditional_css' );
		$this->loader->add_action( 'template_redirect', $plugin_public, 'template_level_parsing' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		
		$this->loader->add_filter( 'dynamic_sidebar_params', $plugin_public, 'widget_display_callback' );
		$this->loader->add_filter( 'body_class', $plugin_public, 'body_class_insertion' );
		$this->loader->add_filter( 'siteorigin_panels_widget_object', $plugin_public, 'siteorigin_panels_widget_filter', 10, 3 );
	}

	/**
	 * Initialize LogicHop to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function init () {
		$this->loader->run();
	}

	/**
	 * @since     1.0.0
	 * @return    string    Plugin name
	 */
	public function get_plugin_name () {
		return $this->plugin_name;
	}

	/**
	 * Reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader () {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    Plugin version number
	 */
	public function get_version () {
		return $this->version;
	}
	
	/**
	 * Return Logic Hop data
	 *
	 * Data extracted from $_SESSION['logichop-data']
	 * Accepts [logichop_data vars=""]
	 * Parameter 'vars' accepts '.' delimited object elements and ':' delimited array elements
	 * Example: Date.DateTime OR QueryStore:ref 
	 *
	 * @since    	1.0.9
	 * @param  		string	$var		Data path
	 * @param  		boolean	$echo		Switch to echo or retrun data
	 * @return  	null or content		Data as a string
	 */
	public function get_data ($var, $echo = true) {
		$data = $this->logic->data_return($var);
		if (!$echo) return $data;
		echo $data;
	}
}
