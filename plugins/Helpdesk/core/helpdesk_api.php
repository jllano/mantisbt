<?php
# Copyright (c) 2016 Victor Boctor @ MantisHub.com

require_once( 'core.php' );
require_api( 'collapse_api.php' );
require_api( 'mantishub_api.php' );

function helpdesk_log( $p_message ) {
	global $global_log_request_id;
	log_event( LOG_EMAIL, $global_log_request_id . ' ' . $p_message );
}

/**
 * Clean project name to be part of the email address.
 * instance+project@mantishub.net
 *
 * Used in creating mailgun routes.
 */
function helpdesk_project_name_clean( $p_project_name ) {
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

function helpdesk_project_get_row_by_clean_name( $p_project_name ) {
	$t_project_id = project_get_id_by_name( $p_project_name );

	if ( $t_project_id == 0 ) {
		$t_projects = project_get_all_rows();
		$t_project_found = false;

		foreach ( $t_projects as $t_project ) {
			if ( helpdesk_project_name_clean( $t_project['name'] ) != $p_project_name ) {
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
function helpdesk_project_from_recipient( $p_recipient ) {
	$t_instance_name = mantishub_instance_name();
	$t_instance_name = strtolower( $t_instance_name );

	$t_target_project_name = strtolower( $p_recipient );
	$t_target_project_name = substr( $t_target_project_name, 0, strpos( $t_target_project_name, '@' ) );

	// If instancename@domain.com, then return false since project is not specified.
	if ( $t_target_project_name == $t_instance_name ) {
		return false;
	}

	$t_target_project_name = str_replace( $t_instance_name . '+', '', $t_target_project_name );

	return helpdesk_project_get_row_by_clean_name( $t_target_project_name );
}

function helpdesk_issue_from_recipient( $p_recipient, &$p_abort_error ) {
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
	$t_project_row = helpdesk_project_get_row_by_clean_name( $t_between_plus_and_at );
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

	helpdesk_log( 'incoming mail: issue id = ' . $t_id );

	if ( !bug_exists( $t_id ) ) {
		$p_abort_error = "issue $t_id doesn't exist.";
		return 0;
	}

	$t_md5 = $t_parts[1];
	helpdesk_log( 'incoming mail: received md5 = ' . $t_md5 );

	$t_expected_md5 = md5( $t_id . config_get( 'crypto_master_salt' ) );
	helpdesk_log( 'incoming mail: expected md5 = ' . $t_expected_md5 );

	if ( $t_md5 != $t_expected_md5 ) {
		$p_abort_error = "Invalid recipient address";
		return 0;
	}

	return $t_id;
}

function helpdesk_add_user_to_issue( $p_issue_id, $p_email_address ) {
	$t_recipients_table = plugin_table( 'recipients' );

	$t_query = "INSERT INTO {$t_recipients_table}
		( bug_id, bugnote_id, email, extra )
		VALUES
		(
			" . db_param() . ",
			" . db_param() . ",
			" . db_param() . ",
			" . db_param() . "
		)";

	db_query( $t_query, array(
		$p_issue_id,		# bug_id
		0,					# bugnote_id
		$p_email_address,	# reporter
		'{}'				# extras
	));

	return db_insert_id( $t_recipients_table );
}

function helpdesk_users_from_additional_information( $p_additional_information ) {
	# If additional info follows the format below, extract the reporter email address.
	# 'MantisHub Email Delivery From: ' . $f_from_name_email;

	$t_prefix = 'MantisHub Email Delivery From: ';
	if ( strpos( $p_additional_information, $t_prefix ) !== 0 ) {
		return false;
	}

	return substr( $p_additional_information, strlen( $t_prefix ) );
}

function helpdesk_users_for_issue( $p_issue_id ) {
	$t_recipients_table = plugin_table( 'recipients' );
	$t_query = "SELECT * FROM {$t_recipients_table} WHERE bug_id=" . db_param();
	$t_result = db_query( $t_query, array( (int)$p_issue_id ) );

	$t_recipients = array();

	while ( ( $t_row = db_fetch_array( $t_result ) ) !== false ) {
		$t_recipients[] = $t_row['email'];
	}

	$t_bug = bug_get( $p_issue_id, true );
	$t_recipient_legacy = helpdesk_users_from_additional_information( $t_bug->additional_information );
	if ( $t_recipient_legacy !== false ) {
		$t_recipients[] = $t_recipient_legacy;
	}

	return $t_recipients;
}

function helpdesk_print_issue_view_info( $p_issue_id ) {
	$t_users = helpdesk_users_for_issue( $p_issue_id );
	if ( empty( $t_users ) ) {
		return;
	}

	echo '<div id="helpdesk" style="padding: 0px; margin-top: 20px; display: none;">';
	collapse_open( 'helpdesk' );
?>
<table class="width100" cellspacing="1">
<tr class="row-2">
	<td width="15%" class="form-title" colspan="2"><?php
		collapse_icon( 'helpdesk' );
		echo plugin_lang_get( 'helpdesk_title' ); ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="15%">
		<?php echo plugin_lang_get( 'customers' ); ?>
	</td>
	<td>
		<?php echo implode( '<br />', $t_users ); ?>
	</td>
</tr>
</table>
<?php collapse_closed( 'helpdesk' ); ?>
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title"><?php
		collapse_icon( 'helpdesk' );
		echo plugin_lang_get( 'helpdesk_title' ); ?>
	</td>
</tr>
</table>
<?php
	collapse_end( 'helpdesk' );
?>
<script type="text/javascript">
	$(document).ready(function() {
		$('#helpdesk').insertAfter('#view-issue-details');
		$('#helpdesk').show();
	});
</script>
</div>
<?php
}
