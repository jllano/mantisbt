<?php
# MantisBT - a php based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This page handles an incoming email message from mailgun.
 *
 * @package MantisBT
 * @copyright Copyright (C) 2014  MantisHub Team - support@mantishub.com
 * @link http://www.mantishub.com
 */
 
 /**
  * MantisBT Core API's
  */
require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'bug_api.php' );
require_api( 'email_api.php' );
require_api( 'event_api.php' );
require_api( 'file_api.php' );
require_api( 'helper_api.php' );
require_api( 'project_api.php' );

$f_subject = trim( gpc_get_string( 'subject', '' ) );
$f_from_name_email = gpc_get_string( 'from' );
$f_from_email = mantishub_get_email_from_name_email( $f_from_name_email );

$f_message_headers = gpc_get_string( 'message-headers' );
$t_headers = json_decode( $f_message_headers );

$t_message_id = mantishub_get_header( $t_headers, 'Message-Id' );
$g_auto_response_suppress = mantishub_get_header( $t_headers, 'X-Auto-Response-Suppress' );

# Check for loopback
if ( stristr( $t_message_id, 'mantishub.com' ) !== false ) {
	header( 'HTTP/1.0 406 Loop detected' );
	$t_event = array( 'level' => 'error', 'comp' => 'email_reporting', 'event' => 'loop' );
	mantishub_event( $t_event );
	exit;
}

function mantishub_get_email_from_name_email( $p_name_email ) {
	$t_start_pos = stripos( $p_name_email, '<' );
	$t_end_pos = stripos( $p_name_email, '>' );

	if ( $t_start_pos === false || $t_end_pos === false || $t_start_pos >= $t_end_pos ) {
		return $p_name_email;
	}

	return substr( $p_name_email, $t_start_pos + 1, $t_end_pos - $t_start_pos - 1 );
}

function mantishub_get_header( $p_headers, $p_header_name ) {
	foreach( $p_headers as $t_header ) {
		if ( strcasecmp( $t_header[0], $p_header_name ) === 0 ) {
			return $t_header[1];
		}
	}

	return null;
}

function mantishub_email_response( $p_message, $p_success = false ) {
    global $f_from_email, $f_subject, $g_auto_response_suppress;

	# Don't respond if message supresses auto-response to avoid loopback.
	if ( $g_auto_response_suppress == 'All' )
	{
		return;
	}

    $t_message = $p_message . "\n";

    if ( !$p_success ) {
        $t_message .= "\nFor more details about how email reporting works, checkout documentation at:\n";
        $t_message .= "http://www.mantishub.com/docs/reporting_issues_via_email.html\n";
    }

    email_store( $f_from_email, 'RE: ' . $f_subject, $t_message );
    log_event( LOG_EMAIL, sprintf( 'Incoming Mail API response to = \'%s\'', $f_from_email ) );

    if( OFF == config_get( 'email_send_using_cronjob' ) ) {
        email_send_all();
    }
}

function mantishub_email_error( $p_error_message ) {
	$t_message = config_get( 'email_incoming_failed_message' );
	$t_message = str_replace( '{error}', $p_error_message, $t_message );

	mantishub_email_response( $t_message );
}

$t_event = array( 'comp' => 'email_reporting', 'event' => 'receiving_email', 'subject' => ( empty( $f_subject ) ? '<blank>' : $f_subject ), 'post' => var_export( $_POST, true ) );
mantishub_event( $t_event );

if ( empty( $f_subject ) ) {
	header( 'HTTP/1.0 406 Empty Subject' );
	$t_event = array( 'level' => 'error', 'comp' => 'email_reporting', 'event' => 'empty_subject' );
	mantishub_event( $t_event );
	mantishub_email_error( 'Message rejected due to empty subject.' );
	exit;
}

if ( config_get( 'email_incoming_enabled' ) == OFF ) {
	header( 'HTTP/1.0 406 Email Reporting Disabled' );
	$t_event = array( 'level' => 'error', 'comp' => 'email_reporting', 'event' => 'feature_disabled' );
	mantishub_event( $t_event );
	mantishub_email_error( 'Message rejected since incoming email reporting feature is disabled.' );
	exit;
}

#
# Mail Reporting only available for Gold Plan
#

if ( !plan_mail_reporting() ) {
	header( 'HTTP/1.0 406 Email reporting not enabled for your plan.' );
	$t_event = array( 'level' => 'error', 'comp' => 'email_reporting', 'event' => 'upgrade_plan' );
	mantishub_event( $t_event );
	mantishub_email_error( 'Message rejected since email reporting is not enabled for your plan.' );
	exit;
}

#
# Authenticate that request is sent from mailgun.
#

$f_timestamp = gpc_get_int( 'timestamp' );
$f_token = gpc_get_string( 'token' );
$f_signature = gpc_get_string( 'signature' );

$t_key = mantishub_mailgun_key();
$t_data = $f_timestamp . $f_token;
$t_hash = hash_hmac ( 'sha256', $t_data, $t_key );

if ( $t_hash != $f_signature ) {
	header( 'HTTP/1.0 406 Invalid Signature' );
	$t_event = array( 'level' => 'error', 'comp' => 'email_reporting', 'event' => 'invalid_signature' );
	mantishub_event( $t_event );
	mantishub_email_error( "Message rejected since it didn't go through standard mail gateway." );
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

$t_user_id = user_get_id_by_email( $f_from_email );
if ( $t_user_id === false ) {
	$t_user_id = user_get_id_by_name( 'email' );
	$t_generic_user = true;
} else {
	$t_generic_user = false;
}

if ( $t_user_id === false ) {
	header( 'HTTP/1.0 406 No Reporter Match' );
	$t_event = array( 'level' => 'error', 'comp' => 'email_reporting', 'event' => 'reporter_not_found', 'sender' => $f_from_email );
	mantishub_event( $t_event );
	mantishub_email_error( 'Message rejected since there is no email account matching sender account.  There is also no fallback "email" user account.' );
	exit;
}

$t_reporter_username = user_get_field( $t_user_id, 'username' );
auth_attempt_script_login( $t_reporter_username );

$t_event = array( 'comp' => 'email_reporting', 'event' => 'reporter_identified', 'user_id' => $t_user_id, 'username' => $t_reporter_username, 'generic_user' => $t_generic_user );
mantishub_event( $t_event );

$f_recipient = gpc_get_string( 'recipient' );

$t_abort_error = '';
$t_bug_id = mantishub_mailgun_issue_from_recipient( $f_recipient, $t_abort_error );
if ( $t_bug_id == 0 && !is_blank( $t_abort_error ) ) {
	header( 'HTTP/1.0 406 ' . $t_abort_error );
	$t_event = array( 'level' => 'error', 'comp' => 'email_reporting', 'event' => 'issue_error', 'msg' => $t_abort_error );
	mantishub_event( $t_event );
	mantishub_email_error( 'Message rejected.  Error: ' . $t_abort_error );
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
		mantishub_email_error( "Message rejected since there is no matching MantisHub." );
		exit;
	}

	$t_project = mantishub_mailgun_project_from_recipient( $f_recipient );
	if ( $t_project === false ) {
		$t_default_project_id = config_get( 'email_incoming_default_project' );
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
			mantishub_email_error( 'Message rejected since there is no selected or default project.' );
			exit;
		}
	}
} else {
	if ( !bug_exists( $t_bug_id ) ) {
		header( 'HTTP/1.0 406 Issue no longer exists' );
		$t_event = array( 'level' => 'error', 'comp' => 'email_reporting', 'event' => 'issue_does_not_exist' );
		mantishub_event( $t_event );
		mantishub_email_error( 'Issue ' . $t_bug_id . ' no longer exists.' );
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
	mantishub_email_error( "Message rejected since user doesn't have access to report issues." );
	exit;
}

#
# Create an issue based on email information
#

$f_attachment_count = gpc_get_int( 'attachment-count', 0 );
$f_stripped_text = trim( gpc_get_string( 'stripped-text', '' ) );

if ( empty( $f_stripped_text ) ) {
	$f_stripped_text = trim( gpc_get_string( 'body-plain', '' ) );
} else {
	# mailgun returns stripped text terminated with >
	$f_stripped_text = trim( $f_stripped_text );
	if ( substr( $f_stripped_text, -1, 1 ) == '>' ) {
		$f_stripped_text = substr( $f_stripped_text, 0, strlen( $f_stripped_text ) - 1 );
		$f_stripped_text = trim( $f_stripped_text );
	}
}

if ( $t_new_issue ) {
	if ( empty( $f_stripped_text ) ) {
		$f_stripped_text = $f_subject;
	}

	$t_bug = new BugData;
	$t_bug->summary = $f_subject;
	$t_bug->description = $f_stripped_text;
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
				mantishub_email_error( "Message rejected since default category wasn't found." );
				exit;
			}
		}
	}

	if ( $t_generic_user ) {
		$t_bug->additional_information = 'MantisHub Email Delivery From: ' . $f_from_name_email;
	}

	$t_bug_id = $t_bug->create();

	$t_event = array( 'comp' => 'email_reporting', 'event' => 'creating_issue', 'issue_id' => $t_bug_id, 'file_count' => $f_attachment_count );
	mantishub_event( $t_event );
} else {
	if ( is_blank( $f_stripped_text ) ) {
		header( 'HTTP/1.0 406 Blank note in reply to issue notification' );
		$t_event = array( 'level' => 'error', 'comp' => 'email_reporting', 'event' => 'empty_note' );
		mantishub_event( $t_event );
		mantishub_email_error( "Message rejected since it has an empty note." );
		exit;
	}

	if( bug_is_readonly( $t_bug_id ) ) {
		header( 'HTTP/1.0 406 Reply to read-only issue rejected' );
		$t_event = array( 'level' => 'error', 'comp' => 'email_reporting', 'event' => 'readonly_issue' );
		mantishub_event( $t_event );
		mantishub_email_error( "Reply to read-only issue rejected." );
		exit;
	}

	$t_note_text = $f_stripped_text;

	if ( $t_generic_user ) {
		$t_note_text .= "\n\n---\n" . $f_from_name_email;
	}

	$t_event = array( 'comp' => 'email_reporting', 'event' => 'adding_note', 'issue_id' => $t_bug_id );
	mantishub_event( $t_event );

	$t_note_id = bugnote_add( $t_bug_id, $t_note_text );

	$t_event = array( 'comp' => 'email_reporting', 'event' => 'added_note', 'issue_id' => $t_bug_id, 'note_id' => $t_note_id );
	mantishub_event( $t_event );
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

	$t_message = config_get( 'email_incoming_issue_reported_message' );
	$t_message = str_replace( '{issue_id}', $t_bug_id, $t_message );

	mantishub_email_response( $t_message, /* success */ true );

	$t_event = array( 'comp' => 'email_reporting', 'event' => 'issue_reported' );
	mantishub_event( $t_event );

	# Allow plugins to post-process bug data with the new bug ID
	# Call this after all native work just in case plugins cause a failure.
	event_signal( 'EVENT_REPORT_BUG', array( $t_bug_data, $t_bug_id ) );
} else {
	$t_event = array( 'comp' => 'email_reporting', 'event' => 'note_reported' );
	mantishub_event( $t_event );
}
