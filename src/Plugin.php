<?php
/**
 * Plugin bootstrap.
 *
 * @package WPCoreTables
 */

declare( strict_types = 1 );

namespace WPCoreTables;

defined( 'ABSPATH' ) || exit;

/**
 * Wires the WordPress core tables into BerlinDB.
 *
 * The Schemas / Queries / Rows do the real work and are just autoloaded classes -
 * a consumer runs `new \WPCoreTables\Queries\Posts( array( ... ) )` directly. This
 * bootstrap is the seam for cross-cutting setup (e.g. registering relationships or
 * meta stores) and never runs a query itself.
 *
 * @since 0.1.0
 */
final class Plugin {

	/**
	 * Cache groups for tables that are GLOBAL in multisite (base-prefixed, shared
	 * across all sites): users + usermeta. Their physical name resolves correctly on
	 * its own (BerlinDB reads $wpdb->users, which WordPress registers globally), but
	 * their BerlinDB object cache must be a network-shared group or every site would
	 * cache the same rows separately. Mirrors WordPress core registering 'users' /
	 * 'usermeta' as global cache groups. Must match the Users / UserMeta query
	 * $cache_group values.
	 *
	 * @since 0.1.0
	 * @var   string[]
	 */
	private const GLOBAL_CACHE_GROUPS = array(
		'wpcoretables_users',
		'wpcoretables_usermeta',
	);

	/**
	 * Boot the plugin on `plugins_loaded`.
	 *
	 * @since 0.1.0
	 */
	public static function boot(): void {

		// Query-only: WordPress owns the tables, their $wpdb registration, and their
		// physical (per-site vs global) names, so there is nothing to register there.
		// The one multisite concern is cache scope for the GLOBAL tables.
		if ( function_exists( 'wp_cache_add_global_groups' ) ) {
			wp_cache_add_global_groups( self::GLOBAL_CACHE_GROUPS );
		}
	}
}
