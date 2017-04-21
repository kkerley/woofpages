<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

if (!class_exists("asp_statistics")) {
    /**
     * Class asp_statistics
     *
     * Statistics controller class
     */
    class asp_statistics {
        static function addKeyword($id, $s) {
            global $wpdb;

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            $in = $wpdb->query(
                $wpdb->prepare(
                    "UPDATE " . wd_asp()->db->table('stats') . " SET num=num+1, last_date=%d WHERE (keyword='%s' AND search_id=%d)",
                    time(),
                    strip_tags($s),
                    $id
                )
            );
            if ($in == false) {
                return $wpdb->query(
                    $wpdb->prepare(
                        "INSERT INTO " . wd_asp()->db->table('stats') . " (search_id, keyword, num, last_date) VALUES (%d, '%s', 1, %d)",
                        $id,
                        strip_tags($s),
                        time()
                    )
                );
            }
            return $in;
        }

        static function getTop($count, $id="") {
            global $wpdb;

            $where = "";
            if ( $id != "" )
                $where = " WHERE search_id=" . ( $id + 0 );

            return $wpdb->get_results(
                "SELECT * FROM " . wd_asp()->db->table('stats') . " " . $where . " ORDER BY num DESC LIMIT ".($count + 0)
                ,ARRAY_A
            );
        }

        static function getLast($count, $id="") {
            global $wpdb;

            $where = "";
            if ( $id != "" )
                $where = " WHERE search_id=" . ( $id + 0 );

            return $wpdb->get_results(
                "SELECT * FROM " . wd_asp()->db->table('stats') . " " . $where . " ORDER BY last_date DESC LIMIT ".($count + 0)
                ,ARRAY_A
            );
        }

        static function clearAll() {
            global $wpdb;

            return $wpdb->query("DELETE FROM " . wd_asp()->db->table('stats'));
        }

        static function deleteKw($id) {
            global $wpdb;

            return $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM " . wd_asp()->db->table('stats') . " WHERE id=%d"
                    , ($id+0)
                )
            ) ;
        }
    }
}