<?php
/**
 * Terraso functions and definitions.
 *
 * @package Terraso
 * @since   1.0.0
 */

define( 'THEME_VERSION', '1.0.2' );

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once __DIR__ . '/includes/class-terraso-cli.php';
}

require_once __DIR__ . '/class-terraso.php';
