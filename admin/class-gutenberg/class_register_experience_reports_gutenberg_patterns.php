<?php

namespace Experience\Reports;

use Wp_Experience_Reports;


defined('ABSPATH') or die();

/**
 * ADMIN Gutenberg Patterns
 * @package Hummelt & Partner WordPress-Plugin
 * Copyright 2022, Jens Wiecker
 * License: Commercial - goto https://www.hummelt-werbeagentur.de/
 *
 * @Since 1.0.0
 */
class Register_Experience_Reports_Gutenberg_Patterns
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
     * TRAIT of Default Settings.
     * @since    1.0.0
     */
    use Trait_Extension_Defaults;


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

    /**
     * Register WP_TEAM Block Patterns
     *
     * @since    1.0.0
     */
    public function register_gutenberg_patterns()
    {
        $tempDir = plugin_dir_path((__FILE__)) . 'gutenberg-block-patterns' . DIRECTORY_SEPARATOR;
        $patternOne = file_get_contents($tempDir . 'TemplateOne.html');
      //  $patternOne = str_replace('###PLACEHOLDERIMAGE###', plugins_url(HUPA_TEAMS_BASENAME).'/admin/images/placeholder-voll.png', $patternOne);
        register_block_pattern(
            'hupa/team-members-block-pattern',
            [
                'title' => __('Reports Template one', 'wp-experience-report'),
                'description' => _x('Template one for team members', 'Block pattern description', 'wp-experience-report'),
                'content' => $patternOne,
                'categories' => [
                    'wwdh/experience-reports-block-patterns',
                ],
            ],
        );

        $args = array(
            'posts_per_page'   => -1,
            'orderby'          => 'menu_order',
            'order'            => 'ASC',
            'post_type'        => 'experience_designs',
            'post_status'      => 'publish',
            'suppress_filters' => true
        );

        $items = get_posts( $args );

        foreach ( $items as $item ) {
            $ID = $item->ID;
            $content = get_post_field( 'post_content', $ID );
            $title = $item->post_title." Vorlage";
            $slug = $item->post_name."-pattern";

            register_block_pattern(
                $slug,
                array(
                    'title'       => $title,
                    'description' => 'Experience Report Vorlagen',
                    'content'     => $content,
                    'categories' => [
                        'hupa/experience-report-block-patterns',
                    ],
                )
            );
        }
    }

    /**
     * Register WP_TEAM Block Pattern Category
     *
     * @since    1.0.0
     */
    public function register_block_pattern_category()
    {
        register_block_pattern_category(
            'hupa/experience-report-block-patterns',
            [
                'label' => __('Experience-Report', 'wp-experience-reports'),
            ]
        );
    }
}