<?php
/**
 * Schema for the WordPress core `posts` table.
 *
 * Faithful to wp-admin/includes/schema.php. Query-only: WordPress owns the table,
 * so this is never installed - the flags (cache_key/in/date_query/sortable/...)
 * exist to make BerlinDB queries over core posts expressive.
 *
 * @package WPCoreTables
 */

declare( strict_types = 1 );

namespace WPCoreTables\Schemas;

use BerlinDB\Database\Kern\Schema;

defined( 'ABSPATH' ) || exit;

/**
 * The `wp_posts` schema.
 *
 * @since 0.1.0
 */
class Posts extends Schema {

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
					'query'  => \WPCoreTables\Queries\PostMeta::class,
					'column' => 'post_id',
					'type'   => 'has_many',
					'name'   => 'meta',
				),
				array(
					'query'  => \WPCoreTables\Queries\TermRelationships::class,
					'column' => 'object_id',
					'type'   => 'has_many',
					'name'   => 'term_relationships',
				),
				array(
					'query'  => \WPCoreTables\Queries\Comments::class,
					'column' => 'comment_post_ID',
					'type'   => 'has_many',
					'name'   => 'comments',
				),
			),
		),
		array(
			'name'          => 'post_author',
			'type'          => 'bigint',
			'length'        => '20',
			'unsigned'      => true,
			'default'       => 0,
			'cache_key'     => true,
			'in'            => true,
			'not_in'        => true,
			'sortable'      => true,
			'relationships' => array(
				array(
					'query'  => \WPCoreTables\Queries\Users::class,
					'column' => 'ID',
					'type'   => 'belongs_to',
					'name'   => 'author',
				),
			),
		),
		array(
			'name'       => 'post_date',
			'type'       => 'datetime',
			'default'    => '0000-00-00 00:00:00',
			'date_query' => true,
			'sortable'   => true,
		),
		array(
			'name'       => 'post_date_gmt',
			'type'       => 'datetime',
			'default'    => '0000-00-00 00:00:00',
			'date_query' => true,
			'sortable'   => true,
		),
		array(
			'name'    => 'post_content',
			'type'    => 'longtext',
			'default' => '',
		),
		array(
			'name'       => 'post_title',
			'type'       => 'text',
			'default'    => '',
			'searchable' => true,
		),
		array(
			'name'       => 'post_excerpt',
			'type'       => 'text',
			'default'    => '',
			'searchable' => true,
		),
		array(
			'name'      => 'post_status',
			'type'      => 'varchar',
			'length'    => '20',
			'default'   => 'publish',
			'cache_key' => true,
			'in'        => true,
			'not_in'    => true,
		),
		array(
			'name'    => 'comment_status',
			'type'    => 'varchar',
			'length'  => '20',
			'default' => 'open',
			'in'      => true,
		),
		array(
			'name'    => 'ping_status',
			'type'    => 'varchar',
			'length'  => '20',
			'default' => 'open',
			'in'      => true,
		),
		array(
			'name'    => 'post_password',
			'type'    => 'varchar',
			'length'  => '255',
			'default' => '',
		),
		array(
			'name'      => 'post_name',
			'type'      => 'varchar',
			'length'    => '200',
			'default'   => '',
			'cache_key' => true,
		),
		array(
			'name'    => 'to_ping',
			'type'    => 'text',
			'default' => '',
		),
		array(
			'name'    => 'pinged',
			'type'    => 'text',
			'default' => '',
		),
		array(
			'name'       => 'post_modified',
			'type'       => 'datetime',
			'default'    => '0000-00-00 00:00:00',
			'date_query' => true,
			'sortable'   => true,
		),
		array(
			'name'       => 'post_modified_gmt',
			'type'       => 'datetime',
			'default'    => '0000-00-00 00:00:00',
			'date_query' => true,
			'sortable'   => true,
		),
		array(
			'name'    => 'post_content_filtered',
			'type'    => 'longtext',
			'default' => '',
		),
		array(
			'name'      => 'post_parent',
			'type'      => 'bigint',
			'length'    => '20',
			'unsigned'  => true,
			'default'   => 0,
			'cache_key' => true,
			'in'        => true,
		),
		array(
			'name'    => 'guid',
			'type'    => 'varchar',
			'length'  => '255',
			'default' => '',
		),
		array(
			'name'     => 'menu_order',
			'type'     => 'int',
			'length'   => '11',
			'default'  => 0,
			'sortable' => true,
		),
		array(
			'name'      => 'post_type',
			'type'      => 'varchar',
			'length'    => '20',
			'default'   => 'post',
			'cache_key' => true,
			'in'        => true,
			'not_in'    => true,
		),
		array(
			'name'    => 'post_mime_type',
			'type'    => 'varchar',
			'length'  => '100',
			'default' => '',
			'in'      => true,
		),
		array(
			'name'     => 'comment_count',
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
			'columns' => array( 'ID' ),
		),
		array(
			'name'    => 'post_name',
			'type'    => 'key',
			'columns' => array( 'post_name(191)' ),
		),
		array(
			'name'    => 'type_status_date',
			'type'    => 'key',
			'columns' => array( 'post_type', 'post_status', 'post_date', 'ID' ),
		),
		array(
			'name'    => 'post_parent',
			'type'    => 'key',
			'columns' => array( 'post_parent' ),
		),
		array(
			'name'    => 'post_author',
			'type'    => 'key',
			'columns' => array( 'post_author' ),
		),
		array(
			'name'    => 'type_status_author',
			'type'    => 'key',
			'columns' => array( 'post_type', 'post_status', 'post_author' ),
		),
	);
}
