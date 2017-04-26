<div class="wrap">
    <div class="wp-defender">
        <div class="auditing">
            <h2 class="title">
				<?php _e( "AUDIT LOGGING", wp_defender()->domain ) ?>
            </h2>
            <div class="dev-box">
                <div class="box-title">
                    <h3><?php _e( "Upgrade", wp_defender()->domain ) ?></h3>
                </div>
                <div class="box-content tc">
                    <img class="mline" src="<?php echo wp_defender()->getPluginUrl() ?>assets/img/audit-free.svg"/>
                    <div class="line max-600">
				        <?php _e( "Track and log each and every events when changes are made to your website and get details reports on everything from what your users are doing to hacking attempts. This is a pro feature that requires an active WPMU DEV membership. Try it free today!", wp_defender()->domain ) ?>
                    </div>
                    <a target="_blank" href="https://premium.wpmudev.org/project/wp-defender/"
                       class="button button-green"><?php esc_html_e( "Upgrade to Pro", wp_defender()->domain ) ?></a>
                </div>
            </div>
        </div>
    </div>
</div>