<?php
if (!defined('ABSPATH')) die('-1');

if (!class_exists("WD_ASP_Autocomplete_Handler")) {
    /**
     * Class WD_ASP_Autocomplete_Handler
     *
     * This is the ajax autocomplete handler class
     *
     * @class         WD_ASP_Autocomplete_Handler
     * @version       1.0
     * @package       AjaxSearchPro/Classes/Ajax
     * @category      Class
     * @author        Ernest Marcinko
     */
    class WD_ASP_Autocomplete_Handler extends WD_ASP_Handler_Abstract {

        /**
         * Handles autocomplete requests
         */
        public function handle() {

            // DO NOT TRIM! It will give incorrect results :)
            $s = preg_replace('/\s+/', ' ', $_POST['sauto']);

            do_action('asp_before_autocomplete', $s);

            if ( empty($_POST['asid']) ) return "";

            $search = wd_asp()->instances->get( $_POST['asid'] + 0 );

            if ( empty($search['data']) )
                return false;

            $sd = &$search['data'];

            $keyword = '';
            $types = array();

            if ($sd['searchinposts'] == 1)
                $types[] = "post";
            if ($sd['searchinpages'] == 1)
                $types[] = "page";
            if (isset($sd['selected-customtypes']) && count($sd['selected-customtypes']) > 0)
                $types = array_merge($types, $sd['selected-customtypes']);

            foreach (w_isset_def($sd['selected-autocomplete_source'], array('google')) as $source) {

                $taxonomy = "";
                // Check if this is a taxonomy
                if (strpos($source, 'xtax_') !== false) {
                    $taxonomy = str_replace('xtax_', '', $source);
                    $source = "terms";
                }

                $t = new  wpd_keywordSuggest($source, array(
                    'maxCount' => 10,
                    'maxCharsPerWord' => $sd['autocomplete_length'],
                    'postTypes' => $types,
                    'lang' => $sd['keywordsuggestionslang'],
                    'overrideUrl' => '',
                    'taxonomy' => $taxonomy,
                    'match_start' => true,
                    'api_key' => $sd['autoc_google_places_api']
                ));

                $res = $t->getKeywords($s);
                if (isset($res[0]) && $keyword = $res[0])
                    break;
            }

            do_action('asp_after_autocomplete', $s, $keyword);
            print $keyword;
            die();

        }

        // ------------------------------------------------------------
        //   ---------------- SINGLETON SPECIFIC --------------------
        // ------------------------------------------------------------
        public static function getInstance() {
            if ( ! ( self::$_instance instanceof self ) ) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }
    }
}