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

access_ensure_global_level( ADMINISTRATOR );

form_security_validate( 'manage_backup' );

$t_from = config_get( 'from_name' ) . ' <' . config_get( 'from_email' ) . '>';
$t_user_email = current_user_get_field( 'email' );

$t_backup_folder = mantishub_backup_folder();

file_put_contents( mantishub_in_progress_file(), date( config_get( 'normal_date_format' ) ) );

set_time_limit( 0 );

if ( !is_blank( $t_user_email ) ) {
	$t_cmd = 'echo "Will send you another email once the backup is ready for download." | mail -r "' . $t_from . '" -s "Preparing your backup" ' . $t_user_email;
	exec( $t_cmd );
}

$t_db_hostname = config_get( 'hostname' );
$t_db_username = config_get( 'db_username' );
$t_db_password = config_get( 'db_password' );
$t_db_name = config_get( 'database_name' );

$t_backup_data_folder = $t_backup_folder . 'data/';
$t_instance_root = dirname( __FILE__ ) . '/';
$t_config_folder = $t_instance_root . 'config/';
$t_original_current_directory = getcwd();

exec( 'rm ' . $t_backup_folder . 'mantishub_*.zip' );
exec( 'rm ' . $t_backup_folder . 'mantishub_*.tar.gz' );

$t_cmd = "rm -rf $t_backup_data_folder";
exec( $t_cmd );

$t_cmd = "mkdir $t_backup_data_folder";
exec( $t_cmd );

mantishub_copy_file( $t_instance_root . 'images/logo.png', $t_backup_data_folder . 'logo.png' );
mantishub_copy_file( $t_config_folder . 'custom_config_inc.php', $t_backup_data_folder . 'custom_config_inc.php' );
mantishub_copy_file( $t_config_folder . 'custom_constant_inc.php', $t_backup_data_folder . 'custom_constant_inc.php' );
mantishub_copy_file( $t_config_folder . 'custom_constants_inc.php', $t_backup_data_folder . 'custom_constants_inc.php' );
mantishub_copy_file( $t_config_folder . 'custom_strings_inc.php', $t_backup_data_folder . 'custom_strings_inc.php' );
mantishub_copy_file( $t_config_folder . 'custom_relationships_inc.php', $t_backup_data_folder . 'custom_relationships_inc.php' );

$t_cmd = "mysqldump -h $t_db_hostname -u $t_db_username -p$t_db_password $t_db_name > " . $t_backup_data_folder . "db.sql";
exec( $t_cmd );

mantishub_delete_file( mantishub_backup_data_file() );
chdir( dirname( $t_backup_data_folder ) );
$t_cmd = 'zip ' . mantishub_backup_data_file() . ' -r -9 data';
exec( $t_cmd );

$t_cmd = "rm -rf $t_backup_data_folder";
exec( $t_cmd );

$t_attach_folder = dirname( __FILE__ ) . '/attach';

mantishub_delete_file( mantishub_backup_attach_file() );
chdir( dirname( $t_attach_folder ) );
$t_cmd = 'zip ' . mantishub_backup_attach_file() . ' -r -9 attach';
exec( $t_cmd );

mantishub_delete_file( mantishub_in_progress_file() );

if ( !is_blank( $t_user_email ) ) {
	$t_cmd = 'echo "Go to Manage > Backups and download it from there." | mail -r "' . $t_from . '" -s "Your backup is ready" ' . $t_user_email;
	exec( $t_cmd );
}

chdir( $t_original_current_directory );

print_successful_redirect( 'manage_backup_page.php' );

/**
 * Copy file from source to destination.  If file doesn't exists, skip without errors.
 * @param  string $p_source_path The source file path.
 * @param  string $p_dest_path   The destination file path.
 * @return bool true if copied successfully or not found, otherwise false.
 */
function mantishub_copy_file( $p_source_path, $p_dest_path ) {
	if( !file_exists( $p_source_path ) ) {
		return true;
	}

	return copy( $p_source_path, $p_dest_path );
}

/**
 * Deletes a file from the specified path.  If file doesn't exist skip without errors.
 * @param  string $p_file_path The file path.
 * @return bool true: file didn't exist or was deleted, false otherwise.
 */
function mantishub_delete_file( $p_file_path ) {
	if ( !file_exists( $p_file_path ) ) {
		return true;
	}

	return unlink( $p_file_path );
}

