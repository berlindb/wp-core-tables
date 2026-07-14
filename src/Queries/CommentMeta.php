<?php
/**
 * Query for the WordPress core `commentmeta` table.
 *
 * @package WPCoreTables
 */

declare( strict_types = 1 );

namespace WPCoreTables\Queries;

use WPCoreTables\Rows\Meta;
use WPCoreTables\Schemas\CommentMeta as CommentMetaSchema;

defined( 'ABSPATH' ) || exit;

/**
 * Query core comment meta the BerlinDB way: `new CommentMeta( array( 'comment_id' => ... ) )`.
 *
 * An empty plugin prefix means the base name `commentmeta` resolves to the site table
 * `{$wpdb->prefix}commentmeta` (e.g. wp_commentmeta), the same relation WordPress registers.
 *
 * @since 0.1.0
 */
class CommentMeta extends Base {

	/** Base (unprefixed) table name: resolves to {$wpdb->prefix}commentmeta. */
	protected $table_name = 'commentmeta';

	/** Short alias used in generated SQL. */
	protected $table_alias = 'cm';

	/** The schema describing this table's columns and indexes. */
	protected $table_schema = CommentMetaSchema::class;

	/*
	 * Item names are NAMESPACED ('wpct_'), NOT the bare 'commentmeta'. They drive
	 * BerlinDB's hook and filter names - with the empty plugin prefix that keeps the
	 * table named 'wp_commentmeta', a bare name would collide with WordPress core's own
	 * meta hooks (see berlindb/core#242).
	 */
	protected $item_name = 'wpct_commentmeta';

	/** Plural item name (namespaced; drives the `the_wpct_commentmetas` filter). */
	protected $item_name_plural = 'wpct_commentmetas';

	/** The Row subclass raw rows are shaped into (shared EAV meta shape). */
	protected $item_shape = Meta::class;

	/** Object cache group for this query (namespaced away from WordPress core). */
	protected $cache_group = 'wpcoretables_commentmeta';
}
