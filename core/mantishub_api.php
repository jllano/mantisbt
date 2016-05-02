<?php
/**
 * @package MantisHub
 * @copyright Copyright (C) 2013 - 2014 Victor Boctor - vboctor@gmail.com
 * @link http://www.mantishub.com
 */

/**
 * MantisBT Core API's
 */
require_once( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'core.php' );

$g_mantishub_domains = array( 'mantishub.com', 'mantishub.io' );

// for log correlation.
$global_log_request_id = time();

// the log fields that don't change over the life of the request
$g_common_log_fields = null;

// Don't re-authenticate when impersonation is ON.
if ( mantishub_impersonation() ) {
	$g_reauthentication = OFF;
}

/**
 * Check if the current session is impersonation session or standard one.
 * @returns true: impersonation, false: otherwise.
 */
function mantishub_impersonation() {
	$t_cookie_name = config_get_global( 'support_cookie' );
	$t_impersonation = (int)gpc_get_cookie( $t_cookie_name, 0 );

	return $t_impersonation == 1;
}

/**
 * Check if the specified text contains any of the mantishub domain.
 *
 * @param  string $p_text The text to check.
 * @return bool true: contains a domain, false: otherwise.
 */
function mantishub_string_contains_domain( $p_text ) {
	global $g_mantishub_domains;

	foreach( $g_mantishub_domains as $t_domain ) {
		if ( stristr( $p_text, $t_domain ) !== false ) {
			return true;
		}
	}

	return false;
}

function mantishub_top_message() {
	global $g_mantishub_info_trial, $g_mantishub_info_payment_on_file;

	if ( $g_mantishub_info_trial && !$g_mantishub_info_payment_on_file && current_user_is_administrator() ) {
		$t_issues_count = mantishub_table_row_count( 'bug' );

		$t_trial_conversion_url = config_get( 'mantishub_info_trial_conversion_url', '' );
		if ( $t_issues_count >= 5 && !is_blank( $t_trial_conversion_url ) ) {
			echo '<div style="background-color: #fff494; z-index: 10; position: absolute; right: 5px; top: 5px; text-align: right;"><b>Trial Version:</b> Click <a href="' . $t_trial_conversion_url . '" target="_blank">here</a> to convert to paid and enable daily backups.</div>';
		}
	}
}

/**
 * Get the username of an enabled administrator account.
 * @returns user name.
 */
function mantishub_get_admin_username() {
	$query = "SELECT username
				  FROM {user}
				  WHERE enabled = 1 AND access_level >= " . db_param();
	$result = db_query( $query, array( ADMINISTRATOR ) );
	$row = db_fetch_array( $result );
	return $row['username'];
}

function mantishub_google_analytics() {
	// If a page auto-refreshes itself then don't report that as activity.
	if ( isset( $_GET['refresh'] ) && $_GET['refresh'] == 'true' ) {
		return;
	}

	// MantisHub Google Analytics to track engagement.
	echo "<script>";
   	echo "(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){";
   	echo "(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),";
  	echo "m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)";
  	echo "})(window,document,'script','//www.google-analytics.com/analytics.js','ga');\n";
  	echo "ga('create', 'UA-330112-9', 'mantishub.com');\n";
	echo "ga('send', 'pageview');\n";
	echo "</script>\n";

	global $g_mantishub_info_trial;

	if ( $g_mantishub_info_trial ) {
		# <!-- Google Code for Trail Starts Conversion Page -->
		echo '<script type="text/javascript">' . "\n";
		echo '/' . '* <![CDATA[ */' . "\n";
		echo 'var google_conversion_id = 970248102;' . "\n";
		echo 'var google_conversion_language = "en";' . "\n";
		echo 'var google_conversion_format = "3";' . "\n";
		echo 'var google_conversion_color = "ffffff";' . "\n";
		echo 'var google_conversion_label = "eQWuCIrG5AkQpp_TzgM";' . "\n";
		echo 'var google_remarketing_only = false;' . "\n";
		echo '/* ]]> */' . "\n";
		echo '</script>' . "\n";
		echo '<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">' . "\n";
		echo '</script>' . "\n";
		echo '<noscript>' . "\n";
		echo '<div style="display:inline;">' . "\n";
		echo '<img height="1" width="1" style="border-style:none;" alt="" src="//www.googleadservices.com/pagead/conversion/970248102/?label=eQWuCIrG5AkQpp_TzgM&guid=ON&script=0"/>' . "\n";
		echo '</div>' . "\n";
		echo '</noscript>' . "\n";
	} else {
		$t_value = plan_price();

		# <!-- Google Code for Trial Converts Conversion Page -->
		echo '<script type="text/javascript">' . "\n";
		echo '/' . '* <![CDATA[ */' . "\n";
		echo 'var google_conversion_id = 970248102;' . "\n";
		echo 'var google_conversion_language = "en";' . "\n";
		echo 'var google_conversion_format = "3";' . "\n";
		echo 'var google_conversion_color = "ffffff";' . "\n";
		echo 'var google_conversion_label = "GYQECPrH5AkQpp_TzgM";' . "\n";
		echo 'var google_conversion_value = ' . $t_value . ';' . "\n";
		echo 'var google_remarketing_only = false;' . "\n";
		echo '/* ]]> */' . "\n";
		echo '</script>' . "\n";
		echo '<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">' . "\n";
		echo '</script>' . "\n";
		echo '<noscript>' . "\n";
		echo '<div style="display:inline;">' . "\n";
		echo '<img height="1" width="1" style="border-style:none;" alt="" src="//www.googleadservices.com/pagead/conversion/970248102/?value=20.000000&label=GYQECPrH5AkQpp_TzgM&guid=ON&script=0"/>' . "\n";
		echo '</div>' . "\n";
		echo '</noscript>' . "\n";
	}
}

function mantishub_bingads_analytics() {
	// If a page auto-refreshes itself then don't report that as activity.
	if ( isset( $_GET['refresh'] ) && $_GET['refresh'] == 'true' ) {
		return;
	}

	global $g_mantishub_info_trial;

	if ( $g_mantishub_info_trial ) {
		# <!-- BingAds Code for Trail Starts Conversion Page -->
		echo '<script>(function(w,d,t,r,u){var f,n,i;w[u]=w[u]||[],f=function(){var o={ti:"4061542"};o.q=w[u],w[u]=new UET(o),w[u].push("pageLoad")},n=d.createElement(t),n.src=r,n.async=1,n.onload=n.onreadystatechange=function(){var s=this.readyState;s&&s!=="loaded"&&s!=="complete"||(f(),n.onload=n.onreadystatechange=null)},i=d.getElementsByTagName(t)[0],i.parentNode.insertBefore(n,i)})(window,document,"script","//bat.bing.com/bat.js","uetq");</script>';
		echo '<noscript><img src="//bat.bing.com/action/0?ti=4061542&Ver=2" height="0" width="0" style="display:none; visibility: hidden;" /></noscript>';
	} else {
		$t_value = plan_price();

		# Pass plan value first
		echo '<script>' . "\n";
		echo '	window.uetq = window.uetq || [];' . "\n";
		echo "	window.uetq.push({ 'gv': " . $t_value . ' })' . "\n";
		echo '</script>' . "\n";

		# <!-- BingAds Code for Trial Converts Conversion Page -->
		echo '<script>(function(w,d,t,r,u){var f,n,i;w[u]=w[u]||[],f=function(){var o={ti:"4061543"};o.q=w[u],w[u]=new UET(o),w[u].push("pageLoad")},n=d.createElement(t),n.src=r,n.async=1,n.onload=n.onreadystatechange=function(){var s=this.readyState;s&&s!=="loaded"&&s!=="complete"||(f(),n.onload=n.onreadystatechange=null)},i=d.getElementsByTagName(t)[0],i.parentNode.insertBefore(n,i)})(window,document,"script","//bat.bing.com/bat.js","uetq");</script>';
		echo '<noscript><img src="//bat.bing.com/action/0?ti=4061543&Ver=2" height="0" width="0" style="display:none; visibility: hidden;" /></noscript>';
	}
}

function mantishub_mailgun_key() {
	return 'key-8j9a2ntv0ti6f4ldntxgpoxkk6774wt0';
}

/**
 * Clean project name to be part of the email address.
 * instance+project@mantishub.net
 *
 * Used in creating mailgun routes.
 */
function mantishub_mailgun_project_name_clean( $p_project_name ) {
	$t_clean_project_name = '';
	$t_last_underscore = true;

	for ( $i = 0; $i < strlen( $p_project_name ); ++$i ) {
		$c = $p_project_name[$i];
		if ( ( $c >= 'a' && $c <= 'z' ) || ( $c >= 'A' && $c <= 'Z' ) || ( $c >= '0' && $c <= '9' ) ) {
			$t_clean_project_name .= $c;
			$t_last_underscore = false;
		} else if ( !$t_last_underscore ) {
			$t_clean_project_name .= '_';
		}
	}

	return strtolower( $t_clean_project_name );
}

function mantishub_project_get_row_by_clean_name( $p_project_name ) {
	$t_project_id = project_get_id_by_name( $p_project_name );

	if ( $t_project_id == 0 ) {
		$t_projects = project_get_all_rows();
		$t_project_found = false;

		foreach ( $t_projects as $t_project ) {
			if ( mantishub_mailgun_project_name_clean( $t_project['name'] ) != $p_project_name ) {
				continue;
			}

			$t_project_found = $t_project;
			break;
		}
	} else {
		$t_project_found = project_get_row( $t_project_id );
	}

	return $t_project_found;
}

/**
 * For recipient instance+proj@mantishub.net, the project is 'proj'.
 * @return false if not found, otherwise project info.
 */
function mantishub_mailgun_project_from_recipient( $p_recipient ) {
	$t_instance_name = mantishub_instance_name();
	$t_instance_name = strtolower( $t_instance_name );

	$t_target_project_name = strtolower( $p_recipient );
	$t_target_project_name = substr( $t_target_project_name, 0, strpos( $t_target_project_name, '@' ) );

	// If instancename@domain.com, then return false since project is not specified.
	if ( $t_target_project_name == $t_instance_name ) {
		return false;
	}

	$t_target_project_name = str_replace( $t_instance_name . '+', '', $t_target_project_name );

	return mantishub_project_get_row_by_clean_name( $t_target_project_name );
}

function mantishub_mailgun_issue_from_recipient( $p_recipient, &$p_abort_error ) {
	$p_abort_error = '';
	$t_instance_name = mantishub_instance_name();
	$t_instance_name = strtolower( $t_instance_name );

	$t_before_at = strtolower( $p_recipient );
	$t_before_at = substr( $t_before_at, 0, strpos( $t_before_at, '@' ) );

	// If instancename@domain.com, then return false since project is not specified.
	if ( $t_before_at == $t_instance_name ) {
		return 0;
	}

	$t_between_plus_and_at = str_replace( $t_instance_name . '+', '', $t_before_at );

	# Don't match an issue if a project exists with the same name.
	$t_project_row = mantishub_project_get_row_by_clean_name( $t_between_plus_and_at );
	if ( $t_project_row !== false ) {
		return 0;
	}

	$t_parts = explode( '-', $t_between_plus_and_at );
	if ( count( $t_parts ) != 2 ) {
		# If numeric, then looks like the email is related to an issue but without a hash.
		# Otherwise, then it is just a project that doesn't exist.
		if ( is_numeric( $t_between_plus_and_at ) ) {
			$p_abort_error = "missing token from recipient address.";
		}

		return 0;
	}

	$t_id = $t_parts[0];

	if ( !is_numeric( $t_id ) ) {
		return 0;
	}

	mantishub_log( 'incoming mail: issue id = ' . $t_id );

	if ( !bug_exists( $t_id ) ) {
		$p_abort_error = "issue $t_id doesn't exist.";
		return 0;
	}

	$t_md5 = $t_parts[1];
	mantishub_log( 'incoming mail: received md5 = ' . $t_md5 );

	$t_expected_md5 = md5( $t_id . config_get( 'crypto_master_salt' ) );
	mantishub_log( 'incoming mail: expected md5 = ' . $t_expected_md5 );

	if ( $t_md5 != $t_expected_md5 ) {
		$p_abort_error = "Invalid recipient address";
		return 0;
	}

	return $t_id;
}

function mantishub_event( $p_event, $p_can_call_db = true ) {
	$t_line = '';

	if ( !isset( $p_event['level'] ) ) {
		$p_event['level'] = 'info';
	}

	if ( $p_can_call_db ) {	
		global $g_common_log_fields;
		if ( $g_common_log_fields === null ) {
			$g_common_log_fields = '';
			$g_common_log_fields .= ' hub=' . mantishub_instance_name();

			if ( auth_is_user_authenticated() ) {
				$g_common_log_fields .= ' user=' . current_user_get_field( 'username' );
				$g_common_log_fields .= ' email=' . current_user_get_field( 'email' );
			}
		}

		$t_line .= $g_common_log_fields;
	} else {
		$t_line .= ' hub=' . mantishub_instance_name();
	}

	foreach ( $p_event as $t_key => $t_value ) {
		$t_field = mantishub_extra_event_key_value( $p_event, $t_key );
		if ( !is_blank( $t_field ) ) {
			$t_line .= ' ' . $t_field;
		}
	}

	mantishub_log( $t_line );
}

function mantishub_extra_event_key_value( $p_event, $p_key, $p_default = null ) {
	$t_result = $p_key . '=';

	if ( isset( $p_event[$p_key] ) ) {
		$t_value = $p_event[$p_key];
	} else if ( is_null( $p_default ) ) {
		return '';
	} else {
		$t_value = $p_default;
	}

	if ( strpos( $t_value, ' ' ) !== false ) {
		if ( strpos( $t_value, '"' ) !== false ) {
			$t_value = str_replace( '"', "'", $t_value );
		}

		$t_value = '"' . $t_value . '"';
	}

	$t_result .= $t_value;
	return $t_result;
}

function mantishub_log( $p_message ) {
	global $global_log_request_id;
	error_log( date( 'c' ) . ' ' . $global_log_request_id . ' ' . $p_message . "\n", 3, dirname( dirname( __FILE__ ) ) . '/logs/mantishub.log' );
}

/**
 * Get the domain for the instance with no dot infront of it.  If there is no
 * match, then mantishub.com is returned.
 *
 * @return string The hosting domain.
 */
function mantishub_get_instance_domain() {
	global $g_path, $g_mantishub_domains;

	foreach( $g_mantishub_domains as $t_domain ) {
		$t_dot_domain = '.' . $t_domain;

		$t_index = strpos( $g_path, $t_dot_domain );
		if ( $t_index != -1 ) {
			return $t_domain;
		}
	}

	return 'mantishub.com';
}

/**
 * Strip the hosting domain from the instance name.
 *
 * @param  string $p_url  The URL for the instance.
 * @return string The instance name without the hosting domain.
 */
function mantishub_strip_domain( $p_url ) {
	global $g_mantishub_domains;

	foreach ( $g_mantishub_domains as $t_domain ) {
		$t_dot_domain = '.' . $t_domain;

		$t_index = strpos( $p_url, $t_dot_domain );
		if ( $t_index != -1 ) {
			return substr( $p_url, 0, $t_index );
		}
	}

	return $p_url;
}

/**
 * Get the current instance name without the hosting domain.
 *
 * @return string The instance name.
 */
function mantishub_instance_name() {
	$t_path = config_get( 'path' );
	$t_path = str_ireplace( 'https://', '', $t_path );
	$t_path = str_ireplace( 'http://', '', $t_path );
	$t_path = trim( $t_path, '/' );

	$t_company_name = $t_path;

	if ( stristr( $t_company_name, 'localhost' ) !== false ) {
		$t_company_name = 'localhost';
	} else {
		$t_company_name = mantishub_strip_domain( $t_company_name );
	}

	return strtolower( $t_company_name );
}

function mantishub_is_manage_section() {
	return is_page_name( 'manage_' ) || is_page_name( 'logo_page' ) || is_page_name( 'adm_' );
}

function mantishub_support_widget() {
	if ( config_get( 'in_app_support_enabled' ) != OFF && mantishub_is_manage_section() ) {
		mantishub_zendesk();
	}
}

function mantishub_zendesk() {
	if ( auth_is_user_authenticated() ) {
		if ( current_user_is_administrator() ) {
			$t_user_email = current_user_get_field( 'email' );
			$t_realname = current_user_get_field( 'realname' );

			echo <<< HTML
					<!-- Support Widget -->
					<script>/*<![CDATA[*/window.zEmbed||function(e,t){var n,o,d,i,s,a=[],r=document.createElement("iframe");window.zEmbed=function(){a.push(arguments)},window.zE=window.zE||window.zEmbed,r.src="javascript:false",r.title="",r.role="presentation",(r.frameElement||r).style.cssText="display: none",d=document.getElementsByTagName("script"),d=d[d.length-1],d.parentNode.insertBefore(r,d),i=r.contentWindow,s=i.document;try{o=s}catch(c){n=document.domain,r.src='javascript:var d=document.open();d.domain="'+n+'";void(0);',o=s}o.open()._l=function(){var o=this.createElement("script");n&&(this.domain=n),o.id="js-iframe-async",o.src=e,this.t=+new Date,this.zendeskHost=t,this.zEQueue=a,this.body.appendChild(o)},o.write('<body onload="document._l();">'),o.close()}("//assets.zendesk.com/embeddable_framework/main.js","mantishub.zendesk.com");/*]]>*/</script>
<script>
  zE(function() {
    zE.identify( { name: '$t_realname', email: '$t_user_email' });
  });
</script>
					<!-- End of Support Widget -->
HTML;
		}
	}	
}

/**
 * Echos the intercom javascript calls with the appropriate MantisHub specific data.
 * It should be called just before the closing html body tag.
 */
function mantishub_intercom() {
	// If a page auto-refreshes itself then don't report that as activity.
	if ( isset( $_GET['refresh'] ) && $_GET['refresh'] == 'true' ) {
		return;
	}

	// MantisHub Intercom-IO
	if ( auth_is_user_authenticated() ) {
		$t_user_email = current_user_get_field( 'email' );

		if ( current_user_is_administrator() && stristr( $t_user_email, "@localhost" ) === false ) {
			// Use the database as the company id since it will never change.
			// The instance name may change due to renaming the instance or using custom domain.
			$t_company_id = config_get( 'database_name' );

			$t_company_name = mantishub_instance_name();
	 		$t_user_created = current_user_get_field( 'date_created' );
	 		$t_user_language = user_pref_get_language( auth_get_current_user_id() );
			$t_username = current_user_get_field( 'username' );
			$t_generation = config_get_global( 'mantishub_gen' );

			$t_security_token = 'Jfm5VSe9aRpM8dAtk9A8Ae5h6TxUnmcF_KFK5EX-';

			echo '<script id="IntercomSettingsScriptTag">';
			echo 'window.intercomSettings = {';
			echo '"user_hash": "' . hash_hmac( 'sha256', $t_user_email, $t_security_token ) . '",';
			echo 'email: "' . $t_user_email . '",';
			echo 'created_at: ' . $t_user_created . ',';
			echo '"username": "' . $t_username . '",';
			echo '"language": "' . $t_user_language . '",';
			echo '"company": {';
			echo 'id: "' . $t_company_id . '",';
			echo 'name: "' . $t_company_name . '",';
			echo '"ip": "' . $_SERVER['SERVER_ADDR'] . '",';
			echo '"gen": "' . $t_generation . '"';
			echo '},';
			echo 'app_id: "eb7d1d2171933b95f1ecb4fc4d1db866879776d2"';
			echo '}';
			echo '</script>';

			if ( $t_company_name !== 'localhost' && !mantishub_impersonation() ) {
				echo <<< HTML
					<script>(function(){var w=window;var ic=w.Intercom;if(typeof ic==="function"){ic('reattach_activator');ic('update',intercomSettings);}else{var d=document;var i=function(){i.c(arguments)};i.q=[];i.c=function(args){i.q.push(args)};w.Intercom=i;function l(){var s=d.createElement('script');s.type='text/javascript';s.async=true;s.src='https://static.intercomcdn.com/intercom.v1.js';var x=d.getElementsByTagName('script')[0];x.parentNode.insertBefore(s,x);}if(w.attachEvent){w.attachEvent('onload',l);}else{w.addEventListener('load',l,false);}}})()</script>
HTML;
			}
		}
	}	
}

function mantishub_team_users_list_info() {
	$t_info = array();

	$t_handle_bug_threshold = config_get( 'handle_bug_threshold' );
	if( is_array( $t_handle_bug_threshold ) ) {
		$t_min = ADMINISTRATOR;

		foreach( $t_handle_bug_threshold as $t_access_level ) {
			if( $t_access_level < $t_min ) {
				$t_min = $t_access_level;
			}
		}

		$t_handle_bug_threshold = $t_min;
	}

	$t_info['count'] = 0;
	$t_info['access_level'] = $t_handle_bug_threshold;

	# Count users that are enabled and can be assigned issues (based on their global access level).
	$t_query = "SELECT id, username FROM {user} WHERE access_level >= $t_handle_bug_threshold AND enabled=1";
	$t_result = db_query( $t_query );

	$t_info['global_users'] = array();
	$t_info['project_users'] = array();

	while ( $t_row = db_fetch_array( $t_result ) ) {
		$t_user_id = (int)$t_row['id'];
		$t_username = $t_row['username'];

		$t_info['global_users'][$t_user_id] = array( 'id' => $t_user_id, 'username' => $t_username );
	}

	# Count users that are enabled and can be assigned issues based on project specific access levels.
	$t_query2 = "SELECT DISTINCT(p.user_id) user_id, p.project_id project_id, u.username username FROM {project_user_list} p, {user} u WHERE p.user_id = u.id AND p.access_level >= $t_handle_bug_threshold AND u.enabled = 1";
	$t_result2 = db_query( $t_query2 );

	while ( $t_row = db_fetch_array( $t_result2 ) ) {
		$t_user_id = (int)$t_row['user_id'];
		$t_username = $t_row['username'];

		if ( !isset( $t_info['global_users'][$t_user_id] ) &&
				!isset( $t_info['project_users'][$t_user_id] ) ) {
			$t_project_id = $t_row['project_id'];
			$t_project_name = project_get_name( $t_project_id );
			$t_info['project_users'][$t_user_id] = array( 'id' => $t_user_id, 'username' => $t_username, 'project' => $t_project_name );
		}
	}

	$t_info['count'] = count( $t_info['global_users'] ) + count( $t_info['project_users'] );

	return $t_info;
}

function plan_team_count() {
	$t_result = mantishub_team_users_list_info();
	return $t_result['count'];
}

/**
 * Counts the number of rows in the specified table name.
 * The table name must be the output of calls to db_get_table().
 *
 * @param string $p_table table name without prefix/suffix
 * @param string $p_where null or condition.
 * @return int The number of rows.
 */
function mantishub_table_row_count( $p_table, $p_where = null ) {
	$t_query = "SELECT COUNT(*) FROM {" . $p_table . "}";
	if( $p_where !== null ) {
		$t_query .= ' WHERE ' . $p_where;
	}

	$t_result = db_query( $t_query );

	$t_count = db_result( $t_result );

	return (int)$t_count;
}

/**
 * Get the number of users with access level greater than or equal to the specified access level.
 * @param $p_access_level The access level, e.g. DEVELOPER.
 * @return int The number of users.
 */
function mantishub_user_count_has_access( $p_access_level ) {
	$query = "SELECT COUNT(*)
				  FROM {user}
				  WHERE access_level >= " . db_param();
	$result = db_query( $query, array( $p_access_level ) );

	return db_result( $result );
}

/**
 * Returns the last_updated timestamp for the last touched issue.
 * Used to determine when the instance was last used (approximately).
 * This method assumes that the instances has at least once issue.
 * @returns int unix timestamp, use strftime() to format it.
 */
function mantishub_last_issue_update() {
	$query = "SELECT last_updated FROM {bug} ORDER BY last_updated DESC";
	$result = db_query( $query, array(), /* one row */ 1 );
	$t_timestamp = db_result( $result );

	return $t_timestamp;
}

function mantishub_backup_folder() {
	return dirname( dirname( __FILE__ ) ) . '/backup/';
}

function mantishub_in_progress_file() {
	return mantishub_backup_folder() . 'in_progress';
}

function mantishub_backup_in_progress() {
	return file_exists( mantishub_in_progress_file() );
}

function mantishub_backup_data_file_name() {
	return 'mantishub_data.zip';
}

function mantishub_backup_data_file() {
	return mantishub_backup_folder() . mantishub_backup_data_file_name();
}

function mantishub_backup_attach_file_name() {
	return 'mantishub_attachments.zip';
}

function mantishub_backup_attach_file() {
	return mantishub_backup_folder() . mantishub_backup_attach_file_name();
}

function mantishub_client_ip() {
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}

	return $ip;
}

function mantishub_impersonation_email() {
	$t_email = config_get( 'mantishub_info_impersonation_email' );

	if ( !is_blank( $t_email ) ) {
		$t_subject = '[' . mantishub_instance_name() . '] Impersonation login';

		$t_message = '';
		$t_message .= 'IP: ' . mantishub_client_ip() . "\n";
		$t_message .= 'Timestamp: ' . date( 'Ymd Hi' ) . "\n";

		email_store( $t_email, $t_subject, $t_message );
		email_send_all();
	}
}

function mantishub_wrap_email( $p_issue_id, $p_message ) {
	$t_message = $p_message;
	$t_message .= sprintf( lang_get( 'mantishub_email_footer' ), 'http://support.mantishub.com' ) . "\n";
	return $t_message;
}

function mantishub_reply_to_address( $p_issue_id ) {
	if ( plan_mail_reporting() ) {
		$t_md5 = md5( $p_issue_id . config_get( 'crypto_master_salt' ) );
		return mantishub_instance_name() . '+' . $p_issue_id . '-' . $t_md5 . '@' . mantishub_get_instance_domain();
	}

	return null;
}

function mantishub_cleanup_plugin_name( $p_name ) {
	$t_name = string_display_line( $p_name );
	$t_name = str_replace( 'MantisBT ', '', $t_name );
	$t_name = str_replace( 'Mantis ', '', $t_name );
	return $t_name;
}

