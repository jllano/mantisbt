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

	# Some customers complained about losing data entered in bug reports due to security token
	# issues.  So disabling this feature.
	$g_form_security_validation = OFF;

    # Plugins that are included in the distribution that we don't want to include.
    # If customer have them installed, they will continue to be available, but they won't be
    # available for installs.
    $g_plugins_to_exclude = array( 'XmlImportExport' );

    # This causes issue for some customers.
	$g_session_validation = OFF;

	# By default notify developers and above for newly created issues.
	$g_notify_flags['new']['threshold_min'] = DEVELOPER;
	$g_notify_flags['new']['threshold_max'] = ADMINISTRATOR;

	$g_crypto_master_salt = $g_database_name . $g_db_username . $g_db_password;

