<?php

class CRED_Form_Rendering {

    public static $current_postid;
    public $controls;
    public $is_submit_success = false;
    public $attributes;
    public $language;
    public $method;
    public $actionUri;
    public $preview;
    public $form_properties;
    public $extra_parameters = array();
    public $top_messages = array();
    public $field_messages = array();
    public $preview_messages = array();
    public $form_id;
    public $html_form_id;
    public $_post_id;
    public $_formHelper;
    public $_translate_field_factory;
    public $_formData;
    public $_shortcodeParser;
    public $_form_content;
    public $_js;
    public $_content;
    public $isForm = false;
    public $isUploadForm = false;

    /**
     * @deprecated
     * @var type array()
     */
    public $form_errors = array();

    /**
     * @deprecated
     * @var type array()
     */
    public $form_messages = array();
    private $_request;
    public $_validation_errors = array();

    public function __construct($form_id, $html_form_id, $form_type, $current_postid, $actionUri, $preview = false) {
        $this->form_id = $form_id;
        $this->html_form_id = $html_form_id;
        $this->form_type = $form_type;
        self::$current_postid = $current_postid;
        $this->actionUri = $actionUri;
        $this->preview = $preview;
        $this->method = CRED_StaticClass::METHOD;

        $_files = array();

        $req = $_REQUEST;
        //Fixed https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/191483153/comments#297748160
        $req = stripslashes_deep($req);
        //##########################################################################################################

        $this->_request = $req;
        //$this->setControls();

        return $this->getForm();
    }

    public function setFormHelper($formHelper) {
        $this->_formHelper = $formHelper;
        $this->_translate_field_factory = new CRED_Translate_Field_Factory($formHelper->_formBuilder, $formHelper);
    }

    public function setLanguage($lang) {
        $this->language = $lang;
    }

    public function cred_filepath_class($type, $class) {
        return CRED_FIELDS_ABSPATH . "class.{$type}.php";
    }

    function cred_fields_include_files($value, $type) {
        //require_once CRED_ABSPATH . "/library/toolset/cred/embedded/fields/class.{$type}.php";
        require_once "field/class.{$type}.php";
    }

    /**
     * @deprecated function since 1.2.6
     * @param type $params
     */
    function set_extra_parameters($params) {
        $this->extra_parameters = array_merge($this->extra_parameters, $params);
    }

    /**
     * add a field content to a form
     * @param type $type
     * @param type $name
     * @param type $value
     * @param type $attributes
     * @param type $field
     * @return type
     */
    function add($type, $name, $value, $attributes, $field = null) {
        $computed_values = array(
            'type' => $type,
            'name' => $name,
            'value' => $value,
            'attributes' => $attributes,
            'field' => $field
        );
        $type = apply_filters('cred_filter_field_type_before_add_to_form', $type, $computed_values);
        $name = apply_filters('cred_filter_field_name_before_add_to_form', $name, $computed_values);
        $value = apply_filters('cred_filter_field_value_before_add_to_form', $value, $computed_values);
        $attributes = apply_filters('cred_filter_field_attributes_before_add_to_form', $attributes, $computed_values);
        $field = apply_filters('cred_filter_field_before_add_to_form', $field, $computed_values);

        $title = isset($field) ? $field['name'] : $name;
        $title = isset($field['label']) ? $field['label'] : $title;

        //Check the case when generic field checkbox does not have label property at all
        if ($type == 'checkbox' && !isset($field['plugin_type'])) {
            if (!isset($field['label']))
                $title = "";
        }

        $f = array();
        $f['type'] = $type;
        $f['name'] = $name;
        if (isset($field['cred_custom'])) {
            $f['cred_custom'] = true;
        }
        $f['title'] = $title;
        $f['value'] = $value;
        $f['attr'] = $attributes;
        $f['data'] = is_array($field) && array_key_exists('data', $field) ? @$field['data'] : array();

        if (isset($field['plugin_type'])) {
            $f['plugin_type'] = $field['plugin_type'];
        }

        $this->form_properties['fields'][] = $f;
        return $f;
    }

    /**
     * add virtual information about a field
     * @param type $type
     * @param type $name
     * @param type $value
     * @param type $attributes
     * @param type $field
     * @return type
     */
    function noadd($type, $name, $value, $attributes, $field = null) {
        $computed_values = array(
            'type' => $type,
            'name' => $name,
            'value' => $value,
            'attributes' => $attributes,
            'field' => $field
        );
        $type = apply_filters('cred_filter_field_type_before_noadd_to_form', $type, $computed_values);
        $name = apply_filters('cred_filter_field_name_before_noadd_to_form', $name, $computed_values);
        $value = apply_filters('cred_filter_field_value_before_noadd_to_form', $value, $computed_values);
        $attributes = apply_filters('cred_filter_field_attributes_before_noadd_to_form', $attributes, $computed_values);
        $field = apply_filters('cred_filter_field_before_noadd_to_form', $field, $computed_values);

        $title = isset($field) ? $field['name'] : $name;

        $f = array();
        $f['type'] = $type;
        $f['name'] = $name;
        $f['title'] = $title;
        $f['value'] = $value;
        $f['attr'] = $attributes;
        $f['data'] = is_array($field) && array_key_exists('data', $field) ? @$field['data'] : array();

        if (isset($field['plugin_type'])) {
            $f['plugin_type'] = $field['plugin_type'];
        }
        return $f;
    }

    /**
     * cred_form_shortcode callback
     * @param type $atts
     * @param type $content
     * @return type
     */
    public function cred_form_shortcode($atts, $content = '') {
        cred_log("cred_form_shortcode");

        extract(shortcode_atts(array(
            'class' => ''
                        ), $atts));

        // return a placeholder instead and store the content in _form_content var
        $this->_form_content = $content;
        $this->html_form_id = $this->form_properties['name'];
        $this->isForm = true;

        if (!empty($class)) {
            $this->_attributes['class'] = esc_attr($class);
        }

        return CRED_StaticClass::FORM_TAG . '_' . $this->form_properties['name'] . '%';
    }

    /**
     * cred_user_form_shortcode parse form shortcode [credform]
     * @param type $atts
     * @param type $content
     * @return type
     */
    public function cred_user_form_shortcode($atts, $content = '') {
        extract(shortcode_atts(array(
            'class' => ''
                        ), $atts));

        // return a placeholder instead and store the content in _form_content var
        $this->_form_content = $content;
        $this->html_form_id = $this->form_properties['name'];
        $this->isForm = true;

        if (!empty($class)) {
            $this->_attributes['class'] = esc_attr($class);
        }

        return CRED_StaticClass::FORM_TAG . '_' . $this->form_properties['name'] . '%';
    }

    /**
     * CRED-Shortcode: cred_field 
     *
     * Description: Render a form field (using fields defined in wp-types plugin and / or Taxonomies)
     * parse field shortcodes [cred_field]
     * 
     * Parameters:
     * 'field' => Field slug name
     * 'post' => [optional] Post Type where this field is defined 
     * 'value'=> [optional] Preset value (does not apply to all field types, eg taxonomies)
     * 'taxonomy'=> [optional] Used by taxonomy auxilliary fields (eg. "show_popular") to signify to which taxonomy this field belongs
     * 'type'=> [optional] Used by taxonomy auxilliary fields (like show_popular) to signify which type of functionality it provides (eg. "show_popular")
     * 'display'=> [optional] Used by fields for Hierarchical Taxonomies (like Categories) to signify the mode of display (ie. "select" or "checkbox")
     * 'single_select'=> [optional] Used by fields for Hierarchical Taxonomies (like Categories) to signify that select field does not support multi-select mode
     * 'max_width'=>[optional] Max Width for image fields
     * 'max_height'=>[optional] Max Height for image fields
     * 'max_results'=>[optional] Max results in parent select field
     * 'order'=>[optional] Order for parent select field (title or date)
     * 'ordering'=>[optional] Ordering for parent select field (asc, desc)
     * 'required'=>[optional] Whether parent field is required, default 'false'
     * 'no_parent_text'=>[optional] Text for no parent selection in parent field
     * 'select_text'=>[optional] Text for required parent selection
     * 'validate_text'=>[optional] Text for error message when parebt not selected
     * 'placeholder'=>[optional] Text to be used as placeholder (HTML5) for text fields, default none
     * 'readonly'=>[optional] Whether this field is readonly (cannot be edited, applies to text fields), default 'false'
     * 'urlparam'=> [optional] URL parameter to be used to give value to the field
     *
     * Example usage:
     *
     *  Render the wp-types field "Mobile" defined for post type Agent
     * [cred_field field="mobile" post="agent" value="555-1234"]
     *
     * Link:
     *
     *
     * Note:
     *  'value'> translated automatically if WPML translation exists
     *  'taxonomy'> used with "type" option
     *  'type'> used with "taxonomy" option
     *
     * */
    public function cred_field_shortcodes($atts) {
        return CRED_Field_Factory::create_field($atts, $this, $this->_formHelper, $this->_formData, $this->_translate_field_factory);
    }

    /**
     * CRED-Shortcode: cred_show_group
     *
     * Description: Show/Hide a group of fields based on conditional logic and values of form fields
     *
     * Parameters:
     * 'if' => Conditional Expression
     * 'mode' => Effect for show/hide group, values are: "fade-slide", "fade", "slide", "none"
     *  
     *   
     * Example usage:
     * 
     *    [cred_show_group if="$(date) gt TODAY()" mode="fade-slide"]
     *       //rest of content to be hidden or shown
     *      // inside the shortcode body..
     *    [/cred_show_group]
     *
     * Link:
     *
     *
     * Note:
     *
     *
     * */
    // parse conditional shortcodes (nested allowed) [cred_show_group]
    public function cred_conditional_shortcodes($atts, $content = '') {
        static $condition_id = 0;

        shortcode_atts(array(
            'if' => '',
            'mode' => 'fade-slide'
                ), $atts); //);

        if (empty($atts['if']) || !isset($content) || empty($content))
            return ''; // ignore

        if (defined('WPTOOLSET_FORMS_VERSION')) {
            $form = &$this->_formData;
            $shortcodeParser = $this->_shortcodeParser;
            ++$condition_id;

            WPToolset_Types::$is_user_meta = $form->getForm()->post_type == CRED_USER_FORMS_CUSTOM_POST_NAME;
            $conditional = self::filterConditional($atts['if'], $this->_post_id);
            $id = $form->getForm()->ID . '_condition_' . $condition_id;
            $config = array('id' => $id, 'conditional' => $conditional);
            $passed = wptoolset_form_conditional_check($config);
            wptoolset_form_add_conditional($this->html_form_id, $config);

            $style = ($passed) ? "" : " style='display:none;'";
            $effect = '';
            if (isset($atts['mode'])) {
                $effect = " data-effectmode='" . esc_attr($atts['mode']) . "'";
            }

            $html = "<div class='cred-group {$id}'{$style}{$effect}>";
            $html .= do_shortcode($content);
            $html .= "</div>";
            return $html;
        }

        return '';
    }

    /**
     * CRED-Shortcode: cred_generic_field
     *
     * Description: Render a form generic field (general fields not associated with types plugin)
     *
     * Parameters:
     * 'field' => Field name (name like used in html forms)
     * 'type' => Type of input field (eg checkbox, email, select, radio, checkboxes, date, file, image etc..)
     * 'class'=> [optional] Css class to apply to the element
     * 'urlparam'=> [optional] URL parameter to be used to give value to the field
     * 'placeholder'=>[optional] Text to be used as placeholder (HTML5) for text fields, default none
     *  
     *  Inside shortcode body the necessary options and default values are defined as JSON string (autogenerated by GUI)
     *   
     * Example usage:
     * 
     *    [cred_generic_field field="gmail" type="email" class=""]
     *    {
     *    "required":0,
     *    "validate_format":0,
     *    "default":""
     *    }
     *    [/cred_generic_field]
     *
     * Link:
     *
     *
     * Note:
     *
     *
     * */
    // parse generic input field shortcodes [cred_generic_field]
    public function cred_generic_field_shortcodes($atts, $content = '') {           
        return CRED_Field_Factory::create_generic_field($atts, $content, $this, $this->_formHelper, $this->_formData, $this->_translate_field_factory);
    }     
    //########### CALLBACKS

    /**
     * function used to set controls in order to do not lost filled field values after a failed form submition 
     */
    public function setControls() {
        $this->controls = array();
        $pattern = get_shortcode_regex();
        foreach ($this->_request as $key => $value) {
            $value = $this->clearControl($value, $pattern);
            $this->controls[$key] = $value;
        }
        //No need anymore
        unset($this->_request);
    }

    /**
     * clearControl
     * @param type $value
     * @param type $pattern
     * @return type
     */
    private function clearControl($value, $pattern) {
        if (is_array($value)) {
            foreach ($value as & $value_entry) {
                $value_entry = $this->clearControl($value_entry, $pattern);
            }
        } else if (is_string($value)) {
            preg_match_all('/' . $pattern . '/', $value, $matches, PREG_SET_ORDER);
            if (!empty($matches)) {
                $value = strip_shortcodes($value);
            }
        }
        return $value;
    }

    /**
     * get the current form
     * @return boolean|\CredForm
     */
    public function getForm() {
        if (!function_exists('wptoolset_form_field')) {
            echo "error";
            return false;
        }

        $this->form_properties = array();

        $this->form_properties['doctype'] = 'xhtml';
        $this->form_properties['action'] = htmlspecialchars($_SERVER['REQUEST_URI']);
        //Fix for todo https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/193382255/comments#303088273
        if (preg_match("/admin-ajax.php/", $this->form_properties['action'])) {
            $this->form_properties['action'] = ( wp_get_referer() ) ? wp_get_referer() : get_home_url();
        }
        $this->form_properties['method'] = 'post';

        $this->form_properties['name'] = $this->html_form_id;
        $this->form_properties['fields'] = array();

        return $this;
    }

    /**
     * render callback
     * @param type $controls
     * @param type $objs
     * @return type
     */
    public function render_callback($controls, &$objs) {
        $shortcodeParser = $this->_shortcodeParser;
        CRED_StaticClass::$out['controls'] = $controls;
        // render shortcodes, _form_content is being continuously replaced recursively
        $this->_form_content = $shortcodeParser->do_shortcode($this->_form_content);
        return $this->_form_content;
    }

    /**
     * render function
     * @return type
     */
    public function render() {
        cred_log("render");
        cred_log("isForm: " . $this->isForm);

        $html = "";
        if ($this->isForm) {
            $this->isForm = false;
            $enctype = "";
            if ($this->isUploadForm) {
                $this->isUploadForm = false;
                $enctype = 'enctype="multipart/form-data"';
            }

            $action = str_replace(array('/', '?'), "", $this->form_properties['action']);

            $amp = '?';
            $_tt = '_tt=' . time();

            //if (!empty($_SERVER['QUERY_STRING']) &&
            //        stripos($action, $_SERVER['QUERY_STRING']) !== false)

            if (strpos($this->form_properties['action'], '?') !== false)
                $amp = '&';

            $this->_form_content = '<form ' . $enctype . ' ' .
                    ($this->form_properties['doctype'] == 'html' ? 'name="' . $this->form_properties['name'] . '" ' : '') .
                    'id="' . $this->form_properties['name'] . '" ' .
                    'class="' . ((isset($this->_attributes['class']) && !empty($this->_attributes['class'])) ? $this->_attributes['class'] : "") . '" ' .
                    'action="' . $this->form_properties['action'] . $amp . $_tt . '" ' .
                    'method="' . strtolower($this->form_properties['method']) . '">' . $this->_form_content . "</form>";
        }

        return $this->_form_content;
    }

    private function typeMessage2textMessage($txt) {
        switch ($txt) {
            case "date":
                return "cred_message_enter_valid_date";
            case "embed":
            case "url":
                return "cred_message_enter_valid_url";
            case "email":
                return "cred_message_enter_valid_email";
            case "integer":
            case "number":
                return "cred_message_enter_valid_number";
            case "captcha":
                return "cred_message_enter_valid_captcha";
            case "button":
                return "cred_message_edit_skype_button";
            case "image":
                return "cred_message_not_valid_image";
            default:
                return "cred_message_field_required";
        }
    }

    private function typeMessage2id($txt) {
        switch ($txt) {
            case "date":
                return "cred_message_enter_valid_date";
            case "embed":
            case "url":
                return "cred_message_enter_valid_url";
            case "email":
                return "cred_message_enter_valid_email";
            case "integer":
            case "number":
                return "cred_message_enter_valid_number";
            case "captcha":
                return "cred_message_enter_valid_captcha";
            case "button":
                return "cred_message_edit_skype_button";
            case "image":
                return "cred_message_not_valid_image";
            default:
                return "cred_message_field_required";
        }
    }

    //Client-side validation is not using the custom messages provided in CRED forms for CRED custom fields
    //https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/187800735/comments
    /**
     * fixCredCustomFieldMessages
     * Fix CRED controlled custom fields validation message
     * replace with cred form settings messages and localize messages
     * @param type $field
     * @return type
     */
    public function fixCredCustomFieldMessages(&$field) {
        if (!isset($field['cred_custom']) || isset($field['cred_custom']) && !$field['cred_custom'])
            return;
        $cred_messages = $this->extra_parameters->messages;
        foreach ($field['data']['validate'] as $a => &$b) {
            $idmessage = $this->typeMessage2textMessage($a);
            $b['message'] = $cred_messages[$idmessage];
            $b['message'] = cred_translate(
                    CRED_Form_Builder_Helper::MSG_PREFIX . $idmessage, $cred_messages[$idmessage], 'cred-form-' . $this->_formData->getForm()->post_title . '-' . $this->_formData->getForm()->ID
            );
        }
    }

    /**
     * This function render the single field
     * @global type $post
     * @param type $field
     * @param type $add2form_content
     * @return type
     */
    public function renderField($field, $add2form_content = false) {
        cred_log("renderField");

        global $post;

        if (defined('WPTOOLSET_FORMS_ABSPATH') &&
                function_exists('wptoolset_form_field')) {

            require_once WPTOOLSET_FORMS_ABSPATH . '/api.php';
            require_once WPTOOLSET_FORMS_ABSPATH . '/classes/class.types.php';

            add_filter("wptoolset_load_field_class_file", array($this, "cred_fields_include_files"), 10, 2);

            $form_id = $this->form_id;
            $id = $this->html_form_id;

            $field['id'] = $id;

            if ($field['type'] == 'messages') {
                $this->isForm = true;
                return;
            }

            if ($this->form_type == 'edit' && $field['name'] == 'user_login') {
                $field['attr']['readonly'] = "readonly";
                $field['attr']['style'] = "background-color:#ddd;";
                $field['attr']['onclick'] = "blur();";
            }

            if ($field['type'] == 'credfile' ||
                    $field['type'] == 'credaudio' ||
                    $field['type'] == 'credvideo' ||
                    $field['type'] == 'credimage' ||
                    $field['type'] == 'file') {
                $this->isUploadForm = true;
                //$field['type'] = 'credfile';
            }

            //#############################################################################################################################################################
            //Client-side validation is not using the custom messages provided in CRED forms for CRED custom fields            
            $this->fixCredCustomFieldMessages($field);
            //#############################################################################################################################################################

            $mytype = $this->transType($field['type']);

            $fieldConfig = new CRED_Field_Config();
            $fieldConfig->setValueAndDefaultValue($field, $_curr_value, $_default_value);

            $fieldConfig->setOptions($field['name'], $field['type'], $field['value'], $field['attr']);
            $fieldConfig->setId($this->form_properties['name'] . "_" . $field['name']);
            $fieldConfig->setName($field['name']);
            $this->cleanAttr($field['attr']);
            $fieldConfig->setAttr($field['attr']);
            $fieldConfig->setDefaultValue($_default_value);
            $fieldConfig->setValue($_curr_value);
            $fieldConfig->setDescription(!empty($field['description']) ? $field['description'] : "");
            $fieldConfig->setTitle($field['title']);
            $fieldConfig->setType($mytype);

            if (isset($field['data']) && isset($field['data']['repetitive'])) {
                $fieldConfig->setRepetitive((bool) $field['data']['repetitive']);
            }

            if (isset($field['attr']) && isset($field['attr']['type'])) {
                $fieldConfig->setDisplay($field['attr']['type']);
            }

            $forms_model = CRED_Loader::get('MODEL/Forms');
            $form_settings = $forms_model->getFormCustomField($form_id, 'form_settings');
            $fieldConfig->setForm_settings($form_settings);

            $config = $fieldConfig->createConfig();

            // Modified by Srdjan
            // Validation and conditional filtering
            if (isset($field['plugin_type']) && $field['plugin_type'] == 'types') {
                // This is not set in DB
                $field['meta_key'] = WPToolset_Types::getMetakey($field);
                $config['validation'] = WPToolset_Types::filterValidation($field);

                if ($post)
                    $config['conditional'] = WPToolset_Types::filterConditional($field, $post->ID);
            } else {
                $config['validation'] = self::filterValidation($field);
            }
            // Modified by Srdjan END
            //Common adaptation
            //TODO:adapting before
            $_values = array();
            if (isset($field['data']['repetitive']) && $field['data']['repetitive'] == 1) {
                //$_values = $field['value'];
                $_values = $_curr_value;
            } else {
                //$_values = array($field['value']);
                $_values = array($_curr_value);
            }

            // Added by Srdjan
            /*
             * Use $_validation_errors
             * set in $this::validate_form()
             */
            if (isset($this->_validation_errors['fields'][$config['id']])) {
                $config['validation_error'] = $this->_validation_errors['fields'][$config['id']];
            }

            if ($this->form_type == 'edit' && $mytype == 'checkbox')
                unset($config['default_value']);


            // Added by Srdjan END            
            $html = wptoolset_form_field($this->html_form_id, $config, $_values);
            if ($add2form_content)
                $this->_form_content.=$html;
            else
                return $html;
        }
    }

    /**
     * clean attr variable
     * @param type $attrs
     * @return type
     */
    public function cleanAttr(&$attrs) {
        if (empty($attrs))
            return;
        foreach ($attrs as $n => $v) {
            if (is_array($v))
                continue;
            $attrs[$n] = esc_attr($v);
        }
        $attrs = array_filter($attrs);
    }


	/**
	 * Filters validation.
	 *
	 * Loop over validation settings and create array of validation rules.
	 * array( $rule => array( 'args' => array, 'message' => string ), ... )
	 *
	 * @param array|string $field settings array (as stored in DB) or field ID
	 * @return array array( $rule => array( 'args' => array, 'message' => string ), ... )
	 */
	public static function filterValidation( $config ){
		/* Placeholder for field value '$value'.
		 *
		 * Used for validation settings.
		 * Field value is not processed here, instead string '$value' is used
		 * to be replaced with actual value when needed.
		 *
		 * For example:
		 * validation['rangelength'] = array(
		 *     'args' => array( '$value', 5, 12 ),
		 *     'message' => 'Value length between %s and %s required'
		 * );
		 * validation['reqiuired'] = array(
		 *     'args' => array( '$value', true ),
		 *     'message' => 'This field is required'
		 * );
		 *
		 * Types have default and custom messages defined on it's side.
		 */
		$value = '$value';
		$validation = array();
		if ( isset( $config['data']['validate'] ) ) {
			foreach ( $config['data']['validate'] as $rule => $settings ) {
				if ( $settings['active'] ) {
					$validation[$rule] = array(
						'args' => isset( $settings['args'] ) ? array_unshift( $value,
							$settings['args'] ) : array($value, true),
						'message' => $settings['message']
					);
				}
			}
		}
		return $validation;
	}

	/**
	 * Filters conditional.
	 *
	 * We'll just handle this as a custom conditional
	 *
	 * Custom conditional
	 * Main properties:
	 * [custom] - custom statement made by user, note that $xxxx should match
	 *      IDs of fields that passed this filter.
	 * [values] - same as for regular conditional
	 *
	 * [conditional] => Array(
			[custom] => ($wpcf-my-date = DATE(01,02,2014)) OR ($wpcf-my-date > DATE(07,02,2014))
			[values] => Array(
			[wpcf-my-date] => 32508691200
			)
		)
	 *
	 * @param array|string $field settings array (as stored in DB) or field ID
	 * @param int $post_id Post or user ID to fetch meta data to check against
	 * @return array
	 */
	public static function filterConditional( $if, $post_id ){
		// Types fields specific
		// @todo is this needed?
		require_once WPTOOLSET_FORMS_ABSPATH . '/classes/class.conditional.php';

		$data = WPToolset_Types::getCustomConditional($if, '', WPToolset_Types::getConditionalValues($post_id));
		return $data;
	}


    /**
     * add field content to the form
     * @param type $type
     * @param type $name
     * @param type $value
     * @param type $attributes
     * @param type $field
     * @return type
     */
    public function add2form_content($type, $name, $value, $attributes, $field = null) {
        $objField2Render = $this->add($type, $name, $value, $attributes);
        return $this->renderField($objField2Render, true);
    }

    /**
     * add js content to the form
     * @param type $js
     */
    public function addJsFormContent($js) {
        //$js = str_replace("'","\'",$js);        
        $this->_js = "<script language='javascript'>{$js}</script>";
    }

    /**
     * sanitize_me
     * @param type $s
     * @return type
     */
    function sanitize_me($s) {
        return htmlspecialchars($s, ENT_QUOTES, 'utf-8');
    }

    /**
     * translate special field types
     * @param type $type
     * @return type
     */
    private function transType($type) {
        switch ($type) {

            case 'text':
                $ret = 'textfield';
                break;

            default:
                $ret = $type;
                break;
        }

        //Forcing
        //$ret = 'textfield';
        return $ret;
    }

    /**
     * is submitted checks function
     * @return boolean
     */
    function isSubmitted() {
        cred_log("isSubmitted");
        $res = $this->isAjaxSubmitted() || $this->isFormSubmitted();
        return $res;
    }

    /**
     * isFormSubmitted
     * @return boolean
     */
    function isFormSubmitted() {
        cred_log("isFormSubmitted");
        cred_log($_POST);

        foreach ($_POST as $name => $value) {
            if (strpos($name, 'form_submit') !== false) {
                cred_log("isFormSubmitted yes");
                return true;
            }
        }
        if (empty($_POST) && isset($_GET['_tt']) && !isset($_GET['_success']) && !isset($_GET['_success_message'])) {
            // HACK in this case, we have used the form to try to upload a file with a size greater then the maximum allowed by PHP
            // The form was indeed submitted, but no data was passed and no redirection was performed
            // We return true here and handle the error in the Form_Builder::form() method
            cred_log("isFormSubmitted yes");
            return true;
        }
        cred_log("isFormSubmitted no");
        return false;
    }

    /**
     * isAjaxSubmitted
     * @return boolean
     */
    function isAjaxSubmitted() {
        cred_log("isAjaxSubmitted");
        if ((defined('DOING_AJAX') && DOING_AJAX) ||
                !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            
            $res = isset($_POST['action']) && $_POST['action'] == 'cred_ajax_form';
            cred_log($res);
            return $res;
        }
        cred_log("no");
        return false;
    }

    /**
     * set the current fields
     * @param type $fields
     */
    public function set_submitted_values($fields) {
        $this->form_properties['fields'] = $fields;
    }

    /**
     * @deprecated deprecated since version 1.2.6
     */
    function get_submitted_values() {
        return true;
    }

    /**
     * Get all POST/FILES values and set to class object variable
     * @return type
     */
    public function get_form_field_values() {
        $fields = array();

        //FIX validation for files elements
        $files = array();
        foreach ($_FILES as $name => $value) {
            $files[$name] = $value['name'];
        }
        $reqs = array_merge($_REQUEST, $files);

        foreach ($this->form_properties['fields'] as $n => $field) {
            if ($field['type'] != 'messages') {
                $value = isset($reqs[$field['name']]) ? $reqs[$field['name']] : "";

                $fields[$field['name']] = array(
                    'value' => $value,
                    'name' => $field['name'],
                    'type' => $field['type'],
                    'repetitive' => isset($field['data']['repetitive']) ? $field['data']['repetitive'] : false
                );
            }
        }
        return $fields;
    }

    /**
     * New validation using API calls from toolset-forms.
     *
     * Uses API cals
     * @uses wptoolset_form_validate_field()
     * @uses wptoolset_form_conditional_check()
     *
     * @todo Make it work with other fields (generic)
     *
     * @param type $post_id
     * @param type $values
     * @return boolean
     * @deprecated since 1.9
     */
    function validate_form($post_id, $values, $is_user_form = false) {
        $recaptcha_validator = new CRED_Validator_Recaptcha($this->_base_form);
        $result[] = $recaptcha_validator->validate();
    }

    /**
     * @deprecated deprecated since version 1.2.6
     */
    function add_repeatable($type) {
        return;
    }

    /**
     * @deprecated deprecated since version 1.2.6
     */
    function add_conditional_group($id) {
        return;
    }

    /**
     * Function that handles warning/error message to top form
     * @param type $message
     * @param type $field_slug
     */
    function add_top_message($message, $field_slug = 'generic') {
        $form_id = $this->html_form_id;
        if ($message == '') {
            return;
        }
        if (!isset($this->top_messages[$form_id]))
            $this->top_messages[$form_id] = array();
        //Fix slug with name
        $message = str_replace("post_title", "Post Name", $message);
        $message = str_replace("post_content", "Description", $message);
        $message = str_replace("user_email", "Email", $message);
        if (!empty($message) && !in_array(trim($message), $this->top_messages[$form_id])) {
            $this->top_messages[$form_id][] = $message;
        }
    }

    /**
     * Function that handles warning/error message to a field or a form
     * @param type $message
     * @param type $field_slug
     */
    function add_field_message($message, $field_slug = 'generic') {
        $form_id = $this->html_form_id;
        if ($message == '') {
            return;
        }
        if (!isset($this->field_messages[$form_id]))
            $this->field_messages[$form_id] = array();
        if (!isset($this->field_messages[$form_id][$field_slug]))
            $this->field_messages[$form_id][$field_slug] = array();
        if (!empty($message) && !in_array(trim($message), $this->field_messages[$form_id]))
            $this->field_messages[$form_id][$field_slug] = $message;
    }

    /**
     * add_success_message
     * @param type $message
     * @param type $field_slug
     * @return type
     */
    function add_success_message($message, $field_slug = 'generic') {
        $form_id = $this->html_form_id;
        if ($message == '') {
            return;
        }
        if (!isset($this->succ_messages[$form_id]))
            $this->succ_messages[$form_id] = array();
        if (!isset($this->succ_messages[$form_id][$field_slug]))
            $this->succ_messages[$form_id][$field_slug] = array();
        if (!empty($message) && !in_array(trim($message), $this->succ_messages[$form_id]))
            $this->succ_messages[$form_id][$field_slug] = $message;
    }

    /**
     * add_preview_message
     * @param type $message
     */
    function add_preview_message($message) {
        $this->preview_messages[] = $message;
    }

    /**
     * getFieldsSuccessMessages
     * @return string
     */
    function getFieldsSuccessMessages() {
        $form_id = $this->html_form_id;
        //
        $msgs = "";
        if (!isset($this->succ_messages) || (isset($this->succ_messages) && empty($this->succ_messages)))
            return $msgs;

        $field_messages = $this->succ_messages[$form_id];
        foreach ($field_messages as $id_field => $text) {
            //if ($id_field!='generic') $text = "<b>".$id_field."</b>: ".$text;
            $msgs .= "<label id=\"lbl_$id_field\" class=\"wpt-form-success\">$text</label><div style='clear:both;'></div>";
        }
        return $msgs;
    }

    /**
     * function to grep all error messages
     * @return type
     */
    function getFieldsErrorMessages() {
        $form_id = $this->html_form_id;
        //https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/195892843/comments#309778558
        //Created separated preview message
        $msgs = "";
        if (!empty($this->preview_messages)) {
            $msgs .= "<label id=\"lbl_preview\" style='background-color: #ffffe0;
                border: 1px solid #e6db55;
                display: block;
                margin: 10px 0;
                padding: 5px 10px;
                width: auto;'>" . $this->preview_messages[0] . "</label><div style='clear:both;'></div>";
        }
        if (!isset($this->field_messages) || (isset($this->field_messages) && empty($this->field_messages)))
            return $msgs;

        $field_messages = $this->field_messages[$form_id];
        foreach ($field_messages as $id_field => $text) {
            //if ($id_field!='generic') $text = "<b>".$id_field."</b>: ".$text;
            $msgs .= "<label id=\"lbl_$id_field\" class=\"wpt-form-error\">$text</label><div style='clear:both;'></div>";
        }
        return $msgs;
    }

    /**
     * Javascript functions that moves error messages close to related field
     * @return string
     */
    function getFieldsErrorMessagesJs() {
        $form_id = $this->html_form_id;
        if (!isset($this->field_messages) || (isset($this->field_messages) && empty($this->field_messages)))
            return;
        $field_messages = $this->field_messages[$form_id];
        $js = '<script language="javascript">
            jQuery(document).ready(function(){';
        foreach ($field_messages as $id_field => $text) {
            if ($id_field != 'generic') {
                //$js.='if (jQuery(\'[data-wpt-name="' . $id_field . '"]:first\').length) jQuery("#lbl_' . $id_field . '").detach().insertAfter(\'[data-wpt-name="' . $id_field . '"]:first\');';
                //$js.='jQuery(\'[data-wpt-name="' . $id_field . '"]:first\').parent().insertAfter(\'[data-wpt-name="' . $id_field . '"]:first\');';
                $js.='jQuery(\'#lbl_' . $id_field . '\').insertBefore(\'[data-wpt-name="' . $id_field . '"]:first\');';
            }
            //$js.='if (jQuery(\'[name="'.$id_field.'"]:first\').length) jQuery("#lbl_'.$id_field.'").detach().insertAfter(\'[name="'.$id_field.'"]:first\');';
            //$js.='if (jQuery(\'[name="'.$id_field.'[0]"]:first\').length) jQuery("#lbl_'.$id_field.'").detach().insertAfter(\'[name="'.$id_field.'[0]"]:first\');';            
            //$js.='if (jQuery(\'[data-wpt-name="'.$id_field.'"]:first\').length) jQuery("#lbl_'.$id_field.'").detach().insertAfter(\'[data-wpt-name="'.$id_field.'"]:first\');';
        }
        $js .= '});
            </script>';

        return $js;
    }

    /**
     * @deprecated function since CRED 1.3b3
     * @param type $error_block
     * @param type $error_message
     */
    function add_form_error($error_block, $error_message) {
        // if the error block was not yet created, create the error block
        if (!isset($this->form_errors[$error_block]))
            $this->form_errors[$error_block] = array();
        if (is_array($error_message))
            $error_message = isset($error_message[0]) ? $error_message[0] : "";
        // if the same exact message doesn't already exists
        if (!empty($error_message) && !in_array(trim($error_message), $this->form_errors[$error_block]))
            $this->form_errors[$error_block][] = trim($error_message);
    }

    /**
     * @deprecated function since CRED 1.3b3
     * @param type $msg_block
     * @param type $message
     */
    function add_form_message($msg_block, $message) {
        // if the error block was not yet created, create the error block
        if (!isset($this->form_messages[$msg_block]))
            $this->form_messages[$msg_block] = array();

        if (is_array($message))
            $message = isset($message[0]) ? $message[0] : "";

        // if the same exact message doesn't already exists
        if (!empty($message) && !in_array(trim($message), $this->form_messages[$msg_block]))
            $this->form_messages[$msg_block][] = trim($message);
    }

    /**
     * @return mixed|string|void
     * @deprecated Use Toolset_Date_Utils::get_supported_date_format() instead.
     */
    public static function getDateFormat() {
        $date_utils = Toolset_Date_Utils::get_instance();
        return $date_utils->get_supported_date_format();
    }

    /**
     * get a field information from id
     * @param type $id
     * @param type $field
     * @return string
     */
    function getFileData($id, $field) {
        $ret = array();
        $ret['value'] = $field['name'];
        $ret['file_data'] = array();
        $ret['file_data'][$id] = array();
        $ret['file_data'][$id] = $field;
        $ret['file_upload'] = "";
        return $ret;
    }

}

?>
