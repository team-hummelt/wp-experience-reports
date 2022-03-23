<?php

namespace Experience\Reports;

use Exception;
use stdClass;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Wp_Experience_Reports;

defined('ABSPATH') or die();

/**
 * Define the Experience_Reports functionality
 *
 * Loads and defines Extensions
 * For the plugin
 *
 * @link       https://wwdh.de
 * @since      1.0.0
 *
 * @package    Experience_Report
 * @subpackage Experience_Report/extensions
 */
class Experience_Report_Extensions
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $basename The ID of this plugin.
     */
    private string $basename;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private string $version;

    /**
     * TRAIT of Default Settings.
     *
     * @since    1.0.0
     */
    use Trait_Extension_Defaults;

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
     * @param string $basename
     * @param string $version
     * @param Wp_Experience_Reports $main
     * @param Environment $twig
     */
    public function __construct(string $basename, string $version, Wp_Experience_Reports $main, Environment $twig)
    {
        $this->basename = $basename;
        $this->version = $version;
        $this->main = $main;
        $this->twig = $twig;
    }

    public function wwdh_check_extension_preview_updates()
    {
        global $plugin_helper;
        global $experienceReportsPublicApi;
        if (!is_dir($this->main->get_extension_preview())) {
            exit();
        }
        $extDirs = [];
        $apiExtFolder = [];
        foreach (scandir($this->main->get_extension_preview()) as $file) {
            if ($file == "." || $file == "..")
                continue;
            if (is_dir($this->main->get_extension_preview() . $file)) {
                if (!is_file($this->main->get_extension_preview() . $file . DIRECTORY_SEPARATOR . 'extension.json')) {
                    continue;
                }
                $extData = json_decode(file_get_contents($this->main->get_extension_preview() . $file . DIRECTORY_SEPARATOR . 'extension.json'));

                $ext_items = [
                    'filename' => $file,
                    'extension_version' => $extData->extension_version
                ];
                $extDirs[] = $ext_items;
            }
        }

        foreach ($extDirs as $tmp) {
            $body = [
                'filename' => $tmp['filename'],
                'extension_version' => $tmp['extension_version'],
            ];
            $checkUpdate = $experienceReportsPublicApi->wwdh_get_public_resource_method('update_preview', '', $body);

            if ($checkUpdate->status) {
                if ($checkUpdate->update) {
                    $plugin_helper->wwdhDestroyDir($this->main->get_extension_preview() . $tmp['filename']);
                    $url = get_option($this->basename . '-api-options')['public_api_preview_url'] . '/update/' . $tmp['filename'];
                    $zipFile = $experienceReportsPublicApi->wwdh_api_public_download($url, $body);

                    if (!$zipFile) {
                        $this->wwdh_set_api_log('error', 'api download no zip file');
                        exit();
                    }
                    @file_put_contents($this->main->get_extension_preview() . $tmp['filename'] . '.zip', $zipFile);
                    WP_Filesystem();
                    $unZipFile = unzip_file($this->main->get_extension_preview() . $tmp['filename'] . '.zip', $this->main->get_extension_preview());
                    if (!$unZipFile) {
                        $this->wwdh_set_api_log('error', 'WP_Filesystem - unzip_file error');
                        exit();
                    }
                    @unlink($this->main->get_extension_preview() . $tmp['filename'] . '.zip');
                }
            }
        }
        try {
            $getExtension = $experienceReportsPublicApi->wwdh_get_public_resource_method('get_extensions');

        } catch (Exception $e) {
            $this->wwdh_set_api_log('error', $e->getMessage());
        }
        if (isset($getExtension->data) && $getExtension->data) {
            foreach ($getExtension->data as $tmp) {
                $apiExtFolder[] = $tmp->extension_filename;
                $folder = $tmp->extension_filename;
                $previewDir = $this->main->get_extension_preview();
                if (is_dir($previewDir . $folder)) {
                    continue;
                }
                $body = [
                    'filename' => $tmp->extension_filename,
                    'extension_version' => $tmp->extension_version,
                ];
                try {
                    $zipFile = $experienceReportsPublicApi->wwdh_api_public_download($tmp->download_url, $body);

                } catch (Exception $e) {
                    $this->wwdh_set_api_log('error', $e->getMessage());
                    exit();
                }
                if (!$zipFile) {
                    $this->wwdh_set_api_log('error', 'api download no zip file');
                    exit();
                }
                @file_put_contents($previewDir . $tmp->extension_filename . '.zip', $zipFile);
                WP_Filesystem();
                $unZipFile = unzip_file($previewDir . $tmp->extension_filename . '.zip', $previewDir);
                if (!$unZipFile) {
                    $this->wwdh_set_api_log('error', 'WP_Filesystem - unzip_file error');
                    exit();
                }
                @unlink($previewDir . $tmp->extension_filename . '.zip');
            }
        }
        foreach (scandir($this->main->get_extension_preview()) as $file) {
            if ($file == "." || $file == "..")
                continue;
            if (is_dir($this->main->get_extension_preview() . $file)) {
                if (!in_array($file, $apiExtFolder)) {
                    $plugin_helper->wwdhDestroyDir($this->main->get_extension_preview() . $file);
                }
            }
        }
    }

    /**
     * @param string $type
     * @param string $message
     */
    public function wwdh_set_api_log(string $type, string $message): void
    {
        $logDir = $this->main->get_api_dir() . 'log' . DIRECTORY_SEPARATOR;
        if(!is_dir($logDir)){
            mkdir($logDir, 0755, true);
        }
        $logFile = 'api.log';
        $msg = $type . '|' . current_time('mysql') . '|' . $message;
        $msg .= "\r\n";

        @file_put_contents($logDir . $logFile, $msg, FILE_APPEND);
    }

    public function wwdh_get_extension_preview_url_data($folder): string
    {
        $dir = $this->main->get_extension_preview();
        if (is_dir($dir . $folder)) {
            return WP_EXPERIENCE_REPORTS_EXTENSION_PREVIEW_URL . $folder . '/extension.json';
        }
        return '';
    }

    public function wwdh_get_extension_preview_language_url(): array
    {
        $urlArr = [];
        $folder = $this->read_wwdh_folder($this->main->get_extension_preview());
        if ($folder) {
            foreach ($folder as $extension) {
                $urlArr[$extension] = $this->get_extension_previews_lang_url($extension);
            }
        }
        return $urlArr;
    }

    /**
     * @param string $path
     * @param bool $full
     * @return array
     */
    public function read_wwdh_folder(string $path, bool $full = false): array
    {
        $folderArr = [];
        foreach (scandir($path) as $folder) {
            if ($folder == "." || $folder == "..")
                continue;
            $full ? $retDir = $path . $folder : $retDir = $folder;
            if (is_dir($path . $folder)) {
                $folderArr[] = $retDir;
            }
        }
        return $folderArr;
    }

    /**
     * @param $extension
     * @return string
     */
    public function get_extension_previews_lang_url($extension): string
    {
        $curLang = substr(get_bloginfo('language'), 0, 2);
        $folder = $this->main->get_extension_preview() . $extension . DIRECTORY_SEPARATOR;
        $path = 'language' . DIRECTORY_SEPARATOR . 'json' . DIRECTORY_SEPARATOR;
        if (is_file($folder . $path . $curLang . '.json')) {
            return WP_EXPERIENCE_REPORTS_EXTENSION_PREVIEW_URL . $extension . '/language/json/' . $curLang . '.json';
        } else {
            return WP_EXPERIENCE_REPORTS_EXTENSION_PREVIEW_URL . $extension . '/language/json/en.json';
        }
    }

    public function get_wwdh_extension_description_template($extension, $template_name): object
    {
        $return = new stdClass();
        $return->status = false;
        $curLang = substr(get_bloginfo('language'), 0, 2);
        $folder = $this->main->get_extension_preview() . $extension . DIRECTORY_SEPARATOR;
        $path = 'includes' . DIRECTORY_SEPARATOR . 'description' . DIRECTORY_SEPARATOR . $curLang . DIRECTORY_SEPARATOR;
        $path_en = 'includes' . DIRECTORY_SEPARATOR . 'description' . DIRECTORY_SEPARATOR . 'en' . DIRECTORY_SEPARATOR;
        $file = $folder . $path . $template_name;
        $file_en = $folder . $path_en . $template_name;
        $description_url = WP_EXPERIENCE_REPORTS_EXTENSION_PREVIEW_URL . $extension . '/' . 'includes/description/';
        if (is_file($file)) {
            $return->status = true;
            $return->language = $curLang;
            $return->description_url = $description_url;
            $return->path = $folder . $path;
            $return->file = $template_name;
        } elseif (is_file($file_en)) {
            $return->description_url = $description_url;
            $return->language = 'en';
            $return->status = true;
            $return->file = $template_name;
            $return->path = $folder . $path_en;
        }

        return $return;
    }

    public function wwdh_check_extensions_installs($extension = ''): object
    {

        $return = new stdClass();
        $record = new stdClass();
        $return->status = false;
        global $wwdhExtensionApi;

        $folderExt = $this->read_wwdh_folder(WP_EXPERIENCE_REPORTS_EXTENSION_DIR . 'installed' . DIRECTORY_SEPARATOR);
        $errDb = 0;
        if ($folderExt) {
            foreach ($folderExt as $folder) {
                $args = sprintf('WHERE folder="%s"', $folder);
                $dbInstalls = apply_filters('wp-experience-reports_get_extension', $args);

                if (!$dbInstalls->status) {
                    $dir = WP_EXPERIENCE_REPORTS_EXTENSION_DIR . 'installed' . DIRECTORY_SEPARATOR . $folder;
                    if (is_dir($dir)) {
                        apply_filters($this->basename . '/destroy_dir', $dir);
                        $errDb++;
                    }
                }
            }
            if ($errDb > 0) {
                wp_redirect(admin_url());
                exit();
            }
        }

        $dbLicense = apply_filters($this->basename . '_get_extension', '');
        update_option($this->basename.'/wwdh_extension_check', current_time('timestamp'));
        if (isset($dbLicense->status) && $dbLicense->status) {
            $dbLicense = $dbLicense->record;
            foreach ($dbLicense as $tmp) {
                $tmp->url_limit_aktiv ? $url_id = $tmp->url_id : $url_id = false;
                $body = [
                    'get_data' => 'check_activated',
                    'license_url' => site_url(),
                    'url_id' => $url_id
                ];
                try {
                    $apiData = $wwdhExtensionApi->wwdh_get_api_post_resource($body, $tmp->license);

                    isset($apiData->activated) ? $activated = $apiData->activated : $activated = $tmp->aktiv;
                    isset($apiData->url_activated) ? $url_activated = $apiData->url_activated : $url_activated = $tmp->url_activated;
                    isset($apiData->url_limit) ? $url_limit = $apiData->url_limit : $url_limit = $tmp->url_limit_aktiv;
                    $record->license = $tmp->license;
                    $record->aktiv = $activated;
                    $record->url_activated = $url_activated;
                    $record->url_limit_aktiv = $url_limit;
                    $record->last_connect = current_time('mysql');

                    apply_filters($this->basename . '_update_activated_extension', $record);
                    $record->errors = 0;
                    do_action($this->basename . '_update_extension_last_connect', $record);
                    if ($apiData->status) {
                        $return->status = true;
                        return $return;
                    }

                    if (isset($apiData->remove) && $apiData->remove) {
                        if ($apiData->extension) {
                            $dir = WP_EXPERIENCE_REPORTS_EXTENSION_DIR . 'installed' . DIRECTORY_SEPARATOR . $apiData->extension;
                            if (is_dir($dir)) {
                               apply_filters($this->basename . '/destroy_dir', $dir);
                            }
                        }
                          apply_filters($this->basename.'_delete_extension', $record->license);
                    }
                } catch (Exception $e) {
                    $record->errors = $tmp->errors + 1;
                    $record->errors == 5 ? $record->aktiv = 0 : $record->aktiv = 1;
                    $record->license = $tmp->license;
                    $record->last_connect = current_time('mysql');
                    do_action($this->basename . '_update_extension_error', $record);
                    $return->msg = $e->getMessage();
                    $return->api_error = true;
                    return $return;
                }
            }
        }
        return $return;
    }

    public function wwdh_check_api_extension()
    {
        $record = new stdClass();
        $dbData = apply_filters($this->basename . '_get_extension', '');
        if ($dbData->status) {
            foreach ($dbData->record as $tmp) {
                try {
                    $apiCheck = $this->wwdh_check_extensions_installs($tmp->license);

                } catch (Exception $e) {
                    // echo $e->getMessage();
                    continue;
                }

                $dir = WP_EXPERIENCE_REPORTS_EXTENSION_DIR . 'installed' . DIRECTORY_SEPARATOR . $tmp->folder;
                if (!$apiCheck->status) {
                    if ($tmp->errors > 4) {
                        if (is_dir($dir)) {
                            apply_filters($this->basename . '/destroy_dir', $dir);
                        }
                        apply_filters($this->basename . '_delete_extension', $tmp->license);
                    } else {
                        $record->errors = $tmp->errors + 1;
                        $record->errors == 5 ? $record->aktiv = 0 : $record->aktiv = 1;
                        $record->license = $tmp->license;
                        $record->last_connect = current_time('mysql');
                        do_action($this->basename . '_update_extension_error', $record);
                    }
                }

                if ($apiCheck->status) {
                    $record->last_connect = current_time('mysql');
                    $record->license = $apiCheck->license;
                    $record->errors = 0;
                    do_action($this->basename . '_update_extension_last_connect', $record);
                }
            }
        }
    }


    public function wwdh_activate_extension($license, $extension = '')
    {
        global $wwdhExtensionApi;
        try {
            $licenseData = $wwdhExtensionApi->wwdh_extension_activate($license, $extension);
        } catch (Exception $e) {
            $this->wwdh_set_api_log('error', $e->getMessage());
            exit();
        }
        return $licenseData;
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function wwdh_twig_template_loader($template, $data)
    {
        echo $this->twig->render($template . '.twig', ['data' => $data]);
    }

    final public function reset_public_api_settings()
    {
        delete_option($this->basename . '-api-options');
        $apiDef = $this->get_theme_default_settings('api_settings');
        update_option($this->basename . '-api-options', $apiDef);
    }

}