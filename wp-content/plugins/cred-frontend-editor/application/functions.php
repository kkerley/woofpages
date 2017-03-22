<?php

/**
 * This file is meant for very generic functions that should be allways available, PHP compatibility fixes and so on.
 *
 * Do not let it grow too much and make sure to wrap each function in !function_exists() condition.
 *
 * @since 1.9
 */
if (!function_exists("cred_get_object_form")) {

    /**
     * cred_get_object_form
     * @param type $text
     * @param type $type (CRED_FORMS_CUSTOM_POST_NAME|CRED_USER_FORMS_CUSTOM_POST_NAME)
     * @return type
     */
    function cred_get_object_form($form, $type) {
        if (is_string($form) && !is_numeric($form)) {
            $result = get_page_by_path(html_entity_decode($form), OBJECT, $type);
            if ($result && is_object($result) && isset($result->ID))
                return $result;
            else {
                $result = get_page_by_title(html_entity_decode($form), OBJECT, $type);
                if ($result && is_object($result) && isset($result->ID))
                    return $result;
            }
        } else {
            if (is_numeric($form)) {
                $result = get_post($form);
                if ($result && is_object($result) && isset($result->ID))
                    return $result;
                else
                    return false;
            }
        }
    }

}

if (!function_exists("cred_get_form_id_by_form")) {

    /**
     * cred_get_form_id_by_form
     * @param type $form
     * @return boolean
     */
    function cred_get_form_id_by_form($form) {
        if (isset($form) && !empty($form) && isset($form->ID))
            return $form->ID;
        return false;
    }

}

    