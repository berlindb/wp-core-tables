<?php
/**
 * Schema for the WordPress core `term_taxonomy` table.
 *
 * Faithful to wp-admin/includes/schema.php. Query-only: WordPress owns the table,
 * so this is never installed - the flags (cache_key/in/sortable/...) exist to make
 * BerlinDB queries over core term taxonomies expressive.
 *
 * @package WPCoreTables
 */

declare( strict_types = 1 );

namespace WPCoreTables\Schemas;

use BerlinDB\Database\Kern\Schema;

defined( 'ABSPATH' ) || exit;

/**
 * The `wp_term_taxonomy` schema.
 *
 * @since 0.1.0
 */
class TermTaxonomy extends Schema {

	/**
	 * Columns, in table order.
	 *
	 * @since 0.1.0
	 * @var   array<int,array<string,mixed>>
	 */
	public $columns = array(
		array(
			'name'          => 'term_taxonomy_id',
			'type'          => 'bigint',
			'length'        => '20',
			'unsigned'      => true,
			'extra'         => 'auto_increment',
			'primary'       => true,
			'default'       => false,
			'cache_key'     => true,
			'sortable'      => true,
			'relationships' => array(
				array(
					'query'  => \WPCoreTables\Queries\TermRelationships::class,
					'column' => 'term_taxonomy_id',
					'type'   => 'has_many',
					'name'   => 'relationships',
				),
			),
		),
		array(
			'name'          => 'term_id',
			'type'          => 'bigint',
			'length'        => '20',
			'unsigned'      => true,
			'default'       => 0,
			'cache_key'     => true,
			'in'            => true,
			'relationships' => array(
				array(
					'query'  => \WPCoreTables\Queries\Terms::class,
					'column' => 'term_id',
					'type'   => 'belongs_to',
					'name'   => 'term',
				),
			),
		),
		array(
			'name'      => 'taxonomy',
			'type'      => 'varchar',
			'length'    => '32',
			'default'   => '',
			'cache_key' => true,
			'in'        => true,
		),
		array(
			'name'    => 'description',
			'type'    => 'longtext',
			'default' => '',
		),
		array(
			'name'     => 'parent',
			'type'     => 'bigint',
			'length'   => '20',
			'unsigned' => true,
			'default'  => 0,
			'in'       => true,
		),
		array(
			'name'     => 'count',
			'type'     => 'bigint',
			'length'   => '20',
			'default'  => 0,
			'sortable' => true,
		),
	);

	/**
	 * Indexes, faithful to core (including prefix lengths).
	 *
	 * @since 0.1.0
	 * @var   array<int,array<string,mixed>>
	 */
	public $indexes = array(
		array(
			'type'    => 'primary',
			'columns' => array( 'term_taxonomy_id' ),
		),
		array(
			'name'    => 'term_id_taxonomy',
			'type'    => 'unique',
			'columns' => array( 'term_id', 'taxonomy' ),
		),
		array(
			'name'    => 'taxonomy',
			'type'    => 'key',
			'columns' => array( 'taxonomy' ),
		),
	);
}
