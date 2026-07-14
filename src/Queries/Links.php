<?php
/**
 * Query for the WordPress core `links` table.
 *
 * @package WPCoreTables
 */

declare( strict_types = 1 );

namespace WPCoreTables\Queries;

use WPCoreTables\Rows\Link;
use WPCoreTables\Schemas\Links as LinksSchema;

defined( 'ABSPATH' ) || exit;

/**
 * Query core links the BerlinDB way: `new Links( array( 'link_visible__in' => ... ) )`.
 *
 * An empty plugin prefix means the base name `links` resolves to the site table
 * `{$wpdb->prefix}links` (e.g. wp_links), the same relation WordPress registers.
 *
 * @since 0.1.0
 */
class Links extends Base {

	/** Base (unprefixed) table name: resolves to {$wpdb->prefix}links. */
	protected $table_name = 'links';

	/** Short alias used in generated SQL. */
	protected $table_alias = 'l';

	/** The schema describing this table's columns and indexes. */
	protected $table_schema = LinksSchema::class;

	/*
	 * Item names are NAMESPACED ('wpct_'), NOT the bare 'link'. They drive
	 * BerlinDB's hook and filter names - with the empty plugin prefix that keeps the
	 * table named 'wp_links', a bare name would collide with WordPress core's own
	 * link hooks (see berlindb/core#242).
	 */
	protected $item_name = 'wpct_link';

	/** Plural item name (namespaced; drives the `the_wpct_links` filter). */
	protected $item_name_plural = 'wpct_links';

	/** The Row subclass raw rows are shaped into. */
	protected $item_shape = Link::class;

	/** Object cache group for this query (namespaced away from WordPress core). */
	protected $cache_group = 'wpcoretables_links';
}
