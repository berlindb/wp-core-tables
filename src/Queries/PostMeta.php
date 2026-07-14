<?php
/**
 * Query for the WordPress core `postmeta` table.
 *
 * @package WPCoreTables
 */

declare( strict_types = 1 );

namespace WPCoreTables\Queries;

use WPCoreTables\Rows\Meta;
use WPCoreTables\Schemas\PostMeta as PostMetaSchema;

defined( 'ABSPATH' ) || exit;

/**
 * Query core post meta the BerlinDB way: `new PostMeta( array( 'post_id' => ... ) )`.
 *
 * An empty plugin prefix means the base name `postmeta` resolves to the site table
 * `{$wpdb->prefix}postmeta` (e.g. wp_postmeta), the same relation WordPress registers.
 *
 * @since 0.1.0
 */
class PostMeta extends Base {

	/** Base (unprefixed) table name: resolves to {$wpdb->prefix}postmeta. */
	protected $table_name = 'postmeta';

	/** Short alias used in generated SQL. */
	protected $table_alias = 'pm';

	/** The schema describing this table's columns and indexes. */
	protected $table_schema = PostMetaSchema::class;

	/*
	 * Item names are NAMESPACED ('wpct_'), NOT the bare 'postmeta'. They drive
	 * BerlinDB's hook and filter names - with the empty plugin prefix that keeps the
	 * table named 'wp_postmeta', a bare name would collide with WordPress core's own
	 * meta hooks (see berlindb/core#242).
	 */
	protected $item_name = 'wpct_postmeta';

	/** Plural item name (namespaced; drives the `the_wpct_postmetas` filter). */
	protected $item_name_plural = 'wpct_postmetas';

	/** The Row subclass raw rows are shaped into (shared EAV meta shape). */
	protected $item_shape = Meta::class;

	/** Object cache group for this query (namespaced away from WordPress core). */
	protected $cache_group = 'wpcoretables_postmeta';
}
