<?php
// filepath: includes/rank-tracker-functions.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Belirli bir anahtar kelime için Google sıralamasını kontrol eder ve geçmişi kaydeder.
 *
 * @param string $keyword Aranacak anahtar kelime.
 * @param string $website Kontrol edilecek web sitesi.
 * @return int|false Sıralama numarası veya hata durumunda false.
 */
function ort_get_google_rank( $keyword, $website ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'ort_rank_history';
    $url = 'https://www.google.com/search?q=' . urlencode( $keyword );

    $response = wp_remote_get( $url );

    if ( is_wp_error( $response ) ) {
        ort_debug_log( 'Google arama isteği başarısız: ' . $response->get_error_message() );
        return false;
    }

    $body = wp_remote_retrieve_body( $response );

    if ( empty( $body ) ) {
        ort_debug_log( 'Google arama sonucu boş.' );
        return false;
    }

    // Basit bir sıralama bulma işlemi (daha gelişmiş bir yöntem gerekebilir)
    $rank = strpos( $body, $website );

    if ( $rank !== false ) {
        // Sıralama geçmişini kaydet
        $wpdb->insert(
            $table_name,
            array(
                'keyword' => $keyword,
                'website' => $website,
                'rank' => $rank,
                'timestamp' => current_time( 'mysql' ),
            ),
            array(
                '%s',
                '%s',
                '%d',
                '%s',
            )
        );

        return $rank; // Basit bir konum döndürüyoruz, daha doğru bir sıralama için geliştirilebilir.
    } else {
        return false;
    }
}

/**
 * Eklenti etkinleştirildiğinde çalışacak ve özel veritabanı tablosunu oluşturacak fonksiyon.
 */
function ort_create_rank_history_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'ort_rank_history';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        timestamp datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        keyword varchar(255) NOT NULL,
        website varchar(255) NOT NULL,
        rank int(11) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}