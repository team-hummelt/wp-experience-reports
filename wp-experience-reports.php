<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://wwdh.de
 * @since             1.0.0
 * @package           Wp_Experience_Reports
 *
 * @wordpress-plugin
 * Plugin Name:       Experience Reports
 * Plugin URI:        https://wwdh.de
 * Description:       Write Experience Reports and insert the reports in pages or posts. With the Experience Reports Gutenberg plugin you have countless setting options and can place the reports anywhere you want.
 * Version:           1.0.0
 * Author:            Jens Wiecker
 * Author URI:        https://wwdh.de
 * License:           GPL3
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       wp-experience-reports
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

/**
 * Currently DATABASE VERSION
 * @since             1.0.0
 */
const WP_EXPERIENCE_REPORTS_DB_VERSION = '1.0.1';

/**
 * Currently Beitrags-Slider ID
 * @since             1.0.0
 */
const WP_EXPERIENCE_REPORTS_SLIDER_ID = '3W1S49XHI76';

/**
 * Currently Shortcode ID
 * @since             1.0.0
 */
const WP_EXPERIENCE_SHORTCODE_ID = '4d9ec351b5d1f696';

/**
 * MIN PHP VERSION for Activate
 * @since             1.0.0
 */
const WP_EXPERIENCE_REPORTS_PHP_VERSION = '7.4';

/**
 * MIN WordPress VERSION for Activate
 * @since             1.0.0
 */
const WP_EXPERIENCE_REPORTS_WP_VERSION = '5.6';


/**
 * PLUGIN ROOT PATH
 * @since             1.0.0
 */
define('WP_EXPERIENCE_REPORTS_PLUGIN_DIR', dirname(__FILE__));

/**
 * PLUGIN SLUG
 * @since             1.0.0
 */
define('WP_EXPERIENCE_REPORTS_SLUG_PATH', plugin_basename(__FILE__));
define('WP_EXPERIENCE_REPORTS_BASENAME', plugin_basename(__DIR__));

/**
 * Extension Update Check Time
 * @since             1.0.0
 */
const WP_EXPERIENCE_REPORTS_UPDATE_EXTENSION_TIME = 43200;

/**
 * PLUGIN API DIR
 * @since             1.0.0
 */
define('WP_EXPERIENCE_REPORTS_API_DIR', dirname(__FILE__). DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'api' . DIRECTORY_SEPARATOR);

/**
 * PLUGIN EXTENSION API DIR
 * @since             1.0.0
 */
define('WP_EXPERIENCE_REPORTS_EXTENSION_DIR',  plugin_dir_path(__FILE__) . 'extensions' . DIRECTORY_SEPARATOR);


/**
 * PLUGIN PREVIEW EXTENSION PREVIEW DIR
 * @since             1.0.0
 */
const WP_EXPERIENCE_REPORTS_EXTENSION_PREVIEW_DIR = WP_EXPERIENCE_REPORTS_EXTENSION_DIR . 'preview' . DIRECTORY_SEPARATOR;


/**
 * PLUGIN PREVIEW EXTENSION INSTALLED DIR
 * @since             1.0.0
 */
const WP_EXPERIENCE_REPORTS_EXTENSION_INSTALL_DIR = WP_EXPERIENCE_REPORTS_EXTENSION_DIR . 'installed' . DIRECTORY_SEPARATOR;


/**
 * PLUGIN EXTENSION PREVIEW URL
 * @since             1.0.0
 */
define('WP_EXPERIENCE_REPORTS_EXTENSION_PREVIEW_URL',  plugins_url(WP_EXPERIENCE_REPORTS_BASENAME) . '/extensions/preview/' );

/**
 * PLUGIN ID_RSA DIR
 * @since             1.0.0
 */
const WP_EXPERIENCE_REPORTS_ID_RSA_DIR = WP_EXPERIENCE_REPORTS_API_DIR . 'id_rsa' . DIRECTORY_SEPARATOR;

/**
 * PLUGIN ADMIN DIR
 * @since             1.0.0
 */
define('WP_EXPERIENCE_REPORTS_PLUGIN_ADMIN_DIR', dirname(__FILE__). DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR);

/**
 * PLUGIN Gutenberg DIR
 * @since             1.0.0
 */
const WP_EXPERIENCE_REPORTS_GUTENBERG_DIR = WP_EXPERIENCE_REPORTS_PLUGIN_ADMIN_DIR . 'gutenberg' . DIRECTORY_SEPARATOR ;


/**
 * PLUGIN Gutenberg URL
 * @since             1.0.0
 */
define('WP_EXPERIENCE_REPORTS_GUTENBERG_URL',  plugins_url(WP_EXPERIENCE_REPORTS_BASENAME) . '/gutenberg/' );


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wp-experience-reports-activator.php
 */
function activate_wp_experience_reports() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-experience-reports-activator.php';
    Wp_Experience_Reports_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wp-experience-reports-deactivator.php
 */
function deactivate_wp_experience_reports() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-experience-reports-deactivator.php';
    Wp_Experience_Reports_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wp_experience_reports' );
register_deactivation_hook( __FILE__, 'deactivate_wp_experience_reports' );

require_once 'extensions/class-wp-experience-reports-extensions-installed.php';
$experienceReportsExtensionsInstalled = Experience_Reports_Extensions_Installed::instance();
$experienceReportsExtensionsInstalled->experience_reports_installed_extensions();

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wp-experience-reports.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */

global $wp_experience_reports_plugin;
$wp_experience_reports_plugin = new Wp_Experience_Reports();
$wp_experience_reports_plugin->run();


