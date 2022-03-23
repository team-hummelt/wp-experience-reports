<?php

namespace Experience\Reports;

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
defined( 'ABSPATH' ) or die();

class Register_Experience_Reports_Endpoint {
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

    public function __construct( string $basename, string $version,  Wp_Experience_Reports $main ) {

        $this->basename   = $basename;
        $this->version    = $version;
        $this->main       = $main;

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
    }

    /**
     * Get one item from the collection.
     *
     * @param WP_REST_Request $request Full data about the request.
     *
     * @return WP_Error|WP_REST_Response
     */
    public function experience_reports_rest_endpoint_get_response(WP_REST_Request $request) {

        $method = $request->get_param( 'method' );
        if (!$method) {
            return new WP_Error(404, ' Method failed');
        }
        $response = new stdClass();
        $args = sprintf('WHERE folder="%s" AND aktiv=1','experience-reports-gallery');
        $galleryDb = apply_filters($this->basename.'_get_extension',$args);
        switch ( $method ) {
            case 'get-data':
                $args = sprintf('WHERE folder="%s" AND aktiv=1','experience-reports-gallery');
                $galleryDb = apply_filters($this->basename.'_get_extension',$args);
                $galArr = [
                    '0' => [
                        'value' => 0,
                        'label' => __('select ...','wp-experience-reports')
                    ]
                ];
                if($galleryDb->status){
                    $response->isGallery = true;
                    $galleryData = apply_filters(REPORTS_GALLERY_BASENAME.'/post_selector_get_galerie','');

                    if($galleryData->status){
                        foreach ($galleryData->record as $tmp) {
                            $gall_items = [
                                'value' => (int) $tmp->id,
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

                $args = sprintf('WHERE folder="%s" AND aktiv=1','experience-reports-gallery');
                $galleryDb = apply_filters($this->basename.'_get_extension',$args);
                $tempArr = [];
                $templates = apply_filters($this->basename . '/get_template_select','');
                if($templates){
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
                    foreach ($terms->terms as $tmp){
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
                $galleryData = apply_filters(REPORTS_GALLERY_BASENAME.'/post_selector_get_galerie','');
                $galleryData->status ? $response->isGallery = true : $response->isGallery = false;
                $pages = get_pages();
                $retArr = [
                    '0' => [
                        'id' => 0,
                        'name' => __('select ...','wp-experience-reports'),
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
                $terms = apply_filters($this->basename.'/get_custom_terms','experience_reports_category');
                $tempName = [];
                if($terms->status){
                    foreach ($terms->terms as $tmp) {
                        $tempName[] = $tmp->name;
                    }
                }

                $tempArr = [];
                $templates = apply_filters($this->basename . '/get_template_select','');
                if($templates){
                    foreach ($templates as $tmp) {
                        $temp_item = [
                            'id' => $tmp['id'],
                            'name' => $tmp['name']
                        ];
                        $tempArr[] = $temp_item;
                    }
                }
                $galleryDb->status ? $response->isGallery = true :  $response->isGallery = false;
                $response->templates = $tempArr;
                $response->category = implode(',',$tempName);

                break;

        }
        return new WP_REST_Response( $response, 200 );

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