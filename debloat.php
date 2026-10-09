<?php
/*
Plugin Name: Debloat WP
Author: Zane Atega
*/

defined('ABSPATH') || exit;

function remove_wp_block_styles() {
    remove_theme_support('wp-block-styles');
}
add_action('after_setup_theme', 'remove_wp_block_styles');

remove_action('setup_theme', '_delete_site_logo_on_remove_custom_logo_on_setup_theme', 11);
remove_action('after_setup_theme', '_add_default_theme_supports', 1);
remove_action('after_setup_theme', 'wp_setup_widgets_block_editor', 1);

function debloat_init() {
    if (class_exists('ActionScheduler'))
        remove_action('action_scheduler_run_queue', [ActionScheduler::runner(), 'run']);

    remove_post_type_support('post', 'post-formats');
}
add_action('init', 'debloat_init');

foreach ([
    'wp_initialize_site_preview_hooks' => 1,
    'smilies_init' => 5,
    'register_core_block_style_handles' => 9,
    'wp_register_core_block_metadata_collection' => 9,
    '_wp_register_default_connector_settings' => 20,
    '_wp_connectors_pass_default_keys_to_ai_client' => 20,
    'wp_custom_css_kses_init' => 20
] as $action => $priority) remove_action('init', $action, $priority);

foreach ([
    'register_block_core_legacy_widget',
    'wp_create_initial_comment_meta',
    'wp_cron',
    'wp_schedule_delete_old_privacy_export_files',
    'wp_schedule_personal_data_cleanup_requests',
    '_show_post_preview',
    '_wp_footnotes_kses_init',
    'wp_register_persisted_preferences_meta',
    'wp_create_initial_post_meta',
    'wp_schedule_update_checks',
    '_register_block_bindings_pattern_overrides_source',
    '_register_block_bindings_post_data_source',
    '_register_block_bindings_post_meta_source',
    '_register_block_bindings_term_data_source'
] as $action) remove_action('init', $action, 10);

foreach ([
    'pre_as_schedule_single_action',
    'pre_as_schedule_recurring_action',
    'pre_as_enqueue_async_action',
    'pre_as_schedule_cron_action'
] as $filter) add_filter($filter, '__return_false', 25);

remove_action('template_redirect', 'rest_output_link_header', 11);
remove_action('template_redirect', 'wp_shortlink_header', 11);
remove_action('template_redirect', 'wp_redirect_admin_locations', 1000);

function deq_der($css, $js) {
    foreach ($css as $d) {
        wp_dequeue_style($d);
        wp_deregister_style($d);
    } 
    foreach ($js as $d) {
        wp_dequeue_script($d);
        wp_deregister_script($d);
    }
}

function deq_der_wp_styles() {
    deq_der(
        ['wp-block-library', 'wp-block-library-theme', 'wp-block-styles-placeholder', 'wp-global-styles', 'classic-theme-styles', 'global-styles'],
        ['heartbeat', 'autosave', 'wp-auth-check']
    );
}
add_action('wp_enqueue_scripts', 'deq_der_wp_styles');

remove_action('wp_enqueue_scripts', 'wp_enqueue_block_style_variation_styles', 1);
remove_action('wp_enqueue_scripts', 'wp_enqueue_block_custom_css', 1);
remove_action('wp_enqueue_scripts', 'wp_localize_jquery_ui_datepicker', 1000);

foreach ([
    'wp_enqueue_global_styles',
    'wp_enqueue_emoji_styles',
    'wp_enqueue_classic_theme_styles',
    'wp_enqueue_stored_styles',
    'wp_enqueue_admin_bar_bump_styles',
    'wp_enqueue_admin_bar_header_styles',
    'wp_enqueue_block_template_skip_link'
] as $action) remove_action('wp_enqueue_scripts', $action, 10);

foreach ([
    'rest_output_link_wp_head',
    'rsd_link',
    'locale_stylesheet',
    'wp_generator',
    'wp_shortlink_wp_head',
    '_custom_logo_header_styles',
    'wp_oembed_add_discovery_links',
    'wp_oembed_add_host_js',
    'rel_canonical'
] as $action) remove_action('wp_head', $action, 10);

foreach ([
    'wp_enqueue_img_auto_sizes_contain_css_fix' => 0,
    'wp_print_auto_sizes_contain_css_fix' => 1,
    '_wp_render_title_tag' => 1,
    'wp_preload_resources' => 1,
    'wp_post_preview_js' => 1,
    'wp_maybe_inline_styles' => 1,
    'wp_resource_hints' => 2,
    'feed_links' => 2,
    'feed_links_extra' => 3,
    'wp_oembed_add_discovery_links' => 4,
    'print_emoji_detection_script' => 7,
    'wp_print_font_faces' => 50
] as $action => $priority) remove_action('wp_head', $action, $priority);

remove_action('wp_print_styles', 'print_emoji_styles');
add_filter('emoji_svg_url', '__return_false');

add_filter('wp_speculation_rules_configuration', '__return_null');
add_filter('render_block', '__return_empty_string', 20, 2);
add_filter('wp_img_tag_add_auto_sizes', '__return_false');

remove_action('wp_footer', 'wp_enqueue_global_styles', 1);
remove_action('wp_footer', 'wp_enqueue_stored_styles', 1);
remove_action('wp_footer', 'wp_maybe_inline_styles', 1);
remove_action('wp_footer', 'wp_print_speculation_rules', 10);
remove_action('wp_footer', 'the_block_template_skip_link', 10);

global $wp_embed;
remove_filter('the_content', 'apply_block_hooks_to_content_from_post_object', 8);
remove_filter('the_content', [$wp_embed, 'run_shortcode'], 8);
remove_filter('the_content', [$wp_embed, 'autoembed'], 8);
remove_filter('the_content', 'do_blocks', 9);
remove_filter('the_content', 'wptexturize', 10);
remove_filter('the_content', 'wpautop', 10);
remove_filter('the_content', 'shortcode_unautop', 10);
remove_filter('the_content', 'prepend_attachment', 10);
remove_filter('the_content', 'wp_replace_insecure_home_url', 10);
remove_filter('the_content', 'capital_P_dangit', 11);
remove_filter('the_content', 'do_shortcode', 11);
remove_filter('the_content', 'wp_filter_content_tags', 12); // HTML tags
remove_filter('the_content', 'convert_smilies', 20);

remove_action('save_post', 'delete_get_calendar_cache');
remove_action('publish_post', '_delete_option_fresh_site', 0);
remove_action('publish_post', '_publish_post_hook', 5);
remove_action('transition_post_status', '_transition_post_status', 5);
remove_action('transition_post_status', '_wp_keep_alive_customize_changeset_dependent_auto_drafts', 20);

foreach ([
    '_update_blog_date_on_post_publish',
    '_update_posts_count_on_transition_post_status',
    '_update_term_count_on_transition_post_status',
    '_wp_auto_add_pages_to_menu',
    '_wp_customize_publish_changeset',
    '__clear_multi_author_cache',
    'block_core_calendar_update_has_published_post_on_transition_post_status'
] as $action) remove_action('transition_post_status', $action, 10);

remove_action('plugins_loaded', 'wp_maybe_load_embeds', 0);
remove_action('plugins_loaded', 'wp_maybe_load_widgets', 0);
remove_action('init', 'wp_widgets_init', 1);
remove_action('plugins_loaded', '_wp_add_additional_image_sizes', 0);
remove_action('plugins_loaded', 'wp_initialize_theme_preview_hooks', 1);
remove_action('plugins_loaded', '_wp_customize_include', 10);

/* --- Admin --- */

remove_action('plugins_loaded', 'action_scheduler_register_4_dot_1_dot_0', 0);

foreach ([
    'wp_schedule_update_user_counts',
    '_wp_check_for_scheduled_split_terms',
    '_wp_check_for_scheduled_update_comment_type',
    'wp_set_client_side_media_processing_flag',
    'wp_font_library_intercept_render',
    'wp_options_connectors_intercept_render',
    '_maybe_update_core',
    '_maybe_update_plugins',
    '_maybe_update_themes'
] as $action) remove_action('admin_init', $action, 10);

remove_action('admin_init', 'handle_legacy_widget_preview_iframe', 20);

function debloat_admin_init() {
    remove_action('wp_head', 'wp_admin_bar_header');
    remove_action('admin_head', 'wp_admin_bar_header');
}
add_action('admin_init', 'debloat_admin_init');

foreach ([
    '_wp_customize_loader_settings',
    'wp_enqueue_view_transitions_admin_css',
    'wp_enqueue_admin_bar_header_styles',
    'wp_font_library_wp_admin_enqueue_scripts',
    'wp_options_connectors_wp_admin_enqueue_scripts',
    'wp_enqueue_emoji_styles'
] as $action) remove_action('admin_enqueue_scripts', $action, 10);

remove_action('admin_enqueue_scripts', 'wp_localize_jquery_ui_datepicker', 1000);

remove_action('wp_head', 'wp_site_icon', 99);
remove_action('wp_default_styles', 'wp_load_classic_theme_block_styles_on_demand', 0);

function debloat_admin_footer_scripts() {
    remove_action('admin_print_footer_scripts', ['_WP_Editors', 'force_uncompressed_tinymce'], 1);
    remove_action('admin_print_footer_scripts', ['_WP_Editors', 'enqueue_scripts'], 1);
    remove_action('admin_print_footer_scripts', ['_WP_Editors', 'editor_js'], 50);

    remove_action('admin_print_footer_scripts', '_print_emoji_detection_script', 10);
}
add_action('admin_print_footer_scripts', 'debloat_admin_footer_scripts', 0);

/* --- */

// The remaining ones aren't worth organizing; I just traced them with Xdebug and removed everything unnecessary.

remove_action('init', '_wp_register_default_icons');
remove_action('init', '_wp_connectors_init', 15); // _wp_connectors_register_default_ai_providers wp-includes/connectors.php
remove_action('init', '_wp_register_default_font_collections');
remove_action('wp_head', 'wp_custom_css_cb', 101);
remove_action('init', 'wp_sitemaps_get_server');
remove_action('setup_theme', 'create_initial_theme_features', 0);
remove_action('wp_default_styles', 'wp_default_styles');
remove_action('after_setup_theme', 'wp_enable_block_templates', 1);
remove_action('init', 'register_core_block_types_from_metadata');
remove_action('init', ['WP_Block_Supports', 'init'], 22);
remove_action('init', 'register_block_core_widget_group');
remove_action('init', 'register_block_core_accordion');
remove_action('init', 'register_block_core_accordion_item');
remove_action('init', '_register_core_block_patterns_and_categories');
remove_action('init', 'register_block_core_button');
remove_action('init', '_register_theme_block_patterns');
remove_action('init', 'register_block_core_post_author');
remove_action('init', 'register_block_core_post_excerpt');
remove_action('init', 'register_block_core_rss');
remove_action('init', 'register_block_core_footnotes');
remove_action('init', 'register_block_core_footnotes_post_meta', 20);
remove_action('init', 'register_block_core_comments');
remove_action('init', 'register_block_core_comment_reply_link');
remove_action('init', 'register_block_core_media_text');
remove_action('init', 'register_block_core_post_date');
remove_action('init', 'register_block_core_term_description');
remove_action('init', 'register_block_core_query_pagination_numbers');
remove_action('init', 'register_legacy_post_comments_block', 21);
remove_filter('register_block_type_args', 'wp_mark_auto_generate_control_attributes', 5);
remove_action('init', 'register_block_core_image');
remove_action('init', 'register_block_core_tab_list');
remove_action('init', 'register_block_core_details');
remove_action('init', 'register_block_core_avatar');
remove_action('init', 'register_block_core_search');
remove_action('init', 'register_block_core_categories');
remove_action('init', 'register_block_core_post_comments_form');
remove_action('init', 'register_block_core_comments_pagination_numbers');
remove_action('init', 'register_block_core_cover');
remove_action('init', 'register_block_core_video');
remove_action('init', 'register_block_core_post_comments_link');
remove_action('init', 'register_block_core_query_total');
remove_action('init', 'register_block_core_tab_panel');
remove_action('init', 'register_block_core_post_title');
remove_action('init', 'register_block_core_calendar');
remove_action('init', 'register_block_core_navigation_link');
remove_action('init', 'register_block_core_icon');
remove_action('init', 'register_block_core_site_logo');
remove_action('rest_api_init', 'register_block_core_site_logo_setting', 10);
remove_action('init', 'register_block_core_page_list');
remove_action('init', 'register_block_core_navigation');
remove_action('init', 'register_block_core_playlist');
remove_action('init', 'register_block_core_gallery');
remove_action('init', 'register_block_core_site_tagline');
remove_action('init', 'register_block_core_file');
remove_action('init', 'register_block_core_latest_posts');
remove_action('init', 'register_block_core_post_featured_image');
remove_action('init', 'register_block_core_paragraph');
remove_action('init', 'register_block_core_comments_pagination');
remove_action('init', 'register_block_core_site_title');
remove_action('init', 'register_block_core_query_pagination');
remove_action('init', 'register_block_core_latest_comments');
remove_action('init', 'register_block_core_comments_pagination_next');
remove_action('init', 'register_block_core_post_time_to_read');
remove_action('init', 'register_block_core_template_part');
remove_action('init', 'register_block_core_query');
remove_action('init', 'register_block_core_navigation_overlay_close');
remove_action('init', 'register_block_core_breadcrumbs');
remove_action('init', 'register_block_core_heading');
remove_action('init', 'register_block_core_read_more');
remove_action('init', 'register_block_core_comment_edit_link');
remove_action('init', 'register_block_core_list');
remove_action('init', 'register_block_core_loginout');
remove_action('init', 'register_block_core_playlist_track');
remove_action('init', 'register_block_core_post_template');
remove_action('init', 'register_block_core_tag_cloud');
remove_action('init', 'register_block_core_comment_template');
remove_action('init', 'register_block_core_navigation_submenu');
remove_action('init', 'register_block_core_comment_content');
remove_action('init', 'register_block_core_block');
remove_action('init', 'register_block_core_comment_author_name');
remove_action('init', 'register_block_core_post_comments_count');
remove_action('init', 'register_block_core_post_content');
remove_action('init', 'register_block_core_comment_date');
remove_action('init', 'register_block_core_post_author_biography');
remove_action('init', 'register_block_core_post_navigation_link');
remove_action('init', 'register_block_core_comments_title');
remove_action('init', 'register_block_core_term_template');
remove_action('init', 'register_block_core_post_author_name');
remove_action('init', 'register_block_core_term_name');
remove_action('init', 'register_block_core_post_terms');
remove_action('init', 'register_block_core_archives');
remove_action('init', 'register_block_core_tabs');
remove_action('init', 'register_block_core_comments_pagination_previous');
remove_action('wp_enqueue_scripts', 'wp_common_block_scripts_and_styles');
remove_action('admin_enqueue_scripts', 'wp_common_block_scripts_and_styles');
remove_action('init', 'register_block_core_home_link');
remove_action('init', 'register_block_core_page_list_item');
remove_action('init', 'register_block_core_query_no_results');
remove_filter('render_block', ['WP_Duotone', 'render_duotone_support'], 10);
remove_filter('render_block_core/image', ['WP_Duotone', 'restore_image_outer_container'], 10);
remove_action('wp_enqueue_scripts', ['WP_Duotone', 'output_block_styles'], 9);
remove_action('wp_enqueue_scripts', ['WP_Duotone', 'output_global_styles'], 11);
remove_action('wp_footer', ['WP_Duotone', 'output_footer_assets'], 10);
remove_filter('block_editor_settings_all', ['WP_Duotone', 'add_editor_settings'], 10);
remove_filter('block_type_metadata_settings', ['WP_Duotone', 'migrate_experimental_duotone_support_flag'], 10);
remove_action('init', 'register_block_core_pattern');
remove_action('init', 'register_block_core_query_title');
remove_action('init', 'register_block_core_query_pagination_previous');
remove_action('init', 'register_block_core_query_title');
remove_action('init', 'register_block_core_term_count');
remove_action('init', 'register_block_core_query_pagination_next');
remove_action('init', 'register_block_core_shortcode');
remove_action('wp_body_open', 'wp_admin_bar_render', 0);
remove_action('wp_footer', 'wp_admin_bar_render', 1000);
remove_action('in_admin_header', 'wp_admin_bar_render', 0);
remove_action('admin_enqueue_scripts', 'wp_enqueue_command_palette_assets');
remove_action('admin_init', 'register_admin_color_schemes', 1);
remove_action('template_redirect', '_wp_admin_bar_init', 0);
remove_action('admin_init', '_wp_admin_bar_init');
remove_action('before_signup_header', '_wp_admin_bar_init');
remove_action('activate_header', '_wp_admin_bar_init');
remove_action('init', 'register_block_core_social_link');