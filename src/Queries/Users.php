<?php
/**
 * Query for the WordPress core `users` table.
 *
 * @package WPCoreTables
 */

declare( strict_types = 1 );

namespace WPCoreTables\Queries;

use WPCoreTables\Rows\User;
use WPCoreTables\Schemas\Users as UsersSchema;

defined( 'ABSPATH' ) || exit;

/**
 * Query core users the BerlinDB way: `new Users( array( 'user_email' => ... ) )`.
 *
 * An empty plugin prefix means the base name `users` resolves to the site table
 * `{$wpdb->prefix}users` (e.g. wp_users), the same relation WordPress registers.
 *
 * @since 0.1.0
 */
class Users extends Base {

	/** Base (unprefixed) table name: resolves to {$wpdb->prefix}users. */
	protected $table_name = 'users';

	/** Short alias used in generated SQL. */
	protected $table_alias = 'u';

	/** The schema describing this table's columns and indexes. */
	protected $table_schema = UsersSchema::class;

	/*
	 * Item names are NAMESPACED ('wpct_'), NOT the bare 'user'/'users'. They drive
	 * BerlinDB's hook and filter names - with the empty plugin prefix that keeps the
	 * table named 'wp_users', a plural of 'users' would collide head-on with
	 * WordPress core's own hooks (see berlindb/core#242).
	 */
	protected $item_name = 'wpct_user';

	/** Plural item name (namespaced; drives the `the_wpct_users` filter). */
	protected $item_name_plural = 'wpct_users';

	/** The Row subclass raw rows are shaped into. */
	protected $item_shape = User::class;

	/** Object cache group for this query (namespaced away from WordPress core). */
	protected $cache_group = 'wpcoretables_users';

	/**
	 * Map the meta type back to the REAL WordPress singular ('user').
	 *
	 * item_name is namespaced ('wpct_user') only to avoid the hook/filter collision
	 * (berlindb/core#242); left to the default, BerlinDB's native item-meta methods
	 * would derive the meta type from it and miss wp_usermeta. Overriding it here
	 * points get_metadata( 'user', ... ) at the real meta table. (A future core
	 * change could derive this from a declared meta relationship instead.)
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	public function get_meta_type() {
		return 'user';
	}
}
