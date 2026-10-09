<?php
/*
Plugin Name: Disable REST API
Author: Zane Atega
*/

defined('ABSPATH') || exit;

remove_action('rest_api_init', 'wp_oembed_register_route'); // wp-includes/embed.php 
remove_action('rest_api_init', 'create_initial_rest_routes', 99);

// wp-includes/rest-api/class-wp-rest-server.php
function remove_batch_endpoints($endpoints) {
    foreach (array_keys($endpoints) as $route)
        if (str_starts_with($route, '/batch')) unset($endpoints[$route]);

    return $endpoints;
}
add_filter('rest_endpoints', 'remove_batch_endpoints');

function hide_rest_index($response, $request) {
    return new WP_REST_Response([]);
}
add_filter('rest_index', 'hide_rest_index', 10, 2);

function remove_application_passwords_from_index() {
    remove_filter('rest_index', 'rest_add_application_passwords_to_index');
}
add_action('rest_api_init', 'remove_application_passwords_from_index', 11, 1);