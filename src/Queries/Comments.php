<?php
/**
 * Query for the WordPress core `comments` table.
 *
 * @package WPCoreTables
 */

declare( strict_types = 1 );

namespace WPCoreTables\Queries;

use WPCoreTables\Rows\Comment;
use WPCoreTables\Schemas\Comments as CommentsSchema;

defined( 'ABSPATH' ) || exit;

/**
 * Query core comments the BerlinDB way: `new Comments( array( 'comment_type__in' => ... ) )`.
 *
 * An empty plugin prefix means the base name `comments` resolves to the site table
 * `{$wpdb->prefix}comments` (e.g. wp_comments), the same relation WordPress registers.
 *
 * @since 0.1.0
 */
class Comments extends Base {

	/** Base (unprefixed) table name: resolves to {$wpdb->prefix}comments. */
	protected $table_name = 'comments';

	/** Short alias used in generated SQL. */
	protected $table_alias = 'c';

	/** The schema describing this table's columns and indexes. */
	protected $table_schema = CommentsSchema::class;

	/*
	 * Item names are NAMESPACED ('wpct_'), NOT the bare 'comment'/'comments'. They
	 * drive BerlinDB's hook and filter names - with the empty plugin prefix that keeps
	 * the table named 'wp_comments', a bare 'comments' would fire the_comments and
	 * collide head-on with WordPress core's own comment hooks (see berlindb/core#242).
	 * (Meta type is mapped back to the real 'comment' via get_meta_type() when meta is
	 * wired.)
	 */
	protected $item_name = 'wpct_comment';

	/** Plural item name (namespaced; drives the `the_wpct_comments` filter). */
	protected $item_name_plural = 'wpct_comments';

	/** The Row subclass raw rows are shaped into. */
	protected $item_shape = Comment::class;

	/** Object cache group for this query (namespaced away from WordPress core). */
	protected $cache_group = 'wpcoretables_comments';

	/**
	 * Map the meta type back to the REAL WordPress singular ('comment').
	 *
	 * item_name is namespaced ('wpct_comment') only to avoid the hook/filter collision
	 * (berlindb/core#242); left to the default, BerlinDB's native item-meta methods
	 * would derive the meta type from it and miss wp_commentmeta. Overriding it here
	 * points get_metadata( 'comment', ... ) at the real meta table. (A future core
	 * change could derive this from a declared meta relationship instead.)
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	public function get_meta_type() {
		return 'comment';
	}
}
