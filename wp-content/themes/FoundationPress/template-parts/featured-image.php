<?php
// If a featured image is set, insert into layout and use Interchange
// to select the optimal image size per named media query.
if ( has_post_thumbnail( $post->ID ) ) : ?>
	<header id="featured-hero" role="banner" data-interchange="[<?php echo the_post_thumbnail_url('featured-small'); ?>, small], [<?php echo the_post_thumbnail_url('featured-medium'); ?>, medium], [<?php echo the_post_thumbnail_url('featured-large'); ?>, large], [<?php echo the_post_thumbnail_url('featured-xlarge'); ?>, xlarge]">
		<?php
		    $queried_obj = get_queried_object();

		    if($queried_obj->post_type === 'dog'):
        ?>

			    <?php
			    $breeds = get_terms('breed', array('hide_empty' => true));
			    $characteristics = get_terms('characteristic', array('hide_empty' => true));
			    ?>
                <h1 class="entry-title"><?php the_title(); ?></h1>
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
						    $output .= ', ';
					    endif;
				    endforeach;

				    $output .= " | ";
				    echo $output;
				    ?>

				    <?php echo types_render_field('sex'); ?> |
				    <?php echo types_render_field('age'); ?> year(s) old |
                    <strong><?php echo types_render_field('adoption-status'); ?></strong>
                </p>

			    <?php if(count($characteristics) > 0): ?>
                <!--                    <h4><i class="fa fa-list"></i> Characteristics</h4>-->

                <div class="wrapper--characteristics">
				    <?php
				    $char_output = "";

				    foreach($characteristics as $characteristic):
					    $char_output .= '<a href="' . get_term_link($characteristic->slug, 'characteristic') . '" class="label alert">';
					    $char_output .= $characteristic->name;
					    $char_output .= '</a> ';

				    endforeach;

				    echo $char_output;
				    ?>
                </div>
		    <?php endif; ?>

			    <?php if(!empty(types_render_field('location'))): ?>
                <p><em><i class="fa fa-globe"></i> Currently located in <?php echo types_render_field('location');?></em></p>
		    <?php endif; ?>


		<?php endif; ?>

    </header>
<?php endif;
