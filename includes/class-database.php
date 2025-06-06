<?php
// includes/class-database.php

if (!defined('ABSPATH')) {
    exit;
}

class Reiseblog_Database {
    
    public static function install() {
        global $wpdb;
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // Beispiel: Tabelle für Positionsdaten
        $table_name = $wpdb->prefix . 'reise_positions';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            latitude DECIMAL(10, 8) NOT NULL,
            longitude DECIMAL(11, 8) NOT NULL,
            timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        dbDelta($sql);

        // Später können wir hier weitere Tabellen für Fotos etc. erstellen
    }
}
?>
