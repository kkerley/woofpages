<?php if (!defined('ABSPATH')) die('Security check'); ?>
<?php
$settings = CRED_Helper::mergeArrays(array(
            'type' => 'new',
            'action' => 'form',
            'user_role' => '',
            'action_page' => '',
            'action_message' => '',
            'redirect_delay' => 0,
            'hide_comments' => 0,
            'theme' => 'minimal',
            'has_media_button' => 0,
            'include_wpml_scaffold' => 0,
            'include_captcha_scaffold' => 0
                ), (array) $settings);

$settings['user_role'] = json_decode($settings['user_role'], true);

if (is_array($settings['user_role'])) {
    array_filter($settings['user_role']);
}
?>
<fieldset class="cred-fieldset">
    <?php wp_nonce_field('cred-admin-post-page-action', 'cred-admin-post-page-field'); ?>

        <!--<p class='cred_create_form-explain-text'><?php //_e('Forms can create new content or edit existing content. Choose what this form will do:', 'wp-cred');                                       ?></p>-->
    <p class="cred_create_form-explain-text">
        <?php _e("This form will:", "wp-cred"); ?>
    </p>
    <p class="cred_form_input left-margin-second-child">
        <?php
        $form_types = apply_filters('cred_admin_form_type_options', array(
            "new" => __('Create new user', 'wp-cred'),
            "edit" => __('Edit existing user', 'wp-cred')
                ), $settings['type'], $form);
        $n = 1;
        foreach ($form_types as $_v => $_l) {
            $class = "";
            if ($n > 1)
                $class = "cred_left_margin";            
            if (empty($settings['type']) && $n == 1)
                $checked = "checked='checked'";
            else
                $checked = (isset($settings['type']) && $settings['type'] == $_v) ? "checked='checked'" : "";
            ?><label class="cred-label <?php echo $class; ?>" style="display: inline;margin-right: 10px;"><input type="radio" name="_cred[form][type]" value="<?php echo $_v; ?>" <?php echo $checked; ?> /><span><?php echo $_l; ?></span></label><?php
            $n++;
        }
        ?>
    </p>

    <p class="cred-label-holder cred_create_form-explain-text">
        <?php _e('Select allowed User Role', 'wp-cred'); ?>
    </p>
    <p class="cred_form_input">
        <select class="roles_selectbox" id="cred_form_user_role" name="_cred[form][user_role][]" autocomplete="false">
            <option value=""><?php echo __('-- Select role --'); ?></option><?php
        foreach ($user_roles as $k => $v) {
            ?><option value="<?php echo $k; ?>"><?php echo $v['name']; ?></option><?php
            }
            ?>
        </select>   

        <?php
        foreach ($user_roles as $k => $v) {
            ?><label class="roles_checkboxes cred-label-inline"><?php
            ?><input class="roles_checkboxes" id="role_<?php echo $k; ?>" type="checkbox" name="_cred[form][user_role][]" value="<?php echo $k; ?>"><?php echo $v['name']; ?><?php
            ?></label><?php
            }
            ?>                    
    </p>

    <script>
        function role_switch() {
            if (jQuery("input[name='_cred[form][type]']:checked").val() == 'edit') {
                jQuery(".roles_checkboxes").prop("disabled", false);
                jQuery(".roles_checkboxes").show();
                jQuery(".roles_selectbox").prop("disabled", true);
                jQuery(".roles_selectbox").hide();
            } else {
                jQuery(".roles_checkboxes").prop("disabled", true);
                jQuery(".roles_checkboxes").hide();
                jQuery(".roles_selectbox").prop("disabled", false);
                jQuery(".roles_selectbox").show();
            }
        }
        jQuery('input[name="_cred[form][type]"]').on("click", function () {
            role_switch();
        });
        jQuery(document).ready(function () {
            role_switch();
<?php
foreach ($user_roles as $k => $v) {
    if ($settings['type'] == 'edit')
        echo (is_array($settings['user_role']) && in_array($k, $settings['user_role'])) ? "jQuery('#role_{$k}').prop( 'checked', true );" : "";
    else {
        if (is_array($settings['user_role']) && empty($settings['user_role'])) {
            echo "jQuery('#cred_form_user_role option:first').prop('selected','selected');";
        } else {
            if (is_array($settings['user_role']) && in_array($k, $settings['user_role'])) {
                echo "jQuery('#cred_form_user_role option[value={$k}]').prop('selected','selected');";
            } else {
                if (isset($settings['user_role'][0]) && empty($settings['user_role'][0]))
                    echo "jQuery('#cred_form_user_role option:first').prop('selected','selected');";
            }
        }
    }
}
?>
        });
    </script>     

    <p class='cred-label-holder cred_create_form-explain-text'>
        <?php _e('After user submits this form:', 'wp-cred'); ?>
    </p>
    <p class="cred_form_input">
        <select id="cred_form_success_action" name="_cred[form][action]">
            <?php
            $form_actions = apply_filters('cred_admin_submit_action_options', array(
                "form" => __('Keep displaying this form', 'wp-cred'),
                "message" => __('Display a message instead of the form...', 'wp-cred'),
                "user" => __('Display the user', 'wp-cred'),
                "page" => __('Go to a page...', 'wp-cred')
                    ), $settings['action'], $form);
            ?><option value="" selected="selected"><?php echo __('-- Select action --', 'wp-cred'); ?></option><?php
            foreach ($form_actions as $_v => $_l) {
                if (isset($settings['action']) && $settings['action'] == $_v) {
                    ?><option value="<?php echo $_v; ?>" selected="selected"><?php echo $_l; ?></option><?php
                } else {
                    ?><option value="<?php echo $_v; ?>"><?php echo $_l; ?></option><?php
                    }
                }
                ?>
        </select>
    </p>

    <table width="100%">
        <tr>
            <td width="40%" style="vertical-align: top;">
            </td>
            <td>
                <span data-cred-bind="{ action: 'show', condition: '_cred[form][action]=page' }">
                    <select id="cred_form_success_action_page" name="_cred[form][action_page]">
                        <optgroup label="<?php echo esc_attr(__('Please Select..', 'wp-cred')); ?>">
                            <?php echo $form_action_pages; ?>
                        </optgroup>
                    </select>
                </span>

                <span data-cred-bind="{ action: 'show', condition: '_cred[form][action]=user' }">
                    <input type='text' id='action_user' name='_cred[form][action_user]' value='' placeholder="<?php echo esc_attr(__('Type some characters..', 'wp-cred')); ?>" />
                </span>

                <span data-cred-bind="{ action: 'show', condition: '_cred[form][action] in [post,page]' }">
                    <?php _e('Redirect delay (in seconds)', 'wp-cred'); ?>
                    <input type='text' size='3' id='cred_form_redirect_delay' name='_cred[form][redirect_delay]' value='<?php echo esc_attr($settings['redirect_delay']); ?>' />
                </span>        
            </td>
        </tr>
    </table>

    <div data-cred-bind="{ action: 'toggle', condition: '_cred[form][action]=message' }">
        <table width="100%">
            <tr>
                <td width="40%" style="vertical-align: top;">
                </td>
                <td>
                    <p class='cred-explain-text'>
                        <?php _e('Enter the message to display instead of the form. You can use HTML and shortcodes. (but no CRED User Forms)', 'wp-cred'); ?>
                    </p>
                    <?php echo CRED_Helper::getRichEditor('credformactionmessage', '_cred[form][action_message]', $settings['action_message'], array('wpautop' => true, 'teeny' => true, 'editor_height' => 200, 'editor_class' => 'wpcf-wysiwyg')); ?>
                    <!--<textarea id='cred_form_action_message' name='_cred[form][action_message]' style="position:relative; width:95%;"><?php //echo esc_textarea($settings['action_message']);                                           ?></textarea>-->
                    <!-- correct initial value -->
                    <script type='text/javascript'>
                        /* <![CDATA[ */
                        (function (window, $, undefined) {
                            $(function () {
                                try {
                                    $('#credformactionmessage').val($('#credformactionmessage').text());
                                } catch (e) {
                                }
                            });
                        })(window, jQuery);
                        /* ]]> */
                    </script>
                </td>
            </tr>
        </table>
    </div> 
</fieldset>

<fieldset class="cred-fieldset">
    <table width="100%">
        <tr>
            <td width="40%" style="vertical-align: top;">
                <strong><?php _e('Other settings:', 'wp-cred'); ?></strong>
            </td>
            <td>
                <label class='cred-label-chk'>
                    <input type='checkbox' class='cred-checkbox-10' name='_cred[form][use_ajax]' id='cred_content_has_media_button' value='1' <?php if (isset($settings['use_ajax']) && $settings['use_ajax'] == '1') echo 'checked="checked"'; ?> /><span class='cred-checkbox-replace'></span>
                    <span><?php _e('AJAX submission', 'wp-cred'); ?></span>
                </label>   
            </td>
        </tr>
    </table>

    <p>
        <?php
        do_action('cred_ext_cred_user_form_settings', $form, $settings);
        ?>
    </p>

</fieldset>

<?php if (false) { ?>
    <a class='cred-help-link' href='<?php echo $help['general_form_settings']['link']; ?>' target='<?php echo $help_target; ?>' title="<?php echo esc_attr($help['general_form_settings']['text']); ?>" style="position:absolute;top:5px;right:10px">
        <i class="icon-question-sign"></i>
        <span><?php echo $help['general_form_settings']['text']; ?></span>
    </a>
<?php } ?>
<script>
    var url = "<?php echo admin_url('admin-ajax.php'); ?>?action=cred_ajax_Posts&_do_=suggestUserByName";
    jQuery('#action_user').cred_suggest(url, {
        delay: 200,
        minchars: 3,
        multiple: false,
        multipleSep: '',
        resultsClass: 'ac_results',
        selectClass: 'ac_over',
        matchClass: 'ac_match',
        onStart: function () {

        },
        onComplete: function () {

        }
    });
</script>