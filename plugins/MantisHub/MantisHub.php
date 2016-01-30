<?php
/**************************************************************************
 MantisHub Plugin
 Copyright (c) MantisHub - Victor Boctor
 All rights reserved.
 **************************************************************************/

require_api( 'plan_api.php' );

/**
 * MantisHub plugin is enabled by default for all instances and can't be
 * uninstalled.  It provides MantisHub features.
 */
class MantisHubPlugin extends MantisPlugin {
	/**
	 * A method that populates the plugin information and minimum requirements.
	 */
	function register() {
		$this->name		= plugin_lang_get( 'title' );
		$this->description	= plugin_lang_get( 'description' );

		$this->version = '0.1';
		$this->requires = array(
			'MantisCore' => '1.3.0',
		);

		$this->author  = 'Victor Boctor';
		$this->contact = 'victor@mantishub.net';
		$this->url     = 'http://www.mantishub.com';
	}

	/**
	 * Event hook declaration.
	 *
	 * @return array An associated array that maps event names to handler names.
	 */
	function hooks() {
		return array(
			'EVENT_LAYOUT_BODY_END' => 'handle_page_render',
		);
	}

	/**
	 * Operations to do when a page is rendered.
	 */
	function handle_page_render() {
		# This will internally check the timestamp for last update of info and
		# only run it every reasonable duration.
		plan_update_info();
	}
}

