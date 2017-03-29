<?php

/**
 * Author: Hoang Ngo
 */
class WD_Post_Audit extends WD_Event_Abstract {
	protected $type = 'content';

	/**
	 * we will add a hook, for updated event, and cache that event cntent
	 * later we weill use the hook save post, to determine this is insert new post
	 * or update
	 * the cache will be array of variuos post, as we dont want data be excluded
	 * this way we can get more control
	 */

	public function __construct() {
		$this->add_action( 'post_updated', 'cache_post_updated', 10, 3 );
	}

	public function cache_post_updated( $post_id, $after, $before ) {
		wp_defender()->global['post_updated'][] = array(
			'post_id' => $post_id,
			'after'   => $after,
			'before'  => $before
		);
	}

	public function get_hooks() {
		return array(
			'save_post'              => array(
				'args'        => array( 'post_ID', 'post', 'is_updated' ),
				'level'       => self::LOG_LEVEL_INFO,
				'callback'    => array( 'WD_Post_Audit', 'post_updated_callback' ),
				'event_type'  => 'content',
				'action_type' => WD_Audit_API::ACTION_UPDATED,
			),
			'transition_post_status' => array(
				'args'         => array( 'new_status', 'old_status', 'post' ),
				'level'        => self::LOG_LEVEL_INFO,
				'event_type'   => 'content',
				'action_type'  => WD_Audit_API::ACTION_UPDATED,
				'false_when'   => array(
					array(
						'{{post->post_status}}',
						array(
							'inherit',
							'new',
							'auto-draft',
							'trash'
						),
					),
					array(
						'{{post->post_type}}',
						'revision'
					),
					array(
						'{{new_status}}',
						'{{old_status}}'
					),
					array(
						'{{old_status}}',
						array(
							'trash',
							'new'
						)
					),
				),
				'text'         => array(
					array(
						sprintf( esc_html__( "%s published %s \"%s\"", wp_defender()->domain ), '{{wp_user}}', '{{post_type_label}}', '{{post_title}}' ),
						'{{new_status}}',
						'publish',
						'=='
					),
					array(
						sprintf( esc_html__( "%s pending %s \"%s\"", wp_defender()->domain ), '{{wp_user}}', '{{post_type_label}}', '{{post_title}}' ),
						'{{new_status}}',
						'pending',
						'=='
					),
					array(
						sprintf( esc_html__( "%s drafted %s \"%s\"", wp_defender()->domain ), '{{wp_user}}', '{{post_type_label}}', '{{post_title}}' ),
						'{{new_status}}',
						'draft',
						'=='
					),
					array(
						sprintf( esc_html__( "%s changed %s \"%s\" status from %s to %s", wp_defender()->domain ), '{{wp_user}}', '{{post_type_label}}', '{{post_title}}', '{{old_status}}', '{{new_status}}' ),
						'{{new_status}}',
						'{{new_status}}',
						'=='
					),
				),
				'program_args' => array(
					'post_type_label' => array(
						'callable'        => 'get_post_type_object',
						'params'          => array(
							'{{post->post_type}}'
						),
						'result_property' => 'labels->singular_name'
					),
				),
				'custom_args'  => array(
					'post_title' => '{{post->post_title}}'
				),
				'context'      => '{{post_type_label}}'
			),
			'delete_post'            => array(
				'args'         => array( 'post_ID' ),
				'level'        => self::LOG_LEVEL_INFO,
				'event_type'   => 'content',
				'action_type'  => WD_Audit_API::ACTION_DELETED,
				'text'         => sprintf( esc_html__( "%s deleted %s \"%s\"", wp_defender()->domain ), '{{wp_user}}', '{{post_type_label}}', '{{post_title}}' ),
				'program_args' => array(
					'post'            => array(
						'callable' => 'get_post',
						'params'   => array(
							'{{post_ID}}'
						),
					),
					'post_type_label' => array(
						'callable'        => 'get_post_type_object',
						'params'          => array(
							'{{post->post_type}}'
						),
						'result_property' => 'labels->singular_name'
					),
					'post_title'      => array(
						'callable'        => 'get_post',
						'params'          => array(
							'{{post_ID}}'
						),
						'result_property' => 'post_title'
					),
				),
				'context'      => '{{post_type_label}}',
				'false_when'   => array(
					array(
						'{{post->post_type}}',
						array(
							'revision'
						)
					),
					array(
						'{{post->post_type}}',
						array(
							'attachment',
							'wdscan_result',
							'wd_iplockout_log',
							'wd_ip_lockout'
						)
					),
					array(
						'{{post_type_label}}',
						''
					)
				),
			),
			'untrashed_post'         => array(
				'args'         => array( 'post_ID' ),
				'level'        => self::LOG_LEVEL_INFO,
				'action_type'  => WD_Audit_API::ACTION_RESTORED,
				'event_type'   => 'content',
				'text'         => sprintf( esc_html__( "%s untrashed %s \"%s\"", wp_defender()->domain ), '{{wp_user}}', '{{post_type_label}}', '{{post_title}}' ),
				'program_args' => array(
					'post'            => array(
						'callable' => 'get_post',
						'params'   => array(
							'{{post_ID}}'
						),
					),
					'post_type_label' => array(
						'callable'        => 'get_post_type_object',
						'params'          => array(
							'{{post->post_type}}'
						),
						'result_property' => 'labels->singular_name'
					),
					'post_title'      => array(
						'callable'        => 'get_post',
						'params'          => array(
							'{{post_ID}}'
						),
						'result_property' => 'post_title'
					),
				),
				'context'      => '{{post_type_label}}',
			),
			'trashed_post'           => array(
				'args'         => array( 'post_ID' ),
				'level'        => self::LOG_LEVEL_INFO,
				'action_type'  => WD_Audit_API::ACTION_TRASHED,
				'event_type'   => 'content',
				'text'         => sprintf( esc_html__( "%s trashed %s \"%s\"", wp_defender()->domain ), '{{wp_user}}', '{{post_type_label}}', '{{post_title}}' ),
				'program_args' => array(
					'post'            => array(
						'callable' => 'get_post',
						'params'   => array(
							'{{post_ID}}'
						),
					),
					'post_type_label' => array(
						'callable'        => 'get_post_type_object',
						'params'          => array(
							'{{post->post_type}}'
						),
						'result_property' => 'labels->singular_name'
					),
					'post_title'      => array(
						'callable'        => 'get_post',
						'params'          => array(
							'{{post_ID}}'
						),
						'result_property' => 'post_title'
					),
				),
				'context'      => '{{post_type_label}}',
			),
			'update_postmeta'        => array(
				'args'        => array( 'meta_id', 'object_id', 'meta_key', 'meta_value' ),
				'callback'    => array( 'WD_Post_Audit', 'update_postmeta_callback' ),
				'level'       => self::LOG_LEVEL_INFO,
				'action_type' => WD_Audit_API::ACTION_UPDATED,
				'event_type'  => 'meta',
			),
		);
	}

	public static function update_postmeta_callback() {
		$args    = func_get_args();
		$meta_id = $args[1]['meta_id'];
		$post_id = $args[1]['object_id'];
		$key     = $args[1]['meta_key'];
		$value   = $args[1]['meta_value'];
		if ( WD_Audit_API::is_xss_positive( $value ) ) {
			$post = get_post( $post_id );

			return array(
				sprintf( esc_html__( "Suspicious meta found %s. Update to %s.", wp_defender()->domain ), esc_textarea( $value ), $key ),
				$post->post_title
			);
		}

		return false;
	}

	public static function post_updated_callback() {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return false;
		}

		$args     = func_get_args();
		$hookname = $args[0];
		$post     = $args[1]['post'];

		if ( in_array( $post->post_status, array(
				'trash',
				'auto-draft',
			) ) || in_array( $post->post_type, array( 'revision' ) )
		) {
			//usually, wp wll append :trash to the post name, so this case we just return
			return false;
		}

		$expclude_post_type = array(
			'wdscan_result',
			'wd_iplockout_log',
			'wd_ip_lockout'
		);

		if ( in_array( $post->post_type, $expclude_post_type ) ) {
			return false;
		}

		$post_type = get_post_type_object( $post->post_type );
		if ( ! is_object( $post_type ) ) {
			return false;
		}

		$is_updated  = $args[1]['is_updated'];
		$post_before = null;
		if ( isset( wp_defender()->global['post_updated'] ) && is_array( wp_defender()->global['post_updated'] ) ) {
			foreach ( wp_defender()->global['post_updated'] as $post_arr ) {
				if ( $post->ID == $post_arr['post_id'] ) {
					$post_before = $post_arr['before'];
					break;
				}
			}
		}

		if ( $is_updated === true ) {
			if ( ! is_null( $post_before ) ) {
				$post_after  = $post->to_array();
				$post_before = $post_before->to_array();
				//unset the date modified, and post status, as we got another hook for that
				unset( $post_after['post_modified'] );
				unset( $post_after['post_modified_gmt'] );
				unset( $post_after['post_status'] );
				unset( $post_before['post_modified'] );
				unset( $post_before['post_modified_gmt'] );
				unset( $post_before['post_status'] );
				if ( serialize( $post_before ) != serialize( $post_after ) ) {
					return array(
						sprintf( esc_html__( "%s updated %s \"%s\"", wp_defender()->domain ), WD_Utils::get_user_name( get_current_user_id() ), $post_type->labels->singular_name, $post_after['post_title'] ),
						$post_type->labels->singular_name
					);
				}
			}
		} else {
			if ( is_null( $post_before ) ) {
				return array(
					sprintf( esc_html__( "%s added new %s \"%s\"", wp_defender()->domain ), WD_Utils::get_user_name( get_current_user_id() ), $post_type->labels->singular_name, $post->post_title ),
					$post_type->labels->singular_name
				);
			}
		}

		return false;
	}

	public static function post_updated_callback1() {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return false;
		}

		$args = func_get_args();

		$hookname    = $args[0];
		$post_after  = $args[1]['post_after'];
		$post_before = $args[1]['post_before'];

		//first we need to check if the status is right
		if ( $post_after->post_status == 'trash' ) {
			//usually, wp wll append :trash to the post name, so this case we just return
			return false;
		}

		$post_type = get_post_type_object( $post_after->post_type );
		if ( ! is_object( $post_type ) ) {
			return false;
		}

		//build array
		$post_after  = $post_after->to_array();
		$post_before = $post_before->to_array();

		//unset the date modified, and post status, as we got another hook for that
		unset( $post_after['post_modified'] );
		unset( $post_after['post_modified_gmt'] );
		unset( $post_after['post_status'] );
		unset( $post_before['post_modified'] );
		unset( $post_before['post_modified_gmt'] );
		unset( $post_before['post_status'] );

		if ( count( array_udiff( $post_after, $post_before, array( 'WD_Post_Audit', 'compare_post' ) ) ) ) {
			return array(
				sprintf( esc_html__( "%s updated %s \"%s\"", wp_defender()->domain ), WD_Utils::get_user_name( get_current_user_id() ), $post_type->labels->singular_name, $post_after['post_title'] ),
				$post_type->labels->singular_name
			);
		}

		return false;

	}

	public static function compare_post( $a, $b ) {
		if ( ! is_array( $a ) && ! is_array( $b ) ) {
			return strcmp( $a, $b ) === 0;
		} elseif ( is_array( $a ) && is_array( $b ) ) {
			return count( array_diff( $a, $b ) ) === 0;
		} else {
			return 0;
		}
	}

	public function dictionary() {
		return array();
	}
}