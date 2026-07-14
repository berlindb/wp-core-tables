<?php
/**
 * Proof of concept: satisfy a core WP_Query from BerlinDB instead of core SQL.
 *
 * @package WPCoreTables\Overrides
 */

declare( strict_types = 1 );

namespace WPCoreTables\Overrides;

use WP_Query;
use WPCoreTables\Queries\Posts;

defined( 'ABSPATH' ) || exit;

/**
 * Hooks `posts_pre_query` so a WordPress `WP_Query` returns rows fetched by BerlinDB's
 * `Posts` query rather than by core's own generated SQL.
 *
 * This is a scoped demonstration, NOT a drop-in replacement for WP_Query:
 *
 *  - It is OPT-IN twice over. Nothing happens unless you (a) call `register()` AND
 *    (b) run a query carrying the `wpct_source` query var, e.g.
 *    `new WP_Query( array( 'post_type' => 'post', 'wpct_source' => true ) )`. Every
 *    other query on the site takes the normal core path untouched. `Plugin::boot()`
 *    deliberately does NOT call `register()` - a POC should never silently reroute a
 *    live site's reads.
 *  - It translates a small, common slice of the WP_Query var surface (post_type,
 *    post_status, ID includes, number/offset, orderby/order, and fields=ids|objects).
 *    The full WP_Query grammar (tax/meta/date queries, sticky posts, search, etc.) is
 *    out of scope and would fall through incompletely - that is the "totally override
 *    core" rabbit hole, deliberately not entered here.
 *
 * What it DOES prove: core's `posts_pre_query` / `found_posts` short-circuits are
 * enough for BerlinDB to back a real WP_Query end to end - list rows and pagination
 * total both come from the BerlinDB query, and WordPress hydrates them into WP_Post
 * objects exactly as if it had run the SQL itself.
 *
 * @since 0.1.0
 */
final class PostsQueryOverride {

	/**
	 * The query var a WP_Query must set to opt into BerlinDB-backed results.
	 *
	 * @since 0.1.0
	 * @var   string
	 */
	public const FLAG = 'wpct_source';

	/**
	 * BerlinDB total-found counts, keyed by the intercepted WP_Query's object id.
	 *
	 * The `posts_pre_query` and `found_posts` filters fire separately, so pre_query()
	 * stashes the true total here for found_posts() to read back. A static map avoids
	 * writing a dynamic property onto WP_Query (deprecated on PHP 8.2+).
	 *
	 * @since 0.1.0
	 * @var   array<int, int>
	 */
	private static $found = array();

	/**
	 * Register the interception hooks. Idempotent; safe to call more than once.
	 *
	 * @since 0.1.0
	 */
	public static function register(): void {
		add_filter( 'posts_pre_query', array( self::class, 'pre_query' ), 10, 2 );
		add_filter( 'found_posts', array( self::class, 'found_posts' ), 10, 2 );
	}

	/**
	 * Remove the interception hooks (mirror of register(), for tests / teardown).
	 *
	 * @since 0.1.0
	 */
	public static function unregister(): void {
		remove_filter( 'posts_pre_query', array( self::class, 'pre_query' ), 10 );
		remove_filter( 'found_posts', array( self::class, 'found_posts' ), 10 );
	}

	/**
	 * Short-circuit `WP_Query::get_posts()` for flagged queries.
	 *
	 * Returning a non-null array tells core to skip its own SQL and use these rows.
	 * The BerlinDB total is stashed on the WP_Query so found_posts() can report it.
	 *
	 * @since 0.1.0
	 *
	 * @param array|null $posts    Core's pre-computed posts (null until filtered).
	 * @param WP_Query   $wp_query The query being run (by reference upstream).
	 * @return array|null Rows for core to use, or null to defer to core.
	 */
	public static function pre_query( $posts, $wp_query ) {

		// Only touch queries that explicitly opted in; leave every other query alone.
		if ( empty( $wp_query->get( self::FLAG ) ) ) {
			return $posts;
		}

		$args     = self::translate_vars( $wp_query );
		$query    = new Posts( $args );
		$total    = (int) $query->get_found_items();
		$per_page = (int) ( $args['number'] ?? 0 );
		$fields   = (string) $wp_query->get( 'fields' );

		/*
		 * Reporting the total (for pagination) depends on the fields path, because
		 * core calls set_found_posts() in different places:
		 *
		 *  - 'ids' / 'id=>parent': set_found_posts() DOES run after the short-circuit,
		 *    but recomputes found_posts from SELECT FOUND_ROWS() (which is 0 here, since
		 *    our SQL never ran). We stash the real total for the found_posts filter to
		 *    restore, and let core derive max_num_pages from it.
		 *  - default (object rows): set_found_posts() is skipped entirely on a
		 *    short-circuit, so nothing would set the totals. We set them ourselves.
		 */
		if ( 'ids' === $fields || 'id=>parent' === $fields ) {
			self::$found[ spl_object_id( $wp_query ) ] = $total;
		} else {
			$wp_query->found_posts   = $total;
			$wp_query->max_num_pages = ( $per_page > 0 )
				? (int) ceil( $total / $per_page )
				: ( $total > 0 ? 1 : 0 );
		}

		// fields=ids => core expects an array of int IDs; otherwise post rows that
		// core will map through get_post() into WP_Post objects.
		if ( 'ids' === $fields ) {
			return array_map( 'intval', $query->items );
		}

		return $query->items;
	}

	/**
	 * Report the BerlinDB total for a flagged query so pagination is correct.
	 *
	 * @since 0.1.0
	 *
	 * @param int      $found_posts Core's found count.
	 * @param WP_Query $wp_query    The query being run.
	 * @return int The BerlinDB total when this query opted in, else core's value.
	 */
	public static function found_posts( $found_posts, $wp_query ) {

		$key = spl_object_id( $wp_query );

		if ( isset( self::$found[ $key ] ) ) {
			$total = self::$found[ $key ];
			unset( self::$found[ $key ] );
			return $total;
		}

		return $found_posts;
	}

	/**
	 * Translate the supported slice of WP_Query vars into BerlinDB `Posts` query vars.
	 *
	 * Intentionally partial - see the class docblock for what is and is not covered.
	 *
	 * @since 0.1.0
	 *
	 * @param WP_Query $wp_query The source query.
	 * @return array BerlinDB query vars.
	 */
	private static function translate_vars( $wp_query ): array {

		$args = array();

		// post_type: string | array | 'any' (omit for 'any' => no filter).
		$post_type = $wp_query->get( 'post_type' );
		if ( ! empty( $post_type ) && 'any' !== $post_type ) {
			$args['post_type__in'] = (array) $post_type;
		}

		// post_status: string | array | 'any'.
		$post_status = $wp_query->get( 'post_status' );
		if ( ! empty( $post_status ) && 'any' !== $post_status ) {
			$args['post_status__in'] = (array) $post_status;
		}

		// ID includes: `p` (single) or `post__in` (many).
		$single = (int) $wp_query->get( 'p' );
		if ( $single > 0 ) {
			$args['include'] = array( $single );
		}
		$post__in = $wp_query->get( 'post__in' );
		if ( ! empty( $post__in ) ) {
			$args['include'] = array_map( 'intval', (array) $post__in );
		}

		// Author.
		$author = (int) $wp_query->get( 'author' );
		if ( $author > 0 ) {
			$args['post_author'] = $author;
		}

		// Number + offset. posts_per_page = -1 means "all"; map to no LIMIT (0).
		$per_page = $wp_query->get( 'posts_per_page' );
		if ( '' === $per_page || null === $per_page ) {
			$per_page = $wp_query->get( 'number' );
		}
		if ( '' === $per_page || null === $per_page ) {
			$args['number'] = 10;
		} elseif ( (int) $per_page < 0 ) {
			$args['number'] = 0;
		} else {
			$args['number'] = (int) $per_page;
		}

		$offset = (int) $wp_query->get( 'offset' );
		if ( $offset > 0 ) {
			$args['offset'] = $offset;
		}

		// BerlinDB skips the total COUNT by default (no_found_rows => true); WP_Query
		// wants the total for pagination unless it opted out, so mirror its choice.
		$args['no_found_rows'] = (bool) $wp_query->get( 'no_found_rows' );

		// Fields: pass through so `ids` yields an ID list from BerlinDB directly.
		if ( 'ids' === $wp_query->get( 'fields' ) ) {
			$args['fields'] = 'ids';
		}

		// Orderby / order: map the common WP_Query aliases to real columns.
		$orderby_map = array(
			'date'     => 'post_date',
			'modified' => 'post_modified',
			'title'    => 'post_title',
			'name'     => 'post_name',
			'id'       => 'ID',
			'author'   => 'post_author',
			'parent'   => 'post_parent',
			'menu_order' => 'menu_order',
		);
		$orderby = strtolower( (string) $wp_query->get( 'orderby' ) );
		$args['orderby'] = $orderby_map[ $orderby ] ?? 'post_date';

		$order = strtoupper( (string) $wp_query->get( 'order' ) );
		$args['order'] = ( 'ASC' === $order ) ? 'ASC' : 'DESC';

		return $args;
	}
}
