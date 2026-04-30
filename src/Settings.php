<?php
namespace Wemdo\AIAgent;

if (!defined('ABSPATH')) {
    exit;
}

class Settings {
    const NONCE_ACTION = 'wemdo_save_settings';
    const NONCE_FIELD  = 'wemdo_settings_nonce';

    public static function init(): void {
        add_action('admin_menu', [self::class, 'register_menu']);
        add_action('admin_post_wemdo_save_settings', [self::class, 'handle_save']);
        add_action('wp_ajax_wemdo_verify_key', [self::class, 'handle_verify_ajax']);
    }

    public static function register_menu(): void {
        add_options_page(
            __('Wemdo AI Agent', 'wemdo-ai-agent'),
            __('Wemdo AI Agent', 'wemdo-ai-agent'),
            'manage_options',
            'wemdo-ai-agent',
            [self::class, 'render_page']
        );
    }

    public static function render_page(): void {
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions.', 'wemdo-ai-agent'));
        }
        $opts = Plugin::settings();
        $current_locale = get_locale();
        $auto_lang = Plugin::map_locale($current_locale);
        include WEMDO_AI_AGENT_DIR . 'views/settings-page.php';
    }

    public static function handle_save(): void {
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions.', 'wemdo-ai-agent'));
        }
        check_admin_referer(self::NONCE_ACTION, self::NONCE_FIELD);

        $existing = Plugin::settings();
        // The settings page renders an EMPTY password field by design (we
        // never echo the saved key into the DOM). An empty submission means
        // "keep the existing key" — only a non-empty paste replaces it.
        $pasted = isset($_POST['api_key']) ? sanitize_text_field(wp_unslash($_POST['api_key'])) : '';
        $existing_key = $existing['api_key'] ?? '';
        $api_key = $pasted !== '' ? $pasted : $existing_key;

        // Verify only when the admin actually pasted a NEW key.
        $verify = ['status' => 'unchanged', 'data' => [], 'message' => ''];
        if ($pasted !== '' && $pasted !== $existing_key) {
            $verify = Api::verify_key($pasted);
            if ($verify['status'] === 'invalid') {
                add_settings_error('wemdo_ai_agent', 'invalid_key', $verify['message'], 'error');
                set_transient('settings_errors', get_settings_errors('wemdo_ai_agent'), 30);
                wp_safe_redirect(admin_url('options-general.php?page=wemdo-ai-agent'));
                exit;
            }
        }

        // hidden_pages: split lines + trim + sanitize_text_field per-line
        // (strips HTML, normalizes whitespace) before storing. Functionally
        // inert today since WidgetEmbed.php uses these only as strpos
        // prefix-matchers against REQUEST_URI, but storing pre-sanitized
        // values is the WP convention and removes one footgun for any
        // future code that renders these (e.g. a "managed list" UI).
        $hidden_raw = isset($_POST['hidden_pages']) ? wp_unslash($_POST['hidden_pages']) : '';
        $hidden_lines = array_filter(array_map(
            static function ($line) { return sanitize_text_field((string) $line); },
            explode("\n", (string) $hidden_raw)
        ));

        $allowed_modes = ['all', 'posts', 'pages', 'disabled'];
        $display_mode = isset($_POST['display_mode']) ? sanitize_text_field(wp_unslash($_POST['display_mode'])) : 'all';
        if (!in_array($display_mode, $allowed_modes, true)) {
            $display_mode = 'all';
        }

        // Language whitelist sourced from Plugin::SUPPORTED_LANG_CODES
        // (single source of truth shared with map_locale).
        $language_in = isset($_POST['language']) ? sanitize_text_field(wp_unslash($_POST['language'])) : 'auto';
        $allowed_langs = array_merge(['auto'], Plugin::SUPPORTED_LANG_CODES);
        if (!in_array($language_in, $allowed_langs, true)) {
            $language_in = 'auto';
        }

        $new = [
            'api_key'          => $api_key,
            'tenant_id'        => $verify['status'] === 'valid' ? ($verify['data']['tenant_id'] ?? '') : ($existing['tenant_id'] ?? ''),
            'tenant_name'      => $verify['status'] === 'valid' ? ($verify['data']['tenant_name'] ?? '') : ($existing['tenant_name'] ?? ''),
            'tenant_plan'      => $verify['status'] === 'valid' ? ($verify['data']['plan'] ?? '') : ($existing['tenant_plan'] ?? ''),
            'display_mode'     => $display_mode,
            'hidden_pages'     => array_values($hidden_lines),
            'language'         => $language_in,
            'kb_sync_enabled'  => false, // v1.0 always false
            'privacy_accepted' => !empty($_POST['privacy_accepted']),
        ];

        update_option(Plugin::OPTION_KEY, $new);

        wp_cache_flush();
        do_action('wemdo_clear_cache');

        if ($verify['status'] === 'valid') {
            add_settings_error('wemdo_ai_agent', 'saved', $verify['message'], 'success');
        } elseif ($verify['status'] === 'unreachable') {
            add_settings_error('wemdo_ai_agent', 'unreachable', $verify['message'], 'warning');
        } else {
            add_settings_error('wemdo_ai_agent', 'saved_basic', __('Settings saved.', 'wemdo-ai-agent'), 'success');
        }
        set_transient('settings_errors', get_settings_errors('wemdo_ai_agent'), 30);
        wp_safe_redirect(admin_url('options-general.php?page=wemdo-ai-agent'));
        exit;
    }

    public static function handle_verify_ajax(): void {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'wemdo-ai-agent')], 403);
        }
        check_ajax_referer(self::NONCE_ACTION, 'nonce');
        $pasted = isset($_POST['api_key']) ? sanitize_text_field(wp_unslash($_POST['api_key'])) : '';
        // Empty input means "verify the key already on file" — the settings
        // page renders an empty input for security, so this is the normal
        // path when the admin clicks Verify without re-pasting.
        $key = $pasted !== '' ? $pasted : (Plugin::settings()['api_key'] ?? '');
        $result = Api::verify_key($key);
        wp_send_json($result);
    }
}
