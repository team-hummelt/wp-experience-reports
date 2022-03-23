<?php

namespace Experience\Reports;

use DOMDocument;
use Exception;
use Wp_Experience_Reports;
use stdClass;
use Throwable;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use WP_Query;

defined('ABSPATH') or die();

/**
 * ADMIN Gutenberg Sidebar
 * @package Hummelt & Partner WordPress-Plugin
 * Copyright 2022, Jens Wiecker
 * License: Commercial - goto https://www.hummelt-werbeagentur.de/
 *
 * @Since 1.0.0
 */
class Render_Experience_Reports_Callback_Templates
{

    protected Wp_Experience_Reports $main;
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
     * TRAIT of Default Settings.
     * @since    1.0.0
     */
    use WP_Experience_Reports_Defaults;

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $basename The ID of this plugin.
     */
    private string $basename;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private string $version;


    /**
     * POST attributes
     *
     * @since    1.0.0
     * @access   private
     * @var    object $attr The current version of this plugin.
     */
    private object $attr;

    /**
     * @param string $plugin_name
     * @param string $version
     * @param Wp_Experience_Reports $main
     * @param Environment $twig
     */
    public function __construct(string $plugin_name, string $version, Wp_Experience_Reports $main, Environment $twig)
    {
        $this->basename = $plugin_name;
        $this->version = $version;
        $this->main = $main;
        $this->twig = $twig;
    }

    /**
     * @param string $block_content
     * @param array $block
     * @return string
     */
    public static function render_core_experience_reports_callback(string $block_content, array $block): string
    {
        if ($block['blockName'] === 'wwdh/experience-reports-block' && !is_admin() && !wp_is_json_request()) {
            return str_replace('wp-block-columns', '', $block_content);
        }
        return $block_content;
    }


    public function render_callback_select_filter($attributes) {
        isset($attributes['selectedFirstCategoryName']) && $attributes['selectedFirstCategoryName'] ? $selectedFirstCategoryName = (string)$attributes['selectedFirstCategoryName'] : $selectedFirstCategoryName = 'Alle';
        isset($attributes['selectCategoryLabel']) && $attributes['selectCategoryLabel'] ? $selectCategoryLabel = (string)$attributes['selectCategoryLabel'] : $selectCategoryLabel = 'Kategorie Auswahl';
        isset($attributes['className']) && $attributes['className'] ? $className = (string)$attributes['className'] : $className = '';
        $rand = apply_filters($this->basename . '/generate_random_id', 12, 0);
        global $post;
        $posts = get_post($post->ID);
        $postAttribute = parse_blocks($posts->post_content);

        $attrArr = [];
        if ($postAttribute) {
            foreach ($postAttribute as $attribute) {
                if ($attribute['blockName'] == 'wwdh/experience-reports-block') {
                    $attrArr[] = $attribute['attrs'];
                }
            }
        }

        $selId = [];
        if($attrArr){
            foreach ($attrArr as $tmp){
                $selId[] = $tmp['selectedCategory'];
            }
        }

        $selId = array_merge_recursive(array_unique($selId));
        $selectData = [];
        if($selId){
            foreach ($selId as $tmp){

                $selectItem = [
                    'id' => $tmp,
                    'name' => $this->get_report_category_name_by_id($tmp)
                ];
                $selectData[] = $selectItem;
            }
        }

        $template = '';
        $twigData = [
            'selectedFirstCategoryName' => $selectedFirstCategoryName,
            'selectCategoryLabel' => $selectCategoryLabel,
            'className' => $className,
            'rand'=> $rand,
            'selected' => $selectData
        ];

        try {
            $template = $this->twig->render('details/select-filter.twig', ['d' => $twigData]);
        } catch (LoaderError|SyntaxError|RuntimeError|Throwable $e) {
            echo '';
        }
        echo $template;
      // print_r($attributes);
    }

    /**
     * @throws Exception
     */
    public function render_callback_template($attributes)
    {

        global $post;
        isset($attributes['postId']) && $attributes['postId'] ? $postId = (int)$attributes['postId'] : $postId = $post->ID;
        isset($attributes['dataLoad']) && $attributes['dataLoad'] ? $dataLoad = (int)$attributes['dataLoad'] : $dataLoad = 1;
        isset($attributes['loadMore']) && $attributes['loadMore'] ? $loadMore = true : $loadMore = false;
        isset($attributes['selectedCategory']) && $attributes['selectedCategory'] ? $selectedCategory = (int)$attributes['selectedCategory'] : $selectedCategory = '';
      //  isset($attributes['catBlockAktiv']) && $attributes['catBlockAktiv'] ? $catBlockAktiv = true : $catBlockAktiv = false;

        $attr = apply_filters($this->basename . '/get_post_attributes', $postId, $selectedCategory);

        $this->attr = $attr;
        $pagination = '';


        if (!$attr->selectedCategory || !$attr->selectedTemplate) {
            return '';
        }
        $args = [
            'post_type' => 'experience_reports',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'tax_query' => [
                [
                    'taxonomy' => 'experience_reports_category',
                    'field' => 'term_id',
                    'terms' => $attr->selectedCategory
                ]
            ]
        ];


        $selectedTemplate = apply_filters($this->basename.'/get_template_select', $attr->selectedTemplate);
        if(!$selectedTemplate['is_gallery']){
            $attr->galleryShowActive = false;
        }

        $totalPosts = new WP_Query($args);
        wp_reset_query();
        $total = count($totalPosts->posts);
        $notPagination = true;
        $start = 0;
        $end = 0;
        $moreStart = 0;
        $moreEnd = 0;
        if ($attr->ERPaginationActive) {
            get_query_var('paged') ? $paged = get_query_var('paged') : $paged = 1;
            if ($attr->paginationMethodSelect == 1) {
                $pagination = $this->make_experience_report_pagination($total, $attr->paginationLimit, $paged);
            }
            if ($attr->paginationMethodSelect == 2) {
                $moreStart = $dataLoad * $attr->paginationLimit - $attr->paginationLimit;
                $moreEnd = ($dataLoad) * $attr->paginationLimit - 2;
                $pagination = $this->make_load_more_button($total, $attr->paginationLimit, $postId, $attr, $dataLoad);
            }
            $start = $paged * $attr->paginationLimit - $attr->paginationLimit;
            $end = ($paged) * $attr->paginationLimit - 2;
            $notPagination = false;
        }

        if (!$attr->ERPaginationActive && $attr->outputLimit > 1) {
            $end = $attr->outputLimit - 2;
            $notPagination = false;
        }


        switch ($attr->ERRadioOrder) {
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

        $posts = new WP_Query($args);
        wp_reset_query();
        $template = '';
        $queryPosts = $this->get_experience_posts_by_category($posts->posts, $attr);
        $reportPosts = $this->order_by_args($queryPosts, $type, $attr->ERRadioOrderBy);
        //JOB Template Ausgabe START
        $slider_template = new stdClass();
        $reportPosts = apply_filters($this->basename . '/array_to_object', $reportPosts);
        $i = 1;
        $x = 0;
        foreach ($reportPosts as $tmp) {

            if ($loadMore) {
                if ($x >= $moreStart && $x <= $moreEnd + 1 ) {
                } else {
                    $x++;
                    continue;
                }
            } else {
                if ($x >= $start && $x <= $end + 1 || $notPagination) {
                } else {
                    $x++;
                    continue;
                }
            }
            $x++;

            $slider_template->rand = apply_filters($this->basename . '/generate_random_id', 12, 0);
            $slider_template->attr = $attr;
            if ($i % 2 == 0) {
                $slider_template->reverse = true;
                $slider_template->bgClass = 'background-even';
            } else {
                $slider_template->reverse = false;
                $slider_template->bgClass = 'background-odd';
            }

            if ($tmp->cover_image->id != 0) {
                $src_cover_img_src = wp_get_attachment_image_src($tmp->cover_image->id, 'large', false);
                $src_cover_img_full_src = wp_get_attachment_image_src($tmp->cover_image->id, 'full', false);
                $slider_template->cover_img_src = $src_cover_img_src[0];
                $slider_template->cover_img_full_src = $src_cover_img_full_src[0];
            } else {
                $slider_template->cover_img_src = false;
                $slider_template->cover_img_full_src = false;
            }

            $slider_template->slider_settings = false;


            if ($tmp->gallery_status && $attr->galleryShowActive) {
                $isSlider = apply_filters($this->basename . '/check_extension_table', 'erg_two_slide');
                if ($isSlider) {
                    $args = sprintf('WHERE slider_id="%s"', WP_EXPERIENCE_REPORTS_SLIDER_ID);
                    $sliderSettings = apply_filters(REPORTS_GALLERY_BASENAME . '/post_selector_get_by_args', $args, false);
                    if ($sliderSettings->status) {
                        $slider_template->slider_settings = $sliderSettings->record->data;
                        $slider_template->slider_id = $sliderSettings->record->id;
                    }
                }
            }

            $imgArr = [];
            if ($tmp->gallery_status && $attr->galleryShowActive && $selectedTemplate['is_gallery']) {
                foreach ($tmp->gallery_images as $img) {
                    $slider_template->slider_settings ? $imgSize = $slider_template->slider_settings->img_size : $imgSize = 'large';
                    $src = wp_get_attachment_image_src($img->img_id, $imgSize, false);
                    $src_full = wp_get_attachment_image_src($img->img_id, 'full', false);
                    $tmp->gallery_image_option == '1' ? $gallery_image_option = false : $gallery_image_option = $tmp->gallery_image_option;
                    $img_items = [
                        'title' => $img->img_title,
                        'description' => $img->img_beschreibung,
                        'src' => $src[0],
                        'src_full' => $src_full[0],
                        'link_option' => $gallery_image_option,
                        'attachment_url' => get_attachment_link($img->img_id)
                    ];
                    $imgArr[] = $img_items;
                }
            }

            $slider_template->images = $imgArr;
            $img_post_src = '';
            $img_post_src_full = '';
            if ($tmp->img_id) {
                $post_img_src = wp_get_attachment_image_src($tmp->img_id, 'large', false);
                $post_src_full = wp_get_attachment_image_src($tmp->img_id, 'full', false);
                $img_post_src = $post_img_src[0];
                $img_post_src_full = $post_src_full[0];
            }
            if ($attr->customPageAktiv && $attr->selectedPages) {
                //$permalink = get_permalink( $attr->selectedPages ).'?report-post='.$tmp->post_id;
                $permalink = get_permalink($attr->selectedPages);
            } else {
                $permalink = $tmp->permalink;
            }

            $tmp->permalink = $permalink;
            $tmp->post_image_src = $img_post_src;
            $tmp->post_image_src_full = $img_post_src_full;
            $slider_template->post = $tmp;


            $i++;
            if ($loadMore) {
                $slider_template->rand = apply_filters($this->basename . '/generate_random_id', 12, 0);
                $slider_template->loadMore = true;
            } else {
                $slider_template->loadMore = false;
            }

            switch ($attr->selectedTemplate) {
                //JOB Template 1
                case '1':
                    $twigData['data'][] = apply_filters($this->basename . '/object_to_array', (array)$slider_template);
                    try {
                        $template = $this->twig->render('Template-1.twig', ['data' => $twigData]);
                    } catch (LoaderError|SyntaxError|RuntimeError|Throwable $e) {
                        echo '';
                    }
                    break;
                case '2':
                    $twigData['data'][] = apply_filters($this->basename . '/object_to_array', (array)$slider_template);
                    try {
                        $template = $this->twig->render('Template-2.twig', ['data' => $twigData]);
                    } catch (LoaderError|SyntaxError|RuntimeError|Throwable $e) {
                        echo '';
                    }
                    break;
                case '3':
                    $twigData['data'][] = apply_filters($this->basename . '/object_to_array', (array)$slider_template);
                    try {
                        $template = $this->twig->render('Template-3.twig', ['data' => $twigData]);
                    } catch (LoaderError|SyntaxError|RuntimeError|Throwable $e) {
                        echo '';
                    }
                    break;
            }
        }
        if ($loadMore) {
            return preg_replace(array('/<!--(.*)-->/Uis', "/[[:blank:]]+/"), array('', ' '), str_replace(array("\n", "\r", "\t"), '', $template));
        }
        $randContainer = apply_filters($this->basename . '/generate_random_id', 12, 0);
        $html = '<div class="animate__animated '.$attr->className.' experience-reports-content reports-category-'.$attr->selectedCategory.'" data-id="'.$attr->selectedCategory.'" data-cat-name="'.$this->get_report_category_name_by_id($attr->selectedCategory).'" data-id="' . $randContainer . '" id="report-wrapper-' . $randContainer . '">';
        $html .= '<div data-id="' . $randContainer . '" id="report-' . $randContainer . '" class="experience-reports-wrapper">';
        $html .= $template;
        $html .= '</div>';
        $html .= $pagination;
        $html .= '</div>';
        echo preg_replace(array('/<!--(.*)-->/Uis', "/[[:blank:]]+/"), array('', ' '), str_replace(array("\n", "\r", "\t"), '', $html));
    }


    /**
     * @param $query
     * @param $attr
     * @return array
     */
    public function get_experience_posts_by_category($query, $attr): array
    {
        $page_id = get_queried_object_id();
        global $post;
        $postArr = [];
        foreach ($query as $post) {
            setup_postdata($post);
            $customTitle = get_post_meta($post->ID, '_hupa_custom_title', true);
            $customTitle ? $title = $customTitle : $title = get_the_title();
            $image_id = get_post_thumbnail_id();
            $attachment = (object)$this->wp_get_experience_attachment($image_id);

            $metaData = $this->get_experience_reports_meta_data($post->ID);

            $metaData->section_excerpt ? $excerpt = $metaData->section_excerpt : $excerpt = get_the_excerpt();
            if ($attr->galleryShowActive) {
                $gallery = $this->get_reports_gallery_data($metaData->gallery_select);
                $gallery_images = $gallery->gallery_images;
                $gallery_settings = $gallery->gallery_settings;
                $gallery_type = $gallery->gallery_type;
                $gallery_status = $gallery->status;
            } else {
                $gallery_images = '';
                $gallery_settings = '';
                $gallery_status = false;
                $gallery_type = '';
            }


            $post_item = [
                'post_id' => get_the_ID(),
                'parent_id' => $page_id,
                'img_id' => $image_id,
                'title' => $title,
                'permalink' => get_the_permalink(),
                'author' => get_the_author(),
                'alt' => $attachment->alt,
                'captions' => $attachment->caption,
                'description' => $attachment->description,
                'href' => $attachment->href,
                'src' => $attachment->src,
                'img_title' => $attachment->title,
                'content' => get_the_content(),
                'excerpt' => $excerpt,
                'page_excerpt' => get_the_excerpt($page_id),
                'date' => esc_html(get_the_date()),
                'post_date' => strtotime($post->post_date),
                'menu_order' => $post->menu_order,
                'cover_image' => $metaData->cover_image,
                'post_sections' => $metaData->post_sections,
                'date_format' => $metaData->date_format,
                'date_from' => $metaData->date_from,
                'date_from_time' => $metaData->from_time,
                'date_to' => $metaData->date_to,
                'date_to_time' => $metaData->to_time,
                'date_from_format' => $metaData->date_from_format,
                'date_to_format' => $metaData->date_to_format,
                'date_to_language' => __('to', 'wp-experience-reports-options'),
                'gallery_image_option' => $metaData->image_option,
                'gallery_id' => $metaData->gallery_select,
                'gallery_status' => $gallery_status,
                'gallery_images' => $gallery_images,
                'gallery_settings' => $gallery_settings,
                'gallery_types' => $gallery_type
            ];
            $postArr[] = $post_item;
        }
        return $postArr;
    }

    /**
     * @param $attachment_id
     *
     * @return array
     */
    private function wp_get_experience_attachment($attachment_id): array
    {

        $attachment = get_post($attachment_id);
        return array(
            'alt' => get_post_meta($attachment->ID, '_wp_attachment_image_alt', true),
            'description' => $attachment->post_content,
            'href' => get_permalink($attachment->ID),
            'src' => $attachment->guid,
            'title' => $attachment->post_title,
            'caption' => $attachment->post_excerpt,
        );
    }

    public function get_experience_reports_meta_data($postId): object
    {

        $record = new stdClass();
        $record->cover_image = get_post_meta($postId, '_reports_cover_image_meta', true);
        $record->gallery_select = get_post_meta($postId, '_reports_gallery_select', true);

        $section_one_headline = get_post_meta($postId, '_experience_reports_section_one_headline', true);
        $section_one_content = get_post_meta($postId, '_experience_reports_section_one_content', true);
        $section_one_is_date = get_post_meta($postId, '_experience_reports_section_one_is_date', true);

        $section_two_headline = get_post_meta($postId, '_experience_reports_section_two_headline', true);
        $section_two_content = get_post_meta($postId, '_experience_reports_section_two_content', true);
        $section_two_is_date = get_post_meta($postId, '_experience_reports_section_two_is_date', true);

        $section_three_headline = get_post_meta($postId, '_experience_reports_section_three_headline', true);
        $section_three_content = get_post_meta($postId, '_experience_reports_section_three_content', true);
        $section_three_is_date = get_post_meta($postId, '_experience_reports_section_three_is_date', true);

        $section_four_headline = get_post_meta($postId, '_experience_reports_section_four_headline', true);
        $section_four_content = get_post_meta($postId, '_experience_reports_section_four_content', true);
        $section_four_is_date = get_post_meta($postId, '_experience_reports_section_four_is_date', true);

        $section_five_headline = get_post_meta($postId, '_experience_reports_section_five_headline', true);
        $section_five_content = get_post_meta($postId, '_experience_reports_section_five_content', true);
        $section_five_is_date = get_post_meta($postId, '_experience_reports_section_five_is_date', true);

        $record->section_excerpt = get_post_meta($postId, '_experience_reports_section_excerpt', true);

        $record->date_format = get_post_meta($postId, '_experience_reports_date_format', true);
        $record->date_from = get_post_meta($postId, '_experience_reports_from', true);
        $record->date_to = get_post_meta($postId, '_experience_reports_to', true);

        $record->image_option = get_post_meta($postId, '_experience_reports_image_option', true);


        if ($record->date_from) {
            $date_from_format = apply_filters($this->basename . '/date_format', $record->date_from, $record->date_format);
            if ($date_from_format->status) {
                $record->date_from_format = $date_from_format->date;
                $record->from_time = strtotime($record->date_from);
            }
        } else {
            $record->date_from_format = false;
            $record->from_time = '';
        }

        if ($record->date_to) {
            $date_to_format = apply_filters($this->basename . '/date_format', $record->date_to, $record->date_format);
            if ($date_to_format->status) {
                $record->date_to_format = $date_to_format->date;
                $record->to_time = strtotime($record->date_to);
            }
        } else {
            $record->date_to_format = false;
            $record->to_time = '';
        }

        $record->cover_image = json_decode($record->cover_image);

        $section = [
            '0' => [
                'headline' => $section_one_headline,
                'content' => $section_one_content,
                'is_date' => $section_one_is_date
            ],
            '1' => [
                'headline' => $section_two_headline,
                'content' => $section_two_content,
                'is_date' => $section_two_is_date
            ],
            '2' => [
                'headline' => $section_three_headline,
                'content' => $section_three_content,
                'is_date' => $section_three_is_date
            ],
            '3' => [
                'headline' => $section_four_headline,
                'content' => $section_four_content,
                'is_date' => $section_four_is_date
            ],
            '4' => [
                'headline' => $section_five_headline,
                'content' => $section_five_content,
                'is_date' => $section_five_is_date
            ],
        ];

        $sectionArr = [];
        foreach ($section as $tmp) {
            if (!$tmp['headline'] && !$tmp['content']) {
                continue;
            }
            $sectionArr[] = $tmp;
        }

        $record->post_sections = $sectionArr;

        return $record;
    }

    private function get_reports_gallery_data($galleryId): object
    {

        $record = new stdClass();
        $record->status = false;
        $gallery = $this->get_experience_report_gallery($galleryId);

        $imageArray = [];
        $record->gallery_settings = '';
        $record->gallery_type = '';
        $record->gallery_images = '';

        if (isset($gallery->images->status) && $gallery->images->status && $gallery->status) {
            foreach ($gallery->images->record as $img) {
                $img->link = $this->get_reports_galerie_vs_image_settings($gallery->gallery_settings, $img, 'link');
                $img->hover_aktiv = $this->get_reports_galerie_vs_image_settings($gallery->gallery_settings, $img, 'hover_aktiv');
                $img->hover_title_aktiv = $this->get_reports_galerie_vs_image_settings($gallery->gallery_settings, $img, 'hover_title_aktiv');
                $img->hover_beschreibung_aktiv = $this->get_reports_galerie_vs_image_settings($gallery->gallery_settings, $img, 'hover_beschreibung_aktiv');
                $img->link_target = $this->get_reports_galerie_vs_image_settings($gallery->gallery_settings, $img, 'link_target');
                $imageArray[] = $img;
            }
            $gallery_settings = $gallery->gallery_settings;
            $gallery_type = $gallery->typeSettings;
        } else {
            return $record;
        }

        if (!$imageArray) {
            return $record;
        }

        $record->status = true;
        $record->gallery_settings = $gallery_settings;
        $record->gallery_type = $gallery_type;
        $record->gallery_images = $imageArray;
        return $record;
    }

    public function get_experience_report_gallery($galleryId): object
    {

        $record = new stdClass();
        $record->status = false;

        $args = 'WHERE folder="experience-reports-gallery" AND aktiv=1';
        $isDb = apply_filters($this->basename . '_get_extension', $args);
        if (!$isDb->status) {
            return $record;
        }

        $args = sprintf('WHERE id=%d', $galleryId);
        $galerie = apply_filters(REPORTS_GALLERY_BASENAME . '/post_selector_get_galerie', $args, false);
        if (!$galerie->status) {
            return $record;
        }

        $galerie = $galerie->record;
        $args = sprintf('WHERE galerie_id=%d ORDER BY position ASC', $galerie->id);
        $images = apply_filters(REPORTS_GALLERY_BASENAME . '/post_selector_get_images', $args);
        if (!$images->status) {
            return $record;
        }

        $record->gallery_settings = $galerie;
        $record->images = $images;
        $record->typeSettings = json_decode($galerie->type_settings);
        unset($record->gallery_settings->type_settings);
        $record->status = true;
        return $record;
    }

    private function get_reports_galerie_vs_image_settings($galSettings, $imgSettings, $type)
    {
        if ($imgSettings->galerie_settings_aktiv) {
            $return = $galSettings->$type;
        } else {
            $return = $imgSettings->$type;
        }
        return $return;
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

    public function get_report_category_name_by_id( $category_id ) {
        $term = get_term_by( 'id', $category_id, 'experience_reports_category', 'ARRAY_A' );
        return $term['name'];
    }

    /**
     * @throws Exception
     */
    public function make_experience_report_pagination($total, $limit, $paged, int $range = 2): string
    {

        if ($total == $limit) {
            return '';
        }

        $pages = ceil($total / $limit);
        if ($pages < 2) {
            return '';
        }


        $showitems = ($range * 2) + 1;
        $paged == (int)$pages ? $last = 'disabled' : $last = '';
        $paged == '1' ? $first = 'disabled' : $first = '';
        $html = '<div class="report-button-outer-wrapper">';
        $html .= '<nav class="experience-reports-pagination" aria-label="Page navigation" role="navigation">';
        $html .= '<span class="sr-only">Page navigation</span>';
        $html .= '<ul class="pagination justify-content-center ft-wpbs mb-4">';
        $html .= '<li class="page-item ' . $first . '"><a class="page-link" href="' . get_pagenum_link(1) . '" aria-label="First Page"><i class="fa fa-angle-double-left"></i></a></li>';
        $html .= '<li class="page-item ' . $first . '"><a class="page-link" href="' . get_pagenum_link($paged - 1) . '" aria-label="Previous Page"><i class="fa fa-angle-left"></i></a></li>';
        for ($i = 1; $i <= $pages; $i++) {
            if (1 != $pages && (!($i >= $paged + $range + 1 || $i <= $paged - $range - 1) || $pages <= $showitems)) {
                $html .= ($paged == $i) ? '<li class="page-item active"><span class="page-link"><span class="sr-only">Current Page </span>' . $i . '</span></li>' : '<li class="page-item"><a class="page-link" href="' . get_pagenum_link($i) . '"><span class="sr-only">Page </span>' . $i . '</a></li>';
            }
        }
        $html .= '<li class="page-item ' . $last . '"><a class="page-link" href="' . get_pagenum_link($paged + 1) . '" aria-label="Next Page"><i class="fa fa-angle-right"></i> </a></li>';
        $html .= '<li class="page-item ' . $last . '"><a class="page-link" href="' . get_pagenum_link($pages) . '" aria-label="Last Page"><i class="fa fa-angle-double-right"></i> </a></li>';
        $html .= '</ul>';
        $html .= '</nav>';
        $html .= '<div class="pagination-info mb-5 text-center"> <span class="text-muted">( Seite</span> ' . $paged . ' <span class="text-muted">von ' . $pages . ' )</span></div>';
        $html .= '</div>';
        return preg_replace(array('/<!--(.*)-->/Uis', "/[[:blank:]]+/"), array('', ' '), str_replace(array("\n", "\r", "\t"), '', $html));
    }

    public function make_load_more_button($total, $limit, $postId, $attr, $loaded = 1)
    {
        if ($total == $limit) {
            return '';
        }

        $html = '<div class="report-button-outer-wrapper">
                 <div class="report-load-more-inner-wrapper">
                 <button data-catId="'.$attr->selectedCategory.'" data-total="' . $total . '" 
                  data-limit="' . $limit . '" data-loaded="' . $loaded . '" data-post-id="' . $postId . '" data-method="load-more-action"
                  class="report-load-more-btn experience-report-actions-button '.$attr->showMoreButtonCss.'">
                 '.$attr->showMoreButtonIcon.' <span class="button-label">'.$attr->showMoreButtonLabel.'</span>
                 </button>
                 </div></div>';
        return preg_replace(array('/<!--(.*)-->/Uis', "/[[:blank:]]+/"), array('', ' '), str_replace(array("\n", "\r", "\t"), '', $html));
    }
}
