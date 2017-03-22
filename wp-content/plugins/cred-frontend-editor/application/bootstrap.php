<?php

// All we have now is CRED_ABSPATH.

/*
 * Toolset Common Library paths
 */
if ( ! defined( 'WPTOOLSET_COMMON_PATH' ) ) {
	define( 'WPTOOLSET_COMMON_PATH', CRED_ABSPATH . '/library/toolset/toolset-common' );
}


/*
 * Loading sequence
 */


// Load common resources
require_once CRED_ABSPATH . '/library/toolset/onthego-resources/loader.php';
onthego_initialize( CRED_ABSPATH . '/library/toolset/onthego-resources/', CRED_ABSURL . '/library/toolset/onthego-resources/');


// Load Toolset Common Library
require_once CRED_ABSPATH . '/library/toolset/toolset-common/loader.php';
toolset_common_initialize( CRED_ABSPATH . '/library/toolset/toolset-common/', CRED_ABSURL . '/library/toolset/toolset-common/');


// Load old CRED
require_once CRED_ABSPATH . '/library/toolset/cred/plugin.php';


// Get new functions.php
require_once CRED_ABSPATH . '/application/functions.php';


// Jumpstart new CRED
require_once CRED_ABSPATH . '/application/controllers/main.php';
CRED_Main::initialize();