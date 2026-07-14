<?php
/**
 * Schema-drift guard: every declared schema must stay COLUMN-faithful to the live
 * WordPress table it registers. Uses BerlinDB's own introspection (Schema::from_table
 * + diff), so the day a WordPress version changes a core table, this goes red.
 *
 * @package WPCoreTables\Tests
 */

declare( strict_types = 1 );

namespace WPCoreTables\Tests;

use BerlinDB\Database\Kern\Schema;
use Yoast\WPTestUtils\WPIntegration\TestCase;

/**
 * @since 0.1.0
 */
class SchemaDriftTest extends TestCase {

	/**
	 * Declared Schema class => the $wpdb property holding the live table name.
	 *
	 * @return array<string,array{0:string,1:string}>
	 */
	public function table_provider(): array {
		return array(
			'posts'              => array( 'Posts', 'posts' ),
			'postmeta'           => array( 'PostMeta', 'postmeta' ),
			'users'              => array( 'Users', 'users' ),
			'usermeta'           => array( 'UserMeta', 'usermeta' ),
			'comments'           => array( 'Comments', 'comments' ),
			'commentmeta'        => array( 'CommentMeta', 'commentmeta' ),
			'terms'              => array( 'Terms', 'terms' ),
			'term_taxonomy'      => array( 'TermTaxonomy', 'term_taxonomy' ),
			'term_relationships' => array( 'TermRelationships', 'term_relationships' ),
			'termmeta'           => array( 'TermMeta', 'termmeta' ),
			'options'            => array( 'Options', 'options' ),
			'links'              => array( 'Links', 'links' ),
		);
	}

	/**
	 * The declared schema's COLUMNS must match the live table exactly.
	 *
	 * Scoped to columns (add/modify/drop of columns): index representation - prefix
	 * lengths, key ordering - round-trips imperfectly through introspection and is not
	 * a faithfulness signal, so it is deliberately excluded here.
	 *
	 * @dataProvider table_provider
	 *
	 * @param string $schema_class Short class name under WPCoreTables\Schemas.
	 * @param string $wpdb_key     $wpdb property holding the live table name.
	 */
	public function test_declared_columns_match_live_table( string $schema_class, string $wpdb_key ): void {
		global $wpdb;

		$fqcn     = "WPCoreTables\\Schemas\\{$schema_class}";
		$declared = new $fqcn();
		$actual   = Schema::from_table( $wpdb->{$wpdb_key} );

		// Column diff in BOTH directions (added, removed, or changed columns).
		$drift = array_merge(
			(array) $actual->diff( $declared )->to_sql( array( 'add', 'modify', 'drop' ) ),
			(array) $declared->diff( $actual )->to_sql( array( 'add', 'modify', 'drop' ) )
		);

		$this->assertSame(
			array(),
			$drift,
			"Declared schema for {$wpdb_key} has drifted from the live table:\n  " . implode( "\n  ", $drift )
		);
	}
}
