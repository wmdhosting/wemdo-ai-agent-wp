<?php
/**
 * Plugin Name:       Wemdo AI Agent
 * Plugin URI:        https://ai-website-layer.com
 * Description:       Add an AI agent to your WordPress site — answers visitors, qualifies leads, captures callbacks.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Wemdo
 * Author URI:        https://wmd.hr
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wemdo-ai-agent
 * Domain Path:       /languages
 */

if (!defined('ABSPATH')) {
    exit; // No direct access.
}

define('WEMDO_AI_AGENT_VERSION', '1.0.0');
define('WEMDO_AI_AGENT_FILE', __FILE__);
define('WEMDO_AI_AGENT_DIR', plugin_dir_path(__FILE__));
define('WEMDO_AI_AGENT_URL', plugin_dir_url(__FILE__));

// Tiny PSR-4 autoloader — no Composer in plugin trunk (WP.org rejects
// vendored deps that aren't in the published artifact).
spl_autoload_register(function ($class) {
    $prefix = 'Wemdo\\AIAgent\\';
    $base_dir = __DIR__ . '/src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// Boot
add_action('plugins_loaded', function () {
    \Wemdo\AIAgent\Plugin::boot();
});

// Activation
register_activation_hook(__FILE__, ['Wemdo\\AIAgent\\Plugin', 'on_activate']);
