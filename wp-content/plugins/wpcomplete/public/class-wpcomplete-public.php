<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://wpcomplete.co
 * @since      1.0.0
 *
 * @package    WPComplete
 * @subpackage wpcomplete/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    WPComplete
 * @subpackage wpcomplete/public
 * @author     Zack Gilbert <zack@zackgilbert.com>
 */
class WPComplete_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wpcomplete-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wpcomplete-public.js', array( 'jquery' ), $this->version, false );

		$completion_nonce = wp_create_nonce( 'completion' );
		wp_localize_script( $this->plugin_name, 'wpcompletable', array( 
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => $completion_nonce
		) );

	}

	/**
	 * Register the shortcode for [complete_button] for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function complete_button_cb($atts, $content = null, $tag = '') {
		if ( ! is_user_logged_in() ) return; // should replace with button redirect to signup
		if ( isset( $atts['id'] ) && !empty( $atts['id'] ) ) {
			$post_id = $atts['id'];
		} else {
			$post_id = get_the_ID();
		}
		if ( ! in_array( get_post_type( $post_id ), $this->get_enabled_post_types() ) ) return;
		if ( ! get_post_meta($post_id, 'completable', true) ) return;
		// Make sure we have a well formed array of user's data
		$user_completed = $this->get_user_completed();

		ob_start();
		if ( isset( $user_completed[ $post_id ] ) ) {
			include 'partials/wpcomplete-public-completed-button.php';
		} else {
			include 'partials/wpcomplete-public-incomplete-button.php';
		}
    return ob_get_clean();
	}

	/**
	 * PREMIUM:
	 * Register the shortcode for [progress_in_percentage] for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function progress_percentage_cb($atts, $content = null, $tag = '') {
		if ( ! is_user_logged_in() ) return;
		// normalize attribute keys, lowercase
    $atts = array_change_key_case((array)$atts, CASE_LOWER);
    // find the current course's
    if ( ( !isset( $atts['course'] ) || empty( $atts['course'] ) ) && is_numeric( get_the_ID() ) ) {
	    $post_course = get_post_meta(get_the_ID(), 'completable', true);
  	  if ( $post_course && ( $post_course !== 'true' ) ) $atts['course'] = $post_course;
    }
		$user_completed = $this->get_user_completed();

		$percentage = $this->get_percentage($user_completed, $atts);

		return '<span class="wpcomplete-progress-percentage ' . $this->get_course_class($atts) . '">' . $percentage . "%" . '</span>';
	}

	/**
	 * PREMIUM:
	 * Register the shortcode for [progress_ratio] for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function progress_ratio_cb($atts, $content = null, $tag = '') {
		if ( ! is_user_logged_in() ) return;
		// normalize attribute keys, lowercase
    $atts = array_change_key_case((array)$atts, CASE_LOWER);
    // find the current course's
    if ( ( !isset( $atts['course'] ) || empty( $atts['course'] ) ) && is_numeric( get_the_ID() ) ) {
	    $post_course = get_post_meta(get_the_ID(), 'completable', true);
  	  if ( $post_course && ( $post_course !== 'true' ) ) $atts['course'] = $post_course;
    }
		$user_completed = $this->get_user_completed();

 		// check if we are looking for a specific course's progress:
    if ( isset( $atts['course'] ) && !empty( $atts['course'] ) && ( $atts['course'] != 'all' ) ) {
			$total_posts = $this->get_meta_posts('completable', $atts['course']);
    } else {
			$total_posts = $this->get_meta_posts('completable');
			if ( count($total_posts) <= 0 ) return;
    }

		$completed_posts = array_intersect( $total_posts, array_keys( $user_completed ) );

		return '<span class="wpcomplete-progress-ratio ' . $this->get_course_class($atts) . '">' . count($completed_posts) . "/" . count($total_posts) . '</span>';
	}

	/**
	 * PREMIUM:
	 * Register the shortcode for [progress_graph] for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function progress_radial_graph_cb($atts, $content = null, $tag = '') {
		if ( ! is_user_logged_in() ) return;
		// normalize attribute keys, lowercase
    $atts = array_change_key_case((array)$atts, CASE_LOWER);
    // find the current course's
    if ( ( !isset( $atts['course'] ) || empty( $atts['course'] ) ) && is_numeric( get_the_ID() ) ) {
	    $post_course = get_post_meta(get_the_ID(), 'completable', true);
  	  if ( $post_course && ( $post_course !== 'true' ) ) $atts['course'] = $post_course;
    }
		$user_completed = $this->get_user_completed();
		
		$percentage = $this->get_percentage($user_completed, $atts);
		
		ob_start();
		include 'partials/wpcomplete-public-radial-graph.php';
    return ob_get_clean();
	}

	/**
	 * PREMIUM:
	 * Register the shortcode for [progress_graph] for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function progress_bar_graph_cb($atts, $content = null, $tag = '') {
		if ( ! is_user_logged_in() ) return;
		// normalize attribute keys, lowercase
    $atts = array_change_key_case((array)$atts, CASE_LOWER);
    // find the current course's
    if ( ( !isset( $atts['course'] ) || empty( $atts['course'] ) ) && is_numeric( get_the_ID() ) ) {
	    $post_course = get_post_meta(get_the_ID(), 'completable', true);
  	  if ( $post_course && ( $post_course !== 'true' ) ) $atts['course'] = $post_course;
    }
    $user_completed = $this->get_user_completed();

    $percentage = $this->get_percentage($user_completed, $atts);
		
		ob_start();
		include 'partials/wpcomplete-public-bar-graph.php';
    return ob_get_clean();
	}

	/**
	 * PREMIUM:
	 * Add custom completion code to the end of post and page content
	 *
	 * @since    1.0.0
	 */
	public function append_custom_styles() {
		if (!WPCOMPLETE_IS_ACTIVATED)
			return;

		$style_default = '
li .wpc-lesson-completed { opacity: .5; }
li .wpc-lesson-completed:after { content: "✔"; margin-left: 5px; }
';

		$complete_background = get_option( $this->plugin_name . '_incomplete_background', '#ff0000' );
		$complete_color = get_option( $this->plugin_name . '_incomplete_color', '#ffffff' );
		$completed_background = get_option( $this->plugin_name . '_completed_background', '#666666' );
		$completed_color = get_option( $this->plugin_name . '_completed_color', '#ffffff' );
		$graph_primary_color = get_option( $this->plugin_name . '_graph_primary', '#97a71d' );
		$graph_secondary_color = get_option( $this->plugin_name . '_graph_secondary', '#ebebeb' );
		$custom_styles = get_option( $this->plugin_name . '_custom_styles', $style_default );

	  echo "<style type=\"text/css\"> a.wpc-complete { background: $complete_background; color: $complete_color; } a.wpc-completed { background: $completed_background; color: $completed_color; } .wpc-radial-progress, .wpc-bar-progress .wpc-progress-track { background-color: $graph_secondary_color; } .wpc-radial-progress .wpc-fill, .wpc-bar-progress .wpc-progress-fill { background-color: $graph_primary_color; } .wpc-radial-progress .wpc-numbers, .wpc-bar-progress .wpc-numbers { color: $graph_primary_color; } .wpc-bar-progress[data-progress=\"75\"] .wpc-numbers, .wpc-bar-progress[data-progress=\"76\"] .wpc-numbers, .wpc-bar-progress[data-progress=\"77\"] .wpc-numbers, .wpc-bar-progress[data-progress=\"78\"] .wpc-numbers, .wpc-bar-progress[data-progress=\"79\"] .wpc-numbers, .wpc-bar-progress[data-progress=\"80\"] .wpc-numbers, .wpc-bar-progress[data-progress=\"81\"] .wpc-numbers, .wpc-bar-progress[data-progress=\"82\"] .wpc-numbers, .wpc-bar-progress[data-progress=\"83\"] .wpc-numbers, .wpc-bar-progress[data-progress=\"84\"] .wpc-numbers, .wpc-bar-progress[data-progress=\"85\"] .wpc-numbers, .wpc-bar-progress[data-progress=\"86\"] .wpc-numbers, .wpc-bar-progress[data-progress=\"87\"] .wpc-numbers, .wpc-bar-progress[data-progress=\"88\"] .wpc-numbers, .wpc-bar-progress[data-progress=\"89\"] .wpc-numbers, .wpc-bar-progress[data-progress=\"90\"] .wpc-numbers, .wpc-bar-progress[data-progress=\"91\"] .wpc-numbers, .wpc-bar-progress[data-progress=\"92\"] .wpc-numbers, .wpc-bar-progress[data-progress=\"93\"] .wpc-numbers, .wpc-bar-progress[data-progress=\"94\"] .wpc-numbers, .wpc-bar-progress[data-progress=\"95\"] .wpc-numbers, .wpc-bar-progress[data-progress=\"96\"] .wpc-numbers, .wpc-bar-progress[data-progress=\"97\"] .wpc-numbers, .wpc-bar-progress[data-progress=\"98\"] .wpc-numbers, .wpc-bar-progress[data-progress=\"99\"] .wpc-numbers, .wpc-bar-progress[data-progress=\"100\"] .wpc-numbers { color: $graph_secondary_color; } $custom_styles </style>";
	}

	/**
	 * Add custom completion code to the end of post and page content
	 *
	 * @since    1.0.0
	 */
	public function append_completion_code($content) {
		$post_type = get_post_type();

		// Don't append if it's been disabled:
		if ( get_option( $this->plugin_name . '_auto_append', 'true' ) == 'false' ) {
			return $content;
		}

		// Don't append if we aren't suppose to complete this type of post:
		if ( ! in_array( $post_type, $this->get_enabled_post_types() ) ) {
			return $content;
		}

		// See if this post is actually completable:
		if ( ! get_post_meta(get_the_ID(), 'completable', true) ) {
			return $content;
		}

		// Only append to body if we can't find any record of the button anywhere on the content:
		// NOTE: This doesn't fix the issue with OptimizePress... but it should help:
		if ( ( strpos( get_the_content(), '[complete_button]' ) === false ) && ( strpos( get_the_content(), '[wpc_complete_button]' ) === false ) && is_main_query() ) {
			if ( ( strpos( $content, '[complete_button]' ) === false ) && ( strpos( $content, '[wpc_complete_button]' ) === false ) && ( strpos( $content, 'class="wpc-button' ) === false ) ) {
				$content .= "\n\n[wpc_complete_button]";
			}
		}

		return $content;
	}

	/**
	 * Helper method to query for all pages and posts that are completable.
	 *
	 * @since    1.0.0
	 */
	public function get_meta_posts( $key = '', $value = '' ) {
    global $wpdb;

    if( empty( $key ) )
        return;
    if ( empty( $value ) ) {
	    $r = $wpdb->get_col( $wpdb->prepare( "
	        SELECT pm.post_id FROM {$wpdb->postmeta} pm
	        LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
	        WHERE pm.meta_key = '%s' 
	        AND (p.post_status != '%s')
	        AND (p.post_status != '%s')
	        AND (p.post_type = '" . join("' OR p.post_type = '", $this->get_enabled_post_types()) . "')
	    ", $key, 'trash', 'draft') );
    } else {
	    $r = $wpdb->get_col( $wpdb->prepare( "
	        SELECT pm.post_id FROM {$wpdb->postmeta} pm
	        LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
	        WHERE pm.meta_key = '%s' 
	        AND (p.post_status != '%s')
	        AND (p.post_status != '%s')
	        AND (pm.meta_value = '%s') 
	        AND (p.post_type = '" . join("' OR p.post_type = '", $this->get_enabled_post_types()) . "')
	    ", $key, 'trash', 'draft', $value) );
    }

    return $r;
	}

	/**
	 * Handle trying to mark a lesson as completed as a logged out user... should just redirect to login.
	 *
	 * @since    1.0.0
	 */
	public function nopriv_mark_completed() {
		$redirect = "http://" . $_SERVER[HTTP_HOST] . $_SERVER[REQUEST_URI];
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			// return something indicating that the page should redirect to login?
			echo json_encode( array( 'redirect' => wp_login_url( $redirect ) ) );
			die();
		} else {
	    wp_redirect( wp_login_url( $redirect ) );
	    exit();
		}
	}

	/**
	 * Handle marking a lesson as completed.
	 *
	 * @since    1.0.0
	 */
	public function mark_completed() {
		check_ajax_referer( 'completion' );
		// Get any existing lessons this user has completed:
		$user_completed = $this->get_user_completed();
		// Add this post id and update the meta storage values:
		$user_completed[ $_POST['post_id'] ] = date('Y-m-d H:i:s');
		
		update_user_meta( get_current_user_id(), 'wp_completed', json_encode($user_completed) );
		
		// update the button
		$updates_to_sendback = array( 
			'this' => $this->complete_button_cb( array( 'id' => $_POST['post_id'] ) )
		);
		// PREMIUM: redirect student if teacher has added redirect url:
		if (WPCOMPLETE_IS_ACTIVATED) {
			// PREMIUM: get info for progress percentage:
			$atts = array();
			if ( ( $course = get_post_meta($_POST['post_id'], 'completable', true) ) && ( $course !== 'true' ) ) {
				$atts['course'] = $course;
			}
			// Update premium feature widgets:
			$updates_to_sendback['.wpcomplete-progress-ratio'] = $this->progress_ratio_cb( $atts );
			$updates_to_sendback['.wpcomplete-progress-percentage'] = $this->progress_percentage_cb( $atts );
			$updates_to_sendback['.wpcomplete-progress-ratio.all-courses'] = $this->progress_ratio_cb( array('course' => 'all') );
			$updates_to_sendback['.wpcomplete-progress-percentage.all-courses'] = $this->progress_percentage_cb( array('course' => 'all') );
			$updates_to_sendback['.' . $this->get_course_class($atts) . '[data-progress]'] = $this->get_percentage($user_completed, $atts);
			$updates_to_sendback['.all-courses[data-progress]'] = $this->get_percentage($user_completed, array('course' => 'all') );

			$redirect_json = get_post_meta( $_POST['post_id'], 'completion-redirect', true );
			if ( $redirect_json && !empty($redirect_json) ) {
				$redirect = json_decode($redirect_json, true);

				if ($redirect['url'] && !empty($redirect['url'])) {
					$updates_to_sendback['redirect'] = $redirect['url'];
				} else if (strpos($redirect['title'], 'http') === 0) {
					$updates_to_sendback['redirect'] = $redirect['title'];
				}
			}
		}

		echo json_encode( $updates_to_sendback );
		die();
	}

	/**
	 * Handle trying to mark a lesson as incomplete as a logged out user... should just redirect to login.
	 *
	 * @since    1.0.0
	 */
	public function nopriv_mark_uncompleted() {
		$redirect = "http://" . $_SERVER[HTTP_HOST] . $_SERVER[REQUEST_URI];
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			// return something indicating that the page should redirect to login?
			echo json_encode( array( 'redirect' => wp_login_url( $redirect ) ) );
			die();
		} else {
	    wp_redirect( wp_login_url( $redirect ) );
	    exit();
		}
	}

	/**
	 * Handle mark a lesson as incomplete.
	 *
	 * @since    1.0.0
	 */
	public function mark_uncompleted() {
		check_ajax_referer( 'completion' );
		// Get any existing lessons this user has completed:
		$user_completed = $this->get_user_completed();
		// Remove this post id and update the meta storage values:
		unset($user_completed[ $_POST['post_id'] ]);

		update_user_meta( get_current_user_id(), 'wp_completed', json_encode($user_completed) );

		$updates_to_sendback = array( 
			'this' => $this->complete_button_cb( array( 'id' => $_POST['post_id'] ) )
		);

		// PREMIUM:
		if (WPCOMPLETE_IS_ACTIVATED) {
			// get info for progress percentage:
			$atts = array();
			if ( ( $course = get_post_meta($_POST['post_id'], 'completable', true) ) && ( $course !== 'true' ) ) {
				$atts['course'] = $course;
			}
			
			$updates_to_sendback['.wpcomplete-progress-ratio'] = $this->progress_ratio_cb( $atts );
			$updates_to_sendback['.wpcomplete-progress-percentage'] = $this->progress_percentage_cb( $atts );
			$updates_to_sendback['.wpcomplete-progress-ratio.all-courses'] = $this->progress_ratio_cb( array('course' => 'all') );
			$updates_to_sendback['.wpcomplete-progress-percentage.all-courses'] = $this->progress_percentage_cb( array('course' => 'all') );
			$updates_to_sendback['.' . $this->get_course_class($atts) . '[data-progress]'] = $this->get_percentage($user_completed, $atts);
			$updates_to_sendback['.all-courses[data-progress]'] = $this->get_percentage($user_completed, array('course' => 'all') );
		}
		
		// Send back new button:
		echo json_encode( $updates_to_sendback );
		die();
	}

	/**
	 * Returns an array of all wordpress posts that are "completable".
	 *
	 * @since  1.2.0
	 */
	public function get_completable_list() {
		$total_posts = $this->get_meta_posts('completable');
		$updates_to_sendback = array( );

		$user_completed = $this->get_user_completed();

		foreach ( $total_posts as $post_id ) {
			$updates_to_sendback[ get_permalink( $post_id ) ] = array(
				'id' => $post_id,
				'completed' => ( isset( $user_completed[ $post_id ] ) ) ? $user_completed[ $post_id ] : false
			);
		}

		// Send back array of posts:
		echo json_encode( $updates_to_sendback );
		die();
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
	 * Returns an array of all the current user's completed posts.
	 *
	 * @since  1.3.0
	 */
	public function get_user_completed() {
		$user_completed_json = get_user_meta( get_current_user_id(), 'wp_completed', true );
		$user_completed = ( $user_completed_json ) ? json_decode( $user_completed_json, true ) : array();
		// Convert to new storage format if we didn't track time of completion:	
		if ( $user_completed == array_values( $user_completed ) ) {
			$new_array = array();
			foreach ( $user_completed as $p ) {
				$new_array[ $p ] = true;
			}
			$user_completed = $new_array;
		}

		return $user_completed;
	}

	/**
	 * Returns a string containing the normalized class name for the current course.
	 *
	 * @since  1.4.0
	 */
	public function get_course_class($atts = array()) {
		if ( isset( $atts['course'] ) && ( $atts['course'] != 'all' ) && ( $atts['course'] !== 'true' ) ) {
			return str_replace(array(' ', '_', "'", '"'), '-', strtolower($atts['course']));
		}
		return 'all-courses';
	}

	/**
	 * Returns a string containing the percentage of completed / total posts for a given course.
	 *
	 * @since  1.4.0
	 */
	public function get_percentage($user_completed = 0, $atts = array()) {
		// check if we are looking for a specific course's progress:
		if ( isset( $atts['course'] ) && !empty( $atts['course'] ) && ( $atts['course'] != 'all' ) ) {
			$total_posts = $this->get_meta_posts('completable', $atts['course']);
    } else {
			$total_posts = $this->get_meta_posts('completable');
    }
		if ( count($total_posts) > 0 ) {
			$completed_posts = array_intersect( $total_posts, array_keys( $user_completed ) );
			$percentage = round(100 * ( count($completed_posts) / count($total_posts) ), 0);
		} else {
			$percentage = 0;
		}
		return $percentage;
	}

}
