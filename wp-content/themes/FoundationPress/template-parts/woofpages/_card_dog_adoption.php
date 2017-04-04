<article class="card dog">
	<?php
	    $dog_id = get_post_meta(get_the_ID(), '_wpcf_belongs_dog_id', true);
	    $adoption_parent_id = get_post_meta(get_the_ID(), '_wpcf_belongs_adoption-parent_id', true);
	    $adoption_status = get_post_meta($dog_id, 'wpcf-adoption-status');
	?>
	<div class="card-image <?php echo $adoption_status[0]; ?>">
		<?php echo get_the_post_thumbnail($dog_id,'fp-small'); ?>
	</div>

	<div class="card-content">
		<h4><a href="<?php echo get_the_permalink($dog_id); ?>"><?php echo get_the_title($dog_id); ?></a></h4>
		<p>Adopted on <?php echo types_render_field('date-of-adoption', array('style' => 'text', 'format' => 'F j, Y')); ?> by <?php echo get_the_title($adoption_parent_id); ?></p>
        <?php the_content(); ?>
		<div class="wrapper--cta">
			<a href="<?php echo get_the_permalink($dog_id); ?>" class="button success expanded"><i class="fa fa-info-circle"></i> Meet <?php echo get_the_title($dog_id); ?></a>
		</div>
	</div>
</article>
