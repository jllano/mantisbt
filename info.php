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
	header( 'Content-Type: text/plain' );
}

html_robots_noindex();

$t_info = plan_info_get_public( /* force refresh */ true );

if ( $f_json ) {
	echo json_encode( $t_info );
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
	echo 'Plan=' . $t_info['plan'] . "\n";
	echo 'Package_Type=' . $t_info['package_type'] . "\n";
	echo 'Generation=' . $t_info['generation'] . "\n";
}
