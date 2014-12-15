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

layout_page_header( 'Logo'  );

layout_page_begin( 'manage_overview_page.php' );
print_manage_menu( 'adm_config_report.php' );
print_manage_config_menu( 'logo_page.php' );
?>
<div class="col-md-12 col-xs-12">
    <div class="space-10"></div>
    <div class="widget-box widget-color-blue2">
    <div class="widget-header widget-header-small">
        <h4 class="widget-title lighter">
            <i class="ace-icon fa fa-upload"></i>
            <?php echo 'Update Branding' ?>
        </h4>
    </div>
    <form id="manage-logo-form" name="manage-logo-form" method="post" enctype="multipart/form-data" action="logo_set.php">
    <div class="widget-body">
        <div class="widget-main no-padding">
        <div class="table-responsive">
        <table class="table table-bordered table-condensed">
            <tr class="field-container">
                <th class="category" width="30%">
                    <label for="name"><span>Company Name</span></label>
                </th>
                <td>
                    <input <?php echo helper_get_tab_index() ?> id="name" name="name" type="text" size="50" value="<?php echo config_get( 'window_title' ); ?>" />
                </td>
            </tr>
            <tr>
                <th class="category">
                    <label for="logo_file"><span>Logo file (smaller than 50K)</span></label>
                </th>
                <td width="70%">
                    <?php echo form_security_field( 'logo' ); ?>
                    <input <?php echo helper_get_tab_index() ?> id="logo_file" name="logo_file" type="file" size="50" />
                </td>
           </tr>
        </table>
        </div>
        </div>
    </div>
    <div class="widget-toolbox padding-8 clearfix">
        <input <?php echo helper_get_tab_index() ?> type="submit" class="btn btn-primary btn-white btn-round" value="Update" />
    </div>
    </form>
    </div>
</div>
<?php
layout_page_end();
