<?php if (!defined('ABSPATH')) die('Security check'); ?>
<?php
if (empty($settings_defaults))
    exit;
    
if (empty($settings))
    $settings = array();

$settings = apply_filters("cred_modify_pe_settings_compatibility", $settings);    
 
if(!isset($settings['expiration_time']['expiration_period']))
    $settings['expiration_time']['expiration_period'] = "";

$expiration_period = (isset($settings['expiration_time']['expiration_period']) ? $settings['expiration_time']['expiration_period'] : "days");
$settings = CRED_PostExpiration::array_merge_distinct($settings_defaults, $settings);
?>

<fieldset class="cred-fieldset">
    <p class='cred_create_form-explain-text'>
        <?php echo _e('Expiration date:', $cred_post_expiration->getLocalizationContext()); ?>
    </p>

    <label class='cred-label' id="credpostexpirationdiv">
        <input type="checkbox" name="<?php echo $field_name; ?>[enable]" value="1" <?php if (1 == $settings['enable']) echo 'checked="checked"'; ?>>
        <span><?php _e('Set expiration date for post created or edited by this form', $cred_post_expiration->getLocalizationContext()); ?></span>
    </label>

    <div class="cred-label cred_post_expiration_panel" style="display: none;">

        <fieldset class="cred-fieldset">
            <p class="cred-explain-text">
                <?php //_e('The expiration time is set according to the publish date. This means it will take effect some time after the publish date of the post.', $cred_post_expiration->getLocalizationContext()); ?>
            </p>
            <p class="cred-label-holder">
                <label for="cred_post_expiration_time"><?php _e('Post will expire in:', $cred_post_expiration->getLocalizationContext()); ?></label>
                <span><input value="<?php echo ($settings['expiration_time']['expiration_date'] != null ? $settings['expiration_time']['expiration_date'] : 0); ?>" class="cred_number_input" type="number" min="0" name="<?php echo $field_name; ?>[expiration_time][expiration_date]" /></span>
                 <span>
                     <select  class="cred_expiration_period_by" name="<?php echo $field_name; ?>[expiration_time][expiration_period]">
                         <option value="minutes" <?php echo ($expiration_period == "minutes" || $expiration_period == "" ? "selected='selected'" : ""); ?>><?php _e('Minutes', $cred_post_expiration->getLocalizationContext()); ?></option>
                         <option value="hours"   <?php echo ($expiration_period == "hours" ? "selected='selected'" : ""); ?>><?php _e('Hours', $cred_post_expiration->getLocalizationContext()); ?></option>
                         <option value="days"    <?php echo ($expiration_period == "days" ? "selected='selected'" : ""); ?>><?php _e('Days', $cred_post_expiration->getLocalizationContext()); ?></option>
                         <option value="weeks"   <?php echo ($expiration_period == "weeks" ? "selected='selected'" : ""); ?>><?php _e('Weeks', $cred_post_expiration->getLocalizationContext()); ?></option>
                     </select>
                 </span>
                 <label><?php _e("from the publish date of the post."); ?></label>
            </p>
            <p class="cred-label-holder">
                <label for="cred_post_expiration_post_status"><?php _e('After expiration change the status of the post to:', $cred_post_expiration->getLocalizationContext()); ?></label>
                <?php
                $options = apply_filters('cred_pe_post_expiration_post_status', $cred_post_expiration->getActionPostStatus());
                ?>
                <select id="cred_post_expiration_post_status" name="<?php echo $field_name; ?>[action][post_status]" class="cred_ajax_change">
                    <?php foreach ($options as $value => $text) { ?>
                        <option value="<?php echo $value; ?>" <?php if ($value == $settings['action']['post_status']) echo 'selected="selected"'; ?>><?php echo $text; ?></option>
                    <?php } ?>
                </select>
            </p>
        </fieldset>
    </div>

</fieldset>


