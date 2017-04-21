<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

$field_visible =
    ($style['showexactmatches'] == 1) ||
    ($style['showsearchintitle'] == 1) ||
    ($style['showsearchincontent'] == 1) ||
    ($style['showsearchincomments'] == 1) ||
    ($style['showsearchinexcerpt'] == 1);

// Search redirection, memorize options
if ( isset($style['_fo']) ) {
    $_checked = array(
        "set_exactonly" => isset($style['_fo']["set_exactonly"]) ? ' checked="checked"' : "",
        "set_intitle" => isset($style['_fo']["set_intitle"]) ? ' checked="checked"' : "",
        "set_incontent" => isset($style['_fo']["set_incontent"]) ? ' checked="checked"' : "",
        "set_incomments" => isset($style['_fo']["set_incomments"]) ? ' checked="checked"' : "",
        "set_inexcerpt" => isset($style['_fo']["set_inexcerpt"]) ? ' checked="checked"' : ""
    );
} else {
    $_checked = array(
        "set_exactonly" => $style['exactonly'] == 1 ? ' checked="checked"' : "",
        "set_intitle" => $style['searchintitle'] == 1 ? ' checked="checked"' : "",
        "set_incontent" => $style['searchincontent'] == 1 ? ' checked="checked"' : "",
        "set_incomments" => $style['searchincomments'] == 1 ? ' checked="checked"' : "",
        "set_inexcerpt" => $style['searchinexcerpt'] == 1 ? ' checked="checked"' : ""
    );
}


if ( function_exists('qtranxf_getLanguage') ) {
    $qtr_lg = qtranxf_getLanguage();
} else if ( function_exists('qtrans_getLanguage') ) {
    $qtr_lg = qtrans_getLanguage();
} else {
    $qtr_lg = 0;
}

do_action('asp_layout_settings_before_first_item', $id);
?>
<fieldset class="<?php echo ($field_visible) ? "" : " hiddend"; ?>">
    <div class="option hiddend">
        <input type='hidden' name='qtranslate_lang'
               value='<?php echo $qtr_lg; ?>'/>
    </div>

    <?php if ( function_exists("pll_current_language") ): ?>
    <div class="option hiddend">
        <input type='hidden' name='polylang_lang'
               value='<?php echo pll_current_language(); ?>'/>
    </div>
    <?php endif; ?>

	<?php if (defined('ICL_LANGUAGE_CODE')
	          && ICL_LANGUAGE_CODE != ''
	          && defined('ICL_SITEPRESS_VERSION')
	): ?>
	<div class="option hiddend">
		<input type='hidden' name='wpml_lang'
		       value='<?php echo ICL_LANGUAGE_CODE; ?>'/>
	</div>
	<?php endif; ?>

    <div class="asp_option<?php echo(($style['showexactmatches'] != 1) ? " hiddend" : ""); ?>">
        <div class="option">
            <input type="checkbox" value="checked" id="set_exactonly<?php echo $id; ?>"
                   name="set_exactonly" <?php echo $_checked["set_exactonly"]; ?>/>
            <label for="set_exactonly<?php echo $id; ?>"></label>
        </div>
        <div class="label">
            <?php echo asp_icl_t("Exact matches option" . " ($real_id)", $style['exactmatchestext']); ?>
        </div>
    </div>
    <div class="asp_option<?php echo(($style['showsearchintitle'] != 1) ? " hiddend" : ""); ?>">
        <div class="option">
            <input type="checkbox" value="None" id="set_intitle<?php echo $id; ?>"
                   name="set_intitle" <?php echo $_checked["set_intitle"]; ?>/>
            <label for="set_intitle<?php echo $id; ?>"></label>
        </div>
        <div class="label">
            <?php echo asp_icl_t("Search in title option" . " ($real_id)", $style['searchintitletext']); ?>
        </div>
    </div>
    <div class="asp_option<?php echo(($style['showsearchincontent'] != 1) ? " hiddend" : ""); ?>">
        <div class="option">
            <input type="checkbox" value="None" id="set_incontent<?php echo $id; ?>"
                   name="set_incontent" <?php echo $_checked["set_incontent"]; ?>/>
            <label for="set_incontent<?php echo $id; ?>"></label>
        </div>
        <div class="label">
            <?php echo asp_icl_t("Search in content option" . " ($real_id)", $style['searchincontenttext']); ?>
        </div>
    </div>
    <div class="asp_option<?php echo(($style['showsearchincomments'] != 1) ? " hiddend" : ""); ?>">
        <div class="option">
            <input type="checkbox" value="None" id="set_incomments<?php echo $id; ?>"
                   name="set_incomments" <?php echo $_checked["set_incomments"]; ?>/>
            <label for="set_incomments<?php echo $id; ?>"></label>
        </div>
        <div class="label">
            <?php echo asp_icl_t("Search in comments option" . " ($real_id)", $style['searchincommentstext']); ?>
        </div>
    </div>
    <div class="asp_option<?php echo(($style['showsearchinexcerpt'] != 1) ? " hiddend" : ""); ?>">
        <div class="option">
            <input type="checkbox" value="None" id="set_inexcerpt<?php echo $id; ?>"
                   name="set_inexcerpt" <?php echo $_checked["set_inexcerpt"]; ?>/>
            <label for="set_inexcerpt<?php echo $id; ?>"></label>
        </div>
        <div class="label">
            <?php echo asp_icl_t("Search in excerpt option" . " ($real_id)", $style['searchinexcerpttext']); ?>
        </div>
    </div>
</fieldset>