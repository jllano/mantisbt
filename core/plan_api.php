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
	return plan_is_gold();
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
		$t_value = '49.95';
	} else if ( plan_is_gold() ) {
		$t_value = '24.95';
	} else if ( plan_is_silver() ) {
		$t_value = '19.95';
	} else {
		$t_value = '14.95';
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

	$t_user_packs = (int)ceil( $t_extra_users / 10 );

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

