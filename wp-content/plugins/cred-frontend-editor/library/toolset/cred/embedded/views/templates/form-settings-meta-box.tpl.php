<?php if (!defined('ABSPATH')) die('Security check'); ?>
<?php
$settings = CRED_Helper::mergeArrays(array(
            'post' => array(
                'post_type' => 'post',
                'post_status' => 'draft'
            ),
            'form' => array(
                'type' => 'new',
                'action' => 'form',
                'action_page' => '',
                'action_message' => '',
                'redirect_delay' => 0,
                'hide_comments' => 0,
                'theme' => 'minimal',
                'has_media_button' => 0,
                'include_wpml_scaffold' => 0,
                'include_captcha_scaffold' => 0
            )
                ), (array) $settings);
?>
<script>
    var $__my_option = "<option value='original'><?php _e("Keep original status", "wp-cred"); ?></option>";
</script>
<fieldset class="cred-fieldset cred-no-bottom-margin">
    <?php wp_nonce_field('cred-admin-post-page-action', 'cred-admin-post-page-field'); ?>

    <!--<p class='cred_create_form-explain-text'><?php //_e('Forms can create new content or edit existing content. Choose what this form will do:', 'wp-cred');                          ?></p>-->
    <p class="cred_create_form-explain-text">
        <?php _e("This form will:", "wp-cred"); ?>
    </p>
    <p class="cred_form_input left-margin-second-child">
        <?php
        $form_types = apply_filters('cred_admin_form_type_options', array(
            "new" => __('Add new content', 'wp-cred'),
            "edit" => __('Edit existing content', 'wp-cred')
                ), $settings['type'], $form);
        $n = 1;
        foreach ($form_types as $_v => $_l) {
            $class = "";
            if (empty($settings['type']) && $n == 1)
                $checked = "checked='checked'";
            else
                $checked = (isset($settings['type']) && $settings['type'] == $_v) ? "checked='checked'" : "";
            ?><label class="cred-label" style="display: inline;margin-right: 10px;"><input type="radio" name="_cred[form][type]" value="<?php echo $_v; ?>" <?php echo $checked; ?> /><span><?php echo $_l; ?></span></label><?php
            $n++;
        }
        ?>
                </p> 

                <p class='cred-label-holder cred_create_form-explain-text'>
                    <label for="cred_post_type"><?php _e('Post Types connected to this form:', 'wp-cred'); ?></label>
                </p>
                <p class="cred_form_input">
                    <select id="cred_post_type" name="_cred[post][post_type]" class='cred_ajax_change'>
                        <?php
                        echo '<option value="" selected="selected">' . __('-- Select Post Type --', 'wp-cred') . '</option>';
                        foreach ($post_types as $pt) {
                            if (!has_filter('cred_wpml_glue_is_translated_and_unique_post_type') || apply_filters('cred_wpml_glue_is_translated_and_unique_post_type', $pt['type'])) {
                                if ($settings['post']['post_type'] == $pt['type'] || (isset($_GET['glue_post_type']) && $pt['type'] == $_GET['glue_post_type']))
                                    echo '<option value="' . $pt['type'] . '" selected="selected">' . $pt['name'] . '</option>';
                                else
                                    echo '<option value="' . $pt['type'] . '">' . $pt['name'] . '</option>';
                            }
                        }
                        ?>
                    </select>
                </p>

                <p class="cred-label-holder cred_create_form-explain-text">
                    <label for="cred_post_status"><?php _e('Status of content created or edited by this form:', 'wp-cred'); ?></label>
                </p>
                <p class="cred_form_input">
                    <select id="cred_post_status" name="_cred[post][post_status]" class='cred_ajax_change'>
                        <option value='' <?php if (!isset($settings['post']['post_status']) || empty($settings['post']['post_status'])) echo 'selected="selected"'; ?>><?php _e('-- Select status --', 'wp-cred'); ?></option>
                        <option value='original' <?php if ($settings['post']['post_status'] == 'original') echo 'selected="selected"'; ?>><?php _e('Keep original status', 'wp-cred'); ?></option>
                        <option value='draft' <?php if ($settings['post']['post_status'] == 'draft') echo 'selected="selected"'; ?>><?php _e('Draft', 'wp-cred'); ?></option>
                        <option value='pending' <?php if ($settings['post']['post_status'] == 'pending') echo 'selected="selected"'; ?>><?php _e('Pending Review', 'wp-cred'); ?></option>
                        <option value='private' <?php if ($settings['post']['post_status'] == 'private') echo 'selected="selected"'; ?>><?php _e('Private', 'wp-cred'); ?></option>
                        <option value='publish' <?php if ($settings['post']['post_status'] == 'publish') echo 'selected="selected"'; ?>><?php _e('Published', 'wp-cred'); ?></option>
                    </select>
                </p>

                <p class='cred_create_form-explain-text'>
                    <?php _e('After visitors submit this form:', 'wp-cred'); ?>
                </p>
                <p class="cred_form_input">
                    <select id="cred_form_success_action" name="_cred[form][action]">
                        <?php
                        $form_actions = apply_filters('cred_admin_submit_action_options', array(
                            "form" => __('Keep displaying this form', 'wp-cred'),
                            "message" => __('Display a message instead of the form...', 'wp-cred'),
                            "post" => __('Display the post', 'wp-cred'),
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
                                    <optgroup label="<?php echo esc_attr(__('- - Select page - -', 'wp-cred')); ?>">
                                        <?php echo $form_action_pages; ?>
                                    </optgroup>
                                </select>
                            </span>

<span data-cred-bind="{ action: 'show', condition: '_cred[form][action]=post' }">
                                <input type='text' id='action_post' name='_cred[form][action_post]' value='' placeholder="<?php echo esc_attr(__('Type some characters..', 'wp-cred')); ?>" />
                            </span>
                            <span data-cred-bind="{ action: 'show', condition: '_cred[form][action] in [post,page]' }">
                                <?php _e('Redirect delay for: ', 'wp-cred'); ?>
                                <input type='text' size='3' id='cred_form_redirect_delay' name='_cred[form][redirect_delay]' value='<?php echo esc_attr($settings['redirect_delay']); ?>' />
                                <?php _e(' seconds.', 'wp-cred'); ?>
                            </span>
                        </td>
                    </tr>
                </table>
                </fieldset>

                <fieldset class="cred-fieldset">                       
                    <div data-cred-bind="{ action: 'toggle', condition: '_cred[form][action]=message' }">
                        <table width="100%">
                            <tr>
                                <td width="40%" style="vertical-align: top;">
                                </td>
                                <td>
                                    <i><?php _e('Enter the message to display instead of the form. You can use HTML and shortcodes. (but no CRED Forms)', 'wp-cred'); ?></i>
                                    <?php echo CRED_Helper::getRichEditor('credformactionmessage', '_cred[form][action_message]', $settings['action_message'], array('wpautop' => true, 'teeny' => true, 'editor_height' => 100, 'editor_class' => 'wpcf-wysiwyg')); ?>
<!--<textarea id='cred_form_action_message' name='_cred[form][action_message]' style="position:relative; width:95%;"><?php //echo esc_textarea($settings['action_message']);                              ?></textarea>-->
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

                <?php
                do_action('cred_ext_cred_form_settings', $form, $settings);
                ?>

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

                                <label class='cred-label-chk'>
                                    <input type='checkbox' class='cred-checkbox-10' name='_cred[form][hide_comments]' id='cred_form_hide_comments' value='1' <?php if ($settings['hide_comments']) echo 'checked="checked"'; ?> />
                                    <span><?php _e('Hide comments when displaying this form', 'wp-cred'); ?></span>
                                </label>

                                <label class='cred-label-chk'>
                                    <input type='checkbox' class='cred-checkbox-10' name='_cred[form][has_media_button]' id='cred_content_has_media_button' value='1' <?php if ($settings['form']['has_media_button']) echo 'checked="checked"'; ?> /><span class='cred-checkbox-replace'></span>
                                    <span><?php _e('Allow Media Insert button in Post Content Rich Text Editor', 'wp-cred'); ?></span>
                                </label>

                            </td>
                        </tr>
                    </table>

                    <?php if (false) { ?>
                        <a class="cred-help-link" style="position:absolute;top:5px;right:10px;" href="<?php echo $help['post_type_settings']['link']; ?>" target="<?php echo $help_target; ?>" title="<?php echo esc_attr($help['post_type_settings']['text']); ?>" >
                            <i class="icon-question-sign"></i>
                            <span><?php echo $help['post_type_settings']['text']; ?></span>
                        </a>
                    <?php } ?>

                </fieldset>

                <?php if (false) { ?>
                    <a class='cred-help-link' href='<?php echo $help['general_form_settings']['link']; ?>' target='<?php echo $help_target; ?>' title="<?php echo esc_attr($help['general_form_settings']['text']); ?>" style="position:absolute;top:5px;right:10px">
                        <i class="icon-question-sign"></i>
                        <span><?php echo $help['general_form_settings']['text']; ?></span>
                    </a>
                <?php } ?>

<script>
                    var url = "<?php echo admin_url('admin-ajax.php'); ?>?action=cred_ajax_Posts&_do_=suggestPostsByTitle";
                    jQuery('#action_post').cred_suggest(url, {
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
