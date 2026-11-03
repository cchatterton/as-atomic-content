# AS Atomic Content

AlphaSys WordPress plugin, version 0.1.1. Requires Advanced Custom Fields 6.1 or later.

The plugin registers `card`, `row`, and `pattern` through ACF Local JSON. It keeps the supplied ACF keys for `card`, `row`, and the Card Markup field group, including the `card_markup` field key. It changes only their admin menu placement and adds `pattern` with row-like editor support. Content is not migrated or deleted.

The WordPress admin menu contains one **Atoms** parent and exactly three child links: **Cards**, **Rows**, and **Patterns**. On those post screens, the menu opens and the desktop sidebar scrolls to the Atoms section. Creation remains available from each post list and editor, but there are no separate creation links in the menu.

ACF is a runtime dependency. If it is inactive, the post types and field group are unavailable and administrators see a notice. Existing sites with ACF post type definitions in the database should review ACF's JSON sync screen so their stored definitions match the plugin package.

The interface uses WordPress-native admin styling (Author Branded mode, with AlphaSys identified in plugin metadata). This plugin does not provide front-end templates or rendering functions.

Build an installable ZIP with `scripts/build-plugin-zip.sh`. The archive has `as-atomic-content/` as its top-level folder.
