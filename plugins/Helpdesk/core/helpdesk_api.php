<?php
# Copyright (c) 2016 Victor Boctor @ MantisHub.com

require_api( 'collapse_api.php' );
require_api( 'mantishub_api.php' );

define( 'HELPDESK_GENERIC_USERNAME', 'Email' );

/**
 * Check if the specified text contains any of the mantishub domain.
 *
 * @param  string $p_text The text to check.
 * @return bool true: contains a domain, false: otherwise.
 */
function helpdesk_string_contains_domain( $p_text ) {
	global $g_mantishub_domains;

	foreach( $g_mantishub_domains as $t_domain ) {
		if ( stristr( $p_text, $t_domain ) !== false ) {
			return true;
		}
	}

	return false;
}

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
	$t_expected_md5 = md5( $t_id . config_get( 'crypto_master_salt' ) );

	if ( $t_md5 != $t_expected_md5 ) {
		helpdesk_log( 'incoming mail: received md5 = ' . $t_md5 );
		helpdesk_log( 'incoming mail: expected md5 = ' . $t_expected_md5 );

		$p_abort_error = "Invalid recipient address";
		return 0;
	}

	return $t_id;
}

function helpdesk_subject_for_issue( $p_issue_id ) {
	$t_issue = bug_get( $p_issue_id );
	$t_project_name = project_get_name( $t_issue->project_id );
	$t_subject = "[{$t_project_name} {$p_issue_id}]: {$t_issue->summary}";
	return $t_subject;
}

function helpdesk_url_for_issue( $p_issue_id ) {
	return config_get( 'path' ) . 'view.php?id=' . $p_issue_id;
}

function helpdesk_headers_for_issue( $p_issue_id ) {
	$t_mail_headers = array();
	$t_reply_to = mantishub_reply_to_address( $p_issue_id );
	if ( $t_reply_to !== null ) {
		$t_mail_headers['Reply-To'] = $t_reply_to;
	}

	$t_issue = bug_get( $p_issue_id );

	$t_message_md5 = md5( $p_issue_id . $t_issue->date_submitted );
	$t_mail_headers['In-Reply-To'] = $t_message_md5;

	return $t_mail_headers;
}

function helpdesk_get_email_from_name_email( $p_name_email ) {
	$t_start_pos = stripos( $p_name_email, '<' );
	$t_end_pos = stripos( $p_name_email, '>' );

	if ( $t_start_pos === false || $t_end_pos === false || $t_start_pos >= $t_end_pos ) {
		return $p_name_email;
	}

	return substr( $p_name_email, $t_start_pos + 1, $t_end_pos - $t_start_pos - 1 );
}

/**
 * Adds the reporter of the issue to the db.
 *
 * @param  int $p_issue_id         The issue id
 * @param  string $p_email_address The name and email address.
 * @return int|bool                The row id or false if duplicate.
 */
function helpdesk_add_user_to_issue( $p_issue_id, $p_email_address ) {
	$t_recipients = helpdesk_users_for_issue( $p_issue_id );

	foreach( $t_recipients as $t_recipient ) {
		if ( $t_recipient == $p_email_address ) {
			return false;
		}
	}

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

function helpdesk_create_generic_user() {
	if ( helpdesk_generic_user_id() === false ) {
		$t_reporter_access = config_get( 'report_bug_threshold' );

		# Create the email user
		user_create(
			HELPDESK_GENERIC_USERNAME,
			auth_generate_random_password(),
			'',
			$t_reporter_access,
			true,			# Protected
			true,			# Enabled
			'' );           # Real name

		# Add explicit reporter access when necessary
		$t_projects = project_get_all_rows();
		$t_user_id = helpdesk_generic_user_id();
		foreach( $t_projects as $t_project ) {
			$t_project_id = $t_project['id'];
			if ( !access_has_project_level( $t_reporter_access, $t_project_id, $t_user_id ) ) {
				project_add_user( $t_project_id, $t_user_id, $t_reporter_access );
			}
		}
	}
}

function helpdesk_generic_user_id() {
	return user_get_id_by_name( HELPDESK_GENERIC_USERNAME );
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

	$t_recipients_for_display = array();
	foreach( $t_users as $t_from ) {
		$t_recipients_for_display[] = string_display( $t_from );
	}

	echo '<div id="helpdesk" style="padding: 0px; margin-top: 20px; display: none;">';
	collapse_open( 'helpdesk' );
?>
	<div class="col-md-12 col-xs-12">
		<div class="space-10"></div>
		<div class="widget-box widget-color-blue2" id="changesets">
			<div class="widget-header widget-header-small">
				<h4 class="widget-title lighter">
					<i class="ace-icon fa fa-medkit"></i>
					<?php echo plugin_lang_get( 'helpdesk_title' ) ?>
				</h4>
				<div class="widget-toolbar">
					<a href="#" data-action="collapse">
						<i class="1 ace-icon fa-chevron-down fa bigger-125"></i>
					</a>
				</div>
			</div>
			<div class="widget-body">
				<div class="table-responsive">
					<table class="table table-bordered table-striped table-condensed no-margin">
						<tr>
							<td class="category" width="15%">
								<?php echo plugin_lang_get( 'customers' ); ?>
							</td>
							<td>
								<?php echo implode( '<br />', $t_recipients_for_display ); ?>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>
	</div>
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
