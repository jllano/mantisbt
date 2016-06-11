<?php
require_once( dirname( dirname( __FILE__ ) ) . '/core/helpdesk_api.php' );

form_security_validate( 'plugin_Helpdesk_config' );
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

$f_enable_unregistered = gpc_get_bool( 'enable_unregistered' ) ? 1 : 0;

plugin_config_set( 'enabled', gpc_get_bool( 'enabled' ) ? 1 : 0 );
plugin_config_set( 'default_project', gpc_get_int( 'default_project' ) );
plugin_config_set( 'enable_unregistered', $f_enable_unregistered );
plugin_config_set( 'issue_reported_message', gpc_get( 'issue_reported_message' ) );
plugin_config_set( 'failed_message', gpc_get( 'failed_message' ) );

if ( $f_enable_unregistered ) {
	helpdesk_create_generic_user();
}

form_security_purge( 'plugin_Helpdesk_config' );
print_successful_redirect( 'manage_plugin_page.php' );

