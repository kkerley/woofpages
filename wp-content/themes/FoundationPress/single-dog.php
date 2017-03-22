<?php
/**
 * The template for displaying all single posts and attachments
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

get_header(); ?>

<?php # get_template_part( 'template-parts/featured-image' ); ?>

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
	            $breeds = get_terms('breed', array('hide_empty' => true));
	            $characteristics = get_terms('characteristic', array('hide_empty' => true));
	            ?>
                <h1 class="entry-title"><?php the_title(); ?></h1>
                <p>
		            <?php
		            $output = "";
		            $count = 0;

		            foreach($breeds as $breed):
			            $output .= '<a href="' . get_term_link($breed->slug, 'breed') . '">';
			            $output .= $breed->name;
			            $output .= '</a>';
			            $count++;
			            if($count < count($breeds)):
				            $output .= ', ';
			            endif;
		            endforeach;

		            $output .= " | ";
		            echo $output;
		            ?>

		            <?php echo types_render_field('sex'); ?> |
		            <?php echo types_render_field('age'); ?> year(s) old |
                    <strong class="<?php echo types_render_field('adoption-status') === 'Available' ? 'available' : 'not-available' ; ?>"><?php echo types_render_field('adoption-status'); ?></strong>
                </p>

	            <?php if(count($characteristics) > 0): ?>
                    <!--                    <h4><i class="fa fa-list"></i> Characteristics</h4>-->

                    <div class="wrapper--characteristics">
			            <?php
			            $char_output = "";

			            foreach($characteristics as $characteristic):
				            $char_output .= '<a href="' . get_term_link($characteristic->slug, 'characteristic') . '" class="label alert">';
				            $char_output .= $characteristic->name;
				            $char_output .= '</a> ';

			            endforeach;

			            echo $char_output;
			            ?>
                    </div>
	            <?php endif; ?>

	            <?php if(!empty(types_render_field('location'))): ?>
                    <p><em><i class="fa fa-globe"></i> Currently located in <?php echo types_render_field('location');?></em></p>
	            <?php endif; ?>


                <div class="dog-featured-image show-for-small-only">
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

				        ?>

                        <section class="wrapper--adoptions">
                            <div class="adoption--inner">
                                <h3>Adopted!</h3>
                                <p><?php echo get_the_title($dog_id); ?> was adopted <?php echo types_render_field('date-of-adoption') ?> by <?php echo get_the_title($adoption_parents_id); ?>.</p>
                            </div>
                        </section>
				        <?php
			        endwhile;
		        endif; // end of $adoption_query->have_posts()
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

                <?php if(!empty(types_render_field('medical-history'))): ?>
                    <h4><i class="fa fa-medkit" aria-hidden="true"></i> Medical history</h4>
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

            <?php
                if(types_render_field('adoption-status') === 'Available'):
            ?>

            <section class="form--adoption">
                <div class="section-divider">
                    <hr />
                </div>

                <h2>Interested in adopting <?php the_title(); ?>?</h2>
                <p>First, ensure you've filled out the global adoption form because if you haven't, filling this form
                    out below won't do anything. The global adoption can be found here <INSERT LINK HERE>. Once that is
                    complete and submitted, apply to adopt <?php the_title(); ?> again.
                </p>
                <?php echo do_shortcode('[gravityform id="1" title="false" description="true"]'); ?>
            </section>
            <?php endif; ?>
        </div>
<!--        <footer>-->
            <?php # wp_link_pages( array('before' => '<nav id="page-nav"><p>' . __( 'Pages:', 'foundationpress' ), 'after' => '</p></nav>' ) ); ?>
<!--            <p>--><?php //# the_tags(); ?><!--</p>-->
<!--        </footer>-->
        <?php # the_post_navigation(); ?>
        <?php # do_action( 'foundationpress_post_before_comments' ); ?>
        <?php # comments_template(); ?>
        <?php # do_action( 'foundationpress_post_after_comments' ); ?>




		<?php if(!empty(types_render_field( "dog-image"))): ?>
            <div class="kk-modal dog--additional-photos">
                <h4><i class="fa fa-picture-o"></i> Additional images of <?php the_title(); ?></h4>
                <div class="carousel--wrapper">
                    <div id="carousel--dog-detail-page_primary" class="carousel--dog-detail-page_primary">
                        <div class="image">
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





<?php get_footer();


