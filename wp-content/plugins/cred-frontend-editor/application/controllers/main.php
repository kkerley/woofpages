<?php

/**
 * Main CRED controller.
 *
 * Determines if we're in admin or front-end mode or if an AJAX call is being performed. Handles tasks
 * that are common to all three modes, if there are any.
 *
 * @since 1.9
 */
class CRED_Main {

	private static $instance;

	public static function initialize() {

		if( null == self::$instance ) {
			self::$instance = new self();
		}

		// Deliberately not giving the instance away. Nobody should need it.
	}

	private function __clone() { }


	private function __construct() {
		// This is happening quite early. We need to wait for everything.
		add_action( 'toolset_common_loaded', array( $this, 'register_autoloaded_classes' ) );
		add_action( 'init', array( $this, 'on_init' ) );
	}


	/**
	 * Register CRED classes with Toolset_Common_Autoloader.
	 *
	 * @since 1.9
	 */
	public function register_autoloaded_classes() {
		// It is possible to regenerate the classmap with Zend framework, for example:
		//
		// cd application
		// /srv/www/ZendFramework-2.4.9/bin/classmap_generator.php --overwrite
		$classmap = include( CRED_ABSPATH . '/application/autoload_classmap.php' );
		$legacy_classmap = $this->get_legacy_classmap();
		$classmap = array_merge( $classmap, $legacy_classmap );

		do_action( 'toolset_register_classmap', $classmap );
	}


	/**
	 * Return the array of autoloaded classes in legacy CRED and their absolute paths.
	 *
	 * If you need to use a class from the legacy code in the new part, use this method for
	 * registering it with the autoloader instead of including files directly.
	 *
	 * @return string[string]
	 * @since 1.9
	 */
	private function get_legacy_classmap() {
		$classmap = array();

		return $classmap;
	}


    /**
     * Shortcut to Toolset_Common_Bootstrap::get_request_mode().
     *
     * @return string Toolset_Common_Bootstrap::MODE_*
     * @since 1.9
     */
    private function get_request_mode() {
        $tb = Toolset_Common_Bootstrap::getInstance();
        return $tb->get_request_mode();
    }


	/**
	 * Determine in which request mode we are and initialize the right dedicated controller.
	 *
	 * @since 1.9
	 */
	public function on_init() {
		switch( $this->get_request_mode() ) {
			case Toolset_Common_Bootstrap::MODE_ADMIN:
				// todo CRED_Admin controller
			case Toolset_Common_Bootstrap::MODE_FRONTEND:
				// todo CRED_Frontend controller
			case Toolset_Common_Bootstrap::MODE_AJAX:
				// todo CRED_Ajax controller
			default:
				return;
		}
	}

}
