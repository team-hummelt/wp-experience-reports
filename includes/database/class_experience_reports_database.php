<?php

namespace Experience\Reports;
defined('ABSPATH') or die();


use stdClass;
use Wp_Experience_Reports;

/**
 * The Table Experience\Report Extension plugin class.
 *
 * @since      1.0.0
 * @package    Experience_Report
 * @subpackage Experience_Report/includes/database
 * @author     Jens Wiecker <email@jenswiecker.de>
 */

/**
 * The Table Experience\Report Extension plugin class.
 *
 * @since      1.0.0
 * @package    Experience_Report
 * @subpackage Experience_Report/includes/database
 * @author     Jens Wiecker <email@jenswiecker.de>
 */
final class Experience_Reports_Database
{

    /**
     * The current version of the DB-Version.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $dbVersion The current version of the database Version.
     */
    protected string $dbVersion;

    /**
     * The current version of the DB-Version.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $basename The current basename.
     */
    protected string $basename;


    /**
     * TRAIT of Default Settings.
     *
     * @since    1.0.0
     */
    use Trait_Extension_Defaults;
    use WP_Experience_Reports_Defaults;

    /**
     * @param $db_version
     * @param $basename
     */
    public function __construct($db_version, $basename)
    {
        $this->dbVersion = $db_version;
        $this->basename = $basename;

    }

    /**
     * Insert | Update Table Editor
     * INIT Function
     * @since 1.0.0
     */
    public function update_create_experience_reports_database()
    {
        if ($this->dbVersion !== get_option('jal_experience_reports_db_version')) {
            $this->create_experience_reports_database();
            update_option('jal_experience_reports_db_version', $this->dbVersion);
            $this->install_default_slider();
            //update_create_experience_reports_database
        }
    }

    private function install_default_slider(){
        global $wpdb;
        $table = $wpdb->prefix . 'erg_two_slide';
        $sliderTable = $wpdb->get_var( "SHOW TABLES LIKE '{$table}'" );
        if($sliderTable){
            $args = 'WHERE folder="experience-reports-gallery" AND aktiv=1';
            $isExtension = apply_filters($this->basename.'_get_extension',$args);
            if(is_object($isExtension) && $isExtension->status){
                $args = sprintf('WHERE slider_id="%s"',WP_EXPERIENCE_REPORTS_SLIDER_ID);
                $ps = apply_filters(REPORTS_GALLERY_BASENAME.'/post_selector_get_by_args', $args, false);
                if(!$ps->status){
                    $record = new stdClass();
                    $sliderSettings = $this->get_report_settings('beitrags-slider');
                    $record->slider_id = WP_EXPERIENCE_REPORTS_SLIDER_ID;
                    $record->bezeichnung = 'template-slider';
                    $record->data = json_encode($sliderSettings);
                    apply_filters(REPORTS_GALLERY_BASENAME.'/post_selector_set_slider', $record);
                }
            }
        }
    }

    /**
     *
     * CREATE Experience_Report Database
     * @since 1.0.0
     */
    private function create_experience_reports_database()
    {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        global $wpdb;

        $table_name = $wpdb->prefix . $this->table_experience_reports_extensions;
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table_name (
        license varchar(32) NOT NULL,
        folder varchar(128) NOT NULL UNIQUE,
        id_rsa varchar(8000) NOT NULL,
        aktiv tinyint(1) NOT NULL DEFAULT 1,
        url_limit_aktiv tinyint(1) NOT NULL,
        url_id varchar(32) NULL,
        url_activated tinyint(1) NOT NULL DEFAULT 1,
        errors tinyint(2) NOT NULL DEFAULT 0,
        last_connect varchar(28) NULL,
        last_update TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
       PRIMARY KEY (license)
     ) $charset_collate;";
        dbDelta($sql);
    }

}