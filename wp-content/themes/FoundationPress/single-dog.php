<?php
/**
 * The template for displaying all single posts and attachments
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

get_header(); ?>

<?php # get_template_part( 'template-parts/featured-image' ); ?>

<article class="sponsor banner full-width">
    <div class="sponsor--inner">
        <img src="https://placehold.it/1024x120&amp;text=Banner Ad" alt="Banner Ad" height="120" width="1024">
    </div>
</article>

<div id="single-post" role="main">

<?php do_action( 'foundationpress_before_content' ); ?>
<?php while ( have_posts() ) : the_post(); ?>

	<article <?php post_class('main-content') ?> id="post-<?php the_ID(); ?>">
        <?php
            // checking to see if this Dog has any Adoptions associated with their record
            $dog_id = get_the_ID(); // getting current dog ID

            $adoption_args = array(
                'post_type'     => 'adoption',
                'meta_key'   => '_wpcf_belongs_dog_id',
                'meta_value' =>  $dog_id,
            );

            $adoptions_query = new WP_Query($adoption_args);
        ?>

        <section class="dog-intro">
            <header>
	            <?php
	            // $breeds = get_the_terms(get_the_ID(), 'breed');
	            // $characteristics = get_the_terms(get_the_ID(), 'characteristic');
	            ?>
                <h1 class="entry-title"><?php the_title(); ?></h1>
                <p>
		            <?php echo woofpages_current_dog_breeds(get_the_ID()); ?>

                    <?php if(!empty(types_render_field('sex'))): ?>
	                    <?php echo types_render_field('sex'); ?>
                    <?php endif; ?>

	                <?php if(!empty(types_render_field('age'))): ?>
                        | <?php echo types_render_field('age'); ?> year<?php echo (int)str_replace(' ', '', types_render_field('age')) > 1 ? 's' : ''; ?> old
	                <?php endif; ?>

	                <?php if(!empty(types_render_field('body-size'))): ?>
                        | <?php echo types_render_field('body-size'); ?>
	                <?php endif; ?>

	                <?php if(!empty(types_render_field('weight'))): ?>
                        | <?php echo types_render_field('weight'); ?> lbs
	                <?php endif; ?>
                </p>

                <div class="wrapper--characteristics">
                    <?php echo woofpages_current_dog_characteristics(get_the_ID()); ?>
                </div>

	            <?php if(!empty(types_render_field('location')) && types_render_field('adoption-status') !== 'Adopted'): ?>
                    <p><em><i class="fa fa-globe"></i> Currently located in <?php echo types_render_field('location');?></em></p>
	            <?php endif; ?>


                <div class="dog-featured-image show-for-small-only<?php echo types_render_field('adoption-status') === 'Adopted' ? ' adopted' : '' ; ?>">
		            <?php the_post_thumbnail('large'); ?>
                </div>
            </header>
        </section>

        <?php do_action( 'foundationpress_post_before_entry_content' ); ?>
        <div class="entry-content">
	        <?php
	        if(count($adoptions_query) > 0 && types_render_field('adoption-status') === 'Adopted'):
		        if($adoptions_query->have_posts()):
			        while($adoptions_query->have_posts()): $adoptions_query->the_post();
				        $adoption_parents_id = get_post_meta(get_the_ID(), '_wpcf_belongs_adoption-parent_id', true);
				        $adoption_videos = get_post_meta(get_the_ID(), 'wpcf-video');
                        $adoption_photos = get_post_meta(get_the_ID(), 'wpcf-after-adoption-photo');
                ?>
                        <section class="wrapper--adoptions adopted">
                            <div class="adoption--inner">
                                <h3><i class="fa fa-check-circle-o"></i> Adopted!</h3>
                                <p><?php echo get_the_title($dog_id); ?> was adopted <?php echo types_render_field('date-of-adoption') ?> by <?php echo get_the_title($adoption_parents_id); ?>.</p>
                            </div>
                        </section>

                        <section class="adoption-update">
                            <?php the_content(); ?>

                            <?php if(sizeof($adoption_videos > 0)):
	                            foreach($adoption_videos as $video):
		                            echo $video;
	                            endforeach;
                            endif; ?>
                        </section>

				        <?php
			        endwhile;
		        endif; // end of $adoption_query->have_posts()
            elseif(types_render_field('adoption-status') === 'Pending Adoption'): ?>
                <section class="wrapper--adoptions pending">
                    <div class="adoption--inner">
                        <h3><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Adoption pending</h3>
                        <p><?php echo get_the_title($dog_id); ?> is pending adoption but you can still <a href="#" class="jump-link single-dog-page-application" data-jump-to="single-dog-application">submit an application to adopt</a> in case this one falls through.</p>
                    </div>
                </section>
            <?php
            elseif(types_render_field('adoption-status') === 'Available'): ?>
                <section class="wrapper--adoptions available">
                    <div class="adoption--inner">
                        <h3><i class="fa fa-thumbs-o-up" aria-hidden="true"></i> Available</h3>
                        <p><?php echo get_the_title($dog_id); ?> is available to adopt and if you're interested, <a href="#" class="jump-link single-dog-page-application" data-jump-to="single-dog-application">submit an application below</a>!</p>
                    </div>
                </section>
            <?php

	        endif; // checking to see if $adoptions_query > 0 && adoption-status === 'Adopted'
	        wp_reset_postdata();
	        ?>


            <?php the_content(); ?>

            <div class="personal-details">
                <h3><?php the_title(); ?>'s personal details</h3>
                <?php if(!empty(types_render_field('birthdate'))): ?>
                    <p><i class="fa fa-birthday-cake" aria-hidden="true"></i> <?php echo types_render_field('birthdate') ?></p>
                <?php endif; ?>

                <?php if(types_render_field('neutered-spayed')): ?>
                    <p><i class="fa fa-ban" aria-hidden="true"></i> Neutered/spayed</p>
                <?php endif; ?>

                <?php if(types_render_field('chipped')): ?>
                    <p><i class="fa fa-map-marker" aria-hidden="true"></i> Microchipped</p>
                <?php endif; ?>

	            <?php if(types_render_field('current-with-routine-shots')): ?>
                    <p><i class="fa fa-medkit" aria-hidden="true"></i> Current with routine shots</p>
	            <?php endif; ?>

	            <?php if(types_render_field('prefers-a-home-without')): ?>
                    <p class="callout warning"><i class="fa fa-ban" aria-hidden="true"></i> Prefers a home without <?php echo types_render_field( 'prefers-a-home-without', array('separator' => ', ' )); ?></p>
	            <?php endif; ?>

                <?php if(!empty(types_render_field('medical-history'))): ?>
                    <h4><i class="fa fa-hospital-o" aria-hidden="true"></i> Medical history</h4>
                    <?php echo types_render_field('medical-history', array('output'=> 'html')); ?>
                <?php endif; ?>

                <?php if(!empty(types_render_field('dietary-concerns-issues'))): ?>
                    <h4><i class="fa fa-cutlery" aria-hidden="true"></i> Dietary concerns/issues</h4>
                    <?php echo types_render_field('dietary-concerns-issues', array('output'=> 'html')); ?>
                <?php endif; ?>

                <?php if(!empty(types_render_field('allergies'))): ?>
                    <h4><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Allergies</h4>
                    <?php echo types_render_field('allergies', array('output'=> 'html')); ?>
                <?php endif; ?>

                <?php if(!empty(types_render_field('other-issues'))): ?>
                    <h4><i class="fa fa-info-circle" aria-hidden="true"></i> Other issues</h4>
                    <?php echo types_render_field('other-issues', array('output'=> 'html')); ?>
                <?php endif; ?>
            </div>
            <?php # edit_post_link( __( 'Edit', 'foundationpress' ), '<hr /><span class="edit-link">', '</span>' ); ?>

        </div>

		<?php if(!empty(types_render_field( "dog-image"))): ?>
            <div class="kk-modal dog--additional-photos">
                <h4><i class="fa fa-picture-o"></i> Additional images of <?php the_title(); ?></h4>
                <div class="carousel--wrapper">
                    <div id="carousel--dog-detail-page_primary" class="carousel--dog-detail-page_primary">
	                    <?php
	                    if(sizeof($adoption_photos) > 0):
		                    foreach($adoption_photos as $photo):
			                    ?>
                                <div class="img">
                                    <img src="<?php echo $photo; ?>" />
                                </div>

			                    <?php
		                    endforeach;
	                    endif;
	                    ?>
                        <div class="img">
                            <?php the_post_thumbnail('dog-carousel-primary'); ?>
                        </div>
                        <div class="img">
                            <?php echo types_render_field( "dog-image", array( "size"=>"dog-carousel-primary", "separator" => "</div><div class='img'>") ); ?>
                        </div>
                    </div>
                </div>
                <i class="fa fa-times-circle kk-modal-trigger" data-target-modal="dog--additional-photos"></i>
            </div>
		<?php endif; ?>
	</article>
<?php endwhile;?>

<?php do_action( 'foundationpress_after_content' ); ?>
<?php get_sidebar(); ?>
</div>

<?php
if(types_render_field('adoption-status') !== 'Adopted'):
	?>
    <section class="form--adoption">
<!--        <div class="section-divider">-->
<!--            <hr />-->
<!--        </div>-->
        <article class="sponsor banner full-width">
            <div class="sponsor--inner">
                <img src="https://placehold.it/1024x120&amp;text=Banner Ad" alt="Banner Ad" height="120" width="1024">
            </div>
        </article>


        <div class="form--inner">
            <h2 id="single-dog-application">Interested in adopting <?php the_title(); ?>?</h2>
            <p>
                First, ensure you've filled out the global adoption form because if you haven't, filling this form
                out below won't do anything. The global adoption can be found here <INSERT LINK HERE>. Once that is
                complete and submitted, apply to adopt <?php the_title(); ?> again.
            </p>
	        <?php echo do_shortcode('[gravityform id="1" title="false" description="true"]'); ?>
        </div>
    </section>
<?php endif; ?>

<?php get_footer();