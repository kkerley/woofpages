<?php
/**
 * Class: WD_Change_Default_Admin
 */

/**
 * @author: Hoang Ngo
 */
class WD_Change_Default_Admin extends WD_Hardener_Abstract {
	public function on_creation() {
		$this->id         = 'change_default_admin';
		$this->title      = esc_html__( 'Change default admin user account', wp_defender()->domain );
		$this->can_revert = true;

		$this->add_action( 'admin_footer', 'print_scripts' );
		$this->add_ajax_action( $this->generate_ajax_action( 'change_default_admin' ), 'process' );
		$this->add_action( 'wd_hardener_layout_end', 'show_dialog' );
		if ( $this->check() && WD_Utils::get_setting( $this->get_setting_key( 'start' ) ) ) {
			WD_Utils::update_setting( $this->get_setting_key( 'start' ), 0 );
			//flag for submit
			WD_Utils::flag_for_submitting();
		}
	}

	/**
	 * We will check if the users table still having default admin account
	 *
	 * @return bool
	 * @access public
	 * @since 1.0
	 */
	public function check() {
		$user = get_user_by( 'login', 'admin' );

		return ! is_object( $user );
	}

	public function show_dialog() {
		?>
		<a href="#<?php echo $this->id ?>_dialog" rel="dialog" class="wd_change_default_admin_trigger"></a>
		<dialog id="<?php echo $this->id ?>_dialog" class="no-close"
		        title="<?php esc_attr_e( "Action Required", wp_defender()->domain ) ?>">

		</dialog>
		<?php
	}

	public function print_scripts() {
		?>
		<script type="text/javascript">
			jQuery(function ($) {
				$('#change_default_admin_frm').submit(function (e) {
					var that = $(this);
					var parent = $(this).closest('.wd-hardener-rule');
					$.ajax({
						type: 'POST',
						url: ajaxurl,
						data: that.serialize(),
						beforeSend: function () {
							that.find('button').attr('disabled', 'disabled');
							that.find('button').css({
								'cursor': 'progress'
							});
						},
						success: function (data) {
							that.find('button').removeAttr('disabled');
							that.find('button').css({
								'cursor': 'pointer'
							});

							if (data.status == 0) {
								$('#change_default_admin .wd-error').html(data.error).removeClass('wd-hide');
							} else {
								$('#change_default_admin .wd-error').html('').addClass('wd-hide');
								$('#change_default_admin_dialog').html(data.message);
								$('.wd_change_default_admin_trigger').trigger('click');
								setTimeout(function () {
									location.reload();
								}, 3000)
							}
						}
					})
					return false;
				})
			})
		</script>
		<?php
	}

	/**
	 * @return bool|void
	 */
	public function process() {
		if ( ! WD_Utils::check_permission() ) {
			return;
		}

		if ( ! wp_verify_nonce( WD_Utils::http_post( '_wdnonce' ), 'change_default_admin' ) ) {
			return;
		}

		$new_user_name = trim( WD_Utils::http_post( 'new_admin_account' ) );
		$this->validate( $new_user_name );
		//everything seems good here
		WD_Utils::update_setting( $this->get_setting_key( 'start' ), 1 );
		$this->change_username( $new_user_name );
		//clear cookie for relogin
		wp_clear_auth_cookie();

		//done
		if ( $this->is_ajax() ) {
			$url = is_multisite() ? network_admin_url( 'admin.php?page=wdf-hardener' ) : admin_url( "admin.php?page=wdf-hardener" );
			wp_send_json( array(
				'status'  => 1,
				'message' => '<div class="wp-defender">' . sprintf( __( "Your admin name has changed. You will need to <a href='" . $url . "'><strong>%s</strong></a>.<br/>This will auto reload after 3 seconds.", wp_defender()->domain ), "re-login" ) . '</div>'
			) );
		}

		return true;
	}

	/**
	 * @param $new_user_name
	 *
	 * @return bool|string
	 */
	public function change_username( $new_user_name ) {
		/**
		 * we will create another username, which data cloning from admin user
		 * then we will assign all the content to this new user, then delete the admin user
		 * purpose for changing the ID=1, meanless if only change username
		 */

		$admin_data = get_user_by( 'login', 'admin' );
		//$this->log( 'current admin data :' . var_export( $admin_data, true ) );

		/**
		 * at this pharse we only rename the login
		 */
		//create new admin account
		global $wpdb;
		$wpdb->update( $wpdb->users, array(
			'ID'         => $admin_data->ID,
			'user_login' => $new_user_name
		), array(
			'ID' => $admin_data->ID
		) );

		if ( is_multisite() ) {
			$site_admins = get_site_option( 'site_admins' );
			if ( is_array( $site_admins ) ) {
				//replace the admin
				$a_key = array_search( strtolower( 'admin' ), array_map( 'strtolower', $site_admins ) );
				if ( isset( $site_admins[ $a_key ] ) ) {
					$site_admins[ $a_key ] = $new_user_name;
					//reupdate
					if ( update_site_option( 'site_admins', $site_admins ) == false ) {
						$this->output_error( 'cant_update', $wpdb->last_error );
					}
				}
			}
		}
		clean_user_cache( $admin_data->ID );

		return true;
	}

	/**
	 * Validate username. The case we do will be
	 * empty check
	 * can't be admin again
	 * a valid name
	 * doesn't exists
	 *
	 * @param $new_user_name
	 *
	 * @return bool
	 */
	public function validate( $new_user_name ) {
		if ( empty( $new_user_name ) ) {
			if ( $this->is_ajax() ) {
				wp_send_json( array(
					'status' => 0,
					'error'  => esc_html__( "That username can't be empty!", wp_defender()->domain )
				) );
			} else {
				return new WP_Error( 'empty_username', esc_html__( "That username can't be empty!", wp_defender()->domain ) );
			}
		}

		if ( strtolower( $new_user_name ) == 'admin' ) {
			if ( $this->is_ajax() ) {
				wp_send_json( array(
					'status' => 0,
					'error'  => esc_html__( "You can't use admin as a username again!", wp_defender()->domain )
				) );
			} else {
				return new WP_Error( 'admin_again', esc_html__( "You can't use admin as a username again!", wp_defender()->domain ) );
			}
		}
		//sanitized it
		$new_user_name = sanitize_text_field( $new_user_name );

		if ( ! validate_username( $new_user_name ) ) {
			if ( $this->is_ajax() ) {
				wp_send_json( array(
					'status' => 0,
					'error'  => esc_html__( "That username is invalid!", wp_defender()->domain )
				) );
			} else {
				return new WP_Error( 'username_invalid', esc_html__( "That username is invalid!", wp_defender()->domain ) );
			}
		}

		//now check if the username unique
		if ( username_exists( $new_user_name ) ) {
			if ( $this->is_ajax() ) {
				wp_send_json( array(
					'status' => 0,
					'error'  => esc_html__( "That username already exists!", wp_defender()->domain )
				) );
			} else {
				return new WP_Error( 'username_exists', esc_html__( "That username already exists!", wp_defender()->domain ) );
			}
		}

		return true;
	}

	public function display() {
		?>
		<div class="wd-hardener-rule">
			<?php echo $this->get_rule_title(); ?>
			<div class="wd-clearfix"></div>

			<div id="<?php echo $this->id ?>" class="wd-rule-content">
				<h4 class="tl"><?php esc_html_e( "Overview", wp_defender()->domain ) ?></h4>

				<p><?php _e( "If you're using the default <strong>admin</strong> login name, you're giving away an important piece of the puzzle hackers need to hijack your website. Having a default admin user account is bad practice, but one that's easily fixed. Ensure you backup your database before choosing a new username.", wp_defender()->domain ) ?></p>

				<h4 class="tl"><?php esc_html_e( "How To Fix", wp_defender()->domain ) ?></h4>

				<div class="wd-error wd-hide">

				</div>
				<div class="wd-well">
					<?php if ( $this->check() ): ?>
						<?php
						esc_html_e( "You don’t have a user with username admin.", wp_defender()->domain )
						?>
					<?php else: ?>
						<p>
							<?php esc_html_e( "Please change the username from admin to something unique.", wp_defender()->domain ) ?>
						</p>

						<form method="post" id="change_default_admin_frm" class="form-button-inline">
							<?php echo wp_nonce_field( 'change_default_admin', '_wdnonce' ) ?>
							<input type="hidden" name="action"
							       value="<?php echo $this->generate_ajax_action( 'change_default_admin' ) ?>">

							<div class="group">
								<div class="col span_10_of_12">
									<input name="new_admin_account" type="text"
									       placeholder="<?php esc_attr_e( "Type a new username", wp_defender()->domain ) ?>">
								</div>
								<div class="col span_2_of_12">
									<button type="submit" class="button wd-button">
										<?php esc_html_e( "Update", wp_defender()->domain ) ?>
									</button>
								</div>
								<div class="wd-clearfix"></div>
							</div>
						</form>
					<?php endif; ?>
				</div>
				<?php echo $this->ignore_button() ?>
			</div>
		</div>
		<?php
	}

	public function revert() {

	}
}