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
	 * Add file and redirect to the referring page
	 * @package MantisBT
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */

	$g_bypass_headers = true; # suppress headers as we will send our own later
	define( 'COMPRESSION_DISABLED', true );

	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	auth_ensure_user_authenticated();
	access_ensure_global_level( ADMINISTRATOR );

	if ( mantishub_backup_in_progress() ) {
		echo 'Backup is still in progress.';
		exit;
	}

	$f_type = gpc_get_string( 'type' );

	switch ( $f_type ) {
		case 'data':
			$t_filename = mantishub_backup_data_file_name();
			$t_local_disk_file = mantishub_backup_data_file();
			break;
		case 'attach':
			$t_filename = mantishub_backup_attach_file_name();
			$t_local_disk_file = mantishub_backup_attach_file();
			break;
		default:
			access_denied();
	}

	if ( !file_exists( $t_local_disk_file ) ) {
		echo "Requested file doesn't exist.";
		exit;
	}

	# throw away output buffer contents (and disable it) to protect download
	while ( @ob_end_clean() );

	if ( ini_get( 'zlib.output_compression' ) && function_exists( 'ini_set' ) ) {
		ini_set( 'zlib.output_compression', false );
	}

	http_security_headers();

	# Make sure that IE can download the attachments under https.
	header( 'Pragma: public' );

	# To fix an IE bug which causes problems when downloading
	# attached files via HTTPS, we disable the "Pragma: no-cache"
	# command when IE is used over HTTPS.
	global $g_allow_file_cache;
	if ( http_is_protocol_https() && is_browser_internet_explorer() ) {
		# Suppress "Pragma: no-cache" header.
	} else {
		if ( !isset( $g_allow_file_cache ) ) {
		    header( 'Pragma: no-cache' );
		}
	}

	header( 'Expires: ' . gmdate( 'D, d M Y H:i:s \G\M\T', time() ) );
	header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s \G\M\T', $v_date_added ) );
	header( 'X-Content-Type-Options: nosniff' );

	http_content_disposition_header( $t_filename, /* show_inline */ false );

	header( 'Content-Length: ' . filesize( $t_local_disk_file ) );
	header( 'Content-Type: application/x-gzip-compressed' );

	readfile( $t_local_disk_file );
