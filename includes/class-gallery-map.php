<?php
// includes/class-gallery-map.php

if (!defined('ABSPATH')) {
    exit;
}

class Reiseblog_Gallery_Map {

    public function __construct() {
        add_shortcode('reiseblog_gallery_map', [$this, 'render_gallery_map']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    public function enqueue_scripts() {
        wp_enqueue_style('lightbox-css', 'https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css');
        wp_enqueue_script('lightbox-js', 'https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js', ['jquery'], null, true);

        wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet/dist/leaflet.css');
        wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet/dist/leaflet.js', [], null, true);

        wp_enqueue_script('reiseblog-gallery-map-js', REISEBLOG_PLUGIN_URL . 'assets/js/reiseblog-gallery-map.js', ['leaflet-js'], null, true);

        wp_localize_script('reiseblog-gallery-map-js', 'reiseblogGalleryMap', [
            'apiUrl' => rest_url('reiseblog/v1/gallery')
        ]);
    }

    public function render_gallery_map() {
        ob_start();
        ?>
        <div id="reiseblog-gallery-map" style="width: 100%; height: 500px;"></div>
        <div id="reiseblog-gallery-thumbnails" style="margin-top: 20px; display: flex; flex-wrap: wrap; gap: 10px;"></div>
        <?php
        return ob_get_clean();
    }
}
?>
