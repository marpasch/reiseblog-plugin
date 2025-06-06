<?php
/**
 * Plugin Name: Reiseblog Plugin
 * Plugin URI: https://reiseblog.marpas.de
 * Description: Ein modulares Plugin zur Dokumentation einer Norwegenreise mit Standort- und Bilderverwaltung.
 * Version: 0.1.0
 * Author: Dein Name
 * Author URI: https://reiseblog.marpas.de
 * License: GPL2
 * Text Domain: reiseblog
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin paths
define('REISEBLOG_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('REISEBLOG_PLUGIN_URL', plugin_dir_url(__FILE__));

// Autoload all modules
function reiseblog_autoload_modules() {
    $modules = [
        'includes/class-owntracks.php',
        'includes/class-gallery.php',
        'includes/class-map.php',
        'includes/class-calendar.php',
        'includes/class-database.php',
	'includes/class-gallery-map.php',
	'includes/class-route-gallery-map.php',
    ];

    foreach ($modules as $module) {
        $file = REISEBLOG_PLUGIN_PATH . $module;
        if (file_exists($file)) {
            require_once $file;
        }
    }
}
add_action('plugins_loaded', 'reiseblog_autoload_modules');

// Init loaded modules
function reiseblog_init_modules() {
    if (class_exists('Reiseblog_OwnTracks')) {
        new Reiseblog_OwnTracks();
    }
    if (class_exists('Reiseblog_Gallery')) {
        new Reiseblog_Gallery();
    }
    if (class_exists('Reiseblog_Map')) {
        new Reiseblog_Map();
    }
    if (class_exists('Reiseblog_Calendar')) {
        new Reiseblog_Calendar();
    }
    if (class_exists('Reiseblog_Gallery_Map')) {
    new Reiseblog_Gallery_Map();
    }
    if (class_exists('Reiseblog_Route_Gallery_Map')) {
    new Reiseblog_Route_Gallery_Map();
    }

}
add_action('plugins_loaded', 'reiseblog_init_modules', 20);

// Activation and Deactivation Hooks
function reiseblog_activate() {
    // Initial database setup can go here
    if (file_exists(REISEBLOG_PLUGIN_PATH . 'includes/class-database.php')) {
        require_once REISEBLOG_PLUGIN_PATH . 'includes/class-database.php';
        Reiseblog_Database::install();
    }

    // Gallery Table Setup
    if (file_exists(REISEBLOG_PLUGIN_PATH . 'includes/class-gallery.php')) {
        require_once REISEBLOG_PLUGIN_PATH . 'includes/class-gallery.php';
        $gallery = new Reiseblog_Gallery();
        $gallery->create_gallery_table();
    }
}
register_activation_hook(__FILE__, 'reiseblog_activate');

function reiseblog_deactivate() {
    // Optional: Cleanup tasks
}
register_deactivation_hook(__FILE__, 'reiseblog_deactivate');

?>
