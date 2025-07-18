<?php
// filepath: includes/project-functions.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Tüm projeleri veritabanından getirir.
 *
 * @return array Proje listesi.
 */
function ort_get_projects() {
    return get_option( 'ort_projects', array() );
}

/**
 * Belirli bir projenin ayarını getirir.
 *
 * @param string $project_id Proje ID'si.
 * @param string $key Ayar anahtarı.
 * @param mixed $default Varsayılan değer.
 * @return mixed Ayar değeri.
 */
function ort_get_project_setting( $project_id, $key, $default = '' ) {
    $projects = ort_get_projects();
    if ( isset( $projects[$project_id] ) && isset( $projects[$project_id][$key] ) ) {
        return $projects[$project_id][$key];
    }
    return $default;
}

/**
 * Ayarlar sayfasından gelen verileri proje bazlı olarak kaydeder.
 * register_setting için bir callback fonksiyonudur.
 *
 * @param array $input Gelen ayarlar.
 * @return array Temizlenmiş ayarlar.
 */
function ort_save_project_settings( $input ) {
    if ( ! isset( $_POST['ort_project_id'] ) ) {
        // Proje ID'si yoksa işlem yapma
        return $input;
    }

    $project_id = sanitize_text_field( $_POST['ort_project_id'] );
    $projects = ort_get_projects();

    if ( isset( $projects[$project_id] ) ) {
        $projects[$project_id]['website'] = sanitize_text_field( $input['website'] ?? '' );
        $projects[$project_id]['email'] = sanitize_email( $input['email'] ?? '' );
        $projects[$project_id]['interval'] = sanitize_text_field( $input['interval'] ?? 'hourly' );
        update_option( 'ort_projects', $projects );
    }
    
    // WordPress'e ayarların boş olduğunu döndürerek kendi kaydetme mekanizmasını atlıyoruz.
    // Çünkü biz veriyi kendi 'ort_projects' option'ımıza kaydettik.
    return array();
}


/**
 * Anahtar kelimeleri ve sıralamalarını gösterir.
 *
 * @param string $project_id Proje ID'si.
 */
function ort_display_keyword_ranks( $project_id ) {
    $keywords = ort_get_project_setting( $project_id, 'keywords', array() );
    $website = ort_get_project_setting( $project_id, 'website' );

    if ( empty( $keywords ) ) {
        echo '<p>Henüz anahtar kelime eklenmemiş.</p>';
        return;
    }

    echo '<table class="widefat fixed" cellspacing="0">';
    echo '<thead><tr><th>Anahtar Kelime</th><th>Sıralama</th><th>İşlem</th></tr></thead>';
    echo '<tbody>';

    foreach ( $keywords as $index => $keyword ) {
        // Not: Gerçek zamanlı sıralama kontrolü burada sunucuya yük bindirebilir.
        // Sıralamalar bir cron job ile arka planda alınıp veritabanına yazılmalıdır.
        // Bu örnekte basitçe gösterim amaçlıdır.
        $rank = 'Henüz kontrol edilmedi';

        echo '<tr>';
        echo '<td>' . esc_html( $keyword ) . '</td>';
        echo '<td>' . esc_html( $rank ) . '</td>';
        echo '<td><a href="' . esc_url( admin_url('admin-post.php?action=ort_delete_keyword&project_id=' . $project_id . '&keyword_index=' . $index . '&_wpnonce=' . wp_create_nonce('ort_delete_keyword_nonce')) ) . '" class="button button-danger">Sil</a></td>';
        echo '</tr>';
    }

    echo '</tbody></table>';
}
