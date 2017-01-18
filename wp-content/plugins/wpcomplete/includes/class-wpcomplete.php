<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://wpcomplete.co
 * @since      1.0.0
 *
 * @package    WPComplete
 * @subpackage wpcomplete/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    WPComplete
 * @subpackage wpcomplete/includes
 * @author     Zack Gilbert <zack@zackgilbert.com>
 */
class WPComplete {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      WPComplete_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

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
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name = WPCOMPLETE_PREFIX;
		$this->version = '1.4.7';
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - WPComplete_Loader. Orchestrates the hooks of the plugin.
	 * - WPComplete_i18n. Defines internationalization functionality.
	 * - WPComplete_Admin. Defines all hooks for the admin area.
	 * - WPComplete_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wpcomplete-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wpcomplete-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wpcomplete-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wpcomplete-public.php';

		$this->loader = new WPComplete_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the WPComplete_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new WPComplete_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new WPComplete_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		// adding settings page and metaboxes
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_options_page' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'register_settings' );
		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'add_completable_metabox' );
		$this->loader->add_action( 'save_post', $plugin_admin, 'save_completable' );
		// adding bulk actions
		$this->loader->add_action( 'admin_footer-edit.php', $plugin_admin, 'add_bulk_actions' );
		$this->loader->add_action( 'load-edit.php', $plugin_admin, 'save_bulk_completable' );
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'show_bulk_action_notice' );
		// adding custom edit column + quick edit
		$this->loader->add_action( 'manage_pages_columns', $plugin_admin, 'add_custom_column_header' );
		$this->loader->add_action( 'manage_posts_columns', $plugin_admin, 'add_custom_column_header' );
		$this->loader->add_action( 'manage_pages_custom_column', $plugin_admin, 'add_custom_column_value', 10, 2 );
		$this->loader->add_action( 'manage_posts_custom_column', $plugin_admin, 'add_custom_column_value', 10, 2 );
		//$this->loader->add_action( 'manage_edit-page_sortable_columns', $plugin_admin, 'sort_custom_column' );
		//$this->loader->add_action( 'manage_edit-post_sortable_columns', $plugin_admin, 'sort_custom_column' );
		$this->loader->add_action( 'quick_edit_custom_box', $plugin_admin, 'add_custom_quick_edit', 10, 2 );
		// adding custom completion column for users
		$this->loader->add_action( 'manage_users_columns', $plugin_admin, 'add_user_column_header' );
		$this->loader->add_action( 'manage_users_custom_column', $plugin_admin, 'add_user_column_value', 10, 3 );
		//$this->loader->add_action( 'manage_users_sortable_columns', $plugin_admin, 'sort_custom_column' );
		//$this->loader->add_action( 'pre_user_query', $plugin_admin, 'user_column_orderby' );

		// PREMIUM: add specific pages to show completion
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_post_completion_page' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_user_completion_page' );

		// PREMIUM: Check for and validate license:
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'show_license_notice' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'activate_license' );

		// PREMIUM:
		if (WPCOMPLETE_IS_ACTIVATED) {
			// auto complete/suggest page/post title lookup
			$this->loader->add_action( 'wp_ajax_post_lookup', $plugin_admin, 'post_lookup');
			$this->loader->add_action( 'wp_ajax_nopriv_post_lookup', $plugin_admin, 'post_lookup');
		}
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new WPComplete_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		
		$this->loader->add_filter( 'the_content', $plugin_public, 'append_completion_code', 1 );

		// Custom ajax functions:
		$this->loader->add_action( 'wp_ajax_mark_completed', $plugin_public , 'mark_completed' );
    $this->loader->add_action( 'wp_ajax_nopriv_mark_completed', $plugin_public , 'no_priv_mark_completed' );
		$this->loader->add_action( 'wp_ajax_mark_uncompleted', $plugin_public , 'mark_uncompleted' );
    $this->loader->add_action( 'wp_ajax_nopriv_mark_uncompleted', $plugin_public , 'no_priv_mark_uncompleted' );

		// Add shortcodes:
		$this->loader->add_shortcode( 'complete_button', $plugin_public, 'complete_button_cb' );
		$this->loader->add_shortcode( 'wpc_complete_button', $plugin_public, 'complete_button_cb' );
		$this->loader->add_shortcode( 'wpc_button', $plugin_public, 'complete_button_cb' );
		$this->loader->add_shortcode( 'wpcomplete_button', $plugin_public, 'complete_button_cb' );

		// PREMIUM:
		if (WPCOMPLETE_IS_ACTIVATED) {
			$this->loader->add_shortcode( 'progress_percentage', $plugin_public, 'progress_percentage_cb' );
			$this->loader->add_shortcode( 'progress_in_percentage', $plugin_public, 'progress_percentage_cb' );
			$this->loader->add_shortcode( 'progress_ratio', $plugin_public, 'progress_ratio_cb' );
			$this->loader->add_shortcode( 'progress_in_ratio', $plugin_public, 'progress_ratio_cb' );
			$this->loader->add_shortcode( 'progress_graph', $plugin_public, 'progress_radial_graph_cb' );
			$this->loader->add_shortcode( 'progress_bar', $plugin_public, 'progress_bar_graph_cb' );
			$this->loader->add_shortcode( 'wpc_progress_percentage', $plugin_public, 'progress_percentage_cb' );
			$this->loader->add_shortcode( 'wpc_progress_in_percentage', $plugin_public, 'progress_percentage_cb' );
			$this->loader->add_shortcode( 'wpc_progress_ratio', $plugin_public, 'progress_ratio_cb' );
			$this->loader->add_shortcode( 'wpc_progress_in_ratio', $plugin_public, 'progress_ratio_cb' );
			$this->loader->add_shortcode( 'wpc_progress_graph', $plugin_public, 'progress_radial_graph_cb' );
			$this->loader->add_shortcode( 'wpc_progress_bar', $plugin_public, 'progress_bar_graph_cb' );
			$this->loader->add_shortcode( 'wpcomplete_progress_percentage', $plugin_public, 'progress_percentage_cb' );
			$this->loader->add_shortcode( 'wpcomplete_progress_in_percentage', $plugin_public, 'progress_percentage_cb' );
			$this->loader->add_shortcode( 'wpcomplete_progress_ratio', $plugin_public, 'progress_ratio_cb' );
			$this->loader->add_shortcode( 'wpcomplete_progress_in_ratio', $plugin_public, 'progress_ratio_cb' );
			$this->loader->add_shortcode( 'wpcomplete_progress_graph', $plugin_public, 'progress_radial_graph_cb' );
			$this->loader->add_shortcode( 'wpcomplete_progress_bar', $plugin_public, 'progress_bar_graph_cb' );
			add_filter( 'widget_text', 'do_shortcode' ); // allow text widgets to render shortcodes

			// Mark links as completable and with their status if logged in:
			$this->loader->add_action( 'wp_ajax_get_completable_list', $plugin_public , 'get_completable_list' );
		  $this->loader->add_action( 'wp_ajax_nopriv_get_completable_list', $plugin_public , 'get_completable_list' );
		}
    $this->loader->add_action( 'wp_head', $plugin_public, 'append_custom_styles' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    WPComplete_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
