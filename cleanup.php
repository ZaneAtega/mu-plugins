<?php
/*
Plugin Name: Cleanup
Author: Zane Atega
*/

defined('ABSPATH') || exit;

function clean_action_scheduler() {
    if (!class_exists('ActionScheduler')) return;

    $store = ActionScheduler::store();

    $actions = as_get_scheduled_actions([
        // 'status' => ActionScheduler_Store::STATUS_, // PENDING CANCELED
        'per_page' => -1
    ], 'OBJECT');

    foreach ($actions as $action_id => $action) {
        // error_log($action_id . ' ' . print_r($action, true));
        // as_unschedule_all_actions($action->get_hook(), $action->get_args(), $action->get_group());
        $store->delete_action($action_id);
    }
}

function clean_sessions_folder() {
    $dir = session_save_path();
    if (!is_dir($dir)) return;

    $items = scandir($dir);

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (!is_dir($path)) @unlink($path);
    }
}