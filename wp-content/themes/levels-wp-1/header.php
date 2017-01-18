<?php
/**
 * The header for our theme.
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package levels
 */

?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta property="og:type" content="website" />
<meta property="og:title" content="Tiny Marketing Wins" />
<meta property="og:description" content="Get a marketing tactic every week for your online business" />
<meta property="og:url" content="https://tinymarketingwins.com" />
<meta property="og:site_name" content="TinyMarketingWins" />
<meta property="og:image" content="https://tinymarketingwins.com/wp-content/uploads/2016/12/TinymarketingWins.jpg">
<meta property="og:image:width" content="1400" />

<link rel="profile" href="http://gmpg.org/xfn/11">
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">

<script type="text/javascript">
  WebFontConfig = {
    google: { families: [ 'Asap:400italic,700italic:latin', 'Source+Sans+Pro:400,300,600,200,300italic,400italic,700:latin' ] }
  };
  (function() {
    var wf = document.createElement('script');
    wf.src = ('https:' == document.location.protocol ? 'https' : 'http') +
      '://ajax.googleapis.com/ajax/libs/webfont/1/webfont.js';
    wf.type = 'text/javascript';
    wf.async = 'true';
    var s = document.getElementsByTagName('script')[0];
    s.parentNode.insertBefore(wf, s);
  })();
</script>

<?php wp_head(); ?>

</head>

<body <?php body_class(); ?>>

<div id="page" class="levels-wp">

  <a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'levels' ); ?></a>

  <?php if ( function_exists('levels_hide_topbar') ): ?>

    <!-- Hide Top Bar -->

  <?php else: ?>

    <nav id="site-navigation" class="main-navigation" role="navigation">

      <div class="container container--normal">

        <button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false"><i class="fa fa-bars"></i></button>

        <a class="site-navigation-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>

        <?php wp_nav_menu( array( 'theme_location' => 'primary', 'menu_id' => 'primary-menu', 'fallback_cb' => false, 'container_class' => 'main-navigation-container' ) ); ?>

      </div>

    </nav><!-- #site-navigation -->

  <?php endif; ?>

  <div id="content" class="site-content <?= function_exists('levels_hide_topbar') ? 'hideTopbar' : '' ?>">
