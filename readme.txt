=== Wemdo AI Agent ===
Contributors: wmdhosting
Tags: chat, chatbot, ai, ai agent, live chat, lead capture, customer support
Requires at least: 6.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add an AI agent to your WordPress site — answers visitors, qualifies leads, captures callbacks.

== Description ==

Wemdo AI Agent embeds an AI-powered chat widget on your WordPress site. The agent answers visitor questions, qualifies leads, and captures callback details when visitors are interested in your services.

This plugin is the WordPress integration for the [Wemdo AI Layer](https://ai.wmd.hr) SaaS. You'll need an account on ai.wmd.hr (free trial available) to get an API key, then paste it into this plugin.

= Features =

* Multilingual (18+ languages, auto-detects WordPress locale)
* Proactive lead capture for pricing/quote questions
* Smart handoff to human operators when the agent isn't sure
* Page-context aware — recognizes which product page the visitor is on
* Configurable display rules (all pages / posts only / pages only / disabled)
* Per-URL hide rules
* Zero-impact on site performance — widget loads from Wemdo CDN, not your WordPress server
* GDPR-aware with explicit privacy disclosure

= Where do I configure colors, copy, and behavior? =

In your Wemdo admin panel at ai.wmd.hr. The WordPress plugin is intentionally minimal — paste your API key, choose where the widget appears, done. All visual and conversational customization lives in one place to prevent config drift.

== Installation ==

1. Upload the plugin to `/wp-content/plugins/wemdo-ai-agent` or install via WordPress Admin → Plugins → Add New
2. Activate the plugin
3. Go to Settings → Wemdo AI Agent
4. Paste your API key (get one at https://ai.wmd.hr/admin/api-keys)
5. Click Verify → Save Changes
6. Visit your site front-end — the chat bubble should appear in the corner

== Frequently Asked Questions ==

= Where does the chat widget actually live? =

The widget JavaScript is served from Wemdo's CDN (ai.wmd.hr). Your WordPress server only renders a single `<script>` tag in the page `<head>`. Visitor messages go directly from the browser to Wemdo's backend — they don't transit your WordPress server.

= Can I customize the widget colors / greeting / conversation starters? =

Yes — but in your Wemdo admin panel, not in this plugin. This is intentional: a single source of truth prevents config drift if you have multiple sites.

= Does the AI know which product page the visitor is on? =

Yes. The widget sends the current page URL with every request, and the AI extracts the specific product, category, or search context from it. So when a visitor on a product page asks "do you have it in blue?", the agent already knows they mean the product on screen.

= What about WooCommerce / Polylang / WPML / page builders? =

The plugin renders a `<script>` tag in `wp_head`. It doesn't filter `the_content`, doesn't enqueue heavy JS bundles, and doesn't hook into checkout or post rendering. It coexists peacefully with all major plugins.

= What data does the plugin send? =

Two events:
1. **One time, on Save:** your pasted API key is sent to https://ai.wmd.hr/api/wp/verify-key to confirm it's valid.
2. **Every page view:** the rendered HTML contains a `<script>` tag with your API key. The browser fetches the widget JS from Wemdo's CDN. Visitor chat messages go directly browser → Wemdo (your WordPress server is not involved).

== External services ==

This plugin connects to the Wemdo AI Layer service operated by Wemdo (wmd.hr) at:

* **https://ai.wmd.hr/api/wp/verify-key** — sent once when you save your API key in plugin settings, to confirm the key is valid. The request body contains only the API key in the `x-api-key` header. The response contains your tenant ID, name, and plan tier.

* **https://ai.wmd.hr/widget/chat.js** — the widget JavaScript loaded by visitor browsers. The widget then communicates directly with `https://ai.wmd.hr/api/widget/*` for chat functionality. Visitor messages are processed by Wemdo's AI infrastructure.

By using this plugin you agree to:
* Wemdo Privacy Policy: https://ai.wmd.hr/privacy
* Wemdo Data Processing Agreement: https://ai.wmd.hr/dpa

== Screenshots ==

1. Settings page with API key paste + 4 sections
2. Verification feedback ("Connected to tenant X")
3. Widget bubble in front-end corner
4. Open chat conversation example

== Changelog ==

= 1.0.0 =
* First release. Paste API key → embed widget. No KB sync, no Gutenberg block (those land in 1.1+).

== Upgrade Notice ==

= 1.0.0 =
First release.
