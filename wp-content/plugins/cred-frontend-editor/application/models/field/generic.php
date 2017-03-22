<?php

class CRED_Generic_Field extends CRED_Generic_Field_Abstract {

    public function __construct($atts, $content, $credRenderingForm, $formHelper, $formData, $translateFieldFactory) {
        parent::__construct($atts, $content, $credRenderingForm, $formHelper, $formData, $translateFieldFactory);
    }

    public function get_field() {
        $atts = shortcode_atts(array(
            'field' => '',
            'type' => '',
            'class' => '',
            'placeholder' => null,
            'urlparam' => ''
                ), $this->_atts);

        $content = $this->_content;

        if (empty($atts['field']) || empty($atts['type']) || null == $content || empty($content))
            return ''; // ignore

        $field_data = json_decode(preg_replace('/[\r\n]/', '', $content), true); // remove NL (crlf) to prevent json_decode from failing        
        // only for php >= 5.3.0
        if (
                (function_exists('json_last_error') && json_last_error() != JSON_ERROR_NONE) ||
                empty($field_data) /* probably JSON decode error */
        ) {
            cred_log('cred_generic_field_shortcodes error: ' . json_last_error());
            return ''; //ignore not valid json
        }

        $formHelper = $this->_formHelper;

        $field = array(
            'id' => $atts['field'],
            'cred_generic' => true,
            'slug' => $atts['field'],
            'type' => $atts['type'],
            'name' => $atts['field'],
            'data' => array(
                'repetitive' => 0,
                'validate' => array(
                    'required' => array(
                        'active' => $field_data['required'],
                        'value' => $field_data['required'],
                        'message' => $formHelper->getLocalisedMessage('field_required')
                    )
                ),
                'validate_format' => $field_data['validate_format'],
                'persist' => isset($field_data['persist']) ? $field_data['persist'] : 0
            )
        );

        $default = $field_data['default'];
        $class = ( isset($atts['class']) ) ? $atts['class'] : '';

        switch ($atts['type']) {
            case 'checkbox':
                $field['label'] = isset($field_data['label']) ? $field_data['label'] : '';
                $field['data']['set_value'] = $field_data['default'];
                if ($field_data['checked'] != 1)
                    $default = null;
                break;
            case 'checkboxes':
                $field['data']['options'] = array();
                foreach ($field_data['options'] as $ii => $option) {
                    $option_id = $option['value'];
                    //$option_id=$atts['field'].'_option_'.$ii;
                    $field['data']['options'][$option_id] = array(
                        'title' => $option['label'],
                        'set_value' => $option['value']
                    );
                    if (in_array($option['value'], $field_data['default'])) {
                        $field['data']['options'][$option_id]['checked'] = true;
                    }
                    /**
                     * check post data, maybe this form fail validation
                     */
                    if (
                            !empty($_POST) && array_key_exists($field['id'], $_POST) && is_array($_POST[$field['id']]) && in_array($option['value'], $_POST[$field['id']])
                    ) {
                        $field['data']['options'][$option_id]['checked'] = true;
                    }
                }
                $default = null;
                break;
            case 'date':
                $field['data']['validate']['date'] = array(
                    'active' => $field_data['validate_format'],
                    'format' => 'mdy',
                    'message' => $formHelper->getLocalisedMessage('enter_valid_date')
                );
                $field['data']['date_and_time'] = isset($field_data['date_and_time']) ? $field_data['date_and_time'] : '';
                // allow a default value
                //$default=null;
                break;
            case 'hidden':
                $field['data']['validate']['hidden'] = array(
                    'active' => $field_data['validate_format'],
                    'message' => $formHelper->getLocalisedMessage('values_do_not_match')
                );
                break;
            case 'radio':
            case 'select':
                $field['data']['options'] = array();
                $default_option = 'no-default';
                foreach ($field_data['options'] as $ii => $option) {
                    $option_id = $option['value'];
                    //$option_id=$atts['field'].'_option_'.$ii;
                    $field['data']['options'][$option_id] = array(
                        'title' => $option['label'],
                        'value' => $option['value'],
                        'display_value' => $option['value']
                    );
                    if (!empty($field_data['default']) && $field_data['default'][0] == $option['value'])
                        $default_option = $option_id;
                }
                $field['data']['options']['default'] = $default_option;
                $default = null;
                break;
            case 'multiselect':
                $field['data']['options'] = array();
                $default_option = array();
                foreach ($field_data['options'] as $ii => $option) {
                    $option_id = $option['value'];
                    //$option_id=$atts['field'].'_option_'.$ii;
                    $field['data']['options'][$option_id] = array(
                        'title' => $option['label'],
                        'value' => $option['value'],
                        'display_value' => $option['value']
                    );
                    if (!empty($field_data['default']) && in_array($option['value'], $field_data['default']))
                        $default_option[] = $option_id;
                }
                $field['data']['options']['default'] = $default_option;
                $field['data']['is_multiselect'] = 1;
                $default = null;
                break;
            case 'email':
                $field['data']['validate']['email'] = array(
                    'active' => $field_data['validate_format'],
                    'message' => $formHelper->getLocalisedMessage('enter_valid_email')
                );
                break;
            case 'numeric':
                $field['data']['validate']['number'] = array(
                    'active' => $field_data['validate_format'],
                    'message' => $formHelper->getLocalisedMessage('enter_valid_number')
                );
                break;
            case 'integer':
                $field['data']['validate']['integer'] = array(
                    'active' => $field_data['validate_format'],
                    'message' => $formHelper->getLocalisedMessage('enter_valid_number')
                );
                break;
            case 'embed':
            case 'url':
                $field['data']['validate']['url'] = array(
                    'active' => $field_data['validate_format'],
                    'message' => $formHelper->getLocalisedMessage('enter_valid_url')
                );
                break;
            default:
                $default = $field_data['default'];
                break;
        }

        $name = $field['slug'];
        if ($atts['type'] == 'image' || $atts['type'] == 'file') {
            if (isset($field_data['max_width']) && is_numeric($field_data['max_width']))
                $max_width = intval($field_data['max_width']);
            else
                $max_width = null;
            if (isset($field_data['max_height']) && is_numeric($field_data['max_height']))
                $max_height = intval($field_data['max_height']);
            else
                $max_height = null;

            if (isset($field_data['generic_type']))
                $generic_type = intval($field_data['generic_type']);
            else
                $generic_type = null;

//            $ids = $formHelper->translate_field($name, $field, array(
//                'preset_value' => $default,
//                'urlparam' => $atts['urlparam'],
//                'is_tax' => false,
//                'max_width' => $max_width,
//                'max_height' => $max_height));
            
            $fieldObj = $this->_translate_field_factory->cred_translate_field($name, $field, array(
                'class' => $class,
                'preset_value' => $default,
                'urlparam' => $atts['urlparam'],
                'generic_type' => $generic_type)
            );
        }
        else if ($atts['type'] == 'hidden') {
            if (isset($field_data['generic_type']))
                $generic_type = intval($field_data['generic_type']);
            else
                $generic_type = null;

//            $ids = $formHelper->translate_field($name, $field, array(
//                'preset_value' => $default,
//                'urlparam' => $atts['urlparam'],
//                'generic_type' => $generic_type)
//            );

            $fieldObj = $this->_translate_field_factory->cred_translate_field($name, $field, array(
                'class' => $class,
                'preset_value' => $default,
                'urlparam' => $atts['urlparam'],
                'generic_type' => $generic_type)
            );
        }
        else {
//            $ids = $formHelper->translate_field($name, $field, array(
//                'preset_value' => $default,
//                'cred_generic' => 1,
//                'placeholder' => $atts['placeholder'],
//                'urlparam' => $atts['urlparam']));

            $fieldObj = $this->_translate_field_factory->cred_translate_field($name, $field, array(
                'class' => $class,
                'preset_value' => $default,
                'cred_generic' => 1,
                'placeholder' => $atts['placeholder'],
                'urlparam' => $atts['urlparam']));
        }

        if ($field['data']['persist']) {
            // this field is going to be saved as custom field to db
            CRED_StaticClass::$out['fields']['post_fields'][$name] = $field;
        }
        // check which fields are actually used in form

        CRED_StaticClass::$out['form_fields'][$name] = $this->_translate_field_factory->cred_translate_form_name($name, $field);
        CRED_StaticClass::$out['form_fields_info'][$name] = array(
            'type' => $field['type'],
            'repetitive' => (isset($field['data']['repetitive']) && $field['data']['repetitive']),
            'plugin_type' => (isset($field['plugin_type'])) ? $field['plugin_type'] : '',
            'name' => $name
        );
//        $this->out_['generic_fields'][$name] = $ids;
        if (!empty($atts['class'])) {
            $atts['class'] = esc_attr($atts['class']);
        }

        return $this->_cred_rendering->renderField($fieldObj);
    }

}
