<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://wwdh.de
 * @since      1.0.0
 *
 * @package    Wp_Experience_Reports
 * @subpackage Wp_Experience_Reports/includes
 */

use Experience\Reports\Experience_Report_Extensions;
use Experience\Reports\Experience_Reports_Block_Callback;
use Experience\Reports\Experience_Reports_Database;
use Experience\Reports\Experience_Reports_Public_API;
use Experience\Reports\Register_Experience_Reports_Endpoint;
use Experience\Reports\Register_Experience_Reports_Gutenberg_Patterns;
use Experience\Reports\Register_Experience_Reports_Gutenberg_Tools;
use Experience\Reports\Register_Product_License;
use Experience\Reports\Render_Experience_Reports_Callback_Templates;
use Experience\Reports\WP_Experience_Projects_Shortcodes;
use Experience\Reports\WP_Experience_Reports_Helper;
use Experience\Reports\WWDH_Extension_API;
use Experience\Reports\WWDH_Extension_Table;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Wp_Experience_Reports
 * @subpackage Wp_Experience_Reports/includes
 * @author     Jens Wiecker <email@jenswiecker.de>
 */
class Wp_Experience_Reports
{

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Wp_Experience_Reports_Loader $loader Maintains and registers all hooks for the plugin.
     */
    protected Wp_Experience_Reports_Loader $loader;

    /**
     * The Public API ID_RSA.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $id_rsa plugin API ID_RSA.
     */
    private string $id_rsa;

    /**
     * The Public API DIR.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $api_dir plugin API DIR.
     */
    private string $api_dir;

    /**
     * The Public Extension DIR.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $extension_dir plugin Extension DIR.
     */
    private string $extension_dir;

    /**
     * The Public Extension Preview DIR.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $extension_preview plugin Extension Preview DIR.
     */
    private string $extension_preview;

    /**
     * The Public plugin Twig Dir.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $twig_template_dir plugin Twig Dir.
     */
    private string $twig_template_dir;

    /**
     * TWIG autoload for PHP-Template-Engine
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Environment $twig TWIG autoload for PHP-Template-Engine
     */
    protected Environment $twig;

    /**
     * TWIG autoload for PHP-Template-Engine
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Environment $twig_templates TWIG autoload for PHP-Template-Engine
     */
    protected Environment $twig_templates;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $plugin_name The string used to uniquely identify this plugin.
     */
    protected string $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $version The current version of the plugin.
     */
    protected string $version = '';

    /**
     * The current database version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $db_version The current database version of the plugin.
     */
    protected string $db_version;

    /**
     * Store plugin main class to allow public access.
     *
     * @since    1.0.0
     * @var object The main class.
     */
    public object $main;

    /**
     * The plugin Slug Path.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_slug plugin Slug Path.
     */
    private string $plugin_slug;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct()
    {

        $this->plugin_name = WP_EXPERIENCE_REPORTS_BASENAME;
        $this->plugin_slug = WP_EXPERIENCE_REPORTS_SLUG_PATH;
        $this->main = $this;

        $plugin = get_file_data(plugin_dir_path(dirname(__FILE__)) . $this->plugin_name . '.php', array('Version' => 'Version'), false);
        if (!$this->version) {
            $this->version = $plugin['Version'];
        }

        if (defined('WP_EXPERIENCE_REPORTS_DB_VERSION')) {
            $this->db_version = WP_EXPERIENCE_REPORTS_DB_VERSION;
        } else {
            $this->db_version = '1.0.0';
        }

        if (is_file(WP_EXPERIENCE_REPORTS_ID_RSA_DIR . 'public_id_rsa')) {
            $id_rsa = file_get_contents(WP_EXPERIENCE_REPORTS_ID_RSA_DIR . 'public_id_rsa');
            $this->id_rsa = base64_encode($id_rsa);
        } else {
            $this->id_rsa = '';
        }

        $this->extension_preview = WP_EXPERIENCE_REPORTS_EXTENSION_PREVIEW_DIR;
        $this->extension_dir = WP_EXPERIENCE_REPORTS_EXTENSION_DIR;
        $this->api_dir = WP_EXPERIENCE_REPORTS_API_DIR;


        $this->check_dependencies();
        $this->load_dependencies();
        $this->set_locale();
        $this->experience_reports_database();
        $this->experience_reports_extension_database();
        $this->register_helper_class();

        $this->define_product_license_class();

        //Extensionen Twig
        $twig_loader = new FilesystemLoader(WP_EXPERIENCE_REPORTS_EXTENSION_DIR . 'templates');
        $this->twig = new Environment($twig_loader);

        // Callback Twig
        $this->twig_template_dir = WP_EXPERIENCE_REPORTS_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'twig-templates' ;
        $twig_callback_loader = new FilesystemLoader($this->twig_template_dir);
        $this->twig_templates = new Environment($twig_callback_loader);


        $this->register_experience_reports_endpoint();
        $this->register_team_members_render_callback();
        $this->register_experience_reports_render_callback();
        $this->register_gutenberg_patterns();
        //EXTENSION

        $this->wwdh_public_api();
        $this->experience_reports_extension_options();
        $this->wwdh_extension_api();
        $this->register_experience_report_gutenberg_sidebar();
        $this->register_plugin_shortcodes();
        $this->define_admin_hooks();
        $this->define_public_hooks();

    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Wp_Experience_Reports_Loader. Orchestrates the hooks of the plugin.
     * - Wp_Experience_Reports_i18n. Defines internationalization functionality.
     * - Wp_Experience_Reports_Admin. Defines all hooks for the admin area.
     * - Wp_Experience_Reports_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies()
    {

        /**
         * The trait for the default settings of the BS-Formular2
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/api/api-classes/trait_extension_defaults.php';

        /**
         * The trait for the default settings
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/trait_wp_experience_reports_defaults.php';

        /**
         * The class for the database of the  Wp_Experience_Reports
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/database/class_experience_reports_database.php';

        /**
         * The class for the database of the  Wp_Experience_Reports
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/database/class_wwdh_extension_table.php';

        /**
         * The class for the Extension Options of the Wp_Experience_Reports
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'extensions/class-wp-experience-reports-extensions.php';

        /**
         * The class responsible for defining all actions of the Public API.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/api/api-classes/class_experience_reports_public_api.php';

        /**
         * The class responsible for defining all actions of the Extension API.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/api/api-classes/class_wwdh_extension_api.php';

        /**
         * The Ajax Wp_Experience_Reports
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/ajax/class_experience_reports_admin_ajax.php';


        /**
         * The class responsible for defining WP REST API Routes
         * side of the site.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-gutenberg/class_register_experience_reports_endpoint.php';


        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-gutenberg/class_register_experience_reports_gutenberg_tools.php';

        /**
         * The class responsible for defining all actions of the Extension API.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-gutenberg/class_register_experience_reports_gutenberg_patterns.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wp-experience-reports-i18n.php';


        /**
         * The code that runs during plugin activation.
         * This action is documented in includes/class-hupa-teams-activator.php
         */
        require_once plugin_dir_path(dirname(__FILE__ ) ) . 'includes/class-wp-experience-reports-activator.php';


        /**
         * The class Helper
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class_wp_experience_reports_helper.php';

        /**
         * Update-Checker-Autoload
         * Git Update for Theme|Plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/update-checker/autoload.php';

        /**
         * // JOB The class responsible for defining all actions that occur in the license area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/license/class_register_product_license.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        if (is_file(plugin_dir_path(dirname(__FILE__)) . 'admin/class-wp-experience-reports-admin.php')) {
            require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-gutenberg/class_experience_reports_block_callback.php';
            require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-wp-experience-reports-admin.php';
        }

        /**
         * TWIG autoload for PHP-Template-Engine
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/Twig/autoload.php';

        /**
         * The class responsible for defining Callback Templates
         * side of the site.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-gutenberg/class_render_experience_reports_callback_templates.php';


        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-wp-experience-reports-public.php';


        /**
         * The class responsible for defining all Shortcodes
         * side of the site.
         */

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class_wp_experience_projects_shortcodes.php';


        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wp-experience-reports-loader.php';

        $this->loader = new Wp_Experience_Reports_Loader();

    }


    /**
     * Check PHP and WordPress Version
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function check_dependencies(): void
    {
        global $wp_version;
        if (version_compare(PHP_VERSION, WP_EXPERIENCE_REPORTS_PHP_VERSION, '<') || $wp_version < WP_EXPERIENCE_REPORTS_WP_VERSION) {
            $this->maybe_self_deactivate();
        }
    }

    /**
     * Self-Deactivate
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function maybe_self_deactivate(): void
    {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        deactivate_plugins($this->plugin_slug);
        add_action('admin_notices', array($this, 'self_deactivate_notice'));
    }

    /**
     * Self-Deactivate Admin Notiz
     * of the plugin.
     *
     * @since    1.0.0
     * @access   public
     */
    public function self_deactivate_notice(): void
    {
        echo sprintf('<div class="notice notice-error is-dismissible" style="margin-top:5rem"><p>' . __('This plugin has been disabled because it requires a PHP version greater than %s and a WordPress version greater than %s. Your PHP version can be updated by your hosting provider.', 'wp-experience-reports') . '</p></div>', WP_EXPERIENCE_REPORTS_PHP_VERSION, WP_EXPERIENCE_REPORTS_WP_VERSION);
        exit();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Wp_Experience_Reports_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale()
    {

        $plugin_i18n = new Wp_Experience_Reports_i18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');

    }


    /**
     * Register all the hooks related to the Gutenberg Sidebar functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function register_experience_report_gutenberg_sidebar() {
        $registerGBTools = new Register_Experience_Reports_Gutenberg_Tools($this->get_plugin_name(), $this->get_version(), $this->main);
        //Sidebar
        $this->loader->add_action( 'init', $registerGBTools, 'experience_reports_posts_sidebar_meta_fields' );
        $this->loader->add_action( 'init', $registerGBTools, 'wp_experience_report_register_sidebar' );
        $this->loader->add_action( 'enqueue_block_editor_assets', $registerGBTools, 'wp_experience_report_sidebar_script_enqueue' );
        //Block Type
        $this->loader->add_action( 'init', $registerGBTools, 'register_experience_report_block_type' );
        $this->loader->add_action( 'enqueue_block_editor_assets', $registerGBTools, 'experience_report_block_type_scripts' );
        //Block Category Type
       // $this->loader->add_action( 'init', $registerGBTools, 'register_experience_report_category_block_type' );
       // $this->loader->add_action( 'enqueue_block_editor_assets', $registerGBTools, 'experience_report_block_category_scripts' );
        //Filter Block
        $this->loader->add_action( 'init', $registerGBTools, 'register_experience_report_filter_block_type' );
        $this->loader->add_action( 'enqueue_block_editor_assets', $registerGBTools, 'experience_report_block_filter_scripts' );

        //Block Gallery
        global $experienceReportsExtensionDB;
        $args = 'WHERE folder="experience-reports-gallery" AND aktiv=1';
        $isGallery = $experienceReportsExtensionDB->wwdh_get_extension($args);
        if($isGallery->status){
            $this->loader->add_action( 'init', $registerGBTools, 'register_experience_report_gallery_block_type' );
            $this->loader->add_action( 'enqueue_block_editor_assets', $registerGBTools, 'experience_report_gallery_block_type_scripts' );
        }
    }

    /**
     * Register all the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_product_license_class()
    {

        if (!get_option('hupa_server_url')) {
            update_option('hupa_server_url', $this->get_license_config()->api_server_url);
        }
        global $product_license;
        $product_license = new Register_Product_License($this->get_plugin_name(), $this->get_version(), $this->get_license_config(), $this->main);
        $this->loader->add_action('init', $product_license, 'license_site_trigger_check');
        $this->loader->add_action('template_redirect', $product_license, 'license_callback_site_trigger_check');
    }

    /**
     * Register all the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function register_helper_class()
    {
        global $plugin_helper;
        $plugin_helper = new WP_Experience_Reports_Helper($this->get_plugin_name(), $this->get_version(), $this->main);
        $this->loader->add_filter($this->plugin_name . '/get_random_string', $plugin_helper, 'getERRandomString');
        $this->loader->add_filter($this->plugin_name . '/generate_random_id', $plugin_helper, 'getERGenerateRandomId',10,4);
        $this->loader->add_filter($this->plugin_name . '/array_to_object', $plugin_helper, 'ERArrayToObject');
        $this->loader->add_filter($this->plugin_name . '/object_to_array', $plugin_helper, 'object2array_recursive');
        $this->loader->add_filter($this->plugin_name . '/er_svg_icons', $plugin_helper, 'er_svg_icons', 10, 3);
        $this->loader->add_filter($this->plugin_name . '/FileSizeConvert', $plugin_helper, 'ExperienceReportsFileSizeConvert');
        $this->loader->add_filter($this->plugin_name . '/destroy_dir', $plugin_helper, 'wwdhDestroyDir');
        $this->loader->add_action( $this->plugin_name.'/user_roles_select', $plugin_helper, 'experience_reports_user_roles_select' );
        $this->loader->add_action( $this->plugin_name.'/date_format', $plugin_helper, 'experience_report_date_format',10, 2 );
        $this->loader->add_action( $this->plugin_name.'/get_post_attributes', $plugin_helper, 'get_report_post_attributes',10,3 );

        //Download Extension Previews
        $this->loader->add_action( $this->plugin_name.'/download_extension_previews', $plugin_helper, 'download_extension_previews' );
        //check is Table
        $this->loader->add_filter( $this->plugin_name.'/check_extension_table', $plugin_helper, 'check_report_extension_database_table' );

    }

    /**
     * Register all the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function register_plugin_shortcodes()
    {
        global $pluginShortCodes;
        $pluginShortCodes = new WP_Experience_Projects_Shortcodes($this->get_plugin_name(), $this->get_version(), $this->main,$this->twig_templates);

    }


    /**
     * Register all the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks()
    {

        if (!get_option('experience_reports_user_role')) {
            update_option('experience_reports_user_role', 'manage_options');
        }

        if (is_file(plugin_dir_path(dirname(__FILE__)) . 'admin/class-wp-experience-reports-admin.php') && get_option("{$this->plugin_name}_product_install_authorize")) {
            $plugin_admin = new Wp_Experience_Reports_Admin($this->get_plugin_name(), $this->get_version(), $this->main, $this->get_license_config(), $this->twig);

            $postTypes = new Wp_Experience_Reports_Activator();
            $this->loader->add_action('init', $postTypes, 'register_experience_reports_post_type');
            $this->loader->add_action('init', $postTypes, 'register_experience_taxonomies');


            $this->loader->add_action('init', $plugin_admin, 'set_experience_reports_update_checker');
            $this->loader->add_action('in_plugin_update_message-' . $this->plugin_name . '/' . $this->plugin_name . '.php', $plugin_admin, 'experience_reports_show_upgrade_notification', 10, 2);

            //JOB WARNING ADD Plugin Settings Link
            $this->loader->add_filter('plugin_action_links_' . $this->plugin_name . '/' . $this->plugin_name . '.php', $plugin_admin, 'experience_reports_plugin_add_action_link');

            //Admin Menu | AJAX
            $this->loader->add_action('admin_menu', $plugin_admin, 'register_experience_reports_menu');
            $this->loader->add_action('wp_ajax_EReportHandle', $plugin_admin, 'prefix_ajax_EReportHandle');
            $this->loader->add_action('wp_ajax_EReportAPIHandle', $plugin_admin, 'prefix_ajax_EReportAPIHandle');

            $this->loader->add_action('plugin_loaded', $plugin_admin, 'check_install_extension');

        }
    }

    /**
     * Register all the hooks related to the Gutenberg Plugins functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function register_experience_reports_render_callback()
    {
        if (is_file(plugin_dir_path(dirname(__FILE__)) . 'admin/class-wp-experience-reports-admin.php') && get_option("{$this->plugin_name}_product_install_authorize")) {
            global $experienceReportsCallback;
            $experienceReportsCallback = new Experience_Reports_Block_Callback();
        }
    }

    /**
     * Register all the hooks related to the Gutenberg Sidebar functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function register_team_members_render_callback() {
        global $registerTeamsRenderCallback;
        $registerTeamsRenderCallback = new Render_Experience_Reports_Callback_Templates($this->get_plugin_name(), $this->get_version(), $this->main, $this->twig_templates);
        $this->loader->add_filter($this->plugin_name.'/render_callback_template', $registerTeamsRenderCallback, 'render_callback_template');
        $this->loader->add_filter($this->plugin_name.'/get_experience_reports_meta_data', $registerTeamsRenderCallback, 'get_experience_reports_meta_data');
        $this->loader->add_filter($this->plugin_name.'/get_experience_posts_by_category', $registerTeamsRenderCallback, 'get_experience_posts_by_category',10,2);
        $this->loader->add_filter($this->plugin_name.'/render_callback_select_filter', $registerTeamsRenderCallback, 'render_callback_select_filter');

    }

    /**
     * Register all the hooks related to the Gutenberg Plugins functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function register_experience_reports_endpoint()
    {
        global $register_experience_endpoint;
        $register_experience_endpoint = new Register_Experience_Reports_Endpoint($this->get_plugin_name(), $this->get_version(), $this->main);
        $this->loader->add_action('rest_api_init', $register_experience_endpoint, 'register_experience_reports_routes');
        $this->loader->add_filter($this->plugin_name.'/get_custom_terms', $register_experience_endpoint, 'experience_reports_get_custom_terms');
    }

    /**
     * Register all the hooks related to the Gutenberg Sidebar functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function register_gutenberg_patterns() {
        $registerPatterns = new Register_Experience_Reports_Gutenberg_Patterns($this->get_plugin_name(), $this->get_version(), $this->main);

        //$this->loader->add_action( 'init', $registerPatterns, 'register_block_pattern_category' );
        //$this->loader->add_action( 'init', $registerPatterns, 'register_gutenberg_patterns' );
        $this->loader->add_filter( $this->plugin_name . '/get_template_select', $registerPatterns, 'get_template_gutenberg_select' );
    }


    /**
     * Register all the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks()
    {

        $plugin_public = new Wp_Experience_Reports_Public($this->get_plugin_name(), $this->get_version(), $this->main);
        $this->loader->add_action('wp_ajax_nopriv_EReportPublicHandle', $plugin_public, 'prefix_ajax_EReportPublicHandle');
        $this->loader->add_action('wp_ajax_EReportPublicHandle', $plugin_public, 'prefix_ajax_EReportPublicHandle');
        //$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
    }

    /**
     * Register all the DATABASE hooks
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function experience_reports_database()
    {
        global $experienceReportsDatabase;
        $experienceReportsDatabase = new Experience_Reports_Database($this->get_db_version(),$this->plugin_name);
        /**
         * Create Database
         * @since    1.0.0
         */
        $this->loader->add_action('init', $experienceReportsDatabase, 'update_create_experience_reports_database');
    }

    /**
     * Register Class Wp_Experience_Reports Database
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function experience_reports_extension_database()
    {
        global $experienceReportsExtensionDB;
        $experienceReportsExtensionDB = new WWDH_Extension_Table($this->get_plugin_name(), $this->main);

        $this->loader->add_filter($this->plugin_name . '_get_extension', $experienceReportsExtensionDB, 'wwdh_get_extension', 10, 2);
        $this->loader->add_action($this->plugin_name . '_set_extension', $experienceReportsExtensionDB, 'wwdhSetExtension');
        $this->loader->add_action($this->plugin_name . '_update_extension_error', $experienceReportsExtensionDB, 'wwdhUpdateExtensionError');
        $this->loader->add_action($this->plugin_name . '_update_extension', $experienceReportsExtensionDB, 'wwdhUpdateExtension');
        $this->loader->add_action($this->plugin_name . '_delete_extension', $experienceReportsExtensionDB, 'wwdhDeleteExtension');
        $this->loader->add_action($this->plugin_name . '_update_extension_last_connect', $experienceReportsExtensionDB, 'wwdhUpdateExtensionLastConnect');
        $this->loader->add_action($this->plugin_name . '_update_extension_id_rsa', $experienceReportsExtensionDB, 'wwdhUpdateExtensionIdRsa');
        $this->loader->add_action($this->plugin_name . '_update_activated_extension', $experienceReportsExtensionDB, 'wwdhUpdateActivatedExtension');

    }

    /**
     * Register all public api hooks
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function wwdh_public_api()
    {

        global $experienceReportsPublicApi;
        $experienceReportsPublicApi = new Experience_Reports_Public_API($this->get_version(), $this->get_plugin_name(), $this->main);

        /**
         * Get Public Support API Token
         * Input: support_id | support_secret
         * @since    1.0.0
         * return access_Token Object
         */
        $this->loader->add_filter('get_public_support_api_token', $experienceReportsPublicApi, 'get_wwdh_public_support_api_token', 10, 2);

        /**
         * Get Public API Resource
         * return API POST SUPPORT Resource Object
         * @since    1.0.0
         */
        $this->loader->add_filter('wwdh_support_api_resource', $experienceReportsPublicApi, 'wwdh_support_api_post_resource', 10, 3);

        /**
         * Get Public API Resource
         * return API POST JWT Resource Object
         * @since    1.0.0
         */

        //WARNING DELETE ?
        $this->loader->add_filter('get_public_resource_method', $experienceReportsPublicApi, 'wwdh_get_public_resource_method', 10, 3);


        // TODO API DOWNLOAD WARNING DELETE ?
        $this->loader->add_filter($this->plugin_name.'/wwdh_api_download', $experienceReportsPublicApi, 'wwdh_api_public_download', 10, 3);
        /**
         * Get Public API Ajax Resource Formular
         * @since    1.0.0
         */
        $this->loader->add_filter('get_public_api_select_commands', $experienceReportsPublicApi, 'wwdh_public_api_select_commands', 10, 3);

        /**
         * Get Public API Formular Language
         * @since    1.0.0
         */
        $this->loader->add_filter($this->plugin_name . '/get_ajax_language', $experienceReportsPublicApi, 'wwdh_ajax_language');

    }


    /**
     * Basic settings Extension Options for Wp_Experience_Reports
     *
     * Uses the BS_Formular2_Extensions class to register the extension options and hook.
     * register with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */

    private function experience_reports_extension_options()
    {
        global $extensionOptions;
        $extensionOptions = new Experience_Report_Extensions($this->get_plugin_name(), $this->get_version(), $this->main, $this->twig);
        //$extensionOptions->bs_formular2_check_extension_preview_updates();

        // TODO API-LOG
        $this->loader->add_action('set_api_log', $extensionOptions, 'wwdh_set_api_log', 10, 2);
        // TODO CHECK EXTENSION PREVIEW
        $this->loader->add_action($this->plugin_name.'/check_extension_preview_updates', $extensionOptions, 'wwdh_check_extension_preview_updates');
        // TODO Load Preview Extensions Data
        $this->loader->add_filter('get_extension_preview_url_data', $extensionOptions, 'wwdh_get_extension_preview_url_data');

        // TODO Twig HTML Template Loader
        $this->loader->add_action('twig_template_loader', $extensionOptions, 'wwdh_twig_template_loader');

        // TODO Return Description Template
        $this->loader->add_filter('get_extension_description_template', $extensionOptions, 'get_wwdh_extension_description_template', 10, 2);
        //TODO JOB CHECK LIZENZ
        $this->loader->add_filter($this->plugin_name.'/check_extensions_installs', $extensionOptions, 'wwdh_check_extensions_installs', 10, 2);

        $this->loader->add_action('check_delete_update_api_extension', $extensionOptions, 'wwdh_check_api_extension');

        // TODO Extension Language URLS
        $this->loader->add_filter('get_preview_language_url', $extensionOptions, 'wwdh_get_extension_preview_language_url');
        // TODO GET Extension Folder

        // TODO Activate Extension
        $this->loader->add_filter($this->plugin_name . '/extension_activate', $extensionOptions, 'wwdh_activate_extension', 10, 3);

        $this->loader->add_filter($this->plugin_name . '/read_folder', $extensionOptions, 'read_wwdh_folder', 10, 2);
    }

    /**
     * Register all extension api hooks
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function wwdh_extension_api()
    {
        global $wwdhExtensionApi;
        $wwdhExtensionApi = new WWDH_Extension_API($this->get_version(), $this->get_plugin_name(), $this->main);

        // TODO Get License Data
        $this->loader->add_filter($this->plugin_name . '/get_api_post_resource', $wwdhExtensionApi, 'wwdh_get_api_post_resource', 10, 4);
        $this->loader->add_filter($this->plugin_name . '/extension_download', $wwdhExtensionApi, 'wwdh_api_extension_download', 10, 3);
    }

    /**
     * Run the loader to execute all the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @return    string    The name of the plugin.
     * @since     1.0.0
     */
    public function get_plugin_name(): string
    {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @return    Wp_Experience_Reports_Loader    Orchestrates the hooks of the plugin.
     * @since     1.0.0
     */
    public function get_loader(): Wp_Experience_Reports_Loader
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @return    string    The version number of the plugin.
     * @since     1.0.0
     */
    public function get_version(): string
    {
        return $this->version;
    }

    /**
     * Retrieve the database version number of the plugin.
     *
     * @return    string    The database version number of the plugin.
     * @since     1.0.0
     */
    public function get_db_version(): string
    {
        return $this->db_version;
    }

    /**
     * License Config for the plugin.
     *
     * @return    object License Config.
     * @since     1.0.0
     */
    public function get_license_config(): object
    {
        $config_file = plugin_dir_path(dirname(__FILE__)) . 'includes/license/config.json';
        return json_decode(file_get_contents($config_file));
    }

    /**
     * The EXTENSION PREVIEW DIR
     *
     *
     * @return    string     EXTENSION PREVIEW DIR of the plugin.
     * @since     1.0.0
     */
    public function get_extension_preview(): string
    {
        return $this->extension_preview;
    }

    /**
     * The API DIR
     *
     *
     * @return    string    API DIR of the plugin.
     * @since     1.0.0
     */
    public function get_api_dir(): string
    {
        return $this->api_dir;
    }

    /**
     * The Public Certificate
     *
     * @return    string    Public Certificate in BASE64.
     * @since     1.0.0
     */
    public function get_id_rsa(): string
    {
        return $this->id_rsa;
    }

    public function get_twig_template_dir() :string {
        return $this->twig_template_dir;
    }

}
