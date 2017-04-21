<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");
?>
<form name='options' autocomplete = 'off'>
    <input type="hidden" style="display:none;" name="current_page_id" value="<?php echo get_the_ID() !== false ? get_the_ID() : '-1'; ?>">
    <?php
    $fields = w_isset_def($style['field_order'], 'general|custom_post_types|custom_fields|categories_terms');
    if (strpos($fields, "general") === false) $fields = "general|" . $fields;
    if (strpos($fields, "post_tags") === false) $fields .= "|post_tags";
    if (strpos($fields, "date_filters") === false) $fields .= "|date_filters";
    $field_order = explode( '|', $fields );
    foreach ($field_order as $field)
        include("asp.shortcode.$field.php");
    ?>
    <div style="clear:both;"></div>
</form>