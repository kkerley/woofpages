<?php
/*
Plugin Name: Recent Dogs function and Shortcode

Description: Modified version of Recent Posts function and Shortcode from WPMU
Author: Kyle Kerley
Version: 1.0

*/

/*
Copyright 2017 Kyle Kerley (http://kkerley.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License (Version 2 - GPLv2) as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/*
Usage:
display_recent_dogs(NUMBER,TITLE_CHARACTERS,CONTENT_CHARACTERS,TITLE_CONTENT_DIVIDER,TITLE_BEFORE,TITLE_AFTER,GLOBAL_BEFORE,GLOBAL_AFTER,BEFORE,AFTER,TITLE_LINK,SHOW_AVATARS,AVATAR_SIZE,POSTTYPE, ECHO);

Ex:
display_recent_dogs(10,40,150,'<br />','<strong>','</strong>','<ul>','</ul>','<li>','</li>','yes','yes',16, 'post', true);
*/
class recentdogsshortcode {

	var $build = 1;

	var $db;

	function __construct() {

		global $wpdb;

		$this->db =& $wpdb;

		if($this->db->blogid == 1) {
			// Only add the feed for the main site
			add_action('init', array(&$this, 'initialise_recentdogsshortcode') );
		}

		add_shortcode( 'globalrecentdogs', array( &$this, 'display_recent_dogs_shortcode') );

	}

	function recentdogsshortcode() {
		$this->__construct();
	}

	function initialise_recentdogsshortcode() {
		// In case we need it in future :)
	}

	function display_recent_dogs($tmp_number,$tmp_title_characters = 0,$tmp_content_characters = 0,$tmp_title_content_divider = '<br />',$tmp_title_before,$tmp_title_after,$tmp_global_before,$tmp_global_after,$tmp_before,$tmp_after,$tmp_title_link = 'no',$tmp_show_avatars = 'yes', $tmp_avatar_size = 16, $posttype = 'post', $output = true) {

		global $network_query, $network_post;

		$network_query = network_query_posts(
			array(
				'post_type' => 'dog',
				'posts_per_page' => $tmp_number,
				'meta_query'        => array(
					'relation'      => 'AND',
					array(
						'key'       => 'wpcf-adoption-status',
						'value'     => "available",
					)
				)
			)
		);

		$html = '';

		if( network_have_posts() ) {
			$html .= $tmp_global_before;
			$default_avatar = get_option('default_avatar');


			while( network_have_posts()) {
				network_the_post();

				$n_post = network_get_post();
				switch_to_blog($n_post->BLOG_ID);

				$blog_name = get_bloginfo('name');
				$blog_url = get_bloginfo('wpurl');
				$the_title = network_get_the_title();
				$adoption_status = get_post_meta(network_get_the_ID(), 'wpcf-adoption-status', true);
				$filter_classes = '';

				$filter_classes .= ' ' . $adoption_status;

				// creating a list of attributes to filter by
				if(!empty(get_post_meta(network_get_the_ID(), 'wpcf-sex', true))):
					$filter_classes .= ' ';
					$filter_classes .= get_post_meta(network_get_the_ID(), 'wpcf-sex', true);
				endif;

				if(!empty(get_post_meta(network_get_the_ID(), 'wpcf-body-size', true))):
					$filter_classes .= ' ';
					$filter_classes .= get_post_meta(network_get_the_ID(), 'wpcf-body-size', true);
				endif;

				$html .= '<div class="card dog'. $filter_classes . '">';
				    $html .= '<div class="card-image">';
				    $html .= get_the_post_thumbnail( $n_post->ID ) ? get_the_post_thumbnail( $n_post->ID, 'dog-square-400' ) : '<img src="http://placehold.it/400x400?text=Need+image" />';
				    $html .= '</div>';
				    $html .= '<div class="card-content">';
					    $html .= '<h4><a href="' . network_get_permalink() . '" >' . substr($the_title,0,$tmp_title_characters) . '</a></h4>';
					    // $html .= '<p>' . get_the_date() .'</p>';
					    $html .= '<p>' . woofpages_current_dog_breeds(network_get_the_ID());
                            if(!empty(get_post_meta(network_get_the_ID(), 'wpcf-sex', true))):
                                $html .= '<span class="dog-sex">' . get_post_meta(network_get_the_ID(), 'wpcf-sex', true) . '</span>';
                            endif;

                            if(!empty(get_post_meta(network_get_the_ID(), 'wpcf-age', true))):
                                $html .= ' | <span class="dog-age">' . get_post_meta(network_get_the_ID(), 'wpcf-age', true) . ' year';
                                $html .= (int)str_replace(' ', '', get_post_meta(network_get_the_ID(), 'wpcf-age', true)) > 1 ? 's ' : '';
                                $html .= ' old</span>';
                            endif;

//                            if(!empty(get_post_meta(network_get_the_ID(), 'wpcf-body-size', true))):
//                                $html .= '| <span class="dog-body-size">' . get_post_meta(network_get_the_ID(), 'wpcf-body-size', true) . '</span>';
//                            endif;

                            if(!empty(get_post_meta(network_get_the_ID(), 'wpcf-weight', true))):
                                $html .= ' | <span class="dog-weight">' . get_post_meta(network_get_the_ID(), 'wpcf-weight', true) . ' lbs</span>';
                            endif;
                        $html .= '</p>';

                        $html .= '<p><a href="' . $blog_url . '" target="_blank">' . $blog_name . ' <i class="fa fa-external-link-square"></i></a></p>';
                        $html .= '<p><i class="fa fa-globe"></i> ' . get_post_meta(network_get_the_ID(), 'wpcf-location-city', true) . ', ' . get_post_meta(network_get_the_ID(), 'wpcf-location-state', true) . ' ' . get_post_meta(network_get_the_ID(), 'wpcf-location-zip', true) . '</p>';

//                    $html .= '<div class="wrapper--characteristics">';
//                        $html .= woofpages_current_dog_characteristics(network_get_the_ID());
//                    $html .= '</div>';

                    $html .= '<div class="wrapper--cta">';
                        $html .= '<a href="' . network_get_permalink() . '" class="button success expanded"><i class="fa fa-info-circle"></i> Meet ' . $the_title . '</a>';
				    $html .= '</div>';
				$html .= '</div>';
				// $html .= $tmp_title_content_divider;

				if ( $tmp_content_characters > 0 ) {
					$the_content = network_get_the_content();
					$html .= substr(strip_tags($the_content),0,$tmp_content_characters);
				}
				// $html .= $tmp_after;
				$html .= '</div>';
				restore_current_blog();
			}
			$html .= $tmp_global_after;
		}

		if($output) {
			echo $html;
		} else {
			return $html;
		}

	}

	function display_recent_dogs_shortcode($atts, $content = null, $code = "") {

		$defaults = array(	'number'	=>	6,
							'title_characters' => 250,
							'content_characters' => 0,
							'title_content_divider' => '<br />',
							'title_before'	=>	'',
							'title_after'	=>	'',
							'global_before'	=>	'',
							'global_after'	=>	'',
							'before'	=>	'',
							'after'	=>	'',
							'title_link' => 'yes',
							'show_avatars' => 'no',
							'avatar_size' => 16,
							'posttype' => 'dog'
						);

		extract(shortcode_atts($defaults, $atts));

		$html = '';

		$html .= $this->display_recent_dogs( $number, $title_characters, $content_characters, $title_content_divider, $title_before, $title_after, $global_before, $global_after, $before, $after, $title_link, $show_avatars, $avatar_size, $posttype, false);

		return $html;

	}

}

function display_recent_dogs($tmp_number,$tmp_title_characters = 0,$tmp_content_characters = 0,$tmp_title_content_divider = '<br />',$tmp_title_before,$tmp_title_after,$tmp_global_before,$tmp_global_after,$tmp_before,$tmp_after,$tmp_title_link = 'no',$tmp_show_avatars = 'yes', $tmp_avatar_size = 16, $posttype = 'post', $output = true) {
	global $recentdogsshortcode;

	$recentdogsshortcode->display_recent_dogs( $tmp_number, $tmp_title_characters, $tmp_content_characters, $tmp_title_content_divider, $tmp_title_before, $tmp_title_after, $tmp_global_before, $tmp_global_after, $tmp_before, $tmp_after, $tmp_title_link, $tmp_show_avatars, $tmp_avatar_size, $posttype, $output );
}

$recentdogsshortcode = new recentdogsshortcode();