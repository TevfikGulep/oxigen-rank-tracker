<?php
// filepath: includes/debug.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Debug log dosyasına mesaj yazar.
 *
 * @param string $message Yazılacak mesaj.
 */
function ort_debug_log( $message ) {
    $log_file = plugin_dir_path( __FILE__ ) . 'debug.log';
    $timestamp = date( 'Y-m-d H:i:s' );
    $log_message = $timestamp . ': ' . $message . "\n";

    error_log( $log_message, 3, $log_file );
}