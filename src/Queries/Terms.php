<?php
/**
 * Query for the WordPress core `terms` table.
 *
 * @package WPCoreTables
 */

declare( strict_types = 1 );

namespace WPCoreTables\Queries;

use WPCoreTables\Rows\Term;
use WPCoreTables\Schemas\Terms as TermsSchema;

defined( 'ABSPATH' ) || exit;

/**
 * Query core terms the BerlinDB way: `new Terms( array( 'slug__in' => ... ) )`.
 *
 * An empty plugin prefix means the base name `terms` resolves to the site table
 * `{$wpdb->prefix}terms` (e.g. wp_terms), the same relation WordPress registers.
 *
 * @since 0.1.0
 */
class Terms extends Base {

	/** Base (unprefixed) table name: resolves to {$wpdb->prefix}terms. */
	protected $table_name = 'terms';

	/** Short alias used in generated SQL. */
	protected $table_alias = 't';

	/** The schema describing this table's columns and indexes. */
	protected $table_schema = TermsSchema::class;

	/*
	 * Item names are NAMESPACED ('wpct_'), NOT the bare 'term'/'terms'. They drive
	 * BerlinDB's hook and filter names - with the empty plugin prefix that keeps the
	 * table named 'wp_terms', a plural of 'terms' would collide head-on with
	 * WordPress core's own term hooks (see berlindb/core#242).
	 */
	protected $item_name = 'wpct_term';

	/** Plural item name (namespaced; drives the `the_wpct_terms` filter). */
	protected $item_name_plural = 'wpct_terms';

	/** The Row subclass raw rows are shaped into. */
	protected $item_shape = Term::class;

	/** Object cache group for this query (namespaced away from WordPress core). */
	protected $cache_group = 'wpcoretables_terms';

	/**
	 * Map the meta type back to the REAL WordPress singular ('term').
	 *
	 * item_name is namespaced ('wpct_term') only to avoid the hook/filter collision
	 * (berlindb/core#242); left to the default, BerlinDB's native item-meta methods
	 * would derive the meta type from it and miss wp_termmeta. Overriding it here
	 * points get_metadata( 'term', ... ) at the real meta table. (A future core
	 * change could derive this from a declared meta relationship instead.)
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	public function get_meta_type() {
		return 'term';
	}
}
