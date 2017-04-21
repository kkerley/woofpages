<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

/**
 * A better method to store the init data.
 *
 * The JSON data is stored inside this invisible element, the it is parsed
 * and passed as an argument to the initialization method.
 */
?>
<?php ob_start(); ?>
{
    "homeurl": "<?php echo function_exists("pll_home_url") ? @pll_home_url() : home_url("/"); ?>",
    "resultstype": "<?php echo ((isset($style['resultstype']) && $style['resultstype']!="")?$style['resultstype']:"vertical"); ?>",
    "resultsposition": "<?php echo $style['resultsposition']; ?>",
    "itemscount": <?php echo ((isset($style['itemscount']) && $style['itemscount']!="")?$style['itemscount']:"2"); ?>,
    "imagewidth": <?php echo ((isset($style['selected-imagesettings']['width']))?$style['selected-imagesettings']['width']:"70"); ?>,
    "imageheight": <?php echo ((isset($style['selected-imagesettings']['height']))?$style['selected-imagesettings']['height']:"70"); ?>,
    "resultitemheight": "<?php echo ((isset($style['resultitemheight']) && $style['resultitemheight']!="")?$style['resultitemheight']:"70"); ?>",
    "showauthor": <?php echo ((isset($style['showauthor']) && $style['showauthor']!="")?$style['showauthor']:"1"); ?>,
    "showdate": <?php echo ((isset($style['showdate']) && $style['showdate']!="")?$style['showdate']:"1"); ?>,
    "showdescription": <?php echo ((isset($style['showdescription']) && $style['showdescription']!="")?$style['showdescription']:"1"); ?>,
    "charcount":  <?php echo w_isset_def($style['charcount'], 3); ?>,
    "noresultstext": "<?php echo ((isset($style['noresultstext']) && $style['noresultstext']!="")?$style['noresultstext']:"3"); ?>",
    "didyoumeantext": "<?php echo asp_icl_t("Did you mean text" . " ($real_id)", w_isset_def($style['didyoumeantext'], 'Did you mean:')); ?>",
    "defaultImage": "<?php echo w_isset_def($style['image_default'], "")==""?ASP_URL."img/default.jpg":$style['image_default']; ?>",
    "highlight": <?php echo ((isset($style['highlight']) && $style['highlight']!="")?$style['highlight']:1); ?>,
    "highlightwholewords": <?php echo ((isset($style['highlightwholewords']) && $style['highlightwholewords']!="")?$style['highlightwholewords']:1); ?>,
    "openToBlank": <?php echo w_isset_def($style['results_click_blank'], 0); ?>,
    "scrollToResults": <?php echo w_isset_def($style['scroll_to_results'], 1); ?>,
    "resultareaclickable": <?php echo ((isset($style['resultareaclickable']) && $style['resultareaclickable']!="")?$style['resultareaclickable']:0); ?>,
    "autocomplete": {
        "enabled": <?php echo ((isset($style['autocomplete']) && $style['autocomplete']!="")?$style['autocomplete']:1); ?>,
        "googleOnly": <?php echo w_isset_def($style['autocomplete_source'], 'google') == 'google' ? 1 : 0; ?>,
        "lang": "<?php echo w_isset_def($style['autocomplete_google_lang'], 'en'); ?>",
        "mobile": <?php echo w_isset_def($style['autocomplete_mobile'], 'en'); ?>
    },
    "triggerontype": <?php echo $style['triggerontype'] == 1 ? 1 : 0; ?>,

    "triggeronreturn": <?php echo $style['return_action'] == 'ajax_search' ? 1 : 0; ?>,
    "triggerOnFacetChange": <?php echo w_isset_def($style['trigger_on_facet'], 0); ?>,
    "trigger": {
        "delay": <?php echo $style['trigger_delay']; ?>,
        "autocomplete_delay": <?php echo $style['autocomplete_trigger_delay']; ?>
    },
    "overridewpdefault": <?php echo w_isset_def($style['override_default_results'], 0); ?>,
    "override_method": "<?php echo $style['override_method']; ?>",
    "redirectonclick": <?php echo $style['click_action'] != 'ajax_search' ? 1 : 0; ?>,
    "redirectClickTo": "<?php echo $style['click_action']; ?>",
    "redirect_on_enter": <?php echo $style['return_action'] != 'ajax_search' ? 1 : 0; ?>,
    "redirectEnterTo": "<?php echo $style['return_action']; ?>",
    "redirect_url": "<?php echo apply_filters( "asp_redirect_url", w_isset_def($style['redirect_url'], '?s={phrase}') ); ?>",
    "settingsimagepos": "<?php echo ((isset($style['settingsimagepos']) && $style['settingsimagepos']!="")?$style['settingsimagepos']:0); ?>",
    "settingsVisible": <?php echo w_isset_def($style['frontend_search_settings_visible'], 0); ?>,
    "hresulthidedesc": "<?php echo ((isset($style['hhidedesc']) && $style['hhidedesc']!="")?$style['hhidedesc']:1); ?>",
    "prescontainerheight": "<?php echo ((isset($style['prescontainerheight']) && $style['prescontainerheight']!="")?$style['prescontainerheight']:"400px"); ?>",
    "pshowsubtitle": "<?php echo ((isset($style['pshowsubtitle']) && $style['pshowsubtitle']!="")?$style['pshowsubtitle']:0); ?>",
    "pshowdesc": "<?php echo ((isset($style['pshowdesc']) && $style['pshowdesc']!="")?$style['pshowdesc']:1); ?>",
    "closeOnDocClick": <?php echo w_isset_def($style['close_on_document_click'], 1); ?>,
    "iifNoImage": "<?php echo w_isset_def($style['i_ifnoimage'], 'description'); ?>",
    "iiRows": <?php echo w_isset_def($style['i_rows'], 2); ?>,
    "iiGutter": <?php echo w_isset_def($style['i_item_margin'], 10); ?>,
    "iitemsWidth": <?php echo w_isset_def($style['i_item_width'], 200); ?>,
    "iitemsHeight": <?php echo w_isset_def($style['i_item_height'], 200); ?>,
    "iishowOverlay": <?php echo w_isset_def($style['i_overlay'], 1); ?>,
    "iiblurOverlay": <?php echo w_isset_def($style['i_overlay_blur'], 1); ?>,
    "iihideContent": <?php echo w_isset_def($style['i_hide_content'], 1); ?>,
    "loaderLocation": "<?php echo $style['loader_display_location']; ?>",
    "analytics": <?php echo w_isset_def($ana_options['analytics'], 0); ?>,
    "analyticsString": "<?php echo w_isset_def($ana_options['analytics_string'], ""); ?>",
    "show_more": {
        "url": "<?php echo apply_filters( "asp_show_more_url", $style['more_redirect_url'] ); ?>",
        "action": "<?php echo $style['more_results_action']; ?>"
    },
    "mobile": {
        "trigger_on_type": <?php echo $style['mob_trigger_on_type']; ?>,
        "click_action": "<?php echo $style['mob_click_action'] == 'same' ? $style['click_action'] : $style['mob_click_action']; ?>",
        "return_action": "<?php echo $style['mob_return_action'] == 'same' ? $style['return_action'] : $style['mob_return_action']; ?>",
        "redirect_url": "<?php echo $style['mob_redirect_url']; ?>",
        "hide_keyboard": <?php echo $style['mob_hide_keyboard']; ?>,
        "force_res_hover": <?php echo $style['mob_force_res_hover']; ?>,
        "force_sett_hover": <?php echo $style['mob_force_sett_hover']; ?>,
        "force_sett_state": "<?php echo $style['mob_force_sett_state']; ?>"
    },
    "compact": {
        "enabled": <?php echo w_isset_def($style['box_compact_layout'], 0); ?>,
        "width": "<?php echo w_isset_def($style['box_compact_width'], "100%"); ?>",
        "closeOnMagnifier": <?php echo w_isset_def($style['box_compact_close_on_magn'], 1); ?>,
        "closeOnDocument": <?php echo w_isset_def($style['box_compact_close_on_document'], 0); ?>,
        "position": "<?php echo w_isset_def($style['box_compact_position'], 0); ?>",
        "overlay": <?php echo w_isset_def($style['box_compact_overlay'], 0); ?>
    },
    "animations": {
        "pc": {
            "settings": {
                "anim" : "<?php echo w_isset_def($style['sett_box_animation'], 'fadedrop'); ?>",
                "dur"  : <?php echo w_isset_def($style['sett_box_animation_duration'], 200); ?>
            },
            "results" : {
                "anim" : "<?php echo w_isset_def($style['res_box_animation'], 'fadedrop'); ?>",
                "dur"  : <?php echo w_isset_def($style['res_box_animation_duration'], 200); ?>
            },
            "items" : "<?php echo w_isset_def($style['res_items_animation'], 'fadeInUp'); ?>"
        },
        "mob": {
            "settings": {
                "anim" : "<?php echo w_isset_def($style['sett_box_animation_m'], 'fade'); ?>",
                "dur"  : <?php echo w_isset_def($style['sett_box_animation_duration_m'], 200); ?>
            },
            "results" : {
                "anim" : "<?php echo w_isset_def($style['res_box_animation_m'], 'fade'); ?>",
                "dur"  : <?php echo w_isset_def($style['res_box_animation_duration_m'], 200); ?>
            },
            "items" : "<?php echo w_isset_def($style['res_items_animation_m'], 'voidanim'); ?>"
        }
    },
    "detectVisibility" : <?php echo $style['visual_detect_visbility']; ?>,
    "autop": {
        "state": "<?php echo $style['auto_populate']; ?>",
        "phrase": "<?php echo $style['auto_populate_phrase']; ?>",
        "count": <?php echo $style['auto_populate_count']; ?>
    },
    "fss_layout": "<?php echo $style['fss_column_layout']; ?>"
}
<?php $_asp_script_out = ob_get_clean(); ?>
<?php if (wd_asp()->o['asp_compatibility']['js_init'] == "blocking"): ?>
<script type="text/javascript">
/* <![CDATA[ */
if ( typeof ASP_INSTANCES == "undefined" )
    var ASP_INSTANCES = {};
ASP_INSTANCES['<?php echo $id; ?>'] = <?php echo $_asp_script_out; ?>;
/* ]]> */
</script>
<?php else: ?>
<div class="asp_init_data" style="display:none !important;" id="asp_init_id_<?php echo $id; ?>" data-aspdata="<?php echo base64_encode($_asp_script_out); ?>"></div>
<?php endif; ?>