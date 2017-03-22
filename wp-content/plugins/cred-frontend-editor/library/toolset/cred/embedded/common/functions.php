<?php

if (!function_exists('cred_sanitize_array')) {

    /**
     * array recursive sanitize_text_field
     * @param mixed $array
     * @return mixed
     */
    function cred_sanitize_array(&$array) {
        if (is_array($array)) {
            foreach ($array as &$value) {
                if (is_string($value)) {
                    $value = sanitize_text_field($value);
                } else {
                    cred_sanitize_array($value);
                }
            }
        }
        return $array;
    }

}

if (!function_exists('cred_is_ajax_call')) {

    /**
     * cred_is_ajax_call
     * @return boolean
     */
    function cred_is_ajax_call() {
        return ((defined('DOING_AJAX') && DOING_AJAX) || !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
    }

}

if (!function_exists('cred__return_zero')) {

    /**
     * cred__return_zero
     * @return int
     */
    function cred__return_zero() {
        return 0;
    }

}

if (!function_exists('cred__create_auto_draft')) {

    /**
     * create a auto draft post using wp_insert_post
     * @param string $post_title
     * @param string $post_type
     * @param int $user_id
     * @return int
     */
    function cred__create_auto_draft($post_title, $post_type, $user_id = "") {
        $mypost = get_default_post_to_edit($post_type);
        $mypost->post_title = $post_title;
        $mypost->content = '';
        $mypost->post_status = 'auto-draft';
        if (!empty($user_id)) {
            $mypost->post_author = $user_id;
        }
        $mypost->post_category = '';
        $mypost_id = wp_insert_post($mypost);
        return $mypost_id;
    }

}

if (!function_exists('cred__parent_sort')) {

    /**
     * cred__parent_sort sort fields related to parents
     * @param array $fields
     * @param array $result
     * @param int $parent
     * @param int $depth
     * @return array
     */
    function cred__parent_sort(array $fields, array &$result = array(), $parent = 0, $depth = 0) {
        foreach ($fields as $key => $field) {
            if ($field['parent'] == $parent) {
                $field['depth'] = $depth;
                array_push($result, $field);
                unset($fields[$key]);
                cred__parent_sort($fields, $result, $field['term_id'], $depth + 1);
            }
        }
        return $result;
    }

}

if (!function_exists('is_cred_embedded')) {

    /**
     * is_cred_embedded
     * @deprecated since version 1.9
     */
    function is_cred_embedded() {
        return CRED_CRED::is_embedded();
    }

}

if (function_exists('add_action')) {

    add_action('init', 'cred_common_path');
    
    /**
     * cred_common_path
     */
    function cred_common_path() {
        if (!defined('WPTOOLSET_FORMS_VERSION')) {
            $toolset_common_bootstrap = Toolset_Common_Bootstrap::getInstance();
            $toolset_common_sections = array(
                'toolset_forms'
            );
            $toolset_common_bootstrap->load_sections($toolset_common_sections);
        }
    }

}

if (!function_exists('cred_log')) {

    /**
     * custom cred log function
     * @param mixed $message
     * @param string $file
     * @param string $type
     * @param int $level
     * @return boolean
     */
    function cred_log($message, $file = null, $type = null, $level = 1) {
        if (!defined("CRED_DEBUG") || (defined("CRED_DEBUG") && !CRED_DEBUG)) {
            return;
        }
        // debug levels
        $dlevels = array(
            'default' => true, //defined('CRED_DEBUG') && CRED_DEBUG,
            'access' => false, //defined('CRED_DEBUG_ACCESS') && CRED_DEBUG_ACCESS
        );

        // check if we need to log..
        if (!$dlevels['default'])
            return false;
        if ($type == null)
            $type = 'default';
        if (!isset($dlevels[$type]) || !$dlevels[$type])
            return false;

        // full path to log file
        if ($file == null) {
            $file = 'debug.log';
        }

        if ('access.log' == $file && !$dlevels['access'])
            return;

        $file = CRED_LOGS_PATH . DIRECTORY_SEPARATOR . $file;

        /* backtrace */
        $bTrace = debug_backtrace(); // assoc array

        /* Build the string containing the complete log line. */
        $line = PHP_EOL . sprintf('[%s, <%s>, (%d)]==> %s', date("Y/m/d h:i:s" /* ,time() */), basename($bTrace[0]['file']), $bTrace[0]['line'], print_r($message, true));

        if ($level > 1) {
            $i = 0;
            $line.=PHP_EOL . sprintf('Call Stack : ');
            while (++$i < $level && isset($bTrace[$i])) {
                $line.=PHP_EOL . sprintf("\tfile: %s, function: %s, line: %d" . PHP_EOL . "\targs : %s", isset($bTrace[$i]['file']) ? basename($bTrace[$i]['file']) : '(same as previous)', isset($bTrace[$i]['function']) ? $bTrace[$i]['function'] : '(anonymous)', isset($bTrace[$i]['line']) ? $bTrace[$i]['line'] : 'UNKNOWN', print_r($bTrace[$i]['args'], true));
            }
            $line.=PHP_EOL . sprintf('End Call Stack') . PHP_EOL;
        }
        // log to file
        file_put_contents($file, $line, FILE_APPEND);

        return true;
    }

}

/**
 * cred_delete_post_link
 * @param int $post_id
 * @param string $text
 * @param string $action
 * @param string $class
 * @param string $style
 * @param string $message
 * @param string $message_after
 * @param string $message_show
 * @param boolean $redirect
 * @param boolean $return
 * @return string
 */
function cred_delete_post_link($post_id = false, $text = '', $action = '', $class = '', $style = '', $message = '', $message_after = '', $message_show = 1, $redirect = false, $return = false) {
    $output = CRED_Helper::cred_delete_post_link($post_id, $text, $action, $class, $style, $message, $message_after, $message_show, $redirect);
    if ($return)
        return $output;
    echo $output;
}

function cred_edit_post_link($form, $post_id = false, $text = '', $class = '', $style = '', $target = '', $attributes = '', $return = false) {
    $output = CRED_Helper::cred_edit_post_link($form, $post_id, $text, $class, $style, $target, $attributes);
    if ($return)
        return $output;
    echo $output;
}

function cred_form($form, $post_id = false, $return = false) {
    $output = CRED_Helper::cred_form($form, $post_id);
    if ($return)
        return $output;
    echo $output;
}

function cred_user_form($form, $user_id = false, $return = false) {
    $output = CRED_Helper::cred_user_form($form, $user_id);
    if ($return)
        return $output;
    echo $output;
}

/**
 * has_cred_form
 * @return boolean
 */
function has_cred_form() {
    if (!class_exists('CRED_Form_Builder', false))
        return false;
    return CRED_Form_Builder::has_form();
}

/**
 * public API to import from XML string
 *
 * @param string $xml
 * @param array $options
 *     'overwrite_forms'=>(0|1)             // Overwrite existing forms
 *     'overwrite_settings'=>(0|1)          // Import and Overwrite CRED Settings
 *     'overwrite_custom_fields'=>(0|1)     // Import and Overwrite CRED Custom Fields
 *     'force_overwrite_post_name'=>array   // Skip all, overwrite only forms from array
 *     'force_skip_post_name'=>array        // Skip forms from array
 *     'force_duplicate_post_name'=>array   // Skip all, duplicate only from array
 * @return array
 *     'settings'=>(int),
 *     'custom_fields'=>(int),
 *     'updated'=>(int),
 *     'new'=>(int),
 *     'failed'=>(int),
 *     'errors'=>array()
 *
 * example:
 *   $result = cred_import_xml_from_string($import_xml_string, array('overwrite_forms'=>1, 'overwrite_settings'=>0, 'overwrite_custom_fields'=>1));
 * note:
 * force_duplicate_post_name, force_skip_post_name, force_overwrite_post_name - can work together
 */
function cred_import_xml_from_string($xml, $options = array()) {
    CRED_Loader::load('CLASS/XML_Processor');
    $result = CRED_XML_Processor::importFromXMLString($xml, $options);
    return $result;
}

/**
 * cred_user_import_xml_from_string
 * @param string $xml
 * @param array $options
 * @return string
 */
function cred_user_import_xml_from_string($xml, $options = array()) {
    CRED_Loader::load('CLASS/XML_Processor');
    $result = CRED_XML_Processor::importUsersFromXMLString($xml, $options);
    return $result;
}

/*
  public API to export to XML string
 */

function cred_export_to_xml_string($forms) {
    CRED_Loader::load('CLASS/XML_Processor');
    $xmlstring = CRED_XML_Processor::exportToXMLString($forms);
    return $xmlstring;
}

/**
 * cred_translate
 * @param string $name
 * @param string $string
 * @param string $context
 * @return string
 */
function cred_translate($name, $string, $context = 'CRED_CRED') {
    if (!function_exists('icl_t'))
        return $string;

    //cred_log("cred_translate");
    //cred_log(array($name, $string, $context));
//    cred_log("########################## cred_translate ############################");
//    cred_log($context);
    if (strpos($context, 'cred-form-') !== false) {
        $tmp = explode("-", $context);
//        cred_log($tmp);
        $form_id = $tmp[count($tmp) - 1];
//        cred_log($form_id);        
        $is_user_form = get_post_type($form_id) == CRED_USER_FORMS_CUSTOM_POST_NAME;
        if ($is_user_form) {
            $context = str_replace('cred-form-', 'cred-user-form-', $context);
        }
//        cred_log("new context");
//        cred_log($context);
    }

    $has_translation = null;
    //cred_log("icl_t");
    //cred_log(array($context, $name, stripslashes($string)));
    $translation = icl_t($context, $name, stripslashes($string), $has_translation);
    if ($has_translation) {
        return $translation;
    } else {
        return $string;
    }
}

/**
 * Registers WPML translation string.
 *
 * @param string $context
 * @param string $name
 * @param string $value
 */
function cred_translate_register_string($context, $name, $value, $allow_empty_value = false) {
    //cred_log("cred_translate_register_string");
    cred_log(array($context, $name, $value));

    //cred_log("########################## cred_translate_register_string ############################");
    //cred_log($context);
    if (strpos($context, 'cred-form-') !== false) {
        $tmp = explode("-", $context);
//        cred_log($tmp);
        $form_id = $tmp[count($tmp) - 1];
//        cred_log($form_id);
        $is_user_form = get_post_type($form_id) == CRED_USER_FORMS_CUSTOM_POST_NAME;
        if ($is_user_form) {
            $context = str_replace('cred-form-', 'cred-user-form-', $context);
        }
        //cred_log("> new context");
        //cred_log($context);
    }

    if (function_exists('icl_register_string')) {
        //cred_log("icl_register_string");
        //cred_log(array($context, $name, stripslashes($value)));
        icl_register_string($context, $name, stripslashes($value), $allow_empty_value);
    }
}

// stub wpml=string shortcode
if (!function_exists('cred_stub_wpml_string_shortcode')) {

    function cred_stub_wpml_string_shortcode($atts, $content = '') {
        // return un-processed.
        return do_shortcode($content);
    }

}

/**
 * Filter the_content tag 
 * Added support for resolving third party shortcodes in cred shortcodes
 */
function cred_do_shortcode($content) {
    $shortcodeParser = CRED_Loader::get('CLASS/Shortcode_Parser');
    $content = $shortcodeParser->parse_content_shortcodes($content);

    return $content;
}

function cred_disable_shortcodes() {
    global $shortcode_tags;

    $shortcode_back = $shortcode_tags;
    $shortcode_tags = array();
    return($shortcode_back);
}

function cred_re_enable_shortcodes($shortcode_back) {
    global $shortcode_tags;

    $shortcode_tags = $shortcode_back;
}

function cred_disable_filters_for($hook) {
    if (has_action($hook)) {
        remove_all_actions($hook);
    }

    if (has_filter($hook)) {
        remove_all_filters($hook);
    }
}

function cred_re_enable_filters_for($hook, $back) {
    global $wp_filter;
    $wp_filter[$hook] = $back;
}
