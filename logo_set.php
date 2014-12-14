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
 * This page stores the reported bug
 *
 * @package MantisBT
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */
 /**
  * MantisBT Core API's
  */
require_once( 'core.php' );

form_security_validate( 'logo' );

$f_logo_file = gpc_get_file( 'logo_file' );
if ( !is_blank( $f_logo_file['tmp_name'] ) ) {
	$t_size = $f_logo_file['size'];
	if ( $t_size > ( 50 * 1024 ) ) {
		trigger_error( ERROR_FILE_TOO_BIG, ERROR );
	}

	$t_temp_file_path = $f_logo_file['tmp_name'];
	copy( $t_temp_file_path, dirname( __FILE__) . '/images/logo.png' );
}

$f_name = gpc_get_string( 'name', 'MantisHub' );
if ( $f_name != config_get( 'window_title' ) ) {
	config_set( 'window_title', $f_name );
}

html_meta_redirect( config_get( 'default_home_page' ) );
