<?php
global $blog_id, $wp_query, $booking, $post, $current_user;
$event = new Eab_EventModel($post);

get_header( );
?>
<?php
the_post();

$start_day = date_i18n('m', strtotime(get_post_meta($post->ID, 'incsub_event_start', true)));
?>

<?php get_template_part( 'template-parts/featured-image' ); ?>

    <div id="single-post" role="main" class="single-event">
        <article class="main-content event <?php echo Eab_Template::get_status_class($post); ?>" id="wpmudevevents-wrapper">
            <header>
                <h1 class="entry-title"><?php echo $event->get_title(); ?></h1>

                <div class="wpmudevevents-contentmeta">
	                <?php echo Eab_Template::get_event_details($post); //event_details(); ?>
                </div>
            </header>

			<?php do_action( 'foundationpress_post_before_entry_content' ); ?>
            <div class="entry-content">
	            <?php
                    add_filter('agm_google_maps-options', 'eab_autoshow_map_off', 99);
                    the_content();
                    remove_filter('agm_google_maps-options', 'eab_autoshow_map_off');
	            ?>
	            <?php if ($event->has_venue_map()): ?>
                    <div class="wpmudevevents-map"><?php echo $event->get_venue_location(Eab_EventModel::VENUE_AS_MAP); ?></div>
	            <?php endif; ?>
                <hr />
	            <?php
                    echo Eab_Template::get_rsvp_form($post);
                    echo Eab_Template::get_inline_rsvps($post);
	            ?>

                <hr />
	            <?php

	            if ($event->is_premium() && $event->user_is_coming() && !$event->user_paid()): ?>
                    <div id="wpmudevevents-payment">
			            <?php _e('You haven\'t paid for this event', Eab_EventsHub::TEXT_DOMAIN); ?>
			            <?php echo Eab_Template::get_payment_forms($post); ?>
                    </div>
	            <?php endif; ?>

	            <?php echo Eab_Template::get_error_notice(); ?>

				<?php # edit_post_link( __( 'Edit', 'foundationpress' ), '<span class="edit-link">', '</span>' ); ?>
            </div>
            <footer>
				<?php wp_link_pages( array('before' => '<nav id="page-nav"><p>' . __( 'Pages:', 'foundationpress' ), 'after' => '</p></nav>' ) ); ?>
                <p><?php the_tags(); ?></p>
            </footer>

			<?php the_post_navigation(); ?>
			<?php # do_action( 'foundationpress_post_before_comments' ); ?>
			<?php # comments_template(); ?>
			<?php # do_action( 'foundationpress_post_after_comments' ); ?>
        </article>

	    <?php do_action( 'foundationpress_after_content' ); ?>
	    <?php get_sidebar(); ?>
    </div>

<?php get_footer('event'); ?>