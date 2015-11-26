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

plan_ensure_allowed();

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

function plan_ensure_allowed() {
	if ( plan_gen() > 1 && plan_is_silver() ) {
		echo "<h1>Silver plan is no longer offered.</h1>";
		exit;
	}
}

function plan_price() {
	if ( plan_is_platinum() ) {
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

function plan_get_disk_usage() {
	$t_output = array();
	$t_result = 0;

	$t_root_path = dirname( dirname( __FILE__ ) );
	if ( !file_exists( $t_root_path . '/core.php' ) ) {
		return 'error';
	}

	# Possible exclusions, but won't be consistent with Stratus.
	# --exclude="backup/*" --exclude="log*/*"
	exec( 'du -c -b -h -s ' . $t_root_path, $t_output, $t_result );

	$t_output = $t_output[0];
	$t_line = str_replace( "\t", " ", $t_output );
	$t_index = strpos( $t_line, ' ' );

	return substr( $t_line, 0, $t_index );
}

function plan_get_disk_space_limit() {
	if ( plan_is_platinum() ) {
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

function plan_users_count() {
	return mantishub_table_row_count( 'user' );
}

function plan_issues_count() {
	return mantishub_table_row_count( 'bug' );
}

function plan_projects_count() {
	return mantishub_table_row_count( 'project' );
}

function plan_attachments_count() {
	return mantishub_table_row_count( 'bug_file' );
}

function plan_max_issues_string() {
	return lang_get( 'mantishub_plan_unlimited' );
}

function plan_max_users_string() {
	return lang_get( 'mantishub_plan_unlimited' );
}

function plan_max_attachments_string() {
	return lang_get( 'mantishub_plan_unlimited' );
}

function plan_max_team_members_string() {
	if ( plan_gen() == 1 ) {
		return lang_get( 'mantishub_plan_unlimited' );
	}

	if ( plan_is_platinum() ) {
		$t_value = '30';
	} else if ( plan_is_gold() ) {
		$t_value = '15';
	} else {
		$t_value = '5';
	}

	return $t_value;
}

function plan_max_projects_string() {
	if ( plan_is_platinum() ) {
		$t_value = lang_get( 'mantishub_plan_unlimited' );
	} else if ( plan_is_gold() ) {
		$t_value = lang_get( 'mantishub_plan_unlimited' );
	} else if ( plan_is_silver() ) {
		$t_value = '10';
	} else {
		$t_value = '1';
	}

	return $t_value;
}

function plan_user_packs_needed( $p_team_user_count ) {
	if ( plan_gen() == 1 ) {
		return 0;
	}

	# gen 2 and above has the user packs concept and doesn't have silver plan
	if ( plan_is_platinum() ) {
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

