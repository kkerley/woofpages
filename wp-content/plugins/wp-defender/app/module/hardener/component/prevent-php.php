<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Hardener\Component;

use Hammer\Helper\WP_Helper;
use WP_Defender\Module\Hardener\Model\Settings;
use WP_Defender\Module\Hardener\Rule;

class Prevent_Php extends Rule {
	static $slug = 'prevent-php-executed';
	static $service;

	function getDescription() {
		$this->renderPartial( 'rules/prevent-php-executed' );
	}

	/**
	 * @return bool|false|mixed|null
	 */
	function check() {
		return $this->getService()->check();
	}

	/**
	 * @return string|void
	 */
	public function getTitle() {
		return __( "Prevent PHP execution", wp_defender()->domain );
	}


	function revert() {
		if ( ! $this->verifyNonce() ) {
			return;
		}
		$ret = $this->getService()->revert();
		if ( ! is_wp_error( $ret ) ) {
			Settings::instance()->addToIssues( self::$slug );
		} else {
			wp_send_json_error( array(
				'message' => $ret->get_error_message()
			) );
		}
	}

	function addHooks() {
		$this->add_action( 'processingHardener' . self::$slug, 'process' );
		$this->add_action( 'processRevert' . self::$slug, 'revert' );
	}

	function process() {
		if ( ! $this->verifyNonce() ) {
			return;
		}
		$ret = $this->getService()->process();
		if ( ! is_wp_error( $ret ) ) {
			Settings::instance()->addToResolved( self::$slug );
		} else {
			wp_send_json_error( array(
				'message' => $ret->get_error_message()
			) );
		}
	}

	/**
	 * @return Prevent_PHP_Service
	 */
	public function getService() {
		if ( self::$service == null ) {
			self::$service = new Prevent_PHP_Service();
		}

		return self::$service;
	}
}