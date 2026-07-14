<?php
/**
 * Query for the WordPress core `term_relationships` table.
 *
 * @package WPCoreTables
 */

declare( strict_types = 1 );

namespace WPCoreTables\Queries;

use WPCoreTables\Rows\TermRelationship;
use WPCoreTables\Schemas\TermRelationships as TermRelationshipsSchema;

defined( 'ABSPATH' ) || exit;

/**
 * Query core term relationships the BerlinDB way:
 * `new TermRelationships( array( 'object_id__in' => ... ) )`.
 *
 * An empty plugin prefix means the base name `term_relationships` resolves to the
 * site table `{$wpdb->prefix}term_relationships` (e.g. wp_term_relationships), the
 * same relation WordPress registers.
 *
 * @since 0.1.0
 */
class TermRelationships extends Base {

	/** Base (unprefixed) table name: resolves to {$wpdb->prefix}term_relationships. */
	protected $table_name = 'term_relationships';

	/** Short alias used in generated SQL. */
	protected $table_alias = 'tr';

	/** The schema describing this table's columns and indexes. */
	protected $table_schema = TermRelationshipsSchema::class;

	/*
	 * Item names are NAMESPACED ('wpct_'), NOT the bare 'term_relationship'. They drive
	 * BerlinDB's hook and filter names - with the empty plugin prefix that keeps the
	 * table named 'wp_term_relationships', a bare name would collide with WordPress
	 * core's own hooks (see berlindb/core#242).
	 */
	protected $item_name = 'wpct_term_relationship';

	/** Plural item name (namespaced; drives the `the_wpct_term_relationships` filter). */
	protected $item_name_plural = 'wpct_term_relationships';

	/** The Row subclass raw rows are shaped into. */
	protected $item_shape = TermRelationship::class;

	/** Object cache group for this query (namespaced away from WordPress core). */
	protected $cache_group = 'wpcoretables_term_relationships';
}
