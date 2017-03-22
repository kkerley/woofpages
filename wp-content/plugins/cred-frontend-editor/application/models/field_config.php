<?php


/**
 * Holds data describing a field definition.
 *
 * Used only in CRED_Form_Rendering and not to be reused.
 *
 * @deprecated Check if you can use Toolset_Field_Definition instead.
 * @since unknown
 */
class CRED_Field_Config {

    private $id;
    private $name = "cred[post_title]";
    private $value;
    private $type = 'textfield';
    private $title = 'Post title';
    private $repetitive = false;
    private $display = '';
    private $description = '';
    private $config = array();
    private $options = array();
    private $default_value = '';
    private $validation = array();
    private $attr;
    private $add_time = false;
    private $form_settings;

    public function __construct() {

    }

    public function getForm_settings() {
        return $this->form_settings;
    }

    public function setForm_settings($form_settings) {
        $this->form_settings = $form_settings;
    }

    public function setRepetitive($repetitive) {
        $this->repetitive = $repetitive;
    }

    public function isRepetitive() {
        return $this->repetitive;
    }

    public function set_add_time($addtime) {
        $this->add_time = $addtime;
    }

    public function setAttr($attr) {
        $this->attr = $attr;
    }

    public function getAttr() {
        return $this->attr;
    }

    public function setDefaultValue($value) {
        $this->default_value = $value;
    }

    public function setOptions($name, $type, $values, $attrs) {
        $arr = array();
        switch ($type) {
            case 'checkbox':
                $arr = $attrs;
                break;
            case 'checkboxes':
                $actual_titles = isset($attrs['actual_titles']) ? $attrs['actual_titles'] : array();
                foreach ($actual_titles as $refvalue => $title) {
                    $_value = $attrs['actual_values'][$refvalue];
                    
					// Look for all the $refvalue values in $attrs['default']
					// Note that $attrs['default'] holds "other" data like whether a field is generic, among other things, 
					// in a mixed array with some associative and some numeric keys, 
					// so just finding the value is not enough: double check that it has a numeric index, 
					// as actual default values to check are added like such.
					// See CRED_Translate_Field_Factory::cred_translate_field
					$arr[$refvalue] = array('value' => $refvalue, 'title' => $title, 'name' => $name, 'data-value' => $_value);
                    $checked_candidate = array_keys( $attrs['default'], $refvalue );
					$checked_candidate = array_filter( $checked_candidate, 'is_numeric' );
					if ( ! empty( $checked_candidate ) ) {
						$arr[$refvalue]['checked'] = true;
					}
                }
                break;
            case 'select':
                $values = isset($attrs['options']) ? $attrs['options'] : array();
                foreach ($values as $refvalue => $title) {
                    $arr[$refvalue] = array(
                        'value' => $refvalue,
                        'title' => $title,
                        'types-value' => $attrs['actual_options'][$refvalue],
                    );
                }
                break;
            case 'radios':
                $actual_titles = isset($attrs['actual_titles']) ? $attrs['actual_titles'] : array();
                foreach ($actual_titles as $refvalue => $title) {
                    $arr[$refvalue] = array(
                        'value' => $attrs['actual_values'][$refvalue],
                        'title' => $title,
                        'checked' => false,
                        'name' => $refvalue,
                        'types-value' => $refvalue,
                    );
                }
                break;
            case 'taxonomy':
                $arr['terms'] = wp_get_post_terms(CRED_Form_Rendering::$current_postid, $name, array("fields" => "all"));
                break;
            default:
                return;
                break;
        }
        $this->options = $arr;
    }

    /**
     * setValueAndDefaultValue set value and default_value
     * @param type $field
     * @param type $_curr_value
     * @param type $_default_value
     */
    function setValueAndDefaultValue($field, &$_curr_value, &$_default_value) {
        switch ($field['type']) {
            case 'date':
                if (isset($field['attr']['repetitive']) && $field['attr']['repetitive'] == 1) {
                    $_default_value = $field['value'];
                    $_curr_value = $_default_value;
                } else {
                    $this->set_add_time(false);
                    if (isset($field['data']['date_and_time']) && (isset($field['data']['date']) && 'and_time' != $field['data']['date'])) {
                        $this->set_add_time(true);
                    }
                    if (isset($field['value']['timestamp'])) {
                        $_default_value = array('timestamp' => $field['value']['timestamp']);
                    } else {
                        //In Edit + Ajax call the object contains array of 5 elements timestamps only 1 and 5 (starting from 0) contains number timestamp
                        if (isset($field['value'][1]['timestamp']) &&
                                is_numeric($field['value'][1]['timestamp']))
                            $_default_value = array('timestamp' => $field['value'][1]['timestamp']);
                    }
                    $_curr_value = $_default_value;
                }
                break;
            case 'select':
                if (isset($field['attr']['multiple'])) {
                    $_default_value = $field['value'];
                } else {
                    if (isset($field['attr']['actual_value'])) {
                        //This value is not array if from parent
                        if (is_array($field['attr']['actual_value']))
                            $_default_value = isset($field['attr']['actual_value'][0]) ? $field['attr']['actual_value'][0] : null;
                        else
                            $_default_value = isset($field['attr']['actual_value']) ? $field['attr']['actual_value'] : null;
                    } else {
                        $_default_value = null;
                    }
                }
                $_curr_value = $_default_value;
                break;
            case 'checkboxes':
                $def = array();
                if (!empty($field['value']))
                    foreach ($field['value'] as $n => $value) {
                        $def[$value] = $value;
                    }
                $_default_value = $def;
                $_curr_value = $_default_value;
                break;
            case 'checkbox':
                $_default_value = isset($field['data']['checked']) ? true : false;
                $_curr_value = $field['value'];
                break;
            case 'radios':
                $_default_value = $field['attr']['default'];
                $_curr_value = $_default_value;
                break;
            case 'numeric':
                $_default_value = isset($field['data']['user_default_value']) ? $field['data']['user_default_value'] : "";
                $_curr_value = $field['value'] != "" ? $field['value'] : $_default_value;
                break;
            default:
                $_default_value = $field['value'];
                $_curr_value = $_default_value;
                break;
        }
    }

    public function createConfig() {
        $base_name = "cred";
        $this->config = array(
            'id' => $this->getId(),
            'type' => $this->getType(),
            'title' => $this->getTitle(),
            'options' => $this->getOptions(),
            'default_value' => $this->getDefaultValue(),
            'description' => $this->getDescription(),
            'repetitive' => $this->isRepetitive(),
            /* 'name' => $base_name."[".$this->getType()."]", */
            'name' => $this->getName(),
            'value' => $this->getValue(),
            'add_time' => $this->getAddTime(),
            'validation' => array(),
            'display' => $this->getDisplay(),
            'attribute' => $this->getAttr(),
            'form_settings' => $this->getForm_settings()
        );
        return $this->config;
    }

    public function getAddTime() {
        return $this->add_time;
    }

    public function getType() {
        return $this->type;
    }

    public function setType($type) {
        $this->type = $type;
    }

    public function getOptions() {
        return $this->options;
    }

    public function getDefaultValue() {
        return $this->default_value;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getDisplay() {
        return $this->display;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function getDescription() {
        return $this->description;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getValue() {
        return $this->value;
    }

    public function setValue($value) {
        $this->value = $value;
    }

    public function getValidation() {
        return !empty($this->validation) ? $this->validation : array();
    }

    public function setValidation($validation) {
        $this->validation = $validation;
    }

    public function getConfig() {
        return $this->config;
    }

    public function setConfig($config) {
        $this->config = $config;
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setDisplay($display) {
        $this->display = $display;
    }

}
