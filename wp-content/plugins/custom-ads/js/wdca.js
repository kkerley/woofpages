(function ($) {

function wdca_insert_ad ($root, $placement, callback) {
	var $add = $root.find('.' + _wdca.pfx + 'ad_item').first();
	$add.removeClass(_wdca.pfx + 'not_placed');
	$placement[callback]($add);
}

$(function () {

var pfx = _wdca.pfx,
	$ads_root = $("#" + pfx + "ads_root")
;

// Dynamic style load
if (_wdca.dynamic_styles) {
	$.post(_wdca.ajax_url, {
		"action": pfx + "get_styles",
	}, function (response) {
		if (response.style) $("head").append("<style>" + response.style + "</style>");
	});
}

if (!$ads_root.length) return false;

var $parent = $ads_root.parent(),
	$ps = $parent.find(_wdca.selector),
	ignore_other = !!_wdca.predefined.ignore_other,
	allow_predefined = (
		!!(ignore_other && !!($ps.length > _wdca.predefined.ignore_requirement))
		||
		(_wdca.predefined.before || _wdca.predefined.middle || _wdca.predefined.after)
	),
	allow_default = !ignore_other
;

// Start the fiddling
if (_wdca.non_indexing_wrapper) {
	var root_markup = $ads_root.html();
	$ads_root.remove();
	$parent.append('<div id="' + pfx + 'ads_root-regenerated" style="display:none" />');
	$ads_root = $("#" + pfx + "ads_root-regenerated");
	$ads_root.html(root_markup);
}
// End fiddling

if (allow_predefined) {
	if (_wdca.predefined.before) {
		wdca_insert_ad($ads_root, $parent.find(":first-child:first"), 'before');
	}

	if (_wdca.predefined.middle && $ps.length) {
		var idx = Math.floor($ps.length / 2),
			$el = $($ps.get(idx))
		;
		if ($el.length) wdca_insert_ad($ads_root, $el, 'after');
	}

	if (_wdca.predefined.after) {
		wdca_insert_ad($ads_root, $parent, 'append');
	}
}

if (!$ps.length) return false;
if (allow_default) {
	var count = 1,
		limit = _wdca.first_ad
	;
	$ps.each(function() {
		if (count == limit) {
			wdca_insert_ad($ads_root, $(this), 'after');
			count = 0;
			limit = _wdca.count; // We're done with first
		}
		count++;
	});
}

// Initialize GA
if (_wdca.ga.enabled && _wdca.ga.category && _wdca.ga.label) {
	if ("undefined" == typeof _gaq) _gaq = []; // _gaq Global setup
	$("." + pfx + "ad_item a").click(function () {
		_gaq.push(['_trackEvent', _wdca.ga.category, 'Click', _wdca.ga.label]);
		return true; // Propagate further up
	});
}



});
})(jQuery);