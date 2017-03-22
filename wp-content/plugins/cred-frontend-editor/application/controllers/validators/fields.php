<?php

class CRED_Validator_Fields extends CRED_Validator_Base implements ICRED_Validator {

    public function validate() {
        $result = true;

        $form = $this->_formData;
        $zebraForm = $this->_zebraForm;
        $formHelper = $this->_formHelper;
        $_fields = $form->getFields();
        $form_id = $form->getForm()->ID;
        $form_type = $_fields['form_settings']->form['type'];
        $post_type = $_fields['form_settings']->post['post_type'];
        $fields = $formHelper->get_form_field_values();
        $zebraForm->set_submitted_values($fields);

        $thisform = array(
            'id' => $form_id,
            'post_type' => $post_type,
            'form_type' => $form_type
        );

        $errors = array();
        $form_slug = $form->getForm()->post_name;
        list($fields, $errors) = apply_filters('cred_form_validate_form_' . $form_slug, array($fields, $errors), $thisform);
        list($fields, $errors) = apply_filters('cred_form_validate_' . $form_id, array($fields, $errors), $thisform);
        list($fields, $errors) = apply_filters('cred_form_validate', array($fields, $errors), $thisform);

        if (!empty($errors)) {
            //Added result to fix conditional elements of this todo
            //Notice: Undefined index: cred_form_6_1_wysiwyg-field in with validation hook
            //https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/189015783/comments
            $result = true;
            foreach ($errors as $fname => $err) {
                $ofname = "";
                if (strpos($fname, "wpcf-") !== false) {
                    $ofname = $fname;
                    $fname = str_replace("wpcf-", "", $fname);
                }

                if (isset(CRED_StaticClass::$out['fields']['extra_fields'][$fname]))
                    $result = false;

                if ($form->getForm()->post_type == CRED_USER_FORMS_CUSTOM_POST_NAME) {
                    if ((isset(CRED_StaticClass::$out['fields']['post_fields']) &&
                            (array_key_exists($fname, CRED_StaticClass::$out['fields']['post_fields']) ||
                            array_key_exists($ofname, CRED_StaticClass::$out['fields']['post_fields']))) ||
                            (isset(CRED_StaticClass::$out['form_fields_info']) &&
                            (array_key_exists($fname, CRED_StaticClass::$out['form_fields_info']) ||
                            array_key_exists($ofname, CRED_StaticClass::$out['form_fields_info'])))
                    ) {
                        //Added result to fix conditional elements of this todo
                        //Notice: Undefined index: cred_form_6_1_wysiwyg-field in with validation hook
                        //https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/189015783/comments
                        if (isset(CRED_StaticClass::$out['fields']['user_fields'][$fname]) &&
                                isset(CRED_StaticClass::$out['fields']['user_fields'][$fname]['plugin_type_prefix'])) {
                            $tmp = CRED_StaticClass::$out['fields']['user_fields'][$fname]['plugin_type_prefix'] . $fname;
                            //Fixed issues on images validation validation i forgot to check $_FILES
                            //Fixed the same for checkboxes/checkbox/radio
                            if (
                                    (CRED_StaticClass::$out['fields']['user_fields'][$fname]['type'] != 'checkboxes' &&
                                    CRED_StaticClass::$out['fields']['user_fields'][$fname]['type'] != 'checkbox' &&
                                    CRED_StaticClass::$out['fields']['user_fields'][$fname]['type'] != 'radio') &&
                                    !isset($_POST[$tmp]) && !isset($_FILES[$tmp])) {
                                continue;
                            }
                        }
                        //##########################################################################################                            
                        //Fix of cred_form_validate_form_'.$form_slug doesn't work
                        //https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/188882358/comments
                        $myfname = isset(CRED_StaticClass::$out['fields']['form_fields'][$fname]['post_labels']) ? CRED_StaticClass::$out['fields']['form_fields'][$fname]['post_labels'] : (isset(CRED_StaticClass::$out['fields']['custom_fields'][$fname]['name']) ? CRED_StaticClass::$out['fields']['custom_fields'][$fname]['name'] : (isset(CRED_StaticClass::$out['fields']['extra_fields'][$fname]['name']) ? CRED_StaticClass::$out['fields']['extra_fields'][$fname]['name'] : $fname));
                        $zebraForm->add_top_message($myfname . ": " . $err);
                        //$zebraForm->controls[CRED_StaticClass::$out['form_fields'][$fname][0]]->addError($err);
                        //############################################################
                        $result = false;
                    }
                } else {
                    if (isset(CRED_StaticClass::$out['form_fields']) &&
                            array_key_exists($fname, CRED_StaticClass::$out['form_fields'])) {
                        //Added result to fix conditional elements of this todo
                        //Notice: Undefined index: cred_form_6_1_wysiwyg-field in with validation hook
                        //https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/189015783/comments
                        if (isset(CRED_StaticClass::$out['fields']['post_fields'][$fname]) &&
                                isset(CRED_StaticClass::$out['fields']['post_fields'][$fname]['plugin_type_prefix'])) {
                            $tmp = CRED_StaticClass::$out['fields']['post_fields'][$fname]['plugin_type_prefix'] . $fname;
                            //Fixed issues on images validation validation i forgot to check $_FILES
                            //Fixed the same for checkboxes/checkbox/radio
                            if (
                                    (CRED_StaticClass::$out['fields']['post_fields'][$fname]['type'] != 'checkboxes' &&
                                    CRED_StaticClass::$out['fields']['post_fields'][$fname]['type'] != 'checkbox' &&
                                    CRED_StaticClass::$out['fields']['post_fields'][$fname]['type'] != 'radio') &&
                                    !isset($_POST[$tmp]) && !isset($_FILES[$tmp])) {
                                continue;
                            }
                        }
                        //##########################################################################################                            
                        //Fix of cred_form_validate_form_'.$form_slug doesn't work
                        //https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/188882358/comments
                        $myfname = isset(CRED_StaticClass::$out['fields']['post_fields'][$fname]['name']) ? CRED_StaticClass::$out['fields']['post_fields'][$fname]['name'] : (isset(CRED_StaticClass::$out['fields']['custom_fields'][$fname]['name']) ? CRED_StaticClass::$out['fields']['custom_fields'][$fname]['name'] : (isset(CRED_StaticClass::$out['fields']['extra_fields'][$fname]['name']) ? CRED_StaticClass::$out['fields']['extra_fields'][$fname]['name'] : $fname));
                        $zebraForm->add_top_message($myfname . ": " . $err);
                        //$zebraForm->controls[CRED_StaticClass::$out['form_fields'][$fname][0]]->addError($err);
                        //############################################################
                        $result = false;
                    }
                }
            }
        }
        cred_log($result);
        return $result;
    }

}
