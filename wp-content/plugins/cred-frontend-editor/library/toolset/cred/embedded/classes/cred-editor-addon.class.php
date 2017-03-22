<?php
if ( defined("TOOLSET_COMMON_PATH") && file_exists( TOOLSET_COMMON_PATH . '/visual-editor/editor-addon-generic.class.php') && !class_exists( 'CRED_Editor_addon', false )  ) {
    require_once( TOOLSET_COMMON_PATH . '/visual-editor/editor-addon-generic.class.php' );

	class CRED_Editor_addon extends Editor_addon_generic{
		public function __construct( $name, $button_text, $plugin_js_url, $media_button_image = '', $print_button = true, $icon_class = '' ) {
			parent::__construct( $name, $button_text, $plugin_js_url, $media_button_image, $print_button, $icon_class );

			//enqueue button assets
			$asset_manager = CRED_Asset_Manager::get_instance();
			$asset_manager->enqueue_cred_button_assets();
		}

		public function add_form_button( $context, $text_area = '', $standard_v = true, $add_views = false, $codemirror_button = false ) {
            global $wp_version;

            $cred_button = apply_filters('toolset_cred_button_before_print', CRED_CRED::addCREDButton($context, "#" . $context), $context);

            if(!$cred_button){
            	return '';
            }

	        if ( version_compare( $wp_version, '3.1.4', '>' ) ) {
	            echo $cred_button;
	        } else {
	            return $cred_button;
	        }
		}

	}
}
?>