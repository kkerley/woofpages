<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Hardener\Component;

use Hammer\Helper\WP_Helper;
use WP_Defender\Behavior\Utils;
use WP_Defender\Component\Error_Code;
use WP_Defender\Module\Hardener\IRule_Service;
use WP_Defender\Module\Hardener\Rule_Service;

class Prevent_PHP_Service extends Rule_Service implements IRule_Service {

	/**
	 * @return bool
	 */
	public function check() {
		$cache = WP_Helper::getArrayCache()->get( 'Prevent_PHP_Service', null );
		if ( $cache === null ) {
			//init upload dir and a php file
			Utils::instance()->getDefUploadDir();
			$url    = WP_Helper::getUploadUrl();
			$url    = $url . '/wp-defender/index.php';
			$status = wp_remote_head( $url, array( 'user-agent' => $_SERVER['HTTP_USER_AGENT'] ) );
			if ( 200 == wp_remote_retrieve_response_code( $status ) ) {
				WP_Helper::getArrayCache()->set( 'Prevent_PHP_Service', false );

				return false;
			}
			WP_Helper::getArrayCache()->set( 'Prevent_PHP_Service', true );

			return true;
		} else {
			return $cache;
		}
	}

	/**
	 * @return bool|\WP_Error
	 */
	public function process() {
		$ret = $this->protectContentDir();
		if ( is_wp_error( $ret ) ) {
			return $ret;
		}

		$ret = $this->protectIncludesDir();
		if ( is_wp_error( $ret ) ) {
			return $ret;
		}

		return true;
	}

	/**
	 * @return bool|\WP_Error
	 */
	private function protectIncludesDir() {
		$htPath = ABSPATH . WPINC . '/' . '.htaccess';

		if ( ! is_file( $htPath ) ) {
			if ( ! file_put_contents( $htPath, '', LOCK_EX ) ) {
				return new \WP_Error( Error_Code::NOT_WRITEABLE,
					sprintf( __( "The file %s is not writeable", wp_defender()->domain ), $htPath ) );
			}
		} elseif ( ! is_writeable( $htPath ) ) {
			return new \WP_Error( Error_Code::NOT_WRITEABLE,
				sprintf( __( "The file %s is not writeable", wp_defender()->domain ), $htPath ) );
		}
		$htConfig = file( $htPath );
		$default  = array(
			PHP_EOL . '## WP Defender - Protect PHP Executed ##' . PHP_EOL,
			'<Files *.php>' . PHP_EOL .
			'Order allow,deny' . PHP_EOL .
			'Deny from all' . PHP_EOL .
			'</Files>' . PHP_EOL,
			'<Files wp-tinymce.php>' . PHP_EOL .
			'Allow from all' . PHP_EOL .
			'</Files>' . PHP_EOL,
			'<Files ms-files.php>' . PHP_EOL .
			'Allow from all' . PHP_EOL .
			'</Files>' . PHP_EOL,
			'## WP Defender - End ##' . PHP_EOL
		);
		/*$status   = wp_remote_head( network_site_url() . 'wp-includes', array( 'user-agent' => $_SERVER['HTTP_USER_AGENT'] ) );
		if ( 200 == wp_remote_retrieve_response_code( $status ) ) {
			$default[] = 'Options -Indexes' . PHP_EOL;
		}*/
		$containsSearch = array_diff( $default, $htConfig );
		if ( count( $containsSearch ) == 0 || ( count( $containsSearch ) == count( $default ) ) ) {
			//append this
			$htConfig = array_merge( $htConfig, array( implode( '', $default ) ) );
			file_put_contents( $htPath, implode( '', $htConfig ), LOCK_EX );
		}

		return true;
	}

	/**
	 * @return bool|\WP_Error
	 */
	private function protectContentDir() {
		$htPath = WP_CONTENT_DIR . '/' . '.htaccess';
		if ( ! file_exists( $htPath ) ) {
			if ( ! file_put_contents( $htPath, '', LOCK_EX ) ) {
				return new \WP_Error( Error_Code::NOT_WRITEABLE,
					sprintf( __( "The file %s is not writeable", wp_defender()->domain ), $htPath ) );
			}
		} elseif ( ! is_writeable( $htPath ) ) {
			return new \WP_Error( Error_Code::NOT_WRITEABLE,
				sprintf( __( "The file %s is not writeable", wp_defender()->domain ), $htPath ) );
		}
		$htConfig = file( $htPath );
		$default  = array(
			PHP_EOL . '## WP Defender - Protect PHP Executed ##' . PHP_EOL,
			'<Files *.php>' . PHP_EOL .
			'Order allow,deny' . PHP_EOL .
			'Deny from all' . PHP_EOL .
			'</Files>' . PHP_EOL,
			'## WP Defender - End ##' . PHP_EOL
		);

		/*$status = wp_remote_head( network_site_url() . 'wp-includes', array( 'user-agent' => $_SERVER['HTTP_USER_AGENT'] ) );
		if ( 200 == wp_remote_retrieve_response_code( $status ) ) {
			$default[] = 'Options -Indexes' . PHP_EOL;
		}*/

		$containsSearch = array_diff( $default, $htConfig );
		if ( count( $containsSearch ) == 0 || ( count( $containsSearch ) == count( $default ) ) ) {
			//append this
			$htConfig = array_merge( $htConfig, array( implode( '', $default ) ) );
			file_put_contents( $htPath, implode( '', $htConfig ), LOCK_EX );
		}

		return true;
	}

	public function unProtectContentDir() {
		$htPath = WP_CONTENT_DIR . '/' . '.htaccess';
		if ( ! is_writeable( $htPath ) ) {
			return new \WP_Error( Error_Code::NOT_WRITEABLE,
				sprintf( __( "The file %s is not writeable", wp_defender()->domain ), $htPath ) );
		}
		$htConfig = file_get_contents( $htPath );
		$default  = array(
			'## WP Defender - Protect PHP Executed ##' . PHP_EOL,
			'<Files *.php>' . PHP_EOL .
			'Order allow,deny' . PHP_EOL .
			'Deny from all' . PHP_EOL .
			'</Files>' . PHP_EOL,
			'## WP Defender - End ##' . PHP_EOL
		);

		$htConfig = str_replace( implode( '', $default ), '', $htConfig );
		$htConfig = trim( $htConfig );
		file_put_contents( $htPath, $htConfig, LOCK_EX );
	}

	public function unProtectIncludeDir() {
		$htPath = ABSPATH . WPINC . '/' . '.htaccess';
		if ( ! is_writeable( $htPath ) ) {
			return new \WP_Error( Error_Code::NOT_WRITEABLE,
				sprintf( __( "The file %s is not writeable", wp_defender()->domain ), $htPath ) );
		}
		$htConfig = file_get_contents( $htPath );
		$default  = array(
			'## WP Defender - Protect PHP Executed ##' . PHP_EOL,
			'<Files *.php>' . PHP_EOL .
			'Order allow,deny' . PHP_EOL .
			'Deny from all' . PHP_EOL .
			'</Files>' . PHP_EOL,
			'<Files wp-tinymce.php>' . PHP_EOL .
			'Allow from all' . PHP_EOL .
			'</Files>' . PHP_EOL,
			'<Files ms-files.php>' . PHP_EOL .
			'Allow from all' . PHP_EOL .
			'</Files>' . PHP_EOL,
			'## WP Defender - End ##' . PHP_EOL
		);
		$htConfig = str_replace( implode( '', $default ), '', $htConfig );
		$htConfig = trim( $htConfig );
		file_put_contents( $htPath, $htConfig, LOCK_EX );
	}

	/**
	 * @return bool|\WP_Error
	 */
	public function revert() {
		$ret = $this->unProtectContentDir();
		if ( is_wp_error( $ret ) ) {
			return $ret;
		}

		$ret = $this->unProtectIncludeDir();
		if ( is_wp_error( $ret ) ) {
			return $ret;
		}

		return true;
	}
}