<div class="card post-or-page">
	<div class="card-image">
		<?php the_post_thumbnail('dog-square-400'); ?>
	</div>
	<div class="card-content">
		<h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
		<p><?php the_date(); ?></p>
		<p><?php echo wp_trim_words( get_the_content(), 40, '...' ); ?></p>
		<p class="text-right"><a href="<?php the_permalink(); ?>">Continue reading <i class="fa fa-chevron-right"></i></a></p>
	</div>
</div>