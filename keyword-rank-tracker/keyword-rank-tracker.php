<?php
/**
 * Plugin Name: Keyword Rank Tracker
 * Description: A plugin to track keyword rankings daily.
 * Version: 1.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define constants
define( 'KRT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'KRT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Include necessary files
require_once KRT_PLUGIN_DIR . 'includes/class-keyword-rank-tracker.php';
require_once KRT_PLUGIN_DIR . 'includes/admin/class-keyword-rank-tracker-admin.php';
require_once KRT_PLUGIN_DIR . 'includes/public/class-keyword-rank-tracker-public.php';

// Initialize the plugin
function run_keyword_rank_tracker() {
    $plugin = new Keyword_Rank_Tracker();
    $plugin->run();
}
run_keyword_rank_tracker();
?>