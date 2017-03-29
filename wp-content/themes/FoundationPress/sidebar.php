<?php
/**
 * The sidebar containing the main widget area
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

?>
<aside class="sidebar">
    <?php
        $queried_obj = get_queried_object();

        if($queried_obj->post_type === 'dog'):
    ?>
            <div class="dog-featured-image hide-for-small-only<?php echo types_render_field('adoption-status') === 'Adopted' ? ' adopted' : '' ; ?>">
		        <?php the_post_thumbnail('large'); ?>
            </div>

	        <?php if(!empty(types_render_field( "dog-image"))): ?>
                <a href="#" class="button expanded primary kk-modal-trigger hide-for-small-only" data-target-modal="dog--additional-photos"><i class="fa fa-picture-o"></i> More photos</a>
            <?php endif; ?>


    <?php endif; ?>
    <div class="wrapper--ad">
        <img src="https://placehold.it/400x200&text=Ad" alt="Ad" height="200" width="400">
    </div>



	<?php do_action( 'foundationpress_before_sidebar' ); ?>
	<?php dynamic_sidebar( 'sidebar-widgets' ); ?>
	<?php do_action( 'foundationpress_after_sidebar' ); ?>
</aside>
