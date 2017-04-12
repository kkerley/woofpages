<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the "off-canvas-wrap" div and all content after.
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

?>

		</section>
		<div id="footer-container">
			<footer id="footer">
				<?php do_action( 'foundationpress_before_footer' ); ?>
                <article class="large-4 small-12 columns">
	                <?php
	                $woofpages_options  = get_option('woofpages_settings');
	                $street_address     = $woofpages_options['woofpages_rescue_location_street_address'];
	                $city               = $woofpages_options['woofpages_rescue_location_city'];
	                $state              = $woofpages_options['woofpages_rescue_location_state'];
	                $zip                = $woofpages_options['woofpages_rescue_location_zip_code'];
	                $primary_phone      = $woofpages_options['woofpages_rescue_location_primary_phone'];
	                ?>

	                <?php if($street_address || $city || $state || $zip || $primary_phone): ?>
                        <section class="rescue-contact-info">
			                <?php if($street_address || $city || $state || $zip): ?>
                                <h4><?php bloginfo( 'name' ); ?></h4>

                                <p class="rescue-contact-info--street-address">
                                    <i class="fa fa-map-marker"></i> <?php echo $street_address; ?><br />
					                <?php echo $city; ?>, <?php echo $state; ?> <?php echo $zip; ?>
                                </p>
			                <?php endif; ?>

			                <?php if($primary_phone): ?>
                                <p class="rescue-contact-info--phone"><i class="fa fa-phone"></i> <?php echo $primary_phone; ?></p>
			                <?php endif; ?>
                        </section>
	                <?php endif; ?>
                </article>

                <article class="large-4 small-12 columns">
	                <?php include 'template-parts/woofpages/_social_media_links.php'; ?>
                </article>
				<?php dynamic_sidebar( 'footer-widgets' ); ?>
				<?php do_action( 'foundationpress_after_footer' ); ?>
			</footer>

            <footer id="copyright">
                <div class="copyright--inner">
                    <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>, part of the <a href="http://www.woofpages.net">Woof Pages Network</a>.</p>
                </div>
            </footer>
		</div>

		<?php do_action( 'foundationpress_layout_end' ); ?>

<?php if ( get_theme_mod( 'wpt_mobile_menu_layout' ) === 'offcanvas' ) : ?>

	</div><!-- Close off-canvas wrapper -->
</div><!-- Close off-canvas content wrapper -->
<?php endif; ?>


<?php wp_footer(); ?>
<?php do_action( 'foundationpress_before_closing_body' ); ?>

<script id="__bs_script__">//<![CDATA[
document.write("<script async src='http://HOST:3000/browser-sync/browser-sync-client.js?v=2.18.8'><\/script>".replace("HOST", location.hostname));
//]]></script>
</body>
</html>
