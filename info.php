<?php
$g_bypass_headers = true; # suppress headers as we will send our own later
define( 'COMPRESSION_DISABLED', true );

# ignore mantis_offline.php
$_GET['mbadmin'] = true;

require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'core.php' );
require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'database_api.php' );
require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'mantishub_api.php' );

http_security_headers();

header( 'Pragma: public' );
header( 'Pragma: no-cache' );
header( 'Expires: ' . gmdate( 'D, d M Y H:i:s \G\M\T', time() ) );
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s \G\M\T', time() ) );
header( 'X-Content-Type-Options: nosniff' );

$f_json = gpc_get_bool( 'json', false );

if ( $f_json ) {
	header( 'Content-Type: application/json' );
} else {
	header( 'Content-Type: text' );
}

html_robots_noindex();

$t_info = array();

$t_info['generation'] = config_get_global( 'mantishub_gen' );
$t_info['package_type'] = trim( @file_get_contents( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'package_type.txt' ) );
$t_info['package_timestamp'] = trim( @file_get_contents( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'package_timestamp.txt' ) );
$t_info['trial'] = $g_mantishub_info_trial;
$t_info['plan'] = plan_name();

$t_issues_count = mantishub_table_row_count( 'bug' );

$t_info['users_count'] = mantishub_table_row_count( 'user' );
$t_info['team_count'] = mantishub_team_users();
$t_info['projects_count'] = mantishub_table_row_count( 'project' );
$t_info['issues_count'] = $t_issues_count;
$t_info['team_packs'] = plan_user_packs_needed( $t_info['team_count'] );
$t_info['attachments_count'] = mantishub_table_row_count( 'bug_file' );
$t_info['email_queue_count'] = mantishub_table_row_count( 'email' );
$t_info['server_ip'] = $_SERVER['SERVER_ADDR'];
$t_info['logo'] = file_exists( dirname( __FILE__ ) . '/images/logo.png' );
$t_info['creation_timestamp'] = strftime( '%m/%d/%Y %H:%M:%S', $g_mantishub_info_creation_date );
if ( $t_issues_count > 0 ) {
	$t_info['last_activity_timestamp'] = strftime( '%m/%d/%Y %H:%M:%S', mantishub_last_issue_update() );
} else {
	$t_info['last_activity_timestamp'] = $t_info['creation_date'];
}

$t_json = json_encode( $t_info );

if ( $f_json ) {
	echo $t_json;
} else {
	echo 'Package_Timestamp=' . $t_info['package_timestamp'] . "\n";
	echo 'Creation_Date=' . $t_info['creation_timestamp'] . "\n";
	echo 'Last_Issue_Update=' . $t_info['last_activity_timestamp'] . "\n";
	echo 'Issues=' . $t_info['issues_count'] . "\n";
	echo 'Projects=' . $t_info['projects_count'] . "\n";
	echo 'Users=' . $t_info['users_count'] . "\n";
	echo "TeamCount=" . $t_info['team_count'] . "\n";
	echo "TeamPacks=" . $t_info['team_packs'] . "\n";
	echo 'Attachments=' . $t_info['attachments_count'] . "\n";
	echo 'EmailQueue=' . $t_info['email_queue_count'] . "\n";
	echo 'Server IP=' . $t_info['server_ip'] . "\n";
	echo 'Trial='. ( $t_info['trial'] ? '1' : '0' ) . "\n";
	echo 'Custom_Logo=' . ( $t_info['logo'] ? '1' : '0' ) . "\n";
	echo 'Plan=' . plan_name() . "\n";
	echo 'Package_Type=' . $t_info['package_type'] . "\n";
	echo 'Generation=' . $t_info['generation'] . "\n";
}

# Add fields that we don't want to disclose on the web, but just internally on the server
# for cronjobs to use.
$t_info['administrator_email'] = config_get_global( 'mantishub_info_administrator_email' );
$t_info['administrator_firstname'] = config_get_global( 'mantishub_info_administrator_firstname' );
$t_info['administrator_lastname'] = config_get_global( 'mantishub_info_administrator_lastname' );

$t_info['company'] = config_get_global( 'mantishub_info_company' );

$t_info['value'] = plan_price() + $t_info['team_packs'] * 10;

$t_output = array();
exec( 'hostname', $t_output );
$t_info['hostname'] = str_replace( '.mantishub.com', '', $t_output[0] );

$t_json_filename = $g_config_path . 'info.json';

# In dev machine, this access may not be granted
@file_put_contents( $t_json_filename, $t_json );
