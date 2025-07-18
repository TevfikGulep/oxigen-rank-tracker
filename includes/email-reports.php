<?php
// filepath: includes/email-reports.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Sıralama raporunu e-posta ile gönderir.
 */
function ort_send_email_report( $project_id ) {
    $keywords = ort_get_project_setting( $project_id, 'keywords' );
    $website = ort_get_project_setting( $project_id, 'website' );
    $email = ort_get_project_setting( $project_id, 'email' );

    if ( empty( $keywords ) || empty( $website ) || empty( $email ) ) {
        ort_debug_log( 'E-posta gönderme başarısız: Gerekli ayarlar eksik. Proje ID: ' . $project_id );
        return;
    }

    if ( ! is_array( $keywords ) ) {
        ort_debug_log( 'Anahtar kelimeler geçerli bir dizi değil. Proje ID: ' . $project_id );
        return;
    }

    $message = '<h1>Sıralama Raporu</h1><ul>';

    foreach ( $keywords as $keyword ) {
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
        ort_debug_log( 'E-posta gönderme başarısız. Proje ID: ' . $project_id );
    } else {
        ort_debug_log( 'E-posta başarıyla gönderildi: ' . $email . ' - Proje ID: ' . $project_id );
    }
}

// Zamanlanmış görev (cron job) ile e-posta gönderme
add_action( 'ort_send_scheduled_email', 'ort_send_email_report' );

function ort_schedule_email_report() {
    $projects = ort_get_projects();

    foreach ( $projects as $project_id => $project ) {
        // Mevcut zamanlanmış görevi temizle
        if ( wp_next_scheduled( 'ort_send_scheduled_email', array( $project_id ) ) ) {
            wp_clear_scheduled_hook( 'ort_send_scheduled_email', array( $project_id ) );
        }

        $interval = ort_get_project_setting( $project_id, 'interval', 'hourly' );

        // Yeni zamanlanmış görevi ayarla
        if ( ! wp_next_scheduled( 'ort_send_scheduled_email', array( $project_id ) ) ) {
            wp_schedule_event( time(), $interval, 'ort_send_scheduled_email', array( $project_id ) );
        }
    }
}
add_action( 'wp', 'ort_schedule_email_report' );