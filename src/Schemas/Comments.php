<?php
/**
 * Schema for the WordPress core `comments` table.
 *
 * Faithful to wp-admin/includes/schema.php. Query-only: WordPress owns the table,
 * so this is never installed - the flags (cache_key/in/date_query/sortable/...)
 * exist to make BerlinDB queries over core comments expressive.
 *
 * @package WPCoreTables
 */

declare( strict_types = 1 );

namespace WPCoreTables\Schemas;

use BerlinDB\Database\Kern\Schema;

defined( 'ABSPATH' ) || exit;

/**
 * The `wp_comments` schema.
 *
 * @since 0.1.0
 */
class Comments extends Schema {

	/**
	 * Columns, in table order.
	 *
	 * @since 0.1.0
	 * @var   array<int,array<string,mixed>>
	 */
	public $columns = array(
		array(
			'name'          => 'comment_ID',
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
					'query'  => \WPCoreTables\Queries\CommentMeta::class,
					'column' => 'comment_id',
					'type'   => 'has_many',
					'name'   => 'meta',
				),
			),
		),
		array(
			'name'          => 'comment_post_ID',
			'type'          => 'bigint',
			'length'        => '20',
			'unsigned'      => true,
			'default'       => 0,
			'cache_key'     => true,
			'in'            => true,
			'sortable'      => true,
			'relationships' => array(
				array(
					'query'  => \WPCoreTables\Queries\Posts::class,
					'column' => 'ID',
					'type'   => 'belongs_to',
					'name'   => 'post',
				),
			),
		),
		array(
			'name'    => 'comment_author',
			'type'    => 'tinytext',
			'default' => '',
		),
		array(
			'name'      => 'comment_author_email',
			'type'      => 'varchar',
			'length'    => '100',
			'default'   => '',
			'cache_key' => true,
		),
		array(
			'name'    => 'comment_author_url',
			'type'    => 'varchar',
			'length'  => '200',
			'default' => '',
		),
		array(
			'name'    => 'comment_author_IP',
			'type'    => 'varchar',
			'length'  => '100',
			'default' => '',
		),
		array(
			'name'    => 'comment_date',
			'type'    => 'datetime',
			'default' => '0000-00-00 00:00:00',
		),
		array(
			'name'       => 'comment_date_gmt',
			'type'       => 'datetime',
			'default'    => '0000-00-00 00:00:00',
			'date_query' => true,
			'sortable'   => true,
		),
		array(
			'name'    => 'comment_content',
			'type'    => 'text',
			'default' => '',
		),
		array(
			'name'    => 'comment_karma',
			'type'    => 'int',
			'length'  => '11',
			'default' => 0,
		),
		array(
			'name'      => 'comment_approved',
			'type'      => 'varchar',
			'length'    => '20',
			'default'   => '1',
			'cache_key' => true,
			'in'        => true,
		),
		array(
			'name'    => 'comment_agent',
			'type'    => 'varchar',
			'length'  => '255',
			'default' => '',
		),
		array(
			'name'    => 'comment_type',
			'type'    => 'varchar',
			'length'  => '20',
			'default' => 'comment',
			'in'      => true,
		),
		array(
			'name'     => 'comment_parent',
			'type'     => 'bigint',
			'length'   => '20',
			'unsigned' => true,
			'default'  => 0,
			'in'       => true,
		),
		array(
			'name'          => 'user_id',
			'type'          => 'bigint',
			'length'        => '20',
			'unsigned'      => true,
			'default'       => 0,
			'cache_key'     => true,
			'in'            => true,
			'relationships' => array(
				array(
					'query'  => \WPCoreTables\Queries\Users::class,
					'column' => 'ID',
					'type'   => 'belongs_to',
					'name'   => 'author',
				),
			),
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
			'columns' => array( 'comment_ID' ),
		),
		array(
			'name'    => 'comment_post_ID',
			'type'    => 'key',
			'columns' => array( 'comment_post_ID' ),
		),
		array(
			'name'    => 'comment_approved_date_gmt',
			'type'    => 'key',
			'columns' => array( 'comment_approved', 'comment_date_gmt' ),
		),
		array(
			'name'    => 'comment_date_gmt',
			'type'    => 'key',
			'columns' => array( 'comment_date_gmt' ),
		),
		array(
			'name'    => 'comment_parent',
			'type'    => 'key',
			'columns' => array( 'comment_parent' ),
		),
		array(
			'name'    => 'comment_author_email',
			'type'    => 'key',
			'columns' => array( 'comment_author_email(10)' ),
		),
	);
}
