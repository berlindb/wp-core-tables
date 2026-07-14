<?php
/**
 * Schema for the WordPress core `users` table.
 *
 * Faithful to wp-admin/includes/schema.php. Query-only: WordPress owns the table,
 * so this is never installed - the flags (cache_key/in/date_query/sortable/...)
 * exist to make BerlinDB queries over core users expressive.
 *
 * @package WPCoreTables
 */

declare( strict_types = 1 );

namespace WPCoreTables\Schemas;

use BerlinDB\Database\Kern\Schema;

defined( 'ABSPATH' ) || exit;

/**
 * The `wp_users` schema.
 *
 * @since 0.1.0
 */
class Users extends Schema {

	/**
	 * Columns, in table order.
	 *
	 * @since 0.1.0
	 * @var   array<int,array<string,mixed>>
	 */
	public $columns = array(
		array(
			'name'          => 'ID',
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
					'query'  => \WPCoreTables\Queries\UserMeta::class,
					'column' => 'user_id',
					'type'   => 'has_many',
					'name'   => 'meta',
				),
				array(
					'query'  => \WPCoreTables\Queries\Posts::class,
					'column' => 'post_author',
					'type'   => 'has_many',
					'name'   => 'posts',
				),
			),
		),
		array(
			'name'      => 'user_login',
			'type'      => 'varchar',
			'length'    => '60',
			'default'   => '',
			'cache_key' => true,
		),
		array(
			'name'    => 'user_pass',
			'type'    => 'varchar',
			'length'  => '255',
			'default' => '',
		),
		array(
			'name'      => 'user_nicename',
			'type'      => 'varchar',
			'length'    => '50',
			'default'   => '',
			'cache_key' => true,
		),
		array(
			'name'      => 'user_email',
			'type'      => 'varchar',
			'length'    => '100',
			'default'   => '',
			'cache_key' => true,
		),
		array(
			'name'    => 'user_url',
			'type'    => 'varchar',
			'length'  => '100',
			'default' => '',
		),
		array(
			'name'       => 'user_registered',
			'type'       => 'datetime',
			'default'    => '0000-00-00 00:00:00',
			'date_query' => true,
			'sortable'   => true,
		),
		array(
			'name'    => 'user_activation_key',
			'type'    => 'varchar',
			'length'  => '255',
			'default' => '',
		),
		array(
			'name'    => 'user_status',
			'type'    => 'int',
			'length'  => '11',
			'default' => 0,
		),
		array(
			'name'       => 'display_name',
			'type'       => 'varchar',
			'length'     => '250',
			'default'    => '',
			'searchable' => true,
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
			'columns' => array( 'ID' ),
		),
		array(
			'name'    => 'user_login_key',
			'type'    => 'key',
			'columns' => array( 'user_login' ),
		),
		array(
			'name'    => 'user_nicename',
			'type'    => 'key',
			'columns' => array( 'user_nicename' ),
		),
		array(
			'name'    => 'user_email',
			'type'    => 'key',
			'columns' => array( 'user_email' ),
		),
	);
}
