<?php

/**
 * Asset management for CRED.
 *
 * All script or style handles should be defined here as constants.
 *
 * All CRED assets should be placed in public/ subdirectories.
 *
 * Note: Not extending the Toolset_Assets_Manager deliberately because it has specific function related to Toolset
 * Common and does some things globally. We may use it here in the future, though.
 *
 * @since 1.9
 */
class CRED_Asset_Manager {

	// Script handles
	const CREDFILE = 'wpt-field-credfile';
	const AJAX_FILE_UPLOADER = 'my_ajax_file_uploader';

	static $instance;

	public static function get_instance() {
		 if( null == self::$instance ) {
			 self::$instance = new self();
		 }
		 return self::$instance;
	}


	private function __construct() {
		$this->register_cred_scripts();
		$this->register_cred_style();
	}

	private function __clone() { }


	/**
	 * Get full asset URL.
	 *
	 * @param string $relative_path Path relative to the asset directory without the initial slash.
	 * @return string Full URL
	 * @since 1.9
	 */
	private function get_asset_url( $relative_path ) {
		return sprintf( '%s/public/%s', untrailingslashit( CRED_ABSURL ), $relative_path );
	}

	/**
	 * Get full admin assets URL.
	 *
	 * @param string $relative_path Path relative to the asset directory without the initial slash.
	 * @return string Full URL
	 * @since 1.9
	 */
	private function get_admin_assets_url( $relative_path ) {
		return sprintf( '%s/library/toolset/cred/embedded/assets/%s', untrailingslashit( CRED_ABSURL ), $relative_path );
	}

	/**
	 * Enqueue all assets needed for the frontend file upload field.
	 *
	 * @param $is_progress_bar_disabled
	 * @since 1.9
	 */
	public function enqueue_file_upload_assets( $is_progress_bar_disabled ) {

		wp_register_script( self::CREDFILE, $this->get_asset_url( 'js/credfile.js' ), array( 'wptoolset-forms' ), WPTOOLSET_FORMS_VERSION, true );

		wp_enqueue_script( self::CREDFILE );;

		if ( $is_progress_bar_disabled ) {
			// Nothing else is needed
			return;
		}

		$base_url = $this->get_asset_url( 'js/jquery_upload' );

		wp_enqueue_style( 'progress_bar-style', "$base_url/progress_bar.css" );

		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-widget' );
		wp_enqueue_script( 'jquery-ui-progressbar' );

		wp_enqueue_script( 'load-image-all-script', "$base_url/load-image.all.min.js", array( 'jquery' ), '', true );
		wp_enqueue_script( 'jquery-iframe-transport-script', "$base_url/jquery.iframe-transport.js", array( 'jquery' ), '', true );
		wp_enqueue_script( 'jquery-fileupload-script', "$base_url/jquery.fileupload.js", array( 'jquery' ), '', true );
		wp_enqueue_script( 'jquery-fileupload-process-script', "$base_url/jquery.fileupload-process.js", array( 'jquery' ), '', true );
		wp_enqueue_script( 'jquery-fileupload-image-script', "$base_url/jquery.fileupload-image.js", array( 'jquery' ), '', true );
		wp_enqueue_script( 'jquery-fileupload-audio-script', "$base_url/jquery.fileupload-audio.js", array( 'jquery' ), '', true );
		wp_enqueue_script( 'jquery-fileupload-video-script', "$base_url/jquery.fileupload-video.js", array( 'jquery' ), '', true );
		wp_enqueue_script( 'jquery-fileupload-validate-script', "$base_url/jquery.fileupload-validate.js", array( 'jquery' ), '', true );
		wp_enqueue_script( 'jquery-fileupload-ui-script', "$base_url/jquery.fileupload-ui.js", array( 'jquery' ), '', true );
		wp_enqueue_script( 'jquery-fileupload-jquery-ui-script', "$base_url/jquery.fileupload-jquery-ui.js", array( 'jquery' ), '', true );
		wp_enqueue_script( self::AJAX_FILE_UPLOADER, "$base_url/file_upload.js", array( 'jquery' ) );

	}

	/**
	 * Adds all assets needed for the frontend form display/submit to the scripts queue
	 *
	 * @since 1.9
	 */
	public function _enqueue_frontend_assets(){
		//Enqueue front-end script
		wp_enqueue_script('cred-frontend-js', $this->get_asset_url( 'js/frontend.js' ), array('jquery', 'knockout'), CRED_FE_VERSION, true);
		wp_localize_script('cred-frontend-js', 'cred_frontend_settings', array(
			'ajaxurl' => admin_url('admin-ajax.php'),
		));
	}

	/**
	 * Adds all assets needed for the CRED button to work to the scripts queue.
	 *
	 * @since 1.9
	 */
	public function _enqueue_cred_button_assets(){
		wp_enqueue_script('cred_settings');
		wp_enqueue_script('cred_cred_post_dev');
		wp_enqueue_style('cred_cred_style_dev');
	}

	/**
	 * Hooks the CRED button assets queueing to correct action.
	 *
	 * @since 1.9
	 */
	public function enqueue_cred_button_assets(){
		add_action('admin_enqueue_scripts', array(&$this, '_enqueue_cred_button_assets'), 1);
	}

	/**
	 * Hooks the frontend assets queueing to correct action.
	 *
	 * @since 1.9
	 */
	public function enqueue_frontend_assets(){
		add_action('wp_enqueue_scripts', array(&$this, '_enqueue_frontend_assets'));
	}

	/**
	 * Registers all CRED scripts
	 *
	 * @since 1.9
	 */
	public function register_cred_scripts(){
		wp_register_script('cred_console_polyfill', $this->get_admin_assets_url('common/js/console_polyfill.js'));
		wp_register_script('cred_template_script', $this->get_admin_assets_url('common/js/gui.js'), array('jquery','jquery-ui-sortable','jquery-ui-dialog','wp-pointer'));
		wp_register_script('cred_extra', $this->get_admin_assets_url('common/js/extra.js'), array('jquery','jquery-effects-scale','toolset-event-manager'));
		wp_register_script('cred_utils', $this->get_admin_assets_url('common/js/utils.js'), array('jquery','cred_extra'));
		wp_register_script('cred_gui', $this->get_admin_assets_url('common/js/gui.js'), array('jquery','jquery-ui-dialog','wp-pointer'));
		wp_register_script('cred_mvc', $this->get_admin_assets_url('common/js/mvc.js'), array('jquery'));
		wp_register_script('cred_codemirror_shortcodes', $this->get_admin_assets_url('third-party/codemirror_shortcodes.js'), array('jquery','toolset-codemirror-script'));
		wp_register_script('cred_cred_dev', $this->get_admin_assets_url('js/cred.js'), array('jquery','underscore','cred_console_polyfill','toolset-meta-html-codemirror-xml-script','toolset-codemirror-script','toolset-meta-html-codemirror-css-script','cred_codemirror_shortcodes','cred_extra','cred_utils','cred_gui','cred_mvc','jquery-ui-sortable','toolset-utils'));
		wp_register_script('cred_cred_nocodemirror_dev', $this->get_admin_assets_url('js/cred.js'), array('jquery','underscore','cred_console_polyfill','cred_extra','cred_utils','cred_gui','cred_mvc','jquery-ui-sortable','toolset-utils'));
		wp_register_script('cred_cred_post_dev', $this->get_admin_assets_url('js/post.js'), array('jquery','cred_console_polyfill','cred_extra','cred_utils','cred_gui','toolset-event-manager','cred_cred_dev','cred_settings'));
		wp_register_script('cred_cred_nocodemirror', $this->get_admin_assets_url('js/cred.js'), array('jquery','underscore','jquery-ui-dialog','wp-pointer','jquery-effects-scale','cred_extra','cred_utils','cred_gui','cred_mvc','jquery-ui-sortable','toolset-utils'));
		wp_register_script('cred_wizard_dev', $this->get_admin_assets_url('js/wizard.js'), array('cred_cred_dev'));
		wp_register_script('cred_settings', $this->get_admin_assets_url('js/settings.js'), array('jquery','underscore','jquery-ui-dialog','jquery-ui-tabs','toolset-settings'));

		wp_localize_script('cred_settings', 'cred_settings', array(
			'_current_page' => CRED_Helper::getCurrentPostType(),
			'_cred_wpnonce' => wp_create_nonce('_cred_wpnonce'),
			'autogenerate_username_scaffold' => isset(CRED_Helper::$current_form_fields) ? CRED_Helper::$current_form_fields['form_settings']->form['autogenerate_username_scaffold'] : 0,
			'autogenerate_nickname_scaffold' => isset(CRED_Helper::$current_form_fields) ? CRED_Helper::$current_form_fields['form_settings']->form['autogenerate_nickname_scaffold'] : 0,
			'autogenerate_password_scaffold' => isset(CRED_Helper::$current_form_fields) ? CRED_Helper::$current_form_fields['form_settings']->form['autogenerate_password_scaffold'] : 0,
			// settings
			'assets' => CRED_ASSETS_URL,
			'ajaxurl' => admin_url('admin-ajax.php'),
			'editurl' => admin_url('post.php'),
			'form_controller_url' => '/Forms/updateFormField',
			'wizard_url' => '/Settings/disableWizard',
			'homeurl' => home_url('/'),
			'settingsurl' => CRED_CRED::$settingsPage,
			// help
			'help' => CRED_CRED::$help,
			'locale' => array(
				'OK' => __('OK', 'wp-cred'),
				'Yes' => __('Yes', 'wp-cred'),
				'No' => __('No', 'wp-cred'),
				'syntax_button_title' => __('Syntax', 'wp-cred'),
				'text_button_title' => __('Text'/* , 'wp-cred' */),
				'title_explain_text' => __('Set the title for this new form.', 'wp-cred'),
				'content_explain_text' => __('Build the form using HTML and CRED shortcodes. Click on the <strong>Auto-Generate Form</strong> button to create the form with default fields. Use the <strong>Add User/Post Fields</strong> button to add fields that belong to this post type, or <strong>Add Generic Fields</strong> to add any other inputs.', 'wp-cred'),
				'next_text' => __('Next', 'wp-cred'),
				'prev_text' => __('Previous', 'wp-cred'),
				'finish_text' => __('Finish', 'wp-cred'),
				'quit_wizard_text' => __('Exit Wizard Mode', 'wp-cred'),
				'quit_wizard_confirm_text' => sprintf(__('Do you want to disable the Wizard for this form only, or disable the Wizard for all future forms as well? <br /><br /><span style="font-style:italic">(You can re-enable the Wizard at the %s Settings Page if you change your mind)</span>', 'wp-cred'), CRED_NAME),
				'quit_wizard_all_forms' => __('All forms', 'wp-cred'),
				'quit_wizard_this_form' => __('This form', 'wp-cred'),
				'cancel_text' => __('Cancel', 'wp-cred'),
				'form_type_missing' => __('You must select the Form Type for the form', 'wp-cred'),
				'post_type_missing' => __('You must select a Post Type for the form', 'wp-cred'),
				'post_status_missing' => __('You must select a Post Status for the form', 'wp-cred'),
				'post_action_missing' => __('You must select a Form Action for the form', 'wp-cred'),
				'ok_text' => __('OK', 'wp-cred'),
				'step_1_title' => __('Title', 'wp-cred'),
				'step_2_title' => __('Settings', 'wp-cred'),
				'step_3_title' => __('Post Type', 'wp-cred'),
				'step_4_title' => __('Build Form', 'wp-cred'),
				'step_5_title' => __('E-mail Notifications', 'wp-cred'),
				'submit_but' => __('Update', 'wp-cred'),
				'form_content' => __('Form Content', 'wp-cred'),
				'form_fields' => __('Form Fields', 'wp-cred'),
				'post_fields' => __('Standard Post Fields', 'wp-cred'),
				//Added
				'user_fields' => __('Standard User Fields', 'wp-cred'),
				'custom_fields' => __('Custom Fields', 'wp-cred'),
				'taxonomy_fields' => __('Taxonomies', 'wp-cred'),
				'parent_fields' => __('Parents', 'wp-cred'),
				'extra_fields' => __('Extra Fields', 'wp-cred'),
				'form_types_not_set' => __('Form Type or Post Type is not set!'),
				'set_form_title' => __('Please set the form Title', 'wp-cred'),
				'create_new_content_form' => __('(Create a new-post form first)', 'wp-cred'),
				'create_edit_content_form' => __('(Create an edit-post form first)', 'wp-cred'),
				'create_new_content_user_form' => __('(Create a new-user form first)', 'wp-cred'),
				'create_edit_content_user_form' => __('(Create an edit-user form first)', 'wp-cred'),
				'show_advanced_options' => __('Show advanced options', 'wp-cred'),
				'hide_advanced_options' => __('Hide advanced options', 'wp-cred'),
				'select_form' => __('Please select a form first', 'wp-cred'),
				'select_post' => __('Please select a post first', 'wp-cred'),
				'insert_post_id' => __('Please insert a valid post ID', 'wp-cred'),
				'insert_shortcode' => __('Click to insert the specified shortcode', 'wp-cred'),
				'select_shortcode' => __('Please select a shortcode first', 'wp-cred'),
				'post_types_dont_match' => __('This post type is incompatible with the selected form', 'wp-cred'),
				'post_status_must_be_public' => __('In order to display the post, post status must be set to Publish', 'wp-cred'),
				'refresh_done' => __('Refresh Complete', 'wp-cred'),
				'enable_popup_for_preview' => __('You have to enable popup windows in order for Preview to work!', 'wp-cred'),
				'show_syntax_highlight' => __('Enable Syntax Highlight', 'wp-cred'),
				'hide_syntax_highlight' => __('Revert to default editor', 'wp-cred'),
				'syntax_highlight_on' => __('Syntax Highlight On', 'wp-cred'),
				'syntax_highlight_off' => __('Syntax Highlight Off', 'wp-cred'),
				'invalid_title' => __('Title should contain only letters, numbers and underscores/dashes', 'wp-cred'),
				'invalid_notification_sender_email' => __('Notifications sender E-mail must be a valid E-mail address', 'wp-cred'),
				'form_user_not_set' => __('Form User Fields not set!'),
				'invalid_user_role' => __('First, please select the role for the user that this form will create.', 'wp-cred'),
				'invalid_form_type' => __('Form Type option cannot be empty.', 'wp-cred'),
				'logged_in_user_shortcodes_warning' => __('Both `User Login Name` and `User Display Name` codes, work only on notifications triggered by a form submission.', 'wp-cred')
			)
		));
	}

	/**
	 * Registers all CRED styles
	 *
	 * @since 1.9
	 */
	public function register_cred_style(){
		wp_register_style('cred_template_style', $this->get_admin_assets_url('css/gfields.css'), array('wp-admin', 'colors-fresh', 'toolset-font-awesome', 'cred_cred_style_nocodemirror_dev'));
		wp_register_style('toolset-font-awesome', $this->get_admin_assets_url('common/css/font-awesome.min.css'));
		wp_register_style('cred_cred_style_dev', $this->get_admin_assets_url('css/cred.css'), array('toolset-font-awesome', 'toolset-meta-html-codemirror-css-hint-css', 'toolset-meta-html-codemirror-css', 'wp-jquery-ui-dialog', 'wp-pointer'));
		wp_register_style('cred_cred_style_nocodemirror_dev', $this->get_admin_assets_url('css/cred.css'), array('toolset-font-awesome', 'wp-jquery-ui-dialog', 'wp-pointer'));

	}

}