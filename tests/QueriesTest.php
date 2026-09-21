<?php
/**
 * Integration tests: query core tables through BerlinDB, resolve relationships, and
 * read meta - against fixtures created via WordPress, proving the two agree.
 *
 * @package WPCoreTables\Tests
 */

declare( strict_types = 1 );

namespace WPCoreTables\Tests;

use WPCoreTables\Queries\Comments;
use WPCoreTables\Queries\Options;
use WPCoreTables\Queries\PostMeta;
use WPCoreTables\Queries\Posts;
use WPCoreTables\Queries\TermRelationships;
use WPCoreTables\Queries\Terms;
use WPCoreTables\Queries\Users;
use Yoast\WPTestUtils\WPIntegration\TestCase;

/**
 * @since 0.1.0
 */
class QueriesTest extends TestCase {

	/** @var int */
	private $author_id;

	/** @var int */
	private $post_id;

	/** @var int */
	private $comment_id;

	public function set_up(): void {
		parent::set_up();

		$this->author_id = self::factory()->user->create( array( 'user_login' => 'wpct_author' ) );
		$this->post_id   = self::factory()->post->create( array(
			'post_author' => $this->author_id,
			'post_title'  => 'WPCT Test Post',
			'post_status' => 'publish',
		) );
		$this->comment_id = self::factory()->comment->create( array(
			'comment_post_ID' => $this->post_id,
			'user_id'         => $this->author_id,
		) );
		update_post_meta( $this->post_id, 'wpct_meta', 'value-42' );
		wp_set_object_terms( $this->post_id, 'wpct-cat', 'category' );
	}

	/**
	 * A BerlinDB Query reads the row WordPress created.
	 *
	 * @since 0.1.0
	 */
	public function test_query_reads_the_post(): void {
		$q     = new Posts( array( 'include' => array( $this->post_id ), 'number' => 1 ) );
		$found = wp_list_pluck( $q->items, 'post_title', 'ID' );

		$this->assertArrayHasKey( $this->post_id, $found );
		$this->assertSame( 'WPCT Test Post', $found[ $this->post_id ] );
	}

	/**
	 * Native post updates invalidate facade result, row, and secondary caches.
	 *
	 * @since 0.1.0
	 */
	public function test_native_post_update_invalidates_facade_caches(): void {
		$vars  = array( 'include' => array( $this->post_id ), 'number' => 1 );
		$query = new Posts( $vars );
		$slug  = get_post_field( 'post_name', $this->post_id );

		$this->assertSame( 'WPCT Test Post', $query->items[0]->post_title );
		$this->assertSame( 'WPCT Test Post', $query->get_item_by( 'ID', $this->post_id )->post_title );
		$this->assertSame( $this->post_id, (int) $query->get_item_by( 'post_name', $slug )->ID );
		wp_cache_set( 'unrelated', 'retained', 'wpct_test_other' );

		wp_update_post( array(
			'ID'         => $this->post_id,
			'post_title' => 'Updated in WordPress',
			'post_name'  => 'updated-in-wordpress',
		) );

		$updated = new Posts( $vars );
		$this->assertSame( 'Updated in WordPress', $updated->items[0]->post_title );
		$this->assertSame( 'Updated in WordPress', $updated->get_item_by( 'ID', $this->post_id )->post_title );
		$this->assertFalse( $updated->get_item_by( 'post_name', $slug ) );
		$this->assertSame( $this->post_id, (int) $updated->get_item_by( 'post_name', 'updated-in-wordpress' )->ID );
		$this->assertSame( 'retained', wp_cache_get( 'unrelated', 'wpct_test_other' ) );
	}

	/**
	 * Native metadata writes invalidate the companion's by-ID row cache.
	 *
	 * @since 0.1.0
	 */
	public function test_native_post_meta_update_invalidates_facade_cache(): void {
		global $wpdb;

		$meta_id = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT meta_id FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = %s",
			$this->post_id,
			'wpct_meta'
		) );
		$query   = new PostMeta( array( 'number' => 0 ) );

		$this->assertSame( 'value-42', $query->get_item_by( 'meta_id', $meta_id )->meta_value );
		update_post_meta( $this->post_id, 'wpct_meta', 'value-43' );
		$this->assertSame( 'value-43', $query->get_item_by( 'meta_id', $meta_id )->meta_value );
	}

	/**
	 * Native user changes refresh the network-global facade cache group.
	 *
	 * @since 0.1.0
	 */
	public function test_native_user_update_invalidates_facade_cache(): void {
		$query = new Users( array( 'number' => 0 ) );

		$this->assertSame( 'wpct_author', $query->get_item_by( 'ID', $this->author_id )->user_login );
		wp_update_user( array( 'ID' => $this->author_id, 'display_name' => 'Updated Author' ) );
		$this->assertSame( 'Updated Author', $query->get_item_by( 'ID', $this->author_id )->display_name );
	}

	/**
	 * Native option changes refresh facade primary and option-name lookups.
	 *
	 * @since 0.1.0
	 */
	public function test_native_option_update_invalidates_facade_cache(): void {
		global $wpdb;

		add_option( 'wpct_test_option', 'before' );
		$query = new Options( array( 'number' => 0 ) );
		$id    = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT option_id FROM {$wpdb->options} WHERE option_name = %s",
			'wpct_test_option'
		) );

		$this->assertSame( 'before', $query->get_item_by( 'option_name', 'wpct_test_option' )->option_value );
		update_option( 'wpct_test_option', 'after' );
		$this->assertSame( 'after', $query->get_item_by( 'option_id', $id )->option_value );
		$this->assertSame( 'after', $query->get_item_by( 'option_name', 'wpct_test_option' )->option_value );
	}

	/**
	 * Native term assignments invalidate cached junction-table result lists.
	 *
	 * @since 0.1.0
	 */
	public function test_native_term_assignment_invalidates_facade_cache(): void {
		$vars   = array( 'object_id' => $this->post_id, 'number' => 50 );
		$before = new TermRelationships( $vars );
		$term   = wp_insert_term( 'wpct-other-cat', 'category' );

		$this->assertNotWPError( $term );
		wp_set_object_terms( $this->post_id, array( (int) $term['term_id'] ), 'category', true );

		$after = new TermRelationships( $vars );
		$this->assertCount( count( $before->items ) + 1, $after->items );
	}

	/**
	 * belongs_to (post -> author) and has_many (post -> meta) resolve.
	 *
	 * @since 0.1.0
	 */
	public function test_post_relationships(): void {
		$q    = new Posts( array( 'include' => array( $this->post_id ), 'number' => 1 ) );
		$post = $q->items[0];

		$author = $q->get_related( $post, 'author' );
		$this->assertIsObject( $author );
		$this->assertSame( 'wpct_author', $author->user_login );

		$meta_keys = wp_list_pluck( (array) $q->get_related( $post, 'meta' ), 'meta_key' );
		$this->assertContains( 'wpct_meta', $meta_keys );
	}

	/**
	 * belongs_to the other way (comment -> post).
	 *
	 * @since 0.1.0
	 */
	public function test_comment_belongs_to_post(): void {
		$q       = new Comments( array( 'include' => array( $this->comment_id ), 'number' => 1 ) );
		$comment = $q->items[0];
		$post    = $q->get_related( $comment, 'post' );

		$this->assertIsObject( $post );
		$this->assertSame( $this->post_id, (int) $post->ID );
	}

	/**
	 * Every meta-bearing query maps its meta type back to the real WordPress singular.
	 *
	 * @since 0.1.0
	 */
	public function test_meta_type_overrides(): void {
		$this->assertSame( 'post', ( new Posts( array( 'number' => 0 ) ) )->get_meta_type() );
		$this->assertSame( 'user', ( new Users( array( 'number' => 0 ) ) )->get_meta_type() );
		$this->assertSame( 'comment', ( new Comments( array( 'number' => 0 ) ) )->get_meta_type() );
		$this->assertSame( 'term', ( new Terms( array( 'number' => 0 ) ) )->get_meta_type() );
	}

	/**
	 * A taxonomy filter resolves through the term_relationships composite-key junction.
	 *
	 * @since 0.1.0
	 */
	public function test_taxonomy_filter_through_junction(): void {
		$q = new Posts( array(
			'fields'   => 'ids',
			'number'   => 50,
			'relation' => array(
				'name'     => 'term_relationships',
				'relation' => array(
					'name'     => 'term_taxonomy',
					'where'    => array( 'taxonomy' => 'category' ),
					'relation' => array(
						'name'  => 'term',
						'where' => array( 'slug' => 'wpct-cat' ),
					),
				),
			),
		) );

		$this->assertContains( $this->post_id, array_map( 'intval', (array) $q->items ) );
	}
}
