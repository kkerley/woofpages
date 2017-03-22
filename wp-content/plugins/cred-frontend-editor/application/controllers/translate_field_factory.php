<?php

/**
 * Class that translates field during cred shortcodes elaboration.
 *
 * @since unknown
 */
class CRED_Translate_Field_Factory {

    public $_formBuilder;
    public $_formHelper;

    public function __construct($formBuilder, $formHelper) {
        cred_log("__construct");
        $this->_formBuilder = $formBuilder;
        $this->_formHelper = $formHelper;
    }

    /**
     * cred_translate_option
     * @param type $option
     * @param type $key
     * @param type $form
     * @param type $field
     * @return type
     */
    private function _cred_translate_option($option, $key, $form, $field) {
        if (!isset($option['title']))
            return $option;
        $original = $option['title'];
        $option['title'] = cred_translate(
                $field['slug'] . " " . $option['title'], $option['title'], 'cred-form-' . $form->getForm()->post_title . '-' . $form->getForm()->ID
        );
        if ($original == $option['title']) {
            // Try translating with types context
            $option['title'] = cred_translate(
                    'field ' . $field['id'] . ' option ' . $key . ' title', $option['title'], 'plugin Types');
        }

        return $option;
    }

    /**
     * cred_translate_form
     * @staticvar array $_count_
     * @param type $name
     * @param type $field
     * @return type
     */
    public function cred_translate_form_name($name, &$field) {
        // allow multiple submit buttons
        static $_count_ = array(
            'submit' => 0
        );

        $count = ($field['type'] == 'form_submit') ? '_' . ($_count_['submit'] ++) : "";
        $f = "";

        if ($field['type'] == 'taxonomy_hierarchical' || $field['type'] == 'taxonomy_plain') {
            $f = "_" . $field['name'];
        } else {
            if (isset($field['master_taxonomy']) && isset($field['type'])) {
                $f = "_" . $field['master_taxonomy'] . "_" . $field['type'];
            } else {
                if (isset($field['id'])) {
                    $f = "_" . $field['id'];
                } else {
                    
                }
            }
        }
        return array("cred_form_" . CRED_StaticClass::$out['prg_id'] . $f . $count);
    }

    /**
     * get_field_object
     * @global type $post
     * @global type $post
     * @staticvar array $_count_
     * @staticvar boolean $wpExtensions
     * @param string $name
     * @param type $field
     * @param type $additional_options
     * @return type
     */
    public function cred_translate_field($name, &$field, $additional_options = array()) {
        static $_count_ = array(
            'submit' => 0
        );

        static $wpExtensions = false;
        // get refs here
        $globals = CRED_StaticClass::$_staticGlobal;
        if (false === $wpExtensions) {
            $wpMimes = $globals['MIMES'];
            $wpExtensions = implode(',', array_keys($wpMimes));
        }

        $supported_date_formats = CRED_StaticClass::$_supportedDateFormats;

        // get refs here
        $form = $this->_formBuilder->_formData;
        $postData = $this->_formBuilder->_postData;
        $zebraForm = $this->_formBuilder->_zebraForm;

        // extend additional_options with defaults
        extract(array_merge(
                        array(
            'preset_value' => null,
            'placeholder' => null,
            'value_escape' => false,
            'make_readonly' => false,
            'is_tax' => false,
            'max_width' => null,
            'max_height' => null,
            'single_select' => false,
            'generic_type' => null,
            'urlparam' => ''
                        ), $additional_options
        ));

        // add the "name" element
        // the "&" symbol is there so that $obj will be a reference to the object in PHP 4
        // for PHP 5+ there is no need for it
        $type = 'text';
        $attributes = array();
        if (isset($class))
            $attributes['class'] = $class;
        $value = '';

        $name_orig = $name;

        $field["name"] = cred_translate($field["name"], $field["name"], $form->getForm()->post_type . "-" . $form->getForm()->post_title . "-" . $form->getForm()->ID);

        if (!$is_tax) {
            // if not taxonomy field
            if (isset($placeholder) && !empty($placeholder) && is_string($placeholder)) {
                // use translated value by WPML if exists
                $placeholder = cred_translate(
                        'Value: ' . $placeholder, $placeholder, 'cred-form-' . $form->getForm()->post_title . '-' . $form->getForm()->ID
                );
                $additional_options['placeholder'] = $placeholder;
            }

            if (
				// There is a preset value
				isset( $preset_value ) 
				&& (
					// The form has not been posted
					! ( 
						$postData 
						&& isset( $postData->fields[ $name_orig ] ) 
					)
				)
            ) {
                //cred_log("preset_value");
                //cred_log($preset_value);
                // use translated value by WPML if exists, only for strings
				// For numeric values, just pass it
				if ( 
					! empty( $preset_value ) 
					&& is_string( $preset_value ) 
				) {
					$data_value = cred_translate(
							'Value: ' . $preset_value, $preset_value, 'cred-form-' . $form->getForm()->post_title . '-' . $form->getForm()->ID
					);

					$additional_options['preset_value'] = $placeholder;
				} else if ( is_numeric( $preset_value ) ) {
					$data_value = $preset_value;
				}
            } elseif ($_POST && isset($_POST) && isset($_POST[$name_orig])) {                
                $data_value = is_array($_POST[$name_orig]) ? array_map('stripslashes', $_POST[$name_orig]) : stripslashes($_POST[$name_orig]);
            } elseif ($postData && isset($postData->fields[$name_orig])) {
                //cred_log("POST DATA");
                //cred_log($postData->fields[$name_orig]);
                if (is_array($postData->fields[$name_orig]) && count($postData->fields[$name_orig]) > 1) {
                    if (isset($field['data']['repetitive']) &&
                            $field['data']['repetitive'] == 1) {
                        $data_value = $postData->fields[$name_orig];
                    }
                } else {
                    $data_value = $postData->fields[$name_orig][0];
                    //checkboxes needs to be different from from db
                    if ($field['type'] == 'checkboxes') {
                        if (isset($postData->fields[$name_orig]) &&
                                isset($postData->fields[$name_orig][0]) && is_array($postData->fields[$name_orig][0])) {
                            $save_empty = ( isset( $field['data']['save_empty'] ) && $field['data']['save_empty'] == 'yes' );
                            $data_value = array();
                            foreach ( $postData->fields[$name_orig][0] as $key => $value ) {
                                if ( $save_empty && $value == 0 ) {
                                    continue;
                                }
                                $data_value[] = $key;                                
                            }
                        }
                    }
                }
            }
            // allow field to get value through url parameter
            elseif (is_string($urlparam) && !empty($urlparam) && isset($_GET[$urlparam])) {
                //cred_log("URL PARAM");
                //cred_log($urlparam);
                // use translated value by WPML if exists
                $data_value = urldecode($_GET[$urlparam]);
            } else {
                if (!isset($preset_value))
                    $data_value = null;
            }

            // save a map between options / actual values for these types to be used later
            if (in_array($field['type'], array('checkboxes', 'radio', 'select', 'multiselect'))) {
                //cred_log($field);                
                $tmp = array();
                foreach ($field['data']['options'] as $optionKey => $optionData) {
                    if ($optionKey !== 'default' && is_array($optionData))
                        $tmp[$optionKey] = ('checkboxes' == $field['type']) ? @$optionData['set_value'] : $optionData['value'];
                }
                CRED_StaticClass::$out['field_values_map'][$field['slug']] = $tmp;
                unset($tmp);
                unset($optionKey);
                unset($optionData);
            }

            if (isset($data_value))
                $value = $data_value;

            switch ($field['type']) {
                case 'form_messages' :
                    $type = 'messages';
                    break;

                case 'form_submit':
                    $type = 'submit';

                    if (isset($preset_value) &&
                            !empty($preset_value) &&
                            is_string($preset_value)
                    ) {

                        //cred_log("preset_value");
                        //cred_log($preset_value);
                        // use translated value by WPML if exists
                        $data_value = cred_translate(
                                'Value: ' . $preset_value, $preset_value, 'cred-form-' . $form->getForm()->post_title . '-' . $form->getForm()->ID
                        );
                        $value = $data_value;

                        $additional_options['preset_value'] = $placeholder;
                    }

                    // allow multiple submit buttons
                    $name.='_' . ++$_count_['submit'];
                    break;

                case 'recaptcha':
                    $type = 'recaptcha';
                    $value = '';
                    $attributes = array(
                        'error_message' => $this->_formHelper->getLocalisedMessage('enter_valid_captcha'),
                        'show_link' => $this->_formHelper->getLocalisedMessage('show_captcha'),
                        'no_keys' => __('Enter your ReCaptcha keys at the CRED Settings page in order for ReCaptcha API to work', 'wp-cred')
                    );
                    if (false !== $globals['RECAPTCHA']) {
                        $attributes['public_key'] = $globals['RECAPTCHA']['public_key'];
                        $attributes['private_key'] = $globals['RECAPTCHA']['private_key'];
                    }
                    if (1 == CRED_StaticClass::$out['count'])
                        $attributes['open'] = true;
                    // used to load additional js script
                    CRED_StaticClass::$out['has_recaptcha'] = true;
                    break;
                case 'audio':
                case 'video':
                case 'file':
                    $type = 'cred' . $field['type'];

                    global $post;
                    if (isset($post))
                        $attachments = get_children(
                                array(
                                    'post_parent' => $post->ID,
                                    //'post_mime_type' => 'image',
                                    'post_type' => 'attachment'
                                )
                        );
                    if (isset($attachments))
                        foreach ($attachments as $pid => $attch) {
                            $guid = $attch->guid;
                            if (is_array($value)) {
                                foreach ($value as $n => &$v) {
                                    if ((isset($v) && !empty($v)) && basename($guid) == basename($v)) {
                                        $v = $guid;
                                        break;
                                    }
                                }
                            } else {
                                if ((isset($value) && !empty($value)) && basename($guid) == basename($value)) {
                                    $value = $guid;
                                }
                            }
                        }

                    break;

                case 'image':
                    //$type='file';  
                    $type = 'cred' . $field['type'];
                    // show previous post featured image thumbnail
                    if ('_featured_image' == $name) {
                        $value = '';
                        if (isset($postData->extra['featured_img_html'])) {
                            $attributes['display_featured_html'] = $value = $postData->extra['featured_img_html'];
                        }
                    }

                    global $post;
                    if (isset($post))
                        $attachments = get_children(
                                array(
                                    'post_parent' => $post->ID,
                                    //'post_mime_type' => 'image',
                                    'post_type' => 'attachment'
                                )
                        );

                    if (isset($attachments))
                        foreach ($attachments as $pid => $attch) {
                            $guid = $attch->guid;
                            if (is_array($value)) {
                                foreach ($value as $n => &$v) {
                                    if ((isset($v) && !empty($v)) && basename($guid) == basename($v)) {
                                        $v = $guid;
                                        break;
                                    }
                                }
                            } else {
                                if ((isset($value) && !empty($value)) && basename($guid) == basename($value)) {
                                    $value = $guid;
                                }
                            }
                        }
                    break;

                case 'date':
                    if (!function_exists('adodb_mktime')) {
                        require_once WPTOOLSET_FORMS_ABSPATH . '/lib/adodb-time.inc.php';
                    }
                    $type = 'date';
                    $value = array();
                    $format = get_option('date_format', '');
                    if (empty($format)) {
                        $format = $zebraForm->getDateFormat();
                        $format .= " h:i:s";
                    }
                    $attributes = array_merge($additional_options, array('format' => $format, 'readonly_element' => false, 'repetitive' => isset($field['data']['repetitive']) ? $field['data']['repetitive'] : 0));
                    if (
                            isset($data_value) &&
                            !empty($data_value) /* &&
                      (is_numeric($data_value) || is_int($data_value) || is_long($data_value)) */
                    ) {
                        if (is_array($data_value)) {
                            foreach ($data_value as $dv) {
                                if (isset($dv['datepicker']))
                                    $value[] = array('timestamp' => $dv['datepicker']);
                                else
                                    $value[] = array('timestamp' => $dv);
                            }
                        } else {
                            $value['timestamp'] = $data_value;
                        }
                    }
                    break;

                case 'select':
                case 'multiselect':

                    $type = 'select';
                    $value = array();
                    $titles = array();
                    $attributes = array();
                    $default = array();

                    if ($field['type'] == 'multiselect') {
                        $attributes = array_merge($additional_options, array('multiple' => 'multiple'));
                    } else {
                        $attributes = array_merge($additional_options);
                    }

                    $attributes['options'] = array();

                    foreach ($field['data']['options'] as $key => $option) {
                        $index = $key; //$option['value'];
                        if ('default' === $key && $option != 'no-default') {
                            $default[] = $option;
                        } else {
                            if (is_admin()) {
                                if (isset($option['title']))
                                    cred_translate_register_string('cred-form-' . $form->getForm()->post_title . '-' . $form->getForm()->ID, $field['slug'] . " " . $option['title'], $option['title'], false);
                            }
                            if (isset($option['title'])) {
                                $option = $this->_cred_translate_option($option, $key, $form, $field);
                                $attributes['options'][$index] = $option['title'];

                                if (isset($data_value) &&
                                        ($data_value == $option['value'] ||
                                        (is_array($data_value) && (array_key_exists($option['value'], $data_value) ||
                                        in_array($option['value'], $data_value))))) {

                                    if ('select' == $field['type']) {
                                        $titles[] = $key;
                                        $value = $option['value'];
                                    } else {
                                        $value = $data_value;
                                    }
                                }
                                if (isset($option['dummy']) && $option['dummy'])
                                    $attributes['dummy'] = $key;
                            }
                        }
                    }

                    if ($field['type'] == 'multiselect') {
                        if (empty($value) && !empty($default)) {
                            $value = $default;
                        }
                    } else {
                        if (empty($titles) && !empty($default[0])) {
                            $titles = isset($field['data']['options'][$default[0]]['value']) ? $field['data']['options'][$default[0]]['value'] : "";
                        }
                        $attributes['actual_value'] = isset($data_value) && !empty($data_value) ? $data_value : $titles;
                    }
                    if (isset(CRED_StaticClass::$out['field_values_map'][$field['slug']]))
                        $attributes['actual_options'] = CRED_StaticClass::$out['field_values_map'][$field['slug']];

                    break;

                case 'radio':
                    $type = 'radios';
                    $value = array();
                    $titles = array();
                    $attributes = array();
                    $attributes = array_merge($additional_options);
                    $default = '';

                    $default = isset($field['data']['options']['default']) ? $field['data']['options']['default'] : "";
                    if (isset($field['data']['options']['default']))
                        unset($field['data']['options']['default']);

                    $set_default = false;
                    foreach ($field['data']['options'] as $key => &$option) {
                        if (isset($option['value']))
                            $option['value'] = str_replace("\\", "", $option['value']);

                        if (!$set_default && $key == $default) {
                            $set_default = true;
                            $default = $option['value'];
                        }

                        $index = $key;

                        if (is_admin()) {
                            //register strings on form save
                            cred_translate_register_string('cred-form-' . $form->getForm()->post_title . '-' . $form->getForm()->ID, $field['slug'] . " " . $option['title'], $option['title'], false);
                        }
                        $option = $this->_cred_translate_option($option, $key, $form, $field);

                        $titles[$index] = $option['title'];

                        if (isset($data_value) && $data_value == $option['value']) {
                            $attributes = isset($option['value']) ? $option['value'] : $key;
                            $value = isset($option['value']) ? $option['value'] : $key;
                        }
                    }

                    if (!isset($data_value) && !empty($default)) {
                        $attributes = $default;
                    }
                    $def = $attributes;
                    $attributes = array('default' => $def);
                    $attributes['actual_titles'] = $titles;

                    if (isset(CRED_StaticClass::$out['field_values_map'][$field['slug']]))
                        $attributes['actual_values'] = CRED_StaticClass::$out['field_values_map'][$field['slug']];

                    foreach ($attributes['actual_values'] as $k => &$option) {
                        $option = str_replace("\\", "", $option);
                    }

                    break;

                case 'checkboxes':
                    $type = 'checkboxes';
                    $save_empty = isset($field['data']['save_empty']) ? $field['data']['save_empty'] : false;
                    $value = array();
                    if (isset($data_value) && !empty($data_value)) {
                        if (!is_array($data_value)) {
                            foreach ($field['data']['options'] as $v => $v1) {
                                if ($v1['set_value'] == $data_value) {
                                    $data_value = array($v => $data_value);
                                }
                            }
                        } else {
                            if (count(array_filter(array_keys($data_value), 'is_string')) > 0) {
                                $new_data_value = array();
                                foreach ($field['data']['options'] as $v => $v1) {
                                    if (in_array($v1['set_value'], $data_value)) {
                                        $new_data_value[$v] = $v1['set_value'];
                                    }
                                }
                                $data_value = $new_data_value;
                                unset($new_data_value);
                            }
                        }
                        foreach ($data_value as $v => $v1) {
                            if ($save_empty || $field['cred_generic'] == 1) {
                                $value[$v] = $v1;
                            } else
                                $value[$v] = 1;
                        }
                    }

                    $titles = array();
                    $attributes = array();
                    $attributes = array_merge($additional_options);

                    if (isset($data_value) && !is_array($data_value))
                        $data_value = array($data_value);

                    foreach ($field['data']['options'] as $key => $option) {
                        if (is_admin()) {
                            //register strings on form save
                            cred_translate_register_string('cred-form-' . $form->getForm()->post_title . '-' . $form->getForm()->ID, $field['slug'] . " " . $option['title'], $option['title'], false);
                        }
                        $option = $this->_cred_translate_option($option, $key, $form, $field);
                        $index = $key;
                        $titles[$index] = $option['title'];
                        if (empty($value)) {
                            if (isset($data_value) && !empty($data_value) && isset($data_value[$index]))
                                $value[$index] = $data_value[$index];
                            else
                                $value[$index] = 0;
                        }
                        if (isset($option['checked']) && $option['checked'] && ! isset( $data_value ) ) {
                            $attributes[] = $index;
                        } elseif (isset($data_value) && isset($data_value[$index]) /* && in_array($index,$data_value) */) {
                            if (
                                    !(isset($field['data']['save_empty']) && 'yes' == $field['data']['save_empty'] && (0 === $data_value[$index] || '0' === $data_value[$index]))
                            )
                                $attributes[] = $index;
                        }
                    }
                    $def = $attributes;
                    $attributes = array('default' => $def);
                    $attributes['actual_titles'] = $titles;
                    if (isset(CRED_StaticClass::$out['field_values_map'][$field['slug']]))
                        $attributes['actual_values'] = CRED_StaticClass::$out['field_values_map'][$field['slug']];
                    break;

                case 'checkbox':
                    $save_empty = isset($field['data']['save_empty']) ? $field['data']['save_empty'] : false;
                    //If save empty and $_POST is set but checkbox is not set data value 0
                    if (isset($data_value) &&
                            $data_value == 1 &&
                            $save_empty == 'no' &&
                            isset($_POST) && !empty($_POST) && !isset($_POST[$name_orig]))
                        $data_value = 0;

                    $type = 'checkbox';

                    $value = $field['data']['set_value'];
                    $attributes = array();
                    if (isset($data_value) && $data_value == $value)
                        $attributes = array('checked' => 'checked');
                    $attributes = array_merge($attributes, $additional_options);
                    if (is_admin()) {
                        //register strings on form save
                        cred_translate_register_string('cred-form-' . $form->getForm()->post_title . '-' . $form->getForm()->ID, $field['slug'], $field['name'], false);
                    }
                    $field['name'] = cred_translate($field['slug'], $field['name'], 'cred-form-' . $form->getForm()->post_title . '-' . $form->getForm()->ID);
                    break;

                case 'textarea':
                    $type = 'textarea';
                    $attributes = array_merge($additional_options);
                    break;

                case 'wysiwyg':
                    $type = 'wysiwyg';
                    $attributes = array_merge($additional_options, array('disable_xss_filters' => true));
                    //cred_log($form->fields);
                    if ('post_content' == $name && isset($form->fields['form_settings']->form['has_media_button']) && $form->fields['form_settings']->form['has_media_button'])
                        $attributes['has_media_button'] = true;
                    break;

                case 'integer':
                    $type = 'integer';
                    $attributes = array_merge($attributes, $additional_options);
                    break;

                case 'numeric':
                    $type = 'numeric';
                    $attributes = array_merge($attributes, $additional_options);
                    break;

                case 'phone':
                    $type = 'phone';
                    $attributes = array_merge($attributes, $additional_options);
                    break;

                case 'embed':
                case 'url':
                    $type = 'url';
                    $attributes = array_merge($attributes, $additional_options);
                    break;

                case 'email':
                    $type = 'email';
                    $attributes = array_merge($attributes, $additional_options);
                    break;

                case 'colorpicker':
                    $type = 'colorpicker';
                    $attributes = array_merge($attributes, $additional_options);
                    break;

                case 'textfield':
                    $type = 'textfield';
                    $attributes = array_merge($attributes, $additional_options);
                    break;

                case 'password':
                    $type = 'password';
                    $attributes = array_merge($attributes, $additional_options);
                    break;

                case 'hidden':
                    $type = 'hidden';
                    $attributes = array_merge($attributes, $additional_options);
                    break;
                case 'skype':
                    $type = 'skype';
                    //if for some reason i receive data_value as array but it is not repetitive i need to get as not array of array
                    //if (isset($field['data']['repetitive']) && $field['data']['repetitive'] == 1)
					// Note that generic skype fields are not repetitive and $data_value is a string...
					if ( isset( $field['cred_generic'] ) && $field['cred_generic'] ) {
						$data_value = array(
							'skypename' => isset( $data_value ) ? $data_value : '', 
							'style' => ''
						);
					} else if ( isset( $data_value ) ) {
						if (isset($field['data']['repetitive']) && $field['data']['repetitive'] == 0 && isset($data_value[0]))
							$data_value = $data_value[0];

						if (isset($field['data']['repetitive']) && $field['data']['repetitive'] == 1 && !isset($data_value[0]))
							$data_value = array($data_value);
					}

                    if (isset($data_value)) {
                        if (isset($field['data']['repetitive']) && $field['data']['repetitive'] == 0)
                            $value = $data_value;
                        else {
                            if (is_string($data_value))
                                $data_value = array('skypename' => $data_value, 'style' => '');
                            $value = $data_value;
                        }
                    } else {
                        $value = array('skypename' => '', 'style' => '');
                        $data_value = $value;
                    }

                    $attributes = array(
                        'ajax_url' => admin_url('admin-ajax.php'),
                        'edit_skype_text' => $this->_formHelper->getLocalisedMessage('edit_skype_button'),
                        'value' => isset($data_value[0]['skypename']) ? $data_value[0]['skypename'] : $data_value['skypename'],
                        '_nonce' => wp_create_nonce('insert_skype_button')
                    );
                    $attributes = array_merge($attributes, $additional_options);
                    break;

                // everything else defaults to a simple text field
                default:
                    $type = 'textfield';
                    $attributes = array_merge($attributes, $additional_options);
                    break;
            }

            if (isset($attributes['make_readonly']) && !empty($attributes['make_readonly'])) {
                unset($attributes['make_readonly']);
                if (!is_array($attributes))
                    $attributes = array();
                $attributes['readonly'] = 'readonly';
            }

            // repetitive field (special care)
            if (isset($field['data']['repetitive']) && $field['data']['repetitive']) {
                $value = isset($postData->fields[$name_orig]) ? $postData->fields[$name_orig] : isset($value) ? $value : array();
                $objs = $zebraForm->add($type, $name, $value, $attributes, $field);
            } else {
                $objs = $zebraForm->add($type, $name, $value, $attributes, $field);
            }
        } else {
            // taxonomy field or auxilliary taxonomy field (eg popular terms etc..)
            if (!array_key_exists('master_taxonomy', $field)) { // taxonomy field
                if ($field['hierarchical']) {
                    if (in_array($preset_value, array('checkbox', 'select')))
                        $tax_display = $preset_value;
                    else
                        $tax_display = 'checkbox';
                }

                if ($postData && isset($postData->taxonomies[$name_orig])) {
                    if (!$field['hierarchical']) {
                        $data_value = array(
                            'terms' => $postData->taxonomies[$name_orig]['terms'],
                            'add_text' => $this->_formHelper->getLocalisedMessage('add_taxonomy'),
                            'remove_text' => $this->_formHelper->getLocalisedMessage('remove_taxonomy'),
                            'ajax_url' => admin_url('admin-ajax.php'),
                            'auto_suggest' => true,
                            'show_popular_text' => $this->_formHelper->getLocalisedMessage('show_popular'),
                            'hide_popular_text' => $this->_formHelper->getLocalisedMessage('hide_popular'),
                            'show_popular' => $show_popular
                        );
                    } else {
                        $data_value = array(
                            'terms' => $postData->taxonomies[$name_orig]['terms'],
                            'all' => $field['all'],
                            'add_text' => $this->_formHelper->getLocalisedMessage('add_taxonomy'),
                            'add_new_text' => $this->_formHelper->getLocalisedMessage('add_new_taxonomy'),
                            'parent_text' => __('-- Parent --', 'wp-cred'),
                            'type' => $tax_display,
                            'single_select' => $single_select
                        );
                    }
                } else {
                    if (!$field['hierarchical']) {
                        $data_value = array(
                            //'terms'=>array(),
                            'add_text' => $this->_formHelper->getLocalisedMessage('add_taxonomy'),
                            'remove_text' => $this->_formHelper->getLocalisedMessage('remove_taxonomy'),
                            'ajax_url' => admin_url('admin-ajax.php'),
                            'auto_suggest' => true,
                            'show_popular_text' => $this->_formHelper->getLocalisedMessage('show_popular'),
                            'hide_popular_text' => $this->_formHelper->getLocalisedMessage('hide_popular'),
                            'show_popular' => $show_popular
                        );
                    } else {
                        $data_value = array(
                            'all' => $field['all'],
                            'add_text' => $this->_formHelper->getLocalisedMessage('add_taxonomy'),
                            'add_new_text' => $this->_formHelper->getLocalisedMessage('add_new_taxonomy'),
                            'parent_text' => __('-- Parent --', 'wp-cred'),
                            'type' => $tax_display,
                            'single_select' => $single_select
                        );
                    }
                }

                // if not hierarchical taxonomy
                if (!$field['hierarchical']) {
                    $objs = /* & */ $zebraForm->add('taxonomy', $name, $value, $data_value);
                } else {
                    $objs = /* & */ $zebraForm->add('taxonomyhierarchical', $name, $value, $data_value);
                }

                // register this taxonomy field for later use by auxilliary taxonomy fields
                CRED_StaticClass::$out['taxonomy_map']['taxonomy'][$name_orig] = &$objs;
                // if a taxonomy auxiliary field exists attached to this taxonomy, add this taxonomy id to it
                if (isset(CRED_StaticClass::$out['taxonomy_map']['aux'][$name_orig])) {
                    CRED_StaticClass::$out['taxonomy_map']['aux'][$name_orig]->set_attributes(array('master_taxonomy_id' => $objs->attributes['id']));
                }
            } else { // taxonomy auxilliary field (eg most popular etc..)
                if (isset($preset_value))
                // use translated value by WPML if exists
                    $data_value = cred_translate(
                            'Value: ' . $preset_value, $preset_value, 'cred-form-' . $form->form->post_title . '-' . $form->form->ID
                    );
                else
                    $data_value = null;
            }
        }

        return $objs;
    }

}
