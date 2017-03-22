<?php

class CRED_Form_Post extends CRED_Form_Base {

    public function __construct($_form_id, $_post_id = false, $_form_count = 0, $_preview = false) {
        parent::__construct( $_form_id, $_post_id, $_form_count, $_preview );
        cred_log( "__construct" );
        CRED_StaticClass::$_current_prefix = "cred-form-";
    }

//    public function print_new_form($_form_id, $_post_id = false, $_form_count = 0, $_preview = false) {
//        return new self($_form_id, $_post_id, $_form_count, $_preview);
//    }

    public function _check_redirection($post_id, $form_id, $form, $_fields, $thisform, $formHelper, $is_ajax, $attachedData) {
        cred_log( "_check_redirection post" );
        cred_log( array($post_id, $form_id, $thisform, $is_ajax) );
        if ( !(is_int( $post_id ) && $post_id > 0) ) {
            return false;
        }

        // set global $post
        $post = get_post( $post_id );

        // send notifications
        //list($n_sent, $n_failed)=$this->notify($post_id);
        // enable notifications and notification events if any
        $this->notify( $post_id, $attachedData );
        unset( $attachedData );

        // save results for later messages if PRG
        //$formHelper->setCookie('_cred_cred_notifications'.$prg_id, array('sent'=>$n_sent, 'failed'=>$n_failed));
        // do custom action here
        // user can redirect, display messages, overwrite page etc..
        $bypass_credaction = apply_filters( 'cred_bypass_credaction_' . $form_id, false, $form_id, $post_id, $thisform );
        $bypass_credaction = apply_filters( 'cred_bypass_credaction', $bypass_credaction, $form_id, $post_id, $thisform );

        //Emerson:->https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/185572863/comments
        /* Add cred_submit_complete_form_ hook in CRED 1.3 */
        $form_slug = $form->getForm()->post_name;
        do_action( 'cred_submit_complete_form_' . $form_slug, $post_id, $thisform );
        do_action( 'cred_submit_complete_' . $form_id, $post_id, $thisform );
        do_action( 'cred_submit_complete', $post_id, $thisform );

        // no redirect url
        $url = false;
        // do success action
        if ( $bypass_credaction ) {
            $credaction = 'form';
        } else {
            $credaction = $_fields['form_settings']->form['action'];
        }

        cred_log( $credaction );

        $prg_id = CRED_StaticClass::$out['prg_id'];
        // do default or custom actions
        switch ($credaction) {
            case 'post':
                $url = $formHelper->getLocalisedPermalink( $post_id, $_fields['form_settings']->post['post_type'] ); //get_permalink($post_id);
                break;
            case 'page':
                $url = (!empty( $_fields['form_settings']->form['action_page'] )) ? $formHelper->getLocalisedPermalink( $_fields['form_settings']->form['action_page'], 'page' )/* get_permalink($_fields['form_settings']->form['action_page']) */ : false;
                break;
            case 'message':
            case 'form':
            // custom 3rd-party action
            default:
                if ( 'form' != $credaction && 'message' != $credaction ) {
                    // add hooks here, to do custom action when custom cred action has been selected
                    do_action( 'cred_custom_success_action_' . $form_id, $credaction, $post_id, $thisform, $is_ajax );
                    do_action( 'cred_custom_success_action', $credaction, $post_id, $thisform, $is_ajax );
                }

                // if previous did not do anything, default to display form
                if ( 'form' != $credaction && 'message' != $credaction )
                    $credaction = 'form';

                // no redirect url
                $url = false;

                // PRG (POST-REDIRECT-GET) pattern,
                // to avoid resubmit on browser refresh issue, and also keep defaults on new form !! :)
                if ( 'message' == $credaction ) {
                    $url = $formHelper->currentURI( array(
                        '_tt' => time(),
                        '_success_message' => $prg_id,
                        '_target' => $post_id
                            ) );
                } else {
                    if ( $is_ajax && $credaction == 'form' ) {
                        //do nothing                        
                    } else {
                        $url = $formHelper->currentURI( array(
                            '_tt' => time(),
                            '_success' => $prg_id,
                            '_target' => $post_id
                                ) );
                    }
                }
                if ( $is_ajax && $credaction == 'form' ) {
                    //do nothing
                } else {
                    $url = $url . '#cred_form_' . $prg_id;
                    // do PRG, redirect now
                    $cred_response = new CRED_Generic_Response( CRED_GENERIC_RESPONSE_RESULT_REDIRECT, $url, $is_ajax, $thisform, $formHelper );
                    return $cred_response->show();
                    exit;  // just in case
                }
                break;
        }

        // do redirect action here
        if ( false !== $url ) {
            if ( 'form' != $credaction && 'message' != $credaction ) {
                $url = apply_filters( 'cred_success_redirect_form_' . $form_slug, $url, $post_id, $thisform );
                $url = apply_filters( 'cred_success_redirect_' . $form_id, $url, $post_id, $thisform );
                $url = apply_filters( 'cred_success_redirect', $url, $post_id, $thisform );
            }

            if ( false !== $url ) {
                cred_log( "redirection" );
                $redirect_delay = $_fields['form_settings']->form['redirect_delay'];
                $cred_response = new CRED_Generic_Response( CRED_GENERIC_RESPONSE_RESULT_REDIRECT, $url, $is_ajax, $thisform, $formHelper, $redirect_delay );
                return $cred_response->show();
                exit;
            }
        }

        $saved_message = $formHelper->getLocalisedMessage( 'post_saved' );
        $saved_message = apply_filters( 'cred_data_saved_message_' . $form_id, $saved_message, $form_id, $post_id, $this->_preview );
        $saved_message = apply_filters( 'cred_data_saved_message', $saved_message, $form_id, $post_id, $this->_preview );
        // add success message
        //$zebraForm->add_form_message('data-saved', $formHelper->getLocalisedMessage('post_saved'));
        $this->_zebraForm->add_success_message( $saved_message );
        
        //is not a redirection
        return false;
    }

    /**
     * check_form_access
     * @param type $form_type
     * @param type $form_id
     * @param type $post
     * @param type $fbHelper
     * @return type
     */
    public function check_form_access($form_type, $form_id, $post, &$fbHelper) {
        cred_log( array($form_type, $form_id, $post) );
        return $fbHelper->checkFormAccess( $form_type, $form_id, $post );
    }

    /**
     * set_authordata
     * @global type $post
     * @global WP_User $authordata
     */
    public function set_authordata() {
        global $post, $authordata;
        if ( is_int( $this->_post_id ) && $this->_post_id > 0 ) {
            if ( !isset( $post->ID ) || (isset( $post->ID ) && $post->ID != $this->_post_id) ) {
                $post = get_post( $this->_post_id );
                // As we modify the global $post, we need to also set the global $authordata and set the Toolset post relationships
                // This will bring compatibility with third party plugins and with shortcodes getting related posts data
                $authordata = new WP_User( $post->post_author );
                do_action( 'toolset_action_record_post_relationship_belongs', $post );
            }
        }
    }

    /**
     * _check_new_post
     * @global type $post
     * @global type $_post_to_create
     * @global type $_current_post
     * @global type $wpdb
     * @param type $form_type
     * @return \WP_Error|boolean
     */
    public function _create_new_post($form_id, $form_type, $post_id, $post_type) {
        cred_log( "_create_new_post" );

        global $post, $_post_to_create, $_current_post;

        // get post inputs
        if ( isset( $post_id ) && !empty( $post_id ) && null !== $post_id && false !== $post_id && !$this->_preview )
            $post_id = intval( $post_id );
        elseif ( isset( $post->ID ) && !$this->_preview )
            $post_id = $post->ID;
        else
            $post_id = false;

        $formHelper = $this->_formHelper;
        $form = $this->_formData;
        $this->_post_id = $post_id;
        //$this->_formData=$formHelper->loadForm($form_id, $preview);
        if ( $formHelper->isError( $form ) )
            return $form;

        //TODO: get recaptcha settings
        CRED_StaticClass::$_staticGlobal['RECAPTCHA'] = $formHelper->getRecaptchaSettings( CRED_StaticClass::$_staticGlobal['RECAPTCHA'] );

        $user_id = get_current_user_id();
        if ( $user_id <= 0 ) {
            if ( !$this->_preview && !$formHelper->checkFormAccess( $form_type, $form_id ) ) {
                return $formHelper->error();
            }
        }

        //Added by Ahmed Hussein, for issue where multiple cred forms in same page get same post ids
        if ( $form_type == "new" ) {
            $_post_to_create = null;
        }

        // if this is an edit form and no post id given
        if ( (('edit' == $form_type && false === $post_id && !$this->_preview) ||
                (isset( $_GET['action'] ) && $_GET['action'] == 'edit_translation' && 'translation' == $form_type)) &&
                false === $post_id && !$this->_preview ) {
            cred_log( "No post specified" );
            return $formHelper->error( __( 'No post specified', 'wp-cred' ) );
        }

        // if this is a new form or preview
        if ( 'new' == $form_type || $this->_preview ||
                (isset( $_GET['action'] ) && $_GET['action'] == 'create_translation' && 'translation' == $form_type) || $this->_preview ) {

            if ( !isset( $_post_to_create ) || empty( $_post_to_create ) ) {
                //Adding auto-draft with each form render
                $unique_value = md5( CRED_StaticClass::getIP() );
                $unique_post_title = "CRED Auto Draft {$unique_value}";

                global $wpdb;
                $querystr = $wpdb->prepare( "SELECT $wpdb->posts.ID FROM $wpdb->posts WHERE $wpdb->posts.post_status = 'auto-draft' AND $wpdb->posts.post_type = %s AND $wpdb->posts.post_title like %s AND $wpdb->posts.post_author = %d ORDER by ID desc Limit 1", $post_type, $unique_post_title, $user_id );
                $_myposts = $wpdb->get_results( $querystr, OBJECT );

                //if exists
                if ( !empty( $_myposts ) ) {
                    $mypost = get_post( $_myposts[0]->ID );
                    $mypost_id = $mypost->ID;
                    unset( $_myposts );
                    cred_log( "Exists:" );
                    cred_log( $mypost_id );
                } else {
                    $mypost_id = cred__create_auto_draft( $unique_post_title, $post_type, $user_id );
                    cred_log( "Not Exists:" );
                    cred_log( $mypost_id );
                }

                $_post_to_create = $mypost_id;
                $this->_post_id = $mypost_id;

                //If post_id is not null and is not auto-draft means that the user used
                //back arrow of the browser
                //So i set post_id with a new one
                if ( $this->_post_id != null ) {
                    $mycheckpost = get_post( $post_id );
                    if ( isset( $mycheckpost ) && $mycheckpost->post_status != 'auto-draft' )
                        $this->_post_id = $_post_to_create;
                }
                //#########################################################################
            } else {
                $this->_post_id = $_post_to_create;
            }
        }

        // get existing post data if edit form and post given
        if ( (('edit' == $form_type && !$this->_preview) ||
                (isset( $_GET['action'] ) &&
                $_GET['action'] == 'edit_translation' &&
                'translation' == $form_type)) &&
                !$this->_preview ) {

            if ( $_current_post == 0 ) {
                $_current_post = $post_id;
            }

            $is_current_edit = false;
            if ( $_current_post > 0 ) {
                $is_current_edit = true;
                $post_id = $_current_post;
            }

            $this->_post_id = $post_id;
            cred_log( "post_id " . $this->_post_id );

            $postdata = new CRED_Post_Data();
            $this->_postData = $postdata->getPostData( $post_id );

            if ( $is_current_edit ) {
                $post_type = $this->_postData->post->post_type;
            }

            if ( $formHelper->isError( $this->_postData ) )
                return $this->_postData;

            if ( !$is_current_edit &&
                    $this->_postData->post->post_type != $post_type ) {
                return $formHelper->error( __( 'Form type and post type do not match', 'wp-cred' ) );
            }
        }

        return $post_id;
    }

    /**
     * build_form
     */
    public function build_form() {
        cred_log( "Post build_form" );

        // get refs here        
        $formHelper = $this->_formHelper;
        $shortcodeParser = $this->_shortcodeParser;

        $zebraForm = $this->_zebraForm;
        $zebraForm->_shortcodeParser = $shortcodeParser;
        $zebraForm->_formHelper = $formHelper;
        $zebraForm->_formData = $this->_formData;
        $zebraForm->_post_id = $this->_post_id;

        if ( $zebraForm->preview )
            $preview_content = $this->_content;

        // remove any HTML comments before parsing, allow to comment-out parts
        $this->_content = $shortcodeParser->removeHtmlComments( $this->_content );
        // do WP shortcode here for final output, moved here to avoid replacing post_content
        // call wpv_do_shortcode instead to fix render wpv shortcodes inside other shortcodes
        $this->_content = apply_filters( 'cred_content_before_do_shortcode', $this->_content );

        //New CRED shortcode to retrieve current container post_id
        if ( isset( CRED_StaticClass::$_cred_container_id ) )
            $this->_content = str_replace( "[cred-container-id]", CRED_StaticClass::$_cred_container_id, $this->_content );

        if ( function_exists( 'wpv_do_shortcode' ) ) {
            $this->_content = wpv_do_shortcode( $this->_content );
        } else {
            $this->_content = do_shortcode( $this->_content );
        }

        // parse all shortcodes internally
        $shortcodeParser->remove_all_shortcodes();
        $shortcodeParser->add_shortcode( 'credform', array(&$zebraForm, 'cred_form_shortcode') );
        $this->_content = $shortcodeParser->do_shortcode( $this->_content );
        $shortcodeParser->remove_shortcode( 'credform', array(&$zebraForm, 'cred_form_shortcode') );

        CRED_StaticClass::fix_cred_field_shortcode_value_attribute_by_single_quote( $zebraForm->_form_content );

        // render any external third-party shortcodes first (enables using shortcodes as values to cred shortcodes)
        $zebraForm->_form_content = do_shortcode( $zebraForm->_form_content );

        // build shortcodes, (backwards compatibility, render first old shortcode format with dashes)
        $shortcodeParser->add_shortcode( 'cred-field', array(&$zebraForm, 'cred_field_shortcodes') );
        $shortcodeParser->add_shortcode( 'cred-generic-field', array(&$zebraForm, 'cred_generic_field_shortcodes') );
        $shortcodeParser->add_shortcode( 'cred-show-group', array(&$zebraForm, 'cred_conditional_shortcodes') );

        // build shortcodes, render new shortcode format with underscores        
        $shortcodeParser->add_shortcode( 'cred_field', array(&$zebraForm, 'cred_field_shortcodes') );
        $shortcodeParser->add_shortcode( 'cred_generic_field', array(&$zebraForm, 'cred_generic_field_shortcodes') );
        $shortcodeParser->add_shortcode( 'cred_show_group', array(&$zebraForm, 'cred_conditional_shortcodes') );
        CRED_StaticClass::$out['child_groups'] = array();
        //$this->_form_content=$shortcodeParser->do_recursive_shortcode('cred-show-group', $this->_form_content);
        $zebraForm->_form_content = $shortcodeParser->do_recursive_shortcode( 'cred_show_group', $zebraForm->_form_content );
        CRED_StaticClass::$out['child_groups'] = array();

        $zebraForm->_form_content = $shortcodeParser->do_shortcode( $zebraForm->_form_content );

        $shortcodeParser->remove_shortcode( 'cred_show_group', array(&$zebraForm, 'cred_conditional_shortcodes') );
        $shortcodeParser->remove_shortcode( 'cred_generic_field', array(&$zebraForm, 'cred_generic_field_shortcodes') );
        $shortcodeParser->remove_shortcode( 'cred_field', array(&$zebraForm, 'cred_field_shortcodes') );

        $shortcodeParser->remove_shortcode( 'cred-show-group', array(&$zebraForm, 'cred_conditional_shortcodes') );
        $shortcodeParser->remove_shortcode( 'cred-generic-field', array(&$zebraForm, 'cred_generic_field_shortcodes') );
        $shortcodeParser->remove_shortcode( 'cred-field', array(&$zebraForm, 'cred_field_shortcodes') );

        // add nonce hidden field
        if ( is_user_logged_in() ) {
            $nonce_id = substr( $zebraForm->form_properties['name'], 0, strrpos( $zebraForm->form_properties['name'], '_' ) );
            $nonceobj = $zebraForm->add2form_content( 'hidden', CRED_StaticClass::NONCE . "_" . $nonce_id, wp_create_nonce( $nonce_id ), array('style' => 'display:none;') );
        }
        // add post_id hidden field
        if ( $this->_post_id ) {
            $post_id_obj = $zebraForm->add2form_content( 'hidden', CRED_StaticClass::PREFIX . 'post_id', $this->_post_id, array('style' => 'display:none;') );
        }

        if ( isset( CRED_StaticClass::$_cred_container_id ) ) {
            $cred_container_id_obj = $zebraForm->add2form_content( 'hidden', CRED_StaticClass::PREFIX . 'cred_container_id', CRED_StaticClass::$_cred_container_id, array('style' => 'display:none;') );
        }

        // add to form
        $_fields = $this->_formData->getFields();
        $form_type = $_fields['form_settings']->form['type'];
        $form_id = $this->_formData->getForm()->ID;
        $form_count = CRED_StaticClass::$out['count'];
        $post_type = $_fields['form_settings']->post['post_type'];
        //$post_type=$this->_postType;

        if ( $zebraForm->preview ) {
            // add temporary content for form preview
            //$obj=$zebraForm->add('textarea', CRED_StaticClass::PREFIX.'form_preview_content', $preview_content, array('style'=>'display:none;'));
            $zebraForm->add2form_content( 'textarea', CRED_StaticClass::PREFIX . 'form_preview_content', $preview_content, array('style' => 'display:none;') );
            // add temporary content for form preview (not added automatically as there is no shortcode to render this)
            //$this->_form_content.=$obj->toHTML();
            // hidden fields are rendered automatically
            //$obj=$zebraForm->add('hidden',CRED_StaticClass::PREFIX.'form_preview_post_type', $post_type, array('style'=>'display:none;'));
            $obj = $zebraForm->add2form_content( 'hidden', CRED_StaticClass::PREFIX . 'form_preview_post_type', $post_type, array('style' => 'display:none;') );
            //$obj=$zebraForm->add('hidden',CRED_StaticClass::PREFIX.'form_preview_form_type', $form_type, array('style'=>'display:none;'));
            $obj = $zebraForm->add2form_content( 'hidden', CRED_StaticClass::PREFIX . 'form_preview_form_type', $form_type, array('style' => 'display:none;') );

            if ( $_fields['form_settings']->form['has_media_button'] ) {
                //$zebraForm->add_form_error('preview_media', __('Media Upload will not work with form preview','wp-cred'));
                $zebraForm->add_field_message( __( 'Media Upload will not work with form preview', 'wp-cred' ) );
            }

            //https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/195892843/comments#309778558
            //Created a separated preview messages
            //$zebraForm->add_form_message('preview_mode', __('Form Preview Mode','wp-cred'));
            $zebraForm->add_preview_message( __( 'Form Preview Mode', 'wp-cred' ) );
        }
        // hidden fields are rendered automatically
        // add form id
        //$obj=$zebraForm->add('hidden', CRED_StaticClass::PREFIX.'form_id', $form_id, array('style'=>'display:none;'));
        $obj = $zebraForm->add2form_content( 'hidden', CRED_StaticClass::PREFIX . 'form_id', $form_id, array('style' => 'display:none;') );
        // add form count
        //$obj=$zebraForm->add('hidden', CRED_StaticClass::PREFIX.'form_count', $form_count, array('style'=>'display:none;'));
        $obj = $zebraForm->add2form_content( 'hidden', CRED_StaticClass::PREFIX . 'form_count', $form_count, array('style' => 'display:none;') );
        // check conditional expressions for javascript

        if ( !empty( CRED_StaticClass::$_mail_error ) ) {
            echo '<label id="lbl_generic" class="wpt-form-error">' . CRED_StaticClass::$_mail_error . "</label>";
            CRED_StaticClass::$_mail_error = "";
            delete_option( '_' . $form_id . '_last_mail_error' );
        }

        // Set cache variable for all forms ( Custom JS)
        $js_content = $_fields['extra']->js;
        if ( !empty( $js_content ) ) {
            $custom_js_cache = wp_cache_get( 'cred_custom_js_cache' );
            if ( false === $custom_js_cache ) {
                $custom_js_cache = '';
            }
            $custom_js_cache .= "\n\n" . $js_content;
            wp_cache_set( 'cred_custom_js_cache', $custom_js_cache );
        }

        // Set cache variable for all forms ( Custom CSS)
        $css_content = $_fields['extra']->css;
        if ( !empty( $css_content ) ) {
            $custom_css_cache = wp_cache_get( 'cred_custom_css_cache' );
            if ( false === $custom_css_cache ) {
                $custom_css_cache = '';
            }
            $custom_css_cache .= "\n\n" . $css_content;
            wp_cache_set( 'cred_custom_css_cache', $custom_css_cache );
        }

        //$js_content = $_fields['extra']->js;
        //$zebraForm->addJsFormContent($js_content);
        //$css_content = $js_content = $_fields['extra']->css;
    }

    /**
     * save_form
     * @param type $post_id
     * @return boolean
     */
    public function save_form($post_id = null, $_post_type = "") {
        cred_log( "post_form cred_save " . $post_id );

        $formHelper = $this->_formHelper;
        $zebraForm = $this->_zebraForm;

        $form = $this->_formData;
        $form_id = $form->getForm()->ID;
        $_fields = $form->getFields();
        $form_type = $_fields['form_settings']->form['type'];
        $post_type = $this->_post_type;

        $thisform = array(
            'id' => $form_id,
            'post_type' => $post_type,
            'form_type' => $form_type,
            'container_id' => CRED_StaticClass::$_cred_container_id,
        );
        cred_log( $thisform );

        // do custom actions before post save
        do_action( 'cred_before_save_data_' . $form_id, $thisform );
        do_action( 'cred_before_save_data', $thisform );

        // track form data for notification mail
        $trackNotification = false;
        if (
                isset( $_fields['notification']->enable ) &&
                $_fields['notification']->enable &&
                !empty( $_fields['notification']->notifications )
        )
            $trackNotification = true;

        // save result (on success this is post ID)
        $new_post_id = false;

        // Check if we are posting nothing, in which case we are dealing with uploads greater than the size limit
        if ( empty( $_POST ) && isset( $_GET['_tt'] ) ) {
            return $new_post_id;
        }

        // default post fields
        $post = $formHelper->CRED_extractPostFields( $post_id, $trackNotification );
        cred_log( $post );

        // custom fields, taxonomies and file uploads; also, catch error_files for sizes lower than the server maximum but higher than the form/site maximum
        list($fields, $fieldsInfo, $taxonomies, $files, $removed_fields, $error_files) = $formHelper->CRED_extractCustomFields( $post_id, $trackNotification );
        cred_log( array($fields, $fieldsInfo, $taxonomies, $files, $removed_fields, $error_files) );

        // upload attachments
        $extra_files = array();
        if ( count( $error_files ) > 0 ) {
            $all_ok = false;
        } else {
            $all_ok = true;
            if ( $this->_disable_progress_bar )
                $all_ok = $formHelper->CRED_uploadAttachments( $post_id, $fields, $files, $extra_files, $trackNotification );
            else {
                $formHelper->CRED_uploadFeaturedImage( $post_id );
            }
        }
        cred_log( $all_ok );

        if ( $all_ok ) {
            add_filter( 'terms_clauses', array(&$this, 'terms_clauses') );
            add_filter( 'wpml_save_post_lang', array(&$this, 'wpml_save_post_lang') );
            //add_filter('wpml_save_post_trid_value',array(&$this,'wpml_save_post_trid_value'),10,2);
            // save everything
            $model = CRED_Loader::get( 'MODEL/Forms' );

            if ( !isset( $post->post_type ) || empty( $post->post_type ) )
                $post->post_type = $post_type;

            //cred-131#            
            $fields = CRED_StaticClass::cf_sanitize_values_on_save( $fields );

            if ( empty( $post->ID ) ) {
                $new_post_id = $model->addPost( $post, array('fields' => $fields, 'info' => $fieldsInfo, 'removed' => $removed_fields), $taxonomies );
            } else {
                $new_post_id = $model->updatePost( $post, array('fields' => $fields, 'info' => $fieldsInfo, 'removed' => $removed_fields), $taxonomies );
            }

            //cred_log(array('fields'=>$fields, 'info'=>$fieldsInfo, 'removed'=>$removed_fields));
            if ( is_int( $new_post_id ) && $new_post_id > 0 ) {
                if ( $this->_disable_progress_bar )
                    $formHelper->attachUploads( $new_post_id, $fields, $files, $extra_files );
                // save notification data (pre-formatted)
                if ( $trackNotification )
                    CRED_StaticClass::$out['notification_data'] = $formHelper->trackData( null, true );
                // for WooCommerce products only (update prices in products)
                if ( class_exists( 'Woocommerce' ) && 'product' == get_post_type( $new_post_id ) ) {
                    if ( isset( $fields['_regular_price'] ) && !isset( $fields['_price'] ) ) {
                        $regular_price = $fields['_regular_price'];
                        update_post_meta( $new_post_id, '_price', $regular_price );
                        $sale_price = get_post_meta( $new_post_id, '_sale_price', true );
                        // Update price if on sale
                        if ( $sale_price != '' ) {
                            $sale_price_dates_from = get_post_meta( $new_post_id, '_sale_price_dates_from', true );
                            $sale_price_dates_to = get_post_meta( $new_post_id, '_sale_price_dates_to', true );
                            if ( $sale_price_dates_to == '' && $sale_price_dates_to == '' )
                                update_post_meta( $new_post_id, '_price', $sale_price );
                            else if ( $sale_price_dates_from && strtotime( $sale_price_dates_from ) < strtotime( 'NOW', current_time( 'timestamp' ) ) )
                                update_post_meta( $new_post_id, '_price', $sale_price );
                            if ( $sale_price_dates_to && strtotime( $sale_price_dates_to ) < strtotime( 'NOW', current_time( 'timestamp' ) ) )
                                update_post_meta( $new_post_id, '_price', $regular_price );
                        }
                    }
                    else if ( isset( $fields['_price'] ) && !isset( $fields['_regular_price'] ) ) {
                        update_post_meta( $new_post_id, '_regular_price', $fields['_price'] );
                    }
                }

                // do custom actions on successful post save
                /* EMERSON: https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/185624661/comments
                  /*Add cred_save_data_form_ hook on CRED 1.3 */
                $form_slug = $form->getForm()->post_name;
                do_action( 'cred_save_data_form_' . $form_slug, $new_post_id, $thisform );
                do_action( 'cred_save_data_' . $form_id, $new_post_id, $thisform );
                do_action( 'cred_save_data', $new_post_id, $thisform );
            }
        } else {
            $WP_Error = new WP_Error();
            $WP_Error->add( 'upload', 'Error some required upload field failed.' );
            $new_post_id = $WP_Error;
        }
        // return saved post_id as result
        return $new_post_id;
    }

}
