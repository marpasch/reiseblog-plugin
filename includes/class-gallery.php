<?php
// includes/class-gallery.php

if (!defined('ABSPATH')) {
    exit;
}

class Reiseblog_Gallery {

    public function __construct() {
        add_action('add_attachment', [$this, 'handle_new_image']);
        add_action('rest_api_init', [$this, 'register_gallery_route']);
    }

    public function create_gallery_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'reiseblog_gallery';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            image_url TEXT NOT NULL,
            latitude DOUBLE NOT NULL,
            longitude DOUBLE NOT NULL,
            timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
            description TEXT
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function handle_new_image($attachment_id) {
        $mime_type = get_post_mime_type($attachment_id);

        // Nur Bilder verarbeiten
        if (strpos($mime_type, 'image/') !== 0) {
            return;
        }

        $file_path = get_attached_file($attachment_id);

        if (!function_exists('exif_read_data')) {
            return;
        }

        $exif = @exif_read_data($file_path, 0, true);

        if (!$exif || !isset($exif['GPS'])) {
            return;
        }

        $latitude = $this->get_gps($exif['GPS']['GPSLatitude'], $exif['GPS']['GPSLatitudeRef']);
        $longitude = $this->get_gps($exif['GPS']['GPSLongitude'], $exif['GPS']['GPSLongitudeRef']);

        if ($latitude && $longitude) {
            global $wpdb;

            $table_name = $wpdb->prefix . 'reiseblog_gallery';

            $image_url = wp_get_attachment_url($attachment_id);

            $timestamp = isset($exif['EXIF']['DateTimeOriginal']) ? 
                date('Y-m-d H:i:s', strtotime($exif['EXIF']['DateTimeOriginal'])) : 
                current_time('mysql');

            $wpdb->insert(
                $table_name,
                [
                    'image_url' => $image_url,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'timestamp' => $timestamp,
                    'description' => ''
                ],
                ['%s', '%f', '%f', '%s', '%s']
            );
        }
    }

    private function get_gps($exifCoord, $hemi) {
        if (!$exifCoord) {
            return false;
        }

        $degrees = count($exifCoord) > 0 ? $this->gps2Num($exifCoord[0]) : 0;
        $minutes = count($exifCoord) > 1 ? $this->gps2Num($exifCoord[1]) : 0;
        $seconds = count($exifCoord) > 2 ? $this->gps2Num($exifCoord[2]) : 0;

        $flip = ($hemi == 'W' || $hemi == 'S') ? -1 : 1;

        return $flip * ($degrees + ($minutes / 60) + ($seconds / 3600));
    }

    private function gps2Num($coordPart) {
        $parts = explode('/', $coordPart);
        if (count($parts) <= 0) {
            return 0;
        }

        if (count($parts) == 1) {
            return (float)$parts[0];
        }

        return (float)$parts[0] / (float)$parts[1];
    }

    public function register_gallery_route() {
        register_rest_route('reiseblog/v1', '/gallery', [
            'methods' => 'GET',
            'callback' => [$this, 'get_gallery_images'],
            'permission_callback' => '__return_true',
        ]);
    }

    public function get_gallery_images($request) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'reiseblog_gallery';
        $results = $wpdb->get_results("SELECT id, image_url, latitude, longitude, timestamp, description FROM $table_name ORDER BY id ASC", ARRAY_A);

        return $results;
    }
}
?>
