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
use WPCoreTables\Queries\Posts;
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
