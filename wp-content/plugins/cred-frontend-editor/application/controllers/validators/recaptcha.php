<?php

class CRED_Validator_Recaptcha extends CRED_Validator_Base implements ICRED_Validator {

    public function validate() {
        $zebraForm = $this->_zebraForm;
        $formHelper = $this->_formHelper;

        $result = true;
        if (isset($_POST['_recaptcha'])) {
            if
            (
                    (isset($_POST["g-recaptcha-response"]) && !empty($_POST["g-recaptcha-response"]))
            ) {
                $captcha = $_POST['g-recaptcha-response'];

                $settings_model = CRED_Loader::get('MODEL/Settings');
                $settings = $settings_model->getSettings();

                $publickey = $settings['recaptcha']['public_key'];
                $privatekey = $settings['recaptcha']['private_key'];

                $secretKey = $settings['recaptcha']['private_key'];
                $ip = $_SERVER['REMOTE_ADDR'];

                if (empty($privatekey) || empty($publickey)) {
                    //$zebraForm->add_form_error('security', $formHelper->getLocalisedMessage('no_recaptcha_keys'));
                    $zebraForm->add_top_message($formHelper->getLocalisedMessage('no_recaptcha_keys'));
                    $zebraForm->add_field_message($formHelper->getLocalisedMessage('no_recaptcha_keys'));
                    $result = false;
                    cred_log("captcha error");
                    cred_log("captcha " . $captcha);
                    cred_log("$privatekey");
                    cred_log("$publickey");
                    //return $result;
                } else {

                    $params = array();
                    $params['secret'] = $secretKey; // Secret key
                    if (!empty($_POST) && isset($_POST['g-recaptcha-response'])) {
                        $params['response'] = urlencode($_POST['g-recaptcha-response']);
                    }
                    $params['remoteip'] = $_SERVER['REMOTE_ADDR'];

                    $params_string = http_build_query($params);
                    $requestURL = 'https://www.google.com/recaptcha/api/siteverify?' . $params_string;

                    cred_log($requestURL);

                    //Try to use curl_init
                    if (function_exists('curl_init')) {
                        // Get cURL resource
                        $curl = curl_init();

                        // Set some options
                        curl_setopt_array($curl, array(
                            CURLOPT_RETURNTRANSFER => 1,
                            CURLOPT_URL => $requestURL,
                        ));

                        // Send the request
                        $response = curl_exec($curl);
                        // Close request to clear up some resources
                        curl_close($curl);
                    }

                    //Try file_get_contents
                    if (!isset($response) || empty($response)) {
                        $response = file_get_contents($requestURL);
                    }
                    $response = json_decode($response, true);
                    cred_log($response);

                    //$response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=" . $secretKey . "&response=" . $captcha . "&remoteip=" . $ip);
                    //$responseKeys = json_decode($response, true);
                    //if (intval($responseKeys["success"]) !== 1) {
                    if ($response["success"] !== true) {
                        //$zebraForm->add_form_error('security', $formHelper->getLocalisedMessage('enter_valid_captcha'));
                        $zebraForm->add_top_message($formHelper->getLocalisedMessage('enter_valid_captcha'));
                        $zebraForm->add_field_message($formHelper->getLocalisedMessage('enter_valid_captcha'));
                        $result = false;
                        //return $result;
                    }
                }
            } else {
                $zebraForm->add_top_message($formHelper->getLocalisedMessage('enter_valid_captcha'));
                $zebraForm->add_field_message($formHelper->getLocalisedMessage('enter_valid_captcha'));
                $result = false;
                cred_log("captcha error");
                cred_log("enter_valid_captcha");
            }
        }
        cred_log($result);
        return $result;
    }

}
