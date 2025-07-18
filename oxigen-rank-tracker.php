<?php
/**
 * Plugin Name: Oxigen Rank Tracker
 * Description: Tracks and reports Google rankings for specific sites.
 * Version: 1.0.0
 * Author: Tevfik Gülep
 */

// Prevent direct access for security
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function ort_plugin_init() {
    // Load plugin files
    require_once plugin_dir_path( __FILE__ ) . 'includes/rank-tracker-functions.php';
    require_once plugin_dir_path( __FILE__ ) . 'includes/admin-settings.php';
    require_once plugin_dir_path( __FILE__ ) . 'includes/email-reports.php';
    require_once plugin_dir_path( __FILE__ ) . 'includes/debug.php';
}
add_action( 'init', 'ort_plugin_init' );

// Actions to be performed when the plugin is activated
register_activation_hook( __FILE__, 'ort_activate_plugin' );
function ort_activate_plugin() {
    // Create necessary tables or set settings
    ort_debug_log( 'Plugin activated.' );
    ort_create_rank_history_table(); // Create database table
}

// Actions to be performed when the plugin is deactivated
register_deactivation_hook( __FILE__, 'ort_deactivate_plugin' );
function ort_deactivate_plugin() {
    ort_debug_log( 'Plugin deactivated.' );
}