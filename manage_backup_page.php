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

html_page_top( 'Backup' );

print_manage_menu( 'manage_backup_page.php' );

access_ensure_global_level( ADMINISTRATOR );

echo '<br />';

if ( mantishub_backup_in_progress() ) {
	echo 'Backup started on ' . file_get_contents( mantishub_in_progress_file() ) . ' and is still in progress.<br />';
} else {
	$t_backup_timestamp = 0;

	$t_backup_data_file = mantishub_backup_data_file();
	if ( file_exists( $t_backup_data_file ) ) {
		$t_file_size = number_format( filesize( $t_backup_data_file ) / 1024 );
		$t_backup_timestamp = filemtime( $t_backup_data_file );
		$t_file_timestamp = date( config_get( 'normal_date_format' ), $t_backup_timestamp );
		echo 'Download <a href="manage_backup_download.php?type=data">database and configuration</a> (' . $t_file_size . 'KB created on ' . $t_file_timestamp . ').<br />';
	}

	$t_backup_attach_file = mantishub_backup_attach_file();
	if ( file_exists( $t_backup_attach_file ) ) {
		$t_file_size = number_format( filesize( $t_backup_attach_file ) / 1024 );
		$t_backup_timestamp = filemtime( $t_backup_attach_file );
		$t_file_timestamp = date( config_get( 'normal_date_format' ), $t_backup_timestamp );
		echo 'Download <a href="manage_backup_download.php?type=attach">attachments</a> (' . $t_file_size . 'KB created on ' . $t_file_timestamp . ').<br />';
	}

	if ( $t_backup_timestamp == 0 || ( time() - $t_backup_timestamp ) > 60 ) {
?>

<br />
Start a new backup. <b>It may take a couple of minutes.</b><br />
<form name="manage_backup_form" method="post" enctype="multipart/form-data" action="manage_backup.php">
<?php echo form_security_field( 'manage_backup' ); ?>
	<input <?php echo helper_get_tab_index() ?> type="submit" class="button" value="Request Backup" />
</form>
<?php
	} else {
		echo "<br />You have created a backup in the last minute.<br />";
	}
}

html_page_bottom();
