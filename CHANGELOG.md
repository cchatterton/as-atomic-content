# Changelog

All notable changes to AS Atomic Content are recorded here.

## 0.2.5 - 2026-11-15

- Added Settings > Pattern Categories, linking to WordPress's native pattern category manager.
- Kept Settings selected while managing pattern categories; Atoms still has only Cards, Rows, and Patterns.

## 0.2.4 - 2026-11-15

- Removed Pattern Categories from the Atoms submenu, restoring its three content links.
- Clarified that a new category must be committed with Enter or comma before saving a pattern.

## 0.2.3 - 2026-11-15

- Used the native pattern taxonomy's configured management capability for the Atoms menu link.

## 0.2.2 - 2026-11-15

- Linked Atoms > Pattern Categories to WordPress's native pattern category manager.
- Kept Atoms visible and selected on the category manager screen.
- Documented how to commit and save category assignments in the native pattern editor.

## 0.2.1 - 2026-11-14

- Changed the native Patterns list back arrow to return to the WordPress Dashboard.

## 0.2.0 - 2026-11-13

- Linked Atoms > Patterns to the native WordPress pattern manager.
- Added automatic, resumable migration of legacy Pattern posts to native unsynced patterns on activation or update.
- Hid the legacy Pattern editor while retaining source posts and signed-in URLs for recovery.

## 0.1.2 - 2026-11-04

- Enabled front-end Pattern viewing for signed-in users.
- Returned 404 to anonymous Pattern requests and blocked anonymous REST, search, and oEmbed exposure.
- Marked Pattern pages noindex and excluded them from the core sitemap.

## 0.1.1 - 2026-11-03

- Renamed the WordPress admin menu to Atoms and added an atom icon.
- Kept the Cards, Rows, and Patterns submenu visible and scrolled the desktop sidebar to it on those screens.

## 0.1.0 - 2026-11-02

- Added ACF-backed Cards, Rows, and Patterns post types under one WordPress admin menu.
- Preserved the supplied Card Markup field group and the existing card and row ACF identifiers.
