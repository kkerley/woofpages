<?php
/*
Template Name: Front
*/
get_header(); ?>

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
			<div class="entry-content">
				<?php the_content(); ?>
			</div>
			<footer>
				<?php wp_link_pages( array('before' => '<nav id="page-nav"><p>' . __( 'Pages:', 'foundationpress' ), 'after' => '</p></nav>' ) ); ?>
				<p><?php the_tags(); ?></p>
			</footer>
			<?php do_action( 'foundationpress_page_before_comments' ); ?>
			<?php comments_template(); ?>
			<?php do_action( 'foundationpress_page_after_comments' ); ?>
		</div>
	</div>
</section>
<?php endwhile;?>
<?php do_action( 'foundationpress_after_content' ); ?>

    <div class="section-divider">
        <hr />
    </div>

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

<?php get_footer();