<?php
function xmlhttprequest_plugin_zendesk_articles() {
	plugin_push_current( 'Zendesk' );

	$query = gpc_get_string('query');
	$url = 'http://mantishub.zendesk.com/api/v2/help_center/articles/search.json?query=' . urlencode( $query ) . '&sort_by=title';

	$ch = curl_init();

	# TODO: extract query parameters
	curl_setopt( $ch, CURLOPT_URL, $url );

	curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'GET' );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );

	$t_basic_auth = plugin_config_get( 'user' ) . '/token:' . plugin_config_get( 'token' );
	$t_basic_auth = base64_encode( $t_basic_auth );

	# TODO: Use config for email and another for key and constructor the base64 of the string.
	curl_setopt($ch, CURLOPT_HTTPHEADER, array( "Authorization: Basic " . $t_basic_auth ) );

	$resp = curl_exec( $ch );

	if( !$resp ) {
		$t_result = json_encode( false );
	} else {
		$t_result = $resp;
	}

	curl_close($ch);

	echo $t_result;
}