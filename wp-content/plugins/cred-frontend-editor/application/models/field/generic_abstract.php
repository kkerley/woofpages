<?php

abstract class CRED_Generic_Field_Abstract {

    protected $_atts;
    protected $_content;
    protected $_cred_rendering;
    protected $_formHelper;
    protected $_formData;
    protected $_translate_field_factory;

    public function __construct($atts, $content, $credRenderingForm, $formHelper, $formData, $translateFieldFactory) {
        $this->_atts = $atts;
        $this->_content = $content;
        $this->_cred_rendering = $credRenderingForm;
        $this->_formHelper = $formHelper;
        $this->_formData = $formData;
        $this->_translate_field_factory = $translateFieldFactory;
    }

}
