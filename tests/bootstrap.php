<?php

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
    $_tests_dir = '/tmp/wordpress-tests-lib';
}

require_once $_tests_dir . '/includes/functions.php';

function _manually_load_plugin() {
    require_once "/tmp/wordpress/wp-admin/includes/class-wp-upgrader.php";
    require_once "/tmp/wordpress/wp-admin/includes/file.php";
    require_once "/tmp/wordpress/wp-admin/includes/misc.php";
    require_once "/tmp/wordpress/wp-admin/includes/plugin-install.php";
    require_once "/tmp/wordpress/wp-admin/includes/plugin.php";
    require_once "/tmp/wordpress/wp-includes/pluggable.php";
    require_once "/tmp/wordpress/wp-includes/default-constants.php";
    require_once "/tmp/wordpress/wp-includes/default-constants.php";
    wp_cookie_constants();
    ( new Plugin_Upgrader() )->install( plugins_api( 'plugin_information', [ 'slug' => 'wp-autoloader' ] )->download_link );
    require WP_PLUGIN_DIR . '/wp-autoloader/index.php';
    require dirname( dirname( __FILE__ ) ) . '/filter_slider.php';
}

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

require $_tests_dir . '/includes/bootstrap.php';
