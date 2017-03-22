<div class="submitbox" id="submitpost">
    <div id="minor-publishing">
        <div id="minor-publishing-actions">
            <div id="preview-action">
                <a class="button cred-preview-button" href="javascript:;" title="Preview"><?php _e("Preview Changes", 'wp-cred'); ?></a>
                <input type="hidden" name="wp-preview" id="wp-preview" value="">
            </div>
            <div class="clear"></div>
        </div>
    </div>
    <div id="major-publishing-actions">
        <div id="delete-action">
            <?php echo $delete_link; ?>
        </div>
        
        <div id="publishing-action">
        <span class="spinner"></span>
        		<input name="save" type="submit" class="button button-primary button-large" value="<?php (get_current_screen()->id == "cred-form" ? _e("Save Post Form", 'wp-cred') : _e("Save User Form", 'wp-cred')); ?>">
        </div>
        <div class="clear"></div>
    </div>
</div>