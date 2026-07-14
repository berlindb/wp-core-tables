<?php
/**
 * Plugin Name:       WordPress Core Tables (BerlinDB)
 * Plugin URI:        https://github.com/berlindb/core
 * Description:       Registers the WordPress core database tables as BerlinDB relations - schemas, queries, rows, and relationships - so core data can be queried the BerlinDB way. It does NOT create or alter core tables; WordPress owns them.
 * Version:           0.1.0
 * Requires PHP:      8.1
 * Author:            JJJ
 * License:           MIT
 * Text Domain:       wp-core-tables
 *
 * @package WPCoreTables
 */

declare( strict_types = 1 );

namespace WPCoreTables;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Composer autoload (BerlinDB core + this plugin's classes).
if ( is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

/**
 * Register the core-table Queries once WordPress (and $wpdb) are ready.
 *
 * Registration is construction: instantiating each Query names its relation on the
 * connection and wires its schema, so later `new Posts( ... )` calls resolve. No
 * install runs - core tables already exist.
 */
add_action( 'plugins_loaded', array( Plugin::class, 'boot' ) );
