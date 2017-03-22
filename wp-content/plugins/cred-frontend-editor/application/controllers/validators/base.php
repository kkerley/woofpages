<?php

abstract class CRED_Validator_Base {

    protected $_post_id;
    protected $_formData;
    protected $_formHelper;
    protected $_zebraForm;

    public function __construct($_base_form) {
        $this->_post_id = $_base_form->_post_id;
        $this->_formData = $_base_form->_formData;
        $this->_formHelper = $_base_form->_formHelper;
        $this->_zebraForm = $_base_form->_zebraForm;
    }

}