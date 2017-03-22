<?php

class CRED_Form_Builder_Base {

    var $_current_post = 0;
    var $_post_to_create;

    public function __construct() {
        cred_log("__construct");
        // parse cred form output
        add_action('wp_loaded', array($this, 'init'), 10);
        // load front end form assets
        add_action('wp_head', array(__CLASS__, 'loadFrontendAssets'));
        add_action('wp_footer', array(__CLASS__, 'unloadFrontendAssets'));
    }

    /**
     * init
     */
    public function init() {
        cred_log("init");

        if (!is_admin()) {
            if (array_key_exists(CRED_StaticClass::PREFIX . 'form_id', $_POST) &&
                    array_key_exists(CRED_StaticClass::PREFIX . 'form_count', $_POST)) {
                $_form_id = intval($_POST[CRED_StaticClass::PREFIX . 'form_id']);
                $_form_count = intval($_POST[CRED_StaticClass::PREFIX . 'form_count']);
                $_post_id = (array_key_exists(CRED_StaticClass::PREFIX . 'post_id', $_POST)) ? intval($_POST[CRED_StaticClass::PREFIX . 'post_id']) : false;
                $_preview = (array_key_exists(CRED_StaticClass::PREFIX . 'form_preview_content', $_POST)) ? true : false;

                return $this->get_form($_form_id, $_post_id, $_form_count, $_preview);
            }
        }
    }

    /**
     * get_html_form
     * @param type $_form_id
     * @return type
     */
//    public function get_html_form($_form_id, $_post_id = false, $_form_count = 0, $_preview = false) {
//        cred_log("get_html_form");
//        cred_log(array($_form_id, $_post_id, $_form_count, $_preview));
//
//        global $post;
//        CRED_StaticClass::$_cred_container_id = (isset($_POST[CRED_StaticClass::PREFIX . 'cred_container_id'])) ? intval($_POST[CRED_StaticClass::PREFIX . 'cred_container_id']) : (isset($post) ? $post->ID : "");
//
//        //Security Check
//        if (isset(CRED_StaticClass::$_cred_container_id) && !empty(CRED_StaticClass::$_cred_container_id)) {
//            if (!is_numeric(CRED_StaticClass::$_cred_container_id))
//                wp_die('Invalid data');
//        }
//
//        return $this->get_form($_form_id, $_post_id, $_form_count, $_preview);
//    }

    /**
     * get_form_by_type
     *
     * @param type $_type_form
     *
     * @return \CRED_Form_Post
     */
    public function get_form($_form_id, $_post_id = false, $_form_count = 0, $_preview = false) {
        cred_log("get_form");
        cred_log(array($_form_id, $_post_id, $_form_count, $_preview));

        global $post;
        CRED_StaticClass::$_cred_container_id = (isset($_POST[CRED_StaticClass::PREFIX . 'cred_container_id'])) ? intval($_POST[CRED_StaticClass::PREFIX . 'cred_container_id']) : (isset($post) ? $post->ID : "");

        //Security Check
        if (isset(CRED_StaticClass::$_cred_container_id) && !empty(CRED_StaticClass::$_cred_container_id)) {
            if (!is_numeric(CRED_StaticClass::$_cred_container_id))
                wp_die('Invalid data');
        }

        $_type_form = get_post_type($_form_id);
        cred_log($_type_form);
        if ($_type_form == CRED_USER_FORMS_CUSTOM_POST_NAME) {
            $form = new CRED_Form_User($_form_id, $_post_id, $_form_count, $_preview);
        } else {
            $form = new CRED_Form_Post($_form_id, $_post_id, $_form_count, $_preview);
        }

        if (isset($this->_post_id))
            $parent_post = get_post($this->_post_id);
        if (
        //TODO: Check this
        /* CRED_StaticClass::$_staticGlobal['CACHE'][$_form_id . '_' . $_form_count]['hide_comments'] || */
                (isset($parent_post) && $parent_post->comment_status == 'closed')
        )
            CRED_Form_Builder_Helper::hideComments();

        CRED_StaticClass::initVars();
        if ($_form_count != 0) {
            ++CRED_StaticClass::$_staticGlobal['COUNT'];
            cred_log(CRED_StaticClass::$_staticGlobal['COUNT']);
        }

        $output = $form->print_form();
        if (!is_wp_error($output)) {
            return $output;
        }
    }

    // load frontend assets on init
    public static function loadFrontendAssets() {
        
    }

    // unload frontend assets if no form rendered on page
    public static function unloadFrontendAssets() {
        //Print custom js/css on front-end	
        $custom_js_cache = wp_cache_get('cred_custom_js_cache');
        if (false !== $custom_js_cache) {
            echo "\n<script type='text/javascript' class='custom-js'>\n";
            echo html_entity_decode($custom_js_cache, ENT_QUOTES) . "\n";
            echo "</script>\n";
        }

        $custom_css_cache = wp_cache_get('cred_custom_css_cache');
        if (false !== $custom_css_cache) {
            echo "\n<style type='text/css'>\n";
            echo $custom_css_cache . "\n";
            echo "</style>\n";
        }
    }

}
