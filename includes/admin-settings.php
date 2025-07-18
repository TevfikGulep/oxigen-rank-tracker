<?php
// filepath: includes/admin-settings.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Create admin menu
add_action( 'admin_menu', 'ort_add_admin_menu' );

function ort_add_admin_menu() {
    add_menu_page(
        'Oxigen Rank Tracker Settings',
        'Rank Tracker',
        'manage_options',
        'oxigen-rank-tracker',
        'ort_admin_page_content',
        'dashicons-chart-line',
        60
    );
}

// Admin page content
function ort_admin_page_content() {
    // Project selection
    $project_id = isset( $_GET['project_id'] ) ? intval( $_GET['project_id'] ) : 0;
    $projects = ort_get_projects();

    if ( empty( $projects ) && $project_id == 0 ) {
        echo '<div class="wrap">';
        echo '<h1>Oxigen Rank Tracker Settings</h1>';
        echo '<p>No project has been created yet. Please create a project.</p>';
        ort_display_add_project_form();
        echo '</div>';
        return;
    }

    if ( $project_id == 0 && ! empty( $projects ) ) {
        $project_id = array_key_first( $projects );
    }

    ?>
    <div class="wrap">
        <h1>Oxigen Rank Tracker Settings</h1>

        <h2>Project Selection</h2>
        <form method="get">
            <input type="hidden" name="page" value="oxigen-rank-tracker">
            <label for="project_id">Select Project:</label>
            <select name="project_id" id="project_id" onchange="this.form.submit()">
                <?php
                foreach ( $projects as $id => $project ) {
                    echo '<option value="' . esc_attr( $id ) . '" ' . selected( $project_id, $id, false ) . '>' . esc_html( $project['name'] ) . '</option>';
                }
                ?>
            </select>
            <?php submit_button( 'Select', 'secondary', 'submit', false ); ?>
        </form>

        <?php ort_display_add_project_form(); ?>

        <h2>Website Settings</h2>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'ort_settings_group' );
            do_settings_sections( 'oxigen-rank-tracker' );
            ?>
            <input type="hidden" name="ort_project_id" value="<?php echo esc_attr( $project_id ); ?>">
            <?php
            submit_button( 'Save Website Settings', 'primary' );
            ?>
        </form>

        <h2>Add Keyword</h2>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <input type="hidden" name="action" value="ort_add_keyword">
            <?php wp_nonce_field( 'ort_add_keyword_nonce' ); ?>
            <label for="ort_new_keyword">New Keyword:</label>
            <input type="text" id="ort_new_keyword" name="ort_new_keyword" required>
            <input type="hidden" name="ort_project_id" value="<?php echo esc_attr( $project_id ); ?>">
            <?php submit_button( 'Add Keyword', 'secondary' ); ?>
        </form>

        <h2>Existing Keywords and Rankings</h2>
        <?php ort_display_keyword_ranks( $project_id ); ?>
    </div>
    <?php
}

// Project add form
function ort_display_add_project_form() {
    ?>
    <h2>Add New Project</h2>
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <input type="hidden" name="action" value="ort_add_project">
        <?php wp_nonce_field( 'ort_add_project_nonce' ); ?>
        <label for="ort_new_project_name">Project Name:</label>
        <input type="text" id="ort_new_project_name" name="ort_new_project_name" required>
        <?php submit_button( 'Add Project', 'secondary' ); ?>
    </form>
    <?php
}

// Register settings
add_action( 'admin_init', 'ort_register_settings' );

function ort_register_settings() {
    register_setting( 'ort_settings_group', 'ort_website' );
    register_setting( 'ort_settings_group', 'ort_email' );
    register_setting( 'ort_settings_group', 'ort_interval' );

    add_settings_section(
        'ort_settings_section',
        'General Settings',
        'ort_settings_section_callback',
        'oxigen-rank-tracker'
    );

    add_settings_field(
        'ort_website',
        'Website',
        'ort_website_field_callback',
        'oxigen-rank-tracker',
        'ort_settings_section'
    );

    add_settings_field(
        'ort_email',
        'Email Address',
        'ort_email_field_callback',
        'oxigen-rank-tracker',
        'ort_settings_section'
    );

    add_settings_field(
        'ort_interval',
        'Check Interval',
        'ort_interval_field_callback',
        'oxigen-rank-tracker',
        'ort_settings_section'
    );
}

// Settings section description
function ort_settings_section_callback() {
    echo '<p>Configure website and email settings here.</p>';
}

// Website field
function ort_website_field_callback() {
    $project_id = isset( $_GET['project_id'] ) ? intval( $_GET['project_id'] ) : 0;
    $website = ort_get_project_setting( $project_id, 'website' );
    echo '<input type="text" name="ort_website" value="' . esc_attr( $website ) . '" size="50" />';
}

// Email field
function ort_email_field_callback() {
    $project_id = isset( $_GET['project_id'] ) ? intval( $_GET['project_id'] ) : 0;
    $email = ort_get_project_setting( $project_id, 'email' );
    echo '<input type="email" name="ort_email" value="' . esc_attr( $email ) . '" size="50" />';
}

// Check interval field
function ort_interval_field_callback() {
    $project_id = isset( $_GET['project_id'] ) ? intval( $_GET['project_id'] ) : 0;
    $interval = ort_get_project_setting( $project_id, 'interval', 'hourly' );
    ?>
    <select name="ort_interval">
        <option value="hourly" <?php selected( $interval, 'hourly' ); ?>>Hourly</option>
        <option value="daily" <?php selected( $interval, 'daily' ); ?>>Daily</option>
        <option value="weekly" <?php selected( $interval, 'weekly' ); ?>>Weekly</option>
        <option value="monthly" <?php selected( $interval, 'monthly' ); ?>>Monthly</option>
    </select>
    <?php
}

// Add new project function
add_action( 'admin_post_ort_add_project', 'ort_add_project' );

function ort_add_project() {
    // Security check
    if ( ! isset( $_POST['ort_new_project_name'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'ort_add_project_nonce' ) ) {
        wp_die( 'Security error!' );
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
        ort_debug_log( 'New project ID: ' . $new_project_id );

        $projects[$new_project_id] = array(
            'name' => $new_project_name,
        );

        // Debug: Output the projects array before saving
        ort_debug_log( 'Projects array: ' . print_r( $projects, true ) );

        update_option( 'ort_projects', $projects );

        ort_debug_log( 'New project added: ' . $new_project_name );
    }

    // Redirect to admin page
    $redirect_url = admin_url( 'admin.php?page=oxigen-rank-tracker' );
    ort_debug_log( 'Redirect URL: ' . $redirect_url );
    wp_redirect( $redirect_url );
    exit;
}

// Add new keyword function
add_action( 'admin_post_ort_add_keyword', 'ort_add_keyword' );

function ort_add_keyword() {
    // Security check
    if ( ! isset( $_POST['ort_new_keyword'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'ort_add_keyword_nonce' ) ) {
        wp_die( 'Security error!' );
    }

    $new_keyword = sanitize_text_field( $_POST['ort_new_keyword'] );
    $project_id = sanitize_text_field( $_POST['ort_project_id'