<?php

require_once WPTOOLSET_FORMS_ABSPATH . '/classes/class.image.php';

class WPToolset_Field_Credimage extends WPToolset_Field_Credfile
{
    public function metaform()
    {
        //TODO: check if this getValidationData does not break PHP Validation _cakePHP required file.
        $validation = $this->getValidationData();
        $validation = WPToolset_Field_Image::addTypeValidation($validation);
        $this->setValidationData($validation);
        return parent::metaform();        
    }
}
