<?php

namespace Experience\Reports;


use Exception;
use stdClass;
use Wp_Experience_Reports;

/**
 * The ADMIN Public API RESPONSE plugin class.
 *
 * @since      1.0.0
 * @package    Experience_Reports
 * @subpackage Experience_Reports/extensions
 * @author     Jens Wiecker <email@jenswiecker.de>
 */
class WWDH_Extension_API
{

    /**
     * TRAIT of Default Settings.
     *
     * @since    1.0.0
     */
    use Trait_Extension_Defaults;

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $basename The ID of this plugin.
     */
    private string $basename;

    /**
     * Store plugin main class to allow public access.
     *
     * @since    1.0.0
     * @access   private
     * @var Wp_Experience_Reports $main The main class.
     */
    private Wp_Experience_Reports $main;

    /**
     * The Version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current Version of this plugin.
     */
    private string $version;

    /**
     * @param string $version
     * @param string $basename
     * @param Wp_Experience_Reports $main
     */
    public function __construct(string $version, string $basename, Wp_Experience_Reports $main)
    {
        global $plugin_helper;
        $this->version = $version;
        $this->basename = $basename;
        $this->main = $main;
        if (!get_option($this->basename . '-api-options')) {
            $plugin_helper->experience_reports_set_default_options();
        }
    }

    /**
     * @throws Exception
     */
    public function wwdh_extension_activate($license, $extension)
    {
        $apiResponse = new stdClass();
        $apiResponse->status = false;
        if (!$license) {
            throw new Exception(__('License ID is empty.', 'wp-experience-reports'));
        }
        $url = get_option($this->basename . '-api-options')['extension_api_activate_url'] . $license;

        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
            throw new Exception($response->get_error_message());
        }

        if (!is_array($response)) {
            throw new Exception(__('Invalid server response.', 'wp-experience-reports'));
        }
        $query = json_decode($response['body']);

        if (!is_object($query) || isset($query->error)) {
            isset($query->error_description) ? $apiResponse->error_description = $query->error_description : $apiResponse->error_description = '';
            $apiResponse->msg = $query->error;
            return $apiResponse;
        }

        try {
          $licenseData =  $this->wwdh_get_id_rsa_activate_token($query, $extension);
        } catch (Exception $e) {
            $apiResponse->msg = $e->getMessage();

            return $apiResponse;
        }

        return $licenseData;
    }

    /**
     * @throws Exception
     */
    private function wwdh_get_id_rsa_activate_token($record, $extension = '')
    {
        global $extensionOptions;
        if (!isset($record->token_url) || !$record->status) {
            $extensionOptions->wwdh_set_api_log('error', 'activate token url is empty!');
            exit();
        }

        $args = [
            'license_url' => site_url(),
            'extension' => $extension
        ];
        $body = $this->wwdh_extension_post_body($args);
        $response = wp_remote_post($record->token_url, $body);

        if (is_wp_error($response)) {
            $extensionOptions->wwdh_set_api_log('error', $response->get_error_message());
            throw new Exception($response->get_error_message());
        }

        if (!is_array($response)) {
            $extensionOptions->wwdh_set_api_log('error', 'activate Response is empty!');
            throw new Exception('activate Response is empty!');
        }
        $query = json_decode($response['body']);
        if (!is_object($query) || isset($query->error)) {
            if(isset($query->error )){
                $errMsg = $query->error_description;
            } else {
                $errMsg = 'activate Response is empty!';
            }
            $extensionOptions->wwdh_set_api_log('error', $errMsg);
            throw new Exception($errMsg);
        }
        if (!$query->access_token) {
            $extensionOptions->wwdh_set_api_log('error', 'activate access token is empty!');
            throw new Exception('activate access token is empty!');
        }

        $body = [
            'get_data' => 'get_license_data',
            'license_url' => get_site_url()
        ];

        try {
            $licenseData = $this->wwdh_get_api_post_resource($body, '', $query->access_token);

        } catch (Exception $e){
            $extensionOptions->wwdh_set_api_log('error', 'activate response data is empty!');
            do_action('set_api_log', 'error', 'activate response data is empty!');
            exit();
        }

        $args = sprintf('WHERE license="%s"', $licenseData->license);
        $dbExtension = apply_filters($this->basename.'_get_extension', $args, false);
        $record = new stdClass();
        $record->license = $licenseData->license;
        $record->id_rsa = $licenseData->id_rsa;
        $record->folder = $licenseData->extension_filename;
        $record->aktiv = 1;
        if($licenseData->url_limit){
            if($licenseData->url_license_data){
                $record->url_activated = $licenseData->url_license_data->activated;
                $record->url_id = $licenseData->url_license_data->url_id;
            } else {
                $record->url_activated = 0;
            }
            $record->url_limit_aktiv = 1;
        } else {
            $record->url_limit_aktiv = 0;
            $record->url_activated = 1;
            $record->url_id = '';
        }
        if(!$dbExtension->status){
            do_action($this->basename.'_set_extension', $record);
        } else {
            $record->errors = 0;
            $record->last_connect = current_time('mysql');
            do_action($this->basename.'_update_extension_error', $record);
        }
        return $licenseData;
    }

    /**
     * @throws Exception
     */
    public function wwdh_get_api_post_resource($body = [], $license = '', $access_token = '')
    {
        global $extensionOptions;
        $apiResponse = new stdClass();
        $apiResponse->status = false;
        if($access_token){
            update_option('license_access_token', $access_token);
        }

        if (!get_option('license_access_token')) {
            try {
                $this->wwdh_licence_jwt_token_controller($license);
            } catch (Exception $e) {
                $extensionOptions->wwdh_set_api_log('error', $e->getMessage());
                $apiResponse->msg = $e->getMessage();
                return $apiResponse;
            }
        }

        $url = get_option($this->basename . '-api-options')['extension_api_resource_url'];
        $args = $this->wwdh_extension_post_args($body);

        $response = wp_remote_post($url, $args);


        if (is_wp_error($response)) {
            $extensionOptions->wwdh_set_api_log('error', $response->get_error_message());
            throw new Exception($response->get_error_message());
        }

        if (!is_array($response)) {
            $extensionOptions->wwdh_set_api_log('error', 'server data is empty!');
            throw new Exception('server data is empty!');
        }

        $query = json_decode($response['body']);
        if (!is_object($query) || isset($query->error)) {
            $this->wwdh_licence_jwt_token_controller($license);
        }

        $response = wp_remote_post($url, $args);
        if (is_wp_error($response)) {
            $extensionOptions->wwdh_set_api_log('error', 'server data is empty!');
            throw new Exception($response->get_error_message());
        }

        if (!is_array($response)) {
            $extensionOptions->wwdh_set_api_log('error', 'server data is empty!');
            throw new Exception('Invalid server response.');
        }

        $query = json_decode($response['body']);

        if (!is_object($query) || isset($query->error)) {
            isset($query->error_description) ? $apiResponse->error_description = $query->error_description : $apiResponse->error_description = '';
            $extensionOptions->wwdh_set_api_log('error', $apiResponse->error_description);
            $apiResponse->msg = $query->error;
            return $apiResponse;
        }
        return $query;
    }

    /**
     * @throws Exception
     */
    public function wwdh_api_extension_download(string $url, array $body, $license)
    {
        global $extensionOptions;
        $apiResponse = new stdClass();
        $apiResponse->status = false;

        if (!get_option('license_access_token')) {
            try {
                $this->wwdh_licence_jwt_token_controller($license);
            } catch (Exception $e) {
                $extensionOptions->wwdh_set_api_log('error', $e->getMessage());
                $apiResponse->msg = $e->getMessage();
                return $apiResponse;
            }
        }

        $args = $this->wwdh_extension_post_args($body);
        $response = wp_remote_post($url, $args);
        if (is_wp_error($response)) {
            apply_filters('set_api_log','error', $response->get_error_message());
            exit();
        }
        if (!is_array($response)) {
            try {
                $this->wwdh_licence_jwt_token_controller($license);
            } catch (Exception $e){
                do_action('set_api_log','error', $e->getMessage());
            }
        }

        $response = wp_remote_post($url, $args);
        if (is_wp_error($response)) {
            apply_filters('set_api_log','error', $response->get_error_message());
            exit();
        }

        if (!is_array($response)) {
            throw new Exception('Invalid server response (Token Error).');
        }
        return $response['body'];
    }

    /**
     * API JWT ACCESS TOKEN (BY id_rsa)
     * @throws Exception
     */
    private function wwdh_licence_jwt_token_controller($license = '')
    {

        $args = sprintf('WHERE license="%s"', $license);
        $dbExtension = apply_filters($this->basename.'_get_extension', $args, false);

        $exception = new stdClass();
        if (!$dbExtension->status) {
            $exception->status = false;
            $exception->license_delete = true;
            $exception->msg = 'ID RSA not found';
            throw new Exception(json_encode($exception));
        }

        $id_rsa = $dbExtension->record->id_rsa;
        $license ? $uri = $license . '/' . $id_rsa : $uri = $id_rsa;
        $apiUrl = get_option($this->basename . '-api-options')['extension_api_id_rsa_token'] . $uri;
        $response = wp_remote_get($apiUrl);
        if (is_wp_error($response)) {
            throw new Exception('WP-ERROR JWT Access-Token server response.');
        }
        if (is_array($response)) {
            $query = json_decode($response['body']);
            if (isset($query->error)) {
                isset($query->error_description) ? $msg = $query->error_description : $msg = 'Invalid JWT Access-Token server response.';
                throw new Exception($msg);
            }
            if (!$query->access_token) {
                isset($query->error) ? $msg = $query->error_description : $msg = 'Invalid JWT Access-Token server response.';
                throw new Exception($msg);
            }
            update_option('license_access_token', $query->access_token);
        }
    }

    private function wwdh_extension_post_args($body = []): array
    {
        $bearerToken = get_option('license_access_token');
        return [
            'method' => 'POST',
            'timeout' => 45,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking' => true,
            'sslverify' => true,
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => "Bearer $bearerToken"
            ],
            'body' => $body
        ];
    }

    private function wwdh_extension_post_body($body = []): array
    {
        return [
            'method' => 'POST',
            'timeout' => 45,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking' => true,
            'sslverify' => true,
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body' => $body
        ];
    }

}