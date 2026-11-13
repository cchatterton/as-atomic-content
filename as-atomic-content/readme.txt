=== AS Atomic Content ===
Tags: acf, custom-post-types, content
Requires at least: 6.3
Stable tag: 0.2.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Manage ACF cards and rows alongside native WordPress patterns in one Atoms menu.

== Description ==

AS Atomic Content provides ACF-backed Cards and Rows and links to WordPress-native Patterns. It ships ACF Local JSON for the existing Card Markup field group and post type definitions. The card and row post type slugs, original ACF keys, and card_markup field key are retained for compatibility with existing content.

Advanced Custom Fields 6.1 or later is required for Cards, Rows, and the one-time legacy Pattern migration. On activation or update, legacy Pattern posts are copied to native wp_block patterns with Not synced status. The migration runs in batches, resumes safely, and keeps the source posts for recovery. Atoms > Patterns opens the native pattern manager. The hidden legacy Pattern posts keep their signed-in front-end URLs; anonymous visits return 404. Cards and Rows retain their existing visibility settings.

== Installation ==

1. Install and activate Advanced Custom Fields 6.1 or later.
2. Upload and activate AS Atomic Content.
3. Use Atoms > Cards, Rows, or Patterns in WordPress admin. Patterns opens the native Site Editor.
4. On sites with existing ACF definitions, review ACF's JSON sync screen before making further changes to those definitions.

== Changelog ==

= 0.2.0 =
* Linked Atoms > Patterns to the native WordPress pattern manager.
* Added a resumable activation and update migration that copies legacy Patterns as Not synced native patterns.
* Hid the legacy Pattern editor while retaining the source posts for recovery.

= 0.1.2 =
* Enabled signed-in front-end viewing of Patterns while restricting anonymous pages, REST responses, search, and embeds.
* Marked Pattern pages noindex and excluded them from the core sitemap.

= 0.1.1 =
* Renamed the admin menu to Atoms, added an atom icon, and kept its three links visible on atomic content screens.

= 0.1.0 =
* Initial release with Cards, Rows, Patterns, and the existing Card Markup field group.
