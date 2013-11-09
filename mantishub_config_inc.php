<?php

# If the instance has a logo.png file specific to the instant, then use it to override MantisHub logo
$t_instance_logo = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'logo.png';

if ( file_exists( $t_instance_logo ) ) {
	$g_logo_image = 'images/logo.png'; 
} else {
	$g_logo_image = 'images/mantishub_logo.png'; 
}
