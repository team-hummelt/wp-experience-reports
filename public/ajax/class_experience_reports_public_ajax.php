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
class Experience_Reports_Public_Ajax
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

    public function experience_reports_public_ajax_handle(): object
    {
        $responseJson = new stdClass();
        $record = new stdClass();
        $responseJson->status = false;
        $responseJson->time = date('H:i:s', current_time('timestamp'));
        switch ($this->method) {
            case 'load-more-action':
                $total = filter_input(INPUT_POST, 'total', FILTER_SANITIZE_NUMBER_INT);
                $catId = filter_input(INPUT_POST, 'cat_id', FILTER_SANITIZE_NUMBER_INT);
                $limit = filter_input(INPUT_POST, 'limit', FILTER_SANITIZE_NUMBER_INT);
                $post_id = filter_input(INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT);
                $loaded = filter_input(INPUT_POST, 'loaded', FILTER_SANITIZE_NUMBER_INT);
                $wrapperId = filter_input(INPUT_POST, 'wrapper_id', FILTER_SANITIZE_STRING);

                if (!$total || !$limit || !$post_id) {
                    return $responseJson;
                }
                if(!$loaded) {
                   $loaded = 0;
                }

                $loaded = $loaded + 1;
                $attr = apply_filters($this->basename.'/get_post_attributes', $post_id, $catId);
                $attr = (array) $attr;
                $attr['dataLoad'] = $loaded;
                $attr['postId'] = $post_id;
                $attr['loadMore'] = true;

                $ins = $loaded * $limit;
                $ins < $total ? $responseJson->showBtn = true : $responseJson->showBtn = false;
                $wrapperId ? $responseJson->wrapperId = $wrapperId : $responseJson->wrapperId= false;
                $responseJson->template = apply_filters($this->basename.'/render_callback_template', $attr);

                $responseJson->loaded = $loaded;
                $responseJson->isGallery = true;
                $responseJson->status = true;
                break;
        }
        return $responseJson;
    }
}