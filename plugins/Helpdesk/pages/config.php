<?php
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

html_page_top();
print_manage_menu();
?>

<br/>
<div class="form-container">
<form action="<?php echo plugin_page( 'config_edit' ) ?>" method="post">
<?php echo form_security_field( 'plugin_Helpdesk_config' ) ?>
<table>

<thead>
	<tr>
		<td class="form-title" colspan="2"><?php echo plugin_lang_get( 'config_title' ) ?></td>
	</tr>
</thead>

<tbody>
	<tr>
		<td class="category" width="30%"><?php echo plugin_lang_get( 'enabled' ) ?></td>
		<td><input type="checkbox" name="enabled" <?php echo plugin_config_get( 'enabled' ) == 1 ? ' checked="checked"' : '' ?> /></td>
	</tr>
	<tr>
		<td class="category" width="30%"><?php echo plugin_lang_get( 'default_project' ) ?></td>
		<td>
			<select name="default_project">
				<option value="0"><?php echo plugin_lang_get( 'no_default_project' ); ?></option>
				<?php print_project_option_list( plugin_config_get( 'default_project' ), /* all projects */ false ); ?>
			</select>
		</td>
	</tr>
	<tr>
		<td class="category" width="30%"><?php echo plugin_lang_get( 'issue_reported_message' ) ?></td>
		<td>
			<textarea id="issue_reported_message" name="issue_reported_message" cols="80" rows="5"
			><?php echo string_textarea( plugin_config_get( 'issue_reported_message') ) ?></textarea>
			<br /><br />
			<?php echo string_display( plugin_lang_get( 'issue_reported_message_help' ) ); ?>
			<br /><br />
		</td>
	</tr>
	<tr>
		<td class="category" width="30%"><?php echo plugin_lang_get( 'failed_message' ) ?></td>
		<td>
			<textarea id="failed_message" name="failed_message" cols="80" rows="5"
			><?php echo string_textarea( plugin_config_get( 'failed_message' ) ) ?></textarea>
			<br /><br />
			<?php echo string_display( plugin_lang_get( 'failed_message_help' ) ); ?>
			<br /><br />
		</td>
	</tr>
</tbody>

<tfoot>
	<tr>
		<td class="center" colspan="2">
			<input type="submit" value="<?php echo plugin_lang_get("update_config") ?>"/>
		</td>
	</tr>
</tfoot>

</table>
</form>
</div>

<?php
html_page_bottom();
