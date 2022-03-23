<?php

namespace Experience\Reports;

use Exception;
use Throwable;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;
use Wp_Experience_Reports;
use stdClass;
use WP_Query;

defined('ABSPATH') or die();

/**
 * Define the Admin AJAX functionality.
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
class WP_Experience_Projects_Shortcodes
{

    /**
     * The plugin Slug Path.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $plugin_dir plugin Slug Path.
     */
    protected string $plugin_dir;


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
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $basename The ID of this plugin.
     */
    private string $basename;

    /**
     * The Version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current Version of this plugin.
     */
    private string $version;

    /**
     * Store plugin main class to allow public access.
     *
     * @since    1.0.0
     * @access   private
     * @var Wp_Experience_Reports $main The main class.
     */
    private Wp_Experience_Reports $main;

    public function __construct(string $basename, string $version, Wp_Experience_Reports $main, Environment $twig)
    {

        $this->basename = $basename;
        $this->version = $version;
        $this->main = $main;
        $this->twig = $twig;
        $this->plugin_dir = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $this->basename . DIRECTORY_SEPARATOR;
        add_shortcode('report-post', array($this, 'experience_reports_template_shortcode'));
    }

    /**
     * @throws Exception
     */
    function experience_reports_template_shortcode($atts, $content, $tag)
    {
        $a = shortcode_atts(array(
            'post' => '',
        ), $atts);

        ob_start();
        if (!$atts['post']) {
            return '';
        }

        //$attr = apply_filters($this->basename.'/get_post_attributes', $atts['post']);
        $attr = $this->get_attribute($atts['post']);
        if (!$attr) {
            return '';
        }

        if (!$attr->selectedCategory) {
            return '';
        }

        if($attr->backToOverviewAktiv && $attr->selectedBackBtnUrl){
            $catUrl = get_page_link($attr->selectedBackBtnUrl);
        } else {
            $catUrl = get_category_link( $attr->selectedCategory );
        }

        $dateHeadline = '';
        $queryPost = $this->make_query_posts($attr->selectedCategory, $attr);
        if ($queryPost->reportPosts) {
            for ($i = 0; $i <= $queryPost->total; $i++) {
                if ($queryPost->paged == $i + 1) {
                    $attr->showPostTitleActive ? $title = $queryPost->reportPosts[$i]['title'] : $title = false;
                    $attr->showPostAutorAktiv ? $author = $queryPost->reportPosts[$i]['author'] : $author = false;
                    foreach ( $queryPost->reportPosts[$i]['post_sections'] as $val){
                        if($val['content'] == 'Date'){
                           $dateHeadline = $val['headline'];
                           break;
                       }
                    }

                    $twigData = [
                        'attr'=> $attr,
                        'showPostDateAktiv' => $attr->showPostDateAktiv,
                        'showCategoryLinkAktiv' => $attr->showCategoryLinkAktiv,
                        'showPostTitleActive' => $attr->showPostTitleActive,
                        'showPostAutorAktiv' => $attr->showPostAutorAktiv,
                        'showDateFromToAktiv' => $attr->showDateFromToAktiv,
                        'title' => $title,
                        'dateHeadline' => $dateHeadline,
                        'posted_on_lang' => __('Posted on','wp-experience-reports'),
                        'by_lang' => __('by','wp-experience-reports'),
                        'author' => $author,
                        'date' => $queryPost->reportPosts[$i]['date'],
                        'date_from_format' => $queryPost->reportPosts[$i]['date_from_format'],
                        'date_to_format' => $queryPost->reportPosts[$i]['date_to_format'],
                        'date_to_language' => $queryPost->reportPosts[$i]['date_to_language'],
                        'cat_url' => $catUrl,
                        'get_cat_name' => $this->get_report_category_name_by_id($attr->selectedCategory),
                    ];
                    try {
                      echo  $this->twig->render('/details/single-page-post-details.twig', ['d' => $twigData]);
                    } catch (LoaderError | SyntaxError | RuntimeError|Throwable $e) {
                      echo '';
                    }
                    echo $queryPost->reportPosts[$i]['content'];
                }
            }
        }

        echo $queryPost->pagination;
        return ob_get_clean();
    }

    /**
     * @throws Exception
     */
    private function make_experience_report_pagination($total, $paged): string
    {
        $pages = ceil($total / 1);
        if ($pages < 2) {
            return '';
        }
        $paged == (int)$pages ? $last = 'd-none' : $last = '';
        $paged == '1' ? $first = 'd-none' : $first = '';
        $html = '<nav class="experience-reports-pagination" aria-label="Page navigation" role="navigation">';
        $html .= '<span class="sr-only">Page navigation</span>';
        $html .= '<ul class="pagination justify-content-center ft-wpbs mb-4">';
        $html .= '<li class="page-item ' . $first . '"><a class="page-link" href="' . get_pagenum_link($paged - 1) . '" aria-label="Previous Page"><i class="bi bi-chevron-left"></i></a></li>';
        $html .= '<li class="page-item ' . $last . '"><a class="page-link" href="' . get_pagenum_link($paged + 1) . '" aria-label="Next Page"><i class="bi bi-chevron-right"></i> </a></li>';
        $html .= '</ul>';
        $html .= '</nav>';
        return preg_replace(array('/<!--(.*)-->/Uis', "/[[:blank:]]+/"), array('', ' '), str_replace(array("\n", "\r", "\t"), '', $html));
    }

    /**
     * @param $postArr
     * @param $value
     * @param $order
     *
     * @return array|mixed
     */
    private function order_by_args($postArr, $value, $order)
    {
        switch ($order) {
            case'1':
                usort($postArr, fn($a, $b) => $a[$value] - $b[$value]);
                return array_reverse($postArr);
            case '2':
                usort($postArr, fn($a, $b) => $a[$value] - $b[$value]);
                break;
        }

        return $postArr;
    }

    /**
     * @throws Exception
     */
    private function make_query_posts($kategorieId, $attr):object{

        get_query_var('paged') ? $paged = get_query_var('paged') : $paged = 1;
        $totalArgs = [
            'post_type' => 'experience_reports',
            'posts_per_page' => -1,
            'tax_query' => [
                [
                    'taxonomy' => 'experience_reports_category',
                    'field' => 'term_id',
                    'terms' => $kategorieId
                ]
            ]
        ];
        $totalPosts = new WP_Query($totalArgs);
        $total = count($totalPosts->posts);
        $pagination = $this->make_experience_report_pagination($total, $paged);
        $customPosts = apply_filters($this->basename.'/get_experience_posts_by_category',$totalPosts->posts, $attr);
        wp_reset_query();
        switch ($attr->ERPaginationActive) {
            case '1':
                $type = 'menu_order';
                break;
            case '2':
                $type = 'post_date';
                break;
            case '3':
                $type = 'date_from_time';
                break;
            default:
                $type = 'post_date';
        }

        $posts = apply_filters($this->basename . '/object_to_array', $customPosts);
        $reportPosts = $this->order_by_args($posts, $type, $attr->ERRadioOrderBy);

        $return = new stdClass();
        $return->reportPosts = $reportPosts;
        $return->pagination = $pagination;
        $return->paged = $paged;
        $return->total = $total;
        return $return;
    }

    public function get_report_category_name_by_id( $category_id ) {
        $term = get_term_by( 'id', $category_id, 'experience_reports_category', 'ARRAY_A' );
        return $term['name'];
    }

    private function get_attribute($postId):object{
        $attr = new stdClass();
        $attributes = [];

        $post = get_post($postId);
        $postAttribute = parse_blocks($post->post_content);
        if ($postAttribute) {
            foreach ($postAttribute as $attribute) {
                if ($attribute['blockName'] == 'wwdh/experience-reports-block') {
                   $attributes = $attribute['attrs'];
                }
            }
        }
        if (!$attributes) {
            return $attr;
        }

        isset($attributes['selectedTemplate']) && $attributes['selectedTemplate'] ? $attr->selectedTemplate = (int)$attributes['selectedTemplate'] : $attr->selectedTemplate = '';
        isset($attributes['selectedCategory']) && $attributes['selectedCategory'] ? $attr->selectedCategory = (int)$attributes['selectedCategory'] : $attr->selectedCategory = '';
        isset($attributes['ERRadioOrder']) && $attributes['ERRadioOrder'] ? $attr->ERRadioOrder = (int)$attributes['ERRadioOrder'] : $attr->ERRadioOrder = 1;
        isset($attributes['ERRadioOrderBy']) && $attributes['ERRadioOrderBy'] ? $attr->ERRadioOrderBy = (int)$attributes['ERRadioOrderBy'] : $attr->ERRadioOrderBy = 1;
        isset($attributes['galleryShowActive']) ? $attr->galleryShowActive = (bool)$attributes['galleryShowActive'] : $attr->galleryShowActive = true;

        isset($attributes['ERPaginationActive']) ? $attr->ERPaginationActive = (bool)$attributes['ERPaginationActive'] : $attr->ERPaginationActive = false;
        isset($attributes['paginationLimit']) && $attributes['paginationLimit'] ? $attr->paginationLimit = (int)$attributes['paginationLimit'] : $attr->paginationLimit = 10;
        isset($attributes['paginationMethodSelect']) && $attributes['paginationMethodSelect'] ? $attr->paginationMethodSelect = (int)$attributes['paginationMethodSelect'] : $attr->paginationMethodSelect = 1;

        isset($attributes['buttonLabel']) && $attributes['buttonLabel'] ? $attr->buttonLabel = (string)$attributes['buttonLabel'] : $attr->buttonLabel = 'Bericht lesen';
        isset($attributes['buttonCss']) && $attributes['buttonCss'] ? $attr->buttonCss = (string)$attributes['buttonCss'] : $attr->buttonCss = 'btn btn-outline-secondary';
        isset($attributes['buttonIcon']) && $attributes['buttonIcon'] ? $attr->buttonIcon = (string)$attributes['buttonIcon'] : $attr->buttonIcon = '<i class="bi bi-chat-square-text"></i>';
        isset($attributes['buttonTargetAktiv']) && $attributes['buttonTargetAktiv'] ? $attr->buttonTargetAktiv = (bool)$attributes['buttonTargetAktiv'] : $attr->buttonTargetAktiv = false;

        isset($attributes['outputLimit']) && $attributes['outputLimit'] ? $attr->outputLimit = (int)$attributes['outputLimit'] : $attr->outputLimit = -1;
        isset($attributes['RadioImgShowOption']) && $attributes['RadioImgShowOption'] ? $attr->RadioImgShowOption = (int)$attributes['RadioImgShowOption'] : $attr->RadioImgShowOption = 1;
        isset($attributes['className']) && $attributes['className'] ? $attr->className = (string)$attributes['className'] : $attr->className = '';

        isset($attributes['customPageAktiv']) ? $attr->customPageAktiv = (bool)$attributes['customPageAktiv'] : $attr->customPageAktiv = false;
        isset($attributes['selectedPages']) && $attributes['selectedPages'] ? $attr->selectedPages = (int)$attributes['selectedPages'] : $attr->selectedPages = 0;

        isset($attributes['showPostTitleActive']) && $attributes['showPostTitleActive'] ? $attr->showPostTitleActive = (bool)$attributes['showPostTitleActive'] : $attr->showPostTitleActive = 0;
        isset($attributes['showPostDateAktiv']) && $attributes['showPostDateAktiv'] ? $attr->showPostDateAktiv = (bool)$attributes['showPostDateAktiv'] : $attr->showPostDateAktiv = 0;
        isset($attributes['showDateFromToAktiv']) && $attributes['showDateFromToAktiv'] ? $attr->showDateFromToAktiv = (bool)$attributes['showDateFromToAktiv'] : $attr->showDateFromToAktiv = 0;
        isset($attributes['showCategoryLinkAktiv']) && $attributes['showCategoryLinkAktiv'] ? $attr->showCategoryLinkAktiv = (bool)$attributes['showCategoryLinkAktiv'] : $attr->showCategoryLinkAktiv = 0;
        isset($attributes['showPostAutorAktiv']) && $attributes['showPostAutorAktiv'] ? $attr->showPostAutorAktiv = (bool)$attributes['showPostAutorAktiv'] : $attr->showPostAutorAktiv = 0;
        isset($attributes['customCategoryPageAktiv']) && $attributes['customCategoryPageAktiv'] ? $attr->customCategoryPageAktiv = (bool)$attributes['customCategoryPageAktiv'] : $attr->customCategoryPageAktiv = 0;
        isset($attributes['selectedCategoryPages']) && $attributes['selectedCategoryPages'] ? $attr->selectedCategoryPages = (int)$attributes['selectedCategoryPages'] : $attr->selectedCategoryPages = 0;

        isset($attributes['backToOverviewAktiv']) && $attributes['backToOverviewAktiv'] ? $attr->backToOverviewAktiv = (bool)$attributes['backToOverviewAktiv'] : $attr->backToOverviewAktiv = false;
        isset($attributes['selectedBackBtnUrl']) && $attributes['selectedBackBtnUrl'] ? $attr->selectedBackBtnUrl = (int)$attributes['selectedBackBtnUrl'] : $attr->selectedBackBtnUrl = 0;
        isset($attributes['backButtonLabel']) && $attributes['backButtonLabel'] ? $attr->backButtonLabel = (string)$attributes['backButtonLabel'] : $attr->backButtonLabel = 'zurück zur Übersicht';
        isset($attributes['backButtonCss']) && $attributes['backButtonCss'] ? $attr->backButtonCss = (string)$attributes['backButtonCss'] : $attr->backButtonCss = 'btn btn-outline-secondary';
        isset($attributes['backButtonIcon']) && $attributes['backButtonIcon'] ? $attr->backButtonIcon = (string)$attributes['backButtonIcon'] : $attr->backButtonIcon = '<i class="bi bi-reply-all-fill"></i>';

        isset($attributes['showMoreButtonLabel']) && $attributes['showMoreButtonLabel'] ? $attr->showMoreButtonLabel = (string)$attributes['showMoreButtonLabel'] : $attr->showMoreButtonLabel = 'mehr anzeigen';
        isset($attributes['showMoreButtonCss']) && $attributes['showMoreButtonCss'] ? $attr->showMoreButtonCss = (string)$attributes['showMoreButtonCss'] : $attr->showMoreButtonCss = 'btn btn-outline-secondary';
        isset($attributes['showMoreButtonIcon']) && $attributes['showMoreButtonIcon'] ? $attr->showMoreButtonIcon = (string)$attributes['showMoreButtonIcon'] : $attr->showMoreButtonIcon = '<i class="bi bi-plus"></i>';
        return $attr;
    }

}