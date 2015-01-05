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
$f_sender = gpc_get_string( 'sender' );

function mantishub_email_response( $p_message, $p_success = false ) {
    global $f_sender, $f_subject;

    $t_message = $p_message . "\n";

    if ( !$p_success ) {
        $t_message .= "\nFor more details about how email reporting works, checkout documentation at:\n";
        $t_message .= "http://www.mantishub.com/docs/reporting_issues_via_email.html\n";
    }

    email_store( $f_sender, 'RE: ' . $f_subject, $t_message );
    log_event( LOG_EMAIL, sprintf( 'Incoming Mail API response to = \'%s\'', $f_sender ) );

    if( OFF == config_get( 'email_send_using_cronjob' ) ) {
        email_send_all();
    }
}

mantishub_log( "Received incoming mail via POST.\n" . var_export( $_POST, true ) );

mantishub_log( 'incoming mail: ' . ( empty( $f_subject ) ? '<blank>' : $f_subject ) );

if ( empty( $f_subject ) ) {
	header( 'HTTP/1.0 406 Empty Subject' );
	mantishub_log( 'incoming mail: rejected message with empty subject.' );
	mantishub_email_response( 'Message rejected due to empty subject.' );
	exit;
}

if ( config_get( 'email_incoming_enabled' ) == OFF ) {
	header( 'HTTP/1.0 406 Email Reporting Disabled' );
	mantishub_log( 'incoming mail: rejected since email_incoming_enabled is OFF.' );
	mantishub_email_response( 'Message rejected since incoming email reporting feature is disabled.' );
	exit;
}

mantishub_log( 'incoming mail: email_incoming_enabled enabled.' );

#
# Mail Reporting only available for Gold Plan
#

if ( !is_gold() ) {
	header( 'HTTP/1.0 406 Plan Must be Gold' );
	mantishub_log( 'incoming mail: rejected since plan is not gold.' );
	mantishub_email_response( 'Message rejected since email reporting is only available for MantisHub Gold plan.' );
	exit;
}

mantishub_log( 'incoming mail: plan is gold.' );

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
	mantishub_log( 'incoming mail: rejected since mailgun signature is invalid.' );
	mantishub_email_response( "Message rejected since it didn't go through standard mail gateway." );
	exit;
}

mantishub_log( 'incoming mail: correct signature.' );

#
# Make sure it is not spam.
#

$f_spam = gpc_get_string( 'X-Mailgun-Sflag', 'No' );
if ( $f_spam == 'Yes' ) {
	header( 'HTTP/1.0 406 Mail Marked as Spam' );
	mantishub_log( 'incoming mail: rejected since mailgun marked email as spam.' );
	exit;
}

mantishub_log( 'incoming mail: Not spam.' );

#
# Retrieve sender (reporter) information.
#

$t_user_id = user_get_id_by_email( $f_sender );
if ( $t_user_id === false ) {
	$t_user_id = user_get_id_by_name( 'email' );
	$t_generic_user = true;
} else {
	$t_generic_user = false;
}

if ( $t_user_id === false ) {
	header( 'HTTP/1.0 406 No Reporter Match' );
	mantishub_log( 'incoming mail: rejected since no user match or "email" account.' );
	mantishub_email_response( 'Message rejected since there is no email account matching sender account.  There is also no fallback "email" user account.' );
	exit;
}

mantishub_log( 'incoming mail: user id: ' . $t_user_id . ' generic: ' . $t_generic_user );

$t_reporter_username = user_get_field( $t_user_id, 'username' );
mantishub_log( 'incoming mail: logging in as username "' . $t_reporter_username . '" with id ' . $t_user_id . '.' );
auth_attempt_script_login( $t_reporter_username );

mantishub_log( 'incoming mail: user authenticated.' );

#
# Get project name.
#

$f_recipient = gpc_get_string( 'recipient' );
$t_instance_name = mantishub_instance_name();

if ( stripos( $f_recipient, $t_instance_name . '+' ) !== 0 &&
	 stripos( $f_recipient, $t_instance_name . '@' ) !== 0 ) {
	header( 'HTTP/1.0 406 Wrong Instance' );
	mantishub_log( 'incoming mail: rejected since targetted to "' . $f_recipient . '" rather than current instance "' . $t_instance_name . '".' );
	mantishub_email_response( "Message rejected since target account doesn't match." );
	exit;
}

$t_project = mantishub_mailgun_project_from_recipient( $f_recipient );
if ( $t_project === false ) {
	mantishub_log( 'incoming mail: project name not specified in recipient.' );

	$t_default_project_id = config_get( 'email_incoming_default_project' );
	if ( $t_default_project_id == 0 ) {
		mantishub_log( 'incoming mail: no default target project in config. Falling back to user default project.' );
		$t_default_project_id = user_pref_get_pref( $t_user_id, 'default_project' );
	}

	if ( $t_default_project_id != 0 ) {
		$t_project = project_get_row( $t_default_project_id );
	} else {
		header( 'HTTP/1.0 406 No Default or Selected Project' );
		mantishub_log( 'incoming mail: rejected since no selected or default project.' );
		mantishub_email_response( 'Message rejected since there is no selected or default project.' );
		exit;
	}
}

mantishub_log( 'incoming mail: project is ' . $t_project['id'] . ': ' . $t_project['name'] );

#
# Verify user has REPORTER access to project.
#

if ( !access_has_project_level( REPORTER, (int)$t_project['id'], $t_user_id ) ) {
	header( 'HTTP/1.0 406 Reporter unauthorized to Report Issues' );
	mantishub_log( "incoming mail: user $t_user_id does not have reporting access to project " . $t_project['id'] );
	mantishub_email_response( "Message rejected since user doesn't have access to report issues." );
	exit;
}

mantishub_log( 'incoming mail: user has reporting access' );

#
# Create an issue based on email information
#

$f_from = gpc_get_string( 'from' );
$f_attachment_count = gpc_get_int( 'attachment-count', 0 );
$f_stripped_text = trim( gpc_get_string( 'stripped-text', '' ) );

if ( empty( $f_stripped_text ) ) {
	$f_stripped_text = trim( gpc_get_string( 'body-plain', '' ) );
}

if ( empty( $f_stripped_text ) ) {
	$f_stripped_text = $f_subject;
}

$t_bug = new BugData;
$t_bug->summary = $f_subject;
$t_bug->description = $f_stripped_text;
$t_bug->project_id = (int)$t_project['id'];
$t_bug->reporter_id = $t_user_id;

if ( $t_generic_user ) {
	$t_bug->additional_information = 'MantisHub Email Delivery From: ' . $f_from;
}

$t_bug_id = $t_bug->create();
mantishub_log( 'incoming mail: accepted as issue ' . $t_bug_id . ' with ' . $f_attachment_count . ' attachments.' );

for ( $i = 1; $i <= (int)$f_attachment_count; ++$i ) {
	$t_file = $_FILES['attachment-' . $i];
	file_add( $t_bug_id, $t_file, 'bug', '', '', $t_user_id );
	mantishub_log( 'incoming mail: file "' . $t_file['name'] . '" attached to issue ' . $t_bug_id );
}

mantishub_log( 'incoming mail: done with issue ' . $t_bug_id );

helper_call_custom_function( 'issue_create_notify', array( $t_bug_id ) );

# Allow plugins to post-process bug data with the new bug ID
event_signal( 'EVENT_REPORT_BUG', array( $t_bug_data, $t_bug_id ) );

email_generic( $t_bug_id, 'new', 'email_notification_title_for_action_bug_submitted' );

mantishub_email_response( "Thanks for your email.  We've recorded the issue with reference number $t_bug_id.", /* success */ true );
