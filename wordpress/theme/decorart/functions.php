<?php
if (!defined('ABSPATH')) { exit; }

function decorart_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'gallery', 'caption', 'style', 'script']);
}
add_action('after_setup_theme', 'decorart_setup');

function decorart_assets() {
    wp_enqueue_style('decorart-fonts', 'https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,600;12..96,700;12..96,800&family=Nunito+Sans:wght@400;500;600;700;800&display=swap', [], null);
    wp_enqueue_style('decorart-site', get_template_directory_uri() . '/assets/css/site.css', [], '1.0.0');
    wp_enqueue_script('decorart-builder', get_template_directory_uri() . '/assets/js/builder.js', [], '1.0.0', true);

    $items = [];
    $query = new WP_Query([
        'post_type' => 'da_item',
        'post_status' => 'publish',
        'posts_per_page' => 100,
        'orderby' => ['menu_order' => 'ASC', 'title' => 'ASC'],
    ]);
    foreach ($query->posts as $post) {
        $items[] = [
            'id' => (string) $post->ID,
            'name' => get_the_title($post),
            'description' => wp_strip_all_tags(get_the_excerpt($post)),
            'category' => (string) get_post_meta($post->ID, '_da_category', true),
            'price' => (float) get_post_meta($post->ID, '_da_price', true),
            'icon' => (string) (get_post_meta($post->ID, '_da_icon', true) ?: '✦'),
        ];
    }
    wp_localize_script('decorart-builder', 'DecorArtCatalog', [
        'items' => $items,
        'isDemo' => true,
        'instagram' => 'https://www.instagram.com/decor4rt_/',
    ]);
}
add_action('wp_enqueue_scripts', 'decorart_assets');

function decorart_body_classes($classes) {
    $classes[] = 'decorart-theme';
    return $classes;
}
add_filter('body_class', 'decorart_body_classes');
