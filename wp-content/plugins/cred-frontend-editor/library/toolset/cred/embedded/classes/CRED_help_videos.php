<?php

if( class_exists('Toolset_HelpVideosFactoryAbstract') === false ){
    return;
}

class CRED_HelpVideos extends Toolset_HelpVideosFactoryAbstract{

    protected function define_toolset_videos(){
        return  array(
            'layout_template' =>  array(
                'name' => 'cred_form',
                'url' => 'http://d7j863fr5jhrr.cloudfront.net/toolset-cred-front-end-forms.mp4',
                'screens' => array('cred-form'),
                'element' => '.toolset-video-box-wrap',
                'title' => __('Creating and displaying a CRED form', 'wp-cred'),
                'width' => '900px',
                'height' => '506px',
                'append_to' => '#post-body-content'
            )
        );
    }
}
add_action( 'init', array("CRED_HelpVideos", "getInstance") );