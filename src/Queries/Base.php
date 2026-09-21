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

	/**
	 * Return every object-cache group this facade uses for rows and lookups.
	 *
	 * @since 0.1.0
	 *
	 * @return string[]
	 */
	public function get_native_cache_groups(): array {
		$groups  = array( $this->cache_group );
		$primary = $this->get_primary_column_name();

		foreach ( $this->get_column_names( array( 'cache_key' => true ) ) as $column ) {
			if ( $column !== $primary ) {
				$groups[] = "{$this->cache_group}-by-{$column}";
			}
		}

		return $groups;
	}
}
