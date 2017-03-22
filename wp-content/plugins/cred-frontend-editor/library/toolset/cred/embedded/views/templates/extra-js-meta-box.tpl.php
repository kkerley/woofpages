<?php if (!defined('ABSPATH'))  die('Security check'); ?>
<?php if (false) : ?>
<p class='cred-explain-text'></p>
<a class='cred-help-link' style='position:absolute;top:5px;right:10px;z-index:1000' href='<?php echo $help['css_settings']['link']; ?>' target='<?php echo $help_target; ?>' title="<?php echo esc_attr($help['css_settings']['text']); ?>">
    <i class="icon-question-sign"></i>
    <span><?php echo $help['css_settings']['text']; ?></span>
</a>
<?php endif; ?>
<div id='cred_extra_settings_panel_container_js' class='cred_extra_settings_panel_container'>    
    <div class='cred_extra_js_settings_panel' style='position:relative;'>
    <div class="cred-editor-wrap">
    <textarea id='cred-extra-js-editor' name='_cred[extra][js]' style="position:relative;overflow-y:auto;" class="cred-extra-js-editor<?php if ($js && !empty($js)) echo ' cred-always-open'; ?>"><?php if ($js && !empty($js)) echo $js; ?></textarea>
    <div class="cred-content-resize-handle" title="<?php _e('Resize', 'wp-cred'); ?>"><br></div>
    </div>
    </div>
</div>