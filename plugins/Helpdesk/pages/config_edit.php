<?php
form_security_validate( 'plugin_Helpdesk_config' );
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

plugin_config_set( 'enabled', gpc_get_bool( 'enabled' ) ? 1 : 0 );
plugin_config_set( 'default_project', gpc_get_int( 'default_project' ) );
plugin_config_set( 'issue_reported_message', gpc_get( 'issue_reported_message' ) );
plugin_config_set( 'failed_message', gpc_get( 'failed_message' ) );

form_security_purge( 'plugin_Helpdesk_config' );
print_successful_redirect( 'manage_plugin_page.php' );

