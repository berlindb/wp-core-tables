<?php
/**
 * Schema for the WordPress core `term_relationships` table.
 *
 * Faithful to wp-admin/includes/schema.php. Query-only: WordPress owns the table,
 * so this is never installed - the flags (cache_key/in/sortable/...) exist to make
 * BerlinDB queries over core term relationships expressive.
 *
 * A COMPOSITE-KEY junction table: its PRIMARY KEY spans both object_id and
 * term_taxonomy_id, so BOTH columns are marked 'primary' and neither
 * auto-increments (there is no surrogate key).
 *
 * @package WPCoreTables
 */

declare( strict_types = 1 );

namespace WPCoreTables\Schemas;

use BerlinDB\Database\Kern\Schema;

defined( 'ABSPATH' ) || exit;

/**
 * The `wp_term_relationships` schema.
 *
 * @since 0.1.0
 */
class TermRelationships extends Schema {

	/**
	 * Columns, in table order.
	 *
	 * @since 0.1.0
	 * @var   array<int,array<string,mixed>>
	 */
	public $columns = array(
		array(
			'name'      => 'object_id',
			'type'      => 'bigint',
			'length'    => '20',
			'unsigned'  => true,
			'primary'   => true,
			'default'   => 0,
			'cache_key' => true,
			'in'        => true,
		),
		array(
			'name'          => 'term_taxonomy_id',
			'type'          => 'bigint',
			'length'        => '20',
			'unsigned'      => true,
			'primary'       => true,
			'default'       => 0,
			'cache_key'     => true,
			'in'            => true,
			'relationships' => array(
				array(
					'query'  => \WPCoreTables\Queries\TermTaxonomy::class,
					'column' => 'term_taxonomy_id',
					'type'   => 'belongs_to',
					'name'   => 'term_taxonomy',
				),
			),
		),
		array(
			'name'     => 'term_order',
			'type'     => 'int',
			'length'   => '11',
			'default'  => 0,
			'sortable' => true,
		),
	);

	/**
	 * Indexes, faithful to core (including the composite primary key).
	 *
	 * @since 0.1.0
	 * @var   array<int,array<string,mixed>>
	 */
	public $indexes = array(
		array(
			'type'    => 'primary',
			'columns' => array( 'object_id', 'term_taxonomy_id' ),
		),
		array(
			'name'    => 'term_taxonomy_id',
			'type'    => 'key',
			'columns' => array( 'term_taxonomy_id' ),
		),
	);
}
