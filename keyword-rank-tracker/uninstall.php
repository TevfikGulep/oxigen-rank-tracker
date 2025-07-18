<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete options
delete_option('keyword_rank_tracker_options');

// Remove any custom database tables if created
global $wpdb;
$table_name = $wpdb->prefix . 'keyword_rank_tracker';
$wpdb->query("DROP TABLE IF EXISTS $table_name");

// Cleanup any other data related to the plugin
// Add additional cleanup code here if necessary
?>