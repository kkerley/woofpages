<?php
/**
 * The template for displaying archive pages
 *
 * Used to display archive-type pages if nothing more specific matches a query.
 * For example, puts together date-based pages if no date.php file exists.
 *
 * If you'd like to further customize these archive views, you may create a
 * new template file for each one. For example, tag.php (Tag archives),
 * category.php (Category archives), author.php (Author archives), etc.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

get_header(); ?>

	<div id="page" role="main">
		<article class="main-content">
			<header>
				<h1>Dogs available for adoption</h1>
                <p>All available dogs and dogs pending adoption are listed below. We may also have <a href="/how-to-help/special-needs-dogs/">special
                    needs dogs</a> looking for someone with a big heart to give them a loving forever home.</p>
			</header>

            <div class="filter-controls--header">
                <p>Filter dogs based on size, sex, and availability&mdash;filters stack.</p>
            </div>
            <div class="filter-controls">
                <button type="button" class="button secondary tiny" data-toggle=".small-bodied" title="Small bodied"><i class="fa fa-chevron-down" aria-hidden="true"></i></button>
                <button type="button" class="button secondary small" data-toggle=".medium-bodied" title="Medium bodied"><i class="fa fa-minus" aria-hidden="true"></i></button>
                <button type="button" class="button secondary medium" data-toggle=".large-bodied" title="Large bodied"><i class="fa fa-chevron-up" aria-hidden="true"></i></button>
                <button type="button" class="button female" data-toggle=".female" title="Female"><i class="fa fa-venus" aria-hidden="true"></i></button>
                <button type="button" class="button male" data-toggle=".male" title="Male"><i class="fa fa-mars" aria-hidden="true"></i></button>
                <button type="button" class="button success" data-toggle=".available" title="Available"><i class="fa fa-thumbs-o-up" aria-hidden="true"></i></button>
                <button type="button" class="button alert" data-toggle=".pending-adoption" title="Pending adoption"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i></button>
            </div>

			<?php if ( have_posts() ) : ?>
				<div class="wrapper--dogs vertical">
					<?php /* Start the Loop */ ?>
					<?php while ( have_posts() ) : the_post(); ?>
						<?php get_template_part( 'template-parts/woofpages/_card_dog'); ?>
					<?php endwhile; ?>

                    <div class="no-matching-dogs">
                        <p>Sorry, no dogs match the filtered criteria.</p>
                    </div>
				</div>
			<?php else : ?>
				<?php get_template_part( 'template-parts/content', 'none' ); ?>

			<?php endif; // End have_posts() check. ?>

			<?php /* Display navigation to next/previous pages when applicable */ ?>
			<?php
			if ( function_exists( 'foundationpress_pagination' ) ) :
				foundationpress_pagination();
			elseif ( is_paged() ) :
				?>
				<nav id="post-nav">
					<div class="post-previous"><?php next_posts_link( __( '&larr; Older posts', 'foundationpress' ) ); ?></div>
					<div class="post-next"><?php previous_posts_link( __( 'Newer posts &rarr;', 'foundationpress' ) ); ?></div>
				</nav>
			<?php endif; ?>

		</article>
		<?php get_sidebar(); ?>

	</div>

<?php get_footer();
