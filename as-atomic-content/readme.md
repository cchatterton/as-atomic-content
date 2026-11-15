# AS Atomic Content

AlphaSys WordPress plugin, version 0.2.2. Requires WordPress 6.3 or later and Advanced Custom Fields 6.1 or later for Cards, Rows, and legacy Pattern migration.

The plugin registers `card` and `row` through ACF Local JSON. It keeps the supplied ACF keys and the Card Markup field group, including the `card_markup` field key. Legacy `pattern` posts are copied to native WordPress `wp_block` patterns with `wp_pattern_sync_status` set to `unsynced`. The original posts are retained for recovery.

The WordPress admin menu contains one **Atoms** parent with **Cards**, **Rows**, **Patterns**, and **Pattern Categories**. Patterns opens the native Site Editor pattern manager. Pattern Categories opens WordPress's native category manager at `edit-tags.php?taxonomy=wp_pattern_category&post_type=wp_block` for users with category-management permission. The back arrow on the Patterns list returns to the WordPress Dashboard. On Atoms admin screens, the menu opens and the desktop sidebar scrolls to Atoms. Creation remains available from the respective editors, without separate creation links in the menu.

To categorise a pattern, open it in the Site Editor, expand **Pattern Categories**, type a category name and press **Enter** (or comma) to add it to the pattern, then click **Save**. Text left in the input has not been assigned and does not enable Save. Use **Atoms > Pattern Categories** to create, rename, or delete categories. The manager's Count column shows how many patterns are assigned to each category.

ACF is a runtime dependency for Cards, Rows, and the legacy Pattern migration. If it is inactive, those post types and field group are unavailable and administrators see a notice; native Patterns remain available. Existing sites with ACF post type definitions in the database should review ACF's JSON sync screen so their stored definitions match the plugin package. The migration runs automatically on activation or after an update in batches of 25. Its completion marker and source-to-destination post meta prevent duplicate copies on retry. Errors are reported in WordPress admin; the old posts remain unchanged.

The interface uses WordPress-native admin styling (Author Branded mode, with AlphaSys identified in plugin metadata). The hidden legacy Pattern posts retain their signed-in front-end URLs for compatibility; anonymous requests return 404. The plugin marks those legacy pages noindex and excludes them from core search and sitemaps. Native patterns are managed in the Site Editor, not as public post pages. Files attached or embedded in a Pattern retain their normal WordPress media access rules.

Build an installable ZIP with `scripts/build-plugin-zip.sh`. The archive has `as-atomic-content/` as its top-level folder.
