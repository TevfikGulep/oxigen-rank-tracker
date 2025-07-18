<?php
// filepath: includes/email-reports.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Sends the ranking report via email for a specific project.
 * @param string $project_id The ID of the project to send the report for.
 */
function ort_send_email_report( $project_id ) {
    $keywords = ort_get_project_setting( $project_id, 'keywords' );
    $website = ort_get_project_setting( $project_id, 'website' );
    $email = ort_get_project_setting( $project_id, 'email' );
    $project_name = ort_get_project_setting( $project_id, 'name' );

    if ( empty( $keywords ) || empty( $website ) || empty( $email ) ) {
        ort_debug_log( 'Failed to send email: Missing required settings. Project ID: ' . $project_id );
        return;
    }

    if ( ! is_array( $keywords ) ) {
        ort_debug_log( 'Keywords is not a valid array. Project ID: ' . $project_id );
        return;
    }

    $message = '<h1>Ranking Report for ' . esc_html($project_name) . '</h1><ul>';

    foreach ( $keywords as $keyword ) {
        $keyword = trim( $keyword );
        // The rank check function now also saves the rank to the database
        $rank = ort_get_google_rank( $keyword, $website, $project_id );

        if ( $rank !== false ) {
            $message .= '<li>' . esc_html($keyword) . ': ' . $rank . '</li>';
        } else {
            $message .= '<li>' . esc_html($keyword) . ': Rank not found.</li>';
        }
    }

    $message .= '</ul>';

    $subject = 'Oxigen Rank Tracker - Ranking Report for ' . $project_name;
    $headers = array( 'Content-Type: text/html; charset=UTF-8' );

    $result = wp_mail( $email, $subject, $message, $headers );

    if ( ! $result ) {
        ort_debug_log( 'Failed to send email. Project ID: ' . $project_id );
    } else {
        ort_debug_log( 'Email sent successfully to ' . $email . ' - Project ID: ' . $project_id );
    }
}

// Hook to send the scheduled email
add_action( 'ort_send_scheduled_email', 'ort_send_email_report' );

/**
 * Schedules the email reports based on project settings.
 */
function ort_schedule_email_reports() {
    $projects = ort_get_projects();

    foreach ( $projects as $project_id => $project ) {
        $interval = ort_get_project_setting( $project_id, 'interval', 'daily' );

        // First, clear any existing scheduled hooks for this project to avoid duplicates
        if ( wp_next_scheduled( 'ort_send_scheduled_email', array( $project_id ) ) ) {
            wp_clear_scheduled_hook( 'ort_send_scheduled_email', array( $project_id ) );
        }

        // Then, schedule the new event
        if ( ! wp_next_scheduled( 'ort_send_scheduled_email', array( $project_id ) ) ) {
            wp_schedule_event( time(), $interval, 'ort_send_scheduled_email', array( $project_id ) );
            ort_debug_log("Scheduled email for project {$project_id} with interval {$interval}");
        }
    }
}
// Use 'init' hook to ensure schedules are set up on every load
add_action( 'init', 'ort_schedule_email_reports' );
