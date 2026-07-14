<?php
/**
 * PHPUnit bootstrap: boots the WordPress integration test suite and loads this
 * plugin, mirroring berlindb/core's own harness (Yoast wp-test-utils).
 *
 * @package WPCoreTables\Tests
 */

declare( strict_types = 1 );

namespace WPCoreTables\Tests;

use Yoast\WPTestUtils\WPIntegration;

$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
$_SERVER['SERVER_NAME']     = '';

define( 'WP_USE_THEMES', false );

$plugin_dir = dirname( __DIR__ );

require_once $plugin_dir . '/vendor/autoload.php';
require_once $plugin_dir . '/vendor/yoast/wp-test-utils/src/WPIntegration/bootstrap-functions.php';

$_tests_dir = WPIntegration\get_path_to_wp_test_dir();
if ( empty( $_tests_dir ) ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

if ( ! defined( 'WP_TESTS_DIR' ) ) {
	define( 'WP_TESTS_DIR', $_tests_dir );
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo 'Could not find ' . $_tests_dir . '/includes/functions.php' . PHP_EOL;
	echo 'Set WP_TESTS_DIR to the WordPress test suite path.' . PHP_EOL;
	exit( 1 );
}

require_once $_tests_dir . '/includes/functions.php';

// Load this plugin as a mu-plugin so its classes/hooks are active in tests.
tests_add_filter(
	'muplugins_loaded',
	static function () use ( $plugin_dir ): void {
		require $plugin_dir . '/wp-core-tables.php';
	}
);

WPIntegration\bootstrap_it();
