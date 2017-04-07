<?php
global $booking, $wpdb, $wp_query;
get_header();
?>
    <div id="page" role="main">
        <article class="main-content">
            <header>
                <h1>Events</h1>
                <p>Take part in or spread the word about activities hosted by and/or involving <?php bloginfo('title'); ?>.</p>
            </header>
            <div id="wpmudevevents-wrapper">
                <?php if ( !have_posts() ) : ?>
                    <p><?php $event_ptype = get_post_type_object( 'incsub_event' ); echo $event_ptype->labels->not_found; ?></p>
                <?php else: ?>
                    <div class="wpmudevevents-list">

                    <?php while ( have_posts() ) : the_post(); ?>
                        <div class="event <?php echo Eab_Template::get_status_class($post); ?>">
                            <div class="wpmudevevents-header">
                                <h3><?php echo Eab_Template::get_event_link($post); ?></h3>
                                <a href="<?php the_permalink(); ?>" class="wpmudevevents-viewevent"><?php _e('View event', Eab_EventsHub::TEXT_DOMAIN); ?></a>
                            </div>
                            <?php
                                echo Eab_Template::get_event_details($post);
                            ?>
                            <?php
                                echo Eab_Template::get_rsvp_form($post);
                            ?>
                            <hr />
                        </div>
                    <?php endwhile; ?>
                    </div>
                <?php endif; ?>
            </div>
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
