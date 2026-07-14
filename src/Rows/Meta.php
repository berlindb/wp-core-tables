<?php
/**
 * Row shape for a WordPress core meta row.
 *
 * @package WPCoreTables
 */

declare( strict_types = 1 );

namespace WPCoreTables\Rows;

use BerlinDB\Database\Kern\Row;

defined( 'ABSPATH' ) || exit;

/**
 * A single meta row (postmeta / usermeta / commentmeta / termmeta), shaped by
 * BerlinDB. All four share the EAV shape (a meta id, an object id, meta_key,
 * meta_value), so they share one Row.
 *
 * @since 0.1.0
 */
class Meta extends Row {

}
