<?php
namespace Wemdo\AIAgent;

if (!defined('ABSPATH')) {
    exit;
}

class Plugin {
    const OPTION_KEY = 'wemdo_ai_agent_settings';

    /**
     * Locales the AI Layer widget knows how to render. Single source of
     * truth for both `map_locale()` (auto-detection from WP locale) and
     * `Settings::handle_save()` (validation of the radio-selected value).
     * The settings page view picks a curated subset for the radio list;
     * the validator accepts any of these so admins on a non-listed locale
     * can still set it via direct option write or future UI expansion.
     */
    const SUPPORTED_LANG_CODES = [
        'en', 'hr', 'sl', 'de', 'it', 'fr', 'es', 'pt', 'pl',
        'cs', 'sk', 'hu', 'ro', 'nl', 'sv', 'da', 'no', 'fi',
    ];

    public static function boot(): void {
        // Translations: WP 6.7+ deprecates calling load_plugin_textdomain
        // before the `init` hook (the .mo loader needs context that isn't
        // ready on `plugins_loaded`). For WP.org-distributed plugins WP
        // auto-loads translations since 4.6 and this call is redundant —
        // but keeping it for sites that vendor the plugin manually.
        // Defer to `init` so 6.7+ doesn't emit a deprecation notice.
        add_action('init', function () {
            load_plugin_textdomain('wemdo-ai-agent', false, dirname(plugin_basename(WEMDO_AI_AGENT_FILE)) . '/languages');
        });

        // Sub-services register their own hooks
        Settings::init();
        WidgetEmbed::init();
        Hooks::init();

        // First-activation admin notice
        add_action('admin_notices', [self::class, 'maybe_show_connect_notice']);
    }

    public static function on_activate(): void {
        $defaults = [
            'api_key'          => '',
            'tenant_id'        => '',
            'tenant_name'      => '',
            'tenant_plan'      => '',
            'display_mode'     => 'all',          // all | posts | pages | disabled
            'hidden_pages'     => [],              // array of url path strings
            'language'         => 'auto',          // auto | en | hr | sl | de | ...
            'kb_sync_enabled'  => false,           // future option C
            'privacy_accepted' => false,           // checkbox in About section
        ];
        if (!get_option(self::OPTION_KEY)) {
            update_option(self::OPTION_KEY, $defaults);
        }
    }

    public static function settings(): array {
        $opts = get_option(self::OPTION_KEY, []);
        return is_array($opts) ? $opts : [];
    }

    public static function maybe_show_connect_notice(): void {
        $opts = self::settings();
        if (!empty($opts['api_key'])) {
            return;
        }
        $url = admin_url('options-general.php?page=wemdo-ai-agent');
        printf(
            '<div class="notice notice-info is-dismissible"><p><strong>%s</strong> %s <a href="%s">%s</a></p></div>',
            esc_html__('Wemdo AI Agent activated.', 'wemdo-ai-agent'),
            esc_html__('Connect your account to start.', 'wemdo-ai-agent'),
            esc_url($url),
            esc_html__('Open settings →', 'wemdo-ai-agent')
        );
    }

    /**
     * Maps a WP locale (e.g. "hr_HR") to a 2-char widget lang ("hr").
     * Falls back to "en" for any unrecognized locale.
     */
    public static function map_locale(string $wp_locale): string {
        $two = strtolower(substr($wp_locale, 0, 2));
        return in_array($two, self::SUPPORTED_LANG_CODES, true) ? $two : 'en';
    }
}
