<?php



// TODO add hook when cloning, so 3rd-party can add its own
// TODO use WP Cache object to cache queries(in base model) and templates(in loader DONE)
/* removed */
// configuration constants
define('CRED_NAME', 'CRED');
define('CRED_CAPABILITY', 'manage_options');
define('CRED_FORMS_CUSTOM_POST_NAME', 'cred-form');
//CredUserForms
define('CRED_USER_FORMS_CUSTOM_POST_NAME', 'cred-user-form');
// for module manager cred support
define('_CRED_MODULE_MANAGER_KEY_', 'cred');
define('_CRED_MODULE_MANAGER_USER_KEY_', 'cred-user');
//define('CRED_DEBUG', true);

/** @deprecated Since 1.9. Use only CRED_ABSPATH. */
define('CRED_ROOT_PLUGIN_PATH', CRED_ABSPATH . '/library/toolset/cred');

/** @deprecated Since 1.9. Use only CRED_ABSPATH. */
define('CRED_ROOT_CLASSES_PATH', CRED_ROOT_PLUGIN_PATH . '/classes');

/** @deprecated Since 1.9. Use only CRED_ABSPATH. */
define('CRED_FILE_PATH', realpath(__FILE__));

/** @deprecated Since 1.9. Use only CRED_ABSPATH. */
define('CRED_FILE_NAME', basename(CRED_FILE_PATH));

/** @deprecated Since 1.9. Use only CRED_ABSPATH. */
define('CRED_PLUGIN_PATH', CRED_ROOT_PLUGIN_PATH . "/embedded");

/** @deprecated Since 1.9. Use only CRED_ABSPATH. */
define('CRED_ASSETS_PATH', CRED_PLUGIN_PATH . '/assets');

/** @deprecated Since 1.9. Use only CRED_ABSPATH. */
define('CRED_CLASSES_PATH', CRED_PLUGIN_PATH . '/classes');

/** @deprecated Since 1.9. Use only CRED_ABSPATH. */
define('CRED_COMMON_PATH', CRED_PLUGIN_PATH . '/classes/common');

/** @deprecated Since 1.9. Use only CRED_ABSPATH. */
define('CRED_CONTROLLERS_PATH', CRED_PLUGIN_PATH . '/controllers');

/** @deprecated Since 1.9. Use only CRED_ABSPATH. */
define('CRED_MODELS_PATH', CRED_PLUGIN_PATH . '/models');

/** @deprecated Since 1.9. Use only CRED_ABSPATH. */
define('CRED_VIEWS_PATH', CRED_PLUGIN_PATH . '/views');

/** @deprecated Since 1.9. Use only CRED_ABSPATH. */
define('CRED_TABLES_PATH', CRED_PLUGIN_PATH . '/views/tables');

/** @deprecated Since 1.9. Use only CRED_ABSPATH. */
define('CRED_TEMPLATES_PATH', CRED_PLUGIN_PATH . '/views/templates');
//define('CRED_LOCALE_PATH_DEFAULT',CRED_PLUGIN_FOLDER.'/locale');// Old definition, DEPRECATED

/** @deprecated Since 1.9. Use only CRED_ABSPATH. */
define('CRED_LOGS_PATH', CRED_PLUGIN_PATH . '/logs');

/** @deprecated Since 1.9. Use only CRED_ABSPATH. */
define('CRED_INI_PATH', CRED_PLUGIN_PATH . '/classes/ini');

// allow to define locale path externally
/*
  if (!defined('CRED_LOCALE_PATH')) {
  define('CRED_LOCALE_PATH',CRED_LOCALE_PATH_DEFAULT);// Old definition, DEPRECATED
  }
 */
if (!interface_exists('CRED_Friendable')) {
    /*
     *   Friend Classes (quasi-)Design Pattern
     */

    interface CRED_Friendable {
        
    }

    interface CRED_FriendableStatic {
        
    }

    interface CRED_Friendly {
        
    }

    interface CRED_FriendlyStatic {
        
    }

}

// include loader
include(CRED_PLUGIN_PATH . '/loader.php');

/** @deprecated Since 1.9. Use only CRED_ABSURL. */
define('CRED_PLUGIN_URL', CRED_ABSURL . '/library/toolset/cred/embedded');

/** @deprecated Since 1.9. Use only CRED_ABSURL. */
define('CRED_FILE_URL', CRED_PLUGIN_URL . '/' . CRED_FILE_NAME);

/** @deprecated Since 1.9. Use only CRED_ABSURL. */
define('CRED_ASSETS_URL', CRED_PLUGIN_URL . '/assets');



if (!function_exists('cred_loaded_common_dependencies')) {
    add_action('after_setup_theme', 'cred_loaded_common_dependencies', 11);

    function cred_loaded_common_dependencies() {
        require_once dirname(__FILE__) . '/embedded/classes/CRED_help_videos.php';
        require_once dirname(__FILE__) . '/embedded/classes/CRED_scripts_manager.php';
    }

}

add_filter('plugin_row_meta', 'toolset_cred_plugin_plugin_row_meta', 10, 4);

function toolset_cred_plugin_plugin_row_meta($plugin_meta, $plugin_file, $plugin_data, $status) {
    $this_plugin = basename(CRED_FILE_PATH) . '/plugin.php';
    if ($plugin_file == $this_plugin) {
        $ver2url = strtolower(str_replace(".", "-", CRED_FE_VERSION));
        $plugin_meta[] = sprintf(
                '<a href="%s" target="_blank">%s</a>', ' https://wp-types.com/version/cred-' . $ver2url . '/?utm_source=credplugin&utm_campaign=cred&utm_medium=release-notes-plugin-row&utm_term=CRED ' . CRED_FE_VERSION . ' release notes', __('CRED ' . CRED_FE_VERSION . ' release notes', 'wpv-views')
        );
    }
    return $plugin_meta;
}

/** @deprecated Since 1.9. Use only CRED_ABSPATH. */
define('CRED_LOCALE_PATH', CRED_PLUGIN_PATH . '/locale');

// whether to try to load assets in concatenated form, much faster
// tested on single site/multisite subdomains/multisite subfolders
if (!defined('CRED_CONCAT_ASSETS'))
    define('CRED_CONCAT_ASSETS', false); // I've disabled this as it was causing compatibility issues with font-awesome in Views 1.3








// enable CRED_DEBUG, on top of this file
/* cred_log($_SERVER);
  cred_log(CRED_Loader::getDocRoot());
  cred_log(CRED_Loader::getBaseUrl());
  cred_log(CRED_PLUGIN_URL); */

// register assets
CRED_Loader::add('assets', array(
    'STYLE' => array(
        'cred_template_style' => array(
            'loader_url' => CRED_FILE_URL,
            'loader_path' => CRED_FILE_PATH,
            'version' => CRED_FE_VERSION,
            'dependencies' => array('wp-admin', 'colors-fresh', 'toolset-font-awesome', 'cred_cred_style_nocodemirror_dev'),
            'path' => CRED_ASSETS_URL . '/css/gfields.css',
            'src' => CRED_ASSETS_PATH . '/css/gfields.css'
        ),
        'toolset-font-awesome' => array(
            'loader_url' => CRED_FILE_URL,
            'loader_path' => CRED_FILE_PATH,
            'version' => CRED_FE_VERSION,
            'dependencies' => null,
            'path' => CRED_ASSETS_URL . '/common/css/font-awesome.min.css',
            'src' => CRED_ASSETS_PATH . '/common/css/font-awesome.min.css'
        ),
        'cred_cred_style_dev' => array(
            'loader_url' => CRED_FILE_URL,
            'loader_path' => CRED_FILE_PATH,
            'version' => CRED_FE_VERSION,
            'dependencies' => array('toolset-font-awesome', 'toolset-meta-html-codemirror-css-hint-css', 'toolset-meta-html-codemirror-css', 'wp-jquery-ui-dialog', 'wp-pointer'),
            'path' => CRED_ASSETS_URL . '/css/cred.css',
            'src' => CRED_ASSETS_PATH . '/css/cred.css'
        ),
        'cred_cred_style_nocodemirror_dev' => array(
            'loader_url' => CRED_FILE_URL,
            'loader_path' => CRED_FILE_PATH,
            'version' => CRED_FE_VERSION,
            'dependencies' => array('toolset-font-awesome', 'wp-jquery-ui-dialog', 'wp-pointer'),
            'path' => CRED_ASSETS_URL . '/css/cred.css',
            'src' => CRED_ASSETS_PATH . '/css/cred.css'
        )
    )
));

// init loader for this specific plugin and load assets if needed
CRED_Loader::init(CRED_FILE_PATH);

// if called when loading assets, ;)
if (!function_exists('add_action'))
    return; /* exit; */

if (defined('ABSPATH')) {
// register dependencies
    CRED_Loader::add('dependencies', array(
        'CONTROLLER' => array(
            '%%PARENT%%' => array(
                array(
                    'class' => 'CRED_Abstract_Controller',
                    'path' => CRED_CONTROLLERS_PATH . '/Abstract.php'
                )
            ),
            'Forms' => array(
                array(
                    'class' => 'CRED_Forms_Controller',
                    'path' => CRED_CONTROLLERS_PATH . '/Forms.php'
                )
            ),
            'Posts' => array(
                array(
                    'class' => 'CRED_Posts_Controller',
                    'path' => CRED_CONTROLLERS_PATH . '/Posts.php'
                )
            ),
            'Settings' => array(
                array(
                    'class' => 'CRED_Settings_Controller',
                    'path' => CRED_CONTROLLERS_PATH . '/Settings.php'
                )
            ),
            'Import' => array(
                array(
                    'class' => 'CRED_Import_Controller',
                    'path' => CRED_CONTROLLERS_PATH . '/Import.php'
                )
            ),
            'Generic_Fields' => array(
                array(
                    'class' => 'CRED_Generic_Fields_Controller',
                    'path' => CRED_CONTROLLERS_PATH . '/Generic_Fields.php'
                )
            )
        ),
        'MODEL' => array(
            '%%PARENT%%' => array(
                array(
                    'class' => 'CRED_Abstract_Model',
                    'path' => CRED_MODELS_PATH . '/Abstract.php'
                )
            ),
            'Forms' => array(
                // dependencies
                array(
                    'path' => ABSPATH . '/wp-admin/includes/post.php'
                ),
                array(
                    'class' => 'CRED_Forms_Model',
                    'path' => CRED_MODELS_PATH . '/Forms.php'
                )
            ),
            'UserForms' => array(
                // dependencies
                array(
                    'path' => ABSPATH . '/wp-admin/includes/post.php'
                ),
                array(
                    'class' => 'CRED_User_Forms_Model',
                    'path' => CRED_MODELS_PATH . '/UserForms.php'
                )
            ),
            'Settings' => array(
                array(
                    'class' => 'CRED_Settings_Model',
                    'path' => CRED_MODELS_PATH . '/Settings.php'
                )
            ),
            'Import' => array(
                array(
                    'class' => 'CRED_Import_Model',
                    'path' => CRED_MODELS_PATH . '/Import.php'
                )
            ),
            'Fields' => array(
                array(
                    'class' => 'CRED_Fields_Model',
                    'path' => CRED_MODELS_PATH . '/Fields.php'
                )
            ),
            'UserFields' => array(
                array(
                    'class' => 'CRED_User_Fields_Model',
                    'path' => CRED_MODELS_PATH . '/UserFields.php'
                )
            )
        ),
        'TABLE' => array(
            '%%PARENT%%' => array(
                array(
                    'class' => 'WP_List_Table',
                    'path' => ABSPATH . '/wp-admin/includes/class-wp-list-table.php'
                )
            ),
            'Forms' => array(
                array(
                    'class' => 'CRED_Forms_List_Table',
                    'path' => CRED_TABLES_PATH . '/Forms.php'
                )
            ),
            'UserForms' => array(
                array(
                    'class' => 'CRED_User_Forms_List_Table',
                    'path' => CRED_TABLES_PATH . '/UserForms.php'
                )
            ),
            'Custom_Fields' => array(
                array(
                    'class' => 'CRED_Custom_Fields_List_Table',
                    'path' => CRED_TABLES_PATH . '/Custom_Fields.php'
                )
            ),
            'Custom_User_Fields' => array(
                array(
                    'class' => 'CRED_Custom_User_Fields_List_Table',
                    'path' => CRED_TABLES_PATH . '/Custom_User_Fields.php'
                )
            )
        ),
        'CLASS' => array(
            'CRED_Helper' => array(
                array(
                    'class' => 'CRED_Helper',
                    'path' => CRED_CLASSES_PATH . '/CRED_Helper.php'
                )
            ),
            'CRED' => array(
                array(
                    'class' => 'CRED_Admin',
                    'path' => CRED_ROOT_CLASSES_PATH . '/CRED_Admin.php'
                ),
                // make CRED Helper a depenency of CRED
                array(
                    'class' => 'CRED_Helper',
                    'path' => CRED_CLASSES_PATH . '/CRED_Helper.php'
                ),
                // make CRED Router a depenency of CRED
                array(
                    'class' => 'CRED_Router',
                    'path' => CRED_COMMON_PATH . '/Router.php'
                ),
                array(
                    'class' => 'CRED_CRED',
                    'path' => CRED_CLASSES_PATH . '/CRED.php'
                ),
                array(
                    'class' => 'CRED_PostExpiration',
                    'path' => CRED_CLASSES_PATH . '/CredPostExpiration.php'
                )
            ),
            'Form_Helper' => array(
                array(
                    'class' => 'CRED_Form_Builder_Helper',
                    'path' => CRED_CLASSES_PATH . '/Form_Builder_Helper.php'
                )
            ),
            'Form_Builder' => array(
                // make Form Helper a depenency of Form Builder
                array(
                    'class' => 'CRED_Form_Builder_Helper',
                    'path' => CRED_CLASSES_PATH . '/Form_Builder_Helper.php'
                ),
                array(
                    'class' => 'CRED_Form_Builder',
                    'path' => CRED_CLASSES_PATH . '/Form_Builder.php'
                )
            ),
            'Form_Translator' => array(
                array(
                    'class' => 'CRED_Form_Translator',
                    'path' => CRED_CLASSES_PATH . '/Form_Translator.php'
                )
            ),
            'XML_Processor' => array(
                array(
                    'class' => 'CRED_XML_Processor',
                    'path' => CRED_COMMON_PATH . '/XML_Processor.php'
                )
            ),
            'Mail_Handler' => array(
                array(
                    'class' => 'CRED_Mail_Handler',
                    'path' => CRED_COMMON_PATH . '/Mail_Handler.php'
                )
            ),
            'Notification_Manager' => array(
                array(
                    'class' => 'CRED_Notification_Manager',
                    'path' => CRED_CLASSES_PATH . '/Notification_Manager.php'
                )
            ),
            'Shortcode_Parser' => array(
                array(
                    'class' => 'CRED_Shortcode_Parser',
                    'path' => CRED_COMMON_PATH . '/Shortcode_Parser.php'
                )
            ),
            'Router' => array(
                array(
                    'class' => 'CRED_Router',
                    'path' => CRED_COMMON_PATH . '/Router.php'
                )
            ),
            'CRED_Editor_addon' => array(
                array(
                    'class' => 'CRED_Editor_addon',
                    'path' => CRED_CLASSES_PATH . '/cred-editor-addon.class.php'
                )
            )
        /* 'Settings_Manager' => array(
          array(
          'class' => 'CRED_Settings_Manager',
          'path' => CRED_COMMON_PATH.'/Settings_Manager.php'
          )
          ) */
        ),
        'VIEW' => array(
            'custom_fields' => array(
                array(
                    'path' => CRED_VIEWS_PATH . '/custom_fields.php'
                )
            ),
            'custom_user_fields' => array(
                array(
                    'path' => CRED_VIEWS_PATH . '/custom_user_fields.php'
                )
            ),
            'forms' => array(
                array(
                    'path' => CRED_VIEWS_PATH . '/forms.php'
                )
            ),
            'user_forms' => array(
                array(
                    'path' => CRED_VIEWS_PATH . '/user_forms.php'
                )
            ),
            'settings-wizard' => array(
                array(
                    'path' => CRED_VIEWS_PATH . '/settings_wizard.php'
                )
            ),
            'settings-export' => array(
                array(
                    'path' => CRED_VIEWS_PATH . '/settings_export.php'
                )
            ),
            'settings-styling' => array(
                array(
                    'path' => CRED_VIEWS_PATH . '/settings_styling.php'
                )
            ),
            'settings-other' => array(
                array(
                    'path' => CRED_VIEWS_PATH . '/settings_other.php'
                )
            ),
            'settings-recaptcha' => array(
                array(
                    'path' => CRED_VIEWS_PATH . '/settings_recaptcha.php'
                )
            ),
            'settings-filter' => array(
                array(
                    'path' => CRED_VIEWS_PATH . '/settings_filter.php'
                )
            ),
            'settings-user-forms' => array(
                array(
                    'path' => CRED_VIEWS_PATH . '/settings_user_forms.php'
                )
            ),
            'export' => array(
                array(
                    'path' => CRED_VIEWS_PATH . '/export.php'
                )
            ),
            'import-post-forms' => array(
                array(
                    'path' => CRED_VIEWS_PATH . '/import_post_forms.php'
                )
            ),
            'import-user-forms' => array(
                array(
                    'path' => CRED_VIEWS_PATH . '/import_user_forms.php'
                )
            ),
            'help' => array(
                array(
                    'path' => CRED_VIEWS_PATH . '/help.php'
                )
            )
        ),
        'TEMPLATE' => array(
            'insert-form-shortcode-button-extra' => array(
                'path' => CRED_TEMPLATES_PATH . '/insert-form-shortcode-button-extra.tpl.php'
            ),
            'insert-field-shortcode-button' => array(
                'path' => CRED_TEMPLATES_PATH . '/insert-field-shortcode-button.tpl.php'
            ),
            'insert-user-field-shortcode-button' => array(
                'path' => CRED_TEMPLATES_PATH . '/insert-user-field-shortcode-button.tpl.php'
            ),
            'insert-generic-field-shortcode-button' => array(
                'path' => CRED_TEMPLATES_PATH . '/insert-generic-field-shortcode-button.tpl.php'
            ),
            'access-button' => array(
                'path' => CRED_TEMPLATES_PATH . '/access-button.tpl.php'
            ),
            'scaffold-button' => array(
                'path' => CRED_TEMPLATES_PATH . '/scaffold-button.tpl.php'
            ),
            'user-scaffold-button' => array(
                'path' => CRED_TEMPLATES_PATH . '/user-scaffold-button.tpl.php'
            ),
            'insert-form-shortcode-button' => array(
                'path' => CRED_TEMPLATES_PATH . '/insert-form-shortcode-button.tpl.php'
            ),
            'form-settings-meta-box' => array(
                'path' => CRED_TEMPLATES_PATH . '/form-settings-meta-box.tpl.php'
            ),
            'user-form-settings-meta-box' => array(
                'path' => CRED_TEMPLATES_PATH . '/user-form-settings-meta-box.tpl.php'
            ),
            'post-type-meta-box' => array(
                'path' => CRED_TEMPLATES_PATH . '/post-type-meta-box.tpl.php'
            ),
            'notification-meta-box' => array(
                'path' => CRED_TEMPLATES_PATH . '/notification-meta-box.tpl.php'
            ),
            'notification-user-meta-box' => array(
                'path' => CRED_TEMPLATES_PATH . '/notification-user-meta-box.tpl.php'
            ),
            'extra-meta-box' => array(
                'path' => CRED_TEMPLATES_PATH . '/extra-meta-box.tpl.php'
            ),
            'extra-css-meta-box' => array(
                'path' => CRED_TEMPLATES_PATH . '/extra-css-meta-box.tpl.php'
            ),
            'extra-js-meta-box' => array(
                'path' => CRED_TEMPLATES_PATH . '/extra-js-meta-box.tpl.php'
            ),
            'text-settings-meta-box' => array(
                'path' => CRED_TEMPLATES_PATH . '/text-settings-meta-box.tpl.php'
            ),
            'text-access-meta-box' => array(
                'path' => CRED_TEMPLATES_PATH . '/text-access-meta-box.tpl.php'
            ),
            'save-form-meta-box' => array(
                'path' => CRED_TEMPLATES_PATH . "/save-form-meta-box.tpl.php"
            ),
            'delete-post-link' => array(
                'path' => CRED_TEMPLATES_PATH . '/delete-post-link.tpl.php'
            ),
            'generic-field-shortcode-setup' => array(
                'path' => CRED_TEMPLATES_PATH . '/generic-field-shortcode-setup.tpl.php'
            ),
            'conditional-shortcode-setup' => array(
                'path' => CRED_TEMPLATES_PATH . '/conditional-shortcode-setup.tpl.php'
            ),
            'custom-field-setup' => array(
                'path' => CRED_TEMPLATES_PATH . '/custom-field-setup.tpl.php'
            ),
            'notification-condition' => array(
                'path' => CRED_TEMPLATES_PATH . '/notification-condition.tpl.php'
            ),
            'notification-subject-codes' => array(
                'path' => CRED_TEMPLATES_PATH . '/notification-subject-codes.tpl.php'
            ),
            'notification-user-subject-codes' => array(
                'path' => CRED_TEMPLATES_PATH . '/notification-user-subject-codes.tpl.php'
            ),
            'notification-body-codes' => array(
                'path' => CRED_TEMPLATES_PATH . '/notification-body-codes.tpl.php'
            ),
            'notification-user-body-codes' => array(
                'path' => CRED_TEMPLATES_PATH . '/notification-user-body-codes.tpl.php'
            ),
            'notification' => array(
                'path' => CRED_TEMPLATES_PATH . '/notification.tpl.php'
            ),
            'notification-user' => array(
                'path' => CRED_TEMPLATES_PATH . '/notification-user.tpl.php'
            ),
            'pe_form_meta_box' => array(
                'path' => CRED_TEMPLATES_PATH . '/pe_form_meta_box.tpl.php'
            ),
            'pe_form_notification_option' => array(
                'path' => CRED_TEMPLATES_PATH . '/pe_form_notification_option.tpl.php'
            ),
            'pe_post_meta_box' => array(
                'path' => CRED_TEMPLATES_PATH . '/pe_post_meta_box.tpl.php'
            ),
            'pe_settings_meta_box' => array(
                'path' => CRED_TEMPLATES_PATH . '/pe_settings_meta_box.tpl.php'
            )
        )
    ));
}

require_once "embedded/common/functions.php";

function cred_start() {
    CRED_Loader::load('CLASS/CRED');
    $cred = new CRED_CRED();
    $cred->init();
}

if (cred_is_ajax_call() && !is_admin()) {
    add_action('wp_ajax_cred_ajax_form', 'cred_start');
    add_action('wp_ajax_nopriv_cred_ajax_form', 'cred_start');
}

cred_start();
