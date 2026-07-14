<?php
/**
 * Schema for the WordPress core `commentmeta` table.
 *
 * Faithful to wp-admin/includes/schema.php. Query-only: WordPress owns the table,
 * so this is never installed - the flags (cache_key/in/...) exist to make BerlinDB
 * queries over core comment meta expressive.
 *
 * @package WPCoreTables
 */

declare( strict_types = 1 );

namespace WPCoreTables\Schemas;

use BerlinDB\Database\Kern\Schema;

defined( 'ABSPATH' ) || exit;

/**
 * The `wp_commentmeta` schema.
 *
 * @since 0.1.0
 */
class CommentMeta extends Schema {

	/**
	 * Columns, in table order.
	 *
	 * @since 0.1.0
	 * @var   array<int,array<string,mixed>>
	 */
	public $columns = array(
		array(
			'name'      => 'meta_id',
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
			'name'      => 'comment_id',
			'type'      => 'bigint',
			'length'    => '20',
			'unsigned'  => true,
			'default'   => 0,
			'cache_key' => true,
			'in'        => true,
		),
		array(
			'name'       => 'meta_key',
			'type'       => 'varchar',
			'length'     => '255',
			'allow_null' => true,
			'default'    => null,
			'cache_key'  => true,
			'in'         => true,
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
			'columns' => array( 'meta_id' ),
		),
		array(
			'name'    => 'comment_id',
			'type'    => 'key',
			'columns' => array( 'comment_id' ),
		),
		array(
			'name'    => 'meta_key',
			'type'    => 'key',
			'columns' => array( 'meta_key(191)' ),
		),
	);
}
