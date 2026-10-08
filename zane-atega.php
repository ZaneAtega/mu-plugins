<?php
/*
Plugin Name: Zane Atega
*/

defined('ABSPATH') || exit;

remove_action('template_redirect', 'wp_old_slug_redirect');

function za_redirect_canonical($redirect_url, $requested_url) {
    return empty($_GET) ? rtrim($redirect_url, '/') : str_replace('/?', '?', $redirect_url);
}
add_filter('redirect_canonical', 'za_redirect_canonical', 10, 2);

function za_get_permalink($permalink, $post, $leavename) {
    return rtrim($permalink, '/');
}
add_filter('post_link', 'za_get_permalink', 10, 3);

/* --- */

define('THEME_ASSETS', get_template_directory_uri().'/assets/');

function za_after_setup_theme() {
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['style', 'script']);
}
add_action('after_setup_theme', 'za_after_setup_theme');

/* --- */

function lang_attrs() {
    return 'lang="'.$_COOKIE['lang'].'"';
}
add_filter('language_attributes', 'lang_attrs');

function za_head() {
    echo '<meta charset="'.get_bloginfo('charset').'">
    <meta name="viewport" content="width=device-width,initial-scale=1">';

    if (defined('GTAG')) echo '<script async src="https://www.googletagmanager.com/gtag/js?id='.GTAG.'"></script>
    <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag("js",new Date());gtag("config","'.GTAG.'")</script>';
}
add_action('wp_head', 'za_head', 1);

function remove_version_strings($src) {
    return explode('?', $src, 2)[0];
}
add_filter('script_loader_src', 'remove_version_strings');
add_filter('style_loader_src', 'remove_version_strings');

/* --- */

wp_cache_add_non_persistent_groups([
    'posts',
    'post_meta',
    'post-queries',
    'post_tag_relationships',
    'post_format_relationships',
    'category_relationships',
    'term-queries',
    'comment',
    'comment-queries',
    'timeinfo'
]);