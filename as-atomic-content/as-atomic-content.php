<?php
/**
 * Plugin Name: AS Atomic Content
 * Description: Reusable cards, rows, and patterns managed with Advanced Custom Fields.
 * Version: 0.1.1
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: AlphaSys
 * Author URI: https://alphasys.com.au
 * Update URI: https://github.com/cchatterton/as-atomic-content
 * AlphaSys Controller API: 1
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: as-atomic-content
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ASAC_VERSION', '0.1.1' );
define( 'ASAC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ASAC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once ASAC_PLUGIN_DIR . 'functions/setup.php';
require_once ASAC_PLUGIN_DIR . 'functions/admin.php';
require_once ASAC_PLUGIN_DIR . 'functions/controller-client.php';

asuc_client_register( __FILE__, 'as-atomic-content' );
