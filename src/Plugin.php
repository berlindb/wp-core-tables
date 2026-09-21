<?php
/**
 * Plugin bootstrap.
 *
 * @package WPCoreTables
 */

declare( strict_types = 1 );

namespace WPCoreTables;

use WPCoreTables\Queries\CommentMeta;
use WPCoreTables\Queries\Comments;
use WPCoreTables\Queries\Links;
use WPCoreTables\Queries\Options;
use WPCoreTables\Queries\PostMeta;
use WPCoreTables\Queries\Posts;
use WPCoreTables\Queries\TermMeta;
use WPCoreTables\Queries\TermRelationships;
use WPCoreTables\Queries\Terms;
use WPCoreTables\Queries\TermTaxonomy;
use WPCoreTables\Queries\UserMeta;
use WPCoreTables\Queries\Users;

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

	/** @var array<string, string[]> */
	private static $native_cache_groups = array();

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

		$hooks = array(
			'clean_post_cache'           => array( Posts::class ),
			'clean_comment_cache'        => array( Comments::class ),
			'clean_user_cache'           => array( Users::class ),
			'clean_term_cache'           => array( Terms::class, TermTaxonomy::class ),
			'edited_term_taxonomy'       => array( TermTaxonomy::class ),
			'deleted_term_taxonomy'      => array( TermTaxonomy::class ),
			'set_object_terms'           => array( TermRelationships::class, TermTaxonomy::class ),
			'deleted_term_relationships' => array( TermRelationships::class, TermTaxonomy::class ),
			'added_option'               => array( Options::class ),
			'updated_option'             => array( Options::class ),
			'deleted_option'             => array( Options::class ),
			'add_link'                   => array( Links::class ),
			'edit_link'                  => array( Links::class ),
			'deleted_link'               => array( Links::class ),
		);

		foreach ( array( 'added', 'updated', 'deleted' ) as $action ) {
			$hooks[ "{$action}_post_meta" ]    = array( PostMeta::class );
			$hooks[ "{$action}_comment_meta" ] = array( CommentMeta::class );
			$hooks[ "{$action}_term_meta" ]    = array( TermMeta::class );
			$hooks[ "{$action}_user_meta" ]    = array( UserMeta::class );
		}

		foreach ( $hooks as $hook => $queries ) {
			add_action(
				$hook,
				static function () use ( $queries ): void {
					self::flush_native_cache_groups( $queries );
				},
				10,
				0
			);
		}
	}

	/**
	 * Flush facade caches after WordPress changes a table it owns.
	 *
	 * A group flush removes result lists, by-ID rows, and secondary lookups.
	 * If a cache drop-in lacks group flushing, a full flush is required to avoid
	 * returning stale rows; rotating last_changed alone cannot clear by-ID rows.
	 *
	 * @since 0.1.0
	 *
	 * @param string[] $queries Query classes for the changed table.
	 */
	private static function flush_native_cache_groups( array $queries ): void {
		if ( ! wp_cache_supports( 'flush_group' ) ) {
			wp_cache_flush();
			return;
		}

		foreach ( $queries as $class ) {
			if ( ! isset( self::$native_cache_groups[ $class ] ) ) {
				$query = new $class( array( 'number' => 0 ) );
				self::$native_cache_groups[ $class ] = $query->get_native_cache_groups();
			}

			foreach ( self::$native_cache_groups[ $class ] as $group ) {
				wp_cache_flush_group( $group );
			}
		}
	}
}
