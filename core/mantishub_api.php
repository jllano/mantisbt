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

// for log correlation.
$global_log_request_id = time();

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

function mantishub_top_message() {
	global $g_mantishub_info_trial;

	if ( $g_mantishub_info_trial && current_user_is_administrator() ) {
		$t_issues_count = mantishub_table_row_count( 'bug' );

		$t_trial_conversion_url = config_get( 'mantishub_info_trial_conversion_url', '' );
		if ( $t_issues_count >= 5 && !is_blank( $t_trial_conversion_url ) ) {
            echo '<div class="alert alert-warning padding-8">';
			echo '<strong><i class="ace-icon fa fa-flag"></i> Trial Version: </strong>';
            echo 'Click <a href="' . $t_trial_conversion_url . '" target="_blank">here</a> to convert to paid and enable daily backups.';
			echo '</div>';
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

	$t_project_id = project_get_id_by_name( $t_target_project_name );

	if ( $t_project_id == 0 ) {
		$t_projects = project_get_all_rows();
		$t_project_found = false;

		foreach ( $t_projects as $t_project ) {
			if ( mantishub_mailgun_project_name_clean( $t_project['name'] ) != $t_target_project_name ) {
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

function mantishub_log( $p_message ) {
	global $global_log_request_id;
	error_log( date( 'c' ) . ' ' . $global_log_request_id . ' ' . $p_message . "\n", 3, dirname( dirname( __FILE__ ) ) . '/logs/mantishub.log' );
}

function mantishub_instance_name() {
	$t_path = config_get( 'path' );
	$t_path = str_ireplace( 'https://', '', $t_path );
	$t_path = str_ireplace( 'http://', '', $t_path );

	$t_company_name = $t_path;

	if ( stristr( $t_company_name, 'localhost' ) !== false ) {
		$t_company_name = 'localhost';
	} else if ( stristr( $t_company_name, '.mantishub.com' ) !== false ) {
		$t_index = strpos( $t_company_name, '.mantishub.com' );
		$t_company_name = substr( $t_company_name, 0, $t_index );
	}

	return strtolower( $t_company_name );
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
			echo '"ip": "' . $_SERVER['SERVER_ADDR'] . '"';
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

/**
 * Counts the number of rows in the specified table name.
 * The table name must be the output of calls to db_get_table().
 */
function mantishub_table_row_count( $p_table ) {
	$query = "SELECT COUNT(*) FROM {" . $p_table . "}";
	$result = db_query( $query );
	$t_users = db_result( $result );

	return $t_users;
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

function mantishub_backup_data_file() {
	return mantishub_backup_folder() . 'mantishub_data.tar.gz';
}

function mantishub_backup_attach_file() {
	return mantishub_backup_folder() . 'mantishub_attachments.tar.gz';
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

	$t_subject = '[' . mantishub_instance_name() . '] Impersonation login';

	$t_message = '';
	$t_message .= 'IP: ' . mantishub_client_ip() . "\n";
	$t_message .= 'Timestamp: ' . date( 'Ymd Hi' ) . "\n";

	email_store( $t_email, $t_subject, $t_message );
	email_send_all();
}


