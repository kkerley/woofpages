<article class="card volunteer">
	<div class="card-image">
		<?php echo the_post_thumbnail('fp-small'); ?>
	</div>

	<div class="card-content">
		<h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
        <?php the_content(); ?>

	</div>
</article>
