# Changelog

All notable changes to AS Atomic Content are recorded here.

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
