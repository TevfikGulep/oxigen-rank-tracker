<?php
/**
 * Plugin Name: Oxigen Rank Tracker
 * Description: Belirli sitelerin Google sıralamalarını izler ve raporlar.
 * Version: 1.0.0
 * Author: Tevfik Gülep
 */

// Güvenlik için doğrudan erişimi engelle
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Eklenti dosyalarını yükle
require_once plugin_dir_path( __FILE__ ) . 'includes/rank-tracker-functions.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin-settings.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/email-reports.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/debug.php';

// Eklenti etkinleştirildiğinde yapılacak işlemler
register_activation_hook( __FILE__, 'ort_activate_plugin' );
function ort_activate_plugin() {
    // Gerekli tabloları oluştur veya ayarları yap
    ort_debug_log( 'Eklenti etkinleştirildi.' );
}

// Eklenti devre dışı bırakıldığında yapılacak işlemler
register_deactivation_hook( __FILE__, 'ort_deactivate_plugin' );
function ort_deactivate_plugin() {
    ort_debug_log( 'Eklenti devre dışı bırakıldı.' );
}