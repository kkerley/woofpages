<?php
/**
 * Save post metadata when a post is saved.
 *
 * @param int $post_id The post ID.
 * @param post $post The post object.
 * @param bool $update Whether this is an existing post being updated or not.
 */

function save_dog_meta( $post_id, $post, $update ) {
	$woofpages_options  = get_option('woofpages_settings');
	$city               = $woofpages_options['woofpages_rescue_location_city'];
	$state              = $woofpages_options['woofpages_rescue_location_state'];
	$zip                = $woofpages_options['woofpages_rescue_location_zip_code'];

	$post_type = get_post_type($post_id);

	// If this isn't a 'dog' post, don't update it.
	if ( "dog" != $post_type ) return;

	// - Update the post's metadata.
	update_post_meta( $post_id, 'wpcf-location-city',$city );
	update_post_meta( $post_id, 'wpcf-location-state',$state );
	update_post_meta( $post_id, 'wpcf-location-zip',$zip );
}

add_action( 'save_post', 'save_dog_meta', 9999, 1 );