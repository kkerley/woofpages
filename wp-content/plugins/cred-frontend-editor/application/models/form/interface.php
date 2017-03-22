<?php

interface ICRED_Form_Base {

    public function print_form();

    public function build_form();

    public function validate_form($error_files);

    public function render_form($msgs = "", $js = "");

    public function save_form($post_id = null, $post_type = "");
}
