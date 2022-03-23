<?php

namespace Experience\Reports;
/**
 * Default Plugin Settings
 *
 * @link       https://wwdh.de
 * @since      1.0.0
 *
 * @package    Wp_Experience_Reports
 * @subpackage Wp_Experience_Reports/includes
 */
defined('ABSPATH') or die();

/**
 * @since      1.0.0
 * @package    Wp_Experience_Reports
 * @subpackage Wp_Experience_Reports/includes
 * @author     Jens Wiecker <email@jenswiecker.de>
 */
trait WP_Experience_Reports_Defaults
{

    protected array $experience_settings;
    //Galerie Table
    protected string $table_slider = 'erg_two_slide';
    protected string $table_galerie = 'erg_two_galerie';
    protected string $table_galerie_images = 'erg_two_galerie_images';


    //Reports Table

    /**
     * @param string $args
     * @return array
     */
    protected function get_report_settings(string $args = ''): array
    {
        $this->experience_settings = [
            'beitrags-slider' => [
                'autoplay' => 1,
                'cover' => 1,
                'trim_space' => 'true',
                'auto_width' => 0,
                'auto_height' => 0,
                'arrows' => 0,
                'lazy_load' => 'nearby',
                'pause_on_hover' => 0,
                'pause_on_focus' => 0,
                'drag' => 0,
                'keyboard' => 0,
                'hover' => 0,
                'label' => 1,

                'img_link_aktiv' => 1,
                'select_design_option' => 0,
                'select_design_btn_link' => 1,
                'design_btn_aktiv' => 0,
                'design_btn_txt' => 'Button Beschriftung',
                'design_btn_css' => '',
                'design_link_tag_txt' => '',
                'design_text_aktiv' => 0,
                'design_titel_css' => '',
                'design_auszug_css' => '',
                'select_title_tag' => 1,
                'select_design_text' => 1,
                'design_container_height' => '450px',
                'inner_container_height' => '150px',

                'textauszug' => 0,
                'rewind' => 1,
                'speed' => 1200,
                'rewind_speed' => 2500,
                'fixed_width' => '',
                'fixed_height' => '',
                'height_ratio' => '',
                'start_index' => 0,
                'flick_power' => 500,
                'preload_pages' => 3,
                'pagination' => 0,
                'slide_focus' => 1,

                'pro_page_xs' => '',
                'pro_page_sm' => '',
                'pro_page_md' => '',
                'pro_page_lg' => '',
                'pro_page_xl' => '',
                'pro_page_xxl' => '',

                'gap_xs' => '',
                'gap_sm' => '',
                'gap_md' => '',
                'gap_lg' => '',
                'gap_xl' => '',
                'gap_xxl' => '',

                'width_xs' => '450px',
                'width_sm' => '450px',
                'width_md' => '450px',
                'width_lg' => '450px',
                'width_xl' => '450px',
                'width_xxl' => '450px',

                'height_xs' => '350px',
                'height_sm' => '350px',
                'height_md' => '350px',
                'height_lg' => '350px',
                'height_xl' => '350px',
                'height_xxl' => '350px',

                'slide_type' => 'fade',
                'pro_move' => 1,
                'pro_page' => 1,
                'gap' => '0',
                'width' => '450px',
                'height' => '350px',
                'intervall' => 8000,
                'focus' => '0',
            ]
        ];

        if ($args) {
            return $this->experience_settings[$args];
        } else {
            return $this->experience_settings;
        }
    }

}