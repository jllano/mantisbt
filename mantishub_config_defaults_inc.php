<?php
	/**
	 * Remove columns from array of columns.  Define here since when this
	 * file is included mantishub_api is not included yet.  This method
	 * should be used instead of copying and altering the list.
	 *
	 * @params array $p_columns The list of columns to filter.
	 * @params array|string $p_columns_to_remove The field(s) to filter out.
	 * @return array The filtered list.
	 */
	function mantishub_remove_columns( $p_columns, $p_columns_to_remove ) {
		if( $p_columns_to_remove === null ) {
			return $p_columns;
		}

		$t_filtered_columns = array();
		if( !is_array( $p_columns_to_remove ) ) {
			$p_columns_to_remove = array( $p_columns_to_remove );
		}

		foreach( $p_columns as $t_column ) {
			if( in_array( $t_column, $p_columns_to_remove ) ) {
				continue;
			}

			$t_filtered_columns[] = $t_column;
		}

		return $t_filtered_columns;
	}

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
	$g_mantishub_info_billing_portal_url = '';

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
    $g_plugins_to_exclude = array( 'XmlImportExport', 'Auth0', 'Zendesk' );

    # This causes issue for some customers.
	$g_session_validation = OFF;

	# By default notify developers and above for newly created issues.
	$g_notify_flags['new']['threshold_min'] = DEVELOPER;
	$g_notify_flags['new']['threshold_max'] = ADMINISTRATOR;


    /**
     * Path to mantishub folder. The default is usually OK
     * @global string $g_mantishub_path
     */
    $g_mantishub_path = $g_absolute_path . 'mantishub' . DIRECTORY_SEPARATOR;

	if ( !isset( $g_mantishub_gen ) ) {
		$g_mantishub_gen = 1;
	}

	$g_mantishub_support_url = 'http://support.mantishub.com/hc/en-us/';
	$g_mantishub_info_impersonation_email = 'management@mantishub.net';

	# Use CDN to optimize performance
	$g_cdn_enabled = ON;

	# In generation 3 and above, disable sub-projects by default.
	if ( $g_mantishub_gen >= 3 ) {
		$g_subprojects_enabled = OFF;
	}

	$g_log_level = LOG_EMAIL | LOG_EMAIL_RECIPIENT;
	$g_log_destination = 'none';

	if ( $g_mantishub_gen >= 4 ) {
		$t_columns_to_remove = array(
			'reproducibility'
			);

		# The steps to reproduce and additional information field won't be removed from the view page
		# since they are hidden by default unless they have data.  For example, we should additional
		# info when an issue is reported by email and the additional info field contains the sender
		# email address.
		$g_bug_view_page_fields = mantishub_remove_columns(
			$g_bug_view_page_fields,
			$t_columns_to_remove );

		$g_bug_print_page_fields = mantishub_remove_columns(
			$g_bug_print_page_fields,
			$t_columns_to_remove );

		$t_columns_to_remove[] = 'additional_info';
		$t_columns_to_remove[] = 'steps_to_reproduce';

		$g_bug_report_page_fields = mantishub_remove_columns(
			$g_bug_report_page_fields,
			$t_columns_to_remove );

		$g_bug_update_page_fields = mantishub_remove_columns(
			$g_bug_update_page_fields,
			$t_columns_to_remove );

		$g_bug_change_status_page_fields = mantishub_remove_columns(
			$g_bug_change_status_page_fields,
			$t_columns_to_remove );
	}

	$g_global_settings[] = 'allow_per_project_upload_path';

	$g_email_login_enabled = ON;

	# This was removed causing some failures when MantisTouch is used.
	# This config still exists in MantisHub, so just whitelisting it should
	# work.
	$g_public_config_names[] = 'administrator_email';

	# A configuration option to control the injection of analytics code
	# like Google Analytics, Bing Analytics and Drip.
	$g_mantishub_analytics_enabled = ON;

	# Increase default preview limit to 1MB.
	$g_preview_attachments_inline_max_size = 1 * 1024 * 1024;
