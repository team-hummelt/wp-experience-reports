<?php

namespace Experience\Reports;

use Exception;
use Wp_Experience_Reports;
use stdClass;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * ADMIN Experience Reports Gutenberg ENDPOINT
 *
 * @link       https://wwdh.de
 * @since      1.0.0
 *
 * @package    Post_Selector
 * @subpackage Experience_Reports/admin/gutenberg/
 */
defined('ABSPATH') or die();

class Register_Experience_Reports_Endpoint
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

    /**
     *
     * @param string $basename
     * @param string $version
     *
     * @since    1.0.0
     * @access   private
     *
     * @var Wp_Experience_Reports $main
     */

    public function __construct(string $basename, string $version, Wp_Experience_Reports $main)
    {

        $this->basename = $basename;
        $this->version = $version;
        $this->main = $main;

    }

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_experience_reports_routes()
    {

        $version = '1';
        $namespace = 'wp-experience-report/v' . $version;
        $base = '/';

        @register_rest_route(
            $namespace,
            $base . '(?P<method>[\S]+)',

            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'experience_reports_rest_endpoint_get_response'),
                'permission_callback' => array($this, 'permissions_check')
            )
        );

        $namespace = 'wp-report-posts/v' . $version;
        @register_rest_route(
            $namespace,
            $base . '(?P<method>[\S]+)',

            array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array($this, 'experience_reports_rest_endpoint_post_response'),
                'permission_callback' => array($this, 'permissions_check')
            )
        );

        $namespace = 'wp-report/v' . $version;
        @register_rest_route(
            $namespace,
            //$base . '(?P<img-id>[\d^/]+)',
            $base . '(?P<method>[\S]+)',

            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'experience_reports_rest_endpoint_public_response'),
                'permission_callback' => '__return_true',
            )
        );
    }

    /**
     * Get one item from the collection.
     *
     * @param WP_REST_Request $request Full data about the request.
     *
     * @return WP_Error|WP_REST_Response
     */
    public function experience_reports_rest_endpoint_get_response(WP_REST_Request $request)
    {

        $method = $request->get_param('method');
        if (!$method) {
            return new WP_Error(404, ' Method failed');
        }
        $response = new stdClass();
        $args = sprintf('WHERE folder="%s" AND aktiv=1', 'experience-reports-gallery');
        $galleryDb = apply_filters($this->basename . '_get_extension', $args);
        switch ($method) {
            case 'get-data':
                $args = sprintf('WHERE folder="%s" AND aktiv=1', 'experience-reports-gallery');
                $galleryDb = apply_filters($this->basename . '_get_extension', $args);
                $galArr = [
                    '0' => [
                        'value' => 0,
                        'label' => __('select ...', 'wp-experience-reports')
                    ]
                ];
                if ($galleryDb->status) {
                    $response->isGallery = true;
                    $galleryData = apply_filters(REPORTS_GALLERY_BASENAME . '/post_selector_get_galerie', '');

                    if ($galleryData->status) {
                        foreach ($galleryData->record as $tmp) {
                            $gall_items = [
                                'value' => (int)$tmp->id,
                                'label' => $tmp->bezeichnung
                            ];
                            $galArr[] = $gall_items;
                        }
                    }
                } else {
                    $response->isGallery = false;
                }

                $response->gallery = $galArr;
                break;
            case'get-experience-block-data':

                $args = sprintf('WHERE folder="%s" AND aktiv=1', 'experience-reports-gallery');
                $galleryDb = apply_filters($this->basename . '_get_extension', $args);
                $tempArr = [];
                $templates = get_option($this->basename . '_twig_templates');
                if ($templates) {
                    foreach ($templates as $tmp) {
                        $temp_item = [
                            'id' => $tmp['id'],
                            'name' => $tmp['name']
                        ];
                        $tempArr[] = $temp_item;
                    }
                }

                $terms = apply_filters($this->basename . '/get_custom_terms', 'experience_reports_category');
                $catArr = [];

                if ($terms->status) {
                    foreach ($terms->terms as $tmp) {
                        $cat_item = [
                            'id' => $tmp->term_id,
                            'name' => $tmp->name
                        ];
                        $catArr[] = $cat_item;
                    }
                }

                $response->templates = $tempArr;
                $response->categories = $catArr;
                $response->isGallery = $galleryDb->status;
                $response->shortcode_id = WP_EXPERIENCE_SHORTCODE_ID;
                break;
            case'get-page-data':
                $galleryData = apply_filters(REPORTS_GALLERY_BASENAME . '/post_selector_get_galerie', '');
                $galleryData->status ? $response->isGallery = true : $response->isGallery = false;
                $pages = get_pages();
                $retArr = [
                    '0' => [
                        'id' => 0,
                        'name' => __('select ...', 'wp-experience-reports'),
                        'type' => 'page'
                    ]
                ];
                foreach ($pages as $page) {
                    $ret_item = [
                        'name' => $page->post_title,
                        'id' => $page->ID,
                        'type' => 'page'
                    ];
                    $retArr[] = $ret_item;
                }
                $response->pages = $retArr;
                // $response->isGallery = true;
                $response->shortcode = WP_EXPERIENCE_SHORTCODE_ID;
                break;
            case'get-data-filter':
                $terms = apply_filters($this->basename . '/get_custom_terms', 'experience_reports_category');
                $catArr = [];
                $tempName = [];
                if ($terms->status) {
                    foreach ($terms->terms as $tmp) {
                        $jsonSend = [
                            'id' => $tmp->term_id,
                            'label' => $tmp->name
                        ];
                        $jsonSend = json_encode($jsonSend);

                        $cat_item = [
                            'label' => $tmp->name,
                            'id' => $jsonSend

                        ];
                        $catArr[] = $cat_item;
                    }
                }

                $tempArr = [];

                //$templates = apply_filters($this->basename . '/get_template_select','');
                $templates = get_option($this->basename . '_twig_templates');
                if ($templates) {
                    foreach ($templates as $tmp) {
                        $temp_item = [
                            'id' => $tmp['id'],
                            'name' => $tmp['name']
                        ];
                        $tempArr[] = $temp_item;
                    }
                }

                //$orderKat = $this->order_by_args($catArr, 'id', '2');
                $galleryDb->status ? $response->isGallery = true : $response->isGallery = false;
                $response->templates = $tempArr;
                $response->categories = $catArr;

                break;
            case'get-twig-file':
                $body = $request->get_body();
                $response->body = $body;
                break;
            case'get-template-data':
                $tempArr = [];
                $templates = get_option($this->basename . '_twig_templates');

                if ($templates) {
                    foreach ($templates as $tmp) {
                        $temp_item = [
                            'id' => $tmp['id'],
                            'name' => $tmp['name']
                        ];
                        $tempArr[] = $temp_item;
                    }
                }

                $terms = apply_filters($this->basename.'/get_custom_terms','experience_reports_category');
                $catArr = [];
                if($terms->status){
                    foreach ($terms->terms as $tmp) {
                        $cat_item = [
                            'id'=>$tmp->term_id,
                            'label'=>$tmp->name
                        ];
                        $catArr[] = $cat_item;
                    }
                }
                $response->categories = $catArr;
                $response->templates = $tempArr;
                break;

        }
        return new WP_REST_Response($response, 200);

    }

    /**
     * Get one item from the collection.
     *
     * @param WP_REST_Request $request Full data about the request.
     *
     * @return WP_Error|WP_REST_Response
     */
    public function experience_reports_rest_endpoint_post_response(WP_REST_Request $request)
    {
        $method = filter_input(INPUT_POST, 'method', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);

        $response = new stdClass();
        switch ($method) {
            case'get-twig-file':
                $file = filter_input(INPUT_POST, 'file', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
                $folder = filter_input(INPUT_POST, 'folder', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
                $folder ? $separator = DIRECTORY_SEPARATOR : $separator = '';
                $dir = $this->main->get_twig_user_templates() . DIRECTORY_SEPARATOR . $folder . $separator;
                if (!is_dir($dir)) {
                    $response->message = 'folder not found';
                    return new WP_REST_Response($response, 401);
                }

                if (!is_file($dir . $file)) {
                    $response->message = 'file not found';
                    return new WP_REST_Response($response, 401);
                }

                $file = file($dir . $file);
                $html = '';
                if ($file) {
                    foreach ($file as $tmp) {
                        $html .= '<span class="number">' . htmlspecialchars($tmp) . '</span>';
                    }
                }

                $response->file = $html;
                break;
            case'sync-template-folder':

                $templates = get_option($this->basename . '_twig_templates');
                $twigTemplatesFiles = apply_filters($this->basename . '/get_files_from_dir', $this->main->get_twig_user_templates());
                $addTemp = [];
                if (!$twigTemplatesFiles) {
                    $response->message = 'files not found';
                    return new WP_REST_Response($response, 401);
                }
                    foreach ($twigTemplatesFiles as $tmp) {
                        if (is_array($tmp)) {
                            continue;
                        }
                        if (!$this->check_is_temp_file($tmp)) {
                            $addTemp[] = $tmp;
                        }
                    }

                $twigTemplates = [];
                if($addTemp){
                    foreach ($addTemp as $tmp){
                        $pathInfo = pathinfo( $this->main->get_twig_user_templates() . DIRECTORY_SEPARATOR . $tmp);
                        if(!$pathInfo['extension'] == 'twig'){
                            continue;
                        }
                        $id = apply_filters($this->basename.'/generate_random_id',12,0,12);
                        $twig_temp = [
                            'id' => $id,
                            'dir' =>  $this->main->get_twig_user_templates(),
                            'file' => $pathInfo['basename'],
                            'name' => $pathInfo['filename'],
                            'is_gallery' => 1
                        ];
                        $twigTemplates[] = $twig_temp;
                    }
                }
                if($twigTemplates){
                    $templates = wp_parse_args($twigTemplates,$templates);
                    update_option($this->basename . '_twig_templates', $templates);
                }
                break;

        }

        return new WP_REST_Response($response, 200);
    }

    private function check_is_temp_file($file): bool
    {
        $templates = get_option($this->basename . '_twig_templates');
        foreach ($templates as $tmp) {
            if ($tmp['file'] == $file) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function experience_reports_rest_endpoint_public_response(WP_REST_Request $request): WP_REST_Response
    {

        $imgId = $request->get_param('img_id');
        $response = new stdClass();
        $response->imgId = 'hello';
        return new WP_REST_Response($response, 200);
    }

    /**
     * Check if a given request has access.
     *
     * @return bool
     */
    public function permissions_check(): bool
    {
        return current_user_can('edit_posts');
    }

    /**
     * @param string $taxonomy
     * @return object
     */
    public function experience_reports_get_custom_terms(string $taxonomy): object
    {
        $return = new  stdClass();
        $return->status = false;
        $terms = get_terms(array(
            'taxonomy' => $taxonomy,
            'parent' => 0,
            'hide_empty' => false,
        ));

        if (!$terms) {
            return $return;
        }
        $return->status = true;
        $return->terms = $terms;
        return $return;
    }
}