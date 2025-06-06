<?php
// includes/class-owntracks.php

if (!defined('ABSPATH')) {
    exit;
}

class Reiseblog_OwnTracks {

    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_shortcode('reiseblog_map', [$this, 'render_map_shortcode']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_leaflet_assets']);
    }

    public function register_routes() {
        register_rest_route('reiseblog/v1', '/position', [
            'methods' => 'POST',
            'callback' => [$this, 'save_position'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('reiseblog/v1', '/position', [
            'methods' => 'GET',
            'callback' => [$this, 'get_positions'],
            'permission_callback' => '__return_true',
        ]);
    }

    public function save_position($request) {
        global $wpdb;

        $latitude = $request->get_param('lat');
        $longitude = $request->get_param('lon');
        $timestamp_unix = $request->get_param('tst');

        if (empty($latitude) || empty($longitude) || empty($timestamp_unix)) {
            return new WP_Error('missing_data', 'Latitude, Longitude und Timestamp (tst) sind erforderlich.', ['status' => 400]);
        }

        $timestamp = date('Y-m-d H:i:s', intval($timestamp_unix));

        $table_name = $wpdb->prefix . 'reise_positions';

        $result = $wpdb->insert(
            $table_name,
            [
                'latitude' => sanitize_text_field($latitude),
                'longitude' => sanitize_text_field($longitude),
                'timestamp' => $timestamp,
            ],
            ['%f', '%f', '%s']
        );

        if (false === $result) {
            return new WP_Error('db_error', 'Fehler beim Speichern der Position.', ['status' => 500]);
        }

        return [
            'success' => true,
            'message' => 'Position gespeichert.',
            'data' => [
                'id' => $wpdb->insert_id,
                'lat' => $latitude,
                'lon' => $longitude,
                'timestamp' => $timestamp,
            ]
        ];
    }

    public function get_positions($request) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'reise_positions';
        $results = $wpdb->get_results("SELECT latitude AS lat, longitude AS lon, timestamp FROM $table_name ORDER BY id ASC", ARRAY_A);

        return $results;
    }

    public function add_admin_menu() {
        add_menu_page(
            'Reiseblog Positionen',
            'Reiseblog',
            'manage_options',
            'reiseblog_positions',
            [$this, 'render_positions_page'],
            'dashicons-location-alt',
            6
        );
    }

    public function render_positions_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'reise_positions';

        if (isset($_POST['delete_selected']) && !empty($_POST['delete_ids'])) {
            $ids_to_delete = array_map('intval', $_POST['delete_ids']);
            $ids_placeholder = implode(',', array_fill(0, count($ids_to_delete), '%d'));
            $query = "DELETE FROM $table_name WHERE id IN ($ids_placeholder)";
            $wpdb->query($wpdb->prepare($query, ...$ids_to_delete));
            echo '<div class="updated notice"><p>Ausgewählte Einträge wurden gelöscht!</p></div>';
        }

        $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC");

        echo '<div class="wrap">';
        echo '<h1>Gespeicherte Positionen</h1>';

        if ($results) {
            echo '<form method="post">';
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead><tr><th><input type="checkbox" id="select-all"></th><th>ID</th><th>Latitude</th><th>Longitude</th><th>Timestamp</th></tr></thead>';
            echo '<tbody>';

            foreach ($results as $row) {
                echo '<tr>';
                echo '<td><input type="checkbox" name="delete_ids[]" value="' . esc_attr($row->id) . '"></td>';
                echo '<td>' . esc_html($row->id) . '</td>';
                echo '<td>' . esc_html($row->latitude) . '</td>';
                echo '<td>' . esc_html($row->longitude) . '</td>';
                echo '<td>' . esc_html($row->timestamp) . '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
            echo '<p><input type="submit" name="delete_selected" class="button button-danger" value="Ausgewählte löschen"></p>';
            echo '</form>';

            // JavaScript um alle Checkboxen auswählen
            echo '<script>
                document.getElementById("select-all").addEventListener("click", function() {
                    var checkboxes = document.querySelectorAll("input[name=\'delete_ids[]\']");
                    for (var checkbox of checkboxes) {
                        checkbox.checked = this.checked;
                    }
                });
            </script>';
        } else {
            echo '<p>Keine Positionen gefunden.</p>';
        }

        echo '</div>';
    }

    public function render_map_shortcode() {
        ob_start();
        echo '<div id="reiseblog-map" style="height: 500px; width: 100%;"></div>';
        echo '<script src="' . plugin_dir_url(__FILE__) . '../assets/js/reiseblog-map.js"></script>';
        return ob_get_clean();
    }

    public function enqueue_leaflet_assets() {
        wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.9.3/dist/leaflet.css');
        wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.9.3/dist/leaflet.js', [], null, true);
    }
}
?>
