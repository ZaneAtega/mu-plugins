<?php
/*
Plugin Name: Strip Down WP Admin
Author: Zane Atega
*/

defined('ABSPATH') || exit;

add_filter('show_admin_bar', '__return_false');
remove_action('admin_enqueue_scripts', 'wp_auth_check_load');

add_action('pre_get_posts', function ($query) {
    $query->set('no_found_rows', true); // skip counting the total number of matching rows
});

add_filter('pre_get_lastpostmodified', function() {
    return gmdate('Y-m-d H:i:s');
});

wp_defer_term_counting(true);

add_filter('query', function($query) {
    if (!is_admin()) return $query;

    if (str_starts_with($_SERVER['REQUEST_URI'], '/wp-admin/edit-tags.php?taxonomy=post_tag')) {
        if (str_starts_with($query, 'SELECT  t.term_id') && !str_starts_with($query, 'SELECT  t.term_id,')) {
            return 'SELECT term_id FROM wp_terms WHERE term_id = 400';
        } else if (str_starts_with($query, 'SELECT  COUNT(*)') && str_contains($query, 'FROM wp_terms AS t')) {
            return 'SELECT 1';
        }
    }

    return $query;
});

/* --- Media --- */

function za_upload_dir($dirs) {
    if (!empty($dirs['error'])) return $dirs;
    
    $dirs['subdir'] = '/' . str_replace('-', '/', explode(' ', current_time('mysql'))[0]);
    $dirs['path'] = $dirs['basedir'] . $dirs['subdir'];
    $dirs['url'] = $dirs['baseurl'] . $dirs['subdir'];
    
    return $dirs;
}
add_filter('upload_dir', 'za_upload_dir');

add_filter('big_image_size_threshold', '__return_false');
add_filter('intermediate_image_sizes_advanced', '__return_empty_array');

add_filter('ajax_query_attachments_args', function ($args) {
    $args['orderby'] = 'ID';
    $args['posts_per_page'] = 5;
    $args['post_status'] = 'inherit';

    unset($args['meta_query']);

    return $args;
}, 15, 1);

add_filter('media_library_months_with_files', function () {
    return [];
});