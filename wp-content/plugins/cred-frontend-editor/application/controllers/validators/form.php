<?php

class CRED_Validator_Form extends CRED_Validator_Base {

    protected $_base_form;
    protected $_legacy_errors;

    public function __construct($base_form, $legacy_errors = "") {
        parent::__construct($base_form);

        $this->_base_form = $base_form;
        $this->_legacy_errors = $legacy_errors;
    }

    public function validate() {
        cred_log("validate");

        $result = array();

        $form = $this->_formData;

        $is_user_form = ($form->getForm()->post_type == CRED_USER_FORMS_CUSTOM_POST_NAME);
        $formHelper = $this->_formHelper;
        $zebraForm = $this->_zebraForm;

        $this->_zebraForm->form_properties;
        if (!$zebraForm->isSubmitted())
            return false;

        $legacy_validator = new CRED_Validator_Legacy($this->_legacy_errors);
        $result[] = $legacy_validator->validate();

        $post_validator = new CRED_Validator_Post($this->_base_form);
        $result[] = $post_validator->validate();

        $nonce_validator = new CRED_Validator_Nonce($this->_base_form);
        $result[] = $nonce_validator->validate();

        $recaptcha_validator = new CRED_Validator_Recaptcha($this->_base_form);
        $result[] = $recaptcha_validator->validate();

        $user_validator = new CRED_Validator_User($this->_base_form);
        $result[] = $user_validator->validate();

        $fields_validator = new CRED_Validator_Fields($this->_base_form);
        $result[] = $fields_validator->validate();

        $toolset_validator = new CRED_Validator_Toolset_Forms($zebraForm, $this->_post_id, $is_user_form);
        $result[] = $toolset_validator->validate();

        cred_log("VALIDATION RESULT");
        cred_log($result);

        return (count(array_unique($result)) === 1);
    }

}
