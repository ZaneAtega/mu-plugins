<?php
/*
Plugin Name: Profiling Tools
Author: Zane Atega
*/

defined('ABSPATH') || exit;

return;

function log_queries() {
    // wp-config.php define('SAVEQUERIES', true);

    if (str_starts_with($_SERVER['REQUEST_URI'], '/wp-json/wp/')) return;
    error_log($_SERVER['REQUEST_URI']);

    global $wpdb;
    // $count = $time = $count_2 = 0;
    // $queries = [];

    foreach ($wpdb->queries as $query) {
        if (str_contains($query[0], 'search_text')) continue;

        if (!isset($count_2)) { // && $query[1] > 0.05) {
            error_log(print_r($query, true));
            continue;
        }

        $count += 1;
        $time += $query[1];
		
        if ($query[1] > 0.001) {
            $count_2 += 1;
            $queries[] = $query;
        }
    }

    if (isset($count_2) && $time > 0.05)
        error_log($time."s ($count_2/$count queries) " . print_r($queries, true));
}
add_action('shutdown', 'log_queries');

function log_filters_styles_scripts() {
    global $wp_filter;

    foreach ($wp_filter as $hook => $wp_hook) {
        $output = [];

        foreach ($wp_hook->callbacks as $priority => $callbacks)
            $output[$priority] = array_keys($callbacks);

        error_log($hook . ' ' . print_r($output, true));
    }

    error_log(print_r(wp_styles()->registered, true));
    error_log(print_r(wp_scripts()->registered, true));
}
add_action('shutdown', 'log_filters_styles_scripts');

add_action('all', function () {
    // if (defined('DOING_AJAX') && DOING_AJAX) return;
    error_log(current_filter());
});
add_action('shutdown', function () {
    error_log($_SERVER['REQUEST_URI']);
});

/*
/etc/php/ /fpm/pool.d/www.conf
;slowlog = log/$pool.log.slow -> /var/log/php-fpm-slow.log
;request_slowlog_timeout = 0 -> 1s
;request_slowlog_trace_depth = 20

ps aux | grep php
strace -p 1234 -f -c
strace -f -p 1234 -e trace=newfstatat -s 200 2>&1 | head -100
*/