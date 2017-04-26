<?php
/*
Template Name: Global Dog Search
*/
get_header(); ?>

<?php get_template_part( 'template-parts/featured-image' ); ?>

<div id="page" role="main">
<?php do_action( 'foundationpress_before_content' ); ?>
<?php while ( have_posts() ) : the_post(); ?>
    <article class="main-content">
        <header>
            <h1><?php the_title(); ?></h1>
        </header>


        <div class="entry-content">
		    <?php the_content(); ?>
		    <?php # edit_post_link( __( 'Edit', 'foundationpress' ), '<span class="edit-link">', '</span>' ); ?>
        </div>



       <section class="wrapper--global-dog-search">
           <header>
	           <?php echo do_shortcode('[wpdreams_ajaxsearchpro_results id="1" element="div"]'); ?>
           </header>

           <footer>
	           <?php echo do_shortcode('[wd_asp id="1"]'); ?>
           </footer>
       </section>

    </article>
<?php endwhile;?>
    <?php get_sidebar(); ?>

</div>

<?php get_footer();
