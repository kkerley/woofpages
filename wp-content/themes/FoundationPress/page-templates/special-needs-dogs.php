<?php
/*
Template Name: Special Needs Dogs
*/
get_header(); ?>

<?php
    $special_needs_args = array(
        'post_type'         => 'dog',
        'posts_per_page'    => -1,
        'orderby'           => 'date',
        'order'             => 'DESC',
        'meta_query'        => array(
	        'relation'      => 'AND',
            array(
                'key'       => 'wpcf-adoption-status',
                'value'     => 'Special Needs',
                'compare'   => '='
            ),
            array(
	            'key'       => 'wpcf-currently-in-need-of-help',
	            'value'     => 1,
	            'compare'   => '='
            ),
        )
    );

    $special_needs_query = new WP_Query($special_needs_args);
?>


<?php get_template_part( 'template-parts/featured-image' ); ?>

<div id="page" role="main">

<?php do_action( 'foundationpress_before_content' ); ?>
<?php while ( have_posts() ) : the_post(); ?>
  <article <?php post_class('main-content') ?> id="post-<?php the_ID(); ?>">
      <header>
          <h1 class="entry-title"><?php the_title(); ?></h1>
      </header>
      <?php do_action( 'foundationpress_page_before_entry_content' ); ?>
      <div class="entry-content">
          <?php the_content(); ?>
      </div>
      <section class="wrapper--dogs vertical">
          <?php if($special_needs_query->have_posts()): ?>

			  <?php while($special_needs_query->have_posts()): $special_needs_query->the_post(); ?>
				  <?php get_template_part( 'template-parts/woofpages/_card_dog'); ?>
			  <?php endwhile; ?>
          <?php else: ?>
              <p><strong><em>There are no dogs with special needs currently in need of a home.</em></strong></p>
          <?php endif; ?>
      </section>
	  <?php wp_reset_postdata(); ?>
  </article>
<?php endwhile;?>


<?php do_action( 'foundationpress_after_content' ); ?>
<?php get_sidebar(); ?>

</div>

<?php get_footer();
