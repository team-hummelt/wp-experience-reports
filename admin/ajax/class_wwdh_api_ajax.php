<?php

namespace Experience\Reports;
defined('ABSPATH') or die();


use Exception;
use stdClass;
use Throwable;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;
use Wp_Experience_Reports;

/**
 * The ADMIN AJAX RESPONSE plugin class.
 *
 * @since      1.0.0
 * @package    Experience_Report
 * @subpackage Experience_Report/includes
 * @author     Jens Wiecker <email@jenswiecker.de>
 */
final class WWDH_Api_Ajax
{

    private static $instance;
    /**
     * The AJAX METHOD
     *
     * @since    1.0.0
     * @access   private
     * @var      string $method The AJAX METHOD.
     */
    protected string $method;

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
     * TWIG for PHP-Template-Engine
     *
     * @since    1.0.0
     * @access   private
     * @var Environment $twig PHP-Template-Engine.
     */
    private Environment $twig;

    /**
     * The Version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current Version of this plugin.
     */
    private string $version;

    /**
     * The AJAX DATA
     *
     * @since    1.0.0
     * @access   private
     * @var      array|object $data The AJAX DATA.
     */
    private $data;

    /**
     * @param string $version
     * @param string $basename
     * @param Wp_Experience_Reports $main
     * @param Environment $twig
     */
    public function __construct(string $version, string $basename, Wp_Experience_Reports $main, Environment $twig)
    {

        $this->version = $version;
        $this->basename = $basename;
        $this->main = $main;
        $this->twig = $twig;
        $this->method = '';
        if (isset($_POST['daten'])) {
            $this->data = $_POST['daten'];
            $this->method = filter_var($this->data['method'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
        }

        if (!$this->method) {
            $this->method = $_POST['method'];
        }
    }

    /**
     * @param string $version
     * @param string $basename
     * @param Wp_Experience_Reports $main
     * @param Environment $twig
     * @return static
     */
    public static function instance(string $version, string $basename, Wp_Experience_Reports $main, Environment $twig): self
    {
        if (is_null(self::$instance)) {
            self::$instance = new self($version, $basename, $main, $twig);
        }
        return self::$instance;
    }

    /**
     * ADMIN AJAX RESPONSE.
     * @return stdClass
     * @since    1.0.0
     */
    public function wwdh_api_ajax_handle(): stdClass
    {
        global $extensionOptions;
        $record = new stdClass();
        $errMsg = apply_filters($this->basename . '/get_ajax_language', 'alert_msg');
        $successMsg = apply_filters($this->basename . '/get_ajax_language', 'success_msg');
        $responseJson = new stdClass();
        $responseJson->status = false;

        switch ($this->method) {
            case'get_public_api_commands_select':
                $comSelect = apply_filters('get_public_api_select_commands', false);
                $responseJson->type = filter_input(INPUT_POST, 'type_response', FILTER_SANITIZE_STRING);
                $retArr = [];
                if ($comSelect) {
                    foreach ($comSelect as $tmp) {
                        unset($tmp->command);
                        $retArr[] = $tmp;
                    }
                }
                if (!get_option($this->basename . '/wwdh_extension_check')) {
                    update_option($this->basename . '/wwdh_extension_check', current_time('timestamp'));
                }

                $time = get_option($this->basename . '/wwdh_extension_check') + WP_EXPERIENCE_REPORTS_UPDATE_EXTENSION_TIME;

                $nextTime = date('d.m.Y \o\n H:i', $time);
                $data = [
                    'time' => $nextTime,
                    'select' => $retArr,
                    'lang' => apply_filters($this->basename . '/get_ajax_language', 'public_command')
                ];

                $path = WP_EXPERIENCE_REPORTS_PLUGIN_ADMIN_DIR . 'js' . DIRECTORY_SEPARATOR . 'templates';
                $twig_loader = new FilesystemLoader($path);
                $twig = new Environment($twig_loader);

                try {
                    $temp = $twig->render('public-api-settings.twig', ['data' => $data]);
                } catch (LoaderError|RuntimeError|SyntaxError $e) {
                    $responseJson->msg = $e->getMessage();
                    return $responseJson;
                }

                $responseJson->template = preg_replace(array('/<!--(.*)-->/Uis', "/[[:blank:]]+/"), array('', ' '), str_replace(array("\n", "\r", "\t"), '', $temp));
                $responseJson->status = true;
                break;

            case'get_public_api_commands':
                $command = filter_input(INPUT_POST, 'command', FILTER_SANITIZE_NUMBER_INT);
                $public_client = filter_input(INPUT_POST, 'public_client', FILTER_SANITIZE_STRING);
                $public_secret = filter_input(INPUT_POST, 'public_secret', FILTER_SANITIZE_STRING);

                if (!$command || !$public_client || !$public_secret) {
                    $responseJson->msg = $errMsg['err_input_fields'];
                    return $responseJson;
                }

                $sendCommand = apply_filters('get_public_api_select_commands', (int)$command);
                $body = [];
                switch ($command) {
                    case '1':
                        if (isset($sendCommand->{0})) {
                            $responseJson->msg = $errMsg['err_no_connect'];
                            return $responseJson;
                        }
                        break;
                    case'2':
                        $extra_command = filter_input(INPUT_POST, 'extra_command', FILTER_SANITIZE_STRING);
                        if (!$extra_command) {
                            $responseJson->msg = $errMsg['err_input_fields'];
                            return $responseJson;
                        }
                        $sendCommand->command = $extra_command;
                        break;
                    case '3':
                        do_action($this->basename . '/check_extension_preview_updates');
                        update_option($this->basename . '/wwdh_extension_check', current_time('timestamp'));
                        $responseJson->msg = $successMsg['success_return'];
                        $responseJson->status = true;
                        return $responseJson;
                    case'4':
                        $extensionOptions->reset_public_api_settings();
                        $responseJson->status = true;
                        $responseJson->msg = $successMsg['success_action'];
                        return $responseJson;
                }

                $accessToken = apply_filters('get_public_support_api_token', $public_client, $public_secret);
                if (!isset($accessToken->access_token)) {
                    if ($accessToken->error_description) {
                        $responseJson->msg = '<b>' . $errMsg['server_response'] . ':</b> ' . $accessToken->error_description;
                    } else {
                        $responseJson->msg = $errMsg['err_no_connect'];
                    }
                    return $responseJson;
                }

                $resource = apply_filters('wwdh_support_api_resource', $sendCommand->command, $accessToken->access_token, $body);

                switch ($command) {
                    case '1':
                        if (!$resource->status) {
                            if ($resource->error_description) {
                                $responseJson->msg = '<b>' . $errMsg['server_response'] . ':</b> ' . $resource->error_description;
                            } else {
                                $responseJson->msg = $errMsg['err_no_connect'];
                            }
                            return $responseJson;
                        }

                        if (isset($resource->data) && $resource->data) {
                            $idRsaDir = WP_EXPERIENCE_REPORTS_ID_RSA_DIR . 'public_id_rsa';
                            $id_rsa = base64_decode($resource->data);
                            file_put_contents($idRsaDir, $id_rsa);
                        } else {
                            $responseJson->msg = $errMsg['err_no_return'];
                        }
                        break;

                }

                $responseJson->msg = $successMsg['success_return'];
                $responseJson->status = true;
                break;
            case'reset_public_api_settings':
                $extensionOptions->reset_public_api_settings();
                $responseJson->status = true;
                $responseJson->msg = $successMsg['success_action'];
                break;

            case 'load_extension_preview_data':
                $template = filter_input(INPUT_POST, 'template', FILTER_SANITIZE_STRING);
                $responseJson->target = filter_input(INPUT_POST, 'target', FILTER_SANITIZE_STRING);
                $extDir = $this->main->get_extension_preview();
                $extension = $extensionOptions->read_wwdh_folder($extDir);

                $dbError = 0;
                $err_msg = '';
                if (!$extension) {
                    do_action($this->basename.'/download_extension_previews');
                    $extDir = $this->main->get_extension_preview();
                    $extension = $extensionOptions->read_wwdh_folder($extDir);
                    if(!$extension){
                        return $responseJson;
                    }
                }

                $retArr = [];
                foreach ($extension as $tmp) {
                    $url = $extensionOptions->get_extension_previews_lang_url($tmp);

                    $dataUrl = $extensionOptions->wwdh_get_extension_preview_url_data($tmp);

                    if (!$dataUrl) {
                        $responseJson->status = $errMsg['err_no_return'];
                        return $responseJson;
                    }

                    $data = json_decode(file_get_contents($dataUrl));
                    $lang = json_decode(file_get_contents($url));
                    $sysLang = $this->get_theme_default_settings('extension_preview_language');

                    $extFrom = str_replace([', ', ' '], ',', $data->extension_from);
                    $extFrom = explode(',', $extFrom);
                    $from = strtoupper(implode(' | ', $extFrom));

                    $args = sprintf('WHERE folder="%s"', $tmp);
                    $dbExtension = apply_filters($this->basename . '_get_extension', $args, false);
                    if ($dbExtension->status) {
                        $dbExtension = $dbExtension->record;
                        $apiLicense = $dbExtension->license;
                    } else {
                        $apiLicense = '';
                    }

                    $ret_item = [
                        'activated' => $dbExtension->aktiv,
                        'url_limit_aktiv' => $dbExtension->url_limit_aktiv,
                        'url_id' => $dbExtension->url_id,
                        'err_msg' => '',
                        'license' => $apiLicense,
                        'extension' => $tmp,
                        'lang' => $lang,
                        'sys_lang' => $sysLang,
                        'data' => [
                            'extension_from' => $from,
                            'extension_version' => $data->extension_version,
                            'extension_logo' => WP_EXPERIENCE_REPORTS_EXTENSION_PREVIEW_URL . $tmp . '/' . $data->extension_logo
                        ]
                    ];
                    $retArr[] = $ret_item;
                }

                try {
                    $template = $this->twig->render($template . '.twig', ['data' => $retArr]);
                    $responseJson->template = preg_replace(array('/<!--(.*)-->/Uis', "/[[:blank:]]+/"), array('', ' '), str_replace(array("\n", "\r", "\t"), '', $template));
                } catch (LoaderError|SyntaxError|RuntimeError $e) {
                    $responseJson->msg = $e->getMessage();
                } catch (Throwable $e) {
                    $responseJson->msg = $e->getMessage();
                }
                $responseJson->status = true;
                break;
            case'load_twig_template':
                $template = filter_input(INPUT_POST, 'template', FILTER_SANITIZE_STRING);
                $extension = filter_input(INPUT_POST, 'extension', FILTER_SANITIZE_STRING);
                $responseJson->target = filter_input(INPUT_POST, 'target', FILTER_SANITIZE_STRING);
                if (!$responseJson->target) {
                    $responseJson->msg = $errMsg['ajax_error'];
                    return $responseJson;
                }
                if (!$template || !$extension) {
                    $responseJson->msg = $errMsg['ajax_error'];
                    return $responseJson;
                }

                $fileData = $extensionOptions->get_wwdh_extension_description_template($extension, $template . '.twig');
                if (!$fileData->status) {
                    $responseJson->msg = $errMsg['ajax_error'];
                    return $responseJson;
                }

                $descriptionJson = $fileData->description_url . 'description.json';
                $json = json_decode(file_get_contents($descriptionJson));
                $json->images ? $images = $json->images : $images = [];

                $args = sprintf('WHERE folder="%s"', $extension);
                $dbExtension = apply_filters($this->basename . '_get_extension', $args, false);
                if ($dbExtension->status) {
                    $dbExtension = $dbExtension->record;
                    $activated = $dbExtension->aktiv;
                    $apiLicense = $dbExtension->license;
                } else {
                    $activated = false;
                    $apiLicense = '';
                    $dbExtension = false;
                }

                $data = [
                    'activated' => $activated,
                    'license' => $apiLicense,
                    'extension' => $extension,
                    'extension_url' => $fileData->description_url,
                    'images' => $images,
                    'dbData' => $dbExtension,
                    'loading_url' => plugins_url($this->basename) . '/admin/images/Spinning arrows.gif'
                ];


                $twig_loader = new FilesystemLoader($fileData->path);
                $twig = new Environment($twig_loader);

                try {
                    $temp = $twig->render($template . '.twig', ['data' => $data]);
                } catch (LoaderError|RuntimeError|SyntaxError $e) {
                    $responseJson->msg = $e->getMessage();
                    return $responseJson;
                }
                $responseJson->template = preg_replace(array('/<!--(.*)-->/Uis', "/[[:blank:]]+/"), array('', ' '), str_replace(array("\n", "\r", "\t"), '', $temp));

                $template == 'details' ?? $responseJson->target = '#twigRenderDetails';
                $responseJson->status = true;
                break;

            case 'load_license_data':

                $lang = $this->get_theme_default_settings('extension_preview_language');
                $license = filter_input(INPUT_POST, 'license', FILTER_SANITIZE_STRING);
                $responseJson->target = filter_input(INPUT_POST, 'target', FILTER_SANITIZE_STRING);
                if (!$license) {
                    $responseJson->msg = 'no license found!';
                    return $responseJson;
                }

                $body = [
                    'get_data' => 'get_license_data',
                    'license_url' => get_site_url()
                ];

                try {
                    $apiData = apply_filters($this->basename . '/get_api_post_resource', $body, $license);

                } catch (Exception $e) {

                    if ($e->getMessage() == 'Client access data is unknown.') {
                        $responseJson->msg = $e->getMessage();
                        apply_filters($this->basename . '_delete_extension', $license);
                    }

                    return $responseJson;
                }

                $licenseData = $this->get_extension_license_data($apiData);
                // print_r($licenseData);
                $licenseData->l = $lang;
                $dataUrl = $extensionOptions->wwdh_get_extension_preview_url_data($licenseData->extension_filename);
                $data = json_decode(file_get_contents($dataUrl));
                $licenseData->extension_logo = WP_EXPERIENCE_REPORTS_EXTENSION_PREVIEW_URL . $licenseData->extension_filename . '/' . $data->extension_logo;

                try {
                    $template = $this->twig->render('license.twig', ['data' => $licenseData]);
                    $responseJson->template = preg_replace(array('/<!--(.*)-->/Uis', "/[[:blank:]]+/"), array('', ' '), str_replace(array("\n", "\r", "\t"), '', $template));
                } catch (LoaderError|SyntaxError|RuntimeError $e) {
                    $responseJson->msg = $e->getMessage();
                } catch (Throwable $e) {
                    $responseJson->msg = $e->getMessage();
                }
                $responseJson->status = true;

                break;
            case'download_extension':

                $license = filter_input(INPUT_POST, 'license', FILTER_SANITIZE_STRING);
                if (!$license) {
                    $responseJson->msg = 'Ajax Error!';
                    return $responseJson;
                }
                $args = sprintf('WHERE license="%s"', $license);
                $extData = apply_filters($this->basename . '_get_extension', $args, false);
                if (!$extData->status) {
                    $responseJson->msg = 'Not data found!';
                    return $responseJson;
                }
                $extData = $extData->record;
                $extData->url_id ? $url_id = $extData->url_id : $url_id = false;
                $extData->url_limit_aktiv ? $url_limit_aktiv = true : $url_limit_aktiv = false;

                $body = [
                    'get_data' => 'download_extension',
                    'license_url' => get_site_url(),
                    'url_limit' => $url_limit_aktiv,
                    'url_id' => $url_id
                ];

                $url = get_option($this->basename . '-api-options')['extension_api_extension_download'];
                $downloadDir = WP_EXPERIENCE_REPORTS_EXTENSION_DIR . 'installed' . DIRECTORY_SEPARATOR;
                $download = apply_filters($this->basename . '/extension_download', $url, $body, $license);

                @file_put_contents($downloadDir . $extData->folder . '.zip', $download);
                WP_Filesystem();
                $unZipFile = unzip_file($downloadDir . $extData->folder . '.zip', $downloadDir);
                if (!$unZipFile) {
                    $extensionOptions->wwdh_set_api_log('error', 'WP_Filesystem - unzip_file error');
                    $responseJson->msg = 'Fehler beim Installieren!';
                    return $responseJson;
                }
                @unlink($downloadDir . $extData->folder . '.zip');
                $extName = strtoupper(str_replace(['-', '_', '|', '/'], ' ', $extData->folder));
                $responseJson->status = true;
                $responseJson->confirm_dialog = true;
                $responseJson->title = 'Erweiterung Installiert!';
                $responseJson->msg = 'Die Erweiterung "' . $extName . '" erfolgreich Installiert!';

                break;

            case'activate_extension':
                $extension = filter_input(INPUT_POST, 'extension', FILTER_SANITIZE_STRING);
                $type = filter_input(INPUT_POST, 'type', FILTER_SANITIZE_STRING);
                $license = filter_input(INPUT_POST, 'license', FILTER_SANITIZE_STRING);
                if (!$extension || !$type) {
                    $responseJson->msg = $errMsg['ajax_error'];
                    return $responseJson;
                }

                if (strlen($license) !== 27) {
                    $responseJson->msg = $errMsg['license_length_error'];
                    return $responseJson;
                }

                $license = str_replace(' ', '', $license);
                $lizenz = explode('-', $license);
                if (count($lizenz) !== 4) {
                    $responseJson->msg = $errMsg['license_length_error'];
                    return $responseJson;
                }

                try {
                    $licenseData = apply_filters($this->basename . '/extension_activate', $license, $extension);

                } catch (Exception $e) {
                    $responseJson->msg = '<b class="strong-font-weight">' . __('Server Response', 'wp-experience-reports') . ':</b> ' . $e->getMessage();
                    return $responseJson;
                }

                if (!$licenseData->status) {
                    $responseJson->msg = '<b class="strong-font-weight"> ' . __('Server Response', 'wp-experience-reports') . ':</b> ' . $licenseData->msg;
                    return $responseJson;
                }

                $dataUrl = $extensionOptions->wwdh_get_extension_preview_url_data($licenseData->extension_filename);
                $data = json_decode(file_get_contents($dataUrl));
                $licenseData->extension_logo = WP_EXPERIENCE_REPORTS_EXTENSION_PREVIEW_URL . $licenseData->extension_filename . '/' . $data->extension_logo;

                $licenseData = $this->get_extension_license_data($licenseData);

                $licenseData->l = $this->get_theme_default_settings('extension_preview_language');

                try {
                    $template = $this->twig->render('license.twig', ['data' => $licenseData]);
                    $responseJson->template = preg_replace(array('/<!--(.*)-->/Uis', "/[[:blank:]]+/"), array('', ' '), str_replace(array("\n", "\r", "\t"), '', $template));
                } catch (LoaderError|SyntaxError|RuntimeError $e) {
                    $responseJson->msg = $e->getMessage();
                } catch (Throwable $e) {
                    $responseJson->msg = $e->getMessage();
                }
                $responseJson->status = true;

                break;
        }
        return $responseJson;
    }

    public function get_extension_license_data($licenseData)
    {

        if ($licenseData->activated_time) {
            $timeStart = explode(' ', $licenseData->activatedTime);
            $licenseData->activatedTime = sprintf($timeStart[0] . ' %s ' . $timeStart[1] . ' %s', __('at', 'wp-experience-reports'), __('Clock', 'wp-experience-reports'));
        }

        if ($licenseData->time_limit_aktiv) {
            $licenseData->time_limit > 1 ? $licenseData->limitMonat = __('Months', 'wp-experience-reports') : $licenseData->limitMonat = __('Month', 'wp-experience-reports');

            $timeEnd = explode(' ', $licenseData->licenseEndTime);
            $licenseData->licenseEndTime = sprintf($timeEnd[0] . ' %s ' . $timeEnd[1] . ' %s', __('at', 'wp-experience-reports'), __('Clock', 'wp-experience-reports'));
        } else {
            $licenseData->licenseEndTime = __('unlimited', 'wp-experience-reports');
            $licenseData->limitMonat = __('unlimited', 'wp-experience-reports');
        }

        if (!$licenseData->url_limit_aktiv) {
            $licenseData->url_limit = __('unlimited', 'wp-experience-reports');
        }

        if ($licenseData->url_limit_aktiv) {
            $licenseData->url_limit > 1 ? $licenseData->urlLimitTxt = __('Installations', 'wp-experience-reports') : $licenseData->urlLimitTxt = __('Installation', 'wp-experience-reports');
        } else {
            $licenseData->url_limit = __('unlimited', 'wp-experience-reports');
            $licenseData->urlLimitTxt = '';
        }

        $productSlug = str_replace(' ', '', $licenseData->product_slug);
        $licenseData->product_slug = explode(',', strtoupper($productSlug));
        $licenseData->file_size = apply_filters($this->basename . '/FileSizeConvert', (float)$licenseData->file_size);
        return $licenseData;
    }
}