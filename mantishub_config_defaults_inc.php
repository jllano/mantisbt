<?php
	$g_window_title			= 'MantisHub';
	$g_allow_signup			= OFF;

	// Enable avatars
	$g_show_avatar = ON;
	$g_show_avatar_threshold = VIEWER;

	// Attachments
	$g_file_upload_method	= DISK;
	$g_max_file_size		= 250000000;
	$g_absolute_path_default_upload_folder = dirname( __FILE__ ) . '/attach/';
	$g_allow_per_project_upload_path = OFF;

	// Cookies
	$g_support_cookie		= '%cookie_prefix%_SUPPORT';   // set if current user is impersonated by MantisHub support.

	// Fields
	$g_enable_profiles = OFF;

	// Email
	$g_from_name	 = 'MantisHub';

	// General Settings
	$g_allow_reporter_close	= ON;
	$g_bug_readonly_status_threshold = CLOSED;

	// MantisTouch - disable the deprecated functionality in favor of the MantisTouchRedirect plugin.
	$g_mantistouch_url = '';

	// MantisHub Info Data
	$g_mantishub_info_trial = true;
	$g_mantishub_info_payment_on_file = false;
	$g_mantishub_info_administrator_email = $g_webmaster_email;
	$g_mantishub_info_administrator_firstname = '';
	$g_mantishub_info_administrator_lastname = '';
	$g_mantishub_info_company = '';

	$t_config_inc = dirname( __FILE__ ) . '/config_inc.php';
	if ( file_exists( $t_config_inc ) ) {
		$g_mantishub_info_creation_date = filemtime( $t_config_inc );
	} else {
		$g_mantishub_info_creation_date = time();
	}

	$t_mantishub_info_file_path = dirname( __FILE__ ) . '/mantishub_info_inc.php';
	if ( file_exists( $t_mantishub_info_file_path ) ) {
		require_once( $t_mantishub_info_file_path );
	}

	// Enable processing of incoming emails.
	$g_email_incoming_enabled = ON;

	// The default project to which issues sent to instance_name are filed under, when recipient
	// email address doesn't include a project name.
	$g_email_incoming_default_project = 0;

	# Template for message sent when an incoming email is reported successfully.
	# Supported parameters: {issue_id}
	$g_email_incoming_issue_reported_message = "Thanks for your email.  We've recorded the issue with reference number {issue_id}.";

	# Template for messages sent when an incoming email is rejected.
	# Supported parameters: {error}
	$g_email_incoming_failed_message = '{error}';

	# Some customers complained about losing data entered in bug reports due to security token
	# issues.  So disabling this feature.
	$g_form_security_validation = OFF;

    # Plugins that are included in the distribution that we don't want to include.
    # If customer have them installed, they will continue to be available, but they won't be
    # available for installs.
    $g_plugins_to_exclude = array( 'XmlImportExport', 'Auth0' );

    # This causes issue for some customers.
	$g_session_validation = OFF;

	# By default notify developers and above for newly created issues.
	$g_notify_flags['new']['threshold_min'] = DEVELOPER;
	$g_notify_flags['new']['threshold_max'] = ADMINISTRATOR;

	if ( !isset( $g_mantishub_gen ) ) {
		$g_mantishub_gen = 1;
	}

	$g_in_app_support_enabled = ON;
	$g_mantishub_info_impersonation_email = 'management@mantishub.net';

	# Use CDN to optimize performance
	$g_cdn_enabled = ON;

	# In generation 3 and above, disable sub-projects by default.
	if ( $g_mantishub_gen >= 3 ) {
		$g_subprojects_enabled = OFF;
	}

	$g_log_level = LOG_EMAIL | LOG_EMAIL_RECIPIENT;
	$g_log_destination = 'none';

