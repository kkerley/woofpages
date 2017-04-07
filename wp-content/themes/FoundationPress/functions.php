<?php
/**
 * Author: Ole Fredrik Lie
 * URL: http://olefredrik.com
 *
 * FoundationPress functions and definitions
 *
 * Set up the theme and provides some helper functions, which are used in the
 * theme as custom template tags. Others are attached to action and filter
 * hooks in WordPress to change core functionality.
 *
 * @link https://codex.wordpress.org/Theme_Development
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

/** Various clean up functions */
require_once( 'library/cleanup.php' );

/** Required for Foundation to work properly */
require_once( 'library/foundation.php' );

/** Register all navigation menus */
require_once( 'library/navigation.php' );

/** Add menu walkers for top-bar and off-canvas */
require_once( 'library/menu-walkers.php' );

/** Create widget areas in sidebar and footer */
require_once( 'library/widget-areas.php' );

/** Return entry meta information for posts */
require_once( 'library/entry-meta.php' );

/** Enqueue scripts */
require_once( 'library/enqueue-scripts.php' );

/** Add theme support */
require_once( 'library/theme-support.php' );

/** Add Nav Options to Customer */
require_once( 'library/custom-nav.php' );

/** Change WP's sticky post class */
require_once( 'library/sticky-posts.php' );

/** Configure responsive image sizes */
require_once( 'library/responsive-images.php' );

/** If your site requires protocol relative url's for theme assets, uncomment the line below */
// require_once( 'library/protocol-relative-theme-assets.php' );

// Including custom Rescue Settings Panel code
include 'library/woofpages/woofpages_settings_panel.php';


add_filter( 'get_nav_search_box_form', function( $current_form, $item, $depth, $args ){
	$new_form = '<form role="search" method="get" id="searchform" action="' . get_site_url();
	$new_form .= '"><div class="input-group"><input type="text" class="input-group-field" value=""';
	$new_form .= 'name="s" id="s" placeholder="Search"><div class="input-group-button"><button id="searchsubmit"><i class="fa fa-search"></i></button></div></div></form>';
	return $new_form;
}, 10, 4 );



if ( ! function_exists('write_log')) {
	function write_log ( $log )  {
		if ( is_array( $log ) || is_object( $log ) ) {
			error_log( print_r( $log, true ) );
		} else {
			error_log( $log );
		}
	}
}

$blog_id = get_current_blog_id();
if($blog_id !== 1) { // checking to make sure this isn't the top-level site
	add_action( 'gform_after_submission_1', 'woofpages_find_master_adoption_application', 10, 2 );
}

// attempting to get a form based on an email address
function woofpages_find_master_adoption_application($entry, $form){

	// Site-specific adoption form is ID 1
	// $subsite_adoption_app_email = $entry['2']; // 'email' is field 2
	$subsite_adoption_app_email = $entry;

	global $switched;
	switch_to_blog(1); // top level site ID

	$search_criteria = array(
		'field_filters' => array(
			'mode' => 'any',
			array(
				'key' => 2,
				'value' => $subsite_adoption_app_email
			)
		)
	);

	$entries = GFAPI::get_entries($form, $search_criteria); // $form should probably be replaced with 1 here to ensure that it's checking the top-level Adoption Application
	write_log($entries);

	restore_current_blog();

	return $entries;
}

// Modifying the loop for the dog archive page to remove adopted and special needs dogs
function woofpages_remove_adopted_and_special_needs_dogs($query){
	if( !is_admin() && is_post_type_archive( 'dog' ) && $query->is_main_query() ){
		$meta_query = array(
			'relation'      => 'OR',
			array(
				'key'       => 'wpcf-adoption-status',
				'value'     => 'Available',
				'compare'   => '='
			),
			array(
				'key'       => 'wpcf-adoption-status',
				'value'     => 'Pending Adoption',
				'compare'   => '='
			)
		);
		$query->set('posts_per_page', -1);
		$query->set('meta_query', $meta_query);
	}
}
add_action( 'pre_get_posts', 'woofpages_remove_adopted_and_special_needs_dogs', 1 );

// Custom taxonomy list output
function woofpages_current_dog_breeds($dog_id){
	$output = "";
	$count = 0;
	$breeds = get_the_terms($dog_id, 'breed');

	foreach($breeds as $breed):
		$output .= '<a href="' . get_term_link($breed->slug, 'breed') . '" class="dog-breed">';
		$output .= $breed->name;
		$output .= '</a>';
		$count++;
		if($count < count($breeds)):
			$output .= ' / ';
		endif;
	endforeach;

	$output .= " | ";
	return $output;
}

function woofpages_current_dog_characteristics($dog_id){
	$characteristics = get_the_terms(get_the_ID(), 'characteristic');
	$char_output = "";

	foreach($characteristics as $characteristic):
		$char_output .= '<a href="' . get_term_link($characteristic->slug, 'characteristic') . '" class="label secondary dog-characteristic">';
		$char_output .= $characteristic->name;
		$char_output .= '</a> ';

	endforeach;

	return $char_output;
}