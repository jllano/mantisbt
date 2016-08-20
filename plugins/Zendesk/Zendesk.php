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
			'subdomain' => '',   # instance subdomain, example for example.zendesk.com
			'user' => '',        # leave user empty to access the articles anonymously.  Hence, no agent only articles.
			'token' => ''
		);
	}

	/**
	 * Include javascript files for chart.js
	 * @return void
	 */
	function resources() {
		# If subdomain is not specified, then disable the plugin.
		$t_subdomain = plugin_config_get( 'subdomain' );
		if( is_blank( $t_subdomain ) ) {
			return;
		}

		# Only include scripts on the pages that needed them.
		switch( basename( $_SERVER['SCRIPT_NAME'] ) ) {
			case 'bug_change_status_page.php':
			case 'view.php':
			case 'bug_update_page.php':
				return '<script src="' . plugin_file( 'zendesk.js' ) . '"></script>
			<script src="' . plugin_file( 'jquery-textrange.js' ) . '"></script>';
			default:
				return '';
		}
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
