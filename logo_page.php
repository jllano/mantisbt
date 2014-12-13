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

/**
 * @package MantisBT
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */
 /**
  * MantisBT Core API's
  */
require_once( 'core.php' );

auth_reauthenticate();

html_page_top( 'Logo' );

print_manage_menu( 'adm_permissions_report.php' );
print_manage_config_menu( 'logo_page.php' );
?>
<br />
<div id="manage-logo-div" class="form-container">
	<form id="manage-logo-form" name="manage-logo-form" method="post" enctype="multipart/form-data" action="logo_set.php">
		<?php echo form_security_field( 'logo' ); ?>

		<fieldset class="has-required">
			<legend><span>Update Branding</span></legend>
			<div class="field-container">
				<label for="name"><span class="required">*</span> <span>Company Name</span></label>
				<input <?php echo helper_get_tab_index() ?> id="name" name="name" type="text" size="50" value="<?php echo config_get( 'window_title' ); ?>" />
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="logo_file"><span class="required">*</span> <span>Logo file (smaller than 50K)</span></label>
				<input <?php echo helper_get_tab_index() ?> id="logo_file" name="logo_file" type="file" size="50" />
				<span class="label-style"></span>
			</div>
			<span class="submit-button"><input <?php echo helper_get_tab_index() ?> type="submit" class="button" value="Upload" /></span>
		</fieldset>
	</form>
</div>

<?php
html_page_bottom();
