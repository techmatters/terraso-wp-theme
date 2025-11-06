<?php
/**
 * Terraso Help WordPress plugin.
 *
 * @package LPKS
 */

/**
 * Plugin Name:       Terraso Help
 * Plugin URI:        https://terraso.org/
 * Description:       Terraso Help CPT
 * Author:            Tech Matters, paulschreiber
 * Text Domain:       lpks
 * Domain Path:       /languages
 * Version:           1.0.0
 * Requires at least: 6.4
 *
 * @package         LPKS
 */

defined( 'ABSPATH' ) || exit;
define( 'TERRASO_HELP_PLUGIN_VERSION', '1.0.0' );

require_once __DIR__ . '/includes/class-terraso-help-cpt.php';
