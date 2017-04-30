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
				<a role="button" class="success large button sites-button" href="<?php echo types_render_field('mission-statement-cta-url'); ?>"><?php echo types_render_field('mission-statement-cta-text') ?></a>
			<?php endif; ?>
		</div>
	</div>
</header>

<?php do_action( 'foundationpress_before_content' ); ?>
<?php while ( have_posts() ) : the_post(); ?>
	<section class="intro" role="main">
		<div class="fp-intro">
            <div class="wrapper--headline">
                <h2>Benefits</h2>
            </div>

            <section class="three-across">
                <article class="three-across--item">
                    <div class="icon"><i class="fa fa-file-text-o" aria-hidden="true"></i></div>

                    <h3>One Application</h3>

                    <div class="content">
                        <p>Fill out a single application and adopt any dog from any rescue that's part of our network!</p>
                    </div>

                    <p><a href="/adoption-application/" class="success button sites-button">Apply now! <i class="fa fa-chevron-right"></i></a></p>

                </article>

                <article class="three-across--item">
                    <div class="icon"><i class="fa fa-search" aria-hidden="true"></i></div>

                    <h3>Global Search</h3>

                    <div class="content">
                        <p>Find dogs from any rescue in the network based on sex, body size, location, and breed using our powerful, fast global search! </p>
                    </div>

                    <p><a href="/dog-search/" class="success button sites-button">Find your dog now! <i class="fa fa-chevron-right"></i></a></p>


                </article>

                <article class="three-across--item for-rescues">
                    <div class="icon">
                        <i class="fa fa-sitemap" aria-hidden="true"></i>
                    </div>

                    <h3>Free Website</h3>

                    <div class="content">
                        <p>We provide the platform, you provide the dogs. Do you run a rescue? If so, let us take care of your website needs...for free!</p>
                    </div>

                    <p><a href="/join/" class="button sites-button original-blue">Join Our Network! <i class="fa fa-chevron-right"></i></a></p>
                </article>

            </section>
        </div>
	</section>
<?php endwhile;?>

<section class="wrapper--latest-dogs">
    <div class="wrapper--headline">
        <h2>Latest dogs</h2>
    </div>

    <div class="latest-dogs--inner">
        <div class="latest-dogs vertical">
            <?php echo do_shortcode('[globalrecentdogs number="12"]'); ?>
        </div>
    </div>

    <div class="wrapper--cta">
        <a class="button success sites-button" href="/dog-search">Search for dogs! <i class="fa fa-chevron-right"></i></a>
    </div>
</section>
<?php do_action( 'foundationpress_after_content' ); ?>

<?php get_footer();