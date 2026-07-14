<?php
/**
 * Row shape for a WordPress core term taxonomy.
 *
 * @package WPCoreTables
 */

declare( strict_types = 1 );

namespace WPCoreTables\Rows;

use BerlinDB\Database\Kern\Row;

defined( 'ABSPATH' ) || exit;

/**
 * A single `wp_term_taxonomy` row, shaped by BerlinDB.
 *
 * The base Row hydrates every schema column onto the object; this subclass exists
 * so callers can type-hint a term taxonomy and so taxonomy-specific accessors can
 * be added later.
 *
 * @since 0.1.0
 */
class TermTaxonomy extends Row {

}
