<?php
/*
 * Plugin Name: Financer Filter Sliders
 * Version: 2.9.0
 */
// Check that WordPress is loaded, if not, terminate the current script
if ( ! defined( 'WP_PLUGIN_DIR' ) ) {
	die();
}
require __DIR__ . '/vendor/autoload.php';
\Financer\FilterSlider\Plugin::Hook();

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	\Financer\FilterSlider\UpgradeCommand::register();
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
    \Financer\FilterSlider\UpgradeCommandRemote::register();
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
    \Financer\FilterSlider\DeletePodCommand::register();
}

function _isset( $val ) {
	return isset( $val );
}
