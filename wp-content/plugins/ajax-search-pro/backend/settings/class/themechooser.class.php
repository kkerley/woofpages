<?php
if (!class_exists("wpdreamsThemeChooser")) {
    /**
     * Class wpdreamsThemeChooser
     *
     * Theme selector class. Uses the json decoded data do form each theme.
     *
     * @package  WPDreams/OptionsFramework/Classes
     * @category Class
     * @author Ernest Marcinko <ernest.marcinko@wp-dreams.com>
     * @link http://wp-dreams.com, http://codecanyon.net/user/anago/portfolio
     * @copyright Copyright (c) 2014, Ernest Marcinko
     */
    class wpdreamsThemeChooser extends wpdreamsType {
        function getType() {
            parent::getType();
            $this->processData();
            echo "
      <div class='wpdreamsThemeChooser'>
       <fieldset style='background:#FAFAFA;padding:0;'>
       <label style='color:#333' for='wpdreamsThemeChooser_'" . self::$_instancenumber . "'>" . $this->label . "</label>";
            $decodedData = json_decode($this->themes);
            echo "<select id='wpdreamsThemeChooser_" . self::$_instancenumber . "' name='" . $this->name . "'>
          <option value=''>Select</option>";
            foreach ($decodedData as $name => $theme) {
                $selected = $name == $this->selected ? " selected='selected'" : "";
                if ($theme === false)
                    echo "<option value='" . $name . "' disabled>" . $name . "</option>";
                else
                    echo "<option value='" . $name . "'".$selected.">" . $name . "</option>";
            }
            echo "</select>";
            foreach ($decodedData as $name => $theme) {
                if ($theme === false) continue;
                echo "<div name='" . $name . "' style='display:none;'>";
                /*foreach ($theme as $pname => $param) {
                    echo "<p paramname='" . $pname . "'>" . $param . "</p>";
                }*/
                echo json_encode($theme);
                echo "</div>";
            }
            echo "
      <span></span>
      <input id='asp_import_theme' class='submit wd_button_red' type='button' value='Import'>
      <input id='asp_export_theme' class='submit wd_button_blue' type='button' value='Export'>
      <p class='descMsg'>Changes not take effect on the frontend until you save them.</p>
      </fieldset>";
      ?>
<div id="wpd_imex_modal_bg" class="wpd-modal-bg"></div>
<div id="wpd_import_modal" class="wpd-modal hiddend">
    <h3 style="font-family: 'Open Sans',sans-serif;text-align: left; margin-top: 0;margin-left: 7px; font-size: 18px; font-weight: 600;">Import theme</h3>
    <div class="wpd-modal-close"></div>
    <div class="wpd_md_col">
        <p class="descMsg">Paste the exported theme here</p>
        <textarea></textarea><br>
        <div class="errorMsg hiddend">Invalid or missing data, please try again!</div>
        <input id='asp_import_theme_btn' class='submit wd_button_red' type='button' value='Import'>
    </div>
</div>
<div id="wpd_export_modal" class="wpd-modal hiddend">
    <h3 style="font-family: 'Open Sans',sans-serif;text-align: left; margin-top: 0;margin-left: 7px; font-size: 18px; font-weight: 600;">Export theme</h3>
    <div class="wpd-modal-close"></div>
    <div class="wpd_md_col">
        <p class="descMsg">Copy this text and save it on your computer</p>
        <textarea></textarea>
    </div>
</div>
      <?php
      echo "</div>";
        }

        function processData() {
            $this->themes = $this->data['themes'];
            $this->selected = $this->data['value'];
        }

        final function getData() {
            return $this->data;
        }

        final function getSelected() {
            return $this->selected;
        }
    }
}