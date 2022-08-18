<?php

namespace Experience\Reports;

use Exception;
use Throwable;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Wp_Experience_Reports;
use stdClass;
use WP_Query;



defined('ABSPATH') or die();

require_once ABSPATH . WPINC . '/general-template.php';

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
    protected string $title;


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

        $currentPostId = filter_input(INPUT_GET, 'report-post', FILTER_SANITIZE_NUMBER_INT);

        if(isset($currentPostId) && $currentPostId){
            if (has_action('wp_head','_wp_render_title_tag') == 1) {
                remove_action('wp_head','_wp_render_title_tag',1);
                add_action('wp_head',array($this,'custom_wp_render_title_tag_filtered'),1);
            }
        }


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
            'id' => '',
            'anker' => ''
        ), $atts);

        ob_start();
        if (!isset($atts['post']) && !$atts['post'] || !isset($atts['id']) && !$atts['id']) {
            return '';
        }



        $catId = '';
        $currentPostId = filter_input(INPUT_GET, 'report-post', FILTER_SANITIZE_NUMBER_INT);
        $randId = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_STRING);
        $category = get_the_terms($currentPostId, 'experience_reports_category');



        $attributes = [];

        $post = get_post($currentPostId);
        $postAttribute = parse_blocks($post->post_content);

        if ($postAttribute) {
            foreach ($postAttribute as $attribute) {
                if ($attribute['blockName'] == 'wwdh/experience-reports-template') {
                        $attributes = $attribute['attrs'];
                        break;

                }
            }
        }



        if (!$category) {
            return '';
        }
        foreach ($category as $cat) {
            $catId = $cat->term_id;
        }
        $catId ? $cat_id = $catId : $cat_id = '';

        if ($cat_id) {
            $attr = apply_filters($this->basename . '/get_post_attributes', $atts['post'], $catId);
        } else {
            $attr = $this->get_attribute($atts['post'], $randId);
        }


        if (!isset($attr->randomPageId) || $attr->randomPageId != $randId) {
            return '';
        }
        if (!$attr) {
            return '';
        }

        if (!$attr->selectedCategory) {
            return '';
        }

        if ($attr->backToOverviewAktiv && $attr->selectedBackBtnUrl) {
            $backUrl = get_page_link($attr->selectedBackBtnUrl);
        } else {
            $backUrl = site_url();
        }


        if ($attr->backToOverviewAktiv && $attr->selectedCategoryPages) {
            $catUrl = get_page_link($attr->selectedCategoryPages);
        } else {
            $catUrl = get_category_link($attr->selectedCategory);
        }

        $dateHeadline = '';
        $queryPost = $this->make_query_posts($attr->selectedCategory, $attr, $currentPostId);

        $twigData = [];
        if ($queryPost->reportPosts) {
            for ($i = 0; $i <= $queryPost->total; $i++) {
                if ($queryPost->paged == $i + 1) {
                    $attr->showPostTitleActive ? $title = $queryPost->reportPosts[$i]['title'] : $title = false;
                    $attr->showPostAutorAktiv ? $author = $queryPost->reportPosts[$i]['author'] : $author = false;
                    foreach ($queryPost->reportPosts[$i]['post_sections'] as $val) {
                        if ($val['content'] == 'Date') {
                            $dateHeadline = $val['headline'];
                            break;
                        }
                    }
                    if ($queryPost->reportPosts[$i]['cover_image']['id'] != 0) {
                        $src_cover_img_src = wp_get_attachment_image_src($queryPost->reportPosts[$i]['cover_image']['id'], 'large', false);
                        $src_cover_img_full_src = wp_get_attachment_image_src($queryPost->reportPosts[$i]['cover_image']['id'], 'full', false);
                        $cover_img_src = $src_cover_img_src[0];
                        $cover_img_full_src = $src_cover_img_full_src[0];
                        $cover_img_id = $queryPost->reportPosts[$i]['cover_image']['id'];
                    } else {
                        $cover_img_src = false;
                        $cover_img_full_src = false;
                        $cover_img_id = false;
                    }
                    $twigData = [
                        'attr' => $attr,
                        'cover_img_src' => $cover_img_src,
                        'cover_img_full_src' => $cover_img_full_src,
                        'cover_img_id' => $cover_img_id,
                        'showPostDateAktiv' => $attr->showPostDateAktiv,
                        'showCategoryLinkAktiv' => $attr->showCategoryLinkAktiv,
                        'showPostTitleActive' => $attr->showPostTitleActive,
                        'showPostAutorAktiv' => $attr->showPostAutorAktiv,
                        'showDateFromToAktiv' => $attr->showDateFromToAktiv,
                        'title' => $title,
                        'dateHeadline' => $dateHeadline,
                        'posted_on_lang' => __('Posted on', 'wp-experience-reports'),
                        'by_lang' => __('by', 'wp-experience-reports'),
                        'author' => $author,
                        'date' => $queryPost->reportPosts[$i]['date'],
                        'date_from_format' => $queryPost->reportPosts[$i]['date_from_format'],
                        'date_to_format' => $queryPost->reportPosts[$i]['date_to_format'],
                        'date_to_language' => $queryPost->reportPosts[$i]['date_to_language'],
                        'cat_url' => $catUrl,
                        'back_url' => $backUrl,
                        'get_cat_name' => $this->get_report_category_name_by_id($attr->selectedCategory),
                    ];

                    try {
                        echo $this->twig->render('/details/single-page-post-details.twig', ['d' => $twigData]);
                    } catch (LoaderError|SyntaxError|RuntimeError|Throwable $e) {
                        echo '';
                    }
                    echo do_blocks($queryPost->reportPosts[$i]['content']);
                }
            }
        }

        $section_one_headline = get_post_meta($currentPostId, '_experience_reports_section_one_headline', true);
        if($section_one_headline){
            $this->title = $this->cleanWhitespace($section_one_headline) . ' &ndash; '.  get_bloginfo('title');
        } else {
            $this->title = get_bloginfo('title');
        }


        $this->_wp_render_title_tag();
        echo $queryPost->pagination;
        return ob_get_clean();
    }

    /**
     * @throws Exception
     */
    private function make_experience_report_pagination($total, $paged, $attr): string
    {

        $pages = ceil($total / 1);
        if ($pages < 2) {
            return '';
        }

        if (!$attr->nextPrevAktiv) {
            return '';
        }

        $attr->nextPrevAnkerLink ? $nextPrevAnkerLink = '&get=' . $attr->nextPrevAnkerLink : $nextPrevAnkerLink = '';
        $attr->nextPrevCss ? $nextPrevCss = $attr->nextPrevCss : $nextPrevCss = '';
        $attr->previousText ? $previousText = $attr->previousText : $previousText = '';
        $attr->nextText ? $nextText = $attr->nextText : $nextText = '';
        $attr->nextPrevAnkerOffset ? $nextPrevAnkerOffset = '&offset=' . $attr->nextPrevAnkerOffset : $nextPrevAnkerOffset = '';
        $paged == (int)$pages ? $last = 'd-none' : $last = '';
        $paged == '1' ? $first = 'd-none' : $first = '';
        $html = '<nav class="experience-reports-pagination" aria-label="Page navigation" role="navigation">';
        $html .= '<span class="sr-only">Page navigation</span>';
        $html .= '<ul class="pagination justify-content-center ft-wpbs mb-4">';
        $html .= '<li class="page-item ' . $first . '"><a class="page-link ' . $nextPrevCss . '" href="' . get_pagenum_link($paged - 1) . $nextPrevAnkerLink . $nextPrevAnkerOffset . '" aria-label="Previous Page"><i class="bi bi-chevron-left"></i> ' . $previousText . '</a></li>';
        $html .= '<li class="page-item ' . $last . '"><a class="page-link ' . $nextPrevCss . '" href="' . get_pagenum_link($paged + 1) . $nextPrevAnkerLink . $nextPrevAnkerOffset . '" aria-label="Next Page">' . $nextText . ' <i class="bi bi-chevron-right"></i> </a></li>';
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
    private function make_query_posts($kategorieId, $attr, $currentPostId): object
    {

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
        $customPosts = apply_filters($this->basename . '/get_experience_posts_by_category', $totalPosts->posts, $attr);
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
        $postArr = [];
        $firstArr = [];
        $x = 0;
        $y = 1;
        if (!get_query_var('paged')) {
            foreach ($reportPosts as $tmp) {
                if ($tmp['post_id'] == $currentPostId) {
                    $firstArr = $tmp;
                    $x++;
                }
            }
            $postArr[] = array_merge_recursive($firstArr, $reportPosts);

        } else {
            foreach ($reportPosts as $tmp) {
                if ($tmp['post_id'] == $currentPostId && get_query_var('paged')) {
                    if ($y == $paged) {
                        continue;
                    }
                }
                $y++;
                $postArr[] = $tmp;
            }
        }
        if (count($postArr) > 1) {
            $total = count($postArr);
        }

        $pagination = $this->make_experience_report_pagination($total, $paged, $attr);
        $return = new stdClass();
        $return->reportPosts = $postArr;
        $return->pagination = $pagination;
        $return->paged = $paged;
        $return->total = $total;
        return $return;
    }

    public function get_report_category_name_by_id($category_id)
    {
        $term = get_term_by('id', $category_id, 'experience_reports_category', 'ARRAY_A');
        return $term['name'];
    }

    public function get_attribute($postId, $randId): object
    {
        $attr = new stdClass();
        $attributes = [];

        $post = get_post($postId);
        $postAttribute = parse_blocks($post->post_content);

        if ($postAttribute) {
            foreach ($postAttribute as $attribute) {
                if ($attribute['blockName'] == 'wwdh/experience-reports-block') {
                    if ($attribute['attrs']['randomPageId'] == $randId) {
                        $attributes = $attribute['attrs'];
                        break;
                    }
                }
            }
        }

        if (!$attributes) {
            if ($postAttribute) {
                foreach ($postAttribute as $attribute) {
                    if ($attribute['innerBlocks']) {
                        foreach ($attribute['innerBlocks'] as $inner) {
                            if ($inner['blockName'] == 'wwdh/experience-reports-block') {
                                if ($inner['attrs']['randomPageId'] == $randId) {
                                    $attributes = $inner['attrs'];
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }

        if (!$attributes) {
            if ($postAttribute) {
                foreach ($postAttribute as $attribute) {
                    if ($attribute['innerBlocks']) {
                        foreach ($attribute['innerBlocks'] as $inner) {
                            if ($inner['blockName'] == 'wwdh/experience-reports-block') {
                                $attributes = $inner['attrs'];
                            }
                        }
                    }
                }
            }
        }


        if (!$attributes) {
            return $attr;
        }

        if (isset($attributes['randomPageId']) && $attributes['randomPageId'] != $randId) {
            return $attr;
        }

        isset($attributes['selectedTemplate']) && $attributes['selectedTemplate'] ? $attr->selectedTemplate = (int)$attributes['selectedTemplate'] : $attr->selectedTemplate = '';
        isset($attributes['selectedCategory']) && $attributes['selectedCategory'] ? $attr->selectedCategory = (int)$attributes['selectedCategory'] : $attr->selectedCategory = '';
        isset($attributes['ERRadioOrder']) && $attributes['ERRadioOrder'] ? $attr->ERRadioOrder = (int)$attributes['ERRadioOrder'] : $attr->ERRadioOrder = 1;
        isset($attributes['ERRadioOrderBy']) && $attributes['ERRadioOrderBy'] ? $attr->ERRadioOrderBy = (int)$attributes['ERRadioOrderBy'] : $attr->ERRadioOrderBy = 1;
        isset($attributes['galleryShowActive']) ? $attr->galleryShowActive = (bool)$attributes['galleryShowActive'] : $attr->galleryShowActive = false;

        isset($attributes['ERPaginationActive']) ? $attr->ERPaginationActive = (bool)$attributes['ERPaginationActive'] : $attr->ERPaginationActive = false;
        isset($attributes['paginationLimit']) && $attributes['paginationLimit'] ? $attr->paginationLimit = (int)$attributes['paginationLimit'] : $attr->paginationLimit = 10;
        isset($attributes['paginationMethodSelect']) && $attributes['paginationMethodSelect'] ? $attr->paginationMethodSelect = (int)$attributes['paginationMethodSelect'] : $attr->paginationMethodSelect = 1;
        isset($attributes['buttonLabel']) && $attributes['buttonLabel'] ? $attr->buttonLabel = (string)$attributes['buttonLabel'] : $attr->buttonLabel = 'Bericht lesen';
        isset($attributes['buttonCss']) && $attributes['buttonCss'] ? $attr->buttonCss = (string)$attributes['buttonCss'] : $attr->buttonCss = 'btn btn-outline-secondary';
        isset($attributes['buttonIcon']) && $attributes['buttonIcon'] ? $attr->buttonIcon = (string)$attributes['buttonIcon'] : $attr->buttonIcon = '<i class="bi bi-chat-square-text"></i>';
        isset($attributes['buttonTargetAktiv']) && $attributes['buttonTargetAktiv'] ? $attr->buttonTargetAktiv = (bool)$attributes['buttonTargetAktiv'] : $attr->buttonTargetAktiv = false;

        isset($attributes['outputLimit']) && $attributes['outputLimit'] ? $attr->outputLimit = (int)$attributes['outputLimit'] : $attr->outputLimit = -1;
        isset($attributes['RadioImgShowOption']) && $attributes['RadioImgShowOption'] ? $attr->RadioImgShowOption = (int)$attributes['RadioImgShowOption'] : $attr->RadioImgShowOption = 0;
        isset($attributes['className']) && $attributes['className'] ? $attr->className = (string)$attributes['className'] : $attr->className = '';

        isset($attributes['customPageAktiv']) ? $attr->customPageAktiv = (bool)$attributes['customPageAktiv'] : $attr->customPageAktiv = false;
        isset($attributes['selectedPages']) && $attributes['selectedPages'] ? $attr->selectedPages = (int)$attributes['selectedPages'] : $attr->selectedPages = 0;

        isset($attributes['showPostTitleActive']) ? $attr->showPostTitleActive = (bool)$attributes['showPostTitleActive'] : $attr->showPostTitleActive = 0;
        isset($attributes['showPostDateAktiv']) ? $attr->showPostDateAktiv = (bool)$attributes['showPostDateAktiv'] : $attr->showPostDateAktiv = 0;
        isset($attributes['showDateFromToAktiv']) ? $attr->showDateFromToAktiv = (bool)$attributes['showDateFromToAktiv'] : $attr->showDateFromToAktiv = 0;
        isset($attributes['showCategoryLinkAktiv']) ? $attr->showCategoryLinkAktiv = (bool)$attributes['showCategoryLinkAktiv'] : $attr->showCategoryLinkAktiv = 0;
        isset($attributes['showPostAutorAktiv']) ? $attr->showPostAutorAktiv = (bool)$attributes['showPostAutorAktiv'] : $attr->showPostAutorAktiv = 0;
        isset($attributes['customCategoryPageAktiv']) ? $attr->customCategoryPageAktiv = (bool)$attributes['customCategoryPageAktiv'] : $attr->customCategoryPageAktiv = 0;
        isset($attributes['selectedCategoryPages']) && $attributes['selectedCategoryPages'] ? $attr->selectedCategoryPages = (int)$attributes['selectedCategoryPages'] : $attr->selectedCategoryPages = 0;

        isset($attributes['dataLoad']) && $attributes['dataLoad'] ? $attr->dataLoad = (int)$attributes['dataLoad'] : $attr->dataLoad = 1;

        isset($attributes['backToOverviewAktiv']) ? $attr->backToOverviewAktiv = (bool)$attributes['backToOverviewAktiv'] : $attr->backToOverviewAktiv = false;
        isset($attributes['selectedBackBtnUrl']) && $attributes['selectedBackBtnUrl'] ? $attr->selectedBackBtnUrl = (int)$attributes['selectedBackBtnUrl'] : $attr->selectedBackBtnUrl = 0;
        isset($attributes['backButtonLabel']) && $attributes['backButtonLabel'] ? $attr->backButtonLabel = (string)$attributes['backButtonLabel'] : $attr->backButtonLabel = '';
        isset($attributes['backButtonCss']) && $attributes['backButtonCss'] ? $attr->backButtonCss = (string)$attributes['backButtonCss'] : $attr->backButtonCss = '';
        isset($attributes['backButtonIcon']) && $attributes['backButtonIcon'] ? $attr->backButtonIcon = (string)$attributes['backButtonIcon'] : $attr->backButtonIcon = '';
        isset($attributes['catBlockAktiv']) && $attributes['catBlockAktiv'] ? (bool)$attr->catBlockAktiv = $attributes['catBlockAktiv'] : $attr->catBlockAktiv = false;

        isset($attributes['showMoreButtonLabel']) && $attributes['showMoreButtonLabel'] ? $attr->showMoreButtonLabel = (string)$attributes['showMoreButtonLabel'] : $attr->showMoreButtonLabel = 'mehr anzeigen';
        isset($attributes['showMoreButtonCss']) && $attributes['showMoreButtonCss'] ? $attr->showMoreButtonCss = (string)$attributes['showMoreButtonCss'] : $attr->showMoreButtonCss = 'btn btn-outline-secondary';
        isset($attributes['showMoreButtonIcon']) && $attributes['showMoreButtonIcon'] ? $attr->showMoreButtonIcon = (string)$attributes['showMoreButtonIcon'] : $attr->showMoreButtonIcon = '<i class="bi bi-plus"></i>';
        isset($attributes['backAnkerLink']) && $attributes['backAnkerLink'] ? $attr->backAnkerLink = (string)$attributes['backAnkerLink'] : $attr->backAnkerLink = '';

        isset($attributes['nextPrevAnkerLink']) && $attributes['nextPrevAnkerLink'] ? $attr->nextPrevAnkerLink = (string)$attributes['nextPrevAnkerLink'] : $attr->nextPrevAnkerLink = '';
        isset($attributes['nextPrevCss']) && $attributes['nextPrevCss'] ? $attr->nextPrevCss = (string)$attributes['nextPrevCss'] : $attr->nextPrevCss = '';
        isset($attributes['previousText']) && $attributes['previousText'] ? $attr->previousText = (string)$attributes['previousText'] : $attr->previousText = '';
        isset($attributes['nextText']) && $attributes['nextText'] ? $attr->nextText = (string)$attributes['nextText'] : $attr->nextText = '';
        isset($attributes['nextPrevAktiv']) ? $attr->nextPrevAktiv = (bool)$attributes['nextPrevAktiv'] : $attr->nextPrevAktiv = true;
        isset($attributes['nextPrevAnkerOffset']) && $attributes['nextPrevAnkerOffset'] ? $attr->nextPrevAnkerOffset = (int)$attributes['nextPrevAnkerOffset'] : $attr->nextPrevAnkerOffset = 0;
        isset($attributes['randomPageId']) && $attributes['randomPageId'] ? $attr->randomPageId = (string)$attributes['randomPageId'] : $attr->randomPageId = '';
        isset($attributes['backAnkerLinkOffset']) && $attributes['backAnkerLinkOffset'] ? $attr->backAnkerLinkOffset = (int)$attributes['backAnkerLinkOffset'] : $attr->backAnkerLinkOffset = '';

        isset($attributes['showCategoryAktiv']) ? $attr->showCategoryAktiv = (bool)$attributes['showCategoryAktiv'] : $attr->showCategoryAktiv = false;
        isset($attributes['selectCategoryLabelCss']) && $attributes['selectCategoryLabelCss'] ? $attr->selectCategoryLabelCss = (string)$attributes['selectCategoryLabelCss'] : $attr->selectCategoryLabelCss = '';
        isset($attributes['disabledCategories']) && $attributes['disabledCategories'] ? $attr->disabledCategories = $attributes['disabledCategories'] : $attr->disabledCategories = '';
        return $attr;
    }

    public function parse_template_button($postId, $btnGroupClass): bool
    {
        $post = get_post($postId);
        $postAttribute = parse_blocks($post->post_content);
        if ($postAttribute) {
            foreach ($postAttribute as $attribute) {
                if ($attribute['blockName'] == 'core/group') {
                    if (isset($attribute['attrs']['className']) && $attribute['attrs']['className']) {
                        $blocks = explode(' ', $attribute['attrs']['className']);
                        if (in_array($btnGroupClass, $blocks)) {
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }

    public function render_button_group($postId, $btnGroupClass, $template)
    {
        $post = get_post($postId);
        $postAttribute = parse_blocks($post->post_content);
        $html = '';
        if ($postAttribute) {
            foreach ($postAttribute as $attribute) {
                if ($attribute['blockName'] == 'core/group') {
                    if (isset($attribute['attrs']['className']) && $attribute['attrs']['className']) {

                        $blocks = explode(' ', $attribute['attrs']['className']);
                        if (in_array($btnGroupClass, $blocks)) {

                        }
                    }
                }
            }
        // return  $template;
        }

    }

   public function _wp_render_title_tag() {

       if ( ! current_theme_supports( 'title-tag' ) ) {
            return;
        }
       echo '<title>'.$this->title.'</title>' . "\n";
    }

  public function custom_wp_render_title_tag($titletag) {
      return str_replace('<title>','<title itemprop="name">',$titletag);
    }

  public function custom_wp_render_title_tag_filtered() {
        if (function_exists('_wp_render_title_tag')) {
            ob_start();
            _wp_render_title_tag();
            $titletag = ob_get_contents();
            ob_end_clean();
        } else {$titletag = '';}
        return apply_filters('wp_render_title_tag_filter',$titletag);
    }

    private function cleanWhitespace($string): string
    {
        return trim( preg_replace('/\s+/', ' ', $string) );
    }
}