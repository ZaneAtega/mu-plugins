<?php
/*
Plugin Name: Utilities
Author: Zane Atega
*/

defined('ABSPATH') || exit;

function remove_comments($str) {
    return preg_replace('!/\*.*?\*/!s', ' ', $str);
}

function resize_image($img_url, $new_dimension = 1080, $delete = true) {
    if (!$img_url) return;

    $parts = explode(ROOT_DOMAIN.'/wp-content', $img_url, 2);

    if (isset($parts[1])) {
        $img_url = ABSPATH.'wp-content'.$parts[1];
        $is_upload = str_starts_with($parts[1], '/uploads');
    } else {
        $is_upload = false;
    }

    $size = getimagesize($img_url);
    if (!$size) return;

    $width = $size[0];
    $height = $size[1];

    $new_width = 0;
    $new_height = 0;

    if ($width > $new_dimension) {
        $new_width  = $new_dimension;
        $new_height = intval($height * ($new_width / $width));
    } else if ($height > $new_dimension) {
        $new_height = $new_dimension;
        $new_width  = intval($width * ($new_height / $height));
    }

    $mime_type = $size['mime'];

    switch ($mime_type) {
        case 'image/jpeg':
        case 'image/jpg':
            $src_image = imagecreatefromjpeg($img_url);
            break;
        case 'image/png':
            $src_image = imagecreatefrompng($img_url);
            break;
        case 'image/webp':
            $src_image = imagecreatefromwebp($img_url);
            break;
        default:
            return;
    }

    if ($new_width && $new_height) {
        $dst_image = imagecreatetruecolor($new_width, $new_height);

        if ($mime_type === 'image/png' || $mime_type === 'image/webp') {
            imagecolortransparent($dst_image, imagecolorallocatealpha($dst_image, 0, 0, 0, 127));
            imagealphablending($dst_image, false);
            imagesavealpha($dst_image, true);
        }

        imagecopyresampled($dst_image, $src_image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
    } else {
        $dst_image = $src_image;
    }

    imagedestroy($src_image);

    if ($delete && $is_upload) {
        global $wpdb;

        $attachment_id = $wpdb->get_var($wpdb->prepare('
            SELECT ID
            FROM '.$wpdb->posts.'
            WHERE post_type = \'attachment\'
                AND guid LIKE %s
            LIMIT 1
        ', '%'.$wpdb->esc_like(basename($img_url)).'%'));

        if ($attachment_id) wp_delete_attachment($attachment_id, true);
    }

    return $dst_image;
}