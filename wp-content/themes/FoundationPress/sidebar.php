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

    <?php
        $woofpages_options  = get_option('woofpages_settings');
        $street_address     = $woofpages_options['woofpages_rescue_location_street_address'];
        $city               = $woofpages_options['woofpages_rescue_location_city'];
        $state              = $woofpages_options['woofpages_rescue_location_state'];
        $zip                = $woofpages_options['woofpages_rescue_location_zip_code'];
        $primary_phone      = $woofpages_options['woofpages_rescue_location_primary_phone'];
    ?>

	<?php if($street_address || $city || $state || $zip || $primary_phone): ?>
        <section class="rescue-contact-info">
            <?php if($street_address || $city || $state || $zip): ?>
                <h4><?php bloginfo( 'name' ); ?></h4>

                <p class="rescue-contact-info--street-address">
                    <i class="fa fa-map-marker"></i> <?php echo $street_address; ?><br />
                    <?php echo $city; ?>, <?php echo $state; ?> <?php echo $zip; ?>
                </p>
            <?php endif; ?>

            <?php if($primary_phone): ?>
                <p class="rescue-contact-info--phone"><i class="fa fa-phone"></i> <?php echo $primary_phone; ?></p>
            <?php endif; ?>


            <?php include 'template-parts/woofpages/_social_media_links.php'; ?>

        </section>
    <?php endif; ?>

	<?php do_action( 'foundationpress_before_sidebar' ); ?>
	<?php dynamic_sidebar( 'sidebar-widgets' ); ?>
	<?php do_action( 'foundationpress_after_sidebar' ); ?>
</aside>
