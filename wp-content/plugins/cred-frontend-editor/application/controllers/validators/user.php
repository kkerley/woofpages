<?php

class CRED_Validator_User extends CRED_Validator_Base implements ICRED_Validator {

    public function validate() {

        $result = true;

        $zebraForm = $this->_zebraForm;
        $formHelper = $this->_formHelper;

        $form = $this->_formData;
        $_fields = $form->getFields();
        $form_type = $_fields['form_settings']->form['type'];

        $is_user_form = ($form->getForm()->post_type == CRED_USER_FORMS_CUSTOM_POST_NAME);

        //no validation if it is not a user
        if ( !$is_user_form ) {
            cred_log( "not user form" );
            return true;
        }

        if ( isset( $_POST['user_pass'] ) ) {
            if ( $form_type == 'edit' && empty( $_POST['user_pass'] ) && empty( $_POST['user_pass2'] ) ) {
                //Fixing cred-161
                unset( $_POST['user_pass'] );
                unset( $_POST['user_pass2'] );
            }
        }

        if ( (isset( $_POST['user_pass'] ) && empty( $_POST['user_pass'] )) ||
                (isset( $_POST['user_pass2'] ) && empty( $_POST['user_pass2'] )) ) {
            $zebraForm->add_top_message( __( 'Password fields are required', 'wp-cred' ) );
            $result = false;
        } else {
            if ( isset( $_POST['user_pass'] ) && isset( $_POST['user_pass2'] ) &&
                    $_POST['user_pass'] != $_POST['user_pass2'] ) {
                $zebraForm->add_top_message( __( 'Password fields do not match', 'wp-cred' ) );
                $zebraForm->add_field_message( __( 'Password fields do not match', 'wp-cred' ), 'user_pass2' );
                $result = false;
            }
        }

        if ( $form_type == 'edit' ) {
            $user_id_to_edit = $_POST[CRED_StaticClass::PREFIX . 'post_id'];
            $_user = new WP_User( $user_id_to_edit );

            if ( isset( $_POST['user_email'] ) &&
                    $_POST['user_email'] != $_user->data->user_email &&
                    email_exists( $_POST['user_email'] ) ) {
                $zebraForm->add_top_message( __( 'Sorry, that email address is already used!', 'wp-cred' ) );
                $zebraForm->add_field_message( __( 'Sorry, that email address is already used!', 'wp-cred' ), 'user_email' );
                $result = false;
            }

            $is_multisite_error = false;
            if ( is_multisite() ) {
                global $current_user;
                wp_get_current_user();
                $super_admins = get_super_admins();
                $is_user_edited_super_admin = (is_array( $super_admins ) && in_array( $_user->data->user_login, $super_admins ));
                $is_user_editing_super_admin = (is_array( $super_admins ) && in_array( $current_user->user_login, $super_admins ));
                if ( $is_user_edited_super_admin && !$is_user_editing_super_admin ) {
                    $is_multisite_error = false;
                }
            }

            $user_role_to_edit = isset( $_user->roles[0] ) ? strtolower( $_user->roles[0] ) : "";
            $user_role_can_edit = json_decode( $_fields['form_settings']->form['user_role'], true );
            if ( !empty( $user_role_can_edit ) && !in_array( $user_role_to_edit, $user_role_can_edit ) && !$is_multisite_error ) {
                $msg = __( 'You can edit only users with following roles', 'wp-cred' );
                $zebraForm->add_top_message( $msg . ': <b>' . implode( ", ", $user_role_can_edit ) . '</b>' );
                $result = false;
            }
        } else {
            if ( isset( $_POST['user_email'] ) && email_exists( $_POST['user_email'] ) ) {
                $zebraForm->add_top_message( __( 'Sorry, that email address is already used!', 'wp-cred' ) );
                $zebraForm->add_field_message( __( 'Sorry, that email address is already used!', 'wp-cred' ), 'user_email' );
                $result = false;
            }
            if ( isset( $_POST['user_login'] ) && username_exists( $_POST['user_login'] ) ) {
                $zebraForm->add_top_message( __( 'Sorry, that username is already used!', 'wp-cred' ) );
                $zebraForm->add_field_message( __( 'Sorry, that username is already used!', 'wp-cred' ), 'user_login' );
                $result = false;
            }
        }
        return $result;
    }

}
