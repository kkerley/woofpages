<?php
$woofpages_options  = get_option('woofpages_settings');
$facebook           = $woofpages_options['woofpages_social_facebook'];
$twitter            = $woofpages_options['woofpages_social_twitter'];
$youtube            = $woofpages_options['woofpages_social_youtube'];
$linkedin           = $woofpages_options['woofpages_social_linkedin'];
$googleplus         = $woofpages_options['woofpages_social_googleplus'];
$pinterest          = $woofpages_options['woofpages_social_pinterest'];
?>
<?php if($facebook || $twitter || $youtube || $linkedin || $googleplus || $pinterest): ?>
    <section class="wrapper--social-media-links">
        <h4>Connect with us!</h4>
        <div class="social-media-links--inner">
            <?php if($facebook): ?>
                <a href="<?php echo $facebook; ?>" class="button social-media facebook"><i class="fa fa-facebook"></i></a>
            <?php endif; ?>

            <?php if($twitter): ?>
                <a href="<?php echo $twitter; ?>" class="button social-media twitter"><i class="fa fa-twitter"></i></a>
            <?php endif; ?>

            <?php if($youtube): ?>
                <a href="<?php echo $youtube; ?>" class="button social-media youtube"><i class="fa fa-youtube"></i></a>
            <?php endif; ?>

            <?php if($linkedin): ?>
                <a href="<?php echo $linkedin; ?>" class="button social-media linkedin"><i class="fa fa-linkedin"></i></a>
            <?php endif; ?>

            <?php if($googleplus): ?>
                <a href="<?php echo $googleplus; ?>" class="button social-media google-plus"><i class="fa fa-google-plus"></i></a>
            <?php endif; ?>

            <?php if($pinterest): ?>
                <a href="<?php echo $pinterest; ?>" class="button social-media pinterest"><i class="fa fa-pinterest-p"></i></a>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>