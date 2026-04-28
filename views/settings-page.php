<?php
if (!defined('ABSPATH')) {
    exit;
}
/** @var array $opts */
/** @var string $current_locale */
/** @var string $auto_lang */
?>
<div class="wrap">
    <h1><?php esc_html_e('Wemdo AI Agent', 'wemdo-ai-agent'); ?></h1>
    <?php settings_errors('wemdo_ai_agent'); ?>

    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="wemdo-settings-form">
        <input type="hidden" name="action" value="wemdo_save_settings">
        <?php wp_nonce_field(\Wemdo\AIAgent\Settings::NONCE_ACTION, \Wemdo\AIAgent\Settings::NONCE_FIELD); ?>

        <h2><?php esc_html_e('1. Connection', 'wemdo-ai-agent'); ?></h2>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><label for="wemdo-api-key"><?php esc_html_e('API key', 'wemdo-ai-agent'); ?></label></th>
                <td>
                    <input type="password" id="wemdo-api-key" name="api_key" value="<?php echo esc_attr($opts['api_key'] ?? ''); ?>" class="regular-text" autocomplete="off">
                    <button type="button" class="button" id="wemdo-toggle-key"><?php esc_html_e('Show', 'wemdo-ai-agent'); ?></button>
                    <button type="button" class="button" id="wemdo-verify-key"><?php esc_html_e('Verify', 'wemdo-ai-agent'); ?></button>
                    <p id="wemdo-verify-result" style="margin-top: 8px;">
                        <?php if (!empty($opts['tenant_name'])): ?>
                            <span style="color: #008a20;">✓ <?php
                                printf(
                                    /* translators: 1: tenant name, 2: plan */
                                    esc_html__('Connected to tenant %1$s (%2$s plan)', 'wemdo-ai-agent'),
                                    esc_html($opts['tenant_name']),
                                    esc_html($opts['tenant_plan'] ?: '?')
                                );
                            ?></span>
                        <?php endif; ?>
                    </p>
                    <p class="description">
                        <?php esc_html_e('Get your key from:', 'wemdo-ai-agent'); ?>
                        <a href="https://ai.wmd.hr/admin/api-keys" target="_blank" rel="noopener">https://ai.wmd.hr/admin/api-keys</a>
                    </p>
                </td>
            </tr>
        </table>

        <h2><?php esc_html_e('2. Display', 'wemdo-ai-agent'); ?></h2>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><?php esc_html_e('Show widget on', 'wemdo-ai-agent'); ?></th>
                <td>
                    <?php
                    $modes = [
                        'all'      => __('All pages', 'wemdo-ai-agent'),
                        'posts'    => __('Posts only', 'wemdo-ai-agent'),
                        'pages'    => __('Pages only', 'wemdo-ai-agent'),
                        'disabled' => __('Disabled (testing)', 'wemdo-ai-agent'),
                    ];
                    foreach ($modes as $val => $label):
                    ?>
                        <label style="display: block; margin: 4px 0;">
                            <input type="radio" name="display_mode" value="<?php echo esc_attr($val); ?>" <?php checked($opts['display_mode'] ?? 'all', $val); ?>>
                            <?php echo esc_html($label); ?>
                        </label>
                    <?php endforeach; ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="wemdo-hidden-pages"><?php esc_html_e('Hide on these URLs', 'wemdo-ai-agent'); ?></label></th>
                <td>
                    <textarea id="wemdo-hidden-pages" name="hidden_pages" rows="4" class="large-text code" placeholder="/checkout&#10;/my-account"><?php echo esc_textarea(implode("\n", $opts['hidden_pages'] ?? [])); ?></textarea>
                    <p class="description"><?php esc_html_e('One path per line. Matches by prefix (e.g. "/checkout" hides on "/checkout" and "/checkout/order/123").', 'wemdo-ai-agent'); ?></p>
                </td>
            </tr>
        </table>

        <h2><?php esc_html_e('3. Language', 'wemdo-ai-agent'); ?></h2>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><?php esc_html_e('Widget language', 'wemdo-ai-agent'); ?></th>
                <td>
                    <label style="display: block; margin: 4px 0;">
                        <input type="radio" name="language" value="auto" <?php checked($opts['language'] ?? 'auto', 'auto'); ?>>
                        <?php
                            printf(
                                /* translators: 1: WP locale, 2: 2-char lang */
                                esc_html__('Auto (WordPress locale is %1$s → sending data-lang="%2$s")', 'wemdo-ai-agent'),
                                esc_html($current_locale),
                                esc_html($auto_lang)
                            );
                        ?>
                    </label>
                    <?php
                    $langs = [
                        'en' => __('English', 'wemdo-ai-agent'),
                        'hr' => __('Croatian', 'wemdo-ai-agent'),
                        'sl' => __('Slovenian', 'wemdo-ai-agent'),
                        'de' => __('German', 'wemdo-ai-agent'),
                        'it' => __('Italian', 'wemdo-ai-agent'),
                        'fr' => __('French', 'wemdo-ai-agent'),
                        'es' => __('Spanish', 'wemdo-ai-agent'),
                        'pt' => __('Portuguese', 'wemdo-ai-agent'),
                        'pl' => __('Polish', 'wemdo-ai-agent'),
                    ];
                    foreach ($langs as $val => $label):
                    ?>
                        <label style="display: block; margin: 4px 0;">
                            <input type="radio" name="language" value="<?php echo esc_attr($val); ?>" <?php checked($opts['language'] ?? 'auto', $val); ?>>
                            <?php echo esc_html($label); ?>
                        </label>
                    <?php endforeach; ?>
                </td>
            </tr>
        </table>

        <h2><?php esc_html_e('4. About', 'wemdo-ai-agent'); ?></h2>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><?php esc_html_e('Plugin info', 'wemdo-ai-agent'); ?></th>
                <td>
                    <p>
                        <?php
                            printf(
                                /* translators: %s = version */
                                esc_html__('Version: %s', 'wemdo-ai-agent'),
                                esc_html(WEMDO_AI_AGENT_VERSION)
                            );
                        ?>
                        &nbsp;|&nbsp;
                        <a href="https://ai.wmd.hr/docs" target="_blank" rel="noopener"><?php esc_html_e('Docs', 'wemdo-ai-agent'); ?></a>
                        &nbsp;|&nbsp;
                        <?php esc_html_e('Support:', 'wemdo-ai-agent'); ?>
                        <a href="mailto:support@wmd.hr">support@wmd.hr</a>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Privacy', 'wemdo-ai-agent'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="privacy_accepted" value="1" <?php checked(!empty($opts['privacy_accepted'])); ?>>
                        <?php esc_html_e('I understand this plugin sends visitor messages to ai.wmd.hr.', 'wemdo-ai-agent'); ?>
                    </label>
                    <p class="description">
                        <a href="https://ai.wmd.hr/privacy" target="_blank" rel="noopener"><?php esc_html_e('Privacy policy', 'wemdo-ai-agent'); ?></a>
                        &nbsp;|&nbsp;
                        <a href="https://ai.wmd.hr/dpa" target="_blank" rel="noopener"><?php esc_html_e('Data processing agreement', 'wemdo-ai-agent'); ?></a>
                    </p>
                </td>
            </tr>
        </table>

        <?php submit_button(__('Save Changes', 'wemdo-ai-agent')); ?>
    </form>
</div>

<script id="wemdo-settings-js" type="application/json">
<?php echo wp_json_encode([
    'nonce'      => wp_create_nonce(\Wemdo\AIAgent\Settings::NONCE_ACTION),
    'verifying'  => __('Verifying…', 'wemdo-ai-agent'),
    'show'       => __('Show', 'wemdo-ai-agent'),
    'hide'       => __('Hide', 'wemdo-ai-agent'),
]); ?>
</script>
<script>
(function () {
    // Read PHP-provided strings + nonce from a JSON island — keeps this
    // script free of inline PHP echoes that are easy to misescape, and
    // avoids any innerHTML concatenation. All UI updates use textContent
    // and createElement, so no XSS surface even if a backend message
    // ever carried unsafe characters.
    var cfg = {};
    try { cfg = JSON.parse(document.getElementById('wemdo-settings-js').textContent); } catch (e) {}
    var nonce      = cfg.nonce || '';
    var t_verifying = cfg.verifying || 'Verifying…';
    var t_show     = cfg.show || 'Show';
    var t_hide     = cfg.hide || 'Hide';

    var keyInput  = document.getElementById('wemdo-api-key');
    var toggleBtn = document.getElementById('wemdo-toggle-key');
    var verifyBtn = document.getElementById('wemdo-verify-key');
    var result    = document.getElementById('wemdo-verify-result');

    toggleBtn.addEventListener('click', function () {
        var showing = keyInput.type === 'text';
        keyInput.type = showing ? 'password' : 'text';
        toggleBtn.textContent = showing ? t_show : t_hide;
    });

    function setResult(color, text) {
        // Clear via removeChild loop — no innerHTML touch
        while (result.firstChild) result.removeChild(result.firstChild);
        result.style.fontStyle = '';
        var span = document.createElement('span');
        span.style.color = color;
        span.textContent = text;
        result.appendChild(span);
    }

    function setPending() {
        while (result.firstChild) result.removeChild(result.firstChild);
        result.style.fontStyle = 'italic';
        result.textContent = t_verifying;
    }

    verifyBtn.addEventListener('click', function () {
        setPending();
        var fd = new FormData();
        fd.append('action', 'wemdo_verify_key');
        fd.append('api_key', keyInput.value);
        fd.append('nonce', nonce);
        fetch(window.ajaxurl, { method: 'POST', credentials: 'same-origin', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                var colors = { valid: '#008a20', invalid: '#d63638', unreachable: '#dba617' };
                var prefix = { valid: '✓', invalid: '✗', unreachable: '⚠' };
                var color = colors[d.status] || '#000';
                var text  = (prefix[d.status] || '') + ' ' + (d.message || '');
                setResult(color, text);
            })
            .catch(function (e) {
                setResult('#d63638', '✗ ' + (e && e.message ? e.message : 'Request failed'));
            });
    });
})();
</script>
