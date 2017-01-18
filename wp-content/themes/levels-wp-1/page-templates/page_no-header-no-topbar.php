<?php
/**
 * Template Name: Hide Page Header and Top Bar
 * @package levels
 */

function levels_hide_topbar() {
	return true;
}

get_header(); ?>

	<div id="primary" class="container container--normal">
		<main id="main" class="site-main" role="main">

			<?php
			while ( have_posts() ) : the_post();

				get_template_part( 'template-parts/content', 'no-header' );

				// If comments are open or we have at least one comment, load up the comment template.
				if ( comments_open() || get_comments_number() ) :
					comments_template();
				endif;

			endwhile; // End of the loop.
			?>

		</main><!-- #main -->
	</div><!-- #primary -->

<?php
get_sidebar();
get_footer();
