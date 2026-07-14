<?php
/**
 * Compute this parity plugin's behavioral (column-flag) readiness and write its badge.
 *
 * Runs WITHOUT WordPress or a database - it only reflects the declared `columns` of
 * this plugin's own core-native Schema classes and scores them against shared
 * berlindb/core. Because these schemas are authored ON core, they use only flags core
 * already has: the score is 100% BY CONSTRUCTION, confirming the reproduction is
 * complete (not an independent finding, unlike a fork consumer). See
 * https://github.com/berlindb/readiness.
 *
 * Usage:  composer install && php bin/readiness-report.php
 *
 * @package WPCoreTables\Schemas
 */

declare( strict_types = 1 );

use BerlinDB\Readiness\Badge;
use BerlinDB\Readiness\CoreCapabilities;
use BerlinDB\Readiness\FlagReadiness;
use BerlinDB\Readiness\Report;
use BerlinDB\Readiness\SchemaSurface;

// The Schema files guard on ABSPATH; define it so they load in this bare PHP context.
defined( 'ABSPATH' ) || define( 'ABSPATH', __DIR__ . '/' );

require_once __DIR__ . '/../vendor/autoload.php';

$dir     = __DIR__ . '/../src/Schemas';
$classes = array();
foreach ( glob( $dir . '/*.php' ) ?: array() as $file ) {
	$base = basename( $file, '.php' );
	if ( 'manifest' === $base ) {
		continue;
	}
	$class = 'WPCoreTables\Schemas\\' . $base;
	if ( class_exists( $class ) ) {
		$classes[] = $class;
	}
}

if ( empty( $classes ) ) {
	fwrite( STDERR, "No Schema classes found under {$dir}\n" );
	exit( 1 );
}

$supported = CoreCapabilities::fromCore();
$declared  = SchemaSurface::fromClasses( $classes );
$report    = FlagReadiness::score( 'WordPress', $supported, $declared );

printf( "\n== WordPress behavioral readiness (flags) ==\n" );
printf( "  schemas: %d   core-recognized: %d   declared flags: %d\n\n", count( $classes ), count( $supported ), $report->total() );
foreach ( $report->rows() as $flag => $row ) {
	$status = ( Report::GAP === $row['status'] ) ? 'GAP' : $row['status'];
	printf( "  %-16s %-11s %-14s %d\n", $flag, $status, $row['via'], $row['columns'] );
}
printf( "\n  READINESS: %s%%  (%d/%d)\n", $report->percent(), $report->covered(), $report->total() );
printf( "  GAPS: %s\n\n", $report->is_ready() ? '(none)' : implode( ', ', $report->gaps() ) );

$out = __DIR__ . '/../.readiness';
if ( ! is_dir( $out ) ) {
	mkdir( $out, 0777, true );
}
file_put_contents( $out . '/wordpress.json', Badge::toJson( $report ) );
printf( "  wrote %s/wordpress.json\n\n", $out );
