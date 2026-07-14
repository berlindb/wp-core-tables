<?php
/**
 * Base Query for a WordPress core table.
 *
 * @package WPCoreTables
 */

declare( strict_types = 1 );

namespace WPCoreTables\Queries;

use BerlinDB\Database\Kern\Query;

defined( 'ABSPATH' ) || exit;

/**
 * Shared configuration for every core-table Query.
 *
 * Core tables carry NO plugin prefix, so the base `table_name` resolves to the real
 * WordPress relation (`posts` -> `{$wpdb->prefix}posts`). Because the same prefix
 * would otherwise namespace BerlinDB's hooks and filters, each subclass gives its
 * `item_name` / `item_name_plural` a `wpct_` namespace instead, so BerlinDB fires
 * `the_wpct_posts` and never collides with WordPress core's own `the_posts` (see
 * berlindb/core#242). Meta type, when needed, is mapped back to the real singular
 * via get_meta_type().
 *
 * @since 0.1.0
 */
abstract class Base extends Query {

	/** No plugin prefix - core tables are named as WordPress names them. */
	protected $prefix = '';
}
