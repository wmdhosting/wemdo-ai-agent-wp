<?php
namespace Wemdo\AIAgent;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Placeholder for future option C (KB sync). Hooks register but
 * handlers early-return so the plugin can ship clean v1.0 trunk while
 * the upgrade path requires only flipping `kb_sync_enabled` + filling
 * in handler bodies + adding the backend sync endpoints. WP.org review
 * is faster for "behavior change toggle" releases than for "new
 * feature" releases that introduce new hooks.
 */
class Hooks {
    public static function init(): void {
        add_action('save_post', [self::class, 'on_save_post'], 10, 3);
        add_action('delete_post', [self::class, 'on_delete_post'], 10, 1);
        add_action('transition_post_status', [self::class, 'on_transition'], 10, 3);
    }

    public static function on_save_post($post_id, $post, $update): void {
        $opts = Plugin::settings();
        if (empty($opts['kb_sync_enabled'])) {
            return; // option C inactive in v1.0
        }
        // future: build payload + Api::sync_post()
    }

    public static function on_delete_post($post_id): void {
        $opts = Plugin::settings();
        if (empty($opts['kb_sync_enabled'])) {
            return;
        }
        // future: Api::delete_post($post_id)
    }

    public static function on_transition($new_status, $old_status, $post): void {
        $opts = Plugin::settings();
        if (empty($opts['kb_sync_enabled'])) {
            return;
        }
        // future: re-sync on publish, drop on trash
    }
}
