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

html_page_top( plugin_lang_get( 'title' ) );

print_manage_menu( );
?>

<div id="graph-config-div" class="form-container">
	<form id="graph-config-form" action="<?php echo plugin_page( 'config_edit' )?>" method="post">
		<fieldset>
			<legend><span><?php echo plugin_lang_get( 'title' ) . ': ' . plugin_lang_get( 'config' )?></span></legend>
			<?php echo form_security_field( 'plugin_graph_config_edit' ) ?>

			<input type="hidden" name="eczlibrary" value="1" />
			<input type="hidden" name="font" value="verdana" />
			<input type="hidden" name="jpgraph_path" value="" />
			<input type="hidden" name="jpgraph_antialias" value="1" />

			<div class="field-container">
				<label><span><?php echo plugin_lang_get( 'window_width' )?></span></label>
				<span class="input">
					<input type="text" name="window_width" value="<?php echo plugin_config_get( 'window_width' )?>" />
				</span>
				<span class="label-style"></span>
			</div>

			<div class="field-container">
				<label><span><?php echo plugin_lang_get( 'bar_aspect' )?></span></label>
				<span class="input">
					<input type="text" name="bar_aspect" value="<?php echo plugin_config_get( 'bar_aspect' )?>" />
				</span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label><span><?php echo plugin_lang_get( 'summary_graphs_per_row' )?></span></label>
				<span class="input">
					<input type="text" name="summary_graphs_per_row" value="<?php echo plugin_config_get( 'summary_graphs_per_row' )?>" />
				</span>
				<span class="label-style"></span>
			</div>

			<span class="submit-button"><input type="submit" class="button" value="<?php echo lang_get( 'change_configuration' )?>" /></span>
		</fieldset>
	</form>
</div>

<?php
html_page_bottom();
