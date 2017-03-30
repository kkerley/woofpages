<?php
/*
Template Name: Front
*/
get_header(); ?>

<?php
    $featured_dogs_args = array(
        'post_type'         => 'dog',
        'orderby'           => 'date' ,
        'order'             => 'DESC' ,
        'posts_per_page'    => 5,
        'meta_query'        => array(
            'relation'      => 'AND',
            array(
	            'key'       => 'wpcf-featured',
	            'value'     => 1,
            ),

            array(
	            'key'       => 'wpcf-adoption-status',
	            'value'     => "Available",
            )
        )

    );

    $featured_dogs_query = new WP_Query($featured_dogs_args);
?>

<header id="front-hero" role="banner" <?php echo !empty(types_render_field('mission-statement-background-image', array('raw' => true))) ? ' style="background-image: url(' . types_render_field('mission-statement-background-image', array('raw' => true)) . ');"' : ''; ?>>
	<div class="marketing">
		<div class="tagline">
			<h1><?php echo types_render_field( 'mission-statement-headline' ); ?></h1>
			<?php echo types_render_field( 'mission-statement-content' ); ?>
            <?php if(!empty(types_render_field('mission-statement-cta-url'))): ?>
			<a role="button" class="primary large button sites-button" href="<?php echo types_render_field('mission-statement-cta-url'); ?>"><?php echo types_render_field('mission-statement-cta-text') ?></a>
            <?php endif; ?>
		</div>

<!--		<div id="watch">-->
<!--			<section id="stargazers">-->
<!--				<a href="https://github.com/olefredrik/foundationpress">1.5k stargazers</a>-->
<!--			</section>-->
<!--			<section id="twitter">-->
<!--				<a href="https://twitter.com/olefredrik">@olefredrik</a>-->
<!--			</section>-->
<!--		</div>-->
	</div>
</header>

<?php do_action( 'foundationpress_before_content' ); ?>
<?php while ( have_posts() ) : the_post(); ?>
<section class="intro" role="main">
	<div class="fp-intro">
		<div <?php post_class() ?> id="post-<?php the_ID(); ?>">
			<?php do_action( 'foundationpress_page_before_entry_content' ); ?>
			<div class="entry-content<?php echo $featured_dogs_query->post_count > 0 ? ' has-featured-dogs' : '' ?>">
				<?php the_content(); ?>
			</div>

            <?php
                if($featured_dogs_query->have_posts()): ?>
                    <div class="featured-dogs">
                        <h3><i class="fa fa-certificate" aria-hidden="true"></i> Featured</h3>
                        <div class="featured-dogs--inner">
                    <?php
                        while($featured_dogs_query->have_posts()): $featured_dogs_query->the_post();
	                        $breeds = get_the_terms(get_the_ID(), 'breed');
	                        $characteristics = get_the_terms(get_the_ID(), 'characteristic');
                    ?>

                            <article class="card dog vertical">
                                <div class="card-image">
                                    <?php the_post_thumbnail('dog-square-400') ?>
                                </div>

                                <div class="card-content">
                                    <h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
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
		                                        $output .= ' / ';
	                                        endif;
                                        endforeach;

                                        echo $output;
                                        ?>
                                        <br />
	                                    <?php if(!empty(types_render_field('sex'))): ?>
		                                    <?php echo types_render_field('sex'); ?>
	                                    <?php endif; ?>

	                                    <?php if(!empty(types_render_field('age'))): ?>
                                            <?php echo types_render_field('age'); ?> year<?php echo (int)str_replace(' ', '', types_render_field('age')) > 1 ? 's' : ''; ?> old
	                                    <?php endif; ?>
                                        <br />
	                                    <?php if(!empty(types_render_field('body-size'))): ?>
                                            <?php echo types_render_field('body-size'); ?>
	                                    <?php endif; ?>

	                                    <?php if(!empty(types_render_field('weight'))): ?>
                                            <?php echo types_render_field('weight'); ?> lbs
	                                    <?php endif; ?>

	                                    <?php if(types_render_field('prefers-a-home-without')): ?>
                                            <br />Prefers a home without <?php echo types_render_field( 'prefers-a-home-without', array('separator' => ', ' )); ?>
                                        <?php endif; ?>
                                    </p>

                                    <div class="wrapper--characteristics">
		                                <?php
		                                $char_output = "";

		                                foreach($characteristics as $characteristic):
			                                $char_output .= '<a href="' . get_term_link($characteristic->slug, 'characteristic') . '" class="label primary">';
			                                $char_output .= $characteristic->name;
			                                $char_output .= '</a> ';

		                                endforeach;

		                                echo $char_output;
		                                ?>
                                    </div>

                                    <div class="wrapper--cta">
                                        <a href="<?php the_permalink(); ?>" class="button success expanded"><i class="fa fa-info-circle"></i> Meet <?php the_title(); ?></a>
                                    </div>
                                </div>
                            </article>

                            <?php
                        endwhile; ?>
                    </div>
                </div>
            <?php
                endif;
            wp_reset_postdata();
            ?>
		</div>
	</div>
</section>
<?php endwhile;?>
<?php do_action( 'foundationpress_after_content' ); ?>

<?php
    $blog_id = get_current_blog_id();
    if($blog_id !== 1): // checking to make sure this isn't the top-level site
?>
        <section class="wrapper--latest-dogs">
            <div class="wrapper--headline">
                <h2>Newest dogs at <?php bloginfo( 'name' ); ?></h2>
            </div>

            <div class="latest-dogs--inner">
	            <div class="latest-dogs">
		            <?php echo do_shortcode('[wpv-view name="latest-dogs"]'); ?>
                </div>

                <div class="wrapper--headline">
                    <a href="/dogs" class="button warning large">View all dogs! <i class="fa fa-chevron-right"></i></a>
                </div>
            </div>
        </section>
<?php
    else:
?>
        <section class="wrapper--sites-in-the-network">
            <div class="wrapper--headline">
                <h3>Sites in the Woof Pages Network Directory</h3>
            </div>

            <div class="sites-in-the-network--inner">
                <?php
                if ( function_exists( 'get_sites' ) && class_exists( 'WP_Site_Query' ) ):
                    $sites = get_sites(array('site__not_in' => '1'));
                    foreach ( $sites as $site ):
                        switch_to_blog( $site->blog_id );
                        $name = get_bloginfo( 'name' );
	                    $woofpages_options  = get_option('woofpages_settings');
	                    // $rescue_logo        = $woofpages_options['woofpages_rescue_custom_logo'];
	                    $city               = $woofpages_options['woofpages_rescue_location_city'];
	                    $state              = $woofpages_options['woofpages_rescue_location_state'];
	                    $zip                = $woofpages_options['woofpages_rescue_location_zip_code'];

                        echo '<div class="card vertical rescue">';
	                    // echo '<img src="' . $rescue_logo . '" />';
                        echo '<h4><a href="' . get_site_url() . '">' . $name . '</a></h4>';
                        echo '<p>' . $city . ', ' . $state . ' ' . $zip . '</p>';
                        echo '</div>';
                        restore_current_blog();
                    endforeach;
                endif; ?>
            </div>
        </section>
        <?php
    endif;
?>

<section class="wrapper--events">
    <div class="events--inner">
	    <?php the_widget('Eab_CalendarUpcoming_Widget'); ?>
        <?php echo do_shortcode('[eab_archive limit="1"]'); ?>
    </div>
</section>


<?php
    $announcements_args = array(
	    'post_type'         => 'post' ,
	    'orderby'           => 'date' ,
	    'order'             => 'DESC' ,
	    'posts_per_page'    => 1,
	    'cat'               => '13',
    );

    $announcements_query = new WP_Query($announcements_args);

    $posts_args = array(
	    'post_type'         => 'post' ,
	    'orderby'           => 'date' ,
	    'order'             => 'DESC' ,
	    'posts_per_page'    => 2,
	    'cat'               => '-13',
    );

    $posts_query = new WP_Query($posts_args);
?>

    <section class="wrapper--announcements-and-posts">
        <div class="announcements-and-posts">
            <div class="announcements--outer">
                <div class="wrapper--headline">
                    <h3><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Announcements</h3>
                </div>
                <div class="announcement--inner">
                    <?php
                        if($announcements_query->have_posts()):
                            while($announcements_query->have_posts()): $announcements_query->the_post();
                    ?>
                        <div class="card vertical">
                            <div class="card-image">
                                <?php the_post_thumbnail('dog-square-400'); ?>
                            </div>
                            <div class="card-content">
                                <h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
                                <p><?php echo wp_trim_words( get_the_content(), 40, '...' ); ?></p>
                                <p class="text-right"><a href="<?php the_permalink(); ?>">Continue reading <i class="fa fa-chevron-right"></i></a></p>
                            </div>
                        </div>
                    <?php
                            endwhile;
                        else:
                            echo '<p><em>No announcements at this time</em></p>';
                        endif;

                        wp_reset_postdata();
                    ?>
                </div>

                <div class="wrapper--cta">
                    <a href="/category/announcements/" class="button primary">All announcements <i class="fa fa-chevron-right"></i></a>
                </div>
            </div>

            <div class="posts--outer">
                <div class="wrapper--headline">
                    <h3><i class="fa fa-rss" aria-hidden="true"></i> Latest from our blog</h3>
                </div>
                <div class="posts--inner">
		            <?php
		            if($posts_query->have_posts()):
			            while($posts_query->have_posts()): $posts_query->the_post();
				            ?>
                            <div class="card vertical">
                                <div class="card-image">
						            <?php the_post_thumbnail('dog-square-400'); ?>
                                </div>
                                <div class="card-content">
                                    <h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
                                    <p><?php echo wp_trim_words( get_the_content(), 40, '...' ); ?></p>
                                    <p class="text-right"><a href="<?php the_permalink(); ?>">Continue reading <i class="fa fa-chevron-right"></i></a></p>
                                </div>
                            </div>
				            <?php
			            endwhile;
		            else:
			            echo '<p><em>No announcements at this time</em></p>';
		            endif;

		            wp_reset_postdata();
		            ?>
                </div>

                <div class="wrapper--cta">
                    <a href="/blog" class="button primary">All blog posts <i class="fa fa-chevron-right"></i></a>
                </div>
            </div>
        </div>
    </section>

<?php get_footer();