<?php
// filepath: includes/admin-settings.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Admin menüsünü oluştur
add_action( 'admin_menu', 'ort_add_admin_menu' );
function ort_add_admin_menu() {
    add_menu_page(
        'Oxigen Rank Tracker',
        'Rank Tracker',
        'manage_options',
        'oxigen-rank-tracker',
        'ort_admin_page_content',
        'dashicons-chart-line',
        60
    );
}

// Admin sayfası içeriği
function ort_admin_page_content() {
    $projects = ort_get_projects();
    $current_project_id = ! empty( $_GET['project_id'] ) ? sanitize_text_field( $_GET['project_id'] ) : ( ! empty( $projects ) ? array_key_first( $projects ) : 0 );

    ?>
    <div class="wrap">
        <h1><span class="dashicons-before dashicons-chart-line"></span> Oxigen Rank Tracker</h1>

        <div style="display: flex; gap: 20px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 300px;">
                <h2>Projeler</h2>
                <?php if ( ! empty( $projects ) ) : ?>
                    <form method="get">
                        <input type="hidden" name="page" value="oxigen-rank-tracker">
                        <label for="project_id">Çalışma Projesini Seç:</label>
                        <select name="project_id" id="project_id" onchange="this.form.submit()">
                            <?php
                            foreach ( $projects as $id => $project ) {
                                echo '<option value="' . esc_attr( $id ) . '" ' . selected( $current_project_id, $id, false ) . '>' . esc_html( $project['name'] ) . '</option>';
                            }
                            ?>
                        </select>
                        <noscript><input type="submit" class="button" value="Seç"></noscript>
                    </form>
                <?php else : ?>
                    <p>Henüz proje oluşturulmadı. Lütfen yeni bir proje ekleyin.</p>
                <?php endif; ?>
                
                <hr>
                <?php ort_display_add_project_form(); ?>
            </div>

            <?php if ( $current_project_id && isset( $projects[$current_project_id] ) ) : ?>
                <div style="flex: 2; min-width: 400px;">
                    <h2>"<?php echo esc_html($projects[$current_project_id]['name']); ?>" Proje Ayarları</h2>
                    <form method="post" action="options.php">
                        <?php
                        settings_fields( 'ort_settings_group' );
                        ?>
                        <input type="hidden" name="ort_project_id" value="<?php echo esc_attr( $current_project_id ); ?>">
                        <?php
                        do_settings_sections( 'oxigen-rank-tracker' );
                        submit_button( 'Proje Ayarlarını Kaydet' );
                        ?>
                    </form>
                    
                    <hr>

                    <h2>Anahtar Kelime Ekle</h2>
                    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                        <input type="hidden" name="action" value="ort_add_keyword">
                        <input type="hidden" name="ort_project_id" value="<?php echo esc_attr( $current_project_id ); ?>">
                        <?php wp_nonce_field( 'ort_add_keyword_nonce' ); ?>
                        <label for="ort_new_keyword">Yeni Anahtar Kelime:</label>
                        <input type="text" id="ort_new_keyword" name="ort_new_keyword" required>
                        <?php submit_button( 'Kelime Ekle', 'secondary' ); ?>
                    </form>

                    <hr>

                    <h2>Mevcut Anahtar Kelimeler ve Sıralamalar</h2>
                    <?php ort_display_keyword_ranks( $current_project_id ); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

// Proje ekleme formu
function ort_display_add_project_form() {
    ?>
    <h3>Yeni Proje Ekle</h3>
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <input type="hidden" name="action" value="ort_add_project">
        <?php wp_nonce_field( 'ort_add_project_nonce' ); ?>
        <label for="ort_new_project_name">Proje Adı:</label>
        <input type="text" id="ort_new_project_name" name="ort_new_project_name" required>
        <?php submit_button( 'Yeni Proje Oluştur', 'primary' ); ?>
    </form>
    <?php
}

// Ayarları kaydet
add_action( 'admin_init', 'ort_register_settings' );
function ort_register_settings() {
    register_setting( 'ort_settings_group', 'ort_project_settings', 'ort_save_project_settings' );

    add_settings_section(
        'ort_settings_section',
        'Genel Ayarlar',
        function() { echo '<p>Projenin web sitesi, raporlama e-postası ve kontrol sıklığı ayarları.</p>'; },
        'oxigen-rank-tracker'
    );

    add_settings_field( 'ort_website', 'Website', 'ort_website_field_callback', 'oxigen-rank-tracker', 'ort_settings_section' );
    add_settings_field( 'ort_email', 'Rapor E-posta Adresi', 'ort_email_field_callback', 'oxigen-rank-tracker', 'ort_settings_section' );
    add_settings_field( 'ort_interval', 'Kontrol Sıklığı', 'ort_interval_field_callback', 'oxigen-rank-tracker', 'ort_settings_section' );
}

// Alanların Callback Fonksiyonları
function ort_website_field_callback() {
    $project_id = ! empty( $_GET['project_id'] ) ? sanitize_text_field( $_GET['project_id'] ) : array_key_first( ort_get_projects() );
    $website = ort_get_project_setting( $project_id, 'website' );
    echo '<input type="text" name="ort_project_settings[website]" value="' . esc_attr( $website ) . '" size="50" placeholder="https://ornek.com"/>';
}

function ort_email_field_callback() {
    $project_id = ! empty( $_GET['project_id'] ) ? sanitize_text_field( $_GET['project_id'] ) : array_key_first( ort_get_projects() );
    $email = ort_get_project_setting( $project_id, 'email' );
    echo '<input type="email" name="ort_project_settings[email]" value="' . esc_attr( $email ) . '" size="50" placeholder="eposta@ornek.com"/>';
}

function ort_interval_field_callback() {
    $project_id = ! empty( $_GET['project_id'] ) ? sanitize_text_field( $_GET['project_id'] ) : array_key_first( ort_get_projects() );
    $interval = ort_get_project_setting( $project_id, 'interval', 'daily' );
    $schedules = wp_get_schedules();
    echo '<select name="ort_project_settings[interval]">';
    foreach ( $schedules as $slug => $details ) {
        echo '<option value="' . esc_attr( $slug ) . '" ' . selected( $interval, $slug, false ) . '>' . esc_html( $details['display'] ) . '</option>';
    }
    echo '</select>';
}

// Yeni proje ekleme fonksiyonu
add_action( 'admin_post_ort_add_project', 'ort_add_project_handler' );
function ort_add_project_handler() {
    if ( ! isset( $_POST['ort_new_project_name'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'ort_add_project_nonce' ) ) {
        wp_die( 'Güvenlik hatası!' );
    }

    $new_project_name = sanitize_text_field( $_POST['ort_new_project_name'] );
    if ( ! empty( $new_project_name ) ) {
        $projects = ort_get_projects();
        $new_project_id = 'ort_' . uniqid();
        $projects[$new_project_id] = array( 'name' => $new_project_name, 'keywords' => array() );
        update_option( 'ort_projects', $projects );
        $redirect_url = admin_url( 'admin.php?page=oxigen-rank-tracker&project_id=' . $new_project_id );
    } else {
        $redirect_url = admin_url( 'admin.php?page=oxigen-rank-tracker' );
    }
    wp_redirect( $redirect_url );
    exit;
}

// Yeni anahtar kelime ekleme fonksiyonu
add_action( 'admin_post_ort_add_keyword', 'ort_add_keyword_handler' );
function ort_add_keyword_handler() {
    if ( ! isset( $_POST['ort_new_keyword'], $_POST['_wpnonce'], $_POST['ort_project_id'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'ort_add_keyword_nonce' ) ) {
        wp_die( 'Güvenlik hatası!' );
    }

    $project_id = sanitize_text_field( $_POST['ort_project_id'] );
    $new_keyword = sanitize_text_field( $_POST['ort_new_keyword'] );
    $projects = ort_get_projects();

    if ( ! empty( $new_keyword ) && isset( $projects[$project_id] ) ) {
        if ( ! isset( $projects[$project_id]['keywords'] ) ) {
            $projects[$project_id]['keywords'] = array();
        }
        $projects[$project_id]['keywords'][] = $new_keyword;
        update_option( 'ort_projects', $projects );
    }

    wp_redirect( admin_url( 'admin.php?page=oxigen-rank-tracker&project_id=' . $project_id ) );
    exit;
}

// Anahtar kelime silme fonksiyonu
add_action( 'admin_post_ort_delete_keyword', 'ort_delete_keyword_handler' );
function ort_delete_keyword_handler() {
    if ( ! isset( $_GET['project_id'], $_GET['keyword_index'], $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'ort_delete_keyword_nonce' ) ) {
        wp_die( 'Güvenlik hatası!' );
    }

    $project_id = sanitize_text_field( $_GET['project_id'] );
    $keyword_index = (int) $_GET['keyword_index'];
    $projects = ort_get_projects();

    if ( isset( $projects[$project_id]['keywords'][$keyword_index] ) ) {
        unset( $projects[$project_id]['keywords'][$keyword_index] );
        // Re-index the array
        $projects[$project_id]['keywords'] = array_values($projects[$project_id]['keywords']);
        update_option( 'ort_projects', $projects );
    }

    wp_redirect( admin_url( 'admin.php?page=oxigen-rank-tracker&project_id=' . $project_id ) );
    exit;
}
