<?php
if (!defined('ABSPATH')) die('-1');

class AjaxSearchProWidget extends WP_Widget {
    function __construct() {
        $widget_ops = array( 'classname' => 'AjaxSearchProWidget', 'description' => 'Displays an Ajax Search Pro!' );
        parent::__construct( 'AjaxSearchProWidget', 'Ajax Search Pro', $widget_ops );
    }

    function form( $instance ) {
        global $wpdb;
        if ( isset( $wpdb->base_prefix ) ) {
            $_prefix = $wpdb->base_prefix;
        } else {
            $_prefix = $wpdb->prefix;
        }
        $searches = $wpdb->get_results( "SELECT * FROM " . $_prefix . "ajaxsearchpro", ARRAY_A );
        $instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
        $title    = $instance['title'];
        if ( isset( $instance['searchid'] ) ) {
            $searchid = $instance['searchid'];
        } else {
            $searchid = '';
        }
        ?>
        <p><label for="<?php echo $this->get_field_id( 'title' ); ?>">Title: <input class="widefat"
                                                                                    id="<?php echo $this->get_field_id( 'title' ); ?>"
                                                                                    name="<?php echo $this->get_field_name( 'title' ); ?>"
                                                                                    type="text"
                                                                                    value="<?php echo esc_attr( $title ); ?>"/></label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'searchid' ); ?>">Select the search form: </label>
            <select class="widefat" name="<?php echo $this->get_field_name( 'searchid' ); ?>"
                    id="<?php echo $this->get_field_id( 'searchid' ); ?>">
                <?php
                if ( is_array( $searches ) ) {
                    foreach ( $searches as $search ) {
                        echo "<option value='" . $search['id'] . "' " . ( ( esc_attr( $searchid ) == $search['id'] ) ? "selected='selected'" : "''" ) . ">" . $search['name'] . "</option>";
                    }
                }
                ?>
            </select>
        </p>
    <?php
    }

    function update( $new_instance, $old_instance ) {
        $instance             = $old_instance;
        $instance['title']    = $new_instance['title'];
        $instance['searchid'] = $new_instance['searchid'];

        return $instance;
    }

    function widget( $args, $instance ) {
        extract( $args, EXTR_SKIP );
        echo $before_widget;
        $title    = empty( $instance['title'] ) ? ' ' : apply_filters( 'widget_title', $instance['title'] );
        $searchid = empty( $instance['searchid'] ) ? '' : $instance['searchid'];
        if ( ! empty( $title ) ) {
            echo $before_title . $title . $after_title;
        };
        // WIDGET CODE GOES HERE
        if ( ! empty( $searchid ) ) {
            echo do_shortcode( "[wpdreams_ajaxsearchpro id=" . $searchid . "]" );
        }
        echo $after_widget;
    }
}