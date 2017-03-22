<?php

class CRED_Validator_Legacy implements ICRED_Validator {

    public $errors;

    public function __construct($errors) {
        $this->errors = $errors;
    }

    public function validate() {
        $result = empty($this->error_files);
        cred_log($result);
        return $result;
    }

}
