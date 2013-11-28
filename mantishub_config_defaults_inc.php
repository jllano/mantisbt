<?php
	if ( is_gold() ) {
		$g_window_title			= 'MantisHub (Gold)';
	} else if ( is_silver() ) {
		$g_window_title			= 'MantisHub (Silver)';
	} else if ( is_bronze() ) {
		$g_window_title			= 'MantisHub (Bronze)';
	}

	$g_copyright_statement = 'Hosted by MantisHub';

