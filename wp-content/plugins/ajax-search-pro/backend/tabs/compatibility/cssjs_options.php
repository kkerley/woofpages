<div class="item">
    <?php
    $o = new wpdreamsCustomSelect("js_source", "Javascript source", array(
            'selects'   => wd_asp()->o['asp_compatibility_def']['js_source_def'],
            'value'     => $com_options['js_source']
        )
    );
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg">
    <ul style="float:right;text-align:left;width:50%;">
        <li><b>Non minified</b> - Low Compatibility, Medium space</li>
        <li><b>Minified</b> - Low Compatibility, Low space</li>
        <li><b>Non minified Scoped</b> - High Compatibility, High space</li>
        <li><b>Minified Scoped</b> - High Compatibility, Medium space</li>
    </ul>
    <div class="clear"></div>
    </p>
</div>
<div class="item">
    <?php
    $o = new wpdreamsCustomSelect("js_init", "Javascript init method", array(
            'selects'=>array(
                array('option'=>'Dynamic (default)', 'value'=>'dynamic'),
                array('option'=>'Blocking', 'value'=>'blocking')
            ),
            'value'=>$com_options['js_init']
        )
    );
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg">
        Try to choose <strong>Blocking</strong> if the search bar is not responding to anything.
    </p>
</div>
<div class="item">
    <?php $o = new wpdreamsYesNo("detect_ajax", "Try to re-initialize if the page was loaded via ajax?",
        $com_options['detect_ajax']
    ); ?>
    <p class='descMsg'>Will try to re-initialize the plugin in case an AJAX page loader is used, like Polylang language switcher etc..</p>
</div>
<div class="item">
    <?php $o = new wpdreamsYesNo("load_in_footer", "Load scripts in footer?",
        $com_options['load_in_footer']
    ); ?>
    <p class='descMsg'>Will load the scripts in the footer for better performance.</p>
</div>
<div class="item">
    <?php
    $o = new wpdreamsCustomSelect("css_compatibility_level", "CSS compatibility level", array(
            'selects'=>array(
                array('option'=>'Maximum', 'value'=>'maximum'),
                array('option'=>'Medium', 'value'=>'medium'),
                array('option'=>'Low', 'value'=>'low')
            ),
            'value'=>$com_options['css_compatibility_level']
        )
    );
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg">
    <ul style="float:right;text-align:left;width:50%;">
        <li><b>Maximum</b> - Highy compatibility, big size</li>
        <li><b>Medium</b> - Medium compatibility, medium size</li>
        <li><b>Low</b> - Low compabibility, small size</li>
    </ul>
    <div class="clear"></div>
    </p>
</div>
<div class="item">
    <p class='infoMsg'>Set to yes if you are experiencing issues with the <b>search styling</b>, or if the styles are <b>not saving</b>!</p>
    <?php $o = new wpdreamsYesNo("forceinlinestyles", "Force inline styles?",
        $com_options['forceinlinestyles']
    ); ?>
</div>
<div class="item">
    <?php $o = new wpdreamsYesNo("css_async_load", "Load CSS files conditionally? (asnychronous, <b>experimental!</b>)",
        $com_options['css_async_load']
    ); ?>
    <p class='descMsg'>
        Will save every search instance CSS file separately and load them with Javascript on the document load event.
        Only loads them if it finds the search instance on the page. Huge performance saver, however it might not work
        so test it seriously! Check the <a target="_blank" href="https://wpdreams.gitbooks.io/ajax-search-pro-documentation/content/performance/visual_performance.html#3-css-compatibility-and-loading">Visual Performance</a> section of the documentation for more info.
    </p>
</div>
<div class="item">
    <?php
    $o = new wpdreamsCustomSelect("load_mcustom_js", "Load the scrollbar script?", array(
            'selects'=>array(
                array('option'=>'Yes', 'value'=>'yes'),
                array('option'=>'No', 'value'=>'no')
            ),
            'value'=>$com_options['load_mcustom_js']
        )
    );
    $params[$o->getName()] = $o->getData();
    ?>
    <p class='descMsg'>
        <ul>
            <li>When set to <strong>No</strong>, the custom scrollbar will <strong>not be used at all</strong>.</li>
        </ul>
    </p>
</div>
<div class="item">
    <p class='infoMsg'>You can turn some of these off, if you are not using them.</p>
    <?php $o = new wpdreamsYesNo("loadpolaroidjs", "Load the polaroid gallery JS?",
        $com_options['loadpolaroidjs']
    ); ?>
    <p class='descMsg'>Don't turn this off if you are using the POLAROID layout.</p>
</div>
<div class="item">
    <?php $o = new wpdreamsYesNo("load_isotope_js", "Load the isotope JS?",
        $com_options['load_isotope_js']
    ); ?>
    <p class='descMsg'>Don't turn this off if you are using the ISOTOPIC layout.</p>
</div>
<div class="item">
    <?php $o = new wpdreamsYesNo("load_noui_js", "Load the NoUI slider JS?",
        $com_options['load_noui_js']
    ); ?>
    <p class='descMsg'>Don't turn this off if you are using SLIDERS in the custom field filters.</p>
</div>
<div class="item">
    <?php $o = new wpdreamsYesNo("load_datepicker_js", "Load the DatePicker UI script?",
        $com_options['load_datepicker_js']
    ); ?>
    <p class='descMsg'>Don't turn this off if you are using date picker on the search front-end.</p>
</div>
<div class="item">
    <p class='infoMsg'>This might speed up the search, but also can cause incompatibility issues with other plugins.</p>
    <?php $o = new wpdreamsYesNo("usecustomajaxhandler", "Use the custom ajax handler?",
        $com_options['usecustomajaxhandler']
    ); ?>
</div>