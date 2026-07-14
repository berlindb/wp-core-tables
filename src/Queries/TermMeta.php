<?php
/**
 * Query for the WordPress core `termmeta` table.
 *
 * @package WPCoreTables
 */

declare( strict_types = 1 );

namespace WPCoreTables\Queries;

use WPCoreTables\Rows\Meta;
use WPCoreTables\Schemas\TermMeta as TermMetaSchema;

defined( 'ABSPATH' ) || exit;

/**
 * Query core term meta the BerlinDB way:
 * `new TermMeta( array( 'term_id__in' => ... ) )`.
 *
 * An empty plugin prefix means the base name `termmeta` resolves to the site table
 * `{$wpdb->prefix}termmeta` (e.g. wp_termmeta), the same relation WordPress registers.
 *
 * @since 0.1.0
 */
class TermMeta extends Base {

	/** Base (unprefixed) table name: resolves to {$wpdb->prefix}termmeta. */
	protected $table_name = 'termmeta';

	/** Short alias used in generated SQL. */
	protected $table_alias = 'tm';

	/** The schema describing this table's columns and indexes. */
	protected $table_schema = TermMetaSchema::class;

	/*
	 * Item names are NAMESPACED ('wpct_'), NOT the bare 'termmeta'. They drive
	 * BerlinDB's hook and filter names - with the empty plugin prefix that keeps the
	 * table named 'wp_termmeta', unnamespaced hooks would collide head-on with
	 * WordPress core's own meta hooks (see berlindb/core#242).
	 */
	protected $item_name = 'wpct_termmeta';

	/** Plural item name (namespaced; drives the `the_wpct_termmetas` filter). */
	protected $item_name_plural = 'wpct_termmetas';

	/** The Row subclass raw rows are shaped into (shared EAV meta shape). */
	protected $item_shape = Meta::class;

	/** Object cache group for this query (namespaced away from WordPress core). */
	protected $cache_group = 'wpcoretables_termmeta';
}
