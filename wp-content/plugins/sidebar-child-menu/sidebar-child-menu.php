<?php
/*
Plugin Name: Sidebar Child Menu
Plugin URI:
Description: A plugin that creates a sidebar menu of all child elements of the parent element
Version: 0.2
Author: Kyle Kerley
Author URI: http://kkerley.com
License: GPL2
*/


/**
 * Checks the current object to see its type, then gathers any child pages to create a simple nested menu
 */

class scm_child_menu extends WP_Widget{

	public function __construct() {
		$widget_options = array(
			'classname'     => 'scm_family_menu',
			'description'   => 'Builds a localized family tree menu for nested page structures',
		);
		parent::__construct( 'scm_family_menu', 'Sidebar Child Menu', $widget_options );
	}

	public function widget( $args, $instance ) {

		$queried_object = get_queried_object();
		$queried_object_id = $queried_object->ID;

		if($queried_object_id):
			$queried_object_permalink = get_post_permalink($queried_object_id);
			$queried_object_post_type = get_post_type($queried_object);
		else:
			$queried_object_post_type = $queried_object->name;
			$queried_object_permalink = get_post_type_archive_link($queried_object_post_type);
			// echo $queried_object_permalink;
		endif;

		$menu = '';

		if( $queried_object_post_type === 'page' ):
			$ancestors = get_post_ancestors($queried_object);

			if( $ancestors ):
				$top_level_parent_id = end($ancestors);
			else:
				$top_level_parent_id = $queried_object_id;
			endif;

			$second_level_parents_args = array(
				'post_type'             => $queried_object_post_type,
				'posts_per_page'        => -1,
				'post_status'           => 'publish',
				'orderby'								=> 'menu_order',
				'order'                 => 'asc',
				'post_parent'           => $top_level_parent_id,
			);
			$second_level_parents = get_children($second_level_parents_args);

			if($second_level_parents):
				echo $args['before_widget'];
				# echo $args['before_title'] . $args['after_title'];
				$menu .= '<ul class="scm-sidebar-child-menu" data-icon-menu-open="' . $instance['custom_icons_open_menu'];
				$menu .= '" data-icon-menu-closed="' . $instance['custom_icons_closed_menu'] . '" ';
				$menu .= 'data-disable-js="'. $instance['disable_js'] . '" ';
				$menu .= 'data-disable-indentation="' . $instance['disable_indentation'] . '">';
				$menu .= '<li class="parent-item has-children top-level';
				$menu .= ($queried_object_permalink === get_post_permalink($top_level_parent_id) ? ' current-page' : '');
				$menu .= '"><a href="' . get_the_permalink($top_level_parent_id) . '">' . get_the_title($top_level_parent_id) . '</a>';

				$menu .= '<ul class="child-menu">';

				foreach($second_level_parents as $child_post):
					$grandchild_args = array(
						'post_type'             => $queried_object_post_type,
						'posts_per_page'        => -1,
						'post_status'           => 'publish',
						'orderby'								=> 'menu_order',
						'order'                 => 'asc',
						'post_parent'           => $child_post->ID,
					);

					$grandchildren = get_children($grandchild_args);

					$menu .= '<li class="menu-item';
					$menu .= ($queried_object_permalink === get_post_permalink($child_post->ID) ? ' current-page' : '');
					$menu .= ($grandchildren ? ' parent-item has-children' : '');

					if($grandchildren):
						foreach($grandchildren as $grand_post):
							if($queried_object_permalink === get_post_permalink($grand_post->ID)):
								$menu .= ' active-trail';
							endif;
						endforeach;
					endif;
					$menu .= '"><a href="' . get_post_permalink($child_post->ID) . '">' . $child_post->post_title . '</a>';

					if($grandchildren):
						$menu .= '<ul class="child-menu">';

						foreach($grandchildren as $grand_post):
							$menu .= '<li class="menu-item';
							$menu .= ($queried_object_permalink === get_post_permalink($grand_post->ID) ? ' current-page' : '');

							$menu .= '"><a href="' . get_post_permalink($grand_post->ID) . '">' . $grand_post->post_title . '</a>';
							$menu .= '</li>';
						endforeach;

						$menu .= '</ul>';
					endif;

					$menu .= '</li>';
				endforeach;

				$menu .= '</ul></li></ul>';
			endif;

			if($menu):
				echo $menu;
				echo $args['after_widget'];
			endif;

		elseif(is_post_type_archive($queried_object_post_type) || $queried_object_post_type !== 'page' || $queried_object_post_type !== 'post' || $queried_object_post_type !== 'dog'):

			$post_type_obj = get_post_type_object($queried_object_post_type);
			$top_level_cpt_entries_args = array(
				'post_type'             => $queried_object_post_type,
				'posts_per_page'        => -1,
				'post_status'           => 'publish',
				'orderby'								=> 'menu_order',
				'order'                 => 'asc',
				'post_parent'           => 0,
			);

			$top_level_cpt_entries = new WP_Query( $top_level_cpt_entries_args );

			if($top_level_cpt_entries->have_posts()):
				echo $args['before_widget'];
				# echo $args['before_title'] . $args['after_title'];
				$menu .= '<ul class="scm-sidebar-child-menu" data-icon-menu-open="' . $instance['custom_icons_open_menu'];
				$menu .= '" data-icon-menu-closed="' . $instance['custom_icons_closed_menu'] . '" ';
				$menu .= 'data-disable-js="'. $instance['disable_js'] . '" ';
				$menu .= 'data-disable-indentation="' . $instance['disable_indentation'] . '">';
				$menu .= '<li class="parent-item has-children top-level';
				$menu .= (is_post_type_archive($queried_object_post_type) ? ' current-page' : '');
				$menu .= '"><a href="' . get_post_type_archive_link( $queried_object_post_type ) . '">' . $post_type_obj->labels->name . '</a>';
				$menu .= '<ul class="child-menu">';

				while($top_level_cpt_entries->have_posts()):
					$top_level_cpt_entries->the_post();

					$top_level_cpt_entry_children_args = array(
						'post_type'             => $queried_object_post_type,
						'posts_per_page'        => -1,
						'post_status'           => 'publish',
						'orderby'								=> 'menu_order',
						'order'                 => 'asc',
						'post_parent'           => get_the_ID(),
					);

					$top_level_cpt_entry_children = new WP_Query($top_level_cpt_entry_children_args);

					$menu .= '<li class="menu-item';
					$menu .= (!is_post_type_archive($queried_object_post_type) && get_the_permalink() === get_post_permalink($queried_object_id) ? ' current-page' : '');

					$parent_page_title = get_the_title();
					$parent_page_permalink = get_the_permalink();

					if($top_level_cpt_entry_children->have_posts()):
						$menu .= ' parent-item has-child';

						while($top_level_cpt_entry_children->have_posts()):
							$top_level_cpt_entry_children->the_post();

							if(!is_post_type_archive($queried_object_post_type) && get_the_permalink() === get_post_permalink($queried_object_id)):
								$menu .= ' active-trail';
							endif;
						endwhile;
						wp_reset_postdata();
					endif;

					$menu .= '"><a href="' . $parent_page_permalink . '">' . $parent_page_title . '</a>';

					if($top_level_cpt_entry_children->have_posts()):
						$menu .= '<ul class="child-menu">';

						while($top_level_cpt_entry_children->have_posts()):
							$top_level_cpt_entry_children->the_post();

							$menu .= '<li class="menu-item';
							$menu .= (get_the_permalink() === get_post_permalink($queried_object_id) ? ' current-page' : '');
							$menu .= '"><a href="' . get_the_permalink() . '">' . get_the_title() . '</a></li>';
						endwhile;
						wp_reset_postdata();
						$menu .= '</ul></li>';

					else:
						$menu .= '</li>';
					endif;
				endwhile;
				wp_reset_postdata();

				$menu .= '</ul>';

				$menu .= '</li></ul>';

				if($menu):
					echo $menu;
					echo $args['after_widget'];
				endif;
			endif;
		endif;
	}

	public function form( $instance ) {
		# Options for defining custom icons for parent menu items
		# Assumes Font Awesome is being used

		$custom_icons_open_menu = !empty($instance['custom_icons_open_menu']) ? $instance['custom_icons_open_menu'] : '';
		$custom_icons_closed_menu = !empty($instance['custom_icons_closed_menu']) ? $instance['custom_icons_closed_menu'] : '';
		$disable_js = isset( $instance['disable_js'] ) ? (bool) $instance['disable_js'] : false;
		$disable_indentation = isset( $instance['disable_indentation'] ) ? (bool) $instance['disable_indentation'] : false;

		echo '<p>';
		echo '<label for="' . $this->get_field_id( 'custom_icons_open_menu' ) . '">Custom icon for open child menu: </label>';
		echo '<input type="text" id="' .  $this->get_field_id( 'custom_icons_open_menu' ) . '" name="' . $this->get_field_name( 'custom_icons_open_menu' ) . '" value="' . esc_attr( $custom_icons_open_menu ) . '" />';
		echo '</p>';

		echo '<p>';
		echo '<label for="' . $this->get_field_id( 'custom_icons_closed_menu' ) . '">Custom icon for closed child menu: </label>';
		echo '<input type="text" id="' .  $this->get_field_id( 'custom_icons_closed_menu' ) . '" name="' . $this->get_field_name( 'custom_icons_closed_menu' ) . '" value="' . esc_attr( $custom_icons_closed_menu ) . '" />';
		echo '</p>';

		echo '<p>';
		echo '<input type="checkbox" ' . checked( $disable_js, true, false ) . 'id="' . $this->get_field_id( 'disable_js' ) . '" name="' . $this->get_field_name( 'disable_js' ) . '" />';
        echo '<label for="' . $this->get_field_id( 'disable_js' ) . '">Disable jQuery to always show full menu tree?</label>';
		echo '</p>';

		echo '<p>';
		echo '<input type="checkbox" ' . checked( $disable_indentation, true, false ) . 'id="' . $this->get_field_id( 'disable_indentation' ) . '" name="' . $this->get_field_name( 'disable_indentation' ) . '" />';
		echo '<label for="' . $this->get_field_id( 'disable_indentation' ) . '">Disable child menu indentation?</label>';
		echo '</p>';
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['custom_icons_open_menu'] = strip_tags( $new_instance['custom_icons_open_menu']);
		$instance['custom_icons_closed_menu'] = strip_tags( $new_instance['custom_icons_closed_menu']);
		$instance['disable_js'] = $new_instance['disable_js'];
		$instance['disable_indentation'] = $new_instance['disable_indentation'];
		return $instance;
	}
}

function scm_register_child_menu() {
	register_widget( 'scm_child_menu' );
}
add_action( 'widgets_init', 'scm_register_child_menu' );

function scm_enqueue_scripts_and_styles(){
	wp_register_script( 'scm-functionality', plugins_url( '/js/scm-functionality.js', __FILE__ ), array( 'jquery' ) );
	wp_enqueue_script( 'scm-functionality' );

	wp_register_style( 'scm-styles', plugins_url( '/css/scm-styles.css', __FILE__ ) );
	wp_enqueue_style( 'scm-styles' );
}
add_action( 'wp_enqueue_scripts', 'scm_enqueue_scripts_and_styles' );