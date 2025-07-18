<?php
// filepath: includes/admin-settings.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Create the admin menu
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

// Admin page content
function ort_admin_page_content() {
    $projects = ort_get_projects();
    $current_project_id = ! empty( $_GET['project_id'] ) ? sanitize_text_field( $_GET['project_id'] ) : ( ! empty( $projects ) ? array_key_first( $projects ) : 0 );

    ?>
    <div class="wrap">
        <h1><span class="dashicons-before dashicons-chart-line"></span> Oxigen Rank Tracker</h1>

        <div style="display: flex; gap: 20px; flex-wrap: wrap; margin-top: 20px;">
            <div style="flex: 1; min-width: 300px; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h2>Projects</h2>
                <?php if ( ! empty( $projects ) ) : ?>
                    <form method="get">
                        <input type="hidden" name="page" value="oxigen-rank-tracker">
                        <label for="project_id"><strong>Select Project to Work On:</strong></label>
                        <select name="project_id" id="project_id" onchange="this.form.submit()" style="width: 100%;">
                            <?php
                            foreach ( $projects as $id => $project ) {
                                echo '<option value="' . esc_attr( $id ) . '" ' . selected( $current_project_id, $id, false ) . '>' . esc_html( $project['name'] ) . '</option>';
                            }
                            ?>
                        </select>
                        <noscript><input type="submit" class="button" value="Select"></noscript>
                    </form>
                <?php else : ?>
                    <p>No projects created yet. Please add a new project.</p>
                <?php endif; ?>
                
                <hr>
                <?php ort_display_add_project_form(); ?>
            </div>

            <?php if ( $current_project_id && isset( $projects[$current_project_id] ) ) : ?>
                <div style="flex: 2; min-width: 400px; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <h2>Settings for "<?php echo esc_html($projects[$current_project_id]['name']); ?>"</h2>
                    <form method="post" action="options.php">
                        <?php
                        settings_fields( 'ort_settings_group' );
                        ?>
                        <input type="hidden" name="ort_project_id" value="<?php echo esc_attr( $current_project_id ); ?>">
                        <?php
                        do_settings_sections( 'oxigen-rank-tracker' );
                        submit_button( 'Save Project Settings' );
                        ?>
                    </form>
                    
                    <hr>

                    <h2>Add Keyword</h2>
                    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                        <input type="hidden" name="action" value="ort_add_keyword">
                        <input type="hidden" name="ort_project_id" value="<?php echo esc_attr( $current_project_id ); ?>">
                        <?php wp_nonce_field( 'ort_add_keyword_nonce' ); ?>
                        <label for="ort_new_keyword">New Keyword:</label>
                        <input type="text" id="ort_new_keyword" name="ort_new_keyword" required style="width: 100%;">
                        <?php submit_button( 'Add Keyword', 'secondary' ); ?>
                    </form>

                    <hr>

                    <h2>Existing Keywords and Rankings</h2>
                    <?php ort_display_keyword_ranks( $current_project_id ); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

// Project add form
function ort_display_add_project_form() {
    ?>
    <h3>Add New Project</h3>
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <input type="hidden" name="action" value="ort_add_project">
        <?php wp_nonce_field( 'ort_add_project_nonce' ); ?>
        <label for="ort_new_project_name">Project Name:</label>
        <input type="text" id="ort_new_project_name" name="ort_new_project_name" required style="width: 100%;">
        <?php submit_button( 'Create New Project', 'primary' ); ?>
    </form>
    <?php
}

// Register settings
add_action( 'admin_init', 'ort_register_settings' );
function ort_register_settings() {
    register_setting( 'ort_settings_group', 'ort_project_settings', 'ort_save_project_settings' );

    add_settings_section(
        'ort_settings_section',
        'General Settings',
        function() { echo '<p>Settings for the project\'s website, reporting email, and check frequency.</p>'; },
        'oxigen-rank-tracker'
    );

    add_settings_field( 'ort_website', 'Website', 'ort_website_field_callback', 'oxigen-rank-tracker', 'ort_settings_section' );
    add_settings_field( 'ort_email', 'Reporting Email Address', 'ort_email_field_callback', 'oxigen-rank-tracker', 'ort_settings_section' );
    add_settings_field( 'ort_interval', 'Check Frequency', 'ort_interval_field_callback', 'oxigen-rank-tracker', 'ort_settings_section' );
}

/**
 * Helper function to get the current project ID from the URL or fallback to the first project.
 * @return string The current project ID.
 */
function ort_get_current_project_id_for_settings() {
    $projects = ort_get_projects();
    // Get project ID from the URL if it exists, otherwise get the first available project.
    return ! empty( $_GET['project_id'] ) ? sanitize_text_field( $_GET['project_id'] ) : ( ! empty( $projects ) ? array_key_first( $projects ) : 0 );
}

// Field Callback Functions
function ort_website_field_callback() {
    $project_id = ort_get_current_project_id_for_settings();
    $website = ort_get_project_setting( $project_id, 'website' );
    echo '<input type="text" name="ort_project_settings[website]" value="' . esc_attr( $website ) . '" class="regular-text" placeholder="https://example.com"/>';
}

function ort_email_field_callback() {
    $project_id = ort_get_current_project_id_for_settings();
    $email = ort_get_project_setting( $project_id, 'email' );
    echo '<input type="email" name="ort_project_settings[email]" value="' . esc_attr( $email ) . '" class="regular-text" placeholder="email@example.com"/>';
}

function ort_interval_field_callback() {
    $project_id = ort_get_current_project_id_for_settings();
    $interval = ort_get_project_setting( $project_id, 'interval', 'daily' );
    $schedules = wp_get_schedules();
    echo '<select name="ort_project_settings[interval]">';
    foreach ( $schedules as $slug => $details ) {
        echo '<option value="' . esc_attr( $slug ) . '" ' . selected( $interval, $slug, false ) . '>' . esc_html( $details['display'] ) . '</option>';
    }
    echo '</select>';
}

// Handler for adding a new project
add_action( 'admin_post_ort_add_project', 'ort_add_project_handler' );
function ort_add_project_handler() {
    if ( ! isset( $_POST['ort_new_project_name'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'ort_add_project_nonce' ) ) {
        wp_die( 'Security error!' );
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

// Handler for adding a new keyword
add_action( 'admin_post_ort_add_keyword', 'ort_add_keyword_handler' );
function ort_add_keyword_handler() {
    if ( ! isset( $_POST['ort_new_keyword'], $_POST['_wpnonce'], $_POST['ort_project_id'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'ort_add_keyword_nonce' ) ) {
        wp_die( 'Security error!' );
    }

    $project_id = sanitize_text_field( $_POST['ort_project_id'] );
    $new_keyword = sanitize_text_field( $_POST['ort_new_keyword'] );
    $projects = ort_get_projects();

    if ( ! empty( $new_keyword ) && isset( $projects[$project_id] ) ) {
        if ( ! isset( $projects[$project_id]['keywords'] ) ) {
            $projects[$project_id]['keywords'] = array();
        }
        // Add keyword only if it doesn't exist
        if ( ! in_array( $new_keyword, $projects[$project_id]['keywords'] ) ) {
            $projects[$project_id]['keywords'][] = $new_keyword;
            update_option( 'ort_projects', $projects );
        }
    }

    wp_redirect( admin_url( 'admin.php?page=oxigen-rank-tracker&project_id=' . $project_id ) );
    exit;
}

// Handler for deleting a keyword
add_action( 'admin_post_ort_delete_keyword', 'ort_delete_keyword_handler' );
function ort_delete_keyword_handler() {
    if ( ! isset( $_GET['project_id'], $_GET['keyword_index'], $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'ort_delete_keyword_nonce' ) ) {
        wp_die( 'Security error!' );
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
