<?php
$settings_model = CRED_Loader::get('MODEL/Settings');
$settings = $settings_model->getSettings();
?>
<div class="js-cred-settings-wrapper">
	<p>
		<label class='cred-label'>
			<input type="checkbox" autocomplete="off" class='cred-checkbox-invalid js-cred-styling-setting' name="cred_use_bootstrap" value="1" <?php if (isset($settings['use_bootstrap']) && $settings['use_bootstrap']) echo "checked='checked'"; ?> />
			<span class='cred-checkbox-replace'></span>
			<span><?php _e('Use bootstrap in CRED Forms', 'wp-cred'); ?></span>
		</label>
	</p>
	<p>
		<label class='cred-label'>
			<input type="checkbox" autocomplete="off" class='cred-checkbox-invalid js-cred-styling-setting' name="cred_dont_load_cred_css" value="1" <?php if (isset($settings['dont_load_cred_css']) && $settings['dont_load_cred_css']) echo "checked='checked'"; ?> />
			<span class='cred-checkbox-replace'></span>
			<span><?php _e('Do not load CRED style sheets on front-end', 'wp-cred'); ?></span>
		</label>
	</p>
</div>
<?php wp_nonce_field( 'cred-styling-settings', 'cred-styling-settings' ); ?>