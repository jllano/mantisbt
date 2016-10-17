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
 * instances is verified to not have been used yet.
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

    if ( mantishub_table_row_count( 'bug' ) != 0 ||
         mantishub_table_row_count( 'user' ) != 1 ) {
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

function mantishub_google_analytics() {
	if ( mantishub_auto_refresh_page() ) {
		return;
	}

	if( config_get( 'mantishub_analytics_enabled' ) == OFF ) {
		return;
	}

	// MantisHub Google Analytics to track engagement.
	echo "<script>";
	echo "(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){";
	echo "(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),";
	echo "m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)";
	echo "})(window,document,'script','//www.google-analytics.com/analytics.js','ga');\n";
	echo "ga('create', 'UA-330112-9', 'mantishub.com');\n";
	echo "ga('send', 'pageview');\n";
	echo "</script>\n";

	global $g_mantishub_info_trial;

	if ( $g_mantishub_info_trial ) {
		# <!-- Google Code for Trail Starts Conversion Page -->
		echo '<script type="text/javascript">' . "\n";
		echo '/' . '* <![CDATA[ */' . "\n";
		echo 'var google_conversion_id = 970248102;' . "\n";
		echo 'var google_conversion_language = "en";' . "\n";
		echo 'var google_conversion_format = "3";' . "\n";
		echo 'var google_conversion_color = "ffffff";' . "\n";
		echo 'var google_conversion_label = "eQWuCIrG5AkQpp_TzgM";' . "\n";
		echo 'var google_remarketing_only = false;' . "\n";
		echo '/* ]]> */' . "\n";
		echo '</script>' . "\n";
		echo '<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">' . "\n";
		echo '</script>' . "\n";
		echo '<noscript>' . "\n";
		echo '<div style="display:inline;">' . "\n";
		echo '<img height="1" width="1" style="border-style:none;" alt="" src="//www.googleadservices.com/pagead/conversion/970248102/?label=eQWuCIrG5AkQpp_TzgM&guid=ON&script=0"/>' . "\n";
		echo '</div>' . "\n";
		echo '</noscript>' . "\n";
	} else {
		$t_value = plan_price();

		# <!-- Google Code for Trial Converts Conversion Page -->
		echo '<script type="text/javascript">' . "\n";
		echo '/' . '* <![CDATA[ */' . "\n";
		echo 'var google_conversion_id = 970248102;' . "\n";
		echo 'var google_conversion_language = "en";' . "\n";
		echo 'var google_conversion_format = "3";' . "\n";
		echo 'var google_conversion_color = "ffffff";' . "\n";
		echo 'var google_conversion_label = "GYQECPrH5AkQpp_TzgM";' . "\n";
		echo 'var google_conversion_value = ' . $t_value . ';' . "\n";
		echo 'var google_remarketing_only = false;' . "\n";
		echo '/* ]]> */' . "\n";
		echo '</script>' . "\n";
		echo '<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">' . "\n";
		echo '</script>' . "\n";
		echo '<noscript>' . "\n";
		echo '<div style="display:inline;">' . "\n";
		echo '<img height="1" width="1" style="border-style:none;" alt="" src="//www.googleadservices.com/pagead/conversion/970248102/?value=20.000000&label=GYQECPrH5AkQpp_TzgM&guid=ON&script=0"/>' . "\n";
		echo '</div>' . "\n";
		echo '</noscript>' . "\n";
	}
}

function mantishub_bingads_analytics() {
	if ( mantishub_auto_refresh_page() ) {
		return;
	}

	if( config_get( 'mantishub_analytics_enabled' ) == OFF ) {
		return;
	}

	global $g_mantishub_info_trial;

	if ( $g_mantishub_info_trial ) {
		# <!-- BingAds Code for Trail Starts Conversion Page -->
		echo '<script>(function(w,d,t,r,u){var f,n,i;w[u]=w[u]||[],f=function(){var o={ti:"4061542"};o.q=w[u],w[u]=new UET(o),w[u].push("pageLoad")},n=d.createElement(t),n.src=r,n.async=1,n.onload=n.onreadystatechange=function(){var s=this.readyState;s&&s!=="loaded"&&s!=="complete"||(f(),n.onload=n.onreadystatechange=null)},i=d.getElementsByTagName(t)[0],i.parentNode.insertBefore(n,i)})(window,document,"script","//bat.bing.com/bat.js","uetq");</script>';
		echo '<noscript><img src="//bat.bing.com/action/0?ti=4061542&Ver=2" height="0" width="0" style="display:none; visibility: hidden;" /></noscript>';
	} else {
		$t_value = plan_price();

		# Pass plan value first
		echo '<script>' . "\n";
		echo '	window.uetq = window.uetq || [];' . "\n";
		echo "	window.uetq.push({ 'gv': " . $t_value . ' })' . "\n";
		echo '</script>' . "\n";

		# <!-- BingAds Code for Trial Converts Conversion Page -->
		echo '<script>(function(w,d,t,r,u){var f,n,i;w[u]=w[u]||[],f=function(){var o={ti:"4061543"};o.q=w[u],w[u]=new UET(o),w[u].push("pageLoad")},n=d.createElement(t),n.src=r,n.async=1,n.onload=n.onreadystatechange=function(){var s=this.readyState;s&&s!=="loaded"&&s!=="complete"||(f(),n.onload=n.onreadystatechange=null)},i=d.getElementsByTagName(t)[0],i.parentNode.insertBefore(n,i)})(window,document,"script","//bat.bing.com/bat.js","uetq");</script>';
		echo '<noscript><img src="//bat.bing.com/action/0?ti=4061543&Ver=2" height="0" width="0" style="display:none; visibility: hidden;" /></noscript>';
	}
}

/**
 * Add drip markup for measuring conversions.
 */
function mantishub_drip() {
	global $g_mantishub_gen;

	# Started measuring conversion from gen 4.
	if ( $g_mantishub_gen < 4 ) {
		return;
	}

	# If auto-refresh page, then ignore it.
	if ( mantishub_auto_refresh_page() ) {
		return;
	}

	# Just trigger on the default page after login
	if ( !is_page_name( config_get_global( 'default_home_page' ) ) ||
		 !current_user_is_administrator() ) {
		return;
	}

	if( config_get( 'mantishub_analytics_enabled' ) == OFF ) {
		return;
	}

	# We want to only trigger the event for the account owner.
	$t_email = config_get_global( 'webmaster_email' );

	global $g_mantishub_info_trial;

	if ( $g_mantishub_info_trial ) {
		# A trial is worth $15
		$t_value = 1500;
		$t_event = "Started a Trial";
	} else {
		# Based on our calculations a paid conversion is worth $300
		$t_value = 30000;
		$t_event = "Converted to Paid";
	}

	echo <<<END

<script type="text/javascript">
  var _dcq = _dcq || [];
  var _dcs = _dcs || {};
  _dcs.account = '4007299';

  (function() {
    var dc = document.createElement('script');
    dc.type = 'text/javascript'; dc.async = true;
    dc.src = '//tag.getdrip.com/4007299.js';
    var s = document.getElementsByTagName('script')[0];
    s.parentNode.insertBefore(dc, s);

    window._dcq = window._dcq || [];
    window._dcq.push(["identify", {
      email: "$t_email"
    }]);
    window._dcq.push(["track", "$t_event", { value: $t_value }]);
  })();
</script>

END;
}

/**
 * Checks whether the current page was auto-refreshed rather than triggered by
 * a user action.
 *
 * @return boolean true: auto-refreshed, false: otherwise.
 */
function mantishub_auto_refresh_page() {
	return ( ( isset( $_GET['refresh'] ) && $_GET['refresh'] == 'true' ) );
}
