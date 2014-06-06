<?php
/**
 * The template for displaying Diary
 *
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */

get_header(); ?>

	<section id="primary" class="site-content">
		<div id="content" role="main">

		<?php if ( have_posts() ) : ?>
			<header class="archive-header">
				<h1 class="archive-title"><?php get_simdiaw_title(true); ?></h1>
			</header><!-- .archive-header -->

			<?php /* Start the Loop */
				the_simdiaw_reminders_query();
				while ( have_posts() ) : the_post();
				
				get_template_part( 'content-reminder', get_post_format() );

			endwhile;

			twentytwelve_content_nav( 'nav-below' );
			?>

		<?php else : ?>
			<?php get_template_part( 'content', 'none' ); ?>
		<?php endif; ?>

		</div><!-- #content -->
	</section><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>