<?php
// filepath: includes/admin-settings.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Yönetim menüsünü oluştur
add_action( 'admin_menu', 'ort_add_admin_menu' );

function ort_add_admin_menu() {
    add_menu_page(
        'Oxigen Rank Tracker Ayarları',
        'Rank Tracker',
        'manage_options',
        'oxigen-rank-tracker',
        'ort_admin_page_content',
        'dashicons-chart-line',
        60
    );
}

// Yönetim sayfası içeriği
function ort_admin_page_content() {
    ?>
    <div class="wrap">
        <h1>Oxigen Rank Tracker Ayarları</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'ort_settings_group' );
            do_settings_sections( 'oxigen-rank-tracker' );
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Ayarları kaydetme
add_action( 'admin_init', 'ort_register_settings' );

function ort_register_settings() {
    register_setting( 'ort_settings_group', 'ort_keywords' );
    register_setting( 'ort_settings_group', 'ort_website' );
    register_setting( 'ort_settings_group', 'ort_email' );
    register_setting( 'ort_settings_group', 'ort_interval' );

    add_settings_section(
        'ort_settings_section',
        'Genel Ayarlar',
        'ort_settings_section_callback',
        'oxigen-rank-tracker'
    );

    add_settings_field(
        'ort_keywords',
        'Anahtar Kelimeler (virgülle ayrılmış)',
        'ort_keywords_field_callback',
        'oxigen-rank-tracker',
        'ort_settings_section'
    );

    add_settings_field(
        'ort_website',
        'Web Sitesi',
        'ort_website_field_callback',
        'oxigen-rank-tracker',
        'ort_settings_section'
    );

    add_settings_field(
        'ort_email',
        'E-posta Adresi',
        'ort_email_field_callback',
        'oxigen-rank-tracker',
        'ort_settings_section'
    );

    add_settings_field(
        'ort_interval',
        'Kontrol Aralığı (dakika)',
        'ort_interval_field_callback',
        'oxigen-rank-tracker',
        'ort_settings_section'
    );
}

// Ayar bölümü açıklaması
function ort_settings_section_callback() {
    echo '<p>Eklenti ayarlarını buradan yapılandırabilirsiniz.</p>';
}

// Anahtar kelimeler alanı
function ort_keywords_field_callback() {
    $keywords = get_option( 'ort_keywords' );
    echo '<input type="text" name="ort_keywords" value="' . esc_attr( $keywords ) . '" size="80" />';
}

// Web sitesi alanı
function ort_website_field_callback() {
    $website = get_option( 'ort_website' );
    echo '<input type="text" name="ort_website" value="' . esc_attr( $website ) . '" size="50" />';
}

// E-posta alanı
function ort_email_field_callback() {
    $email = get_option( 'ort_email' );
    echo '<input type="email" name="ort_email" value="' . esc_attr( $email ) . '" size="50" />';
}

// Kontrol aralığı alanı
function ort_interval_field_callback() {
    $interval = get_option( 'ort_interval' );
    echo '<input type="number" name="ort_interval" value="' . esc_attr( $interval ) . '" />';
}