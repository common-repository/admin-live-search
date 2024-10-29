<?php
/**
 *
 * @link              https://humbertosilva.com/
 * @since             1.0.0
 * @package           Admin_Live_Search
 *
 * @wordpress-plugin
 * Plugin Name:       Admin Live Search
 * Plugin URI:        https://humbertosilva.com/wp-plugins/admin-live-search/
 * Description:       Live search pages and posts in the dashboard / admin area as you type using the internal wordpress search via AJAX. Added filter to search by title only, content only or all (default).
 * Version:           3.2.1
 * Author:            Humberto Silva
 * Author URI:        https://humbertosilva.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       admin-live-search
 * Domain Path:       /languages
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'ADMIN_LIVE_SEARCH_VERSION', '3.2.1' );
define('ADMIN_LIVE_SEARCH_BASE', plugin_basename(__FILE__));

function activate_admin_live_search() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-admin-live-search-activator.php';
	Admin_Live_Search_Activator::activate();
}

function deactivate_admin_live_search() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-admin-live-search-deactivator.php';
	Admin_Live_Search_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_admin_live_search' );
register_deactivation_hook( __FILE__, 'deactivate_admin_live_search' );

require plugin_dir_path( __FILE__ ) . 'includes/class-admin-live-search.php';

function run_admin_live_search() {

	$plugin = new Admin_Live_Search();
	$plugin->run();

}
run_admin_live_search();
