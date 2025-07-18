<?php
// filepath: includes/project-functions.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Gets all projects from the database.
 *
 * @return array The list of projects.
 */
function ort_get_projects() {
    return get_option( 'ort_projects', array() );
}

/**
 * Gets a specific setting for a project.
 *
 * @param string $project_id The project ID.
 * @param string $key The setting key.
 * @param mixed $default The default value.
 * @return mixed The setting value.
 */
function ort_get_project_setting( $project_id, $key, $default = '' ) {
    $projects = ort_get_projects();
    if ( isset( $projects[$project_id] ) && isset( $projects[$project_id][$key] ) ) {
        return $projects[$project_id][$key];
    }
    return $default;
}

/**
 * Saves project settings from the settings page.
 * This is a callback for register_setting.
 *
 * @param array $input The incoming settings.
 * @return array The sanitized settings.
 */
function ort_save_project_settings( $input ) {
    if ( ! isset( $_POST['ort_project_id'] ) ) {
        // Do not proceed if no project ID is specified
        return $input;
    }

    $project_id = sanitize_text_field( $_POST['ort_project_id'] );
    $projects = ort_get_projects();

    if ( isset( $projects[$project_id] ) ) {
        $projects[$project_id]['website'] = sanitize_text_field( $input['website'] ?? '' );
        $projects[$project_id]['email'] = sanitize_email( $input['email'] ?? '' );
        $projects[$project_id]['interval'] = sanitize_text_field( $input['interval'] ?? 'daily' );
        update_option( 'ort_projects', $projects );
    }
    
    // We return an empty array because we are saving the data to our own option ('ort_projects'),
    // effectively bypassing WordPress's own saving mechanism for this setting.
    return array();
}


/**
 * Displays the keywords and their rankings.
 *
 * @param string $project_id The project ID.
 */
function ort_display_keyword_ranks( $project_id ) {
    $keywords = ort_get_project_setting( $project_id, 'keywords', array() );

    if ( empty( $keywords ) ) {
        echo '<p>No keywords have been added yet.</p>';
        return;
    }

    echo '<table class="widefat fixed" cellspacing="0">';
    echo '<thead><tr><th style="width: 40%;">Keyword</th><th style="width: 20%;">Latest Rank</th><th style="width: 25%;">Date Checked</th><th style="width: 15%;">Action</th></tr></thead>';
    echo '<tbody>';

    foreach ( $keywords as $index => $keyword ) {
        $history = ort_get_rank_history_for_keyword( $keyword, $project_id );
        $latest_rank_info = ! empty( $history ) ? $history[0] : null;

        $rank_display = 'Not checked yet';
        $date_display = 'N/A';

        if ( $latest_rank_info ) {
            $rank_display = $latest_rank_info['rank'] > 0 ? $latest_rank_info['rank'] : 'Not Found';
            $date_display = date( 'Y-m-d H:i:s', strtotime( $latest_rank_info['timestamp'] ) );
        }

        echo '<tr>';
        echo '<td>' . esc_html( $keyword ) . '</td>';
        echo '<td>' . esc_html( $rank_display ) . '</td>';
        echo '<td>' . esc_html( $date_display ) . '</td>';
        echo '<td><a href="' . esc_url( admin_url('admin-post.php?action=ort_delete_keyword&project_id=' . $project_id . '&keyword_index=' . $index . '&_wpnonce=' . wp_create_nonce('ort_delete_keyword_nonce')) ) . '" class="button button-small button-danger">Delete</a></td>';
        echo '</tr>';
    }

    echo '</tbody></table>';
}
