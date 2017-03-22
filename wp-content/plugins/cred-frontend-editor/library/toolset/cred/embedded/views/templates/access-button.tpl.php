<?php if (!defined('ABSPATH'))  die('Security check'); ?>
<span id="cred-access-button" class="cred-media-button">
    <a href='javascript:;' id="cred-access-button-button" class='button cred-button' title='<?php echo esc_attr(__('Access Settings','wp-cred')); ?>'>
        <i class="icon-cred-logo ont-icon-18"></i><?php _e('Access Settings','wp-cred'); ?></a>
    <div id="cred-access-box" class="cred-popup-box">
        <div class='cred-popup-heading'>
        <h3><?php _e('Access Settings','wp-cred'); ?></h3>
        <i title='<?php echo esc_attr(__('Close','wp-cred')); ?>' class='icon-remove cred-close-button cred-cred-cancel-close'></i>
        </div>
        <div id="cred-access-box-inner" class="cred-popup-inner">
            <ul>
                <li><span><input type="radio" name="access_options"/> Access First Option</span></li>
                <li><span><input type="radio" name="access_options"/> Access Second Option</span></li>
            </ul>  
        </div>
    </div>
</span>