<?php
$g_mantisbt_folder = __DIR__ . '/../';
$t_plugins_folder = $g_mantisbt_folder . 'plugins/';
$t_source_integration_folder = $t_plugins_folder . 'source-integration/';

function run( $p_command ) {
	return exec( $p_command );
}

function remove_folder( $p_folder ) {
	global $g_mantisbt_folder;
	run( "rm -rf {$g_mantisbt_folder}{$p_folder}" );
}

function remove_file( $p_file ) {
	global $g_mantisbt_folder;
	unlink( "{$g_mantisbt_folder}{$p_file}" );
}

echo "Moving source-integration plugins into right location...\n";
run( "cp -R {$t_source_integration_folder}Source {$t_plugins_folder}Source" );
run( "cp -R {$t_source_integration_folder}SourceGithub {$t_plugins_folder}SourceGithub" );
run( "cp -R {$t_source_integration_folder}SourceBitBucket {$t_plugins_folder}SourceBitBucket" );
run( "cp -R {$t_source_integration_folder}SourceGitlab {$t_plugins_folder}SourceGitlab" );

echo "Deleting files not needed in production...\n";
remove_file( '.gitignore' );
remove_file( '.gitmodules' );
remove_file( '.travis.yml' );
remove_file( '.mailmap' );
remove_file( 'scripts/travis_before_script.sh' );

echo "Deleting unnecessary MantisBT folders\n";
remove_folder( 'tests' );
remove_folder( 'packages' );
remove_folder( 'doc' );
remove_folder( 'docbook' );
remove_folder( 'phing' );
remove_folder( 'hosting' );
remove_folder( 'tools' );

echo "Deleting unnecessary vendor folders\n";
remove_folder( 'vendor/auth0/auth0-php/examples' );
remove_folder( 'vendor/auth0/auth0-php/tests' );
remove_folder( 'vendor/firebase/php-jwt' );
remove_folder( 'vendor/guzzlehttp/guzzle/docs' );
remove_folder( 'vendor/guzzlehttp/guzzle/tests' );
remove_folder( 'vendor/guzzlehttp/promises/tests' );
remove_folder( 'vendor/guzzlehttp/psr7/tests' );

echo "Deleting .git folders\n";
remove_folder( '.git' );
remove_folder( 'm/.git' );

remove_folder( 'library/adodb/.git' );
remove_folder( 'library/disposable/.git' );
remove_folder( 'library/ezc/Base/.git' );
remove_folder( 'library/ezc/Graph/.git' );
remove_folder( 'library/phpmailer/.git' );
remove_folder( 'library/securimage/.git' );

remove_folder( 'plugins/Auth0/.git' );
remove_folder( 'plugins/Csv_import/.git' );
remove_folder( 'plugins/EventLog/.git' );
remove_folder( 'plugins/HipChat/.git' );
remove_folder( 'plugins/Slack/.git' );
remove_folder( 'plugins/MantisTouchRedirect/.git' );
remove_folder( 'plugins/Snippets/.git' );
remove_folder( 'plugins/ImportUsers/.git' );

