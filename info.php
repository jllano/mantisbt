<?php
$g_bypass_headers = true; # suppress headers as we will send our own later
define( 'COMPRESSION_DISABLED', true );

require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'core.php' );
require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'database_api.php' );
require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'mantishub_api.php' );

http_security_headers();

header( 'Pragma: public' );
header( 'Pragma: no-cache' );
header( 'Expires: ' . gmdate( 'D, d M Y H:i:s \G\M\T', time() ) );
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s \G\M\T', time() ) );
header( 'X-Content-Type-Options: nosniff' );
header( 'Content-Type: text' );

html_robots_noindex();

$t_issues_count = (int)mantishub_table_row_count( 'bug' );

echo 'Package_Timestamp=' . @file_get_contents( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'package_timestamp.txt' );
echo 'Creation_Date=' . strftime( '%m/%d/%Y %H:%M:%S', $g_mantishub_info_creation_date ) . "\n";

if ( $t_issues_count > 0 ) {
	echo 'Last_Issue_Update=' . strftime( '%m/%d/%Y %H:%M:%S', mantishub_last_issue_update() ) . "\n";
}

echo 'Issues=' . $t_issues_count . "\n";
echo 'Projects=' . mantishub_table_row_count( 'project' ) . "\n";
echo 'Users=' . mantishub_table_row_count( 'user' ) . "\n";
echo 'Attachments=' . mantishub_table_row_count( 'bug_file' ) . "\n";
echo 'Server IP=' . $_SERVER['SERVER_ADDR'] . "\n";
echo 'Trial='. $g_mantishub_info_trial . "\n";
echo 'Custom_Logo=' . ( file_exists( dirname( __FILE__ ) . '/images/logo.png' ) ? '1' : '0' ) . "\n";
