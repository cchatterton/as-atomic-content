=== AS Atomic Content ===
Tags: acf, custom-post-types, content
Requires at least: 6.0
Stable tag: 0.1.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Manage reusable cards, rows, and patterns in one WordPress admin menu using Advanced Custom Fields.

== Description ==

AS Atomic Content provides the card, row, and pattern post types. It ships ACF Local JSON for the existing Card Markup field group and the post type definitions. The card and row post type slugs, original ACF keys, and card_markup field key are retained for compatibility with existing content.

Advanced Custom Fields 6.1 or later is required. ACF must remain active for the post types and field group to be registered. The plugin does not render these posts on the front end; existing themes and integrations can continue to consume them.

== Installation ==

1. Install and activate Advanced Custom Fields 6.1 or later.
2. Upload and activate AS Atomic Content.
3. Use Atomic Content > Cards, Rows, or Patterns in WordPress admin.
4. On sites with existing ACF definitions, review ACF's JSON sync screen before making further changes to those definitions.

== Changelog ==

= 0.1.0 =
* Initial release with Cards, Rows, Patterns, and the existing Card Markup field group.
