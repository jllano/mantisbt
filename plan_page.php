<?php
require_once( 'core.php' );
require_api( 'database_api.php' );
require_api( 'mantishub_api.php' );
require_api( 'plan_api.php' );

auth_reauthenticate();

access_ensure_global_level( ADMINISTRATOR );

layout_page_header( lang_get( 'mantishub_plan_menu_option' ) );
layout_page_begin();
print_manage_menu( 'plan_page.php' );

function print_plan_form_header( $p_title ) {
	echo '<div class="col-md-12 col-xs-12">';
	echo '<div class="space-10"></div>';
	echo '<div class="form-container">';
	echo '<div class="widget-box widget-color-blue2">';
	echo '<div class="widget-header widget-header-small">';
	echo '<h4 class="widget-title lighter">';
	echo '<i class="ace-icon fa fa-shopping-cart"></i>';
	echo $p_title;
	echo '</h4>';
	echo '</div>';
	echo '<div class="widget-body">';
	echo '<div class="widget-main no-padding">';
	echo '<div class="table-responsive">';
	echo '<table class="table table-bordered table-condensed table-striped">';
	echo '<fieldset>';
}

function print_plan_form_footer() {
	echo '</fieldset>';
	echo '</table>';
	echo '</div></div></div>';
	layout_page_end();
}

function print_field( $p_label, $p_value ) {
	echo '<tr>' . "\n";
	echo '<td>' . $p_label . '</td>' . "\n";
	echo '<td>' . $p_value . '</td>' . "\n";
	echo '</tr>' . "\n";
}

function user_hyperlink( $p_user_id, $p_username ) {
	return '<a href="manage_user_edit_page.php?user_id=' . $p_user_id . '">' . $p_username . '</a>';
}

function team_info( $p_result ) {
	$t_team_members = array_merge( $p_result['global_users'], $p_result['project_users'] );

	$t_team_info = '';

	$t_team_count = count( $t_team_members );
	if ( $t_team_count > 0 ) {
		$t_usernames = array();

		foreach( $t_team_members as $t_user ) {
			$t_usernames[$t_user['username']] = user_hyperlink( $t_user['id'], $t_user['username'] );
		}

		ksort( $t_usernames );
		$t_team_info = '[' . implode( ', ', $t_usernames ) . ']';
		unset( $t_usernames );
	}

	return $t_team_info;
}

$t_result = mantishub_team_users_list_info();

print_plan_form_header( lang_get( 'mantishub_plan_information' ) );

$t_plan = plan_name();
if ( $g_mantishub_info_trial ) {
	$t_plan .= ' (' . lang_get( 'mantishub_plan_trial' ) . ')';
}

$t_value_of_limit = lang_get( 'mantishub_plan_value_of_limit' );

print_field( lang_get( 'mantishub_plan_level' ), $t_plan );
print_field( lang_get( 'mantishub_plan_projects_count' ), sprintf( $t_value_of_limit, plan_projects_count(), plan_max_projects_string() ) );
print_field( lang_get( 'mantishub_plan_issues_count' ), sprintf( $t_value_of_limit, plan_issues_count(), plan_max_issues_string() ) );
print_field( lang_get( 'mantishub_plan_users_count' ), sprintf( $t_value_of_limit, plan_users_count(), plan_max_users_string() ) );
print_field( lang_get( 'mantishub_plan_attachments_count' ), sprintf( $t_value_of_limit, plan_attachments_count(), plan_max_attachments_string() ) );

$t_team_members_value = sprintf( $t_value_of_limit, $t_result['count'], plan_max_team_members_string() );
$t_team_packs_needed = plan_user_packs_needed( $t_result['count'] );
if ( $t_team_packs_needed > 0 ) {
	$t_team_members_value .= sprintf( ' (%d team packs)', $t_team_packs_needed );
}

$t_team_members_value .= '<br /><br />' . team_info( $t_result );

print_field( lang_get( 'mantishub_plan_team_members' ), $t_team_members_value );

print_plan_form_footer();


