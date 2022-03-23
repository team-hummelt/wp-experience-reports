<?php

namespace Experience\Reports;


use Exception;
use stdClass;
use Wp_Experience_Reports;

/**
 * The ADMIN Public API RESPONSE plugin class.
 *
 * @since      1.0.0
 * @package    Bs_Formular2
 * @subpackage Bs_Formular2/extensions
 * @author     Jens Wiecker <email@jenswiecker.de>
 */
class Experience_Reports_Public_API
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
     * @param string $command
     * @param string $api_url
     * @param array $addBody
     * @return object
     * @throws Exception
     */
    public function wwdh_get_public_resource_method(string $command, string $api_url = '', array $addBody = []): object
    {

        if (!get_option('extension_access_token')) {
            try {
                $this->wwdh_get_jwt_token();
            } catch (Exception $e){
                do_action('set_api_log','error', $e->getMessage());
            }
        }

        $apiResponse = new stdClass();
        $apiResponse->status = false;
        $api_url ? $url = $api_url : $url = get_option($this->basename . '-api-options')['public_api_resource_url'];
        $body = [
            'command' => $command,
            'basename' => $this->basename
        ];

        $body = wp_parse_args($body, $addBody);
        $args = $this->wwdh_post_args($body);

        $response = wp_remote_post($url, $args);

        if (is_wp_error($response)) {
            $apiResponse->msg = $response->get_error_message();
            exit();
        }

        if (is_array($response)) {
            $query = json_decode($response['body']);

            if (isset($query->error)) {
                try {
                    $this->wwdh_get_jwt_token();
                } catch (Exception $e){
                    do_action('set_api_log','error', $e->getMessage());
                }
            }
        }

            $response = wp_remote_post($url, $args);
            if (is_wp_error($response)) {
                $apiResponse->msg = $response->get_error_message();
                exit();
            }

            if (!is_array($response)) {
                throw new Exception('Invalid server response.');
            }

             $query = json_decode($response['body']);
            if (isset($query->error)) {
                isset($query->error_description) ? $apiResponse->error_description = $query->error_description : $apiResponse->error_description = '';
                $apiResponse->msg = $query->error;
                return $apiResponse;
            }
            if (!is_object($query)) {
                throw new Exception('Invalid server response.');
            }

            return $query;
    }


    /**
     * API JWT ACCESS TOKEN (BY id_rsa)
     * @throws Exception
     */
    private function wwdh_get_jwt_token()
    {
        $apiUrl = get_option($this->basename . '-api-options')['public_api_token_url'] . '/' . $this->main->get_id_rsa();
        $response = wp_remote_get($apiUrl);
        if (is_wp_error($response)) {
          //  $apiResponse->msg = $response->get_error_message();
            throw new Exception('WP-ERROR JWT Access-Token server response.');
        }
        if (is_array($response)) {
            $query = json_decode($response['body']);
            if (isset($query->error)) {
                throw new Exception('Invalid JWT Access-Token server response.');
            }
            if( !$query->access_token){
               throw new Exception('Invalid JWT Access-Token server response.');
            }
            update_option('extension_access_token', $query->access_token);
        }
    }

    private function wwdh_post_args($body = []): array
    {
        $bearerToken = get_option('extension_access_token');
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

    /**
     * @throws Exception
     */
    public function wwdh_api_public_download(string $url, array $body = [])
    {
        if (!get_option('extension_access_token') !== null) {
            try {
                $this->wwdh_get_jwt_token();
            } catch (Exception $e){
                do_action('set_api_log','error', $e->getMessage());
            }
        }

        $bearerToken = get_option('extension_access_token');
        $args = [
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

        $response = wp_remote_post($url, $args);
        if (is_wp_error($response)) {
            apply_filters('set_api_log','error', $response->get_error_message());
            exit();
        }
        if (!is_array($response)) {
            try {
                $this->wwdh_get_jwt_token();
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
     * @param $command
     * @param null $bearerToken
     * @param array $addBody
     * @return stdClass
     * @throws Exception
     */
    public function wwdh_support_api_post_resource($command, $bearerToken, array $addBody = []): stdClass
    {
        $body = [
            'command' => $command,
            'basename' => $this->basename,
        ];

        $body = wp_parse_args($body, $addBody);


        $apiResponse = new stdClass();
        $apiResponse->status = false;
        $args = [
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

        $url = get_option($this->basename . '-api-options')['public_api_support_url'];

        $response = wp_remote_post($url, $args);
        if (is_wp_error($response)) {
            $apiResponse->msg = $response->get_error_message();
            return $apiResponse;
        }

        if (is_array($response)) {
            $query = json_decode($response['body']);
            if (isset($query->error)) {
                isset($query->error_description) ? $apiResponse->error_description = $query->error_description : $apiResponse->error_description = '';
                $apiResponse->msg = $query->error;
                return $apiResponse;
            }
            if (!is_object($query)) {
                throw new Exception('Invalid server response.');
            }
            return $query;
        }

        return $apiResponse;
    }

    /**
     * API SUPPORT ACCESS TOKEN (BY Client_ID | Client_Secret) <-> client_credentials
     * @param string $public_id
     * @param string $public_secret
     * @return object
     */
    public function get_wwdh_public_support_api_token(string $public_id, string $public_secret): object
    {
        $apiResponse = new stdClass();
        $apiResponse->status = false;

        if (!$public_id || !$public_secret) {
            $apiResponse->msg = 'error API Client';
            return $apiResponse;
        }

        $authorization = base64_encode("$public_id:$public_secret");
        $args = array(
            'headers' => array(
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => "Basic {$authorization}"
            ),
            'body' => [
                'grant_type' => "client_credentials"
            ]
        );

        $response = wp_remote_post(get_option($this->basename . '-api-options')['public_api_token_url'], $args);
        if (is_wp_error($response)) {
            $apiResponse->msg = $response->get_error_message();
            return $apiResponse;
        }

        if (is_array($response)) {
            $query = json_decode($response['body']);
            if (isset($query->error)) {
                isset($query->error_description) ? $apiResponse->error_description = $query->error_description : $apiResponse->error_description = '';
                $apiResponse->msg = $query->error;
                return $apiResponse;
            }
            return $query;
        }
        return $apiResponse;
    }

    /**
     * @param int $get_id
     * @return object
     */
    public function wwdh_public_api_select_commands(int $get_id = 0): object
    {

        global $plugin_helper;
        $commands = [
            '0' => [
                'id' => 1,
                'bezeichnung' => 'Regenerate Public id_rsa',
                'command' => 'public_id_rsa'
            ],
            '1' => [
                'id' => 3,
                'bezeichnung' => 'Check Extension update',
                'command' => ''
            ],
            '2' => [
                'id' => 4,
                'bezeichnung' => 'Reset API Settings',
                'command' => ''
            ],
            '4' => [
                'id' => 2,
                'bezeichnung' => 'Enter command',
                'command' => ''
            ]

        ];

        if ($get_id) {
            foreach ($commands as $tmp) {
                if ($tmp['id'] == $get_id) {
                    return $plugin_helper->ERArrayToObject($tmp);
                }
            }
        }

        return $plugin_helper->ERArrayToObject($commands);
    }

    /**
     * @param string $select
     * @return array
     */
    public function wwdh_ajax_language(string $select): array
    {
        $return = [];
        switch ($select) {
            case 'public_command':
                $return = [
                    'headline' => __('Public API command execute', 'wp-experience-reports'),
                    'card_header' => __('Public API command', 'wp-experience-reports'),
                    'input_id' => __('Support ID', 'wp-experience-reports'),
                    'input_secret' => __('Support Password', 'wp-experience-reports'),
                    'select_execute' => __('Execute command', 'wp-experience-reports'),
                    'button' => __('execute', 'wp-experience-reports'),
                    'cancel' => __('cancel', 'wp-experience-reports'),
                    'btn_reset' => __('Reset Public API Settings', 'wp-experience-reports')

                ];
                break;
            case'alert_msg':
                $return = [
                    //Ein Fehler ist aufgetreten. Alle Eingabefelder müssen ausgefüllt sein.
                    'err_input_fields' => __('An error has occurred. <b>All input fields</b> must be filled in.', 'wp-experience-reports'),
                    //Ein Fehler ist aufgetreten. Es kann keine Verbindung zum Server hergestellt werden.
                    'err_no_connect' => __('An error has occurred. No connection to the server can be established.', 'wp-experience-reports'),
                    //Ein Fehler ist aufgetreten. Ungültige Serverantwort.
                    'err_no_return' => __('An error has occurred. Invalid server response.', 'wp-experience-reports'),
                    //Server-Antwort
                    'server_response' => __('Server Response', 'wp-experience-reports'),
                    //Es ist ein Fehler aufgetreten.
                    'ajax_error' =>  __('An error has occurred.', 'wp-experience-reports'),
                    //Falsches Lizenz-Format. Lizenz ist ungültig!
                    'license_length_error' =>  __('Wrong licence format. Licence is invalid!', 'wp-experience-reports'),
                ];
                break;
            case 'success_msg':
                //Das Kommando wurde erfolgreich ausgeführt.
                $return = [
                    'success_return' => __('The command was executed successfully.', 'wp-experience-reports'),
                    //Aktion erfolgreich ausgeführt.
                    'success_action' => __('Action successfully executed.', 'wp-experience-reports'),
                ];
                break;
        }

        return $return;
    }


}