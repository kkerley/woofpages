jQuery(function($){

    // --- Safety check on max_input_vars
    if ( $('#asp_options_serialized').length > 0 ) {
        $('form[name="asp_data"]').submit(function(e){
            if ( typeof(Base64) != "undefined" ) {
                // Send the back-up form instead, with 1 variable only
                e.preventDefault();
                $('#asp_options_serialized').val( Base64.encode($('form[name="asp_data"]').serialize()) );
                $('form[name="asp_data_serialized"]').submit();
            }
       });
    }

    // --- SHORTCODES AND GENERATOR ---
    $('.asp_b_shortcodes_menu').click(function(){
        $(this).parent().toggleClass('asp_open');
    });

    function sc_generate() {
        var items = [];
        var ratios = [];
        var sid = $('#wpd_shortcode_modal').attr('sid');

        $('#wpd_shortcode_modal ul li').each(function(){
            if ( !$(this).hasClass('hiddend') ) {
                items.push($(this).attr('item'));
                ratios.push($('input',this).val());
            }
        });

        var elements = items.join(',');
        if ( elements != "" )
            elements = " elements='" + elements + "'";
        var ratio = ratios.join('%,');
        if ( ratio != "" )
            ratio = " ratio='" + ratio + "%'";

        $('#wpd_shortcode_modal textarea').val('[wd_asp' + elements + ratio + " id=" + sid + "]");
    }

    $('#shortcode_generator').on('click', function(e){
        e.preventDefault();
        e.stopImmediatePropagation();
        sc_generate();
        $('#wpd_shortcode_modal').removeClass('hiddend');
        $('#wpd_shortcode_modal_bg').css('display', 'block');
    });
    $('#wpd_shortcode_modal .wpd-modal-close, #wpd_shortcode_modal_bg').on('click', function(){
        $('#wpd_shortcode_modal').addClass('hiddend');
        $('#wpd_shortcode_modal_bg').css('display', 'none');
    });

    $('#wpd_shortcode_modal li a.deleteIcon').on('click', function(){
        $(this).parent().addClass('hiddend');
        $('#wpd_shortcode_modal button[item=' + $(this).parent().attr('item') + ']').removeAttr('disabled');
        sc_generate();
    });
    $('#wpd_shortcode_modal li input').on('change', function(){
        $(this).parent().parent().css('width', $(this).val() + "%");
        sc_generate();
    });
    $('#wpd_shortcode_modal .wpd_generated_shortcode button').on('click', function(){
        $(this).attr('disabled', 'disabled');
        $('#wpd_shortcode_modal li[item=' + $(this).attr('item') + ']').detach().appendTo($("#wpd_shortcode_modal .sortablecontainer .ui-sortable"));
        $('#wpd_shortcode_modal li[item=' + $(this).attr('item') + ']').removeClass('hiddend');
        sc_generate();
    });

    $("#wpd_shortcode_modal .sortablecontainer .ui-sortable").sortable({}, {
        update: function (event, ui) {}
    }).disableSelection();
    $("#wpd_shortcode_modal .sortablecontainer .ui-sortable").on('sortupdate', function(event, ui) {
        sc_generate();
    });

    $('#wpd_shortcode_modal .wpd_generated_shortcode select').on('change', function(){
        var items = ['search', 'settings', 'results'];
        var _val = $(this).val().split('|');
        var elements = _val[0].split(',');
        var ratios = _val[1].split(',');

        $('#wpd_shortcode_modal li a.deleteIcon').click();
        $.each(elements, function(i, v) {
            $('#wpd_shortcode_modal .wpd_generated_shortcode button[item='+ items[v] +']').click();
            $('#wpd_shortcode_modal li[item=' + items[v] + '] input').val(ratios[i]).change();
        });

        sc_generate();
    });
    // --------------------------------

    //var ajaxurl = '<?php bloginfo("url"); ?>' + "/wp-content/plugins/ajax-search-pro/ajax_search.php";
    $('.tabs a[tabid=6]').click(function () {
        $('.tabs a[tabid=601]').click();
    });
    $('.tabs a[tabid=1]').click(function () {
        $('.tabs a[tabid=101]').click();
    });
    $('.tabs a[tabid=4]').click(function () {
        $('.tabs a[tabid=401]').click();
    });
    $('.tabs a[tabid=3]').click(function () {
        $('.tabs a[tabid=301]').click();
    });
    $('.tabs a[tabid=5]').click(function () {
        $('.tabs a[tabid=501]').click();
    });
    $('.tabs a[tabid=7]').click(function () {
        $('.tabs a[tabid=701]').click();
    });
    $('.tabs a[tabid=8]').click(function () {
        $('.tabs a[tabid=801]').click();
    });

    $('.tabs a').on('click', function(){
        $('#sett_tabid').val($(this).attr('tabid'));
        location.hash = $(this).attr('tabid');
    });

    $('select[name="cpt_display_mode"]').change(function(){
        if ($(this).val() == "checkboxes")
            $('select[name="cpt_filter_default"]').attr('disabled', 'disabled');
        else
            $('select[name="cpt_filter_default"]').removeAttr('disabled');
    });
    $('select[name="cpt_display_mode"]').change();

    // ---------------------- General/Sources 1 ------------------------
    $('input[name="search_all_cf"]').change(function(){
        if ($(this).val() == 1)
            $('input[name="customfields"]').parent().addClass('disabled');
        else
            $('input[name="customfields"]').parent().removeClass('disabled');
    });
    $('input[name="search_all_cf"]').change();
    // -----------------------------------------------------------------

    // ---------------------- General/Behavior ------------------------
    $('select[name=click_action], select[name=return_action]').change(function(){
        var redirect = false;
        $('select[name=click_action], select[name=return_action]').each(function(i, v) {
            if ( $(v).val() == 'custom_url' ) {
                redirect = true;
                return false; //break
            }
        });
        if ( redirect ) {
            $('input[name=redirect_url]').parent().parent().removeClass('hiddend');
        } else {
            $('input[name=redirect_url]').parent().parent().addClass('hiddend');
        }
    });
    $('select[name=click_action]').change();

    $('select[name=mob_click_action], select[name=mob_return_action]').change(function(){
        var redirect = false;
        $('select[name=mob_click_action], select[name=mob_return_action]').each(function(i, v) {
            if ( $(v).val() == 'custom_url' ) {
                redirect = true;
                return false; //break
            }
        });
        if ( redirect ) {
            $('input[name=mob_redirect_url]').parent().parent().removeClass('hiddend');
        } else {
            $('input[name=mob_redirect_url]').parent().parent().addClass('hiddend');
        }
    });
    $('select[name=mob_click_action]').change();

    $('input[name="exactonly"]').change(function(){
        if ($(this).val() == 0 || $('select[name="secondary_kw_logic"]').val() == 'none') {
            $('input[name="exact_m_secondary"]').val(0);
            $('input[name="exact_m_secondary"]').closest('div').find('.triggerer').trigger('click');
            $('input[name="exact_m_secondary"]').parent().addClass('disabled');
        } else {
            $('input[name="exact_m_secondary"]').parent().removeClass('disabled');
        }
    });
    $('select[name="secondary_kw_logic"]').change(function(){
        if ($(this).val() == 'none' || $('input[name="exactonly"]').val() == 0) {
            $('input[name="exact_m_secondary"]').val(0);
            $('input[name="exact_m_secondary"]').closest('div').find('.triggerer').trigger('click');
            $('input[name="exact_m_secondary"]').parent().addClass('disabled');
        } else {
            $('input[name="exact_m_secondary"]').parent().removeClass('disabled');
        }
    });
    $('input[name="exactonly"]').change();
    $('select[name="secondary_kw_logic"]').change();

    $('select[name="orderby_primary"]').change(function(){
        if ($(this).val().indexOf('customf') == -1) {
            $('input[name="orderby_primary_cf"]').parent().addClass('hiddend');
            $('select[name="orderby_primary_cf_type"]').parent().addClass('hiddend');
        } else {
            $('input[name="orderby_primary_cf"]').parent().removeClass('hiddend');
            $('select[name="orderby_primary_cf_type"]').parent().removeClass('hiddend');
        }
    });
    $('select[name="orderby_primary"]').change();
    $('select[name="orderby"]').change(function(){
        if ($(this).val().indexOf('customf') == -1) {
            $('input[name="orderby_secondary_cf"]').parent().addClass('hiddend');
            $('select[name="orderby_secondary_cf_type"]').parent().addClass('hiddend');
        } else {
            $('input[name="orderby_secondary_cf"]').parent().removeClass('hiddend');
            $('select[name="orderby_secondary_cf_type"]').parent().removeClass('hiddend');
        }
    });
    $('select[name="orderby"]').change();

    $('input[name="override_default_results"]').change(function(){
        if ($(this).val() == 0)
            $('input[name="results_per_page"]').parent().addClass('disabled');
        else
            $('input[name="results_per_page"]').parent().removeClass('disabled');
    });
    $('input[name="override_default_results"]').change();


    // -----------------------------------------------------------------

    // ------------------------- Tags stuff ----------------------------
    $('input[name="display_all_tags_option"]').change(function(){
        if ( $(this).val() == 1 )
            $('input[name="all_tags_opt_text"]').removeAttr("disabled");
        else
            $('input[name="all_tags_opt_text"]').attr('disabled', 'disabled');
    });
    $('input[name="display_all_tags_option"]').change();

    $('input[name="display_all_tags_check_opt"]').change(function(){
        if ( $(this).val() == 1 )
            $('input[name="all_tags_check_opt_text"], select[name="all_tags_check_opt_state"]').removeAttr("disabled");
        else
            $('input[name="all_tags_check_opt_text"], select[name="all_tags_check_opt_state"]').attr('disabled', 'disabled');
    });
    $('input[name="display_all_tags_check_opt"]').change();

    $("select.wd_tagDisplayMode", $('input[name="show_frontend_tags"]').parent()).change(function(){
        if ( $(this).val() == 'checkboxes' ) {
            $(".item.wd_tag_mode_checkbox, .item.wd_tag_mode_dropdown, .item.wd_tag_mode_radio").addClass('hiddend');
            $(".item.wd_tag_mode_checkbox").removeClass('hiddend');
        } else {
            $(".item.wd_tag_mode_checkbox, .item.wd_tag_mode_dropdown, .item.wd_tag_mode_radio").addClass('hiddend');
            $(".item.wd_tag_mode_dropdown").removeClass('hiddend');
        }
    });
    $("select.wd_tagDisplayMode", $('input[name="show_frontend_tags"]').parent()).change();

    $('select.wd_tagDisplayMode').change(function(){
        if ( $(this).val() !='checkboxes' )
            $('select[name=frontend_tags_logic]').parent().parent().addClass('disabled');
        else
            $('select[name=frontend_tags_logic]').parent().parent().removeClass('disabled');
    });
    $('select.wd_tagDisplayMode').change();
    // -----------------------------------------------------------------

    $("select[name='frontend_search_settings_position']").change(function(){
        if ( $(this).val() == 'hover' ) {
            $("select[name='fss_hover_columns']").parent().removeClass("hiddend");
            $("select[name='fss_block_columns']").parent().addClass("hiddend");
        } else {
            $("select[name='fss_hover_columns']").parent().addClass("hiddend");
            $("select[name='fss_block_columns']").parent().removeClass("hiddend");
        }
    });
    $("select[name='frontend_search_settings_position']").change();

    $('input[name="exclude_dates_on"] + .wpdreamsYesNoInner').click(function(){
        if ($(this).prev().val() == 0)
            $('input[name="exclude_dates"]').parent().addClass('disabled');
        else
            $('input[name="exclude_dates"]').parent().removeClass('disabled');
    });
    if ( $('input[name="exclude_dates_on"]').val() == 0 )
        $('input[name="exclude_dates"]').parent().addClass('disabled');
    else
        $('input[name="exclude_dates"]').parent().removeClass('disabled');

    $("select[name='auto_populate']").change(function(){
        if ( $(this).val() == 'phrase' )
            $("input[name='auto_populate_phrase']").parent().css("visibility", "");
        else
            $("input[name='auto_populate_phrase']").parent().css("visibility", "hidden");
    });
    $("select[name='auto_populate']").change();

    $('input[name="use_post_type_order"]').change(function(){
        if ($(this).val() == 0)
            $('input[name="post_type_order"]').parent().parent().addClass('disabled');
        else
            $('input[name="post_type_order"]').parent().parent().removeClass('disabled');
    });
    $('input[name="use_post_type_order"]').change();

    // -------------------------- ADVANCED OPTIONS PANEL --------------------------------
    $("select[name='group_by']").change(function(){
        if ( $(this).val() == 'none' ) {
            $("#wpdreams .item.wd_groupby_op").addClass('hiddend');
            $("#wpdreams .item.wd_groupby").addClass('hiddend');
        } else {
            $("#wpdreams .item.wd_groupby_op").removeClass('hiddend');
            $("#wpdreams .item.wd_groupby").addClass('hiddend');
            $("#wpdreams .item.wd_groupby_" + $(this).val()).removeClass('hiddend');
        }
    });
    $("select[name='group_by']").change();

    $("select[name='group_result_no_group']").change(function(){
        if ( $(this).val() == 'remove' ) {
            $("input[name='group_other_results_head']").parent().parent().css("display", "none");
        } else {
            $("input[name='group_other_results_head']").parent().parent().css("display", "");
        }
    });
    $("select[name='group_result_no_group']").change();

    // Primary and Secondary fields for custom fields
    $.each(['primary_titlefield', 'secondary_titlefield', 'primary_descriptionfield', 'secondary_descriptionfield'],
    function(i, v){
        $("select[name='"+v+"']").change(function(){
            if ( $(this).val() != 'c__f' ) {
                $("input[name='"+v+"_cf']").parent().css("display", "none");
            } else {
                $("input[name='"+v+"_cf']").parent().css("display", "");
            }
        });
        $("select[name='"+v+"']").change();
    });
    // -------------------------- ADVANCED OPTIONS PANEL --------------------------------

    // Remove the # from the hash, as different browsers may or may not include it
    var hash = location.hash.replace('#','');

    if(hash != ''){
        hash = parseInt(hash);
        $('.tabs a[tabid=' + Math.floor( hash / 100 ) + ']').click();
        $('.tabs a[tabid=' + hash + ']').click();
    } else {
        $('.tabs a[tabid=1]').click();
    }

    $('#wpdreams .settings').click(function () {
        $("#preview input[name=refresh]").attr('searchid', $(this).attr('searchid'));
    });
    $("select[id^=wpdreamsThemeChooser]").change(function () {
        $("#preview input[name=refresh]").click();
    });
    $("#preview .refresh").click(function (e) {
        e.preventDefault();
        var $this = $(this).parent();
        var id = $('#wpdreams').data('searchid');
        var loading = $('.big-loading', $this);

        // Remove duplicates first
        $('body>div[id^=ajaxsearchpro]').remove();

        $('.data', $this).html("");
        $('.data', $this).addClass('hidden');
        loading.removeClass('hidden');
        var data = {
            action: 'ajaxsearchpro_preview',
            asid: id,
            formdata: $('form[name="asp_data"]').serialize()
        };

        if ( typeof(Base64) != "undefined" ) {
            $("#asp_preview_options").html( Base64.encode($('form[name="asp_data"]').serialize()) );
        }

        $.post(ajaxurl, data, function (response) {
            loading.addClass('hidden');
            $('.data', $this).html(response);
            $('.data', $this).removeClass('hidden');
            ASP.initialize();
            setTimeout(
                function () {
                    if (typeof aspjQuery != 'undefined')
                        aspjQuery(window).resize();
                    else if (typeof jQuery != 'undefined')
                        jQuery(window).resize();
                },
                1000);
        });
    });

    $("#preview .maximise").click(function (e) {
        e.preventDefault();
        $this = $(this.parentNode);
        if ($(this).html() == "Show") {
            $this.animate({
                bottom: "-2px",
                height: "90%"
            });
            $(this).html('Hide');
            $("#preview a.refresh").trigger('click');
        } else {
            $this.animate({
                bottom: "-2px",
                height: "40px"
            });
            $(this).html('Show');
        }
    });

    // Show-Hide the API input fields
    $("ul.connectedSortable", $("input[name='autocomplete_source']").parent()).on("sortupdate", function(){
        if ( $("input[name='autocomplete_source']").val().indexOf("google_places") > -1 ) {
            $("input[name='autoc_google_places_api']").parent().parent().removeClass("hiddend");
        } else {
            $("input[name='autoc_google_places_api']").parent().parent().addClass("hiddend");
        }
    });
    $("ul.connectedSortable", $("input[name='autocomplete_source']").parent()).trigger("sortupdate");

    $("ul.connectedSortable", $("input[name='keyword_suggestion_source']").parent()).on("sortupdate", function(){
        if ( $("input[name='keyword_suggestion_source']").val().indexOf("google_places") > -1 ) {
            $("input[name='kws_google_places_api']").parent().parent().removeClass("hiddend");
        } else {
            $("input[name='kws_google_places_api']").parent().parent().addClass("hiddend");
        }
    });
    $("ul.connectedSortable", $("input[name='keyword_suggestion_source']").parent()).trigger("sortupdate");

    if (typeof ($.fn.spectrum) != 'undefined')
        $("#bgcolorpicker").spectrum({
            showInput: true,
            showPalette: true,
            showSelectionPalette: true,
            change: function (color) {
                $("#preview").css("background", color.toHexString()); // #ff0000
            }
        });

    // Social stuff
    var url = encodeURIComponent('http://bit.ly/buy_asp');
    var fb_share_url = "https://www.facebook.com/share.php?u=";
    var tw_share_url = "https://twitter.com/intent/tweet";

    function winOpen(url) {
        var width = 575, height = 400,
            left = (document.documentElement.clientWidth / 2 - width / 2),
            top = (document.documentElement.clientHeight - height) / 2,
            opts = 'status=1,resizable=yes' +
                ',width=' + width + ',height=' + height +
                ',top=' + top + ',left=' + left,
            win = window.open(url, '', opts);
        win.focus();
        return win;
    }

    $("#asp_tw_share").on("click", function(e){
        var $this = $(this);
        e.preventDefault();
        winOpen(tw_share_url + "?text=" + encodeURIComponent($this.data("text")) + "&url=" + url + "&via=ernest_marcinko");
    });
    $("#asp_fb_share").on("click", function(e){
        e.preventDefault();
        winOpen(fb_share_url + url);
    });
});