<?php
# Commercial License (MantisHub)

require_once( config_get( 'absolute_path' ) . 'core.php' );
require_once( config_get( 'class_path' ) . 'MantisPlugin.class.php' );
require_once( dirname( __FILE__ ) . '/core/zendesk_api.php' );

/**
 * A plugin for easy access to Zendesk KB
 */
class ZendeskPlugin extends MantisPlugin {
	/**
	 *  A method that populates the plugin information and minimum requirements.
	 */
	function register() {
		$this->name		= plugin_lang_get( 'title' );
		$this->description	= plugin_lang_get( 'description' );

		$this->version		= '1.0';
		$this->requires		= array(
			'MantisCore' => '2.0.0',
		);

		$this->author		= 'Victor Boctor';
		$this->contact		= 'victor@mantishub.net';
		$this->url			= 'https://www.mantishub.com';
	}

	/**
	 * Gets the plugin default configuration.
	 */
	function config() {
		return array(
			'user' => '',
			'token' => ''
		);
	}

	/**
	 * Include javascript files for chart.js
	 * @return void
	 */
	function resources() {
		return '<script src="' . plugin_file( 'zendesk.js' ) . '"></script>
		<script src="' . plugin_file( 'jquery-textrange.js' ) . '"></script>';
	}

	/**
	 * Event hook declaration.
	 * 
	 * @returns An associated array that maps event names to handler names.
	 */
	function hooks() {
		return array(
			'EVENT_LAYOUT_RESOURCES' => 'resources',
		);
	}
}
