<?php
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

layout_page_header( plugin_lang_get( 'title' ) );
layout_page_begin( 'manage_overview_page.php' );
print_manage_menu( 'manage_plugin_page.php' );
?>

<div class="col-md-12 col-xs-12">
	<div class="space-10"></div>
	<div class="form-container">
<form action="<?php echo plugin_page( 'config_edit' ) ?>" method="post">
<?php echo form_security_field( 'plugin_Helpdesk_config' ) ?>
	<div class="widget-box widget-color-blue2">
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<i class="ace-icon fa fa-tasks"></i>
				<?php echo plugin_lang_get( 'config_title' ) ?>
			</h4>
		</div>
		<div class="widget-body">
			<div class="widget-main no-padding">
				<div class="table-responsive">
					<table class="table table-bordered table-condensed table-striped">
<tbody>
	<tr>
		<td class="category">
			<?php echo plugin_lang_get( 'enabled' ) ?>
		</td>
		<td>
			<input type="checkbox" class="ace" name="enabled" <?php echo plugin_config_get( 'enabled' ) == 1 ? ' checked="checked"' : '' ?> />
			<span class="lbl">
		</td>
	</tr>
	<tr>
		<td class="category"><?php echo plugin_lang_get( 'enable_unregistered' ) ?></td>
		<td>
			<input type="checkbox" class="ace" name="enable_unregistered"
			<?php check_checked( plugin_config_get( 'enable_unregistered' ), 1 ) ?> />
			<span class="lbl">
			<?php echo plugin_lang_get( 'enable_unregistered_label' ); ?>
			<br /><br />
			<p class="small">
				<i class="fa fa-info-circle grey"></i>
				<?php echo plugin_lang_get( 'enable_unregistered_help' ); ?>
			</p>
		</td>
	</tr>
	<tr>
		<td class="category">
			<?php echo plugin_lang_get( 'default_project' ) ?>
		</td>
		<td>
			<select name="default_project">
				<option value="0"><?php echo plugin_lang_get( 'no_default_project' ); ?></option>
				<?php print_project_option_list( plugin_config_get( 'default_project' ), /* all projects */ false ); ?>
			</select>
		</td>
	</tr>
	<tr>
		<td class="category">
			<?php echo plugin_lang_get( 'issue_reported_message' ) ?>
		</td>
		<td>
			<textarea id="issue_reported_message" name="issue_reported_message" class ="form-control" rows="5"
				><?php echo string_textarea( plugin_config_get( 'issue_reported_message') ) ?>
			</textarea>
			<br />
			<p class="small">
				<i class="fa fa-info-circle grey"></i>
				<?php echo string_display( plugin_lang_get( 'issue_reported_message_help' ) ); ?>
			</p>
		</td>
	</tr>
	<tr>
		<td class="category">
			<?php echo plugin_lang_get( 'failed_message' ) ?>
		</td>
		<td>
			<textarea id="failed_message" name="failed_message" class ="form-control" rows="5"
				><?php echo string_textarea( plugin_config_get( 'failed_message' ) ) ?>
			</textarea>
			<br />
			<p class="small">
				<i class="fa fa-info-circle grey"></i>
				<?php echo string_display( plugin_lang_get( 'failed_message_help' ) ); ?>
			</p>
		</td>
	</tr>
</tbody>

					</table>
				</div>
			</div>
			<div class="widget-toolbox padding-8 clearfix">
				<input type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo plugin_lang_get("update_config") ?>"/>
			</div>
		</div>
	</div>
</form>
</div>
</div>

<?php
layout_page_end();
