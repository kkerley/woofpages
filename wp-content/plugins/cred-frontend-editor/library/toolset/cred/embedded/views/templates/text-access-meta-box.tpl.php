<?php if (!defined('ABSPATH')) die('Security check'); ?>
<table class="access-form-texts">
    <tbody>
    <div class="cred-notification <?php echo ($form_saved ? "cred-error" :"cred-info"); ?>">
        <div class="<?php echo ($form_saved ? "cred-error" :"cred-info"); ?>">
            <?php
            $txt_anchor = (isset($form_type) && $form_type == 'cred-user-form') ? "__CRED_CRED_USER_GROUP" : "__CRED_CRED_GROUP";
            if ($is_access_active) {
                ?>
                <p>
                    <i class="<?php echo ($form_saved ? "icon-warning-sign" :"icon-info-sign"); ?>"></i> <?php printf(__('To control who can see and use this form, go to %s.', 'wp-cred'), '<a target="_parent" href="' . admin_url( 'admin.php?page=types_access&tab=cred-forms' ) . '">Access settings</a>'); ?>
                </p>    
                <?php
            } else {
                ?>
                <p>
                    <i class="<?php echo ($form_saved ? "icon-warning-sign" :"icon-info-sign"); ?>"></i> <?php printf(__('This Form will be accessible to everyone, including guest (not logged in). They will be able to submit/edit content using this form.<br>To control who can use the form, please install %s.', 'wp-cred'), '<a target="_blank" href="https://wp-types.com/home/toolset-components/#access">Access plugin</a>'); ?>
                </p>
                <?php
            }
            ?>
        </div>
    </div>
</tbody>
</table>