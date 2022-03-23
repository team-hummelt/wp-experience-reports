<?php

namespace Experience\Reports;

use Wp_Experience_Reports;
defined('ABSPATH') or die();

/**
 * ADMIN Gutenberg Sidebar
 * @package Hummelt & Partner WordPress-Plugin
 * Copyright 2022, Jens Wiecker
 * License: Commercial - goto https://www.hummelt-werbeagentur.de/
 *
 * @Since 1.0.0
 */
class Register_Experience_Reports_Gutenberg_Tools
{

    protected Wp_Experience_Reports $main;
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
     * @param string $plugin_name
     * @param string $version
     * @param Wp_Experience_Reports $main
     */
    public function __construct(string $plugin_name, string $version, Wp_Experience_Reports $main)
    {

        $this->basename = $plugin_name;
        $this->version = $version;
        $this->main = $main;

    }

    public function experience_reports_posts_sidebar_meta_fields(): void
    {

        register_meta(
            'post',
            '_reports_cover_image_meta',
            array(
                'type' => 'string',
                'object_subtype' => 'experience_reports',
                'single' => true,
                'show_in_rest' => true,
                'default' => json_encode(['id' => 0, 'url' => '', 'width' => '', 'height' => '']),
                'auth_callback' => array($this, 'sidebar_permissions_check')
            )
        );

        register_meta(
            'post',
            '_reports_gallery_select',
            array(
                'type' => 'number',
                'object_subtype' => 'experience_reports',
                'single' => true,
                'show_in_rest' => true,
                'default' => 0,
                'sanitize_callback' => 'sanitize_text_field',
                'auth_callback' => array($this, 'sidebar_permissions_check')
            )
        );

        register_meta(
            'post',
            '_experience_reports_section_one_headline',
            array(
                'type' => 'string',
                'object_subtype' => 'experience_reports',
                'single' => true,
                'show_in_rest' => true,
                'default' => 'Land / Stadt',
                'sanitize_callback' => '',
                'auth_callback' => array($this, 'sidebar_permissions_check')
            )
        );

        register_meta(
            'post',
            '_experience_reports_section_one_content',
            array(
                'type' => 'string',
                'object_subtype' => 'experience_reports',
                'single' => true,
                'show_in_rest' => true,
                'default' => '',
                'sanitize_callback' => '',
                'auth_callback' => array($this, 'sidebar_permissions_check')
            )
        );

        register_meta(
            'post',
            '_experience_reports_section_one_is_date',
            array(
                'type'              => 'boolean',
                'object_subtype' => 'experience_reports',
                'single'            => true,
                'show_in_rest'      => true,
                'default'           => 0,
                'sanitize_callback' => '',
                'auth_callback' => array($this, 'sidebar_permissions_check')
            )
        );

        register_meta(
            'post',
            '_experience_reports_section_two_headline',
            array(
                'type' => 'string',
                'object_subtype' => 'experience_reports',
                'single' => true,
                'show_in_rest' => true,
                'default' => 'Beruf / Ausbildung / Studium',
                'sanitize_callback' => 'wp_kses',
                'auth_callback' => array($this, 'sidebar_permissions_check')
            )
        );

        register_meta(
            'post',
            '_experience_reports_section_two_content',
            array(
                'type' => 'string',
                'object_subtype' => 'experience_reports',
                'single' => true,
                'show_in_rest' => true,
                'default' => '',
                'sanitize_callback' => '',
                'auth_callback' => array($this, 'sidebar_permissions_check')
            )
        );

        register_meta(
            'post',
            '_experience_reports_section_two_is_date',
            array(
                'type'              => 'boolean',
                'object_subtype' => 'experience_reports',
                'single'            => true,
                'show_in_rest'      => true,
                'default'           => 0,
                'sanitize_callback' => 'sanitize_text_field',
                'auth_callback' => array($this, 'sidebar_permissions_check')
            )
        );

        register_meta(
            'post',
            '_experience_reports_section_three_headline',
            array(
                'type' => 'string',
                'object_subtype' => 'experience_reports',
                'single' => true,
                'show_in_rest' => true,
                'default' => '',
                'sanitize_callback' => '',
                'auth_callback' => array($this, 'sidebar_permissions_check')
            )
        );

        register_meta(
            'post',
            '_experience_reports_section_three_content',
            array(
                'type' => 'string',
                'object_subtype' => 'experience_reports',
                'single' => true,
                'show_in_rest' => true,
                'default' => '',
                'sanitize_callback' => '',
                'auth_callback' => array($this, 'sidebar_permissions_check')
            )
        );

        register_meta(
            'post',
            '_experience_reports_section_three_is_date',
            array(
                'type'              => 'boolean',
                'object_subtype' => 'experience_reports',
                'single'            => true,
                'show_in_rest'      => true,
                'default'           => 0,
                'sanitize_callback' => 'sanitize_text_field',
                'auth_callback' => array($this, 'sidebar_permissions_check')
            )
        );

        register_meta(
            'post',
            '_experience_reports_section_four_headline',
            array(
                'type' => 'string',
                'object_subtype' => 'experience_reports',
                'single' => true,
                'show_in_rest' => true,
                'default' => '',
                'sanitize_callback' => '',
                'auth_callback' => array($this, 'sidebar_permissions_check')
            )
        );

        register_meta(
            'post',
            '_experience_reports_section_four_content',
            array(
                'type' => 'string',
                'object_subtype' => 'experience_reports',
                'single' => true,
                'show_in_rest' => true,
                'default' => '',
                'sanitize_callback' => '',
                'auth_callback' => array($this, 'sidebar_permissions_check')
            )
        );

        register_meta(
            'post',
            '_experience_reports_section_four_is_date',
            array(
                'type'              => 'boolean',
                'object_subtype' => 'experience_reports',
                'single'            => true,
                'show_in_rest'      => true,
                'default'           => 0,
                'sanitize_callback' => 'sanitize_text_field',
                'auth_callback' => array($this, 'sidebar_permissions_check')
            )
        );

        register_meta(
            'post',
            '_experience_reports_section_five_headline',
            array(
                'type' => 'string',
                'object_subtype' => 'experience_reports',
                'single' => true,
                'show_in_rest' => true,
                'default' => '',
                'sanitize_callback' => '',
                'auth_callback' => array($this, 'sidebar_permissions_check')
            )
        );

        register_meta(
            'post',
            '_experience_reports_section_five_content',
            array(
                'type' => 'string',
                'object_subtype' => 'experience_reports',
                'single' => true,
                'show_in_rest' => true,
                'default' => '',
                'sanitize_callback' => '',
                'auth_callback' => array($this, 'sidebar_permissions_check')
            )
        );

        register_meta(
            'post',
            '_experience_reports_section_five_is_date',
            array(
                'type'              => 'boolean',
                'object_subtype' => 'experience_reports',
                'single'            => true,
                'show_in_rest'      => true,
                'default'           => 0,
                'sanitize_callback' => 'sanitize_text_field',
                'auth_callback' => array($this, 'sidebar_permissions_check')
            )
        );

        register_meta(
            'post',
            '_experience_reports_section_excerpt',
            array(
                'type' => 'string',
                'object_subtype' => 'experience_reports',
                'single' => true,
                'show_in_rest' => true,
                'default' => '',
                'sanitize_callback' => '',
                'auth_callback' => array($this, 'sidebar_permissions_check')
            )
        );

        register_meta(
            'post',
            '_experience_reports_from',
            array(
                'type' => 'string',
                'object_subtype' => 'experience_reports',
                'single' => true,
                'show_in_rest' => true,
                'default' => (string) date('Y-m-d', current_time('timestamp')),
                'sanitize_callback' => 'sanitize_text_field',
                'auth_callback' => array($this, 'sidebar_permissions_check')
            )
        );

        register_meta(
            'post',
            '_experience_reports_to',
            array(
                'type' => 'string',
                'object_subtype' => 'experience_reports',
                'single' => true,
                'show_in_rest' => true,
                'default' => '',
                'sanitize_callback' => 'sanitize_text_field',
                'auth_callback' => array($this, 'sidebar_permissions_check')
            )
        );

        register_meta(
            'post',
            '_experience_reports_date_format',
            array(
                'type' => 'string',
                'object_subtype' => 'experience_reports',
                'single' => true,
                'show_in_rest' => true,
                'default' => '1',
                'sanitize_callback' => 'sanitize_text_field',
                'auth_callback' => array($this, 'sidebar_permissions_check')
            )
        );

        register_meta(
            'post',
            '_experience_reports_image_option',
            array(
                'type' => 'string',
                'object_subtype' => 'experience_reports',
                'single' => true,
                'show_in_rest' => true,
                'default' => '1',
                'sanitize_callback' => 'sanitize_text_field',
                'auth_callback' => array($this, 'sidebar_permissions_check')
            )
        );
    }

    /**
     * Register Experience Report GUTENBERG SCRIPTS
     *
     * @since    1.0.0
     */
    public function wp_experience_report_register_sidebar(): void
    {

        $plugin_asset = require WP_EXPERIENCE_REPORTS_GUTENBERG_DIR . 'npm-sidebar/build/index.asset.php';
        wp_register_script(
            'wp-experience-report-sidebar',
            plugins_url($this->basename) . '/admin/gutenberg/npm-sidebar/build/index.js',
            $plugin_asset['dependencies'], $plugin_asset['version'], true
        );

        /*  if (function_exists('wp_set_script_translations')) {
              wp_set_script_translations('wp-experience-report-sidebar', 'hupa-teams', HUPA_TEAMS_GUTENBERG_LANGUAGE);
          }*/

        wp_register_script('wp-experience-report-localize', '', [], $plugin_asset['version'], true);
        wp_enqueue_script('wp-experience-report-localize');
        wp_localize_script('wp-experience-report-localize',
            'ERRestObj',
            array(
                'url' => esc_url_raw(rest_url('wp-experience-report/v1/')),
                'nonce' => wp_create_nonce('wp_rest')
            )
        );
    }

    public function wp_experience_report_sidebar_script_enqueue()
    {
        wp_enqueue_script('wp-experience-report-sidebar');
        wp_enqueue_style('wp-experience-report-sidebar-style');
        wp_enqueue_style(
            'wp-experience-report-sidebar-style',
            plugins_url($this->basename) . '/admin/gutenberg/npm-sidebar/build/index.css', array(), $this->version);
    }

    /**
     * Register TAM MEMBERS REGISTER GUTENBERG BLOCK TYPE
     *
     * @since    1.0.0
     */
    public function register_experience_report_block_type()
    {
        register_block_type('wwdh/experience-reports-block', array(
            'render_callback' => [Experience_Reports_Block_Callback::class, 'callback_experience_report_block'],
            'editor_script' => 'experience-reports-gutenberg-block',
        ));

    }

    /**
     * REGISTER TEAM MEMBERS GUTENBERG SCRIPTS
     *
     * @since    1.0.0
     */
    public function experience_report_block_type_scripts(): void
    {
        $plugin_asset = require WP_EXPERIENCE_REPORTS_GUTENBERG_DIR . 'npm-gutenberg-block/build/index.asset.php';

        wp_enqueue_script(
            'experience-reports-gutenberg-block',
            plugins_url($this->basename) . '/admin/gutenberg/npm-gutenberg-block/build/index.js',
            $plugin_asset['dependencies'], $plugin_asset['version'], true
        );

        wp_enqueue_style(
            'experience-reports-gutenberg-block',
            plugins_url($this->basename) . '/admin/gutenberg/npm-gutenberg-block/build/index.css', array(), $this->version
        );
    }

    /**
     * Register TAM MEMBERS REGISTER GUTENBERG BLOCK TYPE
     *
     * @since    1.0.0
     */
    public function register_experience_report_category_block_type()
    {
        register_block_type('wwdh/experience-reports-category', array(
            'render_callback' => [Experience_Reports_Block_Callback::class, 'callback_experience_report_category'],
            'editor_script' => 'experience-reports-gutenberg-category',
        ));

    }

    /**
     * REGISTER TEAM MEMBERS GUTENBERG SCRIPTS
     *
     * @since    1.0.0
     */
    public function experience_report_block_category_scripts(): void
    {

        $plugin_asset = require WP_EXPERIENCE_REPORTS_GUTENBERG_DIR . 'npm-kategorie-block/build/index.asset.php';

        wp_enqueue_script(
            'experience-reports-gutenberg-category',
            plugins_url($this->basename) . '/admin/gutenberg/npm-kategorie-block/build/index.js',
            $plugin_asset['dependencies'], $plugin_asset['version'], true
        );

        wp_enqueue_style(
            'experience-reports-gutenberg-category',
            plugins_url($this->basename) . '/admin/gutenberg/npm-kategorie-block/build/index.css', array(), $this->version
        );
    }

    /**
     * Register TAM MEMBERS REGISTER GUTENBERG BLOCK TYPE
     *
     * @since    1.0.0
     */
    public function register_experience_report_filter_block_type()
    {
        register_block_type('wwdh/experience-reports-filter', array(
            'render_callback' => [Experience_Reports_Block_Callback::class, 'callback_experience_report_filter'],
            'editor_script' => 'experience-reports-gutenberg-filter',
        ));
    }

    /**
     * REGISTER TEAM MEMBERS GUTENBERG SCRIPTS
     *
     * @since    1.0.0
     */
    public function experience_report_block_filter_scripts(): void
    {

        $plugin_asset = require WP_EXPERIENCE_REPORTS_GUTENBERG_DIR . 'npm-filter-block/build/index.asset.php';

        wp_enqueue_script(
            'experience-reports-gutenberg-filter',
            plugins_url($this->basename) . '/admin/gutenberg/npm-filter-block/build/index.js',
            $plugin_asset['dependencies'], $plugin_asset['version'], true
        );

        wp_enqueue_style(
            'experience-reports-gutenberg-filter',
            plugins_url($this->basename) . '/admin/gutenberg/npm-filter-block/build/index.css', array(), $this->version
        );
    }


    /**
     * Register TAM MEMBERS REGISTER GUTENBERG BLOCK TYPE
     *
     * @since    1.0.0
     */
    public function register_experience_report_gallery_block_type()
    {
        register_block_type('wwdh/experience-reports-gallery-block', array(
            'render_callback' => [Experience_Reports_Block_Callback::class, 'callback_experience_report_gallery_block'],
            'editor_script' => 'experience-reports-gutenberg-gallery-block',
        ));
    }

    /**
     * REGISTER TEAM MEMBERS GUTENBERG SCRIPTS
     *
     * @since    1.0.0
     */
    public function experience_report_gallery_block_type_scripts(): void
    {

        $plugin_asset = require WP_EXPERIENCE_REPORTS_GUTENBERG_DIR . 'npm-gallery/build/index.asset.php';

        wp_enqueue_script(
            'experience-reports-gutenberg-gallery-block',
            plugins_url($this->basename) . '/admin/gutenberg/npm-gallery/build/index.js',
            $plugin_asset['dependencies'], $plugin_asset['version'], true
        );

        wp_enqueue_style(
            'experience-reports-gutenberg-gallery-block',
            plugins_url($this->basename) . '/admin/gutenberg/npm-gallery/build/index.css', array(), $this->version
        );
    }

    /**
     * Check if a given request has access.
     *
     * @return bool
     */
    public function sidebar_permissions_check(): bool
    {
        return current_user_can('edit_posts');
    }
}