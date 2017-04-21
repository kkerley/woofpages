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
			</div>
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
</section>
<?php do_action( 'foundationpress_after_content' ); ?>

<?php get_footer();