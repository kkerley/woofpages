<?php
if (!defined('ABSPATH')) die('-1');

if (!class_exists("WD_ASP_IndexTable_Action")) {
    /**
     * Class WD_ASP_IndexTable_Action
     *
     * Handles index table actions
     *
     * @class         WD_ASP_IndexTable_Action
     * @version       1.0
     * @package       AjaxSearchPro/Classes/Actions
     * @category      Class
     * @author        Ernest Marcinko
     */
    class WD_ASP_IndexTable_Action extends WD_ASP_Action_Abstract {

        public function handle() {}

        public function update( $post_id, $_post, $update ) {

            if ( wp_is_post_revision( $post_id ) )
                return false;

            $it_options = wd_asp()->o['asp_it_options'];

            if ($it_options !== false) {

                /**
                 * In some cases custom fields are not created in time of saving the post.
                 * To solve that, the user has an option to turn off automatic indexing
                 * when the post is created - but not when updated, or when a CRON job is executed.
                 */
                if ( $it_options['it_index_on_save'] == 0 && $update == false )
                    return false;

                $args = array();
                foreach ($it_options as $k => $o) {
                    $args[str_replace('it_', '', $k)] = $o;
                }
                $it_o = new asp_indexTable( $args );

                $post_status = get_post_status( $post_id );

                if ($post_status == 'trash') {
                    $it_o->removeDocument( $post_id, true );
                    return true;
                }

                $it_o->indexDocument( $post_id, true, true );
            }

        }


        public function delete( $post_id ) {
            $it_o = new asp_indexTable();
            $it_o->removeDocument( $post_id, true );
        }

        public function extend() {
            $asp_it_options = get_option('asp_it_options');

            if ($asp_it_options !== false) {
                $args = array();
                foreach ($asp_it_options as $k => $o) {
                    $args[str_replace('it_', '', $k)] = $o;
                }
                $it_obj = new asp_indexTable( $args );
                $res = $it_obj->extendIndex( );
                update_option("asp_it_cron", array(
                    "last_run"  => time(),
                    "result"    => $res
                ));
            }

        }

        public function cron_extend() {
            // Index Table CRON
            if ( !wp_next_scheduled( 'asp_cron_it_extend' ) ) {

                $asp_it_options = get_option('asp_it_options');

                if ($asp_it_options !== false) {
                    if ( w_isset_def($asp_it_options['it_cron_enable'], 0) == 1 )
                        wp_schedule_event( time(), w_isset_def($asp_it_options['it_cron_period'], "hourly"), 'asp_cron_it_extend' );
                }
            }
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