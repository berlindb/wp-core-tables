<?php
/**
 * Schema for the WordPress core `terms` table.
 *
 * Faithful to wp-admin/includes/schema.php. Query-only: WordPress owns the table,
 * so this is never installed - the flags (cache_key/in/searchable/sortable/...)
 * exist to make BerlinDB queries over core terms expressive.
 *
 * @package WPCoreTables
 */

declare( strict_types = 1 );

namespace WPCoreTables\Schemas;

use BerlinDB\Database\Kern\Schema;

defined( 'ABSPATH' ) || exit;

/**
 * The `wp_terms` schema.
 *
 * @since 0.1.0
 */
class Terms extends Schema {

	/**
	 * Columns, in table order.
	 *
	 * @since 0.1.0
	 * @var   array<int,array<string,mixed>>
	 */
	public $columns = array(
		array(
			'name'          => 'term_id',
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
					'query'  => \WPCoreTables\Queries\TermMeta::class,
					'column' => 'term_id',
					'type'   => 'has_many',
					'name'   => 'meta',
				),
				array(
					'query'  => \WPCoreTables\Queries\TermTaxonomy::class,
					'column' => 'term_id',
					'type'   => 'has_many',
					'name'   => 'taxonomies',
				),
			),
		),
		array(
			'name'       => 'name',
			'type'       => 'varchar',
			'length'     => '200',
			'default'    => '',
			'searchable' => true,
			'sortable'   => true,
		),
		array(
			'name'      => 'slug',
			'type'      => 'varchar',
			'length'    => '200',
			'default'   => '',
			'cache_key' => true,
		),
		array(
			'name'    => 'term_group',
			'type'    => 'bigint',
			'length'  => '10',
			'default' => 0,
			'in'      => true,
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
			'columns' => array( 'term_id' ),
		),
		array(
			'name'    => 'slug',
			'type'    => 'key',
			'columns' => array( 'slug(191)' ),
		),
		array(
			'name'    => 'name',
			'type'    => 'key',
			'columns' => array( 'name(191)' ),
		),
	);
}
