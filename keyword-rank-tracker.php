<?php
/**
 * Plugin Name: Keyword Rank Tracker
 * Description: A plugin to track keyword rankings daily.
 * Version: 1.0
 * Author: Tevfik Gülep
 * Author URI: https://oxigen.team
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define constants
define( 'KRT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'KRT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Debug log fonksiyonu
function krt_debug_log($message) {
    $file = KRT_PLUGIN_DIR . 'krt-debug.log';
    $date = date('Y-m-d H:i:s');
    $log = "[$date] $message\n";
    file_put_contents($file, $log, FILE_APPEND);
}

// Include necessary files
require_once KRT_PLUGIN_DIR . 'includes/class-keyword-rank-tracker.php';
require_once KRT_PLUGIN_DIR . 'includes/admin/class-keyword-rank-tracker-admin.php';
require_once KRT_PLUGIN_DIR . 'includes/public/class-keyword-rank-tracker-public.php';

// Initialize the plugin
function run_keyword_rank_tracker() {
    try {
        $plugin = new Keyword_Rank_Tracker();
        if (method_exists($plugin, 'run')) {
            $plugin->run();
        }
    } catch (Throwable $e) {
        krt_debug_log('Plugin error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
        // İsteğe bağlı: Ekrana da hata mesajı göstermek için aşağıdaki satırı ekleyebilirsiniz
        // echo '<pre>' . $e->getMessage() . '</pre>';
    }
}
run_keyword_rank_tracker();