<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://wwdh.de
 * @since      1.0.0
 *
 * @package    Wp_Experience_Reports
 * @subpackage Wp_Experience_Reports/public
 */

use Experience\Reports\Experience_Reports_Public_Ajax;

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Wp_Experience_Reports
 * @subpackage Wp_Experience_Reports/public
 * @author     Jens Wiecker <email@jenswiecker.de>
 */
class Wp_Experience_Reports_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $basename;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

    /**
     * Store plugin main class to allow public access.
     *
     * @since    1.0.0
     * @access   private
     * @var Wp_Experience_Reports $main The main class.
     */
    private Wp_Experience_Reports $main;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param      string    $plugin_name The name of the plugin.
	 * @param string $version    The version of this plugin.
	 *@since    1.0.0
	 */
	public function __construct(string $plugin_name, string $version, Wp_Experience_Reports $main ) {

		$this->basename = $plugin_name;
		$this->version = $version;
        $this->main = $main;

	}

    /**
     * Register Experience Reports AJAX ADMIN RESPONSE HANDLE
     *
     * @since    1.0.0
     */
    public function prefix_ajax_EReportPublicHandle(): void
    {
        check_ajax_referer('experience_reports_public_handle');
        require_once 'ajax/class_experience_reports_public_ajax.php';
        $publicAjaxHandle = new Experience_Reports_Public_Ajax($this->basename, $this->version, $this->main);
        wp_send_json($publicAjaxHandle->experience_reports_public_ajax_handle());
    }

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {


        $title_nonce = wp_create_nonce('experience_reports_public_handle');
        wp_register_script('experience-reports-public-ajax-script', '', [], '', true);
        wp_enqueue_script('experience-reports-public-ajax-script');
        wp_localize_script('experience-reports-public-ajax-script', 'report_public_obj', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => $title_nonce,
        ));


        $modificated = date( 'YmdHi', filemtime( plugin_dir_path(dirname(__FILE__)) . 'includes/tools/bootstrap/bootstrap-icons.css' ) );
        $modificated = date( 'YmdHi', filemtime( plugin_dir_path(dirname(__FILE__)) . 'includes/tools/animate.min.css' ) );
        $modificated = date( 'YmdHi', filemtime( plugin_dir_path(dirname(__FILE__)) . 'includes/tools/bootstrap/bootstrap.min.css' ) );
        $modificated = date( 'YmdHi', filemtime( plugin_dir_path(dirname(__FILE__)) . 'includes/tools/bootstrap/bootstrap.bundle.min.js' ) );
        $modificated = date( 'YmdHi', filemtime( plugin_dir_path(dirname(__FILE__)) . 'public/css/wp-experience-report-public.css' ) );
        $modificated = date( 'YmdHi', filemtime( plugin_dir_path(dirname(__FILE__)) . 'public/js/wp-experience-reports-public.js' ) );

        wp_enqueue_script( $this->basename, plugin_dir_url( __FILE__ ) . 'js/wp-experience-reports-public.js', array(), $modificated, true );
        wp_enqueue_style($this->basename . '-bootstrap-icons', plugin_dir_url(__DIR__) . 'includes/tools/bootstrap/bootstrap-icons.css', array(), $modificated, 'all');
        wp_enqueue_style($this->basename . '-animate', plugin_dir_url(__DIR__) . 'includes/tools/animate.min.css', array(), $modificated, 'all');
        wp_enqueue_style($this->basename . '-bootstrap', plugin_dir_url(__DIR__) . 'includes/tools/bootstrap/bootstrap.min.css', array(), $modificated, 'all');
        wp_enqueue_script($this->basename . '-bootstrap-bundle', plugin_dir_url(__DIR__) . 'includes/tools/bootstrap/bootstrap.bundle.min.js', array(), $modificated, true);
        wp_enqueue_style( $this->basename.'-reports', plugin_dir_url( __FILE__ ) . 'css/wp-experience-report-public.css', array(), $modificated, 'all' );
    }
}
