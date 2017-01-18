<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package levels
 */

?>

	</div><!-- #content -->

	<footer id="colophon" class="footer" role="contentinfo">
    <div class="container container--grid">
  		<div class="footer__bottom">
        <p class="footer__bottom-right"><?php echo get_option('footer_content_right') ?></p>
        <p><?php echo get_option('footer_content_left') ?></p>
      </div>
    </div>
	</footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-89095333-1', 'auto');
  ga('send', 'pageview');

</script>
</body>
</html>
