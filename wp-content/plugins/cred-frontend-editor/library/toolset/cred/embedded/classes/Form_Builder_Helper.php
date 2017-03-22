<?php

/**
 * Form Builder Helper Class
 */
class CRED_Form_Builder_Helper /* implements CRED_Friendly, CRED_FriendlyStatic */ {

    public static $_current_post_title;
    public static $_current_prefix;
    public static $_current_form_id;

    // CONSTANTS
    const MSG_PREFIX = 'Message_';                                 // Message prefix for WPML localization

    public $_formBuilder = null;
    // for delayed redirection, if needed
    private $_uri_ = '';
    private $_delay_ = 0;

    /*
     *   Implement Friendly Interface
     *
     */

//    private $____friend_token____ = null;
    //private static $_______class_______='CRED_Form_Builder_Helper';
    /*
     *   /END Implement Friendly Interface
     *
     */

    /* =============================== STATIC METHODS ======================================== */

    // load frontend assets on init
    public static function loadFrontendAssets() {
        
    }

    // unload frontend assets if no form rendered on page
    public static function unloadFrontendAssets() {
        //Print custom js/css on front-end	
        $custom_js_cache = wp_cache_get('cred_custom_js_cache');
        if (false !== $custom_js_cache) {
            echo "\n<script type='text/javascript' class='custom-js'>\n";
            echo html_entity_decode($custom_js_cache, ENT_QUOTES) . "\n";
            echo "</script>\n";
        }

        $custom_css_cache = wp_cache_get('cred_custom_css_cache');
        if (false !== $custom_css_cache) {
            echo "\n<style type='text/css'>\n";
            echo $custom_css_cache . "\n";
            echo "</style>\n";
        }
    }

    public static function makeCommentsClosed($open, $post_id) {
        return false;
    }

    public static function noComments($comments, $post_id) {
        return array();
    }

    public static function hideComments() {
        global $post, $wp_query;
        // hide comments
        if (isset($post)) {
            //global $_wp_post_type_features;
            remove_post_type_support($post->post_type, 'comments');
            remove_post_type_support($post->post_type, 'trackbacks');
            $post->comment_status = "closed";
            $post->ping_status = "closed";
            $post->comment_count = 0;
            $wp_query->comment_count = 0;
            $wp_query->comments = array();
            add_filter('comments_open', array('CRED_Form_Builder_Helper', 'makeCommentsClosed'), 1000, 2);
            add_filter('pings_open', array('CRED_Form_Builder_Helper', 'makeCommentsClosed'), 1000, 2);
            add_filter('comments_array', array('CRED_Form_Builder_Helper', 'noComments'), 1000, 2);
            // as a last resort, use the template hook
            //add_filter('comments_template', STYLESHEETPATH . $file );
        }
    }

    /* =============================== INSTANCE METHODS ======================================== */

    public function __construct($formBuilder) {
        $this->_formBuilder = $formBuilder;
    }

    // get current url under which this is executed
    public function currentURI($replace_get = array(), $remove_get = array()) {
        $request_uri = htmlspecialchars($_SERVER["REQUEST_URI"]);
        if (!empty($replace_get)) {
            $request_uri = explode('?', $request_uri, 2);
            $request_uri = $request_uri[0];

            parse_str($_SERVER['QUERY_STRING'], $get_params);
            if (empty($get_params))
                $get_params = array();

            foreach ($replace_get as $key => $value) {
                $get_params[$key] = $value;
            }
            if (!empty($remove_get)) {
                foreach ($get_params as $key => $value) {
                    if (isset($remove_get[$key]))
                        unset($get_params[$key]);
                }
            }
            if (!empty($get_params))
                $request_uri.='?' . http_build_query($get_params, '', '&');
        }
        return $request_uri;
    }

    public function getLocalisedPermalink($id, $type = null) {
        static $_cache = array();

        if (!isset($_cache[$id])) {
            /*
              WPML localised ID
              function icl_object_id($element_id, $element_type='post',
              $return_original_if_missing=false, $ulanguage_code=null)
             */
            if (function_exists('icl_object_id')) {
                if (null === $type)
                    $type = get_post_type($id);
                $loc_id = icl_object_id($id, $type, true);
            }
            else {
                $loc_id = $id;
            }
            $_cache[$id] = get_permalink($loc_id);
        }
        return $_cache[$id];
    }

    public function checkFormAccess($form_type, $form_id, $post = false) {
        cred_log(array($form_type, $form_id, $post));
        global $current_user;
        //get_currentuserinfo();
        wp_get_current_user();

        switch ($form_type) {
            case 'edit':
                if (!$post && $current_user->ID != 0)
                    return false;

                if (empty($post))
                    return false;

                cred_log('edit_own_posts_with_cred_' . $form_id);

                if (!current_user_can('edit_own_posts_with_cred_' . $form_id) &&
                        $current_user->ID == $post->post->post_author)
                    return false;

                cred_log('edit_other_posts_with_cred_' . $form_id);
                if (!current_user_can('edit_other_posts_with_cred_' . $form_id) &&
                        $current_user->ID != $post->post->post_author)
                    return false;
                break;
            case 'translation':
                $return = false;
                return apply_filters('cred_wpml_glue_check_user_privileges', $return);
                break;
            case 'new':
                cred_log('create_posts_with_cred_' . $form_id);
                if (!current_user_can('create_posts_with_cred_' . $form_id))
                    return false;
                break;
            default:
                return false;
                break;
        }
        return true;
    }

	public function checkUserFormAccess($form_type, $form_id, $user = false) {
		global $current_user;
		//get_currentuserinfo();
		wp_get_current_user();
		switch ($form_type) {
			case 'edit':
				if (!$user)
					return false;
				if (0 == $current_user->ID)
					return false;
				if ($current_user->ID == $user->post->ID) {
					$current_user_can = current_user_can('edit_own_user_with_cred_' . $form_id);
					if (is_multisite()) {
						return $current_user_can && is_user_member_of_blog($user->post->ID);
					}
					return $current_user_can;
				} else {
					$is_multisite = is_multisite();
					if ($is_multisite) {
						$super_admins = get_super_admins();
						$is_other_user_multisite_super_admin = (is_array($super_admins) && in_array($user->post->user_login, $super_admins));
						if ($is_other_user_multisite_super_admin)
							return false;
					}
					$current_user_can = defined('TACCESS_VERSION') && current_user_can('edit_other_users_with_cred_' . $form_id);
					if ($is_multisite) {
						return $current_user_can && is_user_member_of_blog($user->post->ID);
					}
					return $current_user_can;
				}
				break;
			case 'translation':
				$return = false;
				return apply_filters('cred_wpml_glue_check_user_privileges', $return);
				break;
			case 'new':
				if (!current_user_can('create_users_with_cred_' . $form_id))
					return false;
				break;
			default:
				return false;
				break;
		}
		return true;
	}

	public function error($msg = '') {
		return new WP_Error($msg);
	}

    public function isError($obj) {
        return is_wp_error($obj);
    }

    public function getError($obj) {
        if (is_wp_error($obj))
            return $obj->get_error_message($obj->get_error_code());
        return '';
    }

    /**
     * Deprecated
     * getAllowedExtensions
     * @staticvar type $extensions
     * @return type
     */
    public function getAllowedExtensions() {
        static $extensions = null;

        if (null == $extensions) {
            $extensions = array();
            $wp_mimes = get_allowed_mime_types(); // calls the upload_mimes filter itself, wp-includes/functions.php
            foreach ($wp_mimes as $exts => $mime) {
                $exts_a = explode('|', $exts);
                foreach ($exts_a as $single_ext) {
                    $extensions[] = $single_ext;
                }
            }
            $extensions = implode(',', $extensions);
            unset($wp_mimes);
        }
        return $extensions;
    }

    /**
     * Deprecated
     * getAllowedMimeTypes
     * @staticvar type $mimes
     * @return type
     */
    public function getAllowedMimeTypes() {
        static $mimes = null;

        if (null == $mimes) {
            $mimes = array();
            $wp_mimes = get_allowed_mime_types();
            foreach ($wp_mimes as $exts => $mime) {
                $exts_a = explode('|', $exts);
                foreach ($exts_a as $single_ext) {
                    $mimes[$single_ext] = $mime;
                }
            }
            //$mimes=implode(',',$mimes);
            unset($wp_mimes);
        }
        return $mimes;
    }

    public function getFieldSettings($post_type) {
        cred_log("getFieldSettings " . $post_type);
        static $fields = null;
        static $_post_type = null;

        if (null === $fields || $_post_type != $post_type) {
            $_post_type = $post_type;
            if ($post_type == 'user') {
                $ffm = CRED_Loader::get('MODEL/UserFields');
                $fields = $ffm->getFields(false, '', '', true, array($this, 'getLocalisedMessage'));
            } else {
                $ffm = CRED_Loader::get('MODEL/Fields');
                $fields = $ffm->getFields($post_type, true, array($this, 'getLocalisedMessage'));
            }

            // in CRED 1.1 post_fields and custom_fields are different keys, merge them together to keep consistency
            if (array_key_exists('post_fields', $fields)) {
                $fields['_post_fields'] = $fields['post_fields'];
            }
            if (
                    array_key_exists('custom_fields', $fields) && is_array($fields['custom_fields'])
            ) {
                if (isset($fields['post_fields']) && is_array($fields['post_fields'])) {
                    $fields['post_fields'] = array_merge($fields['post_fields'], $fields['custom_fields']);
                } else {
                    $fields['post_fields'] = $fields['custom_fields'];
                }
            }
        }
        return $fields;
    }

    public function createFormID($id, $count) {
        return 'cred_form_' . $id . '_' . $count;
    }

    public function createPrgID($id, $count) {
        return $id . '_' . $count;
    }
    
    /**
     * @deprecated function since 1.8.6 moved on CRED_Generic_Response
     * @param string $uri
     * @param string $headers
     */
    public function redirect($uri, $headers = array()) {
        if (!headers_sent()) {
            // additional headers
            if (!empty($headers)) {
                foreach ($headers as $header)
                    header("$header");
            }
            // redirect
            header("Location: $uri");
            exit();
        } else {
            echo sprintf("<script>jQuery(document).ready(function() { jQuery('.submit').hide();  } );</script><script type='text/javascript'>document.location='%s';</script>", $uri);
            exit();
        }
    }

    /**
     * @deprecated function since 1.8.6 moved on CRED_Generic_Response
     * @param string $uri
     * @return string
     */
    public function redirectFromAjax($uri) {
        return sprintf("<script type='text/javascript'>document.location='%s';</script>", $uri);
    }

    /**
     * @deprecated function since 1.8.6 moved on CRED_Generic_Response
     * @param string $uri
     * @param int $delay
     * @return string
     */
    public function redirectDelayed($uri, $delay) {
        $delay = intval($delay);
        if ($delay <= 0) {
            $this->redirect($uri);
            return;
        }
        if (!headers_sent()) {
            $this->_uri_ = $uri;
            $this->_delay_ = $delay;
            add_action('wp_head', array(&$this, 'doDelayedRedirect'), 1000);
        } else {
            return sprintf("<script>jQuery(document).ready(function() { jQuery('.submit').hide();  } );</script><script type='text/javascript'>setTimeout(function(){document.location='%s';},%d);</script>", $uri, $delay * 1000);
        }
    }

    /**
     * @deprecated function since 1.8.6 moved on CRED_Generic_Response
     * @param string $uri
     * @param int $delay
     * @return string
     */
    public function redirectDelayedFromAjax($uri, $delay) {
        $delay = intval($delay);
        if ($delay <= 0) {
            return $this->redirectFromAjax($uri);
        }
        return sprintf("<script type='text/javascript'>setTimeout(function(){document.location='%s';},%d);</script>", $uri, $delay * 1000);
    }

    /**
     * @deprecated function since 1.8.6 moved on CRED_Generic_Response
     */
    public function doDelayedRedirect() {
        echo sprintf("<script>jQuery(document).ready(function() { jQuery('.submit').hide();  } );</script><meta http-equiv='refresh' content='%d;url=%s'>", $this->_delay_, $this->_uri_);
    }

    public function displayMessage($form) {
        $_fields = $form->getFields();
        // apply some rich filters
        $mess = CRED_Helper::renderWithBasicFilters(
                        cred_translate(
                                'Display Message: ' . $form->getForm()->post_title, $_fields['form_settings']->form['action_message'], 'cred-form-' . $form->getForm()->post_title . '-' . $form->getForm()->ID
                        )
        );

        $succ_mess = $_GET['_success_message'];
        ob_start();
        ?>
        <div id="cred_form_<?php echo $succ_mess; ?>"><?php echo $mess; ?></div>
        <?php
        $content = ob_get_clean();
        return $content;

        /* return  do_shortcode(
          cred_translate(
          'Display Message: '.$form->form->post_title,
          $form->fields['form_settings']->form['action_message'],
          'cred-form-'.$form->form->post_title.'-'.$form->form->ID
          )
          ); */
    }

    public function getRecaptchaSettings($settings) {
        if (!$settings) {
            $sm = CRED_Loader::get('MODEL/Settings');
            $gen_setts = $sm->getSettings();
            if (
                    isset($gen_setts['recaptcha']['public_key']) &&
                    isset($gen_setts['recaptcha']['private_key']) &&
                    !empty($gen_setts['recaptcha']['public_key']) &&
                    !empty($gen_setts['recaptcha']['private_key'])
            )
                $settings = $gen_setts['recaptcha'];
        }
        return $settings;
    }

    public function getLocalisedMessage($id) {
        static $messages = null;
        static $formData = null;
        $formData = $this->_formBuilder->_formData; //$this->friendGet($this->_formBuilder, '_formData');
        $fields = $formData->getFields();
        $messages = $fields['extra']->messages;
        $messages['cred_message_no_recaptcha_keys'] = "no recaptcha keys found";

        //Commented due to wrong $formData passed when more than one CRED form is present.
        //Commented by Ahmed Hussein
        /*
          if (null == $formData) {
          $formData = $this->friendGet($this->_formBuilder, '_formData');
          $fields = $formData->getFields();
          $messages = $fields['extra']->messages;
          $messages['cred_message_no_recaptcha_keys'] = "no recaptcha keys found";
          }
         */

        $id = 'cred_message_' . $id;
        if (!isset($messages[$id]))
            return '';
        return cred_translate(
                self::MSG_PREFIX . $id, $messages[$id], 'cred-form-' . $formData->getForm()->post_title . '-' . $formData->getForm()->ID
        );
    }

    // extra sanitization methods to be used by form framework
    /* public function esc_js($data) {return esc_js($data);}
      public function esc_attr($data) {return esc_attr($data);}
      public function esc_textarea($data) {return esc_textarea($data);}
      public function esc_html($data) {return esc_html($data);}
      public function esc_url($data) {return esc_url($data);}
      public function esc_url_raw($data) {return esc_url_raw($data);}
      public function esc_sql($data) {return esc_sql($data);} */

    // utility methods
    public function getUserRolesByID($user_id) {
        $user = get_userdata($user_id);
        return empty($user) ? array() : $user->roles;
    }

    public function CRED_extractPostFields($post_id, $track = false) {
        global $user_ID;
        // reference to the form submission method
        global ${'_' . CRED_StaticClass::METHOD
        };
        $method = & ${'_' . CRED_StaticClass::METHOD};

        // get refs here
        $form = $this->_formBuilder->_formData;

        $form_id = $form->getForm()->ID;
        $zebraForm = $this->_formBuilder->_zebraForm;
        $_fields = $form->getFields();
        $form_type = $_fields['form_settings']->form['type'];

        $p = get_post($post_id);

        //Fix Problem with using 2 forms in the same page - saving to the wrong post type
        $post_type = $_fields['form_settings']->post['post_type'];
        //$post_type= isset($this->(isset($p)) ? get_post($post_id)->post_type : '';
        //###############################################################################

        $fields = CRED_StaticClass::$out['fields'];
        $form_fields = CRED_StaticClass::$out['form_fields'];

        // extract main post fields
        $post = new stdClass;
        // ID
        $post->ID = $post_id;
        // author
        if ('new' == $form_type)
            $post->post_author = $user_ID;
        // title
        if (
                array_key_exists('post_title', $form_fields) &&
                array_key_exists('post_title', $method)
        ) {
            $post->post_title = stripslashes($method['post_title']);
            unset($method['post_title']);
        }
        // content
        if (
                array_key_exists('post_content', $form_fields) &&
                array_key_exists('post_content', $method)
        ) {
            $post->post_content = stripslashes($method['post_content']);
            unset($method['post_content']);
        }
        // excerpt
        if (
                array_key_exists('post_excerpt', $form_fields) &&
                array_key_exists('post_excerpt', $method)
        ) {
            $post->post_excerpt = stripslashes($method['post_excerpt']);
            unset($method['post_excerpt']);
        }
        // parent
        if (
                array_key_exists('post_parent', $form_fields) &&
                array_key_exists('post_parent', $method) &&
                isset($fields['parents']) && isset($fields['parents']['post_parent']) &&
                intval($method['post_parent']) >= 0
        ) {
            $post->post_parent = intval($method['post_parent']);
            unset($method['post_parent']);
        }

        // type
        $post->post_type = $post_type;
        // status
        if (
                !isset($_fields['form_settings']->post['post_status']) ||
                !in_array($_fields['form_settings']->post['post_status'], array('draft', 'private', 'pending', 'publish', 'original'))
        )
            $_fields['form_settings']->post['post_status'] = 'draft';

        if (
                isset($_fields['form_settings']->post['post_status']) &&
                'original' == $_fields['form_settings']->post['post_status'] &&
                'edit' != $form_type
        )
            $_fields['form_settings']->post['post_status'] = 'draft';

        if (
                'original' != $_fields['form_settings']->post['post_status']
        )
            $post->post_status = (isset($_fields['form_settings']->post['post_status'])) ? $_fields['form_settings']->post['post_status'] : 'draft';

        if ($track) {
            // track the data, eg for notifications
            if (isset($post->post_title))
                $this->trackData(array('Post Title' => $post->post_title));
            if (isset($post->post_content))
                $this->trackData(array('Post Content' => $post->post_content));
            if (isset($post->post_excerpt))
                $this->trackData(array('Post Excerpt' => $post->post_excerpt));
        }

        // return them
        return $post;
    }

    public function CRED_extractUserFields($user_id, $user_role, $track = false) {
        global $user_ID;
        // reference to the form submission method
        global ${'_' . CRED_StaticClass::METHOD};
        $method = & ${'_' . CRED_StaticClass::METHOD};

        // get refs here
        $form = $this->_formBuilder->_formData;
        $form_id = $form->getForm()->ID;
        $_fields = $form->getFields();
        $form_type = $_fields['form_settings']->form['type'];

        $autogenerate_user = (boolean) $_fields['form_settings']->form['autogenerate_username_scaffold'] ? true : false;
        $autogenerate_nick = (boolean) $_fields['form_settings']->form['autogenerate_nickname_scaffold'] ? true : false;
        $autogenerate_pass = (boolean) $_fields['form_settings']->form['autogenerate_password_scaffold'] ? true : false;

        $u = get_user_by('ID', $user_id);

        //user
        $post_type = $_fields['form_settings']->post['post_type'];

        $fields = CRED_StaticClass::$out['fields'];
        $form_fields = $fields['form_fields'];

        // extract main post fields
        $user = array();
        $user['ID'] = $user_id;
        $user['user_role'] = $user_role;
        foreach ($form_fields as $name => $field) {
            if (array_key_exists($name, $method)) {
                $user[$name] = stripslashes($method[$name]);
            }
        }

        if ($form_type == 'new' && isset($_POST['user_pass'])) {
            CRED_StaticClass::$_password_generated = $_POST['user_pass'];
        }

        if ($form_type == 'new' &&
                isset($user['user_email']) &&
                (
                ($autogenerate_user || !isset($_POST['user_login'])) ||
                ($autogenerate_nick || !isset($_POST['nickname'])) ||
                ($autogenerate_pass || !isset($_POST['user_pass'])))
        ) {

            $settings_model = CRED_Loader::get('MODEL/Settings');
            $settings = $settings_model->getSettings();

            if ($autogenerate_pass || !isset($_POST['user_pass'])) {
                $password_generated = wp_generate_password(10, false);
                CRED_StaticClass::$_password_generated = $password_generated;
                $user["user_pass"] = $password_generated;
            }

            $username_generated = CRED_StaticClass::generateUsername($user['user_email']);

            if (!isset($_POST['nickname'])) {
                if ($autogenerate_nick) {
                    $nick_generated = $username_generated;
                    CRED_StaticClass::$_nickname_generated = $nick_generated;
                    $user["nickname"] = $nick_generated;
                } else {
                    $user["nickname"] = $user['user_email'];
                }
            }

            //user_login is mandatory
            if (!isset($_POST['user_login'])) {
                if ($autogenerate_user) {
                    CRED_StaticClass::$_username_generated = $username_generated;
                    $user["user_login"] = $username_generated;
                } else {
                    $user["user_login"] = $user['user_email'];
                }
            }
        }

        if ($track) {
            // track the data, eg for notifications
            if (isset($user['name']))
                $this->trackData(array('name' => $user['name']));
        }

        // return them
        return $user;
    }

    /**
     * Check if a file has a expected filetype
     * @param type $filetype
     * @param type $expected_filetypes
     * @return type
     */
    private function is_correct_filetype($filename, $filetype, $expected_filetypes) {
        $filetypes = array();
        $filetypes['audio'] = array('mp3|m4a|m4b' => 'audio/mpeg',
            'ra|ram' => 'audio/x-realaudio',
            'wav' => 'audio/wav',
            'ogg|oga' => 'audio/ogg',
            'mid|midi' => 'audio/midi',
            'wma' => 'audio/x-ms-wma',
            'wax' => 'audio/x-ms-wax',
            'mka' => 'audio/x-matroska');
        $filetypes['audio'] = apply_filters('audio_upload_mimes', $filetypes['audio']);
        $filetypes['video'] = array('asf|asx' => 'video/x-ms-asf',
            'wmv' => 'video/x-ms-wmv',
            'wmx' => 'video/x-ms-wmx',
            'wm' => 'video/x-ms-wm',
            'avi' => 'video/avi',
            'divx' => 'video/divx',
            'flv' => 'video/x-flv',
            'mov|qt' => 'video/quicktime',
            'mpeg|mpg|mpe' => 'video/mpeg',
            'mp4|m4v' => 'video/mp4',
            'ogv' => 'video/ogg',
            'webm' => 'video/webm',
            'mkv' => 'video/x-matroska',
            '3gp|3gpp' => 'video/3gpp', // Can also be audio
            '3g2|3gp2' => 'video/3gpp2');
        $filetypes['video'] = apply_filters('video_upload_mimes', $filetypes['video']);
        $filetypes['image'] = array('jpg|jpeg|jpe' => 'image/jpeg',
            'gif' => 'image/gif',
            'png' => 'image/png',
            'bmp' => 'image/bmp',
            'tif|tiff' => 'image/tiff',
            'ico' => 'image/x-icon');
        $filetypes['image'] = apply_filters('image_upload_mimes', $filetypes['image']);

        $filetypes['file'] = array();
        $filetypes['file'] = CRED_StaticClass::$_allowed_mime_types;
        $filetypes['file'] = apply_filters('file_upload_mimes', $filetypes['file']);

        CRED_StaticClass::$_allowed_mime_types = $filetypes['file'];

        add_filter('upload_mimes', array('CRED_StaticClass', 'cred__add_custom_mime_types'));

        $filename_to_check = "";

        if (is_array($filename)) {
            if (isset($filename[0]) && is_string($filename[0])) {
                $filename_to_check = sanitize_file_name($filename[0]);
            }
        } else {
            $filename_to_check = sanitize_file_name($filename);
        }

        $ret = wp_check_filetype($filename_to_check, CRED_StaticClass::$_allowed_mime_types);

        return !empty($ret['ext']);

//        $arr_filetypes_types = array_values($filetypes[$expected_filetypes]);
//        $arr_filetypes_exts = array_keys($filetypes[$expected_filetypes]);
//
//        $arr = @explode("/", $filetype);
//        $isok = (isset($arr) && count($arr) >= 2) ? (in_array($arr[1], $arr_filetypes_exts) || in_array($filetype, $arr_filetypes_types)) : false;
//        return $isok;
    }

    /**
     * 
     * @param type $fields
     */
    public function checkSomeFields($fields) {
        
    }

    /**
     * checkFilePost
     */
    public function checkFilePost($zebraForm, $fields) {
        global ${'_' . CRED_StaticClass::METHOD};
        $method = & ${'_' . CRED_StaticClass::METHOD};
        //if (!isset($_FILES)) return;
        foreach ($_FILES as $k => $v) {
            $fk = str_replace("wpcf-", "", $k);
            // TODO Maybe worth is to add error messages based on cases
            // http://www.php.net/manual/en/features.file-upload.errors.php
            if (!is_array($v['name'])) {
                // This means this is a single file-related field
                if (isset($v['error'])) {
                    if ($v['error'] == 0) {
                        $method[$k] = $v['name'];
                    } else if ($v['error'] == 1 || $v['error'] == 2) {
                        $error_files[] = $v['name'];
                        $zebraForm->add_field_message(__('File Error Code: ', 'wp-cred') . $v['error'] . ', ' . __('file too big ', 'wp-cred'), $v['name']);
                        $zebraForm->add_top_message(__('File Error Code: ', 'wp-cred') . $v['error'] . ', ' . __('file too big ', 'wp-cred'), $v['name']);
                    } else {
                        if (isset($fields[$fk]['data']['validate']['required']['active']) &&
                                $fields[$fk]['data']['validate']['required']['active'] == 1 &&
                                $v['error'] == 4
                        ) {
                            $zebraForm->add_field_message(__($fields[$fk]['name'] . ' Field is required', 'wp-cred'), $k);
                        }
                    }
                }
            } else {
                // This means this is a repetitive file-related field
                // Although it can be passed just one value, it is always posted as an array
                // We need to be careful because we might be posting also data for existing field values!
                foreach ($v['name'] as $key => $value) {
                    if (isset($v ['error'][$key])) {
                        if ($v['error'][$key] == 0) {
                            if (isset($method[$k])) {
                                if (!is_array($method[$k])) {
                                    $method[$k] = array($method[$k]);
                                }
                                if (isset($method[$k][$key])) {
                                    $method[$k][] = $v['name'][$key];
                                } else {
                                    $method[$k][$key] = $v['name'][$key];
                                }
                            } else {
                                $method[$k] = array($key => $v['name'][$key]);
                            }
                        } else if ($v['error'][$key] == 1 || $v['error'][$key] == 2) {
                            $error_files[] = $v['name'][$key];
                            $zebraForm->add_field_message(__('File Error Code: ', 'wp-cred') . $v['error'][$key] . ', ' . __('file too big ', 'wp-cred') . ' (' . __('file', 'wp-cred') . ' ' . $key . ')', $v['name'][$key]);
                            $zebraForm->add_top_message(__('File Error Code: ', 'wp-cred') . $v['error'][$key] . ', ' . __('file too big ', 'wp-cred') . ' (' . __('file', 'wp-cred') . ' ' . $key . ')', $v['name'][$key]);
                        } else {
                            if (isset($fields[$fk]['data']['validate']['required']['active']) &&
                                    $fields[$fk]['data']['validate']['required']['active'] == 1 &&
                                    $v['error'][$key] == 4
                            ) {
                                $zebraForm->add_field_message(__($fields[$fk]['name'] . ' Field is required', 'wp-cred'), $k);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Function used to check if files uploaded have correct type field
     * @param type $_fields
     * @param type $_form_fields_info
     * @param type $zebraForm
     * @param type $error_files
     */
    public function checkFilesType($_fields, $_form_fields_info, &$zebraForm, &$error_files) {
        if (!isset($_fields))
            return;
        //Fix upload filetypes not repetitive one      

        foreach ($_fields as $key => $field) {
            if (
                    ('audio' == $field['type'] ||
                    'video' == $field['type'] ||
                    'file' == $field['type'] ||
                    'image' == $field['type'])
            ) {
                $mykey = isset($field['plugin_type_prefix']) ? $field['plugin_type_prefix'] . $key : $key;
                if (isset($_form_fields_info[$key]) &&
                        isset($_form_fields_info[$key]['repetitive']) &&
                        $_form_fields_info[$key]['repetitive']) {
                    if (isset($_FILES[$mykey])) {
                        $rep_files_array = array();
                        $n = 0;
                        foreach ($_FILES[$mykey]['name'] as $n => $fname) {
                            if (empty($fname))
                                continue;
                            if (!isset($rep_files_array[$n]))
                                $rep_files_array[$n] = array();
                            $rep_files_array[$n]['name'] = $fname;
                            $n++;
                        }

                        $n = 0;
                        foreach ($_FILES[$mykey]['type'] as $n => $ftype) {
                            if (empty($ftype))
                                continue;
                            $rep_files_array[$n]['type'] = $ftype;
                            $n++;
                        }
                        foreach ($rep_files_array as $n => $cfile) {
                            if (!empty($cfile['name']) &&
                                    !$this->is_correct_filetype($cfile['name'], $cfile['type'], $field['type'])) {
                                $error_files[] = $mykey;
                                $zebraForm->add_field_message($field['name'] . ' ' . __("File Type Error", 'wp-cred'), $mykey);
                                $zebraForm->add_top_message($field['name'] . ' ' . __("File Type Error", 'wp-cred'), $mykey);
                                continue;
                            }
                        }
                        unset($rep_files_array);
                    }
                } else {
                    if (isset($_FILES[$mykey]) &&
                            !empty($_FILES[$mykey]['type']) &&
                            (isset($_FILES[$mykey]['error'][0]) && $_FILES[$mykey]['error'][0] != 4) &&
                            !$this->is_correct_filetype($_FILES[$mykey]['name'], $_FILES[$mykey]['type'], $field['type'])
                    ) {
                        $error_files[] = $mykey;
                        $zebraForm->add_field_message($field['name'] . ' ' . __("File Type Error", 'wp-cred'), $mykey);
                        $zebraForm->add_top_message($field['name'] . ' ' . __("File Type Error", 'wp-cred'), $mykey);
                    }
                }
            }
        }
    }

    /**
     * CRED_extractCustomFields
     * @global type $user_ID
     * @param type $post_id
     * @param type $track
     * @return type
     */
    public function CRED_extractCustomFields($post_id, $track = false) {
        cred_log("CRED_extractCustomFields");

        global $user_ID;
        // reference to the form submission method
        global ${'_' . CRED_StaticClass::METHOD};
        $method = & ${'_' . CRED_StaticClass::METHOD};

        $error_files = array();

        $form = $this->_formBuilder->_formData;

        $form_id = $form->getForm()->ID;
        $form_fields = $form->getFields();
        $form_type = $form_fields['form_settings']->form['type'];
        $post_type = $form_fields['form_settings']->post['post_type'];

        $_fields = CRED_StaticClass::$out['fields'];
        $_form_fields = CRED_StaticClass::$out['form_fields'];
        $_form_fields_info = CRED_StaticClass::$out['form_fields_info'];
        $zebraForm = $this->_formBuilder->_zebraForm;

        // custom fields
        $fields = array();
        $removed_fields = array();
        // taxonomies
        $taxonomies = array('flat' => array(), 'hierarchical' => array());
        $fieldsInfo = array();
        // files, require extra care to upload correctly
        $files = array();

        if (count($error_files) > 0) {
            // Bail out early if there are errors when uploading files
            return array($fields, $fieldsInfo, $taxonomies, $files, $removed_fields, $error_files);
        }

        foreach ($_fields['post_fields'] as $key => $field) {
            $field_label = $field['name'];
            $done_data = false;
            //$zebraForm->add_field_message( 'AAA ', $post_id );
            // use the key as was rendered (with potential prefix)
            $key11 = $key;
            if (isset($field['plugin_type_prefix']))
                $key = $field['plugin_type_prefix'] . $key;

            // if this field was not rendered in this specific form, bypass it
            if (!array_key_exists($key11, $_form_fields)) {
                cred_log($key11 . " does not exists");
                continue;
            }

            // if this field was discarded due to some conditional logic, bypass it
            //if (isset($zebraForm->controls) && $zebraForm->controls[$_form_fields[$key11][0]]->isDiscarded())  continue;

            $fieldsInfo[$key] = array('save_single' => false);

            if (
                    ('audio' == $field['type'] ||
                    'video' == $field['type'] ||
                    'file' == $field['type'] ||
                    'image' == $field['type'])
            ) {
                if (
                        !array_key_exists($key, $method)
                ) {
                    // remove the fields
                    $removed_fields[] = $key;
                    unset($fieldsInfo[$key]);
                } else/* if (is_array($method[$key])) */ {
                    $fields[$key] = $method[$key];
                }
            }

            if (
                    'checkboxes' == $field['type'] &&
                    isset($field['data']['save_empty']) &&
                    'yes' == $field['data']['save_empty'] &&
                    !array_key_exists($key, $method)
            ) {
                $values = array();
                foreach ($field['data']['options'] as $optionkey => $optiondata) {
                    $values[$optionkey] = '0';
                }

                // let model serialize once, fix Types-CRED mapping issue with checkboxes
                $fieldsInfo[$key]['save_single'] = true;
                $fields[$key] = $values;
            } elseif (
                    'checkboxes' == $field['type'] &&
                    (!isset($field['data']['save_empty']) ||
                    'yes' != $field['data']['save_empty']) &&
                    !array_key_exists($key, $method)
            ) {
                // remove the fields
                $removed_fields[] = $key;
                unset($fieldsInfo[$key]);
            } elseif (
                    'checkbox' == $field['type'] &&
                    isset($field['data']['save_empty']) &&
                    'yes' == $field['data']['save_empty'] &&
                    !array_key_exists($key, $method)
            ) {
                $fields[$key] = '0';
            } elseif (
                    'checkbox' == $field['type'] &&
                    (!isset($field['data']['save_empty']) ||
                    'yes' != $field['data']['save_empty']) &&
                    !array_key_exists($key, $method)
            ) {
                // remove the fields
                $removed_fields[] = $key;
                unset($fieldsInfo[$key]);
            } elseif (array_key_exists($key, $method)) {
                // normalize repetitive values out  of sequence
                if ($_form_fields_info[$key11]['repetitive'] || 'multiselect' == $_form_fields_info[$key11]['type']) {
                    if (is_array($method[$key])) {
                        $values = array_values($method[$key]);
                    } else {
                        $aux_value_array = array($method[$key]);
                        $values = array_values($aux_value_array);
                    }
                } else {
                    $values = $method[$key];
                }

                if ('audio' == $field['type'] ||
                        'video' == $field['type'] ||
                        'file' == $field['type'] ||
                        'image' == $field['type']) {
                    //TODO check this
                    if (isset($_FILES) && !empty($_FILES[$key])) {
                        $files[$key] = $zebraForm->getFileData($key, $_FILES[$key]); //$zebraForm->controls[$key];//$zebraForm->controls[$_form_fields[$key11][0]]->get_values();
                        $files[$key]['name_orig'] = $key11;
                        $files[$key]['label'] = $field['name'];
                        $files[$key]['repetitive'] = $_form_fields_info[$key11]['repetitive'];
                    }
                } elseif ('textarea' == $field['type'] ||
                        'wysiwyg' == $field['type']) {
                    // stripslashes for textarea, wysiwyg fields
                    if (is_array($values))
                        $values = array_map('stripslashes', $values);
                    else
                        $values = stripslashes($values);
                }
                elseif ('textfield' == $field['type'] ||
                        'text' == $field['type']) {
                    // stripslashes for text fields
                    if (is_array($values))
                        $values = array_map('stripslashes', $values);
                    else
                        $values = stripslashes($values);
                }

                // track form data for notification mail
                if ($track) {
                    $tmp_data = null;
                    if ('checkbox' == $field['type']) {
                        if ('db' == $field['data']['display'])
                            $tmp_data = $values;
                        else
                            $tmp_data = $field['data']['display_value_selected'];
                    }
                    elseif ('radio' == $field['type'] || 'select' == $field['type']) {
                        //$tmp_data = $field['data']['options'][$values]['title'];
                        foreach ($field['data']['options'] as $_key => $_val) {
                            if (isset($_val['value']) && $_val['value'] == $values) {
                                $tmp_data = $_val['title'];
                            }
                        }
                    } elseif ('checkboxes' == $field['type'] || 'multiselect' == $field['type']) {
                        $tmp_data = array();
                        foreach ($values as $tmp_val)
                            $tmp_data[] = $field['data']['options'][$tmp_val]['title'];
                        // $tmp_data=implode(', ',$tmp_data);

                        unset($tmp_val);
                    }
                    if (isset($tmp_data)) {
                        $this->trackData(array($field_label => $tmp_data));
                        $done_data = true;
                    }
                }

                if ('checkboxes' == $field['type'] || 'multiselect' == $field['type']) {
                    if (!is_array($values)) {
                        $values = array($values);
                    }
                    $result = array();
                    foreach ($field['data']['options'] as $optionkey => $optiondata) {
                        if (in_array($optionkey, $values)) {
                            if (array_key_exists('set_value', $optiondata) && isset($optiondata['set_value'])) {
                                $result[$optionkey] = array($optiondata['set_value']);
                            } elseif ('multiselect' == $field['type']) {
                                $result[$optionkey] = array($optionkey);
                            }
                        }
                    }

                    $values = $result;

                    $fieldsInfo[$key]['save_single'] = true;
                } elseif ('radio' == $field['type'] ||
                        'select' == $field['type']) {
                    
                } elseif ('date' == $field['type']) {

                    /*
                     * Single/repetitive values for Date are not set right,
                     * because CRED used Date as string - not array
                     *
                     * NOTE: There is no general method in CRED to check if repetitive?
                     * Types have types_is_repetitive() function.
                     * If it's types fiels - repetitive flag is in
                     * $field['data']['repetitive']
                     */
                    $_values = empty($_form_fields_info[$key11]['repetitive']) ? array($values) : $values;
                    $new_values = array();
                    foreach ($_values as $values) {
                        if (!empty($values['datepicker'])) {
                            $date_format = $zebraForm->getDateFormat();

                            if (!is_array($values))
                                $tmp = array($values);
                            else
                                $tmp = $values;


                            // track form data for notification mail
                            if ($track) {
                                $this->trackData(array($field_label => $tmp));
                                $done_data = true;
                            }

                            $timestamp = $tmp['datepicker'];

                            if (!isset($tmp['hour']))
                                $tmp['hour'] = "00";
                            if (!isset($tmp['minute']))
                                $tmp['minute'] = "00";


                            //if ( stripos( $date_format, "h:" ) === false ) {
                            //    $date_format .= " h:i:s";
                            if ($tmp['hour'] < 10 && strlen($tmp['hour']) == 1)
                                $tmp['hour'] = "0{$tmp['hour']}";
                            if ($tmp['minute'] < 10 && strlen($tmp['minute']) == 1)
                                $tmp['minute'] = "0{$tmp['minute']}";
                            //$tmp['datepicker'] = $tmp['datepicker'] . " " . $tmp['hour'] . ":" . $tmp['minute'] . ":00";
                            //}

                            $timestamp_date = adodb_date('dmY', $timestamp);
                            $date = adodb_mktime(intval($tmp['hour']), intval($tmp['minute']), 0, substr($timestamp_date, 2, 2), substr($timestamp_date, 0, 2), substr($timestamp_date, 4, 4));
                            $timestamp = $date;

                            if (isset($tmp['hour']))
                                unset($tmp['hour']);
                            if (isset($tmp['minute']))
                                unset($tmp['minute']);


                            $new_values[] = $timestamp;
                        } else {

                            if (isset($values['hour']))
                                unset($values['hour']);
                            if (isset($values['minute']))
                                unset($values['minute']);
                        }
                    }
                    $values = $new_values;
                    unset($new_values);
                }
                elseif ('skype' == $field['type']) {

                    //TODO: check this could be no need array($values)                    
                    $values = isset($_form_fields_info[$key11]['repetitive']) && $_form_fields_info[$key11]['repetitive'] == 1 ? $values : array($values);

                    if ($track) {
                        $this->trackData(array($field_label => $values));
                        $done_data = true;
                    }
                }
                // Modified by Srdjan END
                // 
                // dont track file/image data now but after we upload them..
                if (
                        $track && !$done_data &&
                        'audio' != $field['type'] &&
                        'video' != $field['type'] &&
                        'file' != $field['type'] &&
                        'image' != $field['type']
                ) {
                    $this->trackData(array($field_label => $values));
                }
                $fields[$key] = $values;
            }
        }

        // custom parents (Types feature)
        foreach ($_fields['parents'] as $key => $field) {
            $field_label = $field['name'];

            // overwrite parent setting by url, even though no fields might b e set
            if (
                    !array_key_exists($key, $_form_fields) &&
                    array_key_exists('parent_' . $field['data']['post_type'] . '_id', $_GET) &&
                    is_numeric($_GET['parent_' . $field['data']['post_type'] . '_id'])
            ) {
                $fieldsInfo[$key] = array('save_single' => false);
                $fields[$key] = intval($_GET['parent_' . $field['data']['post_type'] . '_id']);
                continue;
            }

            //Not including parent -- not set --
            if (
                    isset($_POST['_wpcf_belongs_' . $field['data']['post_type'] . '_id']) &&
                    $_POST['_wpcf_belongs_' . $field['data']['post_type'] . '_id'] == 0
            ) {
                continue;
            }

            // if this field was not rendered in this specific form, bypass it
            if (!array_key_exists($key, $_form_fields))
                continue;

            // if this field was discarded due to some conditional logic, bypass it
            //if ($zebraForm->controls[$_form_fields[$key][0]]->isDiscarded())  continue;

            if (
                    array_key_exists($key, $method) &&
                    intval($method[$key]) >= -1
            ) {
                $fieldsInfo[$key] = array('save_single' => false);
                $fields[$key] = intval($method[$key]);
            }
        }

        // taxonomies
        foreach ($_fields['taxonomies'] as $key => $field) {
            // if this field was not rendered in this specific form, bypass it
            if (!array_key_exists($key, $_form_fields))
                continue;

            if (
                    array_key_exists($key, $method) ||
                    ($field['hierarchical'] && isset($method[$key . '_hierarchy']))
            ) {
                if ($field['hierarchical'] /* && is_array($method[$key]) */) {
                    $values = isset($method[$key]) ? $method[$key] : array();
                    if (isset($method[$key . '_hierarchy'])) {
                        $add_new = array();
                        preg_match_all("/\{([^\{\}]+?),([^\{\}]+?)\}/", $method[$key . '_hierarchy'], $tmp_a_n);
                        for ($ii = 0; $ii < count($tmp_a_n[1]); $ii++) {
                            $add_new[] = array(
                                'parent' => $tmp_a_n[1][$ii],
                                'term' => $tmp_a_n[2][$ii]
                            );
                        }
                        unset($tmp_a_n);
                    } else {
                        $add_new = array();
                    }

                    $new_numeric_values = array();
                    foreach ($add_new as $one) {
                        if (is_numeric($one['term'])) {
                            $new_numeric_values[] = $one['term'];
                        }
                    }

                    $taxonomies['hierarchical'][] = array(
                        'name' => $key,
                        'terms' => $values,
                        'add_new' => $add_new,
                        'remove' => ''
                    );
                    // track form data for notification mail
                    if ($track) {

                        $result = array();
                        $result = cred__parent_sort($field['all'], $result, 0, 0);

                        $tmp_data = array();
                        foreach ($result as $tmp_tax) {
                            //if (in_array($tmp_tax['term_taxonomy_id'],$values))
                            if (in_array($tmp_tax['term_id'], $values))
                                $tmp_data[] = str_repeat("- ", $tmp_tax['depth']) . $tmp_tax['name'];
                        }
                        // add also new terms created
                        foreach ($values as $val) {
                            if (
                                    ( is_string($val) && !is_numeric($val) ) ||
                                    in_array($val, $new_numeric_values)
                            ) {
                                $tmp_data[] = $val;
                            }
                        }
                        unset($new_numeric_values);

                        $this->trackData(array($field['label'] => $tmp_data));
                        unset($tmp_data);
                    }
                } elseif (!$field['hierarchical']) {
                    $values = $method[$key];

                    // find which to add and which to remove
                    $tax_add = $values;
                    //TODO: use remove ??
                    $tax_remove = "";

                    // allow white space in tax terms
                    $taxonomies['flat'][] = array('name' => $key, 'add' => $tax_add, 'remove' => $tax_remove);

                    // track form data for notification mail
                    if ($track)
                        $this->trackData(array($field['label'] => array('added' => $tax_add, 'removed' => $tax_remove)));
                }
            }
        }
        return array($fields, $fieldsInfo, $taxonomies, $files, $removed_fields, $error_files);
    }

    public function CRED_extractCustomUserFields($user_id, $track = false) {
        global $user_ID;
        // reference to the form submission method
        global ${'_' . CRED_StaticClass::METHOD};
        $method = & ${'_' . CRED_StaticClass::METHOD};

        $error_files = array();

        $form = $this->_formBuilder->_formData;

        $form_id = $form->getForm()->ID;
        $form_fields = $form->getFields();
        $form_type = $form_fields['form_settings']->form['type'];
        $post_type = $form_fields['form_settings']->post['post_type'];

        $_fields = CRED_StaticClass::$out['fields'];
        $_form_fields = CRED_StaticClass::$out['form_fields'];
        $_form_fields_info = CRED_StaticClass::$out['form_fields_info'];
        $zebraForm = $this->_formBuilder->_zebraForm;

        // custom fields
        $fields = array();
        $removed_fields = array();
        $fieldsInfo = array();
        // files, require extra care to upload correctly
        $files = array();

        if (count($error_files) > 0) {
            // Bail out early if there are errors when uploading files
            return array($fields, $fieldsInfo, $files, $removed_fields, $error_files);
        }

        foreach ($_fields['post_fields'] as $key => $field) {
            $field_label = $field['name'];
            $done_data = false;

            // use the key as was rendered (with potential prefix) 
            $key11 = $key;
            if (isset($field['plugin_type_prefix']))
                $key = $field['plugin_type_prefix'] . $key;

            // if this field was not rendered in this specific form, bypass it
            if (!array_key_exists($key11, $_form_fields))
                continue;

            // if this field was discarded due to some conditional logic, bypass it
            //if (isset($zebraForm->controls) && $zebraForm->controls[$_form_fields[$key11][0]]->isDiscarded())  continue;

            $fieldsInfo[$key] = array('save_single' => false);

            if (
                    ('audio' == $field['type'] ||
                    'video' == $field['type'] ||
                    'file' == $field['type'] ||
                    'image' == $field['type'])
            ) {
                if (
                        !array_key_exists($key, $method)
                ) {
                    // remove the fields
                    $removed_fields[] = $key;
                    unset($fieldsInfo[$key]);
                } else/* if (is_array($method[$key])) */ {
                    /* $_hasFile=false;
                      foreach ($method[$key] as $_file_)
                      {
                      if (''!=trim($method[$key]))
                      $_hasFile=true;
                      }
                      // repetitive field and all values are empty, remove
                      if (!$_hasFile)
                      {
                      // remove the fields
                      $removed_fields[]=$key;
                      unset($fieldsInfo[$key]);
                      } */
                    $fields[$key] = $method[$key];
                }
            }

            if (
                    'checkboxes' == $field['type'] &&
                    isset($field['data']['save_empty']) &&
                    'yes' == $field['data']['save_empty'] &&
                    !array_key_exists($key, $method)
            ) {
                $values = array();
                foreach ($field['data']['options'] as $optionkey => $optiondata) {
                    $values[$optionkey] = '0';
                }
                // let model serialize once, fix Types-CRED mapping i ssue with chec kboxes
                $fieldsInfo[$key]['save_single'] = true;
                $fields[$key] = $values;
            } elseif (
                    'checkboxes' == $field['type'] &&
                    (!isset($field['data']['save_empty']) ||
                    'yes' != $field['data']['save_empty']) &&
                    !array_key_exists($key, $method)
            ) {
                // remove the fields
                $removed_fields[] = $key;
                unset($fieldsInfo[$key]);
            } elseif (
                    'checkbox' == $field['type'] &&
                    isset($field['data']['save_empty']) &&
                    'yes' == $field['data']['save_empty'] &&
                    !array_key_exists($key, $method)
            ) {
                $fields[$key] = '0';
            } elseif (
                    'checkbox' == $field['type'] &&
                    (!isset($field['data']['save_empty']) ||
                    'yes' != $field['data']['save_empty']) &&
                    !array_key_exists($key, $method)
            ) {
                // remove the fields
                $removed_fields[] = $key;
                unset($fieldsInfo[$key]);
            } elseif (array_key_exists($key, $method)) {
                // normalize repetitive values out  of sequence
                if ($_form_fields_info[$key11]['repetitive'] ||
                        'multiselect' == $_form_fields_info[$key11]['type']) {
                    if (is_array($method[$key])) {
                        $values = array_values($method[$key]);
                    } else {
                        $aux_value_array = array($method[$key]);
                        $values = array_values($aux_value_array);
                    }
                } else {
                    $values = $method[$key];
                }

                if ('audio' == $field['type'] ||
                        'video' == $field['type'] ||
                        'file' == $field['type'] ||
                        'image' == $field['type']) {
                    //TODO check this
                    if (isset($_FILES) && !empty($_FILES[$key])) {
                        $files[$key] = $zebraForm->getFileData($key, $_FILES[$key]); //$zebraForm->controls[$key];//$zebraForm->controls[$_form_fields[$key11][0]]->get_values();
                        $files[$key]['name_orig'] = $key11;
                        $files[$key]['label'] = $field['name'];
                        $files[$key]['repetitive'] = $_form_fields_info[$key11]['repetitive'];
                    }
                } elseif ('textarea' == $field['type'] ||
                        'wysiwyg' == $field['type']) {
                    // stripslashes for textarea, wysiwyg fields
                    if (is_array($values))
                        $values = array_map('stripslashes', $values);
                    else
                        $values = stripslashes($values);
                }
                elseif ('textfield' == $field['type'] ||
                        'text' == $field['type']) {
                    // stripslashes for text fields
                    if (is_array($values))
                        $values = array_map('stripslashes', $values);
                    else
                        $values = stripslashes($values);
                }

                // track form data for notification mail
                if ($track) {
                    $tmp_data = null;
                    if ('checkbox' == $field['type']) {
                        if ('db' == $field['data']['display'])
                            $tmp_data = $values;
                        else
                            $tmp_data = $field['data']['display_value_selected'];
                    }
                    elseif ('radio' == $field['type'] || 'select' == $field['type']) {
                        //$tmp_data = $field['data']['options'][$values]['title'];
                        foreach ($field['data']['options'] as $_key => $_val) {
                            if (isset($_val['value']) && $_val['value'] == $values) {
                                $tmp_data = $_val['title'];
                            }
                        }
//                        foreach ($field['data']['options'] as $ele) {
//                            if (!isset($ele['value']))
//                                continue;
//                            if ($ele['value'] == $values) {
//                                $tmp_data = $ele['title'];
//                            }
//                        }
                        //$tmp_data = $field['data']['options'][$values]['title'];
                    } elseif ('checkboxes' == $field['type'] || 'multiselect' == $field['type']) {
                        $tmp_data = array();
                        foreach ($values as $tmp_val)
                            $tmp_data[] = $field['data']['options'][$tmp_val]['title'];
                        //$tmp_data=implode(', ',$tmp_data);
                        unset($tmp_val);
                    }
                    if (isset($tmp_data)) {
                        $this->trackData(array($field_label => $tmp_data));
                        $done_data = true;
                    }
                }

                if ('checkboxes' == $field['type'] ||
                        'multiselect' == $field['type']) {
                    if (!is_array($values)) {
                        $values = array($values);
                    }

                    $result = array();
                    foreach ($field['data']['options'] as $optionkey => $optiondata) {
                        if (in_array($optionkey, $values)) {
                            if (array_key_exists('set_value', $optiondata) && isset($optiondata['set_value'])) {
                                $result[$optionkey] = array($optiondata['set_value']);
                            } elseif ('multiselect' == $field['type']) {
                                $result[$optionkey] = array($optionkey);
                            }
                        }
                    }
//                    foreach ($field['data']['options'] as $optionkey => $optiondata) {
//                        if (in_array($optionkey, $values)) {
//                            if (array_key_exists('set_value', $optiondata) && isset($optiondata['set_value'])) {
//                                $result[$optionkey] = $optiondata['set_value'];
//                            } elseif ('multiselect' == $field['type']) {
//                                $result[$optionkey] = $optionkey;
//                            }
//                        }
//                    }

                    $values = $result;
                    $fieldsInfo[$key]['save_single'] = true;
                } elseif ('radio' == $field['type'] ||
                        'select' == $field['type']) {
                    //Fixed cred-211
                    //$values = $field['data']['options'][$values]['value'];
                } elseif ('date' == $field['type']) {
                    // Modified by Srdjan                    
                    /*
                     * Single/repetitive values for Date are not set right,
                     * because CRED used Date as string - not array
                     *
                     * NOTE: There is no general method in CRED to check if repetitive?
                     * Types have types_is_repetitive() function.
                     * If it's types fiels - repetitive flag is in
                     * $field['data']['repetitive']
                     */
                    $_values = empty($_form_fields_info[$key11]['repetitive']) ? array($values) : $values;
                    $new_values = array();
                    foreach ($_values as $values) {
                        if (!empty($values['datepicker'])) {
                            $date_format = $zebraForm->getDateFormat();

                            if (!is_array($values))
                                $tmp = array($values);
                            else
                                $tmp = $values;

                            // track form data for notification mail
                            if ($track) {
                                $this->trackData(array($field_label => $tmp));
                                $done_data = true;
                            }

                            $timestamp = $tmp['datepicker'];

                            if (!isset($tmp['hour']))
                                $tmp['hour'] = "00";
                            if (!isset($tmp['minute']))
                                $tmp['minute'] = "00";

                            //if ( stripos( $date_format, "h:" ) === false ) {
                            //    $date_format .= " h:i:s";
                            if ($tmp['hour'] < 10 && strlen($tmp['hour']) == 1)
                                $tmp['hour'] = "0{$tmp['hour']}";
                            if ($tmp['minute'] < 10 && strlen($tmp['minute']) == 1)
                                $tmp['minute'] = "0{$tmp['minute']}";
                            //$tmp['datepicker'] = $tmp['datepicker'] . " " . $tmp['hour'] . ":" . $tmp['minute'] . ":00";
                            //}


                            $timestamp_date = adodb_date('dmY', $timestamp);
                            $date = adodb_mktime(intval($tmp['hour']), intval($tmp['minute']), 0, substr($timestamp_date, 2, 2), substr($timestamp_date, 0, 2), substr($timestamp_date, 4, 4));
                            $timestamp = $date;

                            if (isset($tmp['hour']))
                                unset($tmp['hour']);
                            if (isset($tmp['minute']))
                                unset($tmp['minute']);

                            $new_values[] = $timestamp;
                        } else {

                            if (isset($values['hour']))
                                unset($values['hour'
                                ]);
                            if (isset($values['minute']))
                                unset($values['minute']);
                        }
                    }
                    $values = $new_values;
                    unset($new_values);
                    // Modified by Srdjan END
                }

                elseif ('skype' == $field['type']) {
                    //TODO: check this could be no need array($values)                    
                    $values = isset($_form_fields_info[$key11]['repetitive']) && $_form_fields_info[$key11]['repetitive'] == 1 ? $values : array($values);

                    if ($track) {
                        $this->trackData(array($field_label => $values));
                        $done_data = true;
                    }
                }

                if (
                        $track && !$done_data &&
                        'audio' != $field['type'] &&
                        'video' != $field['type'] &&
                        'file' != $field['type'] &&
                        'image' != $field['type']
                ) {
                    $this->trackData(array($field_label => $values));
                }
                $fields[$key] = $values;
            }
        }

        return array($fields, $fieldsInfo, $files, $removed_fields, $error_files);
    }

    public function extractCustomFields($post_id, $track = false) {
        global $user_ID;
        // reference to the form submission method
        global ${'_' . CRED_StaticClass::METHOD};
        $method = & ${'_' . CRED_StaticClass::METHOD};

        // get refs here
        $globals = CRED_StaticClass::$_staticGlobal;
        $form = $this->_formBuilder->_formData;

        $form_id = $form->getForm()->ID;
        $form_fields = $form->getFields();
        $form_type = $form_fields['form_settings']->form['type'];
        $post_type = $form_fields['form_settings']->post['post_type'];

        $_fields = CRED_StaticClass::$out['fields'];
        $_form_fields = CRED_StaticClass::$out['form_fields'];
        $_form_fields_info = CRED_StaticClass::$out['form_fields_info'];
        $zebraForm = $this->_formBuilder->_zebraForm;

        // custom fields
        $fields = array();
        $removed_fields = array();
        // taxonomies
        $taxonomies = array('flat' => array(), 'hierarchical' => array());
        $fieldsInfo = array();
        // files, require extra care to upload correctly
        $files = array();
        foreach ($_fields['post_fields'] as $key => $field) {
            $field_label = $field['name'];
            $done_data = false;

            // use the key as was rendered (with potential prefix)
            $key11 = $key;
            if (isset($field['plugin_type_pr efix']))
                $key = $field['plugin_type_prefix'] . $key;

            // if this field was not rendered in this specific form, bypass it
            if (!array_key_exists($key11, $_form_fields))
                continue;

            // if this field was discarded due to some conditional logic, bypass it
            if (isset($zebraForm->controls) && $zebraForm->controls[$_form_fields[$key11][0]]->isDiscarded())
                continue;


            $fieldsInfo[$key] = array('save_single' => false);

            if (
                    ('audio' == $field['type'] ||
                    'video' == $field['type'] ||
                    'file' == $field['type
            '] ||
                    'image' == $field['type'])
            ) {
                if (
                        !array_key_exists($key, $method)
                ) {
                    // remove the fields
                    $removed_fields[] = $key;
                    unset($fieldsInfo[$key]);
                } else/* if (is_array($method[$key])) */ {
                    /* $_hasFile=false;
                      foreach ($method[$key] as $_file_)
                      {
                      if (''!=trim($method[$key]))
                      $_hasFile=true;
                      }
                      // repetitive field and all values are empty, remove
                      if (!$_hasFile)
                      {
                      // remove the fields
                      $removed_fields[]=$key;
                      unset($fieldsInfo[$key]);
                      } */
                    $fields[$key] = $method[$key];
                }
            }
            if (
                    'checkboxes' == $field['type'] &&
                    isset($field['data']['save_empty']) &&
                    'yes' == $field['data']['save_empty'] &&
                    !array_key_exists($key, $method)
            ) {
                $values = array();
                foreach ($field['data']['options'] as $optionkey => $optiondata) {
                    $values[$optionkey] = '0';
                }

                // let model serialize once, fix Types-CRED mapping issue with checkboxes
                $fieldsInfo[$key]['save_single'] = true;
                $fields[$key] = $values;
            } elseif (
                    'checkboxes' == $field['type'] &&
                    (!isset($field['data']['save_empty']) ||
                    'yes' != $field['data']['save_empty']) &&
                    !array_key_exists($key, $method)
            ) {
                // remove the fields
                $removed_fields[] = $key;
                unset($fieldsInfo[$key]);
            } elseif (
                    'checkbox' == $field['type'] &&
                    isset($field['data']['save_empty']) &&
                    'yes' == $field['data']['save_empty'] &&
                    !array_key_exists($key, $method)
            ) {
                $fields[$key] = '0';
            } elseif (
                    'checkbox' == $field['type'] &&
                    (!isset($field['data']['save_empty']) ||
                    'yes' != $field['data']['save_empty']) &&
                    !array_key_exists($key, $method)
            ) {
                // remove the fields
                $removed_fields[] = $key;
                unset($fieldsInfo[$key]);
            } elseif (array_key_exists($key, $method)) {
                // normalize repetitive values out  of sequence
                // NOTE this seems deprecated as we are using the method above... why is this still here?
                if ($_form_fields_info[$key11]['repetitive']) {
                    if (is_array($method[$key])) {
                        $values = array_values($method[$key]);
                    } else {
                        $aux_value_array = array($method[$key]);
                        $values = array_values($aux_value_array);
                    }
                } else {
                    $values = $method[$key];
                }

                if ('audio' == $field['type'] ||
                        'video' == $field['type'] ||
                        'file' == $field['type'] ||
                        'image' == $field['type']) {
                    $files[$key] = $zebraForm->controls[$_form_fields[$key11][0]]->get_values();
                    //cred_log($files[$key]);
                    $files[$key]['name_orig'] = $key11;
                    $files[$key]['label'] = $field['name'];
                    $files[$key]['repetitive'] = $_form_fields_info[$key11]['repetitive'];
                } elseif ('textarea' == $field['type'] || 'wysiwyg' == $field['type']) {
                    // stripslashes for textarea, wysiwyg fields
                    if (is_array($values))
                        $values = array_map('stripslashes', $values);
                    else
                        $values = stripslashes($values);
                }
                elseif ('textfield' == $field['type'] || 'text' == $field['type'] || 'date' == $field['type']) {
                    // stripslashes for text fields
                    if (is_array($values))
                        $values = array_map('stripslashes', $values);
                    else
                        $values = stripslashes($values);
                }

                // track form data for notification mail
                if ($track) {

                    $tmp_data = null;
                    if ('checkbox' == $field['type']) {
                        if ('db' == $field['data']['display'])
                            $tmp_data = $values;
                        else
                            $tmp_data = $field['data']['display_value_selected'];
                    }
                    elseif ('radio' == $field['type'] || 'select' == $field['type']) {

                        $tmp_data = $field['data']['options'][$values]['title'];
                    } elseif ('checkboxes' == $field['type'] || 'multiselect' == $field['type']) {
                        $tmp_data = array();
                        foreach ($values as $tmp_val)
                            $tmp_data[] = $field['data']['options'][$tmp_val]['title'];
                        //$tmp_data=implode(', ',$tmp_data);
                        unset($tmp_val);
                    }
                    if (isset($tmp_data)) {
                        $this->trackData(array($field_label => $tmp_data));
                        $done_data = true;
                    }
                }
                if ('checkboxes' == $field['type'] || 'multiselect' == $field['type']) {
                    $result = array();
                    foreach ($field['data']['options'] as $optionkey => $optiondata) {
                        /* if (
                          isset($field['data']['save_empty']) &&
                          'yes'==$field['data']['save_empty'] &&
                          !in_array($optionkey, $values)
                          )
                          $result[$optionkey]='0';
                          else */
                        if (in_array($optionkey, $values) && isset($optiondata['set_value']))
                            $result[$optionkey] = $optiondata['set_value'];
                    }

                    $values = $result;
                    $fieldsInfo[$key]['save_single'] = true;
                }
                elseif ('radio' == $field['type'] || 'select' == $field['type']) {

                    $values = $field['data']['options'][$values]['value'];
                } elseif ('date' == $field['type']) {
                    $date_format = null;
                    if (isset($field['data']) && isset($field['data']['validate']))
                        $date_format = $field['data']['validate']['date']['format'];
                    if (!in_array($date_format, CRED_StaticClass::$_supported_date_formats))
                        $date_format = 'F j, Y';
                    if (!is_array($values))
                        $tmp = array(
                            $values);
                    else
                        $tmp = $values;

                    // track form data for notification mail
                    if ($track) {

                        $this->trackData(array($field_label => $tmp));
                        $done_data = true;
                    }

                    MyZebra_DateParser::setDateLocaleStrings($globals['LOCALES']['days'], $globals['LOCALES']['months']);
                    foreach ($tmp as $ii => $val) {
                        $val = MyZebra_DateParser::parseDate($val, $date_format);
                        if (false !== $val)  // succesfull
                            $val = $val->getNormalizedTimestamp();
                        else
                            continue;

                        $tmp[$ii] = $val;
                    }

                    if (!is_array($values))
                        $values = $tmp[0];
                    else
                        $values = $tmp;
                }
                elseif ('skype' == $field['type']) {
                    if (
                            array_key_exists('skypename', $values) &&
                            array_key_exists('style', $values)
                    ) {
                        $new_values = array();
                        $values['skypename'] = (array) $values['skypename'];
                        $values['style'] = (array) $values['style'];
                        foreach ($values['skypename'] as $ii => $val) {
                            $new_values[] = array(
                                'skypename' => $values['skypename'][$ii],
                                'style' => $values['style'][$ii]
                            );
                        }
                        $values = $new_values;
                        unset($new_values);
                        if ($track) {
                            $this->trackData(array($field_label => $values));
                            $done_data = true;
                        }
                    }
                }
                // dont track file/image data now but after we upload them..
                if (
                        $track && !$done_data &&
                        'file' != $field['type'] &&
                        'image' != $field['type']
                ) {
                    $this->trackData(array($field_label => $values));
                }
                $fields[$key] = $values;
            }
        }
        // custom parents (Types feature)
        foreach ($_fields['parents'] as $key => $field) {
            $field_label = $field['name'];

            // overwrite parent setting by url, even though no fields might be set
            if (
                    !array_key_exists($key, $_form_fields) &&
                    array_key_exists('parent_' . $field['data']['post_type'] . '_id', $_GET) &&
                    is_numeric($_GET['parent_' . $field['data']['post_type'] . '_id'])
            ) {
                $fieldsInfo[$key] = array('save_single' => false);
                $fields[$key] = intval($_GET['parent_' . $field['data']['post_type'] . '_id']);
                continue;
            }
            // if this field was not rendered in this specific form, bypass it
            if (!array_key_exists($key, $_form_fields))
                continue;

            // if this field was discarded due to some conditional logic, bypass it
            if ($zebraForm->controls[$_form_fields[$key][0]]->isDiscarded())
                continue;

            if (
                    array_key_exists($key, $method) &&
                    intval($method[$key]) >= -1
            ) {
                $fieldsInfo[$key] = array('save_single' => false);
                $fields[$key] = intval($method[$key]);
            }
        }

        // taxonomies

        foreach ($_fields['taxonomies'] as $key => $field) {
            // if this field was not rendered in this specific form, bypass it
            if (!array_key_exists($key, $_form_fields))
                continue;

            if (
                    array_key_exists($key, $method) ||
                    ($field['hierarchical'] && isset($method[$key . '_hie rarchy']))
            ) {
                if ($field['hierarchical'] /* && is_array($method[$key]) */) {
                    $values = isset($method[$key]) ? $method[$key] : array();
                    if (isset($method[$key . '_hierarchy'])) {
                        $add_new = array();
                        preg_match_all("/\{([^\{\}]+?),([^\{\}]+?)\}/", $method[$key . '_hierarchy'], $tmp_a_n);
                        for ($ii = 0; $ii < count($tmp_a_n[1]); $ii++) {
                            $add_new[] = array(
                                'parent' => $tmp_a_n[1][$ii],
                                'term' => $tmp_a_n[2][$ii]
                            );
                        }
                        unset($tmp_a_n);
                    } else
                        $add_new = array();

                    $taxonomies['hierarchical'][] = array(
                        'name' => $key,
                        'terms' => $values,
                        'add_new' => $add_new
                    );
                    // track form data for notification mail
                    if ($track) {
                        $tmp_data = array();
                        foreach ($field['all'] as $tmp_tax) {
                            //if (in_array($tmp_tax['term_taxonomy_id'],$values))
                            if (in_array($tmp_tax['term_id'], $values))
                                $tmp_data[] = $tmp_tax['name'];
                        }
                        // add also new terms created
                        foreach ($values as $val) {
                            if (is_string($val) && !is_numeric($val))
                                $tmp_data[] = $val;
                        }
                        $this->trackData(array($field['label'] => $tmp_data));
                        unset($tmp_data);
                    }
                }
                elseif (!$field['hierarchical']) {
                    $values = $method[$key];

                    // find which to add and which to remove
                    $tax_add = $values;
                    $tax_remove = "";

                    // allow white space in tax terms
                    $taxonomies['flat'][] = array('name' => $key, 'add' => $tax_add, 'remove' => $tax_remove);

                    // track form data for notification mail
                    if ($track)
                        $this->trackData(array($field['label'] => array('added' => $tax_add, 'removed' => $tax_remove)));
                }
            }
        }

        return array($fields, $fieldsInfo, $taxonomies, $files, $removed_fields);
    }

    public function CRED_uploadFeaturedImage($post_id) {
        if (isset($_POST['attachid__featured_image'])) {

            if (empty($_POST['attachid__featured_image'])) {
                delete_post_meta($post_id, '_thumbnail_id');
            } else {
                update_post_meta($post_id, '_thumbnail_id', $_POST['attachid__featured_image']);
            }
        }
    }

    public function CRED_uploadAttachments($post_id, &$fields, &$files, &$extra_files, $track = false) {
        // dependencies
        require_once(ABSPATH . '/wp-admin/includes/file.php');

        $form = $this->_formBuilder->_formData;

        $_form_fields = CRED_StaticClass::$out['form_fields'];

        // upload data
        $all_ok = true;
        // set featured image only if uploaded
        $fkey = '_featured_image';

        if (isset($_POST[$fkey])) {
            $this->trackData(array(__('Featured Image', 'wp-cred') => "<img src='" . $_POST[$fkey] . "'>"));
            //$_feature_image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ));
        }

        $extra_files = array();
        if (
                array_key_exists($fkey, $_form_fields) &&
                array_key_exists($fkey, $_FILES) &&
                isset($_FILES[$fkey]['name']) &&
                !empty($_FILES[$fkey]['name'])
        ) {
            $upload = wp_handle_upload($_FILES[$fkey], array('test_form' => false, 'test_upload' => false));
            if (!isset($upload['error']) && isset($upload['file'])) {
                $extra_files[$fkey]['wp_upload'] = $upload;
                if ($track)
                    $tmp_data = $upload['url'];
            }
            else {
                $all_ok = false;
                if ($track)
                    $tmp_data = $this->getLocalisedMessage('upload_failed');
                $fields[$fkey] = '';
                $extra_files[$fkey]['upload_fail'] = true;
            }
            if ($track) {
                $this->trackData(array(__('Featured Image', 'wp-cred') => $tmp_data));
                unset($tmp_data);
            }
        } else {
            if (array_key_exists($fkey, $_FILES) &&
                    isset($_FILES[$fkey]['name']) &&
                    empty($_FILES[$fkey]['name']) &&
                    is_int($post_id) &&
                    $post_id > 0
            ) {
                delete_post_meta($post_id, '_thumbnail_id');
            }
        }

        if (isset($_POST[$fkey]) && isset($_POST['_cred_cred_prefix_post_id'])) {
            $post_id = intval($_POST['_cred_cred_prefix_post_id']);
            delete_post_meta($post_id, '_thumbnail_id');

            $args = array(
                'post_type' => 'attachment',
                'numberposts' => -1,
                'post_status' => 'any',
                'post_parent' => $post_id
            );

            $attachments = get_posts($args);
            foreach ($attachments as $n => $attachment) {
                if ($attachment->post_title == basename($_POST[$fkey])) {
                    $attachment_id = $attachment->ID;
                    break;
                }
            }
            update_post_meta($post_id, '_thumbnail_id', $attachment_id);
        }

        $new_arr = array();
        $i = 0;
        foreach ($files as $mkey => $file) {
            if ($file['repetitive']) {
                $j = 0;

                if (!isset($new_arr['elements'])) {
                    $new_arr['elements'] = array();
                }

                foreach ($file['value'] as $value) {
                    if (!isset($new_arr['elements'][$j]))
                        $new_arr['elements'][$j] = array();
                    $new_arr['elements'][$j]['value'] = $value;
                    $j++;
                }

                foreach ($file['file_data'][$mkey] as $name => $value) {
                    $t = 0;
                    foreach ($value as $v) {
                        if (!isset($new_arr['elements'][$t]['filedata']))
                            $new_arr['elements'][$t]['filedata'] = array();
                        if (!isset($new_arr['elements'][$t]['filedata'][$mkey]))
                            $new_arr['elements'][$t][' filedata'][$mkey] = array();
                        $new_arr['elements'][$t]['filedat a'][$mkey][$name] = $v;
                        $t++;
                    }
                }


                $j = 0;
                foreach ($file['value'] as $value) {
                    if (!isset($new_arr['elements'][$j]))
                        $new_arr['elements'][$j] = array();
                    $new_arr['elements'][$j]['file_upload'] = $file['file_upload'];
                    $new_arr['elements'][$j]['name_orig'] = $file['name_orig'];
                    $new_arr['elements'][$j]['label'] = $file['label'];
                    $j++;
                }

                $i++;

                if (!isset($new_arr['repetitive']))
                    $new_arr['repetitive'] = $file['repetitive'];

                $files[$mkey] = $new_arr;
            }
        }
        unset($new_arr);

        //$mime_types = wp_get_mime_types();
        //$allowed_file_types = array_merge($mime_types, array('xml' => 'text/xml'));

        foreach ($files as $fkey => $fdata) {
            if ((isset($fdata['repetitive']) && $fdata['repetitive']) && isset($fdata['elements'])) {
                if (!isset($fields[$fkey])) {
                    $fields[$fkey] = array();
                } else {
                    if (is_array($fields[$fkey])) {
                        $fields[$fkey] = array_filter($fields[$fkey]);
                    } else {
                        $aux_value_array = array($fields[$fkey]);
                        $fields[$fkey] = array_filter($aux_value_array);
                    }
                }

                foreach ($fdata['elements'] as $element) {
                    $i = 0;
                    foreach ($element as $ii => $fdata2) {
                        if ($track)
                            $tmp_data = array();

                        //if (!isset($fdata2['file_data'][$fkey]) || !is_array($fdata2['file_data'][$fkey])) continue;
                        //$file_data=$fdata2['file_data'][$fkey];
                        if (!isset($fdata2[$fkey]) || !is_array($fdata2[$fkey]))
                            continue;

                        //TODO: check also $_POST[$fkey]
                        if ($fdata2[$fkey]['error'] == 4)
                            continue;

                        $file_data = $fdata2[$fkey];

                        $upload = wp_handle_upload($file_data, array('test_form' => false, 'test_upload' => false, 'mimes' => CRED_StaticClass::$_allowed_mime_types));
                        if (!isset($upload['error']) && isset($upload['file'])) {
                            $files[$fkey]['elements'][]['wp_upload'] = $upload;
                            $fields[$fkey][] = $upload['url'];
                            if ($track)
                                $tmp_data[] = $upload['url'];
                            $fields = $this->removeFromArray($fields, $fkey, $file_data['name']);
                        }
                        else {
                            $all_ok = false;
                            $files[$fkey]['elements'][$i]['upload_fail'] = true;
                            if ($track)
                                $tmp_data[] = $this->getLocalisedMessage('upload_failed');

                            $files[$fkey]['elements'][$i] = '';
                            $files[$fkey]['elements'][$i]['upload_fail'] = true;
                        }

                        if ($track) {
                            $this->trackData(array($files[$fkey]['elements'][$i]['label'] => $tmp_data));

                            unset($tmp_data);
                        }
                        $i++;
                    }
                }
            } else {

                if (!isset($fdata['file_data'][$fkey]) || !is_array($fdata['file_data'][$fkey]))
                    continue;

                //Fix tssupp-158
                //Fix changed !empty with isset tssupp-152
                if ($fdata['file_data'][$fkey]['error'] == 4 && isset($_POST[$fkey]))
                    continue;

                $file_data = $fdata['file_data'][$fkey];

                $upload = wp_handle_upload($file_data, array('test_form' => false, 'test_upload' =>
                    false, 'mimes' => CRED_StaticClass::$_allowed_mime_types));
                if (!isset($upload['error']) && isset($upload['file'])) {
                    $files[$fkey]['wp_upload'] = $upload;
                    $fields[$fkey] = $upload['url'];
                    if ($track)
                        $tmp_data = $upload['url'];
                    //$zebraForm->controls[$_form_fields[$files[$fkey]['name_orig']][0]]->set_values(array('value'=>$upload['url']));
                }
                else {
                    //Fix if there a File generi cred field not required
                    //cred-14
                    $data_field = CRED_StaticClass::$out['fields']['post_fields'][$fkey];
                    if (isset($data_field['cred_generic']) && $data_field['cred_generic'] == 1 &&
                            (isset($data_field['data']['validate']['required']['active']) &&
                            $data_field['data']['validate']['required']['active'] == 0)) {
                        
                    } else {
                        $all_ok = false;
                        if ($track)
                            $tmp_data = $this->getLocalisedMessage('upload_failed');

                        $fields[$fkey] = '';
                        $files[$fkey]['upload_fail'] = true;
                    }
                    //$zebraForm->controls[$_form_fields[$files[$fkey]['name_orig']][0]]->set_values(array('value'=>''));
                    //$zebraForm->controls[$_form_fields[$files[$fkey]['name_orig']][0]]->addError($upload['error']);
                }
                if ($track) {
                    $this->trackData(array($files[$fkey]['label'] => $tmp_data));
                    unset($tmp_data);
                }
            }
        }

        return $all_ok;
    }

    /**
     * @deprecated since version 1.3.6.2
     * @param type $user_id
     * @param type $fields
     * @param type $files
     * @param type $extra_files
     * @param type $track
     * @return boolean
     */
    public function CRED_userUploadAttachments($user_id, &$fields, &$files, &$extra_files, $track = false) {
        // dependencies
        require_once(ABSPATH . '/wp-admin/includes/file.php');
        //CRED_Loader::loadThe('wp_handle_upload');
        // get ref here
        $form = $this->_formBuilder->_formData;

        $_form_fields = CRED_StaticClass::$out['form_fields'];

        // upload data
        $all_ok = true;
        // set featured image only if uploaded
        $fkey = '_featured_image';
        $extra_files = array();
        if (
                array_key_exists($fkey, $_form_fields) &&
                array_key_exists($fkey, $_FILES) &&
                isset($_FILES[$fkey]['name']) &&
                !empty($_FILES[$fkey]['name'])
        ) {
            $upload = wp_handle_upload($_FILES[$fkey], array('test_form' => false, 'test_upload' => false));
            if (!isset($upload['error']) && isset($upload['file'])) {
                $extra_files[$fkey]['wp_upload'] = $upload;
                if ($track)
                    $tmp_data = $upload['url'];
            }
            else {
                $all_ok = false;
                if ($track)
                    $tmp_data = $this->getLocalisedMessage('upload_failed');
                $fields[$fkey] = '';
                $extra_files[$fkey]['upload_fail'] = true;
            }
            if ($track) {
                $this->trackData(array(__('Featured Image', 'wp-cred') => $tmp_data));
                unset($tmp_data);
            }
        } else {
            if (array_key_exists($fkey, $_FILES) &&
                    isset($_FILES[$fkey]['name']) &&
                    empty($_FILES[$fkey]['name']) &&
                    is_int($user_id) &&
                    $user_id > 0
            ) {
                delete_user_meta($user_id, '_thumbnail_id');
            }
        }

        $new_arr = array();
        $i = 0;
        foreach ($files as $mkey => $file) {
            if ($file['repetitive']) {
                $j = 0;

                if (!isset($new_arr['elements'])) {
                    $new_arr['elements'] = array();
                }

                foreach ($file['value'] as $value) {
                    if (!isset($new_arr['elements'][$j]))
                        $new_arr['elements'][$j] = array();
                    $new_arr ['elements'][$j]['value'] = $value;
                    $j++;
                }

                foreach ($file['file_data'][$mkey] as $name => $value) {
                    $t = 0;
                    foreach ($value as $v) {
                        if (!isset($new_arr['elements'][$t]['filedata']))
                            $new_arr['elements'][$t]['filedata'] = array();
                        if (!isset($new_arr['elements'][$t]['filedata'][$mkey]))
                            $new_arr['elements'][$t]['filedata'][$mkey] = array();
                        $new_arr['elements'][$t]['filedata'][$mkey][$name] = $v;
                        $t++;
                    }
                }

                $j = 0;

                foreach ($file['value'] as $value) {
                    if (!isset($new_arr['elements'][$j]))
                        $new_arr['elements'][$j] = array();
                    $new_arr['elements'][$j]['file_upload'] = $file['file_upload'];
                    $new_arr['elements'][$j]['name_orig'] = $file['name_orig'];
                    $new_arr['elements'][$j]['label'] = $file['label'];
                    $j++;
                }

                $i++;

                if (!isset($new_arr['repetitive']))
                    $new_arr['repetitive'] = $file['repetitive'];

                $files[$mkey] = $new_arr;
            }
        }
        unset($new_arr);

        //$mime_types = wp_get_mime_types();
        //$allowed_file_types = array_merge($mime_types, array('xml' => 'text/xml'));

        foreach ($files as $fkey => $fdata) {
            if ((isset($fdata['repetitive']) && $fdata['repetitive']) && isset
                            ($fdata['elements'])) {
                if (!isset($fields[$fkey])) {
                    $fields[$fkey] = array();
                } else {
                    if (is_array($fields[$fkey])) {
                        $fields[$fkey] = array_filter($fields[$fkey]);
                    } else {
                        $aux_value_array = array($fields[$fkey]);
                        $fields[$fkey] = array_filter($aux_value_array);
                    }
                }

                foreach ($fdata['elements'] as $element) {
                    $i = 0;
                    foreach ($element as $ii => $fdata2) {
                        if ($track)
                            $tmp_data = array();

                        //if (!isset($fdata2['file_data'][$fkey]) || !is_array($fdata2['file_data'][$fkey])) continue;
                        //$file_data=$fdata2['file_data'][$fkey];
                        if (!isset($fdata2[$fkey]) || !is_array($fdata2[$fkey]))
                            continue;
                        if ($fdata2[$fkey]['error'] == 4)
                            continue;

                        $file_data = $fdata2[$fkey];

                        $upload = wp_handle_upload($file_data, array('test_form' => false, 'test_upload' => false, 'mimes' => CRED_StaticClass::$_allowed_mime_types));
                        if (!isset($upload['error']) && isset($upload['file'])) {
                            $files[$fkey]['elements'][]['wp_upload'] = $upload;

                            $fields[$fkey][] = $upload['url'];
                            if ($track)
                                $tmp_data[] = $upload['url'];
                            $fields = $this->removeFromArray($fields, $fkey, $file_data['name']);
                        }
                        else {
                            $all_ok = false;
                            $files[$fkey]['elements'][$i]['upload_fail'] = true;
                            if ($track)
                                $tmp_data[] = $this->getLocalisedMessage('upload_failed');
                            $files[$fkey]['elements'][$i] = '';
                            $files[$fkey]['elements'][$i]['upload_fail'] = true;
                        }

                        if ($track) {
                            $this->trackData(array($files[$fkey]['elements'][$i]['label'] => $tmp_data));

                            unset($tmp_data);
                        }
                        $i++;
                    }
                }
            } else {
                if (!isset($fdata['file_data'][$fkey]) || !is_array($fdata['file_data'][$fkey]))
                    continue;

                $file_data = $fdata['file_data'][$fkey];

                $upload = wp_handle_upload($file_data, array('test_form' => false, 'test_upload' => false, 'mimes' => CRED_StaticClass::$_allowed_mime_types));
                if (!isset($upload['error']) && isset($upload['file'])) {
                    $files[$fkey]['wp_upload'] = $upload;
                    $fields[$fkey] = $upload['url'];
                    if ($track)
                        $tmp_data = $upload['url'];
                    //$zebraForm->controls[$_form_fields[$files[$fkey]['name_orig']][0]]->set_values(array('value'=>$upload['url']));
                }
                else {
                    //Fix if there a File generi cred field not required
                    //cred-14
                    $data_field = CRED_StaticClass::$out['fields']['post_fields'][$fkey];

                    if (isset($data_field['cred_generic']) && $data_field['cred_generic'] == 1 &&
                            (isset($data_field['data']['validate']['required']['active']) &&
                            $data_field['data']['validate']['required']['active'] == 0)) {
                        
                    } else {
                        $all_ok = false;
                        if ($track)
                            $tmp_data = $this->getLocalisedMessage('upload_failed');

                        $fields[$fkey] = '';
                        $files[$fkey]['upload_fail'] = true;
                    }
                    //$zebraForm->controls[$_form_fields[$files[$fkey]['name_orig']][0]]->set_values(array('value'=>''));
                    //$zebraForm->controls[$_form_fields[$files[$fkey]['name_orig']][0]]->addError($upload['error']);
                }
                if ($track) {
                    $this->trackData(array($files[$fkey]['label'] => $tmp_data));
                    unset($tmp_data);
                }
            }
        }

        return $all_ok;
    }

    /**
     * attachUploads
     * @deprecated since version 1.3.6.3
     * @param type $result
     * @param type $fields
     * @param type $files
     * @param type $extra_files
     */
    public function attachUploads($result, &$fields, &$files, &$extra_files) {
        // you must first include the image.php file
        // for the function wp_generate_attachment_metadata() to work
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        //CRED_Loader::loadThe('wp_generate_attachment_metadata');
        // get ref here
        $form = $this->_formBuilder->_formData;

        $_form_fields = CRED_StaticClass::$out['form_fields'];

        foreach ($files as $fkey => $fdata) {
            if ($files[$fkey]['repetitive']) {
                //cred_log($fdata);
                foreach ($fdata['elements'] as $ii => $fdata2) {
                    if (array_key_exists('wp_upload', $fdata2)) {
                        $attachment = array(
                            'post_mime_type' => $fdata2['wp_upload']['type'],
                            'post_title' => basename($fdata2['wp_upload']['file']),
                            'post_content' => '',
                            'post_status' => 'inherit',
                            'post_parent' => $result,
                            'post_type' => 'attachment',
                            'guid' => $fdata2['wp_upload']['url'],
                        );
                        $attach_id = wp_insert_attachment($attachment, $fdata2['wp_upload']['file']);
                        $attach_data = wp_generate_attachment_metadata($attach_id, $fdata2['wp_upload']['file']);
                        wp_update_attachment_metadata($attach_id, $attach_data);
                        continue;
                    }
                    if (!isset($fdata2['file_data'][$fkey]) || !is_array($fdata2['file_data'][$fkey])) {
                        continue;
                    }
                    //if (!isset($files[$fkey][$ii]['upload_fail']) || !$files[$fkey][$ii]['upload_fail'])
                    if (!isset($fdata2['upload_fail']) || !$fdata2['upload_fail']) {
                        //$filetype   = wp_check_filetype(basename($files[$fkey][$ii]['wp_upload']['file']), null);
                        $filetype = wp_check_filetype(basename($fdata2['wp_upload']['file']), null);
                        //$title      = $files[$fkey][$ii]['file_data'][$fkey]['name'];
                        $title = $fdata2['file_data'][$fkey]['name'];
                        $ext = strrchr($title, '.');
                        $title = ($ext !== false) ? substr($title, 0, -strlen($ext)) : $title;
                        $attachment = array(
                            'post_mime_type' => $filetype['type'],
                            'post_title' => addslashes($title),
                            'post_content' => '',
                            'post_status' => 'inherit',
                            'post_parent' => $result,
                            'post_type' => 'attachment',
                            //'guid' => $files[$fkey][$ii]['wp_upload']['url']
                            'guid' => $fdata2['wp_upload']['url']
                        );
                        //$attach_id  = wp_insert_attachment($attachment, $files[$fkey][$ii]['wp_upload']['file']);
                        //$attach_data = wp_generate_attachment_metadata( $attach_id, $files[$fkey][$ii]['wp_upload']['file'] );
                        $attach_id = wp_insert_attachment($attachment, $fdata2['wp_upload']['file']);
                        $attach_data = wp_generate_attachment_metadata($attach_id, $fdata2['wp_upload']['file']);
                        wp_update_attachment_metadata($attach_id, $attach_data);
                    }
                }
            } else {
                if (!isset($fdata['file_data'][$fkey]) || !is_array($fdata['file_data'][$fkey]))
                    continue;

                if (!isset($files[$fkey]['upload_fail']) || !$files[$fkey]['upload_fail']) {
                    $filetype = wp_check_filetype(basename($files[$fkey]['wp_upload']['file']), null);
                    $title = $files[$fkey]['file_data'][$fkey]['name'];
                    $ext = strrchr($title, '.');
                    $title = ($ext !== false) ? substr($title, 0, -strlen($ext)) :
                            $title;
                    $attachment = array(
                        'post_mime_type' => $filetype['type'],
                        'post_title' => addslashes($title),
                        'post_content' => '',
                        'post_status' => 'inherit',
                        'post_parent' => $result,
                        'post_type' => 'attachment',
                        'guid' => $files[$fkey]['wp_upload']['url']
                    );
                    $attach_id = wp_insert_attachment($attachment, $files[$fkey]['wp_upload']['file']);
                    $attach_data = wp_generate_attachment_metadata($attach_id, $files[$fkey]['wp_upload']['file']);
                    wp_update_attachment_metadata($attach_id, $attach_data);
                }
            }
        }

        foreach ($extra_files as $fkey => $fdata) {
            if (!isset($extra_files[$fkey]['upload_fail']) || !$extra_files[$fkey]['upload_fail']) {
                $filetype = wp_check_filetype(basename($extra_files[$fkey]['wp_upload']['file']), null);
                $title = $_FILES[$fkey]['name'];
                $ext = strrchr($title, '.');
                $title = ($ext !== false) ? substr($title, 0, -strlen($ext)) : $title;
                $attachment = array(
                    'post_mime_type' => $filetype['type'],
                    'post_title' => addslashes($title),
                    'post_content' => '',
                    'post_status' => 'inherit',
                    'post_parent' => $result,
                    'post_type' => 'attachment',
                    'guid' => $extra_files[$fkey]['wp_upload']['url']
                );
                $attach_id = wp_insert_attachment($attachment, $extra_files[$fkey]['wp_upload']['file']);
                $attach_data = wp_generate_attachment_metadata($attach_id, $extra_files[$fkey]['wp_upload']['file']);
                wp_update_attachment_metadata($attach_id, $attach_data);

                if ($fkey == '_featured_image') {
                    // set current thumbnail
                    update_post_meta($result, '_thumbnail_id', $attach_id);
                    // get current thumbnail
                    //zebraForm->controls[$_form_fields['_featured_image'][0]]->set_attributes(array('display_featured_html'=>get_the_post_thumbnail( $result, 'thumbnail' /*, $attr*/ )));
                }
            }
        }
    }

    public function setCookie($name, $data) {
        $result = false;
        if (!headers_sent()) {
            $result = setcookie($name, urlencode(serialize($data)));
        }
        return $result;
    }

    public function readCookie($name) {
        $data = false;
        if (isset($_COOKIE[$name])) {
            $data = maybe_unserialize(urldecode($_COOKIE[$name]));
        }
        return $data;
    }

    public function clearCookie($name) {
        if (isset($_COOKIE[$name]))
            unset($_COOKIE[$name]);
        if (!headers_sent())
            $result = setcookie($name, ' ', time() - 5832000);
        //setcookie($cookieName, ' ', time() - YEAR_IN_SECONDS, SITECOOKIEPATH);
    }

    /**
     * trackData used by notification
     * @staticvar array $track
     * @param type $data
     * @param type $return
     * @return type
     */
    public function trackData($data, $return = false) {
        static $track = array();
        if ($return) {
            // format data for output

            $trackRet = $this->formatData($track);
            // reset track data
            $track = array();
            return $trackRet;
        }
        $track = array_merge($track, $data);
    }

    /**
     * formatData used by trackData and notification
     * @param type $data
     * @param type $level
     * @return string
     */
    public function formatData($data, $level = 0) {
        // tabular output format ;)
        $keystyle = ' style="background:#676767;font-weight:bold;color:#e1e1e1"';
        $valuestyle = ' style="background:#ddd;font-weight:normal;color:#121212"';
        $output = '';
        $data = (array) $data;
        foreach ($data as $k => &$v) {
            $output.='<tr>';
            if (!is_numeric($k))
                $output.='<td' . $keystyle . '>' . $k . '</td><td' . $valuestyle . '>';
            else
                $output.='<td colspan=2' . $valuestyle . '>';

            if (is_array($v) || is_object($v))
                $output.=$this->formatData((array) $v, $level + 1);
            else {
                $out = CRED_StaticClass::$out;

                //########### START # String Tra nslati on WPML ##################################################
                $new_v = cred_translate($k . " " . $v, $v, CRED_StaticClass::$_current_prefix . CRED_StaticClass::$_current_post_title . '-' . CRED_StaticClass::$_current_form_id);

                if ($v == $new_v) {
                    $field_id = "";

                    if (isset($out['fields']['post_fields'][$k])) {
                        $field = $out['fields']['post_fields'][$k];
                        if ($field['type'] == 'select' ||
                                $field['type'] == 'radio') {
                            if (isset($field['data']['options']))
                                foreach ($field['data']['options'] as $id => $values) {
                                    if (isset($values['title']) && $values['title'] == $v) {
                                        $field_id = $id;
                                        break;
                                    }
                                }
                        }
                    }
                    if (!empty($field_id)) {
                        $new_v = cred_translate('field ' . $field_id . ' option ' . $k . ' title', $v, 'plug in Types');
                    }
                }
                //########### END # String Translation WPML ##################################################

                $output.=$new_v;
            }

            $output.= '</td></tr>';
        }
        if (0 == $level)
            $output = '<table style="position:relative;width:100%;"><tbody>' . $output . '</tbody></table>';
        else
            $output = '<table><tbody>' . $output . '</tbody></table>';
        return $output;
    }

    // get all form field values to be used in validation hooks
    public function get_form_field_values() {
        $fields = array();

        //FIX validation for files elements
        $files = array();
        foreach ($_FILES as $name => $value) {
            $files[$name] = (isset($_REQUEST[$name]) && !empty($_REQUEST[$name])) ? $_REQUEST[$name] : $value['name'];
        }
        $reqs = array_merge($_REQUEST, $files);

        $zebraForm = $this->_formBuilder->_zebraForm;

        foreach ($zebraForm->form_properties['fields'] as $n => $field) {
            if ($field['type'] != 'messages') {
                $value = isset($reqs[$field['name']]) ? $reqs[$field['name']] : "";
                $fields[$field['name']] = array(
                    'value' => $value,
                    'name' => $field['name'],
                    'type' => $field['type'],
                    'repetitive' => isset($field['data']['repetitive']) ? $field['data']['repetitive'] : false
                );
                //Fix https://icanloc alize. basecamphq.com/projects/7393061-toolset/todo_items/192856893/comments                
                //Added file_data for validation
                if (isset($_FILES) && !empty($_FILES)) {
                    if (isset($_FILES[$field['name']])) {
                        $fields[$field['name']]['file_data'] = $_FILES[$field['name']];
                    }
                }
                //##############################################################################################
                if (isset($field['plugin_type']) && !empty($field['plugin_type'])) {
                    $fields[$field['name']]['plugin_type'] = $field['plugin_type'];
                }
                if (isset($field['data']['validate']) && !empty($field['data']['validate'])) {
                    $fields[$field['name']]['validation'] = $field['data']['validate'];
                }
            }
        }
        return $fields;
    }

    // set form fields values to new values (after validation)
    public function set_form_field_values($fields) {
        return;
    }

    /**
     * cred_translate_field
     * @deprecated since version 1.9
     * @global type $post
     * @global type $post
     * @staticvar array $_count_
     * @staticvar boolean $wpExtensions
     * @param string $name
     * @param type $field
     * @param type $additional_options
     * @return type
     */
    public function cred_translate_field($name, &$field, $additional_options = array()) {
        //cred_log("cred_translate_field");
        // allow multiple submit buttons
        static $_count_ = array(
            'submit' => 0
        );

        static $wpExtensions = false;
        // get refs here
        $globals = CRED_StaticClass::$_staticGlobal;
        if (false === $wpExtensions) {
            $wpMimes = $globals['MIMES'];
            $wpExtensions = implode(',', array_keys($wpMimes));
        }
        // get refs here
        $form = $this->_formBuilder->_formData;
        $supported_date_formats = CRED_StaticClass::$_supportedDateFormats;

        $postData = $this->_formBuilder->_postData;
        $zebraForm = $this->_formBuilder->_zebraForm;

        // extend additional_options with defaults
        extract(array_merge(
                        array(
            'preset_value' => null,
            'placeholder' => null,
            'value_escape' => false,
            'make_readonly' => false,
            'is_tax' => false,
            'max_width' => null,
            'max_height' => null,
            'single_select' => false,
            'generic_type' => null,
            'urlparam' => ''
                        ), $additional_options
        ));

        // add the "name" element
        // the "&" symbol is there so that $obj will be a reference to the object in PHP 4
        // for PHP 5+ there is no need for it
        $type = 'text';
        $attributes = array();
        if (isset($class))
            $attributes['class'] = $class;
        $value = '';

        $name_orig = $name;
        $field["name"] = cred_translate($field["name"], $field["name"], $form->getForm()->post_type . "-" . $form->getForm()->post_title . "-" . $form->getForm()->ID);

        if (!$is_tax) {
            // if not taxonomy field
            if (isset($placeholder) && !empty($placeholder) && is_string($placeholder)) {
                // use translated value by WPML if exists
                $placeholder = cred_translate(
                        'Value: ' . $placeholder, $placeholder, 'cred-form-' . $form->getForm()->post_title . '-' . $form->getForm()->ID
                );
                $additional_options['placeholder'] = $placeholder;
            }

            //before the post meta
            if ($postData && isset($postData->fields[$name_orig])) {
                //cred_log("POST DATA");
                //cred_log($postData->fields[$name_orig]);
                if (is_array($postData->fields[$name_orig]) && count($postData->fields[$name_orig]) > 1) {
                    if (isset($field['data']['repetitive']) &&
                            $field['data']['repetitive'] == 1) {
                        $data_value = $postData->fields[$name_orig];
                    }
                } else {
                    $data_value = $postData->fields[$name_orig][0];
                }
            } elseif (isset($preset_value) &&
                    !empty($preset_value) &&
                    is_string($preset_value)
            ) {
                // use translated value by WPML if exists
                $data_value = cred_translate(
                        'Value: ' . $preset_value, $preset_value, 'cred-form-' . $form->getForm()->post_title . '-' . $form->getForm()->ID
                );

                $additional_options['preset_value'] = $placeholder;
            } elseif ($_POST && isset($_POST) && isset($_POST[$name_orig])) {
                $data_value = $_POST[$name_orig];
            }
            // allow field to get value through url parameter
            elseif (is_string($urlparam) && !empty($urlparam) && isset($_GET[$urlparam])) {
                //cred_log("URL PARAM");
                //cred_log($urlparam);
                // use translated value by WPML if exists
                $data_value = urldecode($_GET[$urlparam]);
            } else {
                if (!isset($preset_value))
                    $data_value = null;
            }

            //cred_log($field['name'] . " " . $field['type']);
            //cred_log("data value");
            //cred_log($data_value);
            // save a map between options / actual values for these types to be used later
            if (in_array($field['type'], array('checkboxes', 'radio', 'select', 'multiselect'))) {
                //cred_log($field);                
                $tmp = array();
                foreach ($field['data']['options'] as $optionKey => $optionData) {
                    if ($optionKey !== 'default' && is_array($optionData))
                        $tmp[$optionKey] = ('checkboxes' == $field['type']) ? @$optionData['set_value'] : $optionData['value'];
                }
                CRED_StaticClass::$out['field_values_map'][$field['slug']] = $tmp;
                unset($tmp);
                unset($optionKey);
                unset($optionData);
            }

            if (isset($data_value))
                $value = $data_value;

            switch ($field['type']) {
                case 'form_messages' :
                    $type = 'messages';

                    break;

                case 'form_submit':
                    $type = 'submit';

                    if (isset($preset_value) &&
                            !empty($preset_value) &&
                            is_string($preset_value)
                    ) {

                        //cred_log("preset_value");
                        //cred_log($preset_value);
                        // use translated value by WPML if exists
                        $data_value = cred_translate(
                                'Value: ' . $preset_value, $preset_value, 'cred-form-' . $form->getForm()->post_title . '-' . $form->getForm()->ID
                        );
                        $value = $data_value;

                        $additional_options['preset_value'] = $placeholder;
                    }

                    // allow multiple submit buttons
                    $name.='_' . ++$_count_['submit'];
                    break;

                case 'recaptcha':
                    $type = 'recaptcha';
                    $value = '';
                    $attributes = array(
                        'error_message' => $this->getLocalisedMessage('enter_valid_captcha'),
                        'show_link' => $this->getLocalisedMessage('show_captcha'),
                        'no_keys' => __('Enter your ReCaptcha keys at the CRED Settings page in order for ReCaptcha API to work', 'wp-cred')
                    );
                    if (false !== $globals['RECAPTCHA']) {
                        $attributes['public_key'] = $globals['RECAPTCHA']['public_key'];
                        $attributes['private_key'] = $globals['RECAPTCHA']['private_key'];
                    }
                    if (1 == CRED_StaticClass::$out['count'])
                        $attributes['open'] = true;
                    // used to load additional js script
                    CRED_StaticClass::$out['has_recaptcha'] = true;
                    break;
                case 'audio':
                case 'video':
                case 'file':
                    $type = 'cred' . $field['type'];

                    global $post;
                    if (isset($post))
                        $attachments = get_children(
                                array(
                                    'post_parent' => $post->ID,
                                    //'post_mime_type' => 'image',
                                    'post_type' => 'attachment'
                                )
                        );
                    if (isset($attachments))
                        foreach ($attachments as $pid => $attch) {
                            $guid = $attch->guid;
                            if (is_array($value)) {
                                foreach ($value as $n => &$v) {
                                    if ((isset($v) && !empty($v)) && basename($guid) == basename($v)) {
                                        $v = $guid;
                                        break;
                                    }
                                }
                            } else {
                                if ((isset($value) && !empty($value)) && basename($guid) == basename($value)) {
                                    $value = $guid;
                                }
                            }
                        }

                    break;

                case 'image':
                    //$type='file';  
                    $type = 'cred' . $field['type'];
                    // show previous post featured image thumbnail
                    if ('_featured_image' == $name) {
                        $value = '';
                        if (isset($postData->extra['featured_img_html'])) {
                            $attributes['display_featured_html'] = $value = $postData->extra['featured_img_html'];
                        }
                    }

                    global $post;
                    if (isset($post))
                        $attachments = get_children(
                                array(
                                    'post_parent' => $post->ID,
                                    //'post_mime_type' => 'image',
                                    'post_type' => 'attachment'
                                )
                        );

                    if (isset($attachments))
                        foreach ($attachments as $pid => $attch) {
                            $guid = $attch->guid;
                            if (is_array($value)) {
                                foreach ($value as $n => &$v) {
                                    if ((isset($v) && !empty($v)) && basename($guid) == basename($v)) {
                                        $v = $guid;
                                        break;
                                    }
                                }
                            } else {

                                if ((isset($value) && !empty($value)) && basename($guid) == basename($value)) {
                                    $value = $guid;
                                }
                            }
                        }
                    break;

                case 'date':
                    if (!function_exists('adodb_mktime')) {
                        require_once WPTOOLSET_FORMS_ABSPATH . '/lib/adodb-time.inc.php';
                    }
                    $type = 'date';
                    $value = array();
                    $format = get_option('date_format', '');
                    if (empty($format)) {
                        $format = $zebraForm->getDateFormat();
                        $format .= " h:i:s";
                    }
                    $attributes = array_merge($additional_options, array('format' => $format, 'readonly_element' => false, 'repetitive' => isset($field['data']['repetitive']) ? $field['data']['repetitive'] : 0));
                    if (
                            isset($data_value) &&
                            !empty($data_value) /* &&
                      (is_numeric($data_value) || is_int($data_value) || is_long($data_value)) */
                    ) {
                        if (is_array($data_value)) {
                            foreach ($data_value as $dv) {
                                if (isset($dv['datepicker']))
                                    $value[] = array('timestamp' => $dv['datepicker']);
                                else
                                    $value[] = array('timestamp' => $dv);
                            }
                        } else {
                            $value['timestamp'] = $data_value;
                        }
                    }
                    break;

                case 'select':
                case 'multiselect':

                    $type = 'select';
                    $value = array();

                    $titles = array();
                    $attributes = array();
                    $default = array();

                    if ($field['type'] == 'multiselect') {
                        $attributes = array_merge($additional_options, array('multiple' => 'multiple'));
                    } else {
                        $attributes = array_merge($additional_options);
                    }

                    $attributes['options'] = array();

                    foreach ($field['data']['options'] as $key => $option) {
                        $index = $key; //$option['value'];
                        if ('default' === $key && $option != 'no-default') {
                            $default[] = $option;
                        } else {
                            if (is_admin()) {
                                if (isset($option['title']))
                                    cred_translate_register_string('cred-form-' . $form->getForm()->post_title . '-' . $form->getForm()->ID, $field['slug'] . " " . $option['title'], $option['ti tle'], false);
                            }
                            if (isset($option['title'])) {
                                $option = $this->translate_option($option, $key, $form, $field);
                                $attributes['options'][$index] = $option['title'];

                                if (isset($data_value) &&
                                        ($data_value == $option['value'] ||
                                        (is_array($data_value) && (array_key_exists($option['value'], $data_value) ||
                                        in_array($option['value'], $data_value))))) {

                                    if ('select' == $field['type']) {
                                        $titles[] = $key;
                                        $value = $option['value'];
                                    } else {
                                        $value = $data_value;
                                    }
                                }
                                if (isset($option['dummy']) && $option['dummy'])
                                    $attributes['dummy'] = $key;
                            }
                        }
                    }

                    if ($field['type'] == 'multiselect') {
                        if (empty($value) && !empty($default)) {
                            $value = $default;
                        }
                    } else {
                        if (empty($titles) && !empty($default[0])) {
                            $titles = isset($field['data']['options'][$default[0]]['value']) ? $field['data']['options'][$default[0]]['value'] : "";
                        }
                        $attributes['actual_value'] = isset($data_value) && !empty($data_value) ? $data_value : $titles;
                    }
                    if (isset(CRED_StaticClass::$out['field_values_map'][$field['slug']]))
                        $attributes['actual_options'] = CRED_StaticClass::$out['field_values_map'][$field['slug']];

                    break;

                case 'radio':
                    $type = 'radios';
                    $value = array();
                    $titles = array();
                    $attributes = array();
                    $attributes = array_merge($additional_options);
                    $default = '';

                    $default = isset($field['data']['options']['default']) ? $field['data']['options']['default'] : "";

                    if (isset($field['data']['options']['default']))
                        unset($field['data']['options']['default']);

                    $set_default = false;
                    foreach ($field['data']['options'] as $key => &$option) {
                        if (isset($option['value']))
                            $option['value'] = str_replace("\\", "", $option['value']);

                        if (!$set_default && $key == $default) {
                            $set_default = true;
                            $default = $option['value'];
                        }

                        $index = $key;

                        if (is_admin()) {
                            //register strings on form save
                            cred_translate_register_string('cred-form-' . $form->getForm()->post_title . '-' . $form->getForm()->ID, $field['slug'] . " " . $option['title'], $option['title'], false);
                        }
                        $option = $this->translate_option($option, $key, $form, $field);

                        $titles[$index] = $option['title'];

                        if (isset($data_value) && $data_value == $option['value']) {
                            $attributes = isset($option['value']) ? $option['value'] : $key;
                            $value = isset($option['value']) ? $option['value'] : $key;
                        }
                    }

                    if (!isset($data_value) && !empty($default)) {
                        $attributes = $default;
                    }
                    $def = $attributes;
                    $attributes = array('default' => $def);
                    $attributes['actual_titles'] = $titles;

                    if (isset(CRED_StaticClass::$out['field_values_map'][$field['slug']]))
                        $attributes['actual_values'] = CRED_StaticClass::$out['field_values_map'][$field['slug']];

                    foreach ($attributes['actual_values'] as $k => &$option) {
                        $option = str_replace("\\", "", $option);
                    }

                    break;

                case 'checkboxes':
                    $type = 'checkboxes';
                    $save_empty = isset($field['data']['save_empty']) ? $field['data']['save_empty'] : false;
                    $value = array();


                    if ($field['type'] == 'checkboxes') {
                        if (isset($postData->fields[$name_orig]) &&
                                isset($postData->fields[$name_orig][0]) && is_array($postData->fields[$name_orig][0])) {
                            $data_value = array();
                            foreach ($postData->fields[$name_orig][0] as $key => $value) {
                                if ($save_empty && $value != 0)
                                    $data_value[] = $key;
                            }
                        }
                    }

                    //CRED_StaticClass::_pre($field['data']['options']);
                    if (isset($data_value) && !empty($data_value)) {
                        if (!is_array($data_value)) {
                            foreach ($field['data']['options'] as $v => $v1) {
                                if ($v1[' set_valu e'] == $data_value) {
                                    $data_value = array($v => $data_value);
                                }
                            }
                        } else {
                            if (count(array_filter(array_keys($data_value), 'is_string')) > 0) {
                                $new_data_value = array();
                                foreach ($field['data']['options'] as $v => $v1) {
                                    if (in_array($v1['se t_value'], $data_value)) {
                                        $new_data_value[$v] = $v1['set_value'];
                                    }
                                }
                                $data_value = $new_data_value;
                                unset($new_data_value);
                            }
                        }
                        foreach ($data_value as $v => $v1) {
                            if ($save_empty || $field['cred_gen eric'] == 1) {
                                $value[$v] = $v1;
                            } else
                                $value[$v] = 1;
                        }
                    }

                    $titles = array();
                    $attributes = array();
                    $attributes = array_merge($additional_options);

                    if (isset($data_value) && !is_array($data_value))
                        $data_value = array($data_value);

                    foreach ($field['data']['options'] as $key => $option) {
                        if (is_admin()) {
                            //register strings on form save
                            cred_translate_register_string('cred-form-' . $form->getForm()->post_title . '-' . $form->getForm()->ID, $field['slug'] . " " . $option['title'], $option['title'], false);
                        }
                        $option = $this->translate_option($option, $key, $form, $field);
                        $index = $key;
                        $titles[$index] = $option['title'];

                        if (($save_empty && $value == 0) || empty($value)) {
                            $value = array();
                            if (isset($data_value) && !empty($data_value) && isset($data_value[$index]))
                                $value[$index] = $data_value[$index];
                            else {
                                $value[$index] = 0;
                            }
                        }
                        if (isset($option['checked']) && $option['checked'] && null === $data_value) {
                            $attributes[] = $index;
                        } elseif (isset($data_value) && isset($data_value[$index]) /* && in_array($index,$data_value) */) {
                            if (
                                    !(isset($field['data']['save_empty']) && 'yes' == $field['data']['save_empty'] && (0 === $data_value[$index] || '0' === $data_value[$index]))
                            )
                                $attributes[] = $index;
                        }
                    }
                    $def = $attributes;
                    $attributes = array('default' => $def);
                    $attributes['actual_titles'] = $titles;
                    if (isset(CRED_StaticClass::$out['field_values_map'][$field['slug']]))
                        $attributes['actual_values'] = CRED_StaticClass::$out['field_values_map'][$field['slug']];
                    break;

                case 'checkbox':
                    $save_empty = isset($field['data']['save_empty']) ? $field[
                            'data']['save_empty'] : false;
                    //If save empty and $_POST is set but checkbox is not set data value 0
                    if (isset($data_value) &&
                            $data_value == 1 &&
                            $save_empty == 'no' &&
                            isset($_POST) && !empty($_POST) && !isset($_POST[$name_orig]))
                        $data_value = 0;

                    $type = 'checkbox';

                    $value = $field['data']['set_value'];
                    $attributes = array();
                    if (isset($data_value) && $data_value == $value)
                        $attributes = array('checked' => 'checked');
                    $attributes = array_merge($attributes, $additional_options);
                    if (is_admin()) {
                        //register strings on form save
                        cred_translate_register_string('cred-form-' . $form->getForm()->post_title . '-' . $form->getForm()->ID, $field['slug'], $field['name'], false);
                    }
                    $field['name'] = cred_translate($field['slug'], $field['name'], 'cred-form-' . $form->getForm()->post_title . '-' . $form->getForm()->ID);
                    break;

                case 'textarea':
                    $type = 'textarea';
                    $attributes = array_merge($additional_options);
                    break;

                case 'wysiwyg':
                    $type = 'wysiwyg';
                    $attributes = array_merge($additional_options, array('disable_xss_filters' => true));
                    //cred_log($form->fields);
                    if ('post_content' == $name && isset($form->fields['form_settings']->form['has_media_button']) && $form->fields['form_settings']->form['has_media_button'])
                        $attributes['has_media_button'] = true;
                    break;

                case 'integer':
                    $type = 'integer';
                    $attributes = array_merge($attributes, $additional_options);
                    break;

                case 'numeric':
                    $type = 'numeric';
                    $attributes = array_merge($attributes, $additional_options);
                    break;

                case 'phone':
                    $type = 'phone';
                    $attributes = array_merge($attributes, $additional_options);
                    break;

                case 'embed':
                case 'url':
                    $type = 'url';
                    $attributes = array_merge($attributes, $additional_options);
                    break;

                case 'email':
                    $type = 'email';
                    $attributes = array_merge($attributes, $additional_options);
                    break;

                case 'colorpicker':
                    $type = 'colorpicker';
                    $attributes = array_merge($attributes, $additional_options);
                    break;

                case 'textfield':
                    $type = 'textfield';
                    $attributes = array_merge($attributes, $additional_options);
                    break;

                case 'password':
                    $type = 'password';
                    $attributes = array_merge($attributes, $additional_options);
                    break;

                case 'hidden':
                    $type = 'hidden';
                    $attributes = array_merge($attributes, $additional_options);
                    break;

                case 'skype':
                    $type = 'skype';
                    //if for some reason i receive data_value as array but it is not repetitive i need to get as not array of array
                    //if (isset($field['data']['repetitive']) && $field['data']['repetitive'] == 1)
                    if (isset($field['data']['repetitive']) && $field['data']['repetitive'] == 0 && isset($data_value[0]))
                        $data_value = $data_value[0];

                    if (isset($field['data']['repetitive']) && $field['data']['repetitive'] == 1 && !isset($data_value[0]))
                        $data_value = array($data_value);

                    if (isset($data_value)) {
                        if (isset($field['data']['repetitive']) && $field['data']['repetitive'] == 0)
                            $value = $data_value;
                        else {
                            if (is_string($data_value))
                                $data_value = array('skypename' => $data_value, 'style' => '');
                            $value = $data_value;
                        }
                    } else {
                        $value = array('skypename' => '', 'style' => '');
                        $data_value = $value;
                    }

                    $attributes = array(
                        'ajax_url' => admin_url('admin-ajax.php'),
                        'edit_skype_text' => $this->getLocalisedMessage('edit_skype_button'),
                        'value' => isset($data_value[0]['skypename']) ? $data_value[0]['skypename'] : $data_value['skypename'],
                        '_nonce' => wp_create_nonce('insert_skype_button')
                    );
                    $attributes = array_merge($attributes, $additional_options);
                    break;

                // everything else defaults to a simple text field
                default:
                    $type = 'textfield';
                    $attributes = array_merge($attributes, $additional_options);
                    break;
            }

            if (isset($attributes['make_readonly']) && !empty($attributes['make_readonly'])) {
                unset($attributes['make_readonly']);
                if (!is_array($attributes))
                    $attributes = array();
                $attributes['readonly'] = 'readonly';
            }

            // repetitive field (special care)
            if (isset($field['data']['repetitive']) && $field['data']['repetitive']) {
                $value = isset($postData->fields[$name_orig]) ? $postData->fields[$name_orig] : isset($value) ? $value : array();
                $objs = $zebraForm->add($type, $name, $value, $attributes, $field);
            } else {
                $objs = $zebraForm->add($type, $name, $value, $attributes, $field);
            }
        } else {
            // taxonomy field or auxilliary taxonomy field (eg popular terms etc..)
            if (!array_key_exists('master_taxonomy', $field)) { // taxonomy field
                if ($field['hierarchical']) {
                    if (in_array($preset_value, array('checkbox', 'select')))
                        $tax_display = $preset_value;
                    else
                        $tax_display = 'checkbox';
                }

                if ($postData && isset($postData->taxonomies[$name_orig])) {
                    if (!$field['hierarchical']) {
                        $data_value = array(
                            'terms' => $postData->taxonomies[$name_orig]['terms'],
                            'add_text' => $this->getLocalisedMessage('add_taxonomy'),
                            'remove_text' => $this->getLocalisedMessage('remove_taxonomy'),
                            'ajax_url' => admin_url('admin-ajax.php'),
                            'auto_suggest' => true,
                            'show_popular_text' => $this->getLocalisedMessage('show_popular'),
                            'hide_popular_text' => $this->getLocalisedMessage('hide_popular'),
                            'show_popular' => $show_popular
                        );
                    } else {
                        $data_value = array(
                            'terms' => $postData->taxonomies[$name_orig]['terms'],
                            'all' => $field['all'],
                            'add_text' => $this->getLocalisedMessage('add_taxonomy'),
                            'add_new_text' => $this->getLocalisedMessage('add_new_taxonomy'),
                            'parent_text' => __('-- Parent --', 'wp-cred'),
                            'type' => $tax_display,
                            'single_select' => $single_select
                        );
                    }
                } else {
                    if (!$field['hierarchical']) {
                        $data_value = array(
                            //'terms'=>array(),
                            'add_text' => $this->getLocalisedMessage('add_taxonomy'),
                            'remove_text' => $this->getLocalisedMessage('remove_taxonomy'),
                            'ajax_url' => admin_url('admin-ajax.php'),
                            'auto_suggest' => true,
                            'show_popular_text' => $this->getLocalisedMessage('show_popular'),
                            'hide_popular_text' => $this->getLocalisedMessage('hide_popular'),
                            'show_popular' => $show_popular
                        );
                    } else {
                        $data_value = array(
                            'all' => $field['all'],
                            'add_text' => $this->getLocalisedMessage('add_taxonomy'),
                            'add_new_text' => $this->getLocalisedMessage('add_new_taxonomy'),
                            'parent_text' => __('-- Parent --', 'wp-cred'),
                            'type' => $tax_display,
                            'single_select' => $single_select
                        );
                    }
                }

                // if not hierarchical taxonomy
                if (!$field['hierarchical']) {
                    $objs = /* & */ $zebraForm->add('taxonomy', $name, $value, $data_value);
                } else {
                    $objs = /* & */ $zebraForm->add('taxonomyhierarchical', $name, $value, $data_value);
                }

                // register this taxonomy field for later use by auxilliary taxonomy fields
                CRED_StaticClass::$out['taxonomy_map']['taxonomy'][$name_orig] = &$objs;
                // if a taxonomy auxiliary field exists attached to this taxonomy, add this taxonomy id to it
                if (isset(CRED_StaticClass::$out['taxonomy_map']['aux'][$name_orig])) {
                    CRED_StaticClass::$out['taxonomy_map']['aux'][$name_orig]->set_attributes(array('master_taxonomy_id' => $objs->attributes['id']));
                }
            } else { // taxonomy auxilliary field (eg most popular etc..)
                if (isset($preset_value))
                // use translated value by WPML if exists
                    $data_value = cred_translate(
                            'Value: ' . $preset_value, $preset_value, 'cred-form-' . $form->form->post_title . '-' . $form->form->ID
                    );
                else
                    $data_value = null;
            }
        }

        return $objs;
    }

    /**
     * translate_option function related to select/multiselect radios checkboxes checkbox
     * @param type $option
     * @param type $key
     * @param type $form
     * @param type $field
     * @return type
     */
    public function translate_option($option, $key, $form, $field) {
        if (!isset($option['title']))
            return $option;
        $original = $option['title'];
        $option['title'] = cred_translate(
                $field['slug'] . " " . $option['title'], $option['title'], 'cred-form-' . $form->getForm()->post_title . '-' . $form->getForm()->ID
        );
        if ($original == $option['title']) {
            // Try translating with types context
            $option['title'] = cred_translate(
                    'field ' . $field['id'] . ' option ' . $key . ' title', $option['title'], 'plugin Types');
        }

        return $option;
    }

    // translate each cred field to a customized Zebra_Form field
    public function translate_field($name, &$field, $additional_options = array()) {
        return array();
        // allow multiple submit buttons
        static $_count_ = array(
            'submit' => 0
        );

        $count = ($field['type'] == 'form_submit') ? '_' . ($_count_['submit'] ++) : "";
        $f = "";

        if ($field['type'] == 'taxonomy_hierarchical' || $field['type'] == 'taxonomy_plain') {
            $f = "_" . $field['name'];
        } else {
            if (isset($field['master_taxonomy']) && isset($field['type'])) {
                $f = "_" . $field['master_taxonomy'] . "_" . $field['type'];
            } else {
                if (isset($field['id'])) {
                    $f = "_" . $field['id'];
                } else {
                    
                }
            }
        }
        return array("cred_form_" . CRED_StaticClass::$out['prg_id'] . $f . $count);
    }

    /*
     *   /END Implement Friendly Interface
     *
     */

    private function removeFromArray($array, $key, $value) {
        if (!array_key_exists($key, $array)) {
            return $array;
        }
        if (!count($array[$key])) {
            return $array;
        }
        $array[$key] = array_diff($array[$key], array($value));
        return $array;
    }

}
