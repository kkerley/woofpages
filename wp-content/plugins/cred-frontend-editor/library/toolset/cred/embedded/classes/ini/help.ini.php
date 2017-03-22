<?php if (!defined('ABSPATH')) die('Security check'); ?>
<?php

// localize links also, to provide locale specific urls
$cred_help = array(
    'conditionals' => array(
        'link' => 'http://wp-types.com/documentation/user-guides/cred-conditional-display-engine/',
        'text' => __('CRED Conditional Expressions', 'wp-cred')
    ),
    'handle_caching' => array(
        'link' => '#',
        'text' => __('How to disable caching for content with forms', 'wp-cred')
    ),
    'add_forms_to_site' => array(        
        'link' => 'https://wp-types.com/documentation/user-guides/creating-cred-forms/',
        'text' => __('How to add Forms to your site', 'wp-cred')
    ),
    'add_post_forms_to_site' => array(        
        'link' => 'https://wp-types.com/documentation/user-guides/creating-cred-forms/#designing-the-cred-post-form?utm_source=credplugin&utm_campaign=cred&utm_medium=help-link-plugin-row&utm_term=CRED '.CRED_FE_VERSION.' help link',
        'text' => __('How to add Post Forms to your site', 'wp-cred')
    ),
    'add_user_forms_to_site' => array(        
        'link' => 'https://wp-types.com/documentation/user-guides/cred-user-forms/#designing-the-cred-user-form?utm_source=credplugin&utm_campaign=cred&utm_medium=help-link-plugin-row&utm_term=CRED '.CRED_FE_VERSION.' help link',
        'text' => __('How to add User Forms to your site', 'wp-cred')
    ),
    'add_user_forms_to_site' => array(
        'link' => 'https://wp-types.com/documentation/user-guides/cred-user-forms/',
        'text' => __('How to add User Forms to your site', 'wp-cred')
    ),
    'general_form_settings' => array(
        'link' => 'https://wp-types.com/documentation/user-guides/creating-cred-forms/',
        'text' => __('Form Settings Help', 'wp-cred')
    ),
    'post_type_settings' => array(
        'link' => 'https://wp-types.com/documentation/user-guides/creating-cred-forms/',
        'text' => __('Post Settings Help', 'wp-cred')
    ),
    'notification_settings' => array(
        'link' => 'https://wp-types.com/documentation/user-guides/creating-cred-forms/#email-notifications',
        'text' => __('Notification Settings Help', 'wp-cred')
    ),
    'css_settings' => array(
        'link' => 'https://wp-types.com/documentation/user-guides/creating-cred-forms/#designing-the-cred-post-form?utm_source=credplugin&utm_campaign=cred&utm_medium=help-link-plugin-row&utm_term=CRED '.CRED_FE_VERSION.' help link',
        'text' => __('Extra CSS Help', 'wp-cred')
    ),
    'scaffold_settings' => array(
        'link' => 'https://wp-types.com/documentation/user-guides/cred-user-forms/#designing-the-cred-user-form?utm_source=credplugin&utm_campaign=cred&utm_medium=help-link-plugin-row&utm_term=CRED '.CRED_FE_VERSION.' help link',
        'text' => __('Scaffold Help', 'wp-cred')
    ),
    'generic_fields_settings' => array(
        'link' => 'http://wp-types.com/documentation/user-guides/inserting-generic-fields-into-forms/',
        'text' => __('Generic Fields Help', 'wp-cred')
    ),
    'fields_settings' => array(
        'link' => 'https://wp-types.com/documentation/user-guides/creating-cred-forms/#designing-the-cred-post-form?utm_source=credplugin&utm_campaign=cred&utm_medium=help-link-plugin-row&utm_term=CRED '.CRED_FE_VERSION.' help link',
        'text' => __('Post Fields Help', 'wp-cred')
    ),
    'fields_settings_users' => array(
        'link' => 'https://wp-types.com/documentation/user-guides/cred-user-forms/#designing-the-cred-user-form?utm_source=credplugin&utm_campaign=cred&utm_medium=help-link-plugin-row&utm_term=CRED '.CRED_FE_VERSION.' help link',
        'text' => __('Post Fields Help', 'wp-cred')
    ),
    'content_creation_shortcode_post_settings' => array(
        'link' => 'https://wp-types.com/documentation/user-guides/creating-cred-forms/#displaying-forms-on-the-front-end',
        'text' => __('Help', 'wp-cred')
    ),
    'content_creation_shortcode_user_settings' => array(
        'link' => 'https://wp-types.com/documentation/user-guides/cred-user-forms/#displaying-forms-on-the-front-end',
        'text' => __('Help', 'wp-cred')
    ),
    'content_delete_shortcode_settings' => array(
        'link' => 'https://wp-types.com/documentation/user-guides/creating-cred-forms/#editing-forms-and-deletion-links',
        'text' => __('Help', 'wp-cred')
    ),
    'content_edit_shortcode_settings' => array(
        'link' => 'https://wp-types.com/documentation/user-guides/creating-cred-forms/#editing-forms-and-deletion-links',
        'text' => __('Help', 'wp-cred')
    ),
    'content_edit_user_shortcode_settings' => array(
        'link' => 'https://wp-types.com/documentation/user-guides/cred-user-forms/#editing-forms',
        'text' => __('Help', 'wp-cred')
    ),
    'autogeneration_notification_missing_alert' => array(
        'link' => 'http://wp-types.com/documentation/user-guides/cred-user-forms-email-notifications/',
        'text' => __('How to create notifications for sending passwords', 'wp-cred')
    ),
);
?>