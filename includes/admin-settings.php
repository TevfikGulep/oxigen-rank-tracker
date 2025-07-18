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
    // Proje seçimi
    $project_id = isset( $_GET['project_id'] ) ? intval( $_GET['project_id'] ) : 0;
    $projects = ort_get_projects();

    if ( empty( $projects ) && $project_id == 0 ) {
        echo '<div class="wrap">';
        echo '<h1>Oxigen Rank Tracker Ayarları</h1>';
        echo '<p>Henüz bir proje oluşturulmadı. Lütfen bir proje oluşturun.</p>';
        ort_display_add_project_form();
        echo '</div>';
        return;
    }

    if ( $project_id == 0 && ! empty( $projects ) ) {
        $project_id = array_key_first( $projects );
    }

    ?>
    <div class="wrap">
        <h1>Oxigen Rank Tracker Ayarları</h1>

        <h2>Proje Seçimi</h2>
        <form method="get">
            <input type="hidden" name="page" value="oxigen-rank-tracker">
            <label for="project_id">Proje Seç:</label>
            <select name="project_id" id="project_id" onchange="this.form.submit()">
                <?php
                foreach ( $projects as $id => $project ) {
                    echo '<option value="' . esc_attr( $id ) . '" ' . selected( $project_id, $id, false ) . '>' . esc_html( $project['name'] ) . '</option>';
                }
                ?>
            </select>
            <?php submit_button( 'Seç', 'secondary', 'submit', false ); ?>
        </form>

        <?php ort_display_add_project_form(); ?>

        <h2>Web Sitesi Ayarları</h2>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'ort_settings_group' );
            do_settings_sections( 'oxigen-rank-tracker' );
            ?>
            <input type="hidden" name="ort_project_id" value="<?php echo esc_attr( $project_id ); ?>">
            <?php
            submit_button( 'Web Sitesi Ayarlarını Kaydet', 'primary' );
            ?>
        </form>

        <h2>Anahtar Kelime Ekle</h2>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <input type="hidden" name="action" value="ort_add_keyword">
            <?php wp_nonce_field( 'ort_add_keyword_nonce' ); ?>
            <label for="ort_new_keyword">Yeni Anahtar Kelime:</label>
            <input type="text" id="ort_new_keyword" name="ort_new_keyword" required>
            <input type="hidden" name="ort_project_id" value="<?php echo esc_attr( $project_id ); ?>">
            <?php submit_button( 'Anahtar Kelime Ekle', 'secondary' ); ?>
        </form>

        <h2>Mevcut Anahtar Kelimeler ve Sıralamalar</h2>
        <?php ort_display_keyword_ranks( $project_id ); ?>
    </div>
    <?php
}

// Proje ekleme formu
function ort_display_add_project_form() {
    ?>
    <h2>Yeni Proje Ekle</h2>
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <input type="hidden" name="action" value="ort_add_project">
        <?php wp_nonce_field( 'ort_add_project_nonce' ); ?>
        <label for="ort_new_project_name">Proje Adı:</label>
        <input type="text" id="ort_new_project_name" name="ort_new_project_name" required>
        <?php submit_button( 'Proje Ekle', 'secondary' ); ?>
    </form>
    <?php
}

// Ayarları kaydetme
add_action( 'admin_init', 'ort_register_settings' );

function ort_register_settings() {
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
        'Kontrol Aralığı',
        'ort_interval_field_callback',
        'oxigen-rank-tracker',
        'ort_settings_section'
    );
}

// Ayar bölümü açıklaması
function ort_settings_section_callback() {
    echo '<p>Web sitesi ve e-posta ayarlarını buradan yapılandırabilirsiniz.</p>';
}

// Web sitesi alanı
function ort_website_field_callback() {
    $project_id = isset( $_GET['project_id'] ) ? intval( $_GET['project_id'] ) : 0;
    $website = ort_get_project_setting( $project_id, 'website' );
    echo '<input type="text" name="ort_website" value="' . esc_attr( $website ) . '" size="50" />';
}

// E-posta alanı
function ort_email_field_callback() {
    $project_id = isset( $_GET['project_id'] ) ? intval( $_GET['project_id'] ) : 0;
    $email = ort_get_project_setting( $project_id, 'email' );
    echo '<input type="email" name="ort_email" value="' . esc_attr( $email ) . '" size="50" />';
}

// Kontrol aralığı alanı
function ort_interval_field_callback() {
    $project_id = isset( $_GET['project_id'] ) ? intval( $_GET['project_id'] ) : 0;
    $interval = ort_get_project_setting( $project_id, 'interval', 'hourly' );
    ?>
    <select name="ort_interval">
        <option value="hourly" <?php selected( $interval, 'hourly' ); ?>>Saatlik</option>
        <option value="daily" <?php selected( $interval, 'daily' ); ?>>Günlük</option>
        <option value="weekly" <?php selected( $interval, 'weekly' ); ?>>Haftalık</option>
        <option value="monthly" <?php selected( $interval, 'monthly' ); ?>>Aylık</option>
    </select>
    <?php
}

// Yeni proje ekleme işlevi
add_action( 'admin_post_ort_add_project', 'ort_add_project' );

function ort_add_project() {
    // Güvenlik kontrolü
    if ( ! isset( $_POST['ort_new_project_name'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'ort_add_project_nonce' ) ) {
        wp_die( 'Güvenlik hatası!' );
    }

    $new_project_name = sanitize_text_field( $_POST['ort_new_project_name'] );

    if ( ! empty( $new_project_name ) ) {
        $projects = ort_get_projects();
        // Use wp_generate_uuid() if available, otherwise generate a random string
        if ( function_exists( 'wp_generate_uuid' ) ) {
            $new_project_id = wp_generate_uuid();
        } else {
            $new_project_id = uniqid( 'ort_' ); // Fallback for older WP versions
        }

        // Debug: Output the generated project ID
        ort_debug_log( 'Yeni proje ID: ' . $new_project_id );

        $projects[$new_project_id] = array(
            'name' => $new_project_name,
        );

        // Debug: Output the projects array before saving
        ort_debug_log( 'Projeler dizisi: ' . print_r( $projects, true ) );

        update_option( 'ort_projects', $projects );

        ort_debug_log( 'Yeni proje eklendi: ' . $new_project_name );
    }

    // Yönetim sayfasına geri yönlendir
    $redirect_url = admin_url( 'admin.php?page=oxigen-rank-tracker' );
    ort_debug_log( 'Yönlendirme URL\'si: ' . $redirect_url );
    wp_redirect( $redirect_url );
    exit;
}

// Yeni anahtar kelime ekleme işlevi
add_action( 'admin_post_ort_add_keyword', 'ort_add_keyword' );

function ort_add_keyword() {
    // Güvenlik kontrolü
    if ( ! isset( $_POST['ort_new_keyword'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'ort_add_keyword_nonce' ) ) {
        wp_die( 'Güvenlik hatası!' );
    }

    $new_keyword = sanitize_text_field( $_POST['ort_new_keyword'] );
    $project_id = sanitize_text_field( $_POST['ort_project_id'] );

    if ( ! empty( $new_keyword ) ) {
        $keywords = ort_get_project_setting( $project_id, 'keywords', array() );
        if ( ! is_array( $keywords ) ) {
            $keywords = array();
        }
        $keywords[] = $new_keyword;
        ort_update_project_setting( $project_id, 'keywords', $keywords );

        ort_debug_log( 'Yeni anahtar kelime eklendi: ' . $new_keyword . ' - Proje: ' . $project_id );
    }

    // Yönetim sayfasına geri yönlendir
    wp_redirect( admin_url( 'admin.php?page=oxigen-rank-tracker&project_id=' . $project_id ) );
    exit;
}

// Anahtar kelime sıralamalarını görüntüleme işlevi
function ort_display_keyword_ranks( $project_id ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'ort_rank_history';
    $keywords = ort_get_project_setting( $project_id, 'keywords', array() );
    $website = ort_get_project_setting( $project_id, 'website' );

    if ( ! is_array( $keywords ) || empty( $keywords ) ) {
        echo '<p>Henüz anahtar kelime eklenmedi.</p>';
        return;
    }

    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>Anahtar Kelime</th><th>İlk Sıralama</th><th>Son Sıralama</th><th>Sıralama Geçmişi</th></tr></thead>';
    echo '<tbody>';

    foreach ( $keywords as $keyword ) {
        $keyword = trim( $keyword );

        // İlk sıralamayı al
        $first_rank_query = $wpdb->prepare(
            "SELECT rank, timestamp FROM $table_name WHERE keyword = %s AND website = %s ORDER BY timestamp ASC LIMIT 1",
            $keyword,
            $website
        );
        $first_rank_data = $wpdb->get_row( $first_rank_query );

        // Son sıralamayı al
        $last_rank_query = $wpdb->prepare(
            "SELECT rank, timestamp FROM $table_name WHERE keyword = %s AND website = %s ORDER BY timestamp DESC LIMIT 1",
            $keyword,
            $website
        );
        $last_rank_data = $wpdb->get_row( $last_rank_query );

        // Tüm sıralama geçmişini al
        $all_ranks_query = $wpdb->prepare(
            "SELECT rank, timestamp FROM $table_name WHERE keyword = %s AND website = %s ORDER BY timestamp ASC",
            $keyword,
            $website
        );
        $all_ranks = $wpdb->get_results( $all_ranks_query );

        echo '<tr>';
        echo '<td>' . esc_html( $keyword ) . '</td>';

        // İlk sıralama verisi
        if ( $first_rank_data ) {
            echo '<td>' . esc_html( $first_rank_data->rank ) . ' (' . date( 'Y-m-d H:i:s', strtotime( $first_rank_data->timestamp ) ) . ')</td>';
        } else {
            echo '<td>Sıralama bulunamadı</td>';
        }

        // Son sıralama verisi
        if ( $last_rank_data ) {
            echo '<td>' . esc_html( $last_rank_data->rank ) . ' (' . date( 'Y-m-d H:i:s', strtotime( $last_rank_data->timestamp ) ) . ')</td>';
        } else {
            echo '<td>Sıralama bulunamadı</td>';
        }

        // Sıralama geçmişi
        echo '<td>';
        if ( ! empty( $all_ranks ) ) {
            echo '<ul>';
            foreach ( $all_ranks as $rank_data ) {
                echo '<li>' . esc_html( $rank_data->rank ) . ' (' . date( 'Y-m-d H:i:s', strtotime( $rank_data->timestamp ) ) . ')</li>';
            }
            echo '</ul>';
        } else {
            echo 'Sıralama geçmişi bulunamadı';
        }
        echo '</td>';

        echo '</tr>';
    }

    echo '</tbody></table>';
}

// Projeleri getirme işlevi
function ort_get_projects() {
    return get_option( 'ort_projects', array() );
}

// Proje ayarını getirme işlevi
function ort_get_project_setting( $project_id, $setting_name, $default = '' ) {
    $projects = ort_get_projects();
    if ( isset( $projects[$project_id] ) && isset( $projects[$project_id][$setting_name] ) ) {
        return $projects[$project_id][$setting_name];
    }
    return $default;
}

// Proje ayarını güncelleme işlevi
function ort_update_project_setting( $project_id, $setting_name, $setting_value ) {
    $projects = ort_get_projects();
    if ( ! isset( $projects[$project_id] ) ) {
        $projects[$project_id] = array();
    }
    $projects[$project_id][$setting_name] = $setting_value;
    update_option( 'ort_projects', $projects );
}