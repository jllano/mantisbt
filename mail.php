<?php
require_once( 'core.php' );
require_api( 'plugin_api.php' );

# redirect to helpdesk plugin
plugin_push_current( 'Helpdesk' );
include( dirname( __FILE__ ) . '/plugins/Helpdesk/pages/mail.php' );

