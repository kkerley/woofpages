<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

define( "CRED_GENERIC_RESPONSE_TYPE_JSON", 1 );

define( "CRED_GENERIC_RESPONSE_RESULT_OK", "ok" );
define( "CRED_GENERIC_RESPONSE_RESULT_KO", "ko" );
define( "CRED_GENERIC_RESPONSE_RESULT_REDIRECT", "redirect" );

/**
 * Description of CRED_Ajax_Response
 *
 * @author flivo
 */
class CRED_Generic_Response {

    public $form_id;
    public $form_html_id;
    public $form_type;
    public $post_id;
    public $is_ajax;
    public $result;
    public $output;
    public $form_helper;
    public $delay = 0;

    public function __construct($result, $output, $is_ajax, $form_data, $form_helper = null, $delay = 0) {
        $this->form_id = isset( $form_data['id'] ) ? $form_data['id'] : 0;
        $this->post_id = isset( $form_data['post_id'] ) ? $form_data['post_id'] : 0;
        $this->form_type = isset( $form_data['form_type'] ) ? $form_data['form_type'] : '';
        $this->form_html_id = isset( $form_data['form_html_id'] ) ? $form_data['form_html_id'] : '';
        $this->result = $result;
        $this->output = $output;
        $this->is_ajax = $is_ajax;
        $this->form_helper = $form_helper;
        $this->delay = $delay;
    }

    public function set_delay($delay) {
        $this->delay = $delay;
    }

    public function show() {
        cred_log( array($this->result, $this->output, $this->is_ajax, $this->form_type, $this->delay) );
        switch ($this->result) {
            case CRED_GENERIC_RESPONSE_RESULT_OK:
            case CRED_GENERIC_RESPONSE_RESULT_KO:

                if ( $this->is_ajax == CRED_GENERIC_RESPONSE_TYPE_JSON ) {
                    ob_start();
                    ?>
                    <script>
                    <?php if ( $this->result == CRED_GENERIC_RESPONSE_RESULT_OK ) { ?>
                            if (typeof jQuery('.wpt-form-error') !== 'undefined')
                                jQuery('.wpt-form-error').hide();
                    <?php } ?>
                        jQuery(document).ready(function () {
                            _.defer(function () {
                                if (typeof wptValidation !== 'undefined') {
                                    //console.log("wptValidation.init();");
                                    wptValidation.init();
                                }
                                if (typeof wptCond !== 'undefined') {
                                    //console.log("wptCond.init();");
                                    wptCond.init();
                                }
                                if (typeof wptRep !== 'undefined') {
                                    //console.log("wptRep.init();");
                                    wptRep.init();
                                }
                                if (typeof wptCredfile !== 'undefined') {
                                    //console.log("wptCredfile.init();");
                                    wptCredfile.init('body');
                                }
                                if (typeof toolsetForms !== 'undefined') {
                                    //console.log("toolsetForms.cred_tax = new toolsetForms.CRED_taxonomy();");
                                    toolsetForms.cred_tax = new toolsetForms.CRED_taxonomy();
                                    if (typeof initCurrentTaxonomy == 'function') {
                                        initCurrentTaxonomy();
                                    }
                                }
                                if (typeof wptDate !== 'undefined') {
                                    wptDate.init('body');
                                }

                                if (typeof jQuery('.wpt-suggest-taxonomy-term') && jQuery('.wpt-suggest-taxonomy-term').length)
                                    jQuery('.wpt-suggest-taxonomy-term').hide();

                                jQuery(document).trigger('js_event_cred_ajax_form_response_completed');
                            });
                        });
                    </script>

                    <?php
                    $script = ob_get_clean();

                    $data = array(
                        'result' => $this->result,
                        'is_ajax' => $this->is_ajax,
                        'output' => $this->output . "\n" . $script,
                        'formtype' => $this->form_type
                    );
                    if ( defined( 'CRED_DEBUG' ) && CRED_DEBUG ) {
                        $data['debug'] = array();
                        $data['debug']['post'] = $_POST;
                        $data['debug']['files'] = $_FILES;
                    }
                    cred_log( $data );
                    echo wp_json_encode( $data );
                    die;
                } else {
                    return $this->output;
                }

                break;
            case CRED_GENERIC_RESPONSE_RESULT_REDIRECT:
                if ( $this->is_ajax == CRED_GENERIC_RESPONSE_TYPE_JSON ) {
                    $data = array(
                        'result' => $this->result,
                        'is_ajax' => $this->is_ajax,
                        'output' => "<p>" . __( 'Please Wait. You are being redirected...', 'wp-cred' ) . "</p>" . $this->do_redirect( $this->output, $this->delay, true ), //($this->delay > 0) ? $this->form_helper->redirectDelayedFromAjax($this->output, $this->delay) : $this->form_helper->redirectFromAjax($this->output),
                        'formtype' => $this->form_type
                    );
                    if ( defined( 'CRED_DEBUG' ) && CRED_DEBUG ) {
                        $data['debug'] = array();
                        $data['debug']['post'] = $_POST;
                        $data['debug']['files'] = $_FILES;
                    }
                    cred_log( $data );
                    echo wp_json_encode( $data );
                    die;
                } else {
                    return "<p>" . __( 'Please Wait. You are being redirected...', 'wp-cred' ) . "</p>" . $this->do_redirect( $this->output, $this->delay, false );
                }
                break;
        }
    }

    private function do_redirect($url, $delay, $with_ajax = false) {
        if ( $with_ajax ) {
            return ($delay > 0) ? $this->redirectDelayedFromAjax( $url, $delay ) : $this->redirectFromAjax( $url );
        } else {
            return ($delay > 0) ? $this->redirectDelayed( $url, $delay ) : $this->redirect( $url, array("HTTP/1.1 303 See Other") );
        }
    }

    private function redirect($uri, $headers = array()) {
        if ( !headers_sent() ) {
            // additional headers
            if ( !empty( $headers ) ) {
                foreach ( $headers as $header )
                    header( "$header" );
            }
            // redirect
            header( "Location: $uri" );
            exit();
        } else {
            echo sprintf( "<script>jQuery(document).ready(function() { jQuery('.submit').hide();  } );</script><script type='text/javascript'>document.location='%s';</script>", $uri );
            exit();
        }
    }

    private function redirectDelayed($uri, $delay) {
        $delay = intval( $delay );
        if ( $delay <= 0 ) {
            $this->redirect( $uri );
            return;
        }
        if ( !headers_sent() ) {
            $this->_uri_ = $uri;
            $this->_delay_ = $delay;
            add_action( 'wp_head', array(&$this, 'doDelayedRedirect'), 1000 );
        } else {
            return sprintf( "<script>jQuery(document).ready(function() { jQuery('.submit').hide();  } );</script><script type='text/javascript'>setTimeout(function(){document.location='%s';},%d);</script>", $uri, $delay * 1000 );
        }
    }

    private function redirectFromAjax($uri) {
        return sprintf( "<script>jQuery(document).ready(function() { jQuery('.submit').hide();  } );</script><script type='text/javascript'>document.location='%s';</script>", $uri );
    }

    private function redirectDelayedFromAjax($uri, $delay) {
        $delay = intval( $delay );
        if ( $delay <= 0 ) {
            return $this->redirectFromAjax( $uri );
        }
        return sprintf( "<script>jQuery(document).ready(function() { jQuery('.submit').hide();  } );</script><script type='text/javascript'>setTimeout(function(){document.location='%s';},%d);</script>", $uri, $delay * 1000 );
    }

    public function doDelayedRedirect() {
        echo sprintf( "<script>jQuery(document).ready(function() { jQuery('.submit').hide();  } );</script><meta http-equiv='refresh' content='%d;url=%s'>", $this->_delay_, $this->_uri_ );
    }

}
