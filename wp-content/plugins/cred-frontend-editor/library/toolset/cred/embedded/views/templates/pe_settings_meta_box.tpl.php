<?php if (!defined('ABSPATH')) die('Security check'); ?>
<?php
if (!isset($settings['post_expiration_cron']['schedule']))
    $settings['post_expiration_cron']['schedule'] = '';
if (!isset($schedules))
    $schedules = wp_get_schedules();
?>
<div id="cred_post_expiration_cron" style="margin-top:5px;margin-left:23px;clear:both;">
    <a id="cred-post-expiration-form"></a>

    <?php _e('Check for expired content:', $cred_post_expiration->getLocalizationContext()); ?>
    <select id="cred_post_expiration_cron" autocomplete="off" name="cred_post_expiration_cron_schedule" class='cred_ajax_change'>
        <?php foreach ($schedules as $schedule => $schedule_definition) { ?>
            <option value="<?php echo $schedule; ?>" <?php if ($schedule == $settings['post_expiration_cron']['schedule']) echo 'selected="selected"'; ?>><?php echo $schedule_definition['display']; ?></option>
        <?php } ?>
    </select>
    <?php if (false) { ?>
        <input type="hidden" name="cred_settings_action" value="cron" />
    <?php } ?>
</div>
