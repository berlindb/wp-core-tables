<?php
/**
 * Query for the WordPress core `term_taxonomy` table.
 *
 * @package WPCoreTables
 */

declare( strict_types = 1 );

namespace WPCoreTables\Queries;

use WPCoreTables\Rows\TermTaxonomy as TermTaxonomyRow;
use WPCoreTables\Schemas\TermTaxonomy as TermTaxonomySchema;

defined( 'ABSPATH' ) || exit;

/**
 * Query core term taxonomies the BerlinDB way:
 * `new TermTaxonomy( array( 'taxonomy__in' => ... ) )`.
 *
 * An empty plugin prefix means the base name `term_taxonomy` resolves to the site
 * table `{$wpdb->prefix}term_taxonomy` (e.g. wp_term_taxonomy), the same relation
 * WordPress registers.
 *
 * @since 0.1.0
 */
class TermTaxonomy extends Base {

	/** Base (unprefixed) table name: resolves to {$wpdb->prefix}term_taxonomy. */
	protected $table_name = 'term_taxonomy';

	/** Short alias used in generated SQL. */
	protected $table_alias = 'tt';

	/** The schema describing this table's columns and indexes. */
	protected $table_schema = TermTaxonomySchema::class;

	/*
	 * Item names are NAMESPACED ('wpct_'), NOT the bare 'term_taxonomy'. They drive
	 * BerlinDB's hook and filter names - with the empty plugin prefix that keeps the
	 * table named 'wp_term_taxonomy', unnamespaced hooks would collide head-on with
	 * WordPress core's own hooks (see berlindb/core#242).
	 */
	protected $item_name = 'wpct_term_taxonomy';

	/** Plural item name (namespaced; drives the `the_wpct_term_taxonomies` filter). */
	protected $item_name_plural = 'wpct_term_taxonomies';

	/** The Row subclass raw rows are shaped into. */
	protected $item_shape = TermTaxonomyRow::class;

	/** Object cache group for this query (namespaced away from WordPress core). */
	protected $cache_group = 'wpcoretables_term_taxonomy';
}
