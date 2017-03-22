<?php

abstract class CRED_Field_Abstract {

    protected $_atts;
    protected $_cred_rendering;
    protected $_formHelper;
    protected $_formData;
    protected $_translate_field_factory;

    public function __construct($atts, $credRenderingForm, $formHelper, $formData, $translateFieldFactory) {
        $this->_atts = $atts;
        $this->_cred_rendering = $credRenderingForm;
        $this->_formHelper = $formHelper;
        $this->_formData = $formData;
        $this->_translate_field_factory = $translateFieldFactory;
    }

    protected function get_field() {
        return null;
    }

}
