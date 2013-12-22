<?php
/**
 * @package MantisHub
 * @copyright Copyright (C) 2013 - 2014 Victor Boctor - vboctor@gmail.com
 * @link http://www.mantishub.com
 */

/**
 * MantisBT Core API's
 */
require_once( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'core.php' );

/**
 * Echos the intercom javascript calls with the appropriate MantisHub specific data.
 * It should be called just before the closing html body tag.
 */
function mantishub_intercom() {
	// MantisHub Intercom-IO
	if ( auth_is_user_authenticated() ) {
		$t_user_email = current_user_get_field( 'email' );

		if ( current_user_is_administrator() && stristr( $t_user_email, "@localhost" ) === false ) {
			global $g_mantishub_plan;
			$t_plan = $g_mantishub_plan;

			if ( is_gold() ) {
				$t_spend = 25;
			} else if ( is_silver() ) {
				$t_spend = 20;
			} else {
				$t_spend = 15;
			}

			$t_path = config_get( 'path' );
			$t_path = str_ireplace( 'https://', '', $t_path );
			$t_path = str_ireplace( 'http://', '', $t_path );

			// Use the database as the company id since it will never change.
			// The instance name may change due to renaming the instance or using custom domain.
			$t_company_id = config_get( 'database_name' );

			$t_company_name = $t_path;

			if ( stristr( $t_path, 'localhost' ) !== false ) {
				$t_company_name = 'localhost';
			} else if ( stristr( $t_path, '.mantishub.com' ) !== false ) {
				$t_index = strpos( $t_path, '.' );
				$t_company_name = substr( $t_path, 0, $t_index );
			}

	 		$t_user_created = current_user_get_field( 'date_created' );
	 		$t_user_language = user_pref_get_language( auth_get_current_user_id() );

	 		$t_issues_count = mantishub_table_row_count( db_get_table( 'mantis_bug_table' ) );
	 		$t_users_count = mantishub_table_row_count( db_get_table( 'mantis_user_table' ) );
	 		$t_projects_count = mantishub_table_row_count( db_get_table( 'mantis_project_table' ) );

			echo '<script id="IntercomSettingsScriptTag">';
			echo 'window.intercomSettings = {';
			echo 'email: "' . $t_user_email . '",';
			echo 'created_at: ' . $t_user_created . ',';
			echo '"language": "' . $t_user_language . '",';
			echo '"company": {';
			echo 'id: "' . $t_company_id . '",';
			echo 'name: "' . $t_company_name . '",';
			echo 'created_at: ' . $t_user_created . ',';
			echo '"issues_count": ' . $t_issues_count . ',';
			echo '"projects_count": ' . $t_projects_count . ',';
			echo '"users_count": ' . $t_users_count . ',';
			echo 'plan: "' . $t_plan . '",';
			echo 'monthly_spend: ' . $t_spend; 
			echo '},';
			echo 'app_id: "eb7d1d2171933b95f1ecb4fc4d1db866879776d2"';
			echo '}';
			echo '</script>';

			echo <<< HTML
				<script>(function(){var w=window;var ic=w.Intercom;if(typeof ic==="function"){ic('reattach_activator');ic('update',intercomSettings);}else{var d=document;var i=function(){i.c(arguments)};i.q=[];i.c=function(args){i.q.push(args)};w.Intercom=i;function l(){var s=d.createElement('script');s.type='text/javascript';s.async=true;s.src='https://static.intercomcdn.com/intercom.v1.js';var x=d.getElementsByTagName('script')[0];x.parentNode.insertBefore(s,x);}if(w.attachEvent){w.attachEvent('onload',l);}else{w.addEventListener('load',l,false);}}})()</script>
HTML;
		}
	}	
}

/**
 * Counts the number of rows in the specified table name.
 * The table name must be the output of calls to db_get_table().
 */
function mantishub_table_row_count( $p_table ) {
	$t_table = $p_table;
	$query = "SELECT COUNT(*) FROM $t_table";
	$result = db_query_bound( $query );
	$t_users = db_result( $result );

	return $t_users;
}
