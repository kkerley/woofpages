<?php

/**
 * Automatic backup controller
 */
class Snapshot_Controller_Full_Cron extends Snapshot_Controller_Full {

	const OPTIONS_FLAG = 'snapshot_cron_backup_run';

	const BACKUP_KICKSTART_ACTION = 'process_backup';
	const BACKUP_FINISHING_ACTION = 'finish_backup-immediate';

	/**
	 * Singleton instance
	 *
	 * @var object
	 */
	private static $_instance;

	/**
	 * Overrides parent constructor to add
	 * the options flag
	 */
	protected function __construct () {
		parent::__construct();
		add_site_option(self::OPTIONS_FLAG, '');
	}

	/**
	 * Gets the singleton instance
	 *
	 * @return Snapshot_Controller_Full_Cron
	 */
	public static function get () {
		if (empty(self::$_instance)) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}

	/**
	 * Reschedules events according to the current (updated) setup
	 *
	 * @uses $this->stop() to stop any currently running processes first
	 * @uses $this->start() to (re)start
	 *
	 * @return bool
	 */
	public function reschedule () {
		$this->stop();
		return $this->start();
	}

	/**
	 * Dispatch cron schedules
	 */
	public function run () {
		add_filter('cron_schedules', array($this, 'add_cron_schedule_intervals'));

		if ('init' === current_filter()) $this->set_up_scheduling();
		else add_action('init', array($this, 'set_up_scheduling'));
	}

	/**
	 * Stops and unschedules cron events
	 *
	 * @return bool
	 */
	public function stop () {
		delete_site_option(self::OPTIONS_FLAG);

		$this->_unschedule_backup_starting();
		$this->_unschedule_backup_processing();

		$this->_delete_started_backups();

		return true;
	}

	/**
	 * Reschedules the backup process.
	 *
	 * This is to be called from user action processing method.
	 *
	 * @uses $this->set_up_scheduling() to attach hooks and set up local rotation scheduling
	 * @since 3.0.1
	 *
	 * @return bool
	 */
	public function start () {
		$this->set_up_scheduling();
		return $this->_schedule_backup_starting();
	}

	/**
	 * Runs on plugin deactivation
	 *
	 * @return bool
	 */
	public function deactivate () {
		$this->stop();
		$this->_unschedule_backup_local_rotation();

		return true;
	}

	/**
	 * Stops any backups already happening
	 *
	 * @return bool
	 */
	private function _delete_started_backups () {
		$idx = $this->_get_backup_type();
		if (empty($idx)) return false;

		$backup = Snapshot_Helper_Backup::load($idx);
		if (empty($backup)) return false;

		return $backup->stop_and_remove();
	}

	/**
	 * Add our cron schedules, as required and set up in settings
	 *
	 * @param array $intervals A list of known intervals
	 *
	 * @return array Augmented intervals list
	 */
	public function add_cron_schedule_intervals ($intervals) {
		if (!is_array($intervals)) return $intervals;

		// Processing interval
		// This is actually the "kickstart" interval - normal processing will work
		// off of single scheduled events firing on next request.
		// This one's just for rebooting the process in case the normal flow staled.
		$intervals[$this->get_filter('process_interval')] = array(
			'display' => __('Managed Backup Processing', SNAPSHOT_I18N_DOMAIN),
			'interval' => 3600,
		);

		// Various start intervals
		$intervals[$this->get_filter('daily')] = array(
			'display' => __('Daily Managed Snapshot', SNAPSHOT_I18N_DOMAIN),
			'interval' => DAY_IN_SECONDS,
		);
		$intervals[$this->get_filter('weekly')] = array(
			'display' => __('Weekly Managed Snapshot', SNAPSHOT_I18N_DOMAIN),
			'interval' => 7 * DAY_IN_SECONDS,
		);
		$intervals[$this->get_filter('monthly')] = array(
			'display' => __('Monthly Managed Snapshot', SNAPSHOT_I18N_DOMAIN),
			'interval' => 30 * DAY_IN_SECONDS,
		);

		return $intervals;
	}

	/**
	 * Sets up backup scheduling when the model data is ready for it
	 */
	public function set_up_scheduling () {
		// Do local backup rotation automagic schedule first.
		// This is so we allow this to happen even if further cron jobs
		// are not being allowed to run (for actual backups making)
		$this->_schedule_backup_local_rotation();

		if ($this->_model->get_config('disable_cron', false)) return false;

		// Deprecated the schedule auto starting in favor of
		// semi-automatic schedule setup with user action
		//$this->_auto_schedule_backup_starting();

		// Now the schedule action listeners here

		// Add scheduled backup start action listener
		$start_action = $this->get_filter('start_backup');
		add_action($start_action, array($this, 'start_backup'));

		// Add kickstart processing action handler listening
		$kickstart_action = $this->get_filter(self::BACKUP_KICKSTART_ACTION);
		add_action($kickstart_action, array($this, 'kickstart_backup_processing'));

		// Additional listener - finish backup action is only scheduled
		// as immediately next process that's to happen
		$finish_action = $this->get_filter(self::BACKUP_FINISHING_ACTION);
		add_action($finish_action, array($this, 'finish_backup'));

		// Cron respawning action hooks
		add_action('wp_ajax_nopriv_snapshot-full_backup-respawn_cron', array($this, 'json_respawn_cron'));
	}

	/**
	 * Actually starts the backup process
	 */
	public function start_backup () {
		delete_site_option(self::OPTIONS_FLAG);
		if ($this->_model->get_config('disable_cron', false)) return false;

		if (!$this->_is_backup_processing_ready()) return false;

		// Signal intent - starting action
		Snapshot_Helper_Log::start();

		Snapshot_Helper_Log::note("Backup ready to start", "Cron");

		$idx = $this->_get_backup_type();
		$this->_start_backup($idx);

		// Reschedule
		$this->_reschedule_immediate_processing();

		$this->_ping_self(self::BACKUP_KICKSTART_ACTION);
	}

	/**
	 * Process the started backup
	 */
	public function process_backup () {
		$this->_ignore_user_abort();

		if ($this->_model->get_config('disable_cron', false)) return false;
		if (!$this->_is_backup_processing_ready()) return false;
		if (get_site_option(Snapshot_Controller_Full_Ajax::OPTIONS_FLAG)) return false;

		update_site_option(self::OPTIONS_FLAG, 'true');
		Snapshot_Helper_Log::info("Processing backup", "Cron");

		$idx = $this->_get_backup_type();

		// Process stuff now!
		$status = false;
		try {
			$status = $this->_process_backup($idx);
		} catch (Snapshot_Exception $e) {
			// First thing's first, unschedule processing
			$this->_unschedule_backup_processing();

			$key = $e->get_error_key();
			$msg = Snapshot_Model_Full_Error::get_human_description($key);

			Snapshot_Helper_Log::error("Error processing automatic backup: {$key}", "Cron");
			Snapshot_Helper_Log::note($msg, "Cron");

			delete_site_option(self::OPTIONS_FLAG);

			/**
			 * Automatic backup processing encountered too many errors
			 *
			 * @since 3.0-beta-12
			 *
			 * @param string Action type indicator (process or finish)
			 * @param string $key Error message key
			 * @param string $msg Human-friendly message description
			 */
			do_action($this->get_filter('cron-error-stop'), 'process', $key, $msg); // Notify anyone interested

			return false; // Just fully stop
		}

		// Are we there yet?
		if ($status) {
			// Done processing, start finalizing
			$this->finish_backup();
		} else {
			// Not done processing, reschedule
			$this->_reschedule_immediate_processing();
		}

		delete_site_option(self::OPTIONS_FLAG);

		// So now that we know we're not yet
		// done processing, lets attempt to ping ourselves
		if (!$status) {
			$this->_ping_self(self::BACKUP_KICKSTART_ACTION);
		}
	}

	/**
	 * Kickstarts the backup processing
	 *
	 * @uses $this->process_backup()
	 */
	public function kickstart_backup_processing () {
		Snapshot_Helper_Log::warn("Immediate hook misfired, kickstart backup processing", "Cron");
		$this->process_backup();
	}

	/**
	 * Finish the started backup
	 *
	 * @return bool
	 */
	public function finish_backup () {
		$this->_ignore_user_abort();

		if ($this->_model->get_config('disable_cron', false)) return false;
		if (!$this->_is_backup_processing_ready()) return false;

		// First thing's first, unschedule processing
		$this->_unschedule_backup_processing();

		update_site_option(self::OPTIONS_FLAG, 'true');
		Snapshot_Helper_Log::info("Finishing backup", "Cron");

		$idx = $this->_get_backup_type();

		$status = false;
		try {
			$status = $this->_finish_backup($idx);
		} catch (Snapshot_Exception $e) {
			// First thing's first, unschedule processing
			$this->_unschedule_backup_processing();

			$key = $e->get_error_key();
			$msg = Snapshot_Model_Full_Error::get_human_description($key);
			Snapshot_Helper_Log::error("Error finalizing automatic backup: {$key}", "Cron");
			Snapshot_Helper_Log::note($msg, "Cron");

			delete_site_option(self::OPTIONS_FLAG);

			/**
			 * Automatic backup processing encountered too many errors
			 *
			 * @since 3.0-beta-12
			 *
			 * @param string Action type indicator (process or finish)
			 * @param string $key Error message key
			 * @param string $msg Human-friendly message description
			 */
			do_action($this->get_filter('cron-error-stop'), 'finish', $key, $msg); // Notify anyone interested

			return false; // Just fully stop
		}

		if (!$status) {
			Snapshot_Helper_Log::note("Rescheduling backup finalization", "Cron");
			$this->_schedule_immediate_backup_finish();
		} else {
			$this->_unschedule_backup_processing(); // Just for good measure, we're done here
			Snapshot_Helper_Log::info("Backup finished", "Cron");
		}

		delete_site_option(self::OPTIONS_FLAG);

		// So now that we know we're not yet
		// done processing, lets attempt to ping ourselves
		if (!$status) {
			$this->_ping_self(self::BACKUP_FINISHING_ACTION);
		}

		return $status;
	}

	/**
	 * Checks whether the cron-scheduled backup is currently running.
	 *
	 * @return bool
	 */
	public static function is_running () {
		if (self::is_processing()) return true; // We're already running

		$next = wp_next_scheduled(self::get()->get_filter(self::BACKUP_KICKSTART_ACTION));
		if ($next) return true;

		$next = wp_next_scheduled(self::get()->get_filter(self::BACKUP_FINISHING_ACTION));
		if ($next) return true;

		return false;
	}

	/**
	 * Checks whether the cron-scheduled backup is currently processing
	 *
	 * @return bool
	 */
	public static function is_processing () {
		$switch = get_site_option(self::OPTIONS_FLAG, false, false);
		return !empty($switch);
	}

	/**
	 * Local backups rotation handler
	 */
	public function rotate_local_backups () {
		return $this->_model->rotate_local_backups();
	}

	/**
	 * Propagate the cron job request
	 *
	 * This method will re-ping the cron endpoint
	 * and force the cron job restart so we rely on
	 * traffic much less
	 *
	 * @param string $job Optional job spec, defaults to kickstart
	 *
	 * @return bool
	 */
	private function _ping_self ($job=false) {
		$job = self::BACKUP_FINISHING_ACTION !== $job
			? self::BACKUP_KICKSTART_ACTION
			: self::BACKUP_FINISHING_ACTION
		;

		return $this->_send_self_request_ping($job);
	}

	/**
	 * Actually sends out the ping request
	 *
	 * @param string $job Optional job spec, defaults to kickstart
	 *
	 * @return bool
	 */
	private function _send_self_request_ping ($job=false) {
		if ($this->_model->get_config('disable_cron', false)) return false;

		$job = empty($job) || !in_array($job, array(self::BACKUP_KICKSTART_ACTION, self::BACKUP_FINISHING_ACTION))
			? self::BACKUP_KICKSTART_ACTION
			: $job
		;

		wp_remote_post(
			admin_url('admin-ajax.php?action=snapshot-full_backup-respawn_cron&doing_wp_cron=1'),
			array(
				'timeout'   => 0.01,
				'blocking'  => false,
				'sslverify' => false,
				'body' => array(
					'job' => $job,
				),
			)
		);

		return true;
	}

	/**
	 * AJAX handler for processing respawns
	 */
	public function json_respawn_cron () {
		$this->_ignore_user_abort();

		if ($this->_model->get_config('disable_cron', false)) die;
		//if (!defined('DOING_CRON')) define('DOING_CRON', true);
		if (!defined('DISABLE_WP_CRON')) define('DISABLE_WP_CRON', true); // No. Bad cron. Not happening.

		$data = stripslashes_deep($_POST);
		$type = !empty($data['job']) && self::BACKUP_FINISHING_ACTION === $data['job']
			? self::BACKUP_FINISHING_ACTION
			: self::BACKUP_KICKSTART_ACTION
		;

		if (self::BACKUP_KICKSTART_ACTION === $type) $this->process_backup();
		else if (self::BACKUP_FINISHING_ACTION === $type) $this->finish_backup();

		die;
	}

	/**
	 * Gets kickstart delay time, in seconds
	 *
	 * @return int
	 */
	public function get_kickstart_delay () {
		return (int)apply_filters(
			$this->get_filter('kickstart-delay'),
			5 * 60
		);
	}

	/**
	 * Wraps the user abort prevention calls
	 *
	 * @return bool
	 */
	private function _ignore_user_abort () {
		if (function_exists('ignore_user_abort')) ignore_user_abort(true);
		if (function_exists('fastcgi_finish_request')) fastcgi_finish_request();
		return true;
	}

	/**
	 * Schedules backup starting
	 *
	 * Dumb, manual schedule starting interface, decoupled from
	 * the action hookup. Serves single duty.
	 *
	 * @since 3.0.1
	 *
	 * @return bool
	 */
	private function _schedule_backup_starting () {
		if ($this->_model->get_config('disable_cron', false)) return false;

		$start_action = $this->get_filter('start_backup');

		$next_scheduled = wp_next_scheduled($start_action);
		if ($next_scheduled) {
			Snapshot_Helper_Log::info("Found previous backup start set for " . date('r', $next_scheduled) . ", rescheduling first.", "Cron");
			$this->_unschedule_backup_starting();
		}

		$frequency = $this->_model->get_frequency();

		$schedule = $this->_model->get_schedule_time();
		$now = Snapshot_Model_Time::get()->get_utc_time();
		$next_event = strtotime(date("Y-m-d 00:00:00", $now), $now) + $schedule;

		if ($now > $next_event) $next_event += DAY_IN_SECONDS; // Local time of next event is in the past, move to future
		// Allow for filtering
		$next_event = apply_filters(
			$this->get_filter('next_backup_start'),
			$next_event
		);

		$status = wp_schedule_event($next_event, $this->get_filter($frequency), $start_action);
		Snapshot_Helper_Log::info("Next start action scheduled for " . date('r', $next_event), "Cron");

		// Use strict type check instead of boolean type casting, because
		// the `wp_schedule_event` will return:
		//   - (bool)false on failure,
		//   - undefined otherwise
		return false !== $status;
	}

	/**
	 * Schedule started backups start.
	 *
	 * This should happen only as set in settings.
	 *
	 * @deprecated since 3.0.1 Also renamed with `auto_` prefix
	 *
	 * @return bool
	 */
	private function _auto_schedule_backup_starting () {
		if ($this->_model->get_config('disable_cron', false)) return false;

		$start_action = $this->get_filter('start_backup');
		add_action($start_action, array($this, 'start_backup'));

		$next_scheduled = wp_next_scheduled($start_action);
		if (!$next_scheduled) {
			$frequency = $this->_model->get_frequency();

			$schedule = $this->_model->get_schedule_time();
			$now = Snapshot_Model_Time::get()->get_utc_time();
			$next_event = strtotime(date("Y-m-d 00:00:00", $now), $now) + $schedule;

			if ($now > $next_event) $next_event += DAY_IN_SECONDS; // Local time of next event is in the past, move to future
			// Allow for filtering
			$next_event = apply_filters(
				$this->get_filter('next_backup_start'),
				$next_event
			);

			wp_schedule_event($next_event, $this->get_filter($frequency), $start_action);
			Snapshot_Helper_Log::info("Next start action scheduled for " . date('r', $next_event), "Cron");
		}/* else {
			Snapshot_Helper_Log::note("Start action already scheduled for " . date('r', $next_scheduled), "Cron");
		}*/

		return true;
	}

	/**
	 * Unchedule backup start events
	 *
	 * @return bool
	 */
	private function _unschedule_backup_starting () {
		return $this->_unschedule_backup_event('start_backup');
	}

	/**
	 * (Re)schedules started backups processing.
	 *
	 * This is a processing kickstart event, in case the
	 * normal single event rescheduling flow got stalled for whatever reason
	 *
	 * @return bool
	 */
	private function _reschedule_backup_kickstart_processing () {
		if ($this->_model->get_config('disable_cron', false)) return false;

		$kickstart_action = $this->get_filter(self::BACKUP_KICKSTART_ACTION);
		$this->_unschedule_backup_event(self::BACKUP_KICKSTART_ACTION); // Kill kickstart event

		$kickstart_time = apply_filters(
			$this->get_filter('backup-kickstart'),
			Snapshot_Model_Time::get()->get_utc_time() + $this->get_kickstart_delay()
		);

		wp_schedule_event($kickstart_time, $this->get_filter('process_interval'), $kickstart_action);
		Snapshot_Helper_Log::info("Next process kickstart action scheduled for " . date('r', $kickstart_time), "Cron");

		return true;
	}

	/**
	 * Set up processing for the immediate next load
	 *
	 * Also reschedule the kickstart processing event
	 *
	 * @return bool
	 */
	private function _reschedule_immediate_processing () {
		if ($this->_model->get_config('disable_cron', false)) return false;

		return $this->_reschedule_backup_kickstart_processing(); // Reschedule kickstart action first
	}

	/**
	 * Schedules immediate backup finishing
	 *
	 * This will happen if the original backup finish didn't succeed
	 * on the first go - e.g. rotation took place
	 *
	 * @return bool
	 */
	private function _schedule_immediate_backup_finish () {
		if ($this->_model->get_config('disable_cron', false)) return false;

		// We're in the home stretch now, nerf the backup processing
		$this->_unschedule_backup_processing(); // This also removes the finishing hook...
		// ... so let's reschedule that one below:

		$immediate_action = $this->get_filter(self::BACKUP_FINISHING_ACTION);
		return wp_schedule_single_event(Snapshot_Model_Time::get()->get_utc_time() + $this->get_kickstart_delay(), $immediate_action);
	}

	/**
	 * Unchedule backup processing events
	 *
	 * @return bool
	 */
	private function _unschedule_backup_processing () {
		$this->_unschedule_backup_event(self::BACKUP_KICKSTART_ACTION);

		// Also unschedule immediate backup finishing
		$this->_unschedule_backup_event(self::BACKUP_FINISHING_ACTION);

		return true;
	}


	/**
	 * Unschedules a backup event
	 *
	 * @param  string $hook Hook to unschedule
	 *
	 * @return bool
	 */
	private function _unschedule_backup_event ($hook) {
		$action = $this->get_filter($hook);

		$next_scheduled = wp_next_scheduled($action);
		if ($next_scheduled) wp_unschedule_event($next_scheduled, $action);

		wp_clear_scheduled_hook($action);
		return true;
	}

	/**
	 * Schedule local backups rotation.
	 *
	 * This should happen once a day
	 *
	 * @return bool
	 */
	private function _schedule_backup_local_rotation () {
		$rotate_action = $this->get_filter('rotate_local_backups');
		add_action($rotate_action, array($this, 'rotate_local_backups'));
		if (!wp_next_scheduled($rotate_action)) {
			$now = Snapshot_Model_Time::get()->get_utc_time();
			$next_event = Snapshot_Model_Time::get()->to_utc_time(strtotime(date("Y-m-d 00:00:00", $now), $now) + 100);
			if ($now > $next_event) $next_event += DAY_IN_SECONDS;

			wp_schedule_event($now, $this->get_filter('daily'), $rotate_action);
		}
		return true;
	}

	/**
	 * Unschedules local backups rotation
	 */
	public function _unschedule_backup_local_rotation () {
		$this->_unschedule_backup_event('rotate_local_backups');
	}

	/**
	 * Filter/action name getter
	 *
	 * @param string $filter Filter name to convert
	 *
	 * @return string Full filter name
	 */
	public function get_filter ($filter=false) {
		if (empty($filter)) return false;
		if (!is_string($filter)) return false;
		return 'snapshot-controller-full-cron-' . $filter;
	}
}