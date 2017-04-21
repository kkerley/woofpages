<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

$i = 1;
if (!isset($style['selected-customtypes']) || !is_array($style['selected-customtypes']))
    $style['selected-customtypes'] = array();
if (!isset($style['selected-showcustomtypes']) || !is_array($style['selected-showcustomtypes']))
    $style['selected-showcustomtypes'] = array();
$flat_show_customtypes = array();

if ( $style['searchinposts'] == 1)
    $style['selected-customtypes'][] = 'post';

if ( $style['searchinpages'] == 1)
    $style['selected-customtypes'][] = 'page';

ob_start();

if ($style['cpt_display_mode'] == "checkboxes") {
    foreach ($style['selected-showcustomtypes'] as $k => $v) {
        $selected = in_array($v[0], $style['selected-customtypes']);
        $hidden = "";
        $flat_show_customtypes[] = $v[0];
        ?>
        <div class="asp_option">
            <div class="option<?php echo $hidden; ?>">
                <input type="checkbox" value="<?php echo $v[0]; ?>" id="<?php echo $id; ?>customset_<?php echo $id . $i; ?>"
                       name="customset[]" <?php echo(($selected) ? 'checked="checked"' : ''); ?>/>
                <label for="<?php echo $id; ?>customset_<?php echo $id . $i; ?>"></label>
            </div>
            <div class="label<?php echo $hidden; ?>">
                <?php echo asp_icl_t($v[0] . " ($real_id)", $v[1]); ?>
            </div>
        </div>
        <?php
        $i++;
    }
} else if ($style['cpt_display_mode'] == "dropdown") {
    ?>
    <div class="asp_select_label asp_select_single">
        <select name="customset[]">
    <?php
    foreach ($style['selected-showcustomtypes'] as $k => $v) {
        $flat_show_customtypes[] = $v[0];
        ?>
            <option value="<?php echo $v[0]; ?>" <?php echo(($v[0] == $style['cpt_filter_default']) ? 'selected="selected"' : ''); ?>>
                <?php echo asp_icl_t($v[0] . " ($real_id)", $v[1]); ?>
            </option>
        <?php
        $i++;
    }
    ?>
        </select>
    </div>
    <?php
} else if($style['cpt_display_mode'] == "radio") {
    echo "<div class='tag_filter_box asp_sett_scroll'>";
    foreach ($style['selected-showcustomtypes'] as $k => $v) {
        $flat_show_customtypes[] = $v[0];
        ?>
        <label class="asp_label">
            <input name="customset[]" type="radio" class="asp_radio" value="<?php echo $v[0]; ?>" <?php echo(($v[0] == $style['cpt_filter_default']) ? 'checked="checked"' : ''); ?>>
            <?php echo asp_icl_t($v[0] . " ($real_id)", $v[1]); ?>
        </label>
        <?php
        $i++;
    }
    echo "</div>";
}


$hidden_types = array();
$hidden_types = array_diff($style['selected-customtypes'], $flat_show_customtypes);


foreach ($hidden_types as $k => $v) {

    ?>
    <div class="option hiddend">
        <input type="checkbox" value="<?php echo $v; ?>"
               id="<?php echo $id; ?>customset_<?php echo $id . $i; ?>"
               name="customset[]" checked="checked"/>
        <label for="<?php echo $id; ?>customset_<?php echo $id . $i; ?>"></label>
    </div>
    <div class="label hiddend"></div>
    <?php
    $i++;
}


$cpt_content = ob_get_clean();

$cpt_label = w_isset_def($style['custom_types_label'], 'Filter by Custom Post Type');
?>
<fieldset class="asp_sett_scroll<?php echo count($style['selected-showcustomtypes']) > 0 ? '' : ' hiddend'; ?>">
    <?php if ($cpt_label != ''): ?>
    <legend><?php echo asp_icl_t("Custom post types label" . " ($real_id)", $cpt_label);  ?></legend>
    <?php endif; ?>
    <?php echo $cpt_content; ?>
</fieldset>