<?php
// filepath: includes/rank-tracker-functions.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Checks Google ranking for a specific keyword and saves the history.
 *
 * @param string $keyword Keyword to search for.
 * @param string $website Website to check.
 * @return int|false Rank number or false on error.
 */
function ort_get_google_rank( $keyword, $website ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'ort_rank_history';
    $url = 'https://www.google.com/search?q=' . urlencode( $keyword );

    $response = wp_remote_get( $url );

    if ( is_wp_error( $response ) ) {
        ort_debug_log( 'Google search request failed: ' . $response->get_error_message() );
        return false;
    }

    $body = wp_remote_retrieve_body( $response );

    if ( empty( $body ) ) {
        ort_debug_log( 'Google search result is empty.' );
        return false;
    }

    // Simple rank finding process (a more advanced method may be needed)
    $rank = strpos( $body, $website );

    if ( $rank !== false ) {
        // Save rank history
        $wpdb->insert(
            $table_name,
            array(
                'keyword' => $keyword,
                'website' => $website,
                'rank' => $rank,
                'timestamp' => current_time( 'mysql' ),
            ),
            array(
                '%s',
                '%s',
                '%d',
                '%s',
            )
        );

        return $rank; // We return a simple position, can be improved for a more accurate ranking.
    } else {
        return false;
    }
}

/**
 * Function to run when the plugin is activated and creates the custom database table.
 */
function ort_create_rank_history_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'ort_rank_history';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        timestamp datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        keyword varchar(255) NOT NULL,
        website varchar(255) NOT NULL,
        rank int(11) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}