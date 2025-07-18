<?php
// filepath: includes/debug.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Writes a message to the debug log file.
 *
 * @param string|array|object $message The message to be written.
 */
function ort_debug_log( $message ) {
    // Ensure the logs directory exists
    $log_dir = plugin_dir_path( __FILE__ ) . 'logs/';
    if ( ! file_exists( $log_dir ) ) {
        wp_mkdir_p( $log_dir );
    }

    $log_file = $log_dir . 'debug.log';
    $timestamp = date( 'Y-m-d H:i:s' );
    
    // If the message is an array or object, print it in a readable format
    if ( is_array( $message ) || is_object( $message ) ) {
        $message = print_r( $message, true );
    }

    $log_message = $timestamp . ': ' . $message . "\n";

    // Use error_log for safer file writing
    error_log( $log_message, 3, $log_file );
}
