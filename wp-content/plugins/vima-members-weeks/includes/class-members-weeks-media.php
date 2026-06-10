<?php

if (!defined('ABSPATH')) exit;

class Members_Weeks_Media
{
    public static function sideload_from_request_file($fileArray)
    {
        if (empty($fileArray) || empty($fileArray['tmp_name'])) {
            return null;
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $overrides = ['test_form' => false];
        $uploaded = wp_handle_upload($fileArray, $overrides);

        if (!empty($uploaded['error'])) {
            return new \WP_Error('upload_error', $uploaded['error'], ['status' => 422]);
        }

        $attachment = [
            'post_mime_type' => $uploaded['type'],
            'post_title'     => sanitize_file_name(basename($uploaded['file'])),
            'post_content'   => '',
            'post_status'    => 'inherit',
        ];

        $attachment_id = wp_insert_attachment($attachment, $uploaded['file']);

        if (is_wp_error($attachment_id)) {
            return $attachment_id;
        }

        $attach_data = wp_generate_attachment_metadata($attachment_id, $uploaded['file']);
        wp_update_attachment_metadata($attachment_id, $attach_data);

        return [
            'id'  => $attachment_id,
            'url' => wp_get_attachment_url($attachment_id),
        ];
    }
}
