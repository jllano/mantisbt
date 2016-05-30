<?php
form_security_validate( 'plugin_Helpdesk_config' );
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

function config_option_update( $name, $value ) {
	if ( $value != plugin_config_get( $name ) ) {
		plugin_config_set( $name, $value );
	}
}

config_option_update( 'enabled', gpc_get_bool( 'enabled' ) );
config_option_update( 'default_project', gpc_get_int( 'default_project' ) );
config_option_update( 'issue_reported_message', gpc_get( 'issue_reported_message' ) );
config_option_update( 'failed_message', gpc_get( 'failed_message' ) );

form_security_purge( 'plugin_Helpdesk_config' );
print_successful_redirect( plugin_page( 'config', true ) );

