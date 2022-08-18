<?php

namespace Experience\Reports;

use Wp_Experience_Reports;
use stdClass;
use WP_Error;
use WP_REST_Controller;
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

class Experience_Reports_Public_Endpoint extends WP_REST_Controller
{

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes()
    {
        $version = '2';
        $namespace = 'experience-report-public/v' . $version;
        $base = '/';

        @register_rest_route(
            $namespace,
            $base,
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_registered_items'),
                'permission_callback' => array($this, 'permissions_check')
            )
        );

        @register_rest_route(
            $namespace,
            $base . '(?P<image>[^/]+)/(?P<img_id>[\d]+)',

            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'experience_reports_rest_public_endpoint'),
                'permission_callback' => array($this, 'permissions_check')
                //'permission_callback' => '__return_true',
            )
        );
    }

    /**
     * Get a collection of items.
     *
     * @param WP_REST_Request $request Full data about the request.
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_registered_items(WP_REST_Request $request)
    {
        $data = [];
        return rest_ensure_response($data);
    }

    /**
     * Get one item from the collection.
     *
     * @param WP_REST_Request $request Full data about the request.
     *
     * @return WP_Error|WP_REST_Response
     */
    public function experience_reports_rest_public_endpoint(WP_REST_Request $request){

        $response = new stdClass();
        $response->status = false;
        $param = $request->get_param('image');
        $imgId = $request->get_param('img_id');
        if (!$param) {
            return new WP_Error(404, ' Method failed');
        }

        switch ($param){
            case 'image-full':
                if($imgId){
                    $src_cover_img_full_src = wp_get_attachment_image_src($imgId, 'full', false);
                    $response->status = true;
                    $response->url = $src_cover_img_full_src[0];
                }
        }


       // return rest_ensure_response('Hello World! This is my first REST API ' . $response);
        return new WP_REST_Response($response, 200);
    }

    /**
     * Check if a given request has access.
     *
     * @return string
     */
    public function permissions_check(): string
    {
        return '__return_true';
       // return current_user_can('publish_posts');
    }
}