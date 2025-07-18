<?php
/**
 * Plugin Name: Oxigen Rank Tracker
 * Description: Tracks and reports Google rankings for specific sites.
 * Version: 1.1.0
 * Author: Tevfik Gülep
 */

// Prevent direct access for security
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Plugin activation hook
register_activation_hook( __FILE__, 'ort_activate_plugin' );

// Plugin deactivation hook
register_deactivation_hook( __FILE__, 'ort_deactivate_plugin' );


/**
 * Actions to be performed when the plugin is activated.
 */
function ort_activate_plugin() {
    // Ensure dependencies are loaded before using them
    if (!function_exists('ort_create_rank_history_table')) {
        require_once plugin_dir_path( __FILE__ ) . 'includes/rank-tracker-functions.php';
    }
    ort_create_rank_history_table(); // Create database table
    ort_debug_log( 'Plugin activated.' );
}

/**
 * Actions to be performed when the plugin is deactivated.
 */
function ort_deactivate_plugin() {
    // Clear scheduled cron jobs to prevent errors
    $projects = get_option('ort_projects', array());
    foreach (array_keys($projects) as $project_id) {
        if (wp_next_scheduled('ort_send_scheduled_email', array($project_id))) {
            wp_clear_scheduled_hook('ort_send_scheduled_email', array($project_id));
        }
    }
    ort_debug_log( 'Plugin deactivated.' );
}


/**
 * Initializes the plugin by loading all necessary files.
 */
function ort_plugin_init() {
    // Load plugin files
    require_once plugin_dir_path( __FILE__ ) . 'includes/debug.php';
    require_once plugin_dir_path( __FILE__ ) . 'includes/rank-tracker-functions.php';
    require_once plugin_dir_path( __FILE__ ) . 'includes/project-functions.php'; // <-- YENİ DOSYAYI EKLE
    require_once plugin_dir_path( __FILE__ ) . 'includes/admin-settings.php';
    require_once plugin_dir_path( __FILE__ ) . 'includes/email-reports.php';
}
add_action( 'plugins_loaded', 'ort_plugin_init' );
