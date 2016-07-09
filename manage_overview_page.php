<?php
# MantisBT - A PHP based bugtracking system

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

/**
 * Overview Page
 *
 * @package MantisBT
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses event_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'current_user_api.php' );
require_api( 'event_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );

auth_reauthenticate();
access_ensure_global_level( config_get( 'manage_site_threshold' ) );

layout_page_header( lang_get( 'manage_link' ) );

layout_page_begin( __FILE__ );

print_manage_menu( 'manage_overview_page.php' );

$t_package_type_file_path = dirname( __FILE__ ) . '/package_type.txt';
$t_package_type = 'mantishub-1.2.x';

if ( file_exists( $t_package_type_file_path ) ) {
	$t_package_type = trim( @file_get_contents( $t_package_type_file_path ) );
}

if ( $t_package_type == 'mantishub-1.2.x' || $t_package_type == 'mantishub-1.3.x' || $t_package_type == 'mantishub-1.3.x-m' ) {

	if ( $t_package_type == 'mantishub-1.3.x-m' ) {
		$t_title = lang_get( 'mantishub_switch_to_classic_ui' );
		$t_image_prefix = 'classic';
		$t_target_package_type = 'mantishub-1.3.x';
	} else {
		$t_title = lang_get( 'mantishub_switch_to_modern_ui' );
		$t_image_prefix = 'modern';
		$t_target_package_type = 'mantishub-1.3.x-m';
	}
?>

<center>

<br />
<br />

<h1><?php echo $t_title; ?></h1>
<form method="post" action="package_type.php">
	<?php echo form_security_field( 'package_type' ); ?>
	<input type="hidden" name="package_type" value="<?php echo $t_target_package_type; ?>" />
	<input type="submit" value="<?php echo lang_get( 'mantishub_switch_now' ) ?>" />
</form>

<br />
<br />
<br />

<img src="images/mantishub/<?php echo $t_image_prefix; ?>_view_issues.png" width="600" border="1"  />
<br />
<br />
<br />

<img src="images/mantishub/<?php echo $t_image_prefix; ?>_my_view.png" width="600" border="1" />
<br />
<br />
<br />

<img src="images/mantishub/<?php echo $t_image_prefix; ?>_view_issue.png" width="600" border="1" />
<br />

</center>

<?php
}

# Don't expose internal information on MantisHub.
layout_page_end();
exit;
?>

<div class="col-md-12 col-xs-12">
	<div class="space-10"></div>
	<div class="widget-box widget-color-blue2">
	<div class="widget-header widget-header-small">
		<h4 class="widget-title lighter">
			<i class="ace-icon fa fa-info"></i>
			<?php echo lang_get('site_information') ?>
		</h4>
	</div>
	<div class="widget-body">
	<div class="widget-main no-padding">
	<div class="table-responsive">
	<table id="manage-overview-table" class="table table-hover table-bordered table-condensed">
		<tr>
			<th class="category"><?php echo lang_get( 'mantis_version' ) ?></th>
			<td><?php echo MANTIS_VERSION . config_get_global( 'version_suffix' ) ?></td>
		</tr>
		<tr>
			<th class="category"><?php echo lang_get( 'schema_version' ) ?></th>
			<td><?php echo config_get( 'database_version' ) ?></td>
		</tr>
		<tr class="spacer">
			<td colspan="2"></td>
		</tr>
		<tr class="hidden"></tr>
	<?php
	$t_is_admin = current_user_is_administrator();
	if( $t_is_admin ) {
	?>
		<tr>
			<th class="category"><?php echo lang_get( 'site_path' ) ?></th>
			<td><?php echo config_get( 'absolute_path' ) ?></td>
		</tr>
		<tr>
			<th class="category"><?php echo lang_get( 'core_path' ) ?></th>
			<td><?php echo config_get( 'core_path' ) ?></td>
		</tr>
		<tr>
			<th class="category"><?php echo lang_get( 'plugin_path' ) ?></th>
			<td><?php echo config_get( 'plugin_path' ) ?></td>
		</tr>
	<?php
	}

	event_signal( 'EVENT_MANAGE_OVERVIEW_INFO', array( $t_is_admin ) )
	?>
	</table>
	</div>
	</div>
	</div>
	</div>
</div>
<?php
layout_page_end();

