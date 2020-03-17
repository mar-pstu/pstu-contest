<?php


namespace pstu_contest;


/**
 * Стартовый файл регистрации плагина в WordPress
 *
 * @link              https://events.pstu.edu/konkurs-energy/
 * @since             2.0.0
 * @package           pstu-contest
 *
 * @wordpress-plugin
 * Plugin Name:       Конкурс «Енергетика» 
 * Plugin URI:        https://events.pstu.edu/konkurs-energy/
 * Description:       Плагин для учета работ II тура Всеукраинского конкурса студеньческих научных работ отрасли "Энергетика" 
 * Version:           2.0.0
 * Author:            chomovva
 * Author URI:        https://cct.pstu.edu/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       pstu_contest
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PSTU_CONTEST_VERSION', '2.0.0' );
define( 'PSTU_CONTEST_NAME', 'pstu_contest' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-plugin-name-activator.php
 */
function activate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-activator.php';
	pstu_contest_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-plugin-name-deactivator.php
 */
function deactivate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-deactivator.php';
	pstu_contest_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'pstu_contest\activate' );
register_deactivation_hook( __FILE__, 'pstu_contest\deactivate' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-manager.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run() {

	$plugin = new Manager();
	$plugin->run();

}
run();