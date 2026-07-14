<?php
/**
 * Schema for the WordPress core `options` table.
 *
 * Faithful to wp-admin/includes/schema.php. Query-only: WordPress owns the table,
 * so this is never installed - the flags (cache_key/in/...) exist to make BerlinDB
 * queries over core options expressive.
 *
 * @package WPCoreTables
 */

declare( strict_types = 1 );

namespace WPCoreTables\Schemas;

use BerlinDB\Database\Kern\Schema;

defined( 'ABSPATH' ) || exit;

/**
 * The `wp_options` schema.
 *
 * @since 0.1.0
 */
class Options extends Schema {

	/**
	 * Columns, in table order.
	 *
	 * @since 0.1.0
	 * @var   array<int,array<string,mixed>>
	 */
	public $columns = array(
		array(
			'name'      => 'option_id',
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
			'name'      => 'option_name',
			'type'      => 'varchar',
			'length'    => '191',
			'default'   => '',
			'cache_key' => true,
		),
		array(
			'name'    => 'option_value',
			'type'    => 'longtext',
			'default' => '',
		),
		array(
			'name'      => 'autoload',
			'type'      => 'varchar',
			'length'    => '20',
			'default'   => 'yes',
			'cache_key' => true,
			'in'        => true,
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
			'columns' => array( 'option_id' ),
		),
		array(
			'name'    => 'option_name',
			'type'    => 'unique',
			'columns' => array( 'option_name' ),
		),
		array(
			'name'    => 'autoload',
			'type'    => 'key',
			'columns' => array( 'autoload' ),
		),
	);
}
