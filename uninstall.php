<?php
/**
 * Runs ONLY on Delete (not Deactivate). Cleans up plugin options.
 */
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

delete_option('wemdo_ai_agent_settings');

// Multisite cleanup: every site in the network
if (is_multisite()) {
    global $wpdb;
    $blog_ids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->blogs}");
    foreach ($blog_ids as $blog_id) {
        switch_to_blog((int) $blog_id);
        delete_option('wemdo_ai_agent_settings');
        restore_current_blog();
    }
}
