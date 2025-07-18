<?php
// filepath: includes/email-reports.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Sıralama raporunu e-posta ile gönderir.
 */
function ort_send_email_report() {
    $keywords = get_option( 'ort_keywords' );
    $website = get_option( 'ort_website' );
    $email = get_option( 'ort_email' );

    if ( empty( $keywords ) || empty( $website ) || empty( $email ) ) {
        ort_debug_log( 'E-posta gönderme başarısız: Gerekli ayarlar eksik.' );
        return;
    }

    $keyword_list = explode( ',', $keywords );
    $message = '<h1>Sıralama Raporu</h1><ul>';

    foreach ( $keyword_list as $keyword ) {
        $keyword = trim( $keyword );
        $rank = ort_get_google_rank( $keyword, $website );

        if ( $rank !== false ) {
            $message .= '<li>' . $keyword . ': ' . $rank . '</li>';
        } else {
            $message .= '<li>' . $keyword . ': Sıralama bulunamadı.</li>';
        }
    }

    $message .= '</ul>';

    $subject = 'Oxigen Rank Tracker - Sıralama Raporu';
    $headers = array( 'Content-Type: text/html; charset=UTF-8' );

    $result = wp_mail( $email, $subject, $message, $headers );

    if ( ! $result ) {
        ort_debug_log( 'E-posta gönderme başarısız.' );
    } else {
        ort_debug_log( 'E-posta başarıyla gönderildi: ' . $email );
    }
}

// Zamanlanmış görev (cron job) ile e-posta gönderme
add_action( 'ort_send_scheduled_email', 'ort_send_email_report' );

function ort_schedule_email_report() {
    $interval = get_option( 'ort_interval', 60 ); // Varsayılan olarak 60 dakika
    if ( ! wp_next_scheduled( 'ort_send_scheduled_email' ) ) {
        wp_schedule_event( time(), 'ort_every_' . $interval . '_minutes', 'ort_send_scheduled_email' );
    }
}
add_action( 'wp', 'ort_schedule_email_report' );

// Özel cron aralıkları tanımlama
add_filter( 'cron_schedules', 'ort_add_custom_cron_intervals' );

function ort_add_custom_cron_intervals( $schedules ) {
    $interval = get_option( 'ort_interval', 60 );
    $schedules['ort_every_' . $interval . '_minutes'] = array(
        'interval' => $interval * 60,
        'display'  => sprintf( __( 'Her %d Dakikada' ), $interval ),
    );
    return $schedules;
}