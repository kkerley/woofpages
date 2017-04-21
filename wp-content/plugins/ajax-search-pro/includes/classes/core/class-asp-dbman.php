<?php
if (!defined('ABSPATH')) die('-1');

if (!class_exists("WD_ASP_DBMan")) {
    /**
     * Class WD_ASP_DBMan
     *
     * Manager of main database related operations
     *
     * @class         WD_ASP_Manager
     * @version       1.0
     * @package       AjaxSearchPro/Classes/Core
     * @category      Class
     * @author        Ernest Marcinko
     */
    class WD_ASP_DBMan {

        /**
         * All the table slug => name combinations used
         *
         * @since 1.0
         * @var array
         */
        private $tables = array(
            "main" => "ajaxsearchpro",
            "stats" => "ajaxsearchpro_statistics",
            "priorities" => "ajaxsearchpro_priorities",
            "synonyms" => "asp_synonyms",
            "index" => "asp_index"
        );

        private static $_instance;

        private function __construct() {
            global $wpdb;

            if (isset($wpdb->base_prefix)) {
                wd_asp()->_prefix = $wpdb->base_prefix;
            } else {
                wd_asp()->_prefix = $wpdb->prefix;
            }

            foreach ($this->tables as $slug => $table)
                $this->tables[$slug] = wd_asp()->_prefix . $table;

            // Push the correct table names to the globals back
            $this->tables = (object) $this->tables;
            wd_asp()->tables = $this->tables;
        }

        function create() {
            global $wpdb;
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

            $charset_collate_bin_column = '';
            $charset_collate = '';

            if (!empty($wpdb->charset)) {
                $charset_collate_bin_column = "CHARACTER SET $wpdb->charset";
                $charset_collate = "DEFAULT $charset_collate_bin_column";
            }
            if (strpos($wpdb->collate, "_") > 0) {
                $charset_collate_bin_column .= " COLLATE " . substr($wpdb->collate, 0, strpos($wpdb->collate, '_')) . "_bin";
                $charset_collate .= " COLLATE $wpdb->collate";
            } else {
                if ($wpdb->collate == '' && $wpdb->charset == "utf8") {
                    $charset_collate_bin_column .= " COLLATE utf8_bin";
                }
            }

            $table_name = $this->table("main");
            $query = "
            CREATE TABLE IF NOT EXISTS `$table_name` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `name` text NOT NULL,
              `data` mediumtext NOT NULL,
              PRIMARY KEY (`id`)
            ) DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
          ";
            dbDelta($query);
            $wpdb->query($query);

            // 4.6.1+ change the data row to medium text
            $query = "ALTER TABLE `$table_name` MODIFY `data` mediumtext";
            dbDelta($query);
            $wpdb->query($query);

            $table_name = $this->table("stats");
            $query = "
            CREATE TABLE IF NOT EXISTS `$table_name` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `search_id` int(11) NOT NULL,
              `keyword` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
              `num` int(11) NOT NULL,
              `last_date` int(11) NOT NULL,
              PRIMARY KEY (`id`)
            ) DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
          ";
            dbDelta($query);
            $wpdb->query($query);

            // ------------- SYNONYMS DB ---------------------------
            /*$table_name = $this->table("synonyms");
            $query = "
            CREATE TABLE IF NOT EXISTS `$table_name` (
              `search_id` int(11) NOT NULL,
              `keyword` varchar(40) NOT NULL DEFAULT '0',
              `synonyms` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
              `lang` varchar(20) NOT NULL DEFAULT '0',
              PRIMARY KEY (`search_id`, `keyword`, `lang`)
            ) DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
          ";*/
            // TODO implement synonyms
            //dbDelta($query);
            //$wpdb->query($query);
            // -----------------------------------------------------

            $table_name = $this->table("priorities");
            $query = "
            CREATE TABLE IF NOT EXISTS `$table_name` (
              `post_id` int(11) NOT NULL,
              `blog_id` int(11) NOT NULL,
              `priority` int(11) NOT NULL,
              PRIMARY KEY (`post_id`, `blog_id`)
            ) DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
          ";
            dbDelta($query);
            $wpdb->query($query);

            $query = "SHOW INDEX FROM `$table_name` WHERE KEY_NAME = 'post_blog_id'";
            $index_exists = $wpdb->query($query);
            if ($index_exists == 0) {
                $query = "ALTER TABLE `$table_name` ADD INDEX `post_blog_id` (`post_id`, `blog_id`);";
                $wpdb->query($query);
            }
        }


        /**
         * Return the table name by table slug
         *
         * @param $table_slug
         * @return string
         */
        function table($table_slug) {
            return $this->tables->{$table_slug};
        }

        // ------------------------------------------------------------
        //   ---------------- SINGLETON SPECIFIC --------------------
        // ------------------------------------------------------------

        /**
         * Get the instane of WD_ASP_Manager
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
}