<?php
/**************************************************************************
 MantisHub Plugin
 Copyright (c) MantisHub - Victor Boctor
 All rights reserved.
 **************************************************************************/

require_api( 'plan_api.php' );
require_once( dirname( __FILE__ ) . '/core/mantishub_plugin_api.php' );

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

		$this->version = '1.0';
		$this->requires = array(
			'MantisCore' => '2.0.0',
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
			'EVENT_MENU_MANAGE' => 'add_billing_menu',
		);
	}

	/**
	 * Add billing menu option to my account menu
	 *
	 * @return null|string The billing menu link
	 */
	function add_billing_menu() {
		if( !current_user_is_administrator() ) {
			return null;
		}

		$t_billing_url = config_get( 'mantishub_info_billing_portal_url' );
		if ( is_blank( $t_billing_url ) ) {
			return null;
		}

		return '<a href="' . $t_billing_url . '" target="_blank">Billing</a>';
	}

	/**
	 * Operations to do when a page is rendered.
	 */
	function handle_page_render() {
		# This will internally check the timestamp for last update of info and
		# only run it every reasonable duration.
		plan_update_info();

		mantishub_google_analytics();
		mantishub_bingads_analytics();
		mantishub_drip();
	}
}

