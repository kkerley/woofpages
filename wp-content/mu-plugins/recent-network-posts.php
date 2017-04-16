<?php
/**
 * Plugin Name: Recent Network Posts
 * Plugin URI: http://stackoverflow.com/q/23713801/1287812
 * Description: Creates a function that lists recent posts from all sites of the network. Call it in another plugins or themes.
 * Author: brasofilo
 */

/**
 * Iterates throught all sites of the network and grab the recent posts
 */
function b5f_print_recent_posts()
{
	$blogs = b5f_get_blog_list( 0, 'all', true );
	$current_blog_id = get_current_blog_id();

	foreach( $blogs as $blog ):
		switch_to_blog( $blog[ 'blog_id' ] );

		$dog_args = array(
			'numberposts' => 6,
			'post_type' => 'dog'
		);

//		$dogs = wp_get_recent_posts( $dog_args, OBJECT );

		echo '<h3>' . $blog['name'] . ' - ' . $blog['domain'] . ' - ' . $blog['desc'] . '</h3>';
		$posts = wp_get_recent_posts( $dog_args, OBJECT );
		if( $posts ):
			foreach( $posts as $post ):
				$permalink = 'http://' . $blog['domain'] . '/dog/' . $post->post_name;

				echo '<div><a href="' . $permalink . '">'. $post->post_title . '</a></div>';
			endforeach;
		endif;
	endforeach;
	switch_to_blog( $current_blog_id );
}

/**
 * Returns an array of arrays containing information about each public blog
 * hosted on this WPMU install.
 *
 * Only blogs marked as public and flagged as safe (mature flag off) are returned.
 *
 * @author Frank Bueltge
 *
 * @param   Integer  The first blog to return in the array.
 * @param   Integer  The number of blogs to return in the array (thus the size of the array).
 *                   Setting this to string 'all' returns all blogs from $start
 * @param   Boolean  Get also Postcount for each blog, default is False for a better performance
 * @param   Integer  Time until expiration in seconds, default 86400s (1day)
 * @return  Array    Returns an array of arrays each representing a blog.
 *                   Details are represented in the following format:
 *                       blog_id   (integer) ID of blog detailed.
 *                       domain    (string)  Domain used to access this blog.
 *                       path      (string)  Path used to access this blog.
 *                       postcount (integer) The number of posts in this blog.
 *                       name      (string) Blog name.
 *                       desc      (string) Blog description.
 */
function b5f_get_blog_list( $start = 0, $num = 10, $details = FALSE, $expires = 86400 ) {

	// get blog list from cache
	$blogs = get_site_transient( 'multisite_blog_list' );

	// For debugging purpose
	if( defined( 'WP_DEBUG' ) && WP_DEBUG ):
		$blogs = FALSE;
	endif;

	if( FALSE === $blogs ):
		global $wpdb;

		// add limit for select
		if ( 'all' === $num ):
			$limit = '';
		else:
			$limit = "LIMIT $start, $num";
		endif;

		$blogs = $wpdb->get_results(
			$wpdb->prepare( "
                SELECT blog_id, domain, path 
                FROM $wpdb->blogs
                WHERE site_id = %d 
                AND public = '1' 
                AND archived = '0' 
                AND mature = '0' 
                AND spam = '0' 
                AND deleted = '0' 
                ORDER BY registered ASC
                $limit
            ", $wpdb->siteid ),
			ARRAY_A );

		// Set the Transient cache
		set_site_transient( 'multisite_blog_list', $blogs, $expires );
	endif;

	// only if usable, set via var
	if ( TRUE === $details ):

		$blog_list = get_site_transient( 'multisite_blog_list_details' );

		// For debugging purpose
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ):
			$blog_list = FALSE;
		endif;

		if ( FALSE === $blog_list ):

			global $wpdb;
			$current_blog_id = get_current_blog_id();
			foreach ( (array) $blogs as $details ):
				$blog_list[ $details['blog_id'] ] = $details;
				$blog_list[ $details['blog_id'] ]['postcount'] = $wpdb->get_var( "
                    SELECT COUNT(ID) 
                    FROM " . $wpdb->get_blog_prefix( $details['blog_id'] ). "posts 
                    WHERE post_status='publish' 
                    AND post_type='page'"
				);
				switch_to_blog( $details['blog_id'] );
				$blog_list[ $details['blog_id'] ]['name'] = get_blog_details()->blogname;
				$blog_list[ $details['blog_id'] ]['desc'] = get_bloginfo( 'description' );
			endforeach;
			switch_to_blog( $current_blog_id );
			// Set the Transient cache
			set_site_transient( 'multisite_blog_list_details', $blog_list, $expires );
		endif;
		unset( $blogs );
		$blogs = $blog_list;
	endif;

	if ( FALSE === is_array( $blogs ) ):
		return array();
	endif;

	return $blogs;
}

//add_action( 'wp_network_dashboard_setup', 'dashboard_setup_so_23713801' );
//
//function dashboard_setup_so_23713801()
//{
//	wp_add_dashboard_widget( 'widget_so_23713801', __( 'Test widget' ), 'print_widget_so_23713801' );
//}
//
//function print_widget_so_23713801()
//{
//	b5f_print_recent_posts();
//}