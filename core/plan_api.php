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
	return (int)config_get_global( 'mantishub_gen' );
}

function plan_mail_reporting() {
	return ( ( plan_gen() == 1 && is_gold() ) || is_platinum() );
}

function plan_mantistouch() {
	return !is_bronze();
}

function plan_ensure_allowed() {
	if ( plan_gen() > 1 && is_silver() ) {
		echo "<h1>Silver plan is no longer offered.</h1>";
		exit;
	}
}

function plan_price() {
	if ( is_platinum() ) {
		$t_value = '49.95';
	} else if ( is_gold() ) {
		$t_value = '24.95';
	} else if ( is_silver() ) {
		$t_value = '19.95';
	} else {
		$t_value = '14.95';
	}

	return $t_value;
}

function is_platinum() {
	global $g_mantishub_plan;
	return $g_mantishub_plan == 'Platinum';
}

function is_gold() {
	global $g_mantishub_plan;
	return $g_mantishub_plan == 'Gold';
}

function is_silver() {
	global $g_mantishub_plan;
	return $g_mantishub_plan == 'Silver';
}

function is_bronze() {
	global $g_mantishub_plan;
	return $g_mantishub_plan == 'Bronze';
}

