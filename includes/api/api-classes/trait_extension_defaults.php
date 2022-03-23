<?php

namespace Experience\Reports;
defined('ABSPATH') or die();

/**
 * The ADMIN Default Settings Trait.
 *
 * @since      1.0.0
 * @package    Experience_Reports
 * @subpackage Experience_Reports/extensions
 * @author     Jens Wiecker <email@jenswiecker.de>
 */
trait Trait_Extension_Defaults
{
    //DATABASE TABLES
    protected string $table_experience_reports_extensions = 'experience_reports_extensions';
    //SETTINGS DEFAULT OBJECT
    protected array $extension_default_values;
    //API Options
    protected string $api_url = 'https://start.hu-ku.com/theme-update/api/v2/';
    protected string $public_api_token_uri = 'public/token';
    protected string $public_api_support_uri = 'public';
    protected string $public_api_public_resource_uri = 'public/resource';
    protected string $public_api_public_preview_uri = 'public/preview';
    protected string $kunden_login_url = 'https://start.hu-ku.com/theme-update/kunden-web';

    // Get activate Token
    protected string $extension_api_activate_uri = 'jwt/extension/license/activate/';
    //Resource Token
    protected string $extension_api_id_rsa_token = 'jwt/extension/license/token/';
    // License Resource URI
    protected string $extension_api_resource_uri = 'jwt/extension/license/resource';
    protected string $extension_api_extension_download = 'jwt/extension/download';

    /**
     * @param string $args
     * @return array
     */
    protected function get_theme_default_settings(string $args = ''): array
    {
        $this->extension_default_values = [
            'api_settings' => [
                'api_url' => $this->api_url,
                'public_api_token_url' => $this->api_url . $this->public_api_token_uri,
                'public_api_support_url' => $this->api_url . $this->public_api_support_uri,
                'public_api_resource_url' => $this->api_url . $this->public_api_public_resource_uri,
                'public_api_preview_url' => $this->api_url . $this->public_api_public_preview_uri,
                //Kunden Login
                'kunden_login_url' => $this->kunden_login_url,
                'extension_api_activate_url' => $this->api_url . $this->extension_api_activate_uri,
                // ID_RSA Resource Token
                'extension_api_id_rsa_token' => $this->api_url . $this->extension_api_id_rsa_token,
                //Resource
                'extension_api_resource_url' => $this->api_url . $this->extension_api_resource_uri,
                //Download
                'extension_api_extension_download' => $this->api_url . $this->extension_api_extension_download,
            ],
            'extension_preview_language' => [
                'extension' => __('Extension', 'wp-experience-reports'),
                'plugin_for' => __('Plugin for', 'wp-experience-reports'),
                'status' => __('Status', 'wp-experience-reports'),
                'details' => __('details', 'wp-experience-reports'),
                'download' => __('download', 'wp-experience-reports'),
                'activate' => __('activate', 'wp-experience-reports'),
                'Activates' => __('Activates', 'wp-experience-reports'),
                'activates' => __('activates', 'wp-experience-reports'),
                'deactivated' => __('deactivated', 'wp-experience-reports'),
                'licence' => __('license', 'wp-experience-reports'),
                'license_details' => __('Licence details', 'wp-experience-reports'),
                'back_btn' => __('back to the overview', 'wp-experience-reports'),
                'Licence_for' => __('Licence for', 'wp-experience-reports'),
                'Licence' => __('Licence', 'wp-experience-reports'),
                'activation_code' => __('Activation-code', 'wp-experience-reports'),
                'copies' => __('copies', 'wp-experience-reports'),
                'copy' => __('copy', 'wp-experience-reports'),
                'time_limit' => __('Time limit', 'wp-experience-reports'),
                'url_limit' => __('Url-Limit', 'wp-experience-reports'),
                'licence_start' => __('Licence start', 'wp-experience-reports'),
                'licence_end' => __('Licence end', 'wp-experience-reports'),
                'version' => __('Version', 'wp-experience-reports'),
                'extension_for' => __('Extension for', 'wp-experience-reports'),
                'file_size' => __('File size', 'wp-experience-reports'),
                'type' => __('Type', 'wp-experience-reports'),
                'php_min' => __('PHP Min', 'wp-experience-reports'),
                'wp_min' => __('WP Min', 'wp-experience-reports'),
                'month' => __('Month', 'wp-experience-reports'),
                'months' => __('Months', 'wp-experience-reports'),
                'at' => __('at', 'wp-experience-reports'),
                'clock' => __('Clock', 'wp-experience-reports'),
                'unlimited' => __('unlimited', 'wp-experience-reports'),
                'installations' => __('Installations', 'wp-experience-reports'),
                'installation' => __('Installation', 'wp-experience-reports'),
                'url_licence' => __('URL Licence', 'wp-experience-reports'),
                'not_activated' => __('not activated', 'wp-experience-reports'),

            ],
        ];

        if ($args) {
            return $this->extension_default_values[$args];
        } else {
            return $this->extension_default_values;
        }
    }

}