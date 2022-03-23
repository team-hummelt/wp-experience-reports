<?php
namespace Experience\Reports;

use Wp_Experience_Reports;
use WP_Query;

/**
 * ADMIN Post-Selector Gutenberg Callback
 *
 * @since      1.0.0
 * @package    Wp_Experience_Reports
 * @subpackage Wp_Experience_Reports/includes
 * @author     Jens Wiecker <email@jenswiecker.de>
 */
defined('ABSPATH') or die();

class Experience_Reports_Block_Callback {

    /**
     * @param $attributes
     *
     * @return false|string|void
     */
    public static function callback_experience_report_block($attributes) {

        if ($attributes) {
            ob_start();
            echo '<div class="experience-reports">';
            add_filter('render_block', array(Render_Experience_Reports_Callback_Templates::class, 'render_core_experience_reports_callback'), 0, 2);
            apply_filters(WP_EXPERIENCE_REPORTS_BASENAME.'/render_callback_template', $attributes);
            echo '</div>';
            return ob_get_clean();
        }
    }

    public static function callback_experience_report_filter($attributes){
        ob_start();
        echo '<div class="experience-reports">';
        //add_filter('render_block', array(Render_Experience_Reports_Callback_Templates::class, 'render_core_experience_reports_callback'), 0, 2);
         apply_filters(WP_EXPERIENCE_REPORTS_BASENAME.'/render_callback_select_filter', $attributes);
         echo '</div>';
        return ob_get_clean();
    }

    /**
     * @param $attributes
     *
     * @return false|string|void
     */
    public static function callback_experience_report_gallery_block($attributes) {
        if ($attributes) {
            ob_start();
            if(defined('REPORTS_GALLERY_BASENAME')){
                echo '<div class="experience-reports">';
                apply_filters(REPORTS_GALLERY_BASENAME.'/load_galerie_templates', $attributes);
                echo '</div>';
            }
            return ob_get_clean();
        }
    }
}