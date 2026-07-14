<?php
/**
 * Query for the WordPress core `usermeta` table.
 *
 * @package WPCoreTables
 */

declare( strict_types = 1 );

namespace WPCoreTables\Queries;

use WPCoreTables\Rows\Meta;
use WPCoreTables\Schemas\UserMeta as UserMetaSchema;

defined( 'ABSPATH' ) || exit;

/**
 * Query core usermeta the BerlinDB way: `new UserMeta( array( 'user_id' => ... ) )`.
 *
 * An empty plugin prefix means the base name `usermeta` resolves to the site table
 * `{$wpdb->prefix}usermeta` (e.g. wp_usermeta), the same relation WordPress registers.
 *
 * @since 0.1.0
 */
class UserMeta extends Base {

	/** Base (unprefixed) table name: resolves to {$wpdb->prefix}usermeta. */
	protected $table_name = 'usermeta';

	/** Short alias used in generated SQL. */
	protected $table_alias = 'um';

	/** The schema describing this table's columns and indexes. */
	protected $table_schema = UserMetaSchema::class;

	/*
	 * Item names are NAMESPACED ('wpct_'), NOT the bare 'usermeta'. They drive
	 * BerlinDB's hook and filter names - with the empty plugin prefix that keeps the
	 * table named 'wp_usermeta', an un-namespaced name would collide head-on with
	 * WordPress core's own hooks (see berlindb/core#242).
	 */
	protected $item_name = 'wpct_usermeta';

	/** Plural item name (namespaced; drives the `the_wpct_usermetas` filter). */
	protected $item_name_plural = 'wpct_usermetas';

	/** The Row subclass raw rows are shaped into (shared EAV meta shape). */
	protected $item_shape = Meta::class;

	/** Object cache group for this query (namespaced away from WordPress core). */
	protected $cache_group = 'wpcoretables_usermeta';
}
