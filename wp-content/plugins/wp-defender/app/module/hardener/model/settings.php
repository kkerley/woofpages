<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Hardener\Model;

use Hammer\Helper\WP_Helper;
use WP_Defender\Behavior\Utils;
use WP_Defender\Module\Hardener\Component\Change_Admin;
use WP_Defender\Module\Hardener\Component\Change_Admin_Service;
use WP_Defender\Module\Hardener\Component\DB_Prefix;
use WP_Defender\Module\Hardener\Component\DB_Prefix_Service;
use WP_Defender\Module\Hardener\Component\Disable_File_Editor;
use WP_Defender\Module\Hardener\Component\Disable_File_Editor_Service;
use WP_Defender\Module\Hardener\Component\Disable_Trackback;
use WP_Defender\Module\Hardener\Component\Disable_Trackback_Service;
use WP_Defender\Module\Hardener\Component\Hide_Error;
use WP_Defender\Module\Hardener\Component\Hide_Error_Service;
use WP_Defender\Module\Hardener\Component\PHP_Version;
use WP_Defender\Module\Hardener\Component\Prevent_Php;
use WP_Defender\Module\Hardener\Component\Protect_Information;
use WP_Defender\Module\Hardener\Component\Security_Key;
use WP_Defender\Module\Hardener\Component\Security_Key_Service;
use WP_Defender\Module\Hardener\Component\WP_Version;
use WP_Defender\Module\Hardener\Component\WP_Version_Service;
use WP_Defender\Module\Hardener\Rule;
use WP_Defender\Module\Hardener\Rule_Service;

class Settings extends \Hammer\WP\Settings {
	private static $_instance;
	/**
	 * @var string
	 */
	public $id = 'hardener_settings';
	/**
	 * Contains issues rules
	 *
	 * @var array
	 */
	public $issues = array();

	/**
	 * Contains fixed rules
	 * @var array
	 */

	public $fixed = array();
	/**
	 * Contains ignored issue
	 * @var array
	 */

	public $ignore = array();
	/**
	 * Store the last status check, we will check & fetch the status intervally, this can reduce load time.
	 * @var null
	 */

	public $last_status_check = null;

	/**
	 * Toggle notification
	 * @var bool
	 */
	public $notification = true;

	/**
	 * Holding receipts info
	 * @var array
	 */
	public $receipts = array();

	/**
	 * shorthand to add to a list
	 *
	 * @param $slug
	 * @param $devPush
	 */
	public function addToIssues( $slug, $devPush = true ) {
		$this->addToList( 'issues', $slug, $devPush );
	}

	/**
	 * shorthand to add to a list
	 *
	 * @param $slug
	 * @param $devPush
	 */
	public function addToIgnore( $slug, $devPush = true ) {
		$this->addToList( 'ignore', $slug, $devPush );
	}

	/**
	 * shorthand to add to a list
	 *
	 * @param $slug
	 * @param $devPush
	 */
	public function addToResolved( $slug, $devPush = true ) {
		$this->addToList( 'fixed', $slug, $devPush );
	}

	/**
	 * @param $list
	 * @param $slug
	 * @param $devPush
	 */
	private function addToList( $list, $slug, $devPush ) {
		$lists = array(
			'issues',
			'fixed',
			'ignore'
		);
		if ( ! in_array( $list, $lists ) ) {
			return;
		}

		//remove from lists
		foreach ( $lists as $l ) {
			if ( $l == $list ) {
				continue;
			}
			$key = array_search( $slug, $this->{$l} );
			if ( $key !== false ) {
				unset( $this->{$l}[ $key ] );
			}
		}

		array_push( $this->$list, $slug );
		$this->$list             = array_unique( $this->$list );
		$this->last_status_check = time();
		$this->save();
		if ( $devPush ) {
			Utils::instance()->submitStatsToDev();
		}
	}

	/**
	 * @return Settings
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new Settings( 'wd_hardener_settings', WP_Helper::is_network_activate( wp_defender()->plugin_slug ) );
		}

		return self::$_instance;
	}

	/**
	 * refresh rules status and store the index
	 */
	public function refreshStatus() {
		$definedRules = $this->getDefinedRules( true );
		$this->fixed  = array();
		$this->issues = array();
		foreach ( $definedRules as $rule ) {
			if ( empty( $rule::$slug ) || in_array( $rule::$slug, $this->ignore ) ) {
				//this rule ignored, no process
				continue;
			}
			if ( $rule->getService()->check() ) {
				$this->fixed[] = $rule::$slug;
			} else {
				$this->issues[] = $rule::$slug;
			}
		}
		$this->last_status_check = time();
		$this->save();
	}

	/**
	 * @return Rule[]
	 */
	public function getIssues() {
		$rules  = $this->getDefinedRules( true );
		$issues = array();
		foreach ( $this->issues as $issue ) {
			if ( isset( $rules[ $issue ] ) ) {
				$issues[] = $rules[ $issue ];
			}
		}

		return $issues;
	}

	/**
	 * @return array
	 */
	public function getIgnore() {
		$rules  = $this->getDefinedRules( true );
		$issues = array();
		foreach ( $this->ignore as $issue ) {
			if ( isset( $rules[ $issue ] ) ) {
				$issues[] = $rules[ $issue ];
			}
		}

		return $issues;
	}

	/**
	 * @return Rule[]
	 */
	public function getFixed() {
		$rules  = $this->getDefinedRules( true );
		$issues = array();
		foreach ( $this->fixed as $issue ) {
			if ( isset( $rules[ $issue ] ) ) {
				$issues[] = $rules[ $issue ];
			}
		}

		return $issues;
	}

	/**
	 * @param $slug
	 *
	 * @return Rule
	 */
	public function getRuleBySlug( $slug ) {
		$rules = $this->getDefinedRules( true );
		if ( isset( $rules[ $slug ] ) ) {
			return $rules[ $slug ];
		}
	}

	/**
	 *
	 * @param bool $init
	 *
	 * @return array
	 */
	public function getDefinedRules( $init = false ) {
		return array(
			Disable_Trackback::$slug   => $init == true ? new Disable_Trackback() : Disable_Trackback::getClassName(),
			WP_Version::$slug          => $init == true ? new WP_Version() : WP_Version::getClassName(),
			PHP_Version::$slug         => $init == true ? new PHP_Version() : PHP_Version::getClassName(),
			Change_Admin::$slug        => $init == true ? new Change_Admin() : Change_Admin::getClassName(),
			DB_Prefix::$slug           => $init == true ? new DB_Prefix() : DB_Prefix::getClassName(),
			Disable_File_Editor::$slug => $init == true ? new Disable_File_Editor() : Disable_File_Editor::getClassName(),
			Hide_Error::$slug          => $init == true ? new Hide_Error() : Hide_Error::getClassName(),
			Security_Key::$slug        => $init == true ? new Security_Key() : Security_Key::getClassName(),
			Protect_Information::$slug => $init == true ? new Protect_Information() : Protect_Information::getClassName(),
			Prevent_Php::$slug         => $init == true ? new Prevent_Php() : Prevent_Php::getClassName()
		);
	}
}