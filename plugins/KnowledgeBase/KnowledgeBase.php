<?php
/**
 * MantisBT - A PHP based bugtracking system
 *
 * MantisBT is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * MantisBT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @copyright Copyright MantisBT Team - mantisbt-dev@lists.sourceforge.net
 */

require_once( dirname( __FILE__ ) . '/../../core.php' );
require_api( 'custom_function_api.php' );
require_api( 'filter_api.php' );
require_api( 'project_api.php' );

/**
 * Mantis KnowledgeBase Plugin
 *
 * This plugin is designed to organize your most common questions or problems and an explanation of how to solve them
 *
 */
class KnowledgeBasePlugin extends MantisPlugin {
	
	public $project_name = 'Knowledge Base';

	/**
	 * A method that populates the plugin information and minimum requirements.
	 * @return void
	 */
	function register() {
		$this->name		= plugin_lang_get( 'title' );
		$this->description	= plugin_lang_get( 'description' );
		$this->page = '';

		$this->version = MANTIS_VERSION;
		$this->requires = array(
			'MantisCore' => '2.0.0',
		);

		$this->author = 'Joel C. Llano';
		$this->contact = 'joel@mantisbt.org';
		$this->url = 'http://www.mantisbt.org';
	}


	/**
	 * Install KnowledgeBase plugin
	 * 
	 * Create a project upon install
	 * @return boolean
	 */
	function install() {

		$t_project_exist = project_get_id_by_name( $this->project_name );

		# check if project exist already
		if( !$t_project_exist ) {
			$this->createProject();
		}

		return true;
	}

	/**
	 * Create Project with named Knowled Base
	 * @return void
	 */
	function createProject() {
		
		$t_name 		= $this->project_name;
		$t_description 	= 'Knowledge Base Description';
		$t_view_state	= VS_PUBLIC;
		$t_status		= 10; //development
		
		$t_project_id = project_create
			( 
				$t_name, 
				$t_description, 
				$t_status, 
				$t_view_state
			);

	}
	
	/**
	 * Fetch all Closed Issues
	 * @return Object BugData
	 */
	static function getClosedIssues() {

		$f_page_number = 1;
		$t_per_page = null;
		$t_bug_count = null;
		$t_page_count = null;

		$t_filter = array(
			'status' => CLOSED,
			'per_page' => 100
		);

		$t_rows = filter_get_bug_rows( 
			$f_page_number, 
			$t_per_page, 
			$t_page_count, 
			$t_bug_count, 
			$t_filter, 
			null, 
			null, 
			false 
		);

		return $t_rows;
	}

}