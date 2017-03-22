<?php

class CRED_Field extends CRED_Field_Abstract {

    public function __construct($atts, $credRenderingForm, $formHelper, $formData, $translate_field_factory) {
        parent::__construct($atts, $credRenderingForm, $formHelper, $formData, $translate_field_factory);
    }

    public function get_field() {                
        $formHelper = $this->_formHelper;
        $form = $this->_formData;
        $_fields = $form->getFields();
        $form_type = $_fields['form_settings']->form['type'];
        $post_type = $_fields['form_settings']->post['post_type'];

        extract(shortcode_atts(array(
            'class' => '',
            'post' => '',
            'field' => '',
            'value' => null,
            'urlparam' => '',
            'placeholder' => null,
            'escape' => 'false',
            'readonly' => 'false',
            'taxonomy' => null,
            'single_select' => null,
            'type' => null,
            'display' => null,
            'max_width' => null,
            'max_height' => null,
            'max_results' => null,
            'order' => null,
            'ordering' => null,
            'required' => 'false',
            'no_parent_text' => __('No Parent', 'wp-cred'),
            'select_text' => __('-- Please Select --', 'wp-cred'),
            'validate_text' => $formHelper->getLocalisedMessage('field_required'),
            'show_popular' => false
                        ), $this->_atts));

        $field_name = $field;

        //result of this use fix_cred_field_shortcode_value_attribute_by_single_quote
        $value = str_replace("@_cred_rsq_@", "'", $value);

        if ($field == 'form_messages') {
            $post_not_saved_singular = str_replace("%PROBLEMS_UL_LIST", "", $formHelper->getLocalisedMessage('post_not_saved_singular'));
            $post_not_saved_plural = str_replace("%PROBLEMS_UL_LIST", "", $formHelper->getLocalisedMessage('post_not_saved_plural'));

            return '<label id="wpt-form-message-' . $form->getForm()->ID . '"
              data-message-single="' . esc_js($post_not_saved_singular) . '"
              data-message-plural="' . esc_js($post_not_saved_plural) . '"
              style="display:none;" class="wpt-top-form-error wpt-form-error"></label><!CRED_ERROR_MESSAGE!>';
        }

        // make boolean
        $escape = false; //(bool)(strtoupper($escape)==='TRUE');
        // make boolean
        $readonly = (bool) (strtoupper($readonly) === 'TRUE');

        if (!$taxonomy) {

            $fieldObj = null;
            if (
                    array_key_exists('post_fields', CRED_StaticClass::$out['fields']) &&
                    is_array(CRED_StaticClass::$out['fields']['post_fields']) &&
                    in_array($field_name, array_keys(CRED_StaticClass::$out['fields']['post_fields']))
            ) {
                if ($post != $post_type)
                    return '';

                $field = CRED_StaticClass::$out['fields']['post_fields'][$field_name];
                $name = $name_orig = $field['slug'];

                if ((isset($value) && empty($value)) && (isset($field['data']['user_default_value']) && !empty($field['data']['user_default_value'])))
                    $value = $field['data']['user_default_value'];

                if ((!isset($placeholder) || empty($placeholder)) && isset($field['data']['placeholder'])) {
                    $placeholder = $field['data']['placeholder'];
                }

                if (isset($field['plugin_type_prefix']))
                    $name = /* 'wpcf-' */$field['plugin_type_prefix'] . $name;

                if ('credimage' == $field['type'] ||
                        'image' == $field['type'] ||
                        'file' == $field['type'] ||
                        'credfile' == $field['type']) {
                    $fieldObj = $this->_translate_field_factory->cred_translate_field($name, $field, array(
                        'class' => $class,
                        'preset_value' => $value,
                        'urlparam' => $urlparam,
                        'is_tax' => false,
                        'max_width' => $max_width,
                        'max_height' => $max_height));
                } else {
                    $fieldObj = $this->_translate_field_factory->cred_translate_field($name, $field, array(
                        'class' => $class,
                        'preset_value' => $value,
                        'urlparam' => $urlparam,
                        'value_escape' => $escape,
                        'make_readonly' => $readonly,
                        'placeholder' => $placeholder));
                }

                // check which fields are actually used in form
                /* old Form_Builder_Helper->translate_field */
                CRED_StaticClass::$out['form_fields'][$name_orig] = $this->_translate_field_factory->cred_translate_form_name($name, $field);
                CRED_StaticClass::$out['form_fields_info'][$name_orig] = array(
                    'type' => $field['type'],
                    'repetitive' => (isset($field['data']['repetitive']) && $field['data']['repetitive']),
                    'plugin_type' => (isset($field['plugin_type'])) ? $field['plugin_type'] : '',
                    'name' => $name,
                );
            } elseif (
                    array_key_exists('custom_fields', CRED_StaticClass::$out['fields']) &&
                    is_array(CRED_StaticClass::$out['fields']['custom_fields']) &&
                    in_array(strtolower($field_name), array_keys(CRED_StaticClass::$out['fields']['custom_fields']))
            ) {
                if ($post != $post_type)
                    return '';

                $field = CRED_StaticClass::$out['fields']['custom_fields'][$field_name];
                $name = $name_orig = $field['slug'];

                if ((isset($value) && empty($value)) && (isset($field['data']['user_default_value']) && !empty($field['data']['user_default_value'])))
                    $value = $field['data']['user_default_value'];

                if (isset($field['plugin_type_prefix']))
                    $name = /* 'wpcf-' */$field['plugin_type_prefix'] . $name;

                if ('credimage' == $field['type'] ||
                        'image' == $field['type'] ||
                        'file' == $field['type'] ||
                        'credfile' == $field['type']) {
                    $fieldObj = $this->_translate_field_factory->cred_translate_field($name, $field, array(
                        'class' => $class,
                        'preset_value' => $value,
                        'urlparam' => $urlparam,
                        'is_tax' => false,
                        'max_width' => $max_width,
                        'max_height' => $max_height));
                } else {
                    $fieldObj = $this->_translate_field_factory->cred_translate_field($name, $field, array(
                        'class' => $class,
                        'preset_value' => $value,
                        'urlparam' => $urlparam,
                        'value_escape' => $escape,
                        'make_readonly' => $readonly,
                        'placeholder' => $placeholder));
                }

                // check which fields are actually used in form
                CRED_StaticClass::$out['form_fields'][$name_orig] = $this->cred_translate_form_name($name, $field);
                CRED_StaticClass::$out['form_fields_info'][$name_orig] = array(
                    'type' => $field['type'],
                    'repetitive' => (isset($field['data']['repetitive']) && $field['data']['repetitive']),
                    'plugin_type' => (isset($field['plugin_type'])) ? $field['plugin_type'] : '',
                    'name' => $name,
                );
            } elseif (
                    array_key_exists('parents', CRED_StaticClass::$out['fields']) &&
                    is_array(CRED_StaticClass::$out['fields']['parents']) &&
                    in_array($field_name, array_keys(CRED_StaticClass::$out['fields']['parents']))
            ) {
                $name = $name_orig = $field_name;
                $field = CRED_StaticClass::$out['fields']['parents'][$field_name];

                if ((isset($value) &&
                        empty($value)) && (isset($field['data']['user_default_value']) &&
                        !empty($field['data']['user_default_value'])))
                    $value = $field['data']['user_default_value'];

                $potential_parents = CRED_Loader::get('MODEL/Fields')->getPotentialParents($field['data']['post_type'], $this->_cred_rendering->_post_id, $max_results, 'title', 'ASC');
                $field['data']['options'] = array();

                $default_option = '';
                // enable setting parent form url param
                if (array_key_exists('parent_' . $field['data']['post_type'] . '_id', $_GET))
                    $default_option = $_GET['parent_' . $field['data']['post_type'] . '_id'];

                $required = (bool) (strtoupper($required) === 'TRUE');
                if (!$required) {
                    $field['data']['options']['-1'] = array(
                        'title' => $no_parent_text,
                        'value' => '-1',
                        'display_value' => '-1'
                    );
                } else {
                    $field['data']['options']['-1'] = array(
                        'title' => $select_text,
                        'value' => '',
                        'display_value' => '',
                        'dummy' => true
                    );
                    $field['data']['validate'] = array(
                        'required' => array('message' => $validate_text, 'active' => 1)
                    );
                }
                foreach ($potential_parents as $ii => $option) {
                    $option_id = (string) ($option->ID);
                    $field['data']['options'][$option_id] = array(
                        'title' => $option->post_title,
                        'value' => $option_id,
                        'display_value' => $option_id
                    );
                }
                $field['data']['options']['default'] = $default_option;

                $add_opt = array('preset_value' => $value, 'urlparam' => $urlparam, 'make_readonly' => $readonly, 'max_width' => $max_width, 'max_height' => $max_height, 'class' => $class);
                $fieldObj = $this->_translate_field_factory->cred_translate_field($name, $field, $add_opt);

                // check which fields are actually used in form
                CRED_StaticClass::$out['form_fields'][$name_orig] = $this->_translate_field_factory->cred_translate_form_name($name, $field);
                CRED_StaticClass::$out['form_fields_info'][$name_orig] = array(
                    'type' => $field['type'],
                    'repetitive' => (isset($field['data']['repetitive']) && $field['data']['repetitive']),
                    'plugin_type' => (isset($field['plugin_type'])) ? $field['plugin_type'] : '',
                    'name' => $name
                );
            } elseif (
                    (array_key_exists('form_fields', CRED_StaticClass::$out['fields']) &&
                    is_array(CRED_StaticClass::$out['fields']['form_fields']) &&
                    in_array($field_name, array_keys(CRED_StaticClass::$out['fields']['form_fields']))) ||
                    (array_key_exists('user_fields', CRED_StaticClass::$out['fields']) &&
                    is_array(CRED_StaticClass::$out['fields']['user_fields']) &&
                    in_array($field_name, array_keys(CRED_StaticClass::$out['fields']['user_fields'])))
            ) {
                $name = $name_orig = $field_name;
                $field = CRED_StaticClass::$out['fields']['form_fields'][$field_name];

                if ((isset($value) && empty($value)) && (isset($field['data']['user_default_value']) && !empty($field['data']['user_default_value'])))
                    $value = $field['data']['user_default_value'];

                $add_opt = array('preset_value' => $value, 'urlparam' => $urlparam, 'make_readonly' => $readonly, 'max_width' => $max_width, 'max_height' => $max_height, 'class' => $class, 'placeholder' => $placeholder);
                $fieldObj = $this->_translate_field_factory->cred_translate_field($name, $field, $add_opt);

                //cred-161
                if ($form_type == 'edit' &&
                        ($fieldObj['name'] == 'user_pass' ||
                        $fieldObj['name'] == 'user_pass2')) {

                    if (isset($fieldObj['data']['validate']) &&
                            isset($fieldObj['data']['validate']['required']))
                        unset($fieldObj['data']['validate']['required']);
                }

                // check which fields are actually used in form
                CRED_StaticClass::$out['form_fields'][$name_orig] = $this->_translate_field_factory->cred_translate_form_name($name, $field);
                CRED_StaticClass::$out['form_fields_info'][$name_orig] = array(
                    'type' => $field['type'],
                    'repetitive' => (isset($field['data']['repetitive']) && $field['data']['repetitive']),
                    'plugin_type' => (isset($field['plugin_type'])) ? $field['plugin_type'] : '',
                    'name' => $name
                );
            } elseif (
                    array_key_exists('extra_fields', CRED_StaticClass::$out['fields']) &&
                    is_array(CRED_StaticClass::$out['fields']['extra_fields']) &&
                    in_array($field_name, array_keys(CRED_StaticClass::$out['fields']['extra_fields']))
            ) {
                $field = CRED_StaticClass::$out['fields']['extra_fields'][$field_name];
                $name = $name_orig = $field['slug'];

                if ((isset($value) && empty($value)) && (isset($field['data']['user_default_value']) && !empty($field['data']['user_default_value'])))
                    $value = $field['data']['user_default_value'];

                $add_opt = array('preset_value' => $value, 'urlparam' => $urlparam, 'make_readonly' => $readonly, 'max_width' => $max_width, 'max_height' => $max_height, 'class' => $class, 'placeholder' => $placeholder);
                $fieldObj = $this->_translate_field_factory->cred_translate_field($name, $field, $add_opt);
                
                // check which fields are actually used in form
                CRED_StaticClass::$out['form_fields'][$name_orig] = $this->_translate_field_factory->cred_translate_form_name($name, $field);
                CRED_StaticClass::$out['form_fields_info'][$name_orig] = array(
                    'type' => $field['type'],
                    'repetitive' => (isset($field['data']['repetitive']) && $field['data']['repetitive']),
                    'plugin_type' => (isset($field['plugin_type'])) ? $field['plugin_type'] : '',
                    'name' => $name
                );
            }
            // taxonomy field
            elseif (
                    array_key_exists('taxonomies', CRED_StaticClass::$out['fields']) &&
                    is_array(CRED_StaticClass::$out['fields']['taxonomies']) &&
                    in_array($field_name, array_keys(CRED_StaticClass::$out['fields']['taxonomies']))
            ) {
                $field = CRED_StaticClass::$out['fields']['taxonomies'][$field_name];
                $name = $name_orig = $field['name'];

                if ((isset($value) && empty($value)) && (isset($field['data']['user_default_value']) && !empty($field['data']['user_default_value'])))
                    $value = $field['data']['user_default_value'];

                $single_select = ($single_select === 'true');
                $add_opt = array('preset_value' => $display, 'is_tax' => true, 'single_select' => $single_select, 'show_popular' => $show_popular, 'placeholder' => $placeholder);
                $fieldObj = $this->_translate_field_factory->cred_translate_field($name, $field, $add_opt);
                
                // check which fields are actually used in form
                CRED_StaticClass::$out['form_fields'][$name_orig] = $this->_translate_field_factory->cred_translate_form_name($name, $field);
                CRED_StaticClass::$out['form_fields_info'][$name_orig] = array(
                    'type' => $field['type'],
                    'repetitive' => (isset($field['data']['repetitive']) && $field['data']['repetitive']),
                    'plugin_type' => (isset($field['plugin_type'])) ? $field['plugin_type'] : '',
                    'name' => $name,
                    'display' => $value,
                );
            }

            if ($fieldObj) {
                return $this->_cred_rendering->renderField($fieldObj);
            } elseif (current_user_can('manage_options')) {
                return sprintf(
                        '<p class="alert">%s</p>', sprintf(
                                __('There is a problem with %s field. Please check CRED form.', 'wp-cred'), $field
                        )
                );
            }
        } else {
            if (
                    array_key_exists('taxonomies', CRED_StaticClass::$out['fields']) &&
                    is_array(CRED_StaticClass::$out['fields']['taxonomies']) &&
                    in_array($taxonomy, array_keys(CRED_StaticClass::$out['fields']['taxonomies'])) &&
                    in_array($type, array('show_popular', 'add_new'))
            ) {
                if (// auxilliary field type matches taxonomy type
                        ($type == 'show_popular' && !CRED_StaticClass::$out['fields']['taxonomies'][$taxonomy]['hierarchical']) ||
                        ($type == 'add_new' && CRED_StaticClass::$out['fields']['taxonomies'][$taxonomy]['hierarchical'])
                ) {
                    // add a placeholder for the 'show_popular' or 'add_new' buttons.
                    // the real buttons will be copied to this position via js                    
                    // added data-label text from value shortcode attribute
                    return '<div class="js-taxonomy-button-placeholder" data-taxonomy="' . $taxonomy . '" data-label="' . $value . '" style="display:none"></div>';
                }
            }
        }
        return '';
    }

}
