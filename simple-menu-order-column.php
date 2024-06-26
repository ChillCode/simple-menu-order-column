<?php
/**
 * Simple Menu Order Column
 *
 * @package   Simple Menu Order Column
 * @author    Chillcode
 * @copyright Copyright (c) 2003-2024, Chillcode (https://github.com/chillcode/)
 * @license   GPLv3
 *
 * @wordpress-plugin
 * Plugin Name: Simple Menu Order Column
 * Plugin URI: https://github.com/chillcode/simple-menu-order-column
 * Description: Add a menu order column to your listings.
 * Version: 1.0.1
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: Chillcode
 * Author URI: https://github.com/chillcode/
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: simple-menu-order-column
 * Domain Path: /i18n/languages/
 */

defined( 'ABSPATH' ) || exit;

define( 'SMOC_PLUGIN_PATH', __DIR__ );
define( 'SMOC_PLUGIN_FILE', __FILE__ );
define( 'SMOC_PLUGIN_VERSION', '1.0.1' );

require_once SMOC_PLUGIN_PATH . '/includes/class-simplemenuordercolumn.php';

/**
 * Main Instance.
 *
 * Ensures only one instance is loaded or can be loaded.
 *
 * @since 1.0
 * @static
 * @return SMOC\SimpleMenuOrderColumn Main instance.
 */
function SMOC(): SMOC\SimpleMenuOrderColumn { //phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	return SMOC\SimpleMenuOrderColumn::instance();
}

/**
 * Initialize the plugin.
 */
SMOC();
