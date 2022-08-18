<?php

namespace Experience\Reports;

use Wp_Experience_Reports;

defined('ABSPATH') or die();

/**
 * Define the Admin Template Folder functionality.
 *
 * Loads and defines the Admin Ajax files for this plugin
 *
 *
 * @link       https://wwdh.de/
 * @since      1.0.0
 */

/**
 * Define the Shortcodes functionality.
 *
 * Loads and defines the Admin Ajax files for this plugin
 *
 * @since      1.0.0
 * @package    Wp_Experience_Reports
 * @subpackage Wp_Experience_Reports/includes
 * @author     Jens Wiecker <email@jenswiecker.de>
 */
class WP_Experience_Reports_Folder_Three
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
     * The Folder .
     *
     * @since    1.0.0
     * @access   private
     * @var      string $folder
     */
    private string $folder;


    /**
     * The Folder .
     *
     * @since    1.0.0
     * @access   private
     * @var      array $files
     */
    private array $files;


    private  $dir;

    /**
     * The Folder Path.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $path The ID of this plugin.
     */
    private string $path;


    /**
     * Store plugin main class to allow public access.
     *
     * @since    1.0.0
     * @access   private
     * @var Wp_Experience_Reports $main The main class.
     */
    private Wp_Experience_Reports $main;

    public function __construct(string $basename, Wp_Experience_Reports $main, string $path)
    {

        $this->basename = $basename;
        $this->main = $main;
        $this->path = $path;

        $this->files = array();
        if ( file_exists( $path ) ) {
            if ( $path[ strlen( $path ) - 1 ] == '/' ) {
                $this->folder = $path;
            } else {
                $this->folder = $path . '/';
            }

            $this->dir = opendir( $path );
            while ( ( $file = readdir( $this->dir ) ) != false ) {
                $this->files[] = $file;
            }
            closedir( $this->dir );
        }

    }

    function create_tree() {

        if ( $this->files && count( $this->files ) > 2 ) {
            natcasesort( $this->files );
            $list = '<ul class="filetree" style="display: none;">';
            // Group folders first
            foreach ( $this->files as $file ) {
                if ( file_exists( $this->folder . $file ) && $file != '.' && $file != '..' && is_dir( $this->folder . $file ) ) {
                    $root = htmlentities($this->folder . $file );
                    $a = strlen(htmlentities($this->main->get_twig_template_dir()));
                    $e = strlen($root);
                    $selectPath = substr($root,$a,$e) . DIRECTORY_SEPARATOR;
                    $list .= '<li class="folder collapsed"><a data-folder="'.$selectPath.'" href="#" rel="' . htmlentities( $this->folder . $file ) . '/">' . htmlentities( $file ) . '</a></li>';
                }
            }
            // Group all files
            foreach ( $this->files as $file ) {
                $selectPath = '';
                if ( file_exists( $this->folder . $file ) && $file != '.' && $file != '..' && ! is_dir( $this->folder . $file ) ) {
                    //JOB FILES AUSGEBLENDET
                    $a = strlen(htmlentities($this->main->get_twig_template_dir()));
                    $e = strlen($this->path);
                    if($this->path){
                        $selectPath = substr($this->path,$a+1,$e);
                    }

                    $ext = preg_replace('/^.*\./', '', $file);
                    $list .= '<li class="file ext_' . $ext . '">
                    <div class="d-flex">
                    <a class="flex-fill link-file" href="#" rel="' . htmlentities( $this->folder . $file ) . '">' . htmlentities( $file ) . '</a>
                    <div class="ms-auto">
                     <button data-method="load-template-file" data-file="'.$file.'" data-folder="'.$selectPath.'" class="btn-show-file btn btn-sm btn-three-list border"><i class="bi bi-card-text"></i></button>
                    <!--<button class="btn  btn-sm border"><i class="bi bi-download"></i></button>-->
                    </div>
                    </div>
                    </li>';
                }
            }
            $list .= '</ul>';
            return $list;
        }
    }
}