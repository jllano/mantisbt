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
 * Edit Graph Plugin Configuration
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

auth_reauthenticate( );
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

layout_page_header( plugin_lang_get( 'title' ) );

layout_page_begin( 'manage_overview_page.php' );

print_manage_menu( 'manage_plugin_page.php' );
?>

<div class="col-md-12 col-xs-12">
<div class="space-10"></div>
<div class="form-container" >
<form id="graph-config-form" action="<?php echo plugin_page( 'config_edit' )?>" method="post">
<?php echo form_security_field( 'plugin_graph_config_edit' ) ?>

<input type="hidden" name="font" value="verdana" />
<input type="hidden" name="jpgraph_path" value="" />
<input type="hidden" name="jpgraph_antialias" value="1" />
    
<div class="widget-box widget-color-blue2">
<div class="widget-header widget-header-small">
<h4 class="widget-title lighter">
	<i class="ace-icon fa fa-bar-chart-o"></i>
	<?php echo plugin_lang_get( 'title' ) . ': ' . plugin_lang_get( 'config' )?>
</h4>
</div>
<div class="widget-body">
<div class="widget-main no-padding">
	<div class="table-responsive">
<table class="table table-bordered table-condensed table-striped">

<tr>
	<th class="category width-40">
		<?php echo plugin_lang_get( 'window_width' )?>
	</th>
	<td class="center" colspan="2">
		<input type="text" name="window_width" class="input-sm" value="<?php echo plugin_config_get( 'window_width' )?>" />
	</td>
</tr>

<tr>
	<th class="category width-40">
		<?php echo plugin_lang_get( 'bar_aspect' )?>
	</th>
	<td class="center" colspan="2">
		<input type="text" name="bar_aspect" class="input-sm" value="<?php echo plugin_config_get( 'bar_aspect' )?>" />
	</td>
</tr>

<tr>
	<th class="category width-40">
		<?php echo plugin_lang_get( 'summary_graphs_per_row' )?>
	</th>
	<td class="center" colspan="2">
		<input type="text" name="summary_graphs_per_row" class="input-sm" value="<?php echo plugin_config_get( 'summary_graphs_per_row' )?>" />
	</td>
</tr>

</table>
</div>
</div>
<div class="widget-toolbox padding-8 clearfix">
	<input type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo lang_get( 'change_configuration' )?>" />
</div>
</div>
</div>
</form>
</div>
</div>

<?php
layout_page_end();
