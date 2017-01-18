<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://wpcomplete.co
 * @since      1.0.0
 *
 * @package    WPComplete
 * @subpackage wpcomplete/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    WPComplete
 * @subpackage wpcomplete/admin
 * @author     Zack Gilbert <zack@zackgilbert.com>
 */
class WPComplete_Admin {

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
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The WPComplete_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wpcomplete-admin.css', array('wp-color-picker'), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The WPComplete_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wpcomplete-admin.js', array( 'jquery', 'jquery-ui-autocomplete', 'wp-color-picker', 'inline-edit-post' ), $this->version, true );

		wp_localize_script( $this->plugin_name, 'WPComplete', array( 'url' => admin_url( 'admin-ajax.php' ) ) );
	}

	/**
	 * Add options to the bulk menu for posts and pages.
	 *
	 * @since  1.0.0
	 */
	public function add_bulk_actions() {
		global $post_type;
 
	  if ( in_array( $post_type, $this->get_enabled_post_types() ) ) {
	    ?>
	    <script type="text/javascript">
	      jQuery(document).ready(function() {
	        jQuery('<option>').val('completable').text("<?php _e('Can Complete', $this->plugin_name)?>").appendTo("select[name='action'],select[name='action2']");
<?php 
	      	$courses = $this->get_course_names();
	      	if ( count( $courses ) > 0 ) { ?>
	      		jQuery('<option>').val('course::true').text("<?php _e('Assign to: No specific course', $this->plugin_name)?>").appendTo("select[name='action'],select[name='action2']");
<?php	     	foreach ( $this->get_course_names() as $course_name ) { ?>
	    	    	jQuery('<option>').val('course::<?php echo $course_name; ?>').text("Assign to: <?php _e($course_name, $this->plugin_name)?>").appendTo("select[name='action'],select[name='action2']");
<?php	      }
	      	} ?>
        
	      });
	    </script>
	    <?php
	  }
	}

	/**
	 * Add WPComplete specific page under the Settings submenu.
	 *
	 * @since  1.0.0
	 */
	public function add_options_page() {
	
		$this->plugin_screen_hook_suffix = add_options_page(
			__( 'WPComplete Settings', $this->plugin_name ),
			__( 'WPComplete', $this->plugin_name ),
			'manage_options',
			$this->plugin_name,
			array( $this, 'display_settings_page' )
		);
	
	}

	/**
	 * Render the WPComplete specific settings page for plugin.
	 *
	 * @since  1.0.0
	 */
	public function display_settings_page() {
		include_once 'partials/wpcomplete-admin-display.php';
	}

	/**
	 * 
	 *
	 * @since  1.4.0
	 */
	public function add_post_completion_page() {
		add_submenu_page( 
			null, 
			__( 'Post Completion', $this->plugin_name ), 
			__( 'Post Completion', $this->plugin_name ), 
			'manage_options', 
			'wpcomplete-posts', 
			array( $this, 'render_post_completion_page' )
		);
	}

	/**
	 * 
	 *
	 * @since  1.4.0
	 */
	public function render_post_completion_page() {
		global $wpdb;

		if ( ! current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		if ( ! $_GET['post_id'] ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		if ( ! WPCOMPLETE_IS_ACTIVATED ) {
			wp_die( __( 'You only get access to this data once you activate your license.' ) );
		}
		// Get post info:
		$post_id = $_GET['post_id'];
		$post = get_post($post_id);

		$selected_role = get_option( $this->plugin_name . '_role', 'subscriber' );
		// Get all users that are able to complete the post:
		$args = array('fields' => 'all');
		if ($selected_role != 'all') $args['role'] = $selected_role;
		$total_users = get_users($args);
		// Get users (ids only) that have completed the post:
		$args = array('fields' => 'id');
		if ($selected_role != 'all') $args['role'] = $selected_role;
		$args['meta_query'] = array(
			array(
				'key' => 'wp_completed',
				'value' => "\"$post_id\"",
				'compare' => 'LIKE'
			)
		);
		$user_completed_ids = get_users($args);

		if ( $user_completed_ids ) {
			// First escape the status, since we don't use it with $wpdb->prepare()    
	    $user_completed_ids = esc_sql( array_values( $user_completed_ids ) );

	    // If its an array, convert to string
	    if ( is_array( $user_completed_ids ) ){
	      $user_completed_ids = implode( ', ', $user_completed_ids );
	    }
	    // Get custom SQL to return user_id and the courses they've completed:
			$sql = $wpdb->prepare( "
	        SELECT pm.user_id, pm.meta_value FROM {$wpdb->usermeta} pm
	        WHERE ( pm.meta_key = %s ) AND ( pm.user_id IN ({$user_completed_ids}) )
	    ", 'wp_completed' );
			$r = $wpdb->get_results( $sql );
		} else {
			$r = array();
		}

		// Store array of users that have completed the post and when:
    $user_completed = array();
    foreach ($r as $user_info) {
    	$user_completed_raw = json_decode($user_info->meta_value, true);
    	// Fix if old way of storing...
    	if ( $user_completed_raw == array_values( $user_completed_raw ) ) {
				$new_array = array();
				foreach ( $user_completed_raw as $p ) {
					$new_array[ $p ] = 'Yes';
				}
				$user_completed_raw = $new_array;
			}
			if ( is_bool( $user_completed_raw[$post_id] ) ) {
				$user_completed_raw[$post_id] = 'Yes';
			}
			$user_completed[$user_info->user_id] = $user_completed_raw[$post_id];
    }

		include_once 'partials/wpcomplete-admin-post-completion.php';
	}

	/**
	 * 
	 *
	 * @since  1.4.0
	 */
	public function add_user_completion_page() {
		add_submenu_page( 
			null, 
			__( 'User Completion', $this->plugin_name ), 
			__( 'User Completion', $this->plugin_name ), 
			'manage_options', 
			'wpcomplete-users', 
			array( $this, 'render_user_completion_page' )
		);
	}

	/**
	 * 
	 *
	 * @since  1.4.0
	 */
	public function render_user_completion_page() {
		if ( ! current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		if ( ! $_GET['user_id'] ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		if ( ! WPCOMPLETE_IS_ACTIVATED ) {
			wp_die( __( 'You only get access to this data once you activate your license.' ) );
		}

		$user_id = $_GET['user_id'];
		$total_posts = $this->get_meta_posts('completable');
			
		$user_completed_json = get_user_meta( $user_id, 'wp_completed', true );
		$user_completed = ( $user_completed_json ) ? json_decode( $user_completed_json, true ) : array();

		$user = get_userdata( $user_id );
  	// Fix if old way of storing...
		if ( $user_completed == array_values( $user_completed ) ) {
			$new_array = array();
			foreach ( $user_completed as $p ) {
				$new_array[ $p ] = 'Yes';
			}
			$user_completed = $new_array;
		}

		foreach ( $total_posts as $post_id ) {
			if ( isset($user_completed[$post_id]) && is_bool( $user_completed[$post_id] ) ) {
				$user_completed[$post_id] = 'Yes';
			}
		}

		include_once 'partials/wpcomplete-admin-user-completion.php';
	}

	/**
	 * Build all the settings for plugin on the WPComplete settings page.
	 *
	 * @since  1.0.0
	 */
	public function register_settings() {
		// PREMIUM:
		register_setting( $this->plugin_name, $this->plugin_name . '_license_key', array( $this, 'sanitize_license' ) );

		// Section related to students:
		add_settings_section(
			$this->plugin_name . '_students',
			__( 'General Settings', $this->plugin_name ),
			array( $this, 'settings_section_cb' ),
			$this->plugin_name
		);

		add_settings_field(
			$this->plugin_name . '_role',
			__( 'Student Role Type', $this->plugin_name ),
			array( $this, 'settings_role_cb' ),
			$this->plugin_name,
			$this->plugin_name . '_students',
			array( 'label_for' => $this->plugin_name . '_role' )
		);
		if (WPCOMPLETE_IS_ACTIVATED)
			register_setting( $this->plugin_name, $this->plugin_name . '_role', 'sanitize_text_field' );

		add_settings_field(
			$this->plugin_name . '_post_type',
			__( 'Lesson Content Type', $this->plugin_name ),
			array( $this, 'settings_post_type_cb' ),
			$this->plugin_name,
			$this->plugin_name . '_students',
			array( 'label_for' => $this->plugin_name . '_post_type' )
		);
		if (WPCOMPLETE_IS_ACTIVATED)
			register_setting( $this->plugin_name, $this->plugin_name . '_post_type', 'sanitize_text_field' );

		add_settings_field(
			$this->plugin_name . '_auto_append',
			'',
			array( $this, 'settings_auto_append_cb' ),
			$this->plugin_name,
			$this->plugin_name . '_students',
			array()
		);
		register_setting( $this->plugin_name, $this->plugin_name . '_auto_append', 'sanitize_text_field' );

		// Section related to the Mark as Complete button:
		add_settings_section(
			$this->plugin_name . '_incomplete_button',
			__( 'Mark Complete Button', $this->plugin_name ),
			array( $this, 'settings_section_cb' ),
			$this->plugin_name
		);

		add_settings_field(
			$this->plugin_name . '_incomplete_text',
			__( 'Button Text', $this->plugin_name ),
			array( $this, 'settings_incomplete_text_cb' ),
			$this->plugin_name,
			$this->plugin_name . '_incomplete_button',
			array( 'label_for' => $this->plugin_name . '_incomplete_text' )
		);
		register_setting( $this->plugin_name, $this->plugin_name . '_incomplete_text', 'sanitize_text_field' );

		add_settings_field(
			$this->plugin_name . '_incomplete_active_text',
			__( 'Saving Text', $this->plugin_name ),
			array( $this, 'settings_incomplete_active_text_cb' ),
			$this->plugin_name,
			$this->plugin_name . '_incomplete_button',
			array( 'label_for' => $this->plugin_name . '_incomplete_active_text' )
		);
		if (WPCOMPLETE_IS_ACTIVATED)
			register_setting( $this->plugin_name, $this->plugin_name . '_incomplete_active_text', 'sanitize_text_field' );

		add_settings_field(
			$this->plugin_name . '_incomplete_background',
			__( 'Button Color', $this->plugin_name ),
			array( $this, 'settings_incomplete_background_cb' ),
			$this->plugin_name,
			$this->plugin_name . '_incomplete_button',
			array( 'label_for' => $this->plugin_name . '_incomplete_background' )
		);
		if (WPCOMPLETE_IS_ACTIVATED)
			register_setting( $this->plugin_name, $this->plugin_name . '_incomplete_background', 'sanitize_text_field' );

		add_settings_field(
			$this->plugin_name . '_incomplete_color',
			__( 'Button Text Color', $this->plugin_name ),
			array( $this, 'settings_incomplete_color_cb' ),
			$this->plugin_name,
			$this->plugin_name . '_incomplete_button',
			array( 'label_for' => $this->plugin_name . '_incomplete_color' )
		);
		if (WPCOMPLETE_IS_ACTIVATED)
			register_setting( $this->plugin_name, $this->plugin_name . '_incomplete_color', 'sanitize_text_field' );

		// Section related to the Completed! button:
		add_settings_section(
			$this->plugin_name . '_completed_button',
			__( 'Completed Button', $this->plugin_name ),
			array( $this, 'settings_section_cb' ),
			$this->plugin_name
		);

		add_settings_field(
			$this->plugin_name . '_completed_text',
			__( 'Button Text', $this->plugin_name ),
			array( $this, 'settings_completed_text_cb' ),
			$this->plugin_name,
			$this->plugin_name . '_completed_button',
			array( 'label_for' => $this->plugin_name . '_completed_text' )
		);
		register_setting( $this->plugin_name, $this->plugin_name . '_completed_text', 'sanitize_text_field' );

		add_settings_field(
			$this->plugin_name . '_completed_active_text',
			__( 'Saving Text', $this->plugin_name ),
			array( $this, 'settings_completed_active_text_cb' ),
			$this->plugin_name,
			$this->plugin_name . '_completed_button',
			array( 'label_for' => $this->plugin_name . '_completed_active_text' )
		);
		if (WPCOMPLETE_IS_ACTIVATED)
			register_setting( $this->plugin_name, $this->plugin_name . '_completed_active_text', 'sanitize_text_field' );

		add_settings_field(
			$this->plugin_name . '_completed_background',
			__( 'Button Color', $this->plugin_name ),
			array( $this, 'settings_completed_background_cb' ),
			$this->plugin_name,
			$this->plugin_name . '_completed_button',
			array( 'label_for' => $this->plugin_name . '_completed_background' )
		);
		if (WPCOMPLETE_IS_ACTIVATED)
			register_setting( $this->plugin_name, $this->plugin_name . '_completed_background', 'sanitize_text_field' );

		add_settings_field(
			$this->plugin_name . '_completed_color',
			__( 'Button Text Color', $this->plugin_name ),
			array( $this, 'settings_completed_color_cb' ),
			$this->plugin_name,
			$this->plugin_name . '_completed_button',
			array( 'label_for' => $this->plugin_name . '_completed_color' )
		);
		if (WPCOMPLETE_IS_ACTIVATED)
			register_setting( $this->plugin_name, $this->plugin_name . '_completed_color', 'sanitize_text_field' );

		// PREMIUM: Section related to the graphs:
		add_settings_section(
			$this->plugin_name . '_graphs',
			__( 'Graph Settings', $this->plugin_name ),
			array( $this, 'settings_section_cb' ),
			$this->plugin_name
		);

		add_settings_field(
			$this->plugin_name . '_graph_primary',
			__( 'Primary Color', $this->plugin_name ),
			array( $this, 'settings_graph_primary_cb' ),
			$this->plugin_name,
			$this->plugin_name . '_graphs',
			array( 'label_for' => $this->plugin_name . '_graph_primary' )
		);
		if (WPCOMPLETE_IS_ACTIVATED)
			register_setting( $this->plugin_name, $this->plugin_name . '_graph_primary', 'sanitize_text_field' );

		add_settings_field(
			$this->plugin_name . '_graph_secondary',
			__( 'Secondary Color', $this->plugin_name ),
			array( $this, 'settings_graph_secondary_cb' ),
			$this->plugin_name,
			$this->plugin_name . '_graphs',
			array( 'label_for' => $this->plugin_name . '_graph_secondary' )
		);
		if (WPCOMPLETE_IS_ACTIVATED)
			register_setting( $this->plugin_name, $this->plugin_name . '_graph_secondary', 'sanitize_text_field' );

		// PREMIUM: Section related to advanced features:
		add_settings_section(
			$this->plugin_name . '_advanced',
			__( 'Advanced Settings', $this->plugin_name ),
			array( $this, 'settings_section_cb' ),
			$this->plugin_name
		);

		add_settings_field(
			$this->plugin_name . '_custom_styles',
			__( 'Custom Styles (CSS)', $this->plugin_name ),
			array( $this, 'settings_custom_styles_cb' ),
			$this->plugin_name,
			$this->plugin_name . '_advanced',
			array( 'label_for' => $this->plugin_name . '_custom_styles' )
		);
		if (WPCOMPLETE_IS_ACTIVATED)
			register_setting( $this->plugin_name, $this->plugin_name . '_custom_styles', 'sanitize_text_field' );
	}

	/**
	 * Render extra text for sections.
	 *
	 * @since  1.0.0
	 */
	public function settings_section_cb() {
	}

	/**
	 * Sanitation helper for license field.
	 *
	 * @since  1.0.0
	 */
	public function sanitize_license( $new ) {
		$old = get_option( $this->plugin_name . '_license_key' );
		if ( $old && $old != $new ) {
			delete_option( $this->plugin_name . '_license_status' ); // new license has been entered, so must reactivate
			wp_cache_delete( $this->plugin_name . '_license_status' );
		}
		return $new;
	}

	/**
	 * Render select menu for assigning which type of user roles should be tracked as students.
	 *
	 * @since  1.0.0
	 */
	public function settings_role_cb() {
		$selected_role = get_option( $this->plugin_name . '_role', 'subscriber' );
		$disabled = !WPCOMPLETE_IS_ACTIVATED;

		include 'partials/wpcomplete-admin-settings-role.php';
	}

	/**
	 * Render select menu for assigning which type of user roles should be tracked as students.
	 *
	 * @since  1.0.3
	 */
	public function settings_post_type_cb() {
		$selected_type = get_option( $this->plugin_name . '_post_type', 'page_post' );
		$disabled = !WPCOMPLETE_IS_ACTIVATED;

		include 'partials/wpcomplete-admin-settings-post-type.php';
	}

	/**
	 * Render checkbox for if should attempt to append [complete_button] shortcode if not found
	 *
	 * @since  1.3.0
	 */
	public function settings_auto_append_cb() {
		$is_enabled = get_option( $this->plugin_name . '_auto_append', 'true' );
		$disabled = false;
		include 'partials/wpcomplete-admin-settings-auto-append.php';
	}

	/**
	 * Render the Mark as Complete button text setting field.
	 *
	 * @since  1.0.0
	 */
	public function settings_incomplete_text_cb() {
		$name = $this->plugin_name . '_incomplete_text';
		$text = get_option( $name, 'Mark as complete' );
		$class = '';
		$disabled = false;

		include 'partials/wpcomplete-admin-settings-input.php';
	}

	/**
	 * Render the Mark as Complete button active text setting field.
	 *
	 * @since  1.4.7
	 */
	public function settings_incomplete_active_text_cb() {
		$name = $this->plugin_name . '_incomplete_active_text';
		$text = get_option( $name, 'Saving...' );
		$class = '';
		$disabled = false;

		include 'partials/wpcomplete-admin-settings-input.php';
	}

	/**
	 * Render the Mark as Complete button background color setting field.
	 *
	 * @since  1.0.0
	 */
	public function settings_incomplete_background_cb() {
		$name = $this->plugin_name . '_incomplete_background';
		$text = get_option( $name, '#ff0000' );
		$class = 'color-picker';
		$disabled = !WPCOMPLETE_IS_ACTIVATED;

		include 'partials/wpcomplete-admin-settings-input.php';
	}

	/**
	 * Render the Mark as Complete button text color setting field.
	 *
	 * @since  1.0.0
	 */
	public function settings_incomplete_color_cb() {
		$name = $this->plugin_name . '_incomplete_color';
		$text = get_option( $name, '#ffffff' );
		$class = 'color-picker';
		$disabled = !WPCOMPLETE_IS_ACTIVATED;

		include 'partials/wpcomplete-admin-settings-input.php';
	}

	/**
	 * Render the Completed! button text setting field.
	 *
	 * @since  1.0.0
	 */
	public function settings_completed_text_cb() {
		$name = $this->plugin_name . '_completed_text';
		$text = get_option( $name, 'COMPLETED!' );
		$class = '';
		$disabled = false;

		include 'partials/wpcomplete-admin-settings-input.php';
	}

	/**
	 * Render the Completed! button active text setting field.
	 *
	 * @since  1.4.7
	 */
	public function settings_completed_active_text_cb() {
		$name = $this->plugin_name . '_completed_active_text';
		$text = get_option( $name, 'Saving...' );
		$class = '';
		$disabled = false;

		include 'partials/wpcomplete-admin-settings-input.php';
	}

	/**
	 * Render the Completed! button background color setting field.
	 *
	 * @since  1.0.0
	 */
	public function settings_completed_background_cb() {
		$name = $this->plugin_name . '_completed_background';
		$text = get_option( $name, '#666666' );
		$class = 'color-picker';
		$disabled = !WPCOMPLETE_IS_ACTIVATED;

		include 'partials/wpcomplete-admin-settings-input.php';
	}

	/**
	 * Render the Completed! button text color setting field.
	 *
	 * @since  1.0.0
	 */
	public function settings_completed_color_cb() {
		$name = $this->plugin_name . '_completed_color';
		$text = get_option( $name, '#ffffff' );
		$class = 'color-picker';
		$disabled = !WPCOMPLETE_IS_ACTIVATED;

		include 'partials/wpcomplete-admin-settings-input.php';
	}

	/**
	 * PREMIUM:
	 * Render graph primary color setting field.
	 *
	 * @since  1.0.0
	 */
	public function settings_graph_primary_cb() {
		$name = $this->plugin_name . '_graph_primary';
		$text = get_option( $name, '#97a71d' );
		$class = 'color-picker';
		$disabled = !WPCOMPLETE_IS_ACTIVATED;

		include 'partials/wpcomplete-admin-settings-input.php';
	}

	/**
	 * PREMIUM:
	 * Render graph secondary color setting field.
	 *
	 * @since  1.0.0
	 */
	public function settings_graph_secondary_cb() {
		$name = $this->plugin_name . '_graph_secondary';
		$text = get_option( $name, '#ebebeb' );
		$class = 'color-picker';
		$disabled = !WPCOMPLETE_IS_ACTIVATED;

		include 'partials/wpcomplete-admin-settings-input.php';
	}

	/**
	 * PREMIUM:
	 * Render textarea for custom styles.
	 *
	 * @since  1.2.0
	 */
	public function settings_custom_styles_cb() {
		$name = $this->plugin_name . '_custom_styles';
		$default = '
li .wpc-lesson {} li .wpc-lesson-complete {} li .wpc-lesson-completed { opacity: .5; } li .wpc-lesson-completed:after { content: "✔"; margin-left: 5px; }
';
		$text = get_option( $name, $default );
		if ( empty( $text ) ) {
			$text = '
.wpc-lesson {} li .wpc-lesson-complete {} li .wpc-lesson-completed {}
';
		}
		$text = str_replace("} ", "}\n", $text);
		$disabled = !WPCOMPLETE_IS_ACTIVATED;

		include 'partials/wpcomplete-admin-settings-textarea.php';
	}


	/* END SETTINGS PAGE HELPERS */

	/**
	 * Render the meta box for this plugin enabling completion functionality
	 *
	 * @since  1.0.0
	 */
	public function add_completable_metabox() {
		$screens = $this->get_enabled_post_types();

		foreach ( $screens as $screen ) {
      add_meta_box(
        'completable',         												 // Unique ID
        __( 'WPComplete', $this->plugin_name ),      	 // Box title
        array( $this, 'add_completable_metabox_cb' ),  // Content callback
        $screen                      									 // post type
      );
    }
	}

	/**
	 * Callback which renders the actual html for completable metabox. Includes enabling completability and redirect url.
	 *
	 * @since  1.0.0
	 */
	public function add_completable_metabox_cb( $post ) {
		// get the variables we need to build the form:
 		$completable = get_post_meta( $post->ID, 'completable', true );
 		$redirect_json = get_post_meta( $post->ID, 'completion-redirect', true );
 		$redirect = ($redirect_json && !empty($redirect_json)) ? (array) json_decode($redirect_json) : array('title' => '', 'url' => '');
 		// include a nonce to ensure we can save:
  	wp_nonce_field( $this->plugin_name, 'completable_nonce' );

 		include 'partials/wpcomplete-admin-metabox.php';
	}

	/**
	 * PREMIUM: 
	 * Script used to activate license keys.
	 *
	 * @since  1.0.0
	 */
	public function activate_license() {
		// Clear cache...
		delete_transient( WPCOMPLETE_PREFIX . '_license_status' );
		// listen for our activate button to be clicked
		if ( isset( $_POST[$this->plugin_name . '_license_activate'] ) ) {
			// run a quick security check 
		 	//if ( ! check_admin_referer( $this->plugin_name . '_license_nonce', $this->plugin_name . '_license_nonce' ) ) 	
			//	return; // get out if we didn't click the Activate button

			// retrieve the license from the database
			$license = trim( $_POST[ $this->plugin_name . '_license_key'] );

			// If posted license isn't the same as what's stored, store it.
			$current = get_option( $this->plugin_name . '_license_key');
			if ( $current != $license ) {
				update_option( $this->plugin_name . '_license_key', $license);
			}
				
			// data to send in our API request
			$api_params = array( 
				'edd_action' => 'activate_license', 
				'license' 	 => $license, 
				'item_name'  => urlencode( WPCOMPLETE_PRODUCT_NAME ),
				'url'        => home_url()
			);
			
			// Call the custom API.
			$response = wp_remote_post( WPCOMPLETE_STORE_URL, array(
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => $api_params
			) );

			$message = '';
			// make sure the response came back okay
			if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
				$message =  ( is_wp_error( $response ) && $response->get_error_message() ) ? $response->get_error_message() : __( 'An error occurred, please try again.' );
			} else {
				$license_data = json_decode( wp_remote_retrieve_body( $response ) );
				if ( false === $license_data->success ) {
					switch( $license_data->error ) {
						case 'expired' :
							$message = sprintf(
								__( 'Your license key expired on %s.' ),
								date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
							);
							break;
						case 'revoked' :
							$message = __( 'Your license key has been disabled.' );
							break;
						case 'missing' :
							$message = __( 'Invalid license.' );
							break;
						case 'invalid' :
						case 'site_inactive' :
							$message = __( 'Your license is not active for this URL.' );
							break;
						case 'item_name_mismatch' :
							$message = sprintf( __( 'This appears to be an invalid license key for %s.' ), WPCOMPLETE_PRODUCT_NAME );
							break;
						case 'no_activations_left':
							$message = __( 'Your license key has reached its activation limit.' );
							break;
						default :
							$message = __( 'An error occurred, please try again.' );
							break;
					}
				}
			}
			// Check if anything passed on a message constituting a failure
			if ( ! empty( $message ) ) {
				$base_url = admin_url( 'options-general.php?page=' . $this->plugin_name );
				$redirect = add_query_arg( array( 'sl_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );
				wp_redirect( $redirect );
				exit();
			}

			update_option( $this->plugin_name . '_license_status', $license_data->expires );
			wp_redirect( admin_url( 'options-general.php?page=' . $this->plugin_name ) );
			exit();

		}
	}

	/**
	 * Save script for saving an individual post/page, enabling it as completable
	 * PREMIUM: and custom redirect url.
	 *
	 * @since  1.0.0
	 */
	public function save_completable( $post_id ) {
		if ( isset( $_POST['completable_nonce'] ) && isset( $_POST['post_type'] ) && isset( $_POST['wpcomplete'] ) && isset( $_POST['wpcomplete']['completable'] ) ) {
      if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
      	//echo '<!-- Autosave -->';
        return;
      } // end if

      // Verify that the input is coming from the proper form
      if ( ! wp_verify_nonce( $_POST['completable_nonce'], $this->plugin_name ) ) {
      	//echo '<!-- NONCE FAILED -->';
        return;
      } // end if

      // Make sure the user has permissions to posts and pages
      if ( ! in_array( $_POST['post_type'], $this->get_enabled_post_types() ) ) {
      	// echo '<!-- Post type isn\'t allowed to be marked as completable -->';
      	return;
      }

      $is_completable = $_POST['wpcomplete']['completable'];
      // PREMIUM:
      $course_name = ( isset( $_POST['wpcomplete']['course-custom'] ) ) ? $_POST['wpcomplete']['course-custom'] : $_POST['wpcomplete']['course'];
      if ( empty( $course_name ) ) $course_name = 'true';
      $redirect_to = $_POST['wpcomplete']['completion-redirect-to'];
      $redirect_url = $_POST['wpcomplete']['completion-redirect-url'];
      $redirect = array('title' => $redirect_to, 'url' => $redirect_url);

      if ($is_completable == 'true') {

	      // Update it for this post.
	      update_post_meta( $post_id, 'completable', $course_name );

	      // PREMIUM: handle redirect url...
	      if (empty($redirect_to)) {
	        delete_post_meta( $post_id, 'completion-redirect' );
	      } else {
		      update_post_meta( $post_id, 'completion-redirect', json_encode( $redirect ) );
	      }

      } else {

      	// If the value exists, delete it.
        delete_post_meta( $post_id, 'completable' );
	    
      }
 
    }
	}

	/**
	 * Save script for the bulk action that marks multiple pages/posts as completable.
	 *
	 * @since  1.0.0
	 */
	public function save_bulk_completable() {
		global $typenow;
		$post_type = $typenow;

		if ( in_array( $post_type, $this->get_enabled_post_types() ) && isset($_REQUEST['post']) ) {
		  if ( (($_REQUEST['action'] == 'completable') || ($_REQUEST['action2'] == 'completable')) ) {
				// security check
				check_admin_referer( 'bulk-posts' );

				$action = ($_REQUEST['action'] == '-1') ? $_REQUEST['action2'] : $_REQUEST['action'];
				
				// make sure ids are submitted.  depending on the resource type, this may be 'media' or 'ids'
				if ( isset($_REQUEST['post'] ) ) {
					$post_ids = array_map( 'intval', $_REQUEST['post'] );
				}
				
				if ( empty( $post_ids ) ) return;

				// this is based on wp-admin/edit.php
				$sendback = remove_query_arg( array('exported', 'untrashed', 'deleted', 'ids'), wp_get_referer() );
				if ( ! $sendback )
					$sendback = admin_url( "edit.php?post_type=$post_type" );			

				// do the marking as complete!
				$marked = 0;
				foreach ( $post_ids as $post_id ) {
					// Update it for this post.
		      update_post_meta( $post_id, 'completable', 'true' );
		      $marked++;
				}

				$sendback = add_query_arg( array('completable' => $marked, 'ids' => join(',', $post_ids) ), $sendback );
				$sendback = remove_query_arg( array('action', 'action2', 'tags_input', 'post_author', 'comment_status', 'ping_status', '_status',  'post', 'bulk_edit', 'post_view'), $sendback );

				wp_redirect( $sendback );
				exit();
			} else if ( ( (substr($_REQUEST['action'], 0, strlen('course::')) == 'course::') || (substr($_REQUEST['action2'], 0, strlen('course::')) == 'course::') ) ) {
				// security check
				check_admin_referer( 'bulk-posts' );

				$action = ($_REQUEST['action'] == '-1') ? $_REQUEST['action2'] : $_REQUEST['action'];
				list($action, $course_name) = explode("::", $action);
				
				// make sure ids are submitted.  depending on the resource type, this may be 'media' or 'ids'
				if ( isset($_REQUEST['post'] ) ) {
					$post_ids = array_map( 'intval', $_REQUEST['post'] );
				}
				
				if ( empty( $post_ids ) ) return;

				// this is based on wp-admin/edit.php
				$sendback = remove_query_arg( array('exported', 'untrashed', 'deleted', 'ids'), wp_get_referer() );
				if ( ! $sendback )
					$sendback = admin_url( "edit.php?post_type=$post_type" );			

				// do the marking as complete!
				$marked = 0;
				foreach ( $post_ids as $post_id ) {
					// Update it for this post.
		      update_post_meta( $post_id, 'completable', $course_name );
		      $marked++;
				}

				$sendback = add_query_arg( array('course' => $marked, 'ids' => join(',', $post_ids) ), $sendback );
				$sendback = remove_query_arg( array('action', 'action2', 'tags_input', 'post_author', 'comment_status', 'ping_status', '_status',  'post', 'bulk_edit', 'post_view'), $sendback );

				wp_redirect( $sendback );
				exit();
			}
		}
	}

	/**
	 * Add a notice message for completed bulk actions.
	 *
	 * @since  1.0.0
	 */
	public function show_bulk_action_notice() {
		global $post_type, $pagenow;
 
	  if ( $pagenow == 'edit.php' && in_array( $post_type, $this->get_enabled_post_types() ) && isset($_REQUEST['completable']) && (int) $_REQUEST['completable']) {
	    $message = sprintf( _n( 'Post marked completable by students.', '%s posts marked as completable by students.', $_REQUEST['completable'] ), number_format_i18n( $_REQUEST['completable'] ) );
	    echo "<div class=\"updated\"><p>{$message}</p></div>";
	  } else if ( $pagenow == 'edit.php' && in_array( $post_type, $this->get_enabled_post_types() ) && isset($_REQUEST['course']) && (int) $_REQUEST['course']) {
	    $message = sprintf( _n( 'Post assigned to course.', '%s posts assigned to course.', $_REQUEST['course'] ), number_format_i18n( $_REQUEST['course'] ) );
	    echo "<div class=\"updated\"><p>{$message}</p></div>";
	  }
	}

	/**
	 * PREMIUM:
	 * If the license has not been configured properly, display an admin notice.
	 *
	 * @since  1.0.0
	 */
	public function show_license_notice() {
		global $pagenow;

		if ( !WPCOMPLETE_IS_ACTIVATED ) {
 			$msg = __( 'Please activate your license key to enable all WPComplete PRO features.', $this->plugin_name );

      include 'partials/wpcomplete-admin-license-notice.php'; 
	 	}
	}

	/**
	 * Add the new custom column header, "User Completion" to pages and posts edit.php page.
	 *
	 * @since  1.0.0
	 */
	public function add_custom_column_header( $columns ) {
		global $post_type;

		if (!$post_type) $post_type = $_POST['post_type'];

		if ( in_array( $post_type, $this->get_enabled_post_types() ) ) {

			if ( count( $this->get_course_names() ) > 0 ) {
				$columns = array_merge( $columns, array( 'completable-course' => __( 'Course Name', $this->plugin_name) ));
			}

			$columns = array_merge( $columns, array( 'completable' => __( 'User Completion', $this->plugin_name) ));
		}

		return $columns;
	}

	/**
	 * Add the values for each post/page of the new custom "Completion %" column.
	 * If post/page isn't enabled to be completed, it shows — in column.
	 * If wordpress install doesn't have any subscribers (students), it shows "0 Students".
	 * Otherwise, it'll show the ratio and percentage of how many students have completed it.
	 *
	 * @since  1.0.0
	 */
	public function add_custom_column_value( $column_name, $post_id ) {
		if ( $column_name == 'completable-course' ) {
			if ( $completable = get_post_meta( $post_id, 'completable', true ) ) {
				$course_name = ($completable == 'true') ? 'No specific course' : $completable;
			} else {
				$course_name = '—';
			}

			echo '<div id="completable-course-' . $post_id . '">' . $course_name . '</div>';

		} else if ( $column_name == 'completable' ) {
			$completable = ((get_post_meta( $post_id, 'completable', true )) ? 'true' : 'false');

			if ($completable == 'true') {
				$users_of_blog = count_users();
				$selected_role = get_option( $this->plugin_name . '_role', 'subscriber' );

				if ( $selected_role == 'all' ) {
					$avail_users = $users_of_blog['total_users'];
				} else {
					$avail_users = ( isset( $users_of_blog['avail_roles'][$selected_role] ) ) ? $users_of_blog['avail_roles'][$selected_role] : 0;
				}

				if ($avail_users > 0) {
					$completion = '';
					$args = array('fields' => 'id');
					
					if ($selected_role != 'all') $args['role'] = $selected_role;
					$args['meta_query'] = array(
						array(
							'key' => 'wp_completed',
							'value' => "\"$post_id\"",
							'compare' => 'LIKE'
						)
					);

					$users = get_users($args);

					$completed_users = count($users);

					$completion = ("$completed_users/$avail_users");
					// - Display percentage
					$completion .= (' (' . round(100 * ($completed_users / $avail_users), 1) . '%)');
				} else {
					$completion = "0 Students";
				}
				echo '<div id="completable-' . $post_id . '"><a href="edit.php?page=wpcomplete-posts&post_id=' . $post_id . '">' . $completion . '</a></div>';
			} else {
				echo '<div id="completable-' . $post_id . '">—</div>';
			}
		}
	}

	/**
	 * Add custom field for quick edit of posts and pages.
	 *
	 * @since  1.0.0
	 */
	public function add_custom_quick_edit( $column_name, $post_type ) {
		if ( in_array( $post_type, $this->get_enabled_post_types() ) ) {
	 		include 'partials/wpcomplete-admin-quickedit.php';
	 	}
	}

	/**
	 * PREMIUM:
	 * Autocomplete ajax lookup function. Given search criteria, returns matching posts and pages.
	 *
	 * @since  1.0.0
	 */
	public function post_lookup() {
		// TODO: don't include current page in returned results.
		$term = strtolower( $_GET['term'] );
		$suggestions = array();
		// We want to allow redirect to ANY post type on completion, not just enabled ones:
		$args = array('s' => $term);

		$loop = new WP_Query( $args );
		
		while ( $loop->have_posts() ) {
			$loop->the_post();
			$suggestion = array();
			$suggestion['label'] = get_the_title() . " (" . ucwords(str_replace("_", " ", get_post_type( get_the_ID() ))) . " #" . get_the_ID() . ")";
			$suggestion['link'] = get_permalink();
			
			$suggestions[] = $suggestion;
		}
		
		wp_reset_query();
    	
    	
    $response = json_encode( $suggestions );
    echo $response;
    exit();
	}

	/**
	 * Helper method to query for all posts that are completable.
	 *
	 * @since    1.0.0
	 */
	public function get_meta_posts( $key = '', $status = 'publish' ) {
    global $wpdb;

    if( empty( $key ) )
        return;

    $r = $wpdb->get_col( $wpdb->prepare( "
        SELECT pm.post_id FROM {$wpdb->postmeta} pm
        LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
        WHERE pm.meta_key = '%s' 
        AND p.post_status = '%s' 
        AND (p.post_type = '" . join("' OR p.post_type = '", $this->get_enabled_post_types()) . "')
    ", $key, $status) );

    return $r;
	}

	/**
	 * Add the new custom column header, "Lesson Completion" to users page.
	 *
	 * @since  1.0.0
	 */
	public function add_user_column_header( $columns ) {
		if ( count($this->get_meta_posts('completable')) > 0 ) {
			return array_merge( $columns, array( 'completable' => __( 'Lesson Completion', $this->plugin_name) ));
		} else {
			return $columns;
		}
	}

	/**
	 * Add the values for each user of the new custom "Completion" column.
	 * If user is not in a student role, it shows — in column.
	 * Otherwise, it'll show the ratio and percentage of student's completion.
	 *
	 * @since  1.0.0
	 */
	public function add_user_column_value( $value, $column_name, $user_id ) {
		if ( $column_name == 'completable' ) {
			$selected_role = get_option( $this->plugin_name . '_role', 'subscriber' );
			$user = get_userdata( $user_id );

			if (($selected_role == 'all') || in_array($selected_role, $user->roles)) {
				$total_posts = count($this->get_meta_posts('completable'));
			
				$user_completed_json = get_user_meta( $user_id, 'wp_completed', true );				
				$user_completed = ( $user_completed_json ) ? json_decode( $user_completed_json, true ) : array();

				$completed_posts = count($user_completed);

				return '<div id="completable-' . $user_id . '"><a href="users.php?page=wpcomplete-users&user_id=' . $user_id . '">' . $completed_posts . '/' . $total_posts . ' (' . round(100 * ($completed_posts / $total_posts), 1) . '%)</a></div>';
			} else {
				return '<div id="completable-' . $user_id . '">—</div>';
			}
			
		}
	}

	/**
	 * Returns an array of all wordpress post types that can be completed. This includes custom types.
	 *
	 * @since  1.1.0
	 */
	public function get_enabled_post_types() {
		$post_type = get_option( $this->plugin_name . '_post_type', 'page_post' );
		if ( $post_type == 'page_post' ) {
			$screens = array();
			$screens['post'] = 'post';
			$screens['page'] = 'page';
		} else if ( $post_type == 'all' ) {
			$screens = get_post_types( array( '_builtin' => false ) );
			$screens['post'] = 'post';
			$screens['page'] = 'page';
		} else {
			$screens = array( $post_type );
		}
		return $screens;
	}

	/**
	 * Returns an array of all specific courses that have been added to the database.
	 *
	 * @since  1.4.0
	 */
	public function get_course_names() {
		global $wpdb;

    $r = $wpdb->get_col( $wpdb->prepare( "
        SELECT DISTINCT pm.meta_value FROM {$wpdb->postmeta} pm
        WHERE pm.meta_key = '%s' 
        AND pm.meta_value != 'true'
    ", 'completable') );

    return $r;
	}

}
