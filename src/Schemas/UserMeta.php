<?php
/**
 * Schema for the WordPress core `usermeta` table.
 *
 * Faithful to wp-admin/includes/schema.php. Query-only: WordPress owns the table,
 * so this is never installed - the flags (cache_key/in/date_query/sortable/...)
 * exist to make BerlinDB queries over core usermeta expressive.
 *
 * @package WPCoreTables
 */

declare( strict_types = 1 );

namespace WPCoreTables\Schemas;

use BerlinDB\Database\Kern\Schema;

defined( 'ABSPATH' ) || exit;

/**
 * The `wp_usermeta` schema.
 *
 * @since 0.1.0
 */
class UserMeta extends Schema {

	/**
	 * Columns, in table order.
	 *
	 * @since 0.1.0
	 * @var   array<int,array<string,mixed>>
	 */
	public $columns = array(
		array(
			'name'      => 'umeta_id',
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
			'name'      => 'user_id',
			'type'      => 'bigint',
			'length'    => '20',
			'unsigned'  => true,
			'default'   => 0,
			'in'        => true,
			'cache_key' => true,
		),
		array(
			'name'       => 'meta_key',
			'type'       => 'varchar',
			'length'     => '255',
			'allow_null' => true,
			'default'    => null,
			'in'         => true,
			'cache_key'  => true,
		),
		array(
			'name'       => 'meta_value',
			'type'       => 'longtext',
			'allow_null' => true,
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
			'columns' => array( 'umeta_id' ),
		),
		array(
			'name'    => 'user_id',
			'type'    => 'key',
			'columns' => array( 'user_id' ),
		),
		array(
			'name'    => 'meta_key',
			'type'    => 'key',
			'columns' => array( 'meta_key(191)' ),
		),
	);
}
