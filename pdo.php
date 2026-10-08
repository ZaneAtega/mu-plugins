<?php
/*
Plugin Name: PDO
Author: Zane Atega
*/

defined('ABSPATH') || exit;

global $pdo;

$pdo = new PDO('mysql:host=localhost;dbname=;charset=utf8mb4', 'username', 'password', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false
]);

/* --- */

$pdo_meta = [];

function pdo_update_meta_cache($ids) {
    global $pdo, $pdo_meta;

    $rows = $pdo->query('
        SELECT post_id, meta_key, meta_value
        FROM wp_postmeta
        WHERE post_id IN (' . implode(',', $ids) . ')
    ')->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $row)
        if (!isset($pdo_meta[$row['post_id']][$row['meta_key']]))
            $pdo_meta[$row['post_id']][$row['meta_key']] = $row['meta_value'];
}

function set_pdo_meta($id) {
    global $pdo, $pdo_meta;

    $rows = $pdo->query('SELECT meta_key, meta_value FROM wp_postmeta WHERE post_id = ' . absint($id))->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $row)
        if (!isset($pdo_meta[$id][$row['meta_key']]))
            $pdo_meta[$id][$row['meta_key']] = $row['meta_value'];
}

function pdo_get_post_meta($id, $key, $_) {
    global $pdo_meta;
    if (!isset($pdo_meta[$id])) set_pdo_meta($id);
    return isset($pdo_meta[$id][$key]) ? maybe_unserialize($pdo_meta[$id][$key]) : '';
}

/* --- */

function pdo_get_the_post_thumbnail_url($id, $_) {
    global $pdo_meta;
    if (!isset($pdo_meta[$id])) set_pdo_meta($id);

    $thumb_id = $pdo_meta[$id]['_thumbnail_id'] ?? 0;
    if (!$thumb_id) return '';

    if (!isset($pdo_meta[$thumb_id])) set_pdo_meta($thumb_id);

    $url = $pdo_meta[$thumb_id]['_wp_attached_file'] ?? '';
    if (!$url) return '';

    return home_url('wp-content/uploads/' . (str_ends_with($url, '.webp') ? $url : $url.'.webp'));
}

function pdo_get_attachment_image_src_get_post_thumbnail_id($id) {
    $guid = pdo_get_the_post_thumbnail_url($id, '');
    if (!$guid) return ['', '', ''];

    global $pdo_meta;
    $metadata = unserialize($pdo_meta[$pdo_meta[$id]['_thumbnail_id']]['_wp_attachment_metadata']);
    return isset($metadata['width']) ? [$guid, $metadata['width'], $metadata['height']] : ['', '', ''];
}

function pdo_get_attachment_url($id) {
    global $pdo_meta;
    if (!isset($pdo_meta[$id])) set_pdo_meta($id);

    $url = $pdo_meta[$id]['_wp_attached_file'];
    return home_url('wp-content/uploads/' . (str_ends_with($url, '.mp4') ? $url : $url.'.mp4'));
}

function pdo_get_attachment_metadata($id) {
    return pdo_get_post_meta($id, '_wp_attachment_metadata', true);
}

function pdo_after_setup_theme() {
    remove_theme_support('post-thumbnails');
}
add_action('after_setup_theme', 'pdo_after_setup_theme', 999);

/* --- */

function pdo_get_post_tags($id) {
    global $pdo;
	
    return $pdo->query('
        SELECT t.name, t.slug
        FROM wp_terms t
        INNER JOIN wp_term_taxonomy tt
            ON t.term_id = tt.term_id
        INNER JOIN wp_term_relationships tr
            ON tr.term_taxonomy_id = tt.term_taxonomy_id
        WHERE tt.taxonomy = \'post_tag\'
          AND tr.object_id = '.absint($id).'
        ORDER BY t.name
    ')->fetchAll(PDO::FETCH_OBJ);
}

/* --- */

function pdo_query_vars($vars) {
    //
}
add_filter('query_vars', 'pdo_query_vars');

function pdo_template_redirect() {
    /*
    $post = $pdo->query('
        SELECT post_name, post_title, post_author, post_content, post_excerpt, post_modified, post_modified_gmt
        FROM wp_posts
        WHERE post_type = \'post\'
            AND ID = '.absint($id).'
        LIMIT 1
    ')->fetch(PDO::FETCH_OBJ);
    
    !$post: 404
    !== $post->post_name: 301
    require_once single.php

    !t.name: 404
    */
}
add_action('template_redirect', 'pdo_template_redirect');