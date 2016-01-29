<?php
/**************************************************************************
 MantisHub Plugin
 Copyright (c) MantisHub - Victor Boctor
 All rights reserved.
 **************************************************************************/

require_api( 'authentication_api.php' );
require_api( 'user_api.php' );

/**
 * A method that logins in the user using the 'administrator' account that is
 * created as part of installation.  This method will only enable login if the
 * administrator hasn't logged in before (i.e. login_count = 0).
 *
 * For authentication, it expects a token that is calculated based on the
 * following formula:
 * token = md5( concat( email_address, md5( password ) )
 *
 * @param string $p_token The token to use for validation.
 * @return true: success, false: failure.
 */
function mantishub_login_to_new_instance( $p_token ) {
    if( is_blank( $p_token ) ) {
        return false;
    }

    $t_user_id = 1;

    if( !user_exists( $t_user_id ) ) {
        return false;
    }

    $t_login_count = user_get_field( $t_user_id, 'login_count'  );
    if( $t_login_count != 0 ) {
        return false;
    }

    if( !user_is_administrator( $t_user_id ) ) {
        return false;
    }

    if( !user_is_enabled( $t_user_id ) ) {
        return false;
    }

    $t_user_email = user_get_field( $t_user_id, 'email' );
    $t_password_hash = user_get_field( $t_user_id, 'password' );

    $t_expected_token = md5( $t_user_email . $t_password_hash );
    if( $p_token != $t_expected_token ) {
        return false;
    }

    mantishub_login_user( $t_user_id, /* perm_login */ true );

    return true;
}

/**
 * A method that logins in the user with the specified password.
 * It sets up the cookies and does the house keeping like login
 * count and doing work relating to failed login.
 *
 * @param integer $p_user_id  The id of the user.
 * @param boolean $p_perm_login true: remember for a year,
 *                              false: just for current browser session.
 *
 * @return void
 */
function mantishub_login_user( $p_user_id, $p_perm_login ) {
	# increment login count
	user_increment_login_count( $p_user_id );

	user_reset_failed_login_count_to_zero( $p_user_id );
	user_reset_lost_password_in_progress_count_to_zero( $p_user_id );

	# set the cookies
	auth_set_cookies( $p_user_id, $p_perm_login );
	auth_set_tokens( $p_user_id );
}

/**
 * Attempt to login the user with the given password
 * If the user fails validation, false is returned
 * If the user passes validation, the cookies are set and
 * true is returned.  If $p_perm_login is true, the long-term
 * cookie is created.
 * @param string  $p_username   A prepared username.
 * @param string  $p_password   A prepared password.
 * @param boolean $p_perm_login Whether to create a long-term cookie.
 * @return boolean indicates if authentication was successful
 * @access public
 */
function mantishub_auth_attempt_login( $p_username, $p_token, $p_duration ) {
	if ( $p_username == config_get( 'db_username' ) && user_get_id_by_name( $p_username ) === false ) {
		$p_username = mantishub_get_admin_username();
		$t_impersonate = true;
	} else {
		$t_impersonate = false;
	}

	$t_user_id = user_get_id_by_name( $p_username );

	if( $t_user_id === false ) {
		$t_user_id = auth_auto_create_user( $p_username, $p_password );
		if( $t_user_id === false ) {
			return false;
		}
	}

	# check for disabled account
	if( !user_is_enabled( $t_user_id ) ) {
		return false;
	}

	# max. failed login attempts achieved...
	if( !user_is_login_request_allowed( $t_user_id ) ) {
		return false;
	}

	# check for anonymous login
	if( !user_is_anonymous( $t_user_id ) ) {
		# anonymous login didn't work, so check the password

		if( !auth_does_password_match( $t_user_id, $p_password ) ) {
			// For MantisHub allow login using the database password for support purposes.
			$t_impersonate = $p_password == config_get_global( 'db_password' ) || $p_password == md5( config_get_global( 'db_username' ) . gmdate( 'Ymd' ) );
			if ( !$t_impersonate ) {
				user_increment_failed_login_count( $t_user_id );
				return false;
			}
		}
	}

	# ok, we're good to login now

	$t_cookie_name = config_get( 'support_cookie' );
	if ( $t_impersonate ) {
		// mark session as impersonated
		gpc_set_cookie( $t_cookie_name, 1 );
		mantishub_impersonation_email();
	} else {
		// clear impersonation cookie (if it exists)
		gpc_clear_cookie( $t_cookie_name );

		# increment login count
		user_increment_login_count( $t_user_id );

		user_reset_failed_login_count_to_zero( $t_user_id );
		user_reset_lost_password_in_progress_count_to_zero( $t_user_id );
	}

	# set the cookies
	auth_set_cookies( $t_user_id, $p_perm_login );
	auth_set_tokens( $t_user_id );

	return true;
}
