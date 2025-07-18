<?php
// filepath: includes/rank-tracker-functions.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Checks Google ranking for a specific keyword and saves the history.
 *
 * @param string $keyword The keyword to search for.
 * @param string $website The website to check for.
 * @param string $project_id The ID of the project this keyword belongs to.
 * @return int|false Rank number or false on error.
 */
function ort_get_google_rank( $keyword, $website, $project_id ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'ort_rank_history';
    
    // Note: Simple GET requests to Google are often blocked. 
    // A more robust solution would use a dedicated SERP API.
    $url = 'https://www.google.com/search?q=' . urlencode( $keyword ) . '&num=100'; // Check top 100 results

    $response = wp_remote_get( $url, array(
        'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/100.0.4896.127 Safari/537.36'
    ) );

    if ( is_wp_error( $response ) ) {
        ort_debug_log( 'Google search request failed: ' . $response->get_error_message() );
        return false;
    }

    $body = wp_remote_retrieve_body( $response );

    if ( empty( $body ) ) {
        ort_debug_log( 'Google search result is empty.' );
        return false;
    }

    // This is a very basic rank detection and might not be accurate.
    // It finds the position of the domain string in the results.
    // A real implementation should parse the search result entries.
    $rank = strpos( $body, $website );

    $rank_to_store = ($rank !== false) ? $rank : 0; // Store 0 if not found

    // Save rank history
    $wpdb->insert(
        $table_name,
        array(
            'project_id' => $project_id,
            'keyword'    => $keyword,
            'website'    => $website,
            'rank'       => $rank_to_store,
            'timestamp'  => current_time( 'mysql' ),
        ),
        array(
            '%s',
            '%s',
            '%s',
            '%d',
            '%s',
        )
    );

    return $rank_to_store > 0 ? $rank_to_store : false;
}

/**
 * Creates the custom database table for rank history upon plugin activation.
 */
function ort_create_rank_history_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'ort_rank_history';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        project_id varchar(255) NOT NULL,
        keyword varchar(255) NOT NULL,
        website varchar(255) NOT NULL,
        rank int(11) NOT NULL,
        timestamp datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}

/**
 * Retrieves the rank history for a specific keyword.
 *
 * @param string $keyword The keyword to get history for.
 * @param string $project_id The project ID.
 * @return array An array of ranking data.
 */
function ort_get_rank_history_for_keyword( $keyword, $project_id ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'ort_rank_history';

    $query = $wpdb->prepare(
        "SELECT rank, timestamp FROM $table_name WHERE keyword = %s AND project_id = %s ORDER BY timestamp DESC LIMIT 30",
        $keyword,
        $project_id
    );

    return $wpdb->get_results( $query, ARRAY_A );
}