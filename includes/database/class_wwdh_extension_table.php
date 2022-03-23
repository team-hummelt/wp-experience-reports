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
final class WWDH_Extension_Table
{
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
     *
     * @since    1.0.0
     */
    use Trait_Extension_Defaults;

    /**
     * Store plugin main class to allow public access.
     *
     * @since    1.0.0
     * @access   private
     * @var Wp_Experience_Reports $main The main class.
     */
    private Wp_Experience_Reports $main;


    /**
     * @param string $basename
     * @param Wp_Experience_Reports $main
     */
    public function __construct(string $basename, Wp_Experience_Reports $main)
    {
        $this->basename = $basename;
        $this->main = $main;
    }

    public function wwdh_get_extension($args, $fetchMethod = true): object
    {
        global $wpdb;
        $return = new stdClass();
        $return->status = false;
        $return->count = 0;
        $fetchMethod ? $fetch = 'get_results' : $fetch = 'get_row';
        $table = $wpdb->prefix . $this->table_experience_reports_extensions;
        $result = $wpdb->$fetch("SELECT * FROM $table $args");
        if (!$result) {
            return $return;
        }
        $fetchMethod ? $count = count($result) : $count = 1;
        $return->count = $count;
        $return->status = true;
        $return->record = $result;
        return $return;
    }

    /**
     * @param $record
     */
    public function wwdhSetExtension($record)
    {
        global $wpdb;
        $table = $wpdb->prefix . $this->table_experience_reports_extensions;
        $wpdb->insert(
            $table,
            array(
                'license' => $record->license,
                'folder' => $record->folder,
                'id_rsa' => $record->id_rsa,
                'aktiv' => $record->aktiv,
                'last_connect' => current_time('mysql'),
                'url_limit_aktiv' => $record->url_limit_aktiv,
                'url_id' => $record->url_id,
                'url_activated' => $record->url_activated,
            ),
            array('%s', '%s', '%s', '%d', '%s', '%d', '%s', '%d')
        );
    }

    /**
     * @param $record
     */
    public function wwdhUpdateExtensionError($record): void
    {
        global $wpdb;
        $table = $wpdb->prefix . $this->table_experience_reports_extensions;
        $wpdb->update(
            $table,
            array(
                'aktiv' => $record->aktiv,
                'errors' => $record->errors,
                'last_connect' => $record->last_connect,
            ),
            array('license' => $record->license),
            array(
                '%d', '%d', '%s',
            ),
            array('%s')
        );
    }

    /**
     * @param $record
     */
    public function wwdhUpdateExtensionIdRsa($record): void
    {
        global $wpdb;
        $table = $wpdb->prefix . $this->table_experience_reports_extensions;
        $wpdb->update(
            $table,
            array(
                'id_rsa' => $record->id_rsa,
            ),
            array('license' => $record->license),
            array(
                '%s',
            ),
            array('%s')
        );
    }

    /**
     * @param $record
     */
    public function wwdhUpdateExtensionLastConnect($record): void
    {
        global $wpdb;
        $table = $wpdb->prefix . $this->table_experience_reports_extensions;
        $wpdb->update(
            $table,
            array(
                'last_connect' => $record->last_connect,
                'errors' => $record->errors,
            ),
            array('license' => $record->license),
            array(
                '%s',
            ),
            array('%s')
        );
    }

    /**
     * @param $record
     */
    public function wwdhUpdateExtension($record): void
    {
        global $wpdb;
        $table = $wpdb->prefix . $this->table_experience_reports_extensions;
        $wpdb->update(
            $table,
            array(
                'folder' => $record->folder,
                'id_rsa' => $record->id_rsa,
                'last_connect' => $record->last_connect,
            ),
            array('license' => $record->license),
            array(
                '%s', '%s','%s',
            ),
            array('%s')
        );
    }

    /**
     * @param $record
     */
    public function wwdhUpdateActivatedExtension($record): void
    {
        global $wpdb;
        $table = $wpdb->prefix . $this->table_experience_reports_extensions;
        $wpdb->update(
            $table,
            array(
                'aktiv' => $record->aktiv,
                'url_limit_aktiv' => $record->url_limit_aktiv,
                'url_activated' => $record->url_activated,
                'last_connect' => $record->last_connect,
            ),
            array('license' => $record->license),
            array(
                '%d', '%d', '%d','%s',
            ),
            array('%s')
        );
    }

    /**
     * @param $license
     */
    public function wwdhDeleteExtension($license): void
    {
        global $wpdb;
        $table = $wpdb->prefix . $this->table_experience_reports_extensions;
        $wpdb->delete(
            $table,
            array(
                'license' => $license
            ),
            array('%s')
        );
    }
}