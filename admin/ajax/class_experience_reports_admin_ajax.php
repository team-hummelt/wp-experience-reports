<?php

namespace Experience\Reports;

use finfo;
use Wp_Experience_Reports;
use stdClass;

defined('ABSPATH') or die();

/**
 * Define the Admin AJAX functionality.
 *
 * Loads and defines the Admin Ajax files for this plugin
 *
 *
 * @link       https://wwdh.de/
 * @since      1.0.0
 */

/**
 * Define the AJAX functionality.
 *
 * Loads and defines the Admin Ajax files for this plugin
 *
 * @since      1.0.0
 * @package    Wp_Experience_Reports
 * @subpackage Wp_Experience_Reports/includes
 * @author     Jens Wiecker <email@jenswiecker.de>
 */
class Experience_Reports_Admin_Ajax
{

    /**
     * The plugin Slug Path.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $plugin_dir plugin Slug Path.
     */
    protected string $plugin_dir;

    /**
     * The AJAX METHOD
     *
     * @since    1.0.0
     * @access   private
     * @var      string $method The AJAX METHOD.
     */
    protected string $method;

    /**
     * The AJAX DATA
     *
     * @since    1.0.0
     * @access   private
     * @var      array|object $data The AJAX DATA.
     */
    private $data;

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $basename The ID of this plugin.
     */
    private string $basename;

    /**
     * The Version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current Version of this plugin.
     */
    private string $version;

    /**
     * Store plugin main class to allow public access.
     *
     * @since    1.0.0
     * @access   private
     * @var Wp_Experience_Reports $main The main class.
     */
    private Wp_Experience_Reports $main;


    public function __construct(string $basename, string $version, Wp_Experience_Reports $main)
    {

        $this->basename = $basename;
        $this->version = $version;
        $this->main = $main;
        $this->plugin_dir = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $this->basename . DIRECTORY_SEPARATOR;
        $this->method = '';
        if (isset($_POST['daten'])) {
            $this->data = $_POST['daten'];
            $this->method = filter_var($this->data['method'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
        }

        if (!$this->method) {
            $this->method = $_POST['method'];
        }
    }

    public function experience_reports_admin_ajax_handle(): object
    {
        $responseJson = new stdClass();
        $record = new stdClass();
        $responseJson->status = false;
        $responseJson->time = date('H:i:s', current_time('timestamp'));
        switch ($this->method) {
            case'update_er_settings':
                $responseJson->spinner = true;
                $userRole = filter_input(INPUT_POST, 'user_role', FILTER_SANITIZE_STRING);
                if (!$userRole) {
                    $responseJson->msg = 'Es wurden keine Daten übertragen!';
                    return $responseJson;
                }

                update_option('experience_reports_user_role', $userRole);
                $responseJson->status = true;
                $responseJson->msg = date('H:i:s', current_time('timestamp'));
                break;

            case'update-twig-templates':
                $checkDescription = function ($array_item) {
                    return filter_var($array_item, FILTER_SANITIZE_STRING);
                };

                $templates = array($_POST['bezeichnung']);
                $templates = array_map($checkDescription, $templates[0]);

                $isGallery = array($_POST['isGallery']);
                $isGallery = array_map($checkDescription, $isGallery[0]);

                $option = get_option($this->basename . '_twig_templates');
                $optArr = [];
                foreach ($option as $tmp) {
                    if (isset($isGallery['gallery#' . $tmp['id']])) {
                        $galleryOn = 1;
                    } else {
                        $galleryOn = 0;
                    }
                    $tmp['name'] = $templates[$tmp['id']];
                    $tmp['is_gallery'] = $galleryOn;
                    $optArr[] = $tmp;
                }

                update_option($this->basename . '_twig_templates', $optArr);
                $responseJson->status = true;
                $responseJson->msg = 'gespeichert';

                break;
            case 'delete_twig_template':
                $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
                $templates = get_option($this->basename . '_twig_templates');
                if (!$id) {
                    $responseJson->msg = 'file not found';
                    return $responseJson;
                }

                $optArr = [];
                foreach ($templates as $tmp) {
                    if ($tmp['id'] == $id) {
                        $file = $this->main->get_twig_user_templates() . DIRECTORY_SEPARATOR . $tmp['file'];
                        if (is_file($file)) {
                            unlink($file);
                        }
                    } else {
                        $optArr[] = $tmp;
                    }
                }
                update_option($this->basename . '_twig_templates', $optArr);
                $responseJson->id = $id;
                $responseJson->status = true;
                $responseJson->msg = 'gelöscht';
                break;
        }

        return $responseJson;
    }
}