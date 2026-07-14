<?php
/**
 * Row shape for a WordPress core comment.
 *
 * @package WPCoreTables
 */

declare( strict_types = 1 );

namespace WPCoreTables\Rows;

use BerlinDB\Database\Kern\Row;

defined( 'ABSPATH' ) || exit;

/**
 * A single `wp_comments` row, shaped by BerlinDB.
 *
 * The base Row hydrates every schema column onto the object; this subclass exists
 * so callers can type-hint a comment and so comment-specific accessors can be added
 * later.
 *
 * @since 0.1.0
 */
class Comment extends Row {

}
