<?php if (!defined('ABSPATH')) die('Security check'); ?>
<span class="cred-media-button cred-media-button2">
    <a href='javascript:;' class='button cred-icon-button' title='<?php echo esc_attr(__('Insert Subject Codes', 'wp-cred')); ?>'>
        <i class="icon-cred-logo ont-icon-18 ont-color-gray"></i><?php echo __('Insert Subject Codes', 'wp-cred'); ?></a>
    <div class="cred-popup-box">
        <div class='cred-popup-heading'>
            <h3><?php _e('Subject Codes (click to insert)', 'wp-cred'); ?></h3>
            <i title='<?php echo esc_attr(__('Close', 'wp-cred')); ?>' class='icon-remove cred-close-button cred-cred-cancel-close'></i>
        </div>
        <div class="cred-popup-inner cred-notification-subject-codes">
            <?php
            $notification_codes = apply_filters('cred_admin_notification_subject_codes', array(
                '%%USER_LOGIN_NAME%%' => __('(Logged in User) User Login Name', 'wp-cred'),
                '%%USER_DISPLAY_NAME%%' => __('(Logged in User) User Display Name', 'wp-cred'),
                '%%FORM_NAME%%' => __('Form Name', 'wp-cred'),
                '%%DATE_TIME%%' => __('Date/Time', 'wp-cred'),
                
                '%%USER_USERNAME%%' => __('User Username', 'wp-cred'),
                '%%USER_NICKNAME%%' => __('User Nickname', 'wp-cred'),
                '%%USER_PASSWORD%%' => __('User Password', 'wp-cred'),
                '%%RESET_PASSWORD_LINK%%' => __('Reset Password Link', 'wp-cred'),
                '%%USER_EMAIL%%' => __('User Email', 'wp-cred'),
                    ), $form, $ii, $notification);
            foreach ($notification_codes as $_v => $_l) {
                ?><a href="javascript:;" class='button cred_field_add_code'  data-area="#<?php echo $area_id; ?>" data-value="<?php echo $_v; ?>"><?php echo $_l; ?></a><?php
            }
            ?>
        </div>
    </div>
</span>