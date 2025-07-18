<?php
// filepath: includes/debug.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Writes a message to the debug log file.
 *
 * @param string $message Message to be written.
 */
function ort_debug_log( $message ) {
    $log_file = plugin_dir_path( __FILE__ ) . 'debug.log';
    $timestamp = date( 'Y-m-d H:i:s' );
    $log_message = $timestamp . ': ' . $message . "\n";

    error_log( $log_message, 3, $log_file );
}