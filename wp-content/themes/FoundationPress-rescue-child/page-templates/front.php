<?php
/*
Template Name: Front
*/
get_header(); ?>

<?php
$featured_dogs_args = array(
	'post_type'         => 'dog',
	'post_status'       => 'publish',
	'posts_per_page'    => 3,
	'orderby'           => 'date' ,
	'order'             => 'DESC' ,
	'numberposts'       =>  3,
	'meta_query'        => array(
		'relation'      => 'AND',
		array(
			'key'       => 'wpcf-featured',
			'value'     => 1,
		),

		array(
			'key'       => 'wpcf-adoption-status',
			'value'     => "available",
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
<?php do_action( 'foundationpress_after_content' ); ?>

<?php
	if($featured_dogs_query->have_posts()):
		?>
		<section class="wrapper--latest-dogs">
			<div class="wrapper--headline">
				<h2>Featured dogs</h2>
			</div>

			<div class="latest-dogs--inner">
				<div class="latest-dogs vertical">
					<?php
					while($featured_dogs_query->have_posts()): $featured_dogs_query->the_post();
						# $breeds = get_the_terms(get_the_ID(), 'breed');
						# $characteristics = get_the_terms(get_the_ID(), 'characteristic');
						?>

						<?php get_template_part( 'template-parts/woofpages/_card_dog'); ?>

						<?php
					endwhile; ?>
				</div>

				<div class="wrapper--headline">
					<a href="/dogs" class="button warning large">View all dogs! <i class="fa fa-chevron-right"></i></a>
				</div>
			</div>
		</section>
		<?php
	endif; // if $featured_dogs->have_posts()
	wp_reset_postdata();
?>


<section class="wrapper--events">
	<div class="events--inner">
		<div class="wrapper--headline">
			<h2><i class="fa fa-calendar"></i> Upcoming events</h2>
		</div>
		<div class="events--calendar">
			<?php the_widget('Eab_CalendarUpcoming_Widget'); ?>
		</div>

		<div class="events--upcoming">
			<h4>Next Event</h4>
			<?php echo do_shortcode('[eab_archive limit="1"]'); ?>
		</div>
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
				<div class="announcement--inner vertical">
					<?php
					if($announcements_query->have_posts()):
						while($announcements_query->have_posts()): $announcements_query->the_post();
							get_template_part( 'template-parts/woofpages/_card_post_page' );
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
				<div class="posts--inner vertical">
					<?php
					if($posts_query->have_posts()):
						while($posts_query->have_posts()): $posts_query->the_post();
							get_template_part( 'template-parts/woofpages/_card_post_page' );
						endwhile;
					else:
						echo '<p><em>No blog posts at this time</em></p>';
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