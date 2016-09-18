<?php
# Copyright (c) 2016 Victor Boctor @ MantisHub.com

require_api( 'email_api.php' );
require_once( config_get( 'plugin_path' ) . 'Helpdesk/core/helpdesk_api.php' );

class HelpdeskPlugin extends MantisPlugin {
	function register() {
		$this->name = plugin_lang_get( 'title' );
		$this->description = plugin_lang_get( 'description' );
		$this->version = '1.3.0';
		$this->requires = array( 'MantisCore' => '2.0.0' );
		$this->author = 'Victor Boctor';
		$this->contact = 'support@mantishub.com';
		$this->url = 'https://www.mantishub.com';
		$this->page = 'config';
		$this->issue_note_id = 0;
	}

	public function config() {
		return array(
			'mailgun_key' => config_get( 'email_incoming_mailgun_key', '' ),
			'enabled' => config_get( 'email_incoming_enabled' ),
			'enable_unregistered' => ON,
			'default_project' => config_get( 'email_incoming_default_project' ),
			'failed_message' => config_get( 'email_incoming_failed_message' ),
			'issue_reported_message' => config_get( 'email_incoming_issue_reported_message' ),
			);
	}

	public function hooks() {
		return array(
			'EVENT_VIEW_BUG_EXTRA' => 'print_emails_for_issue',
			'EVENT_BUGNOTE_ADD' => 'issue_note',
			'EVENT_BUGNOTE_EDIT' => 'issue_note',
			'EVENT_UPDATE_BUG' => 'issue_update',
		);
	}

	public function issue_note( $p_event, $p_issue_id, $p_issue_note_id ) {
		if ( OFF == config_get( 'enable_email_notification' ) ) {
			return;
		}

		# Don't send private notes to external reporters
		$t_view_state = bugnote_get_field( $p_issue_note_id, 'view_state' );
		if ( $t_view_state == VS_PRIVATE ) {
			return;
		}

		$t_recipients = helpdesk_users_for_issue( $p_issue_id );
		if ( count( $t_recipients ) == 0 ) {
			return;
		}

		if ( is_page_name( 'bug_update.php' ) ) {
			$this->issue_note_id = $p_issue_note_id;
			return;
		}

		# Don't notify reporter about their own notes.
		# We will ignore receive own configuration option in this case.
		$t_reporter_id = bugnote_get_field( $p_issue_note_id, 'reporter_id' );
		if ( helpdesk_generic_user_id() == $t_reporter_id ) {
			return;
		}

		$t_sender_name = HelpdeskPlugin::user_get_name( $t_reporter_id );
		$t_note = trim( bugnote_get_text( $p_issue_note_id ) );

		$t_anonymous_enabled = config_get( 'allow_anonymous_login' ) != OFF;

		$t_mail_headers = helpdesk_headers_for_issue( $p_issue_id );
		$t_subject = helpdesk_subject_for_issue( $p_issue_id );
		$t_issue_url = helpdesk_url_for_issue( $p_issue_id );

		foreach( $t_recipients as $t_recipient ) {
			HelpdeskPlugin::send_message(
				$t_sender_name, $t_recipient, $p_issue_id, $t_subject, $t_note, $t_mail_headers,
				$t_issue_url, $t_anonymous_enabled, 'responded' );
		}
	}

	public function issue_update( $p_event, $p_issue_before, $p_issue_after ) {
		$t_issue_id = $p_issue_after->id;

		$t_message = '';
		$t_action = '';
		$t_sender_name = HelpdeskPlugin::user_get_name();

		$t_resolved_threshold = config_get( 'bug_resolved_status_threshold' );
		if ( $p_issue_before->status < $t_resolved_threshold &&
			 $p_issue_after->status >= $t_resolved_threshold ) {
			$t_action = 'resolved_issue';
		} else if ( $p_issue_before->status >= $t_resolved_threshold &&
			$p_issue_after->status < $t_resolved_threshold ) {
			$t_action = 'reopened_issue';
		}

		if ( $this->issue_note_id > 0 ) {
			# Don't send private notes to external reporters
			$t_view_state = bugnote_get_field( $this->issue_note_id, 'view_state' );
			if ( $t_view_state == VS_PUBLIC ) {
				$t_message = trim( bugnote_get_text( $this->issue_note_id ) ) . "\n\n";
			}
		}

		if ( empty( $t_action ) && empty( $t_message ) ) {
			return;
		}

		$t_recipients = helpdesk_users_for_issue( $t_issue_id );
		if ( count( $t_recipients ) == 0 ) {
			return;
		}

		$t_anonymous_enabled = config_get( 'allow_anonymous_login' ) != OFF;

		$t_mail_headers = helpdesk_headers_for_issue( $t_issue_id );
		$t_subject = helpdesk_subject_for_issue( $t_issue_id );
		$t_issue_url = helpdesk_url_for_issue( $t_issue_id );

		foreach( $t_recipients as $t_recipient ) {
			HelpdeskPlugin::send_message(
				$t_sender_name, $t_recipient, $t_issue_id, $t_subject, $t_message, $t_mail_headers,
				$t_issue_url, $t_anonymous_enabled, $t_action );
		}
	}

	private static function send_message(
		$p_sender_name, $p_recipient_email, $p_issue_id, $p_subject, $p_message,
		$p_mail_headers, $p_issue_url, $p_anonymous_enabled, $p_action_lang_string ) {
		$t_recipient_email = helpdesk_get_email_from_name_email( $p_recipient_email );

		$t_message = '';
		$t_message .= sprintf( plugin_lang_get( $p_action_lang_string ), $p_sender_name ) . "\n\n";
		$t_message .= $p_message . "\n\n";

		if ( $p_anonymous_enabled ) {
			$t_message .= sprintf( plugin_lang_get( 'see_issue_details' ), $p_issue_url ) . "\n\n";
		}

		$t_message .= "---\n";

		$t_message = mantishub_wrap_email( $p_issue_id, $t_message );

		email_store( $t_recipient_email, $p_subject, $t_message, $p_mail_headers );
	}

	private static function user_get_name( $p_user_id = null ) {
		if( is_null( $p_user_id ) ) {
			$p_user_id = auth_get_current_user_id();
		}

 		$t_user = user_cache_row( $p_user_id, false );
		if( empty( $t_user ) ) {
			return 'user' . $p_user_id;
		}

		if( !empty( $t_user['realname'] ) ) {
			$t_user_name = $t_user['realname'];
		} else {
			$t_user_name = $t_user['username'];
		}

		return $t_user_name;
	}

	public function print_emails_for_issue() {
		helpdesk_print_issue_view_info( gpc_get_int( 'id' ) );
	}

	public function schema() {
		return array(
			array( 'CreateTableSQL', array( plugin_table( 'recipients' ), "
				id			I		NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
				bug_id      I       NOTNULL UNSIGNED,
				bugnote_id	I		NOTNULL UNSIGNED,
				email		C(128)	NOTNULL,
				extra		XL		NOTNULL
				") ),
			array( 'CreateIndexSQL', array( 'idx_bug_id', plugin_table( "recipients" ), 'bug_id' ) ),
		);
	}
}

