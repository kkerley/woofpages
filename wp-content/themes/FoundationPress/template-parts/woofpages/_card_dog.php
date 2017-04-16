<?php
    $adoption_status = types_render_field('adoption-status', array('raw'=>'true'));
    $filter_classes = '';
    $filter_classes .= $adoption_status . ' ';

    // creating a list of attributes to filter by
    if(!empty(types_render_field('sex'))):
	    $filter_classes .= types_render_field('sex', array('raw'=>'true'));
        $filter_classes .= ' ';
    endif;

    if(!empty(types_render_field('body-size'))):
	    $filter_classes .= types_render_field('body-size', array('raw'=>'true'));
	    $filter_classes .= ' ';
    endif;
    // end of creating a list of attributes to filter by
?>


<article class="mix card dog<?php if($filter_classes){ echo ' ' . $filter_classes; } ?>">
	<div class="card-image <?php echo $adoption_status[0]; ?>">
		<?php the_post_thumbnail('fp-small'); ?>
	</div>

	<div class="card-content">
		<h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
        <p><?php echo get_the_date(); ?></p>

        <p><?php echo woofpages_current_dog_breeds(get_the_ID()); ?>

			<?php if(!empty(types_render_field('sex'))): ?>
                <span class="dog-sex"><?php echo types_render_field('sex'); ?></span>
			<?php endif; ?>

			<?php if(!empty(types_render_field('age'))): ?>
				| <span class="dog-age"><?php echo types_render_field('age'); ?> year<?php echo (int)str_replace(' ', '', types_render_field('age')) > 1 ? 's' : ''; ?> old</span>
			<?php endif; ?>

			<?php if(!empty(types_render_field('body-size'))): ?>
				| <span class="dog-body-size"><?php echo types_render_field('body-size'); ?></span>
			<?php endif; ?>

			<?php if(!empty(types_render_field('weight'))): ?>
				| <span class="dog-weight"><?php echo types_render_field('weight'); ?> lbs</span>
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
