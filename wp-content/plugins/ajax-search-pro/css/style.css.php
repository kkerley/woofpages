<?php
/**
 * This is the dynamic stylesheet file of the plugin. Don't edit unless necessary.
 *
 * If you are wondering, why so many weird rules? Well, some themes appear to use
 * IDs in selectors instead of sticking only with classes,
 * thus making it inevitable to use stronger CSS specificities.
 */

// Since version 3.0 it is possible to use multiple instances, we need at least 2 IDs to be safe

defined('ABSPATH') or die("You can't access this file directly.");

$asp_div_ids = 'div.ajaxsearchpro[id*="ajaxsearchpro'.$id.'_"]';
$asp_res_ids = 'div.ajaxsearchpro[id*="ajaxsearchprores'.$id.'_"]';
$asp_set_ids = 'div.ajaxsearchpro[id*="ajaxsearchprosettings'.$id.'_"]';
$asp_bset_ids = 'div.ajaxsearchpro[id*="ajaxsearchprobsettings'.$id.'_"]';

$asp_div_ids1 = '#ajaxsearchpro'.$id.'_1';
$asp_div_ids2 = '#ajaxsearchpro'.$id.'_2';

$asp_res_ids1 = '#ajaxsearchprores'.$id.'_1';
$asp_res_ids2 = '#ajaxsearchprores'.$id.'_2';

$asp_set_ids1 = '#ajaxsearchprosettings'.$id.'_1';
$asp_set_ids2 = '#ajaxsearchprosettings'.$id.'_2';

$asp_bset_ids1 = '#ajaxsearchprobsettings'.$id.'_1';
$asp_bset_ids2 = '#ajaxsearchprobsettings'.$id.'_2';

$use_compatibility = true;
$use_strong_compatibility = true;
$base_css = "";

$asp_comp = get_option('asp_compatibility');
$comp_level = w_isset_def($asp_comp["css_compatibility_level"], "medium");

if ($comp_level == "medium") {
    $use_compatibility = true;
    $use_strong_compatibility = false;
} else if ($comp_level == "maximum") {
    $use_compatibility = true;
    $use_strong_compatibility = true;
} else {
    $use_compatibility = false;
    $use_strong_compatibility = false;
}

if (!class_exists('aspCSSCompatibilityReplace')) {
    class aspCSSCompatibilityReplace {
        private $id;
        private $end;

        function __construct($id, $end = false) {
            $this->id = $id;
            $this->end = $end;
        }

        function callback($matches) {
            $id = $this->id;
            if (!isset($matches[1])) return "";
            if (strpos($matches[1], "*=") < 0) return $matches[1];
            $instance1 = str_replace(
                array(
                    "*='ajaxsearchpro']",
                    "*='ajaxsearchprosettings']",
                    "*='ajaxsearchprobsettings']",
                    "*='ajaxsearchprores']"
                ),
                array(
                    '#ajaxsearchpro'.$id.'_1',
                    '#ajaxsearchprosettings'.$id.'_1',
                    '#ajaxsearchprobsettings'.$id.'_1',
                    '#ajaxsearchprores'.$id.'_1'
                ),
                $matches[1]);
            $instance2 = str_replace(
                array(
                    "*='ajaxsearchpro']",
                    "*='ajaxsearchprosettings']",
                    "*='ajaxsearchprobsettings']",
                    "*='ajaxsearchprores']"
                ),
                array(
                    '#ajaxsearchpro'.$id.'_2',
                    '#ajaxsearchprosettings'.$id.'_2',
                    '#ajaxsearchprobsettings'.$id.'_2',
                    '#ajaxsearchprores'.$id.'_2'
                ),
                $matches[1]);
            if ($this->end)
                return $instance1 . ", " . $instance2 . " {";
            else
                return $instance1 . " " . $instance2;
        }
    }
}

// Load the corresponding CSS3 loader
$asp_loader_css_full = file_get_contents(ASP_CSS_PATH . "style.loaders.css");
$asp_loader_css = wd_get_inner_substring( $asp_loader_css_full, "/*[general]*/");
$asp_loader_css .= " " . wd_get_inner_substring( $asp_loader_css_full, "/*[" . w_isset_def($style['loader_image'], "simple-circle") . "]*/");
$asp_loader_css = str_replace("#fff", $style['loadingimage_color'], $asp_loader_css);

// Print the loader CSS, without the changes (so same instances above 2 will work)
echo str_replace("id*='ajaxsearchpro'", "id*='ajaxsearchpro".$id."_'", $asp_loader_css);
echo str_replace("id*='ajaxsearchpro']", "id*='ajaxsearchprores".$id."_'] .asp_res_loader", $asp_loader_css);

// Load the required CSS3 animation
$asp_anim_css_full = file_get_contents(ASP_CSS_PATH . "animations.css");
$asp_anim_css =  wd_get_inner_substring( $asp_anim_css_full, "/*[" . w_isset_def($style['res_items_animation'], "fadeInDown") . "]*/");
$asp_anim_css_mob =  wd_get_inner_substring( $asp_anim_css_full, "/*[" . w_isset_def($style['res_items_animation_m'], "voidanim") . "]*/");

if ( $asp_anim_css != $asp_anim_css_mob )
    $asp_anim_css .= " " . $asp_anim_css_mob;

// Do some stuff with the base CSS if the compatibility level is Maximum
if ($use_strong_compatibility == true) {
    $base_css = file_get_contents(ASP_PATH . "/css/style.basic.css");
    $css_helper = new aspCSSCompatibilityReplace($id);
    $base_css = preg_replace_callback("/^div\.ajaxsearchpro\[id(.*?[ ]*,)/im", array($css_helper, "callback"), $base_css);
    $css_helper = new aspCSSCompatibilityReplace($id, true);
    $base_css = preg_replace_callback("/^div\.ajaxsearchpro\[id(.*?)[ ]*\{/im", array($css_helper, "callback"), $base_css);
}

$css_helper = new aspCSSCompatibilityReplace($id);
$asp_loader_css = preg_replace_callback("/^div\[id(.*?[ ]*,)/im", array($css_helper, "callback"), $asp_loader_css);
$css_helper = new aspCSSCompatibilityReplace($id, true);
$asp_loader_css = preg_replace_callback("/^div\[id(.*?)[ ]*\{/im", array($css_helper, "callback"), $asp_loader_css);

echo $base_css;
echo $asp_loader_css;
echo $asp_anim_css;
include(ASP_PATH . "/css/style.shared.css.php");
include(ASP_PATH . "/css/style.".$style['resultstype'].".css.php");
?>
.wpdreams_asp_sc-<?php echo $id; ?>, .asp-try-<?php echo $id; ?> {display: block; max-height: none; overflow: visible;}
<?php
echo "/* Generated at: ".date("Y-m-d H:i:s", time())." */";
