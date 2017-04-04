<?php $adoption_status = get_post_meta(get_the_ID(), 'wpcf-adoption-status'); ?>

<article class="card dog">
	<div class="card-image <?php echo $adoption_status[0]; ?>">
		<?php the_post_thumbnail('fp-small'); ?>
	</div>

	<div class="card-content">
		<h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>

		<p><?php echo woofpages_current_dog_breeds(get_the_ID()); ?>

			<?php if(!empty(types_render_field('sex'))): ?>
				<?php echo types_render_field('sex'); ?>
			<?php endif; ?>

			<?php if(!empty(types_render_field('age'))): ?>
				| <?php echo types_render_field('age'); ?> year<?php echo (int)str_replace(' ', '', types_render_field('age')) > 1 ? 's' : ''; ?> old
			<?php endif; ?>

			<?php if(!empty(types_render_field('body-size'))): ?>
				| <?php echo types_render_field('body-size'); ?>
			<?php endif; ?>

			<?php if(!empty(types_render_field('weight'))): ?>
				| <?php echo types_render_field('weight'); ?> lbs
			<?php endif; ?>
		</p>

		<div class="wrapper--characteristics">
			<?php echo woofpages_current_dog_characteristics(get_the_ID()); ?>
		</div>

		<div class="wrapper--cta">
			<a href="<?php the_permalink(); ?>" class="button success expanded"><i class="fa fa-info-circle"></i> Meet <?php the_title(); ?></a>
		</div>
	</div>
</article>
