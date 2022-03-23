<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @link       https://wwdh.de
 * @since      1.0.0
 *
 * @package    Wp_Experience_Reports
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
delete_option( "wp-experience-reports_show_activated_page");
delete_option("wp-experience-reports_message");
delete_option("wp-experience-reports_product_install_authorize");
delete_option("wp-experience-reports_client_id");
delete_option("wp-experience-reports_client_secret");
delete_option("wp-experience-reports_license_url");
delete_option('wp-experience-reports_user_role');
delete_option("wp-experience-reports_install_time");
delete_option("wp-experience-reports_server_api");
delete_option('jal_experience_reports_db_version');
delete_option('extension_access_token');
delete_option('wp-experience-reports-api-options');
delete_option('wp-experience-reports/wwdh_extension_check');
delete_option('experience_reports_user_role');

delete_transient("wp-experience-reports-admin-notice-error-panel-" . get_current_user_id());
delete_transient("wp-experience-reports-admin-notice-success-panel-" . get_current_user_id());



global $wpdb;
$table_name = $wpdb->prefix . 'erg_two_slide';
$sql = "DROP TABLE IF EXISTS $table_name";
$wpdb->query($sql);

$table_name = $wpdb->prefix . 'erg_two_galerie';
$sql = "DROP TABLE IF EXISTS $table_name";
$wpdb->query($sql);

$table_name = $wpdb->prefix . 'erg_two_galerie_images';
$sql = "DROP TABLE IF EXISTS $table_name";
$wpdb->query($sql);

$table_name = $wpdb->prefix . 'experience_reports_extensions';
$sql = "DROP TABLE IF EXISTS $table_name";
$wpdb->query($sql);