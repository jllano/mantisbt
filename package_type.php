<?php
# MantisBT - a php based bugtracking system

require_once( 'core.php' );
require_api( 'access_api.php' );

access_ensure_global_level( ADMINISTRATOR );

form_security_validate( 'package_type' );

$f_package_type = gpc_get_string( 'package_type' );

switch ( $f_package_type ) {
	case 'mantishub-1.3.x':
	case 'mantishub-1.3.x-m':
		$t_package_type_file_path = dirname( __FILE__ ) . '/package_type_request.txt';
		file_put_contents( $t_package_type_file_path, $f_package_type );
		break;
	default:
		echo "invalid package type";
		exit;
}

html_page_top1();
html_page_top2();

echo '<br />';
echo '<br />';
echo '<center>';
echo 'Your MantisHub will be switched within 10 minutes.<br />';
echo 'If layout shows incorrectly then, click browser refresh button.<br />';
echo '</center>';
echo '<br />';
echo '<br />';

html_page_bottom();

