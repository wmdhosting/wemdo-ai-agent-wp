<?php
namespace Wemdo\AIAgent;

if (!defined('ABSPATH')) {
    exit;
}

class WidgetEmbed {
    public static function init(): void {
        add_action('wp_head', [self::class, 'render'], 99);
    }

    public static function render(): void {
        $opts = Plugin::settings();

        if (empty($opts['api_key'])) {
            return;
        }
        if (($opts['display_mode'] ?? 'all') === 'disabled') {
            return;
        }
        if (!self::should_render_on_current_page($opts)) {
            return;
        }

        $api_key = $opts['api_key'];
        $lang = self::resolve_lang($opts);
        // Per-day cache-bust on the widget URL. ai.wmd.hr serves chat.js
        // through Engintron + browser caches that hold for ~30 days; without
        // a daily key, every WordPress install would freeze on whichever
        // version of chat.js their browser fetched first after install.
        // Daily granularity (UTC) matches what ai.wmd.hr's own embed does
        // for self-served pages — same trade-off, same recovery window.
        $src = Api::BACKEND_URL . '/widget/chat.js?v=' . gmdate('Y-m-d');

        printf(
            "\n<!-- Wemdo AI Agent v%s -->\n<script src=\"%s\" data-api-key=\"%s\" data-lang=\"%s\" async></script>\n",
            esc_attr(WEMDO_AI_AGENT_VERSION),
            esc_url($src),
            esc_attr($api_key),
            esc_attr($lang)
        );
    }

    private static function should_render_on_current_page(array $opts): bool {
        $mode = $opts['display_mode'] ?? 'all';
        if ($mode === 'posts' && !is_singular('post') && !is_home()) {
            return false;
        }
        if ($mode === 'pages' && !is_page()) {
            return false;
        }

        $hidden = $opts['hidden_pages'] ?? [];
        if (!empty($hidden)) {
            // wp_unslash before parsing — magic quotes are off in modern PHP
            // but WP coding standards (and WP.org reviewers) require this
            // on every direct $_SERVER / $_POST / $_GET access.
            $raw_uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '/';
            $request_path = wp_parse_url((string) $raw_uri, PHP_URL_PATH);
            $request_path = is_string($request_path) ? $request_path : '/';
            foreach ($hidden as $pattern) {
                $pattern = trim((string) $pattern);
                if ($pattern === '') continue;
                if (strpos($request_path, $pattern) === 0) {
                    return false;
                }
            }
        }
        return true;
    }

    private static function resolve_lang(array $opts): string {
        $configured = $opts['language'] ?? 'auto';
        if ($configured === 'auto' || $configured === '') {
            return Plugin::map_locale(get_locale());
        }
        return $configured;
    }
}
