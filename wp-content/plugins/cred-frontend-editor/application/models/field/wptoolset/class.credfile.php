<?php


require_once WPTOOLSET_FORMS_ABSPATH . '/classes/class.textfield.php';


class WPToolset_Field_Credfile extends WPToolset_Field_Textfield {

    public $disable_progress_bar;

    public static function get_image_sizes($size = '') {

        global $_wp_additional_image_sizes;

        $sizes = array();
        $get_intermediate_image_sizes = get_intermediate_image_sizes();

        // Create the full array with sizes and crop info
        foreach ($get_intermediate_image_sizes as $_size) {

            if (in_array($_size, array('thumbnail', 'medium', 'large'))) {
                $sizes[$_size]['width'] = get_option($_size . '_size_w');
                $sizes[$_size]['height'] = get_option($_size . '_size_h');
                $sizes[$_size]['crop'] = (bool) get_option($_size . '_crop');
            } elseif (isset($_wp_additional_image_sizes[$_size])) {

                $sizes[$_size] = array(
                    'width' => $_wp_additional_image_sizes[$_size]['width'],
                    'height' => $_wp_additional_image_sizes[$_size]['height'],
                    'crop' => $_wp_additional_image_sizes[$_size]['crop']
                );
            }
        }

        // Get only 1 size if found
        if ($size) {
            if (isset($sizes[$size])) {
                return $sizes[$size];
            } else {
                return false;
            }
        }

        return $sizes;
    }


	/**
	 * Determine if the file upload progress bar should be displayed on the front-end.
	 *
	 * @return bool
	 * @since 1.9
	 */
    private function is_progress_bar_disabled() {

	    /**
	     * cred_file_upload_disable_progress_bar
	     *
	     * Allows for overriding the decision whether the file upload progress bar should be displayed
	     *
	     * @param bool $disable True to disable, false to enable.
	     * @since unknown
	     */
	    $is_disabled = (bool) apply_filters(
	    	'cred_file_upload_disable_progress_bar',
		    version_compare( CRED_FE_VERSION, '1.3.6.2', '<=' )
	    );

	    return $is_disabled;
    }


    public function init() {

	    $this->disable_progress_bar = $this->is_progress_bar_disabled();

	    $asset_manager = CRED_Asset_Manager::get_instance();
	    $asset_manager->enqueue_file_upload_assets( $this->disable_progress_bar );

	    wp_localize_script(
	    	CRED_Asset_Manager::AJAX_FILE_UPLOADER,
		    'settings',
		    array(
			    'media_settings' => self::get_image_sizes( 'thumbnail' ),
			    'ajaxurl' => sprintf( '%s/application/submit.php', untrailingslashit( CRED_ABSURL ) ),
			    'delete_confirm_text' => __( 'Are you sure to delete this file ?', 'wpv-views' ),
			    'delete_alert_text' => __( 'Generic Error in deleting file', 'wpv-views' ),
			    'delete_text' => __( 'delete', 'wpv-views' ),
			    'too_big_file_alert_text' => __( 'File is too big', 'wpv-views' ),
			    'nonce' => wp_create_nonce( 'ajax_nonce' )
		    )
	    );
    }

    public static function registerScripts() {
        
    }

    public static function registerStyles() {
        
    }

    public function enqueueScripts() {
        
    }

    public function enqueueStyles() {
        
    }

    public function metaform() {
        $value = $this->getValue();
        $name = $this->getName();
        if (isset($this->_data['title'])) {
            $title = $this->_data['title'];
        } else {
            $title = $name;
        }

        $id = $this->_data['id']; //str_replace(array("[", "]"), "", $name);
        $preview_span_input_showhide = '';
        $button_extra_classnames = '';

        $has_image = false;
        $is_empty = false;

        if (empty($value)) {
            $value = ''; // NOTE we need to set it to an empty string because sometimes it is NULL on repeating fields
            $is_empty = true;
            $preview_span_input_showhide = ' style="display:none"';
        }

        if (!$is_empty) {
            $pathinfo = pathinfo($value);
            // TODO we should check against the allowed mime types, not file extensions
            if (($this->_data['type'] == 'credimage' || $this->_data['type'] == 'credfile') &&
                    isset($pathinfo['extension']) && in_array(strtolower($pathinfo['extension']), array('png', 'gif', 'jpg', 'jpeg', 'bmp', 'tif'))) {
                $has_image = true;
            }
        }

        if (array_key_exists('use_bootstrap', $this->_data) && $this->_data['use_bootstrap']) {
            $button_extra_classnames = ' btn btn-default btn-sm';
        }

        $preview_file = ''; //WPTOOLSET_FORMS_RELPATH . '/images/icon-attachment32.png';
        $attr_hidden = array(
            'id' => $id . "_hidden",
            'class' => 'js-wpv-credfile-hidden',
            'data-wpt-type' => 'file'
        );
        $attr_file = array(
            'id' => $id . "_file",
            'class' => 'js-wpt-credfile-upload-file wpt-credfile-upload-file',
            'alt' => $value,
            'res' => $value
        );

        if (!$is_empty) {
            $preview_file = $value;
//                $attr_file['disabled'] = 'disabled';
            $attr_file['style'] = 'display:none';
        } else {
            $attr_hidden['disabled'] = 'disabled';
        }

        $form = array();

        $form[] = array(
            '#type' => 'markup',
            '#markup' => '<input type="button" style="display:none" data-action="undo" class="js-wpt-credfile-undo wpt-credfile-undo' . $button_extra_classnames . '" value="' . esc_attr(__('Restore original', 'wpv-views')) . '" />',
        );

        //Attachment id for _featured_image if exists
        //if it does not exists file_upload.js will handle it after file is uploaded
        if ($name == '_featured_image') {
            global $post;
            $post_id = $post->ID;
            $post_thumbnail_id = get_post_thumbnail_id($post_id);
            if (!empty($post_thumbnail_id))
                $form[] = array(
                    '#type' => 'markup',
                    '#markup' => "<input id='attachid_" . $id . "' name='attachid_" . $name . "' type='hidden' value='" . $post_thumbnail_id . "'>"
                );
        }

        $form[] = array(
            '#type' => 'hidden',
            '#name' => $name,
            '#value' => $value,
            '#attributes' => $attr_hidden,
        );
        $form[] = array(
            '#type' => 'file',
            '#name' => $name,
            '#value' => $value,
            '#title' => $title,
            '#before' => '',
            '#after' => '',
            '#attributes' => $attr_file,
            '#validate' => $this->getValidationData(),
            '#repetitive' => $this->isRepetitive(),
        );

        if (!$this->disable_progress_bar) {
            //Progress Bar
            $form[] = array(
                '#type' => 'markup',
                '#markup' => '<div id="progress_' . $id . '" class="meter" style="display:none;"><span class = "progress-bar" style="width:0;"></span></div>',
            );
        }

        $delete_butt = '<input type="button" data-action="delete" class="js-wpt-credfile-delete wpt-credfile-delete' . $button_extra_classnames . '" value="' . __('delete', 'wpv-views') . '" style="width:100%;margin-top:2px;margin-bottom:2px;" />';
        if ($has_image) {
            //$delete_butt = "<input id='butt_{$id}' style='width:100%;margin-top:2px;margin-bottom:2px;' type='button' value='" . __('delete', 'wpv-views') . "' rel='{$preview_file}' class='delete_ajax_file'>";

            $form[] = array(
                '#type' => 'markup',
                '#markup' => '<span class="js-wpt-credfile-preview wpt-credfile-preview" ' . $preview_span_input_showhide . '><img id="' . $id . '_image" src="' . $preview_file . '" title="' . $preview_file . '" alt="' . $preview_file . '" class="js-wpt-credfile-preview-item wpt-credfile-preview-item" style="max-width:150px"/>' . $delete_butt . '</span>',
            );
        } else {

            //if ( !$is_empty )
            $form[] = array(
                '#type' => 'markup',
                '#markup' => '<span class="js-wpt-credfile-preview wpt-credfile-preview" ' . $preview_span_input_showhide . '>' . $preview_file . $delete_butt . '</span>',
            );
        }
        return $form;
    }

}
