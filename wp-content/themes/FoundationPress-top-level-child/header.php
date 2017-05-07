<?php
/**
 * The template for displaying the header
 *
 * Displays all of the head element and everything up until the "container" div.
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

?>
<!doctype html>
<html class="no-js" <?php language_attributes(); ?> >
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<?php wp_head(); ?>
	</head>
	<body <?php body_class('theme--top-level'); ?>>
	<?php do_action( 'foundationpress_after_body' ); ?>

	<?php #if ( get_theme_mod( 'wpt_mobile_menu_layout' ) === 'offcanvas' ) : ?>
	<div class="off-canvas-wrapper">

		<?php get_template_part( 'template-parts/mobile-off-canvas' ); ?>
	<?php # endif; ?>

	<?php do_action( 'foundationpress_layout_start' ); ?>

    <?php
        $woofpages_options      = get_option('woofpages_settings');
        $rescue_logo            = $woofpages_options['woofpages_rescue_custom_logo'];
        $rescue_abbrev          = $woofpages_options['woofpages_rescue_location_abbreviation'] ? $woofpages_options['woofpages_rescue_location_abbreviation'] : get_bloginfo( 'name' );
        $rescue_logo_id         = '';
        $rescue_logo_small      = '';

        if($rescue_logo):
	        $rescue_logo_id     = get_attachment_id($rescue_logo);
            $rescue_logo_small  = wp_get_attachment_image($rescue_logo_id, 'logo-in-nav');
	        $rescue_logo_sticky  = wp_get_attachment_image($rescue_logo_id, 'logo-in-nav-sticky');
        endif;
    ?>

    <header class="header<?php echo $rescue_logo ? ' with-logo' : '' ?>">
	    <div class="wrapper--primary-nav" data-sticky-container>
            <nav class="primary-nav" data-sticky data-top-anchor="main_container" data-options="stickyOn: small">
                <div class="primary-nav--inner">

                    <div class="logo-wrap">
                        <div class="logo-wrap--inner">
                            <div class="save">SAVE</div>
                            <div class="a-heart"><span>A</span></div>
                            <div class="rescue">RESCUE</div>
                        </div>
                        <div class="logo-wrap--inner">
                            <div class="dog">DOG</div>
                            <div class="icon">
                                <object type="image/svg+xml" data="/wp-content/themes/FoundationPress/assets/images/save_a_rescue_dog_logo.svg" ></object>
                            </div>
                        </div>
                    </div>

	                <?php
//	                if($rescue_logo): ?>
<!---->
<!--                        <p class="sticky-site-title"><a href="/">--><?php //echo $rescue_logo_small; ?><!-- <span class="logo--sticky">--><?php //echo $rescue_logo_sticky; ?><!--</span></a></p>-->
<!--		                --><?php
//	                endif;
	                ?>

	                <?php foundationpress_top_bar_r(); ?>

                    <div class="kk-off-canvas-trigger">
                        <button data-open="mobile-menu"><i class="fa fa-bars"></i></button>
                    </div>
                </div>
            </nav>
        </div>

	    <?php # if ( ! get_theme_mod( 'wpt_mobile_menu_layout' ) || get_theme_mod( 'wpt_mobile_menu_layout' ) === 'topbar' ) : ?>
		    <?php # get_template_part( 'template-parts/mobile-top-bar' ); ?>
	    <?php # endif; ?>
    </header>



	<section class="container" id="main_container">
		<?php do_action( 'foundationpress_after_header' ); ?>

<!--        <section class="wrapper--breadcrumbs">-->
<!--            <div class="breadcrumbs--inner">-->
<!--	            --><?php //bcn_display($return = false, $linked = true, $reverse = false); ?>
<!--            </div>-->
<!--        </section>-->