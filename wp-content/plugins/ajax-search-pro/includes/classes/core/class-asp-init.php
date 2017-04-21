<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

/**
 * Class aspInit
 *
 * AJAX SEARCH PRO initializator Class
 */
class WD_ASP_Init {

    /**
     * Core singleton class
     * @var WD_ASP_Init self
     */
    private static $_instance;

    private function __construct() {
        wd_asp()->db = WD_ASP_DBMan::getInstance();
    }

    /**
     * Runs on activation
     */
    public function activate() {

        WD_ASP_DBMan::getInstance()->create();

        $indexObj = new asp_indexTable();
        $indexObj->createIndexTable();

        $this->create_chmod();

        $stored_ver = get_option('asp_version', 0);
        // Was the plugin previously installed, and updated?
        if ($stored_ver != 0 && $stored_ver != ASP_CURR_VER) {
            update_option("asp_recently_updated", 1);
        }

        /**
         * Store the version number after everything is done. This is going to help distinguishing
         * stored asp_version from the ASP_CURR_VER variable. These two are different in cases:
         *  - Uninstalling, installing new versions
         *  - Uploading and overwriting old version with a new one
         */
        update_option('asp_version', ASP_CURR_VER);

        set_transient('asp_just_activated', 1);
    }

    /**
     *  Checks if the user correctly updated the plugin and fixes if not
     */
    public function safety_check() {
        $curr_stored_ver = get_option('asp_version', 0);

        // Run the re-activation actions if this is actually a newer version
        if ($curr_stored_ver != ASP_CURR_VER) {
            $this->activate();
            // Run a backwards compatibility check
            $this->backwards_compatibility_fixes();
            asp_generate_the_css();
            // Take a note on the recent update
            update_option("asp_recently_updated", 1);
        } else {
            // Still, check the folders, they might have been deleted by accident
            $this->create_chmod();

            // Was the plugin just activated, without version change?
            if ( get_transient('asp_just_activated') !== false ) {
                // Run a backwards compatibility check
                $this->backwards_compatibility_fixes();
                asp_generate_the_css();
                delete_transient('asp_just_activated');
            }
        }
    }

    /**
     * Fix known backwards incompatibilities
     * @todo
     */
    public function backwards_compatibility_fixes() {
        /*
         * - Get instances
         * - Check options
         * - Transition to new options based on old ones
         * - Save instances
         * asp_generate_the_css()
         */

        foreach (wd_asp()->instances->get() as $si) {
            $id = $si['id'];
            $sd = $si['data'];

            // ------------------------- 4.9.10 -----------------------------
            // Primary and secondary fields
            $values = array('-1', '0', '1', '2', 'c__f');
            $adv_fields = array(
                'primary_titlefield',
                'secondary_titlefield',
                'primary_descriptionfield',
                'secondary_descriptionfield'
            );
            foreach($adv_fields as $field) {
                // Force string conversion for proper comparision
                if ( !in_array($sd[$field].'', $values) ) {
                    // Custom field value is selected
                    $sd[$field.'_cf'] = $sd[$field];
                    $sd[$field] = 'c__f';
                }
            }

            // Unset some unused search data
            $values = array('magnifierimage_selects', 'settingsimage_selects', 'loadingimage_selects',
                'i_res_magnifierimage_selects', 'i_pagination_arrow_selects', 'keyword_logic_def',
                'user_search_title_field_def', 'frontend_search_settings_position_def', 'term_logic_def',
                'cf_logic_def', 'resultstype_def', 'resultsposition_def', 'box_compact_float_def',
                'box_compact_position_def', 'keyword_suggestion_source_def', 'bpgroupstitle_def', 'bpgroupstitle',
                'settingsimagepos_def', 'blogtitleorderby_def', 'i_ifnoimage_def', 'i_pagination_position_def',
                'weight_def', 'user_search_description_field_def', 'triggeronclick', 'triggeronreturn', 'redirectonclick',
                'redirect_click_to', 'redirect_on_enter', 'redirect_enter_to', 'mob_trigger_on_click');
            foreach ($values as $v) {
                if ( isset($sd[$v]) )
                    unset($sd[$v]);
            }
            // ------------------------- 4.9.10 -----------------------------

            // At the end, update
            wd_asp()->instances->update($id, $sd);
        }
    }

    /**
     * Extra styles if needed..
     */
    public function styles() {
        // Fallback on IE<=8
        if(isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/(?i)msie [6-8]/',$_SERVER['HTTP_USER_AGENT']) ) {
            // Oh, this is IE 8 or below, abort mission
            return;
        }
    }

    /**
     * Prints the scripts
     */
    public function scripts() {

        // Fallback on IE<=8
        if(isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/(?i)msie [6-8]/',$_SERVER['HTTP_USER_AGENT']) ) {
            // Oh, this is IE 8 or below, abort mission
            return;
        }

        $comp_settings = wd_asp()->o['asp_compatibility'];
        $load_in_footer = w_isset_def($comp_settings['load_in_footer'], 1) == 1 ? true : false;
        $css_async_load = w_isset_def($comp_settings['css_async_load'], 0) == 1 ? true : false;
        $media_query = ASP_DEBUG == 1 ? asp_gen_rnd_str() : get_option("asp_media_query", "defn");

        if ($comp_settings !== false && isset($comp_settings['loadpolaroidjs']) && $comp_settings['loadpolaroidjs'] == 0) {
            ;
        } else {
            wp_register_script('wd-asp-photostack', ASP_URL . 'js/nomin/photostack.js', array("jquery"), $media_query, $load_in_footer);
            wp_enqueue_script('wd-asp-photostack');
        }

        $js_source = w_isset_def($comp_settings['js_source'], 'min');
        $load_mcustom = w_isset_def($comp_settings['load_mcustom_js'], "yes") == "yes";
        $load_noui = w_isset_def($comp_settings['load_noui_js'], 1);
        $load_isotope = w_isset_def($comp_settings['load_isotope_js'], 1);
        $load_datepicker = w_isset_def($comp_settings['load_datepicker_js'], 1);
        $minify_string = (($load_noui == 1) ? '-noui' : '') . (($load_isotope == 1) ? '-isotope' : '');

        if (ASP_DEBUG) $js_source = 'nomin';

        if ( $css_async_load ) {
            wp_register_script('wd-asp-async-loader', ASP_URL . 'js/nomin/async.css.js', array("jquery"), $media_query, $load_in_footer);
            wp_enqueue_script('wd-asp-async-loader');
        }

        if ($js_source == 'nomin' || $js_source == 'nomin-scoped') {
            $prereq = "jquery";
            if ($js_source == "nomin-scoped") {
                $prereq = "wd-asp-aspjquery";
                wp_register_script('wd-asp-aspjquery', ASP_URL . 'js/' . $js_source . '/aspjquery.js', array(), $media_query, $load_in_footer);
                wp_enqueue_script('wd-asp-aspjquery');
            }

            wp_register_script('wd-asp-gestures', ASP_URL . 'js/' . $js_source . '/jquery.gestures.js', array($prereq), $media_query, $load_in_footer);
            wp_enqueue_script('wd-asp-gestures');
            if ($load_mcustom) {
                wp_register_script('wd-asp-scroll', ASP_URL . 'js/' . $js_source . '/jquery.mCustomScrollbar.js', array($prereq), $media_query, $load_in_footer);
                wp_enqueue_script('wd-asp-scroll');
            }
            wp_register_script('wd-asp-highlight', ASP_URL . 'js/' . $js_source . '/jquery.highlight.js', array($prereq), $media_query, $load_in_footer);
            wp_enqueue_script('wd-asp-highlight');
            if ($load_noui) {
                wp_register_script('wd-asp-nouislider', ASP_URL . 'js/' . $js_source . '/jquery.nouislider.all.js', array($prereq), $media_query, $load_in_footer);
                wp_enqueue_script('wd-asp-nouislider');
            }
            if ($load_isotope) {
                wp_register_script('wd-asp-rpp-isotope', ASP_URL . 'js/' . $js_source . '/rpp_isotope.js', array($prereq), $media_query, $load_in_footer);
                wp_enqueue_script('wd-asp-rpp-isotope');
            }
            wp_register_script('wd-asp-ajaxsearchpro', ASP_URL . 'js/' . $js_source . '/jquery.ajaxsearchpro.js', array($prereq), $media_query, $load_in_footer);
            wp_enqueue_script('wd-asp-ajaxsearchpro');

            wp_register_script('wd-asp-ajaxsearchpro-widgets', ASP_URL . 'js/' . $js_source . '/asp_widgets.js', array($prereq, "wd-asp-ajaxsearchpro"), $media_query, $load_in_footer);
            wp_enqueue_script('wd-asp-ajaxsearchpro-widgets');

            wp_register_script('wd-asp-ajaxsearchpro-wrapper', ASP_URL . 'js/' . $js_source . '/asp_wrapper.js', array($prereq, "wd-asp-ajaxsearchpro"), $media_query, $load_in_footer);
            wp_enqueue_script('wd-asp-ajaxsearchpro-wrapper');
        } else {
            wp_enqueue_script('jquery');
            wp_register_script('wd-asp-ajaxsearchpro', ASP_URL . "js/" . $js_source . "/jquery.ajaxsearchpro" . $minify_string . ".min.js", array('jquery'), $media_query, $load_in_footer);
            wp_enqueue_script('wd-asp-ajaxsearchpro');
        }

        if ($load_datepicker) {
            wp_enqueue_script('jquery-ui-datepicker');
        }

        $ajax_url = admin_url('admin-ajax.php');
        if (w_isset_def($comp_settings['usecustomajaxhandler'], 0) == 1) {
            $ajax_url = ASP_URL . 'ajax_search.php';
        }

        if (ASP_DEBUG < 1 && strpos(w_isset_def($comp_settings['js_source'], 'min-scoped'), "scoped") !== false) {
            $scope = "aspjQuery";
        } else {
            $scope = "jQuery";
        }

        /**
         * This stays here for a bit, let customers transition smoothly
         *
         * @deprecated since version 4.5.3
         */
        wp_localize_script('wd-asp-ajaxsearchpro', 'ajaxsearchpro', array(
            'ajaxurl' => $ajax_url,
            'backend_ajaxurl' => admin_url('admin-ajax.php'),
            'js_scope' => $scope
        ));

        // The new variable is ASP
        wp_localize_script('wd-asp-ajaxsearchpro', 'ASP', array(
            'ajaxurl' => $ajax_url,
            'backend_ajaxurl' => admin_url('admin-ajax.php'),
            'js_scope' => $scope,
            'asp_url' => ASP_URL,
            'upload_url' => wd_asp()->upload_url,
            'detect_ajax' => w_isset_def($comp_settings['detect_ajax'], 0),
            'media_query' => get_option("asp_media_query", "defn"),
            'version' => ASP_CURR_VER,
            'scrollbar' => $load_mcustom,
            'css_loaded' => !$css_async_load
        ));

    }

    /**
     *  Create and chmod the upload directory, and adds an index.html file to it
     */
    public function create_chmod() {
        global $wp_filesystem;

        if ( !$wp_filesystem->is_dir( wd_asp()->upload_path ) ) {
            if ( !$wp_filesystem->mkdir( wd_asp()->upload_path, 0777 ) ) {
                return false;
            }
        }

        if ( !$wp_filesystem->is_file( wd_asp()->upload_path . "index.html" ) )
            asp_put_file("index.html", "");

        return true;
    }


    /**
     *  If anything we need in the footer
     */
    public function footer() {

    }

    /**
     * Get the instane of asp_indexTable
     *
     * @return self
     */
    public static function getInstance() {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
}