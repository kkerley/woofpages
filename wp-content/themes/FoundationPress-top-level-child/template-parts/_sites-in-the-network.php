<section class="wrapper--sites-in-the-network">
	<div class="wrapper--headline">
		<h3>Sites in the Woof Pages Network Directory</h3>
	</div>

	<div class="sites-in-the-network--inner vertical">
		<?php
		if ( function_exists( 'get_sites' ) && class_exists( 'WP_Site_Query' ) ):
			$sites = get_sites(array('site__not_in' => '1,3')); // excluding top-level and docs sites
			foreach ( $sites as $site ):
				switch_to_blog( $site->blog_id );
				$name = get_bloginfo( 'name' );
				$woofpages_options  = get_option('woofpages_settings');
				// $rescue_logo        = $woofpages_options['woofpages_rescue_custom_logo'];
				$city               = $woofpages_options['woofpages_rescue_location_city'];
				$state              = $woofpages_options['woofpages_rescue_location_state'];
				$zip                = $woofpages_options['woofpages_rescue_location_zip_code'];

				echo '<div class="card rescue">';
				// echo '<img src="' . $rescue_logo . '" />';
				echo '<h4><a href="' . get_site_url() . '">' . $name . '</a></h4>';
				echo '<p>' . $city . ', ' . $state . ' ' . $zip . '</p>';
				echo '</div>';
				restore_current_blog();
			endforeach;
		endif; ?>
	</div>
</section>