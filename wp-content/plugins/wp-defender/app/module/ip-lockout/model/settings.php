<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\IP_Lockout\Model;

use Hammer\Helper\HTTP_Helper;
use Hammer\Helper\WP_Helper;

class Settings extends \Hammer\WP\Settings {
	private static $_instance;

	public $login_protection = false;
	public $login_protection_login_attempt = 5;
	public $login_protection_lockout_timeframe = 300;
	public $login_protection_lockout_duration = 300;
	public $login_protection_lockout_message = "You have been locked out due to too many invalid login attempts.";
	public $login_protection_ban_admin_brute = false;
	public $login_protection_lockout_ban = false;
	public $username_blacklist = '';

	public $detect_404 = false;
	public $detect_404_threshold = 20;
	public $detect_404_timeframe = 300;
	public $detect_404_lockout_duration = 300;
	public $detect_404_whitelist;
	public $detect_404_ignored_filetypes;
	public $detect_404_lockout_message = "You have been locked out due to too many attempts to access a file that doesn’t exist.";
	public $detect_404_lockout_ban = false;
	public $detect_404_logged = true;

	public $ip_blacklist;
	public $ip_whitelist;
	public $ip_lockout_message = 'The administrator has blocked your IP from accessing this website.';

	public $login_lockout_notification = true;
	public $ip_lockout_notification = true;

	public $report = true;
	public $report_frequency = '7';
	public $report_day = 'sunday';
	public $report_time = '0:00';


	public $receipts = array();
	public $report_receipts = array();

	public function __construct( $id, $isMulti ) {
		if ( is_admin() || is_network_admin() && current_user_can( 'manage_options' ) ) {
			$this->receipts[]        = get_current_user_id();
			$this->report_receipts[] = get_current_user_id();
			$this->ip_whitelist      = $this->getUserIp() . PHP_EOL;
		}

		parent::__construct( $id, $isMulti );
	}

	/**
	 * @return array
	 */
	public function behaviors() {
		return array(
			'utils' => '\WP_Defender\Behavior\Utils'
		);
	}

	/**
	 * @return array
	 */
	public function rules() {
		return array(
			array(
				array(
					'login_protection_login_attempt',
					'login_protection_lockout_timeframe',
					'detect_404_threshold',
					'detect_404_timeframe'
				),
				'integer',
			)
		);
	}

	/**
	 * @return array
	 */
	public function get404Whitelist() {
		$arr = array_filter( explode( PHP_EOL, $this->detect_404_whitelist ) );;
		$arr = array_map( 'trim', $arr );

		return $arr;
	}

	/**
	 * @return array
	 */
	public function get404Ignorelist() {
		$arr = array_filter( explode( PHP_EOL, $this->detect_404_ignored_filetypes ) );
		$arr = array_map( 'trim', $arr );

		return $arr;
	}

	/**
	 * @return array
	 */
	public function getIpBlacklist() {
		$arr = array_filter( explode( PHP_EOL, $this->ip_blacklist ) );
		$arr = array_map( 'trim', $arr );

		return $arr;
	}

	/**
	 * @return array
	 */
	public function getIpWhitelist() {
		$arr = array_filter( explode( PHP_EOL, $this->ip_whitelist ) );
		$arr = array_map( 'trim', $arr );

		return $arr;
	}

	/**
	 * @param $ip
	 *
	 * @return bool
	 */
	public function isWhitelist( $ip ) {
		$whitelist = $this->getIpWhitelist();
		foreach ( $whitelist as $wip ) {
			$ips = explode( '-', $wip );
			if ( count( $ips ) == 1 && trim( $wip ) == $ip ) {
				return true;
			} elseif ( count( $ips ) == 2 ) {
				$high = sprintf( "%u", ip2long( $ips[1] ) );
				$low  = sprintf( "%u", ip2long( $ips[0] ) );

				$cip = sprintf( "%u", ip2long( $ip ) );
				if ( $high >= $cip && $cip >= $low ) {
					return true;
				}
			} elseif ( stristr( $wip, '/' ) && $this->cidrMatch( $ip, $wip ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param $ip
	 *
	 * @return bool
	 */
	public function isBlacklist( $ip ) {
		$blacklist = $this->getIpBlacklist();
		foreach ( $blacklist as $wip ) {
			$ips = explode( '-', $wip );
			if ( count( $ips ) == 1 && trim( $wip ) == $ip ) {
				return true;
			} elseif ( count( $ips ) == 2 ) {
				$high = sprintf( "%u", ip2long( $ips[1] ) );
				$low  = sprintf( "%u", ip2long( $ips[0] ) );
				$cip  = sprintf( "%u", ip2long( $ip ) );
				if ( $high >= $cip && $cip >= $low ) {
					return true;
				}
			} elseif ( stristr( $wip, '/' ) && $this->cidrMatch( $ip, $wip ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param $ip
	 * @param $list
	 */
	public function addIpToList( $ip, $list ) {
		$ips  = array();
		$type = '';
		if ( $list == 'blacklist' ) {
			$ips  = $this->getIpBlacklist();
			$type = 'ip_blacklist';
		} else if ( $list == 'whitelist' ) {
			$ips  = $this->getIpWhitelist();
			$type = 'ip_whitelist';
		}
		if ( empty( $type ) ) {
			return;
		}

		$ips[]       = $ip;
		$ips         = array_unique( $ips );
		$this->$type = implode( PHP_EOL, $ips );
		$this->save();
	}

	/**
	 * @param $ip
	 * @param $list
	 */
	public function removeIpFromList( $ip, $list ) {
		$ips  = array();
		$type = '';
		if ( $list == 'blacklist' ) {
			$ips  = $this->getIpBlacklist();
			$type = 'ip_blacklist';
		} else if ( $list == 'whitelist' ) {
			$ips  = $this->getIpWhitelist();
			$type = 'ip_whitelist';
		}
		if ( empty( $type ) ) {
			return;
		}

		$key = array_search( $ip, $ips );
		if ( $key !== false ) {
			unset( $ips[ $key ] );
			$ips         = array_unique( $ips );
			$this->$type = implode( PHP_EOL, $ips );
			$this->save();
		}
	}

	/**
	 * @param $ip
	 * @param $range
	 *
	 * @return bool
	 * @src http://stackoverflow.com/a/594134
	 */
	function cidrMatch( $ip, $range ) {
		list ( $subnet, $bits ) = explode( '/', $range );
		$ip     = ip2long( $ip );
		$subnet = ip2long( $subnet );
		$mask   = - 1 << ( 32 - $bits );
		$subnet &= $mask; # nb: in case the supplied subnet wasn't correctly aligned

		return ( $ip & $mask ) == $subnet;
	}

	public function before_update() {
		//validate ips
		$remove_ips = array();
		$isSelf     = false;
		if ( isset( $_POST['ip_blacklist'] ) ) {
			$blacklist = Http_Helper::retrieve_post( 'ip_blacklist' );
			$blacklist = explode( PHP_EOL, $blacklist );
			foreach ( $blacklist as $k => $ip ) {
				$ip = trim( $ip );
				if ( $this->validateIp( $ip ) === false ) {
					unset( $blacklist[ $k ] );
					$remove_ips[] = $ip;
				} elseif ( $ip == $this->getUserIp() ) {
					$isSelf = true;
				}
			}
			$this->ip_blacklist = implode( PHP_EOL, $blacklist );
		}

		if ( isset( $_POST['ip_whitelist'] ) ) {
			$whitelist = Http_Helper::retrieve_post( 'ip_whitelist' );
			$whitelist = explode( PHP_EOL, $whitelist );
			foreach ( $whitelist as $k => $ip ) {
				$ip = trim( $ip );
				if ( $this->validateIp( $ip ) === false ) {
					unset( $whitelist[ $k ] );
					$remove_ips[] = $ip;
				}
			}
			$this->ip_whitelist = implode( PHP_EOL, $whitelist );
		}
		$remove_ips = array_filter( $remove_ips );

		if ( ! empty( $remove_ips ) && count( $remove_ips ) ) {
			WP_Helper::getArrayCache()->set( 'faultIps', $remove_ips );
			WP_Helper::getArrayCache()->set( 'isBlacklistSelf', $isSelf );
		}
	}

	/**
	 * $ip an be single ip, or a range like xxx.xxx.xxx.xxx - xxx.xxx.xxx.xxx or CIDR
	 *
	 * @param $ip
	 *
	 * @return bool
	 */
	public function validateIp( $ip ) {
		if ( ( ! stristr( $ip, '-' ) || ! stristr( $ip, '/' ) ) && filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {

			return true;
		} elseif ( stristr( $ip, '-' ) ) {
			$ips = explode( '-', $ip );
			foreach ( $ips as $ip ) {
				if ( ! filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
					return false;
				}
			}
			if ( sprintf( "%u", ip2long( $ips[1] ) ) - sprintf( "%u", ip2long( $ips[0] ) ) > 0 ) {
				return true;
			}
		} elseif ( stristr( $ip, '/' ) ) {
			list( $ip, $bits ) = explode( '/', $ip );
			if ( filter_var( $ip, FILTER_VALIDATE_IP ) && filter_var( $bits, FILTER_VALIDATE_INT ) && 0 <= $bits && $bits <= 32 ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @return array
	 */
	public function events() {
		$that = $this;

		return array(
			self::EVENT_BEFORE_SAVE => array(
				array(
					function () use ( $that ) {
						$that->before_update();
					}
				)
			)
		);
	}

	/**
	 * @return array|string
	 */
	public function getUsernameBlacklist() {
		$usernames = $this->username_blacklist;
		$usernames = explode( PHP_EOL, $usernames );
		$usernames = array_map( 'trim', $usernames );
		$usernames = array_map( 'strtolower', $usernames );

		return $usernames;
	}

	/**
	 * @return Settings
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			$class           = new Settings( 'wd_lockdown_settings', WP_Helper::is_network_activate( wp_defender()->plugin_slug ) );
			self::$_instance = $class;
		}

		return self::$_instance;
	}
}