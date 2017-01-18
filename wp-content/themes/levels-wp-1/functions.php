<?php
/**
 * levels functions and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package levels
 */

function wpb_list_child_pages() { 
global $post; 
if ( is_page() && $post->post_parent )
	$childpages = wp_list_pages( 'sort_column=menu_order&title_li=&child_of=' . $post->post_parent . '&echo=0' );
else
	$childpages = wp_list_pages( 'sort_column=menu_order&title_li=&child_of=' . $post->ID . '&echo=0' );
if ( $childpages ) {
	$string = '<ul>' . $childpages . '</ul>';
}
return $string;
}

add_shortcode('wpb_childpages', 'wpb_list_child_pages');

if ( ! function_exists( 'levels_setup' ) ) :
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function levels_setup() {
	/*
	 * Make theme available for translation.
	 * Translations can be filed in the /languages/ directory.
	 * If you're building a theme based on levels, use a find and replace
	 * to change 'levels' to the name of your theme in all the template files.
	 */
	load_theme_textdomain( 'levels', get_template_directory() . '/languages' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	/*
	 * Let WordPress manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 */
	add_theme_support( 'title-tag' );

	/*
	 * Enable support for Post Thumbnails on posts and pages.
	 *
	 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
	 */
	add_theme_support( 'post-thumbnails' );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus( array(
		'primary' => esc_html__( 'Primary', 'levels' ),
	) );

	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
	) );

	/*
	 * Enable support for Post Formats.
	 * See https://developer.wordpress.org/themes/functionality/post-formats/
	 */
	add_theme_support( 'post-formats', array(
		'aside',
		'image',
		'video',
		'quote',
		'link',
	) );

	// Set up the WordPress core custom background feature.
	// add_theme_support( 'custom-background', apply_filters( 'levels_custom_background_args', array(
	// 	'default-color' => 'ffffff',
	// 	'default-image' => '',
	// ) ) );
}
endif;
add_action( 'after_setup_theme', 'levels_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function levels_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'levels_content_width', 640 );
}
add_action( 'after_setup_theme', 'levels_content_width', 0 );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function levels_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar', 'levels' ),
		'id'            => 'sidebar-1',
		'description'   => '',
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'levels_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function levels_scripts() {
	wp_enqueue_style( 'levels-style', get_stylesheet_uri() );

	wp_enqueue_script( 'levels-navigation', get_template_directory_uri() . '/js/navigation.js', array(), '20120206', true );

	wp_enqueue_script( 'levels-skip-link-focus-fix', get_template_directory_uri() . '/js/skip-link-focus-fix.js', array(), '20130115', true );

	wp_enqueue_script( 'levels-app', get_template_directory_uri() . '/js/app.js', array( 'jquery' ), '20160203', true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'levels_scripts' );

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Custom shortcodes for this theme.
 */
require get_template_directory() . '/inc/shortcodes.php';

/**
 * Custom functions that act independently of the theme templates.
 */
require get_template_directory() . '/inc/extras.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
require get_template_directory() . '/inc/jetpack.php';

/**
 * Remove WP Bar
 */
add_filter('show_admin_bar', '__return_false');

/**
 * Add Settings Page to Admin
 */

function display_footer_content_left()
{
	?>
  	<input type="text" name="footer_content_left" id="footer_content_left" value="<?php echo get_option('footer_content_left'); ?>" style="width: 400px;" placeholder="&copy; Your Company Name. All Rights Reserved." />
  	<p class="description" id="tagline-description">This content is shown in the bottom-left area of the footer.</p>
  <?php
}

function display_footer_content_right()
{
	?>
  	<input type="text" name="footer_content_right" id="footer_content_right" value="<?php echo get_option('footer_content_right'); ?>" style="width: 400px;" placeholder="Made by [Your Name]" />
  	<p class="description" id="tagline-description">This content is shown in the bottom-right area of the footer.</p>
  <?php
}

function display_theme_panel_fields()
{
	add_settings_section("section", "General Settings", null, "theme-options");

	add_settings_field("footer_content_left", "Footer Content (Left)", "display_footer_content_left", "theme-options", "section");
  add_settings_field("footer_content_right", "Footer Content (Right)", "display_footer_content_right", "theme-options", "section");

  register_setting("section", "footer_content_left");
  register_setting("section", "footer_content_right");
}

function levels_theme_settings_page() {

	?>
	  <div class="wrap">
	    <h1>Levels Theme</h1>

			<?php if ( isset($_GET['settings-updated']) ) { ?>
				<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
					<p><strong>Settings saved.</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
				</div>
			<?php } ?>

	    <form method="post" action="options.php">
	        <?php
	            settings_fields("section");
	            do_settings_sections("theme-options");
	            submit_button();
	        ?>
	    </form>
		</div>
	<?php

}

function add_levels_theme_menu_item()
{

	$svg_icon = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTciIGhlaWdodD0iMTgiIHZpZXdCb3g9IjAgMCAxNyAxOCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48dGl0bGU+TEVWRUxTIFNoYXBlPC90aXRsZT48cGF0aCBkPSJNLjAxOCAxNi44NTZDLS4wODIgMTcuNDc4LjI0IDE4IC45MTMgMThoMTIuNjNjLjcyIDAgMS4xNDMtLjM0OCAxLjIxOC0xLjAybC4yMjUtMS41OWMuMS0uNzQ2LS4yMjQtMS4xMi0uOTQ1LTEuMTJINC42NjdMNi4zODIgMS4xNDVDNi40ODIuNTIyIDYuMTYgMCA1LjQ2MiAwSDMuMjc1Yy0uNjcyIDAtMS4wNy4zNDgtMS4xOTQgMS4wNDRMLjAyIDE2Ljg1NnptMTAuODY0LTkuMjczaDIuOTg0Yy41NzIgMCAuODk1LS4yNzQuOTk0LS44NDVsLjEyNS0uOTQ1Yy4xLS41NzItLjE1LS44NDUtLjc0Ni0uODQ1aC0zLjAzNGwuMjQ4LTEuODRoNC4wMDNjLjY5NiAwIDEuMDk0LS4zIDEuMTkzLS45MkwxNi44LjkyYy4xLS41OTctLjI1LS45Mi0uOTQ1LS45Mkg5LjIxN2MtLjY5NyAwLTEuMTQ0LjQyMy0xLjIyIDEuMDJsLTEuMzkgMTAuNTRjLS4wMjYuNTk3LjM3MiAxLjAyLjk3IDEuMDJoNi42MzdjLjY5NiAwIDEuMDk0LS4yOTggMS4xOTMtLjkybC4xNS0xLjI5M2MuMS0uNTk2LS4yNS0uOTItLjk0NS0uOTJoLTMuOTc4bC4yNDgtMS44NjR6IiBmaWxsPSIjRkZGIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiLz48L3N2Zz4=';

	add_menu_page("Levels Theme", "Levels Theme", "manage_options", "levels-theme", "levels_theme_settings_page", $svg_icon, 99);

}

add_action("admin_menu", "add_levels_theme_menu_item");
add_action("admin_init", "display_theme_panel_fields");
add_filter( 'jetpack_enable_open_graph', '__return_false' );