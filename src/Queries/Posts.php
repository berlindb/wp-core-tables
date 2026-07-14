<?php
/**
 * Query for the WordPress core `posts` table.
 *
 * @package WPCoreTables
 */

declare( strict_types = 1 );

namespace WPCoreTables\Queries;

use WPCoreTables\Rows\Post;
use WPCoreTables\Schemas\Posts as PostsSchema;

defined( 'ABSPATH' ) || exit;

/**
 * Query core posts the BerlinDB way: `new Posts( array( 'post_type__in' => ... ) )`.
 *
 * An empty plugin prefix means the base name `posts` resolves to the site table
 * `{$wpdb->prefix}posts` (e.g. wp_posts), the same relation WordPress registers.
 *
 * @since 0.1.0
 */
class Posts extends Base {

	/** Base (unprefixed) table name: resolves to {$wpdb->prefix}posts. */
	protected $table_name = 'posts';

	/** Short alias used in generated SQL. */
	protected $table_alias = 'p';

	/** The schema describing this table's columns and indexes. */
	protected $table_schema = PostsSchema::class;

	/*
	 * Item names are NAMESPACED ('wpct_'), NOT the bare 'post'/'posts'. They drive
	 * BerlinDB's hook and filter names - with the empty plugin prefix that keeps the
	 * table named 'wp_posts', a plural of 'posts' would fire the_posts and collide
	 * head-on with WordPress core's own the_posts filter. (Meta type is mapped back to
	 * the real 'post' via get_meta_type() when meta is wired.)
	 */
	protected $item_name = 'wpct_post';

	/** Plural item name (namespaced; drives the `the_wpct_posts` filter). */
	protected $item_name_plural = 'wpct_posts';

	/** The Row subclass raw rows are shaped into. */
	protected $item_shape = Post::class;

	/** Object cache group for this query (namespaced away from WordPress core). */
	protected $cache_group = 'wpcoretables_posts';

	/**
	 * Map the meta type back to the REAL WordPress singular ('post').
	 *
	 * item_name is namespaced ('wpct_post') only to avoid the hook/filter collision
	 * (berlindb/core#242); left to the default, BerlinDB's native item-meta methods
	 * would derive the meta type from it and miss wp_postmeta. Overriding it here
	 * points get_metadata( 'post', ... ) at the real meta table. (A future core
	 * change could derive this from a declared meta relationship instead.)
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	public function get_meta_type() {
		return 'post';
	}
}
