<?php
// filepath: includes/rank-tracker-functions.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Belirli bir anahtar kelime için Google sıralamasını kontrol eder.
 *
 * @param string $keyword Aranacak anahtar kelime.
 * @param string $website Kontrol edilecek web sitesi.
 * @return int|false Sıralama numarası veya hata durumunda false.
 */
function ort_get_google_rank( $keyword, $website ) {
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
        return $rank; // Basit bir konum döndürüyoruz, daha doğru bir sıralama için geliştirilebilir.
    } else {
        return false;
    }
}