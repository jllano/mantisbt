<?php
# Copyright (c) 2016 Victor Boctor @ MantisHub.com

require_once( config_get( 'plugin_path' ) . 'Helpdesk/core/helpdesk_api.php' );

class HelpdeskPlugin extends MantisPlugin {
	function register() {
		$this->name = plugin_lang_get( 'title' );
		$this->description = plugin_lang_get( 'description' );
		$this->version = '1.3.0';
		$this->requires = array( 'MantisCore' => '1.3.0' );
		$this->author = 'Victor Boctor';
		$this->contact = 'support@mantishub.com';
		$this->url = 'https://www.mantishub.com';
		$this->page = 'config';
	}

	public function config() {
		return array(
			'mailgun_key' => config_get( 'email_incoming_mailgun_key', '' ),
			'enabled' => config_get( 'email_incoming_enabled' ),
			'default_project' => config_get( 'email_incoming_default_project' ),
			'failed_message' => config_get( 'email_incoming_failed_message' ),
			'issue_reported_message' => config_get( 'email_incoming_issue_reported_message' ),
			);
	}

	public function hooks() {
		return array(
			'EVENT_VIEW_BUG_EXTRA' => 'print_emails_for_issue',
		);
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

