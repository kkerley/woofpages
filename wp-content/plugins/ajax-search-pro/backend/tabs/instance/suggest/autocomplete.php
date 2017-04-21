<div class="item"><?php
    $o = new wpdreamsYesNo("autocomplete", "Turn on search autocomplete on desktop devices?", $sd['autocomplete']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item"><?php
    $o = new wpdreamsYesNo("autocomplete_mobile", "Turn on search autocomplete on mobile devices?", $sd['autocomplete_mobile']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsDraggable("autocomplete_source", "Autocomplete suggestion sources", array(
        'selects'=>$sugg_select_arr,
        'value'=>$sd['autocomplete_source'],
        'description'=>'Select which sources you prefer for autocomplete. Order counts.'
    ));
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item hiddend"><?php
    $o = new wpdreamsText("autoc_google_places_api", "Google places API key", $sd['autoc_google_places_api']);
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="errorMsg">This is required for the Google Places API to work. You can <a href="https://developers.google.com/places/web-service/autocomplete" target="_blank">get your API key here</a>.</p>
</div>
<div class="item"><?php
    $o = new wpdreamsTextSmall("autocomplete_length", "Max. suggestion length",
        $sd['autocomplete_length']);
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg">The length of each suggestion in characters. 30-60 is a good number to avoid too long suggestions.</p>
</div>
<div class="item"><?php
    $o = new wpdreamsLanguageSelect("autocomplete_google_lang", "Google autocomplete suggestions language",
        $sd['autocomplete_google_lang']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsTextarea("autocompleteexceptions", "Keyword exceptions (comma separated)", $sd['autocompleteexceptions']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>