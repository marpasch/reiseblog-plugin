<?php
// includes/class-exporter.php

if (!defined('ABSPATH')) {
    exit;
}

class Reiseblog_Exporter {

    public static function schedule_events() {
        if (!wp_next_scheduled('reiseblog_daily_export')) {
            wp_schedule_event(strtotime('tomorrow 00:05'), 'daily', 'reiseblog_daily_export');
        }
        add_action('reiseblog_daily_export', [self::class, 'run_daily_export']);
    }

    public static function run_daily_export() {
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        self::export_daily_route($yesterday);
    }

    public static function export_daily_route($date) {
        global $wpdb;

        $table = $wpdb->prefix . 'reise_positions';
        $results = $wpdb->get_results(
            $wpdb->prepare("SELECT latitude, longitude, timestamp FROM $table WHERE DATE(timestamp) = %s ORDER BY id ASC", $date),
            ARRAY_A
        );

        if (!$results) {
            return;
        }

        // GPX-Datei erzeugen
        $gpx  = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $gpx .= "<gpx version=\"1.1\" creator=\"Reiseblog Plugin\">\n<trk><name>$date</name><trkseg>\n";
        foreach ($results as $pos) {
            $gpx .= sprintf(
                "<trkpt lat=\"%s\" lon=\"%s\"><time>%s</time></trkpt>\n",
                esc_attr($pos['latitude']),
                esc_attr($pos['longitude']),
                esc_attr(date(DATE_ATOM, strtotime($pos['timestamp'])))
            );
        }
        $gpx .= "</trkseg></trk>\n</gpx>";

        $dir = REISEBLOG_PLUGIN_PATH . 'exports/';
        if (!file_exists($dir)) {
            wp_mkdir_p($dir);
        }
        file_put_contents($dir . "route-$date.gpx", $gpx);
    }
}
?>
