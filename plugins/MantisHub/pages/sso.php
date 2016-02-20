<?php
/**************************************************************************
 MantisHub Plugin
 Copyright (c) MantisHub - Victor Boctor
 All rights reserved.
 **************************************************************************/

require_api( 'mantishub_api.php' );
require_once( dirname( dirname( __FILE__ ) ) . '/core/mantishub_plugin_api.php' );

$f_token = gpc_get_string( 'token', '' );

if( mantishub_login_to_new_instance( $f_token ) ) {
    if( project_table_empty() ) {
        project_create( /* name */ 'MyProject', /* desc */ '', /* status: stable */ 50 );
    }

    $t_return = string_url( string_sanitize_url( config_get( 'default_home_page' ) ) );
    $t_redirect_url = 'login_cookie_test.php?return=' . $t_return;
} else {
    $t_redirect_url = 'login_page.php';
}

print_header_redirect( $t_redirect_url );
