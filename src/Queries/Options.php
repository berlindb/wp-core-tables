<?php
/**
 * Query for the WordPress core `options` table.
 *
 * @package WPCoreTables
 */

declare( strict_types = 1 );

namespace WPCoreTables\Queries;

use WPCoreTables\Rows\Option;
use WPCoreTables\Schemas\Options as OptionsSchema;

defined( 'ABSPATH' ) || exit;

/**
 * Query core options the BerlinDB way: `new Options( array( 'autoload__in' => ... ) )`.
 *
 * An empty plugin prefix means the base name `options` resolves to the site table
 * `{$wpdb->prefix}options` (e.g. wp_options), the same relation WordPress registers.
 *
 * @since 0.1.0
 */
class Options extends Base {

	/** Base (unprefixed) table name: resolves to {$wpdb->prefix}options. */
	protected $table_name = 'options';

	/** Short alias used in generated SQL. */
	protected $table_alias = 'o';

	/** The schema describing this table's columns and indexes. */
	protected $table_schema = OptionsSchema::class;

	/*
	 * Item names are NAMESPACED ('wpct_'), NOT the bare 'option'. They drive
	 * BerlinDB's hook and filter names - with the empty plugin prefix that keeps the
	 * table named 'wp_options', a bare name would collide with WordPress core's own
	 * option hooks (see berlindb/core#242).
	 */
	protected $item_name = 'wpct_option';

	/** Plural item name (namespaced; drives the `the_wpct_options` filter). */
	protected $item_name_plural = 'wpct_options';

	/** The Row subclass raw rows are shaped into. */
	protected $item_shape = Option::class;

	/** Object cache group for this query (namespaced away from WordPress core). */
	protected $cache_group = 'wpcoretables_options';
}
