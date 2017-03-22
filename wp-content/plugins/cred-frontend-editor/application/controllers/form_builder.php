<?php

class CRED_Form_Builder extends CRED_Form_Builder_Base {

    private static $instance;

    public static function initialize() {
        cred_log("initialize");
        if (null == self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __clone() {        
    }

    public function __construct() {
        parent::__construct();    
        cred_log("__construct");
    }
}
