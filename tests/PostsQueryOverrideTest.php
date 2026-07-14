<?php
/**
 * Integration test for the posts_pre_query POC: a real WP_Query backed by BerlinDB.
 *
 * The invariant under test is agreement: a flagged (BerlinDB-backed) WP_Query must
 * return the same result as the identical unflagged (core SQL) query. That keeps the
 * test robust to whatever posts the test install already contains.
 *
 * @package WPCoreTables\Tests
 */

declare( strict_types = 1 );

namespace WPCoreTables\Tests;

use WP_Post;
use WP_Query;
use WPCoreTables\Overrides\PostsQueryOverride;
use Yoast\WPTestUtils\WPIntegration\TestCase;

/**
 * @since 0.1.0
 */
class PostsQueryOverrideTest extends TestCase {

	public function set_up(): void {
		parent::set_up();

		// A handful of publish posts to page through (on top of any default content).
		for ( $i = 0; $i < 5; $i++ ) {
			self::factory()->post->create( array(
				'post_type'   => 'post',
				'post_status' => 'publish',
				'post_title'  => 'Override Post ' . $i,
			) );
		}

		PostsQueryOverride::register();
	}

	public function tear_down(): void {
		PostsQueryOverride::unregister();
		parent::tear_down();
	}

	/**
	 * The same args with the opt-in flag run one extra query var; strip it to get the
	 * core-SQL baseline for the identical query.
	 *
	 * @param array $args Query args (without the flag).
	 * @return array{0: WP_Query, 1: WP_Query} [core query, BerlinDB-backed query].
	 */
	private function run_both( array $args ): array {
		$core = new WP_Query( $args );
		$bdb  = new WP_Query( array_merge( $args, array( PostsQueryOverride::FLAG => true ) ) );

		return array( $core, $bdb );
	}

	/**
	 * Without the opt-in flag, the override stays out of the way entirely.
	 *
	 * @since 0.1.0
	 */
	public function test_unflagged_query_is_untouched(): void {
		$q = new WP_Query( array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => 5,
		) );

		$this->assertNotEmpty( $q->posts );
		$this->assertContainsOnlyInstancesOf( WP_Post::class, $q->posts );
	}

	/**
	 * A flagged WP_Query returns the same page of posts as core - as real WP_Post
	 * objects - and reports the same pagination total.
	 *
	 * @since 0.1.0
	 */
	public function test_flagged_query_matches_core(): void {
		list( $core, $bdb ) = $this->run_both( array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => 2,
			'orderby'        => 'id',
			'order'          => 'ASC',
		) );

		// Core hydrated BerlinDB's rows into WP_Post objects...
		$this->assertContainsOnlyInstancesOf( WP_Post::class, $bdb->posts );

		// ...the same IDs, in the same order, honoring the LIMIT...
		$this->assertSame(
			wp_list_pluck( $core->posts, 'ID' ),
			wp_list_pluck( $bdb->posts, 'ID' )
		);

		// ...and the same pagination total across all matching posts.
		$this->assertSame( (int) $core->found_posts, (int) $bdb->found_posts );
		$this->assertSame( (int) $core->max_num_pages, (int) $bdb->max_num_pages );
		$this->assertGreaterThan( 1, $bdb->max_num_pages, 'fixtures should span >1 page' );
	}

	/**
	 * fields=ids short-circuits to an ID list straight from BerlinDB, matching core.
	 *
	 * @since 0.1.0
	 */
	public function test_flagged_query_fields_ids_matches_core(): void {
		list( $core, $bdb ) = $this->run_both( array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'orderby'        => 'id',
			'order'          => 'ASC',
		) );

		$this->assertSame(
			array_map( 'intval', $core->posts ),
			array_map( 'intval', $bdb->posts )
		);
		$this->assertSame( (int) $core->found_posts, (int) $bdb->found_posts );
	}
}
