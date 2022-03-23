<?php

/**
 * Fired during plugin activation
 *
 * @link       https://wwdh.de
 * @since      1.0.0
 *
 * @package    Wp_Experience_Reports
 * @subpackage Wp_Experience_Reports/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Wp_Experience_Reports
 * @subpackage Wp_Experience_Reports/includes
 * @author     Jens Wiecker <email@jenswiecker.de>
 */
class Wp_Experience_Reports_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
        self::register_experience_reports_post_type();
        self::register_experience_taxonomies();
        flush_rewrite_rules();
	}

    /**
     * Register Custom Post-Type Team-Members.
     *
     * @since    1.0.0
     */
    public static function register_experience_reports_post_type(): void
    {
        register_post_type(
            'experience_reports',
            array(
                'labels' => array(
                    'name' => __('Reports', 'wp-experience-reports'),
                    'singular_name' => __('Report Posts', 'wp-experience-reports'),
                    'edit_item' => __('Edit Report Post', 'wp-experience-reports'),
                    'all_items' => __('all Reports', 'wp-experience-reports'),
                    'items_list_navigation' => __('Report Posts navigation', 'wp-experience-reports'),
                    'add_new_item' => __('Add new post', 'wp-experience-reports'),
                    'archives' => __('Report Posts Archives', 'wp-experience-reports'),
                ),
                'public' => true,
                'publicly_queryable' => true,
                'show_in_rest' => true,
                'show_ui' => true,
                'show_in_menu' => true,
                'has_archive' => true,
                'query_var' => true,
                'show_in_nav_menus' => true,
                'exclude_from_search' => false,
                'hierarchical' => true,
                'capability_type' => 'post',
                'menu_icon' => self::get_svg_icons('square'),
                'menu_position' => 29,
                'supports' => array(
                    'title', 'excerpt', 'page-attributes', 'author', 'editor', 'thumbnail','custom-fields'
                ),
                'taxonomies' => array('experience_reports_category'),
            )
        );
    }

    /**
     * Register Custom Taxonomies for Team-Members Post-Type.
     *
     * @since    1.0.0
     */
    public static function register_experience_taxonomies(): void
    {
        $labels = array(
            'name' => __('Report Categories', 'wp-experience-reports'),
            'singular_name' => __('Report Category', 'wp-experience-reports'),
            'search_items' => __('Search Reports Categories', 'wp-experience-reports'),
            'all_items' => __('All Reports Categories', 'wp-experience-reports'),
            'parent_item' => __('Parent Report Category', 'wp-experience-reports'),
            'parent_item_colon' => __('Parent Report Category:', 'wp-experience-reports'),
            'edit_item' => __('Edit Report Category', 'wp-experience-reports'),
            'update_item' => __('Update Report Category', 'wp-experience-reports'),
            'add_new_item' => __('Add New Report Category', 'wp-experience-reports'),
            'new_item_name' => __('New Report Category', 'wp-experience-reports'),
            'menu_name' => __('Report Categories', 'wp-experience-reports'),
        );

        $args = array(
            'labels' => $labels,
            'hierarchical' => true,
            'show_ui' => true,
            'sort' => true,
            'show_in_rest' => true,
            'query_var' => true,
            'args' => array('orderby' => 'term_order'),
            'rewrite' => array('slug' => 'experience_reports_category'),
            'show_admin_column' => true
        );
        register_taxonomy('experience_reports_category', array('experience_reports'), $args);

        if (!term_exists('Report General', 'experience_reports_category')) {
            wp_insert_term(
                'Report General',
                'experience_reports_category',
                array(
                    'description' => __('Standard category for Reports posts', 'wp-experience-reports'),
                    'slug' => 'experience-reports-posts'
                )
            );
        }
    }

    /**
     * @param $name
     *
     * @return string
     */
    private static function get_svg_icons($name): string {
        $icon = '';
        switch ($name){
            case'personen':
                $icon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-people" viewBox="0 0 16 16">
                         <path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1h8zm-7.978-1A.261.261 0 0 1 7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002a.274.274 0 0 1-.014.002H7.022zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0zM6.936 9.28a5.88 5.88 0 0 0-1.23-.247A7.35 7.35 0 0 0 5 9c-4 0-5 3-5 4 0 .667.333 1 1 1h4.216A2.238 2.238 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816zM4.92 10A5.493 5.493 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275zM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0zm3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/>
                         </svg>';
                break;
            case'sign-split':
                $icon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="black" class="bi bi-signpost-split" viewBox="0 0 16 16">
                         <path d="M7 7V1.414a1 1 0 0 1 2 0V2h5a1 1 0 0 1 .8.4l.975 1.3a.5.5 0 0 1 0 .6L14.8 5.6a1 1 0 0 1-.8.4H9v10H7v-5H2a1 1 0 0 1-.8-.4L.225 9.3a.5.5 0 0 1 0-.6L1.2 7.4A1 1 0 0 1 2 7h5zm1 3V8H2l-.75 1L2 10h6zm0-5h6l.75-1L14 3H8v2z"/>
                         </svg>';
                break;
            case'square':
                $icon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="black" class="er-chat-square-text" viewBox="0 0 16 16">
                         <path d="M14 1a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1h-2.5a2 2 0 0 0-1.6.8L8 14.333 6.1 11.8a2 2 0 0 0-1.6-.8H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2.5a1 1 0 0 1 .8.4l1.9 2.533a1 1 0 0 0 1.6 0l1.9-2.533a1 1 0 0 1 .8-.4H14a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/>
                         <path d="M3 3.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zM3 6a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9A.5.5 0 0 1 3 6zm0 2.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5z"/>
                          </svg>';
                break;
            case 'cast':
                 $icon = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cast" viewBox="0 0 16 16">
                          <path d="m7.646 9.354-3.792 3.792a.5.5 0 0 0 .353.854h7.586a.5.5 0 0 0 .354-.854L8.354 9.354a.5.5 0 0 0-.708 0z"/>
                          <path d="M11.414 11H14.5a.5.5 0 0 0 .5-.5v-7a.5.5 0 0 0-.5-.5h-13a.5.5 0 0 0-.5.5v7a.5.5 0 0 0 .5.5h3.086l-1 1H1.5A1.5 1.5 0 0 1 0 10.5v-7A1.5 1.5 0 0 1 1.5 2h13A1.5 1.5 0 0 1 16 3.5v7a1.5 1.5 0 0 1-1.5 1.5h-2.086l-1-1z"/>
                          </svg>';
                break;
            default:
        }
        return 'data:image/svg+xml;base64,'. base64_encode($icon);

    }
}
