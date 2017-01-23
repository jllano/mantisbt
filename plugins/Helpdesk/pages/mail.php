<?php
# Copyright (c) 2016 Victor Boctor @ MantisHub.com

/**
 * MantisBT Core API's
 */

require_once( 'core.php' );
require_once( dirname( dirname( __FILE__ ) ) . '/core/helpdesk_api.php' );
require_api( 'access_api.php' );
require_api( 'bug_api.php' );
require_api( 'email_api.php' );
require_api( 'event_api.php' );
require_api( 'file_api.php' );
require_api( 'helper_api.php' );
require_api( 'project_api.php' );

$f_subject = trim( gpc_get_string( 'subject', '' ) );
$f_from_name_email = gpc_get_string( 'from' );
$t_from_email = helpdesk_get_email_from_name_email( $f_from_name_email );

$f_additional_recipients_headers = gpc_get_string( 'To' ) . ',' . gpc_get_string( 'Cc' );
$t_additional_recipients = mantishub_collect_additional_recipients($f_additional_recipients_headers );

$f_message_headers = gpc_get_string( 'message-headers' );
$t_headers = json_decode( $f_message_headers );

$t_message_id = mantishub_get_header( $t_headers, 'Message-Id' );
$f_auto_response_suppress = mantishub_get_header($t_headers, 'X-Auto-Response-Suppress' );
$t_issue_hash = mantishub_get_header( $t_headers, 'X-MantisHub-Hash' );

$t_mail_error_params = array( $t_from_email, $f_subject, $f_auto_response_suppress );

# Check for loopback
if ( helpdesk_string_contains_domain( $t_message_id ) ) {
	header( 'HTTP/1.0 406 Loop detected' );
	$t_event = array( 'level' => 'error', 'comp' => 'email_reporting', 'event' => 'loop' );
	mantishub_event( $t_event );
	exit;
}

function mantishub_collect_additional_recipients( $f_additional_recipients_headers ) {
	$t_emails = array();

	$t_parsed_emails = array_filter( explode( ',', gpc_get_string( $f_additional_recipients_headers ) ) );
	$t_emails += array_filter(
		array_map(
			'helpdesk_get_email_from_name_email',
			array_map(
				'trim',
				$t_parsed_emails
			)
		),
		function( $t_email ) {
			return !preg_match( '#@mantishub.(io|com)$#', $t_email );
		}
	);

	return array_unique( $t_emails );
}

function mantishub_get_header( $p_headers, $p_header_name ) {
	foreach( $p_headers as $t_header ) {
		if ( strcasecmp( $t_header[0], $p_header_name ) === 0 ) {
			return $t_header[1];
		}
	}

	return null;
}

function mantishub_email_new_issue_success( $p_issue_id, $p_message, $p_from_email, $p_auto_response_suppress ) {
	# Don't respond if message suppresses auto-response to avoid loop back.
	if ( $p_auto_response_suppress == 'All' )
	{
		return;
	}

	$t_mail_headers = helpdesk_headers_for_issue( $p_issue_id );
	$t_subject = helpdesk_subject_for_issue( $p_issue_id );

	$t_message = HelpdeskPlugin::get_reply_above();

	$t_message .= $p_message . "\n\n";

	$t_message .= "---\n";

	$t_message .= HelpdeskPlugin::construct_mail_rollback_issue_signature( $p_issue_id );

	$t_message = mantishub_wrap_email( $p_issue_id, $t_message );

	email_store($p_from_email, $t_subject, $t_message, $t_mail_headers );
	log_event( LOG_EMAIL, sprintf('Incoming Mail API response to = \'%s\'', $p_from_email ) );

	if( OFF == config_get( 'email_send_using_cronjob' ) ) {
		email_send_all();
	}
}

function mantishub_email_error( $p_error_message, $p_parameters ) {
	list($t_from_email, $t_subject, $t_auto_response_suppress) = $p_parameters;

	# Don't respond if message suppresses auto-response to avoid loop back.
	if ( $t_auto_response_suppress == 'All' )
	{
		return;
	}

	$t_message = str_replace( '{error}', $p_error_message, plugin_config_get( 'failed_message' ) ) . "\n\n";
	$t_message .= plugin_lang_get( 'documentation_at' ) . "\n";
	$t_message .= "http://support.mantishub.com/hc/en-us/articles/204273585\n";

	if( empty( $t_subject ) ) {
		$t_subject = '-empty subject-';
	}

	email_store($t_from_email, 'RE: ' . $t_subject, $t_message );
	log_event( LOG_EMAIL, sprintf('Incoming Mail API response to = \'%s\'', $t_from_email ) );

	if( OFF == config_get( 'email_send_using_cronjob' ) ) {
		email_send_all();
	}
}

function add_more_recepients_to_issue($p_bug_id, $p_additional_recepients) {
	if( !empty( $p_additional_recepients ) ) {
		foreach ( $p_additional_recepients as $t_recepient_email ) {
			helpdesk_add_user_to_issue( $p_bug_id, $t_recepient_email );
		}
	}
}

$t_event = array( 'comp' => 'email_reporting', 'event' => 'receiving_email', 'subject' => ( empty( $f_subject ) ? '<blank>' : $f_subject ), 'post' => var_export( $_POST, true ) );
mantishub_event( $t_event );

if ( empty( $f_subject ) ) {
	header( 'HTTP/1.0 406 Empty Subject' );
	$t_event = array( 'level' => 'error', 'comp' => 'email_reporting', 'event' => 'empty_subject' );
	mantishub_event( $t_event );
	mantishub_email_error( 'Message rejected due to empty subject.', $t_parameters );
	exit;
}

if ( plugin_config_get( 'enabled' ) == OFF ) {
	header( 'HTTP/1.0 406 Email Reporting Disabled' );
	$t_event = array( 'level' => 'error', 'comp' => 'email_reporting', 'event' => 'feature_disabled' );
	mantishub_event( $t_event );
	mantishub_email_error( 'Message rejected since incoming email reporting feature is disabled.', $t_parameters );
	exit;
}

#
# Mail Reporting only available for Gold Plan
#

if ( !plan_mail_reporting() ) {
	header( 'HTTP/1.0 406 Email reporting not enabled for your plan.' );
	$t_event = array( 'level' => 'error', 'comp' => 'email_reporting', 'event' => 'upgrade_plan' );
	mantishub_event( $t_event );
	mantishub_email_error( 'Message rejected since email reporting is not enabled for your plan.', $t_parameters );
	exit;
}

#
# Authenticate that request is sent from mailgun.
#

$f_timestamp = gpc_get_int( 'timestamp' );
$f_token = gpc_get_string( 'token' );
$f_signature = gpc_get_string( 'signature' );

$t_key = plugin_config_get( 'mailgun_key' );
$t_data = $f_timestamp . $f_token;
$t_hash = hash_hmac ( 'sha256', $t_data, $t_key );

if ( $t_hash != $f_signature ) {
	header( 'HTTP/1.0 406 Invalid Signature' );
	$t_event = array( 'level' => 'error', 'comp' => 'email_reporting', 'event' => 'invalid_signature' );
	mantishub_event( $t_event );
	mantishub_email_error( "Message rejected since it didn't go through standard mail gateway.", $t_parameters );
	exit;
}

#
# Make sure it is not spam.
#

$f_spam = gpc_get_string( 'X-Mailgun-Sflag', 'No' );
if ( $f_spam == 'Yes' ) {
	header( 'HTTP/1.0 406 Mail Marked as Spam' );
	$t_event = array( 'level' => 'error', 'comp' => 'email_reporting', 'event' => 'spam' );
	mantishub_event( $t_event );
	exit;
}

#
# Retrieve sender (reporter) information.
#

$t_user_id = user_get_id_by_email($t_from_email );
if ( $t_user_id === false ) {
	$t_user_id = helpdesk_generic_user_id();
	$t_generic_user = true;
} else {
	$t_generic_user = false;
}

if ( $t_user_id === false ) {
	header( 'HTTP/1.0 406 No Reporter Match' );
	$t_event = array( 'level' => 'error', 'comp' => 'email_reporting', 'event' => 'reporter_not_found', 'sender' => $t_from_email );
	mantishub_event( $t_event );
	mantishub_email_error( 'Message rejected since there is no email account matching sender account.  There is also no fallback "email" user account.', $t_parameters );
	exit;
}

$t_user_id = (int)$t_user_id;

$t_reporter_username = user_get_field( $t_user_id, 'username' );
auth_attempt_script_login( $t_reporter_username );

$t_event = array( 'comp' => 'email_reporting', 'event' => 'reporter_identified', 'user_id' => $t_user_id, 'username' => $t_reporter_username, 'generic_user' => $t_generic_user );
mantishub_event( $t_event );

$f_recipient = gpc_get_string( 'recipient' );

$t_abort_error = '';

$f_body_plain = trim( gpc_get_string( 'body-plain', '' ) );

$t_bug_id = helpdesk_issue_from_recipient( $f_recipient, $t_abort_error );
if( $t_bug_id == 0 ) {
	if( !empty( $t_issue_hash ) ) {
		$t_bug_id = helpdesk_issue_from_recipient( $t_issue_hash );
	} else {
		$t_bug_id = helpdesk_issue_from_mail_body( $f_body_plain, $t_abort_error);
	}
}


if ( $t_bug_id == 0 && !is_blank( $t_abort_error ) ) {
	header( 'HTTP/1.0 406 ' . $t_abort_error );
	$t_event = array( 'level' => 'error', 'comp' => 'email_reporting', 'event' => 'issue_error', 'msg' => $t_abort_error );
	mantishub_event( $t_event );
	mantishub_email_error( 'Message rejected.  Error: ' . $t_abort_error, $t_parameters );
	exit;
}

$t_new_issue = $t_bug_id == 0;

if ( $t_new_issue ) {
	#
	# Get project name.
	#

	$t_instance_name = mantishub_instance_name();
	if ( stripos( $f_recipient, $t_instance_name . '+' ) !== 0 &&
		stripos( $f_recipient, $t_instance_name . '@' ) !== 0 ) {
		header( 'HTTP/1.0 406 Wrong Instance' );
		$t_event = array( 'level' => 'error', 'event' => 'no_route', 'recipient' => $f_recipient );
		mantishub_event( $t_event );
		mantishub_email_error( "Message rejected since there is no matching MantisHub.", $t_parameters );
		exit;
	}

	$t_project = helpdesk_project_from_recipient( $f_recipient );
	if ( $t_project === false ) {
		$t_default_project_id = plugin_config_get( 'default_project' );
		if ( $t_default_project_id == 0 ) {
			$t_default_project_id = user_pref_get_pref( $t_user_id, 'default_project' );
			if ( $t_default_project_id != 0 ) {
				$t_event = array( 'level' => 'info', 'comp' => 'email_reporting', 'event' => 'fallback_to_user_default_project' );
				mantishub_event( $t_event );
			}
		}

		if ( $t_default_project_id != 0 ) {
			$t_project = project_get_row( $t_default_project_id );
		} else {
			header( 'HTTP/1.0 406 No Default or Selected Project' );
			$t_event = array( 'level' => 'error', 'comp' => 'email_reporting', 'event' => 'missing_project' );
			mantishub_event( $t_event );
			mantishub_email_error( 'Message rejected since there is no selected or default project.', $t_parameters );
			exit;
		}
	}
} else {
	if ( !bug_exists( $t_bug_id ) ) {
		header( 'HTTP/1.0 406 Issue no longer exists' );
		$t_event = array( 'level' => 'error', 'comp' => 'email_reporting', 'event' => 'issue_does_not_exist' );
		mantishub_event( $t_event );
		mantishub_email_error( 'Issue ' . $t_bug_id . ' no longer exists.', $t_parameters );
		exit;
	}

	$t_project_id = bug_get_field( $t_bug_id, 'project_id' );
	$t_project = project_get_row( $t_project_id );
}

#
# Verify user has REPORTER access to project.
#
if ( !access_has_project_level( REPORTER, (int)$t_project['id'], $t_user_id ) ) {
	header( 'HTTP/1.0 406 Reporter unauthorized to Report Issues' );
	$t_event = array( 'level' => 'error', 'comp' => 'email_reporting', 'event' => 'unauthorized_reporter', 'project_id' => $t_project['id'], 'user_id' => $t_user_id );
	mantishub_event( $t_event );
	mantishub_email_error( "Message rejected since user doesn't have access to report issues.", $t_parameters );
	exit;
}

#
# Create an issue based on email information
#

$f_attachment_count = gpc_get_int( 'attachment-count', 0 );

if ( $t_new_issue ) {
	if ( empty( $f_body_plain ) ) {
		$f_body_plain = $f_subject;
	}

	$t_description = helpdesk_trim_body_based_on_marker($f_body_plain );
	if ( $t_generic_user ) {
		$t_description .= "\n\n---\n" . $f_from_name_email;
	}

	$t_bug = new BugData;
	$t_bug->summary = $f_subject;
	$t_bug->description = $t_description;
	$t_bug->project_id = (int)$t_project['id'];
	$t_bug->reporter_id = $t_user_id;

	# Make sure that we have a valid category otherwise the bug->create will fail silently with
	# success http status.  Will use default category (if exists), otherwise, default mover category
	# otherwise no category if allowed.  If all fails, fail with a proper error code.
	if ( !category_exists( $t_bug->category_id ) ) {
		$t_bug->category_id = config_get( 'default_category_for_moves' );

		if ( !category_exists( $t_bug->category_id ) ) {
			if ( config_get( 'allow_no_category' ) == ON ) {
				$t_bug->category_id = 0;
			} else {
				header( 'HTTP/1.0 406 Default category not found' );
				$t_event = array( 'level' => 'error', 'comp' => 'email_reporting', 'event' => 'default_category_not_found' );
				mantishub_event( $t_event );
				mantishub_email_error( "Message rejected since default category wasn't found.", $t_parameters );
				exit;
			}
		}
	}

	$t_bug_id = $t_bug->create();

	if ( $t_generic_user ) {
		helpdesk_add_user_to_issue( $t_bug_id, $f_from_name_email );
	}

	add_more_recepients_to_issue($t_bug_id, $t_additional_recipients );

	$t_event = array( 'comp' => 'email_reporting', 'event' => 'creating_issue', 'issue_id' => $t_bug_id, 'file_count' => $f_attachment_count );
	mantishub_event( $t_event );
} else {
	$f_stripped_text = trim( gpc_get_string( 'stripped-text', '' ) );

	# mailgun returns stripped text terminated with >
	if ( substr( $f_stripped_text, -1, 1 ) == '>' ) {
		$f_stripped_text = substr( $f_stripped_text, 0, strlen( $f_stripped_text ) - 1 );
		$f_stripped_text = trim( $f_stripped_text );
	}

	if ( is_blank( $f_stripped_text ) ) {
		header( 'HTTP/1.0 406 Blank note in reply to issue notification' );
		$t_event = array( 'level' => 'error', 'comp' => 'email_reporting', 'event' => 'empty_note' );
		mantishub_event( $t_event );
		mantsihub_email_error( "Message rejected since it has an empty note.", $t_parameters );
		exit;
	}

	$t_note_text = $f_stripped_text;

	if ( $t_generic_user ) {
		$t_note_text .= "\n\n---\n" . $f_from_name_email;
		helpdesk_add_user_to_issue( $t_bug_id, $f_from_name_email );
	}

	$t_note_added = false;

	# If a user adds a note to an issue that was resolved/closed and user doesn't have access to update
	# a read-only issue, then re-open the issue.  Otherwise, just add a note.
	if( bug_is_resolved( $t_bug_id ) ) {
		if( !access_has_bug_level( config_get( 'update_readonly_bug_threshold' ), $t_bug_id, $t_user_id ) ) {
			$t_event = array( 'comp' => 'email_reporting', 'event' => 'reopening_issue', 'issue_id' => $t_bug_id );
			mantishub_event( $t_event );

			bug_reopen( $t_bug_id, $t_note_text );

			$t_event = array( 'level' => 'info', 'comp' => 'email_reporting', 'event' => 'reopened_issue' );
			mantishub_event( $t_event );

			$t_note_added = true;
		}
	}

	if ( !$t_note_added ) {
		$t_event = array( 'comp' => 'email_reporting', 'event' => 'adding_note', 'issue_id' => $t_bug_id );
		mantishub_event( $t_event );

		$t_note_id = bugnote_add( $t_bug_id, $t_note_text );

		$t_event = array( 'comp' => 'email_reporting', 'event' => 'added_note', 'issue_id' => $t_bug_id, 'note_id' => $t_note_id );
		mantishub_event( $t_event );
	}

	add_more_recepients_to_issue($t_bug_id, $t_additional_recipients );
}

for ( $i = 1; $i <= (int)$f_attachment_count; ++$i ) {
	$t_file = $_FILES['attachment-' . $i];
	file_add( $t_bug_id, $t_file, 'bug', '', '', $t_user_id );

	$t_event = array( 'comp' => 'email_reporting', 'event' => 'attached_file', 'issue' => $t_bug_id, 'filename' => $t_file['name'] );
	mantishub_event( $t_event );
}

if ( $t_new_issue ) {
	helper_call_custom_function( 'issue_create_notify', array( $t_bug_id ) );

	email_generic( $t_bug_id, 'new', 'email_notification_title_for_action_bug_submitted' );

	$t_message = plugin_config_get( 'issue_reported_message' ) . "\n\n";
	$t_message = str_replace( '{issue_id}', $t_bug_id, $t_message );
	$t_message .= $f_body_plain;

	mantishub_email_new_issue_success( $t_bug_id, $t_message, $t_from_email, $f_auto_response_suppress );

	$t_event = array( 'comp' => 'email_reporting', 'event' => 'issue_reported' );
	mantishub_event( $t_event );

	# Allow plugins to post-process bug data with the new bug ID
	# Call this after all native work just in case plugins cause a failure.
	event_signal( 'EVENT_REPORT_BUG', array( $t_bug_data, $t_bug_id ) );
} else {
	# TODO: copied from bugnote_add.php -- should be part of bugnote_api.php instead.bugnote_add instead.
	if( config_get( 'reassign_on_feedback' ) ) {
		$t_bug = bug_get( $t_bug_id );

		if( $t_bug->status === config_get( 'bug_feedback_status' ) &&
			$t_bug->handler_id !== $t_user_id &&
			$t_bug->reporter_id === $t_user_id ) {
			if( $t_bug->handler_id !== NO_USER ) {
				bug_set_field( $t_bug_id, 'status', config_get( 'bug_assigned_status' ) );
			} else {
				bug_set_field( $t_bug_id, 'status', config_get( 'bug_submit_status' ) );
			}
		}
	}

	$t_event = array( 'comp' => 'email_reporting', 'event' => 'note_reported' );
	mantishub_event( $t_event );
}