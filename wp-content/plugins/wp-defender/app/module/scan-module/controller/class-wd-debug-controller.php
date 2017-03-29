<?php

/**
 * @author: Hoang Ngo
 */
class WD_Debug_Controller extends WD_Controller {

	public function __construct() {
		if ( WD_Utils::http_get( 'debug', false ) == 1 || WD_Utils::http_get( 'page' ) == 'wdf-debug' ) {
			if ( is_multisite() ) {
				$this->add_action( 'network_admin_menu', 'admin_menu', 13 );
			} else {
				$this->add_action( 'admin_menu', 'admin_menu', 13 );
			}
			$this->add_action( 'admin_enqueue_scripts', 'load_scripts' );
			$this->add_action( 'wp_loaded', 'clear_log' );
		}
	}

	public function admin_menu() {
		$cap = is_multisite() ? 'manage_network_options' : 'manage_options';
		add_submenu_page( 'wp-defender', esc_html__( "Debug", wp_defender()->domain ), esc_html__( "Debug", wp_defender()->domain ), $cap, 'wdf-debug', array(
			$this,
			'display_main'
		) );
	}

	public function clear_log() {
		if ( ! WD_Utils::check_permission() ) {
			return;
		}

		if ( ! wp_verify_nonce( WD_Utils::http_post( 'wd_debug_nonce' ), 'wd_cleanup_log' ) ) {
			return;
		}

		$this->remove_logs();
	}

	/**
	 * check if this page is page of the plugin
	 * @return bool
	 */
	private function is_in_page() {
		$page = WD_Utils::http_get( 'page' );

		return $page == 'wdf-debug';
	}

	/**
	 * Check if in right page, then load assets
	 */
	public function load_scripts() {
		if ( $this->is_in_page() ) {
			WDEV_Plugin_Ui::load( wp_defender()->get_plugin_url() . 'shared-ui/', false );
			wp_enqueue_style( 'wp-defender' );
			wp_enqueue_script( 'wp-defender' );
		}
	}

	public function display_main() {
		$model = WD_Scan_APi::get_active_scan();
		if ( ! is_object( $model ) ) {
			$model = WD_Scan_APi::get_last_scan();
		}
		$logs      = $this->get_log_index();
		$logs_data = array();
		foreach ( $logs as $log ) {
			$logs_data[ $log ] = $this->get_log( $log );
		}

		$this->render( 'debug/debug', array(
			'model'         => $model,
			'core_files'    => WD_Utils::get_cache( WD_Scan_Api::CACHE_CORE_FILES, array() ),
			'content_files' => WD_Utils::get_cache( WD_Scan_Api::CACHE_CONTENT_FILES, array() ),
			'frag_files'    => WD_Utils::get_cache( WD_Scan_Api::CACHE_CONTENT_FILES_FRAG, array() ),
			'logs_data'     => $logs_data
		), true );
	}
}