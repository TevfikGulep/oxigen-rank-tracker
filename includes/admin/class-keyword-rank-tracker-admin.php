<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Keyword_Rank_Tracker_Admin {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    public function add_admin_menu() {
        // Debug log for menu creation
        if (function_exists('krt_debug_log')) {
            krt_debug_log('Trying to add Keyword Rank Tracker menu.');
        }
        try {
            // Position 2 puts it right below Dashboard
            add_menu_page(
                'Keyword Rank Tracker',
                'Keyword Rank Tracker',
                'manage_options',
                'keyword_rank_tracker',
                array( $this, 'admin_page_display' ),
                'dashicons-chart-line',
                2
            );
            if (function_exists('krt_debug_log')) {
                krt_debug_log('Menu added successfully.');
            }
        } catch (Throwable $e) {
            if (function_exists('krt_debug_log')) {
                krt_debug_log('Menu error: ' . $e->getMessage());
            }
        }
    }

    public function register_settings() {
        register_setting( 'keyword_rank_tracker_options_group', 'keyword_rank_tracker_options' );
    }

    public function admin_page_display() {
        include_once plugin_dir_path( __FILE__ ) . 'partials/keyword-rank-tracker-admin-display.php';
    }
}