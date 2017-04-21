<div class="item">
	<?php
	$o = new wpdreamsYesNo("return_attachments", "Return attachments as results?",
		$sd['return_attachments']);
	$params[$o->getName()] = $o->getData();
	?>
</div>
<div class="item">
	<?php
	$o = new wpdreamsYesNo("search_attachments_title", "Search in attachment titles?",
		$sd['search_attachments_title']);
	$params[$o->getName()] = $o->getData();
	?>
</div>
<div class="item">
	<?php
	$o = new wpdreamsYesNo("search_attachments_content", "Search in attachment content?",
		$sd['search_attachments_content']);
	$params[$o->getName()] = $o->getData();
	?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo("search_attachments_caption", "Search in attachment captions?",
        $sd['search_attachments_caption']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
	<?php
	$o = new wpdreamsYesNo("search_attachments_ids", "Search in attachment IDs?",
			$sd['search_attachments_ids']);
	$params[$o->getName()] = $o->getData();
	?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo("search_attachments_terms", "Search in attachment terms (tags, etc..)?",
        $sd['search_attachments_terms']);
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg">Will search in terms (categories, tags) related to the attachments.</p>
    <p class="errorMsg">WARNING: <strong>Search in terms</strong> can be database heavy operation. Not recommended for big databases.</p>
</div>
<div class="item">
	<?php
	$o = new wpdreamsCustomSelect("attachment_link_to", "Link the results to",
			array(
					'selects' => array(
							array("option" => "attachment page", "value" => "page"),
							array("option" => "attachment file directly", "value" => "file")
					),
					'value' => $sd['attachment_link_to']
			));
	$params[$o->getName()] = $o->getData();
	?>
</div>
<div class="item">
	<?php
	$o = new wd_Textarea_B64("attachment_mime_types", "Allowed mime types",
		$sd['attachment_mime_types']);
	$params[$o->getName()] = $o->getData();
	?>
	<p class="descMsg"><strong>Comma separated list</strong> of allowed mime types. List of <a href="https://codex.wordpress.org/Function_Reference/get_allowed_mime_types"
	target="_blank">default allowed mime types</a> in WordPress.</p>
</div>
<div class="item">
	<?php
	$o = new wpdreamsYesNo("attachment_use_image", "Use the image of image mime types as the result image?",
		$sd['attachment_use_image']);
	$params[$o->getName()] = $o->getData();
	?>
</div>
<div class="item">
	<?php
	$o = new wpdreamsTextarea("attachment_exclude", "Exclude attachment IDs",
		$sd['attachment_exclude']);
	$params[$o->getName()] = $o->getData();
	?>
	<p class="descMsg"><strong>Comma separated list</strong> of attachment IDs to exclude.</p>
</div>