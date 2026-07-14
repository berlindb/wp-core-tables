<?php
/**
 * Schema for the WordPress core `links` table.
 *
 * Faithful to wp-admin/includes/schema.php. Query-only: WordPress owns the table,
 * so this is never installed - the flags (cache_key/in/date_query/sortable/...)
 * exist to make BerlinDB queries over core links expressive.
 *
 * @package WPCoreTables
 */

declare( strict_types = 1 );

namespace WPCoreTables\Schemas;

use BerlinDB\Database\Kern\Schema;

defined( 'ABSPATH' ) || exit;

/**
 * The `wp_links` schema.
 *
 * @since 0.1.0
 */
class Links extends Schema {

	/**
	 * Columns, in table order.
	 *
	 * @since 0.1.0
	 * @var   array<int,array<string,mixed>>
	 */
	public $columns = array(
		array(
			'name'      => 'link_id',
			'type'      => 'bigint',
			'length'    => '20',
			'unsigned'  => true,
			'extra'     => 'auto_increment',
			'primary'   => true,
			'default'   => false,
			'cache_key' => true,
			'sortable'  => true,
		),
		array(
			'name'    => 'link_url',
			'type'    => 'varchar',
			'length'  => '255',
			'default' => '',
		),
		array(
			'name'    => 'link_name',
			'type'    => 'varchar',
			'length'  => '255',
			'default' => '',
		),
		array(
			'name'    => 'link_image',
			'type'    => 'varchar',
			'length'  => '255',
			'default' => '',
		),
		array(
			'name'    => 'link_target',
			'type'    => 'varchar',
			'length'  => '25',
			'default' => '',
		),
		array(
			'name'    => 'link_description',
			'type'    => 'varchar',
			'length'  => '255',
			'default' => '',
		),
		array(
			'name'      => 'link_visible',
			'type'      => 'varchar',
			'length'    => '20',
			'default'   => 'Y',
			'cache_key' => true,
			'in'        => true,
		),
		array(
			'name'     => 'link_owner',
			'type'     => 'bigint',
			'length'   => '20',
			'unsigned' => true,
			'default'  => 1,
			'in'       => true,
		),
		array(
			'name'     => 'link_rating',
			'type'     => 'int',
			'length'   => '11',
			'default'  => 0,
			'sortable' => true,
		),
		array(
			'name'       => 'link_updated',
			'type'       => 'datetime',
			'default'    => '0000-00-00 00:00:00',
			'date_query' => true,
			'sortable'   => true,
		),
		array(
			'name'    => 'link_rel',
			'type'    => 'varchar',
			'length'  => '255',
			'default' => '',
		),
		array(
			'name'    => 'link_notes',
			'type'    => 'mediumtext',
			'default' => '',
		),
		array(
			'name'    => 'link_rss',
			'type'    => 'varchar',
			'length'  => '255',
			'default' => '',
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
			'columns' => array( 'link_id' ),
		),
		array(
			'name'    => 'link_visible',
			'type'    => 'key',
			'columns' => array( 'link_visible' ),
		),
	);
}
