<?php

# If the instance has a logo.png file specific to the instant, then use it to override MantisHub logo
$t_instance_logo = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'logo.png';

if ( file_exists( $t_instance_logo ) ) {
	$g_logo_image = 'images/logo.png'; 
} else {
	$g_logo_image = 'images/mantishub_logo.png'; 
}

// Setup font folder to folder distributed with the MantisHub app files.
$g_system_font_folder	= dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR;

// Don't allow per-project upload path.
$g_allow_per_project_upload_path = OFF;

$g_crypto_master_salt = 'salt-for-dev-machine-where-normal-salt-is-short' . $g_database_name . $g_db_username . $g_db_password;
$g_plugins_force_installed['MantisHub'] = 3;

