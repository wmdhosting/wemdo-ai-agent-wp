# Wemdo AI Agent — WordPress plugin

Embeds the [Wemdo AI Agent](https://ai.wmd.hr) chat widget on any WordPress site.

- **WP.org listing**: https://wordpress.org/plugins/wemdo-ai-agent/ (pending review)
- **Backend SaaS**: https://ai.wmd.hr
- **License**: GPLv2+

## What it does

- Adds a settings page at **Settings → Wemdo AI Agent** where the admin pastes their Wemdo API key
- Renders a single `<script>` tag in `wp_head` that loads the AI Agent widget from Wemdo's CDN
- Display rules: all pages / posts only / pages only / disabled, plus per-URL hide list
- Auto-detects WordPress locale and forwards a 2-char language hint to the widget
- Designed to coexist with WooCommerce, Polylang/WPML, Elementor/Divi/Bricks, and major caching plugins

Visitor chat traffic goes **directly browser → Wemdo backend**. The plugin doesn't proxy messages through WordPress, so it has zero performance impact on the host site.

## Why thin plugin, smart backend

Widget colors, conversational behavior, knowledge base, operator queue — all of those are configured in the Wemdo admin panel, not here. Single source of truth prevents config drift if the tenant runs multiple sites.

## Development

```bash
composer install
./vendor/bin/phpunit       # 4 unit tests for the locale mapper
```

Manual integration testing on a real WP install (recommended via [Herd](https://herd.laravel.com/) or any local WP):

1. Symlink or copy this repo into `wp-content/plugins/wemdo-ai-agent/`
2. Activate plugin in WP admin
3. **Settings → Wemdo AI Agent**: paste a real `ak_` key from https://ai.wmd.hr/admin/api-keys
4. Click **Verify** → expect green "Connected to tenant {name}"
5. **Save Changes**, then visit any front-end page → page source contains:
   ```html
   <!-- Wemdo AI Agent v1.0.0 -->
   <script src="https://ai.wmd.hr/widget/chat.js" data-api-key="ak_..." data-lang="en" async></script>
   ```

## Releasing

Three places to bump on every version:

- `wemdo-ai-agent.php` plugin header `Version:`
- `wemdo-ai-agent.php` constant `WEMDO_AI_AGENT_VERSION`
- `readme.txt` `Stable tag:`

Then:

```bash
git tag -a v1.0.0 -m "v1.0.0"
git push origin main v1.0.0
```

WP.org submission is one-time at https://wordpress.org/plugins/developers/add/. After approval, future releases land via SVN sync.

## Roadmap

- **v1.0** (this release) — paste API key, embed widget. Page-context awareness handled by the widget itself.
- **v1.1** — Gutenberg block + shortcode for inline chat embed
- **v2.0** — Knowledge base sync via `save_post` hook (KB sync infrastructure on the backend; this repo's `src/Hooks.php` already has empty-but-wired listeners ready)
- **v3.0** — WooCommerce-aware (product sync, customer awareness, order status hooks)
