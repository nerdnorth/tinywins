<?php
/**
 * The sidebar containing the main widget area.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package levels
 */

if ( ! is_active_sidebar( 'sidebar-1' ) ) {
	return;
}
?>

<aside id="secondary" class="widget-area sidebar" role="complementary">
  <div class="container container--normal">
	 <?php dynamic_sidebar( 'sidebar-1' ); ?>
  </div>
</aside><!-- #secondary -->
