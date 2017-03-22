<?php
$settings_model = CRED_Loader::get('MODEL/Settings');
$settings = $settings_model->getSettings();
?>
<div class="js-cred-settings-wrapper">
	<p>
		<label class='cred-label'>
			<input type="checkbox" autocomplete="off" class='cred-checkbox-invalid js-cred-other-setting' name="cred_syntax_highlight" value="1" <?php if (isset($settings['syntax_highlight']) && $settings['syntax_highlight']) echo "checked='checked'"; ?> />
			<span class='cred-checkbox-replace'></span>
			<span><?php _e('Enable Syntax Highlight for CRED Forms', 'wp-cred'); ?></span>
		</label>
	</p>
	<?php
	// CRED_PostExpiration
	do_action( 'cred_pe_general_settings', $settings );
	?>
</div>
<?php
wp_nonce_field( 'cred-other-settings', 'cred-other-settings' );
?>