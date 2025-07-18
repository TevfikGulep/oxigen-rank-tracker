<?php
/**
 * Plugin Name: Oxigen Rank Tracker
 * Description: Tracks and reports Google rankings for specific sites.
 * Version: 1.2.0
 * Author: Tevfik Gülep
 */

// Prevent direct access for security
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Plugin activation and deactivation hooks
register_activation_hook( __FILE__, 'ort_activate_plugin' );
register_deactivation_hook( __FILE__, 'ort_deactivate_plugin' );

/**
 * Actions to be performed when the plugin is activated.
 * Loads its own dependencies to prevent fatal errors.
 */
function ort_activate_plugin() {
    // Load necessary files for activation
    require_once plugin_dir_path( __FILE__ ) . 'includes/debug.php';
    require_once plugin_dir_path( __FILE__ ) . 'includes/rank-tracker-functions.php';

    // Create the database table for rank history
    ort_create_rank_history_table();
    
    // Log the activation event
    ort_debug_log( 'Plugin activated.' );
}

/**
 * Actions to be performed when the plugin is deactivated.
 * Loads its own dependencies and cleans up scheduled tasks.
 */
function ort_deactivate_plugin() {
    // Load necessary file for deactivation log
    require_once plugin_dir_path( __FILE__ ) . 'includes/debug.php';

    // Clear scheduled cron jobs to prevent errors
    $projects = get_option('ort_projects', array());
    if ( ! empty( $projects ) && is_array( $projects ) ) {
        foreach ( array_keys( $projects ) as $project_id ) {
            // Ensure the hook exists before trying to clear it
            if ( wp_next_scheduled( 'ort_send_scheduled_email', array( $project_id ) ) ) {
                wp_clear_scheduled_hook( 'ort_send_scheduled_email', array( $project_id ) );
            }
        }
    }
    
    // Log the deactivation event
    ort_debug_log( 'Plugin deactivated.' );
}

/**
 * Initializes the plugin by loading all necessary files.
 * This runs after the plugin has been activated and on every page load.
 */
function ort_plugin_init() {
    // Load all plugin files
    require_once plugin_dir_path( __FILE__ ) . 'includes/debug.php';
    require_once plugin_dir_path( __FILE__ ) . 'includes/rank-tracker-functions.php';
    require_once plugin_dir_path( __FILE__ ) . 'includes/project-functions.php';
    require_once plugin_dir_path( __FILE__ ) . 'includes/admin-settings.php';
    require_once plugin_dir_path( __FILE__ ) . 'includes/email-reports.php';
}
add_action( 'plugins_loaded', 'ort_plugin_init' );
