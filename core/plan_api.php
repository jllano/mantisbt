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

function plan_gen() {
	global $g_mantishub_gen;
	return $g_mantishub_gen;
}

function plan_mail_reporting() {
	return !plan_is_bronze() && !plan_is_silver();
}

function plan_mantistouch() {
	return !plan_is_bronze();
}

function plan_price() {
	global $g_mantishub_plan_code;

	if ( $g_mantishub_plan_code == 'platinum_1000' || $g_mantishub_plan_code == 'platinum12_1000' ) {
		$t_value = '500';
	} else if ( $g_mantishub_plan_code == 'platinum_500' || $g_mantishub_plan_code == 'platinum12_500' ) {
		$t_value = '350';
	} else if ( $g_mantishub_plan_code == 'platinum_300' || $g_mantishub_plan_code == 'platinum12_300' ) {
		$t_value = '300';
	} else if ( $g_mantishub_plan_code == 'platinum_200' || $g_mantishub_plan_code == 'platinum12_200' ) {
		$t_value = '250';
	} else  if ( plan_is_enterprise() ) {
		$t_value = '300';
	} else if ( plan_is_platinum() ) {
		$t_value = '50';
	} else if ( plan_is_gold() ) {
		$t_value = '25';
	} else if ( plan_is_silver() ) {
		$t_value = '20';
	} else {
		$t_value = '15';
	}

	return $t_value;
}

function plan_get_disk_usage_in_bytes() {
	$t_query = 'SELECT SUM(filesize) FROM {bug_file}';
	$t_result = db_query( $t_query );
	return db_result( $t_result );
}

function plan_get_disk_usage() {
	$t_root_path = dirname( dirname( __FILE__ ) );
	if ( !file_exists( $t_root_path . '/core.php' ) ) {
		return 'error';
	}

	$t_total_attachments_size = plan_get_disk_usage_in_bytes();

	if( $t_total_attachments_size > 1024 * 1024 * 1024 ) {
		$t_total_attachments_size = $t_total_attachments_size / ( 1024 * 1024 * 1024 );
		$t_unit = 'GB';
	} else {
		$t_total_attachments_size = (float)( $t_total_attachments_size / ( 1024 * 1024 ) );
		$t_unit = 'MB';
	}

	$t_value = sprintf( "%.2f%s", $t_total_attachments_size, $t_unit );

	return $t_value;
}

function plan_get_disk_space_limit_in_mb() {
	global $g_mantishub_plan_code;

	if ( $g_mantishub_plan_code == 'platinum_1000' || $g_mantishub_plan_code == 'platinum12_1000' ) {
		$t_value = 100 * 1024;
	} else if ( $g_mantishub_plan_code == 'platinum_500' || $g_mantishub_plan_code == 'platinum12_500' ) {
		$t_value = 50 * 1024;
	} else if ( $g_mantishub_plan_code == 'platinum_300' || $g_mantishub_plan_code == 'platinum12_300' ) {
		$t_value = 30 * 1024;
	} else if ( $g_mantishub_plan_code == 'platinum_200' || $g_mantishub_plan_code == 'platinum12_200' ) {
		$t_value = 30 * 1024;
	} else if ( plan_is_enterprise() ) {
		$t_value = 4 * 1024;
	} else if ( plan_is_platinum() ) {
		$t_value = 10 * 1024;
	} else if ( plan_is_gold() ) {
		$t_value = 4 * 1024;
	} else if ( plan_is_silver() ) {
		$t_value = 2 * 1024;
	} else {
		$t_value = 200;
	}

	return $t_value;
}

function plan_get_disk_space_limit() {
	global $g_mantishub_plan_code;

	if ( $g_mantishub_plan_code == 'platinum_1000' || $g_mantishub_plan_code == 'platinum12_1000' ) {
		$t_value = '100GB';
	} else if ( $g_mantishub_plan_code == 'platinum_500' || $g_mantishub_plan_code == 'platinum12_500' ) {
		$t_value = '50GB';
	} else if ( $g_mantishub_plan_code == 'platinum_300' || $g_mantishub_plan_code == 'platinum12_300' ) {
		$t_value = '30GB';
	} else if ( $g_mantishub_plan_code == 'platinum_200' || $g_mantishub_plan_code == 'platinum12_200' ) {
		$t_value = '30GB';
	} else if ( plan_is_enterprise() ) {
		$t_value = '100GB';
	} else if ( plan_is_platinum() ) {
		$t_value = '10GB';
	} else if ( plan_is_gold() ) {
		$t_value = '4GB';
	} else if ( plan_is_silver() ) {
		$t_value = '2GB';
	} else {
		$t_value = '200MB';
	}

	return $t_value;
}

function plan_storage_packs() {
	$t_used_mbs = plan_get_disk_usage_in_bytes() / ( 1024 * 1024 );
	$t_plan_mbs = plan_get_disk_space_limit_in_mb();

	if( $t_plan_mbs > $t_used_mbs ) {
		return 0;
	}

	if( $t_used_mbs < 1 ) {
		return 0;
	}

	return ceil( (float) ( ( $t_used_mbs - $t_plan_mbs ) / 1024 ) );
}

function plan_users_count() {
	return mantishub_table_row_count( 'user', 'enabled = 1' );
}

function plan_issues_count() {
	return mantishub_table_row_count( 'bug' );
}

function plan_projects_count() {
	return mantishub_table_row_count( 'project', 'enabled = 1' );
}

function plan_attachments_count() {
	return mantishub_table_row_count( 'bug_file' );
}

function plan_max_issues_string() {
	return lang_get( 'mantishub_plan_unlimited' );
}

function plan_max_users_string() {
	if ( plan_gen() < 5 ) {
		return lang_get( 'mantishub_plan_unlimited' );
	}

	return plan_max_team_members_string();	
}

function plan_max_attachments_string() {
	return lang_get( 'mantishub_plan_unlimited' );
}

function plan_max_team_members_string() {
	if ( plan_gen() == 1 ) {
		return lang_get( 'mantishub_plan_unlimited' );
	}

	global $g_mantishub_plan_code;

	if ( $g_mantishub_plan_code == 'platinum_1000' || $g_mantishub_plan_code == 'platinum12_1000' ) {
		$t_value = '1000';
	} else if ( $g_mantishub_plan_code == 'platinum_500' || $g_mantishub_plan_code == 'platinum12_500' ) {
		$t_value = '500';
	} else if ( $g_mantishub_plan_code == 'platinum_300' || $g_mantishub_plan_code == 'platinum12_300' ) {
		$t_value = '300';
	} else if ( $g_mantishub_plan_code == 'platinum_200' || $g_mantishub_plan_code == 'platinum12_200' ) {
		$t_value = '200';
	} else if ( plan_is_enterprise() ) {
		$t_value = '50';
	} else if ( plan_is_platinum() ) {
		$t_value = '30';
	} else if ( plan_is_gold() ) {
		$t_value = '15';
	} else {
		$t_value = '5';
	}

	return $t_value;
}

function plan_max_projects_string() {
	if ( plan_is_silver() ) {
		$t_value = '10';
	} else if ( plan_is_bronze() ) {
		$t_value = '1';
	} else {
		$t_value = lang_get( 'mantishub_plan_unlimited' );
	}

	return $t_value;
}

function plan_user_packs_needed( $p_team_user_count ) {
	if ( plan_gen() == 1 ) {
		return 0;
	}

	global $g_mantishub_plan_code;

	# gen 2 and above has the user packs concept and doesn't have silver plan
	if ( $g_mantishub_plan_code == 'platinum_1000' || $g_mantishub_plan_code == 'platinum12_1000' ) {
		$t_included_in_plan = 1000;
	} else if ( $g_mantishub_plan_code == 'platinum_500' || $g_mantishub_plan_code == 'platinum12_500' ) {
		$t_included_in_plan = 500;
	} else if ( $g_mantishub_plan_code == 'platinum_300' || $g_mantishub_plan_code == 'platinum12_300' ) {
		$t_included_in_plan = 300;
	} else if ( $g_mantishub_plan_code == 'platinum_200' || $g_mantishub_plan_code == 'platinum12_200' ) {
		$t_included_in_plan = 200;
	} else if ( plan_is_enterprise() ) {
		$t_included_in_plan = 50;
	} else if ( plan_is_platinum() ) {
		$t_included_in_plan = 30;
	} else if ( plan_is_gold() ) {
		$t_included_in_plan = 15;
	} else {
		$t_included_in_plan = 5;
	}

	$t_extra_users = $p_team_user_count - $t_included_in_plan;
	if ( $t_extra_users <= 0 ) {
		return 0;
	}

	$t_user_packs = (int)ceil( $t_extra_users / 5 );

	return $t_user_packs;
}

function plan_name() {
	global $g_mantishub_plan;
	return $g_mantishub_plan;
}

function plan_is_enterprise() {
	return plan_name() == 'Enterprise';
}

function plan_is_platinum() {
	return plan_name() == 'Platinum';
}

function plan_is_gold() {
	return plan_name() == 'Gold';
}

function plan_is_silver() {
	return plan_name() == 'Silver';
}

function plan_is_bronze() {
	return plan_name() == 'Bronze';
}

function plan_info_file_path() {
	global $g_config_path;
	$t_json_filename = $g_config_path . 'info.json';
	return $t_json_filename;
}

function plan_update_info( $p_force_refresh = false ) {
	global $g_mantishub_info_trial, $g_mantishub_info_creation_date;
	$t_json_filename = plan_info_file_path();

	# If force refresh, json file doesn't exist, or older than 3-6 hours, then update it.
	# The reason for the randomness is to distribute the time where instances update so it
	# is not synchronized and hence reducing load.
	if( $p_force_refresh ||
	    !file_exists( $t_json_filename ) ||
	    ( time() - filemtime( $t_json_filename ) ) > rand( 3 * 3600, 6 * 3600 ) ) {
		$t_root_path = dirname( dirname( __FILE__ ) ) . '/';
		$t_info = array();
		$t_info['generation'] = plan_gen();
		$t_info['package_type'] = trim( @file_get_contents( $t_root_path . 'package_type.txt' ) );
		$t_info['package_timestamp'] = trim( @file_get_contents( $t_root_path . 'package_timestamp.txt' ) );
		$t_info['trial'] = $g_mantishub_info_trial;
		$t_info['plan'] = plan_name();

		$t_issues_count = plan_issues_count();

		$t_info['users_count'] = plan_users_count();
		$t_info['team_count'] = plan_team_count();
		$t_info['projects_count'] = plan_projects_count();
		$t_info['issues_count'] = $t_issues_count;
		$t_info['team_packs'] = plan_user_packs_needed( $t_info['team_count'] );
		$t_info['attachments_count'] = plan_attachments_count();
		$t_info['email_queue_count'] = mantishub_table_row_count( 'email' );
		$t_info['server_ip'] = $_SERVER['SERVER_ADDR'];
		$t_info['logo'] = file_exists( dirname( __FILE__ ) . '/images/logo.png' );
		$t_info['creation_timestamp'] = strftime( '%m/%d/%Y %H:%M:%S', $g_mantishub_info_creation_date );
		if ( $t_issues_count > 0 ) {
			$t_info['last_activity_timestamp'] = strftime( '%m/%d/%Y %H:%M:%S', mantishub_last_issue_update() );
		} else {
			$t_info['last_activity_timestamp'] = $t_info['creation_timestamp'];
		}

		# Add fields that we don't want to disclose on the web, but just internally on the server
		# for cronjobs to use.
		$t_info['administrator_email'] = config_get_global( 'mantishub_info_administrator_email' );
		$t_info['administrator_firstname'] = config_get_global( 'mantishub_info_administrator_firstname' );
		$t_info['administrator_lastname'] = config_get_global( 'mantishub_info_administrator_lastname' );

		$t_info['company'] = config_get_global( 'mantishub_info_company' );

		# TODO: Update for enterprise
		$t_info['value'] = plan_price() + $t_info['team_packs'] * 10;

		$t_output = array();
		exec( 'hostname', $t_output );
		$t_info['hostname'] = mantishub_strip_domain( $t_output[0] );

		# In dev machine, this access may not be granted
		$t_json = json_encode( $t_info );
		@file_put_contents( $t_json_filename, $t_json );
	}

	# return void.
	return;
}

function plan_info_get_public( $p_force_push = false ) {
	plan_update_info( $p_force_push );

	$t_json_filename = plan_info_file_path();
	if( !file_exists( $t_json_filename ) ) {
		return null;
	}

	$t_json = @file_get_contents( $t_json_filename );
	if( $t_json === false ) {
		return null;
	}

	$t_info = json_decode( $t_json );
	if ( $t_info === null ) {
		return null;
	}

	$t_public_fields = array(
		'package_timestamp',
		'creation_timestamp',
		'last_activity_timestamp',
		'issues_count',
		'projects_count',
		'users_count',
		'team_count',
		'team_packs',
		'attachments_count',
		'email_queue_count',
		'server_ip',
		'trial',
		'logo',
		'plan',
		'package_type',
		'generation'
	);

	$t_result = array();

	foreach( $t_public_fields as $t_field ) {
		$t_result[$t_field] = $t_info->$t_field;
	}

	return $t_result;
}

function plan_show_user_limits_on_plan_page() {
	return !plan_is_enterprise();
}

function plan_show_storage_limits_on_plan_page() {
	return true;
}

function plan_show_user_list_on_plan_page() {
	return plan_gen() < 5;
}

function plan_access_level_to_charge() {
	if ( plan_gen() >= 5 ) {
		$t_handle_bug_threshold = VIEWER;
	} else {
		$t_handle_bug_threshold = config_get( 'handle_bug_threshold' );
		if( is_array( $t_handle_bug_threshold ) ) {
			$t_min = ADMINISTRATOR;

			foreach( $t_handle_bug_threshold as $t_access_level ) {
				if( $t_access_level < $t_min ) {
					$t_min = $t_access_level;
				}
			}

			$t_handle_bug_threshold = $t_min;
		}
	}

	return $t_handle_bug_threshold;
}

