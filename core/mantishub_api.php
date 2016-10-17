<?php
/**
 * @package MantisHub
 * @copyright Copyright (C) Victor Boctor - vboctor@gmail.com
 * @link http://www.mantishub.com
 */

/**
 * MantisBT Core API's
 */
require_once( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'core.php' );
require_api( 'plugin_api.php' );

$g_mantishub_domains = array( 'mantishub.com', 'mantishub.io' );

# MantisHub Guide Steps
define( 'MANTISHUB_GUIDE_PROJECT',  1 );
define( 'MANTISHUB_GUIDE_CATEGORY', 2 );
define( 'MANTISHUB_GUIDE_BUG',      3 );
define( 'MANTISHUB_GUIDE_USER',     4 );

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

/**
 * Gets the current guide step or false if user has done the steps outlined
 * in the getting started guide and hence it shouldn't be shown.
 *
 * @return false if guide is done, otherwise guide step.
 */
function mantishub_guide_stage() {
	global $g_mantishub_info_trial;

	if ( $g_mantishub_info_trial && current_user_is_administrator() ) {
		if ( mantishub_table_row_count( 'project' ) == 0 ) {
			$t_active_step = MANTISHUB_GUIDE_PROJECT;
		} else if ( mantishub_table_row_count( 'category' ) == 1 ) {
			$t_active_step = MANTISHUB_GUIDE_CATEGORY;
		} else if ( mantishub_table_row_count( 'bug' ) == 0 ) {
			$t_active_step = MANTISHUB_GUIDE_BUG;
		} else if ( mantishub_table_row_count( 'user' ) == 1 ) {
			$t_active_step = MANTISHUB_GUIDE_USER;
		} else {
			$t_active_step = false;
		}
	} else {
		$t_active_step = false;
	}

	return $t_active_step;
}

/**
 * Display instance-wide messages just under the navbar
 * @returns null
 */
function mantishub_show_messages() {

	mantishub_announcements();

	mantishub_trial_message();
}

function mantishub_trial_message() {
	global $g_mantishub_info_trial, $g_mantishub_info_payment_on_file;

	if ( $g_mantishub_info_trial && !$g_mantishub_info_payment_on_file && current_user_is_administrator() ) {
		$t_issues_count = mantishub_table_row_count( 'bug' );

		$t_trial_conversion_url = config_get( 'mantishub_info_trial_conversion_url', '' );
		if ( $t_issues_count >= 5 && !is_blank( $t_trial_conversion_url ) ) {
			echo '<div class="alert alert-warning padding-8 no-margin">';
			echo '<strong><i class="ace-icon fa fa-flag-checkered fa-lg"></i> Trial Version: </strong>';
			echo 'Click <a href="' . $t_trial_conversion_url . '" target="_blank">here</a> to convert to paid and enable daily backups.';
			echo '</div>';
		}
	}
}

function mantishub_announcements() {
	global $g_config_path;

	try {
		$t_messages_file_path = $g_config_path . 'mantishub_config.json';
		if( file_exists( $t_messages_file_path ) ) {
			# warm up the cache if needed
			mantishub_cache_dismissed_blocks();

			$str = file_get_contents( $t_messages_file_path );
			$json = json_decode($str, true);

			foreach ( $json['announcements'] as  $message ) {
				if ( isset( $message['access_level'] ) && $message['access_level'] > current_user_get_access_level() ) {
					continue;
				}

				$t_show = !mantishub_is_dismissed_block( $message['id'] );
				$t_now = time();
				if( $t_show
					&& ( $t_now >= strtotime( $message['valid_from'] ) )
					&& ( $t_now <= strtotime( $message['valid_until'] ) ) ) {

					echo '<div id="' . $message['id'] . '" class="alert alert-warning padding-8 no-margin">';
					if ( isset( $message['dismissable'] ) && $message['dismissable'] === true ) {
						echo '<a data-dismiss="alert" class="close" type="button" href="#">';
						echo '<i class="ace-icon fa fa-times bigger-125"></i> ';
						echo '</a>';
					}
					echo '<i class="ace-icon fa fa-lg ' . $message['icon'] . '"></i> ' . $message['text'];
					echo '</div>';
				}
			}
		} else {
			# clear dismissed blocks cache
			mantishub_clear_dismissed_blocks_cache();
		}
	}
	catch ( Exception $e ) {
		log_event( LOG_ALL, "Processing announcements file failed " . $e->ErrorInfo );
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

		$t_index = stripos( $g_path, $t_dot_domain );
		if ( $t_index !== false ) {
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

		$t_index = stripos( $p_url, $t_dot_domain );
		if ( $t_index !== false ) {
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

/**
 * Print navbar help menu at the top right of the page
 * @return null
 */
function mantishub_navbar_help_menu() {
	$t_support_url = config_get_global( 'mantishub_support_url' );

	echo '<li class="grey">';
	echo '<a id="help-widget" href="', $t_support_url, '" target="blank">';
	echo '<i class="ace-icon fa fa fa-question bigger-150"></i>';
	echo '</a>';
	echo '</li>';
}

function mantishub_team_users_list_info() {
	$t_info = array();

	$t_handle_bug_threshold = plan_access_level_to_charge();

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

function mantishub_upgrade_unattended() {
	mantishub_install_plugin( 'Helpdesk' );

	$t_plugins = plugin_find_all();
	uasort( $t_plugins,
		function ( $p_p1, $p_p2 ) {
			return strcasecmp( $p_p1->name, $p_p2->name );
		}
	);

	foreach( $t_plugins as $t_plugin ) {
		if ( plugin_is_installed( $t_plugin->basename ) ) {
			if ( plugin_needs_upgrade( $t_plugin ) ) {
				echo "{$t_plugin->basename}: upgrading schema...\n";
				if ( !plugin_upgrade( $t_plugin ) ) {
					echo "{$t_plugin->basename}: upgrade failed...\n";
					return false;
				}
			} else {
				echo "{$t_plugin->basename}: schema up-to-date.\n";
			}
		}
	}

	return true;
}

function mantishub_install_plugin( $p_plugin_name ) {
	$t_plugin = plugin_register( $p_plugin_name, true );

	if ( !plugin_is_installed( $p_plugin_name ) ) {
		plugin_install( $t_plugin );
	}
}

/**
 * Determine if a block should not be rendered into the page since
 * it has been dismissed by the user
 * @param string $p_block_id block element id.
 * @return boolean
 */
function mantishub_is_dismissed_block( $p_block_id ) {
	global $g_dismissed_blocks_cache;

	if( !isset( $g_dismissed_blocks_cache[$p_block_id] ) ) {
		return false;
	}

	return( true == $g_dismissed_blocks_cache[$p_block_id] );
}

/**
 * Read dismiss cookie and cache it in global variable
 * @return void
 */
function mantishub_cache_dismissed_blocks() {
	global $g_dismissed_blocks_cache;

	if( !auth_is_user_authenticated() || current_user_is_anonymous() ) {
		$g_dismissed_blocks_cache = array();
		return;
	}

	if( isset( $g_dismissed_blocks_cache ) ) {
		return;
	}

	$t_data = array();
	$t_data['filter'] = false;
	$g_dismissed_blocks_cache = $t_data;

	$t_cookie = gpc_get_cookie( 'MANTIS_HUB_dismissed_blocks', '' );

	if( false !== $t_cookie && !is_blank( $t_cookie ) ) {
		$t_data = explode( '|', $t_cookie );

		foreach( $t_data as $t_pair ) {
			$t_pair = explode( ',', $t_pair );

			if( false !== $t_pair && count( $t_pair ) == 2 ) {
				$g_dismissed_blocks_cache[$t_pair[0]] = ( true == $t_pair[1] );
			}
		}
	}
}

/**
 * Clear cookie of dismissed bloc cache
 * @return void
 */
function mantishub_clear_dismissed_blocks_cache() {
	gpc_clear_cookie('MANTIS_collapse_settings');
}

