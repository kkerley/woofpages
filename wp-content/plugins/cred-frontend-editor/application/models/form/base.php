<?php

abstract class CRED_Form_Base implements ICRED_Form_Base {

    protected $_form_id;
    protected $_form_count;
    public $_post_id;
    protected $_preview;
    protected $_post_type;
    //[CRED_FORMS_CUSTOM_POST_NAME|CRED_USER_FORMS_CUSTOM_POST_NAME]
    protected $_type_form;
    //post data    
    public $_postData;
    //form data
    public $_formData;
    //zebra form
    public $_zebraForm;
    //TODO: remove this
    public $_shortcodeParser;
    public $_formHelper;
    //form content
    public $_content;
    //flags
    protected $_disable_progress_bar;
    private static $_self_updated_form = false;

    public function __construct($_form_id, $_post_id = false, $_form_count = 0, $_preview = false) {
        cred_log( "__construct" );
        cred_log( array($_form_id, $_post_id, $_form_count, $_preview) );

        $this->_form_id = $_form_id;
        $this->_post_id = $_post_id;
        $this->_form_count = $_form_count;
        $this->_preview = $_preview;

        $this->_type_form = get_post_type( $_form_id );

        // shortcodes parsed by custom shortcode parser
        $this->_shortcodeParser = CRED_Loader::get( 'CLASS/Shortcode_Parser' );
        // various functions performed by custom form helper
        require_once CRED_ABSPATH . '/library/toolset/cred/embedded/classes/Form_Builder_Helper.php';
        $this->_formHelper = new CRED_Form_Builder_Helper( $this ); //CRED_Loader::get('CLASS/Form_Helper', $this);

        $this->_disable_progress_bar = version_compare( CRED_FE_VERSION, '1.3.6.2', '<=' );
        $this->_disable_progress_bar = apply_filters( 'cred_file_upload_disable_progress_bar', $this->_disable_progress_bar );
    }

    /**
     * print form
     * @global type $post
     * @global WP_User $authordata
     * @return boolean
     */
    public function print_form() {
        cred_log( "print_form" );

        add_filter( 'wp_revisions_to_keep', 'cred__return_zero', 10, 2 );

        $bypass_form = apply_filters( 'cred_bypass_process_form_' . $this->_form_id, false, $this->_form_id, $this->_post_id, $this->_preview );
        $bypass_form = apply_filters( 'cred_bypass_process_form', $bypass_form, $this->_form_id, $this->_post_id, $this->_preview );

        $form = new CRED_Form_Data( $this->_form_id, $this->_type_form, $this->_preview );
        $this->_formData = $form;

        if ( is_wp_error( $this->_formData ) ) {
            return false;
        }

        $formHelper = $this->_formHelper;
        $_fields = $this->_formData->getFields();
        $_form_type = $_fields['form_settings']->form['type'];
        $_post_type = $_fields['form_settings']->post['post_type'];

        $result = $this->_create_new_post( $this->_form_id, $_form_type, $this->_post_id, $_post_type );
        if ( is_wp_error( $result ) ) {
            return false;
        }
        cred_log( "_create_new_post " . $this->_post_id );

        // check if user has access to this form
        if ( !$this->_preview &&
                !$this->check_form_access( $_form_type, $this->_form_id, $this->_postData, $formHelper ) ) {
            cred_log( "formHelper error" );
            return $formHelper->error();
        }

        // set allowed file types      
        CRED_StaticClass::$_staticGlobal['MIMES'] = $formHelper->getAllowedMimeTypes();

        // get custom post fields
        $fields_settings = $formHelper->getFieldSettings( $_post_type );

        // instantiate Zebra Form
//        if (false !== $force_form_count)
//            $form_count = $force_form_count;
//        else
        $form_count = CRED_StaticClass::$_staticGlobal['COUNT'];

        // strip any unneeded parsms from current uri
        $actionUri = $formHelper->currentURI( array(
            '_tt' => time()       // add time get bypass cache
                ), array(
            '_success', // remove previous success get if set
            '_success_message'   // remove previous success get if set
                ) );

        $prg_form_id = $formHelper->createPrgID( $this->_form_id, $form_count );
        $my_form_id = $formHelper->createFormID( $this->_form_id, $form_count );

        $zebraForm = new CRED_Form_Rendering( $this->_form_id, $my_form_id, $_form_type, $this->_post_id, $actionUri, $this->_preview );
        $this->_zebraForm = $zebraForm;
        $this->_zebraForm->setFormHelper( $formHelper );
        $this->_zebraForm->setLanguage( CRED_StaticClass::$_staticGlobal['LOCALES'] );

        if ( $formHelper->isError( $this->_zebraForm ) ) {
            return $this->_zebraForm;
        }

        // all fine here        
        $this->_post_type = $_post_type;
        $this->_content = $this->_formData->getForm()->post_content;

        CRED_StaticClass::$out['fields'] = $fields_settings;
        CRED_StaticClass::$out['count'] = $form_count;
        CRED_StaticClass::$out['prg_id'] = $prg_form_id;

        //####################################################################################//

        $zebraForm->_formData = $this->_formData;

        $fields = $this->_formData->getFields();
        $zebraForm->extra_parameters = $_fields['extra'];

        //TODO:check if $this->_form_id == $form->getForm()->ID
        //$form_id = $form->getForm()->ID;
        $form_id = $this->_form_id;
        $form_type = $fields['form_settings']->form['type'];

        $form_use_ajax = (isset( $fields['form_settings']->form['use_ajax'] ) && $fields['form_settings']->form['use_ajax'] == 1) ? true : false;
        $is_ajax = (cred_is_ajax_call() && $form_use_ajax);

        $prg_id = CRED_StaticClass::$out['prg_id'];
        $form_count = CRED_StaticClass::$out['count'];
        //TODO: check this
        $post_type = $fields['form_settings']->post['post_type'];
        //$post_type = $this->_post_type;

        $this->set_authordata();


        // show display message from previous submit of same create form (P-R-G pattern)
        if (
                !$zebraForm->preview && /* 'edit'!=$form_type && (isset($_GET['action']) && $_GET['action'] == 'edit_translation' && 'translation'!=$form_type) && */
                isset( $_GET['_success_message'] ) &&
                $_GET['_success_message'] == $prg_id &&
                'message' == $_fields['form_settings']->form['action']
        ) {
            $zebraForm->is_submit_success = true;
            return $formHelper->displayMessage( $form );
        } else
            $zebraForm->is_submit_success = $this->isSubmitted();

        // no message to display if not submitted
        $message = false;

        $_curr_html_id = str_replace( "-", "_", CRED_StaticClass::$_current_prefix );
        $thisform = array(
            'id' => $form_id,
            'post_type' => $post_type,
            'form_type' => $form_type,
            'form_html_id' => '#' . $_curr_html_id . $prg_id
        );

        CRED_StaticClass::$_current_post_title = $this->_formData->getForm()->post_title;
        CRED_StaticClass::$_current_form_id = $form_id;

        /**
         * fix dates
         */
        $this->adodb_date_fix_date_and_time();

        $mime_types = wp_get_mime_types();
        CRED_StaticClass::$_allowed_mime_types = array_merge( $mime_types, array('xml' => 'text/xml') );
        CRED_StaticClass::$_allowed_mime_types = apply_filters( 'upload_mimes', CRED_StaticClass::$_allowed_mime_types );

        /**
         * sanitize input data
         */
        if ( !array_key_exists( 'post_fields', CRED_StaticClass::$out['fields'] ) ) {
            CRED_StaticClass::$out['fields']['post_fields'] = array();
        }

        /**
         * fixed Server side error messages should appear next to the field with the problem
         */
        $formHelper->checkFilePost( $zebraForm, CRED_StaticClass::$out['fields']['post_fields'] );
        if ( isset( CRED_StaticClass::$out['fields']['post_fields'] ) && isset( CRED_StaticClass::$out['form_fields_info'] ) )
            $formHelper->checkFilesType( CRED_StaticClass::$out['fields']['post_fields'], CRED_StaticClass::$out['form_fields_info'], $zebraForm, $error_files );

        CRED_StaticClass::$_reset_file_values = ($is_ajax && $form_type == 'new' && $_fields['form_settings']->form['action'] == 'form' && self::$_self_updated_form);
        cred_log( "_reset_file_values => " . CRED_StaticClass::$_reset_file_values );
        cred_log( "_self_updated_form => " . self::$_self_updated_form );

        $cloned = false;
        if ( isset( $_POST ) && !empty( $_POST ) ) {
            $cloned = true;
            $temp_post = $_POST;
        }

        if ( CRED_StaticClass::$_reset_file_values ) {
            foreach ( CRED_StaticClass::$out['fields']['post_fields'] as $k => $v ) {
                $fname = isset( $v['plugin_type_prefix'] ) ? $v['plugin_type_prefix'] . $k : $k;
                if ( isset( $_POST[$fname] ) ) {
                    unset( $_POST[$fname] ); // = array();
                }
            }
            foreach ( CRED_StaticClass::$out['fields']['taxonomies'] as $k => $v ) {
                if ( isset( $_POST[$k] ) ) {
                    unset( $_POST[$k] ); // = array();
                }
            }

            add_filter( 'toolset_filter_taxonomyhierarchical_terms', array('CRED_StaticClass', 'cred_empty_array'), 1 );
            add_filter( 'toolset_filter_taxonomy_terms', array('CRED_StaticClass', 'cred_empty_array'), 1 );
        }

        if ( $cloned ) {
            $_POST = $temp_post;
        }

        $this->build_form();

        $validate = (self::$_self_updated_form) ? true : $this->validate_form( $error_files );
        cred_log( "VALIDATE" );
        cred_log( $validate );

        if ( $form_use_ajax )
            $bypass_form = self::$_self_updated_form;

        if ( !empty( $_POST ) &&
                array_key_exists( CRED_StaticClass::PREFIX . 'form_id', $_POST ) &&
                $_POST[CRED_StaticClass::PREFIX . 'form_id'] != $form_id/* $form->getForm()->ID */ ) {
            $output = $this->render_form();
            $cred_response = new CRED_Generic_Response( $num_errors > 0 ? CRED_GENERIC_RESPONSE_RESULT_KO : CRED_GENERIC_RESPONSE_RESULT_OK, $output, $is_ajax, $thisform, $formHelper );
            return $cred_response->show();
        }

        //if (!$bypass_form && $_zebraForm->validate_form($post_id, $_zebraForm->form_properties['fields']))
        $num_errors = 0;
        if ( !$bypass_form && $validate ) {
            if ( !$zebraForm->preview ) {
                // save post data
                $bypass_save_form_data = apply_filters( 'cred_bypass_save_data_' . $form_id, false, $form_id, $this->_post_id, $thisform );
                $bypass_save_form_data = apply_filters( 'cred_bypass_save_data', $bypass_save_form_data, $form_id, $this->_post_id, $thisform );

                if ( !$bypass_save_form_data ) {
                    $model = CRED_Loader::get( 'MODEL/Forms' );
                    $attachedData = $model->getAttachedData( $this->_post_id );
                    $post_id = $this->save_form( $this->_post_id );
                    cred_log( "POST_ID" );
                    cred_log( $post_id );
                }

                if ( is_wp_error( $post_id ) ) {
                    cred_log( "ERROR $post_id" );
                    $num_errors++;
                    $zebraForm->add_field_message( $post_id->get_error_message(), 'Post Name' );
                } else {
                    $result = $this->_check_redirection( $post_id, $form_id, $form, $fields, $thisform, $formHelper, $is_ajax, $attachedData );
                    if ( $result != false ) {
                        return $result;
                    } else {
                        if ( isset( $_FILES ) && count( $_FILES ) > 0 ) {
                            // TODO check if this wp_list_pluck works with repetitive files... maybe in_array( array(1), $errors_on_files ) does the trick...
                            $errors_on_files = $food_names = wp_list_pluck( $_FILES, 'error' );
                            if ( in_array( 1, $errors_on_files ) || in_array( 2, $errors_on_files ) ) {
                                $zebraForm->add_field_message( $formHelper->getLocalisedMessage( 'no_data_submitted' ) );
                            } else {
                                $zebraForm->add_field_message( $formHelper->getLocalisedMessage( 'post_not_saved' ) );
                            }
                        } else {
                            // else just show the form again
                            $zebraForm->add_field_message( $formHelper->getLocalisedMessage( 'post_not_saved' ) );
                        }
                    }
                }
            } else {
                //$zebraForm->add_form_message('preview-form',__('Preview Form submitted','wp-cred'));
                $zebraForm->add_field_message( __( 'Preview Form submitted', 'wp-cred' ) );
            }
        } else if ( $this->isSubmitted() ) {
            $form_name = $formHelper->createFormID( $form_id, $form_count );
            $top_messages = isset( $zebraForm->top_messages[$form_name] ) ? $zebraForm->top_messages[$form_name] : array();
            $num_errors = count( $top_messages );
            cred_log( "num_errors " . $num_errors );
            if ( empty( $_POST ) ) {
                $num_errors++;
                $not_saved_message = $formHelper->getLocalisedMessage( 'no_data_submitted' );
                cred_log( "empty _POST" );
            } else {
                //$not_saved_message=$formHelper->getLocalisedMessage('post_not_saved'); // Replaced to new custom error message by Gen
                if ( count( $top_messages ) == 1 ) {
                    $tmpmsg = str_replace( "<br />%PROBLEMS_UL_LIST", "", $formHelper->getLocalisedMessage( 'post_not_saved_singular' ) );
                    $not_saved_message = $tmpmsg . "<br />%PROBLEMS_UL_LIST";
                } else {
                    $tmpmsg = str_replace( "<br />%PROBLEMS_UL_LIST", "", $formHelper->getLocalisedMessage( 'post_not_saved_plural' ) );
                    $not_saved_message = $tmpmsg . "<br />%PROBLEMS_UL_LIST";
                }

                $error_list = '<ul>';
                foreach ( $top_messages as $id_field => $text ) {
                    $error_list .= '<li>' . $text . '</li>';
                }
                $error_list .= '</ul>';
                $not_saved_message = str_replace( array('%PROBLEMS_UL_LIST', '%NN'), array($error_list, count( $top_messages )), $not_saved_message );
            }
            $not_saved_message = apply_filters( 'cred_data_not_saved_message_' . $form_id, $not_saved_message, $form_id, $this->_post_id, $this->_preview );
            $not_saved_message = apply_filters( 'cred_data_not_saved_message', $not_saved_message, $form_id, $this->_post_id, $this->_preview );
            //$zebraForm->add_form_message('data-saved', $not_saved_message);
            $zebraForm->add_field_message( $not_saved_message );
        }

        if (
                (
                isset( $_GET['_success'] ) &&
                $_GET['_success'] == $prg_id
                ) ||
                (
                $is_ajax &&
                self::$_self_updated_form
                )
        ) {
            if ( isset( $_GET['_target'] ) && is_numeric( $_GET['_target'] ) )
                $post_id = $_GET['_target'];

            $saved_message = $formHelper->getLocalisedMessage( 'post_saved' );

            if ( isset( $post_id ) && is_int( $post_id ) ) {
                // add success message from previous submit of same any form (P-R-G pattern)                
                $saved_message = apply_filters( 'cred_data_saved_message_' . $form_id, $saved_message, $form_id, $post_id, $this->_preview );
                $saved_message = apply_filters( 'cred_data_saved_message', $saved_message, $form_id, $post_id, $this->_preview );
            }
            //$zebraForm->add_form_message('data-saved', $saved_message);
            $zebraForm->add_success_message( $saved_message );
        }

        //TODO: FIX THIS removing false and try to fix
        if ( $validate &&
                !self::$_self_updated_form &&
                $is_ajax ) {
            self::$_self_updated_form = true;
            cred_log( "re-print form" );
            cred_log( array($this->_form_id, $this->_post_id, $this->_form_count, $this->_preview) );
            //$this->print_new_form($this->_form_id);
            $this->print_form();
        } else {
            $msgs = $zebraForm->getFieldsSuccessMessages();
            $msgs .= $zebraForm->getFieldsErrorMessages();
            $js = $zebraForm->getFieldsErrorMessagesJs();

            if ( false !== $message )
                $output = $message;
            else
                $output = $this->render_form( $msgs, $js );

            $cred_response = new CRED_Generic_Response( $num_errors > 0 ? CRED_GENERIC_RESPONSE_RESULT_KO : CRED_GENERIC_RESPONSE_RESULT_OK, $output, $is_ajax, $thisform, $formHelper );
            return $cred_response->show();
            //return $output;
        }
    }

    public function _check_redirection($post_id, $form_id, $form, $fields, $thisform, $formHelper, $is_ajax, $attachedData) {
        
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
     * build_form
     */
    public function build_form() {
        cred_log( "build_form" );
    }

    /**
     * render_form
     * @param type $msgs
     * @param type $js
     * @return type
     */
    public function render_form($msgs = "", $js = "") {
        cred_log( "render_form" );

        $shortcodeParser = $this->_shortcodeParser;
        $zebraForm = $this->_zebraForm;

        $shortcodeParser->remove_all_shortcodes();

        $zebraForm->render();
        // post content area might contain shortcodes, so return them raw by replacing with a dummy placeholder
        //By Gen, we use placeholder <!CRED_ERROR_MESSAGE!> in content for errors

        $this->_content = str_replace( CRED_StaticClass::FORM_TAG . '_' . $zebraForm->form_properties['name'] . '%', $zebraForm->_form_content, $this->_content ) . $js;
        $this->_content = str_replace( '<!CRED_ERROR_MESSAGE!>', $msgs, $this->_content );
        // parse old shortcode first (with dashes)
        $shortcodeParser->add_shortcode( 'cred-post-parent', array(&$this, 'cred_parent') );
        $this->_content = $shortcodeParser->do_shortcode( $this->_content );
        $shortcodeParser->remove_shortcode( 'cred-post-parent', array(&$this, 'cred_parent') );
        // parse new shortcode (with underscores)
        $shortcodeParser->add_shortcode( 'cred_post_parent', array(&$this, 'cred_parent') );
        $this->_content = $shortcodeParser->do_shortcode( $this->_content );
        $shortcodeParser->remove_shortcode( 'cred_post_parent', array(&$this, 'cred_parent') );

        return $this->_content;
    }

    /**
     * _create_new_post
     * @param type $_form_type
     * @return boolean
     */
    public function _create_new_post($_form_type, $form_type, $post_id, $post_type) {
        cred_log( "base_form _create_new_post " . $post_id );
        return $post_id;
    }

    public function save_form($post_id = null, $post_type = "") {
        cred_log( "base_form cred_save " . $post_id );
        return $post_id;
    }

    /**
     * getFieldSettings important function that fill $out with all post fields in order to render forms
     * @staticvar type $fields
     * @staticvar type $_post_type
     * @param type $post_type
     * @return type
     */
    public function getFieldSettings($post_type) {
        static $fields = null;
        static $_post_type = null;
        if ( null === $fields || $_post_type != $post_type ) {
            $_post_type = $post_type;
            if ( $post_type == 'user' ) {
                $ffm = CRED_Loader::get( 'MODEL/UserFields' );
                $fields = $ffm->getFields( false, '', '', true, array($this, 'getLocalisedMessage') );
            } else {
                $ffm = CRED_Loader::get( 'MODEL/Fields' );
                $fields = $ffm->getFields( $post_type, true, array($this, 'getLocalisedMessage') );
            }

            // in CRED 1.1 post_fields and custom_fields are different keys, merge them together to keep consistency

            if ( array_key_exists( 'post_fields', $fields ) ) {
                $fields['_post_fields'] = $fields['post_fields'];
            }
            if (
                    array_key_exists( 'custom_fields', $fields ) && is_array( $fields['custom_fields'] )
            ) {
                if ( isset( $fields['post_fields'] ) && is_array( $fields['post_fields'] ) ) {
                    $fields['post_fields'] = array_merge( $fields['post_fields'], $fields['custom_fields'] );
                } else {
                    $fields['post_fields'] = $fields['custom_fields'];
                }
            }
        }
        return $fields;
    }

    /**
     * createFormID
     * @param type $id
     * @param type $count
     * @return type
     */
    public function createFormID($id, $count) {
        return 'cred_form_' . $id . '_' . $count;
    }

    /**
     * createPrgID
     * @param type $id
     * @param type $count
     * @return type
     */
    public function createPrgID($id, $count) {
        return $id . '_' . $count;
    }

    /**
     * currentURI
     * @param type $replace_get
     * @param type $remove_get
     * @return string
     */
    public function currentURI($replace_get = array(), $remove_get = array()) {
        $request_uri = $_SERVER["REQUEST_URI"];
        if ( !empty( $replace_get ) ) {
            $request_uri = explode( '?', $request_uri, 2 );
            $request_uri = $request_uri[0];

            parse_str( $_SERVER['QUERY_STRING'], $get_params );
            if ( empty( $get_params ) )
                $get_params = array();

            foreach ( $replace_get as $key => $value ) {
                $get_params[$key] = $value;
            }
            if ( !empty( $remove_get ) ) {
                foreach ( $get_params as $key => $value ) {
                    if ( isset( $remove_get[$key] ) )
                        unset( $get_params[$key] );
                }
            }
            if ( !empty( $get_params ) )
                $request_uri.='?' . http_build_query( $get_params, '', '&' );
        }
        return $request_uri;
    }

    /**
     * validate_form
     * @param type $error_files
     * @return type
     */
    public function validate_form($error_files) {
        $form_validator = new CRED_Validator_Form( $this, $error_files );
        return $form_validator->validate();
    }

    /**
     * notify
     * @param type $post_id
     * @param type $attachedData
     */
    public function notify($post_id, $attachedData = null) {
        $form = &$this->_formData;
        $_fields = $form->getFields();

        // init notification manager if needed
        if (
                isset( $_fields['notification']->enable ) &&
                $_fields['notification']->enable &&
                !empty( $_fields['notification']->notifications )
        ) {
            // add extra plceholder codes
            add_filter( 'cred_subject_notification_codes', array(&$this, 'extraSubjectNotificationCodes'), 5, 3 );
            add_filter( 'cred_body_notification_codes', array(&$this, 'extraBodyNotificationCodes'), 5, 3 );

            CRED_Loader::load( 'CLASS/Notification_Manager' );
            if ( $form->getForm()->post_type == CRED_USER_FORMS_CUSTOM_POST_NAME )
                CRED_Notification_Manager::set_user_fields();
            // add the post to notification management
            CRED_Notification_Manager::add( $post_id, $form->getForm()->ID, $_fields['notification']->notifications );
            // send any notifications now if needed
            CRED_Notification_Manager::triggerNotifications( $post_id, array(
                'event' => 'form_submit',
                'form_id' => $form->getForm()->ID,
                'notification' => $_fields['notification']
                    ), $attachedData );

            // remove extra plceholder codes
            remove_filter( 'cred_subject_notification_codes', array(&$this, 'extraSubjectNotificationCodes'), 5, 3 );
            remove_filter( 'cred_body_notification_codes', array(&$this, 'extraBodyNotificationCodes'), 5, 3 );
        }
    }

    /**
     * wpml_save_post_lang
     * @global type $sitepress
     * @param type $lang
     * @return type
     */
    public function wpml_save_post_lang($lang) {
        global $sitepress;
        if ( isset( $sitepress ) ) {
            if ( empty( $_POST['icl_post_language'] ) ) {
                if ( isset( $_GET['lang'] ) ) {
                    $lang = $_GET['lang'];
                } else {
                    $lang = $sitepress->get_current_language();
                }
            }
        }
        return $lang;
    }

    /**
     * terms_clauses
     * @global type $sitepress
     * @param type $clauses
     * @return type
     */
    public function terms_clauses($clauses) {
        global $sitepress;
        if ( isset( $sitepress ) ) {
            if ( isset( $_GET['source_lang'] ) ) {
                $src_lang = $_GET['source_lang'];
            } else {
                $src_lang = $sitepress->get_current_language();
            }
            if ( isset( $_GET['lang'] ) ) {
                $lang = sanitize_text_field( $_GET['lang'] );
            } else {
                $lang = $src_lang;
            }
            $clauses['where'] = str_replace( "icl_t.language_code = '" . $src_lang . "'", "icl_t.language_code = '" . $lang . "'", $clauses['where'] );
        }
        return $clauses;
    }

    /**
     * isSubmitted
     * @return type
     */
    public function isSubmitted() {
        return $this->_zebraForm->isSubmitted();
    }

    /**
     * adodb_date_fix_date_and_time
     */
    private function adodb_date_fix_date_and_time() {
        if ( isset( $_POST ) && !empty( $_POST ) )
            foreach ( $_POST as $name => &$value ) {
                if ( $name == CRED_StaticClass::NONCE )
                    continue;
                if ( is_array( $value ) && isset( $value['datepicker'] ) ) {
                    if ( !function_exists( 'adodb_date' ) ) {
                        require_once WPTOOLSET_FORMS_ABSPATH . '/lib/adodb-time.inc.php';
                    }
                    $date_format = get_option( 'date_format' );
                    $date = $value['datepicker'];
                    $value['datetime'] = adodb_date( "Y-m-d", $date );
                    $value['hour'] = isset( $value['hour'] ) ? $value['hour'] : "00";
                    $value['minute'] = isset( $value['minute'] ) ? $value['minute'] : "00";
                    $value['timestamp'] = strtotime( $value['datetime'] . " " . $value['hour'] . ":" . $value['minute'] . ":00" );
                }
            }
    }

    //CALLBACKS

    public function extraSubjectNotificationCodes($codes, $form_id, $post_id) {
        $form = $this->_formData;
        if ( $form_id == $form->getForm()->ID ) {
            //$codes['%%POST_PARENT_TITLE%%'] = $this->cred_parent(array('get' => 'title'));
            $codes['%%POST_PARENT_TITLE%%'] = $this->cred_parent_for_notification( $post_id, array('get' => 'title') );
        }
        return $codes;
    }

    public function extraBodyNotificationCodes($codes, $form_id, $post_id) {
        cred_log( "extraBodyNotificationCodes" );
        cred_log( CRED_StaticClass::$out['notification_data'] );

        $form = $this->_formData;
        if ( $form_id == $form->getForm()->ID ) {
            $codes['%%FORM_DATA%%'] = isset( CRED_StaticClass::$out['notification_data'] ) ? CRED_StaticClass::$out['notification_data'] : '';
            //$codes['%%POST_PARENT_TITLE%%'] = $this->cred_parent(array('get' => 'title'));
            //$codes['%%POST_PARENT_LINK%%'] = $this->cred_parent(array('get' => 'url'));            
            $codes['%%POST_PARENT_TITLE%%'] = $this->cred_parent_for_notification( $post_id, array('get' => 'title') );
            $codes['%%POST_PARENT_LINK%%'] = $this->cred_parent_for_notification( $post_id, array('get' => 'url') );
        }
        return $codes;
    }

    public function cred_parent_for_notification($post_id, $atts) {
        extract( shortcode_atts( array(
            'post_type' => null,
            'get' => 'title'
                        ), $atts ) );

        $post_type = get_post_type( $post_id );
        cred_log( "############################# cred_parent_for_notification $post_id $post_type" );
        $parent_id = null;
        foreach ( CRED_StaticClass::$out['fields']['parents'] as $k => $v ) {
            if ( isset( $_REQUEST[$k] ) ) {
                $parent_id = $_REQUEST[$k];
                break;
            }
        }

        if ( $parent_id !== null ) {
            cred_log( $get );
            switch ($get) {
                case 'title':
                    return get_the_title( $parent_id );
                case 'url':
                    return get_permalink( $parent_id );
                case 'id':
                    return $parent_id;
                default:
                    return '';
            }
        }
        return '';
    }

}
