<?php

/**
 * @author: Hoang Ngo
 */
class WD_Hardener_Module extends WD_Module_Abstract {
	private $controllers = array();

	private static $_instance;

	public static function get_instance() {
		if ( ! self::$_instance instanceof WD_Hardener_Module ) {
			self::$_instance = new WD_Hardener_Module();
		}

		return self::$_instance;
	}

	public function __construct() {
		parent::__construct();
		$this->controllers['hardener'] = new WD_Hardener_Controller( $this );
	}

	/**
	 * @param $controller
	 *
	 * @return null
	 */
	public function get_controller( $controller ) {
		return isset( $this->controllers[ $controller ] ) ? $this->controllers[ $controller ] : null;
	}

	/**
	 * Find a controller instance
	 *
	 * @param $controller
	 *
	 * @return null
	 * @since 1.0.2
	 */
	public static function find_controller( $controller ) {
		$module = self::get_instance();

		return isset( $module->controllers[ $controller ] ) ? $module->controllers[ $controller ] : null;
	}
}