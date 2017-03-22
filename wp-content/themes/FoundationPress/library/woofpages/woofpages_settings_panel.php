<?php
// Custom Woofpages Settings Panel code
add_action( 'admin_menu', 'woofpages_add_admin_menu' );
add_action( 'admin_init', 'woofpages_init_rescue_custom_logo_settings' );
add_action( 'admin_init', 'woofpages_init_rescue_location_settings' );
add_action( 'admin_init', 'woofpages_init_social_settings' );
// add_action( 'admin_init', 'woofpages_options_setup' );
add_action( 'admin_enqueue_scripts', 'woofpages_admin_scripts' );
add_action( 'after_setup_theme', 'woofpages_options_init' );

// Disabled as I was able to use the new/current media library
//function woofpages_options_setup() {
//	global $pagenow;
//
//	if ( 'media-upload.php' == $pagenow || 'async-upload.php' == $pagenow ) {
//		// Now we'll replace the 'Insert into Post Button' inside Thickbox
//		add_filter( 'gettext', 'woofpages_replace_thickbox_text'  , 1, 3 );
//	}
//}

// Disabled as I was able to use the new/current media library
//function woofpages_replace_thickbox_text($translated_text, $text, $domain) {
//	if ('Insert into Post' == $text) {
//		$referer = strpos( wp_get_referer(), 'woofpages_settings_panel' );
//		if ( $referer != '' ) {
//			return __('I want this to be my logo!', 'wordpress' );
//		}
//	}
//	return $translated_text;
//}
function woofpages_admin_scripts(){

	if( 'toplevel_page_woofpages_settings_panel' === get_current_screen()->id):
		wp_enqueue_script('woofpages-uploads', get_template_directory_uri() . '/library/woofpages/js/woofpages-uploads.js', array('jquery','media-upload','thickbox'));
	endif;
}

function woofpages_get_default_options() {
	$options = array(
		'woofpages_rescue_custom_logo' => '',
		'woofpages_rescue_custom_logo_preview' => '',
		'woofpages_rescue_location_street_address' => '',
		'woofpages_rescue_location_city' => '',
		'woofpages_rescue_location_state' => '',
		'woofpages_rescue_location_zip_code' => '',
		'woofpages_rescue_location_primary_phone' => '',
		'woofpages_rescue_location_primary_email' => '',
		'woofpages_social_facebook' => '',
		'woofpages_social_twitter' => '',
		'woofpages_social_youtube' => '',
		'woofpages_social_linkedin' => '',
		'woofpages_social_googleplus' => '',
		'woofpages_social_pinterest' => '',
	);
	return $options;
}

function woofpages_options_init() {
	$options = get_option( 'woofpages_settings' );

	// Are our options saved in the DB?
	if ( $options === false ) {
		// If not, we'll save our default options
		$options = woofpages_get_default_options();
		add_option( 'woofpages_settings', $options );
	}
	// In other words, we don't need to update the DB
}


// Disabled as I was able to use the new/current media library
//function woofpages_delete_image( $image_url ) {
//	global $wpdb;
//
//	// We need to get the image's meta ID.
//	$query = "SELECT ID FROM $wpdb->posts where guid = '" . esc_url($image_url) . "' AND post_type = 'attachment'";
//	$results = $wpdb->get_results($query);
//
//	// And delete it
//	foreach ( $results as $row ) {
//		wp_delete_attachment( $row->ID );
//	}
//}

function woofpages_add_admin_menu() {
	add_menu_page( 'Woofpages Settings Panel', 'Woofpages', 'manage_options', 'woofpages_settings_panel', 'woofpages_options_page' );
}

function woofpages_init_rescue_custom_logo_settings(){
	register_setting( 'woofpages_rescue_custom_logo_settings', 'woofpages_settings' );

	add_settings_section(
		'woofpages_rescue_custom_logo_settings_section',
		__( 'Rescue custom logo', 'wordpress' ),
		'woofpages_rescue_custom_logo_settings_section_callback',
		'woofpages_rescue_custom_logo_settings'
	);

	// START rescue customization fields
	add_settings_field(
		'woofpages_rescue_custom_logo',
		__( 'Rescue logo', 'wordpress' ),
		'woofpages_rescue_custom_logo_render',
		'woofpages_rescue_custom_logo_settings',
		'woofpages_rescue_custom_logo_settings_section'
	);

	add_settings_field(
		'woofpages_rescue_custom_logo_preview',
		__( 'Logo preview', 'wordpress' ),
		'woofpages_rescue_custom_logo_preview_render',
		'woofpages_rescue_custom_logo_settings',
		'woofpages_rescue_custom_logo_settings_section'
	);
	// END rescue customization fields
}

function woofpages_init_rescue_location_settings() {

	register_setting( 'woofpages_rescue_location_settings', 'woofpages_settings' );

	add_settings_section(
		'woofpages_rescue_location_settings_section',
		__( 'Rescue location settings', 'wordpress' ),
		'woofpages_rescue_location_settings_section_callback',
		'woofpages_rescue_location_settings'
	);

	// START rescue location fields
	add_settings_field(
		'woofpages_rescue_location_abbreviation',
		__( 'Rescue acronym/abbreviation', 'wordpress' ),
		'woofpages_rescue_location_abbreviation_render',
		'woofpages_rescue_location_settings',
		'woofpages_rescue_location_settings_section'
	);

    add_settings_field(
		'woofpages_rescue_location_street_address',
		__( 'Street address', 'wordpress' ),
		'woofpages_rescue_location_street_address_render',
		'woofpages_rescue_location_settings',
		'woofpages_rescue_location_settings_section'
	);

	add_settings_field(
		'woofpages_rescue_location_city',
		__( 'City', 'wordpress' ),
		'woofpages_rescue_location_city_render',
		'woofpages_rescue_location_settings',
		'woofpages_rescue_location_settings_section'
	);

	add_settings_field(
		'woofpages_rescue_location_state',
		__( 'State', 'wordpress' ),
		'woofpages_rescue_location_state_render',
		'woofpages_rescue_location_settings',
		'woofpages_rescue_location_settings_section'
	);

	add_settings_field(
		'woofpages_rescue_location_zip_code',
		__( 'ZIP code', 'wordpress' ),
		'woofpages_rescue_location_zip_code_render',
		'woofpages_rescue_location_settings',
		'woofpages_rescue_location_settings_section'
	);

	add_settings_field(
		'woofpages_rescue_location_primary_phone',
		__( 'Primary phone #', 'wordpress' ),
		'woofpages_rescue_location_primary_phone_render',
		'woofpages_rescue_location_settings',
		'woofpages_rescue_location_settings_section'
	);

	add_settings_field(
		'woofpages_rescue_location_primary_email',
		__( 'Primary email address', 'wordpress' ),
		'woofpages_rescue_location_primary_email_render',
		'woofpages_rescue_location_settings',
		'woofpages_rescue_location_settings_section'
	);
	// END rescue location fields
}

function woofpages_init_social_settings() {

	register_setting( 'woofpages_social_media_settings', 'woofpages_settings' );

	add_settings_section(
		'woofpages_social_media_settings_section',
		__( 'Social media settings', 'wordpress' ),
		'woofpages_social_media_settings_section_callback',
		'woofpages_social_media_settings'
	);

	// START social media fields
	add_settings_field(
		'woofpages_social_facebook',
		__( 'Facebook URL', 'wordpress' ),
		'woofpages_social_facebook_render',
		'woofpages_social_media_settings',
		'woofpages_social_media_settings_section'
	);

	add_settings_field(
		'woofpages_social_twitter',
		__( 'Twitter URL', 'wordpress' ),
		'woofpages_social_twitter_render',
		'woofpages_social_media_settings',
		'woofpages_social_media_settings_section'
	);

	add_settings_field(
		'woofpages_social_youtube',
		__( 'Youtube URL', 'wordpress' ),
		'woofpages_social_youtube_render',
		'woofpages_social_media_settings',
		'woofpages_social_media_settings_section'
	);

	add_settings_field(
		'woofpages_social_linkedin',
		__( 'LinkedIn URL', 'wordpress' ),
		'woofpages_social_linkedin_render',
		'woofpages_social_media_settings',
		'woofpages_social_media_settings_section'
	);

	add_settings_field(
		'woofpages_social_googleplus',
		__( 'Google+ URL', 'wordpress' ),
		'woofpages_social_googleplus_render',
		'woofpages_social_media_settings',
		'woofpages_social_media_settings_section'
	);

	add_settings_field(
		'woofpages_social_pinterest',
		__( 'Pinterest URL', 'wordpress' ),
		'woofpages_social_pinterest_render',
		'woofpages_social_media_settings',
		'woofpages_social_media_settings_section'
	);
	// END social media fields
}


// START Social media rendering functions
function woofpages_social_facebook_render() {
	$options = get_option( 'woofpages_settings' );
	echo "<input type='text' name='woofpages_settings[woofpages_social_facebook]' size='50' value='" .  $options['woofpages_social_facebook'] . "'>";
}

function woofpages_social_twitter_render() {
	$options = get_option( 'woofpages_settings' );
	echo "<input type='text' name='woofpages_settings[woofpages_social_twitter]' size='50' value='" .  $options['woofpages_social_twitter'] . "'>";
}

function woofpages_social_youtube_render() {
	$options = get_option( 'woofpages_settings' );
	echo "<input type='text' name='woofpages_settings[woofpages_social_youtube]' size='50' value='" . $options['woofpages_social_youtube'] . "'>";
}

function woofpages_social_linkedin_render() {
	$options = get_option( 'woofpages_settings' );
	echo "<input type='text' name='woofpages_settings[woofpages_social_linkedin]' size='50' value='" . $options['woofpages_social_linkedin'] . "'>";
}

function woofpages_social_googleplus_render() {
	$options = get_option( 'woofpages_settings' );
	echo "<input type='text' name='woofpages_settings[woofpages_social_googleplus]' size='50' value='" . $options['woofpages_social_googleplus'] . "'>";
}

function woofpages_social_pinterest_render() {
	$options = get_option( 'woofpages_settings' );
	echo "<input type='text' name='woofpages_settings[woofpages_social_pinterest]' size='50' value='" . $options['woofpages_social_pinterest'] . "'>";
}
// END Social media rendering functions


// START Rescue location rendering functions
function woofpages_rescue_location_abbreviation_render() {
	$options = get_option( 'woofpages_settings' );
	echo "<input type='text' placeholder='ABCD' name='woofpages_settings[woofpages_rescue_location_abbreviation]' size='50' value='" . $options['woofpages_rescue_location_abbreviation'] . "'>";
}

function woofpages_rescue_location_street_address_render(){
	$options = get_option( 'woofpages_settings' );
	echo "<textarea id='rescue_street_address' placeholder='123 N. Fake Street' name='woofpages_settings[woofpages_rescue_location_street_address]' rows='5' cols='48' >" . $options['woofpages_rescue_location_street_address'] . "</textarea>";
}

function woofpages_rescue_location_city_render() {
	$options = get_option( 'woofpages_settings' );
	echo "<input type='text' placeholder='Denver' name='woofpages_settings[woofpages_rescue_location_city]' size='50' value='" . $options['woofpages_rescue_location_city'] . "'>";
}

function woofpages_rescue_location_state_render() {
	$options = get_option( 'woofpages_settings' );
	echo "<input type='text' placeholder='CO' name='woofpages_settings[woofpages_rescue_location_state]' size='3' value='" . $options['woofpages_rescue_location_state'] . "'>";
}

function woofpages_rescue_location_zip_code_render() {
	$options = get_option( 'woofpages_settings' );
	echo "<input type='text' placeholder='80206' name='woofpages_settings[woofpages_rescue_location_zip_code]' size='50' value='" . $options['woofpages_rescue_location_zip_code'] . "'>";
}

function woofpages_rescue_location_primary_phone_render() {
	$options = get_option( 'woofpages_settings' );
	echo "<input type='text' placeholder='(123) 456-7890' name='woofpages_settings[woofpages_rescue_location_primary_phone]' size='50' value='" . $options['woofpages_rescue_location_primary_phone'] . "'>";
}

function woofpages_rescue_location_primary_email_render() {
	$options = get_option( 'woofpages_settings' );
	echo "<input type='email' placeholder='someone@yourdomain.com' name='woofpages_settings[woofpages_rescue_location_primary_email]' size='50' value='" . $options['woofpages_rescue_location_primary_email'] . "'>";
}
// END Rescue location rendering functions


// START Rescue customization rendering functions
function woofpages_rescue_custom_logo_render(){
	$options = get_option( 'woofpages_settings' );
	wp_enqueue_media();
	?>



	<input type="text" id="woofpages_rescue_custom_logo_text" name="woofpages_settings[woofpages_rescue_custom_logo]" size="50" value="<?php echo esc_url( $options['woofpages_rescue_custom_logo'] ); ?>" />
	<input id="woofpages_rescue_custom_logo_upload_button" type="button" class="button-primary" value="<?php _e( 'Choose or upload logo', 'wordpress' ); ?>" />

<!--	--><?php //if ( '' != $options['woofpages_rescue_custom_logo'] ): ?>
<!--		<input id="delete_logo_button" name="woofpages_settings[woofpages_delete_logo]" type="submit" class="button" value="--><?php //_e( 'Delete Logo', 'wordpress' ); ?><!--" />-->
<!--	--><?php //endif; ?>
	<span class="description"><?php _e('Upload an image or choose one from the Media Library for the logo/banner.', 'wordpress' ); ?></span>

<?php
}

function woofpages_rescue_custom_logo_preview_render(){
	$options = get_option( 'woofpages_settings' );
	?>

	<div id="woofpages_uploaded_logo_preview" style="min-height: 100px;">
		<img style="max-width: 100%;" src="<?php echo esc_url( $options['woofpages_rescue_custom_logo'] ); ?>" />
	</div>
	<?php
}
// END Rescue customization rendering functions


function woofpages_social_media_settings_section_callback() {
	echo __( 'Easily manage your rescue\'s social media links here.', 'wordpress' );
}

function woofpages_rescue_location_settings_section_callback() {
	echo __( 'Control your rescue\'s address and contact information here.', 'wordpress' );
}

function woofpages_rescue_custom_logo_settings_section_callback(){
	echo __( 'Customize your site here.', 'wordpress' );
}


function woofpages_settings_validate( $input ) {
	$default_options = woofpages_get_default_options();
	$valid_input = $default_options;

	$options = get_option('woofpages_settings');

	$submit = !empty($input['submit']);
	$reset = !empty($input['reset']);
	$delete_logo = !empty($input['woofpages_delete_logo']);

	if ( $submit ) {
		if ( $options['woofpages_rescue_custom_logo'] != $input['woofpages_rescue_custom_logo'] && $options['woofpages_rescue_custom_logo'] != '' )
			woofpages_delete_image( $options['woofpages_rescue_custom_logo'] );

		$valid_input['woofpages_rescue_custom_logo'] = $input['woofpages_rescue_custom_logo'];
	}
	elseif ( $reset ) {
		woofpages_delete_image( $options['woofpages_rescue_custom_logo'] );
		$valid_input['woofpages_rescue_custom_logo'] = $default_options['woofpages_rescue_custom_logo'];
	}
	elseif ( $delete_logo ) {
		woofpages_delete_image( $options['woofpages_rescue_custom_logo'] );
		$valid_input['woofpages_rescue_custom_logo'] = '';
	}

	return $valid_input;
}


function woofpages_options_page() { ?>
	<!-- Create a header in the default WordPress 'wrap' container -->
	<div class="wrap">

		<!-- Add the icon to the page -->
		<h2>Woofpages Settings Panel</h2>

		<!-- Make a call to the WordPress function for rendering errors when settings are saved. -->
		<?php settings_errors(); ?>

		<!-- Create the form that will be used to render our options -->
		<form method="post" action="options.php">
			<?php settings_fields( 'woofpages_rescue_custom_logo_settings' ); ?>
			<?php do_settings_sections( 'woofpages_rescue_custom_logo_settings' ); ?>

			<hr />

			<?php settings_fields( 'woofpages_rescue_location_settings' ); ?>
			<?php do_settings_sections( 'woofpages_rescue_location_settings' ); ?>

			<hr />

			<?php settings_fields( 'woofpages_social_media_settings' ); ?>
			<?php do_settings_sections( 'woofpages_social_media_settings' ); ?>

			<hr />

			<input name="woofpages_settings[submit]" id="woofpages_submit_options_form" type="submit" class="button-primary" value="<?php esc_attr_e('Save Settings', 'wordpress'); ?>" />
<!--			<input name="woofpages_settings[reset]" type="submit" class="button-secondary" value="--><?php //esc_attr_e('Reset Defaults', 'wordpress'); ?><!--" />-->
		</form>

	</div><!-- /.wrap -->

	<?php
}