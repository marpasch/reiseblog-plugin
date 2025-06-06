<?php
// includes/class-route-gallery-map.php

if (!defined('ABSPATH')) {
    exit;
}

class Reiseblog_Route_Gallery_Map {

    public function __construct() {
        add_shortcode('reiseblog_route_gallery_map', [$this, 'render_route_gallery_map']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    public function enqueue_scripts() {
    // Flatpickr für Datepicker
    wp_enqueue_style('flatpickr-css', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css');
    wp_enqueue_script('flatpickr-js', 'https://cdn.jsdelivr.net/npm/flatpickr', [], null, true);

    wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet/dist/leaflet.css');
    wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet/dist/leaflet.js', [], null, true);

    // MarkerCluster
    wp_enqueue_style('leaflet-markercluster-css', 'https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.css');
    wp_enqueue_style('leaflet-markercluster-default-css', 'https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.Default.css');
    wp_enqueue_script('leaflet-markercluster-js', 'https://unpkg.com/leaflet.markercluster/dist/leaflet.markercluster.js', ['leaflet-js'], null, true);

    // Lightbox
    wp_enqueue_style('lightbox-css', 'https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css');
    wp_enqueue_script('lightbox-js', 'https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js', ['jquery'], null, true);

    wp_enqueue_script('reiseblog-route-gallery-map-js', REISEBLOG_PLUGIN_URL . 'assets/js/reiseblog-route-gallery-map.js', ['leaflet-js', 'leaflet-markercluster-js'], null, true);

    wp_localize_script('reiseblog-route-gallery-map-js', 'reiseblogRouteGalleryMap', [
        'positionApiUrl' => rest_url('reiseblog/v1/position'),
        'galleryApiUrl' => rest_url('reiseblog/v1/gallery')
    ]);
}


    public function render_route_gallery_map() {
        ob_start();
        ?>
        <div id="reiseblog-route-gallery-map" style="width: 100%; height: 500px;"></div>
        <?php
        return ob_get_clean();
    }
}
?>
